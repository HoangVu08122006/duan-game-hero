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
        $isBossFloor = ($floor % 10 == 0);
        
        // Công thức tăng trưởng: 15% mỗi tầng
        $growthFactor = pow(1.15, $floor - 1);
        
        // Hệ số Boss: 1.5 lần so với quái tầng đó
        $bossMultiplier = $isBossFloor ? 1.5 : 1.0;

        return [
            'monster_id' => $this->id,
            'name'       => $isBossFloor ? "CHÚ TỂ " . strtoupper($this->name) : $this->name,
            'is_boss'    => $isBossFloor,
            'hp'         => round($this->base_hp * $growthFactor * $bossMultiplier),
            'atk'        => round($this->base_atk * $growthFactor * $bossMultiplier),
            'gold'       => round($this->base_gold * $growthFactor * ($isBossFloor ? 3 : 1)), // Boss x3 tiền
            'exp'        => round($this->base_exp * $growthFactor * ($isBossFloor ? 2 : 1)),  // Boss x2 exp
            'prefab'     => $this->prefab_name,
        ];
    }

    public function getStatsForFloor($floor)
{
    $isBossFloor = ($floor % 10 == 0);
    $growthFactor = pow(1.15, $floor - 1);
    $bossMultiplier = $isBossFloor ? 1.5 : 1.0;

    return [
        'monster_id' => $this->id,
        'name'       => $isBossFloor ? "CHÚ TỂ " . strtoupper($this->name) : $this->name,
        'hp'         => round($this->base_hp * $growthFactor * $bossMultiplier),
        'atk'        => round($this->base_atk * $growthFactor * $bossMultiplier),
        'prefab'     => $this->prefab_name,
        'spawn_config' => [
            'is_boss'      => $isBossFloor,
            'spawn_delay'  => $isBossFloor ? 0 : 3, // 3 giây nếu là tầng thường
            'max_entities' => $isBossFloor ? 1 : 5, // Tối đa 5 quái, boss chỉ có 1
        ],
        'rewards' => [
            'gold' => round($this->base_gold * $growthFactor * ($isBossFloor ? 3 : 1)),
            'exp'  => round($this->base_exp * $growthFactor * ($isBossFloor ? 2 : 1)),
        ]
    ];
}
}