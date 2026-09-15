<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
                Riwayat Versi Soal
            </h2>
            <div class="text-sm text-gray-500">
                Bank Soal: <a href="{{ route('question_banks.show', $question->questionBank) }}" class="font-medium text-sapta-600 hover:underline">{{ $question->questionBank->name }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Timeline Versi</h3>
                
                <div class="relative border-l-2 border-gray-200 ml-4 space-y-10">
                    @foreach($question->versions->sortByDesc('version') as $version)
                        <div class="relative pl-8">
                            <!-- Timeline Dot -->
                            <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full border-2 border-white 
                                {{ $version->id === $question->current_version_id ? 'bg-sapta-500 ring-4 ring-sapta-100' : 'bg-gray-300' }}">
                            </div>

                            <!-- Content Card -->
                            <div class="bg-white border rounded-lg shadow-sm overflow-hidden {{ $version->id === $question->current_version_id ? 'border-sapta-300' : 'border-gray-200' }}">
                                <div class="px-4 py-3 bg-gray-50 border-b flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-bold text-gray-900">Versi {{ $version->version }}</span>
                                        @if($version->id === $question->current_version_id)
                                            <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Aktif (Current)</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">Historis</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ $version->created_at->format('d M Y H:i') }}
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex items-center space-x-4 mb-4 text-xs text-gray-500">
                                        <div><span class="font-medium text-gray-700">Tipe:</span> {{ Str::title(str_replace('_', ' ', $version->type)) }}</div>
                                        <div><span class="font-medium text-gray-700">Level:</span> {{ $version->cognitive_level ?? '-' }}</div>
                                        <div><span class="font-medium text-gray-700">Kesulitan:</span> {{ $version->difficulty ?? '-' }}</div>
                                        <div><span class="font-medium text-gray-700">Topik:</span> {{ $version->topic ?? '-' }}</div>
                                    </div>
                                    
                                    <div class="prose max-w-none text-gray-800 text-sm mb-4">
                                        {!! nl2br(e($version->content)) !!}
                                    </div>

                                    @if($version->options->count() > 0)
                                        <div class="mt-4 bg-gray-50 rounded-md p-3">
                                            <h5 class="text-xs font-bold text-gray-700 mb-2 uppercase tracking-wider">Opsi Jawaban:</h5>
                                            <ul class="space-y-2">
                                                @foreach($version->options->sortBy('order') as $option)
                                                    <li class="flex items-start text-sm">
                                                        <span class="mr-2 mt-0.5 {{ $option->is_correct ? 'text-green-500' : 'text-gray-400' }}">
                                                            @if($option->is_correct)
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                            @else
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path></svg>
                                                            @endif
                                                        </span>
                                                        <span class="{{ $option->is_correct ? 'font-medium text-gray-900' : 'text-gray-600' }}">
                                                            {!! nl2br(e($option->content)) !!} 
                                                            @if($option->weight > 0)
                                                                <span class="text-xs text-gray-400 ml-1">({{ $option->weight }}%)</span>
                                                            @endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
