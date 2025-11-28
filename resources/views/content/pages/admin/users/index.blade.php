@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'User Management')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    ])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('page-script')
    @vite(['resources/js/admin/user-management.js'])
@endsection

@section('content')
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">User Management</h4>
            <p class="text-muted mb-0">Kelola pengguna, role, dan hak akses.</p>
        </div>
        @can('users.create')
            <button type="button" class="btn btn-primary" id="createUserBtn" data-bs-toggle="modal"
                data-bs-target="#userModal">
                <i class="ri ri-add-line me-1"></i> Tambah User
            </button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="users-table"
                    data-ajax-url="{{ route('admin.users.index') }}"
                    data-store-url="{{ route('admin.users.store') }}"
                    data-show-url="{{ route('admin.users.show', ['user' => '__ID__']) }}"
                    data-update-url="{{ route('admin.users.update', ['user' => '__ID__']) }}"
                    data-destroy-url="{{ route('admin.users.destroy', ['user' => '__ID__']) }}">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Verified At</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="userFormErrors" class="alert alert-danger d-none" role="alert"></div>
                    <form id="userForm">
                        @csrf
                        <input type="hidden" id="userIdInput" name="user_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nameInput" class="form-label">Nama</label>
                                <input type="text" class="form-control" id="nameInput" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="emailInput" class="form-label">Email</label>
                                <input type="email" class="form-control" id="emailInput" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="passwordInput" class="form-label">Password</label>
                                <input type="password" class="form-control" id="passwordInput" name="password"
                                    placeholder="Minimal 6 karakter">
                                <small class="text-muted">Kosongkan saat edit jika tidak ingin mengganti password.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="rolesInput" class="form-label">Role</label>
                                <select id="rolesInput" name="roles[]" class="form-select" multiple>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih satu atau lebih role.</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="userForm" class="btn btn-primary" id="userFormSubmit">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection
