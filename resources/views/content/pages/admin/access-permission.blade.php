@extends('layouts/layoutMaster')

@section('title', 'Permission - Access Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-access-permission.js'])
@endsection

@section('content')
  <h4 class="mb-1">Permissions List</h4>
  <p class="mb-6">Permissions are assigned to roles to control access to specific features and actions.</p>

  <!-- Permission Table -->
  <div class="card">
    <div class="card-datatable table-responsive">
      <table class="datatables-permissions table">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>Name</th>
            <th>Assigned To</th>
            <th>Created Date</th>
            <th>Actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
  <!--/ Permission Table -->

  <!-- Add Permission Modal -->
  <div class="modal fade" id="addPermissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-simple">
      <div class="modal-content p-4 p-md-12">
        <div class="modal-body p-md-0">
          <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="text-center mb-6">
            <h3 class="mb-2 pb-1">Add New Permission</h3>
            <p>Permissions you may use and assign to your users.</p>
          </div>
          <form id="addPermissionForm" class="row">
            <div class="col-12 form-control-validation mb-4">
              <div class="form-floating form-floating-outline">
                <input type="text" id="modalPermissionName" name="name" class="form-control" placeholder="Permission Name" autofocus />
                <label for="modalPermissionName">Permission Name</label>
              </div>
            </div>
            <div class="col-12 text-center demo-vertical-spacing">
              <button type="submit" class="btn btn-primary me-sm-4 me-1">Create Permission</button>
              <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Discard</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!--/ Add Permission Modal -->
@endsection
