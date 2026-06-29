<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\PlayerPet;
use App\Models\PlayerLog;
use Illuminate\Support\Facades\DB;

class AdminPetController extends Controller
{
public function adminUpgradePet(Request $request)
{
    $validated = $request->validate([
        'player_id' => 'required',
        // 'player_pet_id' ở đây nên là cái ID của bản ghi trong bảng player_pets
        'player_pet_id' => 'required',
        'new_level' => 'required|integer'
    ]);

    // ĐỐI CHIẾU KÉP: Tìm đúng bản ghi mà cả ID và Player_ID đều khớp
    $pet = PlayerPet::where('id', $validated['player_pet_id'])
                    ->where('player_id', $validated['player_id'])
                    ->first();

    // Nếu không tìm thấy, nghĩa là Admin đang truyền sai ID bản ghi hoặc 
    // bản ghi này không thuộc về người chơi đó
    if (!$pet) {
        return response()->json([
            'message' => 'Lỗi: Thú cưng này không thuộc về người chơi này!'
        ], 404);
    }

    // Nếu tìm thấy, thực hiện cập nhật
    $pet->level = $validated['new_level'];
    $pet->save();

    return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
}
}

