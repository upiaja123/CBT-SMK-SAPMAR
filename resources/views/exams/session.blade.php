<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>{{ $exam->title }} - CBT Sapta Marga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .prose img {
            max-height: 20rem; /* max-h-80 = 320px */
            width: auto;
            object-fit: contain;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased" x-data="examSession({{ $exam->id }}, {{ $attempt->id }})">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex flex-col">
                <h1 class="text-lg font-bold text-gray-900 truncate max-w-[200px] sm:max-w-md">{{ $exam->title }}</h1>
                <span class="text-xs text-gray-500">{{ auth()->user()->name }}</span>
            </div>
            
            <div class="flex items-center space-x-2 bg-gray-100 px-3 py-1.5 rounded-lg border" :class="{'border-red-500 text-red-600 bg-red-50': isWarning, 'border-gray-200': !isWarning}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-mono font-bold text-lg" x-text="formattedTime">--:--</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col lg:flex-row gap-6">
        
        <!-- Loading State -->
        <div x-show="isLoading" class="w-full flex justify-center py-20">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Exam Locked / Ended -->
        <div x-show="!isLoading && isEnded" class="w-full bg-white p-8 rounded-xl shadow-sm border border-gray-200 text-center" style="display: none;" x-cloak>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-blue-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2" x-text="status === 'SUBMITTED' || status === 'AUTO_SUBMITTED' ? 'Ujian Telah Dikumpulkan' : 'Waktu Habis'">Waktu Habis</h2>
            <p class="text-gray-600" x-text="status === 'SUBMITTED' ? 'Terima kasih, ujian Anda telah berhasil dikumpulkan dan sudah diverifikasi.' : (status === 'AUTO_SUBMITTED' ? 'Waktu habis dan ujian Anda telah dikumpulkan otomatis.' : 'Sesi ujian Anda telah berakhir.')">Sesi ujian Anda telah berakhir.</p>
            <a href="{{ route('dashboard') }}" class="mt-8 inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-md w-full sm:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Kembali ke Halaman Utama
            </a>
        </div>

        <div x-show="!isLoading && !isEnded && is_locked" class="w-full bg-white p-8 rounded-xl shadow-sm border border-red-200 text-center" style="display: none;" x-cloak>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Ujian Terkunci</h2>
            <p class="text-gray-600">Ujian sementara dikunci oleh pengawas.</p>
        </div>

        <!-- Question Area -->
        <div x-show="!isLoading && !isEnded && !is_locked && currentQuestion" class="flex-1" style="display: none;">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Soal <span x-text="currentQuestionIndex + 1"></span> dari <span x-text="questions.length"></span></h2>
                </div>
                
                <div class="p-4 sm:p-6">
                    <!-- Question Content -->
                    <div class="prose max-w-none text-gray-800 mb-4" x-html="currentQuestion?.content"></div>

                    <!-- Media Rendering -->
                    <template x-if="currentQuestion && currentQuestion.media && currentQuestion.media.length > 0">
                        <div class="mb-8 space-y-4">
                            <template x-for="media in currentQuestion.media" :key="media.id">
                                <div>
                                    <template x-if="media.mime_type.startsWith('image/')">
                                        <img :src="media.url" alt="Media Soal" class="max-w-full max-h-80 w-auto object-contain mx-auto rounded-lg shadow-sm border border-gray-200">
                                    </template>
                                    <template x-if="media.mime_type.startsWith('audio/')">
                                        <audio controls class="w-full max-w-md mx-auto">
                                            <source :src="media.url" :type="media.mime_type">
                                            Browser Anda tidak mendukung elemen audio.
                                        </audio>
                                    </template>
                                    <template x-if="media.mime_type.startsWith('video/')">
                                        <video controls class="max-w-full max-h-80 w-auto object-contain mx-auto rounded-lg shadow-sm border border-gray-200">
                                            <source :src="media.url" :type="media.mime_type">
                                            Browser Anda tidak mendukung elemen video.
                                        </video>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Options (Multiple Choice) -->
                    <template x-if="currentQuestion.question_type === 'multiple_choice'">
                    <div class="space-y-3">
                        <template x-for="option in currentQuestion.options" :key="option.id">
                            <label class="flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-brand-50 hover:border-brand-300 transition-colors"
                                :class="{ 'bg-brand-50 border-brand-500': answers[currentQuestion.id] === option.id }">
                                <div class="flex-shrink-0 mt-0.5">
                                    <input type="radio" :name="'question_'+currentQuestion.id" :value="option.id"
                                           class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-500"
                                           :checked="answers[currentQuestion.id] === option.id"
                                           :disabled="status !== 'IN_PROGRESS'"
                                           @change="handleAnswerChange(currentQuestion.id, option.id)">
                                </div>
                                <div class="ml-3 text-gray-700 text-sm" x-html="option.content"></div>
                            </label>
                        </template>
                    </div>
                </template>
                
                <template x-if="currentQuestion.question_type === 'short_answer'">
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Jawaban Anda</label>
                        <input type="text" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500" 
                               :value="answers[currentQuestion.id]?.text || ''"
                               @input.debounce.500ms="handleAnswerChange(currentQuestion.id, {text: $event.target.value})"
                               :disabled="status !== 'IN_PROGRESS'"
                               placeholder="Ketik jawaban singkat di sini...">
                    </div>
                </template>

                <template x-if="currentQuestion.question_type === 'matching'">
                    <div class="relative mt-6" x-data="matchingQuestion(currentQuestion)">
                        <!-- Empty State Fallback -->
                        <template x-if="!currentQuestion.left_items || currentQuestion.left_items.length === 0">
                            <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200">
                                Soal ini belum memiliki pasangan (premis/jawaban) yang dikonfigurasi.
                            </div>
                        </template>

                        <template x-if="currentQuestion.left_items && currentQuestion.left_items.length > 0">
                            <div>
                                <!-- SVG Overlay for drawn lines -->
                                <svg x-ref="svgCanvas" class="absolute inset-0 pointer-events-none w-full h-full z-10">
                                    <path :d="lines.map(l => `M ${l.x1} ${l.y1} L ${l.x2} ${l.y2}`).join(' ')" stroke="#111827" stroke-width="4" stroke-linecap="round" fill="none" />
                                    <line x-show="isDrawing && currentLine" :x1="currentLine?.x1" :y1="currentLine?.y1" :x2="currentLine?.x2" :y2="currentLine?.y2" stroke="#111827" stroke-width="4" stroke-linecap="round" stroke-dasharray="6,6" opacity="0.5"/>
                                </svg>

                                <div class="grid grid-cols-2 gap-8" @mousemove.window="onMouseMove" @mouseup.window="onMouseUp" @touchmove.window="onTouchMove" @touchend.window="onMouseUp">
                                    <!-- Left Items -->
                                    <div class="space-y-6" x-ref="leftContainer">
                                        <template x-for="(item, index) in currentQuestion.left_items" :key="'l'+index">
                                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between relative select-none"
                                                 :class="{'ring-2 ring-brand-500': isLeftSelected(item)}">
                                                <span x-text="item" class="text-sm font-medium text-gray-800 pr-4"></span>
                                                <div class="w-5 h-5 rounded-full bg-white border-2 border-brand-500 cursor-pointer flex-shrink-0"
                                                     @mousedown.stop.prevent="startDrawing($event, item, 'left')"
                                                     @touchstart.stop.prevent="startDrawing($event, item, 'left')"
                                                     :id="'left-node-' + index"></div>
                                            </div>
                                        </template>
                                    </div>
                            <!-- Right Items -->
                            <div class="space-y-6" x-ref="rightContainer">
                                <template x-for="(item, index) in currentQuestion.right_items" :key="'r'+index">
                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between relative select-none cursor-pointer"
                                         :class="{'ring-2 ring-brand-500': isRightSelected(item)}"
                                         @mouseup.stop.prevent="finishDrawing(item, 'right')"
                                         @touchend.stop.prevent="finishDrawing(item, 'right', $event)">
                                        <div class="w-5 h-5 rounded-full bg-white border-2 border-brand-500 flex-shrink-0"
                                             :id="'right-node-' + index"></div>
                                        <span x-text="item" class="text-sm font-medium text-gray-800 text-right pl-4"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button @click="clearLines()" class="text-sm text-red-600 hover:text-red-800 underline font-medium">Reset Tarik Garis</button>
                        </div>
                            </div>
                        </template>
                    </div>
                </template>
                
                <template x-if="!['multiple_choice', 'short_answer', 'matching'].includes(currentQuestion.question_type)">
                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200">
                        Tipe soal ini belum didukung di tampilan mobile.
                    </div>
                </template>

            </div>

            <!-- Navigation Controls -->
            <div class="mt-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <button @click="prevQuestion" 
                        :disabled="currentQuestionIndex === 0"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <span class="hidden sm:inline">&larr; Sebelumnya</span>
                    <span class="sm:hidden">&larr;</span>
                </button>
                
                <!-- Sync Status Indicator -->
                <div class="flex items-center space-x-2 text-xs font-medium">
                    <template x-if="syncStatus === 'saving'">
                        <span class="text-blue-600 flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...</span>
                    </template>
                    <template x-if="syncStatus === 'saved'">
                        <span class="text-green-600 flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersimpan</span>
                    </template>
                    <template x-if="syncStatus === 'error'">
                        <span class="text-red-600 flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Belum tersimpan (Offline)</span>
                    </template>
                </div>

                <button @click="nextQuestion" 
                        x-show="currentQuestionIndex < questions.length - 1"
                        class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-white hover:bg-blue-700 transition">
                    <span class="hidden sm:inline">Berikutnya &rarr;</span>
                    <span class="sm:hidden">&rarr;</span>
                </button>

                <button x-show="currentQuestionIndex === questions.length - 1 && status === 'IN_PROGRESS'"
                        @click="showSubmitModal = true"
                        class="px-4 py-2 bg-green-600 border border-transparent rounded-lg text-white hover:bg-green-700 transition font-bold">
                    Selesai
                </button>
            </div>
        </div>

        <!-- Right Sidebar (Navigator) -->
        <div x-show="!isLoading && !isEnded && !is_locked" class="w-full lg:w-80 flex-shrink-0" style="display: none;">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sticky top-24">
                <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wider">Navigasi Soal</h3>
                <div class="grid grid-cols-5 gap-2">
                    <template x-for="(q, index) in questions" :key="q.id">
                        <button @click="goToQuestion(index)"
                                class="w-full aspect-square flex items-center justify-center rounded border font-medium text-sm transition-colors"
                                :class="{
                                    'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-300 ring-offset-1': currentQuestionIndex === index,
                                    'bg-gray-800 text-white border-gray-800': currentQuestionIndex !== index && answers[q.id],
                                    'bg-white text-gray-600 border-gray-300 hover:bg-gray-50': currentQuestionIndex !== index && !answers[q.id]
                                }">
                            <span x-text="index + 1"></span>
                        </button>
                    </template>
                </div>
                
                <!-- Legend -->
                <div class="mt-6 space-y-2 text-xs text-gray-600">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-600 rounded-sm mr-2"></div> Sedang dibuka
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-gray-800 rounded-sm mr-2"></div> Sudah dijawab
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-white border border-gray-300 rounded-sm mr-2"></div> Belum dijawab
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Submit Modal -->
    <div x-show="showSubmitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;" x-cloak>
        <div @click.away="!isSubmitting && (showSubmitModal = false)" class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">Konfirmasi Pengumpulan</h3>
            </div>
            <div class="px-6 py-4">
                <p class="text-gray-600 mb-4">Yakin ingin mengumpulkan ujian? Setelah dikumpulkan, Anda tidak dapat mengubah jawaban lagi.</p>
                
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-2">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600 text-sm">Total Soal:</span>
                        <span class="font-bold text-gray-900 text-sm" x-text="questions.length"></span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-green-600 text-sm">Terjawab:</span>
                        <span class="font-bold text-green-700 text-sm" x-text="Object.keys(answers).length"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-red-500 text-sm">Belum Dijawab:</span>
                        <span class="font-bold text-red-600 text-sm" x-text="questions.length - Object.keys(answers).length"></span>
                    </div>
                </div>
                
                <template x-if="syncQueue.length > 0">
                    <div class="mt-4 p-3 bg-yellow-50 text-yellow-800 text-sm rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2 flex-shrink-0 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Terdapat jawaban yang sedang disimpan. Harap tunggu hingga sinkronisasi selesai sebelum mengumpulkan.
                    </div>
                </template>

                <div class="mt-5 p-4 border border-blue-100 bg-blue-50/50 rounded-xl">
                    <label class="flex items-start cursor-pointer">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" x-model="isVerified" class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 cursor-pointer transition">
                        </div>
                        <div class="ml-3 text-sm">
                            <span class="font-bold text-gray-900 block mb-1">Verifikasi Jawaban</span>
                            <span class="text-gray-600">Saya yakin telah memeriksa kembali semua jawaban saya dan siap untuk mengumpulkan ujian secara permanen.</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-2 sm:space-x-3">
                <button @click="showSubmitModal = false; isVerified = false;" :disabled="isSubmitting" class="w-full sm:w-auto px-4 py-2.5 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition disabled:opacity-50 font-medium">Batal & Periksa Lagi</button>
                <button @click="submitExam()" :disabled="isSubmitting || syncQueue.length > 0 || !isVerified" class="w-full sm:w-auto px-4 py-2.5 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex justify-center items-center disabled:opacity-50 disabled:cursor-not-allowed font-bold">
                    <template x-if="isSubmitting">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    Kumpulkan Ujian
                </button>
            </div>
        </div>
    </div>

    <!-- Violation Modal -->
    <div x-show="showViolationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80" style="display: none;" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform transition-all border-t-4 border-red-600 p-6 text-center">
            <svg class="mx-auto h-16 w-16 text-red-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h3 class="text-xl font-bold text-gray-900 mb-2">PERINGATAN KECURANGAN!</h3>
            <p class="text-gray-700 font-medium mb-2" x-text="violationMessage"></p>
            <p class="text-sm text-red-600 font-bold mb-6">Sistem keamanan mendeteksi aktivitas mencurigakan. Jika Anda mengulangi pelanggaran ini berkali-kali, ujian Anda bisa dihentikan paksa!</p>
            <button @click="showViolationModal = false" class="w-full bg-red-600 text-white font-bold py-3 rounded-lg hover:bg-red-700 transition">Saya Mengerti & Tidak Akan Mengulangi</button>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('examSession', (examId, attemptId) => ({
                examId: examId,
                attemptId: attemptId,
                isLoading: true,
                isEnded: false,
                isWarning: false,
                is_locked: false,
                status: 'IN_PROGRESS',
                showSubmitModal: false,
                isVerified: false,
                showViolationModal: false,
                violationCount: 0,
                violationMessage: '',
                isSubmitting: false,
                questions: [],
                currentQuestionIndex: 0,
                answers: {}, // local UI state: { questionId: optionId }
                
                // Sync queue & state
                syncQueue: [],
                syncStatus: 'idle', // idle, saving, saved, error
                isOnline: navigator.onLine,
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                
                deadline: null,
                remainingSeconds: 0,
                formattedTime: '--:--',
                timerInterval: null,
                syncInterval: null,

                async init() {
                    // Setup CSRF if not exists
                    if (!this.csrfToken) {
                        const meta = document.createElement('meta');
                        meta.name = "csrf-token";
                        // Fetching csrf from a global window object if possible or just rely on cookie
                        // Actually laravel breeze adds it to the head usually. Let's make sure it's available.
                        // I'll grab it from the document, or fetch one if missing.
                    }

                    // Prevent Back Navigation
                    history.pushState(null, null, location.href);
                    window.addEventListener('popstate', function () {
                        history.go(1);
                    });

                    window.addEventListener('online', () => {
                        this.isOnline = true;
                        this.flushSyncQueue();
                        this.sendIntegrityEvent('NETWORK_ONLINE', { detail: 'Browser reported online' });
                    });
                    window.addEventListener('offline', () => {
                        this.isOnline = false;
                        this.syncStatus = 'error';
                        this.sendIntegrityEvent('NETWORK_OFFLINE', { detail: 'Browser reported offline' });
                    });
                    
                    // Integrity Events
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden && this.status === 'IN_PROGRESS' && !this.is_locked && !this.showSubmitModal) {
                            this.sendIntegrityEvent('TAB_HIDDEN', { detail: 'Document hidden' });
                            this.registerViolation('Anda terdeteksi berpindah ke aplikasi atau Tab lain.');
                        } else {
                            this.sendIntegrityEvent('TAB_VISIBLE', { detail: 'Document visible' });
                        }
                    });

                    window.addEventListener('blur', () => {
                        if (this.status === 'IN_PROGRESS' && !this.is_locked && !this.showSubmitModal) {
                            this.sendIntegrityEvent('WINDOW_BLUR', { detail: 'Window lost focus' });
                            this.registerViolation('Anda terdeteksi keluar dari layar ujian.');
                        }
                    });
                    
                    window.addEventListener('focus', () => {
                        this.sendIntegrityEvent('WINDOW_FOCUS', { detail: 'Window gained focus' });
                    });

                    document.addEventListener('fullscreenchange', () => {
                        if (!document.fullscreenElement) {
                            this.sendIntegrityEvent('FULLSCREEN_EXIT', { detail: 'Exited fullscreen' });
                        } else {
                            this.sendIntegrityEvent('FULLSCREEN_ENTER', { detail: 'Entered fullscreen' });
                        }
                    });

                    try {
                        const response = await fetch(`/exams/${this.examId}/attempts/${this.attemptId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.status === 403) {
                            this.isEnded = true;
                            this.isLoading = false;
                            return;
                        }
                        
                        const data = await response.json();
                        
                        this.status = data.status;
                        this.is_locked = data.is_locked || false;
                        if (this.status === 'SUBMITTED' || this.status === 'AUTO_SUBMITTED') {
                            this.isEnded = true;
                        }
                        
                        this.questions = data.questions;
                        
                        // Hydrate answers
                        this.questions.forEach(q => {
                            if (q.participant_answer && q.participant_answer.option_id) {
                                this.answers[q.id] = q.participant_answer.option_id;
                            }
                        });
                        
                        // Parse deadline
                        if (data.deadline_at && this.status === 'IN_PROGRESS') {
                            const serverNow = new Date(data.server_now).getTime();
                            const localNow = new Date().getTime();
                            const offset = serverNow - localNow;
                            
                            const deadlineMs = new Date(data.deadline_at).getTime();
                            
                            this.deadline = deadlineMs - offset;
                            this.startTimer();
                        }
                        
                        this.isLoading = false;
                        
                        // Start background sync interval just in case
                        this.syncInterval = setInterval(() => {
                            if (this.syncQueue.length > 0 && this.isOnline) {
                                this.flushSyncQueue();
                            }
                        }, 5000);

                        // Start heartbeat interval
                        setInterval(() => {
                            this.sendHeartbeat();
                        }, 15000); // 15 seconds
                        
                        this.setupRealtime();

                    } catch (e) {
                        console.error('Failed to load exam session', e);
                        alert('Gagal memuat sesi ujian. Silakan muat ulang halaman.');
                    }
                },

                setupRealtime() {
                    if (typeof window.Echo === 'undefined') {
                        setTimeout(() => this.setupRealtime(), 100);
                        return;
                    }

                    // Only connect if not ended
                    if (this.isEnded) return;

                    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
                        // could show a realtime disconnect pill if needed
                    });

                    window.Echo.private(`attempt.${this.attemptId}`)
                        .listen('attempt.control.updated', (e) => {
                            this.status = e.status;
                            this.is_locked = e.is_locked;
                            
                            if (this.status === 'AUTO_SUBMITTED') {
                                this.isEnded = true;
                                this.showSubmitModal = false;
                            }

                            if (e.deadline_at && this.status === 'IN_PROGRESS') {
                                const serverNow = new Date().getTime(); // approximate
                                const deadlineMs = new Date(e.deadline_at).getTime();
                                this.deadline = deadlineMs; // In a robust app, we recalculate offset, here we just use local time approximation as timer base is local Date.now() in startTimer
                                
                                // Proper offset sync requires server_now from event, but we can just set deadline and let local timer tick
                                // To prevent jumping if local clock is wrong, ideally we pass server_now in event payload. 
                                // Since we didn't add server_now to event, we'll just set deadline directly.
                                this.startTimer();
                            }
                        });
                },

                get currentQuestion() {
                    return this.questions[this.currentQuestionIndex] || null;
                },

                registerViolation(msg) {
                    this.violationCount++;
                    this.violationMessage = msg + ` (Peringatan ke-${this.violationCount})`;
                    this.showViolationModal = true;
                    
                    // Force submit on 5 violations
                    if (this.violationCount >= 5) {
                        alert("Batas pelanggaran maksimum tercapai. Ujian Anda dihentikan secara paksa.");
                        this.submitExam();
                    }
                },

                async sendHeartbeat() {
                    if (this.isEnded || !this.isOnline) return;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        await fetch(`/exams/${this.examId}/attempts/${this.attemptId}/heartbeat`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                    } catch (e) {
                        // ignore heartbeat failure
                    }
                },

                async sendIntegrityEvent(eventType, metadata = null) {
                    if (this.isEnded || !this.isOnline) return;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        await fetch(`/exams/${this.examId}/attempts/${this.attemptId}/integrity-events`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                event_type: eventType,
                                occurred_at: new Date().toISOString(),
                                metadata: metadata
                            })
                        });
                    } catch (e) {
                        // ignore failure
                    }
                },

                startTimer() {
                    this.updateTimer();
                    this.timerInterval = setInterval(() => {
                        this.updateTimer();
                    }, 1000);
                },

                updateTimer() {
                    if (!this.deadline) return;
                    
                    const now = new Date().getTime();
                    this.remainingSeconds = Math.floor((this.deadline - now) / 1000);
                    
                    if (this.remainingSeconds <= 0) {
                        this.remainingSeconds = 0;
                        this.isEnded = true;
                        clearInterval(this.timerInterval);
                    }
                    
                    if (this.remainingSeconds <= 300 && this.remainingSeconds > 0) {
                        this.isWarning = true;
                    }

                    const h = Math.floor(this.remainingSeconds / 3600);
                    const m = Math.floor((this.remainingSeconds % 3600) / 60);
                    const s = this.remainingSeconds % 60;
                    
                    if (h > 0) {
                        this.formattedTime = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                    } else {
                        this.formattedTime = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                    }
                },

                handleAnswerChange(questionId, optionId) {
                    if (this.status !== 'IN_PROGRESS' || this.is_locked) return;
                    
                    this.answers[questionId] = optionId;
                    
                    const snapshotId = this.questions.find(q => q.id === questionId)?.id;
                    if (!snapshotId) return;

                    const payload = {
                        attempt_question_snapshot_id: snapshotId,
                        answer: { option_id: optionId },
                        client_timestamp: new Date().getTime()
                    };
                    
                    const existingIndex = this.syncQueue.findIndex(q => q.attempt_question_snapshot_id === snapshotId);
                    if (existingIndex > -1) {
                        this.syncQueue[existingIndex] = payload;
                    } else {
                        this.syncQueue.push(payload);
                    }
                    
                    this.syncStatus = 'saving';
                    this.flushSyncQueue();
                },

                async flushSyncQueue() {
                    if (!this.isOnline || this.syncQueue.length === 0 || this.isEnded) return;
                    
                    this.syncStatus = 'saving';
                    
                    // We sync one by one or all at once? Let's do one by one for simplicity and safety.
                    const payload = this.syncQueue[0];
                    
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        
                        const response = await fetch(`/exams/${this.examId}/attempts/${this.attemptId}/answers`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        if (response.ok) {
                            // Success, remove from queue
                            this.syncQueue.shift();
                            if (this.syncQueue.length === 0) {
                                this.syncStatus = 'saved';
                                setTimeout(() => {
                                    if (this.syncQueue.length === 0) this.syncStatus = 'idle';
                                }, 2000);
                            } else {
                                // Recursively flush the next one
                                this.flushSyncQueue();
                            }
                        } else if (response.status === 403 || response.status === 422) {
                            // Forbidden or validation error - discard it to avoid endless loop, or handle it
                            console.error('Answer rejected by server', await response.json());
                            this.syncQueue.shift();
                            this.syncStatus = 'error';
                        } else {
                            throw new Error('Server error');
                        }
                    } catch (e) {
                        console.error('Sync failed', e);
                        this.syncStatus = 'error';
                    }
                },

                nextQuestion() {
                    if (this.currentQuestionIndex < this.questions.length - 1) {
                        this.currentQuestionIndex++;
                        window.scrollTo({top: 0, behavior: 'smooth'});
                    }
                },

                prevQuestion() {
                    if (this.currentQuestionIndex > 0) {
                        this.currentQuestionIndex--;
                        window.scrollTo({top: 0, behavior: 'smooth'});
                    }
                },

                goToQuestion(index) {
                    this.currentQuestionIndex = index;
                    window.scrollTo({top: 0, behavior: 'smooth'});
                },
                
                async submitExam() {
                    if (this.syncQueue.length > 0) {
                        alert('Harap tunggu hingga proses sinkronisasi selesai.');
                        return;
                    }
                    
                    this.isSubmitting = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await fetch(`/exams/${this.examId}/attempts/${this.attemptId}/submit`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                        
                        if (response.ok) {
                            this.status = 'SUBMITTED';
                            this.isEnded = true;
                            this.showSubmitModal = false;
                            clearInterval(this.timerInterval);
                        } else {
                            const resData = await response.json();
                            alert(resData.message || 'Terjadi kesalahan saat mengumpulkan ujian.');
                        }
                    } catch (e) {
                        console.error('Submit failed', e);
                        alert('Gagal mengumpulkan ujian, periksa koneksi internet Anda.');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }));

            Alpine.data('matchingQuestion', (question) => ({
                question: question,
                lines: [],
                currentLine: null,
                startItem: null,
                isDrawing: false,
                
                init() {
                    this.$watch('answers', (newVal) => {
                        this.redrawExistingLines();
                    });
                    
                    // Re-initialize when navigating between matching questions
                    this.$watch('currentQuestionIndex', () => {
                        this.question = this.currentQuestion;
                        this.lines = [];
                        this.redrawExistingLines();
                    });

                    // Delay initial redraw to ensure DOM is ready
                    setTimeout(() => this.redrawExistingLines(), 100);
                    
                    window.addEventListener('resize', () => {
                        this.redrawExistingLines();
                    });
                },

                redrawExistingLines() {
                    if (!this.$store || !this.answers) return; // Wait until ready
                    const answerObj = this.answers[this.question.id];
                    if (!answerObj || !Array.isArray(answerObj.pairs)) return;
                    const answerArr = answerObj.pairs;

                    this.lines = [];
                    // We need to find the coordinates of left node and right node based on the text
                    answerArr.forEach(pair => {
                        const leftIndex = this.question.left_items.indexOf(pair.left);
                        const rightIndex = this.question.right_items.indexOf(pair.right);
                        if (leftIndex !== -1 && rightIndex !== -1) {
                            const leftEl = document.getElementById('left-node-' + leftIndex);
                            const rightEl = document.getElementById('right-node-' + rightIndex);
                            if (leftEl && rightEl && this.$refs.svgCanvas) {
                                const svgRect = this.$refs.svgCanvas.getBoundingClientRect();
                                const lRect = leftEl.getBoundingClientRect();
                                const rRect = rightEl.getBoundingClientRect();
                                this.lines.push({
                                    x1: lRect.left - svgRect.left + lRect.width/2,
                                    y1: lRect.top - svgRect.top + lRect.height/2,
                                    x2: rRect.left - svgRect.left + rRect.width/2,
                                    y2: rRect.top - svgRect.top + rRect.height/2,
                                    left: pair.left,
                                    right: pair.right
                                });
                            }
                        }
                    });
                },

                getCoordinates(e) {
                    let clientX, clientY;
                    if (e.touches && e.touches.length > 0) {
                        clientX = e.touches[0].clientX;
                        clientY = e.touches[0].clientY;
                    } else if (e.changedTouches && e.changedTouches.length > 0) {
                        clientX = e.changedTouches[0].clientX;
                        clientY = e.changedTouches[0].clientY;
                    } else {
                        clientX = e.clientX;
                        clientY = e.clientY;
                    }
                    
                    const svgRect = this.$refs.svgCanvas.getBoundingClientRect();
                    return {
                        x: clientX - svgRect.left,
                        y: clientY - svgRect.top
                    };
                },

                startDrawing(e, item, side) {
                    if (this.status !== 'IN_PROGRESS') return;
                    if (side !== 'left') return; // Only start from left to simplify

                    // If left is already connected, remove the old line
                    const existingIndex = this.lines.findIndex(l => l.left === item);
                    if (existingIndex !== -1) {
                        this.lines.splice(existingIndex, 1);
                        this.updateAnswers();
                    }

                    this.isDrawing = true;
                    this.startItem = item;
                    
                    const rect = e.target.getBoundingClientRect();
                    const svgRect = this.$refs.svgCanvas.getBoundingClientRect();
                    
                    const startX = rect.left - svgRect.left + rect.width/2;
                    const startY = rect.top - svgRect.top + rect.height/2;

                    this.currentLine = {
                        x1: startX,
                        y1: startY,
                        x2: startX,
                        y2: startY
                    };
                },

                onMouseMove(e) {
                    if (!this.isDrawing || !this.currentLine) return;
                    const coords = this.getCoordinates(e);
                    this.currentLine.x2 = coords.x;
                    this.currentLine.y2 = coords.y;
                },
                
                onTouchMove(e) {
                    if (!this.isDrawing || !this.currentLine) return;
                    // Prevent scrolling while drawing
                    e.preventDefault();
                    this.onMouseMove(e);
                },

                onMouseUp() {
                    if (!this.isDrawing) return;
                    this.isDrawing = false;
                    this.currentLine = null;
                    this.startItem = null;
                },

                finishDrawing(item, side, e = null) {
                    if (!this.isDrawing || !this.currentLine || !this.startItem) return;
                    if (side !== 'right') return;

                    // If right is already connected, remove the old line
                    const existingIndex = this.lines.findIndex(l => l.right === item);
                    if (existingIndex !== -1) {
                        this.lines.splice(existingIndex, 1);
                    }
                    
                    // The element to snap to might be the target of the event
                    // For touch events, the target of touchend is the original element that was touched (the left node).
                    // So we must use document.elementFromPoint
                    let targetEl = null;
                    if (e && e.changedTouches) {
                        const touch = e.changedTouches[0];
                        targetEl = document.elementFromPoint(touch.clientX, touch.clientY);
                        // Make sure targetEl is a right-node
                        if (targetEl && targetEl.classList.contains('bg-white') && targetEl.id.startsWith('right-node')) {
                            // Valid
                        } else {
                            this.onMouseUp();
                            return; // Invalid drop
                        }
                    } else {
                        targetEl = document.getElementById('right-node-' + this.question.right_items.indexOf(item));
                    }

                    if (targetEl && this.$refs.svgCanvas) {
                        const rRect = targetEl.getBoundingClientRect();
                        const svgRect = this.$refs.svgCanvas.getBoundingClientRect();
                        
                        this.lines.push({
                            x1: this.currentLine.x1,
                            y1: this.currentLine.y1,
                            x2: rRect.left - svgRect.left + rRect.width/2,
                            y2: rRect.top - svgRect.top + rRect.height/2,
                            left: this.startItem,
                            right: item
                        });
                        
                        this.updateAnswers();
                    }

                    this.isDrawing = false;
                    this.currentLine = null;
                    this.startItem = null;
                },

                updateAnswers() {
                    const pairs = this.lines.map(l => ({ left: l.left, right: l.right }));
                    // If no pairs, we can set to null or empty array
                    this.handleAnswerChange(this.question.id, { pairs: pairs });
                },

                clearLines() {
                    if (this.status !== 'IN_PROGRESS') return;
                    this.lines = [];
                    this.handleAnswerChange(this.question.id, { pairs: [] });
                },

                isLeftSelected(item) {
                    return this.lines.some(l => l.left === item);
                },

                isRightSelected(item) {
                    return this.lines.some(l => l.right === item);
                }
            }));
        });
    </script>
</body>
</html>
