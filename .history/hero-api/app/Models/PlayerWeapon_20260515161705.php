<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerWeapon extends Model
{
    protected $fillable = ['player_id', 'weapon_id', 'level', 'is_equipped'];

    public function player() {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function weapon() {
        return $this->belongsTo(Weapon::class, 'weapon_id');
    }

    // TUYỆT ĐỐI KHÔNG thêm 'is_owned' vào đây
    protected $appends = ['current_damage', 'upgrade_cost'];

    // XÓA BỎ HOÀN TOÀN hàm getIsOwnedAttribute() đã viết trước đó

    public function getUpgradeCostAttribute() {
        return (int)round(200 * pow(1.1, $this->level - 1));
    }

    public function getCurrentDamageAttribute() {
        $baseAtk = $this->weapon->base_attack ?? 0; 
        return (int)($baseAtk * pow(1.1, $this->level - 1));
    }
}
    // QUAN TRỌNG: Thêm quan hệ với Player để lấy được Level của người chơi
    // public function player() {
    //     return $this->belongsTo(Player::class, 'player_id');
    // }

    public function weapon() {
        return $this->belongsTo(Weapon::class, 'weapon_id');
    }

    // Thêm is_owned vào appends để có thể truy cập từ Controller/JSON
    protected $appends = ['current_damage', 'upgrade_cost'];

    // Accessor tính is_owned (Điều kiện mở khóa vũ khí)
    public function getIsOwnedAttribute() {
        // 1. Lấy danh sách ID vũ khí để xác định thứ tự (Index)
        $allWeapons = \App\Models\Weapon::orderBy('id', 'asc')->pluck('id')->toArray();
        $index = array_search($this->weapon_id, $allWeapons);
        
        // 2. Tính Level cần thiết: Món đầu (index 0) cần lv 1, các món sau cần index * 100
        $requiredLv = ($index === 0) ? 1 : ($index * 100);

        // 3. So sánh với level của Player
        // Dùng $this->player để lấy thông tin người sở hữu món đồ này
        if (!$this->player) return false;
        
        return $this->player->level >= $requiredLv;
    }

    public function getUpgradeCostAttribute() {
        return (int)round(200 * pow(1.1, $this->level - 1));
    }

    public function getCurrentDamageAttribute() {
        // Sử dụng dấu ?? để tránh lỗi nếu weapon chưa được load
        $baseAtk = $this->weapon->base_attack ?? 0; 
        return (int)($baseAtk * pow(1.1, $this->level - 1));
    }

}