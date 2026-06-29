<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php',
    )
   ->withMiddleware(function (Middleware $middleware): void {
    // 1. Cấu hình Sanctum
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);

    // 2. Thêm Middleware của bạn vào nhóm 'api'
    $middleware->appendToGroup('api', [
        \App\Http\Middleware\UpdateUserActivity::class,
    ]);

    // 3. Đăng ký alias
    $middleware->alias([
        'is_admin' => \App\Http\Middleware\IsAdmin::class,
    ]);

})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
