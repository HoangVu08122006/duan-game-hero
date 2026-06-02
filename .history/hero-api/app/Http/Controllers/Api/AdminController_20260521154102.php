<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{

public function index()
    {
        try {
            // Lấy toàn bộ danh sách người chơi từ bảng (giả định tên bảng của bạn là 'users' hoặc 'players')
            // Thay 'users' bằng tên bảng thực tế của bạn nếu khác nhé
            $players = DB::table('players')
                ->select(
                    'id', 'name', 'gold', 'level', 'exp', 
                    'current_floor', 'kill_count', 'highest_floor', 
                    'upgraded_attack_lv', 'upgraded_hp_lv', 'upgraded_crit_rate_lv', 
                    'upgraded_speed_lv', 'total_power', 'base_hp', 'current_hp', 
                    'base_attack', 'upgraded_crit_damage_lv', 'created_at', 'updated_at'
                )
                ->get();

            // Trả về dữ liệu dạng JSON thành công
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy danh sách người chơi thành công!',
                'data' => $players
            ], 200);

        } catch (\Exception $e) {
            // Xử lý lỗi nếu có ngoại lệ xảy ra
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi hệ thống.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // API Admin xem chi tiết người chơi không cần token
public function show($id)
{
    try {
        // Tìm người chơi theo ID, nếu không thấy sẽ trả về lỗi 404
        // Thay 'Player' bằng tên Model thực tế của bạn nếu khác nhé
        $player = Player::with(['weapons', 'pets'])->find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy thông tin chi tiết người chơi thành công!',
            'player' => $player
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // 1. Tìm người chơi theo ID
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        // 2. Dùng Transaction để xóa sạch dữ liệu liên quan một cách an toàn
        DB::transaction(function () use ($player) {
            // Xóa dữ liệu ở các bảng liên kết trước để tránh lỗi khóa ngoại (Foreign Key Constraint)
            $player->weapons()->delete(); // Hoặc DB::table('player_weapons')->where('player_id', $player->id)->delete();
            $player->playerPets()->delete(); // Hoặc DB::table('player_pets')->where('player_id', $player->id)->delete();

            // Cuối cùng là xóa tài khoản người chơi
            $player->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa tài khoản người chơi và toàn bộ dữ liệu liên quan thành công!'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống khi xóa.',
            'error' => $e->getMessage()
        ], 500);
    }
}
// Trong Controller
public function getPlayerTotalPowerForAdmin($id)
{
    try {
        $player = Player::find($id);

        if (!$player) {
            return response()->json(['message' => 'Không tìm thấy người chơi'], 404);
        }

        // Gọi hàm từ Model thông qua đối tượng $player
        $totalPower = $player->calculateTotalPower();
        
        $player->total_power = $totalPower;
        $player->save();

        return response()->json([
            'status' => 'success',
            'calculated_total_power' => $totalPower
        ]);

    } catch (\Exception $e) {
        // Ghi log để bạn biết chính xác lỗi ở đâu
        Log::error($e->getMessage());
        
        // Trả về JSON lỗi để Frontend hiển thị thông báo thay vì lỗi 500
        return response()->json([
            'status' => 'error',
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ], 500);
    }
}
public function getPlayerAccountForAdmin($id)
{
    try {
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy thông tin tài khoản thành công!',
            'data' => [
                'id' => $player->id,
                'current_name' => $player->name,
                'encrypted_password' => $player->password, // Trả về chuỗi Bcrypt đã mã hóa lưu trong DB
                'created_at' => $player->created_at,
                'updated_at' => $player->updated_at
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống.',
            'error' => $e->getMessage()
        ], 500);
    }
}

// 2. API Admin Thay đổi Tên và Mật khẩu của tài khoản
public function updatePlayerAccountByAdmin(Request $request, $id)
{
    try {
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        // Validate dữ liệu: Tên mới không được trùng với người khác (trừ chính họ), mật khẩu tối thiểu 6 ký tự
        $request->validate([
            'name' => 'nullable|string|unique:players,name,' . $player->id,
            'password' => 'nullable|string|min:6'
        ]);

        // Sử dụng Transaction để đảm bảo an toàn dữ liệu
        DB::transaction(function () use ($request, $player) {
            $updateData = [];

            // Nếu admin có truyền lên trường name thì mới sửa
            if ($request->has('name') && !empty($request->name)) {
                $updateData['name'] = $request->name;
            }

            // Nếu admin có truyền lên trường password thì tiến hành mã hóa Hash rồi mới sửa
            if ($request->has('password') && !empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Chỉ cập nhật nếu có dữ liệu thay đổi
            if (!empty($updateData)) {
                $player->update($updateData);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Admin đã cập nhật thông tin tài khoản thành công!',
            'data' => [
                'id' => $player->id,
                'new_name' => $player->name,
                'updated_at' => $player->updated_at
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cập nhật thất bại hoặc tên nhân vật đã tồn tại.',
            'error' => $e->getMessage()
        ], 400);
    }
}

public function adminSetStats(Request $request, $id)
{
    try {
        // 1. Tìm người chơi theo ID
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        // 2. Validate dữ liệu truyền lên (Đảm bảo phải là số nguyên dương và không bắt buộc điền hết)
        $request->validate([
            'upgraded_attack_lv'      => 'nullable|integer|min:0',
            'upgraded_hp_lv'          => 'nullable|integer|min:0',
            'upgraded_crit_rate_lv'   => 'nullable|integer|min:0',
            'upgraded_speed_lv'       => 'nullable|integer|min:0',
            'upgraded_crit_damage_lv' => 'nullable|integer|min:0',
        ]);

        // 3. Lọc lấy những chỉ số được Admin gửi lên để cập nhật
        $updateData = [];
        $statFields = [
            'upgraded_attack_lv',
            'upgraded_hp_lv',
            'upgraded_crit_rate_lv',
            'upgraded_speed_lv',
            'upgraded_crit_damage_lv'
        ];

        foreach ($statFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        if (empty($updateData)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không có chỉ số nào được truyền lên để thay đổi.'
            ], 400);
        }

        // 4. Cập nhật thẳng vào Database (Bỏ qua mọi logic check vàng/cấp độ Hero)
        $player->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin đã thay đổi cấp độ chỉ số thành công (Miễn phí)!',
            'player_info' => [
                'id' => $player->id,
                'name' => $player->name
            ],
            'current_stats' => [
                'upgraded_attack_lv'      => $player->upgraded_attack_lv,
                'upgraded_hp_lv'          => $player->upgraded_hp_lv,
                'upgraded_crit_rate_lv'   => $player->upgraded_crit_rate_lv,
                'upgraded_speed_lv'       => $player->upgraded_speed_lv,
                'upgraded_crit_damage_lv' => $player->upgraded_crit_damage_lv,
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống khi chỉnh sửa chỉ số.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}