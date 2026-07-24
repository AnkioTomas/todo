<?php

declare(strict_types=1);

namespace app\mcp\todo;

use app\todo\TodoOps;
use nova\plugin\mcp\McpTool;

class ListTasksTool extends McpTool
{
    public function __construct(private int $userId)
    {
        parent::__construct('list_tasks', '按视图或列表查询待办任务', [
            'type' => 'object',
            'properties' => [
                'view' => [
                    'type' => 'string',
                    'enum' => ['today', 'important', 'planned', 'list'],
                    'description' => '视图：today=我的一天, important=重要, planned=已计划, list=指定列表',
                    'default' => 'list',
                ],
                'list_id' => [
                    'type' => 'integer',
                    'description' => '列表 ID（view=list 时使用，0 表示默认列表）',
                    'default' => 0,
                ],
            ],
        ]);
    }

    public function execute(array $arguments): array
    {
        $result = TodoOps::listTasks(
            $this->userId,
            (string)($arguments['view'] ?? 'list'),
            (int)($arguments['list_id'] ?? 0)
        );

        return TodoFormat::text([
            'view' => $result['view'],
            'list_id' => $result['list_id'],
            'tasks' => array_map(static fn ($task) => TodoFormat::task($task), $result['tasks']),
        ]);
    }
}
