@extends('layouts.app')

@section('title', 'Detail Permohonan Surat')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Layanan / Permohonan Surat /</span> Detail
        </h4>

        <div class="row">
            <!-- Request Status & Core Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Status Pengajuan</h5>
                        <span class="badge bg-label-primary">{{ $surat->nomor_tiket }}</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center mb-4">
                            @php
                                $statusConfig = match ($surat->status) {
                                    'pending' => ['color' => 'secondary', 'icon' => 'ri-time-line'],
                                    'validasi' => ['color' => 'info', 'icon' => 'ri-check-double-line'],
                                    'disetujui' => ['color' => 'primary', 'icon' => 'ri-thumb-up-line'],
                                    'selesai' => ['color' => 'success', 'icon' => 'ri-checkbox-circle-line'],
                                    'ditolak' => ['color' => 'danger', 'icon' => 'ri-error-warning-line'],
                                    default => ['color' => 'secondary', 'icon' => 'ri-question-line'],
                                };
                            @endphp
                            <div class="avatar avatar-xl mb-3">
                                <span class="avatar-initial rounded bg-label-{{ $statusConfig['color'] }}">
                                    <i class="{{ $statusConfig['icon'] }} ri-48px"></i>
                                </span>
                            </div>
                            <h4 class="mb-1 text-{{ $statusConfig['color'] }}">{{ strtoupper($surat->status) }}</h4>
                            <p class="text-muted small">Update Terakhir: {{ $surat->updated_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <span class="fw-bold">Jenis Surat:</span>
                                <span class="float-end">
                                    @php
                                        $typeConfig = match ($surat->tipe_surat) {
                                            'aktif_kuliah' => ['color' => 'info', 'icon' => 'ri-file-user-line', 'label' => 'Aktif Kuliah'],
                                            'cuti_kuliah' => ['color' => 'warning', 'icon' => 'ri-calendar-close-line', 'label' => 'Cuti Kuliah'],
                                            'pindah_kelas' => ['color' => 'primary', 'icon' => 'ri-arrow-left-right-line', 'label' => 'Pindah Kelas'],
                                            'pindah_pt' => ['color' => 'dark', 'icon' => 'ri-community-line', 'label' => 'Pindah PT'],
                                            'pengunduran_diri' => ['color' => 'danger', 'icon' => 'ri-error-warning-line', 'label' => 'Pengunduran Diri'],
                                            'izin_pkl' => ['color' => 'success', 'icon' => 'ri-map-pin-line', 'label' => 'Izin PKL'],
                                            'permintaan_data' => ['color' => 'secondary', 'icon' => 'ri-database-2-line', 'label' => 'Permintaan Data'],
                                            default => ['color' => 'secondary', 'icon' => 'ri-file-line', 'label' => $surat->tipe_surat],
                                        };
                                    @endphp
                                    <span class="badge bg-label-{{ $typeConfig['color'] }}">
                                        <i class="{{ $typeConfig['icon'] }} me-1"></i> {{ $typeConfig['label'] }}
                                    </span>
                                </span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-bold">Semester:</span>
                                <span class="float-end">{{ $surat->semester->nama_semester ?? $surat->id_semester }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-bold">Nomor Surat:</span>
                                <span class="float-end text-primary fw-bold">{{ $surat->nomor_surat ?? '-' }}</span>
                            </li>
                        </ul>

                        @if($surat->status == 'selesai' && $surat->file_final)
                            <hr>
                            <a href="{{ route('mahasiswa.surat.download', $surat->id) }}" target="_blank"
                                class="btn btn-success w-100">
                                <i class="ri-download-2-line me-1"></i> Unduh Surat Final
                            </a>
                        @endif
                    </div>
                </div>

                @if($surat->catatan_admin)
                    <div class="card border-danger mb-4">
                        <div class="card-header bg-label-danger">
                            <h5 class="mb-0 text-danger"><i class="ri-feedback-line me-1"></i> Catatan Admin</h5>
                        </div>
                        <div class="card-body pt-3">
                            <p class="mb-0">{{ $surat->catatan_admin }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Request Details -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <h5 class="card-header">Rincian Data Pengajuan</h5>
                    <div class="card-body">
                        @if($surat->tipe_surat == 'aktif_kuliah')
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Data Orang Tua / Wali</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Nama Lengkap:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('nama_ortu') }}</div>

                                <div class="col-sm-4 fw-bold">Pekerjaan:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('pekerjaan_ortu') }}</div>

                                <div class="col-sm-4 fw-bold">Alamat:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('alamat_ortu') }}</div>

                                @if($surat->getMeta('nip_ortu'))
                                    <div class="col-sm-4 fw-bold">NIP/NRP:</div>
                                    <div class="col-sm-8 mb-2">{{ $surat->getMeta('nip_ortu') }}</div>
                                @endif

                                @if($surat->getMeta('jabatan_ortu'))
                                    <div class="col-sm-4 fw-bold">Jabatan:</div>
                                    <div class="col-sm-8 mb-2">{{ $surat->getMeta('jabatan_ortu') }}</div>
                                @endif

                                @if($surat->getMeta('instansi_ortu'))
                                    <div class="col-sm-4 fw-bold">Instansi:</div>
                                    <div class="col-sm-8 mb-2">{{ $surat->getMeta('instansi_ortu') }}</div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Informasi Tambahan</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Keperluan Surat:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('keperluan') }}</div>
                            </div>
                        @elseif($surat->tipe_surat == 'cuti_kuliah')
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Informasi Cuti Kuliah</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Alasan Cuti:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->alasan }}</div>
                            </div>
                        @elseif($surat->tipe_surat == 'pindah_kelas')
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Detail Pindah Jenis Kelas</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Tipe Kelas Asal:</div>
                                <div class="col-sm-8 mb-2"><span
                                        class="badge bg-label-secondary">{{ $surat->getMeta('kelas_asal') }}</span></div>

                                <div class="col-sm-4 fw-bold">Tipe Kelas Tujuan:</div>
                                <div class="col-sm-8 mb-2"><span
                                        class="badge bg-label-primary">{{ $surat->getMeta('kelas_tujuan') }}</span></div>
                            </div>
                        @elseif($surat->tipe_surat == 'pindah_pt')
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Detail Pindah Perguruan Tinggi</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Perguruan Tinggi Asal:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('pt_asal') }}</div>

                                <div class="col-sm-4 fw-bold">Perguruan Tinggi Tujuan:</div>
                                <div class="col-sm-8 mb-2 text-primary fw-bold">{{ $surat->getMeta('pt_tujuan_nama') }}</div>

                                <div class="col-sm-4 fw-bold">Akreditasi PT Tujuan:</div>
                                <div class="col-sm-8 mb-2"><span
                                        class="badge bg-label-info">{{ $surat->getMeta('akreditasi_pt_tujuan') }}</span></div>
                            </div>
                        @elseif($surat->tipe_surat == 'pengunduran_diri')
                            <div class="row">
                                <div class="col-12 text-center py-3">
                                    <div class="avatar avatar-xl mb-3 mx-auto">
                                        <span class="avatar-initial rounded bg-label-danger">
                                            <i class="ri-error-warning-line ri-48px"></i>
                                        </span>
                                    </div>
                                    <h5 class="text-danger fw-bold">Permohonan Pengunduran Diri</h5>
                                    <p class="text-muted mx-auto" style="max-width: 500px;">Mahasiswa telah menyatakan
                                        pengunduran diri secara resmi dari institusi melalui permohonan ini.</p>
                                </div>
                            </div>
                        @elseif($surat->tipe_surat == 'permintaan_data')
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2">Detail Permintaan Data
                                        ({{ $surat->getMeta('peruntukan') }})</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Peruntukan:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('peruntukan') }}</div>

                                <div class="col-sm-4 fw-bold">Nama Instansi:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->instansi_tujuan }}</div>

                                <div class="col-sm-4 fw-bold">Alamat Instansi:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->alamat_instansi }}</div>

                                <hr class="my-3">

                                <div class="col-sm-4 fw-bold">Judul Laporan/TA:</div>
                                <div class="col-sm-8 mb-2 fw-bold">{{ $surat->getMeta('judul_laporan') }}</div>

                                <div class="col-sm-4 fw-bold">Data yang Dibutuhkan:</div>
                                <div class="col-sm-8 mb-2">
                                    <div class="bg-light p-3 rounded border">
                                        {!! nl2br(e($surat->getMeta('data_dibutuhkan'))) !!}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('mahasiswa.surat.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection