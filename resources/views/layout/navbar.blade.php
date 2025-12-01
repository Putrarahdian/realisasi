<!-- NAVBAR -->
<div class="top-navbar bg-dark sticky-top">
  <div class="xd-topbar py-2 px-3 d-flex justify-content-between align-items-center">

    <!-- Tombol toggle sidebar -->
    <div id="toggleSidebar" class="xp-menubar d-flex align-items-center" role="button">
      <span class="material-icons text-white fs-4">menu</span>
    </div>

    <!-- Bagian kanan navbar -->
    <div class="d-flex align-items-center">
      @auth
        <span class="user-greeting text-white me-2">
          Halo, <strong>{{ Auth::user()->name }}</strong>
        </span>
        
        <!-- Dropdown profil -->
        <div class="dropdown">
          <button class="btn p-0 border-0 bg-transparent d-flex align-items-center"
                  id="profileDropdown" data-bs-toggle="dropdown" data-bs-display="static" data-bs-offset="0,8" aria-expanded="false">
            <img src="{{ asset('weblab/img/logo.png') }}"
                 class="rounded-circle border border-2 border-white shadow-sm"
                 style="width:40px; height:40px; object-fit:cover; cursor:pointer;">
          </button>

          <ul class="dropdown-menu dropdown-menu-end shadow border-0"
              aria-labelledby="profileDropdown" style="min-width: 200px;">
            <li class="dropdown-header text-center py-2 bg-light rounded-top">
              <img src="{{ asset('weblab/img/logo.png') }}"
                   class="rounded-circle mb-2" style="width:60px; height:60px; object-fit:cover;">
              <div class="fw-semibold">{{ Auth::user()->name }}</div>
              <small class="text-muted">{{ optional(Auth::user()->jabatan)->nama ?? '-' }}</small>
            </li>
            <li><hr class="dropdown-divider"></li>
            <!-- Tombol Toggle Mode -->
            <li>
              <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="dropdown-item text-danger d-flex align-items-center">
                  <i class="material-icons me-2" style="font-size:18px;">logout</i>
                  Logout
                </button>
              </form>
            </li>
          </ul>
        </div>
      @endauth
    </div>
  </div>
</div>
