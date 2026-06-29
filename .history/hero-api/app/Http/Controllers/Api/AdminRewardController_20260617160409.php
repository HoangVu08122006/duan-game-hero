
<?php
namespace App\Http;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRewardController extends Controller
{
    // Cập nhật quà (Sửa)
    public function update(Request $request, $dayIndex)
    {
        $reward = Reward::where('day_index', $dayIndex)->first();
        if (!$reward) return response()->json(['message' => 'Không tìm thấy ngày này'], 404);

        $oldData = $reward->toArray();
        $newData = $request->only(['reward_type', 'amount', 'name']);

        DB::transaction(function () use ($reward, $newData, $oldData) {
            $reward->update($newData);

            // Ghi log thay đổi
            DB::table('player_logs')->insert([
                'player_id'   => 0, // 0 biểu thị hành động của Admin
                'field_name'  => 'admin_update_reward_day_' . $reward->day_index,
                'old_value'   => json_encode($oldData),
                'new_value'   => json_encode($newData),
                'created_at'  => now()
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
    }

    // Thêm mới hoặc Xóa tương tự...
    public function destroy(Request $request, $dayIndex)
    {
        $reward = Reward::where('day_index', $dayIndex)->first();
        if ($reward) {
            DB::table('player_logs')->insert([
                'player_id'   => 0,
                'field_name'  => 'admin_delete_reward_day_' . $dayIndex,
                'old_value'   => json_encode($reward->toArray()),
                'new_value'   => 'DELETED',
                'admin_name'  => $request->user()->name ?? 'Admin',
                'created_at'  => now()
            ]);
            $reward->delete();
        }
        return response()->json(['success' => true]);
    }
}