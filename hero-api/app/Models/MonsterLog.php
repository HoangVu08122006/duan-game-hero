<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonsterLog extends Model
{
    protected $table = 'monster_logs';

    // Bỏ 'created_at' khỏi fillable, để Eloquent tự quản lý
    protected $fillable = [
        'admin_id', 'target_type', 'target_id', 'action', 'payload', 'description'
    ];

    public $timestamps = true; 
}