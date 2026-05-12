<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $player = Player::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'gold' => 500, // Tặng vốn khởi nghiệp
        ]);

        $token = $player->createToken('game_token')->plainTextToken;

        return response()->json(['player' => $player, 'token' => $token]);
    }
}
