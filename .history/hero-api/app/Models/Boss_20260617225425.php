<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boss extends Model
{
    protected $guarded = [];

    // // Định nghĩa các kiểu dữ liệu để Laravel tự parse JSON
    // protected $casts = [
    //     'skills' => 'array',
    // ];

    // public function getStatsForFloor($floor)
    // {
    //     $growthFactor = pow(1.15, $floor - 1);
        
    //     // Boss có hệ số riêng (Ví dụ HP x3, ATK x2)
    //     return [
    //         'boss_id' => $this->id,
    //         'name'    => "[BOSS] " . $this->name,
    //         'hp'      => round($this->base_hp * $growthFactor * 3), 
    //         'atk'     => round($this->base_attack * $growthFactor * 2),
    //         'skills'  => $this->skills,
    //         'prefab'  => $this->prefab_name,
    //     ];
    // }
}
