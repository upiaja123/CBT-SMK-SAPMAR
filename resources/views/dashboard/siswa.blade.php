<x-app-layout>
    <x-slot name="title">
        Dashboard Ujian Siswa
    </x-slot>

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-gradient-to-r from-sapta-700 to-sapta-600 rounded-2xl p-6 md:p-8 text-white shadow-lg mb-8 relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold mb-2">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p class="text-sapta-100 mb-6">NISN: {{ auth()->user()->student?->nisn ?? '-' }} | Kelas: {{ auth()->user()->student?->schoolClass?->name ?? 'Belum ada kelas' }}</p>
            
            <div class="flex flex-wrap gap-4">
                <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-xs text-sapta-100">Ujian Tersedia</p>
                        <p class="font-bold">{{ count($stats['available_exams']) }}</p>
                    </div>
                </div>
                <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-xs text-sapta-100">Ujian Selesai</p>
                        <p class="font-bold">{{ $stats['completed_exams'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <svg class="absolute right-0 bottom-0 text-white/10 w-64 h-64 transform translate-x-16 translate-y-16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"/></svg>
    </div>

    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-sapta-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
        Daftar Ujian Anda Hari Ini
    </h3>

    <div class="space-y-4">
        @forelse($stats['available_exams'] as $exam)
            @php
                $attempt = $exam->attempts->first();
                $isCompleted = $attempt && $attempt->status === 'FINALIZED';
                $isInProgress = $attempt && $attempt->status === 'IN_PROGRESS';
                $isUpcoming = \Carbon\Carbon::now()->lt($exam->start_at);
                $isClosed = \Carbon\Carbon::now()->gt($exam->end_at);
            @endphp
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h4 class="font-bold text-gray-900 text-lg">{{ $exam->title }}</h4>
                        @if($isCompleted)
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase tracking-wider">SELESAI</span>
                        @elseif($isInProgress)
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 uppercase tracking-wider">SEDANG MENGERJAKAN</span>
                        @elseif($isUpcoming)
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-700 uppercase tracking-wider">BELUM MULAI</span>
                        @elseif($isClosed && !$isCompleted && !$isInProgress)
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wider">WAKTU HABIS</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-sapta-100 text-sapta-700 uppercase tracking-wider">TERSEDIA</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            {{ $exam->subject?->name }}
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $exam->duration }} Menit
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ \Carbon\Carbon::parse($exam->start_at)->format('d M Y H:i') }} - {{ \Carbon\Carbon::parse($exam->end_at)->format('H:i') }}
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0 mt-4 md:mt-0">
                    @if($isCompleted)
                        <a href="{{ route('exams.attempts.result', ['exam' => $exam->id, 'attempt' => $attempt->id]) }}" class="block w-full md:w-auto text-center bg-gray-800 text-white hover:bg-gray-900 font-medium px-6 py-2.5 rounded-xl transition shadow-sm">
                            Lihat Hasil
                        </a>
                    @elseif($isInProgress)
                        <a href="{{ route('exams.attempts.session', ['exam' => $exam->id, 'attempt' => $attempt->id]) }}" class="block w-full md:w-auto text-center bg-blue-600 text-white hover:bg-blue-700 font-medium px-6 py-2.5 rounded-xl transition shadow-sm">
                            Lanjutkan Ujian
                        </a>
                    @elseif($isUpcoming)
                        <button disabled class="w-full md:w-auto bg-gray-100 text-gray-400 cursor-not-allowed font-medium px-6 py-2.5 rounded-xl border border-gray-200">
                            Belum Waktunya
                        </button>
                    @elseif($isClosed && !$isCompleted && !$isInProgress)
                        <button disabled class="w-full md:w-auto bg-red-50 text-red-400 cursor-not-allowed font-medium px-6 py-2.5 rounded-xl border border-red-100">
                            Waktu Habis
                        </button>
                    @else
                        {{-- Available to start --}}
                        <form action="{{ route('exams.attempts.store', $exam) }}" method="POST">
                            @csrf
                            @if($exam->token)
                                <div class="flex flex-col md:flex-row gap-2">
                                    <input type="text" name="token" required placeholder="Masukkan Token Ujian" class="w-full md:w-48 px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-sapta-500 outline-none uppercase font-mono tracking-widest text-center md:text-left">
                                    <button type="submit" class="w-full md:w-auto text-center bg-sapta-600 text-white hover:bg-sapta-700 font-bold px-6 py-2.5 rounded-xl transition shadow-sm">
                                        Mulai Ujian
                                    </button>
                                </div>
                            @else
                                <button type="submit" class="w-full md:w-auto text-center bg-sapta-600 text-white hover:bg-sapta-700 font-bold px-6 py-2.5 rounded-xl transition shadow-sm">
                                    Mulai Ujian
                                </button>
                            @endif
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">Tidak Ada Ujian</h3>
                <p class="text-gray-500">Saat ini tidak ada jadwal ujian yang tersedia untuk Anda.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
