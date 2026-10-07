(function () {
  'use strict';

  const carousel = document.querySelector('[data-homepage-carousel]');
  if (!carousel) return;

  const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
  const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
  if (!slides.length) return;

  carousel.classList.add('ssz-carousel--js');
  if (slides.length < 2) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const interval = Math.max(4000, Number.parseInt(carousel.dataset.carouselInterval || '6000', 10) || 6000);
  let active = 0;
  let timer = null;
  let paused = false;
  let pointerStart = null;

  const setSlide = (nextIndex) => {
    active = (nextIndex + slides.length) % slides.length;
    slides.forEach((slide, index) => {
      const isActive = index === active;
      slide.hidden = !isActive;
      slide.classList.toggle('is-active', isActive);
      slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
    });
    dots.forEach((dot, index) => {
      const isActive = index === active;
      dot.classList.toggle('is-active', isActive);
      dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });
  };

  const stop = () => {
    if (timer) window.clearInterval(timer);
    timer = null;
  };

  const start = () => {
    stop();
    if (!paused && !reducedMotion.matches) {
      timer = window.setInterval(() => setSlide(active + 1), interval);
    }
  };

  const manual = (index) => {
    setSlide(index);
    start();
  };

  dots.forEach((dot) => {
    dot.addEventListener('click', () => manual(Number.parseInt(dot.dataset.slideTo || '0', 10)));
  });

  carousel.addEventListener('pointerenter', () => { paused = true; stop(); });
  carousel.addEventListener('pointerleave', () => { paused = false; start(); });
  carousel.addEventListener('focusin', () => { paused = true; stop(); });
  carousel.addEventListener('focusout', (event) => {
    if (!carousel.contains(event.relatedTarget)) {
      paused = false;
      start();
    }
  });
  document.addEventListener('visibilitychange', () => {
    paused = document.hidden;
    if (paused) stop(); else start();
  });
  if (typeof reducedMotion.addEventListener === 'function') reducedMotion.addEventListener('change', start);
  else if (typeof reducedMotion.addListener === 'function') reducedMotion.addListener(start);

  carousel.addEventListener('pointerdown', (event) => {
    if (event.pointerType === 'mouse' && event.button !== 0) return;
    pointerStart = { x: event.clientX, y: event.clientY };
  });
  carousel.addEventListener('pointerup', (event) => {
    if (!pointerStart) return;
    const deltaX = event.clientX - pointerStart.x;
    const deltaY = event.clientY - pointerStart.y;
    pointerStart = null;
    if (Math.abs(deltaX) < 48 || Math.abs(deltaX) <= Math.abs(deltaY)) return;
    manual(active + (deltaX < 0 ? 1 : -1));
  });
  carousel.addEventListener('pointercancel', () => { pointerStart = null; });

  setSlide(0);
  start();
}());
