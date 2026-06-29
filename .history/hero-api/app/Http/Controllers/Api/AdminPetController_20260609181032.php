<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerLog;
use App\Models\PlayerPet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

class AdminPetController extends Controller
{
public function adminUpgradePet(Request $request)
{
    $validated = $request->validate([
        'player_id' => 'required',
        'player_pet_id' => 'required',
        'new_level' => 'required|integer'
    ]);

    // LOG RA ĐỂ KIỂM TRA
    \Log::info("Admin Pet Upgrade Request:", $validated);

    // Dùng Query Builder để xem nó có thực sự tồn tại trong DB không
    $exists = PlayerPet::where('id', (int)$validated['player_pet_id'])
                       ->where('player_id', (int)$validated['player_id'])
                       ->exists();
    
    if (!$exists) {
        Log::error("Pet not found in DB with:", $validated);
        return response()->json(['message' => 'Lỗi: Thú cưng không tồn tại với player_id này!'], 404);
    }

    $pet = PlayerPet::find((int)$validated['player_pet_id']);
    $pet->update(['level' => (int)$validated['new_level']]);

    return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
}
}

