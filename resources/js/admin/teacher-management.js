import axios from 'axios';
import Swal from 'sweetalert2';

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const buildUrl = (template, id) => template.replace('__ID__', id);

document.addEventListener('DOMContentLoaded', () => {
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  const tableElement = document.getElementById('teachers-table');

  if (!tableElement || typeof DataTable === 'undefined') {
    return;
  }

  const csrfToken = getCsrfToken();
  if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
  }

  const urls = {
    ajax: tableElement.dataset.ajaxUrl,
    store: tableElement.dataset.storeUrl,
    show: tableElement.dataset.showUrl,
    update: tableElement.dataset.updateUrl,
    destroy: tableElement.dataset.destroyUrl,
    subjects: tableElement.dataset.subjectsUrl,
    classroom: tableElement.dataset.classroomUrl
  };

  // Teacher Modal Elements
  const teacherErrorsBox = document.getElementById('teacherFormErrors');
  const teacherForm = document.getElementById('teacherForm');
  const teacherModalElement = document.getElementById('teacherModal');
  const teacherModalInstance = teacherModalElement && window.bootstrap ? new window.bootstrap.Modal(teacherModalElement) : null;
  const teacherModalTitle = document.getElementById('teacherModalLabel');
  const teacherSubmitButton = document.getElementById('teacherFormSubmit');
  const userSelect = document.getElementById('userSelect');
  const userSelectWrapper = document.getElementById('userSelectWrapper');

  // Subjects Modal Elements
  const subjectsErrorsBox = document.getElementById('subjectsFormErrors');
  const subjectsForm = document.getElementById('subjectsForm');
  const subjectsModalElement = document.getElementById('subjectsModal');
  const subjectsModalInstance = subjectsModalElement && window.bootstrap ? new window.bootstrap.Modal(subjectsModalElement) : null;
  const subjectsInput = document.getElementById('subjectsInput');
  const subjectsTeacherIdInput = document.getElementById('subjectsTeacherIdInput');

  // Homeroom Modal Elements
  const homeroomErrorsBox = document.getElementById('homeroomFormErrors');
  const homeroomForm = document.getElementById('homeroomForm');
  const homeroomModalElement = document.getElementById('homeroomModal');
  const homeroomModalInstance = homeroomModalElement && window.bootstrap ? new window.bootstrap.Modal(homeroomModalElement) : null;
  const homeroomClassroomInput = document.getElementById('homeroomClassroomInput');
  const homeroomTeacherIdInput = document.getElementById('homeroomTeacherIdInput');

  // Filters
  const filterSubjectSelect = document.getElementById('filterSubject');
  const filterClassroomSelect = document.getElementById('filterClassroom');
  const applyTeacherFiltersButton = document.getElementById('applyTeacherFilters');
  const resetTeacherFiltersButton = document.getElementById('resetTeacherFilters');

  let editingId = null;
  let select2Instance = null;

  const showAlert = (message, variant = 'success') => {
    Swal.fire({
      icon: variant === 'danger' ? 'error' : variant,
      text: message,
      confirmButtonText: 'OK'
    });
  };

  const showErrors = (errorsBox, errors) => {
    if (!errorsBox) return;
    errorsBox.classList.remove('d-none');
    errorsBox.innerHTML = '';

    const messages = Array.isArray(errors)
      ? errors
      : Object.values(errors || {}).flat();

    messages.forEach(message => {
      const item = document.createElement('div');
      item.textContent = message;
      errorsBox.appendChild(item);
    });
  };

  const clearErrors = (errorsBox) => {
    if (!errorsBox) return;
    errorsBox.classList.add('d-none');
    errorsBox.innerHTML = '';
  };

  const resetHomeroomForm = () => {
    homeroomForm?.reset();
    clearErrors(homeroomErrorsBox);
    if (homeroomTeacherIdInput) homeroomTeacherIdInput.value = '';
  };

  const initSelect2 = () => {
    if (!userSelect || typeof window.$ === 'undefined') return;

    if (select2Instance) {
      window.$(userSelect).select2('destroy');
    }

    select2Instance = window.$(userSelect).select2({
      dropdownParent: window.$(teacherModalElement),
      placeholder: 'Ketik untuk mencari user...',
      allowClear: true,
      ajax: {
        url: '/admin/teachers/available-users',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            page: params.page || 1
          };
        },
        processResults: function (data) {
          return {
            results: data.results,
            pagination: data.pagination
          };
        },
        cache: true
      },
      minimumInputLength: 0
    });
  };

  const resetTeacherForm = () => {
    teacherForm?.reset();
    clearErrors(teacherErrorsBox);
    editingId = null;

    if (userSelectWrapper) {
      userSelectWrapper.style.display = 'block';
    }
    if (userSelect && select2Instance) {
      window.$(userSelect).val(null).trigger('change');
    }
  };

  const setModalForCreate = () => {
    resetTeacherForm();
    initSelect2();
    if (teacherModalTitle) teacherModalTitle.textContent = 'Tambah Guru';
    if (teacherSubmitButton) teacherSubmitButton.textContent = 'Simpan';
    if (userSelectWrapper) userSelectWrapper.style.display = 'block';
  };

  const setModalForEdit = () => {
    if (teacherModalTitle) teacherModalTitle.textContent = 'Edit Guru';
    if (teacherSubmitButton) teacherSubmitButton.textContent = 'Update';
    if (userSelectWrapper) userSelectWrapper.style.display = 'none';
  };

  const fillTeacherForm = teacher => {
    if (!teacherForm || !teacher) {
      return;
    }

    teacherForm.querySelector('#teacherNumberInput').value = teacher.teacher_number || '';
    teacherForm.querySelector('#specializationInput').value = teacher.specialization || '';
    teacherForm.querySelector('#dateOfBirthInput').value = teacher.date_of_birth || '';
    teacherForm.querySelector('#genderInput').value = teacher.gender || '';
    teacherForm.querySelector('#addressInput').value = teacher.address || '';
    teacherForm.querySelector('#phoneNumberInput').value = teacher.phone_number || '';
  };

  const teachersTable = new DataTable(tableElement, {
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      url: urls.ajax,
      data: params => {
        params.subject_id = filterSubjectSelect?.value || '';
        params.classroom_id = filterClassroomSelect?.value || '';
      }
    },
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'user_name', name: 'user.name' },
      { data: 'user_email', name: 'user.email' },
      { data: 'teacher_number', name: 'teacher_number' },
      { data: 'specialization', name: 'specialization' },
      { data: 'subjects', name: 'subjects', orderable: false, searchable: false },
      { data: 'homeroom_classrooms', name: 'homeroom_classrooms', orderable: false, searchable: false },
      { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ],
    order: [[1, 'asc']]
  });

  applyTeacherFiltersButton?.addEventListener('click', () => {
    teachersTable.ajax.reload();
  });

  resetTeacherFiltersButton?.addEventListener('click', () => {
    if (filterSubjectSelect) filterSubjectSelect.value = '';
    if (filterClassroomSelect) filterClassroomSelect.value = '';
    teachersTable.ajax.reload();
  });

  document.getElementById('createTeacherBtn')?.addEventListener('click', () => {
    setModalForCreate();
  });

  const fetchTeacher = async id => {
    const { data } = await axios.get(buildUrl(urls.show, id), {
      headers: { Accept: 'application/json' }
    });
    return data?.data;
  };

  const saveTeacher = async payload => {
    if (editingId) {
      return axios.put(buildUrl(urls.update, editingId), payload, { headers: { Accept: 'application/json' } });
    }

    return axios.post(urls.store, payload, { headers: { Accept: 'application/json' } });
  };

  const deleteTeacher = async id => {
    return axios.delete(buildUrl(urls.destroy, id), { headers: { Accept: 'application/json' } });
  };

  const updateSubjects = async (teacherId, subjectIds) => {
    return axios.put(buildUrl(urls.subjects, teacherId), { subject_ids: subjectIds }, { headers: { Accept: 'application/json' } });
  };

  const updateHomeroom = async (teacherId, classroomId) => {
    return axios.put(buildUrl(urls.classroom, teacherId), { classroom_id: classroomId }, { headers: { Accept: 'application/json' } });
  };

  tableElement.addEventListener('click', async event => {
    const editButton = event.target.closest('.edit-teacher');
    const deleteButton = event.target.closest('.delete-teacher');
    const assignSubjectsButton = event.target.closest('.assign-subjects-teacher');
    const assignHomeroomButton = event.target.closest('.assign-homeroom-teacher');

    if (editButton) {
      const teacherId = editButton.dataset.id;
      if (!teacherId) return;
      try {
        const teacher = await fetchTeacher(teacherId);
        editingId = teacherId;
        setModalForEdit();
        fillTeacherForm(teacher);
        teacherModalInstance?.show();
      } catch (error) {
        showAlert(error.response?.data?.message || 'Gagal memuat data guru.', 'danger');
      }
      return;
    }

    if (deleteButton) {
      const teacherId = deleteButton.dataset.id;
      if (!teacherId) return;

      const confirmation = await Swal.fire({
        title: 'Hapus guru?',
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
      });

      if (!confirmation.isConfirmed) return;

      try {
        await deleteTeacher(teacherId);
        teachersTable.ajax.reload(null, false);
        showAlert('Guru berhasil dihapus.', 'success');
      } catch (error) {
        showAlert(error.response?.data?.message || 'Gagal menghapus guru.', 'danger');
      }
      return;
    }

    if (assignSubjectsButton) {
      const teacherId = assignSubjectsButton.dataset.id;
      if (!teacherId) return;

      try {
        const teacher = await fetchTeacher(teacherId);

        if (subjectsTeacherIdInput) {
          subjectsTeacherIdInput.value = teacherId;
        }

        // Clear all selections first
        if (subjectsInput) {
          Array.from(subjectsInput.options).forEach(option => {
            option.selected = false;
          });

          // Select current subjects
          if (teacher.subjects && Array.isArray(teacher.subjects)) {
            teacher.subjects.forEach(subject => {
              const option = subjectsInput.querySelector(`option[value="${subject.id}"]`);
              if (option) {
                option.selected = true;
              }
            });
          }
        }

        clearErrors(subjectsErrorsBox);
        subjectsModalInstance?.show();
      } catch (error) {
        showAlert(error.response?.data?.message || 'Gagal memuat data guru.', 'danger');
      }
      return;
    }

    if (assignHomeroomButton) {
      const teacherId = assignHomeroomButton.dataset.id;
      if (!teacherId) return;

      try {
        const teacher = await fetchTeacher(teacherId);

        resetHomeroomForm();

        if (homeroomTeacherIdInput) {
          homeroomTeacherIdInput.value = teacherId;
        }

        if (homeroomClassroomInput) {
          const homeroomId = Array.isArray(teacher?.homeroom_classrooms) && teacher.homeroom_classrooms.length
            ? teacher.homeroom_classrooms[0].id
            : '';
          homeroomClassroomInput.value = homeroomId || '';
        }

        clearErrors(homeroomErrorsBox);
        homeroomModalInstance?.show();
      } catch (error) {
        showAlert(error.response?.data?.message || 'Gagal memuat data guru.', 'danger');
      }
      return;
    }
  });

  teacherForm?.addEventListener('submit', async event => {
    event.preventDefault();
    clearErrors(teacherErrorsBox);
    const isEdit = Boolean(editingId);

    const payload = {
      teacher_number: teacherForm.querySelector('#teacherNumberInput')?.value,
      specialization: teacherForm.querySelector('#specializationInput')?.value,
      date_of_birth: teacherForm.querySelector('#dateOfBirthInput')?.value,
      gender: teacherForm.querySelector('#genderInput')?.value,
      address: teacherForm.querySelector('#addressInput')?.value,
      phone_number: teacherForm.querySelector('#phoneNumberInput')?.value
    };

    // Add user_id only for create mode
    if (!editingId && userSelect) {
      payload.user_id = userSelect.value;
    }

    // Remove empty/null values
    Object.keys(payload).forEach(key => {
      if (!payload[key]) {
        delete payload[key];
      }
    });

    try {
      await saveTeacher(payload);
      teachersTable.ajax.reload(null, false);
      teacherModalInstance?.hide();
      setModalForCreate();
      showAlert(`Guru berhasil ${isEdit ? 'diupdate' : 'dibuat'}.`, 'success');
    } catch (error) {
      const errors = error.response?.data?.errors;
      if (errors) {
        showErrors(teacherErrorsBox, errors);
        return;
      }
      showAlert(error.response?.data?.message || 'Terjadi kesalahan.', 'danger');
    }
  });

  subjectsForm?.addEventListener('submit', async event => {
    event.preventDefault();
    clearErrors(subjectsErrorsBox);

    const teacherId = subjectsTeacherIdInput?.value;
    if (!teacherId) {
      showAlert('Teacher ID tidak ditemukan.', 'danger');
      return;
    }

    const selectedOptions = Array.from(subjectsInput.selectedOptions || []);
    const subjectIds = selectedOptions.map(option => parseInt(option.value));

    try {
      await updateSubjects(teacherId, subjectIds);
      teachersTable.ajax.reload(null, false);
      subjectsModalInstance?.hide();
      showAlert('Mata pelajaran berhasil diupdate.', 'success');
    } catch (error) {
      const errors = error.response?.data?.errors;
      if (errors) {
        showErrors(subjectsErrorsBox, errors);
        return;
      }
      showAlert(error.response?.data?.message || 'Terjadi kesalahan.', 'danger');
    }
  });

  homeroomForm?.addEventListener('submit', async event => {
    event.preventDefault();
    clearErrors(homeroomErrorsBox);

    const teacherId = homeroomTeacherIdInput?.value;
    if (!teacherId) {
      showAlert('Teacher ID tidak ditemukan.', 'danger');
      return;
    }

    const classroomId = homeroomClassroomInput?.value || null;

    try {
      await updateHomeroom(teacherId, classroomId || null);
      teachersTable.ajax.reload(null, false);
      homeroomModalInstance?.hide();
      resetHomeroomForm();
      showAlert('Penugasan wali kelas berhasil diperbarui.', 'success');
    } catch (error) {
      const errors = error.response?.data?.errors;
      if (errors) {
        showErrors(homeroomErrorsBox, errors);
        return;
      }
      showAlert(error.response?.data?.message || 'Terjadi kesalahan.', 'danger');
    }
  });

  // Initialize Select2 on modal show
  if (teacherModalElement) {
    teacherModalElement.addEventListener('shown.bs.modal', () => {
      if (!editingId) {
        initSelect2();
      }
    });
  }
});
