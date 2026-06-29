<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GachaConfigController extends Controller
{
    // Lấy toàn bộ danh sách cấu hình Gacha
    public function index()
    {
        $configs = DB::table('gacha_configs')->get();
        return response()->json(['data' => $configs]);
    }

    // Thêm cấu hình mới
    public function store(Request $request)
    {
        $validated = $this->validateGacha($request);
        
        $id = DB::table('gacha_configs')->insertGetId($validated);
        
        $this->logAdminAction('CREATE', $id, null, $validated, $request->user()->name ?? 'Admin');
        
        return response()->json(['message' => 'Đã thêm cấu hình thành công', 'id' => $id], 201);
    }

    // Sửa cấu hình
    public function update(Request $request, $id)
    {
        $oldData = DB::table('gacha_configs')->where('id', $id)->first();
        if (!$oldData) return response()->json(['message' => 'Không tìm thấy cấu hình'], 404);

        $validated = $this->validateGacha($request);
        
        DB::table('gacha_configs')->where('id', $id)->update($validated);
        
        $this->logAdminAction('UPDATE', $id, $oldData, $validated, $request->user()->name ?? 'Admin');
        
        return response()->json(['message' => 'Đã cập nhật cấu hình thành công']);
    }

    // Xóa cấu hình
    public function destroy(Request $request, $id)
    {
        $oldData = DB::table('gacha_configs')->where('id', $id)->first();
        if (!$oldData) return response()->json(['message' => 'Không tìm thấy cấu hình'], 404);

        DB::table('gacha_configs')->where('id', $id)->delete();
        
        $this->logAdminAction('DELETE', $id, $oldData, null, $request->user()->name ?? 'Admin');
        
        return response()->json(['message' => 'Đã xóa cấu hình']);
    }

    // Hàm validate dùng chung
    private function validateGacha(Request $request)
    {
        return $request->validate([
            'reward_type' => 'required|string',
            'reward_id'   => 'nullable|integer',
            'amount'      => 'required|integer',
            'weight'      => 'required|integer',
            'description' => 'required|string',
        ]);
    }

    // Hàm ghi log vào bảng admin_gacha_logs
    private function logAdminAction($action, $targetId, $oldData, $newData, $adminName)
    {
        DB::table('admin_gacha_logs')->insert([
            'action'      => $action,
            'table_name'  => 'gacha_configs',
            'target_id'   => $targetId,
            'old_value'   => $oldData ? json_encode($oldData) : null,
            'new_value'   => $newData ? json_encode($newData) : null,
            'admin_name'  => $adminName,
            'created_at'  => now()
        ]);
    }

    
}