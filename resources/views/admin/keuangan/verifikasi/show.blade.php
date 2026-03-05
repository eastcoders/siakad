@extends('layouts.app')
@section('title', 'Detail Verifikasi')

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Info Pembayaran</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">No. Tagihan</dt>
                        <dd class="col-sm-7"><code>{{ $pembayaran->tagihan->nomor_tagihan }}</code></dd>
                        <dt class="col-sm-5">Mahasiswa</dt>
                        <dd class="col-sm-7">{{ $pembayaran->tagihan->mahasiswa->nama_mahasiswa }}</dd>
                        <dt class="col-sm-5">Semester</dt>
                        <dd class="col-sm-7">{{ $pembayaran->tagihan->semester->nama_semester ?? '-' }}</dd>
                        <dt class="col-sm-5">Tanggal Bayar</dt>
                        <dd class="col-sm-7">{{ $pembayaran->tanggal_bayar->format('d F Y') }}</dd>
                        <dt class="col-sm-5">Jumlah Bayar</dt>
                        <dd class="col-sm-7 fw-bold">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            @php
                                $vBadge = match($pembayaran->status_verifikasi) {
                                    'disetujui' => 'bg-success',
                                    'ditolak' => 'bg-danger',
                                    default => 'bg-warning',
                                };
                            @endphp
                            <span class="badge {{ $vBadge }}">{{ \App\Models\Pembayaran::STATUS_OPTIONS[$pembayaran->status_verifikasi] }}</span>
                        </dd>
                        @if($pembayaran->nomor_kuitansi)
                            <dt class="col-sm-5">No. Kuitansi</dt>
                            <dd class="col-sm-7"><strong>{{ $pembayaran->nomor_kuitansi }}</strong></dd>
                        @endif
                        @if($pembayaran->catatan_admin)
                            <dt class="col-sm-5">Catatan Admin</dt>
                            <dd class="col-sm-7 text-danger">{{ $pembayaran->catatan_admin }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($pembayaran->status_verifikasi === 'pending')
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Aksi Verifikasi</h5></div>
                    <div class="card-body">
                        <form action="{{ route('admin.keuangan-modul.verifikasi.approve', $pembayaran->id) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Setujui pembayaran ini?')">
                                <i class="ri-checkbox-circle-line me-1"></i> Setujui Pembayaran
                            </button>
                        </form>

                        <form action="{{ route('admin.keuangan-modul.verifikasi.reject', $pembayaran->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Misal: Bukti transfer tidak jelas, nominal tidak sesuai..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Tolak pembayaran ini?')">
                                <i class="ri-close-circle-line me-1"></i> Tolak Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Bukti Bayar</h5>
                    <a href="{{ route('admin.keuangan-modul.verifikasi.bukti', $pembayaran->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="ri-download-2-line me-1"></i> Download
                    </a>
                </div>
                <div class="card-body text-center">
                    @php
                        $ext = pathinfo($pembayaran->bukti_bayar, PATHINFO_EXTENSION);
                    @endphp
                    @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']))
                        <img src="{{ route('admin.keuangan-modul.verifikasi.bukti', $pembayaran->id) }}"
                            class="img-fluid rounded" style="max-height: 500px;" alt="Bukti Bayar">
                    @elseif(strtolower($ext) === 'pdf')
                        <iframe src="{{ route('admin.keuangan-modul.verifikasi.bukti', $pembayaran->id) }}"
                            style="width:100%; height:500px; border:none;"></iframe>
                    @else
                        <p class="text-muted">Preview tidak tersedia. Silakan download file.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.keuangan-modul.verifikasi.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
        </a>
    </div>
@endsection
