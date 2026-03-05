@extends('layouts.app')
@section('title', 'Verifikasi Pembayaran')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Verifikasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover" id="tableVerifikasi">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Mahasiswa</th>
                                    <th>No. Tagihan</th>
                                    <th>Semester</th>
                                    <th class="text-end">Jumlah</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pembayarans as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->tanggal_bayar->format('d/m/Y') }}</td>
                                        <td>{{ $item->tagihan->mahasiswa->nama_mahasiswa ?? '-' }}</td>
                                        <td><code>{{ $item->tagihan->nomor_tagihan }}</code></td>
                                        <td>{{ $item->tagihan->semester->nama_semester ?? '-' }}</td>
                                        <td class="text-end">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                                        <td>
                                            @php
                                                $vBadge = match ($item->status_verifikasi) {
                                                    'disetujui' => 'bg-success',
                                                    'ditolak' => 'bg-danger',
                                                    default => 'bg-warning',
                                                };
                                            @endphp
                                            <span class="badge {{ $vBadge }}">
                                                {{ \App\Models\Pembayaran::STATUS_OPTIONS[$item->status_verifikasi] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.keuangan-modul.verifikasi.show', $item->id) }}"
                                                class="btn btn-sm btn-icon btn-outline-primary">
                                                <i class="ri-eye-line"></i>
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
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#tableVerifikasi').DataTable();
        });
    </script>
@endpush