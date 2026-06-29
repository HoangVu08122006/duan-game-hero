<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlayerNotBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
{
    $player = $request->user(); // Giả định bạn dùng Sanctum/Auth

    if ($player && $player->is_banned) {
        return response()->json(['message' => 'Tài khoản của bạn đã bị khóa.'], 403);
    }

    return $next($request);
}
}
