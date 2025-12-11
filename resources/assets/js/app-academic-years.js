/**
 * App Academic Years Management
 * Academic Years management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const offcanvasAddAcademicYear = document.getElementById('offcanvasAddAcademicYear');
  const addNewAcademicYearForm = document.getElementById('addNewAcademicYearForm');
  const academicYearIdInput = document.getElementById('academic_year_id');
  const academicYearNameInput = document.getElementById('add-academic-year-name');
  const academicYearStartDateInput = document.getElementById('add-academic-year-start-date');
  const academicYearEndDateInput = document.getElementById('add-academic-year-end-date');
  const academicYearIsActiveInput = document.getElementById('add-academic-year-is-active');
  const academicYearDescriptionInput = document.getElementById('add-academic-year-description');
  const offcanvasTitle = document.getElementById('offcanvasAddAcademicYearLabel');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtAcademicYearTable = document.querySelector('.datatables-academic-years');

  // Base URLs
  const academicYearsBaseUrl = '/admin/academic-years';

  // Initialize Flatpickr for date inputs
  if (academicYearStartDateInput) {
    flatpickr(academicYearStartDateInput, {
      dateFormat: 'Y-m-d',
      allowInput: true
    });
  }

  if (academicYearEndDateInput) {
    flatpickr(academicYearEndDateInput, {
      dateFormat: 'Y-m-d',
      allowInput: true
    });
  }

  // Academic Years DataTable
  let dt_AcademicYear;
  if (dtAcademicYearTable) {
    dt_AcademicYear = new DataTable(dtAcademicYearTable, {
      ajax: {
        url: `${academicYearsBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'name' },
        { data: 'period' },
        { data: 'status' },
        { data: 'description' },
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
          // Name
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="fw-medium">${full.name || ''}</span>`;
          }
        },
        {
          // Period
          targets: 3,
          render: function (data, type, full) {
            return `<span>${full.period || ''}</span>`;
          }
        },
        {
          // Status
          targets: 4,
          render: function (data, type, full) {
            const isActive = full.status;
            if (isActive) {
              return '<span class="badge rounded-pill bg-label-success">Active</span>';
            }
            return '<span class="badge rounded-pill bg-label-secondary">Inactive</span>';
          }
        },
        {
          // Description
          targets: 5,
          render: function (data, type, full) {
            const description = full.description || '-';
            return `<span class="text-truncate d-inline-block" style="max-width: 150px;" title="${description}">${description}</span>`;
          }
        },
        {
          // Actions
          targets: 6,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            const isActive = full.status;
            let setActiveBtn = '';
            if (!isActive) {
              setActiveBtn = `
                <a href="javascript:;" class="btn btn-icon btn-text-success waves-effect waves-light rounded-pill set-active-year" data-id="${full.id}" data-name="${full.name}" title="Set as Active">
                  <i class="icon-base ri ri-checkbox-circle-line icon-22px"></i>
                </a>
              `;
            }
            return `
              <div class="d-flex align-items-center">
                ${setActiveBtn}
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-academic-year" data-id="${full.id}">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-academic-year" data-id="${full.id}" data-name="${full.name}" data-is-active="${isActive}">
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
                placeholder: 'Search Academic Year',
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
  if (offcanvasAddAcademicYear) {
    offcanvasAddAcademicYear.addEventListener('hidden.bs.offcanvas', function () {
      resetForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtAcademicYearTable) {
    dtAcademicYearTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-academic-year');
      if (editBtn) {
        const id = editBtn.getAttribute('data-id');
        loadAcademicYearData(id);
      }

      const deleteBtn = e.target.closest('.delete-academic-year');
      if (deleteBtn) {
        const id = deleteBtn.getAttribute('data-id');
        const name = deleteBtn.getAttribute('data-name');
        const isActive = deleteBtn.getAttribute('data-is-active') === 'true';
        deleteAcademicYear(id, name, isActive);
      }

      const setActiveBtn = e.target.closest('.set-active-year');
      if (setActiveBtn) {
        const id = setActiveBtn.getAttribute('data-id');
        const name = setActiveBtn.getAttribute('data-name');
        setActiveYear(id, name);
      }
    });
  }

  // Form submission
  if (addNewAcademicYearForm) {
    addNewAcademicYearForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const id = academicYearIdInput?.value;
      const name = academicYearNameInput?.value.trim();
      const startDate = academicYearStartDateInput?.value;
      const endDate = academicYearEndDateInput?.value;
      const isActive = academicYearIsActiveInput?.checked || false;
      const description = academicYearDescriptionInput?.value.trim();

      // Basic validation
      if (!name) {
        showAlert('error', 'Validation Error', 'Name is required.');
        return;
      }

      if (!startDate) {
        showAlert('error', 'Validation Error', 'Start date is required.');
        return;
      }

      if (!endDate) {
        showAlert('error', 'Validation Error', 'End date is required.');
        return;
      }

      const isEdit = id !== '';
      const url = isEdit ? `${academicYearsBaseUrl}/${id}` : academicYearsBaseUrl;
      const method = isEdit ? 'PUT' : 'POST';

      const payload = {
        name: name,
        start_date: startDate,
        end_date: endDate,
        is_active: isActive,
        description: description || null
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
              bootstrap.Offcanvas.getInstance(offcanvasAddAcademicYear).hide();
              dt_AcademicYear.ajax.reload();
              // Reload page to update stats
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
    if (addNewAcademicYearForm) addNewAcademicYearForm.reset();
    if (academicYearIdInput) academicYearIdInput.value = '';
    if (academicYearNameInput) academicYearNameInput.value = '';
    if (academicYearStartDateInput) academicYearStartDateInput.value = '';
    if (academicYearEndDateInput) academicYearEndDateInput.value = '';
    if (academicYearIsActiveInput) academicYearIsActiveInput.checked = false;
    if (academicYearDescriptionInput) academicYearDescriptionInput.value = '';
    if (offcanvasTitle) offcanvasTitle.textContent = 'Add Academic Year';
  }

  function loadAcademicYearData(id) {
    fetch(`${academicYearsBaseUrl}/${id}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (academicYearIdInput) academicYearIdInput.value = data.data.id;
          if (academicYearNameInput) academicYearNameInput.value = data.data.name;
          if (academicYearStartDateInput) academicYearStartDateInput.value = data.data.start_date;
          if (academicYearEndDateInput) academicYearEndDateInput.value = data.data.end_date;
          if (academicYearIsActiveInput) academicYearIsActiveInput.checked = data.data.is_active;
          if (academicYearDescriptionInput) academicYearDescriptionInput.value = data.data.description || '';
          if (offcanvasTitle) offcanvasTitle.textContent = 'Edit Academic Year';

          // Show the offcanvas
          const offcanvas = new bootstrap.Offcanvas(offcanvasAddAcademicYear);
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

  function deleteAcademicYear(id, name, isActive) {
    if (isActive) {
      showAlert('error', 'Cannot Delete', 'Cannot delete the active academic year. Please set another year as active first.');
      return;
    }

    Swal.fire({
      title: 'Delete Academic Year?',
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
        fetch(`${academicYearsBaseUrl}/${id}`, {
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
                dt_AcademicYear.ajax.reload();
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

  function setActiveYear(id, name) {
    Swal.fire({
      title: 'Set as Active?',
      text: `Set "${name}" as the active academic year? This will deactivate all other years.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, set active!',
      customClass: {
        confirmButton: 'btn btn-success me-3',
        cancelButton: 'btn btn-outline-secondary'
      },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${academicYearsBaseUrl}/${id}/set-active`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Success!', data.message).then(() => {
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
