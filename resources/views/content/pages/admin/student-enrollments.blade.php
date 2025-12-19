@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Student Enrollments - Management')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-student-enrollments.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Enrollments</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ ($stats['total_active'] ?? 0) + ($stats['total_graduated'] ?? 0) + ($stats['total_transferred'] ?? 0) + ($stats['total_dropped'] ?? 0) }}</h4>
              </div>
              <small class="mb-0">Semua enrollment</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-user-follow-line icon-26px"></div>
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
              <p class="text-heading mb-1">Active</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total_active'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Siswa aktif</small>
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
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Graduated</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total_graduated'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Lulusan</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-info rounded-3">
                <div class="icon-base ri ri-graduation-cap-line icon-26px"></div>
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
              <p class="text-heading mb-1">Transferred</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total_transferred'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Pindah kelas</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-warning rounded-3">
                <div class="icon-base ri ri-arrow-left-right-line icon-26px"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Row -->
  <div class="card mb-6">
    <div class="card-body">
      <div class="row g-4">
        <div class="col-md-3">
          <label class="form-label">Academic Year</label>
          <select class="form-select select2" id="filter-academic-year">
            <option value="">All Academic Years</option>
            @foreach($academicYears as $year)
              <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Classroom</label>
          <select class="form-select select2" id="filter-classroom">
            <option value="">All Classrooms</option>
            @foreach($classrooms as $classroom)
              <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select" id="filter-status">
            <option value="">All Statuses</option>
            @foreach($statuses as $status)
              <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-primary w-100" id="btn-apply-filter">
            <i class="ri ri-filter-line me-1"></i> Apply Filter
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Enrollments List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Student Enrollments</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
          <i class="ri ri-add-line me-1"></i> Enroll Student
        </button>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-enrollments table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Student</th>
            <th>NISN</th>
            <th>Classroom</th>
            <th>Academic Year</th>
            <th>Status</th>
            <th>Enrolled At</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- Modal: Enroll Student -->
  <div class="modal fade" id="enrollStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="enrollStudentModalTitle">Enroll Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="enrollStudentForm">
          <div class="modal-body">
            <input type="hidden" id="enroll-action" value="enroll">
            <input type="hidden" id="enroll-enrollment-id">

            <div class="mb-4" id="student-select-group">
              <label class="form-label">Student <span class="text-danger">*</span></label>
              <select class="form-select select2" id="enroll-student-id" required>
                <option value="">Select Student...</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="form-label">Academic Year <span class="text-danger">*</span></label>
              <select class="form-select select2" id="enroll-academic-year" required>
                <option value="">Select Academic Year...</option>
                @foreach($academicYears as $year)
                  <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-4">
              <label class="form-label">Classroom <span class="text-danger">*</span></label>
              <select class="form-select select2" id="enroll-classroom" required>
                <option value="">Select Classroom...</option>
                @foreach($classrooms as $classroom)
                  <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-4">
              <label class="form-label">Enrolled Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="enroll-date" value="{{ date('Y-m-d') }}" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="btn-submit-enroll">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Graduate/Drop Confirmation -->
  <div class="modal fade" id="actionConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="actionConfirmTitle">Confirm Action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="confirm-enrollment-id">
          <input type="hidden" id="confirm-action">
          <p id="actionConfirmMessage"></p>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" id="confirm-date" value="{{ date('Y-m-d') }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="btn-confirm-action">Confirm</button>
        </div>
      </div>
    </div>
  </div>
@endsection
