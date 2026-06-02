<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;

class BattleController extends Controller
{
    public function getLeaderboard() {
        $topPlayers = Player::orderBy('highest_floor', 'desc')
                            ->orderBy('total_power', 'desc')
                            ->limit(50)
                            ->get(['name', 'total_power', 'highest_floor']);

        return response()->json($topPlayers);
    }

    /**
     * API Triệu hồi quái vật/Boss theo tầng
     */
    public function spawn(Request $request)
    {
        $player = Auth::user(); // Lấy player đang đăng nhập
        $currentFloor = $player->current_floor;

        // 1. Lấy ngẫu nhiên 1 con quái có đủ điều kiện xuất hiện ở tầng này
        $monster = Monster::where('min_floor', '<=', $currentFloor)
                          ->inRandomOrder()
                          ->first();

        if (!$monster) {
            return response()->json(['message' => 'Không tìm thấy quái vật phù hợp!'], 404);
        }

        // 2. Lấy dữ liệu đã qua tính toán scaling
        $data = $monster->getStatsForFloor($currentFloor);

        return response()->json([
            'success' => true,
            'floor'   => $currentFloor,
            'data'    => $data
        ]);
    }
}