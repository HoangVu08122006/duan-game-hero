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

        // 1. Lấy dữ liệu stats hiện tại từ Model (thông qua Accessor hoặc tính trực tiếp)
        // Lưu ý: Đảm bảo các giá trị này đã được tính toán với level nâng cấp
        $stats = [
            'atk'    => $player->current_attack,
            'hp'     => $player->current_hp,
            'crit_r' => $player->current_crit_rate,
            'crit_d' => $player->current_crit_damage,
            'speed'  => $player->current_speed,
        ];

        // 2. Tính $heroDamage theo công thức bạn yêu cầu
        $heroDamage = ($stats['atk'] * 5)
                    + ($stats['hp'] / 10)
                    + ($stats['crit_r']/100 * 2000)
                    + (($stats['crit_d'] - 1) * 1000)
                    + ($stats['speed'] * 100);

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

private function recalculatePlayerStats($player)
{
    // Giả định công thức gốc của bạn là:
    // Base ban đầu * (hệ số ^ lv)
    // Bạn cần thay giá trị 'initial_base_attack' bằng giá trị gốc mặc định của nhân vật
    $player->base_attack = (int)round($player->initial_base_attack * pow(1.08, $player->upgraded_attack_lv));
    $player->base_hp     = (int)round($player->initial_base_hp * pow(1.12, $player->upgraded_hp_lv));
    
    // Cập nhật lại lực chiến tổng
    $player->total_power = $this->calculateTotalPower($player);
    $player->save();
}
public function updatePlayerStats(Request $request, $id)
{
    $player = Player::findOrFail($id);

    // 1. Cập nhật các cấp độ nâng cấp từ Request
    $player->upgraded_hp_lv          = $request->upgraded_hp_lv;
    $player->upgraded_attack_lv      = $request->upgraded_attack_lv;
    $player->upgraded_speed_lv       = $request->upgraded_speed_lv;
    $player->upgraded_crit_rate_lv   = $request->upgraded_crit_rate_lv;
    $player->upgraded_crit_damage_lv = $request->upgraded_crit_damage_lv;

    // 2. Tính toán Lực chiến (Dựa trên các chỉ số hiện tại đã tính ở Model)
    // Lưu ý: Lực chiến = (Attack Hiện tại) + Vũ khí + Pet
    $weaponAtk = $player->weapons()->where('is_equipped', true)->with('weapon')->get()->sum(fn($w) => $w->weapon->damage ?? 0);
    $petAtk = $player->playerPets()->where('is_equipped', true)->with('pet')->get()->sum(fn($p) => $p->pet->base_attack ?? 0);

    // Sử dụng trực tiếp $player->current_attack (Accessor đã tạo ở bước trước)
    $player->total_power = $player->current_attack + $weaponAtk + $petAtk;

    $player->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Cập nhật chỉ số thành công!',
        'player' => $player // Trả về đầy đủ để Vue cập nhật UI
    ]);
}

private function calculateTotalPower($player) {
    // 1. Tính chỉ số cơ bản (Dựa trên cấp độ đã nâng)
    // Lưu ý: $player->current_attack đã bao gồm hệ số tăng trưởng từ level
    $attack     = $player->current_attack; 
    $hp         = $player->current_hp;
    
    // 2. Tính chỉ số từ cấp độ (Lấy trực tiếp từ các cột cấp độ)
    $critRate   = $player->upgraded_crit_rate_lv * 2;   // 2% mỗi cấp
    $critDamage = $player->upgraded_crit_damage_lv * 5; // 5% mỗi cấp
    $speed      = $player->upgraded_speed_lv * 0.2;     // 0.2 mỗi cấp

    // 3. Tính Damage từ trang bị (Weapon) và Pet (Đang xuất trận)
    $weaponAtk = $player->weapons()->where('is_equipped', true)
                 ->with('weapon')->get()->sum(fn($w) => $w->weapon->damage ?? 0);
    
    $petAtk    = $player->playerPets()->where('is_equipped', true)
                 ->with('pet')->get()->sum(fn($p) => $p->pet->base_attack ?? 0);

    // 4. Công thức tổng hợp (Tấn công gốc + Trang bị + Pet + Các chỉ số cộng thêm)
    $total = ($attack + $weaponAtk + $petAtk)
             + ($hp / 10) 
             + ($critRate * 50) 
             + ($critDamage * 20) 
             + ($speed * 100);

    return (int)round($total);
}
}