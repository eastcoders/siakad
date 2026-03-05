@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0">Dashboard Keuangan</h4>
                <div class="badge bg-label-primary">
                    Semester Aktif: {{ $semesterAktif ? $semesterAktif->nama_semester : 'Tidak Ada' }}
                </div>
            </div>
        </div>

        <!-- Widget 1: Antrean Verifikasi -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-warning">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-muted">Antrean Verifikasi</h6>
                        <h4 class="mb-1 fw-bold text-warning">{{ $antreanCount }} <small
                                class="fs-6 fw-normal text-muted">bukti pending</small></h4>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="ri-time-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 2: Realisasi Pembayaran -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-title mb-0 text-muted">Realisasi Pembayaran</h6>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ri-percent-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                    <h4 class="mb-1 fw-bold text-info">{{ $realisasi['persentase'] }}%</h4>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $realisasi['persentase'] }}%"
                            aria-valuenow="{{ $realisasi['persentase'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">Rp {{ number_format($realisasi['total_dibayar'], 0, ',', '.') }}
                        / Rp {{ number_format($realisasi['total_target'], 0, ',', '.') }}</small>
                </div>
            </div>
        </div>

        <!-- Widget 3: Total Piutang -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-danger">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-muted">Total Piutang</h6>
                        <h4 class="mb-0 fw-bold text-danger">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h4>
                        <small class="text-danger fw-semibold">Belum Lunas</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="ri-arrow-down-circle-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 4: Total Pendapatan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-success">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-muted">Total Pendapatan</h6>
                        <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h4>
                        <small class="text-success fw-semibold">Sudah Masuk</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="ri-safe-2-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Chart Transaksi Mingguan -->
        <div class="col-lg-8">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 me-2">Grafik Transaksi Masuk (4 Minggu Terakhir)</h5>
                </div>
                <div class="card-body">
                    <canvas id="transaksiChart" style="min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Mahasiswa Terblokir -->
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header border-bottom">
                    <h5 class="card-title m-0 me-2 text-danger"><i class="ri-shield-keyhole-line me-2"></i>Mahasiswa
                        Terblokir Akademik</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($mahasiswaTerblokir as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <span class="fw-semibold d-block">{{ $item['mahasiswa']->nama }}</span>
                                    <small class="text-muted">{{ $item['mahasiswa']->nim }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-label-danger mb-1">{{ $item['status_blokir'] }}</span><br>
                                    <small class="fw-bold text-danger">Sisa: Rp
                                        {{ number_format($item['tagihan']->sisa_tagihan, 0, ',', '.') }}</small>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted">
                                <i class="ri-check-double-line ri-24px mb-2"></i><br>
                                Tidak ada mahasiswa yang terblokir.
                            </li>
                        @endforelse
                    </ul>
                </div>
                @if(count($mahasiswaTerblokir) == 5)
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.keuangan-modul.tagihan.index') }}" class="btn btn-sm btn-outline-primary">Lihat
                            Semua Tunggakan</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tabel Pembayaran Terbaru -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Pembayaran Masuk Terbaru</h5>
                    <a href="{{ route('admin.keuangan-modul.verifikasi.index') }}" class="btn btn-sm btn-primary">Lihat
                        Semua Verifikasi</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>TANGGAL</th>
                                <th>MAHASISWA</th>
                                <th>NO. KUITANSI</th>
                                <th>NOMINAL</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembayaranTerbaru as $bayar)
                                <tr>
                                    <td>{{ $bayar->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="fw-semibold d-block">{{ $bayar->tagihan->mahasiswa->nama }}</span>
                                        <small class="text-muted">{{ $bayar->tagihan->mahasiswa->nim }}</small>
                                    </td>
                                    <td>
                                        @if($bayar->status_verifikasi === 'disetujui')
                                            <span class="fw-bold">{{ $bayar->nomor_kuitansi }}</span>
                                        @else
                                            <span class="text-muted fst-italic">Menunggu Verifikasi</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">Rp {{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</td>
                                    <td>
                                        @if($bayar->status_verifikasi === 'pending')
                                            <span class="badge bg-label-warning">Pending</span>
                                        @elseif($bayar->status_verifikasi === 'disetujui')
                                            <span class="badge bg-label-success">Disetujui</span>
                                        @else
                                            <span class="badge bg-label-danger">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        Belum ada data pembayaran
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetch('{{ route("admin.keuangan-dashboard.chart") }}')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('transaksiChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Total Pembayaran (Rp)',
                                data: data.data,
                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1,
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching chart data:', error));
        });
    </script>
@endpush