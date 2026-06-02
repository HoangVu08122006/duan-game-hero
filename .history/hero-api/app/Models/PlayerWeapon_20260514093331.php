<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerWeapon extends Model
{
    protected $fillable = ['player_id', 'weapon_id', 'level', 'is_equipped'];

    // Thiết lập quan hệ để lấy base_attack từ bảng weapons
    public function weapon() {
        return $this->belongsTo(Weapon::class);
    }

    // Định nghĩa upgrade_cost: Giá gốc 200, mỗi cấp tăng 10%
    public function getUpgradeCostAttribute() {
        return round(200 * pow(1.1, $this->level - 1));
    }

    // Định nghĩa current_damage: Damage gốc vũ khí * 5% mỗi cấp
    public function getCurrentDamageAttribute() {
        $baseAttack = $this->weapon->base_attack; 
        return round($baseAttack * pow(1.05, $this->level - 1));
    }
}