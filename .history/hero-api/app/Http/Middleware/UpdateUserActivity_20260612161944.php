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
    // Lấy user hiện tại
    $user = Auth::user();

    // Kiểm tra và sử dụng Model Player (hoặc User) của bạn để gọi update()
    if ($user) {
        // Cách tối ưu nhất: Truy vấn lại model Player từ DB
        // Điều này đảm bảo đối tượng bạn thao tác là một Eloquent Model chính gốc
        \App\Models\Player::where('id', $user->id)->update([
            'last_login_at' => now(),
            'is_online' => 1
        ]);
    }

    return $next($request);
}
}