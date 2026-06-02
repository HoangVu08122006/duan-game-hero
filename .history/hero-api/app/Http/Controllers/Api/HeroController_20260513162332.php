<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HeroController extends Controller
{
    // --- 1. NÂNG CẤP TẤN CÔNG (8% Dame, +10% Vàng) ---
    public function upgradeAttack(Request $request) {
        $player = $request->user();
        $cost = (int)round(100 * pow(1.1, $player->upgraded_attack_lv));

        if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

        $player->gold -= $cost;
        $player->upgraded_attack_lv += 1;
        $player->save();

        return response()->json([
            'message' => 'Nâng cấp Attack thành công',
            'level' => $player->upgraded_attack_lv,
            'current_attack' => round($player->base_attack * pow(1.08, $player->upgraded_attack_lv)),
            'gold_remaining' => $player->gold
        ]);
    }

    // --- 2. NÂNG CẤP HP (12% HP, +10% Vàng) ---
    public function upgradeHP(Request $request) {
        $player = $request->user();
        $cost = (int)round(100 * pow(1.1, $player->upgraded_hp_lv));

        if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

        $player->gold -= $cost;
        $player->upgraded_hp_lv += 1;
        $player->save();

        return response()->json([
            'message' => 'Nâng cấp HP thành công',
            'level' => $player->upgraded_hp_lv,
            'current_hp' => round($player->base_hp * pow(1.12, $player->upgraded_hp_lv))
        ]);
    }

    // --- 3. NÂNG CẤP CRIT RATE (2% Crit, +10% Vàng) ---
    public function upgradeCritRate(Request $request) {
        $player = $request->user();
        $cost = (int)round(500 * pow(1.1, $player->upgraded_crit_rate_lv));

        if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

        $player->gold -= $cost;
        $player->upgraded_crit_rate_lv += 1;
        $player->save();

        return response()->json(['message' => 'Nâng cấp Crit Rate thành công', 'crit_rate' => ($player->upgraded_crit_rate_lv * 2) . '%']);
    }

    // --- 4. NÂNG CẤP SPEED (+0.2 Speed, +10% Vàng) ---
    public function upgradeSpeed(Request $request) {
        $player = $request->user();
        $cost = (int)round(700 * pow(1.1, $player->upgraded_speed_lv));

        if ($player->gold < $cost) return response()->json(['message' => 'Không đủ vàng'], 400);

        $player->gold -= $cost;
        $player->upgraded_speed_lv += 1;
        $player->save();

        return response()->json(['message' => 'Nâng cấp Speed thành công', 'current_speed' => 1 + ($player->upgraded_speed_lv * 0.2)]);
    }

    // --- 5. TEST CỘNG EXP ---
    public function addExp(Request $request) {
        $player = $request->user();
        $player->exp += $request->exp_amount;
        $player->save(); // Sự kiện tự động level up nằm ở Model

        return response()->json([
            'current_level' => $player->level,
            'current_exp' => $player->exp,
            'message' => 'Đã cộng EXP'
        ]);
    }
}