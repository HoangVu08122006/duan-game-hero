<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function saveData(Request $request)
    {
        // 1. Lấy thông tin Player đang đăng nhập thông qua Token
        $player = $request->user();

        // 2. Validate dữ liệu gửi từ Unity (phòng trường hợp gửi thiếu)
        $request->validate([
            'gold' => 'required|integer',
            'level' => 'required|integer',
            'current_floor' => 'required|integer',
        ]);

        // 3. Cập nhật dữ liệu vào Database
        $player->update([
            'gold' => $request->gold,
            'level' => $request->level,
            'current_floor' => $request->current_floor,
            // Thêm các trường khác nếu bạn muốn (ví dụ: exp, hp, mana...)
        ]);

        // 4. Trả về thông báo thành công cho Unity
        return response()->json([
            'status' => 'success',
            'message' => 'Lưu dữ liệu game thành công!',
            'data' => $player
        ], 200);
    }

    public function getMyPets(Request $request)
{
    $player = $request->user();

    $pets = $player->playerPets()->with('pet')->get()->map(function ($playerPet) {
        $lv = $playerPet->level;
        $baseAtk = $playerPet->pet->base_attack ?? 50;

        // 1. Tính Sát thương chính (Main Dame)
        $mainDame = round($baseAtk * pow(1.1, $lv - 1));

        // 2. Tính Sát thương Skill 1 (Mở khóa lv 30 - Gây 150% Dame)
        $skill1 = ($lv >= 30) 
            ? round(($baseAtk * pow(1.1, 28) * 1.5) * pow(1.1, $lv - 30)) 
            : 0;

        // 3. Tính Sát thương Skill 2 (Mở khóa lv 60 - Gây 200% Dame)
        $skill2 = ($lv >= 60) 
            ? round(($baseAtk * pow(1.1, 58) * 2.0) * pow(1.1, $lv - 60)) 
            : 0;

        // 4. TỔNG LỰC CHIẾN PET
        $totalPetPower = $mainDame + $skill1 + $skill2;

        return [
            'pet_id' => $playerPet->pet_id,
            'name'   => $playerPet->pet->name,
            'level'  => $lv,
            'stats'  => [
                'main_dame'       => (int)$mainDame,
                'skill_1_dame'    => (int)$skill1,
                'skill_2_dame'    => (int)$skill2,
                'total_pet_power' => (int)$totalPetPower // Đây là con số tổng bạn muốn
            ],
            'is_equipped' => $playerPet->is_equipped
        ];
    });

    return response()->json([
        'player_name' => $player->name,
        'pets'        => $pets
    ]);
}
}