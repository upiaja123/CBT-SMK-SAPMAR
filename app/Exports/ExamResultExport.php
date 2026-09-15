<?php

namespace App\Exports;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamResultExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $exam;
    protected $rowNumber = 0;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    public function query(): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
    {
        // Only include finalized academic results
        return ExamAttempt::query()
            ->with(['student.user', 'student.schoolClass'])
            ->where('exam_id', $this->exam->id)
            ->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED'])
            ->where('grading_status', 'FINAL')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS/NISN',
            'Nama Siswa',
            'Kelas',
            'Ujian',
            'Status Pengumpulan',
            'Status Penilaian',
            'Total Nilai',
            'Nilai Maksimum',
            'Persentase',
            'Status Kelulusan',
        ];
    }

    public function map($attempt): array
    {
        $this->rowNumber++;

        $percentage = null;
        if ($attempt->max_total_score > 0) {
            $percentage = ($attempt->total_score / $attempt->max_total_score) * 100;
        }

        $passingStatus = 'N/A';
        if ($percentage !== null) {
            $passingStatus = $percentage >= 60 ? 'LULUS' : 'TIDAK LULUS';
        }

        $row = [
            $this->rowNumber,
            $attempt->student->nis ?? $attempt->student->nisn ?? '-',
            $attempt->student->user->name ?? '-',
            $attempt->student->schoolClass->name ?? '-',
            $this->exam->title,
            $attempt->status,
            $attempt->grading_status,
            $attempt->total_score,
            $attempt->max_total_score,
            $percentage !== null ? round($percentage, 2) . '%' : 'N/A',
            $passingStatus,
        ];

        return array_map(function ($value) {
            if (is_string($value) && preg_match('/^[\=\+\-\@]/', $value)) {
                return "'" . $value;
            }
            return $value;
        }, $row);
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
