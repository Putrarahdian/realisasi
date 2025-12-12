<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="{{ asset('weblab/img/logo.png') }}">
  <title>DISKOMINFO</title>


  <!-- CSS -->
   <!-- ✅ Tambahkan di dalam <head> -->
  <link rel="stylesheet" href="{{ asset('weblab/css/bootstrap-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/navigasi/reset.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/navigasi/sidebar.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/navigasi/navbar.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/navigasi/content.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/navigasi/animation.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/navigasi/menuadmin.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/navigasi/login.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/export/laporan.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/export/laporan_table.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/export/laporan_word.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/export/rekap.css') }}">
  <link rel="stylesheet" href="{{ asset('weblab/css/export/editanak.css') }}">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

@php
  $loginLayout = isset($hideNavbarSidebar) && $hideNavbarSidebar;
@endphp

<body class="{{ $loginLayout ? 'has-login-layout' : '' }}">

  <div class="wrapper">
    <div class="body-overlay"></div>

    {{-- Sidebar & Navbar hanya jika bukan halaman login --}}
    @if(!isset($hideNavbarSidebar) || !$hideNavbarSidebar)
      @include('layout.sidebar')
      @include('layout.navbar')
    @endif

    <div id="content">
      <div class="main-content {{ (!isset($hideNavbarSidebar) || !$hideNavbarSidebar) ? 'p-3' : '' }}">
        @yield('content')
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      console.log('✅ Bootstrap JS aktif!');
      const toggleBtn = document.getElementById('toggleSidebar');
      const sidebar  = document.getElementById('sidebar');
      const content  = document.getElementById('content');
      const overlay  = document.querySelector('.body-overlay');
      const navbar   = document.querySelector('.top-navbar');

      if (!toggleBtn || !sidebar || !content || !overlay) return;

      function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('show-nav');
        if (window.innerWidth > 991) content.classList.add('active');
      }
      function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('show-nav');
        content.classList.remove('active');
      }

      toggleBtn?.addEventListener('click', () => {
        sidebar.classList.contains('active') ? closeSidebar() : openSidebar();
      });
      overlay?.addEventListener('click', closeSidebar);
      document.addEventListener('keydown', e => e.key === 'Escape' && closeSidebar());

      document.addEventListener('scroll', function () {
        if (navbar) navbar.classList.toggle('sticky-shadow', window.scrollY > 10);
      });
    });
  </script>

{{-- 🔔 Global Bootstrap Toast (Modern Version) --}}
<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000;">
    
    {{-- ✅ Success Toast --}}
    @if(session('success'))
    <div class="toast align-items-center border-0 fade shadow-sm text-bg-success" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-success text-white">
        <strong class="me-auto">✅ Berhasil</strong>
        <small>Baru saja</small>
        <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body fw-semibold">
        {{ session('success') }}
      </div>
    </div>
    @endif

    {{-- ❌ Error Toast --}}
    @if(session('error'))
    <div class="toast align-items-center border-0 fade shadow-sm text-bg-danger" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-danger text-white">
        <strong class="me-auto">❌ Gagal</strong>
        <small>Baru saja</small>
        <button type="button" class="btn-close btn-close-white ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body fw-semibold">
        {{ session('error') }}
      </div>
    </div>
    @endif

    {{-- ⚠️ Validation Error Toast --}}
    @if($errors->any())
    <div class="toast align-items-center border-0 fade shadow-sm text-bg-warning" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-warning text-dark">
        <strong class="me-auto">⚠️ Validasi Gagal</strong>
        <small>Baru saja</small>
        <button type="button" class="btn-close ms-2 mb-1" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body fw-semibold">
        {{ $errors->first() }}
      </div>
    </div>
    @endif

  </div>
</div>

{{-- ✅ Toast Script --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  const toasts = document.querySelectorAll('.toast');
  toasts.forEach(toastEl => {
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toastEl.classList.add('show', 'fade', 'animate-toast');
    toast.show();
  });
  console.log('🔥 Menjalankan toast Bootstrap dengan animasi...');
});
</script>

{{-- ✨ Extra CSS for smooth animation --}}
<style>
  .animate-toast {
    opacity: 0;
    transform: translateY(-15px);
    transition: all 0.4s ease-in-out;
  }
  .toast.show.animate-toast {
    opacity: 1;
    transform: translateY(0);
  }
</style>


<script>
document.addEventListener('DOMContentLoaded', function() {
  const deleteButtons = document.querySelectorAll('.btn-delete');

  deleteButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();

      const form = this.closest('form');
      const message = this.getAttribute('data-message') 
                      || 'Data ini akan dihapus secara permanen!';

      Swal.fire({
        title: 'Hapus Data?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
});
</script>

</body>
</html>
