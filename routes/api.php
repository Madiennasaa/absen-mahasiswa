<?php
use App\Http\Controllers\AttendanceApiController;
use App\Http\Middleware\ValidateDeviceKey;
use Illuminate\Support\Facades\Route;

Route::post('/tap-rfid', [AttendanceApiController::class, 'store'])->middleware(ValidateDeviceKey::class);