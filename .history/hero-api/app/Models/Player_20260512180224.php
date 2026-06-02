<?php

namespace App\Models;

// Sửa lại dòng kế thừa này
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Authenticatable { // Đổi từ Model thành Authenticatable
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name', 
        'password', 
        'gold', 
        'level', 
        'current_floor', 
        'total_power'
    ];

    // Ẩn mật khẩu khi trả về JSON để bảo mật
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Quan hệ 1 - N: Một người chơi có nhiều vũ khí
    public function weapons() {
        return $this->hasMany(PlayerWeapon::class, 'player_id');
    }

    // Lấy vũ khí đang trang bị
    public function activeWeapon() {
        return $this->hasOne(PlayerWeapon::class, 'player_id')->where('is_equipped', true);
    }

    // Quan hệ 1 - 1: Một người chơi có một Pet (hoặc 1-N tùy game bạn)
    public function pet() {
        return $this->hasOne(PlayerPet::class, 'player_id');
    }
}