(function () {
  var btn = document.getElementById('adminThemeToggle');
  if (!btn) return;
  btn.addEventListener('click', function () {
    var root = document.documentElement;
    var next = root.classList.contains('dark') ? 'light' : 'dark';
    root.classList.toggle('dark', next === 'dark');
    root.dataset.theme = next;
    root.style.colorScheme = next;
    try { localStorage.setItem('admin-theme', next); } catch (e) {}
  });
}());
