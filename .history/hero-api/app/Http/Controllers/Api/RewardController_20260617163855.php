<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\DailyReward;
use App\Models\RewardConfig; // Import Model Config
use App\Models\RewardService; // Import Service đã tạo
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    private function getGameDate() {
        $now = Carbon::now();
        return $now->hour < 5 ? $now->subDay()->format('Y-m-d') : $now->format('Y-m-d');
    }

    public function getRewardStatus($playerId)
    {
        $player = Player::find($playerId);
        if (!$player) return response()->json(['message' => 'Người chơi không tồn tại'], 404);

        // 1. Lấy cấu hình active qua Service
        $rewardsList = RewardService::getActiveRewards();
        if ($rewardsList->isEmpty()) return response()->json(['message' => 'Chưa cấu hình quà'], 500);
        
        $activeConfig = RewardConfig::getActiveConfig();
        $totalDays = $activeConfig->duration;

        $rewardState = DailyReward::firstOrCreate(['player_id' => $playerId], ['current_streak_day' => 1]);
        $gameDateToday = $this->getGameDate();
        $isClaimedToday = ($rewardState->last_claimed_date === $gameDateToday);

        // 2. Tính toán ngày hiện tại dựa trên duration của cấu hình active
        $currentDay = $rewardState->current_streak_day;
        if (!$isClaimedToday && $rewardState->last_claimed_date) {
            $daysPassed = Carbon::parse($gameDateToday)->diffInDays(Carbon::parse($rewardState->last_claimed_date));
            if ($daysPassed > 0) {
                $currentDay = (($rewardState->current_streak_day + $daysPassed - 1) % $totalDays) + 1;
            }
        }

        // 3. Map dữ liệu từ RewardItems
        $formattedRewards = $rewardsList->map(function ($item) use ($currentDay, $isClaimedToday) {
            $status = 'locked';
            if ($item->day_index < $currentDay) $status = 'missed_or_claimed';
            if ($item->day_index == $currentDay) $status = $isClaimedToday ? 'claimed' : 'available';

            return [
                'day' => $item->day_index,
                'name' => $item->name,
                'type' => $item->reward_type,
                'amount' => $item->amount,
                'status' => $status
            ];
        });

        return response()->json(['success' => true, 'data' => ['current_day' => $currentDay, 'rewards_wheel' => $formattedRewards]]);
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

            // Tính ngày dựa trên Active Config
            $activeConfig = RewardConfig::getActiveConfig();
            $currentDay = $this->calculateCorrectDay($rewardState, $gameDateToday, $activeConfig->duration);
            
            // Lấy item từ RewardItems của config active
            $item = $activeConfig->items()->where('day_index', $currentDay)->first();

            if (!$item) return response()->json(['message' => 'Cấu hình lỗi'], 500);

            // Cộng quà và ghi log
            $oldValue = ($item->reward_type === 'gold') ? $player->gold : $player->exp;
            $player->increment($item->reward_type, $item->amount);

            DB::table('player_logs')->insert([
                'player_id'   => $playerId,
                'field_name'  => 'daily_reward_' . $item->reward_type,
                'old_value'   => $oldValue,
                'new_value'   => $oldValue + $item->amount,
                'created_at'  => now()
            ]);

            DailyReward::updateOrCreate(['player_id' => $playerId], [
                'last_claimed_date' => $gameDateToday,
                'current_streak_day' => $currentDay
            ]);

            return response()->json(['success' => true, 'reward' => $item]);
        });
    }

    private function calculateCorrectDay($rewardState, $gameDateToday, $totalDays)
    {
        if (!$rewardState || !$rewardState->last_claimed_date) return 1;
        $daysPassed = Carbon::parse($gameDateToday)->diffInDays(Carbon::parse($rewardState->last_claimed_date));
        return (($rewardState->current_streak_day + $daysPassed - 1) % $totalDays) + 1;
    }
}