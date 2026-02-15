@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Data Mahasiswa</h5>
                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label fw-bold">NIM</label>
                        <div class="col-sm-10 d-flex align-items-center">
                            : 2023001
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label fw-bold">Nama</label>
                        <div class="col-sm-10 d-flex align-items-center">
                            : Andi Saputra
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label fw-bold">Prodi</label>
                        <div class="col-sm-10 d-flex align-items-center">
                            : Teknik Informatika
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label fw-bold">Angkatan</label>
                        <div class="col-sm-10 d-flex align-items-center">
                            : 2023
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label fw-bold">Status</label>
                        <div class="col-sm-10 d-flex align-items-center">
                            : <span class="badge bg-label-success">Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection