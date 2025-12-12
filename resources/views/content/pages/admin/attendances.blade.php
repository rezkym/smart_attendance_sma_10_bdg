@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Attendances - Management')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js'
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-attendances.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Hari Ini</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2" id="stats-total">{{ $stats['total'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Total attendance records</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-file-list-3-line icon-26px"></div>
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
              <p class="text-heading mb-1">Hadir</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2" id="stats-present">{{ ($stats['present'] ?? 0) + ($stats['late'] ?? 0) }}</h4>
              </div>
              <small class="mb-0">Present + Late</small>
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
              <p class="text-heading mb-1">Tidak Hadir</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2" id="stats-absent">{{ ($stats['absent'] ?? 0) + ($stats['excused'] ?? 0) + ($stats['sick'] ?? 0) }}</h4>
              </div>
              <small class="mb-0">Absent + Excused + Sick</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-danger rounded-3">
                <div class="icon-base ri ri-close-circle-line icon-26px"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- View Mode Tabs -->
  <div class="nav-align-top mb-6">
    <ul class="nav nav-pills mb-4" role="tablist">
      <li class="nav-item">
        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-records" aria-controls="tab-records" aria-selected="true">
          <i class="ri ri-list-check me-2"></i> Attendance Records
        </button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-bulk" aria-controls="tab-bulk" aria-selected="false">
          <i class="ri ri-group-line me-2"></i> Bulk Attendance Entry
        </button>
      </li>
    </ul>

    <div class="tab-content">
      <!-- Tab: Attendance Records (DataTable) -->
      <div class="tab-pane fade show active" id="tab-records" role="tabpanel">
        <div class="card">
          <div class="card-header border-bottom">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
              <h5 class="card-title mb-3 mb-md-0">Attendance Records</h5>
              <div class="d-flex flex-wrap gap-3">
                <!-- Filter: Date -->
                <div class="input-group input-group-merge" style="width: 180px;">
                  <span class="input-group-text"><i class="ri ri-calendar-line"></i></span>
                  <input type="text" class="form-control" id="filter-date" placeholder="Select Date" />
                </div>
                <!-- Filter: Classroom -->
                <select id="filter-classroom" class="form-select" style="width: 180px;">
                  <option value="">All Classrooms</option>
                  @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                  @endforeach
                </select>
                <!-- Filter: Status -->
                <select id="filter-status" class="form-select" style="width: 150px;">
                  <option value="">All Status</option>
                  @foreach($statuses as $value => $info)
                    <option value="{{ $value }}">{{ $info['label'] }}</option>
                  @endforeach
                </select>
                <button class="btn btn-outline-secondary" id="btn-clear-filters">
                  <i class="ri ri-filter-off-line"></i> Clear
                </button>
              </div>
            </div>
          </div>
          <div class="card-datatable table-responsive">
            <table class="datatables-attendances table">
              <thead>
                <tr>
                  <th></th>
                  <th></th>
                  <th>Date</th>
                  <th>Student</th>
                  <th>Classroom</th>
                  <th>Subject</th>
                  <th>Time</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab: Bulk Attendance Entry -->
      <div class="tab-pane fade" id="tab-bulk" role="tabpanel">
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Bulk Attendance Entry</h5>
          </div>
          <div class="card-body pt-4">
            <!-- Step 1: Select Classroom, Schedule, and Date -->
            <div class="row g-4 mb-4">
              <div class="col-md-4">
                <label class="form-label" for="bulk-classroom">Classroom <span class="text-danger">*</span></label>
                <select id="bulk-classroom" class="form-select select2">
                  <option value="">Select Classroom</option>
                  @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label" for="bulk-schedule">Schedule <span class="text-danger">*</span></label>
                <select id="bulk-schedule" class="form-select" disabled>
                  <option value="">Select classroom first</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label" for="bulk-date">Date <span class="text-danger">*</span></label>
                <input type="text" id="bulk-date" class="form-control flatpickr-date" placeholder="Select Date" />
              </div>
            </div>

            <div class="d-flex gap-2 mb-4">
              <button type="button" class="btn btn-primary" id="btn-load-students">
                <i class="ri ri-refresh-line me-1"></i> Load Students
              </button>
              <button type="button" class="btn btn-success d-none" id="btn-submit-bulk">
                <i class="ri ri-save-line me-1"></i> Submit Attendance
              </button>
            </div>

            <!-- Step 2: Student List for Bulk Entry -->
            <div id="bulk-students-container" class="d-none">
              <hr class="my-4">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0">Students in <span id="bulk-classroom-name" class="fw-bold"></span></h6>
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-success set-all-status" data-status="present">All Present</button>
                  <button type="button" class="btn btn-sm btn-outline-warning set-all-status" data-status="late">All Late</button>
                  <button type="button" class="btn btn-sm btn-outline-danger set-all-status" data-status="absent">All Absent</button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered" id="bulk-students-table">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 60px;">#</th>
                      <th>NISN</th>
                      <th>Student Name</th>
                      <th style="width: 400px;">Status</th>
                      <th>Notes</th>
                    </tr>
                  </thead>
                  <tbody id="bulk-students-body">
                    <!-- Students will be loaded here -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Offcanvas to edit attendance -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditAttendance" aria-labelledby="offcanvasEditAttendanceLabel" style="width: 400px;">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasEditAttendanceLabel" class="offcanvas-title">Edit Attendance</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100" style="overflow-y: auto;">
      <form class="edit-attendance pt-0" id="editAttendanceForm">
        <input type="hidden" id="edit_attendance_id" name="attendance_id" value="" />

        <!-- Student Info (Read-only) -->
        <div class="card bg-lighter mb-4">
          <div class="card-body py-3">
            <h6 class="mb-3 text-muted text-uppercase fw-semibold small">Attendance Info</h6>
            <div class="row">
              <div class="col-6 mb-2">
                <small class="text-muted d-block">Student</small>
                <span id="edit-student-name" class="fw-medium"></span>
              </div>
              <div class="col-6 mb-2">
                <small class="text-muted d-block">NISN</small>
                <span id="edit-student-nisn"></span>
              </div>
            </div>
            <div class="row">
              <div class="col-6 mb-2">
                <small class="text-muted d-block">Classroom</small>
                <span id="edit-classroom-name"></span>
              </div>
              <div class="col-6 mb-2">
                <small class="text-muted d-block">Subject</small>
                <span id="edit-subject-name"></span>
              </div>
            </div>
            <div>
              <small class="text-muted d-block">Date</small>
              <span id="edit-attendance-date"></span>
            </div>
          </div>
        </div>

        <!-- Editable Fields -->
        <div class="mb-4">
          <label for="edit-status" class="form-label">Status <span class="text-danger">*</span></label>
          <select id="edit-status" name="status" class="form-select">
            @foreach($statuses as $value => $info)
              <option value="{{ $value }}">{{ $info['label'] }}</option>
            @endforeach
          </select>
        </div>

        <div class="row mb-4">
          <div class="col-6">
            <label for="edit-check-in-time" class="form-label">Check-in Time</label>
            <input type="time" class="form-control" id="edit-check-in-time" name="check_in_time" step="1" />
          </div>
          <div class="col-6">
            <label for="edit-check-out-time" class="form-label">Check-out Time</label>
            <input type="time" class="form-control" id="edit-check-out-time" name="check_out_time" step="1" />
          </div>
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <textarea class="form-control" id="edit-notes" placeholder="Notes..." name="notes" style="height: 80px"></textarea>
          <label for="edit-notes">Notes</label>
        </div>

        <div class="pt-3">
          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Update</button>
          <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Cancel</button>
        </div>
      </form>
    </div>
  </div>
@endsection
