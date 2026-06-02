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
     * API 1: Triệu hồi quái ngẫu nhiên (Tự động tính chỉ số +10% mỗi tầng, tầng x10 xuất hiện Boss)
     */
    public function spawn(Request $request)
    {
        $player = $request->user();
        $floor = max(1, $player->current_floor);
        $killCount = $player->kill_count;

        // Kiểm tra xem có phải tầng Boss không (Cứ 10 tầng 1 lần)
        $isBossFloor = ($floor % 10 === 0);
        
        // Công thức tăng trưởng chung theo tầng (+10% mỗi tầng)
        $growth = pow(1.10, $floor - 1); 

        if ($isBossFloor) {
            // --- LOGIC TRIỆU HỒI BOSS ---
            // Lấy ngẫu nhiên 1 Boss trong bảng bosses
            $boss = Boss::inRandomOrder()->first();

            if (!$boss) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Tầng Boss nhưng Database hoàn toàn không có dữ liệu Boss! Hãy thêm dữ liệu vào bảng bosses.'
                ], 404);
            }

            // Hệ số vượt trội của Boss so với quái thường (Ví dụ: mạnh gấp 2.5 lần, thưởng gấp 4 lần)
            $bossStatMultiplier = 2.5; 
            $bossRewardMultiplier = 4.0;

            $enemyData = [
                'id' => $boss->id,
                'is_boss' => true,
                'name' => "[BOSS] " . $boss->name,
                'prefab_name' => $boss->prefab_name ?? 'boss_default', // Bạn có thể thêm trường này vào DB nếu cần
                'hp' => round($boss->base_hp * $growth * $bossStatMultiplier),
                'atk' => round($boss->base_attack * $growth * $bossStatMultiplier), // map với thuộc tính base_attack trong ảnh của bạn
                'rewards' => [
                    'gold' => round($boss->base_gold * $growth * $bossRewardMultiplier),
                    'exp' => round($boss->base_exp * $growth * $bossRewardMultiplier),
                ]
            ];
        } else {
            // --- LOGIC TRIỆU HỒI QUÁI THƯỜNG ---
            $monster = Monster::inRandomOrder()->first();

            if (!$monster) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Database hoàn toàn không có dữ liệu Quái!'
                ], 404);
            }

            $enemyData = [
                'id' => $monster->id,
                'is_boss' => false,
                'name' => $monster->name,
                'prefab_name' => $monster->prefab_name,
                'hp' => round($monster->base_hp * $growth),
                'atk' => round($monster->base_atk * $growth),
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
                'monster_index' => $isBossFloor ? 1 : ($killCount + 1), // Tầng Boss thường chỉ có 1 con Boss duy nhất
                'total_monsters' => $isBossFloor ? 1 : 5 
            ],
            'monster_data' => $enemyData // Client Unity đọc chung cục này để hiển thị UI
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
    $result = $request->input('result', 'WIN'); 

    // 2. Lấy ID quái để tính thưởng
    $monsterId = $request->input('monster_id');
    $monster = Monster::find($monsterId) ?: Monster::inRandomOrder()->first();

    if (!$monster) {
        return response()->json(['success' => false, 'message' => 'Monster not found'], 200);
    }

    // Tính toán lượng thưởng tăng 10% theo tầng hiện tại
    $growth = pow(1.10, $floor - 1);
    $rewardGold = (int)round($monster->base_gold * $growth);
    $rewardExp = (int)round($monster->base_exp * $growth);

    $hasStatusChanged = "";

    // 3. PHÂN CHIA LOGIC THẮNG / THUA
    if (strtoupper($result) === 'WIN') {
        // --- TRƯỜNG HỢP THẮNG QUÁI ---
        $player->gold += $rewardGold;
        $player->exp += $rewardExp;

        // Tăng số lượng quái đã giết trong tầng
        $player->kill_count += 1;

        // Nếu đã giết đủ 5 con quái -> TỰ ĐỘNG LÊN TẦNG TIẾP THEO
        if ($player->kill_count >= 5) {
            $player->current_floor += 1;
            $player->kill_count = 0; // Reset đếm quái cho tầng mới
            $player->current_hp = $player->base_hp; // Hồi đầy máu sang tầng mới
            $hasStatusChanged = "AUTO_UP"; // Báo cho Unity biết là tự động qua màn luôn

            if ($player->current_floor > $player->highest_floor) {
                $player->highest_floor = $player->current_floor;
            }
        } else {
            $hasStatusChanged = "STAY"; // Chưa đủ 5 con, đánh tiếp con tiếp theo ở tầng này
        }

    } else {
        // --- TRƯỜNG HỢP THUA QUÁI ---
        // Khi thua, lùi lại 1 tầng (nếu đang ở tầng > 1) 
        if ($player->current_floor > 1) {
            $player->current_floor -= 1;
            $hasStatusChanged = "LOCK_DOWN"; // Báo cho Unity biết: Bị lùi tầng và KHÓA tự động lên tầng
        } else {
            $hasStatusChanged = "STAY"; // Tầng 1 thì ở lại tầng 1
        }

        // Reset số quái đã giết về 0 để lặp lại vòng lặp cày từ đầu ở tầng dưới
        $player->kill_count = 0;

        // Hồi đầy máu để chuẩn bị tự động treo máy cày tiền ở tầng thấp hơn
        $player->current_hp = $player->base_hp;

        // Thua thì không nhận được thưởng
        $rewardGold = 0;
        $rewardExp = 0;
    }

    $player->save();

    // Thiết lập message thông báo dựa trên trạng thái
    $message = "Tiếp tục chiến đấu!";
    if ($hasStatusChanged === "AUTO_UP") {
        $message = "Thắng lợi liên tiếp! Tự động tiến vào tầng " . $player->current_floor;
    } elseif ($hasStatusChanged === "LOCK_DOWN") {
        $message = "Hero thất bại! Đang tự động cày lại ở tầng thấp hơn. Hãy bấm nút thử lại khi đủ mạnh.";
    }

    return response()->json([
        'success' => true,
        'message' => $message,
        'status_floor' => $hasStatusChanged, // "AUTO_UP", "LOCK_DOWN", hoặc "STAY"
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