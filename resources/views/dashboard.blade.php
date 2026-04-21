<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @role('admin')
            <x-slot name="header">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Admin</h2>
                </div>
            </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex justify-between items-center">
                        <div>
                            <h4 class="text-blue-500 text-sm font-semibold mb-1">Open</h4>
                            <p class="text-2xl font-bold text-gray-800">{{ $openTickets }}</p>
                        </div>
                        <div class="p-2 bg-blue-100 text-blue-500 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    </div>
                    <div class="bg-orange-50/50 border border-orange-100 rounded-xl p-4 flex justify-between items-center">
                        <div>
                            <h4 class="text-orange-500 text-sm font-semibold mb-1">Assigned</h4>
                            <p class="text-2xl font-bold text-gray-800">{{ $assignedTickets }}</p>
                        </div>
                        <div class="p-2 bg-orange-100 text-orange-500 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
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
                        <option>Assigned</option>
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
                                            'Assigned' => 'bg-orange-50 text-orange-500 border-orange-100',
                                            'In Progress' => 'bg-pink-50/50 text-pink-500 border-pink-100',
                                            'Resolved' => 'bg-green-50/50 border border-green-100 text-green-500',
                                            'Closed' => 'bg-gray-50 text-gray-600 border-gray-200',
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
                            <p class="text-xs text-gray-400 mt-1">Selesai pada: {{ $ticket->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 italic">Belum ada tugas yang diselesaikan di sistem web ini.</p>
                    @endforelse
                </div>
            @else
                <x-slot name="header">
                    <div class="flex justify-between items-center">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    
                    <div class="md:col-span-2 bg-gradient-to-br from-blue-600 to-indigo-800 rounded-2xl shadow-md p-8 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                        
                        <h2 class="text-3xl font-bold mb-2 relative z-10">Selamat datang, {{ Auth::user()->name }}!</h2>
                        <p class="text-blue-100 text-sm mb-6 flex items-center gap-2 relative z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            {{ Auth::user()->department->name }}
                        </p>

                        <p class="mb-8 text-sm leading-relaxed text-blue-50 relative z-10 max-w-xl">
                            Sistem Informasi Manajemen Layanan IT (ITSM) RSUD dr. Soedarso hadir untuk membantu kelancaran tugas Anda. Jika Anda mengalami kendala perangkat, jaringan, SIMRS, atau fasilitas IT lainnya, silakan laporkan di sini.
                        </p>

                        <div class="flex flex-wrap gap-3 relative z-10">
                            <a href="{{ route('tickets.create') }}" class="bg-white text-blue-700 px-6 py-3 rounded-lg font-bold text-sm shadow-lg hover:bg-gray-50 transition transform hover:-translate-y-0.5">
                                + Buat Tiket Baru
                            </a>
                            <a href="{{ route('tickets.index') }}" class="bg-blue-800/40 text-white border border-blue-400/30 px-6 py-3 rounded-lg font-semibold text-sm hover:bg-blue-800/60 transition backdrop-blur-sm">
                                Lihat Histori Laporan
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            Informasi Layanan
                        </h3>
                        <ul class="space-y-4 text-sm text-gray-600">
                            <li class="flex items-start gap-3">
                                <div class="bg-blue-50 p-2 rounded-lg text-blue-600 mt-0.5">⏱️</div>
                                <span>Layanan operasional IT memprioritaskan penanganan darurat untuk area Kritis (IGD/ICU).</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="bg-green-50 p-2 rounded-lg text-green-600 mt-0.5">💡</div>
                                <span>Coba <em>restart</em> perangkat (PC/Printer) Anda sebelum memutuskan untuk membuat tiket laporan.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="bg-indigo-50 p-2 rounded-lg text-indigo-600 mt-0.5">🤖</div>
                                <span>Gunakan <strong>Asisten IT AI</strong> di pojok kanan bawah untuk mendapat solusi instan.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">Laporan Terbaru Anda</h3>
                        <a href="{{ route('tickets.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                            Lihat Semua <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Tiket</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Subjek & Kategori</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                @forelse($userTickets as $ticket)
                                    <tr class="hover:bg-blue-50 cursor-pointer transition duration-150" onclick="window.location='{{ route('tickets.show', $ticket->id) }}'">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-mono font-semibold text-blue-600">{{ $ticket->ticket_number }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $ticket->created_at->format('d M Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $ticket->created_at->format('H:i') }} WIB</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900 line-clamp-1">{{ $ticket->subject }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $ticket->category->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusBadge = match($ticket->status) {
                                                    'Open' => 'bg-blue-50/50 border border-blue-100 text-blue-500',
                                                    'Assigned' => 'bg-orange-50 text-orange-500 border-orange-100',
                                                    'In Progress' => 'bg-pink-50/50 text-pink-500 border-pink-100',
                                                    'Resolved' => 'bg-green-50/50 border border-green-100 text-green-500',
                                                    'Closed' => 'bg-gray-50 text-gray-600 border-gray-200',
                                                };
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-md border uppercase {{ $statusBadge }}">
                                                {{ $ticket->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <p class="text-sm font-medium">Anda belum pernah membuat tiket laporan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="chatbot-container" class="fixed bottom-6 right-6 z-50 font-sans">
                    
                    <div id="chat-window" class="hidden flex-col w-80 sm:w-96 h-[450px] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden transition-all duration-300 transform origin-bottom-right mb-4">
                        
                        <div class="bg-indigo-600 p-4 text-white flex justify-between items-center shadow-md z-10">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-xl">🤖</div>
                                <div>
                                    <h4 class="font-bold text-sm">Asisten IT Soedarso</h4>
                                    <p class="text-[10px] text-indigo-200 flex items-center gap-1">
                                        <span class="w-2 h-2 bg-green-400 rounded-full inline-block animate-pulse"></span>
                                        Siap membantu
                                    </p>
                                </div>
                            </div>
                            <button id="close-chat" class="text-white hover:text-indigo-200 focus:outline-none transition transform hover:rotate-90">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto bg-slate-50 space-y-4">
                            <div class="flex justify-start">
                                <div class="bg-white border border-gray-100 text-gray-700 text-sm p-3 rounded-2xl rounded-tl-sm max-w-[85%] shadow-sm">
                                    Halo, {{ Auth::user()->name }}! Saya Asisten AI IT RSUD. 🏥<br><br>
                                    Ada kendala seputar Printer, Jaringan, atau SIMRS yang bisa saya bantu selesaikan sebelum Anda memanggil teknisi?
                                </div>
                            </div>
                            
                            </div>

                        <div class="p-3 bg-white border-t border-gray-100">
                            <form id="chat-form" class="flex gap-2 items-center">
                                <input type="text" id="chat-input" class="w-full bg-gray-100 border-transparent focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent rounded-full text-sm px-4 py-2.5 transition" placeholder="Ketik kendala Anda di sini...">
                                <button type="submit" class="bg-indigo-600 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-indigo-700 flex-shrink-0 transition shadow-md">
                                    <svg class="w-4 h-4 transform rotate-45 -mt-0.5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    <button id="chat-toggle" class="w-14 h-14 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center ml-auto relative group">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        
                        <span class="absolute -top-10 right-0 bg-gray-800 text-white text-[10px] font-bold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                            Tanya Asisten AI
                        </span>
                    </button>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const chatToggle = document.getElementById('chat-toggle');
                        const chatWindow = document.getElementById('chat-window');
                        const closeChat = document.getElementById('close-chat');

                        // Logika Buka Chat
                        chatToggle.addEventListener('click', () => {
                            chatWindow.classList.remove('hidden');
                            chatWindow.classList.add('flex');
                            chatToggle.classList.add('hidden'); // Sembunyikan tombol bulat saat chat terbuka
                        });

                        // Logika Tutup Chat
                        closeChat.addEventListener('click', () => {
                            chatWindow.classList.add('hidden');
                            chatWindow.classList.remove('flex');
                            chatToggle.classList.remove('hidden'); // Munculkan kembali tombol bulat
                        });
                    });

                    const chatForm = document.getElementById('chat-form');
                    const chatInput = document.getElementById('chat-input');
                    const chatMessages = document.getElementById('chat-messages');

                    function appendMessage(message, sender) {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `flex ${sender === 'user' ? 'justify-end' : 'justify-start'}`;
                        
                        const bubbleColor = sender === 'user' 
                            ? 'bg-indigo-600 text-white rounded-tr-sm' 
                            : 'bg-white border border-gray-100 text-gray-700 rounded-tl-sm';

                        messageDiv.innerHTML = `
                            <div class="text-sm p-3 rounded-2xl max-w-[85%] shadow-sm ${bubbleColor}">
                                ${message}
                            </div>
                        `;
                        
                        chatMessages.appendChild(messageDiv);
                        
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }

                    chatForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        
                        const text = chatInput.value.trim();
                        if (!text) return;

                        appendMessage(text, 'user');
                        chatInput.value = '';
                        chatInput.disabled = true;

                        const loadingId = 'loading-' + Date.now();
                        chatMessages.innerHTML += `
                            <div id="${loadingId}" class="flex justify-start mb-2">
                                <div class="bg-gray-100 text-gray-500 text-xs p-2 rounded-xl italic">Bot sedang berpikir...</div>
                            </div>
                        `;
                        chatMessages.scrollTop = chatMessages.scrollHeight;

                        try {
                            const response = await fetch('http://10.220.108.71:3000/api/chat', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ message: text }) 
                            });

                            const data = await response.json();
                            
                            document.getElementById(loadingId).remove();

                            if(data.reply) {
                                appendMessage(data.reply, 'bot');
                            } else {
                                appendMessage("Maaf, bot tidak memberikan jawaban yang dapat dibaca.", 'bot');
                            }

                        } catch (error) {
                            document.getElementById(loadingId).remove();
                            appendMessage("Waduh, koneksi ke server Bot terputus.", 'bot');
                            console.error('Error Chatbot:', error);
                        } finally {
                            chatInput.disabled = false;
                            chatInput.focus();
                        }
                    });
                </script>
            @endrole

        </div>
    </div>
</x-app-layout>