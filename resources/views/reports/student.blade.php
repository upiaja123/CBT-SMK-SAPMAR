<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Hasil Ujian Saya') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-6 py-4 rounded-tl-lg">Nama Ujian</th>
                                    <th scope="col" class="px-6 py-4">Mata Pelajaran</th>
                                    <th scope="col" class="px-6 py-4">Guru</th>
                                    <th scope="col" class="px-6 py-4">Status / Diselesaikan</th>
                                    <th scope="col" class="px-6 py-4 rounded-tr-lg">Nilai Anda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($attempts as $attempt)
                                    <tr class="bg-white border-b hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $attempt->exam->title }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $attempt->exam->subject->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $attempt->exam->creator->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($attempt->status === 'SUBMITTED' || $attempt->status === 'AUTO_SUBMITTED')
                                                <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium border border-blue-200">
                                                    Dikumpulkan
                                                </span>
                                            @elseif ($attempt->status === 'FINALIZED')
                                                <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium border border-green-200">
                                                    Selesai & Dinilai
                                                </span>
                                            @endif
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $attempt->updated_at->format('d M Y, H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if (!is_null($attempt->exam->results_published_at))
                                                @php
                                                    $maxScore = $attempt->max_total_score > 0 ? $attempt->max_total_score : 1;
                                                    $percentage = ($attempt->total_score / $maxScore) * 100;
                                                @endphp
                                                <div class="flex flex-col gap-1">
                                                    <div class="text-xl font-black text-blue-700">
                                                        {{ rtrim(rtrim(number_format($attempt->total_score, 2), '0'), '.') }}
                                                        <span class="text-xs text-gray-500 font-normal">/ {{ rtrim(rtrim(number_format($attempt->max_total_score, 2), '0'), '.') }}</span>
                                                    </div>
                                                    <div class="text-sm font-bold text-gray-700">
                                                        {{ number_format($percentage, 0) }}%
                                                    </div>
                                                    <div>
                                                        <span class="inline-block whitespace-nowrap px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-[10px] font-medium border border-green-200">
                                                            Telah Dipublikasi
                                                        </span>
                                                    </div>
                                                </div>
                                            @elseif (in_array($attempt->grading_status, ['FINAL', 'AUTO_GRADED', 'GRADED']))
                                                <div class="flex items-center gap-2 text-yellow-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                    <span class="text-xs font-semibold">Menunggu Publikasi Guru</span>
                                                </div>
                                            @elseif (in_array($attempt->grading_status, ['WAITING_MANUAL', 'PARTIALLY_GRADED', 'NEEDS_GRADING']))
                                                <span class="px-2.5 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium border border-orange-200">
                                                    Menunggu Koreksi Manual
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium border border-gray-200">
                                                    Belum Dinilai
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if (!is_null($attempt->exam->results_published_at))
                                                <a href="{{ route('exams.attempts.result', ['exam' => $attempt->exam_id, 'attempt' => $attempt->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    Lihat Hasil & Pembahasan
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                <p class="text-base font-medium text-gray-900">Belum Ada Hasil Ujian</p>
                                                <p class="text-sm mt-1">Anda belum menyelesaikan ujian apapun atau ujian Anda belum dinilai.</p>
                                            </div>
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
