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
    
    // 1. Lấy vũ khí của người chơi. 
    // Lưu ý: $request->id nên là ID của vũ khí (weapon_id) hoặc ID của dòng trong player_weapons
    $playerWeapon = $player->weapons()->with('weapon')->where('weapon_id', $request->id)->firstOrFail();

    // 2. KIỂM TRA QUYỀN SỞ HỮU (Tính trực tiếp trong Controller)
$allWeapons = \App\Models\Weapon::orderBy('id', 'asc')->pluck('id')->toArray();
$index = array_search($playerWeapon->weapon_id, $allWeapons);
$requiredLv = ($index === 0) ? 1 : ($index * 100);

// Kiểm tra Level của người chơi hiện tại ($player đã lấy ở đầu hàm)
if ($player->level < $requiredLv) {
    return response()->json([
        'message' => "Vũ khí này chưa được mở khóa! Bạn cần đạt Level $requiredLv.",
        'required_level' => $requiredLv
    ], 403);
}

    // 3. KIỂM TRA VÀNG
    $cost = $playerWeapon->upgrade_cost; // Accessor đã viết ở PlayerWeapon model

    if ($player->gold < $cost) {
        return response()->json([
            'message' => 'Bạn không đủ vàng để nâng cấp!',
            'cost' => $cost,
            'current_gold' => $player->gold
        ], 400);
    }

    // 4. THỰC HIỆN NÂNG CẤP
    // Sử dụng Transaction để đảm bảo an toàn dữ liệu (trừ tiền xong phải tăng level)
    \DB::transaction(function () use ($player, $playerWeapon, $cost) {
        $player->decrement('gold', $cost);
        $playerWeapon->increment('level');
    });

    // Refresh để lấy dữ liệu mới nhất (bao gồm cả damage đã tăng)
    $playerWeapon->refresh();

    return response()->json([
        'success' => true,
        'message' => 'Nâng cấp thành công ' . $playerWeapon->weapon->name,
        'data' => [
            'new_level' => $playerWeapon->level,
            'new_damage' => $playerWeapon->current_damage,
            'gold_remaining' => $player->gold,
            'next_upgrade_cost' => $playerWeapon->upgrade_cost
        ]
    ]);
}

public function index(Request $request)
{
    $player = $request->user();
    
    // Lấy tất cả vũ khí của người chơi (đã được khởi tạo lúc Created)
    // Sắp xếp theo ID để đúng thứ tự mốc 100 level
    $playerWeapons = $player->weapons()->with('weapon')->orderBy('weapon_id', 'asc')->get();

    $data = $playerWeapons->map(function ($pw) use ($player) {
        $weaponBase = $pw->weapon;
        
        // Tính toán level yêu cầu để hiển thị cho người chơi biết
        // Món 1 (ID nhỏ nhất): Lv 1, Món 2: Lv 100, Món 3: Lv 200...
        $indexInList = \App\Models\Weapon::where('id', '<', $weaponBase->id)->count();
        $requiredLv = ($indexInList === 0) ? 1 : ($indexInList * 100);

        return [
            'id' => $weaponBase->id,
            'player_weapon_id' => $pw->id,
            'name' => $weaponBase->name,
            'current_damage' => $pw->current_damage, // Accessor bạn đã viết
            'required_level' => $requiredLv, 
            'is_owned' => $isOwned,
            'is_equipped' => (bool)$pw->is_equipped,
            'level' => $pw->level,
            'can_unlock' => $player->level >= $requiredLv,
            'upgrade_cost' => $pw->upgrade_cost,
            'prefab_name' => $weaponBase->prefab_name
        ];
    });

    return response()->json([
        'success' => true,
        'hero_level' => $player->level,
        'weapons' => $data
    ]);
}
}