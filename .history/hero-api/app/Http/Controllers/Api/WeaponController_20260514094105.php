<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlayerWeapon;
use Illuminate\Support\Facades\DB;

class WeaponController extends Controller
{
    public function equip(Request $request) 
    {
        // Vì Player kế thừa Authenticatable, nên $request->user() chính là đối tượng Player
        $player = $request->user();

        if (!$player) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $targetId = $request->id; // ID của dòng trong bảng player_weapons

        return DB::transaction(function () use ($player, $targetId) {
            // 1. Sửa tên hàm từ playerWeapons() thành weapons() cho đúng với Model
            $player->weapons()->update(['is_equipped' => false]);

            // 2. Tìm vũ khí cụ thể và trang bị
            $playerWeapon = $player->weapons()
                ->with('weapon') // Eager load để lấy thông tin từ bảng weapons gốc
                ->findOrFail($targetId);

            $playerWeapon->update(['is_equipped' => true]);

            return response()->json([
                'message' => "Đã trang bị " . $playerWeapon->weapon->name,
                'damage_at_current_level' => $playerWeapon->current_damage,
                'is_equipped' => true
            ]);
        });
    }

    public function upgrade(Request $request)
    {
        $player = $request->user();
        // Tìm vũ khí cần nâng cấp thuộc sở hữu của player này
        $playerWeapon = $player->weapons()->findOrFail($request->id);

        $cost = $playerWeapon->upgrade_cost;

        if ($player->gold < $cost) {
            return response()->json(['message' => 'Không đủ vàng! Cần: ' . $cost], 400);
        }

        // Trừ vàng và tăng cấp
        $player->decrement('gold', $cost);
        $playerWeapon->increment('level');

        return response()->json([
            'message' => 'Nâng cấp thành công!',
            'new_level' => $playerWeapon->level,
            'gold_remaining' => $player->gold
        ]);
    }
}