(function () {
  var frames = document.querySelectorAll('[data-image-frame]');

  function updateDimensions(frame, image) {
    var badge = frame.querySelector('[data-image-dimensions]');
    if (!badge || !image.naturalWidth || !image.naturalHeight) {
      return;
    }
    badge.textContent = image.naturalWidth + ' × ' + image.naturalHeight + ' px';
  }

  frames.forEach(function (frame) {
    var image = frame.querySelector('img');
    var hint = frame.querySelector('[data-image-hint]');
    var badge = frame.querySelector('[data-image-dimensions]');

    if (!image) {
      return;
    }

    if (frame.tagName === 'A') {
      if (!frame.getAttribute('href')) {
        frame.setAttribute('href', image.currentSrc || image.src);
      }
      frame.setAttribute('target', '_blank');
      frame.setAttribute('rel', 'noopener noreferrer');
      if (!frame.getAttribute('aria-label')) {
        frame.setAttribute('aria-label', 'Open original image in a new tab');
      }
    }

    if (!hint) {
      hint = document.createElement('span');
      hint.className = 'image-open-hint';
      hint.setAttribute('data-image-hint', '');
      hint.setAttribute('aria-hidden', 'true');
      hint.textContent = 'Open original';
      frame.appendChild(hint);
    }

    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'image-dimension-badge';
      badge.setAttribute('data-image-dimensions', '');
      badge.setAttribute('aria-hidden', 'true');
      badge.textContent = 'Loading size';
      frame.appendChild(badge);
    }

    if (image.complete) {
      updateDimensions(frame, image);
    } else {
      image.addEventListener('load', function () {
        updateDimensions(frame, image);
      }, { once: true });
    }
  });
}());
