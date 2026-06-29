<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    protected $table = 'entity_drafts';
    
    protected $fillable = [
        'entity_type', // 'monster' hoặc 'boss'
        'target_id',   // null nếu tạo mới
        'payload',     // JSON data
        'status'       // 'pending', 'approved', 'rejected'
    ];

    protected $casts = [
        'payload' => 'array' // Tự động decode JSON thành mảng
    ];
}