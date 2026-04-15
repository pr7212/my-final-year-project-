'use strict';

const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
const tableBody = document.querySelector('#requests-table tbody');
const createForm = document.getElementById('create-form');
const areaInput = document.getElementById('area-input');
const feedbackDiv = document.getElementById('feedback');
const editModal = document.getElementById('edit-modal');

function showFeedback(message, isSuccess = false) {
  if (!feedbackDiv) {
    return;
  }

  feedbackDiv.textContent = message;
  feedbackDiv.style.color = isSuccess ? 'green' : 'red';
  feedbackDiv.style.display = 'block';

  window.setTimeout(() => {
    feedbackDiv.style.display = 'none';
  }, 5000);
}

function validateArea(area) {
  if (!area || area.trim().length < 3 || area.trim().length > 255) {
    return 'Area must be 3-255 characters';
  }

  return null;
}

function createCell(label, value, className = '') {
  const cell = document.createElement('td');
  cell.dataset.label = label;
  cell.textContent = value;

  if (className) {
    cell.className = className;
  }

  return cell;
}

function toStatusClass(status) {
  return `status-${String(status || '').toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;
}

async function ajaxRequest(url, options = {}) {
  const method = options.method || 'POST';
  const headers = {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  };

  const requestOptions = {
    method,
    headers
  };

  if (options.data) {
    headers['Content-Type'] = 'application/x-www-form-urlencoded';
    requestOptions.body = new URLSearchParams(options.data).toString();
  }

  try {
    const response = await fetch(url, requestOptions);
    const contentType = response.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
      return await response.json();
    }

    const text = await response.text();
    return {
      success: response.ok,
      message: text || 'Unexpected server response'
    };
  } catch (error) {
    return {
      success: false,
      message: `Network error: ${error.message}`
    };
  }
}

function renderRows(items) {
  if (!tableBody) {
    return;
  }

  tableBody.innerHTML = '';

  if (!items.length) {
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 4;
    cell.textContent = 'No requests found';
    row.appendChild(cell);
    tableBody.appendChild(row);
    return;
  }

  items.forEach((rowData) => {
    const row = document.createElement('tr');

    row.appendChild(createCell('ID', String(rowData.id)));
    row.appendChild(createCell('Area', rowData.area || ''));

    const statusCell = createCell('Status', rowData.status || '', toStatusClass(rowData.status));
    row.appendChild(statusCell);

    const actionsCell = document.createElement('td');
    actionsCell.dataset.label = 'Actions';

    const editButton = document.createElement('button');
    editButton.type = 'button';
    editButton.textContent = 'Edit';
    editButton.addEventListener('click', () => {
      editRow(rowData.id, rowData.area, rowData.status);
    });

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.textContent = 'Delete';
    deleteButton.style.background = 'red';
    deleteButton.style.color = 'white';
    deleteButton.addEventListener('click', () => {
      deleteRow(rowData.id);
    });

    actionsCell.appendChild(editButton);
    actionsCell.appendChild(deleteButton);
    row.appendChild(actionsCell);
    tableBody.appendChild(row);
  });
}

async function loadTable() {
  const result = await ajaxRequest('actions/fetch_requests.php');

  if (!result.success) {
    showFeedback(result.message || 'Failed to load requests');
    return;
  }

  const items = Array.isArray(result.data)
    ? result.data
    : Array.isArray(result.data?.items)
      ? result.data.items
      : [];

  renderRows(items);
}

function editRow(id, area, status) {
  if (!editModal) {
    return;
  }

  document.getElementById('edit-id').value = id;
  document.getElementById('edit-area').value = area || '';
  document.getElementById('edit-status').value = status || 'pending';
  editModal.style.display = 'block';
}

async function saveEdit() {
  const id = document.getElementById('edit-id').value;
  const area = document.getElementById('edit-area').value;
  const status = document.getElementById('edit-status').value;

  const error = validateArea(area);
  if (error) {
    showFeedback(error);
    return;
  }

  const result = await ajaxRequest('actions/edit_request.php', {
    data: { request_id: id, area, status, csrf_token: csrfToken }
  });

  if (!result.success) {
    showFeedback(result.message || 'Failed to update request');
    return;
  }

  showFeedback(result.message || 'Request updated successfully', true);
  closeModal();
  loadTable();
}

async function deleteRow(id) {
  if (!window.confirm('Delete this request?')) {
    return;
  }

  const result = await ajaxRequest('actions/delete_request.php', {
    data: { request_id: id, csrf_token: csrfToken }
  });

  if (!result.success) {
    showFeedback(result.message || 'Failed to delete request');
    return;
  }

  showFeedback(result.message || 'Request deleted successfully', true);
  loadTable();
}

async function createRequest(event) {
  event.preventDefault();

  const area = areaInput?.value.trim() || '';
  const error = validateArea(area);

  if (error) {
    showFeedback(error);
    return;
  }

  const result = await ajaxRequest('actions/create_request.php', {
    data: { area, csrf_token: csrfToken }
  });

  if (!result.success) {
    showFeedback(result.message || 'Failed to create request');
    return;
  }

  showFeedback(result.message || 'Request created successfully', true);

  if (areaInput) {
    areaInput.value = '';
  }

  loadTable();
}

function closeModal() {
  if (editModal) {
    editModal.style.display = 'none';
  }
}

window.saveEdit = saveEdit;
window.closeModal = closeModal;

document.addEventListener('DOMContentLoaded', () => {
  if (createForm) {
    createForm.addEventListener('submit', createRequest);
  }

  const loadButton = document.getElementById('load-table');
  if (loadButton) {
    loadButton.addEventListener('click', loadTable);
  }

  loadTable();
});
