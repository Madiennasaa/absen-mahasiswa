<?php
use App\Http\Controllers\AttendanceApiController;
use Illmunitae\Support\Facades\Route;

Route::post('/tap-rfid', [AttendanceApiController::class, 'store']);