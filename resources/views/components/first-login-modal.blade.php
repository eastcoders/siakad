@if(Auth::check() && Auth::user()->is_first_login && !Auth::user()->hasRole('admin'))
<div class="modal fade" id="firstLoginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="firstLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title d-flex align-items-center" id="firstLoginModalLabel">
                    <i class="ti ti-shield-lock me-2 fs-3"></i>
                    Keamanan & Aktivasi Akun
                </h5>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info d-flex align-items-start mb-4 border-0 shadow-sm" role="alert">
                    <i class="ti ti-info-circle me-2 fs-4 mt-1"></i>
                    <div>
                        <strong>Selamat Datang!</strong> Demi keamanan akun Anda, silakan ganti password default dan perbarui data kontak untuk keperluan notifikasi aplikasi.
                    </div>
                </div>

                <form id="firstLoginForm" action="{{ route('first-login.update') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Aktif</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                <input type="email" name="email" class="form-control" value="{{ Auth::user()->email }}" placeholder="Masukkan email aktif" required>
                            </div>
                            <div class="form-text">Gunakan email yang sering Anda buka.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor WhatsApp</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-brand-whatsapp"></i></span>
                                <input type="text" name="whatsapp" class="form-control" 
                                    value="{{ Auth::user()->hasRole('Mahasiswa') ? Auth::user()->mahasiswa?->whatsapp : (Auth::user()->hasRole('Dosen') ? Auth::user()->dosen?->whatsapp : '') }}" 
                                    placeholder="Contoh: 08123456789" required>
                            </div>
                            <div class="form-text">Contoh format: 08123456789.</div>
                        </div>
                        <div class="col-12 mt-4">
                            <hr class="my-2">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 small">Ganti Password</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password Baru</label>
                            <div class="input-group input-group-merge form-password-toggle">
                                <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Minimum 8 karakter" required>
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Konfirmasi Password</label>
                            <div class="input-group input-group-merge form-password-toggle">
                                <span class="input-group-text"><i class="ti ti-lock-check"></i></span>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru" required>
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 text-end">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5">
                            <i class="ti ti-device-floppy me-2"></i>
                            Simpan & Masuk Ke Dashboard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var firstLoginModal = new bootstrap.Modal(document.getElementById('firstLoginModal'));
        firstLoginModal.show();
    });
</script>

<style>
    #firstLoginModal {
        z-index: 1090 !important; /* Above sidebars if any */
    }
    .modal-backdrop.show {
        opacity: 0.8 !important;
        backdrop-filter: blur(5px);
    }
</style>
@endif
