import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

import { useAuthStore } from './stores/auth';
import { useThemeStore } from './stores/theme';
import { initContentProtection, showProtectionToast } from './utils/contentProtection';

// Globally override native window.alert to use the premium branded toast view instead
window.alert = (message) => {
  showProtectionToast(message);
};

const pinia = createPinia();
const app = createApp(App);

app.use(pinia);
app.use(router);

// Initialize content protection helper
initContentProtection(app);

// Fetch theme on load
const themeStore = useThemeStore();
themeStore.fetchActiveTheme();

// Initialize auth state
const authStore = useAuthStore(pinia);
if (authStore.token) {
  // Try to fetch user to validate token
  authStore.fetchUser().then(user => {
    if (!user && router.currentRoute.value.path.startsWith('/admin')) {
      router.push('/login');
    }
  });
}

// Global Axios interceptor for 401 Unauthorized
import axios from 'axios';
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      const authStore = useAuthStore();
      authStore.token = null;
      authStore.user = null;
      localStorage.removeItem('auth_token');
      if (router.currentRoute.value.path.startsWith('/admin')) {
        router.push('/login');
      }
    } else if (error.response && error.response.status === 403) {
      if (router.currentRoute.value.path.startsWith('/admin')) {
        alert('Forbidden: You do not have permission to access this resource.');
        router.push('/login');
      }
    }
    return Promise.reject(error);
  }
);

app.mount('#app');
