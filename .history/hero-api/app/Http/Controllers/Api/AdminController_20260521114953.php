<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}