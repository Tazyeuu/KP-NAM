<!DOCTYPE html>
<html>
<head>
    <title>Laporan Layanan IT RSUD dr. Soedarso</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { bg-color: #f2f2f2; }
        .status-badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAPITULASI LAYANAN IT</h2>
        <h3>RSUD dr. Soedarso</h3>
        <p>Periode: {{ $start_date }} s/d {{ $end_date }}</p>
    </div>

    <div style="margin-bottom: 20px;">
        <h4 style="margin-bottom: 5px; border-bottom: 1px solid #ccc; padding-bottom: 3px;">A. Ringkasan Statistik</h4>
        
        <p style="margin-bottom: 10px;"><strong>Total Keseluruhan Keluhan:</strong> {{ $totalTickets }} Tiket</p>
        
        <table style="margin-bottom: 15px;">
            <thead>
                <tr>
                    <th style="background-color: #f2f2f2; width: 40%;">Kategori Kendala</th>
                    <th style="background-color: #f2f2f2;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statsCategory as $category => $count)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="background-color: #f2f2f2;">Unit / Departemen</th>
                    <th style="background-color: #f2f2f2; width: 20%; text-align: center;">Total Keluhan</th>
                    <th style="background-color: #f2f2f2; width: 40%;">Rincian per Kategori</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statsDepartment as $dept => $data)
                <tr>
                    <td><strong>{{ $dept }}</strong></td>
                    <td style="text-align: center;"><strong>{{ $data['total'] }}</strong></td>
                    <td>
                        <ul style="margin: 0; padding-left: 15px;">
                            @foreach($data['categories'] as $cat => $count)
                                <li>{{ $cat }}: {{ $count }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h4 style="margin-bottom: 5px; border-bottom: 1px solid #ccc; padding-bottom: 3px;">B. Rincian Data Keluhan</h4>
    <table>
        <thead>
            <tr>
                <th>No Tiket</th>
                <th>Tgl Masuk</th>
                <th>Pelapor/Unit</th>
                <th>Masalah</th>
                <th>Teknisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
            <tr>
                <td>{{ $ticket->ticket_number }}</td>
                <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                <td>{{ $ticket->user->name }} ({{ $ticket->department->name }})</td>
                <td>{{ $ticket->subject }}</td>
                <td>
                    @if($ticket->assignments->isNotEmpty())
                        {{ $ticket->assignments->first()->teknisi->name }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <p>Dicetak pada: {{ $generated_at }}</p>
        <br><br><br>
        <p>(_______________________)</p>
        <p>Admin IT RSUD Dr. Soedarso</p>
    </div>
</body>
</html>