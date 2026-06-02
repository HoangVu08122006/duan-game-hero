<?php

namespace App\Models;

// Sửa lại dòng kế thừa này
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class Player extends Authenticatable { // Đổi từ Model thành Authenticatable
    use HasApiTokens, Notifiable, HasFactory;

    protected $appends = ['current_hp', 'current_attack', 'pet_skills'];
    protected $fillable = [
    'name', 
    'password', 
    'gold', 
    'level', 
    'exp',
    'kill_count',
    'base_hp', 
    'base_attack', 
    'current_floor', 
    'highest_floor',
    'upgraded_attack_lv',
    'upgraded_hp_lv',
    'upgraded_crit_rate_lv',
    'upgraded_crit_damage_lv',
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

    // CƠ CHẾ 1: Khi Hero mới được tạo (Level 1)
    static::created(function ($player) {
        $allWeapons = \App\Models\Weapon::orderBy('id', 'asc')->get();
        
        $data = [];
        foreach ($allWeapons as $index => $weapon) {
            $data[] = [
                'player_id' => $player->id,
                'weapon_id' => $weapon->id,
                'level'     => 1,
                // Món đầu tiên (index 0) được sở hữu và trang bị ngay lập tức
                
                'is_equipped' => ($index === 0),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }
        // Chèn toàn bộ danh sách vũ khí vào hòm đồ ở trạng thái chờ
        DB::table('player_weapons')->insert($data);
    });

    // CƠ CHẾ 2: Giữ nguyên logic tính Level & EXP khi Saving
    static::saving(function ($player) {
        while (true) {
            $currentLevel = $player->level;
            $expNeeded = 0;

            if ($currentLevel < 10) {
                $expNeeded = 100 + ($currentLevel - 1) * 50;
            } elseif ($currentLevel < 50) {
                $baseAt10 = 500; 
                $expNeeded = (int)round($baseAt10 * pow(1.1, $currentLevel - 9));
            } else {
                $baseAt50 = (int)round(500 * pow(1.1, 40)); 
                $expNeeded = (int)round($baseAt50 * pow(1.05, $currentLevel - 49));
            }

            if ($player->exp >= $expNeeded) {
                $player->exp -= $expNeeded;
                $player->level += 1;
            } else {
                break;
            }
        }

        // Sau khi tính xong Level, chạy cơ chế mở khóa mỗi 100 cấp
        // $player->checkAndUnlockWeaponByMilestone();
    });
}


/**
 * CƠ CHẾ 3: Mở khóa vũ khí dựa trên mốc Level (100, 200, 300...)
 */
public function checkAndUnlockWeaponByMilestone() 
{
    // // 1. Tính toán xem với Level hiện tại, Player được phép sở hữu tối đa bao nhiêu món
    // // Level 1-99: 1 món (index 0)
    // // Level 100-199: 2 món (index 0, 1)
    // // Công thức: số lượng = floor(level / 100) + 1
    // $maxWeaponsCanOwn = floor($this->level / 100) + 1;

    // // 2. Lấy danh sách ID của N vũ khí đầu tiên theo thứ tự trong bảng player_weapons
    // $weaponIdsToUnlock = \DB::table('player_weapons')
    //     ->where('player_id', $this->id)
    //     ->orderBy('id', 'asc') // Hoặc orderBy('weapon_id') tùy bạn sắp xếp
    //     ->limit($maxWeaponsCanOwn)
    //     ->pluck('id');

    // // 3. Cập nhật is_owned = true cho các món đó bằng 1 câu lệnh SQL duy nhất
    // \DB::table('player_weapons')
    //     ->whereIn('id', $weaponIdsToUnlock)
    //     ->where('is_owned', false)
    //     ->update(['is_owned' => true, 'updated_at' => now()]);
}

public function checkAndUnlockWeapon() {
    // Tìm tất cả vũ khí có yêu cầu level thấp hơn hoặc bằng level hiện tại của Hero
    $availableWeapons = \App\Models\Weapon::where('required_hero_level', '<=', $this->level)->get();

    foreach ($availableWeapons as $weapon) {
        // Kiểm tra xem trong túi đồ (player_weapons) đã có món này chưa
        $alreadyHas = \App\Models\PlayerWeapon::where('player_id', $this->id)
                            ->where('weapon_id', $weapon->id)
                            ->exists();

        // Nếu chưa có thì tặng ngay
        if (!$alreadyHas) {
            \App\Models\PlayerWeapon::create([
                'player_id' => $this->id,
                'weapon_id' => $weapon->id,
                'level' => 1,
                'is_equipped' => false // Vũ khí mới mở thì không nên tự trang bị đè lên cái cũ
            ]);
        }
    }
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

/**
     * CƠ CHẾ HIỂN THỊ: Trả về thông tin sát thương và kỹ năng của riêng chú Pet đang trang bị
     */
    public function getPetSkillsAttribute()
    {
        // Sử dụng eager loading 'pet' để tối ưu truy vấn
        $activePetRelation = $this->activePet()->with('pet')->first();
        
        if (!$activePetRelation || !$activePetRelation->pet) {
            return [
                'pet_name'     => 'Chưa có Pet',
                'main_dame'    => 0,
                'skill_1_dame' => 0,
                'skill_2_dame' => 0,
                'skill_3_dame' => 0,
                'total_power'  => 0
            ];
        }

        $lv = $activePetRelation->level ?? 1;
        // Linh hoạt kiểm tra tên cột trong DB của bạn (base_attack hoặc base_dps)
        $baseAtk = $activePetRelation->pet->base_attack ?? ($activePetRelation->pet->base_dps ?? 50);

        // 1. Dame chính: Tăng 10% mỗi cấp (từ lv 1)
        $mainDame = $baseAtk * pow(1.1, $lv - 1);

        // 2. Skill 1: Mở cấp 30
        $skill1 = 0;
        if ($lv >= 30) {
            $mainAt29 = $baseAtk * pow(1.1, 29 - 1);
            $baseSkill1 = $mainAt29 * 1.5;
            $skill1 = $baseSkill1 * pow(1.1, $lv - 30);
        }

        // 3. Skill 2: Mở cấp 60
        $skill2 = 0;
        if ($lv >= 60) {
            $mainAt29ForS2 = $baseAtk * pow(1.1, 29 - 1);
            $skill1At59 = ($mainAt29ForS2 * 1.5) * pow(1.1, 59 - 30);
            $baseSkill2 = $skill1At59 * 1.5;
            $skill2 = $baseSkill2 * pow(1.1, $lv - 60);
        }

        // 4. Skill 3: Mở cấp 90
        $skill3 = 0;
        if ($lv >= 90) {
            $mainAt29ForS3 = $baseAtk * pow(1.1, 29 - 1);
            $skill1At59ForS3 = ($mainAt29ForS3 * 1.5) * pow(1.1, 59 - 30);
            $skill2At89 = ($skill1At59ForS3 * 1.5) * pow(1.1, 89 - 60);
            $baseSkill3 = $skill2At89 * 1.5;
            $skill3 = $baseSkill3 * pow(1.1, $lv - 90);
        }

        $totalPetPower = $mainDame + $skill1 + $skill2 + $skill3;

        return [
            'pet_name'     => $activePetRelation->pet->name,
            'level'        => $lv,
            'main_dame'    => (int)round($mainDame),
            'skill_1_dame' => (int)round($skill1),
            'skill_2_dame' => (int)round($skill2),
            'skill_3_dame' => (int)round($skill3),
            'total_power'  => (int)round($totalPetPower)
        ];
    }




public function getTotalCombatDamage()
    {
        // Lấy damage vũ khí đang đeo
        $weaponDame = $this->weapons()->where('is_equipped', true)->sum('base_damage') ?? 0; // Thay 'damage' bằng tên cột chính xác trong DB của bạn
        
        // Lấy damage từ Pet (thông qua accessor pet_skills hoặc quan hệ)
        $petSkillsData = $this->pet_skills; 
        $petDame = isset($petSkillsData['total_power']) ? $petSkillsData['total_power'] : 0;
        
        return (int)($this->current_attack + $weaponDame + $petDame);
    }

    // 2. Công thức tính Lực chiến tổng (Tổng hợp mọi chỉ số)
    public function calculateTotalPower() 
    {
        $combatDamage = $this->getTotalCombatDamage(); 
        
        // Các chỉ số phụ
        $hp = $this->base_hp;
        $critRate = 10 + ($this->upgraded_crit_rate_lv * 2);
        $critDamage = 100 + ($this->upgraded_crit_damage_lv * 5);
        $speed = $this->upgraded_speed_lv * 0.2;

        // Công thức tổng quát
        $total = $combatDamage 
                 + ($hp / 10) 
                 + ($critRate * 50) 
                 + ($critDamage * 20) 
                 + ($speed * 100);

        return (int)round($total);
    }
}