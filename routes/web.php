<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('tasks', 'tasks')->name('tasks');
    Route::view('statistics', 'statistics')->name('statistics');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
