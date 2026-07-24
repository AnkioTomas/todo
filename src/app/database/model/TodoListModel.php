<?php

declare(strict_types=1);

namespace app\database\model;

use nova\plugin\orm\object\Model;

class TodoListModel extends Model
{
    public int $user_id = 0;
    public string $title = '';
    public int $sort_order = 0;
    public int $is_default = 0;
    public int $created_at = 0;

    public function getUnique(): array
    {
        return [];
    }

    public function getSchemaVersion(): int
    {
        return 1;
    }
}
