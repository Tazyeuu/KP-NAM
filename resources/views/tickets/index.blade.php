<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @role('admin')
                    {{ __('Semua Antrean Tiket') }}
                @elserole('user')
                    {{ __('Histori Laporan') }}
                @endrole
            </h2>
            
            @unlessrole('admin|technician')
                <a href="{{ route('tickets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-bold hover:bg-blue-700 shadow-sm transition">
                    + Buat Tiket Baru
                </a>
            @endunlessrole
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No. Tiket</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Subjek & Kategori</th>
                                
                                @role('admin')
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pelapor</th>
                                @endrole
                                
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($tickets as $ticket)
                                <tr class="hover:bg-blue-50 cursor-pointer transition duration-150 ease-in-out group" 
                                    onclick="window.location='{{ route('tickets.show', $ticket->id) }}'">
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono font-semibold text-blue-600 group-hover:text-blue-800">
                                            {{ $ticket->ticket_number }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->created_at->format('d M Y') }}
                                        <div class="text-xs text-gray-400">{{ $ticket->created_at->format('H:i') }} WIB</div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900 line-clamp-1">{{ $ticket->subject }}</div>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                            <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-600 font-medium">{{ $ticket->category->name }}</span>
                                        </div>
                                    </td>
                                    
                                    @role('admin')
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium">{{ $ticket->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $ticket->department->name }}</div>
                                    </td>
                                    @endrole

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusBadge = match($ticket->status) {
                                                'Open' => 'bg-blue-50/50 border border-blue-100 text-blue-500',
                                                'Assigned' => 'bg-orange-50 text-orange-500 border-orange-200',
                                                'In Progress' => 'bg-pink-50/50 text-pink-500 border-pink-100',
                                                'Resolved' => 'bg-green-50/50 border border-green-100 text-green-500',
                                                'Closed' => 'bg-gray-50 text-gray-600 border-gray-200',
                                            };
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-md uppercase {{ $statusBadge }}">
                                            {{ $ticket->status }}
                                        </span>
                                    </td>
                                    
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <p class="text-base font-medium text-gray-500">Anda belum pernah membuat tiket laporan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- 
            <div class="mt-4">
                {{ $tickets->links() }}
            </div> 
            --}}

        </div>
    </div>
</x-app-layout>