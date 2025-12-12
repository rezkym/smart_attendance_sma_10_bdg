@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'IoT Logs - Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-iot-logs.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Logs</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2" id="total-logs">0</h4>
              </div>
              <small class="mb-0">All API logs</small>
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
              <p class="text-heading mb-1">Error Logs</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2" id="error-logs">0</h4>
              </div>
              <small class="mb-0">Total errors</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-danger rounded-3">
                <div class="icon-base ri ri-error-warning-line icon-26px"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Card -->
  <div class="card mb-6">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">Filters</h5>
    </div>
    <div class="card-body pt-4">
      <div class="row g-4">
        <div class="col-md-3">
          <label class="form-label" for="filter-device">Device</label>
          <select id="filter-device" class="select2 form-select">
            <option value="">All Devices</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="filter-log-type">Log Type</label>
          <select id="filter-log-type" class="form-select">
            <option value="">All Types</option>
            <option value="request">Request</option>
            <option value="response">Response</option>
            <option value="error">Error</option>
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
          <button type="button" class="btn btn-outline-secondary me-2" id="reset-filters">
            <i class="ri ri-refresh-line me-1"></i> Reset
          </button>
          <button type="button" class="btn btn-outline-success" id="export-logs">
            <i class="ri ri-download-line me-1"></i> Export CSV
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- IoT Logs List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">IoT Logs</h5>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-iot-logs table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Timestamp</th>
            <th>Device</th>
            <th>Type</th>
            <th>Endpoint</th>
            <th>Method</th>
            <th>Status</th>
            <th>Duration</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Modal to view log details -->
    <div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="logDetailModalLabel">Log Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <strong>Device:</strong> <span id="detail-device">-</span>
              </div>
              <div class="col-md-6">
                <strong>Timestamp:</strong> <span id="detail-timestamp">-</span>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-4">
                <strong>Method:</strong> <span id="detail-method">-</span>
              </div>
              <div class="col-md-4">
                <strong>Endpoint:</strong> <span id="detail-endpoint">-</span>
              </div>
              <div class="col-md-4">
                <strong>Response Code:</strong> <span id="detail-response-code">-</span>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-4">
                <strong>Duration:</strong> <span id="detail-duration">-</span>
              </div>
              <div class="col-md-4">
                <strong>IP Address:</strong> <span id="detail-ip">-</span>
              </div>
              <div class="col-md-4">
                <strong>Log Type:</strong> <span id="detail-log-type">-</span>
              </div>
            </div>
            <div class="mb-3" id="error-message-container" style="display: none;">
              <strong>Error Message:</strong>
              <div class="alert alert-danger mt-2" id="detail-error-message"></div>
            </div>
            <div class="mb-3">
              <strong>Request Payload:</strong>
              <pre class="bg-light p-3 rounded mt-2" id="detail-request-payload" style="max-height: 200px; overflow: auto;">-</pre>
            </div>
            <div class="mb-3">
              <strong>Response Payload:</strong>
              <pre class="bg-light p-3 rounded mt-2" id="detail-response-payload" style="max-height: 200px; overflow: auto;">-</pre>
            </div>
            <div class="mb-0">
              <strong>User Agent:</strong>
              <div class="text-muted small mt-1" id="detail-user-agent">-</div>
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
