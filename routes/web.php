<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BucketController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', [UserController::class, 'view'])->middleware('auth');
Route::get('/', [UserController::class, 'dashboardView'])->middleware('auth');

Route::get('/buckets', [BucketController::class, 'index'])->middleware('auth');
Route::get('/bucket/create', [BucketController::class, 'create'])->middleware('auth');
Route::post('/bucket/create', [BucketController::class, 'store'])->middleware('auth');

Route::get('/bucket/{bucket}', [BucketController::class, 'show'])->middleware('auth');
Route::post('/bucket/{bucket}/objects', [BucketController::class, 'uploadObject'])->middleware('auth');
Route::get('/bucket/{bucket}/objects/download', [BucketController::class, 'downloadObject'])->middleware('auth');
Route::delete('/bucket/{bucket}/objects', [BucketController::class, 'deleteObject'])->middleware('auth');
Route::get('/bucket/{bucket}/objects/share', [BucketController::class, 'shareObject'])->middleware('auth');

Route::delete('/bucket/{bucket}', [BucketController::class, 'destroy'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/credentials', [CredentialController::class, 'index']);
    Route::post('/credentials', [CredentialController::class, 'store']);
    Route::patch('/credentials/{id}/revoke', [CredentialController::class, 'revoke']);
});

Route::get('/login', [AuthController::class, 'loginView'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/register', [AuthController::class, 'registView'])->name('register');
Route::post('/register', [AuthController::class, 'regist']);
