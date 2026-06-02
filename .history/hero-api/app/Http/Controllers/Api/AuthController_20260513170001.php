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
    try {
        $request->validate([
            'name' => 'required|unique:players,name',
            'password' => 'required|min:6'
        ]);

        $player = Player::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'gold' => 1000,
            'level' => 1,
            'total_power' => 100,
            'base_hp' => 1000,      // Bổ sung dòng này
            'base_attack' => 50,    // Bổ sung dòng này
        ]);

        // Đảm bảo ID 1 đã tồn tại trong bảng tương ứng
        PlayerWeapon::create([
            'player_id' => $player->id,
            'weapon_id' => 1,
            'level' => 1,
            'is_equipped' => true
        ]);

        PlayerPet::create([
            'player_id' => $player->id,
            'pet_id' => 1,
            'level' => 1,
            'is_equipped' => true
        ]);

        $token = $player->createToken('game_token')->plainTextToken;

        return response()->json([
            'message' => 'Thành công!',
            'token' => $token,
            'player' => $player
        ], 201);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function login(Request $request) {
    $player = \App\Models\Player::where('name', $request->name)->first();

    if (!$player || !\Illuminate\Support\Facades\Hash::check($request->password, $player->password)) {
        return response()->json(['message' => 'Sai tên hoặc mật khẩu!'], 401);
    }


    $token = $player->createToken('game_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'player' => $player->load(['weapons', 'pet'])
    ]);
}

// API ĐĂNG XUẤT
    public function logout(Request $request)
    {
        try {
            // 1. Lấy Token hiện tại mà người chơi đang dùng để gọi API này
            $currentToken = $request->user()->currentAccessToken();

            if ($currentToken) {
                // 2. Xóa Token khỏi Database (Vô hiệu hóa chìa khóa)
                $currentToken->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Đăng xuất thành công. Token đã được hủy.'
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy Token hợp lệ.'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Mẹo: Nếu muốn đăng xuất tất cả các thiết bị (ví dụ: nút "Đăng xuất khỏi mọi nơi")
    public function logoutAllDevices(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Đã đăng xuất khỏi tất cả các thiết bị!']);
    }
    
public function me(Request $request)
{
    $player = $request->user();

    if (!$player) {
        return response()->json([
            'message' => 'Bạn chưa đăng nhập hoặc Token không hợp lệ.'
        ], 401); // Trả về 401 thay vì 500
    }

    return response()->json([
        'player' => $player->load(['weapons.weapon', 'activePet.pet_info']) // Load thêm quan hệ chi tiết nếu cần
    ]);
}
}