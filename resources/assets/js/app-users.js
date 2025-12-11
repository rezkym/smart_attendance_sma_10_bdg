/**
 * App Users Management
 * Users management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const offcanvasAddUser = document.getElementById('offcanvasAddUser');
  const addNewUserForm = document.getElementById('addNewUserForm');
  const userIdInput = document.getElementById('user_id');
  const userNameInput = document.getElementById('add-user-fullname');
  const userEmailInput = document.getElementById('add-user-email');
  const userPasswordInput = document.getElementById('add-user-password');
  const userPasswordConfirmInput = document.getElementById('add-user-password-confirmation');
  const userRoleSelect = document.getElementById('user-role');
  const offcanvasTitle = document.getElementById('offcanvasAddUserLabel');
  const passwordHint = document.querySelector('.password-hint');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtUserTable = document.querySelector('.datatables-users');

  // Base URLs
  const usersBaseUrl = '/admin/users';

  // Initialize Select2 for roles
  if (userRoleSelect) {
    $(userRoleSelect).select2({
      dropdownParent: offcanvasAddUser,
      placeholder: 'Select roles',
      allowClear: true
    });
  }

  // Users DataTable with server-side processing
  let dt_User;
  if (dtUserTable) {
    dt_User = new DataTable(dtUserTable, {
      ajax: {
        url: `${usersBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'roles_list' },
        { data: 'actions' }
      ],
      columnDefs: [
        {
          // For Responsive
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
          // User
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full) {
            const name = full.name || '';
            const email = full.email || '';
            const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
            const states = ['success', 'danger', 'warning', 'info', 'primary', 'secondary'];
            const state = states[Math.floor(Math.random() * states.length)];

            return `
              <div class="d-flex justify-content-left align-items-center">
                <div class="avatar-wrapper">
                  <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <span class="text-heading fw-medium">${name}</span>
                  <small class="text-muted">${email}</small>
                </div>
              </div>
            `;
          }
        },
        {
          // Email
          targets: 3,
          render: function (data, type, full) {
            return `<span>${full.email || ''}</span>`;
          }
        },
        {
          // Role
          targets: 4,
          render: function (data, type, full) {
            const roles = full.roles_list || [];
            // UI Configuration: Role-to-color mapping for badge styling only.
            const roleBadgeColors = {
              admin: 'danger',
              manager: 'warning',
              editor: 'info',
              user: 'success',
              teacher: 'primary',
              student: 'secondary'
            };

            return roles.map(role => {
              const colorKey = role.toLowerCase();
              const color = roleBadgeColors[colorKey] || 'primary';
              return `<span class="badge rounded-pill bg-label-${color}">${role}</span>`;
            }).join(' ') || '<span class="text-muted">No role</span>';
          }
        },
        {
          // Actions
          targets: 5,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            return `
              <div class="d-flex align-items-center">
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-user" data-user-id="${full.id}">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-user" data-user-id="${full.id}" data-user-name="${full.name}">
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
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: 'Show _MENU_'
              }
            }
          ]
        },
        topEnd: {
          features: [
            {
              search: {
                placeholder: 'Search User',
                text: '_INPUT_'
              }
            }
          ]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
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
              const data = row.data();
              return 'Details of ' + data.name;
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== ''
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              div.appendChild(table);
              table.classList.add('table');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              return div;
            }
            return false;
          }
        }
      }
    });
  }

  // Reset offcanvas on close
  if (offcanvasAddUser) {
    offcanvasAddUser.addEventListener('hidden.bs.offcanvas', function () {
      resetUserForm();
    });
  }

  // Edit user button click (delegated)
  if (dtUserTable) {
    dtUserTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-user');
      if (editBtn) {
        const userId = editBtn.getAttribute('data-user-id');
        loadUserData(userId);
      }

      const deleteBtn = e.target.closest('.delete-user');
      if (deleteBtn) {
        const userId = deleteBtn.getAttribute('data-user-id');
        const userName = deleteBtn.getAttribute('data-user-name');
        deleteUser(userId, userName);
      }
    });
  }

  // Form submission
  if (addNewUserForm) {
    addNewUserForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const userId = userIdInput?.value;
      const userName = userNameInput?.value.trim();
      const userEmail = userEmailInput?.value.trim();
      const userPassword = userPasswordInput?.value;
      const userPasswordConfirm = userPasswordConfirmInput?.value;
      const selectedRoles = $(userRoleSelect).val() || [];

      // Basic validation
      if (!userName) {
        showAlert('error', 'Validation Error', 'Name is required.');
        return;
      }

      if (!userEmail) {
        showAlert('error', 'Validation Error', 'Email is required.');
        return;
      }

      const isEdit = userId !== '';
      
      // Password validation for create mode
      if (!isEdit && !userPassword) {
        showAlert('error', 'Validation Error', 'Password is required.');
        return;
      }

      if (userPassword && userPassword !== userPasswordConfirm) {
        showAlert('error', 'Validation Error', 'Password confirmation does not match.');
        return;
      }

      const url = isEdit ? `${usersBaseUrl}/${userId}` : usersBaseUrl;
      const method = isEdit ? 'PUT' : 'POST';

      const payload = {
        name: userName,
        email: userEmail,
        roles: selectedRoles
      };

      // Only include password if provided
      if (userPassword) {
        payload.password = userPassword;
        payload.password_confirmation = userPasswordConfirm;
      }

      fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'Success!', data.message).then(() => {
              bootstrap.Offcanvas.getInstance(offcanvasAddUser).hide();
              dt_User.ajax.reload();
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
  function resetUserForm() {
    if (addNewUserForm) addNewUserForm.reset();
    if (userIdInput) userIdInput.value = '';
    if (userNameInput) userNameInput.value = '';
    if (userEmailInput) userEmailInput.value = '';
    if (userPasswordInput) userPasswordInput.value = '';
    if (userPasswordConfirmInput) userPasswordConfirmInput.value = '';
    if (userRoleSelect) $(userRoleSelect).val(null).trigger('change');
    if (offcanvasTitle) offcanvasTitle.textContent = 'Add User';
    if (passwordHint) passwordHint.style.display = 'none';
  }

  function loadUserData(userId) {
    fetch(`${usersBaseUrl}/${userId}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (userIdInput) userIdInput.value = data.data.id;
          if (userNameInput) userNameInput.value = data.data.name;
          if (userEmailInput) userEmailInput.value = data.data.email;
          if (userRoleSelect) {
            $(userRoleSelect).val(data.data.roles).trigger('change');
          }
          if (offcanvasTitle) offcanvasTitle.textContent = 'Edit User';
          if (passwordHint) passwordHint.style.display = 'block';

          // Show the offcanvas
          const offcanvas = new bootstrap.Offcanvas(offcanvasAddUser);
          offcanvas.show();
        } else {
          showAlert('error', 'Error!', data.message || 'Failed to load user data.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error!', 'An unexpected error occurred.');
      });
  }

  function deleteUser(userId, userName) {
    Swal.fire({
      title: 'Delete User?',
      text: `Are you sure you want to delete "${userName}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-danger me-3',
        cancelButton: 'btn btn-outline-secondary'
      },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${usersBaseUrl}/${userId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Deleted!', data.message).then(() => {
                dt_User.ajax.reload();
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
      customClass: {
        confirmButton: 'btn btn-primary'
      },
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
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);
});
