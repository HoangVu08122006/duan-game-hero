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

    // Quan hệ với bảng trung gian (Dùng để lấy Level từng con)
public function playerPets() {
    return $this->hasMany(PlayerPet::class, 'player_id'); 
}

// Quan hệ trực tiếp với bảng Pet gốc (Dùng để lấy Tên, Ảnh) thông qua bảng trung gian
public function pets() {
    return $this->belongsToMany(Pet::class, 'player_pets', 'player_id', 'pet_id')
                ->withPivot('level', 'is_equipped') // Lấy thêm các cột ở bảng trung gian
                ->withTimestamps();
}

// Lấy con Pet đang được trang bị
public function activePet() {
    return $this->hasOne(PlayerPet::class, 'player_id')->where('is_equipped', true);
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

// File: app/Models/Player.php
public function weapons() {
    return $this->hasMany(PlayerWeapon::class, 'player_id');
}

public function pet() {
    return $this->hasOne(PlayerPet::class, 'player_id');
}
    // Lấy thông tin Pet đang sử dụng (giả sử lấy con đầu tiên hoặc con mới nhất)
    public function getActivePetAttribute()
    {
        return $this->pets()->first();
    }

    // Logic tính dame dựa trên level của Pet trong bảng player_pets
    public function getPetSkillsAttribute()
{
    // Lấy pet đang active kèm thông tin gốc của nó
    $activePetRelation = $this->activePet()->with('pet')->first();
    
    if (!$activePetRelation) return null;

    $lv = $activePetRelation->level;
    $baseAtk = $activePetRelation->pet->base_attack ?? 50; // Lấy dame gốc từ bảng Pets

    $mainDame = round($baseAtk * pow(1.1, $lv - 1));
    $skill1 = ($lv >= 30) ? round(($baseAtk * pow(1.1, 28) * 1.5) * pow(1.1, $lv - 30)) : 0;

    return [
        'pet_name'  => $activePetRelation->pet->name,
        'main_dame' => $mainDame,
        'skill_1'   => $skill1,
        'total_dps' => $mainDame + $skill1
    ];
}

// app/Models/Player.php
protected static function booted()
{
    static::saving(function ($player) {
        // EXP lv1->lv2 = 100. Mỗi cấp sau tăng 10%.
        // Công thức tính EXP cần cho cấp hiện tại: 100 * (1.1 ^ (level - 1))
        $expNeeded = (int)round(100 * pow(1.1, $player->level - 1));

        while ($player->exp >= $expNeeded) {
            $player->exp -= $expNeeded; // Trừ EXP đã dùng
            $player->level += 1;        // Lên cấp
            
            // Tính lại mốc EXP cho cấp mới để kiểm tra vòng lặp tiếp theo
            $expNeeded = (int)round(100 * pow(1.1, $player->level - 1));
        }
    });
}
}