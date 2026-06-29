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
        'player_pet_id' => 'required', // Đây là ID từ bảng player_pets
        'new_level' => 'required|integer'
    ]);

    // Đối chiếu kép: Tìm bản ghi phải khớp cả id và player_id
    $pet = PlayerPet::where('id', $validated['player_pet_id'])
                    ->where('player_id', $validated['player_id'])
                    ->first();

    if (!$pet) {
        return response()->json(['message' => 'Lỗi: Thú cưng này không thuộc về người chơi!'], 404);
    }

    $pet->update(['level' => $validated['new_level']]);
    return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
}
}

