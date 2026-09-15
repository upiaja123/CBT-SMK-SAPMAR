<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $exam;
    protected $students;
    protected $index = 0;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
        $this->students = $exam->eligible_students;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->students;
    }

    public function map($student): array
    {
        $this->index++;

        return [
            $this->index,
            $student->user?->username ?? '-',
            $student->user?->name ?? 'User Terhapus',
            $student->schoolClass?->name ?? '-',
            '', // Tanda Tangan
        ];
    }

    public function headings(): array
    {
        return [
            [
                'DAFTAR HADIR PESERTA UJIAN'
            ],
            [
                'Nama Ujian:',
                $this->exam->title,
                '',
                'Mata Pelajaran:',
                $this->exam->subject?->name
            ],
            [
                'Tanggal:',
                $this->exam->start_at ? \Carbon\Carbon::parse($this->exam->start_at)->format('d F Y') : '-',
                '',
                'Waktu:',
                ($this->exam->start_at ? \Carbon\Carbon::parse($this->exam->start_at)->format('H:i') : '?')
                . ' - '
                . ($this->exam->end_at ? \Carbon\Carbon::parse($this->exam->end_at)->format('H:i') : '?')
            ],
            [
                'Durasi:',
                $this->exam->duration . ' Menit',
                '',
                'Ruang:',
                '___________________'
            ],
            [],
            [
                'No',
                'Username',
                'Nama Lengkap',
                'Kelas',
                'Tanda Tangan',
            ]
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Merge title
        $sheet->mergeCells('A1:E1');

        // Header styles
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->getStyle('A2:E4')->getFont()->setBold(true);
        $sheet->getStyle('A6:E6')->getFont()->setBold(true);
        $sheet->getStyle('A6:E6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F4F6');

        // Borders for table
        $lastRow = $this->students->count() + 6;
        $sheet->getStyle('A6:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return [];
    }
}
