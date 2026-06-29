<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function getRegistrationStats(Request $request)
    {
        $range = $request->get('range', 'week'); // Mặc định là week

        // Cấu hình định dạng ngày tháng tùy theo lựa chọn
        if ($range === 'month') {
            // Thống kê theo tháng (lấy 6 tháng gần nhất)
            $stats = Player::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as date, count(*) as count")
                ->groupBy('date')
                ->orderBy('date', 'DESC')
                ->limit(6)
                ->get();
        } elseif ($range === 'day') {
            // Thống kê theo ngày (lấy 30 ngày gần nhất)
            $stats = Player::selectRaw("DATE(created_at) as date, count(*) as count")
                ->groupBy('date')
                ->orderBy('date', 'DESC')
                ->limit(30)
                ->get();
        } else {
            // Mặc định là tuần (lấy 7 ngày gần nhất)
            $stats = Player::selectRaw("DATE(created_at) as date, count(*) as count")
                ->groupBy('date')
                ->orderBy('date', 'DESC')
                ->limit(7)
                ->get();
        }

        return response()->json($stats);
    }
}