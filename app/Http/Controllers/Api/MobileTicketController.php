<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class MobileTicketController extends Controller
{
    public function getMyTasks($teknisi_id)
    {
        $tasks = Ticket::whereHas('assignments', function($query) use ($teknisi_id) {
            $query->where('teknisi_id', $teknisi_id);
        })
        ->where('status', 'In Progress')
        ->with(['category', 'department'])
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar tugas.',
            'data' => $tasks
        ], 200);
    }
}
