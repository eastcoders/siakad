@extends('layouts.app')

@section('title', 'Dashboard Sinkronisasi (Push Data)')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
        <h4 class="fw-bold mb-0">Dashboard Sinkronisasi (Neo Feeder)</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Sinkronisasi</li>
            </ol>
        </nav>
    </div>

    <div class="row mb-4">
        <!-- Summary Cards -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">
                        <h5 class="text-white mb-1">Total Antrean</h5>
                        <p class="mb-0 opacity-75">Data siap push</p>
                    </div>
                    <div class="avatar">
                        <h2 class="text-white mb-0">{{ number_format($countBiodata + $countRiwayat) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-8 mb-4">
            <div class="card h-100 shadow-none border bg-label-warning text-dark">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <i class="ri-error-warning-line ri-3x me-3"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">Penting: Hierarki Data</h6>
                            <p class="mb-0 small">Proses sinkronisasi wajib dilakukan secara berurutan. <b>Biodata</b> harus sukses mendapatkan UUID Feeder sebelum <b>Riwayat Pendidikan</b> dapat dikirim.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0"><i class="ri-table-line me-2"></i>Rekapitulasi Data Belum Sinkron</h5>
                    <button class="btn btn-primary btn-lg shadow" id="btn-push-all">
                        <i class="ri-rocket-2-fill me-2"></i> Push Semua Data Secara Sekuensial
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="60" class="text-center">NO</th>
                                <th>Nama Modul / Jenis Data</th>
                                <th class="text-center">Jumlah Antrean</th>
                                <th class="text-center">Status Hierarki</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modules as $index => $module)
                            <tr>
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3 border rounded bg-light d-flex align-items-center justify-content-center text-primary text-uppercase fw-bold">
                                            {{ substr($module['id'], 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">{{ $module['name'] }}</span>
                                            <small class="text-muted"><code>Insert{{ str_replace(' ', '', $module['name']) }}</code></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-info rounded-pill px-3 py-2 fs-6">
                                        {{ number_format($module['count']) }} <small>Record</small>
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(isset($module['dependency']))
                                        <span class="badge bg-label-warning border">
                                            <i class="ri-links-line me-1"></i> Menunggu {{ $module['dependency'] === 'biodata' ? 'Biodata' : '' }}
                                        </span>
                                    @else
                                        <span class="badge bg-label-success border">
                                            <i class="ri-check-line me-1"></i> Mandiri / Root
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm px-4 btn-push-module" 
                                            data-type="{{ $module['id'] }}" 
                                            data-name="{{ $module['name'] }}"
                                            {{ $module['count'] == 0 ? 'disabled' : '' }}>
                                        <i class="ri-refresh-line me-1"></i> Push
                                    </button>
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

<!-- Modal Progress (Custom Swal Integration) -->
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnPushAll = document.getElementById('btn-push-all');
        const btnModule = document.querySelectorAll('.btn-push-module');

        // Logic Global Push
        btnPushAll.addEventListener('click', async function() {
            const { isConfirmed } = await Swal.fire({
                title: 'Konfirmasi Push Massal',
                text: "Sistem akan mengirimkan data secara bertahap (Biodata -> Riwayat). Proses ini dapat memakan waktu lama.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Mulai Sinkronisasi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#7367f0'
            });

            if (isConfirmed) {
                runSequentialSync();
            }
        });

        // Logic Per Module Push
        btnModule.forEach(btn => {
            btn.addEventListener('click', async function() {
                const type = this.dataset.type;
                const name = this.dataset.name;
                
                const ids = await getIds(type);
                if (ids.length === 0) {
                    Swal.fire('Info', 'Tidak ada data ' + name + ' yang perlu disinkronkan.', 'info');
                    return;
                }

                await processBatch(type, name, ids);
                location.reload();
            });
        });

        async function runSequentialSync() {
            // Urutan 1: Biodata
            const biodataIds = await getIds('biodata');
            if (biodataIds.length > 0) {
                const result = await processBatch('biodata', 'Biodata Mahasiswa', biodataIds);
                if (result.failed > 0 && result.success === 0) {
                    Swal.fire('Gagal', 'Sinkronisasi Biodata gagal total, proses dihentikan.', 'error');
                    return;
                }
            }

            // Urutan 2: Riwayat Pendidikan
            const riwayatIds = await getIds('riwayat');
            if (riwayatIds.length > 0) {
                await processBatch('riwayat', 'Riwayat Pendidikan', riwayatIds);
            }

            Swal.fire({
                title: 'Sinkronisasi Selesai',
                text: 'Proses pengiriman data telah rampung.',
                icon: 'success',
                confirmButtonText: 'Tutup'
            }).then(() => location.reload());
        }

        async function getIds(type) {
            try {
                const response = await axios.get("{{ route('admin.sinkronisasi.get-ids') }}", { params: { type } });
                return response.data;
            } catch (error) {
                console.error('Error fetching IDs:', error);
                Swal.fire('Error', 'Gagal mengambil data antrean: ' + error.message, 'error');
                return [];
            }
        }

        async function processBatch(type, moduleName, ids) {
            let success = 0;
            let failed = 0;
            let total = ids.length;

            Swal.fire({
                title: 'Sinkronisasi ' + moduleName,
                html: `Memproses: <strong id="swal-count">0</strong> / ${total} data<br>
                       <div class="progress mt-3" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="swal-pb" style="width: 0%"></div>
                       </div>
                       <div class="mt-2 small text-muted text-start" id="swal-log">Mulai mengirim...</div>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const endpoint = type === 'biodata' ? "{{ route('admin.sinkronisasi.push-biodata') }}" : "{{ route('admin.sinkronisasi.push-riwayat') }}";

            for (let i = 0; i < ids.length; i++) {
                try {
                    const response = await axios.post(endpoint, { id: ids[i] });
                    if (response.data.success) {
                        success++;
                    } else {
                        failed++;
                    }
                } catch (error) {
                    failed++;
                }

                // Update UI Progress
                const current = i + 1;
                const percent = (current / total) * 100;
                document.getElementById('swal-count').innerText = current;
                document.getElementById('swal-pb').style.width = percent + '%';
                if (failed > 0) {
                    document.getElementById('swal-log').innerHTML = `<span class="text-danger">Gagal: ${failed}</span> | Berhasil: ${success}`;
                } else {
                    document.getElementById('swal-log').innerText = `Mengirim record ke-${current}...`;
                }
            }

            return { success, failed };
        }
    });
</script>
@endpush
