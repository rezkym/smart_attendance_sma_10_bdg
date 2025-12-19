/**
 * App RFID Cards Management
 * RFID Cards management with CRUD operations, block/unblock, and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtCardsTable = document.querySelector('.datatables-rfid-cards');
  const baseUrl = '/admin/rfid-cards';

  // Filter elements
  const filterStatus = document.getElementById('filter-status');
  const filterAssigned = document.getElementById('filter-assigned');
  const btnApplyFilter = document.getElementById('btn-apply-filter');

  // Card modal elements
  const cardModal = document.getElementById('cardModal');
  const cardForm = document.getElementById('cardForm');
  const cardId = document.getElementById('card-id');
  const cardAction = document.getElementById('card-action');
  const cardUid = document.getElementById('card-uid');
  const cardUserId = document.getElementById('card-user-id');
  const cardStatus = document.getElementById('card-status');
  const cardIssuedAt = document.getElementById('card-issued-at');
  const cardExpiresAt = document.getElementById('card-expires-at');
  const cardNotes = document.getElementById('card-notes');
  const cardModalTitle = document.getElementById('cardModalTitle');

  // Block modal elements
  const blockModal = document.getElementById('blockModal');
  const blockCardId = document.getElementById('block-card-id');
  const blockReason = document.getElementById('block-reason');
  const blockMessage = document.getElementById('blockMessage');
  const btnConfirmBlock = document.getElementById('btn-confirm-block');

  let dt_Cards;
  let bsCardModal;
  let bsBlockModal;

  // Initialize Bootstrap modals
  if (cardModal) {
    bsCardModal = new bootstrap.Modal(cardModal);
  }
  if (blockModal) {
    bsBlockModal = new bootstrap.Modal(blockModal);
  }

  // Initialize Select2
  function initSelect2() {
    if (cardUserId) {
      $(cardUserId).select2({
        dropdownParent: $(cardModal),
        placeholder: '-- No Assignment --',
        allowClear: true
      });
    }
  }

  // Load available users for assignment
  function loadAvailableUsers() {
    fetch(`${baseUrl}/available-users`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          $(cardUserId).empty();
          $(cardUserId).append(new Option('-- No Assignment --', '', true, true));
          data.data.forEach(user => {
            const option = new Option(`${user.name} (${user.email})`, user.id, false, false);
            $(cardUserId).append(option);
          });
          $(cardUserId).trigger('change');
        }
      })
      .catch(error => console.error('Error loading users:', error));
  }

  // Initialize DataTable
  if (dtCardsTable) {
    dt_Cards = new DataTable(dtCardsTable, {
      ajax: {
        url: `${baseUrl}/list`,
        dataSrc: 'data',
        data: function (d) {
          d.status = filterStatus?.value || '';
          d.assigned = filterAssigned?.value || '';
        }
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'card_uid' },
        { data: 'user_name' },
        { data: 'status_label' },
        { data: 'issued_at_formatted' },
        { data: 'expires_at_formatted' },
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
            return `<span class="fw-medium font-monospace">${full.card_uid || ''}</span>`;
          }
        },
        {
          targets: 3,
          render: function (data, type, full) {
            if (full.is_assigned) {
              return `<div>
                <span class="fw-medium">${full.user_name}</span>
                <br><small class="text-muted">${full.user_email}</small>
              </div>`;
            }
            return '<span class="badge bg-label-secondary">Unassigned</span>';
          }
        },
        {
          targets: 4,
          render: function (data, type, full) {
            const badgeClass = full.status_badge || 'secondary';
            let badge = `<span class="badge rounded-pill bg-label-${badgeClass}">${full.status_label || full.status}</span>`;
            
            if (full.is_expired) {
              badge += ' <span class="badge rounded-pill bg-label-danger">Expired</span>';
            }
            return badge;
          }
        },
        {
          targets: 5,
          render: function (data, type, full) {
            return full.issued_at_formatted || '-';
          }
        },
        {
          targets: 6,
          render: function (data, type, full) {
            return full.expires_at_formatted || '-';
          }
        },
        {
          targets: 7,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            const isBlocked = full.status_label?.toLowerCase() === 'blocked';
            const isActive = full.status_label?.toLowerCase() === 'active';
            
            let actions = '<div class="d-flex align-items-center">';
            
            // Edit button
            actions += `
              <a href="javascript:;" class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill edit-card" 
                 data-id="${full.id}" title="Edit">
                <i class="icon-base ri ri-pencil-line icon-22px"></i>
              </a>
            `;
            
            // Block/Unblock button
            if (isBlocked) {
              actions += `
                <a href="javascript:;" class="btn btn-icon btn-text-success waves-effect waves-light rounded-pill unblock-card" 
                   data-id="${full.id}" data-uid="${full.card_uid}" title="Unblock">
                  <i class="icon-base ri ri-lock-unlock-line icon-22px"></i>
                </a>
              `;
            } else if (isActive) {
              actions += `
                <a href="javascript:;" class="btn btn-icon btn-text-warning waves-effect waves-light rounded-pill block-card" 
                   data-id="${full.id}" data-uid="${full.card_uid}" title="Block">
                  <i class="icon-base ri ri-lock-line icon-22px"></i>
                </a>
              `;
            }
            
            // Delete button
            actions += `
              <a href="javascript:;" class="btn btn-icon btn-text-danger waves-effect waves-light rounded-pill delete-card" 
                 data-id="${full.id}" data-uid="${full.card_uid}" title="Delete">
                <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
              </a>
            `;
            
            actions += '</div>';
            return actions;
          }
        }
      ],
      order: [[5, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row mx-2',
          features: [{ pageLength: { menu: [10, 25, 50, 100], text: 'Show _MENU_' } }]
        },
        topEnd: {
          features: [{ search: { placeholder: 'Search card UID...', text: '_INPUT_' } }]
        },
        bottomStart: { rowClass: 'row mx-3 justify-content-between', features: ['info'] },
        bottomEnd: 'paging'
      },
      language: {
        paginate: {
          next: '<i class="icon-base ri ri-arrow-right-s-line icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line icon-22px"></i>'
        }
      },
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              return 'Card Details';
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

  // Initialize
  initSelect2();

  // Filter button click
  if (btnApplyFilter) {
    btnApplyFilter.addEventListener('click', function () {
      dt_Cards.ajax.reload();
    });
  }

  // Modal show event - load available users
  if (cardModal) {
    cardModal.addEventListener('show.bs.modal', function () {
      loadAvailableUsers();
    });

    cardModal.addEventListener('hidden.bs.modal', function () {
      resetCardForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtCardsTable) {
    dtCardsTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-card');
      if (editBtn) {
        openEditModal(editBtn.getAttribute('data-id'));
      }

      const blockBtn = e.target.closest('.block-card');
      if (blockBtn) {
        openBlockModal(blockBtn.getAttribute('data-id'), blockBtn.getAttribute('data-uid'));
      }

      const unblockBtn = e.target.closest('.unblock-card');
      if (unblockBtn) {
        unblockCard(unblockBtn.getAttribute('data-id'), unblockBtn.getAttribute('data-uid'));
      }

      const deleteBtn = e.target.closest('.delete-card');
      if (deleteBtn) {
        deleteCard(deleteBtn.getAttribute('data-id'), deleteBtn.getAttribute('data-uid'));
      }
    });
  }

  // Card form submission
  if (cardForm) {
    cardForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const action = cardAction.value;
      const data = {
        card_uid: cardUid.value.toUpperCase().trim(),
        user_id: $(cardUserId).val() || null,
        status: cardStatus.value,
        issued_at: cardIssuedAt.value,
        expires_at: cardExpiresAt.value || null,
        notes: cardNotes.value.trim() || null
      };

      if (!data.card_uid || !data.issued_at) {
        showAlert('error', 'Validation Error', 'Please fill all required fields.');
        return;
      }

      const url = action === 'create' ? baseUrl : `${baseUrl}/${cardId.value}`;
      const method = action === 'create' ? 'POST' : 'PUT';

      fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json'
        },
        body: JSON.stringify(data)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'Success!', data.message).then(() => {
              bsCardModal.hide();
              dt_Cards.ajax.reload();
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

  // Block confirmation button
  if (btnConfirmBlock) {
    btnConfirmBlock.addEventListener('click', function () {
      const id = blockCardId.value;
      const reason = blockReason.value;

      if (!reason) {
        showAlert('error', 'Validation Error', 'Please select a reason for blocking.');
        return;
      }

      fetch(`${baseUrl}/${id}/block`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json'
        },
        body: JSON.stringify({ reason: reason })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'Blocked!', data.message).then(() => {
              bsBlockModal.hide();
              dt_Cards.ajax.reload();
              window.location.reload();
            });
          } else {
            showAlert('error', 'Error!', data.message || 'Something went wrong.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('error', 'Error!', 'An unexpected error occurred.');
        });
    });
  }

  // Helper Functions
  function resetCardForm() {
    if (cardForm) cardForm.reset();
    cardAction.value = 'create';
    cardId.value = '';
    cardModalTitle.textContent = 'Add RFID Card';
    cardUid.disabled = false;
    $(cardUserId).val('').trigger('change');
    cardIssuedAt.value = new Date().toISOString().split('T')[0];
    cardExpiresAt.value = '';
    cardNotes.value = '';
  }

  function openEditModal(id) {
    fetch(`${baseUrl}/${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const card = data.data;
          
          cardAction.value = 'update';
          cardId.value = card.id;
          cardModalTitle.textContent = `Edit Card: ${card.card_uid}`;
          cardUid.value = card.card_uid;
          cardUid.disabled = true; // UID cannot be changed
          cardStatus.value = card.status;
          cardIssuedAt.value = card.issued_at;
          cardExpiresAt.value = card.expires_at || '';
          cardNotes.value = card.notes || '';
          
          // Load users then set selected
          loadAvailableUsers();
          setTimeout(() => {
            $(cardUserId).val(card.user_id).trigger('change');
          }, 500);
          
          bsCardModal.show();
        } else {
          showAlert('error', 'Error!', data.message || 'Card not found.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error!', 'An unexpected error occurred.');
      });
  }

  function openBlockModal(id, uid) {
    blockCardId.value = id;
    blockMessage.textContent = `Are you sure you want to block card "${uid}"?`;
    blockReason.value = '';
    bsBlockModal.show();
  }

  function unblockCard(id, uid) {
    Swal.fire({
      title: 'Unblock Card?',
      text: `Are you sure you want to unblock card "${uid}"?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, unblock it!',
      customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${baseUrl}/${id}/unblock`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Unblocked!', data.message).then(() => {
                dt_Cards.ajax.reload();
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

  function deleteCard(id, uid) {
    Swal.fire({
      title: 'Delete Card?',
      text: `Are you sure you want to delete card "${uid}"? This action cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${baseUrl}/${id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Deleted!', data.message).then(() => {
                dt_Cards.ajax.reload();
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
      { selector: '.dt-layout-start', classToAdd: 'mt-5 mt-md-0 px-lg-5 pe-0 ps-2 d-flex justify-content-center', classToRemove: 'justify-content-between' },
      { selector: '.dt-layout-end', classToRemove: 'justify-content-between', classToAdd: 'justify-content-md-between justify-content-center d-flex' },
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

  // ==========================================
  // QUICK SCAN FUNCTIONALITY (Redesigned)
  // Single combined view, auto-start on user selection
  // ==========================================
  const quickScanModal = document.getElementById('quickScanModal');
  const qsDeviceId = document.getElementById('qs-device-id');
  const qsUserId = document.getElementById('qs-user-id');
  const qsCountdown = document.getElementById('qs-countdown');

  let qsSessionId = null;
  let qsPollingInterval = null;
  let qsCountdownInterval = null;
  let bsQuickScanModal = null;
  let qsIsScanning = false;

  if (quickScanModal) {
    bsQuickScanModal = new bootstrap.Modal(quickScanModal);

    // Initialize Select2 for Quick Scan modal
    $(qsDeviceId).select2({
      dropdownParent: $(quickScanModal),
      placeholder: '-- Pilih Device --',
      allowClear: true
    });

    $(qsUserId).select2({
      dropdownParent: $(quickScanModal),
      placeholder: '-- Pilih User --',
      allowClear: true
    });

    // Load devices and users when modal opens
    quickScanModal.addEventListener('show.bs.modal', function () {
      loadQuickScanDevices();
      loadQuickScanUsersWithoutCard();
      resetQuickScanUI();
      hideLastResult();
    });

    // Auto-start when user is selected (if device is already selected)
    $(qsUserId).on('change', function () {
      const deviceId = $(qsDeviceId).val();
      const userId = $(this).val();

      // Hide previous result when user changes
      hideLastResult();

      if (deviceId && userId && !qsIsScanning) {
        // Auto-trigger start scanning
        startQuickScanProcess();
      }
    });

    // Also auto-start if device is selected after user
    $(qsDeviceId).on('change', function () {
      const deviceId = $(this).val();
      const userId = $(qsUserId).val();

      if (deviceId && userId && !qsIsScanning) {
        // Auto-trigger start scanning
        startQuickScanProcess();
      }
    });

    // Cleanup when modal closes
    quickScanModal.addEventListener('hidden.bs.modal', function () {
      stopQuickScanPolling();
      if (qsSessionId) {
        cancelQuickScanSession();
      }
      resetQuickScanUI();
      qsSessionId = null;
      qsIsScanning = false;
      // Reload DataTable to show new cards
      dt_Cards.ajax.reload();
    });
  }

  function loadQuickScanDevices() {
    fetch(`${baseUrl}/available-devices`, { method: 'GET', headers: { Accept: 'application/json' } })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          $(qsDeviceId).empty().append(new Option('-- Pilih Device --', '', true, true));
          data.data.forEach(device => {
            const label = device.location ? `${device.name} (${device.location})` : device.name;
            $(qsDeviceId).append(new Option(label, device.id, false, false));
          });
        }
      })
      .catch(error => console.error('Error loading devices:', error));
  }

  function loadQuickScanUsersWithoutCard() {
    // Use new endpoint that filters out users who already have cards
    fetch(`${baseUrl}/available-users-without-card`, { method: 'GET', headers: { Accept: 'application/json' } })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          $(qsUserId).empty().append(new Option('-- Pilih User --', '', true, true));
          data.data.forEach(user => {
            $(qsUserId).append(new Option(`${user.name} (${user.email})`, user.id, false, false));
          });
        }
      })
      .catch(error => console.error('Error loading users:', error));
  }

  function resetQuickScanUI() {
    $(qsUserId).val('').trigger('change');
    document.querySelector('input[name="qs-expiry"][value="0"]').checked = true;
    updateQuickScanStatusDisplay('idle');
    qsIsScanning = false;
  }

  function hideLastResult() {
    const lastResult = document.getElementById('qs-last-result');
    if (lastResult) lastResult.style.display = 'none';
  }

  function showLastResult(type, message) {
    const lastResult = document.getElementById('qs-last-result');
    const resultAlert = document.getElementById('qs-result-alert');
    const resultIcon = document.getElementById('qs-result-icon');
    const resultMessage = document.getElementById('qs-result-message');

    if (!lastResult || !resultAlert) return;

    lastResult.style.display = 'block';

    if (type === 'success') {
      resultAlert.className = 'alert alert-success mb-0';
      resultIcon.className = 'ri ri-checkbox-circle-line me-2';
    } else {
      resultAlert.className = 'alert alert-danger mb-0';
      resultIcon.className = 'ri ri-error-warning-line me-2';
    }

    resultMessage.textContent = message;
  }

  function updateQuickScanStatusDisplay(status, message = '') {
    const statusIcon = document.getElementById('qs-status-icon');
    const statusText = document.getElementById('qs-status-text');
    const statusMessage = document.getElementById('qs-status-message');
    const statusBadge = document.getElementById('qs-status-badge');
    const countdownEl = document.getElementById('qs-countdown');

    const states = {
      idle: {
        icon: 'ri-user-search-line',
        color: '#9e9e9e',
        text: 'Pilih User',
        msg: 'Pilih hardware dan user untuk mulai scan',
        badge: 'bg-secondary',
        badgeText: 'Waiting',
        showCountdown: false
      },
      connecting: {
        icon: 'ri-loader-4-line spin-animation',
        color: '#ffc107',
        text: 'Connecting...',
        msg: 'Menghubungkan ke device...',
        badge: 'bg-warning',
        badgeText: 'Connecting',
        showCountdown: false
      },
      ready: {
        icon: 'ri-wifi-line',
        color: '#28a745',
        text: 'Ready to Scan',
        msg: 'Tempelkan kartu RFID ke reader...',
        badge: 'bg-success',
        badgeText: 'Ready',
        showCountdown: true
      },
      processing: {
        icon: 'ri-loader-4-line spin-animation',
        color: '#17a2b8',
        text: 'Processing...',
        msg: 'Memproses kartu...',
        badge: 'bg-info',
        badgeText: 'Processing',
        showCountdown: true
      },
      completed: {
        icon: 'ri-checkbox-circle-line',
        color: '#28a745',
        text: 'Completed!',
        msg: message || 'Kartu berhasil didaftarkan!',
        badge: 'bg-success',
        badgeText: 'Done',
        showCountdown: false
      },
      error: {
        icon: 'ri-error-warning-line',
        color: '#dc3545',
        text: 'Error',
        msg: message || 'Terjadi kesalahan',
        badge: 'bg-danger',
        badgeText: 'Error',
        showCountdown: false
      },
      expired: {
        icon: 'ri-time-line',
        color: '#6c757d',
        text: 'Session Expired',
        msg: 'Sesi habis. Pilih user lagi untuk retry.',
        badge: 'bg-secondary',
        badgeText: 'Expired',
        showCountdown: false
      }
    };

    const state = states[status] || states.idle;

    if (statusIcon) {
      statusIcon.innerHTML = `<i class="ri ${state.icon}" style="font-size: 60px; color: ${state.color};"></i>`;
    }
    if (statusText) statusText.textContent = state.text;
    if (statusMessage) statusMessage.textContent = state.msg;
    if (statusBadge) {
      statusBadge.className = `badge ${state.badge} fs-6`;
      statusBadge.textContent = state.badgeText;
    }
    if (countdownEl) {
      countdownEl.style.display = state.showCountdown ? 'inline-block' : 'none';
    }
  }

  // Main function to start Quick Scan process
  function startQuickScanProcess() {
    const deviceId = $(qsDeviceId).val();
    const userId = $(qsUserId).val();
    const expiryYears = document.querySelector('input[name="qs-expiry"]:checked')?.value || 0;

    if (!deviceId || !userId) {
      return; // Silently return, don't show alert for auto-start
    }

    qsIsScanning = true;
    updateQuickScanStatusDisplay('connecting');

    fetch(`${baseUrl}/start-registration`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json'
      },
      body: JSON.stringify({ device_id: deviceId, user_id: userId, expiry_years: parseInt(expiryYears) })
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          qsSessionId = data.data.session_id;
          const timeout = data.data.timeout_seconds || 30;

          updateQuickScanStatusDisplay('ready');
          startQuickScanPolling();
          startCountdown(timeout);
        } else {
          updateQuickScanStatusDisplay('error', data.message || 'Failed to connect to device.');
          showLastResult('error', data.message || 'Failed to connect to device.');
          qsIsScanning = false;
          // Clear user selection so user can try again
          $(qsUserId).val('').trigger('change.select2');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        updateQuickScanStatusDisplay('error', 'Failed to connect to device.');
        showLastResult('error', 'Failed to connect to device.');
        qsIsScanning = false;
        $(qsUserId).val('').trigger('change.select2');
      });
  }

  function startQuickScanPolling() {
    qsPollingInterval = setInterval(() => {
      if (!qsSessionId) {
        stopQuickScanPolling();
        return;
      }

      fetch(`${baseUrl}/check-registration`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json'
        },
        body: JSON.stringify({ session_id: qsSessionId })
      })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'completed') {
            handleScanCompleted(data.card_uid);
          } else if (data.status === 'error') {
            handleScanError(data.error_message);
          } else if (data.status === 'expired' || !data.success) {
            handleScanExpired();
          }
        })
        .catch(error => console.error('Polling error:', error));
    }, 1000);
  }

  function handleScanCompleted(cardUid) {
    stopQuickScanPolling();
    stopCountdown();

    const message = `Kartu ${cardUid} berhasil didaftarkan!`;
    updateQuickScanStatusDisplay('completed', message);
    showLastResult('success', message);

    qsSessionId = null;
    qsIsScanning = false;

    // Clear user selection for next scan (continuous mode)
    $(qsUserId).val('').trigger('change.select2');

    // Reload user list to remove the newly registered user
    loadQuickScanUsersWithoutCard();

    // Reset status after a short delay
    setTimeout(() => {
      updateQuickScanStatusDisplay('idle');
    }, 2000);
  }

  function handleScanError(errorMessage) {
    stopQuickScanPolling();
    stopCountdown();

    updateQuickScanStatusDisplay('error', errorMessage);
    showLastResult('error', errorMessage);

    qsSessionId = null;
    qsIsScanning = false;

    // Clear user selection so user can try again
    $(qsUserId).val('').trigger('change.select2');

    // Reset status after a short delay
    setTimeout(() => {
      updateQuickScanStatusDisplay('idle');
    }, 2000);
  }

  function handleScanExpired() {
    stopQuickScanPolling();
    stopCountdown();

    updateQuickScanStatusDisplay('expired');
    showLastResult('error', 'Sesi habis. Silakan pilih user lagi.');

    qsSessionId = null;
    qsIsScanning = false;

    // Clear user selection so user can retry
    $(qsUserId).val('').trigger('change.select2');

    // Reset status after a short delay
    setTimeout(() => {
      updateQuickScanStatusDisplay('idle');
    }, 2000);
  }

  function stopQuickScanPolling() {
    if (qsPollingInterval) {
      clearInterval(qsPollingInterval);
      qsPollingInterval = null;
    }
  }

  function startCountdown(seconds) {
    const countdownEl = document.getElementById('qs-countdown');
    let remaining = seconds;

    if (countdownEl) {
      countdownEl.textContent = `${remaining}s`;
    }

    qsCountdownInterval = setInterval(() => {
      remaining--;
      if (countdownEl) {
        countdownEl.textContent = `${remaining}s`;
      }

      if (remaining <= 0) {
        stopCountdown();
        stopQuickScanPolling();
        handleScanExpired();
      }
    }, 1000);
  }

  function stopCountdown() {
    if (qsCountdownInterval) {
      clearInterval(qsCountdownInterval);
      qsCountdownInterval = null;
    }
  }

  function cancelQuickScanSession() {
    if (!qsSessionId) return;

    fetch(`${baseUrl}/cancel-registration`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        Accept: 'application/json'
      },
      body: JSON.stringify({ session_id: qsSessionId })
    }).catch(error => console.error('Cancel error:', error));

    qsSessionId = null;
  }
});



