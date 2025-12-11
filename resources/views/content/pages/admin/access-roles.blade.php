@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Roles - Access Management')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app-access-roles.js'])
@endsection

@section('content')
  <h4 class="mb-1">Roles List</h4>
  <p class="mb-6">A role provides access to predefined menus and features. Depending on the assigned role, an administrator can have access to specific functions.</p>

  <!-- Role cards -->
  <div class="row g-6">
    @foreach ($roles as $role)
      <div class="col-xl-4 col-lg-6 col-md-6">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <p class="mb-0">Total {{ $role->users_count ?? 0 }} user(s)</p>
              <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                @php
                  $avatarCount = min(3, $role->users_count ?? 0);
                  $remaining = max(0, ($role->users_count ?? 0) - 3);
                @endphp
                @for ($i = 1; $i <= $avatarCount; $i++)
                  <li class="avatar pull-up">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                      {{ strtoupper(substr($role->name, 0, 1)) }}
                    </span>
                  </li>
                @endfor
                @if ($remaining > 0)
                  <li class="avatar">
                    <span class="avatar-initial rounded-circle pull-up bg-lightest text-body" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $remaining }} more">+{{ $remaining }}</span>
                  </li>
                @endif
              </ul>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div class="role-heading">
                <h5 class="mb-1">{{ $role->name }}</h5>
                <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#addRoleModal" class="role-edit-modal" data-role-id="{{ $role->id }}">
                  <p class="mb-0">Edit Role</p>
                </a>
              </div>
              @if (strtolower($role->name) !== 'admin')
                <a href="javascript:void(0);" class="text-danger delete-role" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
                  <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    @endforeach

    <!-- Add New Role Card -->
    <div class="col-xl-4 col-lg-6 col-md-6">
      <div class="card h-100">
        <div class="row h-100">
          <div class="col-5">
            <div class="d-flex align-items-end h-100 justify-content-center">
              <img src="{{ asset('assets/img/illustrations/add-new-role-illustration.png') }}" class="img-fluid" alt="Add New Role" width="68" />
            </div>
          </div>
          <div class="col-7">
            <div class="card-body text-sm-end text-center ps-sm-0">
              <button data-bs-target="#addRoleModal" data-bs-toggle="modal" class="btn btn-sm btn-primary mb-4 text-nowrap add-new-role">Add Role</button>
              <p class="mb-0">
                Add new role,<br />
                if it doesn't exist
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Users with Roles Section -->
  <div class="row g-6 mt-2">
    <div class="col-12">
      <h4 class="mt-6 mb-1">Total users with their roles</h4>
      <p class="mb-0">Find all of your administrator accounts and their associated roles.</p>
    </div>
    <div class="col-12">
      <!-- Role Table -->
      <div class="card">
        <div class="card-datatable table-responsive datatable-roles">
          <table class="datatables-users table">
            <thead>
              <tr>
                <th></th>
                <th></th>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
      <!--/ Role Table -->
    </div>
  </div>
  <!--/ Users with Roles Section -->

  <!-- Add Role Modal -->
  <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-simple modal-dialog-centered modal-add-new-role">
      <div class="modal-content">
        <div class="modal-body p-0">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="text-center mb-6">
            <h4 class="role-title mb-2 pb-0">Add New Role</h4>
            <p>Set role permissions</p>
          </div>
          <!-- Add role form -->
          <form id="addRoleForm" class="row g-3">
            <input type="hidden" id="roleId" name="roleId" value="" />
            <div class="col-12 form-control-validation mb-3">
              <div class="form-floating form-floating-outline">
                <input type="text" id="modalRoleName" name="name" class="form-control" placeholder="Enter a role name" />
                <label for="modalRoleName">Role Name</label>
              </div>
            </div>
            <div class="col-12">
              <h5 class="mb-6">Role Permissions</h5>
              <!-- Permission table -->
              <div class="table-responsive">
                <table class="table table-flush-spacing">
                  <tbody>
                    <tr>
                      <td class="text-nowrap fw-medium">
                        Administrator Access
                        <i class="icon-base ri ri-information-line icon-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Allows full access to the system"></i>
                      </td>
                      <td>
                        <div class="d-flex justify-content-end">
                          <div class="form-check mb-0 mt-1">
                            <input class="form-check-input" type="checkbox" id="selectAll" />
                            <label class="form-check-label" for="selectAll"> Select All </label>
                          </div>
                        </div>
                      </td>
                    </tr>
                    @foreach ($permissions->groupBy(fn($p) => explode('.', $p->name)[0]) as $module => $modulePermissions)
                      <tr>
                        <td class="text-nowrap fw-medium align-top pt-4">{{ ucfirst($module) }}</td>
                        <td>
                          <div class="row g-2">
                            @foreach ($modulePermissions as $permission)
                              <div class="col-md-4 col-sm-6">
                                <div class="form-check mb-0">
                                  <input class="form-check-input permission-checkbox" type="checkbox" id="perm_{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}" />
                                  <label class="form-check-label" for="perm_{{ $permission->id }}">{{ ucfirst(explode('.', $permission->name)[1] ?? $permission->name) }}</label>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <!-- Permission table -->
            </div>
            <div class="col-12 text-center">
              <button type="submit" class="btn btn-primary me-3">Submit</button>
              <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
            </div>
          </form>
          <!--/ Add role form -->
        </div>
      </div>
    </div>
  </div>
  <!--/ Add Role Modal -->
@endsection
