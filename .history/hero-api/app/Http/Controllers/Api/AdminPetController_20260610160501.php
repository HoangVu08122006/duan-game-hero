<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlayerLog;
use App\Models\PlayerPet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminPetController extends Controller
{
   public function adminUpgradePet(Request $request)
{
    $validated = $request->validate([
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required|exists:player_pets,id',
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    $playerPet = PlayerPet::where('id', $validated['player_pet_id'])
        ->where('player_id', $validated['player_id'])
        ->first();

    if (!$playerPet) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy pet của người chơi.'
        ], 404);
    }

    $oldLevel = $playerPet->level;

    $playerPet->level = $validated['new_level'];
    $playerPet->save();

    // Ghi log
    PlayerLog::create([
        'player_id' => $validated['player_id'],
        'field_name' => 'pet_level',
        'old_value' => $oldLevel,
        'new_value' => $validated['new_level'],
        'admin_name' => 'Admin'
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Cập nhật level pet thành công!',
        'data' => [
            'old_level' => $oldLevel,
            'new_level' => $playerPet->level
        ]
    ]);
}

//     public function adminUpdatePetLevel(Request $request)
// {
//     // 1. Validate dữ liệu
//     $request->validate([
//         'player_pet_id' => 'required|exists:player_pets,id',
//         'new_level' => 'required|integer|min:1',
//         'admin_name' => 'required|string' // Thêm tên admin thực hiện
//     ]);

//     // 2. Tìm Pet và lấy giá trị cũ
//     $playerPet = \App\Models\PlayerPet::findOrFail($request->player_pet_id);
//     $oldLevel = $playerPet->level;
//     $newLevel = $request->new_level;

//     // 3. Cập nhật Level
//     $playerPet->level = $newLevel;
//     $playerPet->save();

//     // 4. Ghi vào bảng player_logs (dựa trên cấu trúc bạn đã có)
//     \Illuminate\Support\Facades\DB::table('player_logs')->insert([
//         'player_id'   => $playerPet->player_id,
//         'field_name'  => 'pet_level_update', // Hoặc tên field bạn muốn quy định
//         'old_value'   => $oldLevel,
//         'new_value'   => $newLevel,
//         'admin_name'  => $request->admin_name,
//         'created_at'  => now()
//     ]);

//     return response()->json([
//         'success' => true,
//         'message' => 'Đã cập nhật level và ghi log thành công!'
//     ]);
// }
}