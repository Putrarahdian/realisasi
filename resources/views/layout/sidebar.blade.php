<!-- Sidebar -->
<div id="sidebar">
  <div class="sidebar-header text-center">
    <img src="{{ asset('weblab/img/diskominfo.png') }}" alt="Logo" class="img-fluid"/>
  </div>


  <hr class="dropdown-divider">

  
  @php
    $role = auth()->user()->role ?? 'user';
  @endphp

  <ul class="list-unstyled component m-0">

      {{-- ==================== KHUSUS ADMIN ==================== --}}
    @if($role === 'admin')
      {{-- Admin: hanya bisa lihat Rekap --}}
      <li class="{{ request()->is('realisasi/rekap*') ? 'active' : '' }}">
        <a href="{{ route('realisasi.rekap') }}">
          <i class="material-icons">description</i> Rekap
        </a>
      </li>

    {{-- 🏠 Menu Home --}}
    @else
    <li class="{{ request()->is('/') ? 'active' : '' }}">
      <a href="{{ url('/') }}" class="dashboard">
        <i class="material-icons">house</i> Home
      </a>
    </li>

    {{-- 📊 Menu Kegiatan - bisa diakses semua yang login kecuali admin --}}
    <li class="{{ request()->routeIs('realisasi.index','realisasi.create','realisasi.edit','realisasi.show') ? 'active' : '' }}">
      <a href="{{ route('realisasi.index') }}">
        <i class="material-icons">grid_on</i> Kegiatan
      </a>
    </li>
    
    {{-- ⚙️ Menu Triwulan --}}
    <li class="{{ request()->is('realisasi/triwulan*') ? 'active' : '' }}">
      <a  href="#submenuTriwulan"  data-bs-toggle="collapse"  role="button"  aria-expanded="false"  aria-controls="submenuTriwulan"  class="d-flex align-items-center justify-content-between">
        <span><i class="material-icons">book</i> Triwulan</span>
        <i class="material-icons">expand_more</i>
      </a>

      <ul class="collapse list-unstyled ps-3" id="submenuTriwulan">
        <li><a href="{{ route('realisasi.triwulan.index', 1) }}" class="dropdown-item">Triwulan I</a></li>
        <li><a href="{{ route('realisasi.triwulan.index', 2) }}" class="dropdown-item">Triwulan II</a></li>
        <li><a href="{{ route('realisasi.triwulan.index', 3) }}" class="dropdown-item">Triwulan III</a></li>
        <li><a href="{{ route('realisasi.triwulan.index', 4) }}" class="dropdown-item">Triwulan IV</a></li>
      </ul>
    </li>

      {{-- 📄 Menu Rekap (bisa diakses semua role selain admin lewat sini) --}}
      <li class="{{ request()->is('realisasi/rekap*') ? 'active' : '' }}">
        <a href="{{ route('realisasi.rekap') }}">
          <i class="material-icons">description</i> Rekap
        </a>
      </li>

    {{-- 🧑‍💼 Menu khusus Superuser/Admin --}}
    @if(in_array(Auth()->user()->role, ['superuser','admin']))
      <li class="{{ request()->is('admin/menu*') ? 'active' : '' }}">
        <a href="{{ route('admin.menu') }}">
          <i class="material-icons">admin_panel_settings</i> User
        </a>
      </li>
    @endif
    @endif
  </ul>
</div>
