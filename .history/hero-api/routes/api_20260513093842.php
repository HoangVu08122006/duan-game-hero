<?php
// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\UpgradeController;
use App\Http\Controllers\Api\PlayerController;
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