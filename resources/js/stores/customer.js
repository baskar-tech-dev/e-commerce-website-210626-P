import { defineStore } from 'pinia';
import axios from 'axios';

export const useCustomerStore = defineStore('customer', {
  state: () => ({
    customers: [],
    customer: null,
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
    async fetchCustomers(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get('/api/admin/customers', { params: filters });
        if (response.data.data) {
          this.customers = response.data.data;
          if (response.data.meta) {
            this.pagination = response.data.meta;
          }
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch customers';
      } finally {
        this.loading = false;
      }
    },

    async fetchCustomer(id) {
      this.loading = true;
      this.error = null;
      this.customer = null;
      try {
        const response = await axios.get(`/api/admin/customers/${id}`);
        if (response.data.data) {
          this.customer = response.data.data;
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to fetch customer details';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async updateCustomer(id, data) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/admin/customers/${id}`, data);
        if (response.data.data) {
          this.customer = response.data.data;
          return response.data.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update customer';
        throw err.response?.data || { message: 'Failed to update customer' };
      } finally {
        this.loading = false;
      }
    },

    async addAddress(customerId, addressData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post(`/api/admin/customers/${customerId}/addresses`, addressData);
        // Refresh customer profile to reload updated addresses
        await this.fetchCustomer(customerId);
        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to add address';
        throw err.response?.data || { message: 'Failed to add address' };
      } finally {
        this.loading = false;
      }
    },

    async updateAddress(customerId, addressId, addressData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.put(`/api/admin/customers/${customerId}/addresses/${addressId}`, addressData);
        // Refresh customer profile to reload updated addresses
        await this.fetchCustomer(customerId);
        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update address';
        throw err.response?.data || { message: 'Failed to update address' };
      } finally {
        this.loading = false;
      }
    },

    async deleteAddress(customerId, addressId) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.delete(`/api/admin/customers/${customerId}/addresses/${addressId}`);
        // Refresh customer profile to reload updated addresses
        await this.fetchCustomer(customerId);
        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete address';
        throw err.response?.data || { message: 'Failed to delete address' };
      } finally {
        this.loading = false;
      }
    },
  },
});
