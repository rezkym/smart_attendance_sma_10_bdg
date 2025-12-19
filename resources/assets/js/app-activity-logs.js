/**
 * App Activity Logs Viewer
 * Activity logs viewing with DataTable and filters
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const dtLogTable = document.querySelector('.datatables-activity-logs');
  const filterLogName = document.getElementById('filter-log-name');
  const filterEvent = document.getElementById('filter-event');
  const filterDateFrom = document.getElementById('filter-date-from');
  const filterDateTo = document.getElementById('filter-date-to');
  const applyFiltersBtn = document.getElementById('apply-filters');
  const resetFiltersBtn = document.getElementById('reset-filters');
  const logDetailModal = document.getElementById('activityLogDetailModal');

  // Base URLs
  const logsBaseUrl = '/admin/activity-logs';

  // Initialize Flatpickr for date pickers
  if (filterDateFrom) {
    flatpickr(filterDateFrom, {
      dateFormat: 'Y-m-d',
      allowInput: true
    });
  }

  if (filterDateTo) {
    flatpickr(filterDateTo, {
      dateFormat: 'Y-m-d',
      allowInput: true
    });
  }

  // Activity Logs DataTable
  let dt_Log;
  if (dtLogTable) {
    dt_Log = new DataTable(dtLogTable, {
      ajax: {
        url: `${logsBaseUrl}/list`,
        dataSrc: 'data',
        data: function (d) {
          d.log_name = filterLogName?.value;
          d.event = filterEvent?.value;
          d.date_from = filterDateFrom?.value;
          d.date_to = filterDateTo?.value;
        }
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'formatted_time' },
        { data: 'causer_name' },
        { data: 'event' },
        { data: 'subject_name' },
        { data: 'description' },
        { data: 'action' }
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
          // Timestamp
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="text-nowrap">${full.formatted_time || '-'}</span>`;
          }
        },
        {
          // Causer
          targets: 3,
          render: function (data, type, full) {
            return `<span class="fw-medium">${full.causer_name || 'System'}</span>`;
          }
        },
        {
          // Event
          targets: 4,
          render: function (data, type, full) {
            const colors = {
              created: 'success',
              updated: 'warning',
              deleted: 'danger'
            };
            const color = colors[full.event] || 'secondary';
            return `<span class="badge bg-label-${color}">${full.event || '-'}</span>`;
          }
        },
        {
          // Subject
          targets: 5,
          render: function (data, type, full) {
            return `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${full.subject_name}">${full.subject_name || '-'}</span>`;
          }
        },
        {
          // Description
          targets: 6,
          render: function (data, type, full) {
            return `<span class="text-truncate d-inline-block" style="max-width: 150px;" title="${full.description}">${full.description || '-'}</span>`;
          }
        },
        {
          // Actions
          targets: 7,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            return `
              <div class="d-flex align-items-center">
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill view-log" data-id="${full.id}" title="View Details">
                  <i class="icon-base ri ri-eye-line icon-22px"></i>
                </a>
              </div>
            `;
          }
        }
      ],
      order: [[2, 'desc']],
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
                placeholder: 'Search Logs',
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
              return 'Activity Log Details';
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

  // Event delegation for DataTable actions
  if (dtLogTable) {
    dtLogTable.addEventListener('click', function (e) {
      const viewBtn = e.target.closest('.view-log');
      if (viewBtn) {
        const id = viewBtn.getAttribute('data-id');
        loadLogDetails(id);
      }
    });
  }

  // Apply filters button
  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function () {
      if (dt_Log) {
        dt_Log.ajax.reload();
      }
    });
  }

  // Reset filters button
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', function () {
      if (filterLogName) filterLogName.value = '';
      if (filterEvent) filterEvent.value = '';
      if (filterDateFrom) filterDateFrom.value = '';
      if (filterDateTo) filterDateTo.value = '';
      if (dt_Log) {
        dt_Log.ajax.reload();
      }
    });
  }

  // Helper Functions
  function loadLogDetails(id) {
    fetch(`${logsBaseUrl}/${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const activity = data.data.activity;
          const properties = data.data.properties || {};
          const subjectTypeName = data.data.subjectTypeName || '';

          // Populate modal
          document.getElementById('detail-timestamp').textContent = activity.created_at || '-';
          document.getElementById('detail-event').innerHTML = getEventBadge(activity.event);
          document.getElementById('detail-causer').textContent = activity.causer?.name || 'System';
          document.getElementById('detail-log-name').textContent = activity.log_name || '-';
          document.getElementById('detail-subject-type').textContent = subjectTypeName || activity.subject_type || '-';
          document.getElementById('detail-subject-id').textContent = activity.subject_id || '-';
          document.getElementById('detail-description').textContent = activity.description || '-';

          // Old values
          const oldValuesContainer = document.getElementById('old-values-container');
          const oldValues = properties.old;
          if (oldValues && Object.keys(oldValues).length > 0) {
            oldValuesContainer.style.display = 'block';
            document.getElementById('detail-old-values').textContent = JSON.stringify(oldValues, null, 2);
          } else {
            oldValuesContainer.style.display = 'none';
          }

          // New values
          const newValuesContainer = document.getElementById('new-values-container');
          const newValues = properties.attributes;
          if (newValues && Object.keys(newValues).length > 0) {
            newValuesContainer.style.display = 'block';
            document.getElementById('detail-new-values').textContent = JSON.stringify(newValues, null, 2);
          } else {
            newValuesContainer.style.display = 'none';
          }

          // Raw properties
          document.getElementById('detail-raw-properties').textContent = activity.properties
            ? JSON.stringify(activity.properties, null, 2)
            : '-';

          // Show modal
          const modal = new bootstrap.Modal(logDetailModal);
          modal.show();
        } else {
          showAlert('error', 'Error!', data.message || 'Failed to load activity log details.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error!', 'An unexpected error occurred.');
      });
  }

  function getEventBadge(event) {
    const colors = {
      created: 'success',
      updated: 'warning',
      deleted: 'danger'
    };
    const color = colors[event] || 'secondary';
    return `<span class="badge bg-label-${color}">${event || '-'}</span>`;
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
