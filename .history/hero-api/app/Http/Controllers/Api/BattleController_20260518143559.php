<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Monster;
use App\Models\Boss; // Giả định bạn đã tạo Model Boss
use App\Models\Player;
use Illuminate\Http\Request;

class BattleController extends Controller
{
    /**
     * API 1: Triệu hồi quái hiện tại người chơi phải đối mặt
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
     * API 2: Xử lý khi Hero đánh thắng quái
     * Hero nhận vàng, exp và cập nhật tầng/kill_count
     */
    public function defeat(Request $request)
{
    $player = $request->user();
    $floor = $player->current_floor;
    $isBossFloor = ($floor % 10 == 0);

    // 1. Lấy thông tin quái vừa đánh để tính thưởng
    // (Trong thực tế, bạn nên gửi ID quái lên hoặc lưu trạng thái trận đấu để tránh hack)
    $monster = Monster::where('min_floor', '<=', $floor)->inRandomOrder()->first();
    $stats = $monster->getStatsForFloor($floor);

    // 2. Cộng phần thưởng
    $player->gold += $stats['rewards']['gold'];
    $player->exp += $stats['rewards']['exp'];

    // 3. Tăng tiến trình quái vật
    $player->kill_count += 1;
    $requiredKills = $isBossFloor ? 1 : 5;

    $hasLevelUpFloor = false;

    if ($player->kill_count >= $requiredKills) {
        // --- LOGIC LÊN TẦNG MỚI ---
        $player->current_floor += 1;
        $player->kill_count = 0;
        $hasLevelUpFloor = true;

        // HỒI ĐẦY MÁU CHO HERO
        // Giả sử bạn có cột 'current_hp' và 'base_hp' (hoặc max_hp) trong bảng players
        $player->current_hp = $player->base_hp; 

        // Cập nhật kỷ lục tầng
        if ($player->current_floor > $player->highest_floor) {
            $player->highest_floor = $player->current_floor;
        }
    }

    $player->save();

    return response()->json([
        'success' => true,
        'message' => $hasLevelUpFloor ? "Vượt tầng! Hero đã được hồi đầy máu." : "Tiêu diệt quái vật!",
        'rewards' => $stats['rewards'],
        'player_status' => [
            'hp' => $player->current_hp,
            'gold' => $player->gold,
            'floor' => $player->current_floor,
            'kill_count' => $player->kill_count
        ]
    ]);
}

    // Hàm phụ tính chỉ số Boss
    private function scaleBossStats($boss, $floor) {
        $growth = pow(1.2, $floor - 1); // Boss tăng trưởng mạnh hơn (20%)
        return [
            'name' => "BOSS: " . $boss->name,
            'hp' => round($boss->base_hp * $growth),
            'atk' => round($boss->base_attack * $growth),
            'skills' => $boss->skills, // Skill kèm cooldown
            'rewards' => [
                'gold' => round($boss->base_gold * $growth),
                'exp' => round($boss->base_exp * $growth),
            ]
        ];
    }

    // Trong BattleController.php

public function getBattleStatus(Request $request)
{
    $player = $request->user();
    $floor = $player->current_floor;
    $isBossFloor = ($floor % 10 == 0);

    // 1. Tính tổng Damage
    $totalDamage = $player->getTotalDamage();

    // 2. Lấy thông tin quái/Boss hiện tại
    if ($isBossFloor) {
        $boss = \App\Models\Boss::inRandomOrder()->first();
        $monsterData = $this->scaleBossStats($boss, $floor);
    } else {
        $monster = \App\Models\Monster::where('min_floor', '<=', $floor)->inRandomOrder()->first();
        $monsterData = $monster->getStatsForFloor($floor);
    }

    // 3. Tính thời gian dự kiến tiêu diệt (Time to Kill)
    // Giả sử Dame này gây ra mỗi giây
    $timeToKill = $monsterData['hp'] / max(1, $totalDamage);

    return response()->json([
        'success' => true,
        'hero_stats' => [
            'total_damage_per_second' => $totalDamage,
            'current_hp' => $player->current_hp,
            'base_hp' => $player->base_hp,
        ],
        'monster_stats' => [
            'name' => $monsterData['name'],
            'current_hp' => $monsterData['hp'],
            'max_hp' => $monsterData['hp'],
            'atk' => $monsterData['atk'],
        ],
        'battle_config' => [
            'floor' => $floor,
            'is_boss' => $isBossFloor,
            'estimated_seconds' => round($timeToKill, 2)
        ]
    ]);
}
}