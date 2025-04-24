<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CryptoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [CryptoController::class, 'index'])->name('home');

// Rotas para API de criptomoedas
Route::prefix('api/crypto')->group(function () {
    Route::get('/coins', [CryptoController::class, 'getCoins'])->name('api.crypto.coins');
    Route::get('/historical', [CryptoController::class, 'getHistoricalData'])->name('api.crypto.historical');
    Route::get('/ohlc', [CryptoController::class, 'getOHLCData'])->name('api.crypto.ohlc');
    Route::get('/top-gainers', [CryptoController::class, 'getTopGainers'])->name('api.crypto.top-gainers');
    Route::get('/top-losers', [CryptoController::class, 'getTopLosers'])->name('api.crypto.top-losers');
});
