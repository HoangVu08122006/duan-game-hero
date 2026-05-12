<?ph
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerHero;
use App\Models\PlayerPet;
use App\Models\PlayerWeapon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        // 1. Tạo Player
        $player = Player::create([
            'name' => $request->name,
            'password' => Hash::make($request->password), // Nếu có bảng password
            'gold' => 1000, // Tặng vốn khởi tạo
            'level' => 1,
            'current_floor' => 1,
        ]);

        // 2. Tặng Hero mặc định (ID 1 - Chiến Binh)
        PlayerHero::create([
            'player_id' => $player->id,
            'hero_id' => 1,
            'is_active' => true
        ]);

        // 3. Tặng Vũ khí mặc định (ID 1 - Kiếm Gỗ)
        PlayerWeapon::create([
            'player_id' => $player->id,
            'weapon_id' => 1,
            'level' => 1,
            'is_equipped' => true
        ]);

        // 4. Tặng Pet mặc định (ID 1)
        PlayerPet::create([
            'player_id' => $player->id,
            'pet_id' => 1,
            'level' => 1
        ]);

        $token = $player->createToken('game_token')->plainTextToken;

        return response()->json([
            'message' => 'Khởi tạo nhân vật thành công!',
            'token' => $token,
            'player' => $player
        ]);
    }

    public function me(Request $request) {
        // Lấy thông tin người chơi kèm theo Vũ khí và Pet đang dùng
        return response()->json($request->user()->load(['activeWeapon', 'pet']));
    }
}