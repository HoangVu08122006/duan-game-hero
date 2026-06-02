<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReward extends Model
{
    protected $fillable = ['player_id', 'last_claimed_date', 'current_streak_day'];

    // Cấu hình quà tặng cố định cho 7 ngày (Vòng lặp vô hạn)
    const REWARDS_CONFIG = [
        1 => ['type' => 'gold', 'amount' => 1000, 'name' => '1,000 Vàng'],
        2 => ['type' => 'exp',  'amount' => 200,  'name' => '200 EXP'],
        3 => ['type' => 'gold', 'amount' => 2000, 'name' => '2,000 Vàng'],
        4 => ['type' => 'exp',  'amount' => 500,  'name' => '500 EXP'],
        5 => ['type' => 'gold', 'amount' => 5000, 'name' => '5,000 Vàng'],
        6 => ['type' => 'exp',  'amount' => 1000, 'name' => '1,000 EXP'],
        7 => ['type' => 'gold', 'amount' => 10000, 'name' => '10,000 Vàng'], // Ngày cuối tuần quà to hơn
    ];
};