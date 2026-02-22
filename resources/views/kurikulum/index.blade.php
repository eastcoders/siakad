@extends('layouts.app')

@section('title', 'Manajemen Kurikulum')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
@endpush

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Perkuliahan /</span> Kurikulum
    </h4>

    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Kurikulum</h5>
            <div class="d-flex gap-2">
                {{-- Sync Button --}}
                <form action="{{ route('admin.kurikulum.sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="ri-refresh-line me-1"></i> Sync Data
                    </button>
                </form>
                {{-- Add Button --}}
                <a href="{{ route('admin.kurikulum.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i> Tambah
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered table-striped table-hover" id="table-kurikulum">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">Aksi</th>
                            <th width="10%">Status</th>
                            <th width="5%">No</th>
                            <th>Nama Kurikulum</th>
                            <th>Program Studi</th>
                            <th>Mulai Berlaku</th>
                            <th class="text-center">SKS Lulus</th>
                            <th class="text-center">SKS Wajib</th>
                            <th class="text-center">SKS Pilihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kurikulums as $item)
                            <tr>
                                <td>
                                    <div class="d-flex gap-1">
                                        {{-- View Button --}}
                                        <a href="{{ route('admin.kurikulum.show', $item->id) }}"
                                            class="btn btn-icon btn-sm btn-info rounded-pill" title="Detail">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        {{-- Edit Button --}}
                                        <a href="{{ route('admin.kurikulum.edit', $item->id) }}"
                                            class="btn btn-icon btn-sm btn-warning rounded-pill" title="Edit">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        {{-- Delete Button --}}
                                        <form action="{{ route('admin.kurikulum.destroy', $item->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-danger rounded-pill"
                                                title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'bg-label-secondary';
                                        $statusText = 'Unknown';

                                        if ($item->is_deleted_server) {
                                            $statusClass = 'bg-label-danger';
                                            $statusText = 'Dihapus Server';
                                        } else {
                                            if ($item->sumber_data === 'server' && $item->status_sinkronisasi === 'synced') {
                                                $statusClass = 'bg-label-success';
                                                $statusText = 'Server (Synced)';
                                            } elseif ($item->sumber_data === 'lokal' && $item->status_sinkronisasi === 'created_local') {
                                                $statusClass = 'bg-label-warning';
                                                $statusText = 'Lokal (Belum Push)';
                                            } elseif ($item->sumber_data === 'server' && $item->status_sinkronisasi === 'updated_local') {
                                                $statusClass = 'bg-label-info';
                                                $statusText = 'Server (Update Lokal)';
                                            } elseif ($item->status_sinkronisasi === 'push_failed') {
                                                $statusClass = 'bg-label-danger';
                                                $statusText = 'Gagal Push';
                                            } else {
                                                switch ($item->status_sinkronisasi) {
                                                    case 'synced':
                                                        $statusClass = 'bg-label-success';
                                                        $statusText = 'Sudah Sync';
                                                        break;
                                                    case 'created_local':
                                                        $statusClass = 'bg-label-info';
                                                        $statusText = 'Lokal';
                                                        break;
                                                    case 'updated_local':
                                                        $statusClass = 'bg-label-warning';
                                                        $statusText = 'Update Lokal';
                                                        break;
                                                    case 'pending_push':
                                                        $statusClass = 'bg-label-secondary';
                                                        $statusText = 'Pending Push';
                                                        break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill">{{ $statusText }}</span>
                                </td>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $item->nama_kurikulum }}</td>
                                <td>{{ $item->prodi->nama_program_studi ?? '-' }}</td>
                                <td>{{ $item->semester->nama_semester ?? $item->id_semester }}</td>
                                <td class="text-center">{{ $item->jumlah_sks_lulus }}</td>
                                <td class="text-center">{{ $item->jumlah_sks_wajib }}</td>
                                <td class="text-center">{{ $item->jumlah_sks_pilihan }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#table-kurikulum').DataTable({
                responsive: false,
                scrollX: false,
                language: {
                    search: '',
                    searchPlaceholder: 'Cari Kurikulum...',
                    lengthMenu: '_MENU_',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Tidak ada data kurikulum.',
                    paginate: {
                        first: '«',
                        last: '»',
                        next: '›',
                        previous: '‹'
                    }
                },
                columnDefs: [
                    { className: "text-center", targets: [0, 1, 2, 6, 7, 8] },
                    { orderable: false, targets: [0] }
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            });
        });
    </script>
@endpush