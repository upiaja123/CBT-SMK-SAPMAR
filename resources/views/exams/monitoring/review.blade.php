<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Integrity Review: ') }} {{ $attempt->student->user->name }}
        </h2>
        <div class="text-sm text-gray-500 mt-1">
            Ujian: {{ $exam->title }} | NIS: {{ $attempt->student->nis }}
        </div>
    </x-slot>

    <div class="py-12" x-data="integrityReview()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Summary Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Kejadian</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900" x-text="summary.total">0</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-400">
                    <div class="text-sm font-medium text-gray-500">Hilang Fokus (Tab/Window)</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900" x-text="summary.focus_loss">0</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-orange-400">
                    <div class="text-sm font-medium text-gray-500">Keluar Fullscreen</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900" x-text="summary.fullscreen_exit">0</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-400">
                    <div class="text-sm font-medium text-gray-500">Koneksi Terputus</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900" x-text="summary.network_drop">0</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Timeline Section -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Timeline Integritas</h3>
                        
                        <select x-model="filter" @change="fetchEvents(1)" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="all">Semua Kejadian</option>
                            <option value="focus">Fokus & Tab</option>
                            <option value="fullscreen">Fullscreen</option>
                            <option value="network">Koneksi Network</option>
                        </select>
                    </div>

                    <div class="p-6 relative">
                        <!-- Loading State -->
                        <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <!-- Timeline List -->
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                <template x-for="(event, eventIdx) in events" :key="event.id">
                                    <li>
                                        <div class="relative pb-8">
                                            <span x-show="eventIdx !== events.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white" :class="getEventColor(event.event_type)">
                                                        <!-- SVG based on event type -->
                                                        <svg x-show="['TAB_HIDDEN', 'WINDOW_BLUR'].includes(event.event_type)" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        <svg x-show="['FULLSCREEN_EXIT'].includes(event.event_type)" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd" />
                                                        </svg>
                                                        <svg x-show="['NETWORK_OFFLINE'].includes(event.event_type)" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                        </svg>
                                                        <svg x-show="['TAB_VISIBLE', 'WINDOW_FOCUS', 'FULLSCREEN_ENTER', 'NETWORK_ONLINE'].includes(event.event_type)" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm text-gray-500" x-text="event.description"></p>
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                                        <time :datetime="event.server_received_at" x-text="formatTime(event.server_received_at)"></time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>

                            <div x-show="events.length === 0 && !loading" class="text-center py-4 text-gray-500">
                                Tidak ada kejadian yang terekam untuk filter ini.
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6 flex justify-between items-center border-t border-gray-200 pt-4" x-show="totalPages > 1">
                            <button @click="fetchEvents(currentPage - 1)" :disabled="currentPage === 1" class="px-4 py-2 border rounded text-sm font-medium disabled:opacity-50">
                                Sebelumnya
                            </button>
                            <span class="text-sm text-gray-500">Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span></span>
                            <button @click="fetchEvents(currentPage + 1)" :disabled="currentPage === totalPages" class="px-4 py-2 border rounded text-sm font-medium disabled:opacity-50">
                                Selanjutnya
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Review Panel -->
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Status Review</h3>
                            
                            <div class="flex items-center space-x-3 mb-4">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                    :class="{
                                        'bg-gray-100 text-gray-800': reviewStatus === 'UNREVIEWED',
                                        'bg-blue-100 text-blue-800': reviewStatus === 'NOTED',
                                        'bg-green-100 text-green-800': reviewStatus === 'REVIEWED'
                                    }" x-text="reviewStatus">
                                </span>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">Ubah Status</label>
                                <div class="mt-1 flex">
                                    <select x-model="pendingReviewStatus" class="block w-full rounded-l-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 sm:text-sm">
                                        <option value="UNREVIEWED">UNREVIEWED</option>
                                        <option value="NOTED">NOTED</option>
                                        <option value="REVIEWED">REVIEWED</option>
                                    </select>
                                    <button @click="updateStatus()" :disabled="updatingState" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col h-[500px]">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Catatan Review</h3>
                        </div>
                        
                        <div class="p-4 flex-1 overflow-y-auto space-y-4">
                            <!-- Notes List -->
                            @foreach($attempt->reviewNotes as $note)
                            <div class="bg-gray-50 rounded p-3">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-xs font-semibold text-gray-700">{{ $note->reviewer->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $note->created_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <p class="text-sm text-gray-800 whitespace-pre-line">{{ $note->note }}</p>
                            </div>
                            @endforeach

                            <template x-for="note in newNotes" :key="note.id">
                                <div class="bg-gray-50 rounded p-3">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="text-xs font-semibold text-gray-700" x-text="note.reviewer.name"></span>
                                        <span class="text-xs text-gray-500">Baru saja</span>
                                    </div>
                                    <p class="text-sm text-gray-800 whitespace-pre-line" x-text="note.note"></p>
                                </div>
                            </template>

                            <div x-show="{{ $attempt->reviewNotes->isEmpty() ? 'true' : 'false' }} && newNotes.length === 0" class="text-sm text-gray-500 text-center py-4">
                                Belum ada catatan review.
                            </div>
                        </div>

                        <div class="p-4 border-t border-gray-200 bg-gray-50">
                            <textarea x-model="noteContent" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Tambahkan catatan..."></textarea>
                            <div class="mt-3 flex justify-end">
                                <button @click="submitNote()" :disabled="!noteContent.trim() || submittingNote" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-800 hover:bg-gray-900 disabled:opacity-50">
                                    <span x-show="submittingNote">Menyimpan...</span>
                                    <span x-show="!submittingNote">Simpan Catatan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('integrityReview', () => ({
                examId: {{ $exam->id }},
                attemptId: {{ $attempt->id }},
                events: [],
                summary: {
                    total: 0,
                    focus_loss: 0,
                    fullscreen_exit: 0,
                    network_drop: 0
                },
                filter: 'all',
                currentPage: 1,
                totalPages: 1,
                loading: false,
                reviewStatus: '{{ $attempt->review_status }}',
                pendingReviewStatus: '{{ $attempt->review_status }}',
                updatingState: false,
                noteContent: '',
                submittingNote: false,
                newNotes: [],

                init() {
                    this.fetchEvents(1);
                    
                    // Optional: Listen to Reverb for live updates if proctor wants to review a live attempt
                    if (window.Echo) {
                        window.Echo.private(`exam.${this.examId}.monitoring`)
                            .listen('IntegrityEventRecorded', (e) => {
                                if (e.exam_attempt_id === this.attemptId && this.currentPage === 1) {
                                    // Soft refresh to update counts and latest events
                                    this.fetchEvents(1);
                                }
                            });
                    }
                },

                async fetchEvents(page = 1) {
                    this.loading = true;
                    try {
                        const response = await fetch(`/exams/${this.examId}/attempts/${this.attemptId}/integrity-events?page=${page}&filter=${this.filter}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (!response.ok) throw new Error('Network response was not ok');
                        const data = await response.json();
                        
                        this.events = data.events.data;
                        this.currentPage = data.events.current_page;
                        this.totalPages = data.events.last_page;
                        this.summary = data.summary;
                    } catch (error) {
                        console.error('Failed to fetch events:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async updateStatus() {
                    this.updatingState = true;
                    try {
                        const response = await fetch(`/exams/${this.examId}/attempts/${this.attemptId}/review/state`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                review_status: this.pendingReviewStatus
                            })
                        });
                        
                        if (!response.ok) throw new Error('Update failed');
                        
                        const data = await response.json();
                        this.reviewStatus = data.review_status;
                        
                    } catch (error) {
                        console.error('Update state failed:', error);
                        alert('Gagal mengupdate status review');
                    } finally {
                        this.updatingState = false;
                    }
                },

                async submitNote() {
                    if (!this.noteContent.trim()) return;
                    
                    this.submittingNote = true;
                    try {
                        const response = await fetch(`/exams/${this.examId}/attempts/${this.attemptId}/review/notes`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                note: this.noteContent
                            })
                        });
                        
                        if (!response.ok) throw new Error('Note submission failed');
                        
                        const data = await response.json();
                        this.newNotes.push(data.note);
                        this.noteContent = '';
                        
                        if(this.reviewStatus === 'UNREVIEWED') {
                            this.pendingReviewStatus = 'NOTED';
                            this.updateStatus();
                        }
                    } catch (error) {
                        console.error('Note submission failed:', error);
                        alert('Gagal menyimpan catatan');
                    } finally {
                        this.submittingNote = false;
                    }
                },

                formatTime(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second:'2-digit' });
                },

                getEventColor(type) {
                    if (['TAB_HIDDEN', 'WINDOW_BLUR'].includes(type)) return 'bg-yellow-500';
                    if (['FULLSCREEN_EXIT'].includes(type)) return 'bg-orange-500';
                    if (['NETWORK_OFFLINE'].includes(type)) return 'bg-red-500';
                    if (['TAB_VISIBLE', 'WINDOW_FOCUS', 'FULLSCREEN_ENTER', 'NETWORK_ONLINE'].includes(type)) return 'bg-green-500';
                    return 'bg-gray-500';
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
