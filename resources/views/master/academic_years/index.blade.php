<x-app-layout>
    <x-slot name="title">Tahun Ajaran</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Tahun Ajaran</h2>
            <p class="text-sm text-gray-500">Kelola daftar tahun ajaran dan semester aktif.</p>
        </div>
        <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
            class="bg-sapta-600 hover:bg-sapta-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Tahun Ajaran
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Tahun Ajaran</th>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($academicYears as $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-4">{{ $item->year_start }} - {{ $item->year_end }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right align-middle">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button" onclick="openEditModal({{ $item->id }}, '{{ $item->name }}', '{{ $item->year_start }}', '{{ $item->year_end }}', {{ $item->is_active ? 'true' : 'false' }})" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">Edit</button>
                                    <form action="{{ route('master.academic-years.destroy', $item) }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 shadow-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada data tahun ajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $academicYears->links() }}
        </div>
    </div>

    {{-- Create Modal --}}
    <div id="modal-create" class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Tahun Ajaran</h3>
            <form action="{{ route('master.academic-years.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama (contoh: 2025/2026)</label>
                    <input type="text" name="name" required placeholder="2025/2026" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tahun Mulai</label>
                        <input type="number" name="year_start" required value="2025" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tahun Selesai</label>
                        <input type="number" name="year_end" required value="2026" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-gray-300 text-sapta-600 focus:ring-sapta-500">
                    <label for="is_active" class="text-sm text-gray-700">Set sebagai Tahun Ajaran Aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium bg-sapta-600 text-white hover:bg-sapta-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    {{-- Edit Modal --}}
    <div id="modal-edit" class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Tahun Ajaran</h3>
            <form id="form-edit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama (contoh: 2025/2026)</label>
                    <input type="text" name="name" id="edit-name" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tahun Mulai</label>
                        <input type="number" name="year_start" id="edit-year_start" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tahun Selesai</label>
                        <input type="number" name="year_end" id="edit-year_end" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="edit-is_active" value="1" class="rounded border-gray-300 text-sapta-600 focus:ring-sapta-500">
                    <label for="edit-is_active" class="text-sm text-gray-700">Set sebagai Tahun Ajaran Aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium bg-sapta-600 text-white hover:bg-sapta-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditModal(id, name, yearStart, yearEnd, isActive) {
            document.getElementById('form-edit').action = `/master/academic-years/${id}`;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-year_start').value = yearStart;
            document.getElementById('edit-year_end').value = yearEnd;
            document.getElementById('edit-is_active').checked = isActive;
            
            document.getElementById('modal-edit').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
