<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use \App\Models\Assignment;

class DashboardController extends Controller
{
    public function index() {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $openTickets = Ticket::where('status', 'Open')->count();
            $assignedTickets = Ticket::where('status', 'Assigned')->count();
            $inProgressTickets = Ticket::where('status', 'In Progress')->count();
            $resolvedTickets = Ticket::where('status', 'Resolved')->count();
            $closedTickets = Ticket::where('status', 'Closed')->count();

            $tickets = Ticket::with(['user', 'department', 'category'])->latest()->get();

            $activeWorkers = Assignment::with(['teknisi', 'ticket.department'])
                ->whereHas('ticket', function($q) {
                    $q->whereIn('status', ['Assigned', 'In Progress'])
                    ->orWhere(function($q2) {
                        $q2->where('status', 'Resolved')
                            ->where('is_verified', false);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('teknisi_id');

            return view('dashboard', compact(
                'openTickets', 'assignedTickets', 'inProgressTickets', 'resolvedTickets', 'closedTickets', 'tickets', 'activeWorkers'
            ));
        }

        elseif ($user->hasRole('teknisi')) {
            $completedTickets = Ticket::whereHas('assignments', function($query) use ($user) {
                $query->where('teknisi_id', $user->id);
            })
            ->whereIn('status', ['Resolved', 'Closed'])
            ->with(['user', 'department', 'category'])
            ->latest()
            ->get();

            return view('dashboard', compact('completedTickets'));
        }

        else {
            $userTickets = Ticket::where('user_id', $user->id)
                                ->with('category')
                                ->latest()
                                ->take(5) 
                                ->get();

            return view('dashboard', compact('userTickets'));
        }
    }
}
