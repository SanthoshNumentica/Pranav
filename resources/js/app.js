import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './admin/router';
import axios from 'axios';

// Set default base URL for axios
axios.defaults.baseURL = 'http://localhost:8000'; // Adjust if needed
axios.defaults.withCredentials = true;

// Add auth token if exists
const token = localStorage.getItem('auth_token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

const app = createApp(App);

app.use(router);

app.mount('#app');
