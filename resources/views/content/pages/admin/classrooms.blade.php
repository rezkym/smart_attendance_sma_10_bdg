@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Classrooms - Management')

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
  @vite(['resources/assets/js/app-classrooms.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Classrooms</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">All registered classrooms</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-door-line icon-26px"></div>
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
              <p class="text-heading mb-1">Active Classrooms</p>
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

  <!-- Classrooms List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Classrooms</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClassroom">
          <i class="ri ri-add-line me-1"></i> Add Classroom
        </button>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-classrooms table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Name</th>
            <th>Grade</th>
            <th>Academic Year</th>
            <th>Homeroom Teacher</th>
            <th>Capacity</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Offcanvas to add/edit classroom -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddClassroom" aria-labelledby="offcanvasAddClassroomLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddClassroomLabel" class="offcanvas-title">Add Classroom</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-classroom pt-0" id="addNewClassroomForm">
          <input type="hidden" id="classroom_id" name="classroom_id" value="" />

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-classroom-name" placeholder="X IPA 1" name="name" />
            <label for="add-classroom-name">Name</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <select id="add-classroom-grade" name="grade_level" class="form-select">
              <option value="" disabled selected>Select Grade</option>
              <option value="10">Class 10</option>
              <option value="11">Class 11</option>
              <option value="12">Class 12</option>
            </select>
            <label for="add-classroom-grade">Grade Level</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <select id="add-classroom-academic-year" name="academic_year_id" class="select2 form-select">
              <option value="">Select Academic Year</option>
              @foreach($academicYears as $year)
                <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
                  {{ $year->name }} {{ $year->is_active ? '(Active)' : '' }}
                </option>
              @endforeach
            </select>
            <label for="add-classroom-academic-year">Academic Year</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="number" class="form-control" id="add-classroom-capacity" placeholder="30" name="capacity" min="1" max="100" />
            <label for="add-classroom-capacity">Capacity</label>
          </div>

          <div class="mb-5">
            <label for="add-classroom-homeroom-teacher" class="form-label">Homeroom Teacher</label>
            <select id="add-classroom-homeroom-teacher" name="homeroom_teacher_id" class="select2 form-select" data-allow-clear="true">
              <option value="">Select Homeroom Teacher (Optional)</option>
            </select>
          </div>

          <div class="mb-5">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="add-classroom-is-active" name="is_active" checked />
              <label class="form-check-label" for="add-classroom-is-active">Active</label>
            </div>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <textarea class="form-control" id="add-classroom-description" placeholder="Description" name="description" style="height: 100px"></textarea>
            <label for="add-classroom-description">Description (Optional)</label>
          </div>

          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
          <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
  </div>
@endsection
