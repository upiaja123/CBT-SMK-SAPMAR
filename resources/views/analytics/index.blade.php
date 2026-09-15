<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analytics Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Subject Performance Overview</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Pelajaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Ujian</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peserta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rata-rata Nilai</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tingkat Kelulusan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($subjectAnalytics as $subject)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $subject->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $subject->exam_count }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $subject->participant_count }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 font-bold">
                                            @if($subject->avg_score !== null)
                                                {{ number_format($subject->avg_score, 1) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($subject->pass_rate !== null)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subject->pass_rate >= 60 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ number_format($subject->pass_rate, 1) }}%
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data analytics.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
