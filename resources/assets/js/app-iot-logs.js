/**
 * App IoT Logs Viewer
 * IoT logs viewing with DataTable and filters
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const dtLogTable = document.querySelector('.datatables-iot-logs');
  const filterDevice = document.getElementById('filter-device');
  const filterLogType = document.getElementById('filter-log-type');
  const filterDateFrom = document.getElementById('filter-date-from');
  const filterDateTo = document.getElementById('filter-date-to');
  const applyFiltersBtn = document.getElementById('apply-filters');
  const resetFiltersBtn = document.getElementById('reset-filters');
  const exportLogsBtn = document.getElementById('export-logs');
  const logDetailModal = document.getElementById('logDetailModal');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // Base URLs
  const logsBaseUrl = '/admin/iot-logs';

  // Load stats on page load
  loadStats();

  // Load devices for filter dropdown
  loadDevices();

  // Initialize Select2
  if (filterDevice) {
    $(filterDevice).select2({
      placeholder: 'All Devices',
      allowClear: true
    });
  }

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

  // IoT Logs DataTable
  let dt_Log;
  if (dtLogTable) {
    dt_Log = new DataTable(dtLogTable, {
      ajax: {
        url: `${logsBaseUrl}/list`,
        dataSrc: 'data',
        data: function (d) {
          d.device_id = $(filterDevice).val();
          d.log_type = filterLogType?.value;
          d.date_from = filterDateFrom?.value;
          d.date_to = filterDateTo?.value;
        }
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'created_at_formatted' },
        { data: 'device_name' },
        { data: 'log_type_badge' },
        { data: 'endpoint' },
        { data: 'method' },
        { data: 'response_code_badge' },
        { data: 'duration_formatted' },
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
          // Timestamp
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="text-nowrap">${full.created_at_formatted || '-'}</span>`;
          }
        },
        {
          // Device Name
          targets: 3,
          render: function (data, type, full) {
            return `<span class="fw-medium">${full.device_name || '-'}</span>`;
          }
        },
        {
          // Log Type
          targets: 4,
          render: function (data, type, full) {
            return full.log_type_badge;
          }
        },
        {
          // Endpoint
          targets: 5,
          render: function (data, type, full) {
            return `<code class="text-truncate d-inline-block" style="max-width: 150px;" title="${full.endpoint}">${full.endpoint || ''}</code>`;
          }
        },
        {
          // Method
          targets: 6,
          render: function (data, type, full) {
            const colors = {
              GET: 'info',
              POST: 'success',
              PUT: 'warning',
              DELETE: 'danger'
            };
            const color = colors[full.method] || 'secondary';
            return `<span class="badge bg-label-${color}">${full.method || ''}</span>`;
          }
        },
        {
          // Response Code
          targets: 7,
          render: function (data, type, full) {
            return full.response_code_badge;
          }
        },
        {
          // Duration
          targets: 8,
          render: function (data, type, full) {
            return full.duration_formatted || '-';
          }
        },
        {
          // Actions
          targets: 9,
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
              return 'Log Details';
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
      $(filterDevice).val(null).trigger('change');
      if (filterLogType) filterLogType.value = '';
      if (filterDateFrom) filterDateFrom.value = '';
      if (filterDateTo) filterDateTo.value = '';
      if (dt_Log) {
        dt_Log.ajax.reload();
      }
    });
  }

  // Export logs button
  if (exportLogsBtn) {
    exportLogsBtn.addEventListener('click', function () {
      const params = new URLSearchParams();
      const deviceId = $(filterDevice).val();
      const logType = filterLogType?.value;
      const dateFrom = filterDateFrom?.value;
      const dateTo = filterDateTo?.value;

      if (deviceId) params.append('device_id', deviceId);
      if (logType) params.append('log_type', logType);
      if (dateFrom) params.append('date_from', dateFrom);
      if (dateTo) params.append('date_to', dateTo);

      const exportUrl = `${logsBaseUrl}/export?${params.toString()}`;
      window.location.href = exportUrl;
    });
  }

  // Helper Functions
  function loadStats() {
    fetch(`${logsBaseUrl}/stats`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('total-logs').textContent = data.data.total || 0;
          document.getElementById('error-logs').textContent = data.data.errors || 0;
        }
      })
      .catch(error => console.error('Error loading stats:', error));
  }

  function loadDevices() {
    fetch(`${logsBaseUrl}/devices`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success && filterDevice) {
          data.data.forEach(device => {
            const option = new Option(`${device.name} (${device.device_code})`, device.id, false, false);
            $(filterDevice).append(option);
          });
        }
      })
      .catch(error => console.error('Error loading devices:', error));
  }

  function loadLogDetails(id) {
    fetch(`${logsBaseUrl}/${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const log = data.data;

          // Populate modal
          document.getElementById('detail-device').textContent = log.device?.name || '-';
          document.getElementById('detail-timestamp').textContent = log.created_at || '-';
          document.getElementById('detail-method').textContent = log.method || '-';
          document.getElementById('detail-endpoint').textContent = log.endpoint || '-';
          document.getElementById('detail-response-code').textContent = log.response_code || '-';
          document.getElementById('detail-duration').textContent = log.duration_ms ? `${log.duration_ms}ms` : '-';
          document.getElementById('detail-ip').textContent = log.ip_address || '-';
          document.getElementById('detail-log-type').textContent = log.log_type || '-';
          document.getElementById('detail-user-agent').textContent = log.user_agent || '-';

          // Error message
          const errorContainer = document.getElementById('error-message-container');
          const errorMessage = document.getElementById('detail-error-message');
          if (log.error_message) {
            errorContainer.style.display = 'block';
            errorMessage.textContent = log.error_message;
          } else {
            errorContainer.style.display = 'none';
          }

          // Payloads
          document.getElementById('detail-request-payload').textContent = log.request_payload
            ? JSON.stringify(log.request_payload, null, 2)
            : '-';
          document.getElementById('detail-response-payload').textContent = log.response_payload
            ? JSON.stringify(log.response_payload, null, 2)
            : '-';

          // Show modal
          const modal = new bootstrap.Modal(logDetailModal);
          modal.show();
        } else {
          showAlert('error', 'Error!', data.message || 'Failed to load log details.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error!', 'An unexpected error occurred.');
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
