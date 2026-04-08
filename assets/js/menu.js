(function () {
  var openButton = document.querySelector('[data-menu-open]');
  var closeButton = document.querySelector('[data-menu-close]');
  var overlay = document.querySelector('[data-menu-overlay]');
  var drawer = document.querySelector('[data-menu-drawer]');
  var drawerLinks = document.querySelectorAll('.drawer-link');
  var navLinks = document.querySelectorAll('[data-nav]');
  var filterButtons = document.querySelectorAll('[data-filter]');
  var workCards = document.querySelectorAll('.work-card');
  var path = window.location.pathname.split('/').pop() || 'index.html';
  var currentMap = {
    'index.html': 'index',
    'work.html': 'work',
    'project.html': 'work',
    'about.html': 'about',
    'resume.html': 'resume',
    'contact.html': 'contact'
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

  drawerLinks.forEach(function (link) {
    link.addEventListener('click', closeDrawer);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeDrawer();
    }
  });

  filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      applyFilter(button.dataset.filter);
    });
  });

  setCurrentNavigation();
  if (filterButtons.length > 0) {
    applyFilter('all');
  }
}());
