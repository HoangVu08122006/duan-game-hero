<?php
namespace App\Http\Controllers\Api; // Hãy đảm bảo namespace khớp với thư mục của bạn

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerWeapon;
use App\Models\PlayerLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminWeaponController extends Controller
{
public function adminUpdatePetLevel(Request $request)
{
    // 1. Validate dữ liệu
    $request->validate([
        'player_pet_id' => 'required|exists:player_pets,id',
        'new_level' => 'required|integer|min:1',
        'admin_name' => 'required|string' // Thêm tên admin thực hiện
    ]);

    // 2. Tìm Pet và lấy giá trị cũ
    $playerPet = \App\Models\PlayerPet::findOrFail($request->player_pet_id);
    $oldLevel = $playerPet->level;
    $newLevel = $request->new_level;

    // 3. Cập nhật Level
    $playerPet->level = $newLevel;
    $playerPet->save();

    // 4. Ghi vào bảng player_logs (dựa trên cấu trúc bạn đã có)
    \Illuminate\Support\Facades\DB::table('player_logs')->insert([
        'player_id'   => $playerPet->player_id,
        'field_name'  => 'pet_level_update', // Hoặc tên field bạn muốn quy định
        'old_value'   => $oldLevel,
        'new_value'   => $newLevel,
        'admin_name'  => $request->admin_name,
        'created_at'  => now()
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Đã cập nhật level và ghi log thành công!'
    ]);
}
}