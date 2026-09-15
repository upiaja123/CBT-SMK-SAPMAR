<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
                Detail Ujian: {{ $exam->title }}
            </h2>
            <div class="flex items-center space-x-3">
                @can('update', $exam)
                    @if(in_array($exam->status, ['DRAFT', 'PAUSED']))
                        <a href="{{ route('exams.edit', $exam) }}" class="inline-flex items-center px-4 py-2 bg-yellow-50 border border-yellow-300 rounded-md font-semibold text-xs text-yellow-700 uppercase tracking-widest shadow-sm hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Edit Ujian
                        </a>
                    @endif
                @endcan

                @if($exam->status === 'DRAFT')
                    @can('publish', $exam)
                        <button type="button"
                            onclick="document.getElementById('modal-confirm-publish').classList.remove('hidden')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Publish Ujian
                        </button>
                    @endcan
                @elseif($exam->status === 'PUBLISHED')
                    @can('pause', $exam)
                        <button type="button"
                            onclick="document.getElementById('modal-confirm-pause').classList.remove('hidden')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" title="Jeda ujian untuk mencegah peserta submit jawaban, dan untuk memungkinkan edit soal/peserta">
                            Jeda Ujian (Freeze)
                        </button>
                    @endcan
                @elseif($exam->status === 'PAUSED')
                    @can('pause', $exam)
                        <button type="button"
                            onclick="document.getElementById('modal-confirm-unpause').classList.remove('hidden')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Lanjutkan Ujian (Unfreeze)
                        </button>
                    @endcan
                @endif
                @if(!in_array($exam->status, ['DRAFT']))
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <a href="{{ route('prints.attendance', $exam) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-l-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Daftar Hadir
                        </a>
                        <a href="{{ route('prints.attendance', ['exam' => $exam->id, 'format' => 'excel']) }}" class="inline-flex items-center px-3 py-2 bg-green-50 border border-l-0 border-green-300 rounded-r-md font-semibold text-xs text-green-700 uppercase tracking-widest hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150" title="Export Excel">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </a>
                    </div>
                    <a href="{{ route('prints.berita-acara', $exam) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Berita Acara
                    </a>
                @endif
                <div class="inline-flex rounded-md shadow-sm ml-2" role="group">
                    @can('viewResults', $exam)
                        <a href="{{ route('exams.results.index', $exam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 border border-indigo-300 rounded-l-md font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Hasil Ujian
                        </a>
                        <a href="{{ route('analytics.exam', $exam) }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 border-t border-b border-indigo-300 font-semibold text-xs text-indigo-700 uppercase tracking-widest hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Analitik
                        </a>
                    @endcan
                    @can('grade', $exam)
                        <a href="{{ route('exams.grading.index', $exam) }}" class="inline-flex items-center px-4 py-2 bg-purple-50 border {{ auth()->user()->can('viewResults', $exam) ? 'border-l-0 rounded-r-md' : 'rounded-md' }} border-purple-300 font-semibold text-xs text-purple-700 uppercase tracking-widest hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Koreksi Manual
                        </a>
                    @endcan
                </div>
                <span class="px-3 py-1 text-xs font-bold leading-5 rounded-full 
                    {{ $exam->dynamic_status === 'ONGOING' || $exam->dynamic_status === 'OPEN' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $exam->dynamic_status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                    {{ $exam->dynamic_status === 'SCHEDULED' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $exam->dynamic_status === 'PAUSED' ? 'bg-orange-100 text-orange-800' : '' }}
                    {{ $exam->dynamic_status === 'ENDED' || $exam->dynamic_status === 'ARCHIVED' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ $exam->dynamic_status }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- Hidden form for Publish action --}}
    {{-- Hidden form for Publish action --}}
    @if($exam->status === 'DRAFT')
        @can('publish', $exam)
            <form id="form-publish-exam" action="{{ route('exams.publish', $exam->id) }}" method="POST" style="display:none;">
                @csrf
            </form>

            {{-- Confirmation Modal for Publish --}}
            <div id="modal-confirm-publish" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5)">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Publikasikan Ujian?</h3>
                            <p class="text-sm text-gray-500 mt-1">Ujian yang telah dipublikasikan <strong>tidak dapat diubah soal maupun pesertanya</strong>. Pastikan semua soal dan peserta sudah benar sebelum melanjutkan.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('modal-confirm-publish').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="button" onclick="document.getElementById('form-publish-exam').submit()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            Ya, Publish Sekarang
                        </button>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    @if($exam->status === 'PUBLISHED')
        @can('pause', $exam)
            <form id="form-pause-exam" action="{{ route('exams.pause', $exam->id) }}" method="POST" style="display:none;">
                @csrf
            </form>

            <div id="modal-confirm-pause" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5)">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Jeda Ujian (Freeze)?</h3>
                            <p class="text-sm text-gray-500 mt-1">Saat ujian dijeda, semua siswa yang sedang mengerjakan ujian akan tertahan sesinya dan tidak bisa mengirim jawaban. Anda dapat mengedit soal/peserta selama ujian dijeda.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('modal-confirm-pause').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="button" onclick="document.getElementById('form-pause-exam').submit()" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                            Ya, Jeda Sekarang
                        </button>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    @if($exam->status === 'PAUSED')
        @can('pause', $exam)
            <form id="form-unpause-exam" action="{{ route('exams.unpause', $exam->id) }}" method="POST" style="display:none;">
                @csrf
            </form>

            <div id="modal-confirm-unpause" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.5)">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Lanjutkan Ujian?</h3>
                            <p class="text-sm text-gray-500 mt-1">Ujian akan dilanjutkan dan berstatus Publikasi kembali. Peserta dapat melanjutkan mengirim jawaban mereka.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('modal-confirm-unpause').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="button" onclick="document.getElementById('form-unpause-exam').submit()" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                            Lanjutkan Ujian
                        </button>
                    </div>
                </div>
            </div>
        @endcan
    @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Info Kiri -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Informasi Dasar</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Kode Ujian</dt>
                                <dd class="mt-1 text-sm font-bold text-gray-900">{{ $exam->code }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Mata Pelajaran</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $exam->subject->name }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Target Kelas</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $exam->grade ? 'Kelas ' . $exam->grade : 'Semua Kelas' }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Tipe Ujian</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $exam->exam_type ?? '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $exam->description ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Soal Ujian -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Soal Ujian ({{ $exam->examQuestions->count() }})</h3>
                        @if($exam->examQuestions->count() > 0)
                            <div class="space-y-4">
                                @foreach($exam->examQuestions->sortBy('order') as $eq)
                                    <div class="border border-gray-200 rounded p-4 bg-gray-50">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="font-bold text-gray-700">Soal #{{ $eq->order }}</span>
                                            <span class="text-xs text-gray-500 font-medium">Bobot: {{ $eq->weight }}</span>
                                        </div>
                                        <div class="text-sm text-gray-800 line-clamp-3">
                                            {!! strip_tags($eq->questionVersion->content) !!}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-2">
                                            Versi: {{ $eq->questionVersion->version }} | Tipe: {{ Str::title(str_replace('_', ' ', $eq->questionVersion->type)) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">Belum ada soal terpilih.</p>
                        @endif
                    </div>
                </div>

                <!-- Info Kanan -->
                <div class="space-y-6">
                    <!-- Schedule -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Jadwal & Pengaturan</h3>
                        <ul class="space-y-4">
                            <li>
                                <div class="text-xs text-gray-500">Durasi</div>
                                <div class="text-sm font-bold text-gray-900">{{ $exam->duration }} Menit</div>
                            </li>
                            <li>
                                <div class="text-xs text-gray-500">Waktu Mulai</div>
                                <div class="text-sm text-gray-900">{{ $exam->start_at ? $exam->start_at->format('d M Y H:i') : 'Bebas' }}</div>
                            </li>
                            <li>
                                <div class="text-xs text-gray-500">Batas Waktu Akses</div>
                                <div class="text-sm text-gray-900">{{ $exam->end_at ? $exam->end_at->format('d M Y H:i') : 'Bebas' }}</div>
                            </li>
                            <li class="pt-2 border-t">
                                <div class="text-xs text-gray-500">Token Ujian</div>
                                <div class="text-lg font-mono font-bold text-sapta-600 tracking-wider">{{ $exam->token ?? 'NONE' }}</div>
                            </li>
                            <li class="pt-2 border-t space-y-1">
                                <div class="text-xs text-gray-500 mb-1">Pengacakan (Diterapkan saat CBT)</div>
                                <div class="text-sm text-gray-900 flex justify-between">
                                    <span>Acak Soal:</span>
                                    <span class="font-bold {{ $exam->random_question ? 'text-green-600' : 'text-gray-400' }}">{{ $exam->random_question ? 'Ya' : 'Tidak' }}</span>
                                </div>
                                <div class="text-sm text-gray-900 flex justify-between">
                                    <span>Acak Opsi:</span>
                                    <span class="font-bold {{ $exam->random_option ? 'text-green-600' : 'text-gray-400' }}">{{ $exam->random_option ? 'Ya' : 'Tidak' }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Participants -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center border-b pb-2 mb-4">
                            <h3 class="text-lg font-bold text-gray-900">Peserta Terdaftar</h3>
                            @can('update', $exam)
                                <div class="flex space-x-2">
                                    @if(in_array($exam->status, ['DRAFT', 'PAUSED']))
                                    <button onclick="document.getElementById('ekstra-modal').classList.remove('hidden')" class="inline-flex items-center px-3 py-1 bg-green-50 border border-green-200 rounded text-xs text-green-700 font-semibold hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        + Tambah Ekstra
                                    </button>
                                    @endif
                                    <button onclick="document.getElementById('susulan-modal').classList.remove('hidden')" class="inline-flex items-center px-3 py-1 bg-indigo-50 border border-indigo-200 rounded text-xs text-indigo-700 font-semibold hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        + Atur Susulan
                                    </button>
                                </div>
                            @endcan
                        </div>
                        
                        @php
                            $classes = $exam->participants->whereNotNull('school_class_id')->where('is_susulan', false);
                            $students = $exam->participants->whereNotNull('student_id')->where('is_susulan', false);
                            $susulans = $exam->participants->where('is_susulan', true);
                        @endphp
                        
                        <div class="mb-4">
                            <div class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Rombongan Kelas ({{ $classes->count() }})</div>
                            @if($classes->count() > 0)
                                <ul class="text-sm text-gray-700 space-y-1">
                                    @foreach($classes as $c)
                                        <li class="flex items-center justify-between group">
                                            <span>&bull; {{ $c->schoolClass->name }}</span>
                                            @can('update', $exam)
                                                @if(in_array($exam->status, ['DRAFT', 'PAUSED']))
                                                <form action="{{ route('exams.remove_participant', [$exam, $c]) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 transition-colors p-2 flex items-center justify-center" title="Hapus Kelas">
                                                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                                @endif
                                            @endcan
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-sm text-gray-400 italic">Tidak ada kelas</span>
                            @endif
                        </div>

                        <div class="mb-4">
                            <div class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Siswa Ekstra ({{ $students->count() }})</div>
                            @if($students->count() > 0)
                                <ul class="text-sm text-gray-700 space-y-1">
                                    @foreach($students as $s)
                                        <li class="flex items-center justify-between group">
                                            <span>&bull; {{ $s->student->user->name }}</span>
                                            @can('update', $exam)
                                                @if(in_array($exam->status, ['DRAFT', 'PAUSED']))
                                                <form action="{{ route('exams.remove_participant', [$exam, $s]) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 transition-colors p-2 flex items-center justify-center" title="Hapus">
                                                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                                @endif
                                            @endcan
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-sm text-gray-400 italic">Tidak ada siswa ekstra</span>
                            @endif
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-2">Peserta Susulan ({{ $susulans->count() }})</div>
                            @if($susulans->count() > 0)
                                <ul class="text-sm text-gray-700 space-y-2">
                                    @foreach($susulans as $s)
                                        <li class="p-2 border border-orange-200 bg-orange-50 rounded flex justify-between items-start group">
                                            <div>
                                                <div class="font-semibold">{{ $s->student->user->name }}</div>
                                                <div class="text-xs text-orange-800">
                                                    Mulai: {{ $s->susulan_start_at ? \Carbon\Carbon::parse($s->susulan_start_at)->format('d M Y H:i') : 'Sekarang' }}<br>
                                                    Batas: {{ \Carbon\Carbon::parse($s->susulan_end_at)->format('d M Y H:i') }}
                                                </div>
                                            </div>
                                            @can('update', $exam)
                                                <form action="{{ route('exams.remove_participant', [$exam, $s]) }}" method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 transition-colors p-2 flex items-center justify-center" title="Hapus Susulan">
                                                        <svg class="w-5 h-5 mt-1 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-sm text-gray-400 italic">Tidak ada peserta susulan</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Susulan Modal -->
    <div id="susulan-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('susulan-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('exams.susulan', $exam) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Atur Ujian Susulan</h3>
                        
                        <div class="space-y-4">
                            <div x-data="{ search: '', results: [], selectedName: '', studentId: '' }">
                                <label class="block text-sm font-medium text-gray-700">Cari Siswa</label>
                                <input type="hidden" name="student_id" id="susulan_student_id" required>
                                <input type="text" x-model="search" @input.debounce.400ms="
                                    if (search.length < 2) { results = []; return; }
                                    fetch('/api/students/search?q=' + encodeURIComponent(search), {headers: {'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest'}})
                                        .then(r => r.json()).then(d => results = d.slice(0, 15))
                                "
                                placeholder="Ketik nama siswa..."
                                autocomplete="off"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                
                                <div x-show="results.length > 0" class="mt-1 border border-gray-200 rounded-md bg-white shadow-lg max-h-40 overflow-y-auto z-10 relative">
                                    <template x-for="s in results" :key="s.id">
                                        <div @click="document.getElementById('susulan_student_id').value = s.id; document.getElementById('btn_submit_susulan').disabled = false; selectedName = s.name; search = s.name + ' (' + s.class_name + ')'; results = []"
                                             class="px-3 py-2 cursor-pointer hover:bg-indigo-50 text-sm">
                                            <span x-text="s.name" class="font-medium"></span>
                                            <span x-text="'(' + s.class_name + ')'" class="text-gray-400 text-xs ml-1"></span>
                                        </div>
                                    </template>
                                </div>
                                <p x-show="document.getElementById('susulan_student_id') && document.getElementById('susulan_student_id').value" class="text-xs text-green-600 mt-1">✓ Siswa terpilih: <span x-text="selectedName" class="font-semibold"></span></p>
                                <p x-show="(!document.getElementById('susulan_student_id') || !document.getElementById('susulan_student_id').value) && search.length > 0" class="text-xs text-gray-400 mt-1">Pilih siswa dari daftar.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waktu Mulai (Opsional)</label>
                                <input type="datetime-local" name="susulan_start_at" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <p class="text-xs text-gray-500 mt-1">Kosongkan jika siswa bisa langsung mulai sekarang.</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Batas Waktu (Wajib)</label>
                                <input type="datetime-local" name="susulan_end_at" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" id="btn_submit_susulan" disabled class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Susulan
                        </button>
                        <button type="button" onclick="document.getElementById('susulan-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Ekstra Modal -->
    <div id="ekstra-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('ekstra-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('exams.add_ekstra', $exam) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Tambah Siswa Ekstra</h3>
                        <p class="text-sm text-gray-500 mb-4">Siswa ekstra akan mengerjakan ujian pada waktu yang sama dengan waktu normal ujian ini.</p>
                        
                        <div class="space-y-4">
                            <div x-data="{ search: '', results: [], selectedName: '', studentId: '' }">
                                <label class="block text-sm font-medium text-gray-700">Cari Siswa</label>
                                <input type="hidden" name="student_id" id="ekstra_student_id" required>
                                <input type="text" x-model="search" @input.debounce.400ms="
                                    if (search.length < 2) { results = []; return; }
                                    fetch('/api/students/search?q=' + encodeURIComponent(search), {headers: {'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest'}})
                                        .then(r => r.json()).then(d => results = d.slice(0, 15))
                                "
                                placeholder="Ketik nama siswa..."
                                autocomplete="off"
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                
                                <div x-show="results.length > 0" class="mt-1 border border-gray-200 rounded-md bg-white shadow-lg max-h-40 overflow-y-auto z-10 relative">
                                    <template x-for="s in results" :key="s.id">
                                        <div @click="document.getElementById('ekstra_student_id').value = s.id; document.getElementById('btn_submit_ekstra').disabled = false; selectedName = s.name; search = s.name + ' (' + s.class_name + ')'; results = []"
                                             class="px-3 py-2 cursor-pointer hover:bg-green-50 text-sm">
                                            <span x-text="s.name" class="font-medium"></span>
                                            <span x-text="'(' + s.class_name + ')'" class="text-gray-400 text-xs ml-1"></span>
                                        </div>
                                    </template>
                                </div>
                                <p x-show="document.getElementById('ekstra_student_id') && document.getElementById('ekstra_student_id').value" class="text-xs text-green-600 mt-1">✓ Siswa terpilih: <span x-text="selectedName" class="font-semibold"></span></p>
                                <p x-show="(!document.getElementById('ekstra_student_id') || !document.getElementById('ekstra_student_id').value) && search.length > 0" class="text-xs text-gray-400 mt-1">Pilih siswa dari daftar.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" id="btn_submit_ekstra" disabled class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm">
                            Tambah Siswa Ekstra
                        </button>
                        <button type="button" onclick="document.getElementById('ekstra-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
