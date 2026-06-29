<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RewardConfig;
use App\Models\RewardItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminConfigController extends Controller
{
    // Lấy danh sách các gói cấu hình
    public function index()
    {
        return RewardConfig::with('items')->orderBy('id', 'desc')->get();
    }

    private function logAction($action, $config, $oldData = null, $newData = null)
    {
        DB::table('reward_audit_logs')->insert([
            'action' => $action,
            'config_id' => $config->id ?? null,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'admin_name' => 'Admin',
            'created_at' => now()
        ]);
    }


    // Tạo mới gói cấu hình kèm theo danh sách item
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'duration' => 'required|integer',
            'items' => 'required|array'
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Tạo Config
            $config = RewardConfig::create([
                'name' => $request->name,
                'duration' => $request->duration,
                'status' => 'draft' // Mặc định là nháp
            ]);

            // 2. Lưu danh sách Item
            foreach ($request->items as $item) {
                RewardItem::create([
                    'config_id'   => $config->id,
                    'day_index'   => $item['day_index'],
                    'name'        => $item['name'] ?? 'Quà ngày ' . $item['day_index'],
                    'reward_type' => $item['reward_type'] ?? 'gold',
                    'amount'      => $item['amount'] ?? 0
                ]);
            }
            $this->logAction($isUpdate ? 'update' : 'create', $config, $oldData, $newData);

            return response()->json(['success' => true, 'config' => $config]);
        });
    }

    // Kích hoạt một gói cấu hình
    public function activate($id)
    {
        return DB::transaction(function () use ($id) {
            // 1. Chuyển tất cả về draft
            RewardConfig::where('status', 'active')->update(['status' => 'draft']);

            // 2. Kích hoạt gói được chọn
            $config = RewardConfig::findOrFail($id);
            $config->update(['status' => 'active', 'activated_at' => now()]);

            return response()->json(['success' => true]);
        });
    }

    // Xem chi tiết một bản ghi (dùng khi mở form sửa)
    public function show($id)
    {
        // Load cả config và các item liên quan
        return RewardConfig::with('items')->findOrFail($id);
    }

    // Xóa một bản ghi cấu hình
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $config = RewardConfig::findOrFail($id);

            // Không cho phép xóa gói đang Active để tránh lỗi game
            if ($config->status === 'active') {
                return response()->json(['message' => 'Không thể xóa gói đang hoạt động'], 400);
            }

            $config->items()->delete(); // Xóa các item liên quan
            $config->delete();          // Xóa config

            return response()->json(['success' => true, 'message' => 'Đã xóa thành công']);
        });
    }
}
