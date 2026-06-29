<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Models\PlayerPet;
use App\Models\PlayerLog;
use Illuminate\Support\Facades\DB;

class AdminPetController extends Controller
{
    public function adminUpgradePet(Request $request)
{

// Bỏ tạm 'exists' để kiểm tra xem request có vào được database không
    $request->validate([
        'player_id' => 'required',
        'player_pet_id' => 'required',
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    // Tìm pet
    $pet = PlayerPet::where('id', $request->player_pet_id)
        ->where('player_id', $request->player_id)
        ->first();

    // Nếu không tìm thấy, trả về lỗi chi tiết để biết tại sao
    if (!$pet) {
        return response()->json([
            'message' => 'Không tìm thấy bản ghi trong bảng player_pets với ID: ' . $request->player_pet_id
        ], 404);
    }

    // 1. Validate dữ liệu gửi lên từ Vue
    $request->validate([
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required|exists:player_pets,id', // Đây là ID bảng trung gian
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    // 2. Tìm đúng bản ghi trong bảng trung gian (PlayerPet)
    // Đảm bảo Model PlayerPet trỏ đúng table 'player_pets'
    $petPivot = PlayerPet::where('id', $request->player_pet_id)
        ->where('player_id', $request->player_id)
        ->firstOrFail();

    $oldLevel = $petPivot->level;
    $newLevel = $request->new_level;

    // 3. Thực hiện update và ghi log
    DB::transaction(function () use ($petPivot, $newLevel, $oldLevel, $request) {
        $petPivot->update(['level' => $newLevel]);

        PlayerLog::create([
            'player_id'  => $request->player_id,
            'field_name' => "Admin điều chỉnh cấp độ Pet",
            'old_value'  => "Cấp " . $oldLevel,
            'new_value'  => "Cấp " . $newLevel,
            'created_at' => now(),
        ]);
    });

    return response()->json(['success' => true, 'message' => "Đã chỉnh Pet từ cấp $oldLevel sang $newLevel"]);
}
}