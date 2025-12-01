@extends('layout.weblab')

@php
  $hideNavbarSidebar = true;
@endphp

@section('content')
  <div class="login-page">
    <div class="login-card">
      <div class="login-header text-center mb-4">
        <div class="login-logo mb-3">
          <div class="login-logo-circle">
            <img src="{{ asset('weblab/img/logo.png') }}" alt="logo" class="login-logo-img">
          </div>
        </div>
        <h3 class="mb-1">Selamat Datang</h3>
      </div>

      @if(session('error'))
        <div class="alert alert-danger mb-3">
          {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <div class="input-with-icon">
            <span class="input-icon">@</span>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control @error('email') is-invalid @enderror"
              required
              autofocus
              value="{{ old('email') }}"
              placeholder="nama@instansi.go.id"
            >
          </div>
          @error('email')
            <div class="invalid-feedback d-block">
              {{ $message }}
            </div>
          @enderror
        </div>

        <div class="mb-2">
          <label for="password" class="form-label">Kata Sandi</label>
          <div class="input-with-icon">
            <span class="input-icon">•••</span>
            <input
              type="password"
              id="password"
              name="password"
              class="form-control @error('password') is-invalid @enderror"
              required
              placeholder="Masukkan kata sandi"
            >
          </div>
          @error('password')
            <div class="invalid-feedback d-block">
              {{ $message }}
            </div>
          @enderror
        </div>
        <br>
        <button type="submit" class="btn btn-login w-100">
          Masuk
        </button>
      </form>

      <div class="login-footer text-center mt-3 small text-muted">
        &copy; {{ date('Y') }} {{ config('app.name', 'DISKOMINFO') }}. All rights reserved.
      </div>
    </div>
  </div>
@endsection
