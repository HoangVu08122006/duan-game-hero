<?php

namespace App\Models;

// Thay đổi dòng này
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

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