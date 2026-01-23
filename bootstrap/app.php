<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException; // ★追加1
use Illuminate\Http\Request; // ★追加2

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    // ▼ ここを修正します
    ->withExceptions(function (Exceptions $exceptions) {
        // 認証エラー（ログインしていない）が発生したときの処理を上書き
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return redirect()->route('login')->with('error', 'ログインに失敗しました。再度ログインしてください。');
        });
    })->create();
