<!-- Modal Tambah Kolektif Peserta Kelas -->
<div class="modal fade" id="modalKolektifPeserta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalKolektifLabel">Pilih Peserta Kelas Kolektif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.peserta-kelas-kuliah.store-kolektif') }}" method="POST"
                id="form-peserta-kolektif">
                @csrf
                <input type="hidden" name="id_kelas_kuliah" value="{{ $kelasKuliah->id_kelas_kuliah }}">

                <div class="modal-body p-4">
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4 py-3">
                        <div class="small">
                            <i class="ri-information-line me-1"></i> Pilih mahasiswa yang ingin ditambahkan sekaligus ke
                            kelas ini.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mt-3 w-100"
                            id="table-kolektif-mahasiswa">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox" id="checkAllMahasiswa">
                                        </div>
                                    </th>
                                    <th class="text-center">NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>Program Studi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($daftarMahasiswa as $mhsRiwayat)
                                    <tr class="align-middle">
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input check-mahasiswa" type="checkbox"
                                                    name="riwayat_pendidikan_ids[]" value="{{ $mhsRiwayat->id }}"
                                                    id="chk_{{ $mhsRiwayat->id }}">
                                            </div>
                                        </td>
                                        <td class="text-center fw-semibold"><label
                                                class="form-check-label w-100 cursor-pointer"
                                                for="chk_{{ $mhsRiwayat->id }}">{{ $mhsRiwayat->nim ?? '-' }}</label></td>
                                        <td><label class="form-check-label w-100 cursor-pointer"
                                                for="chk_{{ $mhsRiwayat->id }}">{{ $mhsRiwayat->mahasiswa->nama_mahasiswa ?? 'Unknown' }}</label>
                                        </td>
                                        <td><label class="form-check-label text-muted small w-100 cursor-pointer"
                                                for="chk_{{ $mhsRiwayat->id }}">{{ $mhsRiwayat->programStudi->nama_program_studi ?? '-' }}</label>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitKolektif">
                        <i class="ri-save-line me-1"></i> Simpan Terpilih
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize DataTable
        let dt;
        if ($.fn.DataTable.isDataTable('#table-kolektif-mahasiswa')) {
            dt = $('#table-kolektif-mahasiswa').DataTable();
        } else {
            dt = $('#table-kolektif-mahasiswa').DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                    search: "Cari:",
                    lengthMenu: "_MENU_ data per halaman"
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Disable sorting on checkbox column
                ],
                order: [[1, 'asc']], // Default sort by NIM
                dom: '<"row mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end"f>>t<"row mt-3"<"col-md-6"i><"col-md-6 d-flex justify-content-end"p>>',
            });
        }

        const checkAll = document.getElementById('checkAllMahasiswa');

        // Handle "Check All" clicking across all pages in DataTable
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                // Get all rows from DataTable (including those not visible)
                const rows = dt.rows({ search: 'applied' }).nodes();
                const checkboxesConfigured = $('input[type="checkbox"].check-mahasiswa', rows);

                checkboxesConfigured.prop('checked', this.checked);
            });

            // Update Check All status when individual checkboxes change
            // Use event delegation for DataTable compatibility
            $('#table-kolektif-mahasiswa tbody').on('change', 'input[type="checkbox"].check-mahasiswa', function () {
                const allNodes = dt.rows({ search: 'applied' }).nodes();
                const totalCheckboxes = $('input[type="checkbox"].check-mahasiswa', allNodes).length;
                const checkedBoxes = $('input[type="checkbox"].check-mahasiswa:checked', allNodes).length;

                if (totalCheckboxes > 0) {
                    checkAll.checked = (totalCheckboxes === checkedBoxes);
                    checkAll.indeterminate = (checkedBoxes > 0 && checkedBoxes < totalCheckboxes);
                } else {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                }
            });

            // Re-evaluate checkall state on page change/draw
            dt.on('draw', function () {
                const allNodes = dt.rows({ search: 'applied' }).nodes();
                const totalCheckboxes = $('input[type="checkbox"].check-mahasiswa', allNodes).length;
                const checkedBoxes = $('input[type="checkbox"].check-mahasiswa:checked', allNodes).length;

                if (totalCheckboxes > 0) {
                    checkAll.checked = (totalCheckboxes === checkedBoxes);
                    checkAll.indeterminate = (checkedBoxes > 0 && checkedBoxes < totalCheckboxes);
                } else {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                }
            });
        }
    });
</script>