@extends('layouts.app')
@section('title', 'Detail Tagihan')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Info Tagihan</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">No. Tagihan</dt>
                        <dd class="col-sm-7"><code>{{ $tagihan->nomor_tagihan }}</code></dd>
                        <dt class="col-sm-5">Semester</dt>
                        <dd class="col-sm-7">{{ $tagihan->semester->nama_semester ?? $tagihan->id_semester }}</dd>
                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            @php
                                $badgeClass = match($tagihan->status) {
                                    'lunas' => 'bg-success',
                                    'cicil' => 'bg-warning',
                                    default => 'bg-danger',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ \App\Models\Tagihan::STATUS_OPTIONS[$tagihan->status] ?? $tagihan->status }}</span>
                        </dd>
                        <dt class="col-sm-5">Total</dt>
                        <dd class="col-sm-7 fw-bold">Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</dd>
                        @if($tagihan->total_potongan > 0)
                            <dt class="col-sm-5">Potongan</dt>
                            <dd class="col-sm-7 text-success">- Rp {{ number_format($tagihan->total_potongan, 0, ',', '.') }}</dd>
                        @endif
                        <dt class="col-sm-5">Dibayar</dt>
                        <dd class="col-sm-7 text-primary">Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Sisa</dt>
                        <dd class="col-sm-7 fw-bold text-danger">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Form Upload Bukti -->
            @if($tagihan->status !== 'lunas')
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Upload Bukti Bayar</h5></div>
                    <div class="card-body">
                        @if($hasPending)
                            <div class="alert alert-warning mb-0">
                                <i class="ri-time-line me-1"></i> Anda masih memiliki bukti bayar yang menunggu verifikasi.
                            </div>
                        @else
                            <form action="{{ route('mahasiswa.keuangan.upload', $tagihan->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah_bayar" class="form-control" min="1000" required
                                        value="{{ old('jumlah_bayar') }}" placeholder="Contoh: 3000000">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_bayar" class="form-control" required
                                        value="{{ old('tanggal_bayar', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Bukti Bayar <span class="text-danger">*</span></label>
                                    <input type="file" name="bukti_bayar" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <div class="form-text">Format: JPG, PNG, PDF. Maks 2MB.</div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-upload-2-line me-1"></i> Upload Bukti
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Rincian Komponen</h5></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Komponen</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-end">Potongan</th>
                                <th class="text-end">Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tagihan->items as $item)
                                <tr>
                                    <td>{{ $item->komponenBiaya->nama_komponen ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td class="text-end text-success">Rp {{ number_format($item->potongan, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($item->nominal_bersih, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0">Riwayat Pembayaran</h5></div>
                <div class="card-body">
                    @if($tagihan->pembayarans->isEmpty())
                        <p class="text-muted mb-0">Belum ada pembayaran.</p>
                    @else
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th class="text-end">Jumlah</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tagihan->pembayarans->sortByDesc('created_at') as $p)
                                    <tr>
                                        <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                                        <td class="text-end">Rp {{ number_format($p->jumlah_bayar, 0, ',', '.') }}</td>
                                        <td>
                                            @php
                                                $vBadge = match($p->status_verifikasi) {
                                                    'disetujui' => 'bg-success',
                                                    'ditolak' => 'bg-danger',
                                                    default => 'bg-warning',
                                                };
                                            @endphp
                                            <span class="badge {{ $vBadge }}">{{ \App\Models\Pembayaran::STATUS_OPTIONS[$p->status_verifikasi] }}</span>
                                        </td>
                                        <td>
                                            @if($p->status_verifikasi === 'ditolak')
                                                <small class="text-danger">{{ $p->catatan_admin }}</small>
                                            @elseif($p->nomor_kuitansi)
                                                <small>{{ $p->nomor_kuitansi }}</small>
                                            @else
                                                <small class="text-muted">Menunggu verifikasi</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('mahasiswa.keuangan.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
        </a>
    </div>
@endsection
