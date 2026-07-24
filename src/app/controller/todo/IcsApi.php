<?php

declare(strict_types=1);

namespace app\controller\todo;

use app\database\dao\IcsTokenDao;
use nova\framework\http\Response;
use nova\plugin\login\controller\BaseAPIController;

class IcsApi extends BaseAPIController
{
    public function info(): Response
    {
        $token = IcsTokenDao::getInstance()->ensureToken($this->userModel->id);
        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => [
                'token' => $token->token,
                'url' => $this->buildUrl($token->token),
            ],
        ]);
    }

    public function reset(): Response
    {
        $token = IcsTokenDao::getInstance()->resetToken($this->userModel->id);
        return Response::asJson([
            'code' => 200,
            'msg' => 'success',
            'data' => [
                'token' => $token->token,
                'url' => $this->buildUrl($token->token),
            ],
        ]);
    }

    private function buildUrl(string $token): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . '/ics/' . $token . '.ics';
    }
}
