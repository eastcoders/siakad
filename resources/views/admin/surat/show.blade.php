@extends('layouts.app')

@section('title', 'Detail Permohonan Surat')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Akademik / Persetujuan Surat /</span> Detail
            </h4>
            <a href="{{ route('admin.surat-approval.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Student Info & Action Card -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Informasi Mahasiswa</h5>
                        <span class="badge bg-label-primary">{{ $surat->nomor_tiket }}</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center mb-4">
                            <div class="avatar avatar-xl mb-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="ri-user-3-line ri-48px"></i>
                                </span>
                            </div>
                            <h4 class="mb-1">{{ $surat->mahasiswa->nama_mahasiswa }}</h4>
                            <p class="text-muted mb-0">{{ $surat->mahasiswa->nim }}</p>
                            <p class="small text-muted mb-0">{{ $surat->mahasiswa->prodi->nama_prodi ?? '-' }}</p>
                        </div>

                        <div class="info-container">
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <span class="fw-bold">Status:</span>
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
                                    <span class="badge {{ $statusClass }} float-end">{{ strtoupper($surat->status) }}</span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold">Jenis Surat:</span>
                                    <span class="float-end text-primary">
                                        @if($surat->tipe_surat == 'aktif_kuliah')
                                            Aktif Kuliah
                                        @elseif($surat->tipe_surat == 'pindah_kelas')
                                            Pindah Kelas
                                        @elseif($surat->tipe_surat == 'pindah_pt')
                                            Pindah PT
                                        @elseif($surat->tipe_surat == 'pengunduran_diri')
                                            Pengunduran Diri
                                        @elseif($surat->tipe_surat == 'izin_pkl')
                                            Izin PKL
                                        @elseif($surat->tipe_surat == 'permintaan_data')
                                            Permintaan Data
                                        @else
                                            Cuti Kuliah
                                        @endif
                                    </span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold">Semester:</span>
                                    <span
                                        class="float-end">{{ $surat->semester->nama_semester ?? $surat->id_semester }}</span>
                                </li>
                                <li class="mb-3">
                                    <span class="fw-bold">Waktu Pengajuan:</span>
                                    <span class="float-end">{{ $surat->tgl_pengajuan->format('d/m/Y H:i') }}</span>
                                </li>
                            </ul>
                        </div>

                        <hr>

                        <!-- Status Update Actions -->
                        @if(in_array($surat->status, ['pending', 'validasi']))
                            <div class="mt-4">
                                <h6 class="mb-3">Update Status</h6>
                                <div class="d-flex flex-column gap-2">
                                    @if($surat->status == 'pending')
                                        <form action="{{ route('admin.surat-approval.update-status', $surat->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="validasi">
                                            <button type="submit" class="btn btn-info w-100">
                                                <i class="ri-check-double-line me-1"></i> Validasi Berkas
                                            </button>
                                        </form>
                                    @endif

                                    @if($surat->status == 'validasi')
                                        <form action="{{ route('admin.surat-approval.update-status', $surat->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="disetujui">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ri-thumb-up-line me-1"></i> Setujui Permohonan
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                        <i class="ri-error-warning-line me-1"></i> Tolak Permohonan
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($surat->status == 'disetujui')
                            <div class="mt-4">
                                <button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
                                    data-bs-target="#finalizeModal">
                                    <i class="ri-upload-cloud-2-line me-1"></i> Finalisasi & Upload Surat
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Request Details -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <h5 class="card-header">Rincian Data Pengajuan</h5>
                    <div class="card-body">
                        @if($surat->tipe_surat == 'aktif_kuliah')
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2"><i class="ri-user-heart-line me-1"></i> Data
                                        Orang Tua / Wali</h6>
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

                                @if($surat->getMeta('alamat_instansi_ortu'))
                                    <div class="col-sm-4 fw-bold">Alamat Instansi:</div>
                                    <div class="col-sm-8 mb-2">{{ $surat->getMeta('alamat_instansi_ortu') }}</div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2"><i class="ri-information-line me-1"></i>
                                        Informasi Tambahan</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Keperluan Surat:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('keperluan') }}</div>
                            </div>
                        @elseif($surat->tipe_surat == 'cuti_kuliah')
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2"><i class="ri-calendar-event-line me-1"></i>
                                        Informasi Cuti Kuliah</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Alasan Cuti:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->alasan }}</div>
                            </div>
                        @elseif($surat->tipe_surat == 'pindah_kelas')
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2"><i class="ri-arrow-left-right-line me-1"></i>
                                        Detail Pindah Jenis Kelas</h6>
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
                                    <h6 class="text-primary border-bottom pb-2"><i class="ri-community-line me-1"></i> Detail
                                        Pindah Perguruan Tinggi</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Perguruan Tinggi Asal:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('pt_asal') }}</div>

                                <div class="col-sm-4 fw-bold">Perguruan Tinggi Tujuan:</div>
                                <div class="col-sm-8 mb-2 text-primary fw-bold">{{ $surat->getMeta('pt_tujuan_nama') }}</div>

                                <div class="col-sm-4 fw-bold">Akreditasi PT Tujuan:</div>
                                <div class="col-sm-8 mb-2"><span
                                        class="badge bg-label-info">{{ $surat->getMeta('akreditasi_pt_tujuan') }}</span></div>
                            </div>
                        @elseif($surat->tipe_surat == 'izin_pkl')
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2"><i class="ri-map-pin-line me-1"></i> Detail
                                        Lokasi & Penempatan PKL</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Nama Instansi:</div>
                                <div class="col-sm-8 mb-2 text-primary fw-bold">{{ $surat->instansi_tujuan }}</div>

                                <div class="col-sm-4 fw-bold">Pimpinan:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('pkl_pimpinan') }}</div>

                                <div class="col-sm-4 fw-bold">Alamat:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->alamat_instansi }}</div>

                                <div class="col-sm-4 fw-bold">Periode:</div>
                                <div class="col-sm-8 mb-2">
                                    {{ $surat->tgl_mulai ? $surat->tgl_mulai->format('d M Y') : '-' }} -
                                    {{ $surat->tgl_selesai ? $surat->tgl_selesai->format('d M Y') : '-' }}
                                </div>

                                @if($surat->anggotas->count() > 0)
                                    <div class="col-12 mt-3">
                                        <h6 class="text-primary border-bottom pb-2">Teman / Partner Kelompok</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Nama Mahasiswa</th>
                                                        <th>NIM</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($surat->anggotas as $anggota)
                                                        <tr>
                                                            <td>{{ $anggota->mahasiswa->nama_mahasiswa }}</td>
                                                            <td>{{ $anggota->mahasiswa->nim }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif($surat->tipe_surat == 'permintaan_data')
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary border-bottom pb-2"><i class="ri-database-2-line me-1"></i> Detail
                                        Permintaan Data ({{ $surat->getMeta('peruntukan') }})</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Peruntukan:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->getMeta('peruntukan') }}</div>

                                <div class="col-sm-4 fw-bold">Nama Instansi:</div>
                                <div class="col-sm-8 mb-2 text-primary fw-bold">{{ $surat->instansi_tujuan }}</div>

                                <div class="col-sm-4 fw-bold">Alamat Instansi:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->alamat_instansi }}</div>

                                <hr class="my-3">

                                <div class="col-sm-4 fw-bold">Judul Laporan/TA:</div>
                                <div class="col-sm-8 mb-2 fw-bold text-dark">{{ $surat->getMeta('judul_laporan') }}</div>

                                <div class="col-sm-4 fw-bold">Data yang Dibutuhkan:</div>
                                <div class="col-sm-8 mb-2">
                                    <div class="bg-light p-3 rounded border">
                                        {!! nl2br(e($surat->getMeta('data_dibutuhkan'))) !!}
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($surat->status == 'selesai')
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-success border-bottom pb-2"><i class="ri-file-check-line me-1"></i> Berkas
                                        Final</h6>
                                </div>
                                <div class="col-sm-4 fw-bold">Nomor Surat:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->nomor_surat }}</div>
                                <div class="col-sm-4 fw-bold">Tanggal Selesai:</div>
                                <div class="col-sm-8 mb-2">{{ $surat->tgl_selesai->format('d/m/Y') }}</div>
                                <div class="col-sm-12 mt-2">
                                    <a href="{{ route('admin.surat-approval.download', $surat->id) }}" target="_blank"
                                        class="btn btn-outline-success">
                                        <i class="ri-download-line me-1"></i> Unduh/Lihat Surat
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($surat->catatan_admin)
                            <hr class="my-4">
                            <div class="alert alert-warning mb-0">
                                <strong><i class="ri-feedback-line me-1"></i> Catatan Admin:</strong><br>
                                {{ $surat->catatan_admin }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" action="{{ route('admin.surat-approval.update-status', $surat->id) }}"
                method="POST">
                @csrf
                <input type="hidden" name="status" value="ditolak">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Permohonan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="catatan_admin" class="form-label">Alasan Penolakan</label>
                            <textarea class="form-control" id="catatan_admin" name="catatan_admin" rows="3" required
                                placeholder="Jelaskan mengapa permohonan ditolak (contoh: berkas tidak lengkap)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Finalize Modal -->
    <div class="modal fade" id="finalizeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" action="{{ route('admin.surat-approval.finalize', $surat->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Finalisasi & Upload Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col mb-3">
                            <label for="nomor_surat" class="form-label">Nomor Surat Resmi</label>
                            <input type="text" id="nomor_surat" name="nomor_surat" class="form-control"
                                placeholder="Contoh: 123/POLSA/AK/2026" required>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label for="file_final" class="form-label">File Surat (PDF)</label>
                            <input type="file" id="file_final" name="file_final" class="form-control"
                                accept="application/pdf" required>
                            <small class="text-muted">Maksimal 2MB, format PDF.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan & Selesaikan</button>
                </div>
            </form>
        </div>
    </div>
@endsection