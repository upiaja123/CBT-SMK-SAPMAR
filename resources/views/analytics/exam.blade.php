<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analytics Ujian: ') }} {{ $exam->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Overview Cards -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 uppercase">Total Peserta</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $summary['total_participants'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 uppercase">Selesai (Final)</div>
                    <div class="text-3xl font-bold text-green-600">{{ $summary['final'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 uppercase">Rata-rata Nilai</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $summary['avg_score'] !== null ? number_format($summary['avg_score'], 1) : '-' }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 uppercase">Tingkat Kelulusan</div>
                    <div class="text-3xl font-bold {{ $summary['pass_percentage'] >= 60 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $summary['pass_percentage'] !== null ? number_format($summary['pass_percentage'], 1).'%' : '-' }}
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Statistik Nilai</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="p-4 bg-gray-50 rounded-lg border">
                            <span class="block text-sm text-gray-500">Nilai Tertinggi</span>
                            <span class="block text-xl font-bold">{{ $summary['highest_score'] !== null ? number_format($summary['highest_score'], 1) : '-' }}</span>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg border">
                            <span class="block text-sm text-gray-500">Nilai Terendah</span>
                            <span class="block text-xl font-bold">{{ $summary['lowest_score'] !== null ? number_format($summary['lowest_score'], 1) : '-' }}</span>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg border">
                            <span class="block text-sm text-gray-500">Nilai Tengah (Median)</span>
                            <span class="block text-xl font-bold">{{ $summary['median_score'] !== null ? number_format($summary['median_score'], 1) : '-' }}</span>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-bold mb-4">Analisis per Kelas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peserta Mengerjakan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selesai (Final)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rata-rata</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lulus / Tidak</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Persentase Lulus</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($classAnalytics as $classStats)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $classStats->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $classStats->attempt_count }} / {{ $classStats->student_count }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $classStats->final_count }}</td>
                                        <td class="px-6 py-4 text-sm font-bold">{{ $classStats->avg_score !== null ? number_format($classStats->avg_score, 1) : '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <span class="text-green-600">{{ $classStats->pass_count }}</span> / <span class="text-red-600">{{ $classStats->final_count - $classStats->pass_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($classStats->pass_rate !== null)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $classStats->pass_rate >= 60 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ number_format($classStats->pass_rate, 1) }}%
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-right align-middle">
                                            @can('results.export')
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <a href="{{ route('exports.classes.excel', [$exam->id, $classStats->id]) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 shadow-sm" title="Export Excel">
                                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Excel
                                                </a>
                                                <a href="{{ route('exports.classes.pdf', [$exam->id, $classStats->id]) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 shadow-sm" title="Export PDF">
                                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    PDF
                                                </a>
                                            </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data per kelas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6 flex justify-between items-center">
                        <div class="flex space-x-2">
                            @can('results.export')
                            <a href="{{ route('exports.exams.excel', $exam) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Export Excel (Semua)
                            </a>
                            <a href="{{ route('exports.exams.pdf', $exam) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                Export PDF (Semua)
                            </a>
                            @endcan
                        </div>
                        <a href="{{ route('exams.results.index', $exam) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Lihat Detail Hasil Peserta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
