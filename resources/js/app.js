import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
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
});

Alpine.start();
