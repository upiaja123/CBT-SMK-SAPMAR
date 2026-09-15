@php
    $role = auth()->user()->roles->first()?->name;
@endphp

<div class="space-y-1">
    {{-- Dashboard --}}
    <a href="{{ route('dashboard') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-sapta-accent text-white font-medium shadow-md' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0 text-sapta-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-sm truncate">Dashboard</span>
    </a>

    @if ($role === 'super_admin' || $role === 'kurikulum')
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-sapta-300 uppercase tracking-wider">Master Data</div>

        <a href="{{ route('master.academic-years.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('master.academic-years.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-sapta-400"></span>
            Tahun Ajaran
        </a>
        <a href="{{ route('master.majors.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('master.majors.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-sapta-400"></span>
            Jurusan
        </a>
        <a href="{{ route('master.classes.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('master.classes.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-sapta-400"></span>
            Rombel / Kelas
        </a>
        <a href="{{ route('master.subjects.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('master.subjects.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-sapta-400"></span>
            Mata Pelajaran
        </a>

        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-sapta-300 uppercase tracking-wider">Civitas & Pengguna</div>

        <a href="{{ route('users.teachers.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('users.teachers.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-sapta-400"></span>
            Data Guru
        </a>
        <a href="{{ route('users.staff.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('users.staff.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-sapta-400"></span>
            Manajemen Staf
        </a>
        <a href="{{ route('users.students.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('users.students.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
            <span class="w-1.5 h-1.5 rounded-full bg-sapta-400"></span>
            Data Siswa
        </a>

        @if ($role === 'super_admin')
            <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-sapta-300 uppercase tracking-wider">Keamanan & Audit</div>

            <a href="{{ route('audit-logs.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('audit-logs.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-4 h-4 text-sapta-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Audit Log
            </a>

            <a href="{{ route('system.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('system.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-4 h-4 text-sapta-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Sistem Server
            </a>
        @endif
    @endif

    @canany(['question_banks.view', 'exams.create', 'exams.update', 'exams.monitor'])
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-sapta-300 uppercase tracking-wider">Ujian & Penilaian</div>

        @can('question_banks.view')
            <a href="{{ route('question_banks.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('question_banks.*') || request()->routeIs('questions.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-4 h-4 text-sapta-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Bank Soal
            </a>
        @endcan

        @canany(['exams.create', 'exams.update', 'exams.monitor'])
            <a href="{{ route('exams.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('exams.*') && !request()->routeIs('exams.monitoring.*') && !request()->routeIs('exams.results.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-4 h-4 text-sapta-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Manajemen Ujian
            </a>
        @endcanany

    @endcanany

    @if(auth()->user()->hasAnyRole(['super_admin', 'kurikulum', 'guru', 'siswa']))
        <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-sapta-300 uppercase tracking-wider">Laporan</div>
        <a href="{{ route('reports.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('reports.*') || request()->routeIs('exams.results.*') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 text-sapta-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            @if(auth()->user()->hasRole('siswa')) Hasil Ujian Saya @else Laporan Hasil Ujian @endif
        </a>
    @endif

    <div class="pt-3 pb-1 px-3 text-[10px] font-bold text-sapta-300 uppercase tracking-wider">Pengaturan Akun</div>
    <a href="{{ route('profile.edit') }}"
        class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition {{ request()->routeIs('profile.edit') ? 'bg-white/15 text-white font-semibold' : 'text-sapta-100 hover:bg-white/10 hover:text-white' }}">
        <svg class="w-4 h-4 text-sapta-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        Profil Saya
    </a>

</div>