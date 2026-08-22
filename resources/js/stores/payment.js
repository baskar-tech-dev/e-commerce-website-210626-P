import { defineStore } from 'pinia';
import axios from 'axios';

export const usePaymentStore = defineStore('payment', {
  state: () => ({
    loading: false,
    success: false,
    error: null,
    processing: false,
  }),

  actions: {
    /**
     * Initiate payment by creating a Cashfree Order session in the Laravel backend.
     * 
     * @param {number} orderId 
     * @returns {Promise<object>}
     */
    async createPaymentSession(orderId) {
      this.loading = true;
      this.processing = true;
      this.error = null;
      this.success = false;
      
      const token = localStorage.getItem('auth_token');
      const headers = token ? { Authorization: `Bearer ${token}` } : {};

      try {
        const response = await axios.post('/api/payment/cashfree/create', {
          order_id: orderId,
        }, { headers });
        
        if (response.data && response.data.success) {
          return response.data.data;
        } else {
          throw new Error(response.data?.message || 'Failed to initiate Cashfree payment session.');
        }
      } catch (err) {
        this.error = err.response?.data?.message || err.message || 'Failed to create payment order.';
        this.processing = false;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Alias for createPaymentSession.
     */
    async createCashfreeOrder(orderId) {
      return this.createPaymentSession(orderId);
    },

    /**
     * Verify payment on backend against Cashfree API.
     * 
     * @param {object} verificationData 
     * @returns {Promise<object>}
     */
    async verifyPayment(verificationData) {
      this.loading = true;
      this.processing = true;
      this.error = null;

      const token = localStorage.getItem('auth_token');
      const headers = token ? { Authorization: `Bearer ${token}` } : {};

      try {
        const response = await axios.post('/api/payment/cashfree/verify', {
          order_id: verificationData.order_id,
          cashfree_order_id: verificationData.cashfree_order_id || verificationData.order_number,
        }, { headers });
        
        if (response.data && response.data.success) {
          this.success = true;
          this.processing = false;
          return response.data;
        } else {
          throw new Error(response.data?.message || 'Payment verification failed.');
        }
      } catch (err) {
        this.error = err.response?.data?.message || err.message || 'Payment verification failed.';
        this.success = false;
        this.processing = false;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Cancel payment flow to release reserved stock.
     * 
     * @param {number} orderId 
     * @param {string} reason 
     * @returns {Promise<object>}
     */
    async cancelPayment(orderId, reason = 'Payment cancelled or dismissed by user') {
      this.loading = true;
      this.error = null;

      const token = localStorage.getItem('auth_token');
      const headers = token ? { Authorization: `Bearer ${token}` } : {};

      try {
        const response = await axios.post('/api/payment/cashfree/cancel', {
          order_id: orderId,
          reason: reason,
        }, { headers });
        
        this.processing = false;
        return response.data;
      } catch (err) {
        this.error = err.response?.data?.message || err.message || 'Failed to cancel payment.';
        this.processing = false;
        throw err;
      } finally {
        this.loading = false;
      }
    },

    /**
     * Fetch verified payment status from server.
     * 
     * @param {number} orderId 
     * @returns {Promise<object>}
     */
    async getPaymentStatus(orderId) {
      const token = localStorage.getItem('auth_token');
      const headers = token ? { Authorization: `Bearer ${token}` } : {};

      try {
        const response = await axios.get(`/api/payment/cashfree/status/${orderId}`, { headers });
        return response.data;
      } catch (err) {
        console.error('Failed to get payment status:', err);
        throw err;
      }
    },

    /**
     * Reset payment store state.
     */
    reset() {
      this.loading = false;
      this.success = false;
      this.error = null;
      this.processing = false;
    }
  }
});
