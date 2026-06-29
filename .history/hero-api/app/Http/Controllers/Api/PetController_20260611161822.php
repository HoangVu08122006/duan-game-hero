<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PetController extends Controller
{
    
    public function upgrade(Request $request)
{
    $player = $request->user();
    
    // Lấy pet đang được trang bị (is_equipped = 1) từ bảng player_pets
    $activePet = $player->playerPets()->where('is_equipped', 1)->first();

    if (!$activePet) {
        return response()->json(['message' => 'Bạn chưa trang bị Pet nào!'], 404);
    }

    // Tính chi phí dựa trên level hiện tại
    $currentPetLevel = $activePet->level; 
    $cost = (int) round(100 * pow(1.1, $currentPetLevel - 1));

    if ($player->gold < $cost) {
        return response()->json(['message' => 'Không đủ vàng!'], 400);
    }

    // 1. Trừ vàng của Player
    $player->gold -= $cost;
    $player->save();

    // 2. Tăng Level trong bảng player_pets
    // Chắc chắn $activePet là instance của Model PlayerPet
    $activePet->level += 1;
    $activePet->save(); 

    // 3. Lấy chỉ số mới (Gọi lại hàm tính toán Dame 10% và Skill 30/60/90)
    // Giả sử bạn đã viết logic tính toán trong thuộc tính ảo hoặc hàm riêng
    $skills = $this->calculatePetStats($activePet); 

    return response()->json([
        'success' => true,
        'message' => 'Nâng cấp thành công!',
        'new_pet_level' => $activePet->level,
        'gold_remaining' => $player->gold,
        'damage_report' => $skills
    ]);
}

private function calculatePetStats($playerPet)
{
    $lv = $playerPet->level ?? 1;
    $baseAtk = $playerPet->pet->base_attack ?? 50;

    // 1. Dame chính: Tăng 10% mỗi cấp (từ lv 1)
    $mainDame = $baseAtk * pow(1.1, $lv - 1);

    // 2. Skill 1: Mở lv 30. Bằng 1.5 lần Dame chính ở lv 29.
    $skill1 = 0;
    if ($lv >= 30) {
        $mainAt29 = $baseAtk * pow(1.1, 29 - 1);
        $baseSkill1 = $mainAt29 * 1.5;
        $skill1 = $baseSkill1 * pow(1.1, $lv - 30);
    }

    // 3. Skill 2: Mở lv 60. Bằng 1.5 lần Skill 1 ở lv 59.
    $skill2 = 0;
    if ($lv >= 60) {
        $mainAt29ForS2 = $baseAtk * pow(1.1, 29 - 1);
        $skill1At59 = ($mainAt29ForS2 * 1.5) * pow(1.1, 59 - 30);
        $baseSkill2 = $skill1At59 * 1.5;
        $skill2 = $baseSkill2 * pow(1.1, $lv - 60);
    }

    // 4. Skill 3: Mở lv 90. Bằng 1.5 lần Skill 2 ở lv 89.
    $skill3 = 0;
    if ($lv >= 90) {
        $mainAt29ForS3 = $baseAtk * pow(1.1, 29 - 1);
        $skill1At59ForS3 = ($mainAt29ForS3 * 1.5) * pow(1.1, 59 - 30);
        $skill2At89 = ($skill1At59ForS3 * 1.5) * pow(1.1, 89 - 60);
        $baseSkill3 = $skill2At89 * 1.5;
        $skill3 = $baseSkill3 * pow(1.1, $lv - 90);
    }

    return [
        'main_dame'    => (int)round($mainDame),
        'skill_1_dame' => (int)round($skill1),
        'skill_2_dame' => (int)round($skill2),
        'skill_3_dame' => (int)round($skill3),
        'total_power'  => (int)round($mainDame + $skill1 + $skill2 + $skill3)
    ];
}

public function index(): JsonResponse
    {
        try {
            $pets = Pet::all(); // Lấy tất cả thú cưng trong bảng 'pets'
            return response()->json(['success' => true, 'data' => $pets], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi server'], 500);
        }
    }

public function update_pet(Request $request, $id): JsonResponse
{
    $pet = Pet::find($id);
    if (!$pet) {
        return response()->json(['success' => false, 'message' => 'Không tìm thấy Pet'], 404);
    }

    // 1. Chỉ lấy những gì bạn cho phép sửa để tránh lỗi MassAssignment
    $data = $request->only([
        'name', 'prefab_name', 'base_dps', 'growth_rate', 
        'skill_1_name', 'skill_2_name', 'skill_3_name',
        'skill_1_description', 'skill_2_description', 'skill_3_description'
    ]);

    try {
        // 2. Update 1 lần duy nhất
        $pet->update($data); 
        
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
    } catch (\Exception $e) {
        Log::error("Lỗi update pet ID $id: " . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Lỗi hệ thống!'], 500);
    }
}

public function getPetLogs($id)
{
    $logs = \App\Models\PetLog::where('pet_id', $id)->orderBy('created_at', 'desc')->get();
    return response()->json(['success' => true, 'data' => $logs]);
}
}