<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Diskominfo — {{ config('app.name') }}</title>

  {{-- Bootstrap & Icons (LOKAL) --}}
  <link rel="stylesheet" href="{{ asset('weblab/css/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/bootstrap-icons.css') }}">

  {{-- Font --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

  <style>
    :root { --dk-blue:#0d6efd; --dk-dark:#0b1220; }
    body { font-family:'Poppins',sans-serif; background:#ffffff; }
    .hero {
      background:
        radial-gradient(1100px circle at 12% 10%, rgba(13,110,253,.14), transparent 40%),
        radial-gradient(900px circle at 90% 35%, rgba(25,135,84,.10), transparent 45%),
        linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
      border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .brand-dot { width:10px; height:10px; border-radius:999px; background:var(--dk-blue); display:inline-block; }
    .mini-card { border:1px solid rgba(0,0,0,.06); }
    html { scroll-behavior: smooth; }
  </style>
</head>

<body>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg bg-white">
  <div class="container py-2">
    <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="{{ route('landing') }}">
      <span class="brand"></span>
      <span>Diskominfo</span>
    </a>

    <div class="ms-auto d-flex align-items-center gap-2">
      <a href="{{ route('login') }}" class="btn btn-outline-primary">
        <i class="bi bi-box-arrow-in-right me-1"></i> Login
      </a>
    </div>
  </div>
</nav>

{{-- Hero --}}
<header class="hero py-5">
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-9 text-center">
        <p class="text-uppercase text-muted small mb-2">Dinas Komunikasi dan Informatika</p>

        <h1 class="fw-bold mb-3" style="color:var(--dk-dark);">
          Informasi, layanan, dan transformasi digital untuk masyarakat.
        </h1>

        <p class="text-muted mb-4">
          Diskominfo mendukung keterbukaan informasi publik, penguatan layanan TIK,
          serta pengelolaan data dan komunikasi pemerintah daerah.
        </p>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
          {{-- Tombol scroll (bukan pindah halaman) --}}
          <a href="#tentang" class="btn btn-primary btn-lg">
            <i class="bi bi-arrow-down-circle me-1"></i> Lihat Informasi
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

{{-- Tentang --}}
<section id="tentang" class="py-5">
  <div class="container">
    <div class="row g-4 align-items-start">
      <div class="col-lg-6">
        <h2 class="h4 fw-bold mb-2">Tentang Diskominfo</h2>
        <p class="text-muted mb-0">
          Diskominfo berperan dalam pengelolaan komunikasi publik, layanan informatika,
          statistik sektoral, dan keamanan informasi untuk mendukung tata kelola pemerintahan yang efektif.
        </p>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="p-3 rounded-3 mini-card h-100">
              <div class="fw-semibold"><i class="bi bi-megaphone me-2"></i>Komunikasi Publik</div>
              <div class="text-muted small">Informasi resmi dan layanan publik.</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded-3 mini-card h-100">
              <div class="fw-semibold"><i class="bi bi-diagram-3 me-2"></i>Informatika</div>
              <div class="text-muted small">Dukungan aplikasi & infrastruktur TIK.</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded-3 mini-card h-100">
              <div class="fw-semibold"><i class="bi bi-bar-chart me-2"></i>Statistik Sektoral</div>
              <div class="text-muted small">Data untuk kebijakan yang tepat.</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 rounded-3 mini-card h-100">
              <div class="fw-semibold"><i class="bi bi-shield-check me-2"></i>Persandian</div>
              <div class="text-muted small">Keamanan informasi & tata kelola.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Layanan (singkat) --}}
<section id="layanan" class="py-5 bg-light">
  <div class="container">
    <h2 class="h4 fw-bold mb-3">Layanan Singkat</h2>
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card mini-card h-100">
          <div class="card-body">
            <div class="fw-semibold">Keterbukaan Informasi</div>
            <div class="text-muted small">Akses informasi publik yang jelas dan cepat.</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mini-card h-100">
          <div class="card-body">
            <div class="fw-semibold">Layanan TIK</div>
            <div class="text-muted small">Dukungan teknis untuk sistem dan aplikasi.</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card mini-card h-100">
          <div class="card-body">
            <div class="fw-semibold">Data & Statistik</div>
            <div class="text-muted small">Penguatan data sektoral untuk perencanaan.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="py-4 border-top">
  <div class="container d-flex flex-column flex-md-row gap-2 justify-content-between align-items-center">
    <div class="text-muted small">© {{ date('Y') }} Diskominfo</div>
    <div class="text-muted small">Akses internal tersedia melalui tombol Login</div>
  </div>
</footer>

</body>
</html>
