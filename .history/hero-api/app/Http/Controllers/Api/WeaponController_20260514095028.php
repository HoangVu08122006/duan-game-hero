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
    $player = $request->user();
    
    // Lấy weapon_id từ body (ví dụ: 1 hoặc 2)
    $weaponId = $request->id; 

    // Tìm trong túi đồ của Player xem có bản ghi nào ứng với weapon_id này không
    $playerWeapon = $player->weapons()->where('weapon_id', $weaponId)->first();

    if (!$playerWeapon) {
        return response()->json([
            'message' => "Bạn không sở hữu vũ khí ID: $weaponId. Hãy đảm bảo đã mở khóa nó!"
        ], 404);
    }

    // Logic chuyển đổi trang bị
    DB::transaction(function () use ($player, $playerWeapon) {
        // Tắt tất cả vũ khí đang cầm
        $player->weapons()->update(['is_equipped' => false]);
        
        // Bật vũ khí vừa chọn
        $playerWeapon->update(['is_equipped' => true]);
    });

    return response()->json([
        'message' => "Đã trang bị vũ khí thành công!",
        'weapon_name' => $playerWeapon->weapon->name,
        'current_damage' => $playerWeapon->current_damage // Sử dụng Accessor tính Dame
    ]);
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