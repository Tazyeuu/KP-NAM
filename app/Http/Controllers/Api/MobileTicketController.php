<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\TicketLog;
use Illuminate\Support\Facades\Auth;

class MobileTicketController extends Controller
{
    public function getMyTasks($teknisi_id)
    {
        $tasks = Ticket::whereHas('assignments', function($query) use ($teknisi_id) {
            $query->where('teknisi_id', $teknisi_id);
        })
        ->with(['category', 'department'])
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar tugas.',
            'data' => $tasks
        ], 200);
    }

    public function startTask(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'teknisi_id' => 'required|exists:users,id'
        ]);

        $ticket = Ticket::find($request->ticket_id);

        $ticket->update(['status' => 'In Progress']);
        $ticket->assignments()->update(['started_at' => now()]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => $request->teknisi_id,
            'status_to' => 'In Progress',
            'note' => 'Teknisi: Pekerjaan dimulai oleh teknisi.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan dimulai. Status tiket menjadi In Progress.',
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
        $ticket->assignments()->update(['completed_at' => now()]);

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

    public function updateFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required']);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'FCM Token updated successfully']);
    }

    public static function sendNotificationToTeknisi($user, $title, $body, $ticketId)
    {
        if (!$user->fcm_token) return;

        $messaging = app('firebase.messaging');
        
        $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
            'token' => $user->fcm_token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'ticket_id' => (string) $ticketId,
            ]
        ]);

        $messaging->send($message);
    }
}
