<?php

declare(strict_types=1);

namespace app\mcp\todo;

use app\todo\TodoOps;
use nova\plugin\mcp\McpTool;

class CompleteTaskTool extends McpTool
{
    public function __construct(private int $userId)
    {
        parent::__construct('complete_task', '标记任务完成或取消完成', [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => '任务 ID',
                ],
                'completed' => [
                    'type' => 'boolean',
                    'description' => 'true=完成，false=取消完成',
                    'default' => true,
                ],
            ],
            'required' => ['id'],
        ]);
    }

    public function execute(array $arguments): array
    {
        $completed = array_key_exists('completed', $arguments)
            ? $arguments['completed']
            : true;

        $task = TodoOps::updateTask($this->userId, (int)($arguments['id'] ?? 0), [
            'completed' => $completed,
        ]);

        return TodoFormat::text(['task' => TodoFormat::task($task)]);
    }
}
