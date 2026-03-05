@extends('layouts.app')
@section('title', 'Tagihan Mahasiswa')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tagihan Mahasiswa</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#terbitkanModal">
                        <i class="ri-add-line me-1"></i> Terbitkan Tagihan
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="tableTagihan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Tagihan</th>
                                    <th>Mahasiswa</th>
                                    <th>Semester</th>
                                    <th>Total Tagihan</th>
                                    <th>Potongan</th>
                                    <th>Dibayar</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tagihans as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><code>{{ $item->nomor_tagihan }}</code></td>
                                        <td>{{ $item->mahasiswa->nama_mahasiswa ?? '-' }}</td>
                                        <td>{{ $item->semester->nama_semester ?? $item->id_semester }}</td>
                                        <td class="text-end">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($item->total_potongan, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($item->total_dibayar, 0, ',', '.') }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match ($item->status) {
                                                    'lunas' => 'bg-success',
                                                    'cicil' => 'bg-warning',
                                                    default => 'bg-danger',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ \App\Models\Tagihan::STATUS_OPTIONS[$item->status] ?? $item->status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('admin.keuangan-modul.tagihan.show', $item->id) }}"
                                                    class="btn btn-sm btn-icon btn-outline-primary">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                @if($item->status === 'belum_bayar')
                                                    <form action="{{ route('admin.keuangan-modul.tagihan.destroy', $item->id) }}"
                                                        method="POST" onsubmit="return confirm('Hapus tagihan ini?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-icon btn-outline-danger"><i
                                                                class="ri-delete-bin-line"></i></button>
                                                    </form>
                                                @endif
                                            </div>
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

    <!-- Modal Terbitkan Tagihan -->
    <div class="modal fade" id="terbitkanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.keuangan-modul.tagihan.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Terbitkan Tagihan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label d-block">Mode Penerbitan <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="radio" name="mode" id="mode_individual"
                                        value="individual" checked onchange="toggleModeTagihan()">
                                    <label class="form-check-label" for="mode_individual">Individual</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="mode" id="mode_bulk" value="bulk"
                                        onchange="toggleModeTagihan()">
                                    <label class="form-check-label" for="mode_bulk">Bulk (Per Semester)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select name="id_semester" class="form-select select2-tagihan" required style="width:100%">
                                    <option value="">Pilih Semester</option>
                                    @foreach($semesters as $s)
                                        <option value="{{ $s->id_semester }}">{{ $s->nama_semester }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Program Studi</label>
                                <select name="id_prodi" class="form-select select2-tagihan" style="width:100%">
                                    <option value="">Semua Prodi</option>
                                    @foreach($prodis as $p)
                                        <option value="{{ $p->id_prodi }}">{{ $p->nama_program_studi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12" id="mhsSelectWrapper">
                                <label class="form-label">Mahasiswa <span class="text-danger">*</span></label>
                                <select name="id_mahasiswa" id="select2Mahasiswa" class="form-select" style="width:100%">
                                    <option value="">Ketik NIM atau Nama Mahasiswa...</option>
                                </select>
                                <div class="form-text">Cari mahasiswa berdasarkan NIM atau nama (untuk mode individual).
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Terbitkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        function toggleModeTagihan() {
            const isIndividual = document.getElementById('mode_individual').checked;
            document.getElementById('mhsSelectWrapper').style.display = isIndividual ? 'block' : 'none';
        }

        $(document).ready(function () {
            $('#tableTagihan').DataTable();

            // Select2 biasa untuk semester & prodi
            $('select[name="id_semester"]').select2({ dropdownParent: $('#terbitkanModal') });
            $('select[name="id_prodi"]').select2({
                dropdownParent: $('#terbitkanModal'),
                allowClear: true,
                placeholder: 'Semua Prodi'
            });

            // Select2 AJAX untuk pencarian mahasiswa
            $('#select2Mahasiswa').select2({
                dropdownParent: $('#terbitkanModal'),
                placeholder: 'Ketik NIM atau Nama Mahasiswa...',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route("admin.keuangan-modul.search-mahasiswa") }}',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            q: params.term,
                            id_prodi: $('select[name="id_prodi"]').val()
                        };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    },
                    cache: true
                }
            });

            // Reset pilihan mahasiswa ketika prodi berubah
            $('select[name="id_prodi"]').on('change', function () {
                $('#select2Mahasiswa').val(null).trigger('change');
            });
        });
    </script>
@endpush