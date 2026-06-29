<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCreationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'created_by_admin_id',
        'creation_method'
    ];

    // Định nghĩa quan hệ (Tùy chọn, giúp bạn dễ truy vấn hơn)
    public function player() {
        return $this->belongsTo(Player::class);
    }

    public function admin() {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }
}