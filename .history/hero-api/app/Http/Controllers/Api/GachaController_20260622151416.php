<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Cần import Controller cơ sở
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

            // 5. Lưu lịch sử vào bảng player_logs
DB::table('player_logs')->insert([
    'player_id'   => $player->id,
    'field_name'  => 'gacha_spin',           // Đánh dấu đây là hành động quay Gacha
    'old_value'   => 'gems: ' . ($player->gems + $cost), // Trạng thái cũ (Gem trước khi trừ)
    'new_value'   => 'reward: ' . $reward['name'],      // Kết quả quay được
    'admin_name'  => 'SYSTEM',               // Vì đây là hệ thống tự động
    'created_at'  => now()
]);

            return response()->json([
                'message' => 'Quay thành công',
                'reward' => $reward
            ]);
        });
    }

    private function calculateReward($pity)
{
    // 1. Kiểm tra Pity (Bảo hiểm)
    if ($pity >= 50) {
        // Lấy pet "huyền thoại" từ DB dựa trên cấu hình
        return DB::table('gacha_configs')
            ->where('reward_type', 'pet')
            ->orderBy('weight', 'asc') // Hoặc lọc theo cột 'rarity' nếu có
            ->first(); 
    }

    // 2. Quay random theo trọng số (Weighted Random)
    $totalWeight = DB::table('gacha_configs')->sum('weight');
    $rand = rand(1, $totalWeight);
    
    $cumulativeWeight = 0;
    $rewards = DB::table('gacha_configs')->get();

    foreach ($rewards as $reward) {
        $cumulativeWeight += $reward->weight;
        if ($rand <= $cumulativeWeight) {
            return [
                'type' => $reward->reward_type,
                'id'   => $reward->reward_id,
                'name' => $reward->description,
                'amount' => $reward->reward_type == 'gold' ? 100 : null
            ];
        }
    }
}
}