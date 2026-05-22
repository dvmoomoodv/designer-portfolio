(function () {
  var form = document.getElementById('editForm');
  if (!form) return;

  /* ── 폼 변경 감지: 저장 전 페이지 이탈 경고 ── */
  var dirty = false;
  form.addEventListener('input', function () { dirty = true; updateStatus(); });
  form.addEventListener('submit', function () { dirty = false; });
  window.addEventListener('beforeunload', function (e) {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
  function updateStatus() {
    var el = document.getElementById('saveStatus');
    if (el) el.textContent = '저장되지 않은 변경사항이 있습니다.';
  }

  /* ── details 토글: 화살표 회전 ── */
  document.querySelectorAll('details').forEach(function (d) {
    d.addEventListener('toggle', function () {
      var chev = d.querySelector('[data-chevron]');
      if (chev) chev.style.transform = d.open ? 'rotate(0deg)' : 'rotate(-90deg)';
    });
  });

  document.querySelectorAll('[data-expand-all]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      form.querySelectorAll('details').forEach(function (d) { d.open = true; });
    });
  });

  document.querySelectorAll('[data-collapse-all]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      form.querySelectorAll('details').forEach(function (d) { d.open = false; });
    });
  });

  /* ── 제출 전: obj-list 인덱스 재정렬 ── */
  form.addEventListener('submit', function () {
    form.querySelectorAll('[data-obj-list]').forEach(function (container) {
      var basePath = container.dataset.objList;
      var items = container.querySelectorAll(':scope > [data-obj-items] > [data-obj-item]');
      items.forEach(function (item, idx) {
        item.querySelectorAll('[name]').forEach(function (el) {
          // Replace the numeric segment after basePath
          var escaped = basePath.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          el.name = el.name.replace(new RegExp(escaped + '\\[(\\d+)\\]'), basePath + '[' + idx + ']');
        });
      });
    });
  });

  /* ── 이벤트 위임: 모든 list 버튼 ── */
  form.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-obj-up],[data-obj-down],[data-obj-delete],[data-obj-add],[data-scalar-add],[data-scalar-delete]');
    if (!btn) return;
    e.preventDefault();
    dirty = true;

    if (btn.hasAttribute('data-obj-up')) {
      var item = btn.closest('[data-obj-item]');
      var prev = item && item.previousElementSibling;
      if (prev && prev.hasAttribute('data-obj-item')) prev.parentNode.insertBefore(item, prev);

    } else if (btn.hasAttribute('data-obj-down')) {
      var item = btn.closest('[data-obj-item]');
      var next = item && item.nextElementSibling;
      if (next && next.hasAttribute('data-obj-item')) next.parentNode.insertBefore(next, item);

    } else if (btn.hasAttribute('data-obj-delete')) {
      var item = btn.closest('[data-obj-item]');
      if (item && confirm('이 항목을 삭제하시겠습니까?')) item.remove();

    } else if (btn.hasAttribute('data-obj-add')) {
      objAdd(btn);

    } else if (btn.hasAttribute('data-scalar-add')) {
      scalarAdd(btn);

    } else if (btn.hasAttribute('data-scalar-delete')) {
      var row = btn.closest('[data-scalar-item]');
      if (row) row.remove();
    }
    updateStatus();
  });

  /* ── 오브젝트 리스트 항목 추가: 마지막 항목 복제 후 값 초기화 ── */
  function objAdd(btn) {
    var container  = btn.closest('[data-obj-list]');
    var basePath   = container.dataset.objList;
    var itemsEl    = container.querySelector('[data-obj-items]');
    var existing   = itemsEl.querySelectorAll('[data-obj-item]');
    if (existing.length === 0) return; // 템플릿 없음

    var clone = existing[existing.length - 1].cloneNode(true);
    clone.open = true;
    var newIdx = existing.length;
    var escaped = basePath.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

    // 인덱스 교체
    clone.querySelectorAll('[name]').forEach(function (el) {
      el.name = el.name.replace(new RegExp(escaped + '\\[(\\d+)\\]'), basePath + '[' + newIdx + ']');
    });
    // 값 초기화
    clone.querySelectorAll('input[type="text"], input:not([type]), textarea').forEach(function (el) {
      el.value = '';
    });
    // 이미지 미리보기 숨김
    clone.querySelectorAll('img').forEach(function (img) { img.style.display = 'none'; });

    itemsEl.appendChild(clone);
    clone.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  /* ── 스칼라 리스트 항목 추가 ── */
  function scalarAdd(btn) {
    var path       = btn.dataset.scalarPath;
    var listEl     = btn.previousElementSibling; // [data-scalar-list]
    var row        = document.createElement('div');
    row.setAttribute('data-scalar-item', '');
    row.className  = 'flex gap-2';
    row.innerHTML  =
      '<input class="admin-input" type="text" name="' + escHtml(path) + '[]" value="">' +
      '<button type="button" class="admin-btn admin-btn--ghost px-2 text-xs text-red-500 dark:text-red-400" data-scalar-delete title="삭제">×</button>';
    listEl.appendChild(row);
    row.querySelector('input').focus();
  }

  function escHtml(s) {
    return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  /* ── 이미지 업로드 (Ajax) ── */
  form.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-upload-trigger]');
    if (!btn) return;
    e.preventDefault();

    var targetName = btn.dataset.uploadTarget;
    var input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml';
    input.addEventListener('change', function () {
      var file = input.files[0];
      if (!file) return;
      uploadFile(file, targetName, btn);
    });
    input.click();
  });

  function getCsrfToken() {
    var el = form.querySelector('input[name="csrf_token"]');
    return el ? el.value : '';
  }

  function uploadFile(file, targetName, triggerBtn) {
    var fd = new FormData();
    fd.append('file', file);
    fd.append('csrf_token', getCsrfToken());

    triggerBtn.disabled = true;
    triggerBtn.textContent = '업로드 중…';

    fetch('./api/upload.php', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.ok) throw new Error(data.error || '업로드 실패');

        // input[name=targetName] 값 갱신
        var field = form.querySelector('[name="' + escHtml(targetName) + '"][data-image-path]');
        if (field) {
          field.value = data.url;
          // 미리보기 갱신
          var preview = field.closest('div').previousElementSibling;
          if (preview && preview.tagName === 'IMG') {
            preview.src = data.url;
            preview.style.display = '';
          }
        }
        dirty = true;
        updateStatus();
        triggerBtn.textContent = '✓ 업로드됨 — 저장하려면 저장 버튼 클릭';
      })
      .catch(function (err) {
        alert('업로드 오류: ' + err.message);
        triggerBtn.textContent = '↑ 이미지 업로드';
      })
      .finally(function () {
        triggerBtn.disabled = false;
      });
  }
}());
