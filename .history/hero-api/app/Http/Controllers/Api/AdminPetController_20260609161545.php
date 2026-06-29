<?php

namespace App\Http\Controllers\Api\Admin;

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
        // 1. Validate dữ liệu
        $request->validate([
            'player_id' => 'required|exists:players,id',
            'player_pet_id' => 'required|exists:player_pets,id',
            'new_level' => 'required|integer|min:1'
        ]);

        $player = Player::findOrFail($request->player_id);
        $pet = PlayerPet::where('id', $request->player_pet_id)
            ->where('player_id', $player->id)
            ->firstOrFail();

        $oldLevel = $pet->level;

        // 2. Thực hiện cập nhật trong Transaction
        DB::transaction(function () use ($pet, $request, $player, $oldLevel) {
            $pet->update(['level' => $request->new_level]);

            // 3. Ghi log
            PlayerLog::create([
                'player_id'  => $player->id,
                'field_name' => "Admin cập nhật cấp độ Pet",
                'old_value'  => "Cấp " . $oldLevel,
                'new_value'  => "Cấp " . $request->new_level,
                'created_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Đã chỉnh sửa Pet {$pet->id} lên cấp {$request->new_level}."
        ]);
    }
}