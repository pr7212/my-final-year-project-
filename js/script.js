'use strict';

/* =====================================
   ELEMENTS
===================================== */
const csrfToken =
  document.querySelector('input[name="csrf_token"]')?.value || '';
const userRole = document.body.dataset.role || 'resident';

const tableBody = document.querySelector('#requests-table tbody');
const createForm = document.getElementById('create-form');
const areaSelect = document.getElementById('area-select');
const feedbackDiv = document.getElementById('feedback');
const editModal = document.getElementById('edit-modal');

/* =====================================
   FEEDBACK
===================================== */
function showFeedback(message, success = false) {
  if (!feedbackDiv) return;

  feedbackDiv.textContent = message;
  feedbackDiv.style.display = 'flex';
  feedbackDiv.className = success ? 'alert alert-success' : 'alert alert-error';

  setTimeout(() => {
    feedbackDiv.style.display = 'none';
  }, 4000);
}

/* =====================================
   VALIDATION
===================================== */
function validateAreaId(areaId) {
  if (!areaId || parseInt(areaId) <= 0) {
    return 'Please select an area.';
  }

  return null;
}

/* =====================================
   AJAX HELPER
===================================== */
async function ajaxRequest(url, options = {}) {
  const method = options.method || 'POST';

  const config = {
    method,
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  };

  if (options.data) {
    config.headers['Content-Type'] = 'application/x-www-form-urlencoded';
    config.body = new URLSearchParams(options.data).toString();
  }

  try {
    const response = await fetch(url, config);
    return await response.json();
  } catch (error) {
    return {
      success: false,
      message: 'Network error',
    };
  }
}

/* =====================================
   TABLE RENDER
===================================== */
function renderRows(items) {
  if (!tableBody) return;

  tableBody.innerHTML = '';

  if (!items.length) {
    tableBody.innerHTML = `<tr><td colspan="5">No requests found</td></tr>`;
    return;
  }

  items.forEach((item) => {
    const row = document.createElement('tr');

    row.innerHTML = `
    <td>${item.id}</td>
            <td>${item.area_name}</td>
            <td>${item.truck_name || 'No truck'}</td>
            <td><span class="status-badge status-${item.status.toLowerCase()}">${item.status}</span></td>
            <td class="actions-cell"></td>
        `;

    const actionsCell = row.querySelector('.actions-cell');

    /* ==========================
           RESIDENT ACTIONS
        ========================== */
    if (userRole === 'resident') {
      if (item.status === 'pending') {
        const editBtn = createButton('Edit', () =>
          editRow(item.id, item.area_name, item.status)
        );

        const delBtn = createButton('Delete', () => deleteRow(item.id), 'red');

        actionsCell.append(editBtn, delBtn);
      } else {
        actionsCell.textContent = '-';
      }
    } else if (userRole === 'admin') {
      /* ==========================
           ADMIN ACTIONS
        ========================== */
      const editBtn = createButton('Edit', () =>
        editRow(item.id, item.area_name, item.status)
      );

      const delBtn = createButton('Delete', () => deleteRow(item.id), 'red');

      actionsCell.append(editBtn, delBtn);
    } else if (userRole === 'collector') {
      /* ==========================
           COLLECTOR ACTIONS
        ========================== */
      if (item.status === 'assigned') {
        const doneBtn = createButton(
          'Collected',
          () => markCollected(item.id),
          'green'
        );

        actionsCell.append(doneBtn);
      } else {
        actionsCell.textContent = '-';
      }
    } else if (userRole === 'officer') {
      /* ==========================
           OFFICER ACTIONS
        ========================== */
      actionsCell.textContent = 'Read Only';
    }

    tableBody.appendChild(row);
  });
}

/* =====================================
   CREATE BUTTON
===================================== */
function createButton(text, callback, color = '') {
  const btn = document.createElement('button');

  btn.type = 'button';
  btn.textContent = text;
  btn.onclick = callback;
  btn.className = 'btn gap-2';

  if (color === 'red') {
    btn.className = 'btn btn-danger gap-2';
  } else if (color) {
    btn.style.background = color;
    btn.style.color = '#fff';
  }

  return btn;
}

