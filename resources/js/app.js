import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Alpine.plugin(persist);

window.Alpine = Alpine;

window.formatPrice = function (value) {
    const num = parseFloat(value);
    if (isNaN(num)) return 'TSh 0.00';
    return 'TSh ' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

window.parsePrice = function (formatted) {
    if (!formatted) return 0;
    const cleaned = String(formatted).replace(/TSh\s*/gi, '').replace(/,/g, '').trim();
    const num = parseFloat(cleaned);
    return isNaN(num) ? 0 : num;
};

document.addEventListener('alpine:init', () => {
    Alpine.store('erp', {
        activeModule: null,
    });

    Alpine.data('erpSidebar', (initialModule) => ({
        sidebarOpen: window.innerWidth >= 1024,

        init() {
            if (initialModule !== null) {
                this.$store.erp.activeModule = initialModule;
            }
        },

        activateModule(index) {
            this.$store.erp.activeModule = index;
        },

        isActive(index) {
            return this.$store.erp.activeModule === index;
        },
    }));

    Alpine.data('clock', () => ({
        now: new Date(),
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        },
        tick() {
            this.now = new Date();
        },
        get date() {
            return this.now.toLocaleDateString('en-US', {
                weekday: 'short', month: 'short', day: 'numeric', year: 'numeric',
            });
        },
        get time() {
            return this.now.toLocaleTimeString('en-US', {
                hour: '2-digit', minute: '2-digit', second: '2-digit',
            });
        },
    }));

    Alpine.data('dropdown', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    }));

    Alpine.data('priceInput', () => ({
        display: '',
        raw: 0,
        init() {
            const decimals = parseInt(this.$el.dataset.decimals ?? 0);
            const fmt = (n) => n ? n.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) : '';
            this.raw = parseFloat(this.$el.value) || 0;
            this.display = fmt(this.raw);
            this.$el.value = this.raw || '';
            this.$el.type = 'hidden';
            const wrapper = document.createElement('div');
            wrapper.className = 'relative';
            this.$el.parentNode.insertBefore(wrapper, this.$el);
            wrapper.appendChild(this.$el);
            const visible = document.createElement('input');
            visible.type = 'text';
            visible.className = this.$el.className;
            visible.placeholder = 'TSh 0';
            visible.value = this.display;
            visible.setAttribute('x-ref', 'visible');
            wrapper.insertBefore(visible, this.$el);
            visible.addEventListener('input', (e) => {
                const cleaned = e.target.value.replace(/[^0-9.]/g, '');
                this.raw = parseFloat(cleaned) || 0;
                this.$el.value = this.raw || '';
                const pos = e.target.selectionStart;
                const oldLen = e.target.value.length;
                e.target.value = fmt(this.raw);
                const newLen = e.target.value.length;
                e.target.setSelectionRange(pos + (newLen - oldLen), pos + (newLen - oldLen));
            });
            visible.addEventListener('focus', () => {
                visible.value = this.raw || '';
                visible.setSelectionRange(visible.value.length, visible.value.length);
            });
            visible.addEventListener('blur', () => {
                visible.value = fmt(this.raw);
            });
        },
    }));

    Alpine.data('notifications', () => ({
        open: false,
        notifications: [],
        unreadCount: 0,
        async init() {
            this.unreadCount = 3;
            this.notifications = [
                { id: 1, message: 'Low stock alert', time: '5 min ago', read: false },
                { id: 2, message: 'New order received', time: '1 hour ago', read: false },
                { id: 3, message: 'Payment confirmed', time: '2 hours ago', read: false },
            ];
        },
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        get unread() {
            return this.notifications.filter(n => !n.read).length;
        },
        markRead(id) {
            const n = this.notifications.find(n => n.id === id);
            if (n) n.read = true;
            this.unreadCount = this.unread;
        },
    }));
});

Alpine.data('createInline', (config) => ({
    open: false,
    loading: false,
    errors: {},
    form: {},
    init() {
        this.resetForm();
        this.$nextTick(() => {
            const sel = document.getElementById(config.selectId);
            if (sel) {
                sel.addEventListener('change', () => {
                    if (sel.value === '__create__') {
                        this.open = true;
                        sel.value = '';
                    }
                });
            }
        });
    },
    resetForm() {
        this.form = {};
        this.errors = {};
        if (config.fields) {
            config.fields.forEach(f => { this.form[f.name] = f.default || ''; });
        }
    },
    submit() {
        this.loading = true;
        this.errors = {};
        const fd = new FormData();
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        fd.append('_method', 'POST');
        Object.keys(this.form).forEach(k => fd.append(k, this.form[k]));

        fetch(config.createUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                this.errors = data.errors || { name: [data.message || 'Creation failed'] };
                this.loading = false;
                return;
            }
            const sel = document.getElementById(config.selectId);
            if (sel) {
                const opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.name || data.label || data.code;
                sel.appendChild(opt);
                sel.value = data.id;
                sel.dispatchEvent(new Event('change'));
            }
            this.open = false;
            this.resetForm();
            this.loading = false;
        })
        .catch(() => {
            this.errors = { name: ['Network error. Please try again.'] };
            this.loading = false;
        });
    },
}));

Alpine.start();
