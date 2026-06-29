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
    // 1. Chỉ validate 1 lần duy nhất
    $request->validate([
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required|exists:player_pets,id',
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    // 2. Tìm bản ghi dựa trên ID bảng trung gian
    $petPivot = PlayerPet::where('id', $request->player_pet_id)
        ->where('player_id', $request->player_id)
        ->firstOrFail();

        if (!$petPivot) {
        return response()->json(['message' => "Không tìm thấy bản ghi ID {$request->player_pet_id} trong bảng player_pets"], 404);
    }
    $oldLevel = $petPivot->level;
    $newLevel = $request->new_level;

    // 3. Thực hiện transaction
    DB::transaction(function () use ($petPivot, $newLevel, $oldLevel, $request) {
        $petPivot->update(['level' => $newLevel]);

        PlayerLog::create([
            'player_id'  => $request->player_id,
            'field_name' => "Admin điều chỉnh cấp độ Pet",
            'old_value'  => "Cấp " . $oldLevel,
            'new_value'  => "Cấp " . $newLevel,
            'created_at' => now(),
        ]);
    });

    return response()->json(['success' => true, 'message' => "Đã chỉnh Pet từ cấp $oldLevel sang $newLevel"]);
}
}