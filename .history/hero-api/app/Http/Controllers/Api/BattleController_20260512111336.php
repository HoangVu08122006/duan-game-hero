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
}