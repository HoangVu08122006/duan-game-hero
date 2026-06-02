<?php

namespace App\Models;

// Sửa lại dòng kế thừa này
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Authenticatable { // Đổi từ Model thành Authenticatable
    use HasApiTokens, Notifiable, HasFactory;

    protected $appends = ['current_hp', 'current_attack', 'pet_skills'];
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

// Trong App\Models\Player.php
protected static function booted()
{
    parent::booted();

    // Sự kiện sau khi một Player mới được tạo thành công
    static::created(function ($player) {
        // Tặng vũ khí ID 1 (Kiếm Gỗ) và trang bị luôn cho họ
        $player->weapons()->create([
            'weapon_id' => 1,
            'level' => 1,
            'is_equipped' => true
        ]);
        
        // Sau đó có thể gọi hàm checkUnlock để xem họ có đạt mốc lv nào khác không
        $player->checkAndUnlockWeapon();
    });

    // Logic tính EXP khi lưu (đoạn code cũ của bạn)
    static::saving(function ($player) {
        // ... giữ nguyên logic tính exp/level ...
    });
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
    // public function getCurrentAttackAttribute()
    // {
    //     // Mỗi cấp Attack nâng cấp tăng 10% lực chiến gốc
    //     return $this->base_attack * (1 + ($this->upgraded_attack_lv * 0.1));
    // }
    public function getCurrentAttackAttribute()
        {
            $value = $this->base_attack * (1 + ($this->upgraded_attack_lv * 0.1));
            // Trả về số nguyên 106
            return (int)$value; 
        }

    // Hàm cập nhật Total Power (Lực chiến tổng)
    public function updateTotalPower()
    {
        // Công thức: (Atk thực + Hp thực/10) * các chỉ số khác
        $this->total_power = ($this->current_attack + ($this->current_hp / 10));
        $this->save();
    }


    // Lấy thông tin Pet đang sử dụng (giả sử lấy con đầu tiên hoặc con mới nhất)
    public function getActivePetAttribute()
    {
        return $this->pets()->first();
    }



public function getPetSkillsAttribute()
{
    // Thêm optional để nếu không có pet thì trả về 0, không văng lỗi 500
    $activePetRelation = $this->activePet()->with('pet')->first();
    
    if (!$activePetRelation || !$activePetRelation->pet) {
        return [
            'pet_name'  => 'Chưa có Pet',
            'main_dame' => 0,
            'total_dps' => 0
        ];
    }

    $lv = $activePetRelation->level;
    $baseAtk = $activePetRelation->pet->base_dps ?? 50; // Lưu ý: Migration của bạn đặt là 'base_dps' chứ không phải 'base_attack'

    $mainDame = round($baseAtk * pow(1.1, $lv - 1));
    $skill1 = ($lv >= 30) ? round(($baseAtk * pow(1.1, 28) * 1.5) * pow(1.1, $lv - 30)) : 0;

    return [
        'pet_name'  => $activePetRelation->pet->name,
        'main_dame' => (int)$mainDame,
        'skill_1'   => (int)$skill1,
        'total_dps' => (int)($mainDame + $skill1)
    ];
}

// Trong App\Models\Player.php
public function checkAndUnlockWeapon() {
        $shouldHaveCount = floor($this->level / 100) + 1;
        $currentCount = $this->playerWeapons()->count();

        if ($currentCount < $shouldHaveCount) {
            // Code logic thêm vũ khí mới vào bảng player_weapons ở đây
        }
    }


// Cập nhật lại hàm checkAndUnlockWeapon để nó thực hiện việc INSERT
public function checkAndUnlockWeapon() {
    // 1. Tìm tất cả vũ khí trong hệ thống có yêu cầu level <= level hiện tại của hero
    $availableWeapons = \App\Models\Weapon::where('required_hero_level', '<=', $this->level)->get();

    foreach ($availableWeapons as $weapon) {
        // 2. Kiểm tra xem người chơi đã sở hữu vũ khí này trong bảng player_weapons chưa
        $exists = \App\Models\PlayerWeapon::where('player_id', $this->id)
                                         ->where('weapon_id', $weapon->id)
                                         ->exists();
        
        // 3. Nếu chưa có thì tạo mới (mở khóa)
        if (!$exists) {
            \App\Models\PlayerWeapon::create([
                'player_id' => $this->id,
                'weapon_id' => $weapon->id,
                'level'     => 1,
                'is_equipped' => false
            ]);
        }
    }
}
}