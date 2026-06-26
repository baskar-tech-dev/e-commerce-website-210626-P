import { defineStore } from 'pinia';
import axios from 'axios';

export const useBrandStore = defineStore('brand', {
  state: () => ({
    brands: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchBrands() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/brands');
        if (response.data.success) {
          this.brands = response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch brands';
      } finally {
        this.loading = false;
      }
    },

    async createBrand(data) {
      this.loading = true;
      try {
        const response = await axios.post('/api/admin/brands', data);
        if (response.data.success) {
          await this.fetchBrands();
          return response.data.data;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to create brand' };
      } finally {
        this.loading = false;
      }
    },

    async updateBrand(id, data) {
      this.loading = true;
      try {
        const response = await axios.put(`/api/admin/brands/${id}`, data);
        if (response.data.success) {
          await this.fetchBrands();
          return response.data.data;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to update brand' };
      } finally {
        this.loading = false;
      }
    },

    async deleteBrand(id) {
      this.loading = true;
      try {
        const response = await axios.delete(`/api/admin/brands/${id}`);
        if (response.data.success) {
          await this.fetchBrands();
          return true;
        }
      } catch (err) {
        throw err.response?.data || { message: 'Failed to delete brand' };
      } finally {
        this.loading = false;
      }
    },
  },
});
