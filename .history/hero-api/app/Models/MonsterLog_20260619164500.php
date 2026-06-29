<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonsterLog extends Model
{
    // Trỏ đúng bảng bạn đã có
    protected $table = 'monster_logs';

    // Cho phép thêm dữ liệu hàng loạt
    protected $fillable = [
        'admin_id', 
        'target_type', // Dùng cho 'boss' hoặc 'monster'
        'target_id', 
        'action', 
        'payload', 
        'description'
    ];

    // Tắt timestamps nếu bảng của bạn không có created_at/updated_at
    // Nếu bảng có created_at, hãy để là true (mặc định)
    public $timestamps = true; 
}