<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Quan trọng để dùng Auth facade
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra đăng nhập bằng Auth facade
        if (Auth::check()) {
            // Cập nhật bằng Model Player
            // Đảm bảo tên Model là \App\Models\Player hoặc tương ứng với dự án của bạn
            \App\Models\Player::where('id', Auth::id())->update([
                'last_login_at' => now(),
                'is_online' => 1
            ]);
        }

        return $next($request);
    }
}