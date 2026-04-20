'use strict';

/* =====================================
   ELEMENTS
===================================== */
const csrfToken =
  document.querySelector('input[name="csrf_token"]')?.value || '';
const userRole = document.body.dataset.role || 'resident';

const tableBody = document.querySelector('#requests-table tbody');
const createForm = document.getElementById('create-form');
const areaInput = document.getElementById('area-input');
const feedbackDiv = document.getElementById('feedback');
const editModal = document.getElementById('edit-modal');

/* =====================================
   FEEDBACK
===================================== */
function showFeedback(message, success = false) {
  if (!feedbackDiv) return;

  feedbackDiv.textContent = message;
  feedbackDiv.style.display = 'block';
  feedbackDiv.style.color = success ? 'green' : 'red';

  setTimeout(() => {
    feedbackDiv.style.display = 'none';
  }, 4000);
}

/* =====================================
   VALIDATION
===================================== */
function validateArea(area) {
  if (!area || area.trim().length < 3 || area.trim().length > 255) {
    return 'Area must be between 3 and 255 characters.';
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
    tableBody.innerHTML = `<tr><td colspan="4">No requests found</td></tr>`;
    return;
  }

  items.forEach((item) => {
    const row = document.createElement('tr');

    row.innerHTML = `
            <td>${item.id}</td>
            <td>${item.area}</td>
            <td>${item.status}</td>
            <td class="actions-cell"></td>
        `;

    const actionsCell = row.querySelector('.actions-cell');

    /* ==========================
           RESIDENT ACTIONS
        ========================== */
    if (userRole === 'resident') {
      if (item.status === 'pending') {
        const editBtn = createButton('Edit', () =>
          editRow(item.id, item.area, item.status)
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
        editRow(item.id, item.area, item.status)
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

  if (color) {
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

  const area = areaInput.value.trim();
  const error = validateArea(area);

  if (error) {
    showFeedback(error);
    return;
  }

  const result = await ajaxRequest('actions/create_request.php', {
    data: { area, csrf_token: csrfToken },
  });

  if (!result.success) {
    showFeedback(result.message);
    return;
  }

  showFeedback(result.message, true);
  areaInput.value = '';
  loadTable();
}

/* =====================================
   EDIT
===================================== */
function editRow(id, area, status) {
  document.getElementById('edit-id').value = id;
  document.getElementById('edit-area').value = area;
  document.getElementById('edit-status').value = status;

  editModal.style.display = 'block';
}

async function saveEdit() {
  const id = document.getElementById('edit-id').value;
  const area = document.getElementById('edit-area').value;
  const status = document.getElementById('edit-status').value;

  const result = await ajaxRequest('actions/edit_request.php', {
    data: {
      request_id: id,
      area,
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
  const result = await ajaxRequest('actions/edit_request.php', {
    data: {
      request_id: id,
      status: 'completed',
      area: '',
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
document.addEventListener('DOMContentLoaded', () => {
  if (createForm && userRole === 'resident') {
    createForm.addEventListener('submit', createRequest);
  }

  const refreshBtn = document.getElementById('load-table');

  if (refreshBtn) {
    refreshBtn.addEventListener('click', loadTable);
  }

  loadTable();
});
