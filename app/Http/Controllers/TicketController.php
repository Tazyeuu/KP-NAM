<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Category;
use App\Models\User;
use App\Models\TicketLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('user')) {
            $tickets = Ticket::where('user_id', $user->id)->with('category')->latest()->get();
            return view('tickets.index', compact('tickets'));
        }

        $tickets = Ticket::with(['user', 'department', 'category'])->latest()->get();
        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('tickets.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'location_detail' => 'required|string|max:255',
            'priority' => 'required|in:Low,Medium,High',
        ]);

        $today = now()->format('Ymd');
        $countToday = Ticket::whereDate('created_at', now()->toDateString())->count();
        $ticketNumber = 'TKT-' . $today . '-' . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);

        Ticket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'department_id' => Auth::user()->department_id,
            'location_detail' => $request->location_detail,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'Open',
        ]);

        return redirect()->route('dashboard')->with('success', 'Tiket pelaporan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $teknisi = User::role('teknisi')->get();
        return view('tickets.show', compact('ticket', 'teknisi'));
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'teknisi_id' => 'required|exists:users,id',
            'note' => 'nullable|string'
        ]);

        // 1. Update status tiket
        $ticket->update(['status' => 'Assigned']);

        // 2. Catat penugasan di tabel assignments
        $ticket->assignments()->create([
            'teknisi_id' => $request->teknisi_id,
            'note' => 'Admin: ' . ($request->note ?? 'Teknisi ditugaskan oleh Admin.'),
            'assigned_at' => now(),
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => Auth::id(),
            'status_to' => 'Assigned',
            'note' => $request->note ?? 'Teknisi ditugaskan oleh Admin.'
        ]);

        return redirect()->back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    public function verify(Ticket $ticket)
    {
        $ticket->update(['is_verified' => true]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => Auth::id(),
            'status_to' => 'Resolved',
            'note' => 'Pelapor: Tiket diverifikasi dan masalah terselesaikan.'
        ]);

        return redirect()->back()->with('success', 'Tiket berhasil diverifikasi.');
    }

    public function rework(Request $request, Ticket $ticket)
    {
        $request->validate([
            'note' => 'required|string'
        ]);

        $lastAssignment = $ticket->assignments()->latest()->first();

        $ticket->update([
            'status' => 'Assigned',
            'is_verified' => false
        ]);

        $ticket->assignments()->create([
            'teknisi_id' => $lastAssignment->teknisi_id,
            'note' => 'Pelapor: ' . $request->note, 
            'assigned_at' => now(),
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => Auth::id(),
            'status_to' => 'Assigned',
            'note' => 'Pelapor: ' . $request->note,
        ]);

        return redirect()->back()->with('success', 'Tiket dikembalikan ke teknisi.');
    }

    public function close(Request $request, Ticket $ticket)
    {
        $request->validate([
            'note' => 'nullable|string'
        ]);

        $ticket->update(['status' => 'Closed']);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => Auth::id(),
            'status_to' => 'Closed',
            'note' => 'Admin: ' . ($request->note ?? 'Tiket ditutup oleh Admin.'),
        ]);

        return redirect()->back()->with('success', 'Tiket berhasil ditutup.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id || $ticket->status !== 'Open') {
            return redirect()->route('tickets.index')->with('error', 'Tiket tidak dapat diedit.');
        }

        $categories = Category::all(); 

        return view('tickets.edit', compact('ticket', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id || $ticket->status !== 'Open') {
            return redirect()->route('tickets.index')->with('error', 'Tiket tidak dapat diedit.');
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|in:Low,Medium,High',
            'description' => 'required|string',
            'location_detail' => 'required|string'
        ]);

        $ticket->update($request->all());

        return redirect()->route('tickets.show', $ticket->id)->with('success', 'Tiket berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id || $ticket->status !== 'Open') {
            return redirect()->route('tickets.index')->with('error', 'Tiket tidak dapat dihapus.');
        }

        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dihapus.');
    }
}
