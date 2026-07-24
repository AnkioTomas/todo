<?php

declare(strict_types=1);

namespace app;

use nova\framework\App;

use function nova\framework\route;

use nova\framework\route\Route;

class Application extends App
{
    public const string SYSTEM_NAME = 'Todo';

    public function onFrameworkStart(): void
    {
        Route::getInstance()
            ->get('/', route('todo', 'main', 'index'))
            ->get('/ics/{token}.ics', route('todo', 'ics', 'feed'));
    }
}
