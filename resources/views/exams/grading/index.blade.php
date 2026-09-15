<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Penilaian Ujian:') }} {{ $exam->title }}
            </h2>
            <a href="{{ route('exams.show', $exam) }}" class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Kembali ke Detail Ujian
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Daftar Sesi Ujian Selesai</h3>
                    
                    @if($attempts->isEmpty())
                        <div class="p-4 text-sm text-blue-700 bg-blue-100 rounded-lg" role="alert">
                            Belum ada siswa yang menyelesaikan ujian ini.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 whitespace-nowrap">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Nama Siswa</th>
                                        <th scope="col" class="px-6 py-3">Waktu Selesai</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                        <th scope="col" class="px-6 py-3">Nilai Total</th>
                                        <th scope="col" class="px-6 py-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attempts as $attempt)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $attempt->student->user->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $attempt->submitted_at ? $attempt->submitted_at->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($attempt->grading_status === 'WAITING_MANUAL')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Perlu Dinilai
                                                </span>
                                            @elseif($attempt->grading_status === 'FINAL')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Selesai Dinilai
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $attempt->grading_status }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $attempt->total_score ?? '0' }} / {{ $attempt->max_total_score ?? '0' }}
                                        </td>
                                        <td class="px-6 py-4 text-right align-middle">
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <a href="{{ route('exams.grading.show', [$exam, $attempt]) }}" 
                                                   class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                                                    Nilai Jawaban
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $attempts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
