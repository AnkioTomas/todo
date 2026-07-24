<?php

declare(strict_types=1);

namespace app\database\dao;

use app\database\model\McpAuthModel;
use nova\plugin\orm\object\Dao;

class McpAuthDao extends Dao
{
    /**
     * 根据 user_id 查找认证记录
     */
    public function findByUserId(int $userId): ?McpAuthModel
    {
        return $this->find(null, ['user_id' => $userId]);
    }

    /**
     * 根据 token 查找认证记录
     */
    public function findByToken(string $token): ?McpAuthModel
    {
        return $this->find(null, ['token' => $token]);
    }

    /**
     * 生成或重置用户的 Token
     */
    public function generateToken(int $userId): string
    {
        $token = bin2hex(random_bytes(16));
        $now = time();
        $auth = $this->findByUserId($userId);

        if ($auth !== null) {
            $auth->token = $token;
            $auth->updated_at = $now;
            $this->updateModel($auth);
        } else {
            $auth = new McpAuthModel();
            $auth->user_id = $userId;
            $auth->token = $token;
            $auth->created_at = $now;
            $auth->updated_at = $now;
            $this->insertModel($auth);
        }

        return $token;
    }
}
