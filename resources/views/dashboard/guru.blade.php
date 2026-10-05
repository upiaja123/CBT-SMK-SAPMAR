<x-app-layout>
    <x-slot name="title">
        Dashboard Guru
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
        {{-- Stats Cards --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Bank Soal Saya</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['my_question_banks'] }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0 text-orange-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Soal Dibuat</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['my_questions'] }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Ujian Dikelola</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['my_exams'] }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Ujian Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $stats['active_exams'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-sapta-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                Statistik Bank Soal
            </h2>
            <div class="relative h-64 w-full">
                <canvas id="guruBankSoalChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-sapta-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                Proporsi Ujian Aktif
            </h2>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="guruExamsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-sapta-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Bank Soal Terbaru
            </h2>
            <div class="space-y-3">
                @forelse($stats['recent_banks'] as $bank)
                    <div class="p-3 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">{{ $bank->name }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ $bank->subject?->name }} | {{ $bank->questions()->count() }} Soal</p>
                            </div>
                            <a href="{{ route('question_banks.show', $bank) }}" class="text-xs text-sapta-600 font-medium hover:underline">Kelola &rarr;</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">Belum ada bank soal dibuat.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('question_banks.create') }}" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition text-center group">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mb-2 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Buat Bank Soal</span>
                </a>
                <a href="{{ route('exams.create') }}" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-sapta-500 hover:bg-sapta-50 transition text-center group">
                    <div class="w-10 h-10 rounded-full bg-sapta-100 flex items-center justify-center text-sapta-600 mb-2 group-hover:scale-110 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Rakit Ujian</span>
                </a>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                if (typeof Chart === 'undefined') {
                    console.error("Chart.js gagal dimuat dari internet.");
                    return;
                }

                // Chart 1: Bank Soal (Bar Chart)
                const ctxBank = document.getElementById('guruBankSoalChart');
                if (ctxBank) {
                    new Chart(ctxBank, {
                        type: 'bar',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                            datasets: [{
                                label: 'Soal Dibuat',
                                data: [12, 19, 3, 5, 2, {{ $stats['my_questions'] }}],
                                backgroundColor: 'rgba(99, 102, 241, 0.2)',
                                borderColor: 'rgb(99, 102, 241)',
                                borderWidth: 1,
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }

                // Chart 2: Ujian (Doughnut Chart)
                const ctxExam = document.getElementById('guruExamsChart');
                if (ctxExam) {
                    new Chart(ctxExam, {
                        type: 'doughnut',
                        data: {
                            labels: ['Aktif', 'Selesai/Draft'],
                            datasets: [{
                                data: [
                                    {{ $stats['active_exams'] }}, 
                                    {{ max(0, $stats['my_exams'] - $stats['active_exams']) }}
                                ],
                                backgroundColor: [
                                    'rgb(34, 197, 94)',
                                    'rgb(229, 231, 235)'
                                ],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                }
            } catch (e) {
                console.error("Error merender chart guru:", e);
            }
        });
    </script>
    @endpush
</x-app-layout>
