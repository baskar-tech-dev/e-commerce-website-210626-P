import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = localStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Request interceptor ensuring token is always dynamically attached
axios.interceptors.request.use((config) => {
    const currentToken = localStorage.getItem('auth_token');
    if (currentToken && !config.headers.Authorization) {
        config.headers.Authorization = `Bearer ${currentToken}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});
