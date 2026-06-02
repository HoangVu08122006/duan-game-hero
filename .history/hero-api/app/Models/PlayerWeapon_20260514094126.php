<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerWeapon extends Model
{
    protected $fillable = ['player_id', 'weapon_id', 'level', 'is_equipped'];

    public function weapon() {
        return $this->belongsTo(Weapon::class, 'weapon_id');
    }

    // Tính giá nâng cấp
    public function getUpgradeCostAttribute() {
        return (int)round(200 * pow(1.1, $this->level - 1));
    }

    // Tính damage thực tế (Gốc * 1.05 ^ lv)
    public function getCurrentDamageAttribute() {
        $base = $this->weapon->base_attack ?? 200;
        return (int)round($base * pow(1.05, $this->level - 1));
    }
}