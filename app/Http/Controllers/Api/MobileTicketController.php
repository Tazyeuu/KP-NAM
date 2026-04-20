<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\TicketLog;

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

    public function resolveTicket(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'teknisi_id' => 'required|exists:users,id',
            'note' => 'nullable|string'
        ]);

        $ticket = Ticket::find($request->ticket_id);

        $ticket->update([
            'status' => 'Resolved'
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => $request->teknisi_id,
            'status_to' => 'Resolved',
            'note' => 'Teknisi: Diselesaikan di lokasi.'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status tiket berhasil diubah menjadi Resolved.',
            'data' => [
                'ticket_number' => $ticket->ticket_number,
                'status' => $ticket->status
            ]
        ], 200);
    }
}
