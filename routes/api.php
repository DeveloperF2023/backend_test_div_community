<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\NotificationController;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/students', [StudentController::class, 'store']);
Route::get('/classrooms', [ClassroomController::class, 'index']);
Route::get('/attendances', [AttendanceController::class, 'index']);
Route::post('/attendances', [AttendanceController::class, 'store']);
Route::put('/attendances/{id}', [AttendanceController::class, 'update']);
Route::get('/students', [StudentController::class, 'index']);
Route::get('/students/filter', [StudentController::class, 'filter']);
Route::get('/students/gender/{gender}', [StudentController::class, 'getByGender']);
Route::post('/attendance/students', [AttendanceController::class, 'storeMultipleAttendance']);
Route::get('/attendances/date/{date}', [AttendanceController::class, 'getByDate']);
Route::get('/attendances/count/{date}', [AttendanceController::class, 'countByStatusForDate']);
Route::get('/students/{id}', [StudentController::class, 'show']);
Route::get('/attendances/status/monthly/student/{id}', [AttendanceController::class, 'getMonthlyAttendanceStatusByStudent']);
Route::get('/notifications', [NotificationController::class, 'index']);
