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
     * API 1: Triệu hồi quái ngẫu nhiên (Tự động tính chỉ số +10% mỗi tầng)
     */
    public function spawn(Request $request)
    {
        $player = $request->user();
        $floor = max(1, $player->current_floor);
        $killCount = $player->kill_count; // Đang từ 0 đến 4

        // 1. Lấy ngẫu nhiên 1 con quái trong DB (Không quan tâm tầng nào vì chỉ số gốc như nhau)
        $monster = Monster::inRandomOrder()->first();

        if (!$monster) {
            return response()->json([
                'success' => false, 
                'message' => 'Database hoàn toàn không có dữ liệu Quái!'
            ], 404);
        }

        // 2. Tự động tính toán chỉ số tăng công thức: Chỉ số gốc * (1.10 ^ (floor - 1))
        $growth = pow(1.10, $floor - 1); 

        $monsterData = [
            'id' => $monster->id,
            'name' => $monster->name,
            'prefab_name' => $monster->prefab_name,
            'hp' => round($monster->base_hp * $growth),
            'atk' => round($monster->base_atk * $growth),
            'rewards' => [
                'gold' => round($monster->base_gold * $growth),
                'exp' => round($monster->base_exp * $growth),
            ]
        ];

        return response()->json([
            'success' => true,
            'battle_info' => [
                'current_floor' => $floor,
                'monster_index' => $killCount + 1, // Con thứ mấy (1 -> 5)
                'total_monsters' => 5 // Cố định mỗi tầng có 5 con
            ],
            'monster_data' => $monsterData
        ]);
    }

    /**
     * API 2: Xử lý khi Hero đánh thắng 1 con quái
     */
    public function defeat(Request $request)
    {
        $player = $request->user();
        $floor = max(1, $player->current_floor);

        // 1. Lấy ID quái từ client gửi lên để tính thưởng chính xác cho con quái đó
        $monsterId = $request->input('monster_id');
        $monster = Monster::find($monsterId) ?: Monster::inRandomOrder()->first();

        if (!$monster) {
            return response()->json(['success' => false, 'message' => 'Monster not found'], 404);
        }

        // 2. Tính toán lượng thưởng tăng 10% theo tầng hiện tại
        $growth = pow(1.10, $floor - 1);
        $rewardGold = round($monster->base_gold * $growth);
        $rewardExp = round($monster->base_exp * $growth);

        // Cộng thưởng cho Player
        $player->gold += $rewardGold;
        $player->exp += $rewardExp;

        // 3. Tăng số lượng quái đã giết trong tầng
        $player->kill_count += 1;
        $hasLevelUpFloor = false;

        // Nếu đã giết đủ 5 con quái -> Lên tầng mới, reset đếm quái về 0
        if ($player->kill_count >= 5) {
            $player->current_floor += 1;
            $player->kill_count = 0;
            $hasLevelUpFloor = true;

            // Hồi đầy máu cho Hero khi qua màn mới
            $player->current_hp = $player->base_hp; 

            if ($player->current_floor > $player->highest_floor) {
                $player->highest_floor = $player->current_floor;
            }
        }

        $player->save();

        return response()->json([
            'success' => true,
            'message' => $hasLevelUpFloor ? "Vượt tầng thành công! Tiến vào tầng " . $player->current_floor : "Tiêu diệt quái vật!",
            'has_level_up_floor' => $hasLevelUpFloor, // Unity dựa vào biến này để biết khi nào chuyển cảnh/tầng
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
}