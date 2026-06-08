/* WBDLS — main.js (vanilla JS) */
(function () {
    'use strict';

    // Expose BASE_URL globally
    const baseMeta = document.querySelector('meta[name="base-url"]');
    if (baseMeta) window.BASE_URL = baseMeta.content;

    // 1. MOBILE HAMBURGER
    const ham = document.getElementById('hamburgerBtn');
    const overlay = document.getElementById('sidebarOverlay');
    if (ham) {
        ham.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-open');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', function () {
            document.body.classList.remove('sidebar-open');
        });
    }

    // 2. NOTIFICATION DROPDOWN
    const notifBell = document.getElementById('notifBell');
    const notifDropdown = document.getElementById('notifDropdown');
    if (notifBell && notifDropdown) {
        notifBell.addEventListener('click', function (e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('open');
            if (notifDropdown.classList.contains('open')) {
                fetch(BASE_URL + '/actions/notifications/mark_read.php', { method: 'POST' })
                    .then(r => r.json()).catch(() => {});
            }
        });
        document.addEventListener('click', function (e) {
            if (!notifDropdown.contains(e.target) && e.target !== notifBell) {
                notifDropdown.classList.remove('open');
            }
        });
    }

    // 3. OTP INPUT AUTO-FOCUS
    const otpInputs = document.querySelectorAll('.otp-input-group input');
    const otpFull = document.getElementById('otpFull');
    if (otpInputs.length) {
        otpInputs.forEach((input, idx) => {
            input.addEventListener('input', function () {
                if (this.value.length === 1 && idx < otpInputs.length - 1) {
                    otpInputs[idx + 1].focus();
                }
                if (otpFull) {
                    otpFull.value = Array.from(otpInputs).map(i => i.value).join('');
                }
                if (idx === otpInputs.length - 1 && otpFull && otpFull.value.length === otpInputs.length) {
                    const form = input.closest('form');
                    if (form) form.submit();
                }
            });
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    otpInputs[idx - 1].focus();
                }
            });
            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const data = (e.clipboardData || window.clipboardData).getData('text').slice(0, otpInputs.length);
                data.split('').forEach((c, i) => {
                    if (otpInputs[i]) otpInputs[i].value = c;
                });
                if (otpFull) otpFull.value = data;
                const last = otpInputs[Math.min(data.length, otpInputs.length) - 1];
                if (last) last.focus();
            });
        });
    }

    // 4. PASSWORD SHOW/HIDE
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.parentElement.querySelector('input');
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            this.textContent = input.type === 'password' ? 'Show' : 'Hide';
        });
    });

    // 5. CONFIRM DELETE
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // 7. QUIZ OPTION SELECTION
    document.querySelectorAll('.quiz-option').forEach(opt => {
        opt.addEventListener('click', function () {
            const name = this.dataset.name;
            document.querySelectorAll('.quiz-option[data-name="' + name + '"]').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type=radio]');
            if (radio) radio.checked = true;
            const hidden = document.querySelector('input[name="' + name + '_value"]');
            if (hidden) hidden.value = this.dataset.value;
        });
    });

    // 9. MODAL
    window.openModal = function (id) {
        const m = document.getElementById(id);
        if (m) m.classList.add('active');
    };
    window.closeModal = function (id) {
        const m = document.getElementById(id);
        if (m) m.classList.remove('active');
    };
    document.querySelectorAll('.modal-overlay').forEach(m => {
        m.addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('active');
        });
    });
    document.querySelectorAll('.modal-close').forEach(b => {
        b.addEventListener('click', function () {
            const m = this.closest('.modal-overlay');
            if (m) m.classList.remove('active');
        });
    });

    // 10. ACCORDION
    document.querySelectorAll('.accordion-header').forEach(h => {
        h.addEventListener('click', function () {
            this.parentElement.classList.toggle('open');
        });
    });

    // 11. SIDEBAR ACTIVE LINK
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.endsWith(href.replace(BASE_URL, ''))) {
            link.classList.add('active');
        }
    });

    // Auto-dismiss alerts after 5s
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            a.style.transition = 'opacity .5s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 500);
        });
    }, 5000);

    // Tabs
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.addEventListener('click', function () {
            const target = this.dataset.tab;
            this.parentElement.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            const tc = document.getElementById('tab-' + target);
            if (tc) tc.classList.add('active');
        });
    });
})();
