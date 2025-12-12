@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard Admin')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/apex-charts/apex-charts.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/apex-charts/apexcharts.js'
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/dashboards-admin.js'])
@endsection

@section('content')
    <!-- Row 1: Welcome Card + Quick Stats -->
    <div class="row g-6 mb-6">

        <!-- Quick Stats Cards -->
        <div class="col-xl-8">
            <div class="row g-6 h-100">
                <!-- Total Students -->
                <div class="col-sm-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <h5 class="text-heading mb-1" id="stat-total-students">{{ $dashboardData['master_data']['active_students'] ?? 0 }}</h5>
                                    <small class="text-muted">Total Siswa</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded-3 bg-label-primary">
                                        <i class="ri ri-graduation-cap-line ri-24px"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total Teachers -->
                <div class="col-sm-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <h5 class="text-heading mb-1" id="stat-total-teachers">{{ $dashboardData['master_data']['active_teachers'] ?? 0 }}</h5>
                                    <small class="text-muted">Total Guru</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded-3 bg-label-info">
                                        <i class="ri ri-user-star-line ri-24px"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total Classrooms -->
                <div class="col-sm-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <h5 class="text-heading mb-1" id="stat-total-classrooms">{{ $dashboardData['master_data']['active_classrooms'] ?? 0 }}</h5>
                                    <small class="text-muted">Total Kelas</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded-3 bg-label-warning">
                                        <i class="ri ri-door-line ri-24px"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total Subjects -->
                <div class="col-sm-6 col-md-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <h5 class="text-heading mb-1" id="stat-total-subjects">{{ $dashboardData['master_data']['active_subjects'] ?? 0 }}</h5>
                                    <small class="text-muted">Total Mapel</small>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded-3 bg-label-success">
                                        <i class="ri ri-book-2-line ri-24px"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Today's Attendance Summary -->
    <div class="row g-6 mb-6">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Kehadiran Hari Ini</h5>
                    <small class="text-muted">{{ now()->format('l, d F Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Attendance Rate -->
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-lg">
                                    <div class="avatar-initial bg-primary rounded-3">
                                        <i class="ri ri-percent-line ri-24px"></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="mb-0" id="today-attendance-rate">{{ $dashboardData['today_attendance']['attendance_percentage'] ?? 0 }}%</h3>
                                    <small class="text-muted">Tingkat Kehadiran</small>
                                </div>
                            </div>
                        </div>
                        <!-- Present -->
                        <div class="col-md-2 col-6">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-success p-2 rounded-3">
                                    <i class="ri ri-checkbox-circle-line ri-24px"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0" id="today-present">{{ $dashboardData['today_attendance']['present'] ?? 0 }}</h5>
                                    <small class="text-muted">Hadir</small>
                                </div>
                            </div>
                        </div>
                        <!-- Late -->
                        <div class="col-md-2 col-6">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-warning p-2 rounded-3">
                                    <i class="ri ri-time-line ri-24px"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0" id="today-late">{{ $dashboardData['today_attendance']['late'] ?? 0 }}</h5>
                                    <small class="text-muted">Terlambat</small>
                                </div>
                            </div>
                        </div>
                        <!-- Excused -->
                        <div class="col-md-2 col-6">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-info p-2 rounded-3">
                                    <i class="ri ri-file-list-3-line ri-24px"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0" id="today-excused">{{ $dashboardData['today_attendance']['excused'] ?? 0 }}</h5>
                                    <small class="text-muted">Izin</small>
                                </div>
                            </div>
                        </div>
                        <!-- Sick -->
                        <div class="col-md-1 col-6">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-secondary p-2 rounded-3">
                                    <i class="ri ri-hospital-line ri-24px"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0" id="today-sick">{{ $dashboardData['today_attendance']['sick'] ?? 0 }}</h5>
                                    <small class="text-muted">Sakit</small>
                                </div>
                            </div>
                        </div>
                        <!-- Absent -->
                        <div class="col-md-2 col-6">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-danger p-2 rounded-3">
                                    <i class="ri ri-close-circle-line ri-24px"></i>
                                </span>
                                <div>
                                    <h5 class="mb-0" id="today-absent">{{ $dashboardData['today_attendance']['absent'] ?? 0 }}</h5>
                                    <small class="text-muted">Alpha</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Charts -->
    <div class="row g-6 mb-6">
        <!-- Weekly Attendance Trend -->
        <div class="col-xl-8 col-12">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Trend Kehadiran 7 Hari Terakhir</h5>
                    <button class="btn btn-sm btn-outline-primary" id="btn-refresh-trend">
                        <i class="ri ri-refresh-line"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div id="weeklyTrendChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Classroom Ranking -->
        <div class="col-xl-4 col-12">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 5 Kelas Kehadiran Tertinggi</h5>
                </div>
                <div class="card-body">
                    <div id="classroomRankingList">
                        @if(!empty($dashboardData['classroom_ranking']))
                            @foreach($dashboardData['classroom_ranking'] as $index => $classroom)
                                <div class="d-flex align-items-center mb-4">
                                    <div class="avatar me-3">
                                        <span class="avatar-initial rounded-3 {{ $index === 0 ? 'bg-primary' : ($index === 1 ? 'bg-info' : ($index === 2 ? 'bg-success' : 'bg-label-secondary')) }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column flex-grow-1">
                                        <h6 class="mb-1">{{ $classroom['name'] }}</h6>
                                        <small class="text-muted">{{ $classroom['total_students'] }} siswa</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $classroom['attendance_rate'] >= 80 ? 'success' : ($classroom['attendance_rate'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $classroom['attendance_rate'] }}%
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="ri ri-bar-chart-box-line ri-48px text-muted mb-2"></i>
                                <p class="text-muted mb-0">Belum ada data kehadiran</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
