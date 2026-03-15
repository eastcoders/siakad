@extends('layouts.app')

@section('title', 'Manajemen Mata Kuliah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
@endpush

@section('content')
    {{-- Page Header --}}
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Perkuliahan /</span> Mata Kuliah</h4>

    <div class="card">
        <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h5 class="card-title mb-0">Daftar Mata Kuliah</h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <button type="button" class="btn btn-outline-primary waves-effect waves-light" data-bs-toggle="modal"
                    data-bs-target="#modalFilter">
                    <i class="ri-filter-line me-1"></i> Filter
                </button>
                <a href="{{ route('admin.mata-kuliah.create') }}" class="btn btn-primary waves-effect waves-light">
                    <i class="ri-add-line me-1"></i> Tambah
                </a>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table id="mataKuliahTable" class="table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th>Action</th>
                        <th>Status</th>
                        <th>No</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Bobot (sks)</th>
                        <th>Program Studi</th>
                        <th>Jenis Mata Kuliah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mataKuliah as $index => $item)
                        <tr>
                            <td>
                                <div class="d-flex gap-1">
                                    {{-- View Button (Available for all) --}}
                                    {{-- View Button (Available for all) --}}
                                    <a href="{{ route('admin.mata-kuliah.show', $item->id) }}"
                                        class="btn btn-icon btn-sm btn-info rounded-pill" title="Detail">
                                        <i class="ri-eye-line"></i>
                                    </a>

                                    {{-- Edit & Delete (Available for All) --}}
                                    <a href="{{ route('admin.mata-kuliah.edit', $item->id) }}"
                                        class="btn btn-icon btn-sm btn-warning rounded-pill" title="Edit">
                                        <i class="ri-pencil-line"></i>
                                    </a>

                                    <form action="{{ route('admin.mata-kuliah.destroy', $item->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-danger rounded-pill" title="Hapus"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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
                            <td>{{ ($mataKuliah->currentPage() - 1) * $mataKuliah->perPage() + $loop->iteration }}</td>
                            <td><span class="fw-semibold text-primary">{{ $item->kode_mk }}</span></td>
                            <td>{{ $item->nama_mk }}</td>
                            <td>{{ $item->sks }}</td>
                            <td>{{ $item->prodi ? $item->prodi->nama_program_studi : '-' }}</td>
                            <td>{{ $item->jenis_mk }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer py-2">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Paling atas: <b>Belum Sync</b>
                </div>
                <div>
                    {{ $mataKuliah->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Filter -->
    <div class="modal fade" id="modalFilter" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Mata Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.mata-kuliah.index') }}" method="GET">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Pencarian</label>
                                <input type="text" name="search" class="form-control" placeholder="Nama atau Kode MK..." value="{{ request('search') }}">
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
        $(function () {
            // DataTables init
            $('#mataKuliahTable').DataTable({
                responsive: false,
                scrollX: true,
                paging: false,
                searching: false,
                info: false,
                columnDefs: [{
                    targets: 0,
                    orderable: false,
                }],
                language: {
                    emptyTable: 'Tidak ada data mata kuliah.',
                },
                dom: 't',
            });

            // Handle Add Button
            $('#btnAddMataKuliah').on('click', function () {
                $('#mataKuliahModal').modal('show');
            });

            // Temp logic for demo
            $('.btn-edit, .btn-view').on('click', function () {
                console.log($(this).data('matkul'));
                // Only log for now as modal body isn't fully implemented
            });
        });
    </script>
@endpush