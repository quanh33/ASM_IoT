<?php

use App\Http\Controllers\SensorDataController;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::post('/sensor-data', [SensorDataController::class, 'store']);
