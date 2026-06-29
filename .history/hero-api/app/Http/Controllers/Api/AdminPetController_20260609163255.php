<?php

namespace App\Http\Controllers\Api\;

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
    $request->validate([
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required|exists:player_pets,id',
        'new_level' => 'required|integer|min:1|max:999' // Admin có thể set từ 1 đến 999
    ]);

    $pet = PlayerPet::where('id', $request->player_pet_id)
        ->where('player_id', $request->player_id)
        ->firstOrFail();

    $oldLevel = $pet->level;
    $newLevel = $request->new_level;

    DB::transaction(function () use ($pet, $newLevel, $oldLevel, $request) {
        $pet->update(['level' => $newLevel]);

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