<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\dao\McpAuthDao;
use nova\framework\http\Response;
use nova\plugin\login\controller\BaseAPIController;

class McpApi extends BaseAPIController
{
    /**
     * 获取当前用户的 MCP 配置
     */
    public function getConfig(): Response
    {
        $userId = $this->userModel->id;
        $auth = McpAuthDao::getInstance()->findByUserId($userId);
        $token = $auth === null
            ? McpAuthDao::getInstance()->generateToken($userId)
            : $auth->token;

        $url = $this->request->getBasicAddress() . '/mcp';

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => [
                'mcpServers' => [
                    'todo' => [
                        'url' => $url,
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                        ],
                    ],
                ],
                'url' => $url,
                'token' => $token,
            ],
        ]);
    }

    /**
     * 重置 Token
     */
    public function resetToken(): Response
    {
        $token = McpAuthDao::getInstance()->generateToken($this->userModel->id);

        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => ['token' => $token],
        ]);
    }
}
