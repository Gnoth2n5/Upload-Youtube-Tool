<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UpVideoYTController;
use App\Http\Controllers\GoogleOAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::resource('upvideoyt', UpVideoYTController::class);

    Route::get('/auth/google', [GoogleOAuthController::class, 'auth'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleOAuthController::class, 'callback'])->name('auth.google.callback');
});

require __DIR__ . '/auth.php';
