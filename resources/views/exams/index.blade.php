<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
                {{ __('Daftar Ujian (Exams)') }}
            </h2>
            @can('create', App\Models\Exam::class)
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('exams.create') }}" class="inline-flex items-center px-4 py-2 bg-sapta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sapta-700 focus:bg-sapta-700 active:bg-sapta-900 focus:outline-none focus:ring-2 focus:ring-sapta-500 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Ujian Baru
                </a>
            </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($exams->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 hidden md:table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Ujian</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($exams as $exam)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                {{ $exam->code }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <a href="{{ route('exams.show', $exam) }}" class="hover:text-sapta-600 font-medium">
                                                    {{ $exam->title }}
                                                </a>
                                                <div class="text-xs text-gray-400 mt-1">
                                                    Dibuat oleh: {{ $exam->creator->name }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">{{ $exam->subject->code }}</span>
                                                @if($exam->grade)
                                                    <span class="text-xs ml-1">Kls {{ $exam->grade }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $exam->duration }} Menit
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $exam->dynamic_status === 'ONGOING' || $exam->dynamic_status === 'OPEN' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $exam->dynamic_status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $exam->dynamic_status === 'SCHEDULED' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $exam->dynamic_status === 'PAUSED' ? 'bg-orange-100 text-orange-800' : '' }}
                                                    {{ $exam->dynamic_status === 'ENDED' || $exam->dynamic_status === 'ARCHIVED' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ $exam->dynamic_status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right align-middle">
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    <a href="{{ route('exams.show', $exam) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">Detail</a>
                                                    @if($exam->status === 'DRAFT')
                                                        @can('update', $exam)
                                                            <a href="{{ route('exams.edit', $exam) }}" class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-yellow-500 hover:bg-yellow-600 shadow-sm">Edit</a>
                                                        @endcan
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Mobile Cards -->
                            <div class="md:hidden space-y-4">
                                @foreach($exams as $exam)
                                    <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="font-bold text-gray-900">{{ $exam->code }}</span>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                    {{ $exam->dynamic_status === 'ONGOING' || $exam->dynamic_status === 'OPEN' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $exam->dynamic_status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $exam->dynamic_status === 'SCHEDULED' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $exam->dynamic_status === 'PAUSED' ? 'bg-orange-100 text-orange-800' : '' }}
                                                    {{ $exam->dynamic_status === 'ENDED' || $exam->dynamic_status === 'ARCHIVED' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ $exam->dynamic_status }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-900 mb-2 font-medium">
                                            <a href="{{ route('exams.show', $exam) }}">{{ $exam->title }}</a>
                                        </div>
                                        <div class="text-xs text-gray-500 mb-2">
                                            {{ $exam->subject->name }} &bull; {{ $exam->duration }} Menit
                                        </div>
                                        <div class="flex justify-end border-t pt-2 mt-2">
                                            <a href="{{ route('exams.show', $exam) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Detail</a>
                                            @if($exam->status === 'DRAFT')
                                                @can('update', $exam)
                                                    <span class="text-gray-300 mx-2">|</span>
                                                    <a href="{{ route('exams.edit', $exam) }}" class="text-yellow-600 hover:text-yellow-900 text-sm font-medium">Edit</a>
                                                @endcan
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada Ujian</h3>
                            <p class="mt-1 text-sm text-gray-500">Buat ujian baru melalui Exam Builder.</p>
                            @can('create', App\Models\Exam::class)
                            <div class="mt-6">
                                <a href="{{ route('exams.create') }}" class="inline-flex items-center px-4 py-2 bg-sapta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-sapta-700">
                                    Buat Ujian Baru
                                </a>
                            </div>
                            @endcan
                        </div>
                    @endif

                    <div class="mt-6">
                        {{ $exams->links() }}
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
