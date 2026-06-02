<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Player;
class HeroController extends Controller
{
    // --- 1. NÂNG CẤP TẤN CÔNG (Thay đổi base_attack và cập nhật total_power) ---
    public function upgradeAttack(Request $request) {
        $player = $request->user();
        $cost = (int)round(100 * pow(1.1, $player->upgraded_attack_lv));

        if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

        $player->gold -= $cost;
        $player->upgraded_attack_lv += 1;
        
        // Thay đổi trực tiếp chỉ số base_attack (tăng 8%)
        $player->base_attack = (int)round($player->base_attack * 1.08);
        
        // Cập nhật lại lực chiến tổng
        $player->total_power = $this->calculateTotalPower($player);
        $player->save();

        return response()->json([
            'message' => 'Nâng cấp Attack thành công',
            'new_base_attack' => $player->base_attack,
            'total_power' => $player->total_power,
            'gold_remaining' => $player->gold
        ]);
    }

    // --- 2. NÂNG CẤP HP (Thay đổi base_hp và cập nhật total_power) ---
    public function upgradeHP(Request $request) {
        $player = $request->user();
        $cost = (int)round(100 * pow(1.1, $player->upgraded_hp_lv));

        if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

        $player->gold -= $cost;
        $player->upgraded_hp_lv += 1;
        
        // Thay đổi trực tiếp chỉ số base_hp (tăng 12%)
        $player->base_hp = (int)round($player->base_hp * 1.12);
        
        // Cập nhật lại lực chiến tổng
        $player->total_power = $this->calculateTotalPower($player);
        $player->save();

        return response()->json([
            'message' => 'Nâng cấp HP thành công',
            'new_base_hp' => $player->base_hp,
            'total_power' => $player->total_power
        ]);
    }

    // --- 3. NÂNG CẤP CRIT RATE (Gốc 10% + 2% mỗi Lv) ---
public function upgradeCritRate(Request $request) {
    $player = $request->user();
    $cost = (int)round(500 * pow(1.1, $player->upgraded_crit_rate_lv));

    if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

    $player->gold -= $cost;
    $player->upgraded_crit_rate_lv += 1;
    
    $player->total_power = $this->calculateTotalPower($player);
    $player->save();

    return response()->json([
        'message' => 'Nâng cấp Crit Rate thành công',
        'current_crit_rate' => (10 + ($player->upgraded_crit_rate_lv * 2)) . '%',
        'total_power' => $player->total_power
    ]);
}

    // --- 4. NÂNG CẤP SPEED (Cập nhật total_power) ---
    public function upgradeSpeed(Request $request) {
        $player = $request->user();
        $cost = (int)round(700 * pow(1.1, $player->upgraded_speed_lv));

        if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

        $player->gold -= $cost;
        $player->upgraded_speed_lv += 1;
        $player->total_power = $this->calculateTotalPower($player);
        $player->save();

        return response()->json([
            'message' => 'Nâng cấp Speed thành công', 
            'total_power' => $player->total_power
        ]);
    }

    // --- 5. NÂNG CẤP CRIT DAMAGE (Gốc 100% + 5% mỗi Lv) ---
public function upgradeCritDamage(Request $request) {
    $player = $request->user();
    $cost = (int)round(500 * pow(1.1, $player->upgraded_crit_damage_lv));

    if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

    $player->gold -= $cost;
    $player->upgraded_crit_damage_lv += 1;
    
    $player->total_power = $this->calculateTotalPower($player);
    $player->save();

    return response()->json([
        'message' => 'Nâng cấp Crit Damage thành công',
        'current_crit_damage' => (100 + ($player->upgraded_crit_damage_lv * 5)) . '%',
        'total_power' => $player->total_power
    ]);
}




// --- 6. NHẬN EXP (Tự động tính Level và mở khóa vũ khí qua Model) ---
public function addExp(Request $request) {
    $player = $request->user();

    $request->validate([
        'exp' => 'required|integer|min:1'
    ]);

    // CHÚ Ý: Gán giá trị trực tiếp như thế này
    $player->exp += $request->exp; 

    // Khi gọi save(), Laravel thấy exp bị thay đổi (dirty)
    // Nó sẽ kích hoạt static::saving trong Model Player.php
    $player->save(); 

    // Load lại các quan hệ nếu cần (ví dụ để xem vũ khí mới mở)
    $player->load('weapons');

    return response()->json([
        'message' => 'Nhận EXP thành công!',
        'received_exp' => $request->exp,
        'new_level' => $player->level, // Lúc này level đã được logic saving thay đổi
        'remaining_exp' => $player->exp,
        'total_power' => $player->total_power,
        'unlocked_weapons' => $player->weapons
    ]);
}
}