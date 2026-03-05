@extends('layouts.app')
@section('title', 'Komponen Biaya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Master Komponen Biaya</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKomponenModal">
                        <i class="ri-add-line me-1"></i> Tambah Komponen
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="tableKomponen">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Komponen</th>
                                    <th>Kategori</th>
                                    <th>Nominal Standar</th>
                                    <th>Prodi</th>
                                    <th>Angkatan</th>
                                    <th>Wajib KRS</th>
                                    <th>Wajib Ujian</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($komponens as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><code>{{ $item->kode_komponen }}</code></td>
                                        <td>{{ $item->nama_komponen }}</td>
                                        <td>
                                            <span
                                                class="badge bg-label-{{ $item->kategori === 'per_semester' ? 'primary' : 'info' }}">
                                                {{ \App\Models\KomponenBiaya::KATEGORI_OPTIONS[$item->kategori] ?? $item->kategori }}
                                            </span>
                                        </td>
                                        <td class="text-end">Rp {{ number_format($item->nominal_standar, 0, ',', '.') }}</td>
                                        <td>{{ $item->programStudi->nama_program_studi ?? 'Semua Prodi' }}</td>
                                        <td>
                                            @if($item->tahun_angkatan)
                                                <span class="badge bg-label-dark">{{ $item->tahun_angkatan }}</span>
                                            @else
                                                <span class="text-muted"><em>Semua</em></span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($item->is_wajib_krs)
                                                <i class="ri-checkbox-circle-fill text-success"></i>
                                            @else
                                                <i class="ri-close-circle-line text-muted"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($item->is_wajib_ujian)
                                                <i class="ri-checkbox-circle-fill text-success"></i>
                                            @else
                                                <i class="ri-close-circle-line text-muted"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $item->is_active ? 'success' : 'danger' }}">
                                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn btn-sm btn-icon btn-outline-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editKomponenModal{{ $item->id }}">
                                                    <i class="ri-edit-2-line"></i>
                                                </button>
                                                @if($item->is_active)
                                                    <form
                                                        action="{{ route('admin.keuangan-modul.komponen-biaya.destroy', $item->id) }}"
                                                        method="POST" onsubmit="return confirm('Nonaktifkan komponen biaya ini?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-icon btn-outline-danger"><i
                                                                class="ri-close-line"></i></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editKomponenModal{{ $item->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <form
                                                    action="{{ route('admin.keuangan-modul.komponen-biaya.update', $item->id) }}"
                                                    method="POST">
                                                    @csrf @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Komponen Biaya</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @include('admin.keuangan.komponen-biaya._form', ['item' => $item])
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="addKomponenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.keuangan-modul.komponen-biaya.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Komponen Biaya</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.keuangan.komponen-biaya._form', ['item' => null])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
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
            $('#tableKomponen').DataTable();
        });
    </script>
@endpush