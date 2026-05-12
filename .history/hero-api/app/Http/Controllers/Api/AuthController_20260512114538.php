<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerWeapon;
use App\Models\PlayerPet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Kiểm tra dữ liệu đầu vào
        $request->validate([
            'name' => 'required|unique:players,name',
            'password' => 'required|min:6'
        ]);

        // 2. Tạo Player
        $player = Player::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'gold' => 1000,
            'level' => 1,
            'total_power' => 100, // Lực chiến khởi điểm
        ]);

        // 3. Tặng Vũ khí mặc định (ID 1) và Pet mặc định (ID 1)
        // Lưu ý: Bạn phải chạy Seeder trước để có ID 1 trong bảng weapons và pets
        PlayerWeapon::create([
            'player_id' => $player->id,
            'weapon_id' => 1,
            'level' => 1,
            'is_equipped' => true
        ]);

        PlayerPet::create([
            'player_id' => $player->id,
            'pet_id' => 1,
            'level' => 1
        ]);

        // 4. Trả về Token
        $token = $player->createToken('game_token')->plainTextToken;

        return response()->json([
            'message' => 'Khởi tạo nhân vật thành công!',
            'token' => $token,
            'player' => $player
        ]);
    }

    
}