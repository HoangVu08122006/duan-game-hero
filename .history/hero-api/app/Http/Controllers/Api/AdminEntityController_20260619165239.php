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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;



class AdminEntityController extends Controller
{
    // Helper xác định Model
    private function getModelClass(string $type): string
    {
        return $type === 'monster' ? Monster::class : Boss::class;
    }

    private function logAction(string $action, string $type, ?int $entityId, string $description)
    {
        \App\Models\MonsterLog::create([
            'admin_id'    => Auth::id() ?? 0,
            'action'      => $action,
            'target_type' => $type, // Lưu loại: 'boss' hoặc 'monster'
            'target_id'   => $entityId,
            'description' => $description,
        ]);
    }

    public function getLogs()
{
    // Lấy 50 log mới nhất
    $logs = \App\Models\MonsterLog::latest()->limit(50)->get();
    return response()->json(['success' => true, 'data' => $logs]);
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
    // 1. Validate dữ liệu
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'base_hp' => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    // 2. Tạo bản nháp (Draft)
    $draft = Draft::create([
        'entity_type' => $type,
        'target_id'   => $request->input('id'),
        'payload'     => $request->except(['id']), // Model Draft đã có $casts => array, không cần json_encode
        'status'      => 'pending'
    ]);

    // 3. Ghi log vào bảng monster_logs thông qua Model MonsterLog
    \App\Models\MonsterLog::create([
        'admin_id'    => Auth::id() ?? 0,
        'action'      => $request->input('id') ? 'UPDATE_DRAFT' : 'CREATE_DRAFT',
        'target_type' => $type, // Lưu lại là 'monster' hoặc 'boss'
        'target_id'   => $request->input('id'), // ID của thực thể gốc (nếu có)
        'payload'     => json_encode($request->except(['id'])), // Lưu chi tiết thay đổi để tiện tra cứu
        'description' => "Tạo bản nháp cho {$type}: " . $request->name
    ]);

    return response()->json([
        'success' => true, 
        'message' => 'Đã lưu bản nháp chờ duyệt!', 
        'draft_id' => $draft->id
    ]);
}

    /**
     * Xóa bản nháp (Draft)
     */

    public function deleteDraft($id)
{
    // 1. Tìm bản ghi theo ID
    $draft = Draft::find($id);

    // 2. Kiểm tra nếu không tồn tại
    if (!$draft) {
        return response()->json([
            'message' => 'Không tìm thấy bản ghi có ID: ' . $id
        ], 404);
    }

    // 3. Ghi log trước khi xóa (Sử dụng Model MonsterLog)
    \App\Models\MonsterLog::create([
        'admin_id'    => Auth::id() ?? 0,
        'action'      => 'DELETE_DRAFT',
        'target_type' => $draft->entity_type, // Lưu lại là 'boss' hoặc 'monster'
        'target_id'   => $id,
        'description' => "Đã xóa bản nháp {$draft->entity_type} có ID: {$id}"
    ]);

    // 4. Thực hiện xóa bản nháp
    $draft->delete();

    return response()->json([
        'message' => 'Đã xóa thành công bản ghi ID: ' . $id
    ], 200);
}

    // 3. ÁP DỤNG BẢN NHÁP (Publish)
    public function publishDraft(int $draftId)
{
    return DB::transaction(function () use ($draftId) {
        // 1. Tìm draft
        $draft = Draft::findOrFail($draftId);

        // 2. Lấy dữ liệu từ payload (Laravel đã tự cast thành array nhờ $casts trong Model Draft)
        $data = $draft->payload; 
        if (empty($data)) {
            throw new \Exception("Dữ liệu payload trống.");
        }

        // 3. Khởi tạo Model
        $modelClass = $this->getModelClass($draft->entity_type);
        $entity = $modelClass::findOrNew($draft->target_id);

        // 4. Lọc dữ liệu nghiêm ngặt
        $table = $entity->getTable();
        $allColumns = Schema::getColumnListing($table);
        $cleanData = array_intersect_key($data, array_flip($allColumns));

        // 5. Xử lý đặc biệt cho cột 'skills' (giữ nguyên logic của bạn)
        if (in_array('skills', $allColumns) && isset($cleanData['skills'])) {
            $val = $cleanData['skills'];
            if (!is_array($val) && !is_object(json_decode($val))) {
                $cleanData['skills'] = json_encode([$val]);
            }
        }

        // 6. Lưu dữ liệu
        $entity->fill($cleanData);
        $entity->save();

        // 7. Cập nhật trạng thái draft
        $draft->update(['status' => 'approved']);

        // 8. Ghi log hệ thống vào bảng monster_logs qua Model MonsterLog
        \App\Models\MonsterLog::create([
            'admin_id'    => Auth::id() ?? 0,
            'action'      => 'PUBLISH',
            'target_type' => $draft->entity_type, // 'monster' hoặc 'boss'
            'target_id'   => $entity->id,         // ID thực thể sau khi lưu
            'payload'     => json_encode($cleanData), // Lưu dữ liệu đã áp dụng
            'description' => "Đã duyệt và áp dụng {$draft->entity_type} ID: {$entity->id}"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dữ liệu đã được áp dụng vào game!'
        ]);
    });
}

    // 4. XÓA (Xóa chính thức và ghi log)
    public function destroy(string $type, int $id)
{
    // 1. Xác định Model và tìm thực thể
    $modelClass = $this->getModelClass($type);
    $entity = $modelClass::findOrFail($id);
    $name = $entity->name;

    // 2. Ghi log thao tác xóa vào bảng monster_logs
    \App\Models\MonsterLog::create([
        'admin_id'    => Auth::id() ?? 0,
        'action'      => 'DELETE',
        'target_type' => $type,         // 'monster' hoặc 'boss'
        'target_id'   => $id,           // ID của thực thể vừa bị xóa
        'description' => "Đã xóa {$type}: {$name}"
    ]);

    // 3. Thực hiện xóa thực thể
    $entity->delete();

    return response()->json([
        'success' => true, 
        'message' => "Đã xóa {$type} thành công"
    ]);
}

    public function drafts()
    {
        return response()->json([
            'success' => true,
            'data' => Draft::latest()->get()
        ]);
    }
}
