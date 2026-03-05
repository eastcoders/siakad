@extends('layouts.app')
@section('title', 'Keuangan Saya')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Keuangan Saya</h5>
                </div>
                <div class="card-body">
                    @if($tagihans->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="ri-information-line me-2"></i> Belum ada tagihan yang diterbitkan.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($tagihans as $tagihan)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1">
                                                        {{ $tagihan->semester->nama_semester ?? $tagihan->id_semester }}</h6>
                                                    <small class="text-muted">{{ $tagihan->nomor_tagihan }}</small>
                                                </div>
                                                @php
                                                    $badgeClass = match ($tagihan->status) {
                                                        'lunas' => 'bg-success',
                                                        'cicil' => 'bg-warning',
                                                        default => 'bg-danger',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ \App\Models\Tagihan::STATUS_OPTIONS[$tagihan->status] ?? $tagihan->status }}
                                                </span>
                                            </div>

                                            <div class="mb-2">
                                                <small class="text-muted">Total Tagihan</small>
                                                <div class="fw-bold">Rp
                                                    {{ number_format($tagihan->total_tagihan - $tagihan->total_potongan, 0, ',', '.') }}
                                                </div>
                                            </div>

                                            @if($tagihan->total_potongan > 0)
                                                <div class="mb-2">
                                                    <small class="text-success">Potongan: Rp
                                                        {{ number_format($tagihan->total_potongan, 0, ',', '.') }}</small>
                                                </div>
                                            @endif

                                            <div class="progress mb-2" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $tagihan->status === 'lunas' ? 'success' : 'primary' }}"
                                                    style="width: {{ $tagihan->persentase_bayar }}%"></div>
                                            </div>
                                            <small class="text-muted">Dibayar: Rp
                                                {{ number_format($tagihan->total_dibayar, 0, ',', '.') }}
                                                ({{ $tagihan->persentase_bayar }}%)</small>
                                        </div>
                                        <div class="card-footer">
                                            <a href="{{ route('mahasiswa.keuangan.show', $tagihan->id) }}"
                                                class="btn btn-sm btn-outline-primary w-100">
                                                <i class="ri-eye-line me-1"></i> Lihat Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection