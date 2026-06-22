<?php
use App\Http\Controllers\AttendanceApiController;
use App\Http\Middleware\ValidateDeviceKey;
use Illuminate\Support\Facades\Route;

Route::post('/tap-rfid', [\App\Http\Controllers\AttendanceApiController::class, 'store'])->middleware(\App\Http\Middleware\ValidateDeviceKey::class);
Route::post('/device-status', [\App\Http\Controllers\AttendanceApiController::class, 'updateDeviceStatus'])->middleware(\App\Http\Middleware\ValidateDeviceKey::class);
Route::get('/device-status', [\App\Http\Controllers\AttendanceApiController::class, 'getDeviceStatus'])->middleware(\App\Http\Middleware\ValidateDeviceKey::class);