<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardItem extends Model
{
    // Bảng này không dùng created_at/updated_at nên để false
    public $timestamps = false;
    protected $table = 'reward_items';

    // Cho phép gán dữ liệu hàng loạt
    protected $fillable = [
        'config_id', 
        'day_index', 
        'name', 
        'reward_type', 
        'amount'
    ];

    // Thiết lập quan hệ: Một Item thuộc về một Config
    public function config()
    {
        return $this->belongsTo(RewardConfig::class, 'config_id');
    }
}