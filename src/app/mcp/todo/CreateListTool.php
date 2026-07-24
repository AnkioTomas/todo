<?php

declare(strict_types=1);

namespace app\mcp\todo;

use app\todo\TodoOps;
use nova\plugin\mcp\McpTool;

class CreateListTool extends McpTool
{
    public function __construct(private int $userId)
    {
        parent::__construct('create_list', '创建待办列表', [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => '列表名称',
                ],
            ],
            'required' => ['title'],
        ]);
    }

    public function execute(array $arguments): array
    {
        $list = TodoOps::createList($this->userId, (string)($arguments['title'] ?? ''));

        return TodoFormat::text(['list' => TodoFormat::list($list)]);
    }
}
