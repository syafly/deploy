<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ReservasiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/scan/login', [ScanController::class, 'login'])->name('api.scan.login');

Route::post('/scan/register', [ScanController::class, 'register'])->name('api.scan.register');

Route::post('/scan/update', [ScanController::class, 'update'])->name('api.scan.update');

Route::post('/reservasi', [ReservasiController::class, 'getAllByUid'])->name('api.scan.reservasi');

Route::delete('/reservasi/{id}', [ReservasiController::class, 'cancel'])->name('api.action.cancel-reservasi');