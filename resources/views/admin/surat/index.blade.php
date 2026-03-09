@extends('layouts.app')

@section('title', 'Persetujuan Surat Mahasiswa')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Akademik /</span> Persetujuan Surat
        </h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Permohonan Surat</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover" id="table-surat">
                        <thead>
                            <tr>
                                <th>Tgl. Pengajuan</th>
                                <th>No. Tiket</th>
                                <th>Mahasiswa</th>
                                <th>Jenis Surat</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($surats as $surat)
                                <tr>
                                    <td>{{ $surat->tgl_pengajuan->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge bg-label-primary">{{ $surat->nomor_tiket }}</span></td>
                                    <td>
                                        <div><strong>{{ $surat->mahasiswa->nama_mahasiswa }}</strong></div>
                                        <small class="text-muted">{{ $surat->mahasiswa->nim }}</small>
                                    </td>
                                    <td>
                                        @if($surat->tipe_surat == 'aktif_kuliah')
                                            <span class="badge bg-label-info">Aktif Kuliah</span>
                                        @elseif($surat->tipe_surat == 'cuti_kuliah')
                                            <span class="badge bg-label-warning">Cuti Kuliah</span>
                                        @elseif($surat->tipe_surat == 'pindah_kelas')
                                            <span class="badge bg-label-primary">Pindah Kelas</span>
                                        @elseif($surat->tipe_surat == 'pindah_pt')
                                            <span class="badge bg-label-dark"><i class="ri-community-line me-1"></i> Pindah
                                                PT</span>
                                        @elseif($surat->tipe_surat == 'pengunduran_diri')
                                            <span class="badge bg-label-danger"><i class="ri-error-warning-line me-1"></i>
                                                Pengunduran Diri</span>
                                        @elseif($surat->tipe_surat == 'izin_pkl')
                                            <span class="badge bg-label-success"><i class="ri-map-pin-line me-1"></i>
                                                Izin PKL</span>
                                        @elseif($surat->tipe_surat == 'permintaan_data')
                                            <span class="badge bg-label-warning"><i class="ri-database-2-line me-1"></i>
                                                Permintaan Data</span>
                                        @else
                                            <span class="badge bg-label-secondary">{{ $surat->tipe_surat }}</span>
                                        @endif
                                    </td>
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
                                        <a href="{{ route('admin.surat-approval.show', $surat->id) }}"
                                            class="btn btn-sm btn-icon btn-label-primary" title="Review">
                                            <i class="ri-search-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#table-surat').DataTable({
                order: [[0, 'desc']],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                language: {
                    search: "",
                    searchPlaceholder: "Cari permohonan...",
                }
            });
        });
    </script>
@endpush