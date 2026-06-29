<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardConfig extends Model
{
    protected $table = 'reward_configs';
    
    // Tắt timestamps nếu bảng của bạn không có cột created_at/updated_at
    // Nếu có thì bạn có thể xóa dòng này đi
    public $timestamps = false;

    protected $fillable = [
        'name', 
        'duration', 
        'status', 
        'activated_at'
    ];

    // Hàm lấy cấu hình đang chạy
    public static function getActiveConfig() 
    {
        return self::where('status', 'active')->first();
    }

    // Quan hệ: Một cấu hình có nhiều phần thưởng (RewardItems)
    public function items()
    {
        return $this->hasMany(RewardItem::class, 'config_id');
    }
}