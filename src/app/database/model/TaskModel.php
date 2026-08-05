<?php

declare(strict_types=1);

namespace app\database\model;

use nova\plugin\orm\object\Model;

class TaskModel extends Model
{
    public int $user_id = 0;
    public int $list_id = 0;
    public string $title = '';
    public string $note = '';
    public int $important = 0;
    public int $completed = 0;
    public int $due_at = 0;
    public int $my_day_date = 0;
    public int $sort_order = 0;
    public int $completed_at = 0;
    public int $created_at = 0;
    public int $updated_at = 0;

    public function getUnique(): array
    {
        return [];
    }

    public function getSchemaVersion(): int
    {
        return 1;
    }
}
