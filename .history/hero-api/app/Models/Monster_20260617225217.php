<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monster extends Model
{
    class Monster extends Model
{
    protected $guarded = []; // Dùng guarded để linh hoạt khi insert dữ liệu từ Draft

    public function getStatsForFloor($floor)
    {
        // Gợi ý: Dùng hằng số hoặc config để dễ quản lý độ khó
        $growthRate = 1.15; 
        $growthFactor = pow($growthRate, $floor - 1);

        return [
            'monster_id' => $this->id,
            'name'       => $this->name,
            'hp'         => round($this->base_hp * $growthFactor),
            'atk'        => round($this->base_atk * $growthFactor),
            'prefab'     => $this->prefab_name,
            'rewards'    => [
                'gold' => round($this->base_gold * $growthFactor),
                'exp'  => round($this->base_exp * $growthFactor),
            ]
        ];
    }
}
    

}