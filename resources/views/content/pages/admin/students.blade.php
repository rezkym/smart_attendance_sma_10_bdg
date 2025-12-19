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

          <!-- User Selection (Add Mode) -->
          <div id="user-select-group" class="mb-5">
            <label class="form-label" for="add-student-user">Select User with Student Role <span class="text-danger">*</span></label>
            <select id="add-student-user" name="user_id" class="form-select select2">
              <option value="">Select a user...</option>
            </select>
            <small class="text-muted">Only users with 'student' role who don't have a profile yet</small>
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
          <h6 class="mb-4 text-uppercase text-muted fw-semibold small">Student Identifiers</h6>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-student-nisn" placeholder="0012345678" name="nisn" maxlength="20" required />
            <label for="add-student-nisn">NISN <span class="text-danger">*</span></label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-student-nis" placeholder="12345" name="nis" maxlength="20" required />
            <label for="add-student-nis">NIS <span class="text-danger">*</span></label>
          </div>

          <hr class="my-4">
          <h6 class="mb-4 text-uppercase text-muted fw-semibold small">Academic Information</h6>

          <div class="mb-5">
            <label for="add-student-classroom" class="form-label">Classroom</label>
            <select id="add-student-classroom" name="classroom_id" class="select2 form-select">
              <option value="">No Classroom Assigned</option>
            </select>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="date" class="form-control" id="add-student-enrollment-date" name="enrollment_date" />
            <label for="add-student-enrollment-date">Enrollment Date</label>
          </div>

          <hr class="my-4">
          <h6 class="mb-4 text-uppercase text-muted fw-semibold small">RFID Card</h6>

          <!-- RFID Card Info (Read-only, shown when editing) -->
          <div id="rfid-card-info" class="mb-4" style="display: none;">
            <div class="card bg-lighter border-0">
              <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <small class="text-muted d-block">Card UID</small>
                    <span id="display-rfid-uid" class="fw-medium font-monospace fs-5">-</span>
                  </div>
                  <span id="display-rfid-status" class="badge bg-label-success">Active</span>
                </div>
                <div class="row mt-3">
                  <div class="col-6">
                    <small class="text-muted d-block">Issued</small>
                    <span id="display-rfid-issued" class="small">-</span>
                  </div>
                  <div class="col-6">
                    <small class="text-muted d-block">Expires</small>
                    <span id="display-rfid-expires" class="small">-</span>
                  </div>
                </div>
              </div>
            </div>
            <small class="text-muted mt-2 d-block">
              <i class="ri ri-information-line"></i>
              Kelola kartu RFID di <a href="/admin/rfid-cards">RFID Cards Management</a>
            </small>
          </div>

          <!-- No RFID Card Message (shown when editing and no card) -->
          <div id="no-rfid-card-info" class="mb-4" style="display: none;">
            <div class="alert alert-secondary mb-2">
              <i class="ri ri-bank-card-line me-1"></i> Belum ada kartu RFID terdaftar untuk user ini
            </div>
            <small class="text-muted">
              <i class="ri ri-information-line"></i>
              Daftarkan kartu di <a href="/admin/rfid-cards">RFID Cards Management</a> menggunakan Quick Scan
            </small>
          </div>

          <!-- Info for Add Mode -->
          <div id="rfid-add-mode-info" class="mb-4">
            <div class="alert alert-light-secondary mb-2">
              <i class="ri ri-information-line me-1"></i> Kartu RFID dapat didaftarkan setelah student dibuat
            </div>
            <small class="text-muted">
              Gunakan <a href="/admin/rfid-cards">Quick Scan</a> untuk mendaftarkan kartu RFID
            </small>
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

          <div class="pt-3">
            <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
            <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Enrollment History -->
  <div class="modal fade" id="enrollmentHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Enrollment History - <span id="enrollmentStudentName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Current Enrollment Card -->
          <div id="currentEnrollmentCard" class="card mb-4 border-success" style="display: none;">
            <div class="card-header bg-success text-white">
              <i class="ri ri-checkbox-circle-line me-1"></i> Current Active Enrollment
            </div>
            <div class="card-body">
              <div class="row mb-2">
                <div class="col-md-6">
                  <strong>Classroom:</strong> <span id="currentClassroom"></span>
                </div>
                <div class="col-md-6">
                  <strong>Academic Year:</strong> <span id="currentAcademicYear"></span>
                </div>
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <strong>Enrolled At:</strong> <span id="currentEnrolledAt"></span>
                </div>
              </div>
              <div class="btn-group">
                <button class="btn btn-warning btn-sm" id="btnTransferStudent">
                  <i class="ri ri-arrow-left-right-line me-1"></i> Transfer
                </button>
                <button class="btn btn-info btn-sm" id="btnGraduateStudent">
                  <i class="ri ri-graduation-cap-line me-1"></i> Graduate
                </button>
                <button class="btn btn-danger btn-sm" id="btnDropStudent">
                  <i class="ri ri-user-unfollow-line me-1"></i> Drop
                </button>
              </div>
            </div>
          </div>

          <!-- No Current Enrollment -->
          <div id="noCurrentEnrollment" class="alert alert-warning" style="display: none;">
            <i class="ri ri-error-warning-line me-1"></i> No active enrollment.
            <button class="btn btn-primary btn-sm ms-2" id="btnEnrollNow">
              <i class="ri ri-add-line me-1"></i> Enroll Now
            </button>
          </div>

          <!-- Enrollment History Table -->
          <h6 class="mb-3">Enrollment History</h6>
          <div class="table-responsive">
            <table class="table table-sm" id="enrollmentHistoryTable">
              <thead>
                <tr>
                  <th>Academic Year</th>
                  <th>Classroom</th>
                  <th>Status</th>
                  <th>Enrolled At</th>
                  <th>Left At</th>
                </tr>
              </thead>
              <tbody id="enrollmentHistoryBody">
                <tr>
                  <td colspan="5" class="text-center text-muted">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Quick Enroll/Transfer -->
  <div class="modal fade" id="quickEnrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="quickEnrollModalTitle">Enroll Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="quickEnrollForm">
          <div class="modal-body">
            <input type="hidden" id="quickEnrollStudentId">
            <input type="hidden" id="quickEnrollAction" value="enroll">

            <div class="mb-4">
              <label class="form-label">Academic Year <span class="text-danger">*</span></label>
              <select class="form-select" id="quickEnrollAcademicYear" required>
                <option value="">Select Academic Year...</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="form-label">Classroom <span class="text-danger">*</span></label>
              <select class="form-select" id="quickEnrollClassroom" required>
                <option value="">Select Classroom...</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="form-label">Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="quickEnrollDate" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="btnQuickEnrollSubmit">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Graduate/Drop Confirmation -->
  <div class="modal fade" id="studentActionConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="studentActionConfirmTitle">Confirm Action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="studentActionEnrollmentId">
          <input type="hidden" id="studentActionType">
          <p id="studentActionConfirmMessage"></p>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" id="studentActionDate">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="btnStudentActionConfirm">Confirm</button>
        </div>
      </div>
    </div>
  </div>
@endsection

