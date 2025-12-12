/**
 * App Attendances Management
 * Attendance management with DataTable, filters, and bulk entry
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  // DOM Elements - DataTable
  const dtAttendanceTable = document.querySelector('.datatables-attendances');
  const filterDate = document.getElementById('filter-date');
  const filterClassroom = document.getElementById('filter-classroom');
  const filterStatus = document.getElementById('filter-status');
  const btnClearFilters = document.getElementById('btn-clear-filters');

  // DOM Elements - Bulk Entry
  const bulkClassroomSelect = document.getElementById('bulk-classroom');
  const bulkScheduleSelect = document.getElementById('bulk-schedule');
  const bulkDateInput = document.getElementById('bulk-date');
  const btnLoadStudents = document.getElementById('btn-load-students');
  const btnSubmitBulk = document.getElementById('btn-submit-bulk');
  const bulkStudentsContainer = document.getElementById('bulk-students-container');
  const bulkStudentsBody = document.getElementById('bulk-students-body');
  const bulkClassroomName = document.getElementById('bulk-classroom-name');

  // DOM Elements - Edit Offcanvas
  const offcanvasEditAttendance = document.getElementById('offcanvasEditAttendance');
  const editAttendanceForm = document.getElementById('editAttendanceForm');
  const editAttendanceId = document.getElementById('edit_attendance_id');
  const editStudentName = document.getElementById('edit-student-name');
  const editStudentNisn = document.getElementById('edit-student-nisn');
  const editClassroomName = document.getElementById('edit-classroom-name');
  const editSubjectName = document.getElementById('edit-subject-name');
  const editAttendanceDate = document.getElementById('edit-attendance-date');
  const editStatus = document.getElementById('edit-status');
  const editCheckInTime = document.getElementById('edit-check-in-time');
  const editCheckOutTime = document.getElementById('edit-check-out-time');
  const editNotes = document.getElementById('edit-notes');

  // Base URLs
  const attendancesBaseUrl = '/admin/attendances';
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // Status colors mapping
  const statusColors = {
    present: 'success',
    late: 'warning',
    excused: 'info',
    sick: 'primary',
    absent: 'danger'
  };

  const statusLabels = {
    present: 'Hadir',
    late: 'Terlambat',
    excused: 'Izin',
    sick: 'Sakit',
    absent: 'Alpha'
  };

  // Initialize Flatpickr for date inputs
  let filterDatePicker, bulkDatePicker;

  if (filterDate) {
    filterDatePicker = flatpickr(filterDate, {
      dateFormat: 'Y-m-d',
      allowInput: true,
      onChange: function () {
        if (dt_Attendance) {
          dt_Attendance.ajax.reload();
        }
      }
    });
  }

  if (bulkDateInput) {
    bulkDatePicker = flatpickr(bulkDateInput, {
      dateFormat: 'Y-m-d',
      allowInput: true,
      maxDate: 'today',
      defaultDate: 'today'
    });
  }

  // Initialize Select2 for bulk classroom
  if (bulkClassroomSelect && $.fn.select2) {
    $(bulkClassroomSelect).select2({
      placeholder: 'Select Classroom',
      allowClear: true
    });

    $(bulkClassroomSelect).on('change', function () {
      loadSchedulesForClassroom(this.value);
    });
  }

  // Attendances DataTable
  let dt_Attendance;
  if (dtAttendanceTable) {
    dt_Attendance = new DataTable(dtAttendanceTable, {
      ajax: {
        url: `${attendancesBaseUrl}/list`,
        data: function (d) {
          d.date = filterDate?.value || '';
          d.classroom_id = filterClassroom?.value || '';
          d.status = filterStatus?.value || '';
        },
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'attendance_date' },
        { data: 'student_name' },
        { data: 'classroom_name' },
        { data: 'subject_name' },
        { data: 'check_in_time' },
        { data: 'status' },
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
          // Date
          targets: 2,
          render: function (data, type, full) {
            return `<span class="text-nowrap">${full.date_formatted || full.attendance_date}</span>`;
          }
        },
        {
          // Student
          targets: 3,
          responsivePriority: 1,
          render: function (data, type, full) {
            const nisn = full.student_nisn ? `<small class="text-muted d-block">${full.student_nisn}</small>` : '';
            return `<span class="fw-medium">${full.student_name || '-'}</span>${nisn}`;
          }
        },
        {
          // Classroom
          targets: 4,
          render: function (data, type, full) {
            return full.classroom_name || '-';
          }
        },
        {
          // Subject
          targets: 5,
          render: function (data, type, full) {
            return full.subject_name || '-';
          }
        },
        {
          // Time
          targets: 6,
          render: function (data, type, full) {
            const checkIn = full.check_in_time ? full.check_in_time.substring(0, 5) : '--:--';
            const checkOut = full.check_out_time ? full.check_out_time.substring(0, 5) : '--:--';
            return `<span class="text-nowrap">${checkIn} - ${checkOut}</span>`;
          }
        },
        {
          // Status
          targets: 7,
          render: function (data, type, full) {
            const status = full.status;
            const color = full.status_color || statusColors[status] || 'secondary';
            const label = full.status_label || statusLabels[status] || status;
            return `<span class="badge rounded-pill bg-label-${color}">${label}</span>`;
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
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-attendance" data-id="${full.id}">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-attendance" data-id="${full.id}" data-name="${full.student_name}">
                  <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
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
          features: [{ pageLength: { menu: [10, 25, 50, 100], text: 'Show _MENU_' } }]
        },
        topEnd: {
          features: [{ search: { placeholder: 'Search...', text: '_INPUT_' } }]
        },
        bottomStart: { rowClass: 'row mx-3 justify-content-between', features: ['info'] },
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
              return 'Attendance: ' + data.student_name;
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

    // Filter change events
    filterClassroom?.addEventListener('change', () => dt_Attendance.ajax.reload());
    filterStatus?.addEventListener('change', () => dt_Attendance.ajax.reload());

    // Clear filters
    btnClearFilters?.addEventListener('click', function () {
      if (filterDatePicker) filterDatePicker.clear();
      if (filterClassroom) filterClassroom.value = '';
      if (filterStatus) filterStatus.value = '';
      dt_Attendance.ajax.reload();
    });

    // Event delegation for DataTable actions
    dtAttendanceTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-attendance');
      if (editBtn) {
        loadAttendanceData(editBtn.getAttribute('data-id'));
      }

      const deleteBtn = e.target.closest('.delete-attendance');
      if (deleteBtn) {
        deleteAttendance(deleteBtn.getAttribute('data-id'), deleteBtn.getAttribute('data-name'));
      }
    });
  }

  // Load schedules for selected classroom
  function loadSchedulesForClassroom(classroomId) {
    if (!classroomId) {
      bulkScheduleSelect.innerHTML = '<option value="">Select classroom first</option>';
      bulkScheduleSelect.disabled = true;
      return;
    }

    fetch(`${attendancesBaseUrl}/schedules-by-classroom/${classroomId}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          bulkScheduleSelect.innerHTML = '<option value="">Select Schedule</option>';
          data.data.forEach(schedule => {
            const option = document.createElement('option');
            option.value = schedule.id;
            option.textContent = `${schedule.day_label} ${schedule.start_time.substring(0, 5)} - ${schedule.end_time.substring(0, 5)} | ${schedule.subject_name}`;
            bulkScheduleSelect.appendChild(option);
          });
          bulkScheduleSelect.disabled = false;
        }
      })
      .catch(error => {
        console.error('Error loading schedules:', error);
        showAlert('error', 'Error!', 'Failed to load schedules.');
      });
  }

  // Load students for bulk attendance
  btnLoadStudents?.addEventListener('click', function () {
    const classroomId = $(bulkClassroomSelect).val();
    const scheduleId = bulkScheduleSelect.value;
    const date = bulkDateInput.value;

    if (!classroomId || !scheduleId || !date) {
      showAlert('error', 'Validation Error', 'Please select classroom, schedule, and date.');
      return;
    }

    // Get classroom name
    const classroomOption = bulkClassroomSelect.options[bulkClassroomSelect.selectedIndex];
    bulkClassroomName.textContent = classroomOption?.textContent || '';

    // Fetch classroom attendance data
    fetch(`${attendancesBaseUrl}/classroom/${classroomId}/date/${date}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderBulkStudentsTable(data.data.students, data.data.attendances);
          bulkStudentsContainer.classList.remove('d-none');
          btnSubmitBulk.classList.remove('d-none');
        } else {
          showAlert('error', 'Error!', data.message || 'Failed to load students.');
        }
      })
      .catch(error => {
        console.error('Error loading students:', error);
        showAlert('error', 'Error!', 'Failed to load students.');
      });
  });

  // Render bulk students table
  function renderBulkStudentsTable(students, existingAttendances) {
    // Create attendance map for quick lookup
    const attendanceMap = {};
    existingAttendances.forEach(att => {
      attendanceMap[att.student_id] = att;
    });

    let html = '';
    students.forEach((student, index) => {
      const existing = attendanceMap[student.id];
      const currentStatus = existing?.status || '';

      html += `
        <tr data-student-id="${student.id}">
          <td>${index + 1}</td>
          <td>${student.nisn || '-'}</td>
          <td class="fw-medium">${student.name}</td>
          <td>
            <div class="d-flex flex-wrap gap-2">
              <div class="form-check form-check-inline">
                <input class="form-check-input status-radio" type="radio" name="status_${student.id}" id="present_${student.id}" value="present" ${currentStatus === 'present' ? 'checked' : ''}>
                <label class="form-check-label text-success" for="present_${student.id}">Hadir</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input status-radio" type="radio" name="status_${student.id}" id="late_${student.id}" value="late" ${currentStatus === 'late' ? 'checked' : ''}>
                <label class="form-check-label text-warning" for="late_${student.id}">Terlambat</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input status-radio" type="radio" name="status_${student.id}" id="sick_${student.id}" value="sick" ${currentStatus === 'sick' ? 'checked' : ''}>
                <label class="form-check-label text-primary" for="sick_${student.id}">Sakit</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input status-radio" type="radio" name="status_${student.id}" id="excused_${student.id}" value="excused" ${currentStatus === 'excused' ? 'checked' : ''}>
                <label class="form-check-label text-info" for="excused_${student.id}">Izin</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input status-radio" type="radio" name="status_${student.id}" id="absent_${student.id}" value="absent" ${currentStatus === 'absent' ? 'checked' : ''}>
                <label class="form-check-label text-danger" for="absent_${student.id}">Alpha</label>
              </div>
            </div>
          </td>
          <td>
            <input type="text" class="form-control form-control-sm notes-input" placeholder="Notes..." value="${existing?.notes || ''}">
          </td>
        </tr>
      `;
    });

    bulkStudentsBody.innerHTML = html;
  }

  // Set all status buttons
  document.querySelectorAll('.set-all-status').forEach(btn => {
    btn.addEventListener('click', function () {
      const status = this.getAttribute('data-status');
      document.querySelectorAll(`.status-radio[value="${status}"]`).forEach(radio => {
        radio.checked = true;
      });
    });
  });

  // Submit bulk attendance
  btnSubmitBulk?.addEventListener('click', function () {
    const scheduleId = bulkScheduleSelect.value;
    const date = bulkDateInput.value;

    if (!scheduleId || !date) {
      showAlert('error', 'Validation Error', 'Please select schedule and date.');
      return;
    }

    const attendances = [];
    const rows = bulkStudentsBody.querySelectorAll('tr');

    rows.forEach(row => {
      const studentId = row.getAttribute('data-student-id');
      const checkedRadio = row.querySelector('input.status-radio:checked');
      const notesInput = row.querySelector('.notes-input');

      if (checkedRadio) {
        attendances.push({
          student_id: parseInt(studentId),
          status: checkedRadio.value,
          notes: notesInput?.value || null
        });
      }
    });

    if (attendances.length === 0) {
      showAlert('error', 'Validation Error', 'Please select status for at least one student.');
      return;
    }

    const payload = {
      schedule_id: parseInt(scheduleId),
      attendance_date: date,
      attendances: attendances
    };

    fetch(`${attendancesBaseUrl}/bulk`, {
      method: 'POST',
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
          showAlert('success', 'Success!', data.message || 'Bulk attendance recorded successfully.').then(() => {
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

  // Load attendance data for editing
  function loadAttendanceData(id) {
    fetch(`${attendancesBaseUrl}/${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const attendance = data.data;

          editAttendanceId.value = attendance.id;
          editStudentName.textContent = attendance.student_name;
          editStudentNisn.textContent = attendance.student_nisn || '-';
          editClassroomName.textContent = attendance.classroom_name || '-';
          editSubjectName.textContent = attendance.subject_name || '-';
          editAttendanceDate.textContent = attendance.attendance_date_formatted || attendance.attendance_date;
          editStatus.value = attendance.status;
          editCheckInTime.value = attendance.check_in_time ? attendance.check_in_time.substring(0, 8) : '';
          editCheckOutTime.value = attendance.check_out_time ? attendance.check_out_time.substring(0, 8) : '';
          editNotes.value = attendance.notes || '';

          const offcanvas = new bootstrap.Offcanvas(offcanvasEditAttendance);
          offcanvas.show();
        } else {
          showAlert('error', 'Error!', data.message || 'Failed to load attendance data.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error!', 'An unexpected error occurred.');
      });
  }

  // Edit attendance form submission
  editAttendanceForm?.addEventListener('submit', function (e) {
    e.preventDefault();

    const id = editAttendanceId.value;
    const payload = {
      status: editStatus.value,
      check_in_time: editCheckInTime.value || null,
      check_out_time: editCheckOutTime.value || null,
      notes: editNotes.value || null
    };

    fetch(`${attendancesBaseUrl}/${id}`, {
      method: 'PUT',
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
          showAlert('success', 'Success!', data.message).then(() => {
            bootstrap.Offcanvas.getInstance(offcanvasEditAttendance).hide();
            dt_Attendance.ajax.reload();
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

  // Delete attendance
  function deleteAttendance(id, name) {
    Swal.fire({
      title: 'Delete Attendance?',
      text: `Are you sure you want to delete attendance for "${name}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${attendancesBaseUrl}/${id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Deleted!', data.message).then(() => {
                dt_Attendance.ajax.reload();
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

  // Helper: Show SweetAlert
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
        if (classToRemove) classToRemove.split(' ').forEach(c => element.classList.remove(c));
        if (classToAdd) classToAdd.split(' ').forEach(c => element.classList.add(c));
      });
    });
  }, 100);
});
