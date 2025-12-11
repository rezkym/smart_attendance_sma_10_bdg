@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <a href="{{url('/')}}" class="auth-cover-brand d-flex align-items-center gap-2">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
    <span class="app-brand-text demo text-heading fw-semibold">{{config('variables.templateName')}}</span>
  </a>
  <!-- /Logo -->
  <div class="authentication-inner row m-0">
    <!-- /Left Section -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2">
      <img src="{{asset('assets/img/illustrations/auth-login-illustration-'.$configData['theme'].'.png')}}"
        class="auth-cover-illustration w-100" alt="auth-illustration"
        data-app-light-img="illustrations/auth-login-illustration-light.png"
        data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
      <img alt="mask" src="{{asset('assets/img/illustrations/auth-basic-login-mask-'.$configData['theme'].'.png')}}"
        class="authentication-image d-none d-lg-block"
        data-app-light-img="illustrations/auth-basic-login-mask-light.png"
        data-app-dark-img="illustrations/auth-basic-login-mask-dark.png" />
    </div>
    <!-- /Left Section -->

    <!-- Login -->
    <div
      class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
      <div class="w-px-400 mx-auto pt-12 pt-lg-0">
        <h4 class="mb-1">Welcome to {{config('variables.templateName')}}! 👋</h4>
        <p class="mb-5">Please sign-in to your account and start the adventure</p>

        @if (session('status'))
        <div class="alert alert-success mb-1 rounded-0" role="alert">
          <div class="alert-body">
            {{ session('status') }}
          </div>
        </div>
        @endif
        <form id="formAuthentication" class="mb-5" action="{{ route('login') }}" method="POST">
          @csrf
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control @error('email') is-invalid @enderror" id="login-email" name="email"
              placeholder="john@example.com" autofocus value="{{ old('email') }}">
            <label for="login-email" class="form-label">Email</label>
            @error('email')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-5">
            <div class="form-password-toggle">
              <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                <div class="form-floating form-floating-outline">
                  <input type="password" id="login-password"
                    class="form-control @error('password') is-invalid @enderror" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <label class="form-label" for="login-password">Password</label>
                </div>
                <span class="input-group-text cursor-pointer"><i
                    class="icon-base ri ri-eye-off-line icon-20px"></i></span>
              </div>
              @error('password')
              <span class="invalid-feedback" role="alert">
                <span class="fw-medium">{{ $message }}</span>
              </span>
              @enderror
            </div>
          </div>
          <div class="mb-5 d-flex justify-content-between flex-wrap py-2">
            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                {{ old('remember') ? 'checked' : '' }}>
              <label class="form-check-label me-2" for="remember-me"> Remember Me </label>
            </div>
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="float-end mb-1">
              <span>Forgot Password?</span>
            </a>
            @endif
          </div>
          <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
        </form>

        <p class="text-center">
          <span>New on our platform?</span>
          @if (Route::has('register'))
          <a href="{{ route('register') }}">
            <span>Create an account</span>
          </a>
          @endif
        </p>

        <div class="divider my-5">
          <div class="divider-text">or</div>
        </div>

        <div class="d-flex justify-content-center gap-2">
          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-facebook">
            <i class="icon-base ri ri-facebook-fill icon-18px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-twitter">
            <i class="icon-base ri ri-twitter-fill icon-18px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-github">
            <i class="icon-base ri ri-github-fill icon-18px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-google-plus">
            <i class="icon-base ri ri-google-fill icon-18px"></i>
          </a>
        </div>
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>
@endsection