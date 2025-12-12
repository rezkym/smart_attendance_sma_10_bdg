@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Users - Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-users.js'])
@endsection

@section('content')
  <!-- Stats Card -->
  <div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div class="me-1">
              <p class="text-heading mb-1">Total Users</p>
              <div class="d-flex align-items-center">
                <h4 class="mb-1 me-2">{{ $stats['total_users'] ?? 0 }}</h4>
              </div>
              <small class="mb-0">System users</small>
            </div>
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded-3">
                <div class="icon-base ri ri-group-line icon-26px"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Users List Table -->
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Users</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
          <i class="ri ri-add-line me-1"></i> Add User
        </button>
      </div>
      <div class="d-flex justify-content-between align-items-center row gx-5 pt-4 gap-5 gap-md-0">
        <div class="col-md-4 user_role"></div>
        <div class="col-md-4"></div>
        <div class="col-md-4"></div>
      </div>
    </div>
    <div class="card-datatable table-responsive">
      <table class="datatables-users table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>

    <!-- Offcanvas to add/edit user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel" style="width: 400px;">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100" style="overflow-y: auto;">
        <form class="add-new-user pt-0" id="addNewUserForm">
          <input type="hidden" id="user_id" name="user_id" value="" />

          <!-- Account Information Section -->
          <h6 class="text-muted text-uppercase fw-semibold mb-4">Account Information</h6>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="add-user-name" placeholder="Username" name="name" />
            <label for="add-user-name">Username <span class="text-danger">*</span></label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="email" id="add-user-email" class="form-control" placeholder="john.doe@example.com" name="email" />
            <label for="add-user-email">Email <span class="text-danger">*</span></label>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="password" id="add-user-password" class="form-control" placeholder="Password" name="password" />
            <label for="add-user-password">Password</label>
            <small class="text-muted password-hint" style="display: none;">Leave empty to keep current password</small>
          </div>

          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="password" id="add-user-password-confirmation" class="form-control" placeholder="Confirm Password" name="password_confirmation" />
            <label for="add-user-password-confirmation">Confirm Password</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <select id="user-role" class="form-select select2" name="roles[]" multiple>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
              @endforeach
            </select>
            <label for="user-role">User Role</label>
          </div>

          <hr class="my-4" />

          <!-- Profile Information Section -->
          <h6 class="text-muted text-uppercase fw-semibold mb-4">Profile Information</h6>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-user-fullname" placeholder="John Doe" name="full_name" />
            <label for="add-user-fullname">Full Name</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <select id="add-user-gender" class="form-select" name="gender">
              <option value="">Select Gender</option>
              @foreach ($genders as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
            <label for="add-user-gender">Gender</label>
          </div>

          <div class="row mb-5">
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" class="form-control" id="add-user-birthplace" placeholder="City" name="birth_place" />
                <label for="add-user-birthplace">Birth Place</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating form-floating-outline">
                <input type="text" class="form-control flatpickr-date" id="add-user-birthdate" placeholder="YYYY-MM-DD" name="birth_date" />
                <label for="add-user-birthdate">Birth Date</label>
              </div>
            </div>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-user-phone" placeholder="+62812345678" name="phone_number" />
            <label for="add-user-phone">Phone Number</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <textarea class="form-control" id="add-user-address" placeholder="Address" name="address" style="height: 80px;"></textarea>
            <label for="add-user-address">Address</label>
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

