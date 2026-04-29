<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $tickets = Ticket::with(['user', 'department', 'category', 'assignments.teknisi'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->get();

        $totalTickets = $tickets->count();

        $statsCategory = $tickets->groupBy(function($ticket) {
            return $ticket->category->name;
        })->map->count();

        $statsDepartment = $tickets->groupBy(function($ticket) {
            return $ticket->department->name;
        })->map(function ($deptTickets) {
            return [
                'total' => $deptTickets->count(),
                'categories' => $deptTickets->groupBy(function($ticket) {
                    return $ticket->category->name;
                })->map->count()
            ];
        });

        $data = [
            'tickets' => $tickets,
            'start_date' => Carbon::parse($startDate)->format('d M Y'),
            'end_date' => Carbon::parse($endDate)->format('d M Y'),
            'generated_at' => Carbon::now()->format('d M Y, H:i'),
            'totalTickets' => $totalTickets,
            'statsCategory' => $statsCategory,
            'statsDepartment' => $statsDepartment,
        ];

        $pdf = Pdf::loadView('reports.ticket-pdf', $data);
        return $pdf->download('Laporan-Layanan-IT-' . $startDate . '-to-' . $endDate . '.pdf');
    }
}
