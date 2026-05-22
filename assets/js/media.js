(function () {
  var frames = document.querySelectorAll('[data-image-frame]');
  var lightbox;

  function ensureLightbox() {
    if (lightbox) {
      return lightbox;
    }

    lightbox = document.createElement('div');
    lightbox.className = 'image-lightbox';
    lightbox.innerHTML = '<button type="button" class="image-lightbox-close" aria-label="Close image">×</button><img alt=""><p></p>';
    document.body.appendChild(lightbox);

    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox || event.target.classList.contains('image-lightbox-close')) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeLightbox();
      }
    });

    return lightbox;
  }

  function openLightbox(src, alt, sizeText) {
    var box = ensureLightbox();
    var image = box.querySelector('img');
    var caption = box.querySelector('p');
    image.src = src;
    image.alt = alt || '';
    caption.textContent = sizeText || 'Original image';
    box.classList.add('is-open');
    document.body.classList.add('overflow-hidden');
  }

  function closeLightbox() {
    if (!lightbox) {
      return;
    }
    lightbox.classList.remove('is-open');
    document.body.classList.remove('overflow-hidden');
  }

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
    var useLightbox = frame.hasAttribute('data-lightbox');

    if (!image) {
      return;
    }

    if (frame.tagName === 'A' && !useLightbox) {
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
      hint.textContent = useLightbox ? 'View larger' : 'Open original';
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

    if (useLightbox) {
      frame.addEventListener('click', function (event) {
        event.preventDefault();
        var src = image.currentSrc || image.src || frame.getAttribute('href');
        var sizeText = image.naturalWidth && image.naturalHeight
          ? image.naturalWidth + ' × ' + image.naturalHeight + ' px'
          : '';
        openLightbox(src, image.alt, sizeText);
      });
    }
  });
}());
