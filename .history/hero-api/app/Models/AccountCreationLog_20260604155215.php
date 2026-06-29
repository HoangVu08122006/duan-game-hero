<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountCreationLog extends Model
{
    protected $fillable = [
        'player_id',
        'created_by_admin_id',
        'creation_method',
        updated_at
created_at


    ];

    // Quan hệ: Log thuộc về 1 Player
    public function player() {
        return $this->belongsTo(Player::class);
    }

    // Quan hệ: Log thuộc về 1 Admin (nếu là manual)
    public function admin() {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }
}