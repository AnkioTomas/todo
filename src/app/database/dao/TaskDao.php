<?php

declare(strict_types=1);

namespace app\database\dao;

use app\database\model\TaskModel;
use nova\plugin\orm\object\Dao;
use nova\plugin\orm\operation\SelectOperation;

class TaskDao extends Dao
{
    /**
     * @return TaskModel[]
     */
    public function listByView(int $userId, string $view, int $listId = 0): array
    {
        $where = ['user_id' => $userId];

        switch ($view) {
            case 'today':
                $where['my_day_date'] = (int)date('Ymd');
                $where['completed'] = 0;
                break;
            case 'important':
                $where['important'] = 1;
                $where['completed'] = 0;
                break;
            case 'planned':
                $where[] = 'due_at > 0';
                $where['completed'] = 0;
                break;
            case 'list':
            default:
                $where['list_id'] = $listId;
                break;
        }

        return $this->select()
            ->where($where)
            ->orderBy('completed', SelectOperation::SORT_ASC)
            ->orderBy('sort_order', SelectOperation::SORT_ASC)
            ->orderBy('id', SelectOperation::SORT_DESC)
            ->commit();
    }

    public function findOwned(int $id, int $userId): ?TaskModel
    {
        /** @var TaskModel|null $task */
        $task = $this->find(null, ['id' => $id, 'user_id' => $userId]);
        return $task;
    }

    /**
     * @return TaskModel[]
     */
    public function listForIcs(int $userId): array
    {
        return $this->select()
            ->where([
                'user_id' => $userId,
                'completed' => 0,
                'due_at > 0',
            ])
            ->orderBy('due_at', SelectOperation::SORT_ASC)
            ->commit();
    }

    public function nextSortOrder(int $userId, int $listId): int
    {
        /** @var TaskModel[] $tasks */
        $tasks = $this->select()
            ->where(['user_id' => $userId, 'list_id' => $listId])
            ->orderBy('sort_order', SelectOperation::SORT_DESC)
            ->limit(1)
            ->commit();

        if (empty($tasks)) {
            return 1;
        }
        return $tasks[0]->sort_order + 1;
    }

    public function deleteByList(int $listId, int $userId): void
    {
        $this->delete()->where(['list_id' => $listId, 'user_id' => $userId])->commit();
    }
}
