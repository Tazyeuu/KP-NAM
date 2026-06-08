<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-2 mb-6">
                &larr; Kembali ke Dashboard
            </a>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 shadow-sm rounded-r-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <p class="font-bold text-sm">Berhasil!</p>
                        <p class="text-xs">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <div class="mb-8">
                <p class="text-xs font-mono text-gray-400">#{{ strtoupper(substr($ticket->ticket_number, -6)) }}</p>
                <h1 class="text-3xl font-bold text-gray-900 mt-1">{{ $ticket->subject }}</h1>
                <div class="flex gap-2 mt-4">
                    @php
                        $statusBadge = match($ticket->status) {
                            'Open' => 'bg-blue-50/50 border border-blue-100 text-blue-500',
                            'Assigned' => 'bg-orange-50/50 text-orange-500 border-orange-200',
                            'In Progress' => 'bg-pink-50/50 text-pink-500 border-pink-100',
                            'Resolved' => 'bg-green-50/50 border border-green-100 text-green-500',
                            'Closed' => 'bg-gray-50 text-gray-600 border-gray-200',
                        };
                    @endphp
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-md uppercase {{ $statusBadge }}">
                        {{ $ticket->status }}
                    </span>
                    <span class="px-3 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-100">
                        {{ $ticket->category->name }}
                    </span>
                </div>
            </div>

            @if(Auth::id() == $ticket->user_id && $ticket->status == 'Open')
                <div class="flex items-center gap-3 mb-6 border-t border-gray-100">
                    <a href="{{ route('tickets.edit', $ticket->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 shadow-sm hover:bg-gray-50 transition">
                        Edit Laporan
                    </a>
                    
                    <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus laporan ini? Data yang dihapus tidak dapat dikembalikan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-200 rounded-md font-semibold text-xs text-red-600 shadow-sm hover:bg-red-100 transition">
                            Hapus Laporan
                        </button>
                    </form>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-800 mb-4">Deskripsi Kendala</h3>
                        <p class="text-gray-600 leading-relaxed">{{ $ticket->description }}</p>
                    </div>
                    
                    @if(Auth::user()->hasRole('admin') && $ticket->status == 'Open')
                        <div class="bg-white rounded-xl shadow-sm border-l-4 border-l-blue-500 border border-gray-100 p-6">
                            <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-6">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Tugaskan Teknisi
                            </h3>
                            
                            <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-s font-bold text-gray-700 mb-1">Pilih Teknisi</label>
                                    <select name="teknisi_id" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500">
                                        <option value="">Pilih teknisi...</option>
                                        @foreach($teknisi as $it)
                                            <option value="{{ $it->id }}">{{ $it->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-s font-bold text-gray-700 mb-1">Catatan Admin</label>
                                    <textarea name="note" rows="3" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500" placeholder="Tambahkan catatan untuk teknisi..."></textarea>
                                </div>

                                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition shadow-lg shadow-blue-100">
                                    Tugaskan Teknisi
                                </button>
                            </form>
                        </div>
                    @elseif(Auth::user()->hasRole('user') && $ticket->status == 'Open')
                        <div class="p-6 bg-blue-50 border-l-4 border-blue-400">
                            <h3 class="font-bold text-blue-700">Tiket Dibuat</h3>
                            <p class="text-sm text-blue-500">Tiket berhasil dibuat dan dikirim ke Tim IT. Menunggu penugasan Teknisi.</p>
                        </div>
                    @endif

                    @if($ticket->status == 'Assigned')
                        <div class="p-6 bg-blue-50 border-l-4 border-blue-400">
                            <h3 class="font-bold text-blue-700">Teknisi Ditugaskan</h3>
                            <p class="text-sm text-blue-500">Teknisi telah ditugaskan dan sedang dalam perjalanan menuju ke lokasi.</p>
                        </div>

                        @php
                            $assignment = $ticket->assignments->last();
                        @endphp
                        
                        <div class="bg-white/60 rounded-lg p-4 border border-blue-100 space-y-3 text-sm">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1">
                                <span class="text-blue-600 font-semibold">Teknisi Bertugas</span>
                                <span class="md:col-span-2 text-gray-800 font-medium">: {{ $assignment->teknisi->name }}</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1">
                                <span class="text-blue-600 font-semibold">Waktu Ditugaskan</span>
                                <span class="md:col-span-2 text-gray-800">: {{ Carbon\Carbon::parse($assignment->assigned_at)->format('d M Y, H:i') }} WIB</span>
                            </div>
                        </div>
                    @endif

                    @if($ticket->status == 'In Progress')
                        <div class="p-6 bg-blue-50 border-l-4 border-blue-400">
                            <h3 class="font-bold text-blue-700">Sedang Dikerjakan</h3>
                            <p class="text-sm text-blue-500">Saat ini teknisi sedang dalam proses perbaikan/pengecekan di lokasi.</p>
                        </div>

                        @php
                            $assignment = $ticket->assignments->last();
                        @endphp
                        
                        <div class="bg-white/60 rounded-lg p-4 border border-blue-100 space-y-3 text-sm">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1">
                                <span class="text-blue-600 font-semibold">Teknisi Bertugas</span>
                                <span class="md:col-span-2 text-gray-800 font-medium">: {{ $assignment->teknisi->name }}</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1">
                                <span class="text-blue-600 font-semibold">Waktu Mulai</span>
                                <span class="md:col-span-2 text-gray-800">: {{ Carbon\Carbon::parse($assignment->started_at)->format('d M Y, H:i') }} WIB</span>
                            </div>
                        </div>
                    @endif

                    @if($ticket->status == 'Resolved')
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                            {{-- JIKA YANG LOGIN ADALAH PELAPOR (USER) DAN BELUM DIVERIFIKASI --}}
                            @if(Auth::user()->hasRole('user') && !$ticket->is_verified)
                                <div class="p-6 bg-yellow-50 border-b border-yellow-100">
                                    <h3 class="font-bold text-yellow-800 flex items-center gap-2">Verifikasi Diperlukan</h3>
                                    <p class="text-sm text-yellow-700 mt-1">Teknisi melaporkan kendala telah diatasi. Mohon periksa kembali. Apakah sistem/alat sudah berfungsi normal?</p>
                                </div>
                                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <form action="{{ route('tickets.rework', $ticket) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <textarea name="note" rows="2" required class="w-full border-red-200 rounded-md text-sm" placeholder="Jelaskan apa yang masih error..."></textarea>
                                        <button type="submit" class="w-full bg-white border border-red-500 text-red-600 hover:bg-red-50 font-bold py-2 rounded-md transition text-sm">Masih Error</button>
                                    </form>
                                    <form action="{{ route('tickets.verify', $ticket) }}" method="POST" class="flex flex-col justify-end">
                                        @csrf
                                        <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2 rounded-md transition text-sm h-[42px]">Ya, Masalah Selesai</button>
                                    </form>
                                </div>

                            {{-- JIKA YANG LOGIN ADMIN --}}
                            @elseif(Auth::user()->hasRole('admin'))
                                
                                @if(!$ticket->is_verified)
                                    <div class="p-6 bg-gray-50 border-l-4 border-gray-400">
                                        <h3 class="font-bold text-gray-700">Menunggu Verifikasi</h3>
                                        <p class="text-sm text-gray-500">Tiket ini telah diselesaikan oleh Teknisi, namun belum diverifikasi oleh pelapor. TIket belum bisa ditutup.</p>
                                    </div>
                                @else
                                    <div class="p-6 bg-emerald-50 border-l-4 border-emerald-500">
                                        <h3 class="font-bold text-emerald-800 mb-2">Pelapor Telah Memverifikasi</h3>
                                        <p class="text-sm text-emerald-700 mb-4">Pelapor mengonfirmasi bahwa kendala sudah teratasi. Tiket dapat ditutup.</p>
                                        
                                        <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                                            @csrf
                                            <textarea name="note" rows="2" class="w-full border-emerald-200 rounded-md text-sm mb-3" placeholder="Catatan penutupan (opsional)..."></textarea>
                                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 rounded-md transition">Tutup Tiket</button>
                                        </form>
                                    </div>
                                @endif

                            {{-- JIKA YANG LOGIN PELAPOR, TAPI SUDAH DIVERIFIKASI --}}
                            @elseif(Auth::user()->hasRole('user') && $ticket->is_verified)
                                <div class="p-6 bg-blue-50 border-l-4 border-blue-400">
                                    <h3 class="font-bold text-blue-700">Masalah Terselesaikan</h3>
                                    <p class="text-sm text-blue-500 font-semibold">Anda telah memverifikasi penyelesaian tiket ini.</p>
                                </div>
                            @endif
                        </div>

                        @php
                            $assignment = $ticket->assignments->last();
                        @endphp

                        <div class="bg-white/60 rounded-lg p-4 border border-blue-100 space-y-3 text-sm">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1">
                                <span class="text-blue-600 font-semibold">Teknisi Bertugas</span>
                                <span class="md:col-span-2 text-gray-800 font-medium">: {{ $assignment->teknisi->name }}</span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1">
                                <span class="text-blue-600 font-semibold">Waktu Selesai</span>
                                <span class="md:col-span-2 text-gray-800">: {{ Carbon\Carbon::parse($assignment->completed_at)->format('d M Y, H:i') }} WIB</span>
                            </div>
                            @if(!$ticket->is_verified)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-1">
                                <span class="text-blue-600 font-semibold">Catatan Teknisi</span>
                                <span class="md:col-span-2 text-gray-800">: {{ $ticket->logs->last()->note ?? '-' }}</span>
                            </div>
                            @endif
                        </div>
                    @endif

                    @if($ticket->status == 'Closed')
                        <div class="p-6 bg-gray-50 border-l-4 border-gray-400">
                            <h3 class="font-bold text-gray-700">Tiket Ditutup</h3>
                            <p class="text-sm text-gray-500">Tiket ini telah ditutup.</p>
                        </div>
                    @endif

                    <!-- TRACKER PERJALANAN TIKET -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-8">
                        <h3 class="font-bold text-gray-800 mb-6">Riwayat Perjalanan Tiket</h3>
                        
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                
                                {{-- 1. TIKET DIBUAT (Selalu Tampil) --}}
                                <li>
                                    <div class="relative pb-8 group">
                                        <span class="absolute top-8 left-4 -ml-[1px] h-full w-[2px] bg-gray-200 group-last:hidden" aria-hidden="true"></span>
                                        <div class="relative flex items-start space-x-4">
                                            <div>
                                                <div class="relative px-1">
                                                    <div class="h-8 w-8 bg-blue-500 rounded-full ring-4 ring-white flex items-center justify-center shadow-sm">
                                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1 flex flex-col gap-1 pb-2 mt-1">
                                                <div><span class="px-2 py-1 bg-blue-100 text-blue-700 text-[11px] font-bold rounded">Open</span></div>
                                                <h4 class="text-sm font-bold text-gray-900 mt-1">Tiket laporan berhasil dibuat</h4>
                                                <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    {{ $ticket->created_at->format('d M Y, H:i:s') }} WIB
                                                </div>
                                                <div class="text-xs text-gray-500 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                                    Oleh: {{ $ticket->user->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                {{-- LOOPING RIWAYAT PENUGASAN TEKNISI (Akan berulang jika ada Rework) --}}
                                @foreach($ticket->assignments as $index => $assignment)
                                    
                                    {{-- 2. TEKNISI DITUGASKAN / REWORK --}}
                                    <li>
                                        <div class="relative pb-8 group">
                                            <span class="absolute top-8 left-4 -ml-[1px] h-full w-[2px] bg-gray-200 group-last:hidden" aria-hidden="true"></span>
                                            <div class="relative flex items-start space-x-4">
                                                <div>
                                                    <div class="relative px-1">
                                                        <div class="h-8 w-8 {{ $index == 0 ? 'bg-orange-500' : 'bg-red-500' }} rounded-full ring-4 ring-white flex items-center justify-center shadow-sm">
                                                            @if($index == 0)
                                                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                </svg>
                                                            @else
                                                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1 flex flex-col gap-1 pb-2 mt-1">
                                                    <div>
                                                        <span class="px-2 py-1 bg-orange-100 text-orange-700 text-[11px] font-bold rounded">Assigned</span>
                                                    </div>
                                                    <h4 class="text-sm font-bold text-gray-900 mt-1">
                                                        @if($index == 0)
                                                            Teknisi telah ditugaskan oleh Admin
                                                        @else
                                                            Pelapor meminta perbaikan ulang
                                                        @endif
                                                    </h4>
                                                    <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        {{ \Carbon\Carbon::parse($assignment->assigned_at)->format('d M Y, H:i:s') }} WIB
                                                    </div>
                                                    <div class="text-xs text-gray-500 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Teknisi: {{ $assignment->teknisi->name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    {{-- 3. SEDANG DIKERJAKAN --}}
                                    @if($assignment->started_at)
                                    <li>
                                        <div class="relative pb-8 group">
                                            <span class="absolute top-8 left-4 -ml-[1px] h-full w-[2px] bg-gray-200 group-last:hidden" aria-hidden="true"></span>
                                            <div class="relative flex items-start space-x-4">
                                                <div>
                                                    <div class="relative px-1">
                                                        <div class="h-8 w-8 bg-pink-500 rounded-full ring-4 ring-white flex items-center justify-center shadow-sm">
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1 flex flex-col gap-1 pb-2 mt-1">
                                                    <div><span class="px-2 py-1 bg-pink-100 text-pink-700 text-[11px] font-bold rounded">In Progress</span></div>
                                                    <h4 class="text-sm font-bold text-gray-900 mt-1">Proses perbaikan/pengecekan dimulai</h4>
                                                    <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        {{ \Carbon\Carbon::parse($assignment->started_at)->format('d M Y, H:i:s') }} WIB
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endif

                                    {{-- 4. SELESAI DIKERJAKAN --}}
                                    @if($assignment->completed_at)
                                    <li>
                                        <div class="relative pb-8 group">
                                            <span class="absolute top-8 left-4 -ml-[1px] h-full w-[2px] bg-gray-200 group-last:hidden" aria-hidden="true"></span>
                                            <div class="relative flex items-start space-x-4">
                                                <div>
                                                    <div class="relative px-1">
                                                        <div class="h-8 w-8 bg-green-500 rounded-full ring-4 ring-white flex items-center justify-center shadow-sm">
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1 flex flex-col gap-1 pb-2 mt-1">
                                                    <div><span class="px-2 py-1 bg-green-100 text-green-700 text-[11px] font-bold rounded">Resolved</span></div>
                                                    <h4 class="text-sm font-bold text-gray-900 mt-1">Teknisi telah menyelesaikan perbaikan</h4>
                                                    <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        {{ \Carbon\Carbon::parse($assignment->completed_at)->format('d M Y, H:i:s') }} WIB
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endif

                                @endforeach

                                {{-- 5. TIKET DIVERIFIKASI (Oleh Pelapor) --}}
                                @if($ticket->is_verified)
                                    @php
                                        // Mencari data log spesifik saat verifikasi dilakukan
                                        $verifyLog = $ticket->logs->first(function ($log) {
                                            return str_contains(strtolower($log->note), 'diverifikasi');
                                        });
                                    @endphp
                                    <li>
                                        <div class="relative pb-8 group">
                                            <span class="absolute top-8 left-4 -ml-[1px] h-full w-[2px] bg-gray-200 group-last:hidden" aria-hidden="true"></span>
                                            <div class="relative flex items-start space-x-4">
                                                <div>
                                                    <div class="relative px-1">
                                                        <div class="h-8 w-8 bg-emerald-500 rounded-full ring-4 ring-white flex items-center justify-center shadow-sm">
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1 flex flex-col gap-1 pb-2 mt-1">
                                                    <div><span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-[11px] font-bold rounded">Verified</span></div>
                                                    <h4 class="text-sm font-bold text-gray-900 mt-1">Pelapor mengonfirmasi masalah telah teratasi</h4>
                                                    <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        {{ \Carbon\Carbon::parse($verifyLog->created_at)->format('d M Y, H:i:s') }} WIB
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                                {{-- 6. TIKET DITUTUP --}}
                                @if($ticket->status == 'Closed')
                                    <li>
                                        <div class="relative group">
                                            <span class="absolute top-8 left-4 -ml-[1px] h-full w-[2px] bg-gray-200 group-last:hidden" aria-hidden="true"></span>
                                            <div class="relative flex items-start space-x-4">
                                                <div>
                                                    <div class="relative px-1">
                                                        <div class="h-8 w-8 bg-gray-800 rounded-full ring-4 ring-white flex items-center justify-center shadow-sm">
                                                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1 flex flex-col gap-1 mt-1">
                                                    <div><span class="px-2 py-1 bg-gray-100 text-gray-700 text-[11px] font-bold rounded">Closed</span></div>
                                                    <h4 class="text-sm font-bold text-gray-900 mt-1">Tiket resmi ditutup</h4>
                                                    <div class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        {{ $ticket->updated_at->format('d M Y, H:i:s') }} WIB
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif

                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-fit">
                    <h3 class="font-bold text-gray-800 mb-6">Informasi Tiket</h3>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="text-gray-400 mt-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                            <div>
                                <p class="text-xs text-gray-400">Pelapor</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $ticket->user->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="text-gray-400 mt-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
                            <div>
                                <p class="text-xs text-gray-400">Email</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $ticket->user->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="text-gray-400 mt-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>
                            <div>
                                <p class="text-xs text-gray-400">Departemen</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $ticket->department->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="text-gray-400 mt-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg></div>
                            <div>
                                <p class="text-xs text-gray-400">Detail Lokasi</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $ticket->location_detail }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="text-gray-400 mt-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                            <div>
                                <p class="text-xs text-gray-400">Tanggal Laporan</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $ticket->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>