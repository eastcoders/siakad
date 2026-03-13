@extends('layouts.app')

@section('title', 'Buat Permohonan Surat')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Layanan / Permohonan Surat /</span> Buat Baru
        </h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5 class="card-header">Form Pengajuan Surat</h5>
                    <div class="card-body">
                        <form action="{{ route('mahasiswa.surat.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="tipe_surat">Jenis Surat</label>
                                <div class="col-sm-10">
                                    <select class="form-select @error('tipe_surat') is-invalid @enderror" id="tipe_surat"
                                        name="tipe_surat" required onchange="toggleFormFields()">
                                        <option value="">-- Pilih Jenis Surat --</option>
                                        <option value="aktif_kuliah" {{ old('tipe_surat') == 'aktif_kuliah' ? 'selected' : '' }}>Surat Keterangan Aktif Kuliah</option>
                                        <option value="cuti_kuliah" {{ old('tipe_surat') == 'cuti_kuliah' ? 'selected' : '' }}>Surat Permohonan Cuti Kuliah</option>
                                        <option value="pindah_kelas" {{ old('tipe_surat') == 'pindah_kelas' ? 'selected' : '' }}>Surat Permohonan Pindah Jenis Kelas (Pagi/Sore)</option>
                                        <option value="pindah_pt" {{ old('tipe_surat') == 'pindah_pt' ? 'selected' : '' }}>
                                            Surat Permohonan Pindah Perguruan Tinggi</option>
                                        <option value="pengunduran_diri" {{ old('tipe_surat') == 'pengunduran_diri' ? 'selected' : '' }}>Surat Permohonan Pengunduran Diri</option>
                                        <option value="izin_pkl" {{ old('tipe_surat') == 'izin_pkl' ? 'selected' : '' }}>Surat
                                            Permohonan Izin Tempat PKL</option>
                                        <option value="permintaan_data" {{ old('tipe_surat') == 'permintaan_data' ? 'selected' : '' }}>Surat Permohonan Permintaan Data (PKL/TA)</option>
                                    </select>
                                    @error('tipe_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="id_semester">Semester/Tahun Akademik</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control"
                                        value="{{ getActiveSemester()->nama_semester ?? 'N/A' }}" readonly>
                                    <input type="hidden" name="id_semester" value="{{ getActiveSemesterId() }}">
                                    <small class="text-muted">Periode akademik saat ini.</small>
                                </div>
                            </div>

                            <!-- Fields for Surat Aktif Kuliah -->
                            <div id="fields_aktif_kuliah" style="display: none;">
                                <hr class="my-4">
                                <h6 class="mb-3 text-primary"><i class="ri-user-heart-line me-1"></i> Data Orang Tua / Wali
                                </h6>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="nama_ortu">Nama Lengkap</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control @error('nama_ortu') is-invalid @enderror"
                                            id="nama_ortu" name="nama_ortu" value="{{ old('nama_ortu') }}"
                                            placeholder="Contoh: Budi Santoso">
                                        @error('nama_ortu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="alamat_ortu">Alamat Lengkap</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control @error('alamat_ortu') is-invalid @enderror"
                                            id="alamat_ortu" name="alamat_ortu" rows="2">{{ old('alamat_ortu') }}</textarea>
                                        @error('alamat_ortu') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="pekerjaan_ortu">Pekerjaan</label>
                                    <div class="col-sm-10">
                                        <input type="text"
                                            class="form-control @error('pekerjaan_ortu') is-invalid @enderror"
                                            id="pekerjaan_ortu" name="pekerjaan_ortu" value="{{ old('pekerjaan_ortu') }}"
                                            placeholder="Contoh: Pegawai Negeri Sipil">
                                        @error('pekerjaan_ortu') <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="nip_ortu">NIP/NRP (Opsional)</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="nip_ortu" name="nip_ortu"
                                            value="{{ old('nip_ortu') }}">
                                    </div>
                                    <label class="col-sm-2 col-form-label text-sm-end" for="jabatan_ortu">Jabatan
                                        (Opsional)</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="jabatan_ortu" name="jabatan_ortu"
                                            value="{{ old('jabatan_ortu') }}">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="instansi_ortu">Instansi (Opsional)</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="instansi_ortu" name="instansi_ortu"
                                            value="{{ old('instansi_ortu') }}"
                                            placeholder="Contoh: Dinas Pendidikan Kabupaten X">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="alamat_instansi_ortu">Alamat Instansi
                                        (Opsional)</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="alamat_instansi_ortu" name="alamat_instansi_ortu"
                                            rows="2">{{ old('alamat_instansi_ortu') }}</textarea>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="keperluan">Keperluan Surat</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control @error('keperluan') is-invalid @enderror"
                                            id="keperluan" name="keperluan" value="{{ old('keperluan') }}"
                                            placeholder="Contoh: Untuk pengurusan Tunjangan Gaji / BPJS">
                                        @error('keperluan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fields for Surat Cuti Kuliah -->
                            <div id="fields_cuti_kuliah" style="display: none;">
                                <hr class="my-4">
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="alasan">Alasan Cuti</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control @error('alasan') is-invalid @enderror" id="alasan"
                                            name="alasan" rows="3"
                                            placeholder="Jelaskan alasan pengajuan cuti kuliah Anda...">{{ old('alasan') }}</textarea>
                                        @error('alasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fields for Surat Pindah Kelas -->
                            <div id="fields_pindah_kelas" style="display: none;">
                                <hr class="my-4">
                                <h6 class="mb-3 text-primary"><i class="ri-arrow-left-right-line me-1"></i> Detail Pindah
                                    Jenis Kelas</h6>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Tipe Kelas Saat Ini</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control"
                                            value="{{ auth()->user()->mahasiswa->tipe_kelas ?? '-' }}" readonly>
                                        <small class="text-muted">Sesuai data profil Anda.</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="kelas_tujuan">Tipe Kelas Tujuan</label>
                                    <div class="col-sm-10">
                                        <select class="form-select @error('kelas_tujuan') is-invalid @enderror"
                                            id="kelas_tujuan" name="kelas_tujuan">
                                            <option value="">-- Pilih Tipe Kelas --</option>
                                            @php
                                                $currentType = auth()->user()->mahasiswa->tipe_kelas;
                                                $options = ['Pagi', 'Sore'];
                                            @endphp
                                            @foreach($options as $option)
                                                @if($option !== $currentType)
                                                    <option value="{{ $option }}" {{ old('kelas_tujuan') == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('kelas_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fields for Surat Pindah PT -->
                            <div id="fields_pindah_pt" style="display: none;">
                                <hr class="my-4">
                                <h6 class="mb-3 text-primary"><i class="ri-community-line me-1"></i> Detail Pindah Perguruan
                                    Tinggi</h6>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Instansi Saat Ini</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="Politeknik Sawunggalih Aji" readonly>
                                        <small class="text-muted">Perguruan tinggi asal Anda.</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="id_pt_tujuan">Perguruan Tinggi
                                        Tujuan</label>
                                    <div class="col-sm-10">
                                        <select class="form-select select2 @error('id_pt_tujuan') is-invalid @enderror"
                                            id="id_pt_tujuan" name="id_pt_tujuan">
                                            <option value="">-- Cari Perguruan Tinggi Tujuan --</option>
                                        </select>
                                        @error('id_pt_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="akreditasi_pt_tujuan">Akreditasi PT
                                        Tujuan</label>
                                    <div class="col-sm-10">
                                        <select class="form-select @error('akreditasi_pt_tujuan') is-invalid @enderror"
                                            id="akreditasi_pt_tujuan" name="akreditasi_pt_tujuan">
                                            <option value="">-- Pilih Akreditasi --</option>
                                            <option value="A" {{ old('akreditasi_pt_tujuan') == 'A' ? 'selected' : '' }}>A
                                            </option>
                                            <option value="B" {{ old('akreditasi_pt_tujuan') == 'B' ? 'selected' : '' }}>B
                                            </option>
                                            <option value="C" {{ old('akreditasi_pt_tujuan') == 'C' ? 'selected' : '' }}>C
                                            </option>
                                            <option value="Unggul" {{ old('akreditasi_pt_tujuan') == 'Unggul' ? 'selected' : '' }}>Unggul</option>
                                            <option value="Baik Sekali" {{ old('akreditasi_pt_tujuan') == 'Baik Sekali' ? 'selected' : '' }}>Baik Sekali</option>
                                            <option value="Baik" {{ old('akreditasi_pt_tujuan') == 'Baik' ? 'selected' : '' }}>Baik</option>
                                        </select>
                                        @error('akreditasi_pt_tujuan') <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fields for Surat Pengunduran Diri -->
                            <div id="fields_pengunduran_diri" style="display: none;">
                                <div class="alert alert-warning border-2 d-flex align-items-start" role="alert">
                                    <i class="ri-error-warning-line me-3 ri-24px"></i>
                                    <div>
                                        <h5 class="alert-heading fw-bold mb-1">⚠️ Peringatan Penting</h5>
                                        <p class="mb-0">Anda sedang memuat Permohonan Surat Pengunduran Diri dari Perguruan
                                            Tinggi. Pastikan Anda telah mempertimbangkan keputusan ini dengan matang, karena
                                            pengunduran diri bersifat permanen dan dapat memengaruhi status akademik serta
                                            hak mahasiswa. Jika Anda yakin untuk melanjutkan, silakan lanjutkan proses
                                            permohonan.</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="alamat_undur_diri">Alamat Saat Ini</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control @error('alamat_undur_diri') is-invalid @enderror" 
                                            id="alamat_undur_diri" name="alamat_undur_diri" rows="2"
                                            placeholder="Alamat lengkap Anda saat ini">{{ old('alamat_undur_diri', auth()->user()->mahasiswa->alamat) }}</textarea>
                                        <small class="text-muted">Alamat ini akan dicantumkan dalam surat pengunduran diri.</small>
                                        @error('alamat_undur_diri') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="alasan_undur_diri">Alasan Pengunduran Diri</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control @error('alasan_undur_diri') is-invalid @enderror" 
                                            id="alasan_undur_diri" name="alasan_undur_diri" rows="2"
                                            placeholder="Opsional: Tuliskan alasan Anda mengundurkan diri...">{{ old('alasan_undur_diri') }}</textarea>
                                        @error('alasan_undur_diri') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fields for Izin Tempat PKL -->
                            <div id="fields_izin_pkl" style="display: none;">
                                <hr class="my-4">
                                <h6 class="mb-3 text-primary"><i class="ri-map-pin-line me-1"></i> Detail Lokasi &
                                    Penempatan PKL</h6>

                                @if($isCoveredPKL)
                                    <div class="alert alert-info border-2 d-flex align-items-start mb-4" role="alert">
                                        <i class="ri-information-line me-3 ri-24px"></i>
                                        <div>
                                            <h6 class="alert-heading fw-bold mb-1">Anda Sudah Terdaftar</h6>
                                            <p class="mb-0">Sistem mendeteksi Anda sudah memiliki permohonan PKL aktif atau
                                                terdaftar sebagai partner dalam permohonan teman Anda. Anda tidak perlu
                                                mengajukan surat baru kecuali ada perubahan lokasi.</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="instansi_tujuan">Nama Instansi/Lokasi
                                        PKL</label>
                                    <div class="col-sm-10">
                                        <input type="text"
                                            class="form-control @error('instansi_tujuan') is-invalid @enderror"
                                            id="instansi_tujuan" name="instansi_tujuan" value="{{ old('instansi_tujuan') }}"
                                            placeholder="Contoh: PT. Teknologi Maju Bersama">
                                        @error('instansi_tujuan') <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="pkl_pimpinan">Pimpinan Instansi</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control @error('pkl_pimpinan') is-invalid @enderror"
                                            id="pkl_pimpinan" name="pkl_pimpinan" value="{{ old('pkl_pimpinan') }}"
                                            placeholder="Nama Pimpinan / HRD / Manager">
                                        @error('pkl_pimpinan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="alamat_instansi">Alamat Instansi</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control @error('alamat_instansi') is-invalid @enderror"
                                            id="alamat_instansi" name="alamat_instansi" rows="2"
                                            placeholder="Alamat lengkap lokasi PKL">{{ old('alamat_instansi') }}</textarea>
                                        @error('alamat_instansi') <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="tgl_mulai">Periode Penempatan</label>
                                    <div class="col-sm-5">
                                        <div class="input-group">
                                            <span class="input-group-text">Mulai</span>
                                            <input type="date" class="form-control @error('tgl_mulai') is-invalid @enderror"
                                                id="tgl_mulai" name="tgl_mulai" value="{{ old('tgl_mulai') }}">
                                        </div>
                                        @error('tgl_mulai') <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="input-group">
                                            <span class="input-group-text">Selesai</span>
                                            <input type="date"
                                                class="form-control @error('tgl_selesai') is-invalid @enderror"
                                                id="tgl_selesai" name="tgl_selesai" value="{{ old('tgl_selesai') }}">
                                        </div>
                                        @error('tgl_selesai') <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="partners">Teman/Partner (Opsional)</label>
                                    <div class="col-sm-10">
                                        <select
                                            class="form-select select2-mahasiswa @error('partners') is-invalid @enderror"
                                            id="partners" name="partners[]" multiple>
                                        </select>
                                        <small class="text-muted">Gunakan fitur ini jika Anda PKL berkelompok dengan teman
                                            (Cari berdasarkan Nama atau NIM).</small>
                                        @error('partners') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fields for Permintaan Data -->
                            <div id="fields_permintaan_data" style="display: none;">
                                <hr class="my-4">
                                <h6 class="mb-3 text-primary"><i class="ri-database-2-line me-1"></i> Detail Permintaan Data (PKL/TA)</h6>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="peruntukan">Peruntukan</label>
                                    <div class="col-sm-10">
                                        <div class="form-check form-check-inline mt-2">
                                            <input class="form-check-input" type="radio" name="peruntukan" id="peruntukan_pkl" value="PKL" {{ old('peruntukan') == 'PKL' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="peruntukan_pkl">Laporan PKL</label>
                                        </div>
                                        <div class="form-check form-check-inline mt-2">
                                            <input class="form-check-input" type="radio" name="peruntukan" id="peruntukan_ta" value="Tugas Akhir" {{ old('peruntukan') == 'Tugas Akhir' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="peruntukan_ta">Tugas Akhir (TA)</label>
                                        </div>
                                        @error('peruntukan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="instansi_tujuan_data">Nama Instansi</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control @error('instansi_tujuan_data') is-invalid @enderror" 
                                            id="instansi_tujuan_data" name="instansi_tujuan_data" value="{{ old('instansi_tujuan_data') }}"
                                            placeholder="Nama instansi tempat pengambilan data">
                                        @error('instansi_tujuan_data') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="pimpinan_instansi">Pimpinan Instansi</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control @error('pimpinan_instansi') is-invalid @enderror" 
                                            id="pimpinan_instansi" name="pimpinan_instansi" value="{{ old('pimpinan_instansi') }}"
                                            placeholder="Nama Pimpinan / HRD / Manager di Instansi">
                                        @error('pimpinan_instansi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="alamat_instansi_data">Alamat Instansi</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control @error('alamat_instansi_data') is-invalid @enderror" 
                                            id="alamat_instansi_data" name="alamat_instansi_data" rows="2"
                                            placeholder="Alamat lengkap instansi">{{ old('alamat_instansi_data') }}</textarea>
                                        @error('alamat_instansi_data') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="judul_laporan">Judul Laporan/TA</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control @error('judul_laporan') is-invalid @enderror" 
                                            id="judul_laporan" name="judul_laporan" value="{{ old('judul_laporan') }}"
                                            placeholder="Masukkan judul laporan atau Tugas Akhir Anda">
                                        @error('judul_laporan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="data_dibutuhkan">List Data yang Dibutuhkan</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control @error('data_dibutuhkan') is-invalid @enderror" 
                                            id="data_dibutuhkan" name="data_dibutuhkan" rows="4"
                                            placeholder="Sebutkan data apa saja yang ingin diminta (Contoh: Data Keuangan 2023, Profil Perusahaan, dll)">{{ old('data_dibutuhkan') }}</textarea>
                                        <small class="text-muted">Gunakan baris baru atau nomor untuk daftar data yang banyak.</small>
                                        @error('data_dibutuhkan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label" for="partners_data">Teman/Partner (Opsional)</label>
                                    <div class="col-sm-10">
                                        <select
                                            class="form-select select2-mahasiswa @error('partners_data') is-invalid @enderror"
                                            id="partners_data" name="partners_data[]" multiple>
                                        </select>
                                        <small class="text-muted">Gunakan fitur ini jika Anda mengambil data secara berkelompok (Cari berdasarkan Nama atau NIM).</small>
                                        @error('partners_data') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row justify-content-end">
                                <div class="col-sm-10">
                                    <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                                    <a href="{{ route('mahasiswa.surat.index') }}"
                                        class="btn btn-outline-secondary">Batal</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
        <script>
            function toggleFormFields() {
                const type = document.getElementById('tipe_surat').value;
                const aktifFields = document.getElementById('fields_aktif_kuliah');
                const cutiFields = document.getElementById('fields_cuti_kuliah');
                const pindahFields = document.getElementById('fields_pindah_kelas');
                const pindahPtFields = document.getElementById('fields_pindah_pt');
                const resignationFields = document.getElementById('fields_pengunduran_diri');
                const pklFields = document.getElementById('fields_izin_pkl');
                const dataFields = document.getElementById('fields_permintaan_data');

                aktifFields.style.display = (type === 'aktif_kuliah') ? 'block' : 'none';
                cutiFields.style.display = (type === 'cuti_kuliah') ? 'block' : 'none';
                pindahFields.style.display = (type === 'pindah_kelas') ? 'block' : 'none';
                pindahPtFields.style.display = (type === 'pindah_pt') ? 'block' : 'none';
                resignationFields.style.display = (type === 'pengunduran_diri') ? 'block' : 'none';
                pklFields.style.display = (type === 'izin_pkl') ? 'block' : 'none';
                dataFields.style.display = (type === 'permintaan_data') ? 'block' : 'none';

                // Initialize Select2 based on type
                if (type === 'pindah_pt') {
                    initSelect2PT();
                } else if (type === 'izin_pkl' || type === 'permintaan_data') {
                    initSelect2Mahasiswa();
                }
            }

            function initSelect2PT() {
                $('.select2').each(function () {
                    $(this).select2({
                        placeholder: "Cari Perguruan Tinggi...",
                        allowClear: true,
                        dropdownParent: $(this).parent(),
                        ajax: {
                            url: "{{ route('mahasiswa.surat.search-pt') }}",
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 3
                    });
                });
            }

            function initSelect2Mahasiswa() {
                $('.select2-mahasiswa').each(function () {
                    $(this).select2({
                        placeholder: "Cari Teman/Partner (NIM atau Nama)...",
                        allowClear: true,
                        dropdownParent: $(this).parent(),
                        ajax: {
                            url: "{{ route('mahasiswa.surat.search-mahasiswa') }}",
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 3
                    });
                });
            }

            // Run on load to handle old input
            window.onload = toggleFormFields;
        </script>
    @endpush
@endsection