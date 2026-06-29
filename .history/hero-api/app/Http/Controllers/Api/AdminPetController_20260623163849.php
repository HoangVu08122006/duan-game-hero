<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\PlayerLog;
use App\Models\PlayerPet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminPetController extends Controller
{
   public function adminUpdatePetLevel(Request $request)
{
    $validated = $request->validate([
        'player_id' => 'required|exists:players,id',
        'player_pet_id' => 'required|exists:player_pets,id',
        'new_level' => 'required|integer|min:1|max:9999'
    ]);

    $playerPet = PlayerPet::where('id', $validated['player_pet_id'])
        ->where('player_id', $validated['player_id'])
        ->first();

    if (!$playerPet) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy pet của người chơi.'
        ], 404);
    }

    $oldLevel = $playerPet->level;

    $playerPet->level = $validated['new_level'];
    $playerPet->save();

    // Ghi log
    PlayerLog::create([
        'player_id' => $validated['player_id'],
        'field_name' => 'pet_level',
        'old_value' => $oldLevel,
        'new_value' => $validated['new_level'],
        'admin_name' => 'Admin'
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Cập nhật level pet thành công!',
        'data' => [
            'old_level' => $oldLevel,
            'new_level' => $playerPet->level
        ]
    ]);
}

public function index(): JsonResponse
        {
            try {
                $pets = Pet::all(); // Lấy tất cả thú cưng trong bảng 'pets'
                return response()->json(['success' => true, 'data' => $pets], 200);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Lỗi server'], 500);
            }
        }

    public function update_pet(Request $request, $id): JsonResponse
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Not found'], 404);

        // BẮT LỖI VALIDATION: Nếu validate fail, nó sẽ tự trả về lỗi 422
        $request->validate([
            'name' => 'required',
            'base_dps' => 'required|numeric',
        ]);

        try {
            $data = $request->only([
                'name', 'prefab_name', 'base_dps', 'growth_rate', 
                'skill_1_name', 'skill_2_name', 'skill_3_name',
                'skill_1_description', 'skill_2_description', 'skill_3_description'
            ]);

            $pet->fill($data);
            
            // Ghi log lỗi nếu pet không có thay đổi gì (không gọi được save)
            if (!$pet->isDirty()) {
                return response()->json(['success' => true, 'message' => 'Không có thay đổi nào']);
            }

            $pet->save();
            
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (\Exception $e) {
            // GHI LỖI VÀO LOG ĐỂ XEM
            Log::error("DEBUG LỖI PET: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPetLogs($id)
    {
        $logs = \App\Models\PetLog::where('pet_id', $id)->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $logs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prefab_name' => 'required|string',
            'base_dps' => 'required|numeric',
            'growth_rate' => 'required|numeric',
            // Thêm validate cho các skill...
        ]);

        $pet = Pet::create($validated);

        return response()->json(['message' => 'Pet added successfully', 'data' => $pet], 201);
    }

    public function show($id)
    {
        $pet = Pet::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $pet
        ]);
    }

}