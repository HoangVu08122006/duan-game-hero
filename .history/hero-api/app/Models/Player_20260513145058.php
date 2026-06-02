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
    'exp',
    'base_hp', 
    'base_attack', 
    'current_floor', 
    'highest_floor',
    'upgraded_attack_lv',
    'upgraded_hp_lv',
    'upgraded_crit_lv',
    'upgraded_speed_lv',
    'total_power' // Hãy chắc chắn có dòng này
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
    // Thiết lập mối quan hệ với bảng trung gian player_pets
    public function pets()
    {
        return $this->hasMany(PlayerPet::class, 'player_id');
    }

    // Lấy thông tin Pet đang sử dụng (giả sử lấy con đầu tiên hoặc con mới nhất)
    public function getActivePetAttribute()
    {
        return $this->pets()->first();
    }

    // Logic tính dame dựa trên level của Pet trong bảng player_pets
    public function getPetSkillsAttribute()
    {
        $activePet = $this->active_pet;
        $lv = $activePet ? $activePet->level : 1;
        $baseAtk = 50;

        $mainDame = round($baseAtk * pow(1.1, $lv - 1));

        // Logic Skill 1, 2, 3 dựa trên $lv...
        $skill1 = ($lv >= 30) ? round(($baseAtk * pow(1.1, 28) * 1.5) * pow(1.1, $lv - 30)) : 0;

        return [
            'main_dame' => $mainDame,
            'skill_1'   => $skill1,
            'total_dps' => $mainDame + $skill1
        ];
    }
}