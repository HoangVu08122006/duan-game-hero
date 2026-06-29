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
$validated = $request->validate([
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required', // Bỏ tạm validate exists để log lỗi
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    // LOG để xem giá trị gửi lên
    \Log::info('Admin Update Pet Request:', $validated);

    $petPivot = PlayerPet::where('id', $validated['player_pet_id'])
        ->where('player_id', $validated['player_id'])
        ->first();

    if (!$petPivot) {
        // Trả về thông tin chi tiết hơn để debug
        return response()->json([
            'message' => 'Không tìm thấy bản ghi!',
            'debug' => "Không tìm thấy bản ghi với ID: " . $validated['player_pet_id'] . " cho Player: " . $validated['player_id']
        ], 404);
    }

    $oldLevel = $petPivot->level;
    $newLevel = $validated['new_level'];

    DB::transaction(function () use ($petPivot, $newLevel, $oldLevel, $validated) {
        $petPivot->update(['level' => $newLevel]);

        PlayerLog::create([
            'player_id'  => $validated['player_id'],
            'field_name' => "Admin điều chỉnh cấp độ Pet",
            'old_value'  => "Cấp " . $oldLevel,
            'new_value'  => "Cấp " . $newLevel,
            'created_at' => now(),
        ]);
    });

    return response()->json(['success' => true, 'message' => "Đã chỉnh Pet từ cấp $oldLevel sang $newLevel"]);
}
}