import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './admin/router';
import axios from 'axios';

// Set default base URL for axios
axios.defaults.baseURL = '/'; // Use relative URL to work in both local and production
axios.defaults.withCredentials = true;

// Add auth token if exists
const token = localStorage.getItem('auth_token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Add interceptor to handle session expiration (401 errors)
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            // Clear auth token and redirect to login
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];

            // Avoid redirecting if already on login page
            if (router.currentRoute.value.name !== 'Login') {
                router.push({ name: 'Login' });
            }
        }
        return Promise.reject(error);
    }
);

const app = createApp(App);

app.use(router);

app.mount('#app');
