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

    protected $fillable = [
    'name', 'prefab_name', 'base_dps', 'growth_rate',
    'skill_1_name', 'skill_2_name', 'skill_3_name',
    'skill_1_description', 'skill_2_description', 'skill_3_description'
];

protected static function booted()
{
    static::updated(function ($pet) {
        $changes = $pet->getChanges(); 
        unset($changes['updated_at']);

        foreach ($changes as $key => $newValue) {
            $oldValue = $pet->getOriginal($key);
            
            // Ép kiểu về string để so sánh chính xác hơn
            if ((string)$oldValue !== (string)$newValue) {
                \App\Models\PetLog::create([
                    'pet_id'     => $pet->id,
                    'action'     => 'update',
                    'field_name' => $key,
                    'old_value'  => (string)$oldValue,
                    'new_value'  => (string)$newValue,
                    'admin_name' => 'Admin'
                ]);
            }
        }
    });
}

    
}
