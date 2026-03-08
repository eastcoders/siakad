@extends('layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <a href="{{ route('dosen.kuisioner.index') }}" class="text-muted fw-light">Kuesioner /</a>
                    <a href="{{ route('dosen.kuisioner.show', $kuisioner->id) }}" class="text-muted fw-light">Laporan /</a>
                    Detail Masukan Esai
                </h4>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge bg-label-primary fs-6">{{ $kuisioner->judul }}</span>
                    <span class="badge bg-label-info">{{ $kuisioner->target_ujian }}</span>
                    <span class="text-muted fs-tiny"><i class="ri-user-smile-line me-1"></i>{{ $totalMahasiswa }} Mahasiswa
                        Berpartisipasi</span>
                </div>
            </div>
            <div>
                <a href="{{ route('dosen.kuisioner.show', $kuisioner->id) }}" class="btn btn-outline-secondary">
                    <i class="ri-arrow-left-line me-1"></i> Kembali ke Rekap
                </a>
            </div>
        </div>

        @forelse($pertanyaans as $p)
            <div class="card mb-4 border-top border-primary border-3">
                <div class="card-header bg-label-light py-3 border-bottom">
                    <h5 class="mb-0 text-dark">
                        <i class="ri-question-line me-2 text-primary"></i>{{ $p->teks_pertanyaan }}
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="list-group list-group-flush">
                        @forelse($p->jawaban_esai as $jawaban)
                            <div class="list-group-item py-4 px-0 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="avatar me-3 mt-1">
                                        <span class="avatar-initial rounded-circle bg-label-secondary shadow-sm">
                                            <i class="ri-chat-1-line"></i>
                                        </span>
                                    </div>
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            @if($kuisioner->tipe === 'dosen')
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <span class="badge bg-label-primary">Dosen:
                                                        {{ $jawaban->submission->dosen->nama ?? 'N/A' }}</span>
                                                </div>
                                            @else
                                                <span class="badge bg-label-info">Respon Pelayanan</span>
                                            @endif
                                            <small class="text-muted fst-italic">{{ $jawaban->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="bg-light p-3 rounded shadow-sm border-start border-primary border-4 ms-1">
                                            <p class="mb-0 text-dark fs-5 fw-normal" style="line-height: 1.6;">
                                                "{{ $jawaban->jawaban_teks }}"</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="ri-chat-delete-line ri-3x text-light mb-2"></i>
                                <p class="text-muted mb-0">Belum ada jawaban teks (esai) untuk pertanyaan ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ri-error-warning-line ri-4x text-warning mb-3"></i>
                    <h4>Tidak Ada Pertanyaan Esai</h4>
                    <p class="text-muted">Formulir kuesioner ini tidak memiliki instrumen pertanyaan tipe Esai.</p>
                    <a href="{{ route('dosen.kuisioner.show', $kuisioner->id) }}" class="btn btn-primary mt-2">Kembali ke
                        Laporan</a>
                </div>
            </div>
        @endforelse
    </div>
@endsection