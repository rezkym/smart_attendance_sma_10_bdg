/**
 * App Schedules Management
 * Schedule/Timetable management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const offcanvasAddSchedule = document.getElementById('offcanvasAddSchedule');
  const addNewScheduleForm = document.getElementById('addNewScheduleForm');
  const scheduleIdInput = document.getElementById('schedule_id');
  const academicYearSelect = document.getElementById('add-schedule-academic-year');
  const semesterSelect = document.getElementById('add-schedule-semester');
  const classroomSelect = document.getElementById('add-schedule-classroom');
  const subjectSelect = document.getElementById('add-schedule-subject');
  const teacherSelect = document.getElementById('add-schedule-teacher');
  const daySelect = document.getElementById('add-schedule-day');
  const startTimeInput = document.getElementById('add-schedule-start-time');
  const endTimeInput = document.getElementById('add-schedule-end-time');
  const notesInput = document.getElementById('add-schedule-notes');
  const isActiveInput = document.getElementById('add-schedule-is-active');
  const offcanvasTitle = document.getElementById('offcanvasAddScheduleLabel');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtScheduleTable = document.querySelector('.datatables-schedules');

  // Base URLs
  const schedulesBaseUrl = '/admin/schedules';
  const semestersBaseUrl = '/admin/semesters';
  let isEditMode = false;
  let select2Initialized = false;
  let pendingSemesterId = null; // Store semester ID for edit mode

  // Day mapping for display (Indonesian)
  const dayNames = {
    1: 'Senin',
    2: 'Selasa',
    3: 'Rabu',
    4: 'Kamis',
    5: 'Jumat',
    6: 'Sabtu'
  };

  // Initialize Select2 for all dropdowns
  function initSelect2() {
    if (!select2Initialized) {
      const select2Elements = [
        { element: academicYearSelect, placeholder: 'Select Academic Year' },
        { element: classroomSelect, placeholder: 'Select Classroom' },
        { element: subjectSelect, placeholder: 'Select Subject' },
        { element: teacherSelect, placeholder: 'Select Teacher' }
      ];

      select2Elements.forEach(({ element, placeholder }) => {
        if (element) {
          $(element).select2({
            dropdownParent: $('#offcanvasAddSchedule'),
            placeholder: placeholder,
            allowClear: true
          });
        }
      });

      select2Initialized = true;
    }
  }

  // Load dropdown data from endpoint
  function loadDropdownData(selectedData = null) {
    fetch(`${schedulesBaseUrl}/dropdown-data`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Populate Academic Years
          $(academicYearSelect).empty().append(new Option('Select Academic Year', '', true, true));
          data.data.academicYears.forEach(year => {
            const isActive = year.is_active ? ' (Active)' : '';
            const option = new Option(`${year.name}${isActive}`, year.id, false, false);
            $(academicYearSelect).append(option);
          });

          // Populate Classrooms
          $(classroomSelect).empty().append(new Option('Select Classroom', '', true, true));
          data.data.classrooms.forEach(classroom => {
            const option = new Option(`${classroom.name} (Grade ${classroom.grade_level})`, classroom.id, false, false);
            $(classroomSelect).append(option);
          });

          // Populate Subjects
          $(subjectSelect).empty().append(new Option('Select Subject', '', true, true));
          data.data.subjects.forEach(subject => {
            const option = new Option(`${subject.name} (${subject.code})`, subject.id, false, false);
            $(subjectSelect).append(option);
          });

          // Populate Teachers
          $(teacherSelect).empty().append(new Option('Select Teacher', '', true, true));
          data.data.teachers.forEach(teacher => {
            const displayName = teacher.display_name || teacher.name;
            const nip = teacher.nip ? ` (${teacher.nip})` : '';
            const option = new Option(`${displayName}${nip}`, teacher.id, false, false);
            $(teacherSelect).append(option);
          });

          // Set selected values if editing
          if (selectedData) {
            setTimeout(() => {
              if (selectedData.academic_year_id) {
                $(academicYearSelect).val(selectedData.academic_year_id).trigger('change');
                // Store semester ID to set after semesters are loaded
                if (selectedData.semester_id) {
                  pendingSemesterId = selectedData.semester_id;
                }
              }
              if (selectedData.classroom_id) {
                $(classroomSelect).val(selectedData.classroom_id).trigger('change');
              }
              if (selectedData.subject_id) {
                $(subjectSelect).val(selectedData.subject_id).trigger('change');
              }
              if (selectedData.teacher_id) {
                $(teacherSelect).val(selectedData.teacher_id).trigger('change');
              }
            }, 100);
          }
        }
      })
      .catch(error => {
        console.error('Error loading dropdown data:', error);
      });
  }

  // Load semesters for selected academic year
  function loadSemestersByAcademicYear(academicYearId) {
    if (!academicYearId) {
      semesterSelect.innerHTML = '<option value="">Pilih Tahun Ajaran Terlebih Dahulu</option>';
      semesterSelect.disabled = true;
      return;
    }

    semesterSelect.disabled = true;
    semesterSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`${semestersBaseUrl}/by-academic-year/${academicYearId}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          semesterSelect.innerHTML = '<option value="">Pilih Semester</option>';
          data.data.forEach(semester => {
            const isActive = semester.is_active ? ' (Aktif)' : '';
            const option = document.createElement('option');
            option.value = semester.id;
            option.textContent = `${semester.label}${isActive}`;
            semesterSelect.appendChild(option);
          });
          semesterSelect.disabled = false;

          // Set pending semester ID if in edit mode
          if (pendingSemesterId) {
            semesterSelect.value = pendingSemesterId;
            pendingSemesterId = null;
          }
        }
      })
      .catch(error => {
        console.error('Error loading semesters:', error);
        semesterSelect.innerHTML = '<option value="">Error loading semesters</option>';
      });
  }

  // Academic year change handler
  if (academicYearSelect) {
    $(academicYearSelect).on('change', function() {
      loadSemestersByAcademicYear(this.value);
    });
  }

  // Format time for display (HH:MM - HH:MM)
  function formatTimeRange(startTime, endTime) {
    const formatTime = time => {
      if (!time) return '--:--';
      // Handle both H:i:s and H:i formats
      return time.substring(0, 5);
    };
    return `${formatTime(startTime)} - ${formatTime(endTime)}`;
  }

  // Schedules DataTable
  let dt_Schedule;
  if (dtScheduleTable) {
    dt_Schedule = new DataTable(dtScheduleTable, {
      ajax: {
        url: `${schedulesBaseUrl}/list`,
        dataSrc: 'data'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'classroom_name' },
        { data: 'day_of_week' },
        { data: 'start_time' },
        { data: 'subject_name' },
        { data: 'teacher_name' },
        { data: 'is_active' },
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
          // Classroom
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full) {
            return `<span class="fw-medium">${full.classroom_name || '-'}</span>`;
          }
        },
        {
          // Day of Week
          targets: 3,
          render: function (data, type, full) {
            const dayName = dayNames[full.day_of_week] || '-';
            return `<span class="badge bg-label-info">${dayName}</span>`;
          }
        },
        {
          // Time Range
          targets: 4,
          render: function (data, type, full) {
            return `<span class="text-nowrap">${formatTimeRange(full.start_time, full.end_time)}</span>`;
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
          // Teacher
          targets: 6,
          render: function (data, type, full) {
            return full.teacher_name || '-';
          }
        },
        {
          // Status
          targets: 7,
          render: function (data, type, full) {
            const isActive = full.is_active;
            if (isActive) {
              return '<span class="badge rounded-pill bg-label-success">Active</span>';
            }
            return '<span class="badge rounded-pill bg-label-secondary">Inactive</span>';
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
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-schedule" data-id="${full.id}">
                  <i class="icon-base ri ri-edit-box-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-schedule" data-id="${full.id}" data-name="${full.classroom_name} - ${dayNames[full.day_of_week] || ''}">
                  <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
                </a>
              </div>
            `;
          }
        }
      ],
      order: [[3, 'asc'], [4, 'asc']], // Sort by day, then time
      layout: {
        topStart: {
          rowClass: 'row mx-2',
          features: [{ pageLength: { menu: [10, 25, 50, 100], text: 'Show _MENU_' } }]
        },
        topEnd: {
          features: [{ search: { placeholder: 'Search Schedule', text: '_INPUT_' } }]
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
              return 'Schedule: ' + data.classroom_name + ' - ' + (dayNames[data.day_of_week] || '');
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

  // Initialize Select2 and load dropdown data when offcanvas opens
  if (offcanvasAddSchedule) {
    offcanvasAddSchedule.addEventListener('show.bs.offcanvas', function () {
      initSelect2();
      if (!isEditMode) {
        loadDropdownData();
      }
    });

    offcanvasAddSchedule.addEventListener('hidden.bs.offcanvas', function () {
      resetForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtScheduleTable) {
    dtScheduleTable.addEventListener('click', function (e) {
      const editBtn = e.target.closest('.edit-schedule');
      if (editBtn) {
        loadScheduleData(editBtn.getAttribute('data-id'));
      }

      const deleteBtn = e.target.closest('.delete-schedule');
      if (deleteBtn) {
        deleteSchedule(deleteBtn.getAttribute('data-id'), deleteBtn.getAttribute('data-name'));
      }
    });
  }

  // Form submission
  if (addNewScheduleForm) {
    addNewScheduleForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const id = scheduleIdInput?.value;
      const academicYearId = $(academicYearSelect).val();
      const semesterId = semesterSelect?.value;
      const classroomId = $(classroomSelect).val();
      const subjectId = $(subjectSelect).val();
      const teacherId = $(teacherSelect).val();
      const dayOfWeek = daySelect?.value;
      const startTime = startTimeInput?.value;
      const endTime = endTimeInput?.value;
      const notes = notesInput?.value.trim();
      const isActive = isActiveInput?.checked || false;

      // Validation
      if (!academicYearId || !semesterId || !classroomId || !subjectId || !teacherId || !dayOfWeek || !startTime || !endTime) {
        showAlert('error', 'Validation Error', 'Please fill in all required fields.');
        return;
      }

      // Validate end time > start time
      if (endTime <= startTime) {
        showAlert('error', 'Validation Error', 'End time must be after start time.');
        return;
      }

      const url = isEditMode ? `${schedulesBaseUrl}/${id}` : schedulesBaseUrl;
      const method = isEditMode ? 'PUT' : 'POST';

      const payload = {
        academic_year_id: parseInt(academicYearId),
        semester_id: parseInt(semesterId),
        classroom_id: parseInt(classroomId),
        subject_id: parseInt(subjectId),
        teacher_id: parseInt(teacherId),
        day_of_week: parseInt(dayOfWeek),
        start_time: startTime,
        end_time: endTime,
        notes: notes || null,
        is_active: isActive
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
            showAlert('success', 'Success!', data.message).then(() => {
              bootstrap.Offcanvas.getInstance(offcanvasAddSchedule).hide();
              dt_Schedule.ajax.reload();
              window.location.reload();
            });
          } else {
            // Handle time conflict or other errors
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
    if (addNewScheduleForm) addNewScheduleForm.reset();
    if (scheduleIdInput) scheduleIdInput.value = '';
    if (daySelect) daySelect.value = '';
    if (startTimeInput) startTimeInput.value = '';
    if (endTimeInput) endTimeInput.value = '';
    if (notesInput) notesInput.value = '';
    if (isActiveInput) isActiveInput.checked = true;
    if (offcanvasTitle) offcanvasTitle.textContent = 'Add Schedule';

    // Reset Select2
    $(academicYearSelect).val('').trigger('change');
    $(classroomSelect).val('').trigger('change');
    $(subjectSelect).val('').trigger('change');
    $(teacherSelect).val('').trigger('change');

    // Reset semester dropdown
    if (semesterSelect) {
      semesterSelect.innerHTML = '<option value="">Pilih Tahun Ajaran Terlebih Dahulu</option>';
      semesterSelect.disabled = true;
    }

    pendingSemesterId = null;
    isEditMode = false;
  }

  function loadScheduleData(id) {
    isEditMode = true;
    
    fetch(`${schedulesBaseUrl}/${id}`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const scheduleData = data.data;

          // Set hidden ID
          if (scheduleIdInput) scheduleIdInput.value = scheduleData.id;

          // Set day of week
          if (daySelect) daySelect.value = scheduleData.day_of_week;

          // Set times (handle H:i:s format by taking first 5 chars)
          if (startTimeInput) {
            startTimeInput.value = scheduleData.start_time ? scheduleData.start_time.substring(0, 5) : '';
          }
          if (endTimeInput) {
            endTimeInput.value = scheduleData.end_time ? scheduleData.end_time.substring(0, 5) : '';
          }

          // Set notes
          if (notesInput) notesInput.value = scheduleData.notes || '';

          // Set is_active
          if (isActiveInput) isActiveInput.checked = scheduleData.is_active;

          // Update title
          if (offcanvasTitle) offcanvasTitle.textContent = 'Edit Schedule';

          // Load dropdown data with selected values
          loadDropdownData(scheduleData);

          // Show offcanvas
          const offcanvas = new bootstrap.Offcanvas(offcanvasAddSchedule);
          offcanvas.show();
        } else {
          showAlert('error', 'Error!', data.message || 'Failed to load schedule data.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error!', 'An unexpected error occurred.');
      });
  }

  function deleteSchedule(id, name) {
    Swal.fire({
      title: 'Delete Schedule?',
      text: `Are you sure you want to delete schedule "${name}"?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-outline-secondary' },
      buttonsStyling: false
    }).then(result => {
      if (result.isConfirmed) {
        fetch(`${schedulesBaseUrl}/${id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Deleted!', data.message).then(() => {
                dt_Schedule.ajax.reload();
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
