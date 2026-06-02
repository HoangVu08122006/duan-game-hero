<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $lv = $playerPet->level ?? 1;
        $baseAtk = $playerPet->pet->base_attack ?? 50;

        // 1. Dame chính: Tăng 10% mỗi cấp (từ lv 1)
        // Công thức: Base * 1.1^(lv - 1)
        $mainDame = $baseAtk * pow(1.1, $lv - 1);

        // 2. Skill 1: Mở lv 30. Bằng 1.5 lần Dame chính ở lv 29.
        $skill1 = 0;
        if ($lv >= 30) {
            $mainAt29 = $baseAtk * pow(1.1, 29 - 1);
            $baseSkill1 = $mainAt29 * 1.5;
            // Sau khi mở, tăng 10% mỗi cấp
            $skill1 = $baseSkill1 * pow(1.1, $lv - 30);
        }

        // 3. Skill 2: Mở lv 60. Bằng 1.5 lần Skill 1 ở lv 59.
        $skill2 = 0;
        if ($lv >= 60) {
            $mainAt29ForS2 = $baseAtk * pow(1.1, 29 - 1);
            $skill1At59 = ($mainAt29ForS2 * 1.5) * pow(1.1, 59 - 30);
            $baseSkill2 = $skill1At59 * 1.5;
            // Sau khi mở, tăng 10% mỗi cấp
            $skill2 = $baseSkill2 * pow(1.1, $lv - 60);
        }

        // 4. Skill 3: Mở lv 90. Bằng 1.5 lần Skill 2 ở lv 89.
        $skill3 = 0;
        if ($lv >= 90) {
            $mainAt29ForS3 = $baseAtk * pow(1.1, 29 - 1);
            $skill1At59ForS3 = ($mainAt29ForS3 * 1.5) * pow(1.1, 59 - 30);
            $skill2At89 = ($skill1At59ForS3 * 1.5) * pow(1.1, 89 - 60);
            $baseSkill3 = $skill2At89 * 1.5;
            // Sau khi mở, tăng 10% mỗi cấp
            $skill3 = $baseSkill3 * pow(1.1, $lv - 90);
        }

        $totalPower = $mainDame + $skill1 + $skill2 + $skill3;

        return [
            'pet_id' => $playerPet->pet_id,
            'name'   => $playerPet->pet->name,
            'level'  => $lv,
            'stats'  => [
                'main_dame'    => (int)round($mainDame),
                'skill_1_dame' => (int)round($skill1),
                'skill_2_dame' => (int)round($skill2),
                'skill_3_dame' => (int)round($skill3),
                'total_power'  => (int)round($totalPower)
            ],
            'is_equipped' => (bool)$playerPet->is_equipped
        ];
    });

    return response()->json([
        'player_name' => $player->name,
        'pets'        => $pets
    ]);
}

public function index()
    {
        try {
            // Lấy toàn bộ danh sách người chơi từ bảng (giả định tên bảng của bạn là 'users' hoặc 'players')
            // Thay 'users' bằng tên bảng thực tế của bạn nếu khác nhé
            $players = DB::table('users')
                ->select(
                    'id', 'name', 'gold', 'level', 'exp', 
                    'current_floor', 'kill_count', 'highest_floor', 
                    'upgraded_attack_lv', 'upgraded_hp_lv', 'upgraded_crit_rate_lv', 
                    'upgraded_speed_lv', 'total_power', 'base_hp', 'current_hp', 
                    'base_attack', 'upgraded_crit_damage_lv', 'created_at', 'updated_at'
                )
                ->get();

            // Trả về dữ liệu dạng JSON thành công
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy danh sách người chơi thành công!',
                'data' => $players
            ], 200);

        } catch (\Exception $e) {
            // Xử lý lỗi nếu có ngoại lệ xảy ra
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi hệ thống.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}