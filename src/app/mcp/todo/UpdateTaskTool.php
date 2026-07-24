<?php

declare(strict_types=1);

namespace app\mcp\todo;

use app\todo\TodoOps;
use nova\plugin\mcp\McpTool;

class UpdateTaskTool extends McpTool
{
    public function __construct(private int $userId)
    {
        parent::__construct('update_task', '更新待办任务（标题/备注/重要/截止/我的一天/列表）', [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => '任务 ID',
                ],
                'title' => [
                    'type' => 'string',
                    'description' => '新标题',
                ],
                'note' => [
                    'type' => 'string',
                    'description' => '新备注',
                ],
                'important' => [
                    'type' => 'boolean',
                    'description' => '是否重要',
                ],
                'my_day' => [
                    'type' => 'boolean',
                    'description' => '是否加入我的一天',
                ],
                'due_at' => [
                    'type' => 'string',
                    'description' => '截止日期 Y-m-d；传空字符串清除截止',
                ],
                'list_id' => [
                    'type' => 'integer',
                    'description' => '移动到的列表 ID',
                ],
            ],
            'required' => ['id'],
        ]);
    }

    public function execute(array $arguments): array
    {
        $id = (int)($arguments['id'] ?? 0);
        $patch = [];
        foreach (['title', 'note', 'important', 'my_day', 'due_at', 'list_id'] as $key) {
            if (array_key_exists($key, $arguments)) {
                $patch[$key] = $arguments[$key];
            }
        }

        $task = TodoOps::updateTask($this->userId, $id, $patch);

        return TodoFormat::text(['task' => TodoFormat::task($task)]);
    }
}
