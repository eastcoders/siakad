<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">KRS Mahasiswa</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-secondary d-flex align-items-center mb-4" role="alert">
            <i class="ri-information-line fs-5 me-2"></i>
            <div>Menampilkan Data berdasarkan semester :
                <strong>{{ $activeSemester ? $activeSemester->nama_semester : 'Belum Ada Semester Aktif' }}</strong>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
            <div class="flex-grow-1" style="max-width: 600px;">
                @if(isset($riwayatPendidikan) && isset($activeSemester))
                    <form action="{{ route('admin.peserta-kelas-kuliah.store') }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" name="riwayat_pendidikan_id" value="{{ $riwayatPendidikan->id }}">

                        <div class="flex-grow-1">
                            <select name="id_kelas_kuliah" class="form-select select2-kelas " required>
                                <option value=""></option>
                                @foreach($daftarKelas as $kelas)
                                    <option value="{{ $kelas->id_kelas_kuliah }}">
                                        {{ $kelas->mataKuliah->kode_mk ?? '-' }} -
                                        {{ $kelas->mataKuliah->nama_mk ?? '-' }}
                                        (Kelas: {{ $kelas->nama_kelas_kuliah }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary text-nowrap">
                            <i class="ri-add-circle-line me-1"></i> Tambah Kelas
                        </button>
                    </form>
                @else
                    <div class="text-muted fst-italic small"><i class="ri-error-warning-line text-warning me-1"></i> Tidak
                        dapat menambah kelas. Pastikan Mahasiswa memiliki Riwayat Pendidikan aktif.</div>
                @endif
            </div>

            <div class="d-flex gap-2 mt-2 mt-md-0">
                <button class="btn btn-success btn-sm text-nowrap">
                    <i class="ri-printer-line me-1"></i> CETAK KRS
                </button>
            </div>
        </div>

        <div class="table-responsive text-nowrap mb-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="5%">Action</th>
                        <th width="5%">No</th>
                        <th>Kode MK</th>
                        <th>Nama MK</th>
                        <th>Kelas</th>
                        <th class="text-center">Bobot MK (sks)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesertaKelasKuliah as $index => $peserta)
                        @php
                            $kelas = $peserta->kelasKuliah;
                            $mk = $kelas ? $kelas->mataKuliah : null;
                        @endphp
                        <tr>
                            <td class="text-center">
                                <form action="{{ route('admin.peserta-kelas-kuliah.destroy', $peserta->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill"
                                        onclick="return confirm('Yakin ingin membatalkan KRS untuk mata kuliah ini?')">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><span class="fw-semibold text-primary">{{ $mk ? $mk->kode_mk : '-' }}</span></td>
                            <td>{{ $mk ? $mk->nama_mk : '-' }}</td>
                            <td class="text-center">{{ $kelas ? $kelas->nama_kelas_kuliah : '-' }}</td>
                            <td class="text-center">{{ number_format($kelas ? ($kelas->sks_mk ?? 0) : 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Mahasiswa belum mengisi KRS pada semester
                                ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="alert alert-secondary p-3 mb-0">
            <strong>TOTAL SKS MAHASISWA ADALAH : {{ $totalSks ?? 0 }} SKS</strong>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@push('partial_scripts')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script>
        $(function () {
            if ($('.select2-kelas').length) {
                $('.select2-kelas').select2({
                    placeholder: 'Ketik Kode / Nama Kelas Kuliah...',
                    width: '100%',
                    allowClear: true
                });
            }
        });
    </script>
@endpush