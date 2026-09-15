<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
                {{ __('Bank Soal') }}
            </h2>
            @can('create', App\Models\QuestionBank::class)
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('question_banks.create') }}" class="inline-flex items-center px-4 py-2 bg-sapta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sapta-700 focus:bg-sapta-700 active:bg-sapta-900 focus:outline-none focus:ring-2 focus:ring-sapta-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Bank Soal
                </a>
            </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters & Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('question_banks.index') }}" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <x-input-label for="search" :value="__('Cari Nama Bank')" />
                            <x-text-input id="search" class="block mt-1 w-full" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama..." />
                        </div>
                        <div class="w-full md:w-64">
                            <x-input-label for="subject_id" :value="__('Mata Pelajaran')" />
                            <select id="subject_id" name="subject_id" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Semua Mata Pelajaran</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <x-primary-button class="w-full justify-center md:w-auto bg-sapta-900 hover:bg-sapta-800">
                                {{ __('Filter') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List/Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($banks as $bank)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col">
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-4">
                            <div class="px-2 py-1 bg-sapta-100 text-sapta-800 text-xs font-bold rounded">
                                {{ $bank->subject->code }}
                            </div>
                            @if($bank->grade)
                            <div class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-bold rounded">
                                Kelas {{ $bank->grade }}
                            </div>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2">
                            <a href="{{ route('question_banks.show', $bank) }}" class="hover:text-sapta-600 transition-colors">
                                {{ $bank->name }}
                            </a>
                        </h3>
                        <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $bank->description ?? 'Tidak ada deskripsi' }}</p>
                        
                        <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $bank->teacher->user->name }}
                            </div>
                            <div class="flex items-center text-sm text-gray-500 font-medium">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                {{ $bank->questions_count }} Soal
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada bank soal</h3>
                    <p class="mt-1 text-sm text-gray-500">Belum ada bank soal yang tersedia untuk kriteria ini.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $banks->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
