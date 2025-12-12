@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Students - Management')

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
  @vite(['resources/assets/js/app-students.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Students</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">All registered students</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
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
              <p class="text-heading mb-1">Active Students</p>
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

  <!-- Students List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Students</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddStudent">
          <i class="ri ri-add-line me-1"></i> Add Student
        </button>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-students table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>NISN</th>
            <th>Full Name</th>
            <th>Classroom</th>
            <th>Gender</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Offcanvas to add/edit student -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddStudent" aria-labelledby="offcanvasAddStudentLabel" style="width: 450px;">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddStudentLabel" class="offcanvas-title">Add Student</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100" style="overflow-y: auto;">
        <form class="add-new-student pt-0" id="addNewStudentForm">
          <input type="hidden" id="student_id" name="student_id" value="" />

          <h6 class="mb-4">Student Identifiers</h6>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-student-nisn" placeholder="0012345678" name="nisn" maxlength="20" required />
            <label for="add-student-nisn">NISN *</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-student-nis" placeholder="12345" name="nis" maxlength="20" required />
            <label for="add-student-nis">NIS *</label>
          </div>

          <hr class="my-4">
          <h6 class="mb-4">Personal Information</h6>

          <div class="mb-5">
            <label for="add-student-user" class="form-label">User Account (Optional)</label>
            <select id="add-student-user" name="user_id" class="select2 form-select" data-allow-clear="true">
              <option value="">No User Account</option>
            </select>
            <small class="text-muted">Link to user account for login access</small>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-student-full-name" placeholder="Ahmad Fauzi" name="full_name" maxlength="100" required />
            <label for="add-student-full-name">Full Name *</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <select id="add-student-gender" name="gender" class="form-select" required>
              <option value="" disabled selected>Select Gender</option>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
            <label for="add-student-gender">Gender *</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <select id="add-student-classroom" name="classroom_id" class="select2 form-select">
              <option value="">No Classroom Assigned</option>
            </select>
            <label for="add-student-classroom">Classroom</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-student-birth-place" placeholder="Bandung" name="birth_place" maxlength="100" />
            <label for="add-student-birth-place">Birth Place</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="date" class="form-control" id="add-student-birth-date" name="birth_date" />
            <label for="add-student-birth-date">Birth Date</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <textarea class="form-control" id="add-student-address" placeholder="Jl. Merdeka No. 123" name="address" style="height: 80px"></textarea>
            <label for="add-student-address">Address</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-student-phone" placeholder="081234567890" name="phone_number" maxlength="20" />
            <label for="add-student-phone">Phone Number</label>
          </div>

          <hr class="my-4">
          <h6 class="mb-4">Additional Information</h6>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-student-rfid" placeholder="A1B2C3D4E5" name="rfid_card_number" maxlength="50" />
            <label for="add-student-rfid">RFID Card Number</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="date" class="form-control" id="add-student-enrollment-date" name="enrollment_date" />
            <label for="add-student-enrollment-date">Enrollment Date</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <textarea class="form-control" id="add-student-notes" placeholder="Additional notes..." name="notes" style="height: 80px"></textarea>
            <label for="add-student-notes">Notes</label>
          </div>

          <div class="mb-5">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="add-student-is-active" name="is_active" checked />
              <label class="form-check-label" for="add-student-is-active">Active</label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
          <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
  </div>
@endsection
