<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Player.php
class Player extends Model {
    use HasApiTokens, Notifiable; // Thêm dòng này để dùng được Token

    protected $fillable = ['name', 'password', 'gold', 'level', 'current_floor', 'total_power'];

    public function weapons() {
        return $this->hasMany(PlayerWeapon::class);
    }

    public function activeWeapon() {
        return $this->hasOne(PlayerWeapon::class)->where('is_equipped', true);
    }

    public function pet() {
        return $this->hasOne(PlayerPet::class);
    }
}