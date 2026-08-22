import { defineStore } from 'pinia';
import axios from 'axios';

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    variants: [],
    ledger: [],
    currentMatrix: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    },
    ledgerPagination: {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0
    },
    loading: false,
    matrixLoading: false,
    submitting: false,
    error: null,
  }),

  actions: {
    async fetchVariants(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/inventory', { params: filters });
        if (response.data.success) {
          this.variants = response.data.data;
          if (response.data.meta) {
            this.pagination = response.data.meta;
          }
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch stock levels';
      } finally {
        this.loading = false;
      }
    },

    async fetchLedger(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/inventory/ledger', { params: filters });
        if (response.data.success) {
          this.ledger = response.data.data;
          if (response.data.meta) {
            this.ledgerPagination = response.data.meta;
          }
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch ledger logs';
      } finally {
        this.loading = false;
      }
    },

    async adjustStock(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/admin/inventory/adjust', data);
        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to adjust stock';
        throw err.response?.data || { message: 'Failed to adjust stock' };
      } finally {
        this.loading = false;
      }
    },

    async fetchProductMatrix(productId) {
      this.matrixLoading = true;
      this.error = null;
      try {
        const response = await axios.get(`/api/admin/inventory/product-matrix/${productId}`);
        if (response.data.success) {
          this.currentMatrix = response.data.data;
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load product stock matrix';
        throw err;
      } finally {
        this.matrixLoading = false;
      }
    },

    async bulkUpdateMatrix(payload) {
      this.submitting = true;
      this.error = null;
      try {
        const response = await axios.post('/api/admin/inventory/bulk-matrix-update', payload);
        if (response.data.success) {
          return response.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update stock matrix';
        throw err;
      } finally {
        this.submitting = false;
      }
    },

    async importCsvStock(formData) {
      this.submitting = true;
      this.error = null;
      try {
        const response = await axios.post('/api/admin/inventory/import-csv', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to import CSV';
        throw err;
      } finally {
        this.submitting = false;
      }
    },
  },
});
