<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerLog;
use App\Models\PlayerWeapon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeaponController extends Controller
{
    public function equip(Request $request)
{
    // Xác định ID người chơi: Nếu là Admin gửi lên thì lấy từ request, nếu là User thì lấy từ Token
    $playerId = $request->input('player_id', $request->user()->id);
    $weaponId = $request->input('id'); // ID bản ghi trong bảng player_weapons

    $player = Player::findOrFail($playerId);
    $targetWeapon = $player->weapons()->where('id', $weaponId)->first();

    PlayerWeapon::where('player_id', $player->id)->update(['is_equipped' => 0]);
    $targetWeapon->update(['is_equipped' => 1]);
    
    if (!$targetWeapon) {
        return response()->json(['message' => 'Người chơi không sở hữu vũ khí này!'], 404);
    }

    // Kiểm tra level (logic của bạn)
    if ($player->level < $targetWeapon->weapon->required_hero_level) {
        return response()->json(['message' => 'Chưa đủ level!'], 403);
    }

    DB::transaction(function () use ($player, $targetWeapon) {
        // Lấy tên vũ khí cũ để ghi log
        $oldWeapon = $player->weapons()->where('is_equipped', 1)->with('weapon')->first();
        
        // Cập nhật: Tắt hết -> Bật cái mới
        $player->weapons()->update(['is_equipped' => 0]);
        $targetWeapon->update(['is_equipped' => 1]);

        // Ghi log vào bảng player_logs
        \App\Models\PlayerLog::create([
            'player_id'  => $player->id,
            'field_name' => 'Trang bị vũ khí',
            'old_value'  => $oldWeapon ? $oldWeapon->weapon->name : 'Tay không',
            'new_value'  => $targetWeapon->weapon->name,
            'created_at' => now(),
        ]);
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Trang bị thành công!',
        'new_damage' => $targetWeapon->current_damage 
    ]);
}
public function upgrade(Request $request)
{
    $player = $request->user();
    
    // 1. Lấy vũ khí của người chơi dựa trên weapon_id
    $playerWeapon = $player->weapons()
        ->with('weapon')
        ->where('weapon_id', $request->id)
        ->firstOrFail();

    // --- LOGIC MỚI: KIỂM TRA XEM CÓ ĐANG TRANG BỊ KHÔNG ---
    if (!$playerWeapon->is_equipped) {
        return response()->json([
            'message' => 'Bạn phải trang bị vũ khí này trước khi nâng cấp!'
        ], 400); // Trả về lỗi 400 (Bad Request)
    }
    // ---------------------------------------------------

    // 2. KIỂM TRA ĐIỀU KIỆN MỞ KHÓA (Cấp độ yêu cầu)
    $allWeapons = \App\Models\Weapon::orderBy('id', 'asc')->pluck('id')->toArray();
    $index = array_search($playerWeapon->weapon_id, $allWeapons);
    $requiredLv = ($index === 0) ? 1 : ($index * 100);

    if ($player->level < $requiredLv) {
        return response()->json([
            'message' => "Vũ khí này chưa được mở khóa! Bạn cần đạt Level $requiredLv.",
            'required_level' => $requiredLv
        ], 403);
    }

    // 3. KIỂM TRA VÀNG
    $cost = $playerWeapon->upgrade_cost; 

    if ($player->gold < $cost) {
        return response()->json([
            'message' => 'Bạn không đủ vàng để nâng cấp!',
            'cost' => $cost,
            'current_gold' => $player->gold
        ], 400);
    }

    // 4. THỰC HIỆN NÂNG CẤP TRONG TRANSACTION
    DB::transaction(function () use ($player, $playerWeapon, $cost) {
        $player->decrement('gold', $cost);
        $playerWeapon->increment('level');
    });

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
    
    // Lấy vũ khí kèm thông tin gốc, sắp xếp để tính mốc level chính xác
    $playerWeapons = $player->weapons()->with('weapon')->orderBy('weapon_id', 'asc')->get();

    // Lấy danh sách ID để tính index nhanh, tránh query lặp lại trong vòng lặp
    $allWeaponIds = \App\Models\Weapon::orderBy('id', 'asc')->pluck('id')->toArray();

    $data = $playerWeapons->map(function ($pw) use ($player, $allWeaponIds) {
        $weaponBase = $pw->weapon;
        if (!$weaponBase) return null;

        // Tính toán vị trí và level yêu cầu
        $indexInList = array_search($weaponBase->id, $allWeaponIds);
        $requiredLv = ($indexInList === 0) ? 1 : ($indexInList * 100);

        // ĐỊNH NGHĨA BIẾN IS_OWNED TẠI ĐÂY (Khắc phục lỗi 500)
        $isOwned = $player->level >= $requiredLv;

        return [
            'id' => $weaponBase->id,
            'player_weapon_id' => $pw->id,
            'name' => $weaponBase->name,
            'current_damage' => $pw->current_damage, 
            'required_level' => $requiredLv, 
            'is_owned' => $isOwned,
            'is_equipped' => (bool)$pw->is_equipped,
            'level' => $pw->level,
            'can_unlock' => $isOwned,
            'upgrade_cost' => $pw->upgrade_cost,
            'prefab_name' => $weaponBase->prefab_name
        ];
    })->filter();

    return response()->json([
        'success' => true,
        'hero_level' => $player->level,
        'weapons' => $data
    ]);
}
}