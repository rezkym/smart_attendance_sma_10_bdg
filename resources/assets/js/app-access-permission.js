/**
 * App Access Permission
 * Permission management with DataTables and CRUD operations
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const dataTablePermissions = document.querySelector('.datatables-permissions');
  const addPermissionModal = document.getElementById('addPermissionModal');
  const addPermissionForm = document.getElementById('addPermissionForm');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // Base URLs
  const permissionsBaseUrl = '/admin/access-permission';

  let dt_permission;

  // Initialize DataTable
  if (dataTablePermissions) {
    dt_permission = new DataTable(dataTablePermissions, {
      ajax: {
        url: `${permissionsBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'name' },
        { data: 'assigned_to' },
        { data: 'created_at_formatted' },
        { data: 'id' }
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
          // Name
          targets: 2,
          render: function (data, type, full) {
            return `<span class="text-nowrap text-heading">${full.name}</span>`;
          }
        },
        {
          // Assigned To
          targets: 3,
          orderable: false,
          render: function (data, type, full) {
            const assignedTo = full.assigned_to || [];
            let output = '';
            
            // UI Configuration: Role-to-color mapping for badge styling only.
            // This is NOT business logic - safe to modify for visual preferences.
            const roleBadgeColors = {
              admin: 'primary',
              manager: 'warning',
              user: 'success',
              teacher: 'info',
              student: 'secondary'
            };

            assignedTo.forEach(role => {
              const colorKey = role.toLowerCase();
              const color = roleBadgeColors[colorKey] || 'primary';
              output += `<span class="badge rounded-pill bg-label-${color} me-2">${role}</span>`;
            });

            return output || '<span class="text-muted">Not assigned</span>';
          }
        },
        {
          // Created Date
          targets: 4,
          orderable: false,
          render: function (data, type, full) {
            return `<span class="text-nowrap">${full.created_at_formatted || '-'}</span>`;
          }
        },
        {
          // Actions
          targets: -1,
          searchable: false,
          title: 'Actions',
          orderable: false,
          render: function (data, type, full) {
            return `
              <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill delete-permission text-body waves-effect" data-permission-id="${full.id}" data-permission-name="${full.name}">
                  <i class="icon-base ri ri-delete-bin-7-line icon-20px"></i>
                </button>
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
                placeholder: 'Search Permissions',
                text: '_INPUT_'
              }
            },
            {
              buttons: [
                {
                  text: `<i class="icon-base ri ri-add-line icon-sm me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Permission</span>`,
                  className: 'add-new btn btn-primary',
                  attr: {
                    'data-bs-toggle': 'modal',
                    'data-bs-target': '#addPermissionModal'
                  }
                }
              ]
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

    // Event delegation for dynamic buttons
    dataTablePermissions.addEventListener('click', function (e) {
      // Delete permission
      if (e.target.closest('.delete-permission')) {
        const btn = e.target.closest('.delete-permission');
        const permissionId = btn.getAttribute('data-permission-id');
        const permissionName = btn.getAttribute('data-permission-name');
        deletePermission(permissionId, permissionName);
      }
    });
  }

  // Add Permission Form Submission
  if (addPermissionForm) {
    addPermissionForm.addEventListener('submit', function (e) {
      e.preventDefault();
      
      const permissionName = document.getElementById('modalPermissionName')?.value.trim();

      if (!permissionName) {
        Swal.fire({
          icon: 'error',
          title: 'Validation Error',
          text: 'Permission name is required.',
          customClass: { confirmButton: 'btn btn-primary' },
          buttonsStyling: false
        });
        return;
      }

      fetch(permissionsBaseUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ name: permissionName })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: data.message,
              customClass: { confirmButton: 'btn btn-primary' },
              buttonsStyling: false
            }).then(() => {
              bootstrap.Modal.getInstance(addPermissionModal).hide();
              addPermissionForm.reset();
              dt_permission.ajax.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: data.message || 'Something went wrong.',
              customClass: { confirmButton: 'btn btn-primary' },
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
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
          });
        });
    });
  }



  function deletePermission(permissionId, permissionName) {
    Swal.fire({
      title: 'Delete Permission?',
      text: `Are you sure you want to delete "${permissionName}"?`,
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
        fetch(`${permissionsBaseUrl}/${permissionId}`, {
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
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
              }).then(() => {
                dt_permission.ajax.reload();
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message,
                customClass: { confirmButton: 'btn btn-primary' },
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
              customClass: { confirmButton: 'btn btn-primary' },
              buttonsStyling: false
            });
          });
      }
    });
  }

  // Filter form control styling
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-search', classToAdd: 'me-4' },
      { selector: '.dt-length', classToAdd: 'mb-0 mb-md-5' },
      { selector: '.dt-buttons', classToAdd: 'mb-0 w-auto' },
      { selector: '.dt-layout-start', classToAdd: 'mt-0 px-5' },
      {
        selector: '.dt-layout-end',
        classToAdd: 'justify-content-md-between justify-content-center d-flex',
        classToRemove: 'justify-content-between d-md-flex'
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
