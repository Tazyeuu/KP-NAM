<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Category;
use App\Models\User;
use App\Models\TicketLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tickets = Ticket::all();
        return view('tickets.index', compact('tickets'));;
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
            'location_detail' => 'nullable|string|max:255',
            'priority' => 'required|in:Low,Medium,High',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('tickets', 'public');
        }

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
            'image_path' => $imagePath,
            'status' => 'Open',
        ]);

        return redirect()->route('dashboard')->with('success', 'Tiket pelaporan berhasil dikirim ke Tim IT!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        // Ambil daftar user yang memiliki role 'teknisi'
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
        $ticket->update(['status' => 'In Progress']);

        // 2. Catat penugasan di tabel assignments
        $ticket->assignments()->create([
            'teknisi_id' => $request->teknisi_id,
            'note' => $request->note ?? 'Teknisi ditugaskan oleh Admin.',
            'assigned_at' => now(),
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => Auth::id(),
            'status_to' => 'In Progress',
            'note' => 'Teknisi ditugaskan oleh Admin.'
        ]);

        // 3. KIRIM TUGAS KE APLIKASI MOBILE (Proyek Temanmu)
        // Asumsikan temanmu punya endpoint API atau menggunakan Firebase (FCM)
        /*try {
            Http::post('https://api-mobile-temanmu.com/v1/send-task', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->subject,
                'description' => $ticket->description,
                'department_lat' => $ticket->department->latitude,
                'department_long' => $ticket->department->longitude,
                'teknisi_id' => $request->teknisi_id,
            ]);
        } catch (\Exception $e) {
            // Log error jika API mobile tidak bisa dijangkau
        }*/

        return redirect()->back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    public function verify(Ticket $ticket)
    {
        $ticket->update(['is_verified' => true]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => Auth::id(),
            'status_to' => 'Resolved',
            'note' => 'Tiket diverifikasi dan masalah terselesaikan.'
        ]);

        return redirect()->back()->with('success', 'Tiket berhasil diverifikasi.');
    }

    public function rework(Request $request, Ticket $ticket)
    {
        $request->validate([
            'note' => 'required|string'
        ]);

        $ticket->update([
            'status' => 'In Progress',
            'is_verified' => false
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'changed_by' => Auth::id(),
            'status_to' => 'In Progress',
            'note' => 'Pelapor: ' . $request->note,
        ]);

        /* try {
            Http::post('https://api-mobile-rekanmu.com/v1/notifikasi-rework', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'note' => $request->note, // Catatan mengapa dikembalikan
            ]);
        } catch (\Exception $e) { } */

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
            'note' => $request->note ?? 'Tiket ditutup oleh Admin.',
        ]);

        return redirect()->back()->with('success', 'Tiket berhasil ditutup.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }
}
