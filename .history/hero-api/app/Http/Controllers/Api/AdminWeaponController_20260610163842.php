<?php
namespace App\Http\Controllers\Api; // Hãy đảm bảo namespace khớp với thư mục của bạn

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerWeapon;
use App\Models\PlayerLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminWeaponController extends Controller
{
public function adminUpgrade(Request $request)
{
    // Validate đầu vào
    $request->validate([
    'player_id' => 'required|exists:players,id',
    'weapon_id' => 'required|exists:player_weapons,id',
    'new_level' => 'required|integer|min:1'
]);

    $player = Player::findOrFail($request->player_id);
    $playerWeapon = PlayerWeapon::where('id', $request->weapon_id)
        ->where('player_id', $player->id)
        ->with('weapon')
        ->firstOrFail();

    $oldLevel = $playerWeapon->level;
    $newLevel = $oldLevel + $request->level_to_add;

    DB::transaction(function () use ($playerWeapon, $newLevel, $oldLevel, $player) {
        // Cập nhật level trực tiếp
        $playerWeapon->update([
    'level' => $request->new_level
]);

        // Ghi log vào PlayerLog
        PlayerLog::create([
            'player_id'  => $player->id,
            'field_name' => "Admin nâng cấp " . $playerWeapon->weapon->name,
            'old_value'  => "Cấp " . $oldLevel,
            'new_value'  => "Cấp " . $newLevel,
            'created_at' => now(),
    ]);
    });

    return response()->json([
        'success' => true,
        'message' => "Admin đã nâng cấp thành công {$playerWeapon->weapon->name} cho người chơi.",
        'data' => [
            'player' => $player->name,
            'weapon' => $playerWeapon->weapon->name,
            'from_level' => $oldLevel,
            'to_level' => $newLevel
        ]
    ]);
}
}