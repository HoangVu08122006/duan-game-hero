<?php
// routes/api.php
use App\Http\Controllers\Api\AdminConfigController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminEntityController;
use App\Http\Controllers\Api\AdminPetController;
use App\Http\Controllers\Api\AdminRewardController;
use App\Http\Controllers\Api\AdminWeaponController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\GachaController;
use App\Http\Controllers\Api\HeroController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\UpgradeController;
use App\Http\Controllers\Api\WeaponController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    // Lấy thông tin nhân vật
    Route::get('/player/me', [AuthController::class, 'me']);

    // 1. API Triệu hồi quái (Đã làm ở bước trước)
    Route::get('battle/spawn', [BattleController::class, 'spawn']);

    // 2. API Xử lý kết quả trận đấu (Thắng/Thua tự động lùi tầng hoặc lên tầng)
    Route::post('battle/defeat', [BattleController::class, 'defeat']);

    // 3. API Bấm nút thủ công để tiến lên lại tầng cao nhất (Khi đang bị kẹt ở tầng dưới)
    Route::post('battle/retry-highest-floor', [BattleController::class, 'retryHighestFloor']);

    // 4. API Lấy trạng thái hiển thị thông số trận đấu (Nếu có dùng)
    Route::get('battle/status', [BattleController::class, 'getBattleStatus']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/player/save', [PlayerController::class, 'saveData']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/battle/spawn', [BattleController::class, 'spawn']);
});

// Đưa vào trong middleware auth:sanctum để bảo mật (chỉ người dùng đã đăng nhập mới nâng cấp được)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/pet/upgrade', [PetController::class, 'upgrade']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/player/pets', [PetController::class, 'getMyPets']);
});

Route::middleware('auth:sanctum')->prefix('hero')->group(function () {
    // Nâng cấp chỉ số bằng Vàng
    Route::post('/upgrade/attack', [HeroController::class, 'upgradeAttack']);
    Route::post('/upgrade/hp', [HeroController::class, 'upgradeHP']);
    Route::post('/upgrade/crit-rate', [HeroController::class, 'upgradeCritRate']);
    Route::post('/upgrade/crit-damage', [HeroController::class, 'upgradeCritDamage']);
    Route::post('/upgrade/speed', [HeroController::class, 'upgradeSpeed']);

    // API để test cộng EXP (Hệ thống sẽ tự động check level up)
    Route::post('/add-exp', [HeroController::class, 'addExp']);
});

        
Route::middleware('auth:sanctum')->group(function () {
    
    // --- CÁC ROUTE NGƯỜI CHƠI ---
    Route::post('/weapon/upgrade', [WeaponController::class, 'upgrade']);
    Route::post('/weapon/equip', [WeaponController::class, 'equip']);

    // --- CÁC ROUTE ADMIN ---
    Route::middleware('is_admin')->group(function () {
        Route::post('/admin/weapon/upgrade', [AdminWeaponController::class, 'adminUpgrade']); 
        Route::post('/admin/weapon/equip', [WeaponController::class, 'equip']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Thêm dòng này
    Route::get('/player/weapons', [WeaponController::class, 'index']);
});

// Route này khớp với gọi từ Axios: axios.get('/api/leaderboard/rankings')
Route::get('/leaderboard/rankings', [LeaderboardController::class, 'getRankings']);

// Route lấy hạng chi tiết
Route::get('/leaderboard/my-rank/{playerId}', [LeaderboardController::class, 'getMyRank']);

// Route lấy trạng thái điểm danh (cần truyền playerId hoặc lấy từ Auth)
Route::get('/daily-reward/status/{playerId}', [RewardController::class, 'getRewardStatus']);

// Route gửi lệnh nhận thưởng
Route::post('/daily-reward/claim/{playerId}', [RewardController::class, 'claimReward']);

Route::post('/admin/pet/upgrade', [AdminPetController::class, 'adminUpdatePetLevel']);
Route::post('/admin/login', [AdminController::class, 'login']);
//danh sach nguoi choi
Route::get('list/players', [AdminController::class, 'index']);
//xem chi tiết tk player
Route::get('/list/players/{id}', [AdminController::class, 'show']);
//Xóa tài khoản
Route::delete('/list/players/{id}', [AdminController::class, 'destroy']);
//tổng lực chiến của các tk
Route::get('/list/players/{id}/total-power', [AdminController::class, 'getPlayerTotalPowerForAdmin']);
//xem name và pass và sửa
Route::prefix('list/players/{id}')->group(function () {
    Route::get('/account', [AdminController::class, 'getPlayerAccountForAdmin']);
    Route::put('/account', [AdminController::class, 'updatePlayerAccountByAdmin']);
});
// sauwr chỉ số
Route::patch('/list/players/{id}/stats', [AdminController::class, 'updatePlayerStats']);

//quản lý vũ khí
Route::get('/weapons', [AdminController::class, 'index_weapon']);
Route::get('/weapons/{id}', [AdminController::class, 'show_weapon']);
Route::post('/weapons', [AdminController::class, 'store_weapon']);
Route::post('/weapons/{id}', [AdminController::class, 'update_weapon']);
Route::delete('/weapons/{id}', [AdminController::class, 'destroy_weapon']);

Route::get('/logs/all', [AdminController::class, 'getLogs']);
Route::get('/players/{id}/logs', [AdminController::class, 'getLogsUpdate']);

Route::get('/pets', [PetController::class, 'index']);
Route::post('/pets', [PetController::class, 'store']);
Route::get('/pets/{id}', [PetController::class, 'show']);
Route::put('/pets/{id}', [PetController::class, 'update_pet']);
Route::get('/pets/{id}/logs', [PetController::class, 'getPetLogs']);


Route::get('/stats/player-growth', [StatsController::class, 'getPlayerGrowth']);

Route::prefix('admin')->group(function () {
    Route::get('/configs', [AdminConfigController::class, 'index']);
    Route::get('/configs/{id}', [AdminConfigController::class, 'show']); // API Xem chi tiết
    Route::post('/configs', [AdminConfigController::class, 'store']);
    Route::post('/configs/{id}/activate', [AdminConfigController::class, 'activate']);
    Route::delete('/configs/{id}', [AdminConfigController::class, 'destroy']); // API Xóa
    Route::get('/logs', [AdminConfigController::class, 'getLogs']);
});

Route::prefix('admin')->group(function () {
    Route::get('/entity/drafts',[AdminEntityController::class, 'drafts']);
    // Dùng chung 1 route cho cả quái vật và boss
    Route::get('/entity/{type}', [AdminEntityController::class, 'index']);
    
    Route::post('/entity/{type}/draft', [AdminEntityController::class, 'storeDraft']);
    Route::post('/entity/publish/{draftId}', [AdminEntityController::class, 'publishDraft']);
    Route::delete('/entity/{type}/{id}', [AdminEntityController::class, 'destroy']);
    Route::delete('/delete-draft/{id}', [AdminEntityController::class, 'deleteDraft']);
    Route::get('/logs', [AdminEntityController::class, 'getLogs']);
});
