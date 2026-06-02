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
    $weaponId = $request->id; 

    // Lấy thông tin vũ khí người chơi đang muốn trang bị
    $playerWeapon = $player->weapons()->with('weapon')->where('weapon_id', $weaponId)->first();

    if (!$playerWeapon) {
        return response()->json(['message' => 'Bạn không sở hữu vũ khí này!'], 404);
    }

    // CHẶN TRANG BỊ NẾU CHƯA ĐỦ LEVEL
    if ($player->level < $playerWeapon->weapon->required_hero_level) {
        return response()->json([
            'message' => 'Bạn cần đạt Level ' . $playerWeapon->weapon->required_hero_level . ' để sử dụng vũ khí này.'
        ], 403);
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

// app/Http/Controllers/Api/WeaponController.php

public function index(Request $request)
{
    $player = $request->user();

    // 1. Lấy tất cả vũ khí có trong game
    $allWeapons = \App\Models\Weapon::all();

    // 2. Lấy danh sách vũ khí người chơi đang sở hữu
    $playerWeapons = $player->weapons->keyBy('weapon_id');

    $data = $allWeapons->map(function ($weapon) use ($player, $playerWeapons) {
        // Kiểm tra xem người chơi đã có vũ khí này chưa
        $ownedWeapon = $playerWeapons->get($weapon->id);
        
        $isOwned = $ownedWeapon ? true : false;
        $level = $ownedWeapon ? $ownedWeapon->level : 0;
        $isEquipped = $ownedWeapon ? (bool)$ownedWeapon->is_equipped : false;

        // Tính toán sát thương hiện tại dựa trên level nâng cấp
        // Công thức: Dame gốc + (10% mỗi cấp)
        $currentDamage = $isOwned 
            ? round($weapon->base_damage * pow(1.1, $level - 1)) 
            : $weapon->base_damage;

        return [
            'id' => $weapon->id,
            'name' => $weapon->name,
            'base_damage' => $weapon->base_damage,
            'current_damage' => (int)$currentDamage,
            'required_hero_level' => $weapon->required_hero_level,
            'prefab_name' => $weapon->prefab_name,
            'status' => [
                'is_owned' => $isOwned,
                'is_equipped' => $isEquipped,
                'level' => $level,
                'can_equip' => $player->level >= $weapon->required_hero_level,
                'upgrade_cost' => $isOwned ? (int)round(200 * pow(1.2, $level)) : 0
            ]
        ];
    });

    return response()->json([
        'success' => true,
        'player_level' => $player->level,
        'weapons' => $data
    ]);
}
}