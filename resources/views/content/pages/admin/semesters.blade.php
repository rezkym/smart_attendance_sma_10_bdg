@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Semesters - Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-semesters.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Semester</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Semester terkonfigurasi</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-calendar-check-line icon-26px"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Semester Aktif</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['active_semester'] ?? 'Tidak ada' }}</h4>
              </div>
              <small class="mb-0">Semester yang sedang berjalan</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-success rounded-3">
                <div class="icon-base ri ri-checkbox-circle-line icon-26px"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Semesters List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Daftar Semester</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddSemester">
          <i class="ri ri-add-line me-1"></i> Tambah Semester
        </button>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-semesters table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Tahun Ajaran</th>
            <th>Tipe</th>
            <th>Periode</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Offcanvas to add/edit semester -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddSemester" aria-labelledby="offcanvasAddSemesterLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddSemesterLabel" class="offcanvas-title">Tambah Semester</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-semester pt-0" id="addNewSemesterForm">
          <input type="hidden" id="semester_id" name="semester_id" value="" />

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <select class="form-select select2" id="add-semester-academic-year" name="academic_year_id">
              <option value="">Pilih Tahun Ajaran</option>
              @foreach($academicYears as $academicYear)
                <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
              @endforeach
            </select>
            <label for="add-semester-academic-year">Tahun Ajaran</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <select class="form-select" id="add-semester-type" name="type">
              <option value="">Pilih Tipe Semester</option>
              @foreach($semesterTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
            <label for="add-semester-type">Tipe Semester</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-semester-start-date" placeholder="YYYY-MM-DD" name="start_date" />
            <label for="add-semester-start-date">Tanggal Mulai</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-semester-end-date" placeholder="YYYY-MM-DD" name="end_date" />
            <label for="add-semester-end-date">Tanggal Akhir</label>
          </div>

          <div class="mb-5">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="add-semester-is-active" name="is_active" />
              <label class="form-check-label" for="add-semester-is-active">Set sebagai Semester Aktif</label>
            </div>
            <small class="text-muted">Hanya satu semester yang bisa aktif dalam satu waktu.</small>
          </div>

          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Simpan</button>
          <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Batal</button>
        </form>
      </div>
    </div>
  </div>
@endsection
