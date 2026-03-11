@extends('layouts.app')

@section('title', 'Validasi Surat Mahasiswa')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Kaprodi /</span> Validasi Surat
        </h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Permohonan Surat Prodi</h5>
                <form action="{{ route('kaprodi.surat.index') }}" method="GET" class="d-flex align-items-center">
                    <label for="id_semester" class="me-2 mb-0" style="white-space: nowrap;">Semester:</label>
                    <select name="id_semester" id="id_semester" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">-- Semua Semester --</option>
                        @foreach(\App\Models\Semester::orderBy('id_semester', 'desc')->get() as $smt)
                            <option value="{{ $smt->id_semester }}" {{ request('id_semester') == $smt->id_semester ? 'selected' : '' }}>
                                {{ $smt->nama_semester }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body">
                <x-surat.table :surats="$surats" role="kaprodi" />
            </div>
        </div>
    </div>
@endsection