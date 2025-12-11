@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Academic Years - Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-academic-years.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Academic Years</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Configured periods</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-calendar-line icon-26px"></div>
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
              <p class="text-heading mb-1">Active Year</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['active_year'] ?? 'None' }}</h4>
              </div>
              <small class="mb-0">Current academic year</small>
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

  <!-- Academic Years List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Academic Years</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddAcademicYear">
          <i class="ri ri-add-line me-1"></i> Add Academic Year
        </button>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-academic-years table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Name</th>
            <th>Period</th>
            <th>Status</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Offcanvas to add/edit academic year -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddAcademicYear" aria-labelledby="offcanvasAddAcademicYearLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddAcademicYearLabel" class="offcanvas-title">Add Academic Year</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-academic-year pt-0" id="addNewAcademicYearForm">
          <input type="hidden" id="academic_year_id" name="academic_year_id" value="" />

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-academic-year-name" placeholder="2024/2025" name="name" />
            <label for="add-academic-year-name">Name</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-academic-year-start-date" placeholder="YYYY-MM-DD" name="start_date" />
            <label for="add-academic-year-start-date">Start Date</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-academic-year-end-date" placeholder="YYYY-MM-DD" name="end_date" />
            <label for="add-academic-year-end-date">End Date</label>
          </div>

          <div class="mb-5">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="add-academic-year-is-active" name="is_active" />
              <label class="form-check-label" for="add-academic-year-is-active">Set as Active Year</label>
            </div>
            <small class="text-muted">Only one academic year can be active at a time.</small>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <textarea class="form-control" id="add-academic-year-description" placeholder="Description" name="description" style="height: 100px"></textarea>
            <label for="add-academic-year-description">Description (Optional)</label>
          </div>

          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
          <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
  </div>
@endsection
