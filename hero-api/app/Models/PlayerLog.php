<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerLog extends Model
{
    protected $fillable = ['player_id', 'field_name', 'old_value', 'new_value', 'created_at'];
    // Tắt timestamps tự động nếu bạn tự quản lý created_at
    public $timestamps = false;
    public function player() {
    return $this->belongsTo(Player::class);
}
}
