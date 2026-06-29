<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Quan trọng để dùng Auth facade
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    // app/Http/Middleware/UpdateUserActivity.php
public function handle(Request $request, Closure $next): Response
{
    // Kiểm tra login và cập nhật trực tiếp qua Model
    if (auth()->check()) {
        \App\Models\Player::where('id', auth()->id())->update([
            'last_login_at' => now(),
            'is_online' => 1
        ]);
    }
    return $next($request);
}
}