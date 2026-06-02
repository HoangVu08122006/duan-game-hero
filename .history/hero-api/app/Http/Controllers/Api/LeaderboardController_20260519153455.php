<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * API 1: Lấy danh sách Top 100 người chơi
     */
    public function getTop100()
    {
        $topPlayers = Player::select('id', 'name', 'total_power', 'highest_floor')
            ->orderByDesc('total_power')
            ->orderByDesc('highest_floor') // Nếu bằng lực chiến, ai tầng cao hơn xếp trước
            ->limit(100)
            ->get()
            ->map(function ($player, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $player->name,
                    'total_power' => $player->total_power,
                    'highest_floor' => $player->highest_floor,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topPlayers
        ]);
    }

    /**
     * API 2: Lấy thứ hạng chi tiết của một người chơi cụ thể
     */
    public function getMyRank(Request $request, $playerId)
    {
        // 1. Lấy thông tin người chơi hiện tại
        $currentPlayer = Player::find($playerId);

        if (!$currentPlayer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người chơi này.'
            ], 404);
        }

        // 2. Tính tổng số người chơi trong server
        $totalPlayers = Player::count();
        if ($totalPlayers === 0) $totalPlayers = 1;

        // 3. Tính số người có lực chiến cao hơn người chơi này
        // (Tối ưu bằng cách đếm số người giỏi hơn thay vì load hết database)
        $higherPlayersCount = Player::where('total_power', '>', $currentPlayer->total_power)
            ->orWhere(function ($query) use ($currentPlayer) {
                $query->where('total_power', $currentPlayer->total_power)
                      ->where('highest_floor', '>', $currentPlayer->highest_floor);
            })
            ->count();

        // Thứ hạng thực tế của người chơi (bắt đầu từ 1)
        $myRank = $higherPlayersCount + 1;

        // 4. Xử lý logic hiển thị theo yêu cầu
        $rankDisplay = null;
        $topPercentage = null;

        if ($myRank <= 100) {
            $rankDisplay = $myRank; // Nằm trong top 100 thì hiện số hạng
        } else {
            // Ngoài top 100 thì tính theo công thức phần trăm
            // Ví dụ: Đứng thứ 150 trên 1000 người -> (150 / 1000) * 100 = Top 15%
            $topPercentage = round(($myRank / $totalPlayers) * 100, 2);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $currentPlayer->id,
                'name' => $currentPlayer->name,
                'total_power' => $currentPlayer->total_power,
                'highest_floor' => $currentPlayer->highest_floor,
                'rank' => $rankDisplay, // Trả về null nếu ngoài top 100
                'top_percentage' => $topPercentage, // Trả về null nếu trong top 100
                'display_text' => $myRank <= 100 ? "Hạng $myRank" : "Top $topPercentage%"
            ]
        ]);
    }
}