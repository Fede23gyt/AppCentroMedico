import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';

// Admin One imports
import { useMainStore } from '@/stores/main.js';
import { useStyleStore } from '@/stores/style.js';
import { darkModeKey, styleKey } from '@/config.js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia);

        // Ziggy será incluido globalmente via blade
        if (typeof Ziggy !== 'undefined') {
            app.config.globalProperties.route = route;
        }

        // Mount app
        app.mount(el);

        // Initialize Admin One stores
        const mainStore = useMainStore(pinia);
        const styleStore = useStyleStore(pinia);

        // Set initial dark mode
        if ((!localStorage[darkModeKey] && window.matchMedia('(prefers-color-scheme: dark)').matches) || localStorage[darkModeKey] === '1') {
            styleStore.setDarkMode(true);
        }

        // Set style from localStorage
        if (localStorage[styleKey]) {
            styleStore.setStyle(localStorage[styleKey]);
        } else {
            styleStore.setStyle('white');
        }

        // Fetch sample data
        mainStore.fetchSampleClients();
        mainStore.fetchSampleHistory();

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
