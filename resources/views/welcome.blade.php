<!doctype html>
<html lang="id" class="light-style layout-navbar-fixed layout-wide" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}/" data-template="front-pages-no-customizer" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>SIAP - Portal Akademik Politeknik Sawunggalih Aji</title>
    <meta name="description" content="Portal Akademik Digital Politeknik Sawunggalih Aji" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/remixicon/remixicon.css') }}" />

    <!-- Menu waves for no-customizer fix -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/front-page.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/nouislider/nouislider.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/front-page-landing.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/front-config.js') }}"></script>
    <style>
        .app-brand-logo img {
            height: 45px;
            width: auto;
        }

        .landing-hero {
            padding-top: 10rem !important;
            padding-bottom: 8rem !important;
        }

        .hero-title {
            letter-spacing: -1px;
            line-height: 1.1;
        }

        .text-gradient-primary {
            background: linear-gradient(135deg, #666cff 0%, #a3a7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .animation-float {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;
        }

        .btn-hover {
            transition: all 0.2s ease;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }

        .footer-link-item {
            transition: all 0.2s ease;
        }

        .footer-link-item:hover {
            padding-left: 5px;
            color: var(--bs-primary) !important;
        }

        @media (max-width: 991.98px) {
            .landing-hero {
                padding-top: 8rem !important;
                text-align: center;
            }

            .hero-illustration-img {
                margin: 4rem auto 0;
            }

            .hero-actions {
                justify-content: center;
            }
        }

        /* Navbar Scrolled Effect (Full Width) */
        .layout-navbar {
            transition: all 0.3s ease;
            background-color: transparent;
        }

        .layout-navbar.navbar-active {
            background-color: #fff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        }

        .navbar.landing-navbar {
            background-color: transparent !important;
            box-shadow: none !important;
            border: none !important;
        }
    </style>
</head>

<body>
    <script src="{{ asset('assets/vendor/js/dropdown-hover.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/mega-dropdown.js') }}"></script>

    <!-- Navbar: Start -->
    <nav class="layout-navbar shadow-none py-0">
        <div class="container">
            <div class="navbar navbar-expand-lg landing-navbar border-top-0 px-3 px-lg-4">
                <!-- Menu logo wrapper: Start -->
                <div class="navbar-brand app-brand demo d-flex py-0 py-lg-2 me-0">
                    <a href="{{ url('/') }}" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <img src="{{ asset('img/logo.png') }}" alt="Logo SIAP">
                        </span>
                    </a>
                </div>
                <!-- Menu logo wrapper: End -->

                <!-- Toolbar: Start -->
                <ul class="navbar-nav flex-row align-items-center ms-auto">
                    <li>
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="btn btn-primary px-4 btn-hover shadow-md rounded-pill">
                                <i class="ri-dashboard-line me-2"></i>
                                <span class="d-none d-md-inline">Ke Dashboard</span>
                                <span class="d-md-none">Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary px-4 btn-hover shadow-md rounded-pill">
                                <i class="ri-user-unfollow-line me-2"></i>
                                <span class="d-none d-md-inline">Portal Login</span>
                                <span class="d-md-none">Login</span>
                            </a>
                        @endauth
                    </li>
                </ul>
                <!-- Toolbar: End -->
            </div>
        </div>
    </nav>
    <!-- Navbar: End -->

    <!-- Sections: Start -->
    <div data-bs-spy="scroll" class="scrollspy-example">

        <!-- Hero: Start -->
        <section id="landingHero" class="section-py landing-hero position-relative bg-white overflow-hidden">
            <div class="container">
                <div class="row align-items-center g-10">
                    <!-- Text Column (LHS) -->
                    <div class="col-lg-6 order-2 order-lg-1">
                        <div class="hero-content mt-lg-0 mt-5">
                            <div
                                class="d-inline-flex align-items-center bg-label-primary rounded-pill mb-4 px-4 py-2 border border-primary border-opacity-10">
                                <i class="ri-graduation-cap-line me-2 fs-5"></i>
                                <span class="fw-bold text-uppercase fs-tiny tracking-wider">Tahun Akademik
                                    {{ getActiveSemester()->nama_semester ?? '2026/2027' }}</span>
                            </div>

                            <h1 class="display-3 fw-bolder text-heading hero-title mb-4">
                                Transformasi Akademik <br>
                                <span class="text-gradient-primary">Digital Bersama SIAP</span>
                            </h1>

                            <!-- CTA Actions -->
                            <div class="d-flex gap-3 flex-wrap hero-actions mb-8">
                                @auth
                                    <a href="{{ route('dashboard') }}"
                                        class="btn btn-primary btn-lg px-5 py-3 btn-hover shadow-lg rounded-pill">
                                        <i class="ri-dashboard-line me-2 fs-4"></i>Ke Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="btn btn-primary btn-lg px-5 py-3 btn-hover shadow-lg rounded-pill">
                                        <i class="ri-shield-user-line me-2 fs-4"></i>Masuk Portal
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Image Column (RHS) -->
                    <div class="col-lg-6 order-1 order-lg-2">
                        <div class="hero-illustration-wrapper text-center">
                            <img src="{{ asset('img/hero-img.png') }}" alt="SIAP Hero Illustration"
                                class="img-fluid hero-illustration-img animation-float" style="max-height: 500px;">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Hero: End -->

        <!-- Announcement Section: Start -->
        <section id="landingAnnouncement" class="section-py bg-body border-top">
            <div class="container pb-10">
                <div class="text-center mb-12">
                    <h6 class="text-primary text-uppercase fw-bold ls-1 mb-2">Pusat Informasi</h6>
                    <h2 class="display-5 fw-bold text-heading">Pengumuman Terbaru</h2>
                    <p class="text-muted fs-5">Tetap terhubung dengan berita dan informasi akademik terkini.</p>
                </div>

                <div class="row gy-6">
                    @php
                        $filteredAnnouncements = \App\Models\Pengumuman::aktif()
                            ->where('judul', 'not like', '%AMI%')
                            ->latest()
                            ->take(6)
                            ->get();
                    @endphp
                    @forelse($filteredAnnouncements as $announcement)
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card h-100 shadow-sm border-0 card-hover bg-white overflow-hidden">
                                <div class="card-body p-6 d-flex flex-column">
                                    <!-- Meta Info -->
                                    <div
                                        class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-light">
                                        <span class="badge bg-label-primary rounded-pill px-3">
                                            {{ $announcement->category ?? 'Akademik' }}
                                        </span>
                                        <div class="text-muted d-flex align-items-center small fw-medium">
                                            <i class="ri-calendar-event-line me-1"></i>
                                            {{ $announcement->created_at?->format('d M Y') ?? 'Terbaru' }}
                                        </div>
                                    </div>

                                    <h5 class="card-title text-heading fw-bold mb-3 ls-n05 lh-base">
                                        {{ $announcement->title }}
                                    </h5>

                                    <p class="card-text text-muted flex-grow-1 mb-6 lh-relaxed">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($announcement->content), 120) }}
                                    </p>

                                    <div class="mt-auto">
                                        <a href="{{ $announcement->url ?? '#' }}"
                                            class="btn btn-link p-0 fw-bold text-primary text-decoration-none d-inline-flex align-items-center">
                                            Baca Selengkapnya
                                            <i class="ri-arrow-right-line ms-2 transition-all"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-10 bg-white rounded-4 shadow-sm">
                            <div class="avatar avatar-xl mx-auto mb-4 bg-label-secondary rounded-pill">
                                <span class="avatar-initial">
                                    <i class="ri-notification-3-line ri-42px"></i>
                                </span>
                            </div>
                            <h4 class="text-heading fw-bold mb-2">Belum Ada Pengumuman</h4>
                            <p class="text-muted mb-0 mx-auto" style="max-width: 400px;">
                                Saat ini belum ada pengumuman terbaru yang dipublikasikan. Silakan kembali lagi nanti untuk
                                informasi terkini.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
        <!-- Announcement Section: End -->

    </div>
    <!-- / Sections: End -->

    <!-- Footer: Start -->
    <footer class="landing-footer bg-dark pt-12">
        <div class="footer-top border-bottom border-secondary border-opacity-10 pb-10">
            <div class="container">
                <div class="row gy-10 gx-lg-12">

                    <!-- Left: Description -->
                    <div class="col-lg-5">
                        <div class="footer-brand mb-6 d-flex align-items-center">
                            <img src="{{ asset('img/logo.png') }}" alt="Logo SIAP" class="me-3" style="height: 40px;">
                            <h3 class="text-white fw-bold mb-0">SIAP</h3>
                        </div>
                        <p class="footer-text text-light text-opacity-75 mb-6 pe-lg-10 lh-lg">
                            SIAP (Sistem Informasi Akademik Politeknik Sawunggalih Aji) adalah platform terintegrasi
                            untuk pengelolaan data dan layanan akademik.
                        </p>
                    </div>

                    <!-- Middle: Quick Links -->
                    <div class="col-lg-3 col-md-6">
                        <h6 class="text-white fw-bold text-uppercase ls-1 mb-6">Tautan Cepat</h6>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                            <li>
                                <a href="{{ route('login') }}"
                                    class="footer-link-item text-light text-opacity-75 text-decoration-none d-flex align-items-center">
                                    <i class="ri-arrow-right-s-line me-2"></i>Portal Login
                                </a>
                            </li>
                            <li>
                                <a href="https://pmb.polsa.ac.id" target="_blank"
                                    class="footer-link-item text-light text-opacity-75 text-decoration-none d-flex align-items-center">
                                    <i class="ri-arrow-right-s-line me-2"></i>Pendaftaran Mahasiswa Baru
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Right: Contact -->
                    <div class="col-lg-4 col-md-6">
                        <h6 class="text-white fw-bold text-uppercase ls-1 mb-6">Kontak Kampus</h6>
                        <div class="d-flex align-items-start mb-4">
                            <i class="ri-map-pin-2-line text-primary me-3 mt-1 fs-5"></i>
                            <p class="text-light text-opacity-75 mb-0">
                                Jl. Let. Jend. S. Parman No. 21, Kutoarjo, Purworejo, Jawa Tengah.
                            </p>
                        </div>
                        <div class="d-flex align-items-center mb-4">
                            <i class="ri-mail-send-line text-primary me-3 fs-5"></i>
                            <p class="text-light text-opacity-75 mb-0">info@polsa.ac.id</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="footer-copyright py-6 bg-black bg-opacity-25">
            <div class="container">
                <div class="d-flex flex-wrap justify-content-between align-items-center flex-column flex-md-row gap-4">
                    <p class="text-light text-opacity-50 mb-0 small">
                        ©
                        <script>document.write(new Date().getFullYear());</script>
                        <strong class="text-white">Politeknik Sawunggalih Aji</strong>.
                    </p>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer: End -->

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/nouislider/nouislider.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/front-main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/js/front-page-landing.js') }}"></script>

    <script>
        // Navbar scroll effect handler
        (function () {
            const layoutNavbar = document.querySelector('.layout-navbar');
            const handleScroll = () => {
                if (window.scrollY > 10) {
                    layoutNavbar.classList.add('navbar-active');
                } else {
                    layoutNavbar.classList.remove('navbar-active');
                }
            };
            window.addEventListener('scroll', handleScroll);
            handleScroll(); // Check on load
        })();
    </script>
</body>

</html>