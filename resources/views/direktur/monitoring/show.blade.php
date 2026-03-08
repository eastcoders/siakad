@extends('layouts.app')

@section('title', 'Detail Monitoring Perkuliahan')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <a href="{{ route('direktur.monitoring.index') }}" class="text-muted fw-light">Monitoring /</a> Detail
                    Kelas
                </h4>
                <p class="text-muted mb-0">{{ $kelas->mataKuliah->nama_mk }} - {{ $kelas->nama_kelas_kuliah }}</p>
            </div>
            <a href="{{ route('direktur.monitoring.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
        </div>

        <div class="row g-4 mb-4">
            <!-- Info Kelas -->
            <div class="col-md-8">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">Jurnal Perkuliahan</h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Prtm</th>
                                    <th>Tanggal</th>
                                    <th>Dosen</th>
                                    <th>Materi/Bahasan</th>
                                    <th class="text-center">Hadir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jurnal as $j)
                                    <tr>
                                        <td>{{ $j->pertemuan_ke }}</td>
                                        <td>{{ \Carbon\Carbon::parse($j->tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ $j->dosen->nama ?? '-' }}</td>
                                        <td class="text-wrap" style="min-width: 250px;">
                                            <small>{{ $j->materi }}</small>
                                        </td>
                                        <td class="text-center text-primary fw-bold">
                                            {{ $j->presensiMahasiswas()->where('status', 'H')->count() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data pertemuan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Stat -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-3">Statistik Kelas</h6>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-info"><i class="ri-group-line"></i></span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $rekapAbsensi->count() }}</h5>
                                <small class="text-muted">Total Mahasiswa</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-success"><i
                                        class="ri-calendar-check-line"></i></span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $kelas->presensi_pertemuans_count }} /
                                    {{ config('academic.target_pertemuan') }}</h5>
                                <small class="text-muted">Progres Pertemuan</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">Kehadiran Mahasiswa</h5>
                    </div>
                    <div class="p-0 overflow-auto" style="max-height: 400px;">
                        <ul class="list-group list-group-flush">
                            @foreach($rekapAbsensi->sortByDesc('percent') as $r)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-semibold small">{{ $r['nama'] }}</span>
                                        <span
                                            class="badge bg-label-{{ $r['percent'] >= 75 ? 'success' : 'danger' }}">{{ $r['percent'] }}%</span>
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-{{ $r['percent'] >= 75 ? 'success' : 'danger' }}"
                                            style="width: {{ $r['percent'] }}%"></div>
                                    </div>
                                    <small class="text-muted fs-tiny">{{ $r['hadir'] }} dari {{ $r['total'] }} Pertemuan</small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection