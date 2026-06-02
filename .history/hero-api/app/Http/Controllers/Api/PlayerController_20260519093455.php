<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function saveData(Request $request)
    {
        // 1. Lấy thông tin Player đang đăng nhập thông qua Token
        $player = $request->user();

        // 2. Validate dữ liệu gửi từ Unity (phòng trường hợp gửi thiếu)
        $request->validate([
            'gold' => 'required|integer',
            'level' => 'required|integer',
            'current_floor' => 'required|integer',
        ]);

        // 3. Cập nhật dữ liệu vào Database
        $player->update([
            'gold' => $request->gold,
            'level' => $request->level,
            'current_floor' => $request->current_floor,
            // Thêm các trường khác nếu bạn muốn (ví dụ: exp, hp, mana...)
        ]);

        // 4. Trả về thông báo thành công cho Unity
        return response()->json([
            'status' => 'success',
            'message' => 'Lưu dữ liệu game thành công!',
            'data' => $player
        ], 200);
    }

    
}