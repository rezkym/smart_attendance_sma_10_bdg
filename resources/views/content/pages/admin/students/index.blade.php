@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Student Management')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
        'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
        'resources/assets/vendor/libs/select2/select2.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
        'resources/assets/vendor/libs/select2/select2.js',
    ])
@endsection

@section('page-script')
    @vite(['resources/js/admin/student-management.js'])
@endsection

@section('content')
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Student Management</h4>
            <p class="text-muted mb-0">Kelola data siswa, RFID, dan penempatan kelas.</p>
        </div>
        @can('students.create')
            <button type="button" class="btn btn-primary" id="createStudentBtn" data-bs-toggle="modal"
                data-bs-target="#studentModal">
                <i class="ri ri-add-line me-1"></i> Tambah Siswa
            </button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="students-table"
                    data-ajax-url="{{ route('admin.students.index') }}"
                    data-store-url="{{ route('admin.students.store') }}"
                    data-show-url="{{ route('admin.students.show', ['student' => '__ID__']) }}"
                    data-update-url="{{ route('admin.students.update', ['student' => '__ID__']) }}"
                    data-destroy-url="{{ route('admin.students.destroy', ['student' => '__ID__']) }}"
                    data-rfid-url="{{ route('admin.students.assign-rfid', ['student' => '__ID__']) }}"
                    data-classroom-url="{{ route('admin.students.assign-classroom', ['student' => '__ID__']) }}">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>NIS</th>
                            <th>RFID</th>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="studentModal" tabindex="-1" aria-labelledby="studentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentModalLabel">Tambah Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="studentFormErrors" class="alert alert-danger d-none" role="alert"></div>
                    <form id="studentForm">
                        @csrf
                        <input type="hidden" id="studentIdInput" name="student_id">

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="mb-3">Pilih User</h6>
                            </div>
                            <div class="col-12" id="userSelectWrapper">
                                <label for="userSelect" class="form-label">User (dengan role Student) <span class="text-danger">*</span></label>
                                <select id="userSelect" name="user_id" class="form-select select2" required>
                                    <option value="">Pilih user...</option>
                                </select>
                                <small class="text-muted">User harus memiliki role 'student'. Buat user terlebih dahulu di User Management jika belum ada.</small>
                            </div>

                            <div class="col-12">
                                <hr class="my-3">
                                <h6 class="mb-3">Data Siswa</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="studentNumberInput" class="form-label">NIS (Nomor Induk Siswa) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="studentNumberInput" name="student_number" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rfidCardNumberInput" class="form-label">Nomor Kartu RFID</label>
                                <input type="text" class="form-control" id="rfidCardNumberInput" name="rfid_card_number"
                                    placeholder="Opsional">
                            </div>
                            <div class="col-md-6">
                                <label for="dateOfBirthInput" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="dateOfBirthInput" name="date_of_birth">
                            </div>
                            <div class="col-md-6">
                                <label for="genderInput" class="form-label">Jenis Kelamin</label>
                                <select id="genderInput" name="gender" class="form-select">
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="male">Laki-laki</option>
                                    <option value="female">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="phoneNumberInput" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" id="phoneNumberInput" name="phone_number"
                                    placeholder="Opsional">
                            </div>
                            <div class="col-md-6">
                                <label for="classroomInput" class="form-label">Kelas</label>
                                <select id="classroomInput" name="classroom_id" class="form-select">
                                    <option value="">Belum ditentukan</option>
                                    @foreach ($classrooms as $classroom)
                                        <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="addressInput" class="form-label">Alamat</label>
                                <textarea class="form-control" id="addressInput" name="address" rows="2"
                                    placeholder="Opsional"></textarea>
                            </div>

                            <div class="col-12">
                                <hr class="my-3">
                                <h6 class="mb-3">Data Orang Tua / Wali</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="parentNameInput" class="form-label">Nama Orang Tua / Wali</label>
                                <input type="text" class="form-control" id="parentNameInput" name="parent_name"
                                    placeholder="Opsional">
                            </div>
                            <div class="col-md-6">
                                <label for="parentPhoneInput" class="form-label">Nomor Telepon Orang Tua / Wali</label>
                                <input type="text" class="form-control" id="parentPhoneInput" name="parent_phone"
                                    placeholder="Opsional">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="studentForm" class="btn btn-primary" id="studentFormSubmit">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection
