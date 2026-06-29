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
        'player_pet_id' => 'required', // ID của bảng player_pets
        'new_level' => 'required|integer|min:1|max:999'
    ]);

    $player = Player::findOrFail($validated['player_id']);
    
    // Tìm thú cưng trong tập hợp thú cưng của người chơi đó
    $pet = $player->playerPets()->where('player_pets.id', $validated['player_pet_id'])->first();

    if (!$pet) {
        return response()->json(['message' => 'Lỗi: Không tìm thấy thú cưng này trong tài khoản người chơi!'], 404);
    }

    $pet->level = $validated['new_level'];
    $pet->save();

    return response()->json(['success' => true, 'message' => "Đã chỉnh Pet sang cấp {$pet->level}"]);
}
}

