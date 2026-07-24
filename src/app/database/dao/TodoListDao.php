<?php

declare(strict_types=1);

namespace app\database\dao;

use app\database\model\TodoListModel;
use nova\plugin\orm\object\Dao;
use nova\plugin\orm\operation\SelectOperation;

class TodoListDao extends Dao
{
    /**
     * @return TodoListModel[]
     */
    public function listByUser(int $userId): array
    {
        return $this->select()
            ->where(['user_id' => $userId])
            ->orderBy('is_default', SelectOperation::SORT_DESC)
            ->orderBy('sort_order', SelectOperation::SORT_ASC)
            ->orderBy('id', SelectOperation::SORT_ASC)
            ->commit();
    }

    public function findOwned(int $id, int $userId): ?TodoListModel
    {
        /** @var TodoListModel|null $list */
        $list = $this->find(null, ['id' => $id, 'user_id' => $userId]);
        return $list;
    }

    public function findDefault(int $userId): ?TodoListModel
    {
        /** @var TodoListModel|null $list */
        $list = $this->find(null, ['user_id' => $userId, 'is_default' => 1]);
        return $list;
    }

    public function ensureDefault(int $userId): TodoListModel
    {
        $existing = $this->findDefault($userId);
        if ($existing !== null) {
            return $existing;
        }

        $lists = $this->listByUser($userId);
        if (!empty($lists)) {
            return $lists[0];
        }

        $list = new TodoListModel();
        $list->user_id = $userId;
        $list->title = '任务';
        $list->sort_order = 0;
        $list->is_default = 1;
        $list->created_at = time();
        $list->id = $this->insertModel($list);
        return $list;
    }

    public function nextSortOrder(int $userId): int
    {
        $lists = $this->listByUser($userId);
        $max = 0;
        foreach ($lists as $list) {
            if ($list->sort_order > $max) {
                $max = $list->sort_order;
            }
        }
        return $max + 1;
    }
}
