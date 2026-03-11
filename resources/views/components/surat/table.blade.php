@props(['surats', 'role' => 'admin'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endpush

<div class="table-responsive text-nowrap">
    <table class="table table-hover datatables-surat">
        <thead>
            <tr>
                <th>Tgl. Pengajuan</th>
                <th>No. Tiket</th>
                @if($role !== 'mahasiswa')
                    <th>Mahasiswa</th>
                @endif
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
                    
                    @if($role !== 'mahasiswa')
                        <td>
                            <div><strong>{{ $surat->mahasiswa->nama_mahasiswa }}</strong></div>
                            <small class="text-muted">{{ $surat->mahasiswa->nim }}</small>
                        </td>
                    @endif
                    
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
                            // Default admin & mahasiswa styling
                            $statusClass = match ($surat->status) {
                                'pending' => 'bg-label-secondary',
                                'validasi' => 'bg-label-info',
                                'disetujui' => 'bg-label-primary',
                                'selesai' => 'bg-label-success',
                                'ditolak' => 'bg-label-danger',
                                default => 'bg-label-secondary',
                            };
                            $statusLabel = strtoupper($surat->status);

                            // Override Kaprodi specific labels
                            if ($role === 'kaprodi') {
                                $statusConfig = match ($surat->status) {
                                    'pending' => ['class' => 'bg-label-secondary', 'label' => 'MENUNGGU KAPRODI'],
                                    'validasi' => ['class' => 'bg-label-info', 'label' => 'DIVALIDASI (ADMIN)'],
                                    'ditolak' => ['class' => 'bg-label-danger', 'label' => 'DITOLAK'],
                                    'disetujui' => ['class' => 'bg-label-primary', 'label' => 'PROSES ADMIN'],
                                    'selesai' => ['class' => 'bg-label-success', 'label' => 'SELESAI'],
                                    default => ['class' => 'bg-label-secondary', 'label' => strtoupper($surat->status)],
                                };
                                $statusClass = $statusConfig['class'];
                                $statusLabel = $statusConfig['label'];
                            }
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td>
                        @if($role === 'admin')
                            <a href="{{ route('admin.surat-approval.show', $surat->id) }}"
                                class="btn btn-sm btn-icon btn-label-primary" title="Review">
                                <i class="ri-search-line"></i>
                            </a>
                        @elseif($role === 'kaprodi')
                            <a href="{{ route('kaprodi.surat.show', $surat->id) }}"
                                class="btn btn-sm btn-icon btn-label-primary" title="Review">
                                <i class="ri-search-line"></i>
                            </a>
                        @elseif($role === 'mahasiswa')
                            <a href="{{ route('mahasiswa.surat.show', $surat->id) }}"
                                class="btn btn-sm btn-icon btn-label-primary" title="Detail">
                                <i class="ri-eye-line"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function () {
            if ($('.datatables-surat').length) {
                $('.datatables-surat').DataTable({
                    order: [[0, 'desc']],
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    language: {
                        search: "",
                        searchPlaceholder: "Cari permohonan...",
                    }
                });
            }
        });
    </script>
@endpush
