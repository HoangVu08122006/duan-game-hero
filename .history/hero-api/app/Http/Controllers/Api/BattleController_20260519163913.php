<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Monster;
use App\Models\Boss; 
use App\Models\Player;
use Illuminate\Http\Request;

class BattleController extends Controller
{
    /**
     * API 1: Triệu hồi quái ngẫu nhiên (Đã đồng bộ prefab_name cho Boss)
     */
    public function spawn(Request $request)
    {
        $player = $request->user();
        $floor = max(1, $player->current_floor);
        $killCount = $player->kill_count;

        // Kiểm tra tầng Boss (10, 20, 30...)
        $isBossFloor = ($floor % 10 === 0);
        
        // Công thức tăng trưởng chung theo tầng (+10% mỗi tầng)
        $growth = pow(1.10, $floor - 1); 

        if ($isBossFloor) {
            // --- LOGIC TRIỆU HỒI BOSS NGẪU NHIÊN ---
            $boss = Boss::inRandomOrder()->first();

            if (!$boss) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Vui lòng thêm dữ liệu vào bảng bosses!'
                ], 404);
            }

            // Hệ số sức mạnh của Boss so với quái thường
            $bossHpMultiplier = 3.0;     // Máu gấp 3
            $bossAtkMultiplier = 2.0;    // Tấn công thường gấp 2
            $bossRewardMultiplier = 5.0; // Thưởng gấp 5

            // Tính sát thương đòn đánh thường của Boss sau khi tăng trưởng theo tầng
            $normalAtk = round($boss->base_attack * $growth * $bossAtkMultiplier);

            // Cấu hình SKILL của Boss: Mạnh gấp 1.5 lần đòn đánh thường, hồi chiêu mỗi 3 giây
            $skillDamageMultiplier = 1.5; 
            $skillAtk = round($normalAtk * $skillDamageMultiplier);

            $enemyData = [
                'id' => $boss->id,
                'is_boss' => true,
                'name' => "[BOSS TẦNG " . $floor . "] " . $boss->name,
                'prefab_name' => $boss->prefab_name ?? 'boss_default', // Trả về prefab_name cho Front-end dẫn ảnh
                'hp' => round($boss->base_hp * $growth * $bossHpMultiplier),
                'atk' => $normalAtk, 
                'skill_config' => [
                    'has_skill' => true,
                    'skill_name' => $boss->skill_name ?? $boss->skill ?? 'Tuyệt Chiêu Đại Boss', // Sửa lại lỗi gọi sai trường skills
                    'skill_atk' => $skillAtk,       
                    'cooldown_seconds' => 3.0       
                ],
                'rewards' => [
                    'gold' => round($boss->base_gold * $growth * $bossRewardMultiplier),
                    'exp' => round($boss->base_exp * $growth * $bossRewardMultiplier),
                ]
            ];
        } else {
            // --- LOGIC TRIỆU HỒI QUÁI THƯỜNG ---
            $monster = Monster::inRandomOrder()->first();

            if (!$monster) {
                return response()->json(['success' => false, 'message' => 'Thiếu dữ liệu Quái!'], 404);
            }

            $enemyData = [
                'id' => $monster->id,
                'is_boss' => false,
                'name' => $monster->name,
                'prefab_name' => $monster->prefab_name ?? 'monster_default',
                'hp' => round($monster->base_hp * $growth),
                'atk' => round($monster->base_atk * $growth),
                'skill_config' => [
                    'has_skill' => false 
                ],
                'rewards' => [
                    'gold' => round($monster->base_gold * $growth),
                    'exp' => round($monster->base_exp * $growth),
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'battle_info' => [
                'current_floor' => $floor,
                'is_boss_floor' => $isBossFloor,
                'monster_index' => $isBossFloor ? 1 : ($killCount + 1),
                'total_monsters' => $isBossFloor ? 1 : 5 
            ],
            'monster_data' => $enemyData
        ]);
    }

    /**
     * API 2: Xử lý khi Hero đánh thắng/thua 1 thực thể
     */
    public function defeat(Request $request)
    {
        $player = $request->user();
        $floor = max(1, $player->current_floor);
        
        $result = $request->input('result', 'WIN'); 
        $enemyId = $request->input('enemy_id'); 
        $isBoss = $request->input('is_boss', false); 

        $growth = pow(1.10, $floor - 1);
        $rewardGold = 0;
        $rewardExp = 0;

        // 1. KIỂM TRA ĐỐI THỦ LÀ BOSS HAY QUÁI ĐỂ TÍNH THƯỞNG GỐC
        if ($isBoss || ($floor % 10 === 0)) {
            $boss = Boss::find($enemyId) ?: Boss::inRandomOrder()->first();
            if (!$boss) {
                return response()->json(['success' => false, 'message' => 'Boss data not found'], 200);
            }
            $bossRewardMultiplier = 4.0;
            $rewardGold = (int)round($boss->base_gold * $growth * $bossRewardMultiplier);
            $rewardExp = (int)round($boss->base_exp * $growth * $bossRewardMultiplier);
            $isBossFloor = true;
        } else {
            $monster = Monster::find($enemyId) ?: Monster::inRandomOrder()->first();
            if (!$monster) {
                return response()->json(['success' => false, 'message' => 'Monster data not found'], 200);
            }
            $rewardGold = (int)round($monster->base_gold * $growth);
            $rewardExp = (int)round($monster->base_exp * $growth);
            $isBossFloor = false;
        }

        $hasStatusChanged = "";

        // 2. PHÂN CHIA LOGIC THẮNG / THUA
        if (strtoupper($result) === 'WIN') {
            $player->gold += $rewardGold;
            $player->exp += $rewardExp;

            if ($isBossFloor) {
                $player->current_floor += 1;
                $player->kill_count = 0; 
                $player->current_hp = $player->base_hp; 
                $hasStatusChanged = "AUTO_UP";
            } else {
                $player->kill_count += 1;

                if ($player->kill_count >= 5) {
                    $player->current_floor += 1;
                    $player->kill_count = 0; 
                    $player->current_hp = $player->base_hp; 
                    $hasStatusChanged = "AUTO_UP";
                } else {
                    $hasStatusChanged = "STAY"; 
                }
            }

            if ($player->current_floor > $player->highest_floor) {
                $player->highest_floor = $player->current_floor;
            }

        } else {
            // --- TRƯỜNG HỢP THUA TRẬN ---
            if ($player->current_floor > 1) {
                $player->current_floor -= 1;
                $hasStatusChanged = "LOCK_DOWN"; 
            } else {
                $hasStatusChanged = "STAY"; 
            }

            $player->kill_count = 0;
            $player->current_hp = $player->base_hp;
            $rewardGold = 0;
            $rewardExp = 0;
        }

        $player->save();

        $message = "Tiếp tục chiến đấu!";
        if ($hasStatusChanged === "AUTO_UP") {
            $message = $isBossFloor ? "Đã tiêu diệt Đại Boss! Tự động tiến vào tầng " . $player->current_floor 
                                    : "Thắng lợi liên tiếp! Tự động tiến vào tầng " . $player->current_floor;
        } elseif ($hasStatusChanged === "LOCK_DOWN") {
            $message = "Hero thất bại trước kẻ địch! Đang tự động lùi tầng cày lại.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'status_floor' => $hasStatusChanged, 
            'rewards' => [
                'gold' => $rewardGold,
                'exp' => $rewardExp
            ],
            'player_status' => [
                'hp' => $player->current_hp,
                'gold' => $player->gold,
                'floor' => $player->current_floor,
                'kill_count' => $player->kill_count
            ]
        ]);
    }

    /**
     * API 3: Lấy trạng thái hiển thị DPS (Bổ sung thêm thông tin prefab_name của Boss)
     */
    public function getBattleStatus(Request $request)
    {
        $player = $request->user();
        $floor = max(1, $player->current_floor);
        $heroDps = max(1, $player->getTotalDamage()); 

        $isBossFloor = ($floor % 10 === 0);
        $growth = pow(1.10, $floor - 1);
        $prefabName = 'default';

        if ($isBossFloor) {
            $boss = Boss::inRandomOrder()->first();
            if (!$boss) return response()->json(['success' => false], 404);

            $normalAtk = round($boss->base_attack * $growth * 2.0);
            $skillAtk = round($normalAtk * 1.5);
            $monsterHp = round($boss->base_hp * $growth * 3.0);
            $monsterName = "[BOSS] " . $boss->name;
            $prefabName = $boss->prefab_name ?? 'boss_default'; // Thêm dữ liệu để đồng bộ ảnh
            
            $bossDpsValue = $normalAtk + ($skillAtk / 3); 
        } else {
            $monster = Monster::inRandomOrder()->first();
            if (!$monster) return response()->json(['success' => false], 404);

            $monsterHp = round($monster->base_hp * $growth);
            $normalAtk = round($monster->base_atk * $growth);
            $monsterName = $monster->name;
            $prefabName = $monster->prefab_name ?? 'monster_default';
            $bossDpsValue = $normalAtk; 
        }

        $timeToKill = $monsterHp / $heroDps;

        return response()->json([
            'success' => true,
            'hero_stats' => [
                'total_damage_per_second' => $heroDps,
                'current_hp' => $player->current_hp,
                'base_hp' => $player->base_hp,
            ],
            'monster_stats' => [
                'name' => $monsterName,
                'prefab_name' => $prefabName, // Front-end nhận biến này để render ảnh chuẩn xác
                'is_boss' => $isBossFloor,
                'current_hp' => $monsterHp,
                'max_hp' => $monsterHp,
                'atk' => $normalAtk,
                'avg_dps_received' => round($bossDpsValue, 2) 
            ],
            'battle_config' => [
                'floor' => $floor,
                'monster_index' => $isBossFloor ? 1 : ($player->kill_count + 1),
                'total_monsters' => $isBossFloor ? 1 : 5,
                'estimated_seconds' => round($timeToKill, 2)
            ]
        ]);
    }

    /**
     * API 4: Thử thách lại tầng cao nhất
     */
    public function retryHighestFloor(Request $request)
    {
        $player = $request->user();

        if ($player->current_floor >= $player->highest_floor) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã ở tầng cao nhất hiện tại!'
            ], 400);
        }

        $player->current_floor = $player->highest_floor;
        $player->kill_count = 0; 
        $player->current_hp = $player->base_hp; 
        $player->save();

        return response()->json([
            'success' => true,
            'message' => "Quay trở lại thử thách tầng cao nhất: Tầng " . $player->current_floor,
            'player_status' => [
                'hp' => $player->current_hp,
                'gold' => $player->gold,
                'floor' => $player->current_floor,
                'kill_count' => $player->kill_count,
                'highest_floor' => $player->highest_floor
            ]
        ]);
    }
}