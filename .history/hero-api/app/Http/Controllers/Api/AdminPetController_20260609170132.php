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
// Validate: chỉ cần player_id, pet_id (đang là 1), và new_level
  // Validate chặt chẽ
    $validated = $request->validate([
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required|exists:player_pets,id', // Kiểm tra ID này trong bảng player_pets
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    // Tìm bản ghi sở hữu dựa trên khóa chính 'id' của bảng 'player_pets'
    $petPivot = PlayerPet::where('id', $validated['player_pet_id'])
        ->where('player_id', $validated['player_id'])
        ->first();

    if (!$petPivot) {
        return response()->json(['message' => 'Không tìm thấy bản ghi sở hữu!'], 404);
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