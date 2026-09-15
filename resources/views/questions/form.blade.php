<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
                {{ isset($question) ? 'Edit Soal' : 'Tambah Soal Baru' }}
            </h2>
            <div class="text-sm text-gray-500">
                Bank Soal: <span class="font-medium text-gray-900">{{ $questionBank->name }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="questionEditor()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(isset($question) && $question->status === 'PUBLISHED')
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Perhatian:</strong> Soal ini sudah di-publish. Mengedit soal ini akan membuat <strong>Versi Baru</strong>. Ujian yang sudah dibuat menggunakan versi sebelumnya tidak akan terpengaruh.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form @submit.prevent="saveQuestion" class="bg-white shadow-sm sm:rounded-lg">
                <!-- Top Actions Bar -->
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center rounded-t-lg">
                    <div class="flex items-center">
                        <x-input-label for="type" :value="__('Tipe Soal')" class="mr-3 mb-0" />
                        <select x-model="form.type" @change="handleTypeChange" id="type" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm text-sm">
                            <option value="multiple_choice">Pilihan Ganda (Satu Jawaban)</option>
                            <option value="complex_multiple_choice">Pilihan Ganda Kompleks (Banyak Jawaban)</option>
                            <option value="true_false">Benar / Salah</option>
                            <option value="matching">Menjodohkan</option>
                            <option value="short_answer">Isian Singkat</option>
                            <option value="essay">Uraian / Essay</option>
                        </select>
                    </div>
                    <div>
                        <a href="{{ route('question_banks.show', $questionBank) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 mr-2">Batal</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-sapta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sapta-700" :disabled="isSaving">
                            <span x-show="!isSaving">Simpan Soal</span>
                            <span x-show="isSaving">Menyimpan...</span>
                        </button>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Content & Options -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Question Text -->
                        <div>
                            <x-input-label for="content" :value="__('Pertanyaan')" class="text-base" />
                            <p class="text-xs text-gray-500 mb-2">Gunakan editor ini untuk mengetik teks soal.</p>
                            
                            <textarea x-model="form.content" rows="6" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block w-full"></textarea>
                            <p x-show="errors.content" class="text-red-500 text-xs mt-1" x-text="errors.content"></p>
                            
                            <!-- Media Uploader -->
                            <div class="mt-4 border border-dashed border-gray-300 rounded-md p-4 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between">
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900">Lampiran Media</h5>
                                    <p class="text-xs text-gray-500 mt-1">Format didukung: JPG, PNG, GIF, MP3, MP4 (Maks 10MB)</p>
                                </div>
                                <div class="mt-3 sm:mt-0 relative">
                                    <input type="file" @change="uploadMedia" accept="image/*,audio/*,video/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" :disabled="isUploading">
                                    <button type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50" :class="isUploading ? 'opacity-50 cursor-wait' : ''">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        <span x-text="isUploading ? 'Mengunggah...' : 'Pilih File'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Media Previews -->
                            <div x-show="mediaFiles.length > 0" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                <template x-for="(media, index) in mediaFiles" :key="media.id">
                                    <div class="relative group border border-gray-200 rounded-md overflow-hidden bg-white shadow-sm">
                                        <!-- Image Preview -->
                                        <template x-if="media.mime_type.startsWith('image/')">
                                            <img :src="media.url" alt="Media" class="w-full h-24 object-cover">
                                        </template>
                                        <!-- Audio Preview -->
                                        <template x-if="media.mime_type.startsWith('audio/')">
                                            <div class="w-full h-24 flex flex-col items-center justify-center bg-blue-50 text-blue-500 p-2 text-center">
                                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path></svg>
                                                <span class="text-xs truncate w-full" x-text="media.file_name"></span>
                                            </div>
                                        </template>
                                        <!-- Video Preview -->
                                        <template x-if="media.mime_type.startsWith('video/')">
                                            <div class="w-full h-24 flex flex-col items-center justify-center bg-purple-50 text-purple-500 p-2 text-center">
                                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                <span class="text-xs truncate w-full" x-text="media.file_name"></span>
                                            </div>
                                        </template>

                                        <!-- Delete Button overlay -->
                                        <button type="button" @click="removeMedia(index)" class="absolute top-1 right-1 bg-white rounded-full p-1 shadow hover:bg-red-50 text-red-500 border border-transparent transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Dynamic Options based on Type -->
                        <div x-show="requiresOptions()" class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded-md">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-medium text-gray-900" x-text="getOptionsLabel()">Opsi Jawaban</h4>
                                <button type="button" @click="addOption" x-show="canAddOptions()" class="text-sm text-sapta-600 hover:text-sapta-800 font-medium flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Opsi
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(option, index) in form.options" :key="index">
                                    <div class="flex items-start bg-white p-3 border border-gray-200 rounded shadow-sm">
                                        <!-- Drag handle placeholder -->
                                        <div class="mt-2 text-gray-400 cursor-move mr-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                        </div>
                                        
                                        <!-- Correct toggle (Radio or Checkbox) -->
                                        <div class="mt-2 mr-3 flex-shrink-0" x-show="form.type !== 'matching'">
                                            <input :type="form.type === 'multiple_choice' || form.type === 'true_false' ? 'radio' : 'checkbox'" 
                                                   :name="'is_correct_group'" 
                                                   :checked="option.is_correct" 
                                                   @change="toggleCorrect(index)"
                                                   class="border-gray-300 text-sapta-600 shadow-sm focus:border-sapta-300 focus:ring focus:ring-sapta-200 focus:ring-opacity-50 h-5 w-5 cursor-pointer">
                                        </div>

                                        <div class="flex-1">
                                            <!-- Matching requires two parts: we simulate it by parsing or dual fields. For simplicity, just one field here for UI -->
                                            <textarea x-model="option.content" rows="2" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block w-full text-sm" placeholder="Teks opsi..."></textarea>
                                        </div>

                                        <!-- Weight -->
                                        <div class="w-20 mx-3">
                                            <input type="number" x-model.number="option.weight" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block w-full text-sm" placeholder="Bobot" min="0" max="100">
                                            <div class="text-[10px] text-gray-500 mt-1 text-center">Bobot (%)</div>
                                        </div>

                                        <!-- Remove -->
                                        <div class="mt-2 flex-shrink-0 ml-1" x-show="canRemoveOptions()">
                                            <button type="button" @click="removeOption(index)" class="text-red-500 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Feedback / Input if short answer / essay -->
                        <div x-show="!requiresOptions() && !requiresMatching()" class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded-md">
                            <div x-show="form.type === 'short_answer'">
                                <x-input-label for="accepted_answers" :value="__('Kunci Jawaban (Isian Singkat)')" />
                                <p class="text-xs text-gray-500 mb-2">Masukkan kemungkinan jawaban benar, pisahkan dengan koma (,). Besar/kecil huruf akan diabaikan (case-insensitive).</p>
                                <textarea id="accepted_answers" rows="3" class="shadow-sm focus:ring-sapta-500 focus:border-sapta-500 block w-full sm:text-sm border-gray-300 rounded-md p-3" placeholder="Contoh: Jakarta, DKI Jakarta, Kota Jakarta" x-model="shortAnswerText"></textarea>
                            </div>
                            <p class="text-sm text-gray-600 mt-2" x-show="form.type === 'essay'">
                                Tipe soal Uraian (Essay) membutuhkan penilaian manual (Manual Grading) oleh Guru. Siswa akan diberikan area teks untuk menjawab.
                            </p>
                        </div>
                        
                        <!-- Matching Pairs (Tarik Garis) UI -->
                        <div x-show="requiresMatching()" class="mt-8" style="display: none;">
                            <h4 class="font-medium text-gray-900 border-b pb-2 mb-4 flex justify-between items-center">
                                Pasangan Tarik Garis
                                <button type="button" @click="addMatchingPair()" class="text-sm text-sapta-600 hover:text-sapta-800 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Pasangan
                                </button>
                            </h4>
                            <div class="space-y-3">
                                <template x-for="(pair, index) in matchingPairs" :key="index">
                                    <div class="flex items-center space-x-3 bg-gray-50 p-3 rounded border border-gray-200">
                                        <div class="flex-1">
                                            <input type="text" x-model="pair.left" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block w-full text-sm" placeholder="Sisi Kiri (Premis)">
                                        </div>
                                        <div class="text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </div>
                                        <div class="flex-1">
                                            <input type="text" x-model="pair.right" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block w-full text-sm" placeholder="Sisi Kanan (Jawaban)">
                                        </div>
                                        <!-- Remove -->
                                        <div class="flex-shrink-0 ml-1" x-show="matchingPairs.length > 2">
                                            <button type="button" @click="removeMatchingPair(index)" class="text-red-500 hover:text-red-700 p-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Sistem akan secara otomatis mengacak urutan Sisi Kanan saat siswa mengerjakan ujian.</p>
                        </div>
                    </div>

                    <!-- Right Column: Metadata -->
                    <div class="space-y-5">
                        <div class="bg-gray-50 p-4 border border-gray-200 rounded-md">
                            <h4 class="font-medium text-gray-900 border-b pb-2 mb-4">Metadata Soal</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="cognitive_level" :value="__('Tingkat Kognitif')" />
                                    <select x-model="form.cognitive_level" id="cognitive_level" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full text-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="C1">C1 - Mengingat</option>
                                        <option value="C2">C2 - Memahami</option>
                                        <option value="C3">C3 - Mengaplikasikan</option>
                                        <option value="C4">C4 - Menganalisis</option>
                                        <option value="C5">C5 - Mengevaluasi</option>
                                        <option value="C6">C6 - Mencipta</option>
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="difficulty" :value="__('Tingkat Kesulitan')" />
                                    <select x-model="form.difficulty" id="difficulty" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full text-sm">
                                        <option value="">-- Pilih --</option>
                                        <option value="Mudah">Mudah</option>
                                        <option value="Sedang">Sedang</option>
                                        <option value="Sulit">Sulit</option>
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="topic" :value="__('Topik / Materi')" />
                                    <x-text-input x-model="form.topic" id="topic" type="text" class="block mt-1 w-full text-sm" placeholder="Contoh: Aljabar" />
                                </div>

                                <div>
                                    <x-input-label for="competency" :value="__('Kompetensi Dasar (KD)')" />
                                    <x-text-input x-model="form.competency" id="competency" type="text" class="block mt-1 w-full text-sm" placeholder="Contoh: 3.1" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function questionEditor() {
            const existingQuestion = @json(isset($question) ? $question : null);
            const questionBankId = {{ $questionBank->id }};
            
            let defaultForm = {
                type: 'multiple_choice',
                content: '',
                cognitive_level: '',
                difficulty: '',
                topic: '',
                competency: '',
                options: [
                    { content: '', is_correct: false, order: 1, weight: 100 },
                    { content: '', is_correct: false, order: 2, weight: 0 },
                    { content: '', is_correct: false, order: 3, weight: 0 },
                    { content: '', is_correct: false, order: 4, weight: 0 },
                ],
                scoring_metadata: null,
                media_ids: []
            };

            let initialShortAnswer = '';
            let initialMedia = [];
            let initialMatchingPairs = [
                { left: '', right: '' },
                { left: '', right: '' }
            ];

            if (existingQuestion && existingQuestion.current_version) {
                const ver = existingQuestion.current_version;
                defaultForm.type = ver.type;
                defaultForm.content = ver.content;
                defaultForm.cognitive_level = ver.cognitive_level || '';
                defaultForm.difficulty = ver.difficulty || '';
                defaultForm.topic = ver.topic || '';
                defaultForm.competency = ver.competency || '';
                
                if (ver.options && ver.options.length > 0) {
                    defaultForm.options = ver.options.map(opt => ({
                        content: opt.content,
                        is_correct: opt.is_correct === 1 || opt.is_correct === true,
                        order: opt.order,
                        weight: parseFloat(opt.weight)
                    }));
                }

                if (ver.media && ver.media.length > 0) {
                    initialMedia = ver.media.map(m => ({
                        id: m.id,
                        file_name: m.file_name,
                        url: `/media/${m.id}`,
                        mime_type: m.mime_type
                    }));
                }
                if (ver.scoring_metadata && ver.scoring_metadata.accepted_answers) {
                    initialShortAnswer = ver.scoring_metadata.accepted_answers.join(', ');
                }
                if (ver.scoring_metadata && ver.scoring_metadata.pairs) {
                    initialMatchingPairs = ver.scoring_metadata.pairs;
                }
            }

            return {
                form: defaultForm,
                shortAnswerText: initialShortAnswer,
                matchingPairs: initialMatchingPairs,
                mediaFiles: initialMedia,
                isSaving: false,
                isUploading: false,
                errors: {},

                requiresOptions() {
                    return ['multiple_choice', 'complex_multiple_choice', 'true_false'].includes(this.form.type);
                },

                requiresMatching() {
                    return this.form.type === 'matching';
                },

                canAddOptions() {
                    return ['multiple_choice', 'complex_multiple_choice', 'matching'].includes(this.form.type);
                },

                canRemoveOptions() {
                    if (this.form.type === 'true_false') return false;
                    return this.form.options.length > 2;
                },

                getOptionsLabel() {
                    if (this.form.type === 'true_false') return 'Pernyataan Benar/Salah';
                    if (this.form.type === 'matching') return 'Pasangan (Kiri - Kanan)';
                    return 'Opsi Jawaban';
                },

                handleTypeChange() {
                    if (this.form.type === 'true_false') {
                        this.form.options = [
                            { content: 'Benar', is_correct: true, order: 1, weight: 100 },
                            { content: 'Salah', is_correct: false, order: 2, weight: 0 }
                        ];
                    } else if (this.form.type === 'multiple_choice' && this.form.options.length < 4) {
                        this.addOption();
                        this.addOption();
                    }
                },

                addOption() {
                    this.form.options.push({
                        content: '',
                        is_correct: false,
                        order: this.form.options.length + 1,
                        weight: 0
                    });
                },

                removeOption(index) {
                    this.form.options.splice(index, 1);
                    // Re-order
                    this.form.options.forEach((opt, i) => opt.order = i + 1);
                },

                toggleCorrect(index) {
                    if (this.form.type === 'multiple_choice' || this.form.type === 'true_false') {
                        // Only one can be correct
                        this.form.options.forEach((opt, i) => {
                            opt.is_correct = i === index;
                            opt.weight = i === index ? 100 : 0;
                        });
                    } else {
                        // Complex multiple choice / matching allows multiple
                        this.form.options[index].is_correct = !this.form.options[index].is_correct;
                    }
                },

                async uploadMedia(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Validasi ukuran (10MB max)
                    if (file.size > 10 * 1024 * 1024) {
                        alert('Ukuran file maksimal 10MB.');
                        event.target.value = '';
                        return;
                    }

                    this.isUploading = true;
                    
                    const formData = new FormData();
                    formData.append('file', file);
                    // Pass empty or dummy since it's nullable now
                    
                    try {
                        const response = await fetch('/media', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            alert(result.message || 'Gagal mengunggah media.');
                        } else {
                            // Tambahkan ke UI preview
                            this.mediaFiles.push({
                                id: result.media.id,
                                file_name: result.media.file_name,
                                url: result.url,
                                mime_type: result.media.mime_type
                            });
                            // Tambahkan ke payload
                            this.form.media_ids.push(result.media.id);
                        }
                    } catch (error) {
                        alert('Terjadi kesalahan saat mengunggah.');
                    } finally {
                        this.isUploading = false;
                        event.target.value = ''; // reset input
                    }
                },

                async removeMedia(index) {
                    if (!confirm('Hapus media ini?')) return;
                    
                    const media = this.mediaFiles[index];
                    
                    try {
                        // Delete via API
                        const response = await fetch(`/media/${media.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            this.mediaFiles.splice(index, 1);
                            // Hapus juga dari form.media_ids jika belum disubmit
                            const idIndex = this.form.media_ids.indexOf(media.id);
                            if (idIndex > -1) {
                                this.form.media_ids.splice(idIndex, 1);
                            }
                        } else {
                            alert('Gagal menghapus media dari server.');
                        }
                    } catch (error) {
                        alert('Terjadi kesalahan jaringan saat menghapus media.');
                    }
                },

                addMatchingPair() {
                    this.matchingPairs.push({ left: '', right: '' });
                },

                removeMatchingPair(index) {
                    if (this.matchingPairs.length > 2) {
                        this.matchingPairs.splice(index, 1);
                    }
                },

                async saveQuestion() {
                    this.isSaving = true;
                    this.errors = {};

                    if (this.form.type === 'short_answer') {
                        const answers = this.shortAnswerText.split(',').map(s => s.trim()).filter(s => s !== '');
                        this.form.scoring_metadata = {
                            accepted_answers: answers
                        };
                    } else if (this.form.type === 'matching') {
                        // Filter out empty pairs
                        const validPairs = this.matchingPairs.filter(p => p.left.trim() !== '' && p.right.trim() !== '');
                        this.form.scoring_metadata = {
                            pairs: validPairs
                        };
                    } else if (this.form.type === 'essay') {
                        this.form.scoring_metadata = null;
                    }

                    const url = existingQuestion 
                        ? `/questions/${existingQuestion.id}`
                        : `/question_banks/${questionBankId}/questions`;
                    
                    const method = existingQuestion ? 'PUT' : 'POST';

                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            if (response.status === 422) {
                                this.errors = result.errors;
                                alert('Periksa kembali inputan Anda.');
                            } else {
                                alert(result.message || 'Terjadi kesalahan');
                            }
                        } else {
                            // Success
                            window.location.href = `/question_banks/${questionBankId}`;
                        }
                    } catch (error) {
                        alert('Terjadi kesalahan jaringan.');
                    } finally {
                        this.isSaving = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
