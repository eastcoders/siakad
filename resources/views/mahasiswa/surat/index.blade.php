@extends('layouts.app')

@section('title', 'Riwayat Permohonan Surat')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Layanan /</span> Permohonan Surat
            </h4>
            <a href="{{ route('mahasiswa.surat.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Buat Permohonan Baru
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Riwayat Pengajuan</h5>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Tiket</th>
                            <th>Jenis Surat</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($surats as $surat)
                            <tr>
                                <td><strong>{{ $surat->nomor_tiket }}</strong></td>
                                <td>
                                    @if($surat->tipe_surat == 'aktif_kuliah')
                                        <span class="badge bg-label-info">Aktif Kuliah</span>
                                    @elseif($surat->tipe_surat == 'cuti_kuliah')
                                        <span class="badge bg-label-warning">Cuti Kuliah</span>
                                    @elseif($surat->tipe_surat == 'pindah_kelas')
                                        <span class="badge bg-label-primary">Pindah Kelas</span>
                                    @elseif($surat->tipe_surat == 'pindah_pt')
                                        <span class="badge bg-label-dark"><i class="ri-community-line me-1"></i> Pindah PT</span>
                                    @elseif($surat->tipe_surat == 'pengunduran_diri')
                                        <span class="badge bg-label-danger"><i class="ri-error-warning-line me-1"></i> Pengunduran
                                            Diri</span>
                                    @elseif($surat->tipe_surat == 'izin_pkl')
                                        <span class="badge bg-label-success"><i class="ri-map-pin-line me-1"></i> Izin PKL</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ $surat->tipe_surat }}</span>
                                    @endif
                                </td>
                                <td>{{ $surat->tgl_pengajuan->format('d/m/Y H:i') }}</td>
                                <td>{{ $surat->semester->nama_semester ?? $surat->id_semester }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($surat->status) {
                                            'pending' => 'bg-label-secondary',
                                            'validasi' => 'bg-label-info',
                                            'disetujui' => 'bg-label-primary',
                                            'selesai' => 'bg-label-success',
                                            'ditolak' => 'bg-label-danger',
                                            default => 'bg-label-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ strtoupper($surat->status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('mahasiswa.surat.show', $surat->id) }}"
                                        class="btn btn-sm btn-icon btn-label-primary" title="Detail">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    @if($surat->status == 'selesai' && $surat->file_final)
                                        <a href="{{ asset('storage/' . $surat->file_final) }}"
                                            class="btn btn-sm btn-icon btn-label-success" target="_blank" title="Unduh">
                                            <i class="ri-download-2-line"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat permohonan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection