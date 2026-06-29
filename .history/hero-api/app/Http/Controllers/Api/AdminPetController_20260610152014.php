<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlayerPet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminPetController extends Controller
{
    public function adminUpdatePetLevel(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'player_id'     => 'required|integer',
            'player_pet_id' => 'required|integer',
            'new_level'     => 'required|integer|min:1|max:999'
        ]);

        try {
            // 2. Tìm Pet với đối chiếu kép (Đúng Pet và Đúng Chủ)
$pet = PlayerPet::where('id', $validated['player_pet_id'])
                ->where('player_id', $validated['player_id'])
                ->first();

            if (!$pet) {
                return response()->json(['message' => 'Lỗi: Thú cưng này không thuộc về người chơi này!'], 404);
            }

            $oldLevel = $pet->level;
            $newLevel = $validated['new_level'];

            // 3. Sử dụng Transaction để đảm bảo an toàn dữ liệu
            DB::beginTransaction();

            // Cập nhật level
            $pet->level = $newLevel;
            $pet->save();

            // 4. Ghi log vào bảng player_logs (Dựa trên cấu trúc bạn đã có)
            DB::table('player_logs')->insert([
                'player_id'   => $validated['player_id'],
                'field_name'  => 'admin_pet_level_update',
                'old_value'   => $oldLevel,
                'new_value'   => $newLevel,
                'admin_name'  => 'Admin', // Bạn có thể thay bằng Auth::user()->name nếu có
                'created_at'  => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "Cập nhật thành công từ cấp {$oldLevel} lên {$newLevel}!"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi cập nhật Pet: " . $e->getMessage());
            return response()->json(['message' => 'Có lỗi hệ thống xảy ra!'], 500);
        }
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