/* =====================================
   LOAD TABLE
===================================== */
async function loadTable() {
  const result = await ajaxRequest('actions/fetch_requests.php');

  if (!result.success) {
    showFeedback(result.message);
    return;
  }

  renderRows(result.data || []);
}

/* =====================================
   CREATE REQUEST
===================================== */
async function createRequest(e) {
  e.preventDefault();

  const areaId = areaSelect.value;
  const error = validateAreaId(areaId);

  if (error) {
    showFeedback(error);
    return;
  }

  const result = await ajaxRequest('actions/create_request.php', {
    data: { area_id: areaId, csrf_token: csrfToken },
  });

  if (!result.success) {
    showFeedback(result.message);
    return;
  }

  showFeedback(result.message, true);
  areaSelect.value = '';
  loadTable();
}

/* =====================================
   EDIT
===================================== */
async function editRow(id, areaName, status) {
  document.getElementById('edit-id').value = id;
  document.getElementById('edit-area_id').value = '';
  document.getElementById('edit-status').value = status;

  // Load areas for edit select
  const result = await ajaxRequest('actions/fetch_areas.php');
  if (result.success) {
    populateSelect('edit-area_id', result.data, 'Select Area');
  }

  editModal.style.display = 'block';
}

async function saveEdit() {
  const id = document.getElementById('edit-id').value;
  const areaId = document.getElementById('edit-area_id').value;
  const status = document.getElementById('edit-status').value;

  const error = validateAreaId(areaId);
  if (error) {
    showFeedback(error);
    return;
  }

  const result = await ajaxRequest('actions/edit_request.php', {
    data: {
      request_id: id,
      area_id: areaId,
      status,
      csrf_token: csrfToken,
    },
  });

  if (!result.success) {
    showFeedback(result.message);
    return;
  }

  showFeedback(result.message, true);
  closeModal();
  loadTable();
}

/* =====================================
   DELETE
===================================== */
async function deleteRow(id) {
  if (!confirm('Delete request?')) return;

  const result = await ajaxRequest('actions/delete_request.php', {
    data: {
      request_id: id,
      csrf_token: csrfToken,
    },
  });

  if (!result.success) {
    showFeedback(result.message);
    return;
  }

  showFeedback(result.message, true);
  loadTable();
}

/* =====================================
   COLLECTOR COMPLETE
===================================== */
async function markCollected(id) {
  const result = await ajaxRequest('actions/update_status.php', {
    data: {
      request_id: id,
      status: 'completed',
      csrf_token: csrfToken,
    },
  });

  if (!result.success) {
    showFeedback(result.message);
    return;
  }

  showFeedback('Marked as collected', true);
  loadTable();
}

/* =====================================
   MODAL
===================================== */
function closeModal() {
  if (editModal) {
    editModal.style.display = 'none';
  }
}

window.saveEdit = saveEdit;
window.closeModal = closeModal;

/* =====================================
   INIT
===================================== */
async function populateSelect(selectId, areas, placeholder = 'Select...') {
  const select = document.getElementById(selectId);
  if (!select) return;

  select.innerHTML = `<option value="">${placeholder}</option>`;
  areas.forEach((area) => {
    const option = document.createElement('option');
    option.value = area.id;
    option.textContent = area.name;
    select.appendChild(option);
  });
}

document.addEventListener('DOMContentLoaded', async () => {
  // Load areas for create form
  if (createForm) {
    const result = await ajaxRequest('actions/fetch_areas.php');
    if (result.success) {
      populateSelect('area-select', result.data);
    }
  }

  if (createForm && userRole === 'resident') {
    createForm.addEventListener('submit', createRequest);
  }

  const refreshBtn = document.getElementById('load-table');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', loadTable);
  }

  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = currentTheme === 'light' ? 'dark' : 'light';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
    });
  }

  loadTable();
});
