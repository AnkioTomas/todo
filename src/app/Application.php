<?php

declare(strict_types=1);

namespace app;

use nova\framework\App;

use nova\framework\event\EventManager;
use function nova\framework\route;

use nova\framework\route\Route;

class Application extends App
{
    public const string SYSTEM_NAME = 'Todo';

    public function onFrameworkStart(): void
    {
        $router = ['todo','main'];
        EventManager::trigger('admin.router', $router);
        Route::getInstance()
            ->get('/', route('todo', 'main', 'home'))
            ->getOrPost('/mcp', route('todo', 'mcp', 'handleMcpRequest'));
    }
}
