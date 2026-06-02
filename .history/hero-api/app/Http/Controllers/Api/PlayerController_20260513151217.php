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
    // Lấy player từ token (nếu dùng Sanctum) hoặc ID từ request
    $player = $request->user(); 

    if (!$player) {
        return response()->json(['message' => 'Không tìm thấy người chơi'], 404);
    }

    // Lấy danh sách pet kèm theo thông tin chi tiết từ bảng pets
    $pets = $player->playerets()->with('pet')->get()->map(function ($playerPet) {
        // Tính toán chỉ số dựa trên Level của từng con Pet
        $lv = $playerPet->level;
        $baseAtk = $playerPet->pet->base_attack ?? 50; // Lấy dame gốc từ bảng pets

        $mainDame = round($baseAtk * pow(1.1, $lv - 1));
        
        return [
            'player_pet_id' => $playerPet->id,
            'pet_id' => $playerPet->pet_id,
            'name' => $playerPet->pet->name,
            'level' => $lv,
            'image' => $playerPet->pet->image_url,
            'stats' => [
                'main_dame' => $mainDame,
                'skill_1' => ($lv >= 30) ? round($mainDame * 1.5) : 0,
            ]
        ];
    });

    return response()->json([
        'player_name' => $player->name,
        'total_pets' => $pets->count(),
        'pets' => $pets
    ]);
}
}