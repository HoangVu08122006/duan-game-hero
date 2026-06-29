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
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required|exists:player_pets,id',
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    // BỎ QUA điều kiện where('player_id', ...) tạm thời để kiểm tra
    // Hoặc kiểm tra xem $petPivot có tồn tại trước đã
    $petPivot = PlayerPet::find($validated['player_pet_id']);

    if (!$petPivot) {
        return response()->json(['message' => 'Không tìm thấy ID trong bảng player_pets!'], 404);
    }
    
    // Nếu muốn bảo mật: chỉ kiểm tra xem pet này có thuộc về player này không
    if ($petPivot->player_id != $validated['player_id']) {
        return response()->json([
            'message' => "Lỗi dữ liệu: Pet ID {$validated['player_pet_id']} thuộc về Player ID {$petPivot->player_id}, không phải Player {$validated['player_id']}"
        ], 400);
    }
    
    $oldLevel = $petPivot->level;
    $newLevel = $validated['new_level'];

    DB::transaction(function () use ($petPivot, $newLevel, $oldLevel, $validated) {
        $petPivot->update(['level' => $newLevel]);

        PlayerLog::create([
            'player_id'  => $validated['player_id'],
            'field_name' => "Admin điều chỉnh cấp độ Pet",
            'old_value'  => "Cấp " . $oldLevel,
            'new_value'  => "Cấp " . $newLevel,
            'created_at' => now(),
        ]);
    });

    return response()->json(['success' => true, 'message' => "Đã chỉnh Pet từ cấp $oldLevel sang $newLevel"]);
}
}