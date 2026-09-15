<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Penilaian: {{ $attempt->student->user->name }}
            </h2>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">Total Nilai: <strong>{{ $attempt->total_score ?? '0' }} / {{ $attempt->max_total_score ?? '0' }}</strong></span>
                <a href="{{ route('exams.grading.index', $exam) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                    &larr; Kembali ke Daftar
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .prose img {
            max-height: 20rem; /* max-h-80 */
            width: auto;
            object-fit: contain;
            margin-left: auto;
            margin-right: auto;
        }
    </style>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="p-4 mb-6 text-sm text-green-700 bg-green-100 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 mb-6 text-sm text-red-700 bg-red-100 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if($answers->isEmpty())
                <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-200">
                    <p class="text-gray-500">Tidak ada jawaban essay yang perlu dinilai secara manual pada sesi ini.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($answers as $index => $answer)
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
                            <div class="p-6 border-b border-gray-100 bg-gray-50">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        Soal {{ $answer->questionSnapshot->order ?? ($index + 1) }}
                                        <span class="ml-2 text-sm font-normal text-gray-500">
                                            ({{ $answer->questionSnapshot->question_type }})
                                        </span>
                                    </h3>
                                    
                                    @if($answer->grading_status === 'WAITING_MANUAL')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Menunggu Penilaian
                                        </span>
                                    @elseif($answer->grading_status === 'MANUALLY_GRADED')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Telah Dinilai
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="prose max-w-none text-gray-800">
                                    {!! $answer->questionSnapshot->content !!}
                                </div>
                            </div>
                            
                            <div class="p-6 bg-white border-b border-gray-100">
                                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Jawaban Siswa</h4>
                                <div class="p-4 bg-gray-50 rounded-md border border-gray-200 text-gray-800 whitespace-pre-wrap">{{ $answer->answer['text'] ?? '(Tidak ada jawaban)' }}</div>
                            </div>

                            <div class="p-6 bg-gray-50">
                                <form action="{{ route('exams.grading.update', [$exam, $attempt, $answer]) }}" method="POST" class="space-y-4">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                        <div class="md:col-span-1">
                                            <label for="awarded_score_{{ $answer->id }}" class="block text-sm font-medium text-gray-700">Nilai (Maks: {{ $answer->max_score }})</label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <input type="number" 
                                                       name="awarded_score" 
                                                       id="awarded_score_{{ $answer->id }}" 
                                                       step="0.01" 
                                                       min="0" 
                                                       max="{{ $answer->max_score }}"
                                                       value="{{ old('awarded_score', $answer->awarded_score) }}" 
                                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                       required>
                                            </div>
                                            @error('awarded_score')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div class="md:col-span-3">
                                            <label for="feedback_{{ $answer->id }}" class="block text-sm font-medium text-gray-700">Komentar / Feedback (Opsional)</label>
                                            <div class="mt-1">
                                                <textarea id="feedback_{{ $answer->id }}" 
                                                          name="feedback" 
                                                          rows="3" 
                                                          class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                >{{ old('feedback', $answer->feedback) }}</textarea>
                                            </div>
                                            @error('feedback')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Simpan Nilai
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
