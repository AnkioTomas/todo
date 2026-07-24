<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\dao\McpAuthDao;
use app\mcp\todo\CompleteTaskTool;
use app\mcp\todo\CreateListTool;
use app\mcp\todo\CreateTaskTool;
use app\mcp\todo\DeleteTaskTool;
use app\mcp\todo\ListListsTool;
use app\mcp\todo\ListTasksTool;
use app\mcp\todo\UpdateTaskTool;
use nova\framework\http\Response;
use nova\plugin\mcp\McpController;
use nova\plugin\mcp\McpResponse;
use nova\plugin\mcp\McpServer;

/**
 * Todo MCP 入口：Bearer token 鉴权后暴露列表/任务工具。
 */
class Mcp extends McpController
{
    protected function createMcpServer(): McpServer
    {
        return new McpServer('Todo', '1.0.0', '待办列表与任务管理');
    }

    protected function registerComponents(): void
    {
        // 延迟到 handleMcpRequest 中，获取到用户身份后再注册
    }

    public function handleMcpRequest(): Response
    {
        $userId = $this->authenticate();
        if ($userId === null) {
            return McpResponse::error($this->mcpRequest->getId(), -32001, 'Unauthorized: invalid or missing MCP token');
        }

        $this->mcpServer
            ->registerTool(new ListListsTool($userId))
            ->registerTool(new CreateListTool($userId))
            ->registerTool(new ListTasksTool($userId))
            ->registerTool(new CreateTaskTool($userId))
            ->registerTool(new UpdateTaskTool($userId))
            ->registerTool(new CompleteTaskTool($userId))
            ->registerTool(new DeleteTaskTool($userId));

        return parent::handleMcpRequest();
    }

    private function authenticate(): ?int
    {
        $token = $this->extractToken();
        if ($token === null || $token === '') {
            return null;
        }

        $auth = McpAuthDao::getInstance()->findByToken($token);
        if ($auth === null) {
            return null;
        }

        return $auth->user_id;
    }

    private function extractToken(): ?string
    {
        $auth = (string)(
            $this->request->getHeaderValue('Auth')
            ?? $this->request->getHeaderValue('AUTH')
            ?? $this->request->getHeaderValue('Authorization')
            ?? $this->request->getHeaderValue('AUTHORIZATION')
            ?? ''
        );
        
        // 兼容 Bearer 格式和纯 Auth 头部格式
        if (preg_match('/^Bearer\s+(\S+)$/i', $auth, $matches) === 1) {
            return $matches[1];
        }

        if ($auth !== '') {
            return $auth; // 直接作为 Token
        }

        $queryToken = trim((string)$this->request->get('token', ''));
        return $queryToken !== '' ? $queryToken : null;
    }
}
