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

  // Auto-dismiss success/info flash messages.
  document.querySelectorAll('.flash-stack .alert-success, .flash-stack .alert-info').forEach((el) => {
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(el).close(), 6000);
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

    const update = () => {
      if (from.value) {
        to.min = from.value;
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
