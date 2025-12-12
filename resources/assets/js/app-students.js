/**
 * App Students Management
 * Students management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const offcanvasAddStudent = document.getElementById('offcanvasAddStudent');
  const addNewStudentForm = document.getElementById('addNewStudentForm');
  const studentIdInput = document.getElementById('student_id');
  const studentNisnInput = document.getElementById('add-student-nisn');
  const studentNisInput = document.getElementById('add-student-nis');
  const studentFullNameInput = document.getElementById('add-student-full-name');
  const studentGenderSelect = document.getElementById('add-student-gender');
  const studentClassroomSelect = document.getElementById('add-student-classroom');
  const studentBirthPlaceInput = document.getElementById('add-student-birth-place');
  const studentBirthDateInput = document.getElementById('add-student-birth-date');
  const studentAddressInput = document.getElementById('add-student-address');
  const studentPhoneInput = document.getElementById('add-student-phone');
  const studentRfidInput = document.getElementById('add-student-rfid');
  const studentEnrollmentDateInput = document.getElementById('add-student-enrollment-date');
  const studentNotesInput = document.getElementById('add-student-notes');
  const studentIsActiveInput = document.getElementById('add-student-is-active');
  const offcanvasTitle = document.getElementById('offcanvasAddStudentLabel');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtStudentTable = document.querySelector('.datatables-students');

  // Base URLs
  const studentsBaseUrl = '/admin/students';
  let isEditMode = false;
  let select2Initialized = false;

  // Initialize Select2 for classroom dropdown
  function initSelect2() {
    if (studentClassroomSelect && !select2Initialized) {
      $(studentClassroomSelect).select2({
        dropdownParent: $('#offcanvasAddStudent'),
        placeholder: 'Select a classroom...',
        allowClear: true
      });
      select2Initialized = true;
    }
  }

  // Load available classrooms for Select2
  function loadAvailableClassrooms() {
    fetch(`${studentsBaseUrl}/available-classrooms`, {
      method: 'GET',
      headers: {
        Accept: 'application/json'
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Clear existing options
          $(studentClassroomSelect).empty();
          $(studentClassroomSelect).append(new Option('No Classroom Assigned', '', true, true));

          // Add classrooms grouped by grade level
          data.data.forEach(classroom => {
            const option = new Option(`${classroom.name} (Grade ${classroom.grade_level})`, classroom.id, false, false);
            $(studentClassroomSelect).append(option);
          });

          $(studentClassroomSelect).trigger('change');
        }
      })
      .catch(error => {
        console.error('Error loading classrooms:', error);
      });
  }

  // Students DataTable
  let dt_Student;
  if (dtStudentTable) {
    dt_Student = new DataTable(dtStudentTable, {
      ajax: {
        url: `${studentsBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'nisn' },
        { data: 'full_name' },
        { data: 'classroom_name' },
        { data: 'gender_label' },
        { data: 'status' },
        { data: 'actions' }
      ],
      columnDefs: [
        {
          className: 'control',
          orderable: false,
          searchable: false,
          responsivePriority: 2,
          targets: 0,
          render: function () {
            return '';
          }
        },
        {
          targets: 1,
          searchable: false,
          visible: false
        },
        {
          targets: 2,
          render: function (data, type, full) {
            return `<span class="badge bg-label-secondary">${full.nisn || '-'}</span>`;
          }
        },
        {
          targets: 3,
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="fw-medium">${full.full_name || ''}</span>`;
          }
        },
        {
          targets: 4,
          render: function (data, type, full) {
            return full.classroom_name || '<span class="text-muted">-</span>';
          }
        },
        {
          targets: 5,
          render: function (data, type, full) {
            const genderLabel = full.gender_label || '';
            if (genderLabel === 'Laki-laki') {
              return '<span class="badge rounded-pill bg-label-primary">Laki-laki</span>';
            }
            if (genderLabel === 'Perempuan') {
              return '<span class="badge rounded-pill bg-label-info">Perempuan</span>';
            }
            return '-';
          }
        },
        {
          targets: 6,
          render: function (data, type, full) {
            const isActive = full.status;
            if (isActive) {
              return '<span class="badge rounded-pill bg-label-success">Active</span>';
            }
            return '<span class="badge rounded-pill bg-label-secondary">Inactive</span>';
          }
        },
        {
          targets: 7,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            return `
              <div class="d-flex align-items-center">
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-student" data-id="${full.id}">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-student" data-id="${full.id}" data-name="${full.full_name}">
                  <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
                </a>
              </div>
            `;
          }
        }
      ],
      order: [[3, 'asc']],
      layout: {
        topStart: {
          rowClass: 'row mx-2',
          features: [{ pageLength: { menu: [10, 25, 50, 100], text: 'Show _MENU_' } }]
        },
        topEnd: {
          features: [{ search: { placeholder: 'Search Student', text: '_INPUT_' } }]
        },
        bottomStart: { rowClass: 'row mx-3 justify-content-between', features: ['info'] },
        bottomEnd: 'paging'
      },
      language: {
        paginate: {
          next: '<i class="icon-base ri ri-arrow-right-s-line scaleX-n1-rtl icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line scaleX-n1-rtl icon-22px"></i>',
          first: '<i class="icon-base ri ri-skip-back-mini-line scaleX-n1-rtl icon-22px"></i>',
          last: '<i class="icon-base ri ri-skip-forward-mini-line scaleX-n1-rtl icon-22px"></i>'
        }
      },
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              return 'Details of ' + row.data().full_name;
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(col => (col.title !== '' ? `<tr><td>${col.title}:</td><td>${col.data}</td></tr>` : ''))
              .join('');
            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              div.innerHTML = `<table class="table"><tbody>${data}</tbody></table>`;
              return div;
            }
            return false;
          }
        }
      }
    });
  }

  // Initialize Select2 when offcanvas opens
  if (offcanvasAddStudent) {
    offcanvasAddStudent.addEventListener('show.bs.offcanvas', function () {
      initSelect2();
      loadAvailableClassrooms();
    });

    offcanvasAddStudent.addEventListener('hidden.bs.offcanvas', function () {
      resetForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtStudentTable) {
    dtStudentTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-student');
      if (editBtn) {
        loadStudentData(editBtn.getAttribute('data-id'));
      }

      const deleteBtn = e.target.closest('.delete-student');
      if (deleteBtn) {
        deleteStudent(deleteBtn.getAttribute('data-id'), deleteBtn.getAttribute('data-name'));
      }
    });
  }

  // Form submission
  if (addNewStudentForm) {
    addNewStudentForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const id = studentIdInput?.value;
      const nisn = studentNisnInput?.value.trim();
      const nis = studentNisInput?.value.trim();
      const fullName = studentFullNameInput?.value.trim();
      const gender = studentGenderSelect?.value;
      const classroomId = $(studentClassroomSelect).val();
      const birthPlace = studentBirthPlaceInput?.value.trim();
      const birthDate = studentBirthDateInput?.value;
      const address = studentAddressInput?.value.trim();
      const phoneNumber = studentPhoneInput?.value.trim();
      const rfidCardNumber = studentRfidInput?.value.trim();
      const enrollmentDate = studentEnrollmentDateInput?.value;
      const notes = studentNotesInput?.value.trim();
      const isActive = studentIsActiveInput?.checked || false;

      // Basic validation
      if (!nisn || !nis || !fullName || !gender) {
        showAlert('error', 'Validation Error', 'Please fill in all required fields (NISN, NIS, Full Name, Gender).');
        return;
      }

      const url = isEditMode ? `${studentsBaseUrl}/${id}` : studentsBaseUrl;
      const method = isEditMode ? 'PUT' : 'POST';

      const payload = {
        nisn: nisn,
        nis: nis,
        full_name: fullName,
        gender: gender,
        classroom_id: classroomId || null,
        birth_place: birthPlace || null,
        birth_date: birthDate || null,
        address: address || null,
        phone_number: phoneNumber || null,
        rfid_card_number: rfidCardNumber || null,
        enrollment_date: enrollmentDate || null,
        notes: notes || null,
        is_active: isActive
      };

      fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json'
        },
        body: JSON.stringify(payload)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'Success!', data.message).then(() => {
              bootstrap.Offcanvas.getInstance(offcanvasAddStudent).hide();
              dt_Student.ajax.reload();
              window.location.reload();
            });
          } else {
            let errorMessage = data.message || 'Something went wrong.';
            if (data.errors) {
              errorMessage = Object.values(data.errors).flat().join('\n');
            }
            showAlert('error', 'Error!', errorMessage);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('error', 'Error!', 'An unexpected error occurred.');
        });
    });
  }

  // Helper Functions
  function resetForm() {
    if (addNewStudentForm) addNewStudentForm.reset();
    if (studentIdInput) studentIdInput.value = '';
    if (studentNisnInput) studentNisnInput.value = '';
    if (studentNisInput) studentNisInput.value = '';
    if (studentFullNameInput) studentFullNameInput.value = '';
    if (studentGenderSelect) studentGenderSelect.value = '';
    if (studentBirthPlaceInput) studentBirthPlaceInput.value = '';
    if (studentBirthDateInput) studentBirthDateInput.value = '';
    if (studentAddressInput) studentAddressInput.value = '';
    if (studentPhoneInput) studentPhoneInput.value = '';
    if (studentRfidInput) studentRfidInput.value = '';
    if (studentEnrollmentDateInput) studentEnrollmentDateInput.value = '';
    if (studentNotesInput) studentNotesInput.value = '';
    if (studentIsActiveInput) studentIsActiveInput.checked = true;
    if (offcanvasTitle) offcanvasTitle.textContent = 'Add Student';

    // Reset Select2
    $(studentClassroomSelect).val('').trigger('change');

    // Reset to add mode
    isEditMode = false;
  }

  function loadStudentData(id) {
    fetch(`${studentsBaseUrl}/${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          isEditMode = true;
          if (studentIdInput) studentIdInput.value = data.data.id;
          if (studentNisnInput) studentNisnInput.value = data.data.nisn || '';
          if (studentNisInput) studentNisInput.value = data.data.nis || '';
          if (studentFullNameInput) studentFullNameInput.value = data.data.full_name || '';
          if (studentGenderSelect) studentGenderSelect.value = data.data.gender || '';
          if (studentBirthPlaceInput) studentBirthPlaceInput.value = data.data.birth_place || '';
          if (studentBirthDateInput) studentBirthDateInput.value = data.data.birth_date || '';
          if (studentAddressInput) studentAddressInput.value = data.data.address || '';
          if (studentPhoneInput) studentPhoneInput.value = data.data.phone_number || '';
          if (studentRfidInput) studentRfidInput.value = data.data.rfid_card_number || '';
          if (studentEnrollmentDateInput) studentEnrollmentDateInput.value = data.data.enrollment_date || '';
          if (studentNotesInput) studentNotesInput.value = data.data.notes || '';
          if (studentIsActiveInput) studentIsActiveInput.checked = data.data.is_active;
          if (offcanvasTitle) offcanvasTitle.textContent = 'Edit Student';

          // Load classrooms first, then set value
          loadAvailableClassrooms();
          setTimeout(() => {
            if (data.data.classroom_id) {
              $(studentClassroomSelect).val(data.data.classroom_id).trigger('change');
            }
          }, 500);

          const offcanvas = new bootstrap.Offcanvas(offcanvasAddStudent);
          offcanvas.show();
        } else {
          showAlert('error', 'Error!', data.message || 'Failed to load data.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error!', 'An unexpected error occurred.');
      });
  }

  function deleteStudent(id, name) {
    Swal.fire({
      title: 'Delete Student?',
      text: `Are you sure you want to delete student "${name}"? This action cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${studentsBaseUrl}/${id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Deleted!', data.message).then(() => {
                dt_Student.ajax.reload();
                window.location.reload();
              });
            } else {
              showAlert('error', 'Error!', data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error!', 'An unexpected error occurred.');
          });
      }
    });
  }

  function showAlert(icon, title, text) {
    return Swal.fire({
      icon: icon,
      title: title,
      text: text,
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
  }

  // Filter form control styling
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-length', classToAdd: 'my-md-5 my-0 me-lg-2 me-md-1 me-2' },
      { selector: '.dt-search', classToRemove: 'mt-5', classToAdd: 'mb-sm-5 mb-0' },
      {
        selector: '.dt-layout-start',
        classToAdd: 'mt-5 mt-md-0 px-lg-5 pe-0 ps-2 d-flex justify-content-center',
        classToRemove: 'justify-content-between'
      },
      {
        selector: '.dt-layout-end',
        classToRemove: 'justify-content-between',
        classToAdd: 'justify-content-md-between justify-content-center d-flex'
      },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) classToRemove.split(' ').forEach(c => element.classList.remove(c));
        if (classToAdd) classToAdd.split(' ').forEach(c => element.classList.add(c));
      });
    });
  }, 100);
});
