<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    // Chỉ định tên bảng nếu tên bảng trong DB không phải là dạng số nhiều của Model (pets)
    protected $table = 'pets';

    // Cho phép Laravel cập nhật tất cả các cột từ request
    protected $guarded = [];
}
