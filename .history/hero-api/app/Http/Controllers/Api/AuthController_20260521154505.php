<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerPet;
use App\Models\PlayerWeapon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
public function register(Request $request)
{
    try {
        // Lớp bảo vệ 1: Validate cơ bản chặn trùng tên trên logic thông thường
        $request->validate([
            'name' => 'required|unique:players,name',
            'password' => 'required|min:6'
        ]);

        // Sử dụng Transaction để bảo vệ toàn vẹn dữ liệu
        $data = DB::transaction(function () use ($request) {
            
            // Lớp bảo vệ 2: Chặn trùng tên tuyệt đối ở mức Database Ghi (Write)
            // Nếu phát hiện tên này vừa mới được lưu bởi request trước, nó sẽ trả về bản ghi cũ luôn chứ không tạo mới
            $player = Player::firstOrCreate(
                ['name' => $request->name], // Điều kiện kiểm tra trùng tên
                [
                    'password' => Hash::make($request->password),
                    'gold' => 1000,
                    'level' => 1,
                    'total_power' => 100,
                    'base_hp' => 1000,
                    'base_attack' => 50,
                ]
            );

            // Kiểm tra xem đây có phải tài khoản vừa mới được tạo ra hay không
            // Nếu wasRecentlyCreated = false nghĩa là tên này ĐÃ BỊ TRÙNG do request chạy song song, lập tức hủy tiến trình
            if (!$player->wasRecentlyCreated) {
                throw new \Exception('Tên nhân vật này đã tồn tại, vui lòng chọn tên khác!');
            }

            // Tặng vũ khí mặc định (Sử dụng firstOrCreate cho an toàn tuyệt đối)
            \App\Models\PlayerWeapon::firstOrCreate(
                [
                    'player_id' => $player->id,
                    'weapon_id' => 1
                ],
                [
                    'level' => 1,
                    'is_equipped' => true
                ]
            );

            // Tặng pet mặc định (Sử dụng firstOrCreate)
            PlayerPet::firstOrCreate(
                [
                    'player_id' => $player->id,
                    'pet_id' => 1
                ],
                [
                    'level' => 1,
                    'is_equipped' => true
                ]
            );

            $token = $player->createToken('game_token')->plainTextToken;

            return [
                'token' => $token,
                'player' => $player
            ];
        });

        return response()->json([
            'message' => 'Thành công!',
            'token' => $data['token'],
            'player' => $data['player']
        ], 201);

    } catch (\Exception $e) {
        // Nếu dính lỗi trùng tên hoặc bất kỳ lỗi gì, DB::transaction tự động hủy (Rollback), không sinh data rác
        return response()->json(['error' => $e->getMessage()], 400);
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
        'player' => $player->load(['weapons', 'pets'])
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
        return response()->json(['message' => 'Token không hợp lệ.'], 401);
    }

    // Load đúng tên hàm đã định nghĩa trong Model Player
    // weapons: có / activePet: có / pets: có
    // Nhưng 'pets.pet_info' là SAI vì trong Model PlayerPet bạn đặt tên hàm là 'pet'
    return response()->json([
        'player' => $player->load(['weapons', 'pets'])
    ]);
}
}