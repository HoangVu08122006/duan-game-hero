<?php

// routes/api.php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BattleController;
use App\Http\Controllers\Api\HeroController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\WeaponController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Không cần đăng nhập)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


/*
|--------------------------------------------------------------------------
| Protected Routes (Bắt buộc phải đăng nhập thông qua Token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- 1. QUẢN LÝ PLAYER & THÔNG TIN ---
    Route::get('/player/me', [AuthController::class, 'me']);
    Route::post('/player/save', [PlayerController::class, 'saveData']);


    // --- 2. HỆ THỐNG CHIẾN ĐẤU (BATTLE) ---
    Route::get('/battle/spawn', [BattleController::class, 'spawn']);
    Route::post('/battle/defeat', [BattleController::class, 'defeat']);
    Route::get('/battle/status', [BattleController::class, 'getBattleStatus']);
    Route::post('/battle/retry-highest-floor', [BattleController::class, 'retryHighestFloor']);


    // --- 3. QUẢN LÝ VÀ NÂNG CẤP HERO ---
    Route::prefix('hero')->group(function () {
        Route::post('/upgrade/attack', [HeroController::class, 'upgradeAttack']);
        Route::post('/upgrade/hp', [HeroController::class, 'upgradeHP']);
        Route::post('/upgrade/crit-rate', [HeroController::class, 'upgradeCritRate']);
        Route::post('/upgrade/crit-damage', [HeroController::class, 'upgradeCritDamage']);
        Route::post('/upgrade/speed', [HeroController::class, 'upgradeSpeed']);
        
        // API Test cộng EXP để kiểm tra thăng cấp Hero
        Route::post('/add-exp', [HeroController::class, 'addExp']);
    });


    // --- 4. HỆ THỐNG VŨ KHÍ (WEAPON) ---
    Route::get('/player/weapons', [WeaponController::class, 'index']);
    Route::post('/weapon/upgrade', [WeaponController::class, 'upgrade']);
    Route::post('/weapon/equip', [WeaponController::class, 'equip']);


    // --- 5. HỆ THỐNG THÚ NUÔI (PET) ---
    // Gọi đúng sang PlayerController vì hàm getMyPets của bạn nằm ở đó
    Route::get('/player/pets', [PlayerController::class, 'getMyPets']); 
    
    // Nâng cấp level cho Pet đang trang bị
    Route::post('/pet/upgrade', [PetController::class, 'upgrade']);
    
    // Nút bấm Trang bị (EQUIP) hoặc Tháo gỡ (UNEQUIP) cho Pet (Đã bổ sung)
    Route::post('/pet/toggle-equip', [PetController::class, 'toggleEquip']);

});