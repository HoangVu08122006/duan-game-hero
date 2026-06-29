<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    /**
     * API chung: Lấy danh sách xếp hạng
     * Admin dùng để quản lý (có phân trang), Người chơi dùng để xem (có thể giới hạn)
     */
    public function getRankings(Request $request)
    {
        $query = Player::select('id', 'name', 'total_power', 'highest_floor', 'reached_highest_floor_at')
            ->orderByDesc('total_power')
            ->orderByDesc('highest_floor')
            ->orderBy('reached_highest_floor_at', 'asc'); // Ai đạt trước xếp trên

        // Nếu là Admin, trả về phân trang đầy đủ
        if ($request->user() && $request->user()->is_admin) {
            return response()->json([
                'success' => true,
                'data' => $query->paginate(50)
            ]);
        }

        // Nếu là người chơi, chỉ lấy Top 100
        $topPlayers = $query->limit(100)->get()->map(function ($player, $index) {
            return [
                'rank' => $index + 1,
                'name' => $player->name,
                'total_power' => $player->total_power,
                'highest_floor' => $player->highest_floor,
            ];
        });

        return response()->json(['success' => true, 'data' => $topPlayers]);
    }

    /**
     * API: Lấy thứ hạng chi tiết của bất kỳ người chơi nào
     */
    public function getMyRank($playerId)
    {
        $currentPlayer = Player::find($playerId);

        if (!$currentPlayer) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người chơi.'], 404);
        }

        // Đếm chính xác thứ hạng bằng 3 tiêu chí
        $myRank = Player::where('total_power', '>', $currentPlayer->total_power)
            ->orWhere(function ($query) use ($currentPlayer) {
                $query->where('total_power', $currentPlayer->total_power)
                      ->where('highest_floor', '>', $currentPlayer->highest_floor);
            })
            ->orWhere(function ($query) use ($currentPlayer) {
                $query->where('total_power', $currentPlayer->total_power)
                      ->where('highest_floor', $currentPlayer->highest_floor)
                      ->where('reached_highest_floor_at', '<', $currentPlayer->reached_highest_floor_at);
            })
            ->count() + 1;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $currentPlayer->id,
                'name' => $currentPlayer->name,
                'rank' => $myRank,
                'display_text' => "Hạng $myRank"
            ]
        ]);
    }

    public function getRegistrationStats(Request $request)
{
    $range = $request->get('range', 'week'); // Mặc định là tuần

    // Ví dụ truy vấn thống kê theo ngày (đơn giản nhất)
    $stats = Player::selectRaw('DATE(created_at) as date, count(*) as count')
        ->groupBy('date')
        ->orderBy('date', 'DESC')
        ->limit(30)
        ->get();

    return response()->json($stats);
}
}