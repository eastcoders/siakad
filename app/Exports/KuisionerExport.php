<?php

namespace App\Exports;

use App\Models\Kuisioner;
use App\Models\KuisionerSubmission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KuisionerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $kuisioner;
    protected $pertanyaans;
    protected $rowNumber = 0;

    public function __construct(Kuisioner $kuisioner)
    {
        $this->kuisioner = $kuisioner;
        $this->pertanyaans = $kuisioner->pertanyaans()->orderBy('urutan', 'asc')->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return KuisionerSubmission::with(['dosen', 'jawabanDetails'])
            ->where('id_kuisioner', $this->kuisioner->id)
            ->latest()
            ->get();
    }

    /**
     * @var KuisionerSubmission $submission
     */
    public function map($submission): array
    {
        $this->rowNumber++;

        $results = [
            $this->rowNumber,
            $submission->created_at->format('d/m/Y H:i'),
        ];

        if ($this->kuisioner->tipe === 'dosen') {
            $results[] = $submission->dosen->nama ?? '-';
            $results[] = $submission->dosen->nidn ?? '-';
        }

        foreach ($this->pertanyaans as $p) {
            $detail = $submission->jawabanDetails->where('id_pertanyaan', $p->id)->first();
            if ($detail) {
                if ($p->tipe_input === 'likert') {
                    $results[] = $detail->jawaban_skala;
                } else {
                    $results[] = $detail->jawaban_teks;
                }
            } else {
                $results[] = '';
            }
        }

        return $results;
    }

    public function headings(): array
    {
        $headers = [
            'No',
            'Tanggal Submit',
        ];

        if ($this->kuisioner->tipe === 'dosen') {
            $headers[] = 'Nama Dosen';
            $headers[] = 'NIDN';
        }

        foreach ($this->pertanyaans as $p) {
            $headers[] = $p->teks_pertanyaan;
        }

        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
