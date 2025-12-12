/**
 * App Teachers Management
 * Teachers management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const offcanvasAddTeacher = document.getElementById('offcanvasAddTeacher');
  const addNewTeacherForm = document.getElementById('addNewTeacherForm');
  const teacherIdInput = document.getElementById('teacher_id');
  const teacherUserSelect = document.getElementById('add-teacher-user');
  const teacherNipInput = document.getElementById('add-teacher-nip');
  const teacherIsActiveInput = document.getElementById('add-teacher-is-active');
  const offcanvasTitle = document.getElementById('offcanvasAddTeacherLabel');
  const userSelectGroup = document.getElementById('user-select-group');
  const userInfoGroup = document.getElementById('user-info-group');
  const displayUserName = document.getElementById('display-user-name');
  const displayUserEmail = document.getElementById('display-user-email');
  const displayUserGender = document.getElementById('display-user-gender');
  const displayUserPhone = document.getElementById('display-user-phone');
  const displayUserBirthplace = document.getElementById('display-user-birthplace');
  const displayUserBirthdate = document.getElementById('display-user-birthdate');
  const displayUserAddress = document.getElementById('display-user-address');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtTeacherTable = document.querySelector('.datatables-teachers');

  // Base URLs
  const teachersBaseUrl = '/admin/teachers';
  let isEditMode = false;
  let select2Initialized = false;

  // Initialize Select2
  function initSelect2() {
    if (teacherUserSelect && !select2Initialized) {
      $(teacherUserSelect).select2({
        dropdownParent: $('#offcanvasAddTeacher'),
        placeholder: 'Select a user...',
        allowClear: true
      });
      select2Initialized = true;
    }
  }

  // Load available users for Select2
  function loadAvailableUsers() {
    fetch(`${teachersBaseUrl}/available-users`, {
      method: 'GET',
      headers: {
        Accept: 'application/json'
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Clear existing options
          $(teacherUserSelect).empty();
          $(teacherUserSelect).append(new Option('Select a user...', '', true, true));

          // Add users
          data.data.forEach(user => {
            const option = new Option(`${user.name} (${user.email})`, user.id, false, false);
            $(teacherUserSelect).append(option);
          });

          $(teacherUserSelect).trigger('change');
        }
      })
      .catch(error => {
        console.error('Error loading users:', error);
      });
  }

  // Teachers DataTable
  let dt_Teacher;
  if (dtTeacherTable) {
    dt_Teacher = new DataTable(dtTeacherTable, {
      ajax: {
        url: `${teachersBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'user_name' },
        { data: 'user_email' },
        { data: 'nip' },
        { data: 'user_phone' },
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
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="fw-medium">${full.user_name || ''}</span>`;
          }
        },
        {
          targets: 3,
          render: function (data, type, full) {
            return `<span class="text-muted">${full.user_email || ''}</span>`;
          }
        },
        {
          targets: 4,
          render: function (data, type, full) {
            const nip = full.nip || '-';
            return `<span class="badge bg-label-secondary">${nip}</span>`;
          }
        },
        {
          targets: 5,
          render: function (data, type, full) {
            return full.user_phone || '-';
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
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-teacher" data-id="${full.id}">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-teacher" data-id="${full.id}" data-name="${full.user_name}">
                  <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
                </a>
              </div>
            `;
          }
        }
      ],
      order: [[2, 'asc']],
      layout: {
        topStart: {
          rowClass: 'row mx-2',
          features: [{ pageLength: { menu: [10, 25, 50, 100], text: 'Show _MENU_' } }]
        },
        topEnd: {
          features: [{ search: { placeholder: 'Search Teacher', text: '_INPUT_' } }]
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
              return 'Details of ' + row.data().user_name;
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
  if (offcanvasAddTeacher) {
    offcanvasAddTeacher.addEventListener('show.bs.offcanvas', function () {
      initSelect2();
      if (!isEditMode) {
        loadAvailableUsers();
      }
    });

    offcanvasAddTeacher.addEventListener('hidden.bs.offcanvas', function () {
      resetForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtTeacherTable) {
    dtTeacherTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-teacher');
      if (editBtn) {
        loadTeacherData(editBtn.getAttribute('data-id'));
      }

      const deleteBtn = e.target.closest('.delete-teacher');
      if (deleteBtn) {
        deleteTeacher(deleteBtn.getAttribute('data-id'), deleteBtn.getAttribute('data-name'));
      }
    });
  }

  // Form submission
  if (addNewTeacherForm) {
    addNewTeacherForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const id = teacherIdInput?.value;
      const userId = $(teacherUserSelect).val();
      const nip = teacherNipInput?.value.trim();
      const isActive = teacherIsActiveInput?.checked || false;

      // Validation for add mode
      if (!isEditMode && !userId) {
        showAlert('error', 'Validation Error', 'Please select a user.');
        return;
      }

      const url = isEditMode ? `${teachersBaseUrl}/${id}` : teachersBaseUrl;
      const method = isEditMode ? 'PUT' : 'POST';

      const payload = {
        nip: nip || null,
        is_active: isActive
      };

      // Only include user_id for create
      if (!isEditMode) {
        payload.user_id = parseInt(userId);
      }

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
              bootstrap.Offcanvas.getInstance(offcanvasAddTeacher).hide();
              dt_Teacher.ajax.reload();
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
    if (addNewTeacherForm) addNewTeacherForm.reset();
    if (teacherIdInput) teacherIdInput.value = '';
    if (teacherNipInput) teacherNipInput.value = '';
    if (teacherIsActiveInput) teacherIsActiveInput.checked = true;
    if (offcanvasTitle) offcanvasTitle.textContent = 'Add Teacher';

    // Reset to add mode
    isEditMode = false;
    if (userSelectGroup) userSelectGroup.style.display = 'block';
    if (userInfoGroup) userInfoGroup.style.display = 'none';
    $(teacherUserSelect).val('').trigger('change');

    // Reset profile display
    if (displayUserName) displayUserName.textContent = '';
    if (displayUserEmail) displayUserEmail.textContent = '';
    if (displayUserGender) displayUserGender.textContent = '-';
    if (displayUserPhone) displayUserPhone.textContent = '-';
    if (displayUserBirthplace) displayUserBirthplace.textContent = '-';
    if (displayUserBirthdate) displayUserBirthdate.textContent = '-';
    if (displayUserAddress) displayUserAddress.textContent = '-';
  }

  function loadTeacherData(id) {
    fetch(`${teachersBaseUrl}/${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          isEditMode = true;

          // Teacher fields
          if (teacherIdInput) teacherIdInput.value = data.data.id;
          if (teacherNipInput) teacherNipInput.value = data.data.nip || '';
          if (teacherIsActiveInput) teacherIsActiveInput.checked = data.data.is_active;
          if (offcanvasTitle) offcanvasTitle.textContent = 'Edit Teacher';

          // Show user info, hide select
          if (userSelectGroup) userSelectGroup.style.display = 'none';
          if (userInfoGroup) userInfoGroup.style.display = 'block';

          // User account display
          if (displayUserName) displayUserName.textContent = data.data.full_name || data.data.name || '';
          if (displayUserEmail) displayUserEmail.textContent = data.data.email || '';

          // User profile display
          if (displayUserGender) displayUserGender.textContent = data.data.gender_label || '-';
          if (displayUserPhone) displayUserPhone.textContent = data.data.phone_number || '-';
          if (displayUserBirthplace) displayUserBirthplace.textContent = data.data.birth_place || '-';
          if (displayUserBirthdate) displayUserBirthdate.textContent = data.data.birth_date || '-';
          if (displayUserAddress) displayUserAddress.textContent = data.data.address || '-';

          const offcanvas = new bootstrap.Offcanvas(offcanvasAddTeacher);
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

  function deleteTeacher(id, name) {
    Swal.fire({
      title: 'Delete Teacher?',
      text: `Are you sure you want to delete teacher "${name}"? The user account will remain but lose the teacher role.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${teachersBaseUrl}/${id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Deleted!', data.message).then(() => {
                dt_Teacher.ajax.reload();
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

