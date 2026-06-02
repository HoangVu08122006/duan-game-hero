<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Weapon extends Model
{
    // Tự động quản lý created_at và updated_at
    protected $fillable = [
        'image_weapon', 
        'name', 
        'base_attack', 
        'current_attack', 
        'required_hero_level'
    ];
}
