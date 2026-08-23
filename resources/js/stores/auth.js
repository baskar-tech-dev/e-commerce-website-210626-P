import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null,
    validationErrors: {},
    authModalOpen: false,
    authModalTab: 'login', // 'login' | 'register' | 'forgot'
    intendedDestination: null,
    accountPromptDismissed: sessionStorage.getItem('account_prompt_dismissed') === 'true',
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    userName: (state) => {
      if (!state.user) return 'Account';
      return state.user.first_name || state.user.name?.split(' ')[0] || 'Account';
    },
  },

  actions: {
    openAuthModal(tab = 'login', intended = null) {
      this.authModalTab = tab;
      if (intended) {
        this.intendedDestination = intended;
      }
      this.error = null;
      this.validationErrors = {};
      this.authModalOpen = true;
    },

    closeAuthModal() {
      this.authModalOpen = false;
      this.error = null;
      this.validationErrors = {};
    },

    dismissAccountPrompt() {
      this.accountPromptDismissed = true;
      sessionStorage.setItem('account_prompt_dismissed', 'true');
    },

    async login(credentials) {
      this.loading = true;
      this.error = null;
      this.validationErrors = {};
      try {
        const response = await axios.post('/api/auth/login', credentials);
        const { access_token, user } = response.data;
        
        this.token = access_token;
        this.user = user;
        
        localStorage.setItem('auth_token', access_token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;
        
        await this.handleAuthSuccess();
        return user;
      } catch (err) {
        if (err.response?.status === 422) {
          this.validationErrors = err.response.data.errors || {};
          this.error = err.response.data.message || 'Validation failed. Please check your inputs.';
        } else {
          this.error = err.response?.data?.message || 'Invalid credentials. Please try again.';
        }
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async register(data) {
      this.loading = true;
      this.error = null;
      this.validationErrors = {};
      try {
        const response = await axios.post('/api/auth/register', data);
        const { access_token, user } = response.data;

        this.token = access_token;
        this.user = user;

        localStorage.setItem('auth_token', access_token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;

        await this.handleAuthSuccess();
        return user;
      } catch (err) {
        if (err.response?.status === 422) {
          this.validationErrors = err.response.data.errors || {};
          const firstErr = Object.values(this.validationErrors)[0]?.[0];
          this.error = firstErr || err.response.data.message || 'Registration failed. Please check inputs.';
        } else {
          this.error = err.response?.data?.message || 'Failed to create account. Please try again.';
        }
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async forgotPassword(email) {
      this.loading = true;
      this.error = null;
      this.validationErrors = {};
      try {
        const response = await axios.post('/api/auth/forgot-password', { email });
        return response.data;
      } catch (err) {
        if (err.response?.status === 422) {
          this.validationErrors = err.response.data.errors || {};
        }
        this.error = err.response?.data?.message || 'Failed to request password reset.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;
      this.error = null;
      try {
        if (this.token) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
          await axios.post('/api/auth/logout');
        }
      } catch (err) {
        console.error('Logout error', err);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        delete axios.defaults.headers.common['Authorization'];
        this.loading = false;
      }
    },

    async fetchUser() {
      if (!this.token) return null;
      
      this.loading = true;
      try {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        const response = await axios.get('/api/auth/user');
        this.user = response.data.user || response.data;
        
        // Sync wishlist upon fetching user if token exists
        this.syncWishlist();

        return this.user;
      } catch (err) {
        this.logout();
        return null;
      } finally {
        this.loading = false;
      }
    },

    async syncWishlist() {
      if (!this.token) return;
      try {
        const localWishlist = JSON.parse(localStorage.getItem('vibe_wishlist_items') || '[]');
        if (localWishlist.length > 0) {
          const mergeResponse = await axios.post('/api/customer/wishlist/merge', {
            items: localWishlist,
          });
          if (mergeResponse.data && mergeResponse.data.success) {
            localStorage.setItem('vibe_wishlist_items', JSON.stringify(mergeResponse.data.data));
          }
        } else {
          const getResponse = await axios.get('/api/customer/wishlist');
          if (getResponse.data && getResponse.data.success) {
            localStorage.setItem('vibe_wishlist_items', JSON.stringify(getResponse.data.data));
          }
        }
      } catch (err) {
        console.error('Wishlist sync failed:', err);
      }
    },

    async handleAuthSuccess() {
      await this.syncWishlist();
      this.authModalOpen = false;
    }
  },
});
