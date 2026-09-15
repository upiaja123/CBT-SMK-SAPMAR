<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Ujian - {{ $exam->title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .logo { width: 80px; position: absolute; left: 20px; top: 10px; }
        .school-name { font-size: 18px; font-weight: bold; margin: 0; }
        .school-address { font-size: 12px; margin: 5px 0 0 0; }
        .title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .info-table { width: 50%; border: none; margin-bottom: 20px; }
        .info-table td { border: none; padding: 2px; }
        .text-success { color: green; font-weight: bold; }
        .text-danger { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
        @elseif(file_exists(public_path('images/logo.jpg')))
            <img src="{{ public_path('images/logo.jpg') }}" class="logo" alt="Logo">
        @endif
        <p class="school-name">SMK SAPTA MARGA</p>
        <p class="school-address">Platform Ujian Berbasis Komputer (CBT)</p>
    </div>

    <div class="title">LAPORAN HASIL UJIAN</div>

    <table class="info-table">
        <tr>
            <td width="100"><strong>Nama Ujian</strong></td>
            <td width="10">:</td>
            <td>{{ $exam->title }}</td>
        </tr>
        <tr>
            <td><strong>Mata Pelajaran</strong></td>
            <td>:</td>
            <td>{{ $exam->subject->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal Ujian</strong></td>
            <td>:</td>
            <td>{{ $exam->start_at ? $exam->start_at->format('d M Y H:i') : '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>NIS/NISN</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Status Pengumpulan</th>
                <th>Nilai</th>
                <th>Kelulusan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attempts as $index => $attempt)
                @php
                    $percentage = null;
                    if ($attempt->max_total_score > 0) {
                        $percentage = ($attempt->total_score / $attempt->max_total_score) * 100;
                    }
                    $passingStatus = 'N/A';
                    if ($percentage !== null) {
                        $passingStatus = $percentage >= 60 ? 'LULUS' : 'TIDAK LULUS';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $attempt->student->nis ?? $attempt->student->nisn ?? '-' }}</td>
                    <td>{{ $attempt->student->user->name ?? '-' }}</td>
                    <td>{{ $attempt->student->schoolClass->name ?? '-' }}</td>
                    <td>{{ $attempt->status }}</td>
                    <td class="text-center">{{ $attempt->total_score }} / {{ $attempt->max_total_score }}</td>
                    <td class="text-center">
                        @if($passingStatus === 'LULUS')
                            <span class="text-success">{{ $passingStatus }}</span>
                        @else
                            <span class="text-danger">{{ $passingStatus }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data hasil ujian yang final.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
