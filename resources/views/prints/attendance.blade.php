<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir - {{ $exam->title }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .print-container {
            max-width: 210mm; /* A4 width */
            margin: 0 auto;
            background-color: white;
            padding: 20mm;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        @media print {
            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .print-container {
                max-width: 100%;
                width: 100%;
                padding: 10mm;
                box-shadow: none;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
        table.border-print th, table.border-print td {
            border: 1px solid #000;
        }
    </style>
</head>
<body class="text-gray-900 text-sm">
    <div class="no-print max-w-[210mm] mx-auto mb-4 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Cetak Daftar Hadir</h1>
            <p class="text-sm text-gray-500">Gunakan kertas A4 untuk hasil terbaik.</p>
        </div>
        <button onclick="window.print()" class="bg-sapta-600 hover:bg-sapta-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Sekarang
        </button>
    </div>

    <div class="print-container">
        <div class="text-center mb-6">
            <h1 class="font-bold text-xl uppercase">DAFTAR HADIR PESERTA UJIAN</h1>
            <h2 class="font-bold text-lg uppercase">SMK SAPTA MARGA</h2>
            <p class="text-sm">TAHUN PELAJARAN 2026/2027</p>
        </div>

        <table class="w-full mb-6 text-sm font-semibold">
            <tr>
                <td class="w-32 py-1">Nama Ujian</td>
                <td class="w-4 py-1">:</td>
                <td class="py-1">{{ $exam->title }}</td>
                <td class="w-32 py-1">Mata Pelajaran</td>
                <td class="w-4 py-1">:</td>
                <td class="py-1">{{ $exam->subject?->name }}</td>
            </tr>
            <tr>
                <td class="py-1">Tanggal</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ \Carbon\Carbon::parse($exam->start_at)->format('d F Y') }}</td>
                <td class="py-1">Waktu</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ \Carbon\Carbon::parse($exam->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($exam->end_at)->format('H:i') }}</td>
            </tr>
            <tr>
                <td class="py-1">Durasi</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $exam->duration }} Menit</td>
                <td class="py-1">Ruang</td>
                <td class="py-1">:</td>
                <td class="py-1">___________________</td>
            </tr>
        </table>

        <table class="w-full border-print text-sm mb-8 border-collapse">
            <thead>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="py-2 w-10">No</th>
                    <th class="py-2 w-32">Username</th>
                    <th class="py-2">Nama Lengkap</th>
                    <th class="py-2 w-24">Kelas</th>
                    <th class="py-2 w-48" colspan="2">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students->sortBy('schoolClass.name') as $index => $student)
                    <tr>
                        <td class="py-3 text-center">{{ $index + 1 }}</td>
                        <td class="py-3 px-2 text-center">{{ $student->user?->username ?? '-' }}</td>
                        <td class="py-3 px-2 font-semibold">{{ $student->user?->name ?? 'User Terhapus' }}</td>
                        <td class="py-3 px-2 text-center">{{ $student->schoolClass?->name ?? '-' }}</td>
                        @if(($index + 1) % 2 != 0)
                            <td class="py-3 px-2 w-24 border-r-0">{{ $index + 1 }}. </td>
                            <td class="py-3 px-2 w-24 border-l-0"></td>
                        @else
                            <td class="py-3 px-2 w-24 border-r-0"></td>
                            <td class="py-3 px-2 w-24 border-l-0">{{ $index + 1 }}. </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end mt-8 mr-12">
            <div class="text-center w-48">
                <p>............, ..............................</p>
                <p class="mb-16">Pengawas Ruang,</p>
                <p class="font-bold underline">(.................................................)</p>
                <p>NIP. </p>
            </div>
        </div>
    </div>
</body>
</html>
