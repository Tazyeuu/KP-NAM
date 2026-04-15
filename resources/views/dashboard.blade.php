<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @role('admin')
            <x-slot name="header">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Admin</h2>
                </div>
            </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex justify-between items-center">
                        <div>
                            <h4 class="text-blue-500 text-sm font-semibold mb-1">Open</h4>
                            <p class="text-2xl font-bold text-gray-800">{{ $openTickets }}</p>
                        </div>
                        <div class="p-2 bg-blue-100 text-blue-500 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    </div>
                    <div class="bg-pink-50/50 border border-pink-100 rounded-xl p-4 flex justify-between items-center">
                        <div>
                            <h4 class="text-pink-500 text-sm font-semibold mb-1">In Progress</h4>
                            <p class="text-2xl font-bold text-gray-800">{{ $inProgressTickets }}</p>
                        </div>
                        <div class="p-2 bg-pink-100 text-pink-500 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </div>
                    </div>
                    <div class="bg-green-50/50 border border-green-100 rounded-xl p-4 flex justify-between items-center">
                        <div>
                            <h4 class="text-green-500 text-sm font-semibold mb-1">Resolved</h4>
                            <p class="text-2xl font-bold text-gray-800">{{ $resolvedTickets }}</p>
                        </div>
                        <div class="p-2 bg-green-100 text-green-500 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-4 flex justify-between items-center">
                        <div>
                            <h4 class="text-gray-500 text-sm font-semibold mb-1">Closed</h4>
                            <p class="text-2xl font-bold text-gray-800">{{ $closedTickets }}</p>
                        </div>
                        <div class="p-2 bg-gray-200 text-gray-500 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-3 mb-6">
                    <select class="border-gray-200 rounded-lg text-sm text-gray-600 focus:ring-blue-500">
                        <option>Semua Status</option>
                        <option>Open</option>
                        <option>In Progress</option>
                        <option>Resolved</option>
                        <option>Closed</option>
                    </select>
                    <select class="border-gray-200 rounded-lg text-sm text-gray-600 focus:ring-blue-500">
                        <option>Semua Kategori</option>
                        <option value="">Jaringan</option>
                        <option value="">Software</option>
                        <option value="">Hardware</option>
                    </select>
                    <select class="border-gray-200 rounded-lg text-sm text-gray-600 focus:ring-blue-500">
                        <option>Semua Prioritas</option>
                        <option>High</option>
                        <option>Medium</option>
                        <option>Low</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @forelse($tickets as $ticket)
                        <a href="{{ route('tickets.show', $ticket->id) }}" class="block">
                            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-xs text-gray-400">{{ $ticket->ticket_number }}</span>
                                    {{-- Logika Warna Badge Status --}}
                                    @php
                                        $statusColor = match($ticket->status) {
                                            'Open' => 'bg-blue-50/50 border border-blue-100 text-blue-500',
                                            'In Progress' => 'bg-pink-50/50 text-pink-500 border-pink-100',
                                            'Resolved' => 'bg-green-50/50 border border-green-100 text-green-500',
                                            'Closed' => 'bg-gray-50 text-gray-600 border-gray-200',
                                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                                        };
                                    @endphp
                                    <span class="{{ $statusColor }} border text-xs px-2.5 py-1 rounded-md font-semibold">
                                        {{ $ticket->status }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-gray-800 text-base mb-1">{{ $ticket->subject }}</h3>
                                <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $ticket->description }}</p>
                                
                                <div class="flex gap-2 mb-4">
                                    <span class="bg-indigo-50 text-indigo-600 border border-indigo-100 text-xs px-2 py-1 rounded">{{ $ticket->category->name }}</span>
                                    {{-- Logika Warna Badge Prioritas --}}
                                    @php
                                        $priorityColor = match($ticket->priority) {
                                            'High' => 'bg-red-50 text-red-600 border-red-100',
                                            'Medium' => 'bg-orange-50 text-orange-600 border-orange-100',
                                            'Low' => 'bg-slate-50 text-slate-600 border-slate-100',
                                        };
                                    @endphp
                                    <span class="{{ $priorityColor }} border text-xs px-2 py-1 rounded">
                                        {{ $ticket->priority }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between items-center text-xs text-gray-400 mt-4 pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <span class="flex items-center gap-1">{{ $ticket->user->name }}</span>
                                        <span class="flex items-center gap-1">{{ $ticket->department->name }}</span>
                                    </div>
                                    <span class="flex items-center gap-1">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full bg-gray-50 p-8 text-center rounded-xl border border-dashed border-gray-300">
                            <p class="text-gray-500">Belum ada tiket laporan yang masuk saat ini.</p>
                        </div>
                    @endforelse
                </div>
            @elserole('teknisi')
                <x-slot name="header">
                    <div class="flex justify-between items-center">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Tugas</h2>
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @forelse($completedTickets as $ticket)
                        <div class="bg-green-50/30 border border-green-100 rounded-xl p-5 shadow-sm">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs text-gray-400 font-mono">{{ $ticket->ticket_number }}</span>
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded font-bold">{{ $ticket->status }}</span>
                            </div>
                            <h3 class="font-bold text-gray-800">{{ $ticket->subject }}</h3>
                            <p class="text-xs text-gray-500 mt-2">Pelapor: {{ $ticket->user->name }} ({{ $ticket->department->name }})</p>
                            <p class="text-xs text-gray-400 mt-1">Selesai pada: {{ $ticket->updated_at->format('d M Y') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 italic">Belum ada tugas yang diselesaikan di sistem web ini.</p>
                    @endforelse
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        Selamat datang, {{ Auth::user()->name }}! ({{ Auth::user()->department?->name }})

                        <div class="mt-6">
                            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
                                <h3 class="text-lg font-bold text-green-800">Panel Pelaporan</h3>
                                <p class="text-sm text-green-600">Buat laporan jika ada kendala IT di ruangan Anda.</p>
                            </div>
                            <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500">
                                + Buat Tiket Baru
                            </a>
                            <a href="{{ route('tickets.index') }}" class="ml-2 text-sm text-gray-600 hover:text-gray-900 underline">
                                Lihat Histori Laporan Saya
                            </a>
                        </div>
                    </div>
                </div>
            @endrole

        </div>
    </div>
</x-app-layout>