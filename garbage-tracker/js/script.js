// Garbage Tracker JS - Dashboard AJAX CRUD
// Compatible with modern browsers, no jQuery dependency

'use strict';

// Global variables
let csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';

// DOM elements
const tableBody = document.querySelector('#requests-table tbody');
const createForm = document.getElementById('create-form');
const areaInput = document.getElementById('area-input');
const feedbackDiv = document.getElementById('feedback');
const editModal = document.getElementById('edit-modal');

// Show feedback message
function showFeedback(message, isSuccess = false) {
  if (feedbackDiv) {
    feedbackDiv.textContent = message;
    feedbackDiv.style.color = isSuccess ? 'green' : 'red';
    feedbackDiv.style.display = 'block';
    setTimeout(() => { feedbackDiv.style.display = 'none'; }, 5000);
  }
}

// Validate area input
function validateArea(area) {
  if (!area || area.trim().length < 3 || area.trim().length > 255) {
    return 'Area must be 3-255 characters';
  }
  return null;
}

// AJAX helper
async function ajaxRequest(url, options = {}) {
  try {
    const response = await fetch(url, {
      method: options.method || 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams(options.data || {})
    });
    return await response.json();
  } catch (error) {
    return { success: false, message: 'Network error: ' + error.message };
  }
}

// Load requests table
async function loadTable() {
  const result = await ajaxRequest('actions/fetch_requests.php');
  if (result.success) {
    const tbody = tableBody;
    tbody.innerHTML = '';
    if (result.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4">No requests found</td></tr>';
      return;
    }
    result.data.forEach(row => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${row.id}</td>
        <td>${escapeHtml(row.area)}</td>
        <td>${escapeHtml(row.status)}</td>
        <td>
          <button onclick="editRow(${row.id}, '${escapeHtml(row.area)}', '${row.status}')">Edit</button>
          <button onclick="deleteRow(${row.id})" style="background:red;color:white;">Delete</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  } else {
    showFeedback(result.message || 'Failed to load requests');
  }
}

// Edit row - populate modal
function editRow(id, area, status) {
  if (editModal) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-area').value = area;
    document.getElementById('edit-status').value = status;
    editModal.style.display = 'block';
  }
}

// Save edit
async function saveEdit() {
  const id = document.getElementById('edit-id').value;
  const area = document.getElementById('edit-area').value;
  const status = document.getElementById('edit-status').value;

  const error = validateArea(area);
  if (error) {
    showFeedback(error);
    return;
  }

  const data = { request_id: id, area, status, csrf_token: csrfToken };
  const result = await ajaxRequest('actions/edit_request.php', { data });

  if (result.success) {
    showFeedback(result.message, true);
    closeModal();
    loadTable();
  } else {
    showFeedback(result.message);
  }
}

// Delete row
async function deleteRow(id) {
  if (!confirm('Delete this request?')) return;

  const data = { request_id: id, csrf_token: csrfToken };
  const result = await ajaxRequest('actions/delete_request.php', { data });

  if (result.success) {
    showFeedback(result.message, true);
    loadTable();
  } else {
    showFeedback(result.message);
  }
}

// Create request
async function createRequest(e) {
  e.preventDefault();
  const area = areaInput.value.trim();
  const error = validateArea(area);
  if (error) {
    showFeedback(error);
    return;
  }

  const data = { area, csrf_token: csrfToken };
  const result = await ajaxRequest('actions/create_request.php', { data });

  if (result.success || result === 'success') { // create_request uses redirect but AJAX
    showFeedback('Request created successfully', true);
    areaInput.value = '';
    loadTable();
  } else {
    showFeedback(result.message || 'Failed to create request');
  }
}

// Close modal
function closeModal() {
  if (editModal) editModal.style.display = 'none';
}

// Escape HTML for security
function escapeHtml(text) {
  const map = { '&': '&amp;', '<': '<', '>': '>', '"': '"', \"'\": '&#039;' };
  return text.replace(/[&<>\"']/g, m => map[m]);
}

// Init
document.addEventListener('DOMContentLoaded', () => {
  if (createForm) createForm.addEventListener('submit', createRequest);
  if (document.getElementById('load-table')) document.getElementById('load-table').addEventListener('click', loadTable);
  loadTable(); // Auto-load on page load
});

