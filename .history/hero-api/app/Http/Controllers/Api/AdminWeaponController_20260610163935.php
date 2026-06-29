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
    $newLevel = $request->new_level;

    DB::transaction(function () use ($playerWeapon, $newLevel, $oldLevel, $player) {

        $playerWeapon->update([
            'level' => $newLevel
        ]);

        PlayerLog::create([
            'player_id'  => $player->id,
            'field_name' => "Admin chỉnh cấp " . $playerWeapon->weapon->name,
            'old_value'  => "Cấp " . $oldLevel,
            'new_value'  => "Cấp " . $newLevel,
            'created_at' => now(),
        ]);
    });

    return response()->json([
        'success' => true,
        'message' => "Admin đã cập nhật cấp {$playerWeapon->weapon->name} thành công.",
        'data' => [
            'player' => $player->name,
            'weapon' => $playerWeapon->weapon->name,
            'from_level' => $oldLevel,
            'to_level' => $newLevel
        ]
    ]);
}
}