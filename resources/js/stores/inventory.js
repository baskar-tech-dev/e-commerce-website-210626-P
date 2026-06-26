import { defineStore } from 'pinia';
import axios from 'axios';

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    variants: [],
    ledger: [],
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
  },
});
