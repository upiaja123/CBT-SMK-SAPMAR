<x-app-layout>
    <x-slot name="title">Verifikasi Akun Baru</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Verifikasi Pendaftar Google</h2>
            <p class="text-sm text-gray-500">Kelola akun baru yang mendaftar via Google Login.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Nama / Username</th>
                        <th class="px-6 py-4">Email Google</th>
                        <th class="px-6 py-4">Tgl Daftar</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($pendingUsers as $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $item->avatar }}" alt="" class="w-8 h-8 rounded-full bg-gray-200">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $item->name }}</p>
                                        <p class="font-mono text-xs text-sapta-600">{{ $item->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $item->email }}</td>
                            <td class="px-6 py-4 text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right align-middle">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <button type="button" onclick="openVerifyModal({{ $item->id }}, '{{ addslashes($item->name) }}')" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 shadow-sm">
                                        Verifikasi
                                    </button>
                                    <form action="{{ route('users.verifications.destroy', $item) }}" method="POST" class="m-0" onsubmit="return confirm('Tolak dan hapus akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded text-gray-600 bg-gray-100 hover:bg-red-100 hover:text-red-600 transition shadow-sm">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">Tidak ada pendaftar baru yang menunggu verifikasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pendingUsers->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $pendingUsers->links() }}
        </div>
        @endif
    </div>

    {{-- Verify Modal --}}
    <div id="modal-verify" class="fixed inset-0 bg-gray-900/60 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Verifikasi Akun</h3>
            <p class="text-sm text-gray-500 mb-4" id="verify-user-name"></p>

            <form id="form-verify" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Peran (Role)</label>
                    <select name="role" id="verify-role" onchange="toggleFields()" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                        <option value="">-- Pilih Role --</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Fields for Siswa -->
                <div id="fields-siswa" class="hidden space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kelas</label>
                        <select name="school_class_id" id="verify-class" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NIS / NISN (Opsional)</label>
                        <input type="text" name="nis" placeholder="Contoh: 12345" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                </div>

                <!-- Fields for Guru -->
                <div id="fields-guru" class="hidden space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NIP (Opsional)</label>
                        <input type="text" name="nip" placeholder="Contoh: 1980..." class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="document.getElementById('modal-verify').classList.add('hidden')" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium bg-sapta-600 text-white hover:bg-sapta-700">Setujui & Aktifkan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openVerifyModal(userId, name) {
            document.getElementById('form-verify').action = `/users/verifications/${userId}`;
            document.getElementById('verify-user-name').innerText = name;
            
            // Reset form
            document.getElementById('verify-role').value = '';
            toggleFields();
            
            document.getElementById('modal-verify').classList.remove('hidden');
        }

        function toggleFields() {
            const role = document.getElementById('verify-role').value;
            const fieldsSiswa = document.getElementById('fields-siswa');
            const fieldsGuru = document.getElementById('fields-guru');
            const classSelect = document.getElementById('verify-class');

            // Hide all first
            fieldsSiswa.classList.add('hidden');
            fieldsGuru.classList.add('hidden');
            classSelect.required = false;

            if (role === 'siswa') {
                fieldsSiswa.classList.remove('hidden');
                classSelect.required = true;
            } else if (role === 'guru') {
                fieldsGuru.classList.remove('hidden');
            }
        }
    </script>
    @endpush
</x-app-layout>
