<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Monster;
use App\Models\Boss;
use App\Models\Draft;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminEntityController extends Controller
{
    // Helper xác định Model
    private function getModelClass(string $type): string
    {
        return $type === 'monster' ? Monster::class : Boss::class;
    }

    // 1. XEM DANH SÁCH (Dữ liệu chính thức)
    public function index(string $type)
    {
        $model = $this->getModelClass($type);
        return response()->json(['success' => true, 'data' => $model::all()]);
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
            $draft = Draft::findOrFail($draftId);
            $data = json_decode($draft->payload, true);
            $modelClass = $this->getModelClass($draft->entity_type);

            $entity = $modelClass::updateOrCreate(['id' => $draft->target_id], $data);
            $draft->update(['status' => 'approved']);

            AdminLog::create([
                'admin_id'    => Auth::id(),
                'action'      => 'PUBLISH',
                'table_name'  => $draft->entity_type . 's',
                'entity_id'   => $entity->id,
                'description' => "Đã duyệt và áp dụng {$draft->entity_type} ID: {$entity->id}"
            ]);

            return response()->json(['success' => true, 'message' => 'Dữ liệu đã được áp dụng vào game!']);
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
}