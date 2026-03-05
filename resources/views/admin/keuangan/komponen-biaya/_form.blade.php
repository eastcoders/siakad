@php $item = $item ?? null; @endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Kode Komponen <span class="text-danger">*</span></label>
        <input type="text" name="kode_komponen" class="form-control"
            value="{{ $item->kode_komponen ?? old('kode_komponen') }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label">Nama Komponen <span class="text-danger">*</span></label>
        <input type="text" name="nama_komponen" class="form-control"
            value="{{ $item->nama_komponen ?? old('nama_komponen') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Kategori <span class="text-danger">*</span></label>
        <select name="kategori" class="form-select" required>
            @foreach(\App\Models\KomponenBiaya::KATEGORI_OPTIONS as $val => $text)
                <option value="{{ $val }}" {{ ($item->kategori ?? old('kategori')) == $val ? 'selected' : '' }}>{{ $text }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Nominal Standar (Rp) <span class="text-danger">*</span></label>
        <input type="number" name="nominal_standar" class="form-control" step="0.01" min="0"
            value="{{ $item->nominal_standar ?? old('nominal_standar', 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Khusus Prodi</label>
        <select name="id_prodi" class="form-select">
            <option value="">Semua Prodi (Global)</option>
            @foreach($prodis as $p)
                <option value="{{ $p->id_prodi }}" {{ ($item->id_prodi ?? old('id_prodi')) == $p->id_prodi ? 'selected' : '' }}>
                    {{ $p->nama_program_studi }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12">
        <label class="form-label">Tahun Angkatan</label>
        <input type="text" name="tahun_angkatan" class="form-control" maxlength="4"
            placeholder="Misal: 2023 (kosongkan = semua)" value="{{ $item->tahun_angkatan ?? old('tahun_angkatan') }}">
        <small class="text-muted">Kosongkan jika berlaku untuk semua angkatan.</small>
    </div>
    <div class="col-md-4">
        <div class="form-check mt-4">
            <input type="hidden" name="is_wajib_krs" value="0">
            <input class="form-check-input" type="checkbox" name="is_wajib_krs" value="1"
                id="is_wajib_krs_{{ $item->id ?? 'new' }}" {{ ($item->is_wajib_krs ?? old('is_wajib_krs')) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_wajib_krs_{{ $item->id ?? 'new' }}">
                Wajib Lunas untuk <strong>KRS</strong>
            </label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check mt-4">
            <input type="hidden" name="is_wajib_ujian" value="0">
            <input class="form-check-input" type="checkbox" name="is_wajib_ujian" value="1"
                id="is_wajib_ujian_{{ $item->id ?? 'new' }}" {{ ($item->is_wajib_ujian ?? old('is_wajib_ujian')) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_wajib_ujian_{{ $item->id ?? 'new' }}">
                Wajib Lunas untuk <strong>Ujian</strong>
            </label>
        </div>
    </div>
</div>