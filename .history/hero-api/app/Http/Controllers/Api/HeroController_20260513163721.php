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

    // --- 3. NÂNG CẤP CRIT RATE (Cập nhật total_power) ---
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
    

    /**
     * Hàm tính tổng lực chiến (Total Power)
     * Công thức mẫu: Attack + (HP/10) + (CritRate * 50) + (Speed * 100)
     */
    private function calculateTotalPower($player) {
        $attack = $player->base_attack;
        $hp = $player->base_hp;
        $crit = $player->upgraded_crit_rate_lv * 2; // Mỗi lv tăng 2%
        $speed = 1 + ($player->upgraded_speed_lv * 0.2); // Gốc là 1

        return (int)round($attack + ($hp / 10) + ($crit * 50) + ($speed * 100));
    }
}