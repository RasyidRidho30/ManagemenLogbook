<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import semua Controller agar rapi
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProjekController;
use App\Http\Controllers\Api\ModulController;
use App\Http\Controllers\Api\KegiatanController;
use App\Http\Controllers\Api\TugasController;
use App\Http\Controllers\Api\LogbookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. PUBLIC ROUTES (Bisa diakses tanpa Token)
// =========================================================================
Route::post('login', [AuthController::class, 'login']);
// Public registration endpoint (create user)
Route::post('register', [UserController::class, 'store']);


// =========================================================================
// 2. PROTECTED ROUTES (Wajib menyertakan Token Bearer)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- AUTHENTICATION & PROFILE ---
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // Helper bawaan Laravel untuk cek user (return resource for consistent shape)
    Route::get('/user', function (Request $request) {
        return new \App\Http\Resources\UserResource($request->user());
    });

    // --- CORE RESOURCES (FULL CRUD) ---

    Route::apiResource('users', UserController::class);
    Route::apiResource('projek', ProjekController::class);
    Route::apiResource('modul', ModulController::class);
    Route::apiResource('tugas', TugasController::class);

    // --- KEGIATAN (PARTIAL CRUD) ---
    Route::get('kegiatan', [KegiatanController::class, 'index']);
    Route::post('kegiatan', [KegiatanController::class, 'store']);

    // --- LOGBOOK ---
    Route::get('logbook', [LogbookController::class, 'index']);
    Route::post('logbook', [LogbookController::class, 'store']);

    // --- PROJEK SPECIFIC LOGIC (DASHBOARD) ---
    Route::get('/projek/{id}/stats', [ProjekController::class, 'getDashboardStats']);
    Route::get('/projek/{id}/breakdown', [ProjekController::class, 'getProjectBreakdown']);
    Route::post('/projek/{id}/recalculate', [ProjekController::class, 'recalculateProgress']);
    Route::get('/projek/{id}/members', [App\Http\Controllers\Api\ProjekController::class, 'getMembers']);

    Route::post('/profile/update', [UserController::class, 'update']);
});
