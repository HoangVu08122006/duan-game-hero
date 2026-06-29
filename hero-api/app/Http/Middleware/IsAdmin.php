<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Thêm dòng này

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Kiểm tra xem user đã đăng nhập chưa và có role là 'admin' trong bảng admins không
        // Giả sử bảng 'users' của bạn có liên kết với bảng 'admins' qua email hoặc id
        $isAdmin = DB::table('admins')->where('email', $user->email)
                                      ->where('role', 'admin')
                                      ->exists();

        if ($isAdmin) {
            return $next($request);
        }

        return response()->json(['message' => 'Bạn không có quyền admin.'], 403);
    }
}