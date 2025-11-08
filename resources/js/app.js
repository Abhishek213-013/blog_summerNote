import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

// Create a simple route helper
const route = (name, params = {}) => {
    const routes = {
        'blog.index': '/',
        'blog.edit': '/edit-blog',
        'blog.store': '/blogs',
        'blog.update': (id) => `/blogs/${id}`,
        'blog.destroy': (id) => `/blogs/${id}`,
        'blog.like': (id) => `/blogs/${id}/like`,
    };
    
    if (typeof routes[name] === 'function') {
        return routes[name](params);
    }
    
    return routes[name];
};

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);
        
        // Make route function available globally
        app.config.globalProperties.$route = route;
        
        app.mount(el);
    },
});