<x-app-layout>
    <x-slot name="title">Mata Pelajaran</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Mata Pelajaran</h2>
            <p class="text-sm text-gray-500">Kelola daftar mata pelajaran di sekolah.</p>
        </div>
        <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
            class="bg-sapta-600 hover:bg-sapta-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Mata Pelajaran
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Kode Mapel</th>
                        <th class="px-6 py-4">Nama Mata Pelajaran</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($subjects as $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-mono font-bold text-sapta-600">{{ $item->code }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $item->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right align-middle">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button" onclick="openEditModal({{ $item->id }}, '{{ $item->code }}', '{{ $item->name }}', '{{ $item->description }}', {{ $item->is_active ? 'true' : 'false' }})" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">Edit</button>
                                    <form action="{{ route('master.subjects.destroy', $item) }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 shadow-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada data mata pelajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $subjects->links() }}
        </div>
    </div>

    {{-- Create Modal --}}
    <div id="modal-create" class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Mata Pelajaran</h3>
            <form action="{{ route('master.subjects.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Mapel (contoh: MTK)</label>
                    <input type="text" name="code" required placeholder="MTK" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Mata Pelajaran</label>
                    <input type="text" name="name" required placeholder="Matematika" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Deskripsi (Opsional)</label>
                    <textarea name="description" placeholder="Deskripsi ringkas mapel" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none" rows="3"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active_subject" value="1" checked class="rounded border-gray-300 text-sapta-600 focus:ring-sapta-500">
                    <label for="is_active_subject" class="text-sm text-gray-700">Status Aktif</label>
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
            <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Mata Pelajaran</h3>
            <form id="form-edit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Mapel</label>
                    <input type="text" name="code" id="edit-code" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Mata Pelajaran</label>
                    <input type="text" name="name" id="edit-name" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Deskripsi (Opsional)</label>
                    <textarea name="description" id="edit-description" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none" rows="3"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="edit-is_active" value="1" class="rounded border-gray-300 text-sapta-600 focus:ring-sapta-500">
                    <label for="edit-is_active" class="text-sm text-gray-700">Status Aktif</label>
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
        function openEditModal(id, code, name, description, isActive) {
            document.getElementById('form-edit').action = `/master/subjects/${id}`;
            document.getElementById('edit-code').value = code;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-description').value = description !== '-' ? description : '';
            document.getElementById('edit-is_active').checked = isActive;
            
            document.getElementById('modal-edit').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
