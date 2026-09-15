<x-app-layout>
    <x-slot name="title">
        Laporan Hasil Ujian
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Hasil Ujian') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="mb-4 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar Rekapitulasi Hasil Ujian</h3>
                            <p class="text-sm text-gray-500">Pilih ujian untuk melihat nilai dan detail jawaban peserta.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Judul Ujian
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mata Pelajaran
                                    </th>
                                    @if(auth()->user()->hasAnyRole(['super_admin', 'kurikulum']))
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pembuat (Guru)
                                    </th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Waktu Pelaksanaan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Partisipan Selesai
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($exams as $exam)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900">{{ $exam->title }}</div>
                                            <div class="text-xs text-gray-500">Token: <span class="font-mono bg-gray-100 px-1 rounded">{{ $exam->token ?? '-' }}</span></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $exam->subject?->name ?? '-' }}
                                            </span>
                                        </td>
                                        @if(auth()->user()->hasAnyRole(['super_admin', 'kurikulum']))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $exam->creator?->name ?? '-' }}
                                        </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            <div>{{ \Carbon\Carbon::parse($exam->start_at)->format('d M Y') }}</div>
                                            <div class="text-xs">{{ \Carbon\Carbon::parse($exam->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($exam->end_at)->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <div class="w-16 bg-gray-200 rounded-full h-2.5">
                                                    @php
                                                        $percentage = $exam->total_participants > 0 ? ($exam->finished_participants / $exam->total_participants) * 100 : 0;
                                                    @endphp
                                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-700">{{ $exam->finished_participants }}/{{ $exam->total_participants }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('exams.results.index', $exam->id) }}" class="inline-flex items-center gap-1 text-white bg-sapta-600 hover:bg-sapta-700 px-3 py-1.5 rounded-lg transition shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Lihat Rekap
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <p class="text-lg font-medium text-gray-900">Belum ada data ujian</p>
                                            <p class="text-sm">Silakan buat ujian terlebih dahulu atau tunggu sampai ada peserta yang mengerjakan.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $exams->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
