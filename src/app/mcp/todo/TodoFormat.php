<?php

declare(strict_types=1);

namespace app\mcp\todo;

use app\database\model\TaskModel;
use app\database\model\TodoListModel;

final class TodoFormat
{
    public static function task(TaskModel $task): array
    {
        return [
            'id' => $task->id,
            'list_id' => $task->list_id,
            'title' => $task->title,
            'note' => $task->note,
            'important' => $task->important === 1,
            'completed' => $task->completed === 1,
            'due_at' => $task->due_at > 0 ? date('Y-m-d', $task->due_at) : null,
            'my_day' => $task->my_day_date === (int)date('Ymd'),
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at,
        ];
    }

    public static function list(TodoListModel $list): array
    {
        return [
            'id' => $list->id,
            'title' => $list->title,
            'is_default' => $list->is_default === 1,
            'sort_order' => $list->sort_order,
        ];
    }

    public static function text(mixed $data): array
    {
        return [
            'content' => [[
                'type' => 'text',
                'text' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]],
        ];
    }
}
