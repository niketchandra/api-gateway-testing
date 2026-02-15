<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\PatTokenController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SystemRegisterController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth.session');

// PAT token management - requires session token (temporary bearer token)
Route::post('/auth/pat-tokens', [PatTokenController::class, 'store'])->middleware('auth.session');
Route::get('/auth/pat-tokens', [PatTokenController::class, 'index'])->middleware('auth.session');

Route::apiResource('users', UserController::class);
Route::apiResource('products', ProductController::class);

// File operations - require PAT token only (permanent token with atgla- prefix)
Route::post('/files/upload', [FileController::class, 'upload'])->middleware('auth.pat');
Route::get('/files/{fileId}', [FileController::class, 'download'])->middleware('auth.pat');

// System registration - requires PAT token only
Route::post('/system-register', [SystemRegisterController::class, 'store'])->middleware('auth.pat');
