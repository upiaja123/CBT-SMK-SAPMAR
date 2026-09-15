<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
            Buat Ujian Baru (Exam Builder)
        </h2>
    </x-slot>

    <div class="py-12" x-data="examBuilder">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Stepper -->
            <div class="mb-8 overflow-x-auto">
                <nav aria-label="Progress">
                    <ol role="list" class="flex items-center">
                        <template x-for="(step, index) in steps" :key="index">
                            <li class="relative" :class="index !== steps.length - 1 ? 'flex-1' : ''">
                                <!-- Connecting line -->
                                <div class="absolute top-4 left-1/2 w-full flex items-center" aria-hidden="true" x-show="index !== steps.length - 1">
                                    <div class="h-0.5 w-full" :class="currentStep > index + 1 ? 'bg-sapta-600' : 'bg-gray-200'"></div>
                                </div>
                                
                                <div class="relative flex flex-col items-center">
                                    <button type="button" @click="goToStep(index + 1)" 
                                        class="relative flex h-8 w-8 items-center justify-center rounded-full z-10"
                                        :class="currentStep > index + 1 ? 'bg-sapta-600 hover:bg-sapta-900' : (currentStep === index + 1 ? 'border-2 border-sapta-600 bg-white' : 'border-2 border-gray-300 bg-white hover:border-gray-400')"
                                        :disabled="!canGoToStep(index + 1)">
                                        
                                        <template x-if="currentStep > index + 1">
                                            <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                        </template>
                                        <template x-if="currentStep === index + 1">
                                            <span class="h-2.5 w-2.5 rounded-full bg-sapta-600"></span>
                                        </template>
                                    </button>
                                    
                                    <div class="mt-2 text-center text-[10px] sm:text-xs font-medium leading-tight max-w-[64px] sm:max-w-[80px]" 
                                        :class="currentStep === index + 1 ? 'text-sapta-600' : (currentStep > index + 1 ? 'text-gray-900' : 'text-gray-500')" 
                                        x-text="step.title"></div>
                                </div>
                            </li>
                        </template>
                    </ol>
                </nav>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <!-- STEP 1: Basic Info -->
                <div x-show="currentStep === 1" class="p-6">
                    <h3 class="text-lg font-bold mb-4">Langkah 1: Informasi Dasar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="title" value="Judul Ujian *" />
                            <x-text-input id="title" x-model="form.title" type="text" class="mt-1 block w-full" placeholder="Contoh: Ujian Tengah Semester Ganjil" />
                        </div>
                        <div>
                            <x-input-label for="code" value="Kode Ujian *" />
                            <x-text-input id="code" x-model="form.code" type="text" class="mt-1 block w-full uppercase" placeholder="UTS-MTK-2026" />
                            <p class="text-xs text-gray-500 mt-1">Kode harus unik.</p>
                        </div>
                        <div>
                            <x-input-label for="subject_id" value="Mata Pelajaran *" />
                            @if($subjects->isEmpty())
                                <div class="mt-1 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm flex items-start gap-2">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <p>Anda belum ditugaskan untuk mata pelajaran apapun. Silakan hubungi Administrator atau Kurikulum untuk mengonfigurasi data pengajar Anda.</p>
                                </div>
                                <select id="subject_id" x-model="form.subject_id" disabled class="border-gray-300 bg-gray-100 text-gray-400 rounded-md shadow-sm block mt-2 w-full">
                                    <option value="">Belum ada mata pelajaran</option>
                                </select>
                            @else
                                <select id="subject_id" x-model="form.subject_id" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="">-- Pilih --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div>
                            <x-input-label for="grade" value="Target Kelas" />
                            <select id="grade" x-model="form.grade" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">Semua Kelas</option>
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="description" value="Deskripsi / Instruksi" />
                            <textarea id="description" x-model="form.description" rows="3" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block w-full mt-1"></textarea>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Schedule -->
                <div x-show="currentStep === 2" class="p-6" style="display: none;">
                    <h3 class="text-lg font-bold mb-4">Langkah 2: Jadwal & Durasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="duration" value="Durasi Ujian (Menit) *" />
                            <x-text-input id="duration" x-model.number="form.duration" type="number" min="1" class="mt-1 block w-full" />
                        </div>
                        <div class="hidden md:block"></div>
                        <div>
                            <x-input-label for="start_at" value="Waktu Mulai *" />
                            <x-text-input id="start_at" x-model="form.start_at" type="datetime-local" class="mt-1 block w-full" required />
                            <p class="text-xs text-gray-500 mt-1">Waktu dimulainya ujian secara otomatis.</p>
                        </div>
                        <div>
                            <x-input-label for="end_at" value="Batas Waktu Akses *" />
                            <x-text-input id="end_at" x-model="form.end_at" type="datetime-local" class="mt-1 block w-full" required />
                            <p class="text-xs text-gray-500 mt-1">Ujian akan berakhir dan ditutup otomatis pada waktu ini.</p>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Participants -->
                <div x-show="currentStep === 3" class="p-6" style="display: none;">
                    <h3 class="text-lg font-bold mb-4">Langkah 3: Peserta Ujian</h3>
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-800">
                        Pilih rombongan belajar (Kelas) secara keseluruhan, atau pilih siswa secara individual. Anda dapat menggabungkan keduanya.
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Select Classes -->
                        <div class="border rounded-md p-4">
                            <h4 class="font-medium border-b pb-2 mb-3">Pilih Kelas</h4>
                            <div class="max-h-64 overflow-y-auto space-y-2">
                                @if($classes->isEmpty())
                                    <div class="p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm flex items-start gap-2">
                                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <p>Anda belum ditugaskan untuk mengajar di kelas manapun. Silakan hubungi Administrator atau Kurikulum.</p>
                                    </div>
                                @else
                                    @foreach($classes as $class)
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox" value="{{ $class->id }}" x-model="form.school_class_ids" class="rounded border-gray-300 text-sapta-600 focus:ring-sapta-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $class->name }}</span>
                                    </label>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Select Individual Students -->
                        <div class="border rounded-md p-4 flex flex-col">
                            <h4 class="font-medium border-b pb-2 mb-3">Pilih Siswa Ekstra (Individual)</h4>
                            
                            <input type="text" x-model="studentSearch" placeholder="Cari nama siswa..." class="mb-3 block w-full text-sm border-gray-300 rounded-md">
                            
                            <div class="flex-1 max-h-48 overflow-y-auto space-y-2">
                                <template x-for="student in filteredStudents" :key="student.id">
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox" :value="student.id" x-model="form.student_ids" class="rounded border-gray-300 text-sapta-600 focus:ring-sapta-500">
                                        <div class="ml-2">
                                            <div class="text-sm text-gray-700" x-text="student.name"></div>
                                            <div class="text-xs text-gray-400" x-text="student.class_name"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-sm text-gray-600 bg-gray-50 p-3 rounded-md border">
                        <span class="font-bold text-gray-900" x-text="form.school_class_ids.length"></span> Kelas Terpilih <br/>
                        <span class="font-bold text-gray-900" x-text="form.student_ids.length"></span> Siswa Ekstra Terpilih
                    </div>
                </div>

                <!-- STEP 4: Questions -->
                <div x-show="currentStep === 4" class="p-6" style="display: none;">
                    <h3 class="text-lg font-bold mb-4">Langkah 4: Pilih Soal dari Bank</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Browse Bank -->
                        <div class="border rounded-md p-4 lg:col-span-1 flex flex-col h-[500px]">
                            <h4 class="font-medium border-b pb-2 mb-3">Bank Soal Anda</h4>
                            <div class="flex-1 overflow-y-auto space-y-3">
                                @foreach($banks as $bank)
                                    <div class="border border-gray-200 rounded p-3 bg-white" x-show="form.subject_id == '' || form.subject_id == {{ $bank->subject_id }}">
                                        <h5 class="font-bold text-sm text-gray-900">{{ $bank->name }}</h5>
                                        <p class="text-xs text-gray-500 mb-2">{{ $bank->subject->name }} | {{ $bank->questions->count() }} Soal</p>
                                        
                                        <div class="space-y-2 mt-2 pt-2 border-t border-gray-100 max-h-40 overflow-y-auto">
                                            @php $hasPublished = false; @endphp
                                            @foreach($bank->questions as $q)
                                                @if($q->status === 'PUBLISHED')
                                                    @php $hasPublished = true; @endphp
                                                    @php
                                                        $qLabel = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($q->currentVersion->content))), 50);
                                                    @endphp
                                                    <div class="flex justify-between items-center bg-gray-50 p-1.5 rounded text-xs">
                                                        <span class="line-clamp-1 flex-1 mr-2" title="{{ strip_tags($q->currentVersion->content) }}">
                                                            {{ Str::limit(strip_tags($q->currentVersion->content), 20) }}
                                                        </span>
                                                        <button type="button"
                                                                data-qid="{{ $q->current_version_id }}"
                                                                data-qlabel="{{ e($qLabel) }}"
                                                                @click="addQuestionToExam($event.currentTarget.dataset.qid, $event.currentTarget.dataset.qlabel)"
                                                                class="px-2 py-1 bg-sapta-100 text-sapta-700 hover:bg-sapta-200 rounded shrink-0">
                                                            Tambah
                                                        </button>
                                                    </div>
                                                @endif
                                            @endforeach
                                            
                                            @if(!$hasPublished)
                                                <div class="text-xs text-gray-400 text-center py-2">
                                                    Belum ada soal berstatus PUBLISHED
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Selected Questions -->
                        <div class="border rounded-md p-4 lg:col-span-2 flex flex-col h-[500px]">
                            <h4 class="font-medium border-b pb-2 mb-3 flex justify-between items-center">
                                <span>Soal Terpilih untuk Ujian Ini</span>
                                <span class="bg-sapta-100 text-sapta-800 text-xs px-2 py-1 rounded-full"><span x-text="form.questions.length"></span> Soal</span>
                            </h4>
                            
                            <div class="flex-1 overflow-y-auto space-y-2 bg-gray-50 p-2 rounded" x-init="initSortable($el)">
                                <template x-for="(q, index) in form.questions" :key="q.question_version_id">
                                    <div class="bg-white border border-gray-200 rounded p-3 flex items-center shadow-sm cursor-move sortable-item">
                                        <div class="cursor-move text-gray-300 mr-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                        </div>
                                        <div class="font-bold text-gray-400 mr-3" x-text="(index + 1) + '.'"></div>
                                        <div class="flex-1 text-sm text-gray-800 line-clamp-2" x-text="q.previewText"></div>
                                        <div class="mx-4 flex items-center">
                                            <span class="text-xs text-gray-500 mr-2">Bobot:</span>
                                            <input type="number" x-model.number="q.weight" class="w-16 h-8 text-sm border-gray-300 rounded" min="1">
                                        </div>
                                        <button type="button" @click="removeQuestionFromExam(index)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </template>
                                
                                <div x-show="form.questions.length === 0" class="text-center py-8 text-gray-400 text-sm">
                                    Belum ada soal terpilih. Tambahkan dari Bank Soal di sebelah kiri.
                                    <br/><span class="text-xs">Hanya soal dengan status PUBLISHED yang dapat dipilih.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Randomization & Scoring -->
                <div x-show="currentStep === 5" class="p-6" style="display: none;">
                    <h3 class="text-lg font-bold mb-4">Langkah 5: Pengacakan</h3>
                    <div class="space-y-4">
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <div class="flex items-center h-5">
                                <input type="checkbox" x-model="form.random_question" class="focus:ring-sapta-500 h-5 w-5 text-sapta-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-bold text-gray-900 text-base">Acak Urutan Soal</span>
                                <p class="text-gray-500 mt-1">Urutan soal akan diacak secara berbeda untuk setiap peserta ujian pada saat ujian dimulai (CBT Engine).</p>
                            </div>
                        </label>

                        <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <div class="flex items-center h-5">
                                <input type="checkbox" x-model="form.random_option" class="focus:ring-sapta-500 h-5 w-5 text-sapta-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-bold text-gray-900 text-base">Acak Urutan Opsi (Pilihan Ganda)</span>
                                <p class="text-gray-500 mt-1">Opsi jawaban akan diacak secara berbeda untuk setiap peserta (Kecuali Tipe Benar/Salah).</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- STEP 6: Review -->
                <div x-show="currentStep === 6" class="p-6" style="display: none;">
                    <h3 class="text-lg font-bold mb-4 text-gray-900 border-b pb-2">Langkah 6: Review & Simpan</h3>
                    
                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Judul Ujian</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold" x-text="form.title || '-'"></dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Kode Ujian</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold" x-text="form.code || '-'"></dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Durasi</dt>
                                <dd class="mt-1 text-sm text-gray-900"><span x-text="form.duration"></span> Menit</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Peserta</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span x-text="form.school_class_ids.length"></span> Kelas, <span x-text="form.student_ids.length"></span> Siswa Individu
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Jumlah Soal</dt>
                                <dd class="mt-1 text-sm text-gray-900"><span x-text="form.questions.length"></span> Soal</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Pengacakan</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    Soal: <span class="font-bold" x-text="form.random_question ? 'Ya' : 'Tidak'"></span> <br/>
                                    Opsi: <span class="font-bold" x-text="form.random_option ? 'Ya' : 'Tidak'"></span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div x-show="errorMessage" class="mt-4 p-4 bg-red-50 text-red-700 border border-red-200 rounded">
                        <strong>Gagal:</strong> <span x-text="errorMessage"></span>
                    </div>
                </div>

                <!-- Footer Nav -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between rounded-b-lg">
                    <button type="button" @click="prevStep" x-show="currentStep > 1" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                        Kembali
                    </button>
                    <div x-show="currentStep === 1"></div>
                    
                    <button type="button" @click="nextStep" x-show="currentStep < 6" class="inline-flex items-center px-4 py-2 bg-sapta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sapta-700">
                        Lanjut
                    </button>
                    
                    <button type="button" @click="submitExam" x-show="currentStep === 6" :disabled="isSubmitting" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 disabled:opacity-50">
                        <span x-show="!isSubmitting">Simpan Ujian Sekarang</span>
                        <span x-show="isSubmitting">Menyimpan...</span>
                    </button>
                </div>
            </div>
            
        </div>
    </div>

    @push('scripts')
    <script>
        const allStudents = @json($students);
        const existingExam = @json(isset($exam) ? $exam : null);

        document.addEventListener('alpine:init', () => {
        Alpine.data('examBuilder', () => {
            let defaultForm = {
                title: '',
                code: '',
                subject_id: '',
                description: '',
                grade: '',
                duration: 90,
                start_at: '',
                end_at: '',
                school_class_ids: [],
                student_ids: [],
                questions: [],
                random_question: true,
                random_option: true
            };

            if (existingExam) {
                defaultForm.title = existingExam.title;
                defaultForm.code = existingExam.code;
                defaultForm.subject_id = existingExam.subject_id;
                defaultForm.description = existingExam.description || '';
                defaultForm.grade = existingExam.grade || '';
                defaultForm.duration = existingExam.duration;
                // Format datetime-local requires YYYY-MM-DDTHH:MM
                defaultForm.start_at = existingExam.start_at ? existingExam.start_at.substring(0, 16) : '';
                defaultForm.end_at = existingExam.end_at ? existingExam.end_at.substring(0, 16) : '';
                defaultForm.random_question = existingExam.random_question === 1 || existingExam.random_question === true;
                defaultForm.random_option = existingExam.random_option === 1 || existingExam.random_option === true;

                if (existingExam.participants && existingExam.participants.length > 0) {
                    existingExam.participants.forEach(p => {
                        if (p.school_class_id) defaultForm.school_class_ids.push(p.school_class_id.toString());
                        if (p.student_id) defaultForm.student_ids.push(p.student_id.toString());
                    });
                }

                if (existingExam.exam_questions && existingExam.exam_questions.length > 0) {
                    existingExam.exam_questions.forEach(eq => {
                        if (eq.question_version) {
                            defaultForm.questions.push({
                                question_version_id: eq.question_version_id,
                                previewText: eq.question_version.content.replace(/<[^>]*>?/gm, '').substring(0, 50),
                                order: eq.order,
                                weight: parseFloat(eq.weight)
                            });
                        }
                    });
                }
            }

            return {
                steps: [
                    { id: 1, title: 'Info Dasar' },
                    { id: 2, title: 'Jadwal' },
                    { id: 3, title: 'Peserta' },
                    { id: 4, title: 'Soal' },
                    { id: 5, title: 'Acak' },
                    { id: 6, title: 'Review' }
                ],
                currentStep: 1,
                studentSearch: '',
                isSubmitting: false,
                errorMessage: '',
                form: defaultForm,

                get filteredStudents() {
                    const s = this.studentSearch.toLowerCase();
                    let results = allStudents;
                    if (s !== '') {
                        results = allStudents.filter(st => st.user.name.toLowerCase().includes(s));
                    }
                    return results.slice(0, 50).map(st => ({
                        id: st.id,
                        name: st.user.name,
                        class_name: st.school_class ? st.school_class.name : '-'
                    }));
                },

                nextStep() {
                    // Simple Validation per step
                    if (this.currentStep === 1) {
                        if (!this.form.title || !this.form.code || !this.form.subject_id) {
                            alert('Judul, Kode, dan Mata Pelajaran wajib diisi.');
                            return;
                        }
                    }
                    if (this.currentStep === 2) {
                        if (!this.form.duration || this.form.duration < 1) {
                            alert('Durasi wajib diisi dan minimal 1 menit.');
                            return;
                        }
                    }
                    if (this.currentStep === 4) {
                        if (this.form.questions.length === 0) {
                            if (!confirm('Anda belum memilih soal. Yakin ingin lanjut?')) return;
                        }
                    }

                    if (this.currentStep < 6) this.currentStep++;
                },

                prevStep() {
                    if (this.currentStep > 1) this.currentStep--;
                },

                goToStep(step) {
                    if (this.canGoToStep(step)) {
                        this.currentStep = step;
                    }
                },

                canGoToStep(step) {
                    // Prevent skipping forward if basic validation fails
                    if (step > 1 && (!this.form.title || !this.form.code || !this.form.subject_id)) return false;
                    if (step > 2 && (!this.form.duration)) return false;
                    return true;
                },

                addQuestionToExam(versionId, previewText) {
                    // Check if already added
                    if (this.form.questions.find(q => q.question_version_id === versionId)) return;
                    
                    this.form.questions.push({
                        question_version_id: versionId,
                        previewText: previewText,
                        order: this.form.questions.length + 1,
                        weight: 1
                    });
                },

                removeQuestionFromExam(index) {
                    this.form.questions.splice(index, 1);
                    // reorder
                    this.form.questions.forEach((q, i) => q.order = i + 1);
                },
                
                initSortable(el) {
                    if (typeof Sortable === 'undefined') return;
                    Sortable.create(el, {
                        handle: '.cursor-move',
                        animation: 150,
                        onEnd: (evt) => {
                            if (evt.oldIndex === evt.newIndex) return;
                            
                            // Alpine's x-for with template adds a hidden comment node, so index is slightly off in raw DOM if not careful,
                            // but Sortable manages it. We need to update our array.
                            const item = this.form.questions.splice(evt.oldIndex, 1)[0];
                            this.form.questions.splice(evt.newIndex, 0, item);
                            
                            // reorder
                            this.form.questions.forEach((q, i) => q.order = i + 1);
                        }
                    });
                },

                async submitExam() {
                    this.isSubmitting = true;
                    this.errorMessage = '';

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        // 1. Create or Update Exam
                        const url = existingExam ? `/exams/${existingExam.id}` : '/exams';
                        const method = existingExam ? 'PUT' : 'POST';

                        const examRes = await fetch(url, {
                            method: method,
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({
                                title: this.form.title,
                                code: this.form.code,
                                subject_id: this.form.subject_id,
                                description: this.form.description,
                                grade: this.form.grade,
                                duration: this.form.duration,
                                start_at: this.form.start_at,
                                end_at: this.form.end_at,
                                random_question: this.form.random_question,
                                random_option: this.form.random_option
                            })
                        });

                        const examData = await examRes.json();
                        if (!examRes.ok) throw new Error(examData.message || 'Gagal menyimpan ujian.');
                        
                        const examId = examData.exam.id;

                        // 2. Add Participants
                        if (this.form.school_class_ids.length > 0 || this.form.student_ids.length > 0) {
                            const partRes = await fetch(`/exams/${examId}/participants`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({
                                    school_class_ids: this.form.school_class_ids,
                                    student_ids: this.form.student_ids
                                })
                            });
                            if (!partRes.ok) throw new Error('Gagal menyimpan peserta.');
                        }

                        // 3. Add Questions
                        if (this.form.questions.length > 0) {
                            const qRes = await fetch(`/exams/${examId}/questions`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify({
                                    questions: this.form.questions
                                })
                            });
                            if (!qRes.ok) throw new Error('Gagal menyimpan soal.');
                        }

                        // Redirect to success
                        window.location.href = `/exams/${examId}`;
                        
                    } catch (error) {
                        this.errorMessage = error.message;
                        this.isSubmitting = false;
                    }
                }
            }
        });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    @endpush
</x-app-layout>
