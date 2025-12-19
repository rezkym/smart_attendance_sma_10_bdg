/**
 * App IoT Devices Management
 * IoT devices management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const offcanvasAddDevice = document.getElementById('offcanvasAddDevice');
  const addNewDeviceForm = document.getElementById('addNewDeviceForm');
  const deviceIdInput = document.getElementById('device_id');
  const deviceNameInput = document.getElementById('add-device-name');
  const deviceCodeInput = document.getElementById('add-device-code');
  const deviceDescriptionInput = document.getElementById('add-device-description');
  const deviceLocationInput = document.getElementById('add-device-location');
  const deviceClassroomSelect = document.getElementById('add-device-classroom');
  const deviceStatusSelect = document.getElementById('add-device-status');
  const deviceFirmwareInput = document.getElementById('add-device-firmware');
  const offcanvasTitle = document.getElementById('offcanvasAddDeviceLabel');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtDeviceTable = document.querySelector('.datatables-iot-devices');
  const apiKeyModal = document.getElementById('apiKeyModal');
  const apiKeyDisplay = document.getElementById('api-key-display');
  const copyApiKeyBtn = document.getElementById('copy-api-key');

  // Base URLs
  const devicesBaseUrl = '/admin/iot-devices';

  // Load stats on page load
  loadStats();

  // Load classrooms for dropdown
  loadClassrooms();

  // Initialize Select2
  if (deviceClassroomSelect) {
    $(deviceClassroomSelect).select2({
      dropdownParent: offcanvasAddDevice,
      placeholder: 'Select Classroom',
      allowClear: true
    });
  }

  // IoT Devices DataTable
  let dt_Device;
  if (dtDeviceTable) {
    dt_Device = new DataTable(dtDeviceTable, {
      ajax: {
        url: `${devicesBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'device_code' },
        { data: 'name' },
        { data: 'location' },
        { data: 'classroom_name' },
        { data: 'status_badge' },
        { data: 'last_seen_formatted' },
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
          // Device Code
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="badge bg-label-info">${full.device_code || ''}</span>`;
          }
        },
        {
          // Name
          targets: 3,
          render: function (data, type, full) {
            let html = `<span class="fw-medium">${full.name || ''}</span>`;
            if (full.firmware_version) {
              html += `<br><small class="text-muted">v${full.firmware_version}</small>`;
            }
            return html;
          }
        },
        {
          // Location
          targets: 4,
          render: function (data, type, full) {
            return full.location || '<span class="text-muted">-</span>';
          }
        },
        {
          // Classroom
          targets: 5,
          render: function (data, type, full) {
            return full.classroom_name || '<span class="text-muted">-</span>';
          }
        },
        {
          // Status
          targets: 6,
          render: function (data, type, full) {
            return full.status_badge;
          }
        },
        {
          // Last Seen
          targets: 7,
          render: function (data, type, full) {
            return full.last_seen_formatted;
          }
        },
        {
          // Actions
          targets: 8,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            return `
              <div class="d-flex align-items-center">
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-device" data-id="${full.id}" title="Edit">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-warning waves-effect waves-light rounded-pill regenerate-key" data-id="${full.id}" data-name="${full.name}" title="Regenerate API Key">
                  <i class="icon-base ri ri-key-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-device" data-id="${full.id}" data-name="${full.name}" title="Delete">
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
                placeholder: 'Search Device',
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
  if (offcanvasAddDevice) {
    offcanvasAddDevice.addEventListener('hidden.bs.offcanvas', function () {
      resetForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtDeviceTable) {
    dtDeviceTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-device');
      if (editBtn) {
        const id = editBtn.getAttribute('data-id');
        loadDeviceData(id);
      }

      const deleteBtn = e.target.closest('.delete-device');
      if (deleteBtn) {
        const id = deleteBtn.getAttribute('data-id');
        const name = deleteBtn.getAttribute('data-name');
        deleteDevice(id, name);
      }

      const regenerateBtn = e.target.closest('.regenerate-key');
      if (regenerateBtn) {
        const id = regenerateBtn.getAttribute('data-id');
        const name = regenerateBtn.getAttribute('data-name');
        regenerateApiKey(id, name);
      }
    });
  }

  // Copy API key button
  if (copyApiKeyBtn) {
    copyApiKeyBtn.addEventListener('click', function () {
      if (apiKeyDisplay) {
        navigator.clipboard.writeText(apiKeyDisplay.value).then(() => {
          showAlert('success', 'Copied!', 'API key copied to clipboard.');
        });
      }
    });
  }

  // Form submission
  if (addNewDeviceForm) {
    addNewDeviceForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const id = deviceIdInput?.value;
      const name = deviceNameInput?.value.trim();
      const deviceCode = deviceCodeInput?.value.trim();
      const description = deviceDescriptionInput?.value.trim();
      const location = deviceLocationInput?.value.trim();
      const classroomId = $(deviceClassroomSelect).val();
      const status = deviceStatusSelect?.value;
      const firmwareVersion = deviceFirmwareInput?.value.trim();

      // Basic validation
      if (!name) {
        showAlert('error', 'Validation Error', 'Device name is required.');
        return;
      }

      if (!deviceCode) {
        showAlert('error', 'Validation Error', 'Device code is required.');
        return;
      }

      const isEdit = id !== '';
      const url = isEdit ? `${devicesBaseUrl}/${id}` : devicesBaseUrl;
      const method = isEdit ? 'PUT' : 'POST';

      const payload = {
        name: name,
        device_code: deviceCode,
        description: description || null,
        location: location || null,
        classroom_id: classroomId || null,
        status: status || 'active',
        firmware_version: firmwareVersion || null
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
            bootstrap.Offcanvas.getInstance(offcanvasAddDevice).hide();
            dt_Device.ajax.reload();
            loadStats();

            // If new device, show the API key
            if (!isEdit && data.data?.api_key) {
              showApiKeyModal(data.data.api_key);
            } else {
              showAlert('success', 'Success!', data.message);
            }
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
  function loadStats() {
    fetch(`${devicesBaseUrl}/stats`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('total-devices').textContent = data.data.total || 0;
          document.getElementById('active-devices').textContent = data.data.active || 0;
        }
      })
      .catch(error => console.error('Error loading stats:', error));
  }

  function loadClassrooms() {
    fetch(`${devicesBaseUrl}/available-classrooms`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success && deviceClassroomSelect) {
          data.data.forEach(classroom => {
            const option = new Option(classroom.name, classroom.id, false, false);
            $(deviceClassroomSelect).append(option);
          });
        }
      })
      .catch(error => console.error('Error loading classrooms:', error));
  }

  function resetForm() {
    if (addNewDeviceForm) addNewDeviceForm.reset();
    if (deviceIdInput) deviceIdInput.value = '';
    if (deviceNameInput) deviceNameInput.value = '';
    if (deviceCodeInput) deviceCodeInput.value = '';
    if (deviceDescriptionInput) deviceDescriptionInput.value = '';
    if (deviceLocationInput) deviceLocationInput.value = '';
    if (deviceFirmwareInput) deviceFirmwareInput.value = '';
    if (deviceStatusSelect) deviceStatusSelect.value = 'active';
    $(deviceClassroomSelect).val(null).trigger('change');
    if (offcanvasTitle) offcanvasTitle.textContent = 'Add IoT Device';
  }

  function loadDeviceData(id) {
    fetch(`${devicesBaseUrl}/${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const device = data.data;
          if (deviceIdInput) deviceIdInput.value = device.id;
          if (deviceNameInput) deviceNameInput.value = device.name;
          if (deviceCodeInput) deviceCodeInput.value = device.device_code;
          if (deviceDescriptionInput) deviceDescriptionInput.value = device.description || '';
          if (deviceLocationInput) deviceLocationInput.value = device.location || '';
          if (deviceFirmwareInput) deviceFirmwareInput.value = device.firmware_version || '';
          if (deviceStatusSelect) deviceStatusSelect.value = device.status;
          $(deviceClassroomSelect).val(device.classroom_id).trigger('change');
          if (offcanvasTitle) offcanvasTitle.textContent = 'Edit IoT Device';

          // Show the offcanvas
          const offcanvas = new bootstrap.Offcanvas(offcanvasAddDevice);
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

  function deleteDevice(id, name) {
    Swal.fire({
      title: 'Delete Device?',
      text: `Are you sure you want to delete "${name}"? All associated logs will also be deleted.`,
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
        fetch(`${devicesBaseUrl}/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json'
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Deleted!', data.message).then(() => {
                dt_Device.ajax.reload();
                loadStats();
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

  function regenerateApiKey(id, name) {
    Swal.fire({
      title: 'Regenerate API Key?',
      text: `Are you sure you want to regenerate the API key for "${name}"? The old key will be invalidated immediately.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, regenerate!',
      customClass: {
        confirmButton: 'btn btn-warning me-3',
        cancelButton: 'btn btn-outline-secondary'
      },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${devicesBaseUrl}/${id}/regenerate-key`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json'
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showApiKeyModal(data.data.api_key);
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

  function showApiKeyModal(apiKey) {
    if (apiKeyDisplay) {
      apiKeyDisplay.value = apiKey;
    }
    const modal = new bootstrap.Modal(apiKeyModal);
    modal.show();
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

  // ==========================================
  // NETWORK SCANNING & PAIRING
  // ==========================================

  const scanNetworkModal = document.getElementById('scanNetworkModal');
  const ipRangeInput = document.getElementById('ip-range');
  const btnStartScan = document.getElementById('btn-start-scan');
  const scanProgress = document.getElementById('scan-progress');
  const scanCurrentIp = document.getElementById('scan-current-ip');
  const scanProgressCount = document.getElementById('scan-progress-count');
  const scanProgressTotal = document.getElementById('scan-progress-total');
  const scanProgressBar = document.getElementById('scan-progress-bar');
  const discoveredDevicesBody = document.getElementById('discovered-devices-body');
  const discoveredCount = document.getElementById('discovered-count');
  const noDevicesRow = document.getElementById('no-devices-row');

  let discoveredDevices = [];
  let isScanning = false;

  // Start scan button
  if (btnStartScan) {
    btnStartScan.addEventListener('click', function () {
      if (isScanning) return;
      const ipRange = ipRangeInput?.value.trim();
      if (!ipRange) {
        showAlert('error', 'Error', 'Please enter an IP range (e.g., 192.168.1.1-254)');
        return;
      }
      startNetworkScan(ipRange);
    });
  }

  // Reset modal on close
  if (scanNetworkModal) {
    scanNetworkModal.addEventListener('hidden.bs.modal', function () {
      resetScanModal();
    });
  }

  // Event delegation for pair buttons
  if (discoveredDevicesBody) {
    discoveredDevicesBody.addEventListener('click', function (e) {
      const pairBtn = e.target.closest('.btn-pair-device');
      if (pairBtn) {
        const ip = pairBtn.getAttribute('data-ip');
        const deviceCode = pairBtn.getAttribute('data-device-code');
        pairDevice(ip, deviceCode);
      }
    });
  }

  function parseIpRange(rangeStr) {
    // Supported formats: "192.168.1.1-254" or "192.168.1.100-200"
    const match = rangeStr.match(/^(\d+\.\d+\.\d+)\.(\d+)-(\d+)$/);
    if (!match) {
      return null;
    }
    const prefix = match[1];
    const start = parseInt(match[2], 10);
    const end = parseInt(match[3], 10);
    if (start < 1 || end > 254 || start > end) {
      return null;
    }
    const ips = [];
    for (let i = start; i <= end; i++) {
      ips.push(`${prefix}.${i}`);
    }
    return ips;
  }

  async function startNetworkScan(rangeStr) {
    const ips = parseIpRange(rangeStr);
    if (!ips) {
      showAlert('error', 'Invalid Format', 'Please enter a valid IP range (e.g., 192.168.1.1-254)');
      return;
    }

    isScanning = true;
    discoveredDevices = [];
    updateDiscoveredTable();

    // Show progress
    scanProgress.style.display = 'block';
    scanProgressTotal.textContent = ips.length;
    scanProgressCount.textContent = '0';
    scanProgressBar.style.width = '0%';
    btnStartScan.disabled = true;
    btnStartScan.innerHTML = '<i class="ri ri-loader-4-line me-1 spin"></i> Scanning...';

    let scanned = 0;

    // Scan in parallel batches of 10
    const batchSize = 10;
    for (let i = 0; i < ips.length; i += batchSize) {
      const batch = ips.slice(i, i + batchSize);
      const promises = batch.map(ip => identifyDevice(ip));
      await Promise.all(promises);
      scanned += batch.length;
      scanProgressCount.textContent = scanned;
      scanProgressBar.style.width = `${(scanned / ips.length) * 100}%`;
      if (batch.length > 0) {
        scanCurrentIp.textContent = batch[batch.length - 1];
      }
    }

    isScanning = false;
    btnStartScan.disabled = false;
    btnStartScan.innerHTML = '<i class="ri ri-radar-line me-1"></i> Start Scan';
    scanCurrentIp.textContent = 'Complete';

    if (discoveredDevices.length === 0) {
      showAlert('info', 'Scan Complete', 'No IoT devices found in the specified IP range.');
    } else {
      showAlert('success', 'Scan Complete', `Found ${discoveredDevices.length} device(s).`);
    }
  }

  async function identifyDevice(ip) {
    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 1000); // 1 second timeout

      const response = await fetch(`http://${ip}/identify`, {
        method: 'GET',
        signal: controller.signal
      });
      clearTimeout(timeoutId);

      if (response.ok) {
        const data = await response.json();
        if (data.success && data.device_code) {
          discoveredDevices.push({
            ip: ip,
            device_code: data.device_code,
            firmware_version: data.firmware_version || '-',
            is_configured: data.is_configured || false,
            api_host: data.api_host || ''
          });
          updateDiscoveredTable();
        }
      }
    } catch (error) {
      // Device not found or timeout - ignore
    }
  }

  function updateDiscoveredTable() {
    discoveredCount.textContent = discoveredDevices.length;

    if (discoveredDevices.length === 0) {
      noDevicesRow.style.display = '';
      return;
    }

    noDevicesRow.style.display = 'none';

    // Clear existing rows except no-devices-row
    const rows = discoveredDevicesBody.querySelectorAll('tr:not(#no-devices-row)');
    rows.forEach(row => row.remove());

    discoveredDevices.forEach(device => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><code>${device.ip}</code></td>
        <td><span class="badge bg-label-info">${device.device_code}</span></td>
        <td>${device.firmware_version}</td>
        <td>${device.is_configured ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>'}</td>
        <td>
          <button class="btn btn-sm btn-primary btn-pair-device" data-ip="${device.ip}" data-device-code="${device.device_code}">
            <i class="ri ri-link me-1"></i> Pair
          </button>
        </td>
      `;
      discoveredDevicesBody.appendChild(row);
    });
  }

  async function pairDevice(ip, deviceCode) {
    try {
      // Step 1: Get server config (host, port, path, api_key)
      const configResponse = await fetch(`${devicesBaseUrl}/pairing/config`, {
        method: 'GET',
        headers: { Accept: 'application/json' }
      });
      const configData = await configResponse.json();

      if (!configData.success) {
        showAlert('error', 'Error', 'Failed to get server configuration.');
        return;
      }

      const config = configData.data;

      // Step 2: Send pairing request to ESP32
      const pairFormData = new URLSearchParams();
      pairFormData.append('host', config.host);
      pairFormData.append('port', config.port);
      pairFormData.append('path', config.path);
      pairFormData.append('key', config.api_key);

      const pairResponse = await fetch(`http://${ip}/pair`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: pairFormData.toString()
      });

      if (!pairResponse.ok) {
        showAlert('error', 'Pairing Failed', 'Failed to send configuration to device.');
        return;
      }

      const pairData = await pairResponse.json();
      if (!pairData.success) {
        showAlert('error', 'Pairing Failed', pairData.message || 'Device rejected the pairing request.');
        return;
      }

      // Step 3: Complete pairing on backend
      const completeResponse = await fetch(`${devicesBaseUrl}/pairing/complete`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json'
        },
        body: JSON.stringify({
          device_code: deviceCode,
          ip_address: ip,
          api_key: config.api_key, // Send the same api_key that was sent to ESP32
          firmware_version: pairData.firmware_version || null
        })
      });

      const completeData = await completeResponse.json();

      if (completeData.success) {
        // Close modal and refresh table
        bootstrap.Modal.getInstance(scanNetworkModal)?.hide();
        dt_Device.ajax.reload();
        loadStats();

        // Show API key if new device
        if (completeData.data?.api_key) {
          showApiKeyModal(completeData.data.api_key);
        } else {
          showAlert('success', 'Device Paired!', `${deviceCode} has been paired successfully.`);
        }
      } else {
        showAlert('error', 'Error', completeData.message || 'Failed to complete pairing.');
      }
    } catch (error) {
      console.error('Pairing error:', error);
      showAlert('error', 'Error', 'An error occurred during pairing. Make sure the device is reachable.');
    }
  }

  function resetScanModal() {
    if (!isScanning) {
      discoveredDevices = [];
      updateDiscoveredTable();
      scanProgress.style.display = 'none';
      scanProgressBar.style.width = '0%';
      scanCurrentIp.textContent = '-';
    }
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

