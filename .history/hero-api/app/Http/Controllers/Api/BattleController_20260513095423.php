<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Monster;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    $player = $request->user();
    $floor = $player->current_floor;

    $monster = Monster::where('min_floor', '<=', $floor)
                      ->inRandomOrder()
                      ->first();

    if (!$monster) {
        return response()->json(['message' => 'No monsters'], 404);
    }

    $data = $monster->getStatsForFloor($floor);

    return response()->json([
        'success' => true,
        'floor_info' => [
            'current_floor' => $floor,
            'type' => ($floor % 10 == 0) ? 'BOSS_FLOOR' : 'NORMAL_FLOOR'
        ],
        'monster_data' => $data
    ]);
}
}