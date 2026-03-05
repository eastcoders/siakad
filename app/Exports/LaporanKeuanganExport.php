<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanKeuanganExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $idProdi;
    protected $idKomponen;

    public function __construct($startDate, $endDate, $idProdi = null, $idKomponen = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->idProdi = $idProdi;
        $this->idKomponen = $idKomponen;
    }

    public function query()
    {
        $query = Pembayaran::query()
            ->with(['tagihan.mahasiswa.riwayatPendidikans', 'tagihan.items.komponenBiaya'])
            ->where('status_verifikasi', 'disetujui')
            ->whereBetween('tanggal_bayar', [$this->startDate, $this->endDate]);

        if ($this->idProdi) {
            $query->whereHas('tagihan.mahasiswa.riwayatPendidikans', function ($q) {
                $q->where('id_prodi', $this->idProdi);
            });
        }

        if ($this->idKomponen) {
            $query->whereHas('tagihan.items', function ($q) {
                $q->where('komponen_biaya_id', $this->idKomponen);
            });
        }

        return $query->orderBy('tanggal_bayar', 'asc');
    }

    public function headings(): array
    {
        return [
            'No. Kuitansi',
            'Tanggal Bayar',
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Nama Komponen Biaya',
            'Total Tagihan',
            'Total Potongan',
            'Jumlah Bayar',
            'Keterangan / Status',
        ];
    }

    public function map($pembayaran): array
    {
        $tagihan = $pembayaran->tagihan;
        $mahasiswa = $tagihan->mahasiswa;

        $prodi = $mahasiswa->riwayatPendidikans->first() ? $mahasiswa->riwayatPendidikans->first()->nama_program_studi : '-';

        // Gabungkan nama komponen biaya
        $komponenNames = $tagihan->items->map(function ($item) {
            return $item->komponenBiaya->nama_komponen;
        })->implode(', ');

        return [
            $pembayaran->nomor_kuitansi,
            $pembayaran->tanggal_bayar->format('Y-m-d'),
            $mahasiswa->nim,
            $mahasiswa->nama,
            $prodi,
            $komponenNames,
            $tagihan->total_tagihan,
            $tagihan->total_potongan,
            $pembayaran->jumlah_bayar,
            'Lunas/Disetujui'
        ];
    }
}
