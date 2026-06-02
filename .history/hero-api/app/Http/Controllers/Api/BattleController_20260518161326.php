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
    
    // 1. Client gửi lên kết quả trận đấu: 'WIN' hoặc 'LOSE'
    $result = $request->input('result', 'WIN'); // Mặc định là WIN nếu không truyền

    // 2. Lấy ID quái để tính thưởng
    $monsterId = $request->input('monster_id');
    $monster = Monster::find($monsterId) ?: Monster::inRandomOrder()->first();

    if (!$monster) {
        return response()->json(['success' => false, 'message' => 'Monster not found'], 200);
    }

    // Tính toán lượng thưởng gốc tăng 10% theo tầng hiện tại
    $growth = pow(1.10, $floor - 1);
    $rewardGold = (int)round($monster->base_gold * $growth);
    $rewardExp = (int)round($monster->base_exp * $growth);

    $hasLevelUpFloor = false;
    $hasStatusChanged = "";

    // 3. PHÂN CHIA LOGIC THẮNG / THUA
    if (strtoupper($result) === 'WIN') {
        // --- TRƯỜNG HỢP THẮNG QUÁI ---
        // Cộng thưởng đầy đủ
        $player->gold += $rewardGold;
        $player->exp += $rewardExp;

        // Tăng số lượng quái đã giết trong tầng
        $player->kill_count += 1;

        // Nếu đã giết đủ 5 con quái -> Lên tầng mới
        if ($player->kill_count >= 5) {
            $player->current_floor += 1;
            $player->kill_count = 0;
            $hasLevelUpFloor = true;
            $hasStatusChanged = "UP"; // Báo cho Unity biết là lên tầng

            // Hồi đầy máu cho Hero khi qua màn mới
            $player->base_hp = $player->base_hp; 

            if ($player->current_floor > $player->highest_floor) {
                $player->highest_floor = $player->current_floor;
            }
        } else {
            $hasStatusChanged = "STAY"; // Vẫn ở lại tầng cũ đánh tiếp con tiếp theo
        }

    } else {
        // --- TRƯỜNG HỢP THUA QUÁI ---
        // Khi thua, lùi lại 1 tầng (nếu đang ở tầng > 1) để lặp lại vòng lặp cày tiền
        if ($player->current_floor > 1) {
            $player->current_floor -= 1;
            $hasStatusChanged = "DOWN"; // Báo cho Unity biết là bị lùi tầng
        } else {
            $hasStatusChanged = "STAY"; // Tầng 1 thì ở lại tầng 1
        }

        // Reset số quái đã giết về 0 để cày lại từ đầu ở tầng dưới
        $player->kill_count = 0;

        // Hồi đầy máu để chuẩn bị cho vòng lặp treo máy cày tiền ở tầng thấp hơn
        $player->ccbasse_hp_hp = $player->base_hp;

        // Thua thì không nhận được thưởng (hoặc bạn có thể cho 50% thưởng nếu muốn)
        $rewardGold = 0;
        $rewardExp = 0;
    }

    $player->save();

    return response()->json([
        'success' => true,
        'message' => $hasStatusChanged === "UP" ? "Vượt tầng thành công!" : ($hasStatusChanged === "DOWN" ? "Hero thất bại! Lùi tầng để rèn luyện." : "Tiếp tục chiến đấu!"),
        'status_floor' => $hasStatusChanged, // Trả về "UP", "DOWN", hoặc "STAY" để Unity dễ xử lý đổi cảnh
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