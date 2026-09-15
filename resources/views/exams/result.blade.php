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

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center mb-8">
                        <p class="text-sm text-blue-600 font-bold tracking-widest uppercase mb-2">Status Penilaian</p>
                        
                        @if(!is_null($exam->results_published_at))
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
                                <svg class="mx-auto h-12 w-12 text-orange-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-2xl font-bold text-gray-800 mb-2">Menunggu Koreksi Manual</p>
                                <p class="text-gray-600">Ujian Anda mengandung soal esai yang perlu diperiksa manual oleh guru. Nilai akan tampil setelah pemeriksaan dan publikasi selesai.</p>
                            </div>
                        @else
                            <div class="py-8">
                                <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-2xl font-bold text-gray-800 mb-2">Menunggu Publikasi Guru</p>
                                <p class="text-gray-600">Hasil penilaian sedang diproses dan menunggu validasi serta publikasi oleh guru pengampu.</p>
                            </div>
                        @endif
                    </div>

                    @if(!is_null($exam->results_published_at))
                        <div class="mb-8">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Detail Jawaban Anda</h4>
                            <div class="space-y-6">
                                @foreach($attempt->questionSnapshots as $index => $snapshot)
                                    @php
                                        $answer = $snapshot->participantAnswer;
                                        $isCorrect = $answer ? $answer->is_correct : false;
                                        $score = $answer ? $answer->score : 0;
                                        $max = $snapshot->weight ?? 1;
                                    @endphp
                                    <div class="bg-white border {{ $isCorrect ? 'border-green-300' : 'border-red-300' }} rounded-lg p-5 shadow-sm">
                                        <div class="flex justify-between items-start mb-3">
                                            <span class="font-bold text-gray-700">Soal #{{ $index + 1 }}</span>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $isCorrect ? 'BENAR' : 'SALAH' }} ({{ $score }} / {{ $max }} Poin)
                                            </span>
                                        </div>
                                        <div class="prose max-w-none text-sm text-gray-800 mb-4 border-b border-gray-100 pb-4">
                                            {!! $snapshot->content !!}
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Jawaban Anda:</p>
                                            @if($snapshot->question_type === 'multiple_choice' || $snapshot->question_type === 'multiple_select')
                                                <div class="space-y-2">
                                                    @foreach($snapshot->optionSnapshots as $option)
                                                        @php
                                                            $userAnswered = $answer && is_array($answer->answer) && in_array($option->id, $answer->answer);
                                                            $isKey = current(array_filter($snapshot->scoring_metadata['options'] ?? [], fn($o) => $o['id'] == $option->id))['is_correct'] ?? false;
                                                        @endphp
                                                        <div class="flex items-center gap-3 p-2 rounded {{ $userAnswered ? ($isKey ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200') : ($isKey ? 'bg-blue-50 border border-blue-200 border-dashed' : 'bg-gray-50') }}">
                                                            <div class="w-5 h-5 flex items-center justify-center rounded-full border {{ $userAnswered ? 'bg-blue-500 border-blue-500 text-white' : 'border-gray-300' }}">
                                                                @if($userAnswered)
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                @endif
                                                            </div>
                                                            <div class="text-sm {!! $isKey ? 'font-bold' : '' !!}">{!! $option->content !!}</div>
                                                            @if($isKey)
                                                                <span class="ml-auto text-xs text-blue-600 font-bold bg-blue-100 px-2 py-0.5 rounded">KUNCI</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif($snapshot->question_type === 'essay')
                                                <div class="bg-gray-50 p-3 rounded text-sm text-gray-800 italic">
                                                    {{ $answer->answer ?? 'Tidak dijawab' }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-8 flex justify-center">
                        <a href="{{ route('reports.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Kembali ke Daftar Hasil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
