import { createApp, h } from 'vue';
import i18n from '@/libs/i18n';
import router from './router';
import store from './store';
import App from './App.vue';
import { VueCookieNext } from 'vue-cookie-next'
import VueCookieAcceptDecline from 'vue-cookie-accept-decline';
import mitt from "mitt";
import LayoutLanding from './layouts/LayoutLanding.vue';
import LayoutMain from './layouts/LayoutMain.vue';

import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.css';

const app = createApp({
    render: ()=>h(App)
});
const emitter = mitt();
app.use(i18n);
app.use(store);
app.use(router);
app.use(VueCookieNext)

// set default config
VueCookieNext.config({ expire: Infinity })


app.component('layout-landing', LayoutLanding);
app.component('layout-main', LayoutMain);
app.component('vue-cookie-accept-decline', VueCookieAcceptDecline);
app.config.globalProperties.emitter = emitter;
// Define global translation helpers
Object.defineProperty(app.config.globalProperties, '$translations', {
    get() {return store.getters.translations}
})
Object.defineProperty(app.config.globalProperties, '$translationsLanding', {
    get() {return store.getters.translationsLanding}
})

app.mount("#app");

// Expose the main vue app for plugins
Object.defineProperty(window, 'app', {
    get() {return app},
    set(val) {}
})
