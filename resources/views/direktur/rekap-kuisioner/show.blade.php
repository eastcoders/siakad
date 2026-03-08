@extends('layouts.app')

@section('title', 'Detail Hasil Kuisioner')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <a href="{{ route('direktur.rekap-kuisioner.index') }}" class="text-muted fw-light">Rekap Kuisioner
                        /</a> Dashboard Laporan
                </h4>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge bg-label-primary fs-6">{{ $kuisioner->judul }}</span>
                    @if($kuisioner->target_ujian)
                        <span class="badge bg-label-info">{{ $kuisioner->target_ujian }}</span>
                    @endif
                    <span class="text-muted fs-tiny"><i
                            class="ri-calendar-line me-1"></i>{{ $kuisioner->semester->nama_semester ?? '' }}</span>
                </div>
            </div>
            <a href="{{ route('direktur.rekap-kuisioner.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>

        <div class="row g-4 mb-4">
            <!-- Partisipasi Card -->
            <div class="col-sm-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-muted">Statistik Partisipasi</span>
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri-group-line ri-20px"></i>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-end mb-2">
                            <h2 class="mb-0 me-2 fw-bold text-primary">{{ $coverage }}%</h2>
                            <small class="text-success mb-1">(Coverage)</small>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $coverage }}%"
                                aria-valuenow="{{ $coverage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded text-center">
                                    <h6 class="mb-0 fw-bold">{{ $totalMhsSudah }}</h6>
                                    <small class="text-muted fs-tiny">Sudah Mengisi</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded text-center">
                                    <h6 class="mb-0 fw-bold text-danger">{{ $totalMhsBelum }}</h6>
                                    <small class="text-muted fs-tiny">Belum Mengisi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grand Average Score -->
            <div class="col-sm-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="content-left">
                                <span class="fw-semibold">Rata-Rata Indeks Skala</span>
                                <div class="d-flex align-items-center mt-2">
                                    <h3 class="mb-0 me-2">{{ number_format($grandAverage, 2) }}</h3>
                                    <i class="ri-star-fill text-warning mb-1"></i>
                                </div>
                                <small class="mb-0 text-muted">Dari Skala 1.00 - 5.00</small>
                            </div>
                            <div class="avatar">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ri-bar-chart-grouped-line ri-24px"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Label Kepuasan / Kesimpulan -->
            <div class="col-sm-6 col-lg-4">
                <div class="card border-{{ $grandKesimpulan['color'] }} h-100 shadow-sm">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title text-muted mb-1">Kesimpulan Penilaian</h5>
                        <h2 class="text-{{ $grandKesimpulan['color'] }} fw-bold mb-0 mt-2">{{ $grandKesimpulan['teks'] }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        @if($kuisioner->tipe === 'dosen' && !empty($rekapDosen))
            <div class="card border-0 shadow-sm mb-4">
                <h5 class="card-header border-bottom">Rekapitulasi Nilai per Dosen</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th>Nama Dosen</th>
                                <th>NIDN</th>
                                <th class="text-center">Rata-Rata</th>
                                <th class="text-center">Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapDosen as $index => $rd)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $rd->nama }}</strong></td>
                                    <td>{{ $rd->nidn ?? '-' }}</td>
                                    <td class="text-center fw-bold">{{ number_format($rd->avg_score, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-label-{{ $rd->kesimpulan['color'] }} w-100">
                                            {{ $rd->kesimpulan['teks'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <h5 class="card-header border-bottom">Perincian per Aspek (Likert)</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%">No</th>
                            <th style="width: 60%">Aspek Pertanyaan</th>
                            <th class="text-center">Rata-Rata</th>
                            <th class="text-center">Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapPertanyaan as $index => $rp)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-wrap">{{ $rp['teks'] }}</td>
                                <td class="text-center fw-bold">{{ number_format($rp['avg'], 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-label-{{ $rp['label']['color'] }} w-100">
                                        {{ $rp['label']['teks'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data likert.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(!empty($esaiTerbaru) && count($esaiTerbaru) > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Sorotan Masukan Esai Terbaru</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="list-group list-group-flush">
                        @foreach($esaiTerbaru as $esai)
                            <div class="list-group-item d-flex align-items-start py-3">
                                <div class="avatar me-3">
                                    <span class="avatar-initial rounded-circle bg-label-secondary"><i
                                            class="ri-chat-1-line"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-primary">{{ Str::limit($esai->pertanyaan->teks_pertanyaan, 80) }}</h6>
                                    <p class="mb-0 italic text-dark">"{{ $esai->jawaban_teks }}"</p>
                                    <small class="text-muted">{{ $esai->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection