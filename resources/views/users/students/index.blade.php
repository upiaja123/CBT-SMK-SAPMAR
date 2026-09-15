<x-app-layout>
    <x-slot name="title">Data Siswa</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Data Siswa Peserta Ujian</h2>
            <p class="text-sm text-gray-500">Kelola akun, NISN/NIS, dan rombel siswa.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('users.students.import.show') }}"
                class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Impor dari Excel / CSV
            </a>
            <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
                class="bg-sapta-600 hover:bg-sapta-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Siswa
            </button>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-100 mb-6 shadow-sm flex flex-wrap items-center gap-4">
        <form method="GET" action="{{ route('users.students.index') }}" class="flex flex-wrap items-center gap-4 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NISN / username..." class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-sapta-500 outline-none flex-1 min-w-[200px]">
            <select name="school_class_id" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-sapta-500 outline-none w-full md:w-auto min-w-[150px]">
                <option value="">-- Semua Kelas --</option>
                @foreach ($classes as $c)
                    <option value="{{ $c->id }}" {{ request('school_class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->grade }})</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-xl hover:bg-gray-900 transition">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Nama Siswa</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4">NISN / NIS</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($students as $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $item->user?->name }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-sapta-600 font-bold">{{ $item->user?->username }}</td>
                            <td class="px-6 py-4 font-medium">{{ $item->schoolClass?->name ?? '-' }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">
                                {{ $item->nisn ?? '-' }} / {{ $item->nis ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right align-middle">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button" onclick="openEditModal({{ $item->id }}, '{{ $item->user?->name }}', '{{ $item->user?->username }}', '{{ $item->user?->email }}', '{{ $item->nisn }}', '{{ $item->nis }}', '{{ $item->school_class_id }}')" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">Edit</button>
                                    <form action="{{ route('users.students.destroy', $item) }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 shadow-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada data siswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $students->withQueryString()->links() }}
        </div>
    </div>

    {{-- Create Modal --}}
    <div id="modal-create" class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Siswa Peserta Ujian</h3>
            


            <form action="{{ route('users.students.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="name" required placeholder="Ahmad Fauzi" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Username / No Ujian</label>
                        <input type="text" name="username" required placeholder="ahmad.fauzi" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kelas</label>
                        <select name="school_class_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NISN (Opsional)</label>
                        <input type="text" name="nisn" placeholder="0051234567" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NIS (Opsional)</label>
                        <input type="text" name="nis" placeholder="25001001" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi</label>
                    <div class="relative">
                        <input type="text" id="create-password" name="password" required placeholder="password" class="w-full px-3 py-2 pr-10 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                        <button type="button" onclick="togglePassword('create-password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3 text-sapta-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Minimal 8 karakter.
                    </p>
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
            <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Siswa Peserta Ujian</h3>
            <form id="form-edit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="name" id="edit-name" placeholder="Ahmad Fauzi" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Username / No Ujian</label>
                        <input type="text" name="username" id="edit-username" placeholder="ahmad.fauzi" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kelas</label>
                        <select name="school_class_id" id="edit-school_class_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NISN (Opsional)</label>
                        <input type="text" name="nisn" id="edit-nisn" placeholder="0051234567" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NIS (Opsional)</label>
                        <input type="text" name="nis" id="edit-nis" placeholder="25001001" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email (Opsional)</label>
                    <input type="email" name="email" id="edit-email" placeholder="ahmad.fauzi@cbt-sapmar.sch.id" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi Baru (Opsional)</label>
                    <div class="relative">
                        <input type="text" id="edit-password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full px-3 py-2 pr-10 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                        <button type="button" onclick="togglePassword('edit-password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3 text-sapta-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Minimal 8 karakter jika diisi.
                    </p>
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
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>`;
            } else {
                input.type = 'password';
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`;
            }
        }

        function openEditModal(id, name, username, email, nisn, nis, classId) {
            document.getElementById('form-edit').action = `/users/students/${id}`;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-username').value = username;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-nisn').value = nisn !== '-' ? nisn : '';
            document.getElementById('edit-nis').value = nis !== '-' ? nis : '';
            document.getElementById('edit-school_class_id').value = classId;
            
            document.getElementById('modal-edit').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
