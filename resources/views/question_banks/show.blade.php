<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
                    {{ $questionBank->name }}
                </h2>
                <div class="flex items-center mt-1 text-sm text-gray-500 gap-3">
                    <span class="px-2 py-0.5 bg-sapta-100 text-sapta-800 font-medium rounded">{{ $questionBank->subject->name }}</span>
                    @if($questionBank->grade)
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-800 font-medium rounded">Kelas {{ $questionBank->grade }}</span>
                    @endif
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        {{ $questionBank->teacher->user->name }}
                    </span>
                </div>
            </div>
            
            <div class="mt-4 sm:mt-0 flex space-x-3">
                @can('update', $questionBank)
                <a href="{{ route('question_banks.edit', $questionBank) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sapta-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Edit Bank
                </a>
                @endcan
                @can('create', App\Models\Question::class)
                <a href="{{ route('questions.create', ['question_bank' => $questionBank->id]) }}" class="inline-flex items-center px-4 py-2 bg-sapta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sapta-700 focus:bg-sapta-700 active:bg-sapta-900 focus:outline-none focus:ring-2 focus:ring-sapta-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Soal
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Questions List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Daftar Soal ({{ $questionBank->questions->count() }})</h3>
                    
                    @if($questionBank->questions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 hidden md:table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konten Soal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level / Diff</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($questionBank->questions as $question)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ Str::title(str_replace('_', ' ', $question->currentVersion->type)) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <div class="line-clamp-2">{!! strip_tags($question->currentVersion->content) !!}</div>
                                                <div class="text-xs text-gray-400 mt-1">Versi {{ $question->currentVersion->version }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $question->currentVersion->cognitive_level ?? '-' }} / {{ $question->currentVersion->difficulty ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $question->status === 'PUBLISHED' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $question->status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $question->status === 'REVIEW' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $question->status === 'APPROVED' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $question->status === 'ARCHIVED' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ $question->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-medium align-middle">
                                                <div class="flex flex-wrap justify-end gap-2 min-w-[160px]">
                                                    @can('view', $question)
                                                        <a href="{{ route('questions.preview', $question) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 shadow-sm" title="Preview">Preview</a>
                                                        <a href="{{ route('questions.history', $question) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-gray-600 hover:bg-gray-700 shadow-sm" title="History">History</a>
                                                    @endcan
                                                    @can('update', $question)
                                                        <a href="{{ route('questions.edit', $question) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm" title="Edit">Edit</a>
                                                    @endcan
                                                    @can('publish', $question)
                                                        @if(in_array($question->status, ['DRAFT', 'REVIEW', 'APPROVED', 'ARCHIVED']))
                                                            <form action="{{ route('questions.publish', $question) }}" method="POST" class="m-0">
                                                                @csrf
                                                                <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin mem-publish soal ini?')">Publish</button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                    @can('draft', $question)
                                                        @if(in_array($question->status, ['PUBLISHED', 'ARCHIVED']))
                                                            <form action="{{ route('questions.draft', $question) }}" method="POST" class="m-0">
                                                                @csrf
                                                                <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-yellow-500 hover:bg-yellow-600 shadow-sm" onclick="return confirm('Kembalikan soal ini ke status Draft?')">Draft</button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                    @can('delete', $question)
                                                        @if($question->status !== 'ARCHIVED')
                                                            <form action="{{ route('questions.destroy', $question) }}" method="POST" class="m-0">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin mengarsipkan soal ini?')">Archive</button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <!-- Mobile Cards -->
                            <div class="md:hidden space-y-4">
                                @foreach($questionBank->questions as $question)
                                    <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full 
                                                    {{ $question->status === 'PUBLISHED' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $question->status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $question->status === 'REVIEW' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $question->status === 'APPROVED' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $question->status === 'ARCHIVED' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ $question->status }}
                                            </span>
                                            <span class="text-xs text-gray-500">{{ Str::title(str_replace('_', ' ', $question->currentVersion->type)) }}</span>
                                        </div>
                                        <div class="text-sm text-gray-900 mb-2 line-clamp-3">
                                            {!! strip_tags($question->currentVersion->content) !!}
                                        </div>
                                        <div class="flex justify-between items-center text-xs text-gray-500 mb-3 border-b pb-3">
                                            <span>Versi {{ $question->currentVersion->version }}</span>
                                            <span>{{ $question->currentVersion->cognitive_level ?? '-' }} / {{ $question->currentVersion->difficulty ?? '-' }}</span>
                                        </div>
                                        <div class="flex flex-wrap justify-end gap-2 pt-3 mt-3 border-t border-gray-100">
                                            @can('view', $question)
                                                <a href="{{ route('questions.preview', $question) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 shadow-sm">Preview</a>
                                            @endcan
                                            @can('update', $question)
                                                <a href="{{ route('questions.edit', $question) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">Edit</a>
                                            @endcan
                                            @can('publish', $question)
                                                @if(in_array($question->status, ['DRAFT', 'REVIEW', 'APPROVED', 'ARCHIVED']))
                                                    <form action="{{ route('questions.publish', $question) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin mem-publish soal ini?')">Publish</button>
                                                    </form>
                                                @endif
                                            @endcan
                                            @can('draft', $question)
                                                @if(in_array($question->status, ['PUBLISHED', 'ARCHIVED']))
                                                    <form action="{{ route('questions.draft', $question) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-yellow-500 hover:bg-yellow-600 shadow-sm" onclick="return confirm('Kembalikan soal ini ke status Draft?')">Draft</button>
                                                    </form>
                                                @endif
                                            @endcan
                                            @can('delete', $question)
                                                @if($question->status !== 'ARCHIVED')
                                                    <form action="{{ route('questions.destroy', $question) }}" method="POST" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 shadow-sm" onclick="return confirm('Apakah Anda yakin ingin mengarsipkan soal ini?')">Archive</button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada soal</h3>
                            <p class="mt-1 text-sm text-gray-500">Bank soal ini belum memiliki pertanyaan.</p>
                            @can('create', App\Models\Question::class)
                            <div class="mt-6">
                                <a href="{{ route('questions.create', ['question_bank' => $questionBank->id]) }}" class="inline-flex items-center px-4 py-2 bg-sapta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sapta-700">
                                    Tambah Soal Sekarang
                                </a>
                            </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
