@extends('layouts.app')
@section('title', 'Detail Tagihan')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Info Tagihan</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">No. Tagihan</dt>
                        <dd class="col-sm-7"><code>{{ $tagihan->nomor_tagihan }}</code></dd>
                        <dt class="col-sm-5">Mahasiswa</dt>
                        <dd class="col-sm-7">{{ $tagihan->mahasiswa->nama_mahasiswa }}</dd>
                        <dt class="col-sm-5">NIM</dt>
                        <dd class="col-sm-7">{{ $tagihan->mahasiswa->nim ?? '-' }}</dd>
                        <dt class="col-sm-5">Semester</dt>
                        <dd class="col-sm-7">{{ $tagihan->semester->nama_semester ?? $tagihan->id_semester }}</dd>
                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7">
                            @php
                                $badgeClass = match ($tagihan->status) {
                                    'lunas' => 'bg-success',
                                    'cicil' => 'bg-warning',
                                    default => 'bg-danger',
                                };
                            @endphp
                            <span
                                class="badge {{ $badgeClass }}">{{ \App\Models\Tagihan::STATUS_OPTIONS[$tagihan->status] ?? $tagihan->status }}</span>
                        </dd>
                        <dt class="col-sm-5">Total Tagihan</dt>
                        <dd class="col-sm-7 fw-bold">Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Potongan</dt>
                        <dd class="col-sm-7 text-success">- Rp {{ number_format($tagihan->total_potongan, 0, ',', '.') }}
                        </dd>
                        <dt class="col-sm-5">Dibayar</dt>
                        <dd class="col-sm-7 text-primary">Rp {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Sisa</dt>
                        <dd class="col-sm-7 fw-bold text-danger">Rp {{ number_format($tagihan->sisa_tagihan, 0, ',', '.') }}
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Rincian Komponen</h5>
                </div>
                <div class="card-body">
                    @if($tagihan->status !== \App\Models\Tagihan::STATUS_LUNAS)
                        <form action="{{ route('admin.keuangan-modul.tagihan.potongan', $tagihan->id) }}" method="POST">
                            @csrf
                    @endif
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Komponen</th>
                                        <th class="text-end">Nominal Asli</th>
                                        <th>Keterangan Potongan</th>
                                        <th class="text-end" style="width: 15%;">Potongan (Rp)</th>
                                        <th class="text-end">Bersih (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tagihan->items as $item)
                                        <tr>
                                            <td>{{ $item->komponenBiaya->nama_komponen ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                            <td>
                                                @if($tagihan->status !== \App\Models\Tagihan::STATUS_LUNAS)
                                                    <input type="text" name="keterangan_potongan[{{ $item->id }}]"
                                                        class="form-control form-control-sm"
                                                        value="{{ old('keterangan_potongan.' . $item->id, $item->keterangan_potongan) }}"
                                                        placeholder="Alasan diskon (opsional)">
                                                @else
                                                    <span class="text-muted">{{ $item->keterangan_potongan ?? '-' }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($tagihan->status !== \App\Models\Tagihan::STATUS_LUNAS)
                                                    <input type="number" name="potongan[{{ $item->id }}]"
                                                        class="form-control form-control-sm text-end"
                                                        value="{{ old('potongan.' . $item->id, $item->potongan) }}" min="0"
                                                        max="{{ $item->nominal }}">
                                                @else
                                                    <span class="text-success">Rp
                                                        {{ number_format($item->potongan, 0, ',', '.') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold">Rp
                                                {{ number_format($item->nominal_bersih, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($tagihan->status !== \App\Models\Tagihan::STATUS_LUNAS)
                                <div class="mt-3 text-end">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="ri-save-line me-1"></i> Simpan Potongan
                                    </button>
                                </div>
                            </form>
                        @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Pembayaran</h5>
                </div>
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
                                    <th>No. Kuitansi</th>
                                    <th>Verifikator</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tagihan->pembayarans as $p)
                                    <tr>
                                        <td>{{ $p->tanggal_bayar->format('d/m/Y') }}</td>
                                        <td class="text-end">Rp {{ number_format($p->jumlah_bayar, 0, ',', '.') }}</td>
                                        <td>
                                            @php
                                                $vBadge = match ($p->status_verifikasi) {
                                                    'disetujui' => 'bg-success',
                                                    'ditolak' => 'bg-danger',
                                                    default => 'bg-warning',
                                                };
                                            @endphp
                                            <span
                                                class="badge {{ $vBadge }}">{{ \App\Models\Pembayaran::STATUS_OPTIONS[$p->status_verifikasi] }}</span>
                                        </td>
                                        <td>{{ $p->nomor_kuitansi ?? '-' }}</td>
                                        <td>{{ $p->verifier->name ?? '-' }}</td>
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
        <a href="{{ route('admin.keuangan-modul.tagihan.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
        </a>
    </div>
@endsection