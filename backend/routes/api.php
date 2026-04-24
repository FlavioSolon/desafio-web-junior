<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CepController;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/auth/me', [AuthController::class, 'me'])->middleware([Authenticate::using('api')])->name('auth.me');
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware([Authenticate::using('api')])->name('auth.logout');
Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware([Authenticate::using('api')])->name('auth.refresh');

Route::apiResource('/clientes', ClientController::class)->middleware([Authenticate::using('api')]);
Route::get('/cep/{cep}', [CepController::class, 'show'])->middleware([Authenticate::using('api')]);
