
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
    private function getGameDate() {
        $now = Carbon::now();
        return $now->hour < 5 ? $now->subDay()->format('Y-m-d') : $now->format('Y-m-d');
    }

    public function getRewardStatus($playerId) {
        $player = Player::find($playerId);
        if (!$player) return response()->json(['message' => 'Người chơi không tồn tại'], 404);

        $rewardState = DailyReward::firstOrCreate(['player_id' => $playerId], ['current_streak_day' => 1]);
        $gameDateToday = $this->getGameDate();
        $isClaimedToday = ($rewardState->last_claimed_date === $gameDateToday);

        // Tính ngày hiện tại dựa trên khoảng cách ngày
        $currentDay = $rewardState->current_streak_day;
        if (!$isClaimedToday && $rewardState->last_claimed_date) {
            $daysPassed = Carbon::parse($gameDateToday)->diffInDays(Carbon::parse($rewardState->last_claimed_date));
            if ($daysPassed > 0) {
                $currentDay = (($rewardState->current_streak_day + $daysPassed - 1) % 7) + 1;
            }
        }

        // Lấy danh sách quà từ DB
        $allRewards = RewardConfig::all();
        $rewardsList = $allRewards->map(function ($config) use ($currentDay, $isClaimedToday, $rewardState) {
            $status = 'locked';
            if ($config->day_index < $currentDay) $status = 'missed_or_claimed';
            if ($config->day_index == $currentDay) $status = $isClaimedToday ? 'claimed' : 'available';
            
            return [
                'day' => $config->day_index,
                'name' => $config->name,
                'type' => $config->type,
                'amount' => $config->amount,
                'status' => $status
            ];
        });

        return response()->json(['success' => true, 'data' => ['current_day' => $currentDay, 'rewards_wheel' => $rewardsList]]);
    }

    public function claimReward(Request $request, $playerId) {
        return DB::transaction(function () use ($playerId) {
            $player = Player::find($playerId);
            $rewardState = DailyReward::where('player_id', $playerId)->first();
            $gameDateToday = $this->getGameDate();

            if ($rewardState && $rewardState->last_claimed_date === $gameDateToday) {
                return response()->json(['message' => 'Đã nhận hôm nay rồi!'], 400);
            }

            // Tính lại ngày nhận thưởng (cần đồng bộ với logic getRewardStatus)
            $currentDay = $this->calculateCorrectDay($rewardState, $gameDateToday);
            
            // Lấy quà từ DB
            $config = RewardConfig::where('day_index', $currentDay)->first();

            // Cộng quà
            $player->increment($config->type, $config->amount);

            // Cập nhật trạng thái
            DailyReward::updateOrCreate(['player_id' => $playerId], [
                'last_claimed_date' => $gameDateToday,
                'current_streak_day' => $currentDay
            ]);

            return response()->json(['success' => true, 'reward' => $config]);
        });
    }

    private function calculateCorrectDay($rewardState, $gameDateToday) {
        if (!$rewardState || !$rewardState->last_claimed_date) return 1;
        $daysPassed = Carbon::parse($gameDateToday)->diffInDays(Carbon::parse($rewardState->last_claimed_date));
        return (($rewardState->current_streak_day + $daysPassed - 1) % 7) + 1;
    }
}