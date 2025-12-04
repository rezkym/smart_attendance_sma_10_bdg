@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Teacher Management')

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
    @vite(['resources/js/admin/teacher-management.js'])
@endsection

@section('content')
    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Teacher Management</h4>
            <p class="text-muted mb-0">Kelola data guru, mata pelajaran, dan wali kelas.</p>
        </div>
        @can('teachers.create')
            <button type="button" class="btn btn-primary" id="createTeacherBtn" data-bs-toggle="modal"
                data-bs-target="#teacherModal">
                <i class="ri ri-add-line me-1"></i> Tambah Guru
            </button>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="filterSubject" class="form-label">Filter Mata Pelajaran</label>
                    <select id="filterSubject" class="form-select">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filterClassroom" class="form-label">Filter Wali Kelas</label>
                    <select id="filterClassroom" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach ($classrooms as $classroom)
                            <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="button" class="btn btn-primary" id="applyTeacherFilters">Terapkan Filter</button>
                    <button type="button" class="btn btn-outline-secondary" id="resetTeacherFilters">Reset</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="teachers-table"
                    data-ajax-url="{{ route('admin.teachers.index') }}"
                    data-store-url="{{ route('admin.teachers.store') }}"
                    data-show-url="{{ route('admin.teachers.show', ['teacher' => '__ID__']) }}"
                    data-update-url="{{ route('admin.teachers.update', ['teacher' => '__ID__']) }}"
                    data-destroy-url="{{ route('admin.teachers.destroy', ['teacher' => '__ID__']) }}"
                    data-subjects-url="{{ route('admin.teachers.assign-subjects', ['teacher' => '__ID__']) }}"
                    data-classroom-url="{{ route('admin.teachers.assign-classroom', ['teacher' => '__ID__']) }}">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>NIP</th>
                            <th>Spesialisasi</th>
                            <th>Mata Pelajaran</th>
                            <th>Wali Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Assign Homeroom --}}
    <div class="modal fade" id="homeroomModal" tabindex="-1" aria-labelledby="homeroomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="homeroomModalLabel">Atur Wali Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="homeroomFormErrors" class="alert alert-danger d-none" role="alert"></div>
                    <form id="homeroomForm">
                        @csrf
                        <input type="hidden" id="homeroomTeacherIdInput" name="teacher_id">

                        <div class="mb-3">
                            <label for="homeroomClassroomInput" class="form-label">Kelas</label>
                            <select id="homeroomClassroomInput" name="classroom_id" class="form-select">
                                <option value="">Tidak ada</option>
                                @foreach ($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block">Pilih kelas untuk ditetapkan sebagai wali kelas, atau pilih "Tidak ada" untuk melepas penugasan.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="homeroomForm" class="btn btn-primary" id="homeroomFormSubmit">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Create/Edit Teacher --}}
    <div class="modal fade" id="teacherModal" tabindex="-1" aria-labelledby="teacherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="teacherModalLabel">Tambah Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="teacherFormErrors" class="alert alert-danger d-none" role="alert"></div>
                    <form id="teacherForm">
                        @csrf
                        <input type="hidden" id="teacherIdInput" name="teacher_id">

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="mb-3">Pilih User</h6>
                            </div>
                            <div class="col-12" id="userSelectWrapper">
                                <label for="userSelect" class="form-label">User (dengan role Teacher) <span class="text-danger">*</span></label>
                                <select id="userSelect" name="user_id" class="form-select select2" required>
                                    <option value="">Pilih user...</option>
                                </select>
                                <small class="text-muted">User harus memiliki role 'teacher'. Buat user terlebih dahulu di User Management jika belum ada.</small>
                            </div>

                            <div class="col-12">
                                <hr class="my-3">
                                <h6 class="mb-3">Data Guru</h6>
                            </div>
                            <div class="col-md-6">
                                <label for="teacherNumberInput" class="form-label">NIP (Nomor Induk Pegawai) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="teacherNumberInput" name="teacher_number" required>
                            </div>
                            <div class="col-md-6">
                                <label for="specializationInput" class="form-label">Spesialisasi</label>
                                <input type="text" class="form-control" id="specializationInput" name="specialization"
                                    placeholder="Contoh: Matematika, Fisika, dll.">
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
                            <div class="col-12">
                                <label for="addressInput" class="form-label">Alamat</label>
                                <textarea class="form-control" id="addressInput" name="address" rows="2"
                                    placeholder="Opsional"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="teacherForm" class="btn btn-primary" id="teacherFormSubmit">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Assign Subjects --}}
    <div class="modal fade" id="subjectsModal" tabindex="-1" aria-labelledby="subjectsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subjectsModalLabel">Kelola Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="subjectsFormErrors" class="alert alert-danger d-none" role="alert"></div>
                    <form id="subjectsForm">
                        @csrf
                        <input type="hidden" id="subjectsTeacherIdInput" name="teacher_id">

                        <div class="mb-3">
                            <label for="subjectsInput" class="form-label">Mata Pelajaran</label>
                            <select id="subjectsInput" name="subject_ids[]" class="form-select" multiple size="8">
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Gunakan Ctrl/Cmd + klik untuk memilih beberapa mata pelajaran.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="subjectsForm" class="btn btn-primary" id="subjectsFormSubmit">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection
