import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Alpine.plugin(persist);

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('sidebarNav', (activeParentId) => ({
        sidebarOpen: window.innerWidth >= 1024,
        openSections: Alpine.$persist([]).as('sidebar-open-sections'),

        init() {
            if (activeParentId && !this.openSections.includes(activeParentId)) {
                this.openSections.push(activeParentId);
            }
        },

        toggleSection(id) {
            if (this.openSections.includes(id)) {
                this.openSections = this.openSections.filter(s => s !== id);
            } else {
                this.openSections = [...this.openSections, id];
            }
        },

        isOpen(id) {
            return this.openSections.includes(id);
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

Alpine.start();
