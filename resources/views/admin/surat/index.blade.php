@extends('layouts.app')

@section('title', 'Persetujuan Surat Mahasiswa')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Akademik /</span> Persetujuan Surat
        </h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Daftar Permohonan Surat</h5>
                    <form action="{{ route('admin.surat-approval.index') }}" method="GET" class="d-flex align-items-center">
                        <input type="hidden" name="status" value="{{ $status }}">
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
                <div class="nav-align-top">
                    <ul class="nav nav-tabs nav-fill" role="tablist">
                        <li class="nav-item">
                            <a href="{{ route('admin.surat-approval.index', ['status' => 'validasi']) }}"
                                class="nav-link {{ $status == 'validasi' ? 'active' : '' }}">
                                <i class="ri-check-double-line me-1"></i> Divalidasi Kaprodi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.surat-approval.index', ['status' => 'pending']) }}"
                                class="nav-link {{ $status == 'pending' ? 'active' : '' }}">
                                <i class="ri-time-line me-1"></i> Menunggu Kaprodi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.surat-approval.index', ['status' => 'disetujui']) }}"
                                class="nav-link {{ $status == 'disetujui' ? 'active' : '' }}">
                                <i class="ri-thumb-up-line me-1"></i> Disetujui
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.surat-approval.index', ['status' => 'selesai']) }}"
                                class="nav-link {{ $status == 'selesai' ? 'active' : '' }}">
                                <i class="ri-checkbox-circle-line me-1"></i> Selesai
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.surat-approval.index', ['status' => 'ditolak']) }}"
                                class="nav-link {{ $status == 'ditolak' ? 'active' : '' }}">
                                <i class="ri-close-circle-line me-1"></i> Ditolak
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body mt-3">
                <x-surat.table :surats="$surats" role="admin" />
            </div>
        </div>
    </div>
@endsection