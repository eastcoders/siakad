@extends('layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Jabatan /</span> Kuisioner AMI
        </h4>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <div class="avatar avatar-xl bg-label-primary p-2">
                                <i class="ri-survey-line ri-40px"></i>
                            </div>
                            <div class="button-wrapper">
                                <h5 class="mb-1">Audit Mutu Internal (AMI)</h5>
                                <p class="text-muted mb-0">
                                    Partisipasi Anda sangat penting untuk meningkatkan kualitas akademik dan layanan di
                                    SIA-POLSA.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @forelse($kuisioners as $k)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 {{ $k->is_done ? 'border-success' : 'border-primary' }} border-top border-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-label-{{ $k->is_done ? 'success' : 'primary' }}">
                                    {{ $k->is_done ? 'Sudah Diisi' : 'Perlu Diisi' }}
                                </span>
                                <small class="text-muted">Semester {{ $k->semester->nama_semester }}</small>
                            </div>
                            <h5 class="card-title">{{ $k->judul }}</h5>
                            <p class="card-text text-muted small">
                                {{ Str::limit($k->deskripsi, 100) }}
                            </p>

                            <div class="mt-4">
                                @if($k->is_done)
                                    <button class="btn btn-label-success w-100" disabled>
                                        <i class="ri-checkbox-circle-line me-1"></i> Selesai
                                    </button>
                                @else
                                    <a href="{{ route('jabatan.kuisioner.show', $k->id) }}" class="btn btn-primary w-100">
                                        <i class="ri-edit-line me-1"></i> Mulai Mengisi
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="mb-3">
                        <i class="ri-survey-line ri-64px text-muted opacity-50"></i>
                    </div>
                    <h5>Tidak ada Kuesioner AMI aktif saat ini</h5>
                    <p class="text-muted">Pantau terus pengumuman untuk jadwal pengisian kuesioner berikutnya.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection