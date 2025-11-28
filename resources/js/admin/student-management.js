import axios from 'axios';
import Swal from 'sweetalert2';

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const buildUrl = (template, id) => template.replace('__ID__', id);

document.addEventListener('DOMContentLoaded', () => {
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  const tableElement = document.getElementById('students-table');

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
    rfid: tableElement.dataset.rfidUrl,
    classroom: tableElement.dataset.classroomUrl
  };

  const errorsBox = document.getElementById('studentFormErrors');
  const form = document.getElementById('studentForm');
  const modalElement = document.getElementById('studentModal');
  const modalInstance = modalElement && window.bootstrap ? new window.bootstrap.Modal(modalElement) : null;
  const modalTitle = document.getElementById('studentModalLabel');
  const submitButton = document.getElementById('studentFormSubmit');
  const userSelect = document.getElementById('userSelect');
  const userSelectWrapper = document.getElementById('userSelectWrapper');

  let editingId = null;
  let select2Instance = null;
  let currentPage = 0;

  const showAlert = (message, variant = 'success') => {
    Swal.fire({
      icon: variant === 'danger' ? 'error' : variant,
      text: message,
      confirmButtonText: 'OK'
    });
  };

  const showErrors = errors => {
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

  const clearErrors = () => {
    if (!errorsBox) return;
    errorsBox.classList.add('d-none');
    errorsBox.innerHTML = '';
  };

  const initSelect2 = () => {
    if (!userSelect || typeof window.$ === 'undefined') return;

    // Destroy existing instance if any
    if (select2Instance) {
      window.$(userSelect).select2('destroy');
    }

    // Initialize Select2
    select2Instance = window.$(userSelect).select2({
      dropdownParent: window.$(modalElement),
      placeholder: 'Ketik untuk mencari user...',
      allowClear: true,
      ajax: {
        url: '/admin/students/available-users',
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

  const resetForm = () => {
    form?.reset();
    clearErrors();
    editingId = null;

    // Reset & show user select
    if (userSelectWrapper) {
      userSelectWrapper.style.display = 'block';
    }
    if (userSelect && select2Instance) {
      window.$(userSelect).val(null).trigger('change');
    }
  };

  const setModalForCreate = () => {
    resetForm();
    initSelect2();
    if (modalTitle) modalTitle.textContent = 'Tambah Siswa';
    if (submitButton) submitButton.textContent = 'Simpan';
    // Show user select untuk create mode
    if (userSelectWrapper) userSelectWrapper.style.display = 'block';
  };

  const setModalForEdit = () => {
    if (modalTitle) modalTitle.textContent = 'Edit Siswa';
    if (submitButton) submitButton.textContent = 'Update';
    // Hide user select untuk edit mode (user tidak bisa diubah)
    if (userSelectWrapper) userSelectWrapper.style.display = 'none';
  };

  const fillForm = student => {
    if (!form || !student) {
      return;
    }

    // Fill student-specific fields only (no user fields)
    form.querySelector('#studentNumberInput').value = student.student_number || '';
    form.querySelector('#rfidCardNumberInput').value = student.rfid_card_number || '';
    form.querySelector('#dateOfBirthInput').value = student.date_of_birth || '';
    form.querySelector('#genderInput').value = student.gender || '';
    form.querySelector('#addressInput').value = student.address || '';
    form.querySelector('#phoneNumberInput').value = student.phone_number || '';
    form.querySelector('#parentNameInput').value = student.parent_name || '';
    form.querySelector('#parentPhoneInput').value = student.parent_phone || '';

    const classroomSelect = form.querySelector('#classroomInput');
    if (classroomSelect && student.classroom) {
      classroomSelect.value = student.classroom.id || '';
    } else if (classroomSelect) {
      classroomSelect.value = '';
    }
  };

  const studentsTable = new DataTable(tableElement, {
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: urls.ajax,
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'user_name', name: 'user.name' },
      { data: 'user_email', name: 'user.email' },
      { data: 'student_number', name: 'student_number' },
      { data: 'rfid_card_number', name: 'rfid_card_number' },
      { data: 'classroom_name', name: 'classroom.name', orderable: false },
      { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ],
    order: [[1, 'asc']]
  });

  document.getElementById('createStudentBtn')?.addEventListener('click', () => {
    setModalForCreate();
  });

  const fetchStudent = async id => {
    const { data } = await axios.get(buildUrl(urls.show, id), {
      headers: { Accept: 'application/json' }
    });
    return data?.data;
  };

  const saveStudent = async payload => {
    if (editingId) {
      return axios.put(buildUrl(urls.update, editingId), payload, { headers: { Accept: 'application/json' } });
    }

    return axios.post(urls.store, payload, { headers: { Accept: 'application/json' } });
  };

  const deleteStudent = async id => {
    return axios.delete(buildUrl(urls.destroy, id), { headers: { Accept: 'application/json' } });
  };

  tableElement.addEventListener('click', async event => {
    const editButton = event.target.closest('.edit-student');
    const deleteButton = event.target.closest('.delete-student');

    if (editButton) {
      const studentId = editButton.dataset.id;
      if (!studentId) return;
      try {
        const student = await fetchStudent(studentId);
        editingId = studentId;
        setModalForEdit();
        fillForm(student);
        modalInstance?.show();
      } catch (error) {
        showAlert(error.response?.data?.message || 'Gagal memuat data siswa.', 'danger');
      }
      return;
    }

    if (deleteButton) {
      const studentId = deleteButton.dataset.id;
      if (!studentId) return;

      const confirmation = await Swal.fire({
        title: 'Hapus siswa?',
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
      });

      if (!confirmation.isConfirmed) return;

      try {
        await deleteStudent(studentId);
        studentsTable.ajax.reload(null, false);
        showAlert('Siswa berhasil dihapus.', 'success');
      } catch (error) {
        showAlert(error.response?.data?.message || 'Gagal menghapus siswa.', 'danger');
      }
    }
  });

  form?.addEventListener('submit', async event => {
    event.preventDefault();
    clearErrors();

    const payload = {
      student_number: form.querySelector('#studentNumberInput')?.value,
      rfid_card_number: form.querySelector('#rfidCardNumberInput')?.value,
      date_of_birth: form.querySelector('#dateOfBirthInput')?.value,
      gender: form.querySelector('#genderInput')?.value,
      address: form.querySelector('#addressInput')?.value,
      phone_number: form.querySelector('#phoneNumberInput')?.value,
      parent_name: form.querySelector('#parentNameInput')?.value,
      parent_phone: form.querySelector('#parentPhoneInput')?.value,
      classroom_id: form.querySelector('#classroomInput')?.value
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
      await saveStudent(payload);
      studentsTable.ajax.reload(null, false);
      modalInstance?.hide();
      setModalForCreate();
      showAlert(`Siswa berhasil ${editingId ? 'diupdate' : 'dibuat'}.`, 'success');
    } catch (error) {
      const errors = error.response?.data?.errors;
      if (errors) {
        showErrors(errors);
        return;
      }
      showAlert(error.response?.data?.message || 'Terjadi kesalahan.', 'danger');
    }
  });

  // Initialize Select2 on page load
  document.addEventListener('DOMContentLoaded', () => {
    initSelect2();
  });
});
