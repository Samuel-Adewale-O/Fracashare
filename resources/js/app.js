import './bootstrap';
import { createApp } from 'vue';
import { createStore } from 'vuex';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';

// Store modules
import auth from './store/auth';
import assets from './store/assets';
import transactions from './store/transactions';

// Create Vuex store
const store = createStore({
    modules: {
        auth,
        assets,
        transactions
    }
});

// Create Vue Router
const router = createRouter({
    history: createWebHistory(),
    routes: [
        // Routes will be added here
    ]
});

// Create Vue app
const app = createApp(App);

app.use(store);
app.use(router);

app.mount('#app');