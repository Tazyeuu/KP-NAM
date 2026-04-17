<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Tiket Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('tickets.update', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Kategori Kendala</label>
                            <select name="category_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (old('category_id', $ticket->category_id) == $category->id) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Subjek Singkat</label>
                            <input type="text" name="subject" value="{{ old('subject', $ticket->subject) }}" required placeholder="Misal: Printer IGD Tinta Habis" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Detail Masalah</label>
                            <textarea name="description" rows="4" required placeholder="Jelaskan kendala secara rinci..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $ticket->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tingkat Prioritas</label>
                                <select name="priority" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="Low">Rendah (Bisa ditunda)</option>
                                    <option value="Medium" {{ (old('priority', $ticket->priority) == 'Medium') ? 'selected' : '' }}>Sedang (Mengganggu kerja)</option>
                                    <option value="High">Tinggi (Sistem lumpuh total)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Detail Lokasi</label>
                                <input type="text" name="location_detail" value="{{ old('location_detail', $ticket->location_detail) }}" required placeholder="Misal: Lantai 2 No. 1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md mr-2 hover:bg-gray-400">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Perbarui Tiket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>