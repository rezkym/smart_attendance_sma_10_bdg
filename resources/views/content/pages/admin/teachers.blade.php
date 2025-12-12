@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Teachers - Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-teachers.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Teachers</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">All registered teachers</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-user-star-line icon-26px"></div>
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
              <p class="text-heading mb-1">Active Teachers</p>
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

  <!-- Teachers List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Teachers</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddTeacher">
          <i class="ri ri-add-line me-1"></i> Add Teacher
        </button>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-teachers table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Name</th>
            <th>Email</th>
            <th>NIP</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Offcanvas to add/edit teacher -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddTeacher" aria-labelledby="offcanvasAddTeacherLabel" style="width: 400px;">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddTeacherLabel" class="offcanvas-title">Add Teacher</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100" style="overflow-y: auto;">
        <form class="add-new-teacher pt-0" id="addNewTeacherForm">
          <input type="hidden" id="teacher_id" name="teacher_id" value="" />

          <!-- User Selection (Add Mode) -->
          <div id="user-select-group" class="mb-5">
            <label class="form-label" for="add-teacher-user">Select User with Teacher Role <span class="text-danger">*</span></label>
            <select id="add-teacher-user" name="user_id" class="form-select select2">
              <option value="">Select a user...</option>
            </select>
            <small class="text-muted">Only users with 'teacher' role who don't have a profile yet</small>
          </div>

          <!-- User Info Display (Edit Mode) -->
          <div id="user-info-group" class="mb-5" style="display: none;">
            <div class="card bg-lighter mb-3">
              <div class="card-body py-3">
                <h6 class="mb-3 text-muted text-uppercase fw-semibold small">User Account</h6>
                <div class="mb-2">
                  <small class="text-muted d-block">Name</small>
                  <span id="display-user-name" class="fw-medium"></span>
                </div>
                <div class="mb-2">
                  <small class="text-muted d-block">Email</small>
                  <span id="display-user-email" class="text-muted"></span>
                </div>
              </div>
            </div>
            <div class="card bg-lighter">
              <div class="card-body py-3">
                <h6 class="mb-3 text-muted text-uppercase fw-semibold small">Profile Information</h6>
                <div class="row">
                  <div class="col-6 mb-2">
                    <small class="text-muted d-block">Gender</small>
                    <span id="display-user-gender">-</span>
                  </div>
                  <div class="col-6 mb-2">
                    <small class="text-muted d-block">Phone</small>
                    <span id="display-user-phone">-</span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6 mb-2">
                    <small class="text-muted d-block">Birth Place</small>
                    <span id="display-user-birthplace">-</span>
                  </div>
                  <div class="col-6 mb-2">
                    <small class="text-muted d-block">Birth Date</small>
                    <span id="display-user-birthdate">-</span>
                  </div>
                </div>
                <div>
                  <small class="text-muted d-block">Address</small>
                  <span id="display-user-address">-</span>
                </div>
              </div>
            </div>
            <small class="text-muted mt-2 d-block">
              <i class="ri ri-information-line"></i> To edit profile data, go to <a href="/admin/users">Users Management</a>
            </small>
          </div>

          <hr class="my-4">

          <h6 class="mb-4 text-uppercase text-muted fw-semibold small">Teacher Information</h6>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-teacher-nip" placeholder="199001012020011001" name="nip" maxlength="30" />
            <label for="add-teacher-nip">NIP (Employee Number)</label>
          </div>

          <div class="mb-5">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="add-teacher-is-active" name="is_active" checked />
              <label class="form-check-label" for="add-teacher-is-active">Active</label>
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

