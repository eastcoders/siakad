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
                {{-- Filter Button --}}
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFilter">
                    <i class="ri-filter-line me-1"></i> Filter
                </button>
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
                                    @if ($item->is_synced)
                                        <span class="badge bg-success rounded-pill"><i class="ri-check-line me-1"></i> Sudah Sync</span>
                                    @else
                                        <span class="badge bg-warning rounded-pill"><i class="ri-time-line me-1"></i> Belum Sync</span>
                                    @endif
                                </td>
                                <td>{{ ($kurikulums->currentPage() - 1) * $kurikulums->perPage() + $loop->iteration }}</td>
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
        <div class="card-footer py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Paling atas: <b>Belum Sync</b>
                </div>
                <div>
                    {{ $kurikulums->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Filter -->
    <div class="modal fade" id="modalFilter" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Kurikulum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.kurikulum.index') }}" method="GET">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Pencarian</label>
                                <input type="text" name="search" class="form-control" placeholder="Nama Kurikulum..." value="{{ request('search') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Status Sinkronisasi</label>
                                <select class="form-select" name="sync_status">
                                    <option value="">-- Semua Status --</option>
                                    <option value="1" {{ $syncStatus === '1' ? 'selected' : '' }}>Sudah Sync</option>
                                    <option value="0" {{ $syncStatus === '0' ? 'selected' : '' }}>Belum Sync</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    </div>
                </form>
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
                scrollX: true,
                paging: false,
                searching: false,
                info: false,
                columnDefs: [
                    { className: "text-center", targets: [0, 1, 2, 6, 7, 8] },
                    { orderable: false, targets: [0] }
                ],
                dom: 't',
            });
        });
    </script>
@endpush