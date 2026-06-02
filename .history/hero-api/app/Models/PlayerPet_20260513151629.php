<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerPet extends Model
{
    // Chỉ định tên bảng nếu bạn đặt tên không theo chuẩn số nhiều (optional)
    protected $table = 'player_pets';

    protected $fillable = [
        'player_id', 
        'pet_id', 
        'level', 
        'is_equipped' // Đảm bảo có cột này để biết con nào đang được dùng
    ];

    /**
     * Quan hệ: Bản ghi sở hữu này thuộc về một Pet cụ thể
     * Giúp lấy được name, base_attack, image từ bảng pets
     */
    public function pet()
    {
        return $this->belongsTo(Pet::class, 'pet_id');
    }

    /**
     * Quan hệ: Bản ghi sở hữu này thuộc về một người chơi
     */
    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}