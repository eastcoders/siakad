<div class="modal fade" id="modalDosen" tabindex="-1" aria-labelledby="modalDosenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            @php
                $jenisEvaluasiOptions = \App\Models\DosenPengajarKelasKuliah::JENIS_EVALUASI;
            @endphp

            <div class="modal-header">
                <h5 class="modal-title" id="modalDosenLabel">Tambah Dosen Pengajar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDosen" action="{{ route('kelas.dosen.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formDosenMethod" value="POST">
                <input type="hidden" name="kelas_kuliah_id" value="{{ $kelasKuliah->id }}">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12" id="dosenSelectWrapper">
                            <label for="dosen_id" class="form-label">Dosen Penugasan (Pusat) <span
                                    class="text-danger">*</span></label>
                            <select name="dosen_id" id="dosen_id"
                                class="form-select select2-modal @error('dosen_id') is-invalid @enderror" required>
                                <option value="">Pilih Dosen</option>
                                @foreach($daftarDosen as $d)
                                    <option value="{{ $d->id }}" {{ old('dosen_id') == $d->id ? 'selected' : '' }}>
                                        {{ $d->nidn ? '[' . $d->nidn . '] ' : '' }}{{ $d->nama_admin_display }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-primary">
                                <i class="ri-information-line me-1"></i> Dosen yang memiliki penugasan resmi (NIDN/NIDK)
                                untuk dilaporkan ke pusat.
                            </div>
                            @error('dosen_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Info dosen saat mode edit --}}
                        <div class="col-12 d-none" id="dosenInfoWrapper">
                            <label class="form-label">Dosen Penugasan (Pusat)</label>
                            <input type="text" class="form-control bg-light" id="dosenInfoText" readonly disabled>
                            <div class="form-text text-muted">
                                <i class="ri-lock-line me-1"></i> Dosen penugasan tidak dapat diubah pada mode edit.
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 border rounded bg-label-secondary">
                                <label for="id_dosen_alias_lokal" class="form-label fw-bold">Dosen Pengajar Asli
                                    (Alias)</label>
                                <select name="id_dosen_alias_lokal" id="id_dosen_alias_lokal"
                                    class="form-select select2-modal @error('id_dosen_alias_lokal') is-invalid @enderror">
                                    <option value="">-- Tetap Gunakan Dosen Penugasan --</option>
                                    @foreach($daftarDosenLokal as $d)
                                        <option value="{{ $d->id }}" {{ old('id_dosen_alias_lokal') == $d->id ? 'selected' : '' }}>
                                            {{ $d->nama_admin_display }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-2">
                                    <i class="ri-lightbulb-line me-1"></i> Gunakan jika pengajar di lapangan berbeda
                                    dengan dosen resmi. Data ini hanya untuk jadwal & laporan lokal.
                                </div>
                                @error('id_dosen_alias_lokal') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="bobot_sks" class="form-label">Bobot SKS <span
                                    class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="bobot_sks" id="bobot_sks"
                                class="form-control @error('bobot_sks') is-invalid @enderror"
                                value="{{ old('bobot_sks', 0) }}" required>
                            <div class="form-text mt-1 text-muted">
                                Bobot SKS maksimal: <strong>{{ number_format($kelasKuliah->sks_mk, 2) }}</strong>
                            </div>
                            @error('bobot_sks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jenis_evaluasi" class="form-label">Jenis Evaluasi <span
                                    class="text-danger">*</span></label>
                            <select name="jenis_evaluasi" id="jenis_evaluasi"
                                class="form-select @error('jenis_evaluasi') is-invalid @enderror" required>
                                <option value="">Pilih Jenis</option>
                                @foreach($jenisEvaluasiOptions as $val => $text)
                                    <option value="{{ $val }}" {{ old('jenis_evaluasi') == $val ? 'selected' : '' }}>
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_evaluasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jumlah_rencana_pertemuan" class="form-label">Rencana Pertemuan <span
                                    class="text-danger">*</span></label>
                            <input type="number" min="0" name="jumlah_rencana_pertemuan" id="jumlah_rencana_pertemuan"
                                class="form-control @error('jumlah_rencana_pertemuan') is-invalid @enderror"
                                value="{{ old('jumlah_rencana_pertemuan', 0) }}" required>
                            @error('jumlah_rencana_pertemuan') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="jumlah_realisasi_pertemuan" class="form-label">Realisasi Pertemuan</label>
                            <input type="number" min="0" name="jumlah_realisasi_pertemuan"
                                id="jumlah_realisasi_pertemuan"
                                class="form-control @error('jumlah_realisasi_pertemuan') is-invalid @enderror"
                                value="{{ old('jumlah_realisasi_pertemuan') }}">
                            @error('jumlah_realisasi_pertemuan') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitDosen">
                        <i class="ri-save-line me-1"></i> Simpan Dosen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function () {
            const modalElement = $('#modalDosen');

            // Initialize Select2 when modal is shown
            modalElement.on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    dropdownParent: modalElement,
                    width: '100%',
                    placeholder: 'Pilih Dosen'
                });
            });

            // Reset modal to "Create" mode when manually opened via "Tambah" button
            $('[data-bs-target="#modalDosen"]').not('.btn-edit-dosen').on('click', function () {
                resetModalToCreate();
            });

            // Reset when modal is hidden
            modalElement.on('hidden.bs.modal', function () {
                resetModalToCreate();
            });

            function resetModalToCreate() {
                $('#modalDosenLabel').text('Tambah Dosen Pengajar');
                $('#formDosen').attr('action', '{{ route("kelas.dosen.store") }}');
                $('#formDosenMethod').val('POST');
                $('#btnSubmitDosen').html('<i class="ri-save-line me-1"></i> Simpan Dosen');
                $('#dosenSelectWrapper').removeClass('d-none');
                $('#dosenInfoWrapper').addClass('d-none');
                $('#dosen_id').prop('required', true).val('').trigger('change');
                $('#id_dosen_alias_lokal').val('').trigger('change');
                $('#bobot_sks').val(0);
                $('#jenis_evaluasi').val('');
                $('#jumlah_rencana_pertemuan').val(0);
                $('#jumlah_realisasi_pertemuan').val('');
            }
        });

        // Function to open modal in edit mode
        function openEditDosenModal(data) {
            const baseUrl = '{{ url("admin/kelas-dosen") }}';
            $('#modalDosenLabel').text('Edit Dosen Pengajar');
            $('#formDosen').attr('action', baseUrl + '/' + data.id);
            $('#formDosenMethod').val('PUT');
            $('#btnSubmitDosen').html('<i class="ri-save-line me-1"></i> Simpan Perubahan');

            // Hide dosen select, show info text (dosen tidak boleh diubah)
            $('#dosenSelectWrapper').addClass('d-none');
            $('#dosen_id').prop('required', false);
            $('#dosenInfoWrapper').removeClass('d-none');
            $('#dosenInfoText').val(data.dosen_nama);

            // Populate fields
            $('#id_dosen_alias_lokal').val(data.id_dosen_alias_lokal).trigger('change');
            $('#bobot_sks').val(data.bobot_sks);
            $('#jenis_evaluasi').val(data.jenis_evaluasi);
            $('#jumlah_rencana_pertemuan').val(data.jumlah_rencana_pertemuan);
            $('#jumlah_realisasi_pertemuan').val(data.jumlah_realisasi_pertemuan || '');

            $('#modalDosen').modal('show');
        }
    </script>
@endpush