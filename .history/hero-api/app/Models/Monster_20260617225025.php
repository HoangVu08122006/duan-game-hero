<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monster extends Model
{
    protected $fillable = ['name', 'type', 'base_hp', 'base_atk', 'base_gold', 'base_exp', 'min_floor', 'prefab_name'];

    /**
     * Tính toán chỉ số thực tế dựa trên số tầng
     */
    public function getStatsForFloor($floor)
{
    // Công thức tăng trưởng: Chỉ số gốc * (1.15 mũ n)
    // Tầng 1: hệ số 1 | Tầng 2: hệ số 1.15 | Tầng 10: hệ số ~3.5
    $growthFactor = pow(1.15, $floor - 1);

    return [
        'monster_id' => $this->id,
        'name'       => $this->name,
        // HP và ATK tăng theo tầng
        'hp'         => round($this->base_hp * $growthFactor),
        'atk'        => round($this->base_atk * $growthFactor),
        'prefab'     => $this->prefab_name,
        'spawn_config' => [
            'is_boss'      => false,
            'max_entities' => 5,
        ],
        'rewards' => [
            // Thưởng cũng tăng theo tầng để Hero có động lực
            'gold' => round($this->base_gold * $growthFactor),
            'exp'  => round($this->base_exp * $growthFactor),
        ]
    ];
}
    

class Monster extends Model
{
    // Cho phép tất cả các trường trong CSDL được gán dữ liệu
    protected $guarded = []; 
}

class Boss extends Model
{
    protected $guarded = [];
}
}