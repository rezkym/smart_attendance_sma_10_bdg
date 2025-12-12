@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Attendance Report')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/apex-charts/apexcharts.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-report-attendance.js'])
@endsection

@section('content')
  <!-- Page Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-6">
    <div>
      <h4 class="mb-1">Laporan Kehadiran</h4>
      <p class="text-muted mb-0">Lihat dan analisa data kehadiran siswa</p>
    </div>
    <div>
      <button type="button" class="btn btn-primary" id="btn-export-report" disabled>
        <i class="ri ri-download-line me-1"></i> Export CSV
      </button>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="card mb-6">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0"><i class="ri ri-filter-3-line me-2"></i>Filter Laporan</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-4">
        <div class="col-md-3">
          <label class="form-label" for="filter-classroom">Kelas <span class="text-danger">*</span></label>
          <select id="filter-classroom" class="form-select select2">
            <option value="">Pilih Kelas</option>
            @foreach($classrooms as $classroom)
              <option value="{{ $classroom->id }}">{{ $classroom->name }} (Tingkat {{ $classroom->grade_level }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="filter-start-date">Tanggal Mulai</label>
          <input type="text" class="form-control flatpickr-date" id="filter-start-date" placeholder="Pilih tanggal mulai" />
        </div>
        <div class="col-md-3">
          <label class="form-label" for="filter-end-date">Tanggal Akhir</label>
          <input type="text" class="form-control flatpickr-date" id="filter-end-date" placeholder="Pilih tanggal akhir" />
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="button" class="btn btn-primary flex-grow-1" id="btn-apply-filter" disabled>
            <i class="ri ri-search-line me-1"></i> Tampilkan
          </button>
          <button type="button" class="btn btn-outline-secondary" id="btn-reset-filter">
            <i class="ri ri-refresh-line"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Report Content (Hidden until filter applied) -->
  <div id="report-content" class="d-none">
    <!-- Summary Cards -->
    <div class="row g-4 mb-6">
      <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-primary rounded-3">
                  <i class="ri ri-group-line ri-24px"></i>
                </div>
              </div>
              <div>
                <h4 class="mb-0" id="summary-total-records">0</h4>
                <small class="text-muted">Total Record</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-success rounded-3">
                  <i class="ri ri-checkbox-circle-line ri-24px"></i>
                </div>
              </div>
              <div>
                <h4 class="mb-0"><span id="summary-present">0</span><small class="text-muted ms-1">(<span id="summary-present-pct">0</span>%)</small></h4>
                <small class="text-muted">Hadir</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-warning rounded-3">
                  <i class="ri ri-time-line ri-24px"></i>
                </div>
              </div>
              <div>
                <h4 class="mb-0"><span id="summary-late">0</span><small class="text-muted ms-1">(<span id="summary-late-pct">0</span>%)</small></h4>
                <small class="text-muted">Terlambat</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-info rounded-3">
                  <i class="ri ri-file-list-3-line ri-24px"></i>
                </div>
              </div>
              <div>
                <h4 class="mb-0"><span id="summary-excused">0</span><small class="text-muted ms-1">(<span id="summary-excused-pct">0</span>%)</small></h4>
                <small class="text-muted">Izin</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-secondary rounded-3">
                  <i class="ri ri-hospital-line ri-24px"></i>
                </div>
              </div>
              <div>
                <h4 class="mb-0"><span id="summary-sick">0</span><small class="text-muted ms-1">(<span id="summary-sick-pct">0</span>%)</small></h4>
                <small class="text-muted">Sakit</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-sm-4 col-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-danger rounded-3">
                  <i class="ri ri-close-circle-line ri-24px"></i>
                </div>
              </div>
              <div>
                <h4 class="mb-0"><span id="summary-absent">0</span><small class="text-muted ms-1">(<span id="summary-absent-pct">0</span>%)</small></h4>
                <small class="text-muted">Alpha</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Attendance Rate Card -->
    <div class="row g-4 mb-6">
      <div class="col-12">
        <div class="card bg-primary">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div>
                <h5 class="text-white mb-1">Tingkat Kehadiran</h5>
                <p class="text-white-50 mb-0">Persentase kehadiran (Hadir + Terlambat) dari total record</p>
              </div>
              <div class="text-end">
                <h2 class="text-white mb-0"><span id="summary-attendance-rate">0</span>%</h2>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-6">
      <!-- Weekly Trend Chart -->
      <div class="col-xl-8 col-12">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Trend Kehadiran</h5>
          </div>
          <div class="card-body">
            <div id="attendanceTrendChart" style="min-height: 350px;"></div>
          </div>
        </div>
      </div>
      <!-- Status Breakdown Donut -->
      <div class="col-xl-4 col-12">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Distribusi Status</h5>
          </div>
          <div class="card-body">
            <div id="statusDonutChart" style="min-height: 350px;"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Detail Table -->
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Detail Per Siswa</h5>
      </div>
      <div class="card-datatable table-responsive">
        <table class="table" id="studentReportTable">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Siswa</th>
              <th>NISN</th>
              <th class="text-center">Hadir</th>
              <th class="text-center">Terlambat</th>
              <th class="text-center">Izin</th>
              <th class="text-center">Sakit</th>
              <th class="text-center">Alpha</th>
              <th class="text-center">Tingkat Kehadiran</th>
            </tr>
          </thead>
          <tbody id="studentReportBody">
            <!-- Data will be loaded dynamically -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div id="loading-state" class="d-none">
    <div class="card">
      <div class="card-body text-center py-5">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mb-0">Memuat data laporan...</p>
      </div>
    </div>
  </div>

  <!-- Empty State -->
  <div id="empty-state" class="d-none">
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="ri ri-file-search-line ri-64px text-muted mb-3"></i>
        <h5 class="text-muted">Pilih Kelas untuk Melihat Laporan</h5>
        <p class="text-muted mb-0">Gunakan filter di atas untuk menampilkan data laporan kehadiran.</p>
      </div>
    </div>
  </div>
@endsection
