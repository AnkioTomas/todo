<?php

declare(strict_types=1);

namespace app\mcp\todo;

use app\todo\TodoOps;
use nova\plugin\mcp\McpTool;

class ListListsTool extends McpTool
{
    public function __construct(private int $userId)
    {
        parent::__construct('list_lists', '列出当前用户的所有待办列表', [
            'type' => 'object',
            'properties' => (object)[],
        ]);
    }

    public function execute(array $arguments): array
    {
        $lists = TodoOps::listLists($this->userId);

        return TodoFormat::text([
            'lists' => array_map(static fn ($list) => TodoFormat::list($list), $lists),
        ]);
    }
}
