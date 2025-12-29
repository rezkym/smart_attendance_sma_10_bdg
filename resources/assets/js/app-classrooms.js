/**
 * App Classrooms List
 */

'use strict';

// Datatable (jquery)
$(function () {
  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config?.colors_dark?.borderColor || '#3b3f5c';
    bodyBg = config?.colors_dark?.bodyBg || '#25293c';
    headingColor = config?.colors_dark?.headingColor || '#d5d6dc';
  } else {
    borderColor = config?.colors?.borderColor || '#e6e6e8';
    bodyBg = config?.colors?.bodyBg || '#fff';
    headingColor = config?.colors?.headingColor || '#444050';
  }

  var dt_classrooms_table = $('.datatables-classrooms'),
    bsOffcanvasAddClassroom = $('#offcanvasAddClassroom');

  // Initialize Select2 if it exists
  const select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      var placeholder = $this.attr('id') === 'add-classroom-homeroom-teacher' 
        ? 'Select Homeroom Teacher' 
        : 'Select Academic Year';
      $this.wrap('<div class="position-relative"></div>').select2({
        placeholder: placeholder,
        allowClear: $this.data('allow-clear') || false,
        dropdownParent: $this.parent()
      });
    });
  }

  // Function to load homeroom teachers
  function loadHomeroomTeachers(selectedId = null) {
    $.get(`${baseUrl}admin/classrooms/available-homeroom-teachers`, function (response) {
      if (response.success) {
        var select = $('#add-classroom-homeroom-teacher');
        select.find('option:not(:first)').remove();
        response.data.forEach(function (teacher) {
          var selected = selectedId && selectedId == teacher.id ? 'selected' : '';
          select.append(`<option value="${teacher.id}" ${selected}>${teacher.name}${teacher.nip ? ' (' + teacher.nip + ')' : ''}</option>`);
        });
        if (selectedId) {
          select.val(selectedId).trigger('change');
        }
      }
    });
  }

  // Users List Datatable
  if (dt_classrooms_table.length) {
    var dt_classroom = dt_classrooms_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'admin/classrooms/list'
      },
      columns: [
        { data: 'id' },
        { data: 'id' },
        { data: 'name' },
        { data: 'grade_level' },
        { data: 'academic_year_name' },
        { data: 'homeroom_teacher_name' },
        { data: 'capacity' },
        { data: 'status' },
        { data: 'actions' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // For Checkboxes
          targets: 1,
          orderable: false,
          checkboxes: {
            selectAllRender: '<input type="checkbox" class="form-check-input">'
          },
          render: function () {
            return '<input type="checkbox" class="dt-checkboxes form-check-input" >';
          },
          searchable: false
        },
        {
          // Name
          targets: 2,
          render: function (data, type, full, meta) {
            var $name = full['name'];
            return '<span class="fw-medium text-heading">' + $name + '</span>';
          }
        },
        {
          // Grade Level
          targets: 3,
          render: function (data, type, full, meta) {
            return '<span class="text-truncate d-flex align-items-center">' + full['grade_level'] + '</span>';
          }
        },
        {
          // Academic Year
          targets: 4,
          render: function (data, type, full, meta) {
            return '<span class="text-truncate">' + full['academic_year_name'] + '</span>';
          }
        },
        {
          // Homeroom Teacher
          targets: 5,
          render: function (data, type, full, meta) {
            var teacher = full['homeroom_teacher_name'];
            return teacher && teacher !== '-' 
              ? '<span class="badge bg-label-info">' + teacher + '</span>' 
              : '<span class="text-muted">-</span>';
          }
        },
        {
          // Capacity
          targets: 6,
          render: function (data, type, full, meta) {
            return  full['capacity'] ? full['capacity'] : '-';
          }
        },
        {
          // Status
          targets: 7,
          render: function (data, type, full, meta) {
            var $status = full['status'];
            return (
              '<span class="badge rounded-pill ' +
              ($status ? 'bg-label-success' : 'bg-label-secondary') +
              '">' +
              ($status ? 'Active' : 'Inactive') +
              '</span>'
            );
          }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-flex align-items-center">' +
              '<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-record" data-id="' +
              full['id'] +
              '"><i class="icon-base ri ri-delete-bin-7-line icon-22px"></i></a>' +
              '<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary waves-effect waves-light rounded-pill edit-record" data-id="' +
              full['id'] +
              '" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClassroom"><i class="icon-base ri ri-edit-box-line icon-22px"></i></a>' +
              '</div>'
            );
          }
        }
      ],
      order: [[2, 'desc']],
      dom:
        '<"row mx-1"' +
        '<"col-md-2 d-flex align-items-center justify-content-md-start justify-content-center ps-4"<"dt-action-buttons mt-4 mt-md-0"B>>' +
        '<"col-md-10"<"d-flex align-items-center justify-content-md-end justify-content-center"<"me-4"f><"add-new">>>' +
        '>t' +
        '<"row mx-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        sLengthMenu: 'Show _MENU_',
        search: '',
        searchPlaceholder: 'Search Classroom'
      },
      // Buttons with Dropdown
      buttons: [],
      // For responsive popup
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                    col.rowIndex +
                    '" data-dt-column="' +
                    col.columnIndex +
                    '">' +
                    '<td>' +
                    col.title +
                    ':' +
                    '</td> ' +
                    '<td>' +
                    col.data +
                    '</td>' +
                    '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      }
    });
  }

  // Delete Record
  $(document).on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    var dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}admin/classrooms/${recordId}`,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            if (response.success) {
              dt_classroom.draw();
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: response.message,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
            } else {
               Swal.fire({
                title: 'Error!',
                text: response.message,
                icon: 'error',
                customClass: {
                  confirmButton: 'btn btn-primary'
                }
              });
            }
          },
          error: function (error) {
            Swal.fire({
              title: 'Error!',
              text: error.responseJSON?.message || 'Something went wrong!',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-primary'
              }
            });
          }
        });
      }
    });
  });

  // Edit Record
  $(document).on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    var dtrModal = $('.dtr-bs-modal.show');

    // hide responsive modal in small screen
    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    // Changing the title
    $('#offcanvasAddClassroomLabel').html('Edit Classroom');

    // Fetch data
    $.get(`${baseUrl}admin/classrooms/${recordId}`, function (response) {
      if (response.success) {
        var data = response.data;
        $('#classroom_id').val(data.id);
        $('#add-classroom-name').val(data.name);
        $('#add-classroom-grade').val(data.grade_level);
        $('#add-classroom-academic-year').val(data.academic_year_id).trigger('change');
        $('#add-classroom-capacity').val(data.capacity);
        $('#add-classroom-is-active').prop('checked', data.is_active);
        $('#add-classroom-description').val(data.description);
        
        // Load homeroom teachers with selected value
        loadHomeroomTeachers(data.homeroom_teacher_id);
      }
    });
  });

  // Form Validation
  const addNewClassroomForm = document.getElementById('addNewClassroomForm');

  // Add New Classroom Form Validation
  const fv = FormValidation.formValidation(addNewClassroomForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter classroom name'
          }
        }
      },
      grade_level: {
        validators: {
          notEmpty: {
            message: 'Please select grade level'
          }
        }
      },
      academic_year_id: {
        validators: {
          notEmpty: {
            message: 'Please select academic year'
          }
        }
      },
      capacity: {
        validators: {
           greaterThan: {
              min: 1,
              message: 'Capacity must be greater than 0'
           },
           lessThan: {
              max: 100,
              message: 'Capacity must be less than or equal to 100'
           }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.form-floating-outline';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    // Send the form data to backend
    var form = $('#addNewClassroomForm');
    var recordId = $('#classroom_id').val();
    var url = recordId ? `${baseUrl}admin/classrooms/${recordId}` : `${baseUrl}admin/classrooms`;
    var method = recordId ? 'PUT' : 'POST';

    // Build data object manually to handle is_active properly
    var formData = {
      name: $('#add-classroom-name').val(),
      grade_level: $('#add-classroom-grade').val(),
      academic_year_id: $('#add-classroom-academic-year').val(),
      homeroom_teacher_id: $('#add-classroom-homeroom-teacher').val() || null,
      capacity: $('#add-classroom-capacity').val() || null,
      description: $('#add-classroom-description').val() || null,
      is_active: $('#add-classroom-is-active').is(':checked') ? 1 : 0
    };

    $.ajax({
      data: formData,
      url: url,
      type: method,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        if (response.success) {
            dt_classroom.draw();
            bsOffcanvasAddClassroom.offcanvas('hide');
            
            // Sweet alert
            Swal.fire({
                icon: 'success',
                title: recordId ? 'Updated!' : 'Created!',
                text: response.message,
                customClass: {
                    confirmButton: 'btn btn-success'
                }
            });
            
            // Reset form
            if (!recordId) {
                addNewClassroomForm.reset();
                $('.select2').val('').trigger('change');
            }
        } else {
             Swal.fire({
                title: 'Error!',
                text: response.message,
                icon: 'error',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        }
      },
      error: function (error) {
        Swal.fire({
          title: 'Error!',
          text: error.responseJSON?.message || 'Something went wrong!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-primary'
          }
        });
      }
    });
  });
  
  // Clean up form when offcanvas is hidden
  bsOffcanvasAddClassroom.on('hidden.bs.offcanvas', function () {
      $('#classroom_id').val('');
      $('#offcanvasAddClassroomLabel').html('Add Classroom');
      addNewClassroomForm.reset();
      $('#add-classroom-academic-year').val('').trigger('change');
      $('#add-classroom-homeroom-teacher').val('').trigger('change');
      
      // Reset validation
      fv.resetForm(true);
  });

  // Load homeroom teachers when offcanvas is opened for new classroom
  bsOffcanvasAddClassroom.on('show.bs.offcanvas', function (e) {
    var trigger = $(e.relatedTarget);
    // Only load if it's not an edit (no data-id)
    if (!trigger.hasClass('edit-record')) {
      loadHomeroomTeachers();
    }
  });
});
