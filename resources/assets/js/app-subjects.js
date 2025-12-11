/**
 * App Subjects Management
 * Subjects management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const offcanvasAddSubject = document.getElementById('offcanvasAddSubject');
  const addNewSubjectForm = document.getElementById('addNewSubjectForm');
  const subjectIdInput = document.getElementById('subject_id');
  const subjectCodeInput = document.getElementById('add-subject-code');
  const subjectNameInput = document.getElementById('add-subject-name');
  const subjectDescriptionInput = document.getElementById('add-subject-description');
  const subjectIsActiveInput = document.getElementById('add-subject-is-active');
  const offcanvasTitle = document.getElementById('offcanvasAddSubjectLabel');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtSubjectTable = document.querySelector('.datatables-subjects');

  // Base URLs
  const subjectsBaseUrl = '/admin/subjects';

  // Subjects DataTable
  let dt_Subject;
  if (dtSubjectTable) {
    dt_Subject = new DataTable(dtSubjectTable, {
      ajax: {
        url: `${subjectsBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'code' },
        { data: 'name' },
        { data: 'description' },
        { data: 'status' },
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
          // Code
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="badge bg-label-primary">${full.code || ''}</span>`;
          }
        },
        {
          // Name
          targets: 3,
          render: function (data, type, full) {
            return `<span class="fw-medium">${full.name || ''}</span>`;
          }
        },
        {
          // Description
          targets: 4,
          render: function (data, type, full) {
            const description = full.description || '-';
            return `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${description}">${description}</span>`;
          }
        },
        {
          // Status
          targets: 5,
          render: function (data, type, full) {
            const isActive = full.status;
            if (isActive) {
              return '<span class="badge rounded-pill bg-label-success">Active</span>';
            }
            return '<span class="badge rounded-pill bg-label-secondary">Inactive</span>';
          }
        },
        {
          // Actions
          targets: 6,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            return `
              <div class="d-flex align-items-center">
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-subject" data-id="${full.id}">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-subject" data-id="${full.id}" data-name="${full.name}">
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
                placeholder: 'Search Subject',
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
  if (offcanvasAddSubject) {
    offcanvasAddSubject.addEventListener('hidden.bs.offcanvas', function () {
      resetForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtSubjectTable) {
    dtSubjectTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-subject');
      if (editBtn) {
        const id = editBtn.getAttribute('data-id');
        loadSubjectData(id);
      }

      const deleteBtn = e.target.closest('.delete-subject');
      if (deleteBtn) {
        const id = deleteBtn.getAttribute('data-id');
        const name = deleteBtn.getAttribute('data-name');
        deleteSubject(id, name);
      }
    });
  }

  // Form submission
  if (addNewSubjectForm) {
    addNewSubjectForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const id = subjectIdInput?.value;
      const code = subjectCodeInput?.value.trim();
      const name = subjectNameInput?.value.trim();
      const description = subjectDescriptionInput?.value.trim();
      const isActive = subjectIsActiveInput?.checked || false;

      // Basic validation
      if (!code) {
        showAlert('error', 'Validation Error', 'Code is required.');
        return;
      }

      if (!name) {
        showAlert('error', 'Validation Error', 'Name is required.');
        return;
      }

      const isEdit = id !== '';
      const url = isEdit ? `${subjectsBaseUrl}/${id}` : subjectsBaseUrl;
      const method = isEdit ? 'PUT' : 'POST';

      const payload = {
        code: code,
        name: name,
        description: description || null,
        is_active: isActive
      };

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
              bootstrap.Offcanvas.getInstance(offcanvasAddSubject).hide();
              dt_Subject.ajax.reload();
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
    if (addNewSubjectForm) addNewSubjectForm.reset();
    if (subjectIdInput) subjectIdInput.value = '';
    if (subjectCodeInput) subjectCodeInput.value = '';
    if (subjectNameInput) subjectNameInput.value = '';
    if (subjectDescriptionInput) subjectDescriptionInput.value = '';
    if (subjectIsActiveInput) subjectIsActiveInput.checked = true;
    if (offcanvasTitle) offcanvasTitle.textContent = 'Add Subject';
  }

  function loadSubjectData(id) {
    fetch(`${subjectsBaseUrl}/${id}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (subjectIdInput) subjectIdInput.value = data.data.id;
          if (subjectCodeInput) subjectCodeInput.value = data.data.code;
          if (subjectNameInput) subjectNameInput.value = data.data.name;
          if (subjectDescriptionInput) subjectDescriptionInput.value = data.data.description || '';
          if (subjectIsActiveInput) subjectIsActiveInput.checked = data.data.is_active;
          if (offcanvasTitle) offcanvasTitle.textContent = 'Edit Subject';

          // Show the offcanvas
          const offcanvas = new bootstrap.Offcanvas(offcanvasAddSubject);
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

  function deleteSubject(id, name) {
    Swal.fire({
      title: 'Delete Subject?',
      text: `Are you sure you want to delete "${name}"?`,
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
        fetch(`${subjectsBaseUrl}/${id}`, {
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
                dt_Subject.ajax.reload();
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
