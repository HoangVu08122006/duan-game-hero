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
        'player_pet_id' => 'required', 
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    // 1. Tìm người chơi trước
    $player = Player::findOrFail($validated['player_id']);

    // 2. Tìm thú cưng thông qua quan hệ của người chơi (Tương tự hàm upgrade)
    // Cách này đảm bảo thú cưng chắc chắn thuộc về người chơi đó
    $pet = $player->playerPets()->where('id', $validated['player_pet_id'])->first();

    if (!$pet) {
        return response()->json(['message' => 'Không tìm thấy thú cưng này trong tài khoản người chơi!'], 404);
    }

    // 3. Thực hiện update an toàn
    $pet->level = $validated['new_level'];
    $pet->save();

    return response()->json(['success' => true, 'message' => "Đã chỉnh Pet sang cấp {$pet->level}"]);
}

   public function adminUpgradePet(Request $request)
{
    
    $validated = $request->validate([
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required|exists:player_pets,id', // Đây là ID của bản ghi trong bảng player_pets
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    // Tìm bản ghi thú cưng của người chơi
    $petPivot = PlayerPet::find($validated['player_pet_id']);

    if (!$petPivot) {
        return response()->json(['message' => 'Không tìm thấy bản ghi trong bảng player_pets!'], 404);
    }

    // Xử lý cập nhật
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

