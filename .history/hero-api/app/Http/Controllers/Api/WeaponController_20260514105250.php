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
    
    // 1. Lấy vũ khí kèm theo thông tin gốc từ bảng 'weapons'
    $playerWeapon = $player->weapons()->with('weapon')->findOrFail($request->id);

    // 2. KIỂM TRA LEVEL NHÂN VẬT [Sửa lỗi bạn đang gặp]
    // Giả sử cột yêu cầu level trong bảng 'weapons' là 'required_hero_level'
    if ($player->level < $playerWeapon->weapon->required_hero_level) {
        return response()->json([
            'message' => 'Cấp độ nhân vật không đủ! Cần Lv.' . $playerWeapon->weapon->required_hero_level
        ], 403);
    }

    $cost = $playerWeapon->upgrade_cost;

    if ($player->gold < $cost) {
        return response()->json(['message' => 'Không đủ vàng! Cần: ' . $cost], 400);
    }

    // 3. Thực hiện nâng cấp
    $player->decrement('gold', $cost);
    $playerWeapon->increment('level');

    // Lưu ý: Sau khi increment, bạn nên refresh để lấy level mới nhất hiển thị
    $playerWeapon->refresh();

    return response()->json([
        'message' => 'Nâng cấp thành công!',
        'new_level' => $playerWeapon->level,
        'gold_remaining' => $player->gold
    ]);
}
}