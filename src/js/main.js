/**
 * MWS theme — page behaviour.
 *
 * Progressive enhancement only: everything still works with JS disabled.
 *
 * Swiper is provided as a global by dist/js/vendor.min.js, which is
 * enqueued ahead of this file (see functions.php). Both are bundled from
 * node_modules by gulp — nothing is loaded from a CDN.
 */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ── Mobile navigation ─────────────────────────────────────────────── */
  var toggle = document.querySelector('[data-nav-toggle]');
  var nav    = document.getElementById('nav');
  var HIDDEN = ['invisible', 'opacity-0', '-translate-y-2'];

  function setNav(open) {
    if (!toggle || !nav) return;
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    toggle.classList.toggle('is-open', open);
    HIDDEN.forEach(function (cls) { nav.classList.toggle(cls, !open); });
    document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 1024);
  }

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      setNav(toggle.getAttribute('aria-expanded') !== 'true');
    });

    nav.addEventListener('click', function (event) {
      if (event.target.closest('a') && window.innerWidth < 1024) setNav(false);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') setNav(false);
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1024) setNav(false);
    });
  }

  /* ── Sticky header + back-to-top button ────────────────────────────────
     `.is-stuck` frosts and compresses the header once the page has scrolled
     past the point where it would otherwise float unexplained over the
     content — the styling for it lives in the premium layer of main.css.

     The threshold is hysteretic (40 on, 10 off) so a header sitting exactly
     on the boundary cannot flicker between the two states. */
  var header = document.querySelector('[data-header]');
  var toTop  = document.querySelector('[data-to-top]');
  var stuck  = false;

  /* The button used to be a plain link to #top. #top is the header, which is
     `sticky top-0` and therefore always in the viewport — so the browser had
     nothing to scroll to and the click did nothing. Scroll the document
     explicitly instead; the href stays as a no-JS fallback. */
  if (toTop) {
    toTop.addEventListener('click', function (event) {
      event.preventDefault();
      window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }

  function onScroll() {
    var y = window.scrollY;

    if (header) {
      if (!stuck && y > 40)      { stuck = true;  header.classList.add('is-stuck'); }
      else if (stuck && y < 10)  { stuck = false; header.classList.remove('is-stuck'); }
    }

    if (toTop) {
      var show = y > 600;
      ['invisible', 'opacity-0', 'translate-y-3'].forEach(function (cls) {
        toTop.classList.toggle(cls, !show);
      });
    }
  }

  var ticking = false;
  window.addEventListener('scroll', function () {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(function () { onScroll(); ticking = false; });
  }, { passive: true });
  onScroll();

  /* ── Estimate modal ────────────────────────────────────────────────────
     The site's conversion path. Opened by every banner's button, and by any
     link still pointing at the old #booking anchor — those live in
     inc/config.php in a dozen places, so intercepting them here is far less
     brittle than rewriting each one.

     The Jobber iframe carries its URL in data-src rather than src, so a
     third-party form embedded on every page costs nothing until someone
     actually opens it. */
  var modal = document.querySelector('[data-estimate-modal]');

  if (modal) {
    var lastFocused = null;
    var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), ' +
                    'select:not([disabled]), textarea:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])';

    var loadFrame = function () {
      var frame = modal.querySelector('[data-estimate-frame]');
      if (frame && !frame.getAttribute('src') && frame.dataset.src) {
        frame.setAttribute('src', frame.dataset.src);
      }
    };

    var openModal = function () {
      lastFocused = document.activeElement;
      modal.hidden = false;
      loadFrame();

      // Next frame, so the transition has a starting state to animate from.
      window.requestAnimationFrame(function () {
        modal.classList.add('is-open');
      });

      document.body.classList.add('overflow-hidden');

      var first = modal.querySelector(FOCUSABLE);
      if (first) first.focus();
    };

    var closeModal = function () {
      modal.classList.remove('is-open');
      document.body.classList.remove('overflow-hidden');

      // Wait out the transition before hiding, otherwise it snaps shut.
      window.setTimeout(function () {
        modal.hidden = true;
      }, reduceMotion ? 0 : 400);

      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    };

    document.addEventListener('click', function (event) {
      var opener = event.target.closest('[data-estimate-open], a[href="#booking"], a[href="#estimate"]');
      if (opener) {
        event.preventDefault();
        openModal();
        return;
      }

      if (event.target.closest('[data-estimate-close]')) {
        event.preventDefault();
        closeModal();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (modal.hidden) return;

      if (event.key === 'Escape') {
        closeModal();
        return;
      }

      // Focus trap: keep Tab inside the dialog while it is open.
      if (event.key !== 'Tab') return;

      var items = Array.prototype.filter.call(
        modal.querySelectorAll(FOCUSABLE),
        function (el) { return el.offsetParent !== null || el.tagName === 'IFRAME'; }
      );
      if (!items.length) return;

      var first = items[0];
      var last  = items[items.length - 1];

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });

    // Coming back from a successful fallback submission — show the confirmation.
    if (/[?&]estimate=(sent|error)/.test(window.location.search)) {
      openModal();
    }
  }

  /* ── Promotion pop-up ──────────────────────────────────────────────────
     Opens itself a beat after load, then stays shut for the rest of the
     session. sessionStorage rather than localStorage is deliberate: an offer
     that never returns is as bad as one that nags — this shows again on the
     visitor's next visit, but not on every page of this one.

     It also refuses to open while the estimate modal is up, so a visitor who
     went straight for the form is never interrupted by an advert. */
  var promo = document.querySelector('[data-promo-modal]');

  if (promo) {
    var PROMO_KEY = 'mws-promo-dismissed';
    var promoSeen = false;

    try { promoSeen = window.sessionStorage.getItem(PROMO_KEY) === '1'; } catch (e) { promoSeen = false; }

    var closePromo = function () {
      promo.classList.remove('is-open');
      document.body.classList.remove('overflow-hidden');
      window.setTimeout(function () { promo.hidden = true; }, reduceMotion ? 0 : 400);

      try { window.sessionStorage.setItem(PROMO_KEY, '1'); } catch (e) { /* private mode — just skip */ }
    };

    var openPromo = function () {
      if (promo.classList.contains('is-open')) return;
      if (modal && !modal.hidden) return;   // estimate form already open

      promo.hidden = false;
      window.requestAnimationFrame(function () { promo.classList.add('is-open'); });
      document.body.classList.add('overflow-hidden');

      var close = promo.querySelector('[data-promo-close]');
      if (close) close.focus();
    };

    if (!promoSeen) {
      window.setTimeout(openPromo, parseInt(promo.dataset.promoDelay, 10) || 1200);
    }

    document.addEventListener('click', function (event) {
      if (event.target.closest('[data-promo-close]')) {
        event.preventDefault();
        closePromo();
        return;
      }

      /* Anything that opens the estimate form should take the pop-up down with
         it, otherwise the two stack. */
      if (!promo.hidden && event.target.closest('[data-estimate-open], a[href="#booking"], a[href="#estimate"]')) {
        closePromo();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !promo.hidden) closePromo();
    });
  }

  /* ── Contact Form 7: redirect after a successful send ──────────────────
     CF7 removed on_sent_ok, so `wpcf7mailsent` is the supported hook. The
     destination comes off the wrapper's data attribute rather than being
     hard-coded here, so each form can land somewhere different.

     Only fires on mailsent — a validation failure or a mail error leaves the
     visitor on the form with CF7's own message, which is what should happen. */
  document.addEventListener('wpcf7mailsent', function (event) {
    var wrap = event.target.closest('[data-cf7-redirect]');
    var to   = wrap && wrap.dataset.cf7Redirect;

    if (!to) return;

    /* A beat so the visitor sees CF7's success state before the page changes —
       without it a submit reads as an unexplained navigation. */
    window.setTimeout(function () { window.location.assign(to); }, 600);
  });

  /* ── Sliders (Swiper) ──────────────────────────────────────────────── */
  if (typeof Swiper === 'function') {

    /* Banner: crossfading slides, autoplaying until the visitor takes over. */
    document.querySelectorAll('[data-hero]').forEach(function (hero) {
      var el = hero.querySelector('[data-hero-swiper]');
      if (!el || el.querySelectorAll('.swiper-slide').length < 2) return;

      var controls = hero.querySelector('[data-hero-controls]');
      if (controls) controls.hidden = false;

      var banner = new Swiper(el, {
        loop: true,
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: reduceMotion ? 0 : 900,
        autoplay: reduceMotion ? false : {
          delay: 6000,
          disableOnInteraction: false,
          pauseOnMouseEnter: true
        },
        navigation: {
          prevEl: hero.querySelector('[data-hero-prev]'),
          nextEl: hero.querySelector('[data-hero-next]')
        },
        pagination: {
          el: hero.querySelector('[data-hero-dots]'),
          clickable: true
        },
        keyboard: { enabled: true, onlyInViewport: true },
        a11y: {
          containerRoleDescriptionMessage: 'Banner slideshow',
          prevSlideMessage: 'Previous slide',
          nextSlideMessage: 'Next slide',
          paginationBulletMessage: 'Go to slide {{index}}'
        }
      });

      // Swiper pauses on hover and on tab-blur, but not while the booking form
      // inside the banner has focus — hold the rotation there too.
      if (banner.autoplay) {
        hero.addEventListener('focusin', function () { banner.autoplay.stop(); });
        hero.addEventListener('focusout', function () { banner.autoplay.start(); });
      }
    });

    /* Before & after: one matched pair per slide. */
    document.querySelectorAll('[data-ba-swiper]').forEach(function (el) {
      if (el.querySelectorAll('.swiper-slide').length < 2) return;

      new Swiper(el, {
        slidesPerView: 1,
        spaceBetween: 24,
        grabCursor: true,
        speed: reduceMotion ? 0 : 500,
        pagination: {
          el: el.parentElement.querySelector('[data-ba-dots]'),
          clickable: true
        },
        keyboard: { enabled: true, onlyInViewport: true },
        a11y: { paginationBulletMessage: 'Show before and after pair {{index}}' }
      });
    });

    /* Services: 1 → 2 → 3 cards per view, matching the old scroll-snap widths. */
    document.querySelectorAll('[data-services-swiper]').forEach(function (el) {
      new Swiper(el, {
        slidesPerView: 1.15,
        spaceBetween: 24,
        grabCursor: true,
        speed: reduceMotion ? 0 : 500,
        breakpoints: {
          640:  { slidesPerView: 2 },
          1024: { slidesPerView: 3 }
        },
        pagination: {
          el: el.parentElement.querySelector('[data-services-dots]'),
          clickable: true
        },
        keyboard: { enabled: true, onlyInViewport: true },
        a11y: { paginationBulletMessage: 'Go to services page {{index}}' }
      });
    });
  }

  /* ── Journal table of contents ────────────────────────────────────────
     The links work as plain anchors on their own; this only marks which
     section the reader is in. Sections are tracked by watching a band across
     the upper part of the viewport — the heading nearest the top of that band
     is the current one, which keeps the marker steady while a long section
     scrolls past instead of flickering between neighbours. */
  var toc = document.querySelector('[data-toc]');

  if (toc && 'IntersectionObserver' in window) {
    var tocLinks = Array.prototype.slice.call(toc.querySelectorAll('[data-toc-link]'));
    var byId     = {};
    var headings = [];

    tocLinks.forEach(function (link) {
      var id      = decodeURIComponent(link.hash.slice(1));
      var heading = id && document.getElementById(id);

      if (!heading) return;
      byId[id] = link;
      headings.push(heading);
    });

    var visible = [];

    var markActive = function () {
      /* Past the last heading nothing intersects the band, so the previous
         choice is left standing rather than clearing the rail entirely. */
      if (!visible.length) return;

      var current = visible.reduce(function (top, heading) {
        return heading.offsetTop < top.offsetTop ? heading : top;
      });

      tocLinks.forEach(function (link) {
        link.classList.toggle('is-active', link === byId[current.id]);
      });
    };

    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var at = visible.indexOf(entry.target);

        if (entry.isIntersecting && at === -1) visible.push(entry.target);
        if (!entry.isIntersecting && at !== -1) visible.splice(at, 1);
      });

      markActive();
    }, { rootMargin: '-140px 0px -55% 0px' });

    headings.forEach(function (heading) { spy.observe(heading); });
  }

})();
