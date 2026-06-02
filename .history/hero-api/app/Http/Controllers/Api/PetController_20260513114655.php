<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetController extends Controller
{
    /**
     * Copy đoạn code của bạn vào đây
     */
    public function upgrade(Request $request)
{
    $player = $request->user();
    $activePet = $player->active_pet; // Lấy bản ghi từ bảng player_pets

    if (!$activePet) {
        return response()->json(['message' => 'Bạn chưa sở hữu Pet nào!'], 404);
    }

    // Tính chi phí dựa trên level của Pet
    $currentPetLevel = $activePet->level;
    $cost = (int) round(100 * pow(1.1, $currentPetLevel - 1));

    if ($player->gold < $cost) {
        return response()->json(['message' => 'Không đủ vàng!'], 400);
    }

    // 1. Trừ vàng của Player
    $player->gold -= $cost;
    $player->save();

    // 2. Tăng Level trong bảng player_pets
    $activePet->level += 1;
    $activePet->save();

    // 3. Lấy chỉ số mới sau khi đã lưu DB
    $skills = $player->pet_skills;

    return response()->json([
        'success' => true,
        'new_pet_level' => $activePet->level,
        'gold_remaining' => $player->gold,
        'damage_report' => $skills
    ]);
}
}