<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\DailyReward;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{


    /**
     * Helper: Lấy ngày game hiện tại (Reset lúc 5h sáng)
     */
    private function getGameDate()
    {
        $now = Carbon::now(); // Đảm bảo timezone trong config/app.php đã chuẩn Asia/Ho_Chi_Minh
        return $now->hour < 5 ? $now->subDay()->format('Y-m-d') : $now->format('Y-m-d');
    }

    /**
     * API 1: Lấy trạng thái điểm danh hôm nay của Player
     */
    public function getRewardStatus($playerId)
    {
        $player = Player::find($playerId);
        if (!$player) return response()->json(['message' => 'Không tìm thấy người chơi'], 404);

        // Lấy hoặc tự tạo bản ghi điểm danh nếu chưa có
        $rewardState = DailyReward::firstOrCreate(
            ['player_id' => $player->id],
            ['last_claimed_date' => null, 'current_streak_day' => 1]
        );

        $gameDateToday = $this->getGameDate();
        
        // Kiểm tra xem hôm nay đã nhận chưa
        $isClaimedToday = ($rewardState->last_claimed_date === $gameDateToday);

        // Tính toán xem họ đang ở ngày mấy trong tuần
        // Nếu hôm nay chưa nhận, và ngày hôm qua họ CŨNG KHÔNG nhận (bị lỡ) -> Reset tiến trình hoặc giữ nguyên?
        // Theo yêu cầu của bạn: "ko bấm nhận thì phần thưởng khóa lại, lặp vô hạn theo tuần".
        // Nghĩa là nếu họ bỏ lỡ ngày cũ, hôm sau họ vẫn tiếp tục ngày tiếp theo của tuần, hoặc tự động nhảy ngày.
        
        // Cách xử lý chuẩn: Dựa vào khoảng cách ngày để xác định ngày hiện tại trong tuần
        $currentDayInWeek = $rewardState->current_streak_day;

        // Nếu đã bước sang ngày game mới và ngày trước đó đã nhận thành công, tiến cấp ngày tiếp theo
        if (!$isClaimedToday && $rewardState->last_claimed_date !== null) {
            $lastClaimed = Carbon::parse($rewardState->last_claimed_date);
            $daysPassed = Carbon::parse($gameDateToday)->diffInDays($lastClaimed);
            
            if ($daysPassed > 0) {
                // Tăng số ngày lên dựa theo số ngày trôi qua, vòng lặp 7 ngày (% 7)
                $currentDayInWeek = (($rewardState->current_streak_day + $daysPassed - 1) % 7) + 1;
            }
        }

        // Chuẩn bị danh sách hiển thị 7 ngày cho Client hiển thị UI
        $rewardsList = [];
        foreach (DailyReward::REWARDS_CONFIG as $day => $config) {
            $status = 'locked'; // Mặc định là khóa
            
            if ($day < $currentDayInWeek) {
                $status = 'missed_or_claimed'; // Ngày cũ đã qua (Có thể là đã nhận hoặc bỏ lỡ)
                if ($day == $rewardState->current_streak_day && $isClaimedToday) {
                    $status = 'claimed';
                }
            } elseif ($day == $currentDayInWeek) {
                $status = $isClaimedToday ? 'claimed' : 'available'; // Ngày hiện tại: Đã nhận hoặc Có thể nhận
            }

            $rewardsList[] = [
                'day' => $day,
                'name' => $config['name'],
                'type' => $config['type'],
                'amount' => $config['amount'],
                'status' => $status
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current_day' => $currentDayInWeek,
                'is_claimed_today' => $isClaimedToday,
                'rewards_wheel' => $rewardsList
            ]
        ]);
    }

    /**
     * API 2: Bấm Nhận Thưởng
     */
    public function claimReward(Request $request, $playerId)
    {
        return DB::transaction(function () use ($playerId) {
            $player = Player::find($playerId);
            if (!$player) return response()->json(['message' => 'Người chơi không tồn tại'], 404);

            $rewardState = DailyReward::where('player_id', $playerId)->first();
            $gameDateToday = $this->getGameDate();

            // 1. Kiểm tra xem hôm nay nhận chưa
            if ($rewardState && $rewardState->last_claimed_date === $gameDateToday) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hôm nay bạn đã nhận phần thưởng rồi, hãy quay lại sau 5h sáng mai!'
                ], 400);
            }

            // 2. Tính ngày hiện tại chính xác (như logic ở API status)
            $currentDayInWeek = $rewardState ? $rewardState->current_streak_day : 1;
            if ($rewardState && $rewardState->last_claimed_date !== null) {
                $daysPassed = Carbon::parse($gameDateToday)->diffInDays(Carbon::parse($rewardState->last_claimed_date));
                if ($daysPassed > 0) {
                    $currentDayInWeek = (($rewardState->current_streak_day + $daysPassed - 1) % 7) + 1;
                }
            }

            // 3. Lấy phần thưởng tương ứng ngày đó từ Config
            $rewardConfig = DailyReward::REWARDS_CONFIG[$currentDayInWeek];

            // 4. Cộng thưởng trực tiếp vào bảng `players`
            if ($rewardConfig['type'] === 'gold') {
                $player->increment('gold', $rewardConfig['amount']);
            } elseif ($rewardConfig['type'] === 'exp') {
                $player->increment('exp', $rewardConfig['amount']);
                // Ở đây nếu có logic lên cấp (level up) từ EXP thì bạn gọi thêm hàm xử lý nhé
            }

            // 5. Cập nhật trạng thái bảng Điểm Danh
            DailyReward::updateOrCreate(
                ['player_id' => $playerId],
                [
                    'last_claimed_date' => $gameDateToday,
                    'current_streak_day' => $currentDayInWeek
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Nhận thưởng thành công!',
                'reward' => [
                    'day' => $currentDayInWeek,
                    'reward_name' => $rewardConfig['name'],
                    'type' => $rewardConfig['type'],
                    'amount' => $rewardConfig['amount']
                ],
                'player_balances' => [
                    'gold' => $player->gold,
                    'exp' => $player->exp
                ]
            ]);
        });
    }
}