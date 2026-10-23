/* Five Star Tours — progressive enhancements (no dependencies besides Bootstrap). */
(() => {
  'use strict';

  // Navbar: solid background once the page is scrolled.
  const nav = document.querySelector('.site-nav');
  const backToTop = document.querySelector('.back-to-top');
  const onScroll = () => {
    const y = window.scrollY;
    nav?.classList.toggle('scrolled', y > 24);
    backToTop?.classList.toggle('show', y > 600);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // The transparent home navbar needs a background while the mobile menu is open.
  document.getElementById('mainNav')?.addEventListener('show.bs.collapse', () => nav?.classList.add('scrolled'));
  document.getElementById('mainNav')?.addEventListener('hidden.bs.collapse', onScroll);

  backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  // Reveal-on-scroll animations.
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -60px 0px' });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('in'));
  }

  // Auto-dismiss success/info flash messages, pausing while hovered or focused.
  document.querySelectorAll('.flash-stack .alert-success, .flash-stack .alert-info').forEach((el) => {
    if (el.querySelector('a')) return; // keep messages that contain a link
    let timer;
    const start = () => { timer = setTimeout(() => bootstrap.Alert.getOrCreateInstance(el).close(), 8000); };
    const stop = () => clearTimeout(timer);
    el.addEventListener('mouseenter', stop);
    el.addEventListener('focusin', stop);
    el.addEventListener('mouseleave', start);
    el.addEventListener('focusout', start);
    start();
  });

  // Confirmation prompts for destructive actions: <form data-confirm="...">
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });

  // Client-side validation styling (server-side validation always runs too).
  document.querySelectorAll('form.needs-validation').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });

  // Prevent double submissions: once a POST form is really being sent,
  // disable its submit button. Runs after validation/confirm handlers.
  document.querySelectorAll('form[method="post" i]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (event.defaultPrevented) return;
      if (form.dataset.submitting) {
        event.preventDefault();
        return;
      }
      form.dataset.submitting = '1';
      const button = event.submitter || form.querySelector('[type="submit"]');
      if (button) {
        // Deferred so the clicked button's name/value is still part of this submission.
        setTimeout(() => {
          button.disabled = true;
          button.setAttribute('aria-busy', 'true');
          if (button.textContent.trim().length > 2) {
            button.dataset.label = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Please wait…';
          }
        }, 0);
      }
    });
  });

  // Re-enable buttons if the page is restored from the back/forward cache.
  window.addEventListener('pageshow', (event) => {
    if (!event.persisted) return;
    document.querySelectorAll('form[data-submitting]').forEach((form) => {
      delete form.dataset.submitting;
      form.querySelectorAll('[aria-busy="true"]').forEach((b) => {
        b.disabled = false;
        b.removeAttribute('aria-busy');
        if (b.dataset.label) b.innerHTML = b.dataset.label;
      });
    });
  });

  // Show / hide password fields.
  document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.passwordToggle);
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });

  // Password confirmation must match.
  document.querySelectorAll('[data-match]').forEach((confirmInput) => {
    const original = document.getElementById(confirmInput.dataset.match);
    const check = () => confirmInput.setCustomValidity(confirmInput.value === original.value ? '' : 'Passwords do not match');
    confirmInput.addEventListener('input', check);
    original.addEventListener('input', check);
  });

  // Registration: live email availability check.
  const emailInput = document.querySelector('[data-check-email]');
  if (emailInput) {
    const feedback = document.getElementById('email-availability');
    emailInput.addEventListener('blur', async () => {
      const value = emailInput.value.trim();
      feedback.textContent = '';
      emailInput.setCustomValidity('');
      if (!value || !emailInput.validity.valid) return;
      try {
        const res = await fetch(`check_availability.php?email=${encodeURIComponent(value)}`, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!data.available) {
          emailInput.setCustomValidity('Email already registered');
          feedback.innerHTML = 'This email is already registered. <a href="login.php">Sign in instead?</a>';
          feedback.className = 'form-text text-danger';
        } else {
          feedback.textContent = 'Email is available.';
          feedback.className = 'form-text text-success';
        }
      } catch {
        /* Network errors are ignored — the server validates again on submit. */
      }
    });
  }

  // Booking form: keep dates consistent and show an estimated total.
  const booking = document.getElementById('booking-form');
  if (booking) {
    const from = booking.querySelector('[name="fromdate"]');
    const to = booking.querySelector('[name="todate"]');
    const travellers = booking.querySelector('[name="travellers"]');
    const total = document.getElementById('booking-total');
    const price = Number(booking.dataset.price || 0);
    const money = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 });

    const maxDays = Number(booking.dataset.maxDays || 30);
    const update = () => {
      if (from.value) {
        to.min = from.value;
        const last = new Date(`${from.value}T00:00:00`);
        last.setDate(last.getDate() + maxDays);
        to.max = `${last.getFullYear()}-${String(last.getMonth() + 1).padStart(2, '0')}-${String(last.getDate()).padStart(2, '0')}`;
        if (to.value && to.value < from.value) to.value = from.value;
      }
      const count = Math.max(1, Number(travellers.value) || 1);
      total.textContent = `USD ${money.format(price * count)}`;
    };
    [from, to, travellers].forEach((el) => el.addEventListener('input', update));
    update();
  }

  // Gallery lightbox with keyboard navigation.
  const lightboxEl = document.getElementById('lightbox');
  if (lightboxEl) {
    const items = [...document.querySelectorAll('.gallery-item')];
    const img = lightboxEl.querySelector('img');
    const caption = lightboxEl.querySelector('.lightbox-caption');
    const modal = new bootstrap.Modal(lightboxEl);
    let index = 0;

    const show = (i) => {
      index = (i + items.length) % items.length;
      img.src = items[index].dataset.full;
      img.alt = items[index].querySelector('img').alt;
      caption.textContent = img.alt;
    };
    items.forEach((item, i) => item.addEventListener('click', () => { show(i); modal.show(); }));
    lightboxEl.querySelector('.prev').addEventListener('click', () => show(index - 1));
    lightboxEl.querySelector('.next').addEventListener('click', () => show(index + 1));
    lightboxEl.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') show(index - 1);
      if (e.key === 'ArrowRight') show(index + 1);
    });
  }
})();
