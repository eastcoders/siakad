@extends('layouts.app')
@section('title', 'Kuitansi Pembayaran')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" id="kuitansi-content">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold mb-1">KUITANSI PEMBAYARAN</h4>
                        <p class="text-muted mb-0">SIA-POLSA</p>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-6">
                            <dl class="row mb-0">
                                <dt class="col-5">No. Kuitansi</dt>
                                <dd class="col-7"><strong>{{ $pembayaran->nomor_kuitansi }}</strong></dd>
                                <dt class="col-5">Tanggal</dt>
                                <dd class="col-7">{{ $pembayaran->verified_at->format('d F Y') }}</dd>
                            </dl>
                        </div>
                        <div class="col-6">
                            <dl class="row mb-0">
                                <dt class="col-5">Mahasiswa</dt>
                                <dd class="col-7">{{ $mahasiswa->nama_mahasiswa }}</dd>
                                <dt class="col-5">NIM</dt>
                                <dd class="col-7">{{ $mahasiswa->nim ?? '-' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Keterangan</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    Pembayaran tagihan {{ $pembayaran->tagihan->semester->nama_semester ?? '' }}
                                    <br><small class="text-muted">No. Tagihan:
                                        {{ $pembayaran->tagihan->nomor_tagihan }}</small>
                                </td>
                                <td class="text-end fw-bold">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row mt-5">
                        <div class="col-6"></div>
                        <div class="col-6 text-center">
                            <p class="mb-5">Diverifikasi oleh:</p>
                            <p class="fw-bold mb-0">{{ $pembayaran->verifier->name ?? 'Admin Keuangan' }}</p>
                            <small class="text-muted">Staff Keuangan</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3 d-print-none">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="ri-printer-line me-1"></i> Cetak Kuitansi
                </button>
                <a href="{{ route('mahasiswa.keuangan.index') }}" class="btn btn-outline-secondary ms-2">
                    <i class="ri-arrow-left-line me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection