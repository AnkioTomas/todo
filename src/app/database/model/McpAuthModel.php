<?php

declare(strict_types=1);

namespace app\database\model;

use nova\plugin\orm\object\Model;

class McpAuthModel extends Model
{
    public int $id = 0;
    public int $user_id = 0;
    public string $token = '';
    public int $created_at = 0;
    public int $updated_at = 0;

    public function getUnique(): array
    {
        return ['user_id' ,'token' ];
    }
}
