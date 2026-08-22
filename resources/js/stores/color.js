import { defineStore } from 'pinia';
import axios from 'axios';

export const useColorStore = defineStore('color', {
  state: () => ({
    colors: [],
    activeColors: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchColors(search = '') {
      this.loading = true;
      this.error = null;
      try {
        const url = search ? `/api/admin/colors?search=${encodeURIComponent(search)}` : '/api/admin/colors';
        const response = await axios.get(url);
        if (response.data.success) {
          this.colors = response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch colors';
      } finally {
        this.loading = false;
      }
    },

    async fetchActiveColors() {
      try {
        const response = await axios.get('/api/admin/colors/active');
        if (response.data.success) {
          this.activeColors = response.data.data;
          return this.activeColors;
        }
      } catch (err) {
        console.error('Failed to fetch active colors', err);
      }
      return [];
    },

    async createColor(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/admin/colors', data);
        if (response.data.success) {
          this.colors.push(response.data.data);
          this.activeColors.push(response.data.data);
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create color';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async updateColor(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/admin/colors/${id}`, data);
        if (response.data.success) {
          const index = this.colors.findIndex(c => c.id === id);
          if (index !== -1) {
            this.colors[index] = response.data.data;
          }
          const activeIdx = this.activeColors.findIndex(c => c.id === id);
          if (activeIdx !== -1) {
            if (response.data.data.is_active) {
              this.activeColors[activeIdx] = response.data.data;
            } else {
              this.activeColors.splice(activeIdx, 1);
            }
          }
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update color';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async deleteColor(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.delete(`/api/admin/colors/${id}`);
        if (response.data.success) {
          this.colors = this.colors.filter(c => c.id !== id);
          this.activeColors = this.activeColors.filter(c => c.id !== id);
          return true;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete color';
        throw err;
      } finally {
        this.loading = false;
      }
    },
  },
});
