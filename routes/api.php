<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\MobileTicketController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/mobile/tasks/{technician_id}', [MobileTicketController::class, 'getMyTasks']);