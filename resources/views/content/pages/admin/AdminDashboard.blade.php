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
                            <p class="mb-0">You have <span class="fw-bold">{{ $totalCount }}</span> total users in your system.</p>
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

        <!-- Statistics Cards -->
        <div class="col-xxl-2 col-md-6 col-sm-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start card-widget-1 pb-3 pb-sm-0">
                        <div>
                            <h4 class="mb-1">{{ $studentCount }}</h4>
                            <p class="mb-0">Total Students</p>
                        </div>
                        <div class="avatar me-sm-4">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base ri ri-user-line icon-24px"></i>
                            </span>
                        </div>
                    </div>
                    <hr class="d-none d-sm-block d-lg-none me-4">
                </div>
            </div>
        </div>
        <div class="col-xxl-2 col-md-6 col-sm-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start card-widget-2 pb-3 pb-sm-0">
                        <div>
                            <h4 class="mb-1">{{ $teacherCount }}</h4>
                            <p class="mb-0">Total Teachers</p>
                        </div>
                        <div class="avatar me-sm-4">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base ri ri-user-star-line icon-24px"></i>
                            </span>
                        </div>
                    </div>
                    <hr class="d-none d-sm-block d-lg-none">
                </div>
            </div>
        </div>
        <!--/ Statistics Cards -->
    </div>

@endsection
