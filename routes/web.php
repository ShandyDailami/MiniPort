<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'view'])->middleware('auth');

Route::get('/login', [LoginController::class, 'view'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);

Route::get('/register', [RegisterController::class, 'view'])->name('register');
Route::post('/register', [RegisterController::class, 'regist']);
