<x-app-layout>
    <x-slot name="title">Kelas</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Rombongan Belajar / Kelas</h2>
            <p class="text-sm text-gray-500">Kelola daftar kelas dan kapasitas siswa.</p>
        </div>
        <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
            class="bg-sapta-600 hover:bg-sapta-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Kelas
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Tingkat</th>
                        <th class="px-6 py-4">Nama Kelas</th>
                        <th class="px-6 py-4">Jurusan</th>
                        <th class="px-6 py-4">Tahun Ajaran</th>
                        <th class="px-6 py-4">Kapasitas</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($classes as $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $item->grade }}</td>
                            <td class="px-6 py-4 font-semibold text-sapta-600">{{ $item->name }}</td>
                            <td class="px-6 py-4">{{ $item->major?->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->academicYear?->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $item->capacity }} Siswa</td>
                            <td class="px-6 py-4 text-right align-middle">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('prints.cards', $item) }}" target="_blank" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 shadow-sm gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        Kartu
                                    </a>
                                    <button type="button" onclick="openEditModal({{ $item->id }}, '{{ $item->grade }}', '{{ $item->name }}', '{{ $item->major_id }}', '{{ $item->academic_year_id }}', '{{ $item->capacity }}')" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">Edit</button>
                                    <form action="{{ route('master.classes.destroy', $item) }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 shadow-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data kelas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $classes->links() }}
        </div>
    </div>

    {{-- Create Modal --}}
    <div id="modal-create" class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Rombongan Belajar</h3>
            <form action="{{ route('master.classes.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tingkat</label>
                        <select name="grade" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                            <option value="X">X</option>
                            <option value="XI">XI</option>
                            <option value="XII">XII</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Kelas (contoh: RPL 1)</label>
                        <input type="text" name="name" required placeholder="RPL 1" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jurusan</label>
                    <select name="major_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                        @foreach ($majors as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kapasitas Maksimal</label>
                    <input type="number" name="capacity" value="36" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
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
            <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Rombongan Belajar</h3>
            <form id="form-edit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tingkat</label>
                        <select name="grade" id="edit-grade" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                            <option value="X">X</option>
                            <option value="XI">XI</option>
                            <option value="XII">XII</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Kelas</label>
                        <input type="text" name="name" id="edit-name" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jurusan</label>
                    <select name="major_id" id="edit-major_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                        @foreach ($majors as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" id="edit-academic_year_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kapasitas Maksimal</label>
                    <input type="number" name="capacity" id="edit-capacity" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
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
        function openEditModal(id, grade, name, majorId, academicYearId, capacity) {
            document.getElementById('form-edit').action = `/master/classes/${id}`;
            document.getElementById('edit-grade').value = grade;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-major_id').value = majorId;
            document.getElementById('edit-academic_year_id').value = academicYearId;
            document.getElementById('edit-capacity').value = capacity;
            
            document.getElementById('modal-edit').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
