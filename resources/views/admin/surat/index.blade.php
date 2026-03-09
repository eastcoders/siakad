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