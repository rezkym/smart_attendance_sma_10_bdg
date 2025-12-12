@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'IoT Devices - Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-iot-devices.js'])
@endsection

@section('content')
  <!-- Stats Cards -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Devices</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2" id="total-devices">0</h4>
              </div>
              <small class="mb-0">All IoT devices</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-router-line icon-26px"></div>
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
              <p class="text-heading mb-1">Active Devices</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2" id="active-devices">0</h4>
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

  <!-- IoT Devices List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">IoT Devices</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddDevice">
          <i class="ri ri-add-line me-1"></i> Add Device
        </button>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-iot-devices table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Device Code</th>
            <th>Name</th>
            <th>Location</th>
            <th>Classroom</th>
            <th>Status</th>
            <th>Last Seen</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Offcanvas to add/edit device -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDevice" aria-labelledby="offcanvasAddDeviceLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddDeviceLabel" class="offcanvas-title">Add IoT Device</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-device pt-0" id="addNewDeviceForm">
          <input type="hidden" id="device_id" name="device_id" value="" />

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-device-name" placeholder="ESP32 Ruang 101" name="name" maxlength="100" />
            <label for="add-device-name">Device Name</label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-device-code" placeholder="ESP32-R101" name="device_code" maxlength="50" />
            <label for="add-device-code">Device Code</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <textarea class="form-control" id="add-device-description" placeholder="Description" name="description" style="height: 80px"></textarea>
            <label for="add-device-description">Description (Optional)</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-device-location" placeholder="Floor 1, Room 101" name="location" maxlength="255" />
            <label for="add-device-location">Location (Optional)</label>
          </div>

          <div class="mb-5">
            <label class="form-label" for="add-device-classroom">Classroom (Optional)</label>
            <select id="add-device-classroom" name="classroom_id" class="select2 form-select">
              <option value="">Select Classroom</option>
            </select>
          </div>

          <div class="mb-5">
            <label class="form-label" for="add-device-status">Status</label>
            <select id="add-device-status" name="status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="maintenance">Maintenance</option>
            </select>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-device-firmware" placeholder="1.0.0" name="firmware_version" maxlength="20" />
            <label for="add-device-firmware">Firmware Version (Optional)</label>
          </div>

          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
          <button type="reset" class="btn btn-outline-danger" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>

    <!-- Modal to show API Key -->
    <div class="modal fade" id="apiKeyModal" tabindex="-1" aria-labelledby="apiKeyModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="apiKeyModalLabel">API Key</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning mb-3">
              <i class="ri ri-error-warning-line me-2"></i>
              <strong>Important:</strong> Copy this API key now. You won't be able to see it again!
            </div>
            <div class="input-group">
              <input type="text" class="form-control" id="api-key-display" readonly />
              <button class="btn btn-outline-primary" type="button" id="copy-api-key">
                <i class="ri ri-file-copy-line"></i>
              </button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I've copied it</button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
