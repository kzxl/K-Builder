/**
 * KBuilder frontend runtime.
 * Nhẹ, không phụ thuộc, chạy trên trang công khai.
 */
(function () {
  'use strict';

  // base_url được nhúng vào <html data-base-url="..."> bởi base.twig
  var root = document.documentElement;
  window.kbuilder_base_url = root.getAttribute('data-base-url') || '';

  function onReady(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  // ── Lazy-load ảnh ────────────────────────────────────────────────
  function initLazyImages() {
    var imgs = document.querySelectorAll('img[data-src], img[loading="lazy"]');
    if (!imgs.length) return;

    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          observer.unobserve(img);
        });
      }, { rootMargin: '200px' });

      imgs.forEach(function (img) { io.observe(img); });
    } else {
      // Fallback: tải ngay
      imgs.forEach(function (img) {
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
        }
      });
    }
  }

  // ── Smooth scroll cho anchor nội bộ ─────────────────────────────
  function initSmoothScroll() {
    document.addEventListener('click', function (e) {
      var link = e.target.closest('a[href^="#"]');
      if (!link) return;
      var id = link.getAttribute('href');
      if (id.length <= 1) return;
      var target = document.querySelector(id);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  // ── Mobile menu toggle (nếu có nút .kb-nav-toggle) ──────────────
  function initMobileMenu() {
    var toggle = document.querySelector('.kb-nav-toggle');
    var links = document.querySelector('.kb-nav-links');
    if (toggle && links) {
      toggle.addEventListener('click', function () {
        links.classList.toggle('is-open');
      });
    }
  }

  onReady(function () {
    initLazyImages();
    initSmoothScroll();
    initMobileMenu();
  });
})();
