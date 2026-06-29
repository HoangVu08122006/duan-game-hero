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
}