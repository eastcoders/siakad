@extends('layouts.app')

@section('title', 'Dashboard Pegawai')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12 pb-4">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Sistem Informasi /</span> Dashboard Pegawai
                </h4>
            </div>

            <div class="col-12 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="ri-user-smile-line fs-3"></i>
                                </span>
                            </div>
                            <div>
                                <h5 class="card-title mb-1 text-primary">Selamat datang, {{ auth()->user()->name }}!</h5>
                                <small class="text-muted">Anda saat ini login sebagai Pegawai.</small>
                            </div>
                        </div>
                        <p class="mb-4">
                            Ini adalah halaman utama dashboard khusus untuk akun Pegawai. Melalui sidebar di sebelah kiri,
                            Anda dapat mengakses menu-menu yang relevan dengan tanggung jawab dan wewenang operasional Anda
                            sehari-hari, salah satunya seperti menu pengisian Kuisioner AMI.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection