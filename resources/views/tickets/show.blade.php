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