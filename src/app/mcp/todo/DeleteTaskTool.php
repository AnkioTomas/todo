<?php

declare(strict_types=1);

namespace app\mcp\todo;

use app\todo\TodoOps;
use nova\plugin\mcp\McpTool;

class DeleteTaskTool extends McpTool
{
    public function __construct(private int $userId)
    {
        parent::__construct('delete_task', '删除待办任务', [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => '任务 ID',
                ],
            ],
            'required' => ['id'],
        ]);
    }

    public function execute(array $arguments): array
    {
        $id = (int)($arguments['id'] ?? 0);
        TodoOps::deleteTask($this->userId, $id);

        return TodoFormat::text(['deleted' => true, 'id' => $id]);
    }
}
