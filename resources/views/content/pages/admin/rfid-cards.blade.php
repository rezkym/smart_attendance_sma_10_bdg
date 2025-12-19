@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'RFID Cards - Management')

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
  @vite(['resources/assets/js/app-rfid-cards.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Cards</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Semua kartu RFID</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-bank-card-line icon-26px"></div>
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
                <h4 class="mb-1 me-2">{{ $stats['active'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Kartu aktif</small>
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
              <p class="text-heading mb-1">Blocked</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['blocked'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Kartu diblokir</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-danger rounded-3">
                <div class="icon-base ri ri-lock-line icon-26px"></div>
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
              <p class="text-heading mb-1">Unassigned</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['unassigned'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">Belum ditetapkan</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-warning rounded-3">
                <div class="icon-base ri ri-user-unfollow-line icon-26px"></div>
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
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select class="form-select" id="filter-status">
            <option value="">All Statuses</option>
            @foreach($cardStatuses as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Assignment</label>
          <select class="form-select" id="filter-assigned">
            <option value="">All Cards</option>
            <option value="yes">Assigned</option>
            <option value="no">Unassigned</option>
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button class="btn btn-primary w-100" id="btn-apply-filter">
            <i class="ri ri-filter-line me-1"></i> Apply Filter
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- RFID Cards List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">RFID Cards</h5>
        <div class="d-flex gap-2">
          @can('create', App\Models\RfidCard::class)
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickScanModal">
            <i class="ri ri-flashlight-line me-1"></i> Quick Scan
          </button>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cardModal">
            <i class="ri ri-add-line me-1"></i> Add Card
          </button>
          @endcan
        </div>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-rfid-cards table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Card UID</th>
            <th>User</th>
            <th>Status</th>
            <th>Issued At</th>
            <th>Expires At</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- Modal: Add/Edit Card -->
  <div class="modal fade" id="cardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cardModalTitle">Add RFID Card</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="cardForm">
          <div class="modal-body">
            <input type="hidden" id="card-id">
            <input type="hidden" id="card-action" value="create">

            <div class="mb-4">
              <label class="form-label">Card UID <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="card-uid" placeholder="e.g., A1B2C3D4" required>
              <small class="text-muted">Unique identifier from RFID card (8-20 hex characters)</small>
            </div>

            <div class="mb-4">
              <label class="form-label">Assign to User</label>
              <select class="form-select select2" id="card-user-id">
                <option value="">-- No Assignment --</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select class="form-select" id="card-status" required>
                @foreach($cardStatuses as $value => $label)
                  <option value="{{ $value }}" {{ $value === 'active' ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div class="row">
              <div class="col-md-6 mb-4">
                <label class="form-label">Issued Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="card-issued-at" value="{{ date('Y-m-d') }}" required>
              </div>
              <div class="col-md-6 mb-4">
                <label class="form-label">Expiry Date</label>
                <input type="date" class="form-control" id="card-expires-at">
                <small class="text-muted">Leave empty for no expiry</small>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Notes</label>
              <textarea class="form-control" id="card-notes" rows="2" placeholder="Optional notes..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="btn-submit-card">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Block Card -->
  <div class="modal fade" id="blockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Block RFID Card</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="block-card-id">
          <p id="blockMessage">Are you sure you want to block this card?</p>
          <div class="mb-3">
            <label class="form-label">Reason for blocking <span class="text-danger">*</span></label>
            <select class="form-select" id="block-reason" required>
              <option value="">Select reason...</option>
              <option value="lost">Card Lost</option>
              <option value="damaged">Card Damaged</option>
              <option value="stolen">Card Stolen</option>
              <option value="expired">Card Expired</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="btn-confirm-block">Block Card</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Quick Scan -->
  <div class="modal fade" id="quickScanModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri ri-flashlight-line me-2"></i>Quick Scan Registration
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btn-close-quick-scan"></button>
        </div>
        <div class="modal-body">
          <!-- Hardware Selection -->
          <div class="mb-4">
            <label class="form-label">Select Hardware <span class="text-danger">*</span></label>
            <select class="form-select select2" id="qs-device-id" style="width: 100%;">
              <option value="">-- Pilih Device --</option>
            </select>
          </div>

          <hr class="my-3">

          <!-- User Selection -->
          <div class="mb-4">
            <label class="form-label">Assign to User <span class="text-danger">*</span></label>
            <select class="form-select select2" id="qs-user-id" style="width: 100%;">
              <option value="">-- Pilih User --</option>
            </select>
            <small class="text-muted">User yang sudah memiliki kartu tidak ditampilkan</small>
          </div>

          <!-- Card Expiry -->
          <div class="mb-4">
            <label class="form-label d-block">Card Expiry</label>
            <div class="btn-group w-100" role="group">
              <input type="radio" class="btn-check" name="qs-expiry" id="qs-expiry-1" value="1" checked>
              <label class="btn btn-outline-secondary" for="qs-expiry-1">+1 Year</label>
              <input type="radio" class="btn-check" name="qs-expiry" id="qs-expiry-2" value="2">
              <label class="btn btn-outline-secondary" for="qs-expiry-2">+2 Years</label>
              <input type="radio" class="btn-check" name="qs-expiry" id="qs-expiry-3" value="3">
              <label class="btn btn-outline-secondary" for="qs-expiry-3">+3 Years</label>
            </div>
          </div>

          <hr class="my-3">

          <!-- Scan Status Section (Always Visible) -->
          <div id="quick-scan-status-section">
            <div class="text-center py-3">
              <div id="qs-status-icon" class="mb-2">
                <i class="ri ri-user-search-line" style="font-size: 60px; color: #9e9e9e;"></i>
              </div>
              <h5 id="qs-status-text" class="mb-1">Pilih User</h5>
              <p id="qs-status-message" class="text-muted mb-2">Pilih hardware dan user untuk mulai scan</p>
              <div class="d-flex justify-content-center align-items-center gap-2">
                <span id="qs-status-badge" class="badge bg-secondary fs-6">Waiting</span>
                <span id="qs-countdown" class="badge bg-secondary fs-6" style="display: none;">30s</span>
              </div>
            </div>
          </div>

          <!-- Last Result Section -->
          <div id="qs-last-result" class="mt-3" style="display: none;">
            <div id="qs-result-alert" class="alert mb-0">
              <i id="qs-result-icon" class="me-2"></i>
              <span id="qs-result-message"></span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btn-close-modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endsection

