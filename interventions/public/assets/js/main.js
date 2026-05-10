/**
 * CityZen - Main JavaScript
 * Gestion UI, AJAX, animations
 */

document.addEventListener('DOMContentLoaded', function () {

  /* ─── Navbar Scroll Effect ─── */
  const navbar = document.getElementById('mainNavbar');
  const headerStack = document.querySelector('.interventions-header-stack');
  if (headerStack || navbar) {
    const onScroll = () => {
      const y = window.scrollY > 12;
      if (headerStack) headerStack.classList.toggle('is-scrolled', y);
      if (navbar) navbar.classList.toggle('scrolled', y);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ─── Mobile Nav Toggle ─── */
  const navToggle  = document.getElementById('navToggle');
  const navLinks   = document.getElementById('navLinks');
  const navActions = document.querySelector('.nav-actions');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      const open = navLinks.classList.toggle('open');
      if (navActions) navActions.classList.toggle('open', open);
      navToggle.classList.toggle('active', open);
    });
  }

  /* ─── User Dropdown ─── */
  const userDropBtn  = document.getElementById('userDropBtn');
  const userDropMenu = document.getElementById('userDropMenu');
  if (userDropBtn && userDropMenu) {
    userDropBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      userDropMenu.classList.toggle('show');
    });
    document.addEventListener('click', () => userDropMenu.classList.remove('show'));
  }

  /* ─── Flash Auto-dismiss ─── */
  const flashContainer = document.getElementById('flashContainer');
  if (flashContainer) {
    setTimeout(() => {
      flashContainer.style.opacity = '0';
      flashContainer.style.transform = 'translateX(100px)';
      flashContainer.style.transition = 'all 0.4s ease';
      setTimeout(() => flashContainer.remove(), 400);
    }, 5000);
  }

  /* ─── Intersection Observer (Reveal animations) ─── */
  const revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length > 0) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    revealEls.forEach(el => observer.observe(el));
  }

  /* ─── Counter Animation ─── */
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length > 0) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const target = parseInt(entry.target.dataset.count);
          animateCounter(entry.target, target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(c => counterObserver.observe(c));
  }

  function animateCounter(el, target) {
    let start = 0;
    const duration = 1800;
    const step = (timestamp) => {
      if (!start) start = timestamp;
      const progress = Math.min((timestamp - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * target).toLocaleString('fr-FR');
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }

  /* ─── File Upload Preview ─── */
  const fileInput   = document.getElementById('imageInput');
  const filePreview = document.getElementById('imagePreview');
  const fileUpload  = document.querySelector('.file-upload');
  if (fileInput && filePreview && fileUpload) {
    fileUpload.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (file) showPreview(file);
    });
    fileUpload.addEventListener('dragover', (e) => { e.preventDefault(); fileUpload.classList.add('dragging'); });
    fileUpload.addEventListener('dragleave', () => fileUpload.classList.remove('dragging'));
    fileUpload.addEventListener('drop', (e) => {
      e.preventDefault();
      fileUpload.classList.remove('dragging');
      const file = e.dataTransfer.files[0];
      if (file && file.type.startsWith('image/')) { fileInput.files = e.dataTransfer.files; showPreview(file); }
    });
    function showPreview(file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        filePreview.innerHTML = `<img src="${e.target.result}" style="max-height:200px;border-radius:12px;margin-top:12px;">`;
        fileUpload.querySelector('p').textContent = file.name;
      };
      reader.readAsDataURL(file);
    }
  }

  /* ─── AJAX Contact Form ─── */
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = contactForm.querySelector('[type="submit"]');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;display:inline-block;"></span> Envoi...';
      btn.disabled = true;

      try {
        const res = await fetch(contactForm.action, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(contactForm)
        });
        const data = await res.json();
        showToast(data.success ? 'success' : 'error', data.message);
        if (data.success) contactForm.reset();
      } catch {
        showToast('error', 'Erreur de connexion.');
      } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    });
  }

  /* ─── AJAX Signalement Filters ─── */
  const filterForm = document.getElementById('filterForm');
  if (filterForm) {
    const controls = filterForm.querySelectorAll('select, input');
    controls.forEach(c => c.addEventListener('change', () => filterForm.submit()));
  }

  /* ─── Suivi Search ─── */
  const suiviForm = document.getElementById('suiviSearchForm');
  if (suiviForm) {
    suiviForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const ref = document.getElementById('refInput').value.trim();
      if (!ref) return;
      window.location.href = suiviForm.action + '?reference=' + encodeURIComponent(ref);
    });
  }

  /* ─── Toast Utility ─── */
  function showToast(type, message) {
    const existing = document.getElementById('toastEl');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'toastEl';
    toast.className = 'flash-container';
    toast.innerHTML = `
      <div class="flash flash-${type}">
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'times-circle'}"></i>
        <span>${message}</span>
        <button class="flash-close" onclick="this.parentElement.parentElement.remove()">
          <i class="fas fa-times"></i>
        </button>
      </div>`;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100px)';
      toast.style.transition = 'all 0.4s ease';
      setTimeout(() => toast.remove(), 400);
    }, 4500);
  }

  /* ─── Active nav link highlight ─── */
  const currentPath = window.location.pathname;
  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && href !== '/' && currentPath.includes(href.split('/').pop())) {
      link.classList.add('active');
    }
  });

  /* ─── Smooth scroll for anchor links ─── */
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });

  /* ─── Auto-hide nav on mobile when link clicked ─── */
  document.querySelectorAll('.nav-link').forEach(l => {
    l.addEventListener('click', () => {
      if (navLinks) navLinks.classList.remove('open');
      if (navToggle) navToggle.classList.remove('active');
    });
  });

});
