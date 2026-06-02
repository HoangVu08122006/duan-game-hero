<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; 
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Authenticatable { 
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name', 'password', 'gold', 'level', 'exp',
        'base_hp', 'base_attack', 
        'current_floor', 'highest_floor',
        'upgraded_attack_lv', 'upgraded_hp_lv', 
        'upgraded_crit_rate_lv', 'upgraded_crit_damage_lv', 'upgraded_speed_lv',
        'total_power'
    ];

    protected $hidden = ['password', 'remember_token'];

    // --- RELATIONSHIPS ---

    public function playerPets() {
        return $this->hasMany(PlayerPet::class, 'player_id'); 
    }

    // Quan hệ lấy con Pet đang được trang bị kèm thông tin gốc (Pet)
    public function equippedPet() {
        return $this->hasOne(PlayerPet::class, 'player_id')
                    ->where('is_equipped', true)
                    ->with('pet');
    }

    public function weapons() {
        return $this->hasMany(PlayerWeapon::class, 'player_id');
    }

    // --- LOGIC TỰ ĐỘNG LEVEL UP (EXP) ---

    protected static function booted()
    {
        static::saving(function ($player) {
            // EXP lv1->lv2 = 100. Mỗi cấp sau tăng 10%
            $expNeeded = (int)round(100 * pow(1.1, $player->level - 1));

            while ($player->exp >= $expNeeded) {
                $player->exp -= $expNeeded; 
                $player->level += 1;        
                $expNeeded = (int)round(100 * pow(1.1, $player->level - 1));
            }
        });
    }

    // --- ATTRIBUTES & CALCULATIONS ---

    /**
     * Lấy chỉ số Pet đang trang bị (Dùng cho API me)
     */
    public function getActivePetStatsAttribute()
    {
        $active = $this->equippedPet;
        if (!$active) return null;

        $lv = $active->level;
        $baseAtk = $active->pet->base_attack ?? 50;

        // Công thức dame Pet 10% mỗi cấp
        $mainDame = round($baseAtk * pow(1.1, $lv - 1));
        
        return [
            'name'      => $active->pet->name,
            'level'     => $lv,
            'main_dame' => $mainDame,
        ];
    }

    /**
     * Hàm tính lại Total Power (Gọi sau khi nâng cấp chỉ số)
     */
    public function calculateAndSaveTotalPower()
    {
        // Chỉ số chí mạng thực tế (Gốc + Lv nâng cấp)
        $actualCritRate = 10 + ($this->upgraded_crit_rate_lv * 2);      // Gốc 10%
        $actualCritDamage = 100 + ($this->upgraded_crit_damage_lv * 5); // Gốc 100%
        $actualSpeed = 1 + ($this->upgraded_speed_lv * 0.2);           // Gốc 1.0

        // 1. Sát thương trung bình mỗi phát (Tính cả chí mạng)
        $avgDamagePerHit = $this->base_attack * (1 + ($actualCritRate / 100) * ($actualCritDamage / 100));

        // 2. Lực chiến tấn công (DPS): Sát thương x Tốc độ đánh
        $powerFromOffense = $avgDamagePerHit * $actualSpeed;

        // 3. Lực chiến phòng thủ
        $powerFromDefense = $this->base_hp / 10;

        $this->total_power = (int)round($powerFromOffense + $powerFromDefense);
        $this->save();
    }
}