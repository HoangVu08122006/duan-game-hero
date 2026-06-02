<?php
// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\HeroController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\UpgradeController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    // Lấy thông tin nhân vật
    Route::get('/player/me', [AuthController::class, 'me']);
    
    // Nâng cấp
    Route::post('/upgrade/pet', [UpgradeController::class, 'upgradePet']);
    Route::post('/upgrade/weapon', [UpgradeController::class, 'upgradeWeapon']);
    
    // Chiến đấu
    Route::post('/battle/win-monster', [BattleController::class, 'winMonster']);
    Route::get('/leaderboard', [BattleController::class, 'getLeaderboard']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Đây chính là link: http://IP:8000/api/player/save
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
    Route::get('/player/pets', [PlayerController::class, 'getMyPets']);
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
    // API nâng cấp: truyền id của bản ghi trong bảng player_weapons
    Route::post('/weapon/upgrade', [WeaponController::class, 'upgrade']);
    
    // API trang bị vũ khí
    Route::post('/weapon/equip', [WeaponController::class, 'equip']);
});