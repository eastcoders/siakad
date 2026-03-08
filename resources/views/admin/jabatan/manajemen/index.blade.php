@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manajemen Penugasan Jabatan Struktural</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJabatanModal">
                        <i class="ri-add-line me-1"></i> Tugaskan Jabatan Baru
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="tableManajemenJabatan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pemegang Jabatan</th>
                                    <th>Jabatan</th>
                                    <th>Nomor SK</th>
                                    <th>Mulai Menjabat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($penugasans as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex justify-content-start align-items-center">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium text-heading">{{ $p->user->name }}</span>
                                                    @if($p->user->dosen)
                                                        <small class="text-muted">DOSEN:
                                                            {{ $p->user->dosen->nidn ?? $p->user->dosen->nip ?? '-' }}</small>
                                                    @elseif($p->user->pegawai)
                                                        <small class="text-muted">PEGAWAI:
                                                            {{ $p->user->pegawai->nip ?? '-' }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info">{{ $p->jabatan->nama_jabatan }}</span>
                                        </td>
                                        <td>{{ $p->nomor_sk ?? '-' }}</td>
                                        <td>{{ $p->tanggal_mulai ? $p->tanggal_mulai->format('d/m/Y') : '-' }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.manajemen-jabatan.destroy', $p->id) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin mencabut jabatan ini? Hak akses (Role) user akan otomatis dicabut.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect"
                                                    title="Cabut Jabatan">
                                                    <i class="ri-delete-bin-line text-danger"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Penugasan -->
    <div class="modal fade" id="addJabatanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.manajemen-jabatan.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tugaskan Jabatan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih User (Dosen/Pegawai)</label>
                            <select name="user_id" id="select2-user-search"
                                class="form-select @error('user_id') is-invalid @enderror" required></select>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Jabatan</label>
                            <select name="jabatan_id" class="form-select @error('jabatan_id') is-invalid @enderror"
                                required>
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($jabatans as $jabatan)
                                    <option value="{{ $jabatan->id }}">{{ $jabatan->nama_jabatan }}</option>
                                @endforeach
                            </select>
                            @error('jabatan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor SK (Opsional)</label>
                            <input type="text" name="nomor_sk" class="form-control" placeholder="Contoh: SK/001/2024">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mulai Menjabat</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Penugasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTables
            if ($.fn.DataTable) {
                $('#tableManajemenJabatan').DataTable({
                    processing: true,
                    serverSide: false,
                    language: {
                        search: "",
                        searchPlaceholder: "Cari data..."
                    }
                });
            }

            // Centralized Select2 Initialization
            $('#addJabatanModal').on('shown.bs.modal', function () {
                $('#select2-user-search').select2({
                    dropdownParent: $('#addJabatanModal'),
                    placeholder: 'Ketik nama/NIDN/NIP Dosen atau Pegawai...',
                    allowClear: true,
                    minimumInputLength: 2,
                    ajax: {
                        url: "{{ route('admin.manajemen-jabatan.search-user') }}",
                        dataType: 'json',
                        delay: 300,
                        processResults: function (data) {
                            return { results: data };
                        },
                        cache: true
                    },
                    templateResult: formatUserResult,
                    templateSelection: formatUserSelection
                });
            });

            function formatUserSelection(user) {
                return user.text;
            }

            function formatUserResult(user) {
                if (!user.id) return user.text;

                // Jika ini adalah header grup (DOSEN/PEGAWAI)
                if (user.children) return user.text;

                return $(`
                            <div class="d-flex flex-column">
                                <span class="fw-medium">${user.text}</span>
                            </div>
                        `);
            }
        });
    </script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #d9dee3 !important;
            border-radius: 0.375rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .select2-dropdown {
            z-index: 2000 !important;
        }
    </style>
@endpush