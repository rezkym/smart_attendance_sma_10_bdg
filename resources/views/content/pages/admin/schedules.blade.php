@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Schedules - Management')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js'
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-schedules.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Schedules</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">All registered schedules</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-calendar-schedule-line icon-26px"></div>
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
              <p class="text-heading mb-1">Active Schedules</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['active'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Currently active</small>
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

  <!-- Schedules List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Schedules</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddSchedule">
          <i class="ri ri-add-line me-1"></i> Add Schedule
        </button>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-schedules table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Classroom</th>
            <th>Day</th>
            <th>Time</th>
            <th>Subject</th>
            <th>Teacher</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Offcanvas to add/edit schedule -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddSchedule" aria-labelledby="offcanvasAddScheduleLabel" style="width: 450px;">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddScheduleLabel" class="offcanvas-title">Add Schedule</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100" style="overflow-y: auto;">
        <form class="add-new-schedule pt-0" id="addNewScheduleForm">
          <input type="hidden" id="schedule_id" name="schedule_id" value="" />

          <div class="mb-5">
            <label for="add-schedule-academic-year" class="form-label">Academic Year <span class="text-danger">*</span></label>
            <select id="add-schedule-academic-year" name="academic_year_id" class="select2 form-select">
              <option value="">Select Academic Year</option>
            </select>
          </div>

          <div class="mb-5">
            <label for="add-schedule-classroom" class="form-label">Classroom <span class="text-danger">*</span></label>
            <select id="add-schedule-classroom" name="classroom_id" class="select2 form-select">
              <option value="">Select Classroom</option>
            </select>
          </div>

          <div class="mb-5">
            <label for="add-schedule-subject" class="form-label">Subject <span class="text-danger">*</span></label>
            <select id="add-schedule-subject" name="subject_id" class="select2 form-select">
              <option value="">Select Subject</option>
            </select>
          </div>

          <div class="mb-5">
            <label for="add-schedule-teacher" class="form-label">Teacher <span class="text-danger">*</span></label>
            <select id="add-schedule-teacher" name="teacher_id" class="select2 form-select">
              <option value="">Select Teacher</option>
            </select>
          </div>

          <div class="mb-5">
            <label for="add-schedule-day" class="form-label">Day of Week <span class="text-danger">*</span></label>
            <select id="add-schedule-day" name="day_of_week" class="form-select">
              <option value="" disabled selected>Select Day</option>
              <option value="1">Senin</option>
              <option value="2">Selasa</option>
              <option value="3">Rabu</option>
              <option value="4">Kamis</option>
              <option value="5">Jumat</option>
              <option value="6">Sabtu</option>
            </select>
          </div>

          <div class="row mb-5">
            <div class="col-6">
              <label for="add-schedule-start-time" class="form-label">Start Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="add-schedule-start-time" name="start_time" />
            </div>
            <div class="col-6">
              <label for="add-schedule-end-time" class="form-label">End Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="add-schedule-end-time" name="end_time" />
            </div>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <textarea class="form-control" id="add-schedule-notes" placeholder="Additional notes..." name="notes" style="height: 80px"></textarea>
            <label for="add-schedule-notes">Notes (Optional)</label>
          </div>

          <div class="mb-5">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="add-schedule-is-active" name="is_active" checked />
              <label class="form-check-label" for="add-schedule-is-active">Active</label>
            </div>
          </div>

          <div class="pt-3">
            <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
            <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
