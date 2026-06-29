<?php

namespace App\Models;

use App\Models\PetLog;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    // Chỉ định tên bảng nếu tên bảng trong DB không phải là dạng số nhiều của Model (pets)
    protected $table = 'pets';

    // Cho phép Laravel cập nhật tất cả các cột từ request
    protected $guarded = [];

    protected static function booted()
    {
        static::updated(function ($pet) {
            // Lấy danh sách các thay đổi
            $changes = $pet->getChanges(); 
            
            // Loại bỏ 'updated_at' khỏi danh sách log nếu không cần thiết
            unset($changes['updated_at']);

            foreach ($changes as $key => $newValue) {
                // Lấy giá trị cũ từ $pet->getOriginal()
                $oldValue = $pet->getOriginal($key);

                PetLog::create([
                    'pet_id'     => $pet->id,
                    'action'     => 'update',
                    'field_name' => $key,
                    'old_value'  => (string)$oldValue,
                    'new_value'  => (string)$newValue,
                    'admin_name' => 'Admin' // Bạn có thể thay bằng Auth::user()->name nếu có đăng nhập
                ]);
            }
        });
    }
}
