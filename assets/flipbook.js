import * as pdfjsLib from './vendor/pdfjs/pdf.min.mjs';
import { PageFlip } from './vendor/page-flip/page-flip.module.js';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
  './vendor/pdfjs/pdf.worker.min.mjs',
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
  const renderedPages = new Map();

  const setError = () => {
    root.classList.add('is-error');
    loadingText.textContent = 'Önizleme yüklenemedi. PDF bağlantısından belgeyi açabilirsiniz.';
    loading.querySelector('.asoprint-flipbook__spinner')?.remove();
  };

  try {
    const pdf = await pdfjsLib.getDocument({ url: pdfUrl }).promise;
    const pages = [];

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

    await Promise.all([renderPage(1), renderPage(2), renderPage(3)]);

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
    };

    pageFlip.loadFromHTML(pages);
    pageFlip.on('flip', (event) => updateControls(Number(event.data) || 0));
    pageFlip.on('changeOrientation', () => updateControls());
    previous.addEventListener('click', () => pageFlip.flipPrev());
    next.addEventListener('click', () => pageFlip.flipNext());
    stage.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft') {
        event.preventDefault();
        pageFlip.flipPrev();
      }
      if (event.key === 'ArrowRight') {
        event.preventDefault();
        pageFlip.flipNext();
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
    updateControls(0);
  } catch (error) {
    setError();
    console.error('Asosyoloji PDF okuyucu:', error);
  }
};

document.querySelectorAll('[data-asoprint-flipbook]').forEach((root) => {
  renderFlipbook(root);
});
