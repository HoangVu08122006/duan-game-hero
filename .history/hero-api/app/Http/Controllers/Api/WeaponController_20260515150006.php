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

public function index(Request $request)
{
    $player = $request->user();

    // 1. Lấy tất cả vũ khí gốc có trong game
    $allWeapons = \App\Models\Weapon::all();

    // 2. Lấy danh sách vũ khí người chơi ĐÃ sở hữu
    $playerWeapons = $player->weapons->keyBy('weapon_id');

    $data = $allWeapons->map(function ($weapon) use ($player, $playerWeapons) {
        // Kiểm tra xem người chơi đã có món này trong bảng player_weapons chưa
        $owned = $playerWeapons->get($weapon->id);
        
        $isOwned = $owned ? true : false;
        $level = $owned ? $owned->level : 1;
        $isEquipped = $owned ? (bool)$owned->is_equipped : false;

        // Tính sát thương hiển thị
        // Nếu đã có thì tính theo level người chơi, nếu chưa có thì hiển thị dame lv 1
        $currentDamage = (int)($weapon->base_attack * pow(1.1, $level - 1));

        return [
            'id' => $weapon->id, // ID gốc của vũ khí
            'player_weapon_id' => $owned ? $owned->id : null, // ID để gửi lên khi nâng cấp
            'name' => $weapon->name,
            'description' => $weapon->description, // Nếu bạn có cột này
            'required_level' => $weapon->required_hero_level,
            'base_attack' => $weapon->base_attack,
            'current_damage' => $currentDamage,
            'prefab_name' => $weapon->prefab_name,
            'is_owned' => $isOwned,
            'is_equipped' => $isEquipped,
            'level' => $level,
            'can_unlock' => $player->level >= $weapon->required_hero_level,
            'upgrade_cost' => (int)round(200 * pow(1.1, $level - 1))
        ];
    });

    return response()->json([
        'success' => true,
        'hero_level' => $player->level,
        'weapons' => $data
    ]);
}
}