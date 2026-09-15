<x-app-layout>
    <x-slot name="title">Impor Data Siswa</x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800">Impor Massal Data Siswa</h2>
            <p class="text-sm text-gray-500 mt-1">Unggah file CSV/Excel berisi daftar siswa peserta ujian.</p>
        </div>

        <div class="mb-6 p-4 bg-sapta-50 border border-sapta-100 rounded-xl text-xs text-sapta-800 space-y-1">
            <p class="font-bold">Format Kolom File CSV:</p>
            <p><code class="bg-white px-1.5 py-0.5 rounded font-mono">nama, username, nisn, nis, password, kelas</code></p>
            <p class="text-gray-500 mt-1">* Jika kolom <code class="bg-white px-1.5 py-0.5 rounded">password</code> dikosongkan, kata sandi adalah <code class="bg-white px-1.5 py-0.5 rounded font-bold">12345678</code>.</p>
        </div>

        <form action="{{ route('users.students.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Target Kelas Default</label>
                <select name="school_class_id" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->grade }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">File CSV / Excel</label>
                <input type="file" name="file" accept=".csv,.txt,.xlsx" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sapta-50 file:text-sapta-700 hover:file:bg-sapta-100 cursor-pointer">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('users.students.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">Batal</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-medium bg-sapta-600 text-white hover:bg-sapta-700 shadow-sm">Mulai Impor</button>
            </div>
        </form>
    </div>
</x-app-layout>
