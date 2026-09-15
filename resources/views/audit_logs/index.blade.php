<x-app-layout>
    <x-slot name="title">Audit Log System</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Audit Log Sistem</h2>
        <p class="text-sm text-gray-500">Catatan riwayat seluruh tindakan administratif sensitif.</p>
    </div>

    {{-- Filter --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-100 mb-6 shadow-sm flex flex-wrap items-center gap-4">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap items-center gap-4 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pengguna..." class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-sapta-500 outline-none flex-1 min-w-[200px]">
            <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter jenis aksi (e.g. created)..." class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-sapta-500 outline-none">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-xl hover:bg-gray-900 transition">Filter</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Aktor / User</th>
                        <th class="px-6 py-4">Tindakan / Aksi</th>
                        <th class="px-6 py-4">Entitas Target</th>
                        <th class="px-6 py-4">IP Address</th>
                        <th class="px-6 py-4">Hasil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($auditLogs as $log)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-sapta-600">{{ $log->action }}</td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $log->outcome === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ strtoupper($log->outcome) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $auditLogs->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
