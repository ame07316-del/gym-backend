<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;

Route::post('/bookings', [BookingController::class, 'store']);
