<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Monitoring Ujian
        </h2>
        <p class="text-sm text-gray-500 mt-1">Daftar ujian yang dapat Anda pantau secara real-time.</p>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if($exams->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                    <p class="text-lg font-medium text-gray-900">Tidak ada ujian aktif</p>
                    <p class="text-sm text-gray-500 mt-2">
                        @if(auth()->user()->hasRole('proktor'))
                            Anda belum ditugaskan ke ujian manapun, atau tidak ada ujian yang sedang berlangsung.
                        @else
                            Belum ada ujian dengan status PUBLISHED / ACTIVE / PAUSED.
                        @endif
                    </p>
                </div>
            @else

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-5 text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ $exams->count() }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total Ujian Aktif</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $exams->sum('active_count') }}</div>
                    <div class="text-xs text-gray-500 mt-1">Siswa Mengerjakan</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5 text-center border-l-4 border-yellow-400">
                    <div class="text-3xl font-bold text-yellow-600">{{ $exams->sum('alert_count') }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total Peringatan</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5 text-center border-l-4 border-red-400">
                    <div class="text-3xl font-bold text-red-600">{{ $exams->sum('locked_count') }}</div>
                    <div class="text-xs text-gray-500 mt-1">Sesi Dikunci</div>
                </div>
            </div>

            @if($exams->sum('locked_count') > 0)
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-300 text-red-800 rounded-lg text-sm font-medium">
                Ada {{ $exams->sum('locked_count') }} sesi siswa yang sedang dikunci.
                Klik tombol "Pantau" pada ujian terkait, lalu klik "Review Integritas" untuk membuka kunci siswa tersebut.
            </div>
            @endif

            <!-- Exams Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Ujian</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Ujian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aktif / Total</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Peringatan</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Dikunci</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($exams as $exam)
                            @php
                                $rowClass = $exam->locked_count > 0 ? 'bg-red-50' : ($exam->alert_count > 0 ? 'bg-yellow-50' : '');
                                $statusColors = ['PUBLISHED'=>'bg-blue-100 text-blue-800','ACTIVE'=>'bg-green-100 text-green-800','PAUSED'=>'bg-yellow-100 text-yellow-800'];
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $exam->title }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ optional($exam->subject)->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$exam->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $exam->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm">
                                    <span class="font-bold text-blue-600">{{ $exam->active_count }}</span>
                                    <span class="text-gray-500"> / {{ $exam->total_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($exam->alert_count > 0)
                                        <span class="font-bold text-red-600">{{ $exam->alert_count }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($exam->locked_count > 0)
                                        <span class="font-bold text-red-700">{{ $exam->locked_count }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('exams.monitoring.index', $exam) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm">
                                        Pantau
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
