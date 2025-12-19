/**
 * App Student Enrollments Management
 * Student Enrollments management with CRUD operations and DataTable
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const dtEnrollmentsTable = document.querySelector('.datatables-enrollments');
  const baseUrl = '/admin/student-enrollments';

  // Filter elements
  const filterAcademicYear = document.getElementById('filter-academic-year');
  const filterClassroom = document.getElementById('filter-classroom');
  const filterStatus = document.getElementById('filter-status');
  const btnApplyFilter = document.getElementById('btn-apply-filter');

  // Enroll modal elements
  const enrollStudentModal = document.getElementById('enrollStudentModal');
  const enrollStudentForm = document.getElementById('enrollStudentForm');
  const enrollAction = document.getElementById('enroll-action');
  const enrollEnrollmentId = document.getElementById('enroll-enrollment-id');
  const enrollStudentId = document.getElementById('enroll-student-id');
  const enrollAcademicYear = document.getElementById('enroll-academic-year');
  const enrollClassroom = document.getElementById('enroll-classroom');
  const enrollDate = document.getElementById('enroll-date');
  const enrollModalTitle = document.getElementById('enrollStudentModalTitle');
  const studentSelectGroup = document.getElementById('student-select-group');

  // Confirm modal elements
  const actionConfirmModal = document.getElementById('actionConfirmModal');
  const confirmEnrollmentId = document.getElementById('confirm-enrollment-id');
  const confirmAction = document.getElementById('confirm-action');
  const actionConfirmTitle = document.getElementById('actionConfirmTitle');
  const actionConfirmMessage = document.getElementById('actionConfirmMessage');
  const confirmDate = document.getElementById('confirm-date');
  const btnConfirmAction = document.getElementById('btn-confirm-action');

  let dt_Enrollments;
  let bsEnrollModal;
  let bsConfirmModal;

  // Initialize Bootstrap modals
  if (enrollStudentModal) {
    bsEnrollModal = new bootstrap.Modal(enrollStudentModal);
  }
  if (actionConfirmModal) {
    bsConfirmModal = new bootstrap.Modal(actionConfirmModal);
  }

  // Initialize Select2
  function initSelect2() {
    if (filterAcademicYear) {
      $(filterAcademicYear).select2({ placeholder: 'All Academic Years', allowClear: true });
    }
    if (filterClassroom) {
      $(filterClassroom).select2({ placeholder: 'All Classrooms', allowClear: true });
    }
    if (enrollStudentId) {
      $(enrollStudentId).select2({
        dropdownParent: $(enrollStudentModal),
        placeholder: 'Select Student...',
        allowClear: true
      });
    }
    if (enrollAcademicYear) {
      $(enrollAcademicYear).select2({
        dropdownParent: $(enrollStudentModal),
        placeholder: 'Select Academic Year...'
      });
    }
    if (enrollClassroom) {
      $(enrollClassroom).select2({
        dropdownParent: $(enrollStudentModal),
        placeholder: 'Select Classroom...'
      });
    }
  }

  // Load available students for enrollment
  function loadAvailableStudents() {
    fetch(`${baseUrl}/available-students`, {
      method: 'GET',
      headers: { Accept: 'application/json' }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          $(enrollStudentId).empty();
          $(enrollStudentId).append(new Option('Select Student...', '', true, true));
          data.data.forEach(student => {
            const option = new Option(`${student.name} (${student.nisn})`, student.id, false, false);
            $(enrollStudentId).append(option);
          });
          $(enrollStudentId).trigger('change');
        }
      })
      .catch(error => console.error('Error loading students:', error));
  }

  // Initialize DataTable
  if (dtEnrollmentsTable) {
    dt_Enrollments = new DataTable(dtEnrollmentsTable, {
      ajax: {
        url: `${baseUrl}/list`,
        dataSrc: 'data',
        data: function (d) {
          d.academic_year_id = $(filterAcademicYear).val() || '';
          d.classroom_id = $(filterClassroom).val() || '';
          d.status = $(filterStatus).val() || '';
        }
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'student_name' },
        { data: 'student_nisn' },
        { data: 'classroom_name' },
        { data: 'academic_year_name' },
        { data: 'status' },
        { data: 'enrolled_at' },
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
            return `<span class="fw-medium">${full.student_name || ''}</span>`;
          }
        },
        {
          targets: 3,
          render: function (data, type, full) {
            return `<span class="badge bg-label-secondary">${full.student_nisn || '-'}</span>`;
          }
        },
        {
          targets: 4,
          render: function (data, type, full) {
            return full.classroom_name || '-';
          }
        },
        {
          targets: 5,
          render: function (data, type, full) {
            return full.academic_year_name || '-';
          }
        },
        {
          targets: 6,
          render: function (data, type, full) {
            const statusColors = {
              active: 'success',
              graduated: 'info',
              transferred: 'warning',
              dropped: 'danger'
            };
            const color = statusColors[full.status_key] || 'secondary';
            return `<span class="badge rounded-pill bg-label-${color}">${full.status_label || full.status}</span>`;
          }
        },
        {
          targets: 7,
          render: function (data, type, full) {
            return full.enrolled_at || '-';
          }
        },
        {
          targets: 8,
          orderable: false,
          searchable: false,
          render: function (data, type, full) {
            const isActive = full.status_key === 'active';
            let actions = `
              <div class="d-flex align-items-center">
            `;

            if (isActive) {
              actions += `
                <a href="javascript:;" class="btn btn-icon btn-text-warning waves-effect waves-light rounded-pill transfer-enrollment" 
                   data-id="${full.id}" data-student-id="${full.student_id}" data-student-name="${full.student_name}" title="Transfer">
                  <i class="icon-base ri ri-arrow-left-right-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-info waves-effect waves-light rounded-pill graduate-enrollment" 
                   data-id="${full.id}" data-student-name="${full.student_name}" title="Graduate">
                  <i class="icon-base ri ri-graduation-cap-line icon-22px"></i>
                </a>
                <a href="javascript:;" class="btn btn-icon btn-text-danger waves-effect waves-light rounded-pill drop-enrollment" 
                   data-id="${full.id}" data-student-name="${full.student_name}" title="Drop">
                  <i class="icon-base ri ri-user-unfollow-line icon-22px"></i>
                </a>
              `;
            }

            actions += `
                <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-enrollment" 
                   data-id="${full.id}" data-student-name="${full.student_name}" title="Delete">
                  <i class="icon-base ri ri-delete-bin-7-line icon-22px"></i>
                </a>
              </div>
            `;

            return actions;
          }
        }
      ],
      order: [[7, 'desc']],
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
          next: '<i class="icon-base ri ri-arrow-right-s-line icon-22px"></i>',
          previous: '<i class="icon-base ri ri-arrow-left-s-line icon-22px"></i>'
        }
      },
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              return 'Enrollment Details';
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
      dt_Enrollments.ajax.reload();
    });
  }

  // Modal show event - load available students
  if (enrollStudentModal) {
    enrollStudentModal.addEventListener('show.bs.modal', function () {
      loadAvailableStudents();
    });

    enrollStudentModal.addEventListener('hidden.bs.modal', function () {
      resetEnrollForm();
    });
  }

  // Event delegation for DataTable actions
  if (dtEnrollmentsTable) {
    dtEnrollmentsTable.addEventListener('click', function (e) {
      const transferBtn = e.target.closest('.transfer-enrollment');
      if (transferBtn) {
        openTransferModal(
          transferBtn.getAttribute('data-id'),
          transferBtn.getAttribute('data-student-id'),
          transferBtn.getAttribute('data-student-name')
        );
      }

      const graduateBtn = e.target.closest('.graduate-enrollment');
      if (graduateBtn) {
        openConfirmModal('graduate', graduateBtn.getAttribute('data-id'), graduateBtn.getAttribute('data-student-name'));
      }

      const dropBtn = e.target.closest('.drop-enrollment');
      if (dropBtn) {
        openConfirmModal('drop', dropBtn.getAttribute('data-id'), dropBtn.getAttribute('data-student-name'));
      }

      const deleteBtn = e.target.closest('.delete-enrollment');
      if (deleteBtn) {
        deleteEnrollment(deleteBtn.getAttribute('data-id'), deleteBtn.getAttribute('data-student-name'));
      }
    });
  }

  // Enroll form submission
  if (enrollStudentForm) {
    enrollStudentForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const action = enrollAction.value;
      const studentId = $(enrollStudentId).val();
      const academicYearId = $(enrollAcademicYear).val();
      const classroomId = $(enrollClassroom).val();
      const date = enrollDate.value;

      if (action === 'enroll') {
        // Enroll new student
        if (!studentId || !academicYearId || !classroomId || !date) {
          showAlert('error', 'Validation Error', 'Please fill all required fields.');
          return;
        }

        fetch(baseUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json'
          },
          body: JSON.stringify({
            student_id: parseInt(studentId),
            classroom_id: parseInt(classroomId),
            academic_year_id: parseInt(academicYearId),
            enrolled_at: date
          })
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Success!', data.message).then(() => {
                bsEnrollModal.hide();
                dt_Enrollments.ajax.reload();
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
      } else if (action === 'transfer') {
        // Transfer student
        const enrollmentId = enrollEnrollmentId.value;

        if (!classroomId || !academicYearId || !date) {
          showAlert('error', 'Validation Error', 'Please fill all required fields.');
          return;
        }

        fetch(`${baseUrl}/transfer`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json'
          },
          body: JSON.stringify({
            student_id: parseInt(studentId),
            classroom_id: parseInt(classroomId),
            academic_year_id: parseInt(academicYearId),
            transfer_date: date
          })
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              showAlert('success', 'Success!', data.message).then(() => {
                bsEnrollModal.hide();
                dt_Enrollments.ajax.reload();
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
      }
    });
  }

  // Confirm action button
  if (btnConfirmAction) {
    btnConfirmAction.addEventListener('click', function () {
      const enrollmentId = confirmEnrollmentId.value;
      const action = confirmAction.value;
      const date = confirmDate.value;

      let url, body;

      if (action === 'graduate') {
        url = `${baseUrl}/graduate`;
        body = { enrollment_id: parseInt(enrollmentId), graduation_date: date };
      } else if (action === 'drop') {
        url = `${baseUrl}/drop`;
        body = { enrollment_id: parseInt(enrollmentId), drop_date: date };
      } else {
        return;
      }

      fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json'
        },
        body: JSON.stringify(body)
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', 'Success!', data.message).then(() => {
              bsConfirmModal.hide();
              dt_Enrollments.ajax.reload();
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
  function resetEnrollForm() {
    if (enrollStudentForm) enrollStudentForm.reset();
    enrollAction.value = 'enroll';
    enrollEnrollmentId.value = '';
    enrollModalTitle.textContent = 'Enroll Student';
    studentSelectGroup.style.display = 'block';
    $(enrollStudentId).val('').trigger('change');
    $(enrollAcademicYear).val('').trigger('change');
    $(enrollClassroom).val('').trigger('change');
    enrollDate.value = new Date().toISOString().split('T')[0];
  }

  function openTransferModal(enrollmentId, studentId, studentName) {
    enrollAction.value = 'transfer';
    enrollEnrollmentId.value = enrollmentId;
    enrollModalTitle.textContent = `Transfer: ${studentName}`;
    studentSelectGroup.style.display = 'none';

    // Set student ID hidden
    $(enrollStudentId).val(studentId).trigger('change');

    bsEnrollModal.show();
  }

  function openConfirmModal(action, enrollmentId, studentName) {
    confirmEnrollmentId.value = enrollmentId;
    confirmAction.value = action;

    if (action === 'graduate') {
      actionConfirmTitle.textContent = 'Graduate Student';
      actionConfirmMessage.textContent = `Are you sure you want to graduate "${studentName}"?`;
      btnConfirmAction.className = 'btn btn-info';
    } else if (action === 'drop') {
      actionConfirmTitle.textContent = 'Drop Student';
      actionConfirmMessage.textContent = `Are you sure you want to drop "${studentName}"? This action indicates the student has left the school.`;
      btnConfirmAction.className = 'btn btn-danger';
    }

    confirmDate.value = new Date().toISOString().split('T')[0];
    bsConfirmModal.show();
  }

  function deleteEnrollment(id, studentName) {
    Swal.fire({
      title: 'Delete Enrollment?',
      text: `Are you sure you want to delete the enrollment record for "${studentName}"?`,
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
                dt_Enrollments.ajax.reload();
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
});
