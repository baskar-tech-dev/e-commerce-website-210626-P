import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null,
    validationErrors: {},
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;
      this.validationErrors = {};
      try {
        const response = await axios.post('/api/login', credentials);
        
        const { access_token, user } = response.data;
        
        this.token = access_token;
        this.user = user;
        
        localStorage.setItem('auth_token', access_token);
        
        // Setup axios default auth header
        axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;
        
        return user;
      } catch (err) {
        if (err.response?.status === 422) {
          this.validationErrors = err.response.data.errors || {};
          this.error = null; // Clear global error if it's a validation error
        } else {
          this.error = err.response?.data?.message || 'Failed to authenticate';
        }
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
          // Setup axios default auth header just in case it's not set
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
          await axios.post('/api/logout');
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
        const response = await axios.get('/api/user');
        this.user = response.data;
        return this.user;
      } catch (err) {
        // If token is invalid, log out locally
        this.logout();
        return null;
      } finally {
        this.loading = false;
      }
    }
  },
});
