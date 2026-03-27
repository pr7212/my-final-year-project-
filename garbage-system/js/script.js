// ─── Shared Utilities ────────────────────────────────────────────────────────

function showError(input, errorElement, message) {
  input.classList.add('invalid');
  errorElement.textContent = message;
  errorElement.classList.add('show');
}

function clearError(input, errorElement) {
  input.classList.remove('invalid');
  errorElement.textContent = '';
  errorElement.classList.remove('show');
}

function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email) && email.trim() !== '';
}

// ─── Login Validation ─────────────────────────────────────────────────────────

function validateLoginField(input, errorId, validateFn, errorMsg) {
  const errorElement = document.getElementById(errorId);
  const value = input.value;

  if (!validateFn(value)) {
    showError(input, errorElement, errorMsg);
    return false;
  } else {
    clearError(input, errorElement);
    return true;
  }
}

function validateLogin() {
  const emailInput = document.getElementById('login-email');
  const passwordInput = document.getElementById('login-password');

  const emailValid = validateLoginField(
    emailInput,
    'email-error',
    validateEmail,
    'Please enter a valid email'
  );

  const passwordValid = validateLoginField(
    passwordInput,
    'password-error',
    (pass) => pass.length >= 6,
    'Password must be at least 6 characters'
  );

  return emailValid && passwordValid;
}

// ─── Register Validation ──────────────────────────────────────────────────────

function validateRegister() {
  const nameInput = document.getElementById('register-name');
  const emailInput = document.getElementById('register-email');
  const passwordInput = document.getElementById('register-password');

  const nameError = document.getElementById('register-name-error');
  const emailError = document.getElementById('register-email-error');
  const passwordError = document.getElementById('register-password-error');

  let valid = true;

  if (nameInput.value.trim() === '') {
    if (nameError) showError(nameInput, nameError, 'Full name is required');
    else alert('Full name is required');
    valid = false;
  } else if (nameError) clearError(nameInput, nameError);

  if (!validateEmail(emailInput.value)) {
    if (emailError)
      showError(emailInput, emailError, 'Please enter a valid email');
    else alert('Please enter a valid email');
    valid = false;
  } else if (emailError) clearError(emailInput, emailError);

  if (passwordInput.value.length < 6) {
    if (passwordError)
      showError(
        passwordInput,
        passwordError,
        'Password must be at least 6 characters'
      );
    else alert('Password must be at least 6 characters');
    valid = false;
  } else if (passwordError) clearError(passwordInput, passwordError);

  return valid;
}

// ─── API Functions ────────────────────────────────────────────────────────────

const API_BASE = '../garbage-tracker/actions/';

async function loadDashboardData() {
  try {
    const res = await fetch(`${API_BASE}fetch_requests.php`, {
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const result = await res.json();
    if (result.success) {
      updateStats(result.data);
      updateReportsTable(result.data);
    } else {
      console.error('API error:', result.message);
      document.getElementById('requests-tbody').innerHTML =
        '<tr><td colspan="5">No data or unauthorized. Login required.</td></tr>';
    }
  } catch (err) {
    console.error('Fetch error:', err);
    document.getElementById('requests-tbody').innerHTML =
      '<tr><td colspan="5">Connection failed. Start server & DB.</td></tr>';
  }
}

function updateStats(data) {
  const totalEl = document.getElementById('stat-total');
  const pendingEl = document.getElementById('stat-pending');

  if (totalEl) totalEl.textContent = data.length;
  if (pendingEl) {
    const pending = data.filter((r) => r.status === 'pending').length;
    pendingEl.textContent = pending;
  }

  const collectedEl = document.getElementById('stat-collected');
  if (collectedEl)
    collectedEl.textContent =
      data.length - (pendingEl ? parseInt(pendingEl.textContent) : 0);
}

function updateReportsTable(data) {
  const tbody = document.getElementById('requests-tbody');
  if (!tbody || !data || data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5">No requests found</td></tr>';
    return;
  }

  tbody.innerHTML = data
    .slice(0, 10)
    .map(
      (row) => `
    <tr>
      <td>#${String(row.id).padStart(3, '0')}</td>
      <td><i class="fas fa-map-marker-alt" style="color: #2c7be5"></i> ${row.area}</td>
      <td>${row.status.replace('-', ' ').replace(/\b\w/g, (l) => l.toUpperCase())}</td>
      <td><span class="status-${row.status}">${row.status}</span></td>
      <td>${new Date(row.timestamp).toLocaleString()}</td>
    </tr>
  `
    )
    .join('');
}

// ─── Single DOMContentLoaded ──────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
  // ── Real-time login validation ──
  const emailInput = document.getElementById('login-email');
  const passwordInput = document.getElementById('login-password');

  if (emailInput) {
    emailInput.addEventListener('blur', function () {
      validateLoginField(
        this,
        'email-error',
        validateEmail,
        'Please enter a valid email'
      );
    });
    emailInput.addEventListener('input', function () {
      clearError(this, document.getElementById('email-error'));
    });
  }

  if (passwordInput) {
    passwordInput.addEventListener('blur', function () {
      validateLoginField(
        this,
        'password-error',
        (pass) => pass.length >= 6,
        'Password must be at least 6 characters'
      );
    });
    passwordInput.addEventListener('input', function () {
      clearError(this, document.getElementById('password-error'));
    });
  }

  // ── Dashboard logic ──
  const sidebar = document.getElementById('sidebar');

  if (sidebar) {
    loadDashboardData();

    // Dark mode toggle
    const themeToggle = document.createElement('button');
    themeToggle.innerHTML = '🌙';
    themeToggle.className = 'theme-toggle';
    themeToggle.style.position = 'fixed';
    themeToggle.style.top = '20px';
    themeToggle.style.right = '20px';
    themeToggle.style.zIndex = '1000';
    document.body.appendChild(themeToggle);

    themeToggle.addEventListener('click', function () {
      document.body.classList.toggle('dark-mode');
      this.innerHTML = document.body.classList.contains('dark-mode')
        ? '☀️'
        : '🌙';
      localStorage.setItem(
        'darkMode',
        document.body.classList.contains('dark-mode')
      );
    });

    if (localStorage.getItem('darkMode') === 'true') {
      document.body.classList.add('dark-mode');
      themeToggle.innerHTML = '☀️';
    }

    // Mobile sidebar
    function initMobileMenu() {
      if (window.innerWidth <= 768) sidebar.classList.remove('open');
    }
    window.addEventListener('resize', initMobileMenu);
    initMobileMenu();

    // Reports search
    const searchInput = document.getElementById('reports-search');
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        const rows = document.querySelectorAll('.reports-table tbody tr');
        const term = this.value.toLowerCase();
        rows.forEach((row) => {
          row.style.display = row.textContent.toLowerCase().includes(term)
            ? ''
            : 'none';
        });
      });
    }
  }
});
