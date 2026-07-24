<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\dao\TaskDao;
use app\database\dao\TodoListDao;
use app\database\model\TaskModel;
use nova\framework\http\Response;
use nova\plugin\login\controller\BaseAPIController;

class TaskApi extends BaseAPIController
{
    public function index(): Response
    {
        $view = $this->request->get('view', 'list');
        $listId = $this->request->get('list_id', 0);
        $userId = $this->userModel->id;

        if (!in_array($view, ['today', 'important', 'planned', 'list'], true)) {
            $view = 'list';
        }

        if ($view === 'list') {
            if ($listId <= 0) {
                $default = TodoListDao::getInstance()->ensureDefault($userId);
                $listId = $default->id;
            } elseif (TodoListDao::getInstance()->findOwned($listId, $userId) === null) {
                return Response::asJson(['code' => 404, 'msg' => '列表不存在', 'data' => []]);
            }
        }

        $tasks = TaskDao::getInstance()->listByView($userId, $view, $listId);

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => array_map(static fn (TaskModel $task) => $task->toArray(false), $tasks),
        ]);
    }

    public function detail(): Response
    {
        $id = (int)$this->request->get('id', 0);
        if ($id <= 0) {
            return Response::asJson(['code' => 400, 'msg' => '参数错误', 'data' => []]);
        }

        $task = TaskDao::getInstance()->findOwned($id, $this->userModel->id);
        if ($task === null) {
            return Response::asJson(['code' => 404, 'msg' => '任务不存在', 'data' => []]);
        }

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => $task->toArray(false),
        ]);
    }

    public function create(): Response
    {
        $listId = (int)$this->request->post('list_id', 0);
        $title = trim((string)$this->request->post('title', ''));
        if ($title === '') {
            return Response::asJson(['code' => 400, 'msg' => '任务标题不能为空', 'data' => []]);
        }

        $userId = $this->userModel->id;
        if ($listId <= 0) {
            $listId = TodoListDao::getInstance()->ensureDefault($userId)->id;
        } elseif (TodoListDao::getInstance()->findOwned($listId, $userId) === null) {
            return Response::asJson(['code' => 404, 'msg' => '列表不存在', 'data' => []]);
        }

        $now = time();
        $task = new TaskModel();
        $task->user_id = $userId;
        $task->list_id = $listId;
        $task->title = $title;
        $task->note = '';
        $task->important = ((int)$this->request->post('important', 0)) === 1 ? 1 : 0;
        $task->completed = 0;
        $task->due_at = 0;
        $task->my_day_date = ((int)$this->request->post('my_day', 0)) === 1 ? (int)date('Ymd') : 0;
        $task->sort_order = TaskDao::getInstance()->nextSortOrder($userId, $listId);
        $task->completed_at = 0;
        $task->created_at = $now;
        $task->updated_at = $now;
        $task->id = TaskDao::getInstance()->insertModel($task);

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => $task->toArray(false),
        ]);
    }

    public function update(): Response
    {
        $id = (int)$this->request->post('id', 0);
        if ($id <= 0) {
            return Response::asJson(['code' => 400, 'msg' => '参数错误', 'data' => []]);
        }

        $task = TaskDao::getInstance()->findOwned($id, $this->userModel->id);
        if ($task === null) {
            return Response::asJson(['code' => 404, 'msg' => '任务不存在', 'data' => []]);
        }

        $post = $this->request->post();
        if (!is_array($post)) {
            $post = [];
        }

        if (array_key_exists('title', $post)) {
            $title = trim((string)$post['title']);
            if ($title === '') {
                return Response::asJson(['code' => 400, 'msg' => '任务标题不能为空', 'data' => []]);
            }
            $task->title = $title;
        }

        if (array_key_exists('note', $post)) {
            $task->note = (string)$post['note'];
        }

        if (array_key_exists('important', $post)) {
            $task->important = ((int)$post['important']) === 1 ? 1 : 0;
        }

        if (array_key_exists('completed', $post)) {
            $completed = ((int)$post['completed']) === 1 ? 1 : 0;
            $task->completed = $completed;
            $task->completed_at = $completed === 1 ? time() : 0;
        }

        if (array_key_exists('due_at', $post)) {
            $task->due_at = $this->parseDueAt($post['due_at']);
        }

        if (array_key_exists('my_day', $post)) {
            $task->my_day_date = ((int)$post['my_day']) === 1 ? (int)date('Ymd') : 0;
        }

        if (array_key_exists('list_id', $post)) {
            $listId = (int)$post['list_id'];
            if ($listId <= 0 || TodoListDao::getInstance()->findOwned($listId, $this->userModel->id) === null) {
                return Response::asJson(['code' => 404, 'msg' => '列表不存在', 'data' => []]);
            }
            $task->list_id = $listId;
        }

        $task->updated_at = time();
        TaskDao::getInstance()->updateModel($task);

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => $task->toArray(false),
        ]);
    }

    public function delete(): Response
    {
        $id = (int)$this->request->post('id', 0);
        if ($id <= 0) {
            return Response::asJson(['code' => 400, 'msg' => '参数错误', 'data' => []]);
        }

        $task = TaskDao::getInstance()->findOwned($id, $this->userModel->id);
        if ($task === null) {
            return Response::asJson(['code' => 404, 'msg' => '任务不存在', 'data' => []]);
        }

        TaskDao::getInstance()->delete()->where(['id' => $task->id, 'user_id' => $this->userModel->id])->commit();

        return Response::asJson(['code' => 200, 'msg' => 'success', 'data' => []]);
    }

    private function parseDueAt(mixed $value): int
    {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            return 0;
        }

        if (is_numeric($value)) {
            $ts = (int)$value;
            return $ts > 0 ? $ts : 0;
        }

        $date = trim((string)$value);
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $date, new \DateTimeZone(date_default_timezone_get()));
        if ($dt === false) {
            return 0;
        }

        return $dt->setTime(23, 59, 59)->getTimestamp();
    }
}
