<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerWeapon extends Model
{
    protected $fillable = ['player_id', 'weapon_id', 'level', 'is_equipped'];

    public function getUpgradeCostAttribute() {
    return round(200 * pow(1.1, $this->level - 1));
}

public function getCurrentDamageAttribute() {
    // Giả sử có relationship 'weapon' để lấy base_attack
    return round($this->weapon->base_attack * pow(1.05, $this->level - 1));
}
}