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
            console.warn('Unauthorized request detected. Clearing session...');

            // Clear all auth-related data
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            localStorage.removeItem('auth_permissions');

            // Clear default headers
            delete axios.defaults.headers.common['Authorization'];

            // Ensure we are not in an infinite redirect loop
            const currentPath = window.location.pathname;
            if (currentPath !== '/login' && !currentPath.includes('public')) {
                console.log('Redirecting to login...');
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

import { formatDate } from './admin/utils/format';
const app = createApp(App);

app.config.globalProperties.$indianDate = formatDate;
app.use(router);

app.mount('#app');
