import { defineStore } from 'pinia';
import axios from 'axios';

export const useProductStore = defineStore('product', {
  state: () => ({
    products: [],
    product: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    },
    loading: false,
    error: null,
  }),

  actions: {
    async fetchProducts(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/products', { params: filters });
        if (response.data.success) {
          this.products = response.data.data;
          if (response.data.meta) {
            this.pagination = response.data.meta;
          }
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch products';
      } finally {
        this.loading = false;
      }
    },

    async fetchProduct(id) {
      this.loading = true;
      this.error = null;
      this.product = null;
      try {
        const response = await axios.get(`/api/admin/products/${id}`);
        if (response.data.success) {
          this.product = response.data.data;
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch product details';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async createProduct(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/admin/products', data);
        if (response.data.success) {
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create product';
        throw err.response?.data || { message: 'Failed to create product' };
      } finally {
        this.loading = false;
      }
    },

    async updateProduct(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/admin/products/${id}`, data);
        if (response.data.success) {
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update product';
        throw err.response?.data || { message: 'Failed to update product' };
      } finally {
        this.loading = false;
      }
    },

    async deleteProduct(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.delete(`/api/admin/products/${id}`);
        if (response.data.success) {
          return true;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete product';
        throw err.response?.data || { message: 'Failed to delete product' };
      } finally {
        this.loading = false;
      }
    },
  },
});
