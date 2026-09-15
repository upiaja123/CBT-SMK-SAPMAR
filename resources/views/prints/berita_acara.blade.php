<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara - {{ $exam->title }}</title>
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
            min-height: 297mm;
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
                padding: 15mm;
                box-shadow: none;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="text-gray-900 text-sm">
    <div class="no-print max-w-[210mm] mx-auto mb-4 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Cetak Berita Acara</h1>
            <p class="text-sm text-gray-500">Gunakan kertas A4 untuk hasil terbaik.</p>
        </div>
        <button onclick="window.print()" class="bg-sapta-600 hover:bg-sapta-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Sekarang
        </button>
    </div>

    <div class="print-container leading-relaxed">
        <div class="text-center mb-8 border-b-2 border-gray-900 pb-4">
            <h1 class="font-bold text-xl uppercase">BERITA ACARA PELAKSANAAN UJIAN</h1>
            <h2 class="font-bold text-lg uppercase">SMK SAPTA MARGA</h2>
            <p class="text-sm">TAHUN PELAJARAN 2026/2027</p>
        </div>

        <p class="mb-4 indent-8">
            Pada hari ini <strong>{{ \Carbon\Carbon::parse($exam->start_at)->translatedFormat('l') }}</strong> 
            tanggal <strong>{{ \Carbon\Carbon::parse($exam->start_at)->format('d') }}</strong> 
            bulan <strong>{{ \Carbon\Carbon::parse($exam->start_at)->translatedFormat('F') }}</strong> 
            tahun <strong>{{ \Carbon\Carbon::parse($exam->start_at)->format('Y') }}</strong>, 
            telah diselenggarakan Ujian Berbasis Komputer (CBT):
        </p>

        <table class="w-full mb-6 text-sm ml-8">
            <tr>
                <td class="w-48 py-1">1. Mata Pelajaran</td>
                <td class="w-4 py-1">:</td>
                <td class="py-1 font-bold">{{ $exam->subject?->name }}</td>
            </tr>
            <tr>
                <td class="py-1">2. Nama Ujian</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $exam->title }}</td>
            </tr>
            <tr>
                <td class="py-1">Waktu Ujian</td>
                <td class="py-1">:</td>
                <td class="py-1 font-semibold">{{ \Carbon\Carbon::parse($exam->start_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($exam->end_at)->format('H:i') }} WIB</td>
            </tr>
            <tr>
                <td class="py-1">4. Ruang / Lab</td>
                <td class="py-1">:</td>
                <td class="py-1">__________________________</td>
            </tr>
            <tr>
                <td class="py-1">5. Jumlah Peserta Terdaftar</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $participants->count() }} Orang</td>
            </tr>
            <tr>
                <td class="py-1">6. Jumlah Peserta Hadir</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $attempts->count() }} Orang</td>
            </tr>
            <tr>
                <td class="py-1">7. Jumlah Peserta Absen</td>
                <td class="py-1">:</td>
                <td class="py-1">{{ $participants->count() - $attempts->count() }} Orang</td>
            </tr>
        </table>

        <p class="mb-2 font-bold">Catatan Selama Ujian / Peserta Absen:</p>
        <div class="border border-gray-400 min-h-[150px] w-full mb-8 p-2 text-sm leading-8" style="background-image: repeating-linear-gradient(to bottom, transparent, transparent 31px, #ccc 31px, #ccc 32px);">
            {{-- Blank space for handwriting notes --}}
        </div>

        <p class="mb-12">Demikian Berita Acara ini dibuat dengan sesungguhnya untuk dapat digunakan sebagaimana mestinya.</p>

        <div class="grid grid-cols-2 gap-8 text-center mt-8">
            <div>
                <p class="mb-24">Proktor / Teknisi,</p>
                <p class="font-bold underline">(.................................................)</p>
                <p>NIP. </p>
            </div>
            <div>
                <p class="mb-24">Pengawas Ruang,</p>
                <p class="font-bold underline">(.................................................)</p>
                <p>NIP. </p>
            </div>
        </div>
    </div>
</body>
</html>
