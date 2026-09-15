<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Monitoring Ujian: ') }} {{ $exam->title }}
            </h2>
            <div id="connection-status" class="px-3 py-1 text-sm rounded-full bg-gray-200 text-gray-700">
                Menghubungkan...
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="monitoringDashboard()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-4 flex flex-wrap justify-end gap-3">
                <a href="{{ route('prints.attendance', $exam) }}" target="_blank" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-2 rounded-xl shadow-sm text-sm font-medium flex items-center gap-2 border border-indigo-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Daftar Hadir
                </a>
                <a href="{{ route('prints.berita-acara', $exam) }}" target="_blank" class="bg-sapta-50 hover:bg-sapta-100 text-sapta-700 px-4 py-2 rounded-xl shadow-sm text-sm font-medium flex items-center gap-2 border border-sapta-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Cetak Berita Acara
                </a>
                <button @click="fetchData()" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-xl shadow-sm text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </button>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    
                    <template x-if="loading">
                        <div class="text-center py-4 text-gray-500">Memuat data...</div>
                    </template>

                    <template x-if="!loading && attempts.length === 0">
                        <div class="text-center py-4 text-gray-500">Belum ada peserta ujian.</div>
                    </template>

                    <!-- Desktop Table -->
                    <table class="min-w-full divide-y divide-gray-200 hidden md:table" x-show="!loading && attempts.length > 0">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peserta</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koneksi</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Dilihat</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Integritas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="attempt in attempts" :key="attempt.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="attempt.student_name"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatStatus(attempt.status)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="getConnectionColor(attempt.computed_connection)"
                                              x-text="attempt.computed_connection">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(attempt.last_seen_at)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="font-bold text-red-600" x-show="attempt.integrity_event_count > 0">
                                            <span x-text="attempt.integrity_event_count"></span> peringatan
                                        </div>
                                        <div class="text-xs mt-1" x-show="attempt.last_integrity_event" x-text="translateEvent(attempt.last_integrity_event?.event_type)"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <template x-if="attempt.status === 'IN_PROGRESS' || attempt.status === 'NOT_STARTED'">
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                <button @click="openExtraTimeModal(attempt)" class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 shadow-sm" title="Tambah Waktu">+ Waktu</button>
                                                <template x-if="!attempt.is_locked">
                                                    <button @click="openLockModal(attempt)" class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded text-white bg-yellow-500 hover:bg-yellow-600 shadow-sm" title="Kunci">Kunci</button>
                                                </template>
                                                <template x-if="attempt.is_locked">
                                                    <button @click="unlockAttempt(attempt)" class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 shadow-sm" title="Buka Kunci">Buka Kunci</button>
                                                </template>
                                                <button @click="openForceSubmitModal(attempt)" class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 shadow-sm" title="Paksa Kumpulkan">Submit</button>
                                            </div>
                                        </template>
                                        <div class="mt-2" x-show="attempt.integrity_event_count > 0">
                                            <a :href="`/exams/${examId}/attempts/${attempt.id}/review`" class="text-indigo-600 hover:text-indigo-900 text-xs bg-indigo-50 px-2 py-1 rounded inline-block">
                                                Review Integritas
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4" x-show="!loading && attempts.length > 0">
                        <template x-for="attempt in attempts" :key="attempt.id">
                            <div class="bg-white border rounded-lg p-4 shadow-sm">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-bold text-gray-900" x-text="attempt.student_name"></div>
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                          :class="getConnectionColor(attempt.computed_connection)"
                                          x-text="attempt.computed_connection">
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600 mb-1">Status: <span x-text="formatStatus(attempt.status)"></span></div>
                                <div class="text-sm text-gray-600 mb-2">Terakhir Dilihat: <span x-text="formatDate(attempt.last_seen_at)"></span></div>
                                
                                <div class="bg-red-50 p-2 rounded text-sm" x-show="attempt.integrity_event_count > 0">
                                    <div class="font-bold text-red-600"><span x-text="attempt.integrity_event_count"></span> peringatan</div>
                                    <div class="text-red-500 text-xs mt-1" x-text="translateEvent(attempt.last_integrity_event?.event_type)"></div>
                                </div>
                                <template x-if="attempt.status === 'IN_PROGRESS' || attempt.status === 'NOT_STARTED'">
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button @click="openExtraTimeModal(attempt)" class="text-blue-600 hover:bg-blue-100 bg-blue-50 px-3 py-1.5 rounded text-sm font-medium">+ Waktu</button>
                                        <template x-if="!attempt.is_locked">
                                            <button @click="openLockModal(attempt)" class="text-yellow-600 hover:bg-yellow-100 bg-yellow-50 px-3 py-1.5 rounded text-sm font-medium">Kunci</button>
                                        </template>
                                        <template x-if="attempt.is_locked">
                                            <button @click="unlockAttempt(attempt)" class="text-green-600 hover:bg-green-100 bg-green-50 px-3 py-1.5 rounded text-sm font-medium">Buka Kunci</button>
                                        </template>
                                        <button @click="openForceSubmitModal(attempt)" class="text-red-600 hover:bg-red-100 bg-red-50 px-3 py-1.5 rounded text-sm font-medium">Submit</button>
                                    </div>
                                </template>
                                <div class="mt-2" x-show="attempt.integrity_event_count > 0">
                                    <a :href="`/exams/${examId}/attempts/${attempt.id}/review`" class="text-indigo-600 hover:bg-indigo-100 bg-indigo-50 px-3 py-1.5 rounded text-sm font-medium inline-block">
                                        Review Integritas
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div x-data="monitoringDashboard()">
        <!-- Extra Time Modal -->
        <div x-show="modals.extraTime.show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="modals.extraTime.show = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">Tambah Waktu Ujian</h3>
                        <p class="text-sm text-gray-500 mb-4">Tambahkan perpanjangan waktu untuk peserta <strong x-text="modals.extraTime.attempt?.student_name"></strong>.</p>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Durasi (Menit)</label>
                            <input type="number" min="1" max="120" x-model="modals.extraTime.minutes" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitExtraTime()" :disabled="submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            Simpan
                        </button>
                        <button type="button" @click="modals.extraTime.show = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lock Modal -->
        <div x-show="modals.lock.show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="modals.lock.show = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">Kunci Sesi Ujian</h3>
                        <p class="text-sm text-gray-500 mb-4">Siswa <strong x-text="modals.lock.attempt?.student_name"></strong> tidak akan bisa menjawab soal selama ujian dikunci.</p>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Alasan Penguncian (Opsional)</label>
                            <input type="text" x-model="modals.lock.reason" placeholder="Misal: Mencurigakan..." class="mt-1 focus:ring-yellow-500 focus:border-yellow-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitLock()" :disabled="submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            Kunci Sekarang
                        </button>
                        <button type="button" @click="modals.lock.show = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Force Submit Modal -->
        <div x-show="modals.forceSubmit.show" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="modals.forceSubmit.show = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Paksa Kumpulkan Ujian</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Anda yakin ingin memaksa mengumpulkan ujian untuk <strong x-text="modals.forceSubmit.attempt?.student_name"></strong>?
                                        Siswa tidak akan dapat melanjutkan ujian. Tindakan ini tidak dapat dibatalkan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submitForceSubmit()" :disabled="submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            Ya, Paksa Kumpulkan
                        </button>
                        <button type="button" @click="modals.forceSubmit.show = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function monitoringDashboard() {
            return {
                attempts: [],
                loading: true,
                submitting: false,
                examId: {{ $exam->id }},
                modals: {
                    extraTime: { show: false, attempt: null, minutes: 15 },
                    lock: { show: false, attempt: null, reason: '' },
                    forceSubmit: { show: false, attempt: null }
                },
                
                init() {
                    this.fetchData();
                    this.setupRealtime();
                    
                    // Periodically compute OFFLINE status visually based on last_seen_at
                    setInterval(() => {
                        this.recomputeConnections();
                    }, 5000);
                },

                async fetchData() {
                    try {
                        const response = await fetch(`/exams/${this.examId}/monitoring`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();
                        this.attempts = data.attempts.map(att => this.processAttempt(att));
                        this.loading = false;
                    } catch (e) {
                        console.error('Failed to fetch monitoring data', e);
                    }
                },
                
                processAttempt(attempt) {
                    attempt.computed_connection = attempt.connection_status;
                    return attempt;
                },

                recomputeConnections() {
                    const now = new Date();
                    this.attempts.forEach(attempt => {
                        if (attempt.last_seen_at) {
                            const lastSeen = new Date(attempt.last_seen_at);
                            const diffSeconds = (now - lastSeen) / 1000;
                            if (diffSeconds > 30) {
                                attempt.computed_connection = 'OFFLINE';
                            } else {
                                attempt.computed_connection = 'ONLINE';
                            }
                        } else {
                            attempt.computed_connection = 'UNKNOWN';
                        }
                    });
                },

                setupRealtime() {
                    if (typeof window.Echo === 'undefined') {
                        setTimeout(() => this.setupRealtime(), 100);
                        return;
                    }

                    // Connection status tracking
                    const statusBadge = document.getElementById('connection-status');
                    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
                        if (states.current === 'connected') {
                            statusBadge.className = 'px-3 py-1 text-sm rounded-full bg-green-100 text-green-800';
                            statusBadge.innerText = 'Realtime Terhubung';
                        } else {
                            statusBadge.className = 'px-3 py-1 text-sm rounded-full bg-red-100 text-red-800';
                            statusBadge.innerText = 'Realtime Terputus';
                        }
                    });

                    window.Echo.private(`exam.${this.examId}.monitoring`)
                        .listen('ExamAttemptPresenceUpdated', (e) => {
                            const index = this.attempts.findIndex(a => a.id === e.attempt_id);
                            if (index !== -1) {
                                this.attempts[index].last_seen_at = e.last_seen_at;
                                this.attempts[index].status = e.status;
                                this.attempts[index].computed_connection = e.connection_status;
                                this.recomputeConnections(); // forces re-evaluation
                            }
                        })
                        .listen('IntegrityEventRecorded', (e) => {
                            const index = this.attempts.findIndex(a => a.id === e.attempt_id);
                            if (index !== -1) {
                                this.attempts[index].integrity_event_count++;
                                this.attempts[index].last_integrity_event = {
                                    event_type: e.event_type,
                                    occurred_at: e.occurred_at
                                };
                            }
                        })
                        .listen('attempt.control.updated', (e) => {
                            const index = this.attempts.findIndex(a => a.id === e.attempt_id);
                            if (index !== -1) {
                                this.attempts[index].status = e.status;
                                this.attempts[index].is_locked = e.is_locked;
                                this.attempts[index].deadline_at = e.deadline_at;
                            }
                        });
                },

                getConnectionColor(status) {
                    if (status === 'ONLINE') return 'bg-green-100 text-green-800';
                    if (status === 'OFFLINE') return 'bg-red-100 text-red-800';
                    return 'bg-gray-100 text-gray-800';
                },

                formatStatus(status) {
                    const map = {
                        'NOT_STARTED': 'Belum Mulai',
                        'IN_PROGRESS': 'Mengerjakan',
                        'SUBMITTED': 'Selesai',
                        'AUTO_SUBMITTED': 'Selesai Otomatis'
                    };
                    return map[status] || status;
                },

                formatDate(isoString) {
                    if (!isoString) return '-';
                    const d = new Date(isoString);
                    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },

                translateEvent(type) {
                    if (!type) return '-';
                    const map = {
                        'TAB_HIDDEN': 'Tab disembunyikan',
                        'TAB_VISIBLE': 'Tab dibuka kembali',
                        'WINDOW_BLUR': 'Perubahan fokus window',
                        'WINDOW_FOCUS': 'Fokus window kembali',
                        'FULLSCREEN_EXIT': 'Keluar dari fullscreen',
                        'FULLSCREEN_ENTER': 'Masuk ke fullscreen',
                        'NETWORK_OFFLINE': 'Koneksi internet terputus',
                        'NETWORK_ONLINE': 'Koneksi internet tersambung'
                    };
                    return map[type] || type;
                },

                // Actions
                openExtraTimeModal(attempt) {
                    this.modals.extraTime.attempt = attempt;
                    this.modals.extraTime.minutes = 15;
                    this.modals.extraTime.show = true;
                },
                
                openLockModal(attempt) {
                    this.modals.lock.attempt = attempt;
                    this.modals.lock.reason = '';
                    this.modals.lock.show = true;
                },
                
                openForceSubmitModal(attempt) {
                    this.modals.forceSubmit.attempt = attempt;
                    this.modals.forceSubmit.show = true;
                },

                async submitExtraTime() {
                    this.submitting = true;
                    try {
                        const res = await this.postAction(this.modals.extraTime.attempt.id, 'extra-time', { minutes: parseInt(this.modals.extraTime.minutes) });
                        if (res.ok) this.modals.extraTime.show = false;
                    } finally {
                        this.submitting = false;
                    }
                },
                
                async submitLock() {
                    this.submitting = true;
                    try {
                        const res = await this.postAction(this.modals.lock.attempt.id, 'lock', { reason: this.modals.lock.reason });
                        if (res.ok) this.modals.lock.show = false;
                    } finally {
                        this.submitting = false;
                    }
                },
                
                async unlockAttempt(attempt) {
                    if (!confirm(`Buka kunci ujian untuk ${attempt.student_name}?`)) return;
                    await this.postAction(attempt.id, 'unlock');
                },

                async submitForceSubmit() {
                    this.submitting = true;
                    try {
                        const res = await this.postAction(this.modals.forceSubmit.attempt.id, 'force-submit');
                        if (res.ok) this.modals.forceSubmit.show = false;
                    } finally {
                        this.submitting = false;
                    }
                },

                async postAction(attemptId, action, data = {}) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    try {
                        const res = await fetch(`/exams/${this.examId}/attempts/${attemptId}/${action}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify(data)
                        });
                        if (!res.ok) {
                            const result = await res.json();
                            alert(result.message || 'Terjadi kesalahan.');
                            return {ok: false};
                        }
                        // Note: state is updated via realtime events, no need to manually update state here
                        return {ok: true};
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan jaringan.');
                        return {ok: false};
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
