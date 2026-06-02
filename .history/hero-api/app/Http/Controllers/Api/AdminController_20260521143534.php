<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

public function index()
    {
        try {
            // Lấy toàn bộ danh sách người chơi từ bảng (giả định tên bảng của bạn là 'users' hoặc 'players')
            // Thay 'users' bằng tên bảng thực tế của bạn nếu khác nhé
            $players = DB::table('players')
                ->select(
                    'id', 'name', 'gold', 'level', 'exp', 
                    'current_floor', 'kill_count', 'highest_floor', 
                    'upgraded_attack_lv', 'upgraded_hp_lv', 'upgraded_crit_rate_lv', 
                    'upgraded_speed_lv', 'total_power', 'base_hp', 'current_hp', 
                    'base_attack', 'upgraded_crit_damage_lv', 'created_at', 'updated_at'
                )
                ->get();

            // Trả về dữ liệu dạng JSON thành công
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy danh sách người chơi thành công!',
                'data' => $players
            ], 200);

        } catch (\Exception $e) {
            // Xử lý lỗi nếu có ngoại lệ xảy ra
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi hệ thống.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // API Admin xem chi tiết người chơi không cần token
public function show($id)
{
    try {
        // Tìm người chơi theo ID, nếu không thấy sẽ trả về lỗi 404
        // Thay 'Player' bằng tên Model thực tế của bạn nếu khác nhé
        $player = Player::with(['weapons', 'pets'])->find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy thông tin chi tiết người chơi thành công!',
            'player' => $player
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function destroy($id)
{
    try {
        // 1. Tìm người chơi theo ID
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        // 2. Dùng Transaction để xóa sạch dữ liệu liên quan một cách an toàn
        DB::transaction(function () use ($player) {
            // Xóa dữ liệu ở các bảng liên kết trước để tránh lỗi khóa ngoại (Foreign Key Constraint)
            $player->weapons()->delete(); // Hoặc DB::table('player_weapons')->where('player_id', $player->id)->delete();
            $player->playerPets()->delete(); // Hoặc DB::table('player_pets')->where('player_id', $player->id)->delete();

            // Cuối cùng là xóa tài khoản người chơi
            $player->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa tài khoản người chơi và toàn bộ dữ liệu liên quan thành công!'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống khi xóa.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getPlayerTotalPowerForAdmin($id)
{
    try {
        // 1. Tìm người chơi theo ID truyền từ URL
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        // Sát thương gốc của Hero
        $heroDamage = $player->base_attack ?? 50; 

        // 2. Tính Damage của VŨ KHÍ đang đeo
        $weaponDamage = 0;
        $equippedWeapon = $player->weapons()
            ->with('weapon')
            ->where('is_equipped', true)
            ->first();

        if ($equippedWeapon) {
            $weaponDamage = $equippedWeapon->current_damage ?? 0;
        }

        // 3. Tính Damage của PET đang đeo
        $petDamage = 0;
        $equippedPet = $player->playerPets()
            ->with('pet')
            ->where('is_equipped', true)
            ->first();

        if ($equippedPet) {
            $petLv = $equippedPet->level ?? 1;
            $petBaseAtk = $equippedPet->pet->base_attack ?? 50;

            // Tính Dame chính của Pet
            $mainPetDame = $petBaseAtk * pow(1.1, $petLv - 1);

            // Tính thêm các kĩ năng phụ thuộc cấp độ Pet
            $skill1 = 0;
            $baseSkill1 = ($petBaseAtk * pow(1.1, 28)) * 1.5;
            if ($petLv >= 30) {
                $skill1 = $baseSkill1 * pow(1.1, $petLv - 30);
            }

            $skill2 = 0;
            if ($petLv >= 60) {
                $skill1At59 = $baseSkill1 * pow(1.1, 59 - 30);
                $skill2 = ($skill1At59 * 1.5) * pow(1.1, $petLv - 60);
            }

            $skill3 = 0;
            if ($petLv >= 90) {
                $skill1At59ForS3 = $baseSkill1 * pow(1.1, 59 - 30);
                $skill2At89 = ($skill1At59ForS3 * 1.5) * pow(1.1, 89 - 60);
                $skill3 = ($skill2At89 * 1.5) * pow(1.1, $petLv - 90);
            }

            $petDamage = $mainPetDame + $skill1 + $skill2 + $skill3;
        }

        // 4. Cộng dồn tổng lực chiến
        $totalPower = $heroDamage + $weaponDamage + $petDamage;

        // 5. Cập nhật lại cột total_power của người chơi đó vào DB để đồng bộ dữ liệu
        $player->update([
            'total_power' => (int)round($totalPower)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin kiểm tra lực chiến thành công!',
            'player_info' => [
                'id' => $player->id,
                'name' => $player->name,
                'level' => $player->level
            ],
            'damage_details' => [
                'hero_base' => (int)$heroDamage,
                'equipped_weapon' => (int)round($weaponDamage),
                'equipped_pet' => (int)round($petDamage),
            ],
            'calculated_total_power' => (int)round($totalPower)
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống.',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getPlayerAccountForAdmin($id)
{
    try {
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy thông tin tài khoản thành công!',
            'data' => [
                'id' => $player->id,
                'current_name' => $player->name,
                'encrypted_password' => $player->password, // Trả về chuỗi Bcrypt đã mã hóa lưu trong DB
                'created_at' => $player->created_at,
                'updated_at' => $player->updated_at
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống.',
            'error' => $e->getMessage()
        ], 500);
    }
}

// 2. API Admin Thay đổi Tên và Mật khẩu của tài khoản
public function updatePlayerAccountByAdmin(Request $request, $id)
{
    try {
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        // Validate dữ liệu: Tên mới không được trùng với người khác (trừ chính họ), mật khẩu tối thiểu 6 ký tự
        $request->validate([
            'name' => 'nullable|string|unique:players,name,' . $player->id,
            'password' => 'nullable|string|min:6'
        ]);

        // Sử dụng Transaction để đảm bảo an toàn dữ liệu
        DB::transaction(function () use ($request, $player) {
            $updateData = [];

            // Nếu admin có truyền lên trường name thì mới sửa
            if ($request->has('name') && !empty($request->name)) {
                $updateData['name'] = $request->name;
            }

            // Nếu admin có truyền lên trường password thì tiến hành mã hóa Hash rồi mới sửa
            if ($request->has('password') && !empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Chỉ cập nhật nếu có dữ liệu thay đổi
            if (!empty($updateData)) {
                $player->update($updateData);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Admin đã cập nhật thông tin tài khoản thành công!',
            'data' => [
                'id' => $player->id,
                'new_name' => $player->name,
                'updated_at' => $player->updated_at
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cập nhật thất bại hoặc tên nhân vật đã tồn tại.',
            'error' => $e->getMessage()
        ], 400);
    }
}

public function adminSetStats(Request $request, $id)
{
    try {
        // 1. Tìm người chơi theo ID
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người chơi với ID này.'
            ], 404);
        }

        // 2. Validate dữ liệu truyền lên (Đảm bảo phải là số nguyên dương và không bắt buộc điền hết)
        $request->validate([
            'upgraded_attack_lv'      => 'nullable|integer|min:0',
            'upgraded_hp_lv'          => 'nullable|integer|min:0',
            'upgraded_crit_rate_lv'   => 'nullable|integer|min:0',
            'upgraded_speed_lv'       => 'nullable|integer|min:0',
            'upgraded_crit_damage_lv' => 'nullable|integer|min:0',
        ]);

        // 3. Lọc lấy những chỉ số được Admin gửi lên để cập nhật
        $updateData = [];
        $statFields = [
            'upgraded_attack_lv',
            'upgraded_hp_lv',
            'upgraded_crit_rate_lv',
            'upgraded_speed_lv',
            'upgraded_crit_damage_lv'
        ];

        foreach ($statFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->input($field);
            }
        }

        if (empty($updateData)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không có chỉ số nào được truyền lên để thay đổi.'
            ], 400);
        }

        // 4. Cập nhật thẳng vào Database (Bỏ qua mọi logic check vàng/cấp độ Hero)
        $player->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin đã thay đổi cấp độ chỉ số thành công (Miễn phí)!',
            'player_info' => [
                'id' => $player->id,
                'name' => $player->name
            ],
            'current_stats' => [
                'upgraded_attack_lv'      => $player->upgraded_attack_lv,
                'upgraded_hp_lv'          => $player->upgraded_hp_lv,
                'upgraded_crit_rate_lv'   => $player->upgraded_crit_rate_lv,
                'upgraded_speed_lv'       => $player->upgraded_speed_lv,
                'upgraded_crit_damage_lv' => $player->upgraded_crit_damage_lv,
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Đã xảy ra lỗi hệ thống khi chỉnh sửa chỉ số.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}