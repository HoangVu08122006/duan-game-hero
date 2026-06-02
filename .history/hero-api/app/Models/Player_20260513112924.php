<?php

namespace App\Models;

// Sửa lại dòng kế thừa này
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Authenticatable { // Đổi từ Model thành Authenticatable
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name', 
        'password', 
        'gold', 
        'level', 
        'current_floor', 
        'total_power'
    ];

    // Ẩn mật khẩu khi trả về JSON để bảo mật
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Quan hệ 1 - N: Một người chơi có nhiều vũ khí
    public function weapons() {
        return $this->hasMany(PlayerWeapon::class, 'player_id');
    }

    // Lấy vũ khí đang trang bị
    public function activeWeapon() {
        return $this->hasOne(PlayerWeapon::class, 'player_id')->where('is_equipped', true);
    }

    // Quan hệ 1 - 1: Một người chơi có một Pet (hoặc 1-N tùy game bạn)
    public function pet() {
        return $this->hasOne(PlayerPet::class, 'player_id');
    }

    // Tính toán HP thực tế sau khi đã cộng bonus từ nâng cấp
    public function getCurrentHpAttribute()
    {
        // Giả sử mỗi cấp HP nâng cấp tăng 10% máu gốc
        return $this->base_hp * (1 + ($this->upgraded_hp_lv * 0.1));
    }

    // Tính toán Attack thực tế
    public function getCurrentAttackAttribute()
    {
        // Mỗi cấp Attack nâng cấp tăng 10% lực chiến gốc
        return $this->base_attack * (1 + ($this->upgraded_attack_lv * 0.1));
    }

    // Hàm cập nhật Total Power (Lực chiến tổng)
    public function updateTotalPower()
    {
        // Công thức: (Atk thực + Hp thực/10) * các chỉ số khác
        $this->total_power = ($this->current_attack + ($this->current_hp / 10));
        $this->save();
    }

    /**
     * Tính toán toàn bộ chỉ số sức mạnh của Pet dựa trên Level hiện tại
     */
    public function getPetSkillsAttribute()
    {
        $lv = $this->pet_level;
        $baseAtk = 50;

        // 1. Tính Dame Chính
        $mainDame = round($baseAtk * pow(1.1, $lv - 1));

        // 2. Tính Skill 1 (Mở tại Lv 30)
        $skill1 = 0;
        if ($lv >= 30) {
            $dameAtLv29 = $baseAtk * pow(1.1, 29 - 1);
            $skill1 = round(($dameAtLv29 * 1.5) * pow(1.1, $lv - 30));
        }

        // 3. Tính Skill 2 (Mở tại Lv 60)
        $skill2 = 0;
        if ($lv >= 60) {
            $skill1AtLv59 = ($baseAtk * pow(1.1, 28) * 1.5) * pow(1.1, 59 - 30);
            $skill2 = round(($skill1AtLv59 * 1.5) * pow(1.1, $lv - 60));
        }

        // 4. Tính Skill 3 (Mở tại Lv 90)
        $skill3 = 0;
        if ($lv >= 90) {
            // Tính ngược dòng từ Skill 2 ở lv 89
            $skill1AtLv59 = ($baseAtk * pow(1.1, 28) * 1.5) * pow(1.1, 29);
            $skill2AtLv89 = ($skill1AtLv59 * 1.5) * pow(1.1, 89 - 60);
            $skill3 = round(($skill2AtLv89 * 1.5) * pow(1.1, $lv - 90));
        }

        return [
            'main_dame' => $mainDame,
            'skill_1'   => $skill1,
            'skill_2'   => $skill2,
            'skill_3'   => $skill3,
            'total_dps' => $mainDame + $skill1 + $skill2 + $skill3
        ];
    }
}