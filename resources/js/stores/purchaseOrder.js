import { defineStore } from 'pinia';
import axios from 'axios';

export const usePurchaseOrderStore = defineStore('purchaseOrder', {
  state: () => ({
    purchaseOrders: [],
    purchaseOrder: null,
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
    async fetchPurchaseOrders(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/purchase-orders', { params: filters });
        if (response.data.success) {
          this.purchaseOrders = response.data.data;
          if (response.data.meta) {
            this.pagination = response.data.meta;
          }
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch purchase orders';
      } finally {
        this.loading = false;
      }
    },

    async fetchPurchaseOrder(id) {
      this.loading = true;
      this.error = null;
      this.purchaseOrder = null;
      try {
        const response = await axios.get(`/api/admin/purchase-orders/${id}`);
        if (response.data.success) {
          this.purchaseOrder = response.data.data;
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch purchase order details';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async createPurchaseOrder(data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/api/admin/purchase-orders', data);
        if (response.data.success) {
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create purchase order';
        throw err.response?.data || { message: 'Failed to create purchase order' };
      } finally {
        this.loading = false;
      }
    },

    async updatePurchaseOrder(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/admin/purchase-orders/${id}`, data);
        if (response.data.success) {
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update purchase order';
        throw err.response?.data || { message: 'Failed to update purchase order' };
      } finally {
        this.loading = false;
      }
    },

    async deletePurchaseOrder(id) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.delete(`/api/admin/purchase-orders/${id}`);
        if (response.data.success) {
          return true;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete purchase order';
        throw err.response?.data || { message: 'Failed to delete purchase order' };
      } finally {
        this.loading = false;
      }
    },

    async receiveStock(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post(`/api/admin/purchase-orders/${id}/receive`, data);
        if (response.data.success) {
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to receive stock';
        throw err.response?.data || { message: 'Failed to receive stock' };
      } finally {
        this.loading = false;
      }
    },
  },
});
