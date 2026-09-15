<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Hasil Ujian: ') }} {{ $exam->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-8 text-center">
                        <h3 class="text-2xl font-bold">{{ $exam->title }}</h3>
                        <p class="text-gray-500">{{ $exam->subject->name ?? '-' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-sm text-gray-500 font-semibold mb-1">Status Pengerjaan</p>
                                <p class="text-lg">
                                    @if($attempt->status === 'SUBMITTED')
                                        Selesai (Dikumpulkan manual)
                                    @elseif($attempt->status === 'AUTO_SUBMITTED')
                                        Selesai (Otomatis karena waktu habis)
                                    @else
                                        {{ $attempt->status }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-semibold mb-1">Waktu Selesai</p>
                                <p class="text-lg">{{ $attempt->updated_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
                        <p class="text-sm text-blue-600 font-bold tracking-widest uppercase mb-2">Status Penilaian</p>
                        
                        @if($attempt->grading_status === 'FINAL')
                            <p class="text-gray-600 mb-4">Nilai Akhir Anda</p>
                            
                            @php
                                $maxScore = $attempt->max_total_score > 0 ? $attempt->max_total_score : 1;
                                $percentage = ($attempt->total_score / $maxScore) * 100;
                            @endphp
                            
                            <div class="flex flex-col items-center justify-center">
                                <div class="text-6xl font-black text-blue-900 mb-2">
                                    {{ rtrim(rtrim(number_format($attempt->total_score, 2), '0'), '.') }}
                                    <span class="text-2xl text-blue-400 font-normal">/ {{ rtrim(rtrim(number_format($attempt->max_total_score, 2), '0'), '.') }}</span>
                                </div>
                                <div class="bg-blue-100 text-blue-800 px-4 py-1 rounded-full font-bold text-lg">
                                    {{ number_format($percentage, 0) }}%
                                </div>
                            </div>
                        @elseif($attempt->grading_status === 'WAITING_MANUAL')
                            <div class="py-8">
                                <svg class="mx-auto h-12 w-12 text-blue-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-2xl font-bold text-gray-800 mb-2">Menunggu Penilaian Guru</p>
                                <p class="text-gray-600">Ujian Anda mengandung soal esai yang perlu diperiksa manual oleh guru. Nilai akan tampil setelah pemeriksaan selesai.</p>
                            </div>
                        @elseif($attempt->grading_status === 'AUTO_GRADED')
                            <div class="py-8">
                                <p class="text-2xl font-bold text-gray-800 mb-2">Penilaian Diproses</p>
                                <p class="text-gray-600">Hasil penilaian belum difinalisasi.</p>
                            </div>
                        @else
                            <div class="py-8">
                                <p class="text-xl text-gray-600">Status Penilaian: {{ $attempt->grading_status }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8 flex justify-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
