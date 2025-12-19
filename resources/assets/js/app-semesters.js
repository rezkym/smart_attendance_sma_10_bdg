/**
 * App Semesters Management
 * Semesters management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const offcanvasAddSemester = document.getElementById('offcanvasAddSemester');
  const addNewSemesterForm = document.getElementById('addNewSemesterForm');
  const semesterIdInput = document.getElementById('semester_id');
  const semesterAcademicYearInput = document.getElementById('add-semester-academic-year');
  const semesterTypeInput = document.getElementById('add-semester-type');
  const semesterStartDateInput = document.getElementById('add-semester-start-date');
  const semesterEndDateInput = document.getElementById('add-semester-end-date');
  const semesterIsActiveInput = document.getElementById('add-semester-is-active');
  const offcanvasTitle = document.getElementById('offcanvasAddSemesterLabel');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtSemesterTable = document.querySelector('.datatables-semesters');

  // Base URLs
  const semestersBaseUrl = '/admin/semesters';

  // Initialize Select2
  if (semesterAcademicYearInput && typeof $.fn.select2 !== 'undefined') {
    $(semesterAcademicYearInput).select2({
      dropdownParent: offcanvasAddSemester,
      placeholder: 'Pilih Tahun Ajaran',
      allowClear: true
    });
  }

  // Initialize Flatpickr for date inputs
  if (semesterStartDateInput) {
    flatpickr(semesterStartDateInput, {
      dateFormat: 'Y-m-d',
      allowInput: true
    });
  }

  if (semesterEndDateInput) {
    flatpickr(semesterEndDateInput, {
      dateFormat: 'Y-m-d',
      allowInput: true
    });
  }

  // Semesters DataTable
  let dt_Semester;
  if (dtSemesterTable) {
    dt_Semester = new DataTable(dtSemesterTable, {
      ajax: {
        url: `${semestersBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'academic_year_name' },
        { data: 'type_label' },
        { data: 'period' },
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
          // Academic Year
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="fw-medium">${full.academic_year_name || ''}</span>`;
          }
        },
        {
          // Type
          targets: 3,
          render: function (data, type, full) {
            const typeLabel = full.type_label || '';
            const badgeClass = typeLabel === 'Ganjil' ? 'bg-label-info' : 'bg-label-warning';
            return `<span class="badge rounded-pill ${badgeClass}">${typeLabel}</span>`;
          }
        },
        {
          // Period
          targets: 4,
          render: function (data, type, full) {
            return `<span>${full.period || ''}</span>`;
          }
        },
        {
          // Status
          targets: 5,
          render: function (data, type, full) {
            const isActive = full.status;
            if (isActive) {
              return '<span class="badge rounded-pill bg-label-success">Aktif</span>';
            }
            return '<span class="badge rounded-pill bg-label-secondary">Tidak Aktif</span>';
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
                <a href="javascript:;" class="btn btn-icon btn-text-success waves-effect waves-light rounded-pill set-active-semester" data-id="${full.id}" data-name="${full.academic_year_name} - ${full.type_label}" title="Set sebagai Aktif">
                  <i class="icon-base ri ri-checkbox-circle-line icon-22px"></i>
                </a>
              `;
            }
            return `
              <div class="d-flex align-items-center">
                ${setActiveBtn}
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-semester" data-id="${full.id}">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-semester" data-id="${full.id}" data-name="${full.academic_year_name} - ${full.type_label}" data-is-active="${isActive}">
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
                text: 'Tampilkan _MENU_'
              }
            }
          ]
        },
        topEnd: {
          features: [
            {
              search: {
                placeholder: 'Cari Semester',
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
              return 'Detail Semester: ' + data.academic_year_name + ' - ' + data.type_label;
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
  if (offcanvasAddSemester) {
    offcanvasAddSemester.addEventListener('hidden.bs.offcanvas', function () {
      resetForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtSemesterTable) {
    dtSemesterTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-semester');
      if (editBtn) {
        const id = editBtn.getAttribute('data-id');
        loadSemesterData(id);
      }

      const deleteBtn = e.target.closest('.delete-semester');
      if (deleteBtn) {
        const id = deleteBtn.getAttribute('data-id');
        const name = deleteBtn.getAttribute('data-name');
        const isActive = deleteBtn.getAttribute('data-is-active') === 'true';
        deleteSemester(id, name, isActive);
      }

      const setActiveBtn = e.target.closest('.set-active-semester');
      if (setActiveBtn) {
        const id = setActiveBtn.getAttribute('data-id');
        const name = setActiveBtn.getAttribute('data-name');
        setActiveSemester(id, name);
      }
    });
  }

  // Form submission
  if (addNewSemesterForm) {
    addNewSemesterForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const id = semesterIdInput?.value;
      const academicYearId = semesterAcademicYearInput?.value;
      const type = semesterTypeInput?.value;
      const startDate = semesterStartDateInput?.value;
      const endDate = semesterEndDateInput?.value;
      const isActive = semesterIsActiveInput?.checked || false;

      // Basic validation
      if (!academicYearId) {
        showAlert('error', 'Error Validasi', 'Tahun ajaran wajib dipilih.');
        return;
      }

      if (!type) {
        showAlert('error', 'Error Validasi', 'Tipe semester wajib dipilih.');
        return;
      }

      if (!startDate) {
        showAlert('error', 'Error Validasi', 'Tanggal mulai wajib diisi.');
        return;
      }

      if (!endDate) {
        showAlert('error', 'Error Validasi', 'Tanggal akhir wajib diisi.');
        return;
      }

      const isEdit = id !== '';
      const url = isEdit ? `${semestersBaseUrl}/${id}` : semestersBaseUrl;
      const method = isEdit ? 'PUT' : 'POST';

      const payload = {
        academic_year_id: parseInt(academicYearId),
        type: parseInt(type),
        start_date: startDate,
        end_date: endDate,
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
            showAlert('success', 'Berhasil!', data.message).then(() => {
              bootstrap.Offcanvas.getInstance(offcanvasAddSemester).hide();
              dt_Semester.ajax.reload();
              // Reload page to update stats
              window.location.reload();
            });
          } else {
            let errorMessage = data.message || 'Terjadi kesalahan.';
            if (data.errors) {
              errorMessage = Object.values(data.errors).flat().join('\n');
            }
            showAlert('error', 'Error!', errorMessage);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('error', 'Error!', 'Terjadi kesalahan tidak terduga.');
        });
    });
  }

  // Helper Functions
  function resetForm() {
    if (addNewSemesterForm) addNewSemesterForm.reset();
    if (semesterIdInput) semesterIdInput.value = '';
    if (semesterAcademicYearInput) {
      $(semesterAcademicYearInput).val('').trigger('change');
    }
    if (semesterTypeInput) semesterTypeInput.value = '';
    if (semesterStartDateInput) semesterStartDateInput.value = '';
    if (semesterEndDateInput) semesterEndDateInput.value = '';
    if (semesterIsActiveInput) semesterIsActiveInput.checked = false;
    if (offcanvasTitle) offcanvasTitle.textContent = 'Tambah Semester';
  }

  function loadSemesterData(id) {
    fetch(`${semestersBaseUrl}/${id}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (semesterIdInput) semesterIdInput.value = data.data.id;
          if (semesterAcademicYearInput) {
            $(semesterAcademicYearInput).val(data.data.academic_year_id).trigger('change');
          }
          if (semesterTypeInput) semesterTypeInput.value = data.data.type;
          if (semesterStartDateInput) semesterStartDateInput.value = data.data.start_date;
          if (semesterEndDateInput) semesterEndDateInput.value = data.data.end_date;
          if (semesterIsActiveInput) semesterIsActiveInput.checked = data.data.is_active;
          if (offcanvasTitle) offcanvasTitle.textContent = 'Edit Semester';

          // Show the offcanvas
          const offcanvas = new bootstrap.Offcanvas(offcanvasAddSemester);
          offcanvas.show();
        } else {
          showAlert('error', 'Error!', data.message || 'Gagal memuat data.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error!', 'Terjadi kesalahan tidak terduga.');
      });
  }

  function deleteSemester(id, name, isActive) {
    if (isActive) {
      showAlert('error', 'Tidak Dapat Menghapus', 'Tidak dapat menghapus semester yang sedang aktif. Silakan aktifkan semester lain terlebih dahulu.');
      return;
    }

    Swal.fire({
      title: 'Hapus Semester?',
      text: `Apakah Anda yakin ingin menghapus "${name}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal',
      customClass: {
        confirmButton: 'btn btn-danger me-3',
        cancelButton: 'btn btn-outline-secondary'
      },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${semestersBaseUrl}/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Terhapus!', data.message).then(() => {
                dt_Semester.ajax.reload();
                window.location.reload();
              });
            } else {
              showAlert('error', 'Error!', data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error!', 'Terjadi kesalahan tidak terduga.');
          });
      }
    });
  }

  function setActiveSemester(id, name) {
    Swal.fire({
      title: 'Set sebagai Aktif?',
      text: `Set "${name}" sebagai semester aktif? Ini akan menonaktifkan semua semester lainnya.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, aktifkan!',
      cancelButtonText: 'Batal',
      customClass: {
        confirmButton: 'btn btn-success me-3',
        cancelButton: 'btn btn-outline-secondary'
      },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${semestersBaseUrl}/${id}/set-active`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Berhasil!', data.message).then(() => {
                window.location.reload();
              });
            } else {
              showAlert('error', 'Error!', data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error!', 'Terjadi kesalahan tidak terduga.');
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
