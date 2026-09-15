<x-app-layout>
    <x-slot name="title">
        Dashboard Admin
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-sapta-50 flex items-center justify-center flex-shrink-0 text-sapta-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Siswa Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['total_students'] }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Ujian Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['active_exams'] }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Bank Soal</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['total_question_banks'] }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0 text-orange-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Sesi Berjalan</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['ongoing_attempts'] }}</h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-sapta-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Ujian Terbaru
            </h2>
            <div class="space-y-3">
                @forelse($stats['recent_exams'] as $exam)
                    <div class="p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">{{ $exam->title }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ $exam->subject?->name }} | {{ \Carbon\Carbon::parse($exam->start_at)->format('d M Y, H:i') }}</p>
                            </div>
                            <span class="px-2 py-1 bg-{{ $exam->status == 'PUBLISHED' ? 'green' : 'gray' }}-100 text-{{ $exam->status == 'PUBLISHED' ? 'green' : 'gray' }}-700 text-xs font-medium rounded">
                                {{ $exam->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada ujian dibuat.</p>
                @endforelse
            </div>
            @if(count($stats['recent_exams']) > 0)
                <div class="mt-4 text-center">
                    <a href="{{ route('exams.index') }}" class="text-sm text-sapta-600 font-medium hover:underline">Lihat Semua Ujian &rarr;</a>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('exams.create') }}" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-sapta-500 hover:bg-sapta-50 transition text-center group">
                    <div class="w-10 h-10 rounded-full bg-sapta-100 flex items-center justify-center text-sapta-600 mb-2 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Buat Ujian Baru</span>
                </a>
                <a href="{{ route('question_banks.create') }}" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition text-center group">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mb-2 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Buat Bank Soal</span>
                </a>
                <a href="{{ route('users.students.index') }}" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition text-center group">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-2 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Kelola Siswa</span>
                </a>
                <a href="{{ route('system.index') }}" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-gray-500 hover:bg-gray-50 transition text-center group">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 mb-2 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Pengaturan Sistem</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
