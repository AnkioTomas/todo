<?php

declare(strict_types=1);

namespace app\todo;

use app\database\dao\TaskDao;
use app\database\dao\TodoListDao;
use app\database\model\TaskModel;
use app\database\model\TodoListModel;

/**
 * 待办业务操作：HTTP API 与 MCP 共用，禁止再抄一份。
 */
final class TodoOps
{
    public static function parseDueAt(mixed $value): int
    {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            return 0;
        }

        if (is_numeric($value)) {
            $ts = (int)$value;
            return max($ts, 0);
        }

        $date = trim((string)$value);
        $dt = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $date,
            new \DateTimeZone(date_default_timezone_get())
        );
        if ($dt === false) {
            return 0;
        }

        return $dt->setTime(23, 59, 59)->getTimestamp();
    }

    /**
     * @return array{view: string, list_id: ?int, tasks: TaskModel[]}
     */
    public static function listTasks(int $userId, string $view, int $listId = 0): array
    {
        if (!in_array($view, ['today', 'important', 'planned', 'list'], true)) {
            $view = 'list';
        }

        if ($view === 'list') {
            $listId = self::resolveListId($userId, $listId);
        }

        return [
            'view' => $view,
            'list_id' => $view === 'list' ? $listId : null,
            'tasks' => TaskDao::getInstance()->listByView($userId, $view, $listId),
        ];
    }

    /**
     * @param array{list_id?: int, note?: string, important?: mixed, my_day?: mixed, due_at?: mixed} $opts
     */
    public static function createTask(int $userId, string $title, array $opts = []): TaskModel
    {
        $title = trim($title);
        if ($title === '') {
            throw new \RuntimeException('任务标题不能为空', 400);
        }

        $listId = self::resolveListId($userId, (int)($opts['list_id'] ?? 0));
        $now = time();

        $task = new TaskModel();
        $task->user_id = $userId;
        $task->list_id = $listId;
        $task->title = $title;
        $task->note = trim((string)($opts['note'] ?? ''));
        $task->important = self::asFlag($opts['important'] ?? false);
        $task->completed = 0;
        $task->due_at = self::parseDueAt($opts['due_at'] ?? null);
        $task->my_day_date = self::asFlag($opts['my_day'] ?? false) === 1 ? (int)date('Ymd') : 0;
        $task->sort_order = TaskDao::getInstance()->nextSortOrder($userId, $listId);
        $task->completed_at = 0;
        $task->created_at = $now;
        $task->updated_at = $now;
        $task->id = TaskDao::getInstance()->insertModel($task);

        return $task;
    }

    /**
     * @param array<string, mixed> $patch 支持 title/note/important/completed/due_at/my_day/list_id
     */
    public static function updateTask(int $userId, int $id, array $patch): TaskModel
    {
        if ($id <= 0) {
            throw new \RuntimeException('参数错误', 400);
        }

        $task = TaskDao::getInstance()->findOwned($id, $userId);
        if ($task === null) {
            throw new \RuntimeException('任务不存在', 404);
        }

        if (array_key_exists('title', $patch)) {
            $title = trim((string)$patch['title']);
            if ($title === '') {
                throw new \RuntimeException('任务标题不能为空', 400);
            }
            $task->title = $title;
        }

        if (array_key_exists('note', $patch)) {
            $task->note = (string)$patch['note'];
        }

        if (array_key_exists('important', $patch)) {
            $task->important = self::asFlag($patch['important']);
        }

        if (array_key_exists('completed', $patch)) {
            $completed = self::asFlag($patch['completed']);
            $task->completed = $completed;
            $task->completed_at = $completed === 1 ? time() : 0;
        }

        if (array_key_exists('due_at', $patch)) {
            $task->due_at = self::parseDueAt($patch['due_at']);
        }

        if (array_key_exists('my_day', $patch)) {
            $task->my_day_date = self::asFlag($patch['my_day']) === 1 ? (int)date('Ymd') : 0;
        }

        if (array_key_exists('list_id', $patch)) {
            $task->list_id = self::resolveListId($userId, (int)$patch['list_id'], false);
        }

        $task->updated_at = time();
        TaskDao::getInstance()->updateModel($task);

        return $task;
    }

    public static function getTask(int $userId, int $id): TaskModel
    {
        if ($id <= 0) {
            throw new \RuntimeException('参数错误', 400);
        }

        $task = TaskDao::getInstance()->findOwned($id, $userId);
        if ($task === null) {
            throw new \RuntimeException('任务不存在', 404);
        }

        return $task;
    }

    public static function deleteTask(int $userId, int $id): void
    {
        $task = self::getTask($userId, $id);
        TaskDao::getInstance()->delete()->where(['id' => $task->id, 'user_id' => $userId])->commit();
    }

    /**
     * @return TodoListModel[]
     */
    public static function listLists(int $userId): array
    {
        TodoListDao::getInstance()->ensureDefault($userId);
        return TodoListDao::getInstance()->listByUser($userId);
    }

    public static function createList(int $userId, string $title): TodoListModel
    {
        $title = trim($title);
        if ($title === '') {
            throw new \RuntimeException('列表名称不能为空', 400);
        }

        TodoListDao::getInstance()->ensureDefault($userId);

        $list = new TodoListModel();
        $list->user_id = $userId;
        $list->title = $title;
        $list->sort_order = TodoListDao::getInstance()->nextSortOrder($userId);
        $list->is_default = 0;
        $list->created_at = time();
        $list->id = TodoListDao::getInstance()->insertModel($list);

        return $list;
    }

    /**
     * @param bool $allowDefault list_id<=0 时是否回落到默认列表；移动任务时应为 false
     */
    public static function resolveListId(int $userId, int $listId, bool $allowDefault = true): int
    {
        if ($listId <= 0) {
            if (!$allowDefault) {
                throw new \RuntimeException('列表不存在', 404);
            }
            return TodoListDao::getInstance()->ensureDefault($userId)->id;
        }

        if (TodoListDao::getInstance()->findOwned($listId, $userId) === null) {
            throw new \RuntimeException('列表不存在', 404);
        }

        return $listId;
    }

    private static function asFlag(mixed $value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        return ((int)$value) === 1 || $value === '1' || $value === 'true' ? 1 : 0;
    }
}
