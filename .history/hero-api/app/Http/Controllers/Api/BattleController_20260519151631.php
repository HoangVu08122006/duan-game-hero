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
     * API 1: Triệu hồi quái ngẫu nhiên (Tầng x10 xuất hiện Boss + Cơ chế Skill)
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
                'prefab_name' => $boss->prefab_name ?? 'boss_default',
                'hp' => round($boss->base_hp * $growth * $bossHpMultiplier),
                'atk' => $normalAtk, // Sát thương đánh thường
                'skill_config' => [
                    'has_skill' => true,
                    'skill_name' => $boss->skills ?? 'Tuyệt Chiêu Đại Boss',
                    'skill_atk' => $skillAtk,       // Sát thương của chiêu thức (Mạnh hơn đánh thường)
                    'cooldown_seconds' => 3.0       // Cứ mỗi 3 giây tung chiêu 1 lần
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
                'prefab_name' => $monster->prefab_name,
                'hp' => round($monster->base_hp * $growth),
                'atk' => round($monster->base_atk * $growth),
                'skill_config' => [
                    'has_skill' => false // Quái thường không có skill
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
     * API 2: Xử lý khi Hero đánh thắng/thua 1 thực thể (Quái hoặc Boss)
     */
    public function defeat(Request $request)
    {
        $player = $request->user();
        $floor = max(1, $player->current_floor);
        
        $result = $request->input('result', 'WIN'); 
        $enemyId = $request->input('enemy_id'); // Thay monster_id bằng enemy_id cho tổng quan
        $isBoss = $request->input('is_boss', false); // Client truyền lên xem trận này là đánh Boss hay Quái

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
                // Nếu thắng tầng BOSS: Lên thẳng tầng tiếp theo luôn không cần đếm kill_count
                $player->current_floor += 1;
                $player->kill_count = 0; 
                $player->current_hp = $player->base_hp; 
                $hasStatusChanged = "AUTO_UP";
            } else {
                // Nếu thắng QUÁI THƯỜNG: Tăng đếm mạng
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
            // Nếu thua ở tầng Boss hoặc tầng thường thì đều lùi 1 tầng
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

        // Thiết lập message thông báo
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
     * API 3: Lấy trạng thái hiển thị DPS và tiến trình (Nếu có dùng)
     */
    public function getBattleStatus(Request $request)
    {
        $player = $request->user();
        $floor = max(1, $player->current_floor);
        $totalDamage = max(1, $player->getTotalDamage());

        // Lấy đại 1 con quái để giả lập chỉ số hiển thị
        $monster = Monster::inRandomOrder()->first();
        if (!$monster) return response()->json(['success' => false, 'message' => 'No monster data'], 404);

        $growth = pow(1.10, $floor - 1);
        $monsterHp = round($monster->base_hp * $growth);
        $timeToKill = $monsterHp / $totalDamage;

        return response()->json([
            'success' => true,
            'hero_stats' => [
                'total_damage_per_second' => $totalDamage,
                'current_hp' => $player->current_hp,
                'base_hp' => $player->base_hp,
            ],
            'monster_stats' => [
                'name' => $monster->name,
                'current_hp' => $monsterHp,
                'max_hp' => $monsterHp,
                'atk' => round($monster->base_atk * $growth),
            ],
            'battle_config' => [
                'floor' => $floor,
                'monster_index' => $player->kill_count + 1,
                'total_monsters' => 5,
                'estimated_seconds' => round($timeToKill, 2)
            ]
        ]);
    }

    /**
 * API: Người chơi chủ động bấm nút để vượt qua vòng lặp, leo lên lại tầng cao
 */
public function retryHighestFloor(Request $request)
{
    $player = $request->user();

    // Nếu người chơi chưa từng bị lùi tầng (đang ở tầng cao nhất rồi) thì không cần bấm
    if ($player->current_floor >= $player->highest_floor) {
        return response()->json([
            'success' => false,
            'message' => 'Bạn đã ở tầng cao nhất hiện tại!'
        ], 400);
    }

    // Đưa người chơi quay lại tầng cao nhất (tầng mà họ đã bị thua trước đó)
    $player->current_floor = $player->highest_floor;
    $player->kill_count = 0; // Đặt lại về 0 để bắt đầu tính 5 con ở tầng cao
    $player->current_hp = $player->base_hp; // Bơm đầy máu để phục thù
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