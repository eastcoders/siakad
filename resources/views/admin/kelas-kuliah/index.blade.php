@extends('layouts.app')

@section('title', 'Manajemen Kelas Kuliah')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    {{-- Page Header --}}
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Perkuliahan /</span> Kelas Kuliah</h4>

    <div class="card">
        <div class="card-datatable table-responsive">
            <table id="kelasKuliahTable" class="table table-bordered table-hover text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th width="100">Action</th>
                        <th>Status</th>
                        <th width="50">No</th>
                        <th>Semester</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Nama Kelas</th>
                        <th>Bobot (sks)</th>
                        <th>Dosen Pengajar</th>
                        <th>Peserta</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data loaded via Ajax --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Filter --}}
    <div class="modal fade" id="modalFilter" tabindex="-1" aria-labelledby="modalFilterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFilterTitle">Filter Data Kelas Kuliah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Semester</label>
                            <select id="filter_semester" class="form-select select2-modal"
                                data-placeholder="-- Semua Semester --">
                                <option value="">-- Semua Semester --</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id_semester }}"
                                        {{ isset($activeSemester) && $semester->id_semester == $activeSemester->id_semester ? 'selected' : '' }}>
                                        {{ $semester->nama_semester }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Program Studi</label>
                            <select id="filter_prodi" class="form-select select2-modal"
                                data-placeholder="-- Semua Prodi --">
                                <option value="">-- Semua Prodi --</option>
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id_prodi }}">
                                        {{ $prodi->nama_program_studi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status Sinkronisasi</label>
                            <select id="filter_status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="synced">Sudah Sync</option>
                                <option value="pending_push">Pending Push</option>
                                <option value="created_local">Lokal (Belum Sync)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-reset-filter">Reset Filter</button>
                    <div>
                        <button type="button" class="btn btn-outline-secondary me-2"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary btn-apply-filter">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>

    <script>
        $(function () {
            // Select2 init di dalam modal (penting: dropdownParent harus modal)
            $('.select2-modal').select2({
                dropdownParent: $('#modalFilter')
            });

            // DataTables init
            let table = $('#kelasKuliahTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.kelas-kuliah.index') }}",
                    data: function (d) {
                        d.id_semester = $('#filter_semester').val();
                        d.id_prodi = $('#filter_prodi').val();
                        d.status_sinkronisasi = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'semester_nama', name: 'semester.nama_semester' },
                    { data: 'kode_mk', name: 'mataKuliah.kode_mk' },
                    { data: 'nama_mk', name: 'mataKuliah.nama_mk' },
                    { data: 'nama_kelas_kuliah', name: 'nama_kelas_kuliah' },
                    { data: 'bobot_sks', name: 'sks_mk' },
                    { data: 'dosen_pengajar', name: 'dosen_pengajar', orderable: false, searchable: false },
                    { data: 'peserta_kelas', name: 'peserta_kelas', orderable: false, searchable: false },
                ],
                order: [[3, 'desc'], [5, 'asc']],
                responsive: false,
                scrollX: true,
                pageLength: 10,
                language: {
                    search: '',
                    searchPlaceholder: 'Cari Kelas / MK...',
                    lengthMenu: '_MENU_',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    emptyTable: 'Tidak ada data kelas kuliah.',
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    paginate: {
                        first: '«',
                        last: '»',
                        next: '›',
                        previous: '‹'
                    }
                },
                buttons: [
                    {
                        text: '<i class="ri-filter-3-line me-sm-1"></i> <span class="d-none d-sm-inline-block">Filter</span>',
                        className: 'btn btn-outline-secondary waves-effect waves-light me-2',
                        action: function () {
                            $('#modalFilter').modal('show');
                        }
                    },
                    {
                        text: '<i class="ri-add-line ri-16px me-sm-1"></i> <span class="d-none d-sm-inline-block">Tambah Kelas</span>',
                        className: 'create-new btn btn-primary waves-effect waves-light',
                        action: function () {
                            window.location.href = "{{ route('admin.kelas-kuliah.create') }}";
                        }
                    }
                ],
                dom: '<"card-header flex-column flex-md-row border-bottom pb-3"<"head-label text-center"><"dt-action-buttons text-end pt-3 pt-md-0"B>>' +
                     '<"row px-3 py-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>' +
                     't' +
                     '<"row px-3 py-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                initComplete: function () {
                    // Build filter badge
                    let semesterText = $('#filter_semester option:selected').text().trim();
                    let filterBadge = '';
                    if ($('#filter_semester').val()) {
                        filterBadge = '<span class="badge bg-primary ms-2 fs-6"><i class="ri-filter-fill"></i> ' + semesterText + '</span>';
                    }
                    $('div.head-label').html('<h5 class="card-title mb-0">Daftar Kelas Kuliah' + filterBadge + '</h5>');
                }
            });

            // Terapkan Filter (dari modal)
            $('.btn-apply-filter').on('click', function () {
                table.draw();
                $('#modalFilter').modal('hide');

                // Update badge
                let semesterText = $('#filter_semester option:selected').text().trim();
                let prodiText = $('#filter_prodi option:selected').text().trim();
                let filterBadge = '';

                let labels = [];
                if ($('#filter_semester').val()) labels.push(semesterText);
                if ($('#filter_prodi').val()) labels.push(prodiText);

                if (labels.length > 0) {
                    filterBadge = '<span class="badge bg-primary ms-2 fs-6"><i class="ri-filter-fill"></i> ' + labels.join(' · ') + '</span>';
                }
                $('div.head-label').html('<h5 class="card-title mb-0">Daftar Kelas Kuliah' + filterBadge + '</h5>');
            });

            // Reset Filter
            $('.btn-reset-filter').on('click', function () {
                $('#filter_semester').val('').trigger('change');
                $('#filter_prodi').val('').trigger('change');
                $('#filter_status').val('');
                table.draw();
                $('#modalFilter').modal('hide');
                $('div.head-label').html('<h5 class="card-title mb-0">Daftar Kelas Kuliah</h5>');
            });

            // Delete Confirmation
            $(document).on('click', '.btn-delete', function () {
                let form = $(this).closest('form');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush