<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Hasil Ujian: ') }} {{ $exam->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xl font-bold">{{ $exam->title }}</h3>
                            <p class="text-gray-500">{{ $exam->subject->name ?? '-' }} | Dibuat oleh: {{ $exam->creator->name ?? '-' }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            @can('viewResults', $exam)
                                <a href="{{ route('analytics.exam', $exam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 border border-indigo-300 rounded-md font-semibold text-xs text-indigo-700 uppercase tracking-widest shadow-sm hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    Analitik
                                </a>
                            @endcan
                            
                            @can('grade', $exam)
                                <a href="{{ route('exams.grading.index', $exam) }}" class="inline-flex items-center px-4 py-2 bg-purple-50 border border-purple-300 rounded-md font-semibold text-xs text-purple-700 uppercase tracking-widest shadow-sm hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Koreksi Manual
                                </a>
                            @endcan

                            @if(is_null($exam->results_published_at))
                                <form action="{{ route('exams.results.publish', $exam->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mempublikasikan nilai ini? Semua siswa yang berpartisipasi akan dapat melihat nilai mereka.')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Validasi & Publikasi
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 border border-green-200 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm">
                                    Telah Dipublikasikan
                                </span>
                            @endif
                            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-300 focus:outline-none focus:border-gray-400 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Peserta</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Submit</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Nilai</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($attempts as $attempt)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $attempt->student->user->name ?? 'Unknown' }}</div>
                                            <div class="text-sm text-gray-500">{{ $attempt->student->nis ?? '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($attempt->status === 'SUBMITTED')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Manual Submit
                                                </span>
                                            @elseif($attempt->status === 'AUTO_SUBMITTED')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Auto Submit
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ $attempt->status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if(!is_null($exam->results_published_at))
                                                <span class="px-2 mb-1 inline-flex text-[10px] leading-4 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Dipublikasi
                                                </span><br>
                                            @endif
                                            @if($attempt->grading_status === 'FINAL' || $attempt->grading_status === 'GRADED')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Final
                                                </span>
                                            @elseif($attempt->grading_status === 'WAITING_MANUAL')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                    Menunggu Manual
                                                </span>
                                            @elseif($attempt->grading_status === 'AUTO_GRADED')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Auto Graded
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ $attempt->grading_status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if(in_array($attempt->grading_status, ['FINAL', 'AUTO_GRADED', 'GRADED']))
                                                <span class="font-bold">{{ rtrim(rtrim(number_format($attempt->total_score, 2), '0'), '.') }}</span>
                                                <span class="text-gray-500">/ {{ rtrim(rtrim(number_format($attempt->max_total_score, 2), '0'), '.') }}</span>
                                            @else
                                                <span class="text-gray-400 italic">Belum Final</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if(in_array($attempt->grading_status, ['FINAL', 'AUTO_GRADED', 'GRADED']))
                                                @php
                                                    $maxScore = $attempt->max_total_score > 0 ? $attempt->max_total_score : 1;
                                                    $percentage = ($attempt->total_score / $maxScore) * 100;
                                                @endphp
                                                {{ number_format($percentage, 0) }}%
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            Belum ada hasil ujian yang disubmit.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $attempts->links() }}
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
