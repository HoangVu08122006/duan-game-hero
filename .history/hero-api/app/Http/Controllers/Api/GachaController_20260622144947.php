<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GachaController extends Controller
{
    public function spin(Request $request)
    {
        $player = $request->user(); // Lấy thông tin player đã đăng nhập
        $cost = 50; // Giá mỗi lần quay

        return DB::transaction(function () use ($player, $cost) {
            // 1. Kiểm tra Gem
            if ($player->gems < $cost) {
                return response()->json(['message' => 'Không đủ Gem'], 400);
            }

            // 2. Trừ Gem và tăng Pity
            $player->decrement('gems', $cost);
            $player->increment('gacha_pity_count', 1);

            // 3. Xác định phần thưởng (Logic Gacha)
            $reward = $this->calculateReward($player->gacha_pity_count);

            // 4. Trao thưởng và Reset Pity nếu cần
            if ($reward['type'] === 'pet') {
                $player->playerPets()->create(['pet_id' => $reward['id']]);
                $player->update(['gacha_pity_count' => 0]);
            }

            // 5. Lưu lịch sử
            DB::table('gacha_logs')->insert([
                'player_id' => $player->id,
                'reward_name' => $reward['name'],
                'reward_type' => $reward['type'],
                'created_at' => now()
            ]);

            return response()->json([
                'message' => 'Quay thành công',
                'reward' => $reward
            ]);
        });
    }

    private function calculateReward($pity)
    {
        // Pity cứng ở mốc 50
        if ($pity >= 50) {
            return ['type' => 'pet', 'id' => 1, 'name' => 'Pet Huyền Thoại'];
        }

        // Logic quay ngẫu nhiên theo Weight
        $rand = rand(1, 100);
        return ($rand <= 10) 
            ? ['type' => 'pet', 'id' => 2, 'name' => 'Pet Thường'] 
            : ['type' => 'gold', 'amount' => 100, 'name' => '100 Vàng'];
    }
}