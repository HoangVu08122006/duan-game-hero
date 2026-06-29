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
use Illuminate\Support\Facades\Validator;
use Schema;
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
        return DB::transaction(function () use ($draftId) {
            // 1. Tìm draft
            $draft = Draft::findOrFail($draftId);

            // 2. Giải mã dữ liệu
            $data = json_decode($draft->payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Dữ liệu payload không hợp lệ.");
            }

            // 3. Khởi tạo Model
            $modelClass = $this->getModelClass($draft->entity_type);
            $entity = $modelClass::findOrNew($draft->target_id);

            // 4. Lọc dữ liệu nghiêm ngặt: Chỉ lấy các cột thực sự có trong Database
            // Điều này loại bỏ hoàn toàn lỗi 'Unknown column'
            $table = $entity->getTable();
            $allColumns = Schema::getColumnListing($table);
            $cleanData = array_intersect_key($data, array_flip($allColumns));

            // 5. Xử lý đặc biệt cho cột kiểu JSON (như 'skills')
            // Kiểm tra nếu bảng có cột 'skills' và dữ liệu gửi lên không phải JSON
            if (in_array('skills', $allColumns) && isset($cleanData['skills'])) {
                $val = $cleanData['skills'];
                // Nếu là mảng thì để nguyên, nếu là chuỗi thì ép thành mảng JSON
                if (!is_array($val) && !is_object(json_decode($val))) {
                    $cleanData['skills'] = json_encode([$val]);
                }
            }

            // 6. Lưu dữ liệu
            $entity->fill($cleanData);
            $entity->save();

            // 7. Cập nhật trạng thái draft
            $draft->update(['status' => 'approved']);

            // 8. Ghi log hệ thống
            AdminLog::create([
                'admin_id'    => Auth::id(),
                'action'      => 'PUBLISH',
                'table_name'  => $table, // Dùng $table thay vì nối chuỗi để đảm bảo chính xác
                'entity_id'   => $entity->id,
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
