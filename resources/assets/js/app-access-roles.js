/**
 * App Access Roles
 * Role management with CRUD operations and Users DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const addRoleModal = document.getElementById('addRoleModal');
  const addRoleForm = document.getElementById('addRoleForm');
  const roleIdInput = document.getElementById('roleId');
  const roleNameInput = document.getElementById('modalRoleName');
  const roleTitle = document.querySelector('.role-title');
  const selectAllCheckbox = document.getElementById('selectAll');
  const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtUserTable = document.querySelector('.datatables-users');

  // Base URLs
  const rolesBaseUrl = '/admin/access-roles';

  // Users DataTable
  let dt_User;
  if (dtUserTable) {
    dt_User = new DataTable(dtUserTable, {
      ajax: {
        url: `${rolesBaseUrl}/users`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'roles' }
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
            const roles = full.roles || [];
            // UI Configuration: Role-to-color mapping for badge styling only.
            // This is NOT business logic - safe to modify for visual preferences.
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

  // Select All functionality
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function () {
      permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
    });

    // Update Select All when individual checkboxes change
    permissionCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function () {
        const allChecked = Array.from(permissionCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(permissionCheckboxes).some(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
      });
    });
  }

  // Reset modal on close
  if (addRoleModal) {
    addRoleModal.addEventListener('hidden.bs.modal', function () {
      resetRoleModal();
    });
  }

  // Add new role button click
  const addNewRoleBtn = document.querySelector('.add-new-role');
  if (addNewRoleBtn) {
    addNewRoleBtn.addEventListener('click', function () {
      resetRoleModal();
      if (roleTitle) roleTitle.textContent = 'Add New Role';
    });
  }

  // Edit role button click
  const roleEditBtns = document.querySelectorAll('.role-edit-modal');
  roleEditBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const roleId = this.getAttribute('data-role-id');
      if (roleTitle) roleTitle.textContent = 'Edit Role';
      loadRoleData(roleId);
    });
  });

  // Form submission
  if (addRoleForm) {
    addRoleForm.addEventListener('submit', function (e) {
      e.preventDefault();
      
      const roleId = roleIdInput?.value;
      const roleName = roleNameInput?.value.trim();
      const selectedPermissions = Array.from(permissionCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

      if (!roleName) {
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          text: 'Role name is required.',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        });
        return;
      }

      const isEdit = roleId !== '';
      const url = isEdit ? `${rolesBaseUrl}/${roleId}` : rolesBaseUrl;
      const method = isEdit ? 'PUT' : 'POST';

      fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          name: roleName,
          permissions: selectedPermissions
        })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: data.message,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            }).then(() => {
              // Close modal and reload page
              bootstrap.Modal.getInstance(addRoleModal).hide();
              window.location.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: data.message || 'Something went wrong.',
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred.',
            customClass: {
              confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
          });
        });
    });
  }

  // Delete role
  const deleteRoleBtns = document.querySelectorAll('.delete-role');
  deleteRoleBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const roleId = this.getAttribute('data-role-id');
      const roleName = this.getAttribute('data-role-name');

      Swal.fire({
        title: 'Delete Role?',
        text: `Are you sure you want to delete the role "${roleName}"?`,
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
          fetch(`${rolesBaseUrl}/${roleId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            }
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Deleted!',
                  text: data.message,
                  customClass: {
                    confirmButton: 'btn btn-primary'
                  },
                  buttonsStyling: false
                }).then(() => {
                  window.location.reload();
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error!',
                  text: data.message,
                  customClass: {
                    confirmButton: 'btn btn-primary'
                  },
                  buttonsStyling: false
                });
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An unexpected error occurred.',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
              });
            });
        }
      });
    });
  });

  // Helper Functions
  function resetRoleModal() {
    if (addRoleForm) addRoleForm.reset();
    if (roleIdInput) roleIdInput.value = '';
    if (roleNameInput) roleNameInput.value = '';
    permissionCheckboxes.forEach(cb => {
      cb.checked = false;
    });
    if (selectAllCheckbox) {
      selectAllCheckbox.checked = false;
      selectAllCheckbox.indeterminate = false;
    }
  }

  function loadRoleData(roleId) {
    fetch(`${rolesBaseUrl}/${roleId}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (roleIdInput) roleIdInput.value = data.data.id;
          if (roleNameInput) roleNameInput.value = data.data.name;
          
          // Check the appropriate permission checkboxes
          const rolePermissions = data.data.permissions || [];
          permissionCheckboxes.forEach(cb => {
            cb.checked = rolePermissions.includes(cb.value);
          });

          // Update Select All state
          const allChecked = Array.from(permissionCheckboxes).every(cb => cb.checked);
          const someChecked = Array.from(permissionCheckboxes).some(cb => cb.checked);
          if (selectAllCheckbox) {
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
          }
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: data.message || 'Failed to load role data.',
            customClass: {
              confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
          });
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'An unexpected error occurred.',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        });
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
