<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard/chart-data', function (Request $request) {
        $range = $request->query('range', 'all');
        $query = Ticket::query();

        // Logika Filter Rentang Waktu
        if ($range === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($range === 'week') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($range === 'month') {
            $query->where('created_at', '>=', Carbon::now()->subMonth());
        } elseif ($range === 'year') {
            $query->where('created_at', '>=', Carbon::now()->subYear());
        }

        // Eksekusi query untuk mendapatkan data tiket
        $tickets = $query->with(['department', 'category'])->get();

        // Olah data untuk Bar Chart (Kelompokkan berdasarkan nama departemen)
        $deptData = $tickets->groupBy('department.name')->map->count();
        
        // Olah data untuk Pie Chart (Kelompokkan berdasarkan nama kategori)
        $catData = $tickets->groupBy('category.name')->map->count();

        // Kembalikan dalam format JSON yang siap dibaca oleh Chart.js
        return response()->json([
            'departments' => [
                'labels' => $deptData->keys(),
                'values' => $deptData->values(),
            ],
            'categories' => [
                'labels' => $catData->keys(),
                'values' => $catData->values(),
            ]
        ]);
    })->middleware(['auth']);

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

    Route::post('/tickets/{ticket}/verify', [TicketController::class, 'verify'])->name('tickets.verify');
    Route::post('/tickets/{ticket}/rework', [TicketController::class, 'rework'])->name('tickets.rework');

    // Rute Edit dan Hapus Tiket
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

    Route::middleware(['role:admin'])->group(function () {
        Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
        Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    });
});

require __DIR__.'/auth.php';
