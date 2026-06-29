<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UpdateUserActivity
{
public function handle(Request $request, Closure $next): Response
{
    // Kiểm tra và lấy ID từ Auth
    if (auth()->check()) {
        // Truy vấn trực tiếp vào model Player để thực hiện update
        // Thay '\App\Models\Player' bằng đường dẫn model thực tế của bạn
        \App\Models\Player::where('id', auth()->id())->update([
            'last_login_at' => now(),
            'is_online' => 1
        ]);
    }

    return $next($request);
}
}