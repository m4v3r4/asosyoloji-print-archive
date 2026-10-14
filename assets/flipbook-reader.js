import * as pdfjsLib from './vendor/pdfjs/pdf.min.js';
import { PageFlip } from './vendor/page-flip/page-flip.module.js';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
  './vendor/pdfjs/pdf.worker.min.js',
  import.meta.url
).href;

const renderFlipbook = async (root) => {
  const pdfUrl = root.dataset.pdfUrl;
  const stage = root.querySelector('[data-asoprint-stage]');
  const book = root.querySelector('[data-asoprint-book]');
  const loading = root.querySelector('[data-asoprint-loading]');
  const loadingText = root.querySelector('[data-asoprint-loading-text]');
  const counter = root.querySelector('[data-asoprint-counter]');
  const previous = root.querySelector('[data-asoprint-prev]');
  const next = root.querySelector('[data-asoprint-next]');
  const fullscreen = root.querySelector('[data-asoprint-fullscreen]');
  const thumbnails = root.querySelector('[data-asoprint-thumbnails]');
  const zoomOut = root.querySelector('[data-asoprint-zoom-out]');
  const zoomReset = root.querySelector('[data-asoprint-zoom-reset]');
  const zoomIn = root.querySelector('[data-asoprint-zoom-in]');
  const renderedPages = new Map();
  const renderedThumbnails = new Map();
  const storageKey = `asoprint:last-page:${window.location.pathname}:${pdfUrl}`;
  const zoomLevels = [1, 1.25, 1.5, 1.75, 2];
  let zoomIndex = 0;

  const readSavedPage = (pageCount) => {
    try {
      const stored = Number.parseInt(window.localStorage.getItem(storageKey) || '0', 10);
      return Number.isFinite(stored) ? Math.min(Math.max(stored, 0), pageCount - 1) : 0;
    } catch (error) {
      return 0;
    }
  };

  const savePage = (pageIndex) => {
    try {
      window.localStorage.setItem(storageKey, String(pageIndex));
    } catch (error) {
      // Storage can be disabled; reading remains fully functional.
    }
  };

  const setError = () => {
    root.classList.add('is-error');
    loadingText.textContent = 'Önizleme yüklenemedi. PDF bağlantısından belgeyi açabilirsiniz.';
    loading.querySelector('.asoprint-flipbook__spinner')?.remove();
  };

  try {
    const pdf = await pdfjsLib.getDocument({ url: pdfUrl }).promise;
    const pages = [];
    const thumbnailButtons = [];

    for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
      const page = document.createElement('div');
      page.className = 'asoprint-flipbook__page';
      page.dataset.pageNumber = String(pageNumber);

      if (pageNumber === 1 || pageNumber === pdf.numPages) {
        page.dataset.density = 'hard';
        page.classList.add('asoprint-flipbook__page--cover');
      }

      const placeholder = document.createElement('span');
      placeholder.className = 'asoprint-flipbook__placeholder';
      placeholder.textContent = `Sayfa ${pageNumber}`;
      page.append(placeholder);
      book.append(page);
      pages.push(page);

      const thumbnailButton = document.createElement('button');
      thumbnailButton.type = 'button';
      thumbnailButton.className = 'asoprint-flipbook__thumbnail';
      thumbnailButton.dataset.pageIndex = String(pageNumber - 1);
      thumbnailButton.setAttribute('aria-label', `Sayfa ${pageNumber}`);
      const thumbnailPlaceholder = document.createElement('span');
      thumbnailPlaceholder.textContent = String(pageNumber);
      thumbnailButton.append(thumbnailPlaceholder);
      thumbnails.append(thumbnailButton);
      thumbnailButtons.push(thumbnailButton);
    }

    const renderPage = (pageNumber) => {
      if (pageNumber < 1 || pageNumber > pdf.numPages) {
        return Promise.resolve();
      }

      if (renderedPages.has(pageNumber)) {
        return renderedPages.get(pageNumber);
      }

      const task = pdf.getPage(pageNumber).then((pdfPage) => {
        const initialViewport = pdfPage.getViewport({ scale: 1 });
        const scale = Math.min(2.2, Math.max(1.25, 1100 / initialViewport.width));
        const viewport = pdfPage.getViewport({ scale });
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d', { alpha: false });

        canvas.width = Math.floor(viewport.width);
        canvas.height = Math.floor(viewport.height);
        canvas.setAttribute('aria-label', `Sayfa ${pageNumber}`);

        return pdfPage.render({ canvasContext: context, viewport }).promise.then(() => {
          pages[pageNumber - 1].replaceChildren(canvas);
          pages[pageNumber - 1].classList.add('is-rendered');
        });
      }).catch(() => {
        pages[pageNumber - 1].classList.add('has-render-error');
      });

      renderedPages.set(pageNumber, task);
      return task;
    };

    const renderThumbnail = (pageNumber) => {
      if (pageNumber < 1 || pageNumber > pdf.numPages) {
        return Promise.resolve();
      }
      if (renderedThumbnails.has(pageNumber)) {
        return renderedThumbnails.get(pageNumber);
      }

      const task = pdf.getPage(pageNumber).then((pdfPage) => {
        const viewport = pdfPage.getViewport({ scale: 0.18 });
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d', { alpha: false });
        canvas.width = Math.max(1, Math.floor(viewport.width));
        canvas.height = Math.max(1, Math.floor(viewport.height));
        canvas.setAttribute('aria-hidden', 'true');
        return pdfPage.render({ canvasContext: context, viewport }).promise.then(() => {
          thumbnailButtons[pageNumber - 1].replaceChildren(canvas);
          thumbnailButtons[pageNumber - 1].classList.add('is-rendered');
        });
      }).catch(() => {
        thumbnailButtons[pageNumber - 1].classList.add('has-render-error');
      });

      renderedThumbnails.set(pageNumber, task);
      return task;
    };

    const savedPageIndex = readSavedPage(pdf.numPages);
    await Promise.all(
      [0, 1, 2, savedPageIndex - 1, savedPageIndex, savedPageIndex + 1]
        .filter((index) => index >= 0 && index < pdf.numPages)
        .map((index) => renderPage(index + 1))
    );

    const pageFlip = new PageFlip(book, {
      width: 520,
      height: 693,
      size: 'stretch',
      minWidth: 245,
      maxWidth: 560,
      minHeight: 327,
      maxHeight: 747,
      drawShadow: true,
      flippingTime: 850,
      usePortrait: true,
      startZIndex: 10,
      autoSize: true,
      maxShadowOpacity: 0.48,
      showCover: true,
      mobileScrollSupport: false,
      swipeDistance: 20,
      clickEventForward: true,
      useMouseEvents: true,
      showPageCorners: true
    });

    const updateControls = (pageIndex = pageFlip.getCurrentPageIndex()) => {
      const displayedPage = Math.min(pageIndex + 1, pdf.numPages);
      counter.textContent = `${displayedPage} / ${pdf.numPages}`;
      previous.disabled = pageIndex <= 0;
      next.disabled = pageIndex >= pdf.numPages - 1;
      [pageIndex - 1, pageIndex, pageIndex + 1, pageIndex + 2, pageIndex + 3]
        .forEach((index) => renderPage(index + 1));
      thumbnailButtons.forEach((button, index) => {
        const active = index === pageIndex;
        button.classList.toggle('is-active', active);
        if (active) {
          button.setAttribute('aria-current', 'page');
          button.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
          button.removeAttribute('aria-current');
        }
      });
      savePage(pageIndex);
    };

    pageFlip.loadFromHTML(pages);
    pageFlip.on('flip', (event) => updateControls(Number(event.data) || 0));
    pageFlip.on('changeOrientation', () => updateControls());
    previous.addEventListener('click', () => pageFlip.flipPrev());
    next.addEventListener('click', () => pageFlip.flipNext());
    thumbnailButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const pageIndex = Number.parseInt(button.dataset.pageIndex || '0', 10);
        renderPage(pageIndex + 1).then(() => pageFlip.turnToPage(pageIndex));
      });
    });

    if ('IntersectionObserver' in window) {
      const thumbnailObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          renderThumbnail(Number.parseInt(entry.target.dataset.pageIndex || '0', 10) + 1);
          observer.unobserve(entry.target);
        });
      }, { root: thumbnails, rootMargin: '120px' });
      thumbnailButtons.forEach((button) => thumbnailObserver.observe(button));
    } else {
      thumbnailButtons.slice(0, 12).forEach((button) => renderThumbnail(Number(button.dataset.pageIndex) + 1));
    }

    const updateZoom = () => {
      const zoom = zoomLevels[zoomIndex];
      book.style.setProperty('--asoprint-zoom', String(zoom));
      stage.classList.toggle('is-zoomed', zoom > 1);
      zoomReset.textContent = `${Math.round(zoom * 100)}%`;
      zoomOut.disabled = zoomIndex === 0;
      zoomIn.disabled = zoomIndex === zoomLevels.length - 1;
    };
    zoomOut.addEventListener('click', () => {
      zoomIndex = Math.max(0, zoomIndex - 1);
      updateZoom();
    });
    zoomReset.addEventListener('click', () => {
      zoomIndex = 0;
      updateZoom();
    });
    zoomIn.addEventListener('click', () => {
      zoomIndex = Math.min(zoomLevels.length - 1, zoomIndex + 1);
      updateZoom();
    });
    stage.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        pageFlip.flipPrev();
      }
      if (event.key === 'ArrowRight') {
        event.preventDefault();
        pageFlip.flipNext();
      }
      if (event.key === '+' || event.key === '=') {
        event.preventDefault();
        zoomIndex = Math.min(zoomLevels.length - 1, zoomIndex + 1);
        updateZoom();
      }
      if (event.key === '-') {
        event.preventDefault();
        zoomIndex = Math.max(0, zoomIndex - 1);
        updateZoom();
      }
    });

    if (!document.fullscreenEnabled) {
      fullscreen.hidden = true;
    } else {
      fullscreen.addEventListener('click', async () => {
        if (document.fullscreenElement === root) {
          await document.exitFullscreen();
        } else {
          await root.requestFullscreen();
        }
      });
      document.addEventListener('fullscreenchange', () => {
        const isFullscreen = document.fullscreenElement === root;
        root.classList.toggle('is-fullscreen', isFullscreen);
        fullscreen.textContent = isFullscreen ? 'Tam ekrandan çık' : 'Tam ekran';
        window.setTimeout(() => pageFlip.update(), 120);
      });
    }

    root.classList.add('is-ready');
    loading.setAttribute('aria-hidden', 'true');
    if (savedPageIndex > 0) {
      pageFlip.turnToPage(savedPageIndex);
    }
    updateZoom();
    updateControls(savedPageIndex);
  } catch (error) {
    setError();
    console.error('Asosyoloji PDF okuyucu:', error);
  }
};

document.querySelectorAll('[data-asoprint-flipbook]').forEach((root) => {
  renderFlipbook(root);
});
