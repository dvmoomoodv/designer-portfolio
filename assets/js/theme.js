(function () {
  var STORAGE_KEY = 'theme';
  var root = document.documentElement;
  var toggles = document.querySelectorAll('[data-theme-toggle]');
  var icons = document.querySelectorAll('[data-theme-icon]');
  var mediaQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

  function getSystemTheme() {
    return mediaQuery && mediaQuery.matches ? 'dark' : 'light';
  }

  function getStoredTheme() {
    try {
      return localStorage.getItem(STORAGE_KEY);
    } catch (error) {
      return null;
    }
  }

  function getPreferredTheme() {
    var storedTheme = getStoredTheme();
    if (storedTheme === 'light' || storedTheme === 'dark') {
      return storedTheme;
    }
    return getSystemTheme();
  }

  function renderTheme(theme) {
    root.classList.toggle('dark', theme === 'dark');
    root.dataset.theme = theme;
    root.style.colorScheme = theme;
    icons.forEach(function (icon) {
      icon.textContent = theme === 'dark' ? '☾' : '◐';
    });
    toggles.forEach(function (toggle) {
      toggle.setAttribute('aria-pressed', String(theme === 'dark'));
      toggle.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    });
  }

  function setTheme(theme) {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch (error) {
      // Ignore storage errors and still render the requested theme.
    }
    renderTheme(theme);
  }

  toggles.forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      var nextTheme = root.classList.contains('dark') ? 'light' : 'dark';
      setTheme(nextTheme);
    });
  });

  if (mediaQuery) {
    mediaQuery.addEventListener('change', function (event) {
      if (getStoredTheme()) {
        return;
      }
      renderTheme(event.matches ? 'dark' : 'light');
    });
  }

  renderTheme(getPreferredTheme());
}());
