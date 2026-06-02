<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlayerWeapon;

class WeaponController extends Controller
{
        public function upgrade(Request $request) {
    $player = $request->user()->player;
    // Tìm vũ khí mà người chơi muốn nâng cấp (thông qua ID trong bảng player_weapons)
    $playerWeapon = $player->playerWeapons()->findOrFail($request->id);

    $cost = $playerWeapon->upgrade_cost;

    if ($player->gold < $cost) {
        return response()->json(['message' => 'Không đủ vàng!'], 400);
    }

    // Thực hiện trừ tiền và tăng cấp
    $player->decrement('gold', $cost);
    $playerWeapon->increment('level');

    return response()->json([
        'message' => 'Nâng cấp thành công!',
        'new_level' => $playerWeapon->level,
        'new_damage' => $playerWeapon->current_damage
    ]);
    }

    
}