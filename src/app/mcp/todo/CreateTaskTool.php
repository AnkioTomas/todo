<?php

declare(strict_types=1);

namespace app\mcp\todo;

use app\todo\TodoOps;
use nova\plugin\mcp\McpTool;

class CreateTaskTool extends McpTool
{
    public function __construct(private int $userId)
    {
        parent::__construct('create_task', '新增待办任务', [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => '任务标题',
                ],
                'list_id' => [
                    'type' => 'integer',
                    'description' => '所属列表 ID，省略则写入默认列表',
                ],
                'note' => [
                    'type' => 'string',
                    'description' => '备注',
                ],
                'important' => [
                    'type' => 'boolean',
                    'description' => '是否标为重要',
                    'default' => false,
                ],
                'my_day' => [
                    'type' => 'boolean',
                    'description' => '是否加入我的一天',
                    'default' => false,
                ],
                'due_at' => [
                    'type' => 'string',
                    'description' => '截止日期，格式 Y-m-d，或留空',
                ],
            ],
            'required' => ['title'],
        ]);
    }

    public function execute(array $arguments): array
    {
        $task = TodoOps::createTask($this->userId, (string)($arguments['title'] ?? ''), [
            'list_id' => (int)($arguments['list_id'] ?? 0),
            'note' => (string)($arguments['note'] ?? ''),
            'important' => $arguments['important'] ?? false,
            'my_day' => $arguments['my_day'] ?? false,
            'due_at' => $arguments['due_at'] ?? null,
        ]);

        return TodoFormat::text(['task' => TodoFormat::task($task)]);
    }
}
