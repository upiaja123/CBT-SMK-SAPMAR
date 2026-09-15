<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Administrator Sistem</h2>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
                    {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Server & DB Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-sapta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                        Informasi Server
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex justify-between"><span class="font-medium">Sistem Operasi:</span> <span>{{ $serverInfo['os'] }}</span></li>
                        <li class="flex justify-between"><span class="font-medium">Software Server:</span> <span>{{ $serverInfo['server_software'] }}</span></li>
                        <li class="flex justify-between"><span class="font-medium">Versi PHP:</span> <span>{{ $serverInfo['php_version'] }}</span></li>
                        <li class="flex justify-between"><span class="font-medium">Versi Laravel:</span> <span>{{ $serverInfo['laravel_version'] }}</span></li>
                    </ul>

                    <h3 class="text-lg font-semibold border-b pb-2 mt-6 mb-4 text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-sapta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        Informasi Database
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex justify-between"><span class="font-medium">Database:</span> <span class="capitalize">{{ $dbInfo['connection'] }}</span></li>
                        <li class="flex justify-between"><span class="font-medium">Versi:</span> <span>{{ $dbInfo['version'] }}</span></li>
                        <li class="flex justify-between"><span class="font-medium">Nama DB:</span> <span>{{ $dbInfo['name'] }}</span></li>
                        <li class="flex justify-between"><span class="font-medium">Ukuran Perkiraan:</span> <span class="text-sapta-600 font-bold">{{ $dbInfo['size_mb'] }} MB</span></li>
                    </ul>
                </div>

                <!-- Maintenance & Logs -->
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-sapta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Mode Perbaikan (Maintenance)
                        </h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Saat mode perbaikan diaktifkan, semua pengguna kecuali Super Admin akan melihat halaman perbaikan dan tidak dapat login atau mengakses sistem.
                        </p>
                        <form action="{{ route('system.maintenance.toggle') }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Anda yakin ingin mengubah status maintenance?')" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $isDown ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                                {{ $isDown ? 'Nonaktifkan Mode Perbaikan (Buka Sistem)' : 'Aktifkan Mode Perbaikan (Tutup Sistem)' }}
                            </button>
                        </form>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-sapta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Pemantauan Log Error
                        </h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Lihat riwayat error aplikasi, peringatan, dan catatan sistem secara detail untuk mempermudah perbaikan.
                        </p>
                        <a href="{{ url('log-viewer') }}" target="_blank" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Buka Log Viewer (Tab Baru)
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Database Backup -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-sapta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Backup Database
                    </h3>
                    <form action="{{ route('system.backup.run') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Mulai proses backup database? Ini mungkin memakan waktu sesaat.')" class="bg-sapta-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-sapta-700">
                            Backup Sekarang
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama File</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ukuran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($backups as $backup)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $backup['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $backup['size'] }} MB</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $backup['date']->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('system.backup.download', ['path' => $backup['path']]) }}" class="text-sapta-600 hover:text-sapta-900 flex items-center justify-end gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Download
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Belum ada file backup.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
