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
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Fixed: \s and \.
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

  // Reuse error spans if present, otherwise fall back to alert
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

  // ── Dashboard logic (only runs if sidebar exists) ──
  const sidebar = document.getElementById('sidebar');

  if (sidebar) {
    // Dark mode toggle
    const themeToggle = document.createElement('button');
    themeToggle.innerHTML = '🌙';
    themeToggle.className = 'theme-toggle';
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

    // Mobile sidebar — uses the .sidebar-toggle button already in dashboard.html navbar
    function initMobileMenu() {
      if (window.innerWidth <= 768) {
        sidebar.classList.remove('open');
      }
    }

    window.addEventListener('resize', initMobileMenu);
    initMobileMenu();

    // Reports search/filter
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

    // Stat card fade-in animation
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, index) => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      setTimeout(() => {
        card.style.transition = 'all 0.6s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, index * 200);
    });
  }
});
