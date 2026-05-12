<?php

namespace App\Models;

// Thay đổi dòng này
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Player extends Authenticatable {
    use HasApiTokens, Notifiable; // Thêm dòng này để dùng được Token

    protected $fillable = ['name', 'password', 'gold', 'level', 'current_floor', 'total_power'];

    // ... các function weapons(), pet() giữ nguyên
}