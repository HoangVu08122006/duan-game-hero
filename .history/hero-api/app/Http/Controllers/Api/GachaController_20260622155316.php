<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Cần import Controller cơ sở
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GachaController extends Controller
{
    public function spin(Request $request)
    {
        $player = $request->user();
        $cost = 50;

        return DB::transaction(function () use ($player, $cost) {
            // 1. Kiểm tra Gem
            if ($player->gems < $cost) {
                return response()->json(['message' => 'Không đủ Gem'], 400);
            }

            // 2. Trừ Gem và tăng Pity
            $player->decrement('gems', $cost);
            $player->increment('gacha_pity_count', 1);

            // 3. Xác định phần thưởng
            $reward = $this->calculateReward($player->gacha_pity_count);

            // 4. Trao thưởng và cập nhật trạng thái
            if ($reward['type'] === 'pet') {
                $player->playerPets()->create(['pet_id' => $reward['id']]);
                $player->update(['gacha_pity_count' => 0]); // Reset Pity khi ra Pet
            } elseif ($reward['type'] === 'gold') {
                // Cộng vàng vào database
                $player->increment('gold', $reward['amount']);
            }

            // Refresh model để lấy dữ liệu mới nhất (đã cập nhật vàng/gem)
            $player->refresh();

            // 5. Lưu lịch sử vào bảng gacha_logs
            DB::table('gacha_logs')->insert([
                'player_id' => $player->id,
                'reward_name' => $reward['name'],
                'reward_type' => $reward['type'],
                'created_at' => now()
            ]);

            // 6. Lưu lịch sử vào bảng player_logs (Bảng quản lý log chung)
            DB::table('player_logs')->insert([
                'player_id'   => $player->id,
                'field_name'  => 'gacha_spin',
                'old_value'   => 'gems: ' . ($player->gems + $cost) . ', gold: ' . ($reward['type'] === 'gold' ? ($player->gold - $reward['amount']) : $player->gold),
                'new_value'   => 'reward: ' . $reward['name'] . ', current_gold: ' . $player->gold,
                'admin_name'  => 'SYSTEM',
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
            $r = DB::table('gacha_configs')->where('reward_type', 'pet')->orderBy('weight', 'asc')->first();
            return [
                'type'   => $r->reward_type,
                'id'     => $r->reward_id,
                'name'   => $r->description,
                'amount' => 0 // Pet không có amount
            ];
        }

        // 2. Quay random theo trọng số
        $totalWeight = DB::table('gacha_configs')->sum('weight');
        $rand = rand(1, $totalWeight);

        $cumulativeWeight = 0;
        $rewards = DB::table('gacha_configs')->get();

        foreach ($rewards as $reward) {
            $cumulativeWeight += $reward->weight;
            if ($rand <= $cumulativeWeight) {
                return [
                    'type'   => $reward->reward_type,
                    'id'     => $reward->reward_id,
                    'name'   => $reward->description,
                    // Sửa tại đây: ép kiểu về số nguyên từ cột 'amount' trong DB
                    'amount' => (int) $reward->amount
                ];
            }
        }
    }

    public function history(Request $request)
    {
        $player = $request->user();

        // Thay get() bằng paginate()
        // Mặc định mỗi trang sẽ lấy 15 bản ghi
        $history = DB::table('gacha_logs')
            ->where('player_id', $player->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'message' => 'Lấy lịch sử quay thành công',
            'data'    => $history // Laravel sẽ tự trả về định dạng data kèm thông tin phân trang (current_page, last_page, total...)
        ]);
    }
}
