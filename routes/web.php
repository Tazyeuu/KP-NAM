<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute untuk melihat form pembuatan tiket (Staf RS)
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    // Rute untuk menyimpan tiket ke database
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    // Rute untuk melihat daftar tiket (baik untuk histori user maupun antrean admin)
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    
    // Rute untuk melihat detail 1 tiket
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::post('/tickets/{ticket}/rework', [TicketController::class, 'rework'])->name('tickets.rework');
});

require __DIR__.'/auth.php';
