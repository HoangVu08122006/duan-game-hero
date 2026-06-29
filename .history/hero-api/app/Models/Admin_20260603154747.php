<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // Kế thừa để dùng được Auth
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Các cột được phép insert/update
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    // Ẩn mật khẩu khi lấy dữ liệu Admin
    protected $hidden = [
        'password',
    ];
}