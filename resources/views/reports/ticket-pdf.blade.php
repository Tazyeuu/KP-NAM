<!DOCTYPE html>
<html>
<head>
    <title>Laporan Layanan IT RSUD dr. Soedarso</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; } /* Ukuran font diperkecil sedikit agar 7 kolom muat */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
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
                    <th style="width: 40%;">Kategori Kendala</th>
                    <th>Total</th>
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
                    <th>Unit / Departemen</th>
                    <th style="width: 20%; text-align: center;">Total Keluhan</th>
                    <th style="width: 40%;">Rincian per Kategori</th>
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
                <th style="width: 12%;">Waktu Masuk</th>
                <th style="width: 18%;">Pelapor / Unit</th>
                <th style="width: 20%;">Masalah</th>
                <th style="width: 10%;">Teknisi</th>
                <th style="width: 12%;">Waktu Mulai</th>
                <th style="width: 12%;">Waktu Selesai</th>
                <th style="width: 16%;">Durasi Penyelesaian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
                @php
                    // Mengambil data penugasan TERAKHIR (antisipasi jika ada Rework)
                    $assignment = $ticket->assignments->last();
                    
                    $startedAt = $assignment && $assignment->started_at ? \Carbon\Carbon::parse($assignment->started_at) : null;
                    $completedAt = $assignment && $assignment->completed_at ? \Carbon\Carbon::parse($assignment->completed_at) : null;
                    $createdAt = \Carbon\Carbon::parse($ticket->created_at);
                    
                    $durasiString = '-';
                    
                    // Hitung durasi menggunakan total menit lalu dibagi manual (LEBIH AMAN)
                    if ($completedAt) {
                        // Dapatkan total seluruh menit dari awal sampai akhir
                        $totalMinutes = $createdAt->diffInMinutes($completedAt);
                        
                        // Bagi 60 dan bulatkan ke bawah untuk dapatkan angka Jam bulat
                        $hours = (int) floor($totalMinutes / 60);
                        
                        // Sisa bagi (modulus) 60 untuk dapatkan sisa Menit
                        $minutes = $totalMinutes % 60;
                        
                        $durasiString = $hours . ' jam ' . $minutes . ' menit';
                    }
                @endphp
            <tr>
                <td>{{ $createdAt->format('d/m/Y H:i') }} WIB</td>
                <td>{{ $ticket->user->name }}<br><span style="font-size: 9px; color: #555;">({{ $ticket->department->name }})</span></td>
                <td>{{ $ticket->subject }}</td>
                <td>
                    {{ $assignment ? $assignment->teknisi->name : '-' }}
                </td>
                <td>
                    {{ $startedAt ? $startedAt->format('d/m/Y H:i') . ' WIB' : '-' }}
                </td>
                <td>
                    {{ $completedAt ? $completedAt->format('d/m/Y H:i') . ' WIB' : '-' }}
                </td>
                <td>
                    <strong>{{ $durasiString }}</strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right;">
        <p>Dicetak pada: {{ \Carbon\Carbon::parse($generated_at)->format('d F Y, H:i') }} WIB</p>
        <br><br><br>
        <p>(_______________________)</p>
        <p>Admin IT RSUD dr. Soedarso</p>
    </div>
</body>
</html>