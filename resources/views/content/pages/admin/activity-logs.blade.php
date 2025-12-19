@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Activity Logs - Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-activity-logs.js'])
@endsection

@section('content')
  <!-- Filter Card -->
  <div class="card mb-6">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Filters</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-4">
        <div class="col-md-3">
          <label class="form-label" for="filter-log-name">Log Name</label>
          <select id="filter-log-name" class="form-select">
            <option value="">All Logs</option>
            <option value="user">User</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="classroom">Classroom</option>
            <option value="schedule">Schedule</option>
            <option value="attendance">Attendance</option>
            <option value="iot-device">IoT Device</option>
            <option value="semester">Semester</option>
            <option value="student-enrollment">Student Enrollment</option>
            <option value="rfid-card">RFID Card</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="filter-event">Event Type</label>
          <select id="filter-event" class="form-select">
            <option value="">All Events</option>
            <option value="created">Created</option>
            <option value="updated">Updated</option>
            <option value="deleted">Deleted</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="filter-date-from">Date From</label>
          <input type="text" id="filter-date-from" class="form-control flatpickr-date" placeholder="YYYY-MM-DD" />
        </div>
        <div class="col-md-3">
          <label class="form-label" for="filter-date-to">Date To</label>
          <input type="text" id="filter-date-to" class="form-control flatpickr-date" placeholder="YYYY-MM-DD" />
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-12">
          <button type="button" class="btn btn-primary me-2" id="apply-filters">
            <i class="ri ri-filter-line me-1"></i> Apply Filters
          </button>
          <button type="button" class="btn btn-outline-secondary" id="reset-filters">
            <i class="ri ri-refresh-line me-1"></i> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Activity Logs List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Activity Logs</h5>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-activity-logs table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Timestamp</th>
            <th>Causer</th>
            <th>Event</th>
            <th>Subject</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Modal to view log details -->
    <div class="modal fade" id="activityLogDetailModal" tabindex="-1" aria-labelledby="activityLogDetailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="activityLogDetailModalLabel">Activity Log Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Timestamp:</strong> <span id="detail-timestamp">-</span>
              </div>
              <div class="col-md-6">
                <strong>Event:</strong> <span id="detail-event">-</span>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Causer:</strong> <span id="detail-causer">-</span>
              </div>
              <div class="col-md-6">
                <strong>Log Name:</strong> <span id="detail-log-name">-</span>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Subject Type:</strong> <span id="detail-subject-type">-</span>
              </div>
              <div class="col-md-6">
                <strong>Subject ID:</strong> <span id="detail-subject-id">-</span>
              </div>
            </div>
            <div class="mb-3">
              <strong>Description:</strong>
              <div class="text-muted mt-1" id="detail-description">-</div>
            </div>
            <div class="mb-3" id="old-values-container" style="display: none;">
              <strong>Old Values:</strong>
              <pre class="bg-light p-3 rounded mt-2" id="detail-old-values" style="max-height: 200px; overflow: auto;">-</pre>
            </div>
            <div class="mb-3" id="new-values-container" style="display: none;">
              <strong>New Values:</strong>
              <pre class="bg-light p-3 rounded mt-2" id="detail-new-values" style="max-height: 200px; overflow: auto;">-</pre>
            </div>
            <div class="mb-0">
              <strong>Raw Properties:</strong>
              <pre class="bg-light p-3 rounded mt-2" id="detail-raw-properties" style="max-height: 200px; overflow: auto;">-</pre>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
