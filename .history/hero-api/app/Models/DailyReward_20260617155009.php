<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReward extends Model
{
    class DailyReward extends Model
{
    // Kiểm tra xem tên các cột này có khớp với bảng 'daily_rewards' không
    protected $fillable = ['player_id', 'last_claimed_date', 'current_streak_day'];
}
};