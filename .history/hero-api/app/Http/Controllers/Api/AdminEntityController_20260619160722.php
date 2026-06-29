<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Boss;
use App\Models\Draft;
use App\Models\Monster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminEntityController extends Controller
{
    // Helper xác định Model
    private function getModelClass(string $type): string
    {
        return $type === 'monster' ? Monster::class : Boss::class;
    }

    public function index(string $type)
    {
        // Kiểm tra an toàn, chỉ cho phép monster hoặc boss
        if (!in_array($type, ['monster', 'boss'])) {
            return response()->json(['message' => 'Loại thực thể không hợp lệ'], 400);
        }

        $modelClass = $this->getModelClass($type); // Sử dụng sẵn hàm helper của bạn

        return response()->json([
            'success' => true,
            'data' => $modelClass::all()
        ]);
    }

    // 2. TẠO HOẶC SỬA BẢN NHÁP (Draft)
    public function storeDraft(Request $request, string $type)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'base_hp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $draft = Draft::create([
            'entity_type' => $type,
            'target_id'   => $request->input('id'),
            'payload'     => json_encode($request->except(['id'])),
            'status'      => 'pending'
        ]);

        AdminLog::create([
            'admin_id'    => Auth::id(),
            'action'      => $request->input('id') ? 'UPDATE_DRAFT' : 'CREATE_DRAFT',
            'table_name'  => $type . 's',
            'entity_id'   => $request->input('id'),
            'description' => "Tạo bản nháp cho {$type}: " . $request->name
        ]);

        return response()->json(['success' => true, 'message' => 'Đã lưu bản nháp chờ duyệt!', 'draft_id' => $draft->id]);
    }

    // 3. ÁP DỤNG BẢN NHÁP (Publish)
    public function publishDraft(int $draftId)
{
    try {
        return DB::transaction(function () use ($draftId) {
            $draft = Draft::findOrFail($draftId);
            $data = json_decode($draft->payload, true);
            
            $modelClass = $this->getModelClass($draft->entity_type);
            $entity = $modelClass::findOrNew($draft->target_id);

            // GHI LOG DỮ LIỆU ĐỂ KIỂM TRA
            Log::info('Data payload:', $data); 

            $entity->fill($data);
            $entity->save(); // LỖI THƯỜNG XẢY RA Ở ĐÂY

            $draft->update(['status' => 'approved']);
            // ... (phần còn lại giữ nguyên)
        });
    } catch (\Exception $e) {
        // TRẢ VỀ LỖI CỤ THỂ ĐỂ XEM TRÊN TRÌNH DUYỆT
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    // 4. XÓA (Xóa chính thức và ghi log)
    public function destroy(string $type, int $id)
    {
        $modelClass = $this->getModelClass($type);
        $entity = $modelClass::findOrFail($id);
        $name = $entity->name;

        AdminLog::create([
            'admin_id'    => Auth::id(),
            'action'      => 'DELETE',
            'table_name'  => $type . 's',
            'entity_id'   => $id,
            'description' => "Đã xóa {$type}: {$name}"
        ]);

        $entity->delete();
        return response()->json(['success' => true, 'message' => "Đã xóa {$type} thành công"]);
    }

    public function drafts()
    {
        return response()->json([
            'success' => true,
            'data' => Draft::latest()->get()
        ]);
    }
}
