<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\DailyReward;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    private function getGameDate()
    {
        $now = Carbon::now();
        return $now->hour < 5 ? $now->subDay()->format('Y-m-d') : $now->format('Y-m-d');
    }

    public function getRewardStatus($playerId)
    {
        $player = Player::find($playerId);
        if (!$player) return response()->json(['message' => 'Người chơi không tồn tại'], 404);

        $rewardState = DailyReward::firstOrCreate(['player_id' => $playerId], ['current_streak_day' => 1]);
        $gameDateToday = $this->getGameDate();
        $isClaimedToday = ($rewardState->last_claimed_date === $gameDateToday);

        $totalDays = Reward::count(); // Tự động lấy chu kỳ từ database
        if ($totalDays == 0) return response()->json(['message' => 'Chưa cấu hình quà'], 500);

        $currentDay = $rewardState->current_streak_day;
        if (!$isClaimedToday && $rewardState->last_claimed_date) {
            $daysPassed = Carbon::parse($gameDateToday)->diffInDays(Carbon::parse($rewardState->last_claimed_date));
            if ($daysPassed > 0) {
                $currentDay = (($rewardState->current_streak_day + $daysPassed - 1) % $totalDays) + 1;
            }
        }

        $allRewards = Reward::all();
        $rewardsList = $allRewards->map(function ($config) use ($currentDay, $isClaimedToday) {
            $status = 'locked';
            if ($config->day_index < $currentDay) $status = 'missed_or_claimed';
            if ($config->day_index == $currentDay) $status = $isClaimedToday ? 'claimed' : 'available';

            return [
                'day' => $config->day_index,
                'name' => $config->name,
                'type' => $config->reward_type, // Khớp với DB
                'amount' => $config->amount,
                'status' => $status
            ];
        });

        return response()->json(['success' => true, 'data' => ['current_day' => $currentDay, 'rewards_wheel' => $rewardsList]]);
    }

    public function claimReward(Request $request, $playerId)
    {
        return DB::transaction(function () use ($playerId) {
            $player = Player::find($playerId);
            $rewardState = DailyReward::where('player_id', $playerId)->first();
            $gameDateToday = $this->getGameDate();

            if ($rewardState && $rewardState->last_claimed_date === $gameDateToday) {
                return response()->json(['message' => 'Đã nhận hôm nay rồi!'], 400);
            }

            $currentDay = $this->calculateCorrectDay($rewardState, $gameDateToday);
            $config = Reward::where('day_index', $currentDay)->first();

            if (!$config) return response()->json(['message' => 'Cấu hình lỗi'], 500);

            // Ghi nhận số dư cũ
            $oldValue = ($config->reward_type === 'gold') ? $player->gold : $player->exp;

            // Cộng quà (chỉ hỗ trợ gold hoặc exp)
            $player->increment($config->reward_type, $config->amount);

            // Ghi log
            DB::table('player_logs')->insert([
                'player_id'   => $playerId,
                'field_name'  => 'daily_reward_' . $config->reward_type,
                'old_value'   => $oldValue,
                'new_value'   => $oldValue + $config->amount,
                'admin_name'  => 'SYSTEM',
                'created_at'  => now()
            ]);

            // Cập nhật trạng thái
            DailyReward::updateOrCreate(['player_id' => $playerId], [
                'last_claimed_date' => $gameDateToday,
                'current_streak_day' => $currentDay
            ]);

            return response()->json(['success' => true, 'reward' => $config]);
        });
    }

    private function calculateCorrectDay($rewardState, $gameDateToday)
    {
        $totalDays = Reward::count(); // Tự động lấy chu kỳ
        if ($totalDays == 0) return 1;
        if (!$rewardState || !$rewardState->last_claimed_date) return 1;
        
        $daysPassed = Carbon::parse($gameDateToday)->diffInDays(Carbon::parse($rewardState->last_claimed_date));
        return (($rewardState->current_streak_day + $daysPassed - 1) % $totalDays) + 1;
    }
}