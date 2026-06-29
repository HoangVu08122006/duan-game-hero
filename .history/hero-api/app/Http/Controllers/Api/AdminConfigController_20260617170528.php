<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RewardConfig;
use App\Models\RewardItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminConfigController extends Controller
{
    // Lấy danh sách
    public function index()
    {
        return RewardConfig::with('items')->orderBy('id', 'desc')->get();
    }

    // Helper ghi log - dùng chung cho mọi hàm
    private function logAction($action, $config_id, $oldData = null, $newData = null)
    {
        DB::table('reward_audit_logs')->insert([
            'action' => $action, // 'create', 'update', 'delete', 'activate'
            'config_id' => $config_id,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'admin_name' => 'Admin', // Có thể lấy từ auth()->user()->name
            'created_at' => now()
        ]);
    }

    // Xử lý cả Tạo mới và Sửa
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string', 'duration' => 'required|integer', 'items' => 'required|array']);

        return DB::transaction(function () use ($request) {
            $isUpdate = $request->filled('id');
            $oldData = null;

            if ($isUpdate) {
                $config = RewardConfig::findOrFail($request->id);
                $oldData = $config->load('items')->toArray();
                $config->update(['name' => $request->name, 'duration' => $request->duration]);
                $config->items()->delete();
            } else {
                $config = RewardConfig::create([
                    'name' => $request->name,
                    'duration' => $request->duration,
                    'status' => 'draft'
                ]);
            }

            foreach ($request->items as $item) {
                $config->items()->create([
                    'day_index' => $item['day_index'],
                    'name' => $item['name'] ?? 'Quà ngày ' . $item['day_index'],
                    'reward_type' => $item['reward_type'] ?? 'gold',
                    'amount' => $item['amount'] ?? 0
                ]);
            }

            $this->logAction($isUpdate ? 'update' : 'create', $config->id, $oldData, $config->fresh()->load('items'));
            return response()->json(['success' => true]);
        });
    }

    // Kích hoạt (kèm log)
    public function activate($id)
    {
        return DB::transaction(function () use ($id) {
            RewardConfig::where('status', 'active')->update(['status' => 'draft']);
            $config = RewardConfig::findOrFail($id);
            $config->update(['status' => 'active', 'activated_at' => now()]);

            $this->logAction('activate', $id, ['status' => 'draft'], ['status' => 'active']);
            return response()->json(['success' => true]);
        });
    }

    public function show($id)
    {
        return RewardConfig::with('items')->findOrFail($id);
    }

    // Xóa (kèm log)
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $config = RewardConfig::findOrFail($id);
            if ($config->status === 'active') {
                return response()->json(['message' => 'Không thể xóa gói đang hoạt động'], 400);
            }

            $oldData = $config->load('items')->toArray();
            $config->items()->delete();
            $config->delete();

            $this->logAction('delete', $id, $oldData);
            return response()->json(['success' => true]);
        });
    }
}