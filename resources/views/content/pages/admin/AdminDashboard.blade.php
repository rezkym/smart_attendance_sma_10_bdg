@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard Admin')

@section('content')
    <h4>Selamat Datang {{ auth()->user()->name }}</h4>
    <div class="row g-6">
        <!-- Gamification Card -->
        <div class="col-md-12 col-xxl-8">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-md-6 order-2 order-md-1">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Selamat Datang <span class="fw-bold">{{ auth()->user()->name }}!</span> 🎉</h4>
                            <p class="mb-0">Welcome to the Admin Dashboard.</p>
                            <p>Manage your school data efficiently.</p>
                            <a href="{{ route('profile.show') }}" class="btn btn-primary">View Profile</a>
                        </div>
                    </div>
                    <div class="col-md-6 text-center text-md-end order-1 order-md-2">
                        <div class="card-body pb-0 px-0 pt-2">
                            <img src="{{ asset('assets/img/illustrations/illustration-john-' . $configData['theme'] . '.png') }}"
                                height="186" class="scaleX-n1-rtl" alt="View Profile"
                                data-app-light-img="illustrations/illustration-john-light.png"
                                data-app-dark-img="illustrations/illustration-john-dark.png" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Gamification Card -->
    </div>

@endsection
