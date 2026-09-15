<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Ujian - {{ $class->name }}</title>
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
        }
        .page-break {
            page-break-after: always;
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
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="no-print mb-4 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Cetak Kartu Ujian - {{ $class->name }}</h1>
                <p class="text-sm text-gray-500">Gunakan kertas A4 dan margin minimal untuk hasil terbaik.</p>
            </div>
            <button onclick="window.print()" class="bg-sapta-600 hover:bg-sapta-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Sekarang
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            @foreach($students as $index => $student)
                <div class="border-2 border-blue-300 rounded-lg p-0 bg-white overflow-hidden" style="break-inside: avoid; height: 7.2cm;">
                    <div class="bg-blue-100 text-blue-900 p-2 text-center border-b-2 border-blue-300 flex items-center justify-between">
                        <div class="w-16 h-16 shrink-0 flex items-center justify-center">
                            <img src="{{ asset('images/logo-sapmar.png') }}" alt="Logo Sapmar" class="w-full h-full object-contain drop-shadow-sm">
                        </div>
                        <div class="flex-1 text-center">
                            <h2 class="text-sm font-bold uppercase tracking-wider">KARTU PESERTA UJIAN</h2>
                            <p class="text-[10px] font-semibold">SMK SAPTA MARGA</p>
                            <p class="text-[10px]">TAHUN PELAJARAN {{ $class->academicYear->name ?? '2026/2027' }}</p>
                        </div>
                        <div class="w-16 shrink-0"></div> {{-- Spacer for balance --}}
                    </div>
                    <div class="p-4 flex gap-4 h-[calc(100%-3.5rem)]">
                        <div class="w-24 h-32 border border-gray-300 rounded flex items-center justify-center bg-gray-50 text-gray-400 shrink-0">
                            FOTO
                            3x4
                        </div>
                        <div class="flex-1">
                            <table class="w-full text-xs font-bold text-gray-800">
                                <tr>
                                    <td class="py-1 w-20 align-top">Nama</td>
                                    <td class="py-1 w-2 align-top">:</td>
                                    <td class="py-1 align-top uppercase">{{ $student->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="py-1 align-top">NIS/NISN</td>
                                    <td class="py-1 align-top">:</td>
                                    <td class="py-1 align-top">{{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="py-1 align-top">Kelas</td>
                                    <td class="py-1 align-top">:</td>
                                    <td class="py-1 align-top">{{ $class->name }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="py-2">
                                        <div class="border-t border-dashed border-gray-400 my-1"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-1 align-top text-sapta-700">Username</td>
                                    <td class="py-1 align-top">:</td>
                                    <td class="py-1 align-top text-sapta-700">{{ $student->user->username }}</td>
                                </tr>
                                <tr>
                                    <td class="py-1 align-top">Password</td>
                                    <td class="py-1 align-top">:</td>
                                    <td class="py-1 align-top">******</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Break page every 10 cards (5 rows of 2) --}}
                @if(($index + 1) % 10 == 0)
                    </div>
                    <div class="page-break"></div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                @endif
            @endforeach
        </div>
    </div>
</body>
</html>
