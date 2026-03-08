@extends('layouts.app')

@section('title', 'Rekapitulasi Kuisioner - Direktur')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Rekapitulasi Kuisioner</h4>
            <span class="badge bg-label-primary px-3 py-2">Role: Direktur</span>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="card-title mb-0">Daftar Hasil Kuisioner</h5>
                <form action="{{ route('direktur.rekap-kuisioner.index') }}" method="GET"
                    class="d-flex align-items-center gap-2">
                    <label for="id_semester" class="form-label mb-0 text-nowrap">Filter Semester:</label>
                    <select name="id_semester" id="id_semester" class="form-select form-select-sm"
                        onchange="this.form.submit()">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id_semester }}" {{ $idSemester == $sem->id_semester ? 'selected' : '' }}>
                                {{ $sem->nama_semester }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Judul Kuisioner</th>
                            <th>Tipe</th>
                            <th class="text-center">Periode</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kuisioners as $k)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $k->judul }}</div>
                                    @if($k->target_ujian)
                                        <small class="badge bg-label-info p-1 px-2 mt-1">{{ $k->target_ujian }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $tipeLabels = [
                                            'pelayanan' => ['label' => 'Pelayanan Akademik', 'color' => 'success'],
                                            'dosen' => ['label' => 'Kinerja Dosen', 'color' => 'primary'],
                                            'ami' => ['label' => 'AMI (Internal)', 'color' => 'warning'],
                                        ];
                                        $label = $tipeLabels[$k->tipe] ?? ['label' => $k->tipe, 'color' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $label['color'] }}">{{ $label['label'] }}</span>
                                </td>
                                <td class="text-center"><small>{{ $k->semester->nama_semester ?? '-' }}</small></td>
                                <td class="text-center">
                                    <span class="badge bg-label-{{ $k->status === 'published' ? 'success' : 'secondary' }}">
                                        {{ strtoupper($k->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('direktur.rekap-kuisioner.show', $k->id) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="ri-bar-chart-2-line me-1"></i> Lihat Hasil
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <p class="text-muted">Belum ada kuisioner yang tersedia untuk periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection