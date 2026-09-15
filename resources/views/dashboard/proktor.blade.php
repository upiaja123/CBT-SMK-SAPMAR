<x-app-layout>
    <x-slot name="title">
        Dashboard Proktor
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-sapta-50 flex items-center justify-center flex-shrink-0 text-sapta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Siswa Sedang Ujian</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['ongoing_attempts'] }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Ujian Aktif Saat Ini</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['active_exams'] }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-sapta-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Ujian Tersedia untuk Dipantau
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3">Nama Ujian</th>
                        <th class="px-4 py-3">Mata Pelajaran</th>
                        <th class="px-4 py-3">Waktu Mulai</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($stats['recent_exams'] as $exam)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $exam->title }}</td>
                            <td class="px-4 py-3">{{ $exam->subject?->name }}</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($exam->start_at)->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-right align-middle">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('exams.monitoring.index', $exam) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-sapta-600 hover:bg-sapta-700 shadow-sm">
                                        Pantau Ujian &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">Tidak ada ujian aktif saat ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
