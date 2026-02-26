/* ============================================================
   Simple Scooters - Main JavaScript
   ============================================================ */

const App = {
  baseUrl: document.querySelector('meta[name="base-url"]')?.content || '',

  init() {
    this.initTheme();
    this.initSidebar();
    this.initToasts();
    this.initModals();
    this.initConfirmations();
    this.initAutoFilters();
    this.markActiveNav();
  },

  // ---- THEME ----
  initTheme() {
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);
    document.querySelectorAll('.theme-toggle').forEach(btn => {
      btn.addEventListener('click', () => this.toggleTheme());
      btn.textContent = theme === 'dark' ? '☀️' : '🌙';
    });
  },

  toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    document.querySelectorAll('.theme-toggle').forEach(btn => {
      btn.textContent = next === 'dark' ? '☀️' : '🌙';
    });
  },

  // ---- SIDEBAR ----
  initSidebar() {
    document.querySelector('.sidebar-toggle')?.addEventListener('click', () => {
      document.querySelector('.sidebar')?.classList.toggle('open');
    });

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 768) {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.sidebar-toggle');
        if (sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
          sidebar.classList.remove('open');
        }
      }
    });
  },

  // ---- ACTIVE NAV ----
  markActiveNav() {
    const path = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
      const href = link.getAttribute('href') || '';
      if (href && path.includes(href.split('/').filter(p => p.length > 2)[0] || '__')) {
        link.classList.add('active');
      }
    });
  },

  // ---- TOAST NOTIFICATIONS ----
  initToasts() {
    if (!document.querySelector('.toast-container')) {
      const tc = document.createElement('div');
      tc.className = 'toast-container';
      document.body.appendChild(tc);
    }
  },

  toast(message, type = 'success', duration = 4000) {
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const tc = document.querySelector('.toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
      <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
      <span class="toast-message">${message}</span>
      <span class="toast-close" onclick="this.parentElement.remove()">✕</span>
    `;
    tc.appendChild(toast);
    setTimeout(() => { toast.style.animation = 'slideInRight .3s reverse'; setTimeout(() => toast.remove(), 300); }, duration);
  },

  // ---- MODALS ----
  initModals() {
    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('[data-modal]');
      if (trigger) {
        const modalId = trigger.getAttribute('data-modal');
        this.openModal(modalId, trigger);
      }
      if (e.target.closest('.modal-overlay') && e.target === e.target.closest('.modal-overlay')) {
        this.closeAllModals();
      }
      if (e.target.closest('[data-modal-close]')) {
        this.closeAllModals();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.closeAllModals();
    });
  },

  openModal(id, triggerEl) {
    const modal = document.getElementById(id);
    if (!modal) return;

    // Populate edit data
    if (triggerEl?.dataset.id) {
      modal.querySelectorAll('[name]').forEach(field => {
        const key = field.getAttribute('name');
        if (triggerEl.dataset[key]) {
          field.value = triggerEl.dataset[key];
        }
      });
      // Update form action
      const form = modal.querySelector('form');
      if (form && form.dataset.actionTemplate) {
        form.action = form.dataset.actionTemplate.replace('{id}', triggerEl.dataset.id);
      }
    }

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  },

  closeAllModals() {
    document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
    document.body.style.overflow = '';
  },

  // ---- CONFIRM DIALOGS ----
  initConfirmations() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
      el.addEventListener('click', (e) => {
        const msg = el.getAttribute('data-confirm') || 'Are you sure?';
        if (!confirm(msg)) e.preventDefault();
      });
    });
  },

  // ---- AUTO SUBMIT FILTERS ----
  initAutoFilters() {
    document.querySelectorAll('[data-auto-submit]').forEach(el => {
      el.addEventListener('change', () => el.closest('form')?.submit());
    });
  },

  // ---- AJAX FORM SUBMIT ----
  async submitForm(form, onSuccess) {
    const btn = form.querySelector('[type="submit"]');
    const originalText = btn?.innerHTML;
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner" style="width:16px;height:16px;border-width:2px;display:inline-block;"></span> Saving...'; }
    try {
      const formData = new FormData(form);
      const response = await fetch(form.action, {
        method: form.method || 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await response.json();
      if (data.success) {
        this.toast(data.message || 'Saved successfully', 'success');
        if (onSuccess) onSuccess(data);
      } else {
        this.toast(data.message || 'An error occurred', 'error');
      }
    } catch (err) {
      this.toast('Network error. Please try again.', 'error');
    } finally {
      if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
    }
  },

  // ---- CURRENCY FORMAT ----
  formatCurrency(amount) {
    return '₹' + parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
  },

  // ---- SALE CALCULATOR ----
  initSaleCalculator() {
    const fields = ['unit_price', 'quantity', 'discount', 'tax_percent', 'amount_paid'];
    fields.forEach(id => {
      document.getElementById(id)?.addEventListener('input', () => this.calculateSale());
    });
    document.getElementById('inventory_id')?.addEventListener('change', function() {
      const option = this.options[this.selectedIndex];
      const price = option.dataset.price;
      if (price) {
        document.getElementById('unit_price').value = price;
        App.calculateSale();
      }
    });
  },

  calculateSale() {
    const qty      = parseFloat(document.getElementById('quantity')?.value || 1);
    const price    = parseFloat(document.getElementById('unit_price')?.value || 0);
    const discount = parseFloat(document.getElementById('discount')?.value || 0);
    const taxPct   = parseFloat(document.getElementById('tax_percent')?.value || 0);
    const paid     = parseFloat(document.getElementById('amount_paid')?.value || 0);

    const subtotal = (price * qty) - discount;
    const tax      = subtotal * (taxPct / 100);
    const total    = subtotal + tax;
    const balance  = Math.max(0, total - paid);

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = this.formatCurrency(val); };
    const setInput = (id, val) => { const el = document.getElementById(id); if (el) el.value = val.toFixed(2); };

    set('display_subtotal', subtotal);
    set('display_tax', tax);
    set('display_total', total);
    set('display_balance', balance);
    setInput('tax_amount', tax);
    setInput('total_amount', total);
    setInput('balance_due', balance);
  },

  // ---- WHATSAPP ----
  openWhatsApp(phone, message = '') {
    const clean = phone.replace(/\D/g, '');
    const number = clean.startsWith('91') ? clean : '91' + clean;
    const url = `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
  },

  // ---- QR CODE ----
  generateQR(text, canvasId) {
    // Requires QRCode.js library
    if (typeof QRCode !== 'undefined') {
      new QRCode(document.getElementById(canvasId), { text, width: 100, height: 100 });
    }
  },

  // ---- PRINT ----
  printInvoice() {
    window.print();
  },
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => App.init());

// Expose globally
window.App = App;
