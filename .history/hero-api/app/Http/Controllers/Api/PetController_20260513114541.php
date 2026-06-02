<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetController extends Controller
{
    /**
     * Copy đoạn code của bạn vào đây
     */
    public function upgrade(Request $request)
    {
        $player = $request->user(); // Lấy người chơi đang đăng nhập qua Token
        
        // Tính toán chi phí
        $cost = (int) round(100 * pow(1.1, $player->pet_level - 1));

        if ($player->gold < $cost) {
            return response()->json(['message' => 'Không đủ vàng!'], 400);
        }

        // Thực hiện nâng cấp
        $player->gold -= $cost;
        $player->pet_level += 1;
        $player->save();

        // Lấy chỉ số mới (Hàm này bạn đã viết trong Model Player)
        $skills = $player->pet_skills;

        return response()->json([
            'success' => true,
            'level'   => $player->pet_level,
            'gold_remaining' => $player->gold,
            'damage_report' => [
                'main'   => $skills['main_dame'],
                'skill1' => $skills['skill_1'] > 0 ? $skills['skill_1'] : "Chưa mở khóa (Lv 30)",
                'skill2' => $skills['skill_2'] > 0 ? $skills['skill_2'] : "Chưa mở khóa (Lv 60)",
                'skill3' => $skills['skill_3'] > 0 ? $skills['skill_3'] : "Chưa mở khóa (Lv 90)",
                'total_dps' => $skills['total_dps']
            ],
            // Nhớ định nghĩa hàm getNextPetUpgradeCost() trong Model Player luôn nhé
            'next_upgrade_cost' => (int) round(100 * pow(1.1, $player->pet_level - 1))
        ]);
    }
}