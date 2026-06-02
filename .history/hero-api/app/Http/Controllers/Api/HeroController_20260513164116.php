<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    // --- 5. NÂNG CẤP CRIT DAMAGE (+5% Crit Damage, +10% Vàng) ---
public function upgradeCritDamage(Request $request) {
    $player = $request->user();
    // Giá khởi điểm 500 vàng, tăng 10% mỗi cấp nâng cấp
    $cost = (int)round(500 * pow(1.1, $player->upgraded_crit_damage_lv));

    if ($player->gold < $cost) {
        return response()->json(['message' => 'Không đủ vàng'], 400);
    }

    $player->gold -= $cost;
    $player->upgraded_crit_damage_lv += 1;
    
    // Cập nhật lại lực chiến tổng (bao gồm cả Crit Damage mới)
    $player->total_power = $this->calculateTotalPower($player);
    $player->save();

    return response()->json([
        'message' => 'Nâng cấp Crit Damage thành công',
        'level' => $player->upgraded_crit_damage_lv,
        'new_crit_damage' => (150 + ($player->upgraded_crit_damage_lv * 5)) . '%', // Giả sử gốc là 150%
        'total_power' => $player->total_power,
        'gold_remaining' => $player->gold
    ]);
}

    /**
 * Hàm tính tổng lực chiến (Total Power) cập nhật theo yêu cầu
 */
private function calculateTotalPower($player) {
    $attack = $player->base_attack; // Đã được nhân % tăng trưởng ở API upgradeAttack
    $hp = $player->base_hp;         // Đã được nhân % tăng trưởng ở API upgradeHP
    
    $critRate = $player->upgraded_crit_rate_lv * 2;   // 2% mỗi cấp
    $critDamage = $player->upgraded_crit_damage_lv * 5; // 5% mỗi cấp
    $speed = $player->upgraded_speed_lv * 0.2;        // 0.2 mỗi cấp

    // Công thức tính tổng sức mạnh (Bạn có thể điều chỉnh hệ số ưu tiên)
    // Ví dụ: Attack + (HP/10) + (CritRate*50) + (CritDamage*20) + (Speed*100)
    $total = $attack 
             + ($hp / 10) 
             + ($critRate * 50) 
             + ($critDamage * 20) 
             + ($speed * 100);

    return (int)round($total);
}
}