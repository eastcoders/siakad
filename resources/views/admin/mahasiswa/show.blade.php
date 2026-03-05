@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Mahasiswa /</span> Detail Mahasiswa</h4>
                @if(isset($isKrsEligible) && isset($isUjianEligible))
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        @if(!$isKrsEligible)
                            <span class="badge bg-label-danger fs-6"><i class="ri-forbid-2-line me-1"></i> Terblokir KRS</span>
                        @else
                            <span class="badge bg-label-success fs-6"><i class="ri-check-double-line me-1"></i> KRS Aktif</span>
                        @endif

                        @if(!$isUjianEligible)
                            <span class="badge bg-label-danger fs-6"><i class="ri-forbid-2-line me-1"></i> Terblokir Ujian</span>
                        @else
                            <span class="badge bg-label-success fs-6"><i class="ri-check-double-line me-1"></i> Ujian Aktif</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Submenu -->
        <div class="col-12 mb-4">
            @include('admin.mahasiswa.partials._submenu')
        </div>

        <!-- Content based on Route -->
        @if(request()->routeIs('admin.mahasiswa.detail'))
            @include('admin.mahasiswa.partials._detail')
        @elseif(request()->routeIs('admin.mahasiswa.histori'))
            <div class="col-12 mb-4">
                @include('admin.mahasiswa.partials._histori')
            </div>
        @elseif(request()->routeIs('admin.mahasiswa.krs'))
            <div class="col-12 mb-4">
                @include('admin.mahasiswa.partials._krs')
            </div>
        @elseif(request()->routeIs('admin.mahasiswa.akun'))
            <div class="col-12 mb-4">
                @include('admin.mahasiswa.partials._akun')
            </div>
        @endif

    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script>
        $(function () {
            // Initialize Flatpickr if present
            if ($('.flatpickr-input').length) {
                $('.flatpickr-input').flatpickr({
                    dateFormat: 'Y-m-d',
                    monthSelectorType: 'static'
                });
            }
        });
    </script>
    {{-- Stack additional scripts from partials if needed --}}
    @stack('partial_scripts')
@endpush