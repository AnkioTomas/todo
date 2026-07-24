<?php

declare(strict_types=1);

namespace app\database\dao;

use app\database\model\IcsTokenModel;
use nova\plugin\orm\object\Dao;

class IcsTokenDao extends Dao
{
    public function findByUser(int $userId): ?IcsTokenModel
    {
        /** @var IcsTokenModel|null $row */
        $row = $this->find(null, ['user_id' => $userId]);
        return $row;
    }

    public function findByToken(string $token): ?IcsTokenModel
    {
        if ($token === '') {
            return null;
        }
        /** @var IcsTokenModel|null $row */
        $row = $this->find(null, ['token' => $token]);
        return $row;
    }

    public function ensureToken(int $userId): IcsTokenModel
    {
        $existing = $this->findByUser($userId);
        if ($existing !== null) {
            return $existing;
        }
        return $this->createToken($userId);
    }

    public function resetToken(int $userId): IcsTokenModel
    {
        $existing = $this->findByUser($userId);
        if ($existing !== null) {
            $this->delete()->where(['id' => $existing->id])->commit();
        }
        return $this->createToken($userId);
    }

    private function createToken(int $userId): IcsTokenModel
    {
        $row = new IcsTokenModel();
        $row->user_id = $userId;
        $row->token = bin2hex(random_bytes(32));
        $row->created_at = time();
        $row->id = $this->insertModel($row);
        return $row;
    }
}
