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
        'player_id' => 'required',
        'player_pet_id' => 'required',
        'new_level' => 'required|integer'
    ]);

    // Tìm thú cưng thông qua quan hệ của người chơi
    // Cách này ĐẢM BẢO thú cưng phải thuộc về đúng player_id đó
    $player = Player::findOrFail($validated['player_id']);
    $pet = $player->playerPets()->where('player_pets.id', $validated['player_pet_id'])->first();

    if (!$pet) {
        return response()->json(['message' => 'Không tìm thấy thú cưng này trong tài khoản người chơi!'], 404);
    }

    $pet->update(['level' => $validated['new_level']]);
    return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
}
}

