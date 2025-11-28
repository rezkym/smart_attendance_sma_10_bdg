import axios from 'axios';
import Swal from 'sweetalert2';

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const buildUrl = (template, id) => template.replace('__ID__', id);

document.addEventListener('DOMContentLoaded', () => {
  axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  const tableElement = document.getElementById('users-table');

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
    destroy: tableElement.dataset.destroyUrl
  };

  const errorsBox = document.getElementById('userFormErrors');
  const form = document.getElementById('userForm');
  const modalElement = document.getElementById('userModal');
  const modalInstance = modalElement && window.bootstrap ? new window.bootstrap.Modal(modalElement) : null;
  const modalTitle = document.getElementById('userModalLabel');
  const submitButton = document.getElementById('userFormSubmit');
  const rolesSelect = document.getElementById('rolesInput');
  const passwordInput = document.getElementById('passwordInput');

  let editingId = null;

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

  const resetForm = () => {
    form?.reset();
    clearErrors();
    editingId = null;
    if (rolesSelect) {
      Array.from(rolesSelect.options).forEach(option => {
        option.selected = false;
      });
    }
    if (passwordInput) {
      passwordInput.value = '';
    }
  };

  const setModalForCreate = () => {
    resetForm();
    if (modalTitle) modalTitle.textContent = 'Tambah User';
    if (submitButton) submitButton.textContent = 'Simpan';
  };

  const setModalForEdit = () => {
    if (modalTitle) modalTitle.textContent = 'Edit User';
    if (submitButton) submitButton.textContent = 'Update';
  };

  const fillForm = user => {
    if (!form || !user) {
      return;
    }
    form.querySelector('#nameInput').value = user.name || '';
    form.querySelector('#emailInput').value = user.email || '';
    if (rolesSelect && Array.isArray(user.roles)) {
      const roleNames = user.roles.map(role => (typeof role === 'string' ? role : role.name));
      Array.from(rolesSelect.options).forEach(option => {
        option.selected = roleNames.includes(option.value);
      });
    }
    if (passwordInput) {
      passwordInput.value = '';
    }
  };

  const usersTable = new DataTable(tableElement, {
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: urls.ajax,
    columns: [
      { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
      { data: 'name', name: 'name' },
      { data: 'email', name: 'email' },
      { data: 'roles', name: 'roles', orderable: false, searchable: false },
      { data: 'email_verified_at', name: 'email_verified_at' },
      { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ],
    order: [[1, 'asc']]
  });

  document.getElementById('createUserBtn')?.addEventListener('click', () => {
    setModalForCreate();
  });

  const fetchUser = async id => {
    const { data } = await axios.get(buildUrl(urls.show, id), {
      headers: { Accept: 'application/json' }
    });
    return data?.data;
  };

  const saveUser = async payload => {
    if (editingId) {
      return axios.put(buildUrl(urls.update, editingId), payload, { headers: { Accept: 'application/json' } });
    }

    return axios.post(urls.store, payload, { headers: { Accept: 'application/json' } });
  };

  const deleteUser = async id => {
    return axios.delete(buildUrl(urls.destroy, id), { headers: { Accept: 'application/json' } });
  };

  tableElement.addEventListener('click', async event => {
    const editButton = event.target.closest('.edit-user');
    const deleteButton = event.target.closest('.delete-user');

    if (editButton) {
      const userId = editButton.dataset.id;
      if (!userId) return;
      try {
        const user = await fetchUser(userId);
        editingId = userId;
        setModalForEdit();
        fillForm(user);
        modalInstance?.show();
      } catch (error) {
        showAlert(error.response?.data?.message || 'Gagal memuat data user.', 'danger');
      }
      return;
    }

    if (deleteButton) {
      const userId = deleteButton.dataset.id;
      if (!userId) return;

      const confirmation = await Swal.fire({
        title: 'Hapus user?',
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
      });

      if (!confirmation.isConfirmed) return;

      try {
        await deleteUser(userId);
        usersTable.ajax.reload(null, false);
        showAlert('User berhasil dihapus.', 'success');
      } catch (error) {
        showAlert(error.response?.data?.message || 'Gagal menghapus user.', 'danger');
      }
    }
  });

  form?.addEventListener('submit', async event => {
    event.preventDefault();
    clearErrors();

    const payload = {
      name: form.querySelector('#nameInput')?.value,
      email: form.querySelector('#emailInput')?.value,
      password: passwordInput?.value,
      roles: Array.from(rolesSelect?.selectedOptions || []).map(option => option.value)
    };

    if (!payload.password) {
      delete payload.password;
    }

    try {
      await saveUser(payload);
      usersTable.ajax.reload(null, false);
      modalInstance?.hide();
      setModalForCreate();
      showAlert(`User berhasil ${editingId ? 'diupdate' : 'dibuat'}.`, 'success');
    } catch (error) {
      const errors = error.response?.data?.errors;
      if (errors) {
        showErrors(errors);
        return;
      }
      showAlert(error.response?.data?.message || 'Terjadi kesalahan.', 'danger');
    }
  });
});
