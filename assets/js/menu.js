(function () {
  var openButton = document.querySelector('[data-menu-open]');
  var closeButton = document.querySelector('[data-menu-close]');
  var overlay = document.querySelector('[data-menu-overlay]');
  var drawer = document.querySelector('[data-menu-drawer]');
  var drawerLinks = document.querySelectorAll('.drawer-link');
  var navLinks = document.querySelectorAll('[data-nav]');
  var dropdownToggles = document.querySelectorAll('[data-dropdown-toggle]');
  var filterButtons = document.querySelectorAll('[data-filter]');
  var workCards = document.querySelectorAll('.work-card');
  var path = window.location.pathname.split('/').pop() || 'index.html';
  var currentMap = {
    'index.html': 'index',
    'index.php': 'index',
    'work.html': 'work',
    'work.php': 'work',
    'project.html': 'work',
    'project.php': 'work',
    'research.html': 'research',
    'research.php': 'research',
    'photograph.html': 'photograph',
    'photograph.php': 'photograph',
    'doodle.html': 'doodle',
    'doodle.php': 'doodle',
    'about.html': 'about',
    'about.php': 'about',
    'resume.html': 'resume',
    'resume.php': 'resume',
    'contact.html': 'contact',
    'contact.php': 'contact'
  };

  function setCurrentNavigation() {
    var current = currentMap[path];
    navLinks.forEach(function (link) {
      if (link.dataset.nav === current) {
        link.setAttribute('aria-current', 'page');
      }
    });
  }

  function openDrawer() {
    if (!overlay || !drawer) {
      return;
    }
    overlay.classList.add('is-open');
    drawer.classList.add('is-open');
    document.body.classList.add('overflow-hidden');
  }

  function closeDrawer() {
    if (!overlay || !drawer) {
      return;
    }
    overlay.classList.remove('is-open');
    drawer.classList.remove('is-open');
    document.body.classList.remove('overflow-hidden');
  }

  function closeDropdowns(exceptWrap) {
    dropdownToggles.forEach(function (toggle) {
      var wrap = toggle.closest('.nav-dropdown-wrap');
      if (wrap && wrap !== exceptWrap) {
        wrap.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function applyFilter(filter) {
    filterButtons.forEach(function (button) {
      button.classList.toggle('is-active', button.dataset.filter === filter);
      button.setAttribute('aria-pressed', String(button.dataset.filter === filter));
    });

    workCards.forEach(function (card) {
      var matches = filter === 'all' || card.dataset.category === filter;
      card.classList.toggle('is-hidden', !matches);
    });
  }

  if (openButton) {
    openButton.addEventListener('click', openDrawer);
  }

  if (closeButton) {
    closeButton.addEventListener('click', closeDrawer);
  }

  if (overlay) {
    overlay.addEventListener('click', closeDrawer);
  }

  dropdownToggles.forEach(function (toggle) {
    toggle.addEventListener('click', function (event) {
      event.preventDefault();
      var wrap = toggle.closest('.nav-dropdown-wrap');
      if (!wrap) {
        return;
      }
      var willOpen = !wrap.classList.contains('is-open');
      closeDropdowns(wrap);
      wrap.classList.toggle('is-open', willOpen);
      toggle.setAttribute('aria-expanded', String(willOpen));
    });
  });

  document.addEventListener('click', function (event) {
    if (!event.target.closest('.nav-dropdown-wrap')) {
      closeDropdowns();
    }
  });

  drawerLinks.forEach(function (link) {
    link.addEventListener('click', closeDrawer);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeDrawer();
      closeDropdowns();
    }
  });

  filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      applyFilter(button.dataset.filter);
    });
  });

  setCurrentNavigation();
  if (filterButtons.length > 0) {
    var params = new URLSearchParams(window.location.search);
    var requestedFilter = params.get('filter') || 'all';
    var hasFilter = Array.prototype.some.call(filterButtons, function (button) {
      return button.dataset.filter === requestedFilter;
    });
    applyFilter(hasFilter ? requestedFilter : 'all');
  }
}());
