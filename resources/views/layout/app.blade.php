<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Realisasi Kegiatan')</title>

  {{-- CSS --}}
  @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])

  {{-- Bootstrap Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  {{-- Material Icons --}}
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<div class="wrapper">

  {{-- Sidebar --}}
  @include('layout.sidebar')

  {{-- Navbar di luar #content supaya sticky --}}
  @include('layout.navbar')

  {{-- Page Content --}}
  <div id="content" class="main-content">
    <div class="content-inner p-3">

      {{-- Pesan sukses atau error global --}}
      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      {{-- Konten dinamis --}}
      @yield('content')
    </div>
  </div>

  {{-- Overlay untuk mobile --}}
  <div class="body-overlay"></div>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Sidebar toggle script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('toggleSidebar');
  const sidebar = document.getElementById('sidebar');
  const content = document.getElementById('content');
  const overlay = document.querySelector('.body-overlay');

  function openSidebar() {
    sidebar.classList.add('active');
    content.classList.add('active');
    overlay.classList.add('show-nav');
  }

  function closeSidebar() {
    sidebar.classList.remove('active');
    content.classList.remove('active');
    overlay.classList.remove('show-nav');
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
      if (sidebar.classList.contains('active')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  }

  overlay.addEventListener('click', closeSidebar);

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar();
  });

  // Shadow saat scroll
  const navbar = document.querySelector('.top-navbar');
  document.addEventListener('scroll', function() {
    if(window.scrollY > 10) {
        navbar.classList.add('sticky-shadow');
    } else {
        navbar.classList.remove('sticky-shadow');
    }
  });
});
</script>

</body>
</html>
