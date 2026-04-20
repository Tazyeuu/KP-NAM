<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\MobileTicketController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/mobile/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    
    // Endpoint: GET http://it-helpdesk-rsud.test:8080/api/mobile/tasks/{technician_id}
    Route::get('/mobile/tasks/{technician_id}', [MobileTicketController::class, 'getMyTasks']);
    
    // Endpoint: POST http://it-helpdesk-rsud.test:8080/api/mobile/tickets/resolve
    Route::post('/mobile/tickets/resolve', [MobileTicketController::class, 'resolveTicket']);

    // Rute untuk mengecek profile/token masih valid atau tidak
    Route::get('/mobile/user', function (Request $request) {
        return $request->user();
    });
});