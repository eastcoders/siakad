@extends('layouts.app')

@push('styles')
    <style>
        .form-question-card {
            border-top: 4px solid #696cff;
            border-radius: 8px;
            box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.4);
            margin-bottom: 1.5rem;
            background: #fff;
        }

        .question-title {
            font-size: 1.1rem;
            font-weight: 500;
            color: #32475c;
            margin-bottom: 1rem;
        }

        .rating-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 6px;
        }

        .rating-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .rating-item input[type="radio"] {
            width: 1.5rem;
            height: 1.5rem;
            cursor: pointer;
        }

        .rating-label {
            font-size: 0.85rem;
            color: #697a8d;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="mb-4">
                    <a href="{{ route('jabatan.kuisioner.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="ri-arrow-left-line me-1"></i> Kembali ke Daftar
                    </a>
                    <h3 class="fw-bold mb-1">{{ $kuisioner->judul }}</h3>
                    <p class="text-muted">{{ $kuisioner->deskripsi }}</p>
                </div>

                @include('components.alert')

                <form action="{{ route('jabatan.kuisioner.store', $kuisioner->id) }}" method="POST">
                    @csrf

                    @foreach($kuisioner->pertanyaans as $index => $q)
                        <div class="form-question-card p-4">
                            <div class="question-title">
                                {{ $index + 1 }}. {{ $q->teks_pertanyaan }}
                                <span class="text-danger">*</span>
                            </div>

                            @if($q->tipe_input === 'likert')
                                <div class="rating-group mt-3">
                                    <div class="text-muted small fw-semibold me-2">Sangat Kurang</div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="rating-item">
                                            <input type="radio" class="form-check-input" name="jawaban[{{ $q->id }}][skala]"
                                                value="{{ $i }}" id="q{{ $q->id }}_opt{{ $i }}" required>
                                            <label class="rating-label" for="q{{ $q->id }}_opt{{ $i }}">{{ $i }}</label>
                                        </div>
                                    @endfor
                                    <div class="text-muted small fw-semibold ms-2">Sangat Baik</div>
                                </div>

                            @elseif($q->tipe_input === 'pilihan_ganda')
                                <div class="d-flex flex-column gap-2 mt-2">
                                    @foreach($q->opsi_jawaban as $optIndex => $opsiText)
                                        <div class="form-check custom-option custom-option-basic">
                                            <label class="form-check-label custom-option-content"
                                                for="q{{ $q->id }}_opt{{ $optIndex }}">
                                                <input name="jawaban[{{ $q->id }}][teks]" class="form-check-input" type="radio"
                                                    value="{{ $opsiText }}" id="q{{ $q->id }}_opt{{ $optIndex }}" required>
                                                <span class="custom-option-header">
                                                    <span class="h6 mb-0">{{ $opsiText }}</span>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif($q->tipe_input === 'esai')
                                <div class="mt-2">
                                    <textarea name="jawaban[{{ $q->id }}][teks]" class="form-control" rows="3"
                                        placeholder="Tuliskan masukan / jawaban Anda di sini..." required></textarea>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="card shadow-none bg-transparent mt-4 mb-5 pb-5">
                        <div class="card-body p-0 text-end">
                            <button type="submit" class="btn btn-primary btn-lg"
                                onclick="return confirm('Apakah Anda yakin jawaban sudah sesuai? Jawaban yang dikirim tidak dapat diubah kembali.')">
                                <i class="ri-send-plane-fill me-1"></i> Kirim Jawaban Kuesioner
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection