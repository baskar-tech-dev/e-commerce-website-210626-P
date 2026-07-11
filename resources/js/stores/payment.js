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
     * Initiate payment by creating a Razorpay Order in the Laravel backend.
     * 
     * @param {number} orderId 
     * @returns {Promise<object>}
     */
    async createRazorpayOrder(orderId) {
      this.loading = true;
      this.processing = true;
      this.error = null;
      this.success = false;
      
      try {
        const response = await axios.post('/api/payment/create-order', {
          order_id: orderId,
        });
        
        if (response.data && response.data.success) {
          return response.data.data;
        } else {
          throw new Error(response.data?.message || 'Failed to initiate payment.');
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
     * Verify Razorpay payment signature in the Laravel backend.
     * 
     * @param {object} verificationData 
     * @returns {Promise<object>}
     */
    async verifyPayment(verificationData) {
      this.loading = true;
      this.processing = true;
      this.error = null;
      
      try {
        const response = await axios.post('/api/payment/verify', {
          order_id: verificationData.order_id,
          razorpay_order_id: verificationData.razorpay_order_id,
          razorpay_payment_id: verificationData.razorpay_payment_id,
          razorpay_signature: verificationData.razorpay_signature,
        });
        
        if (response.data && response.data.success) {
          this.success = true;
          this.processing = false;
          return response.data;
        } else {
          throw new Error(response.data?.message || 'Payment signature verification failed.');
        }
      } catch (err) {
        this.error = err.response?.data?.message || err.message || 'Signature verification failed.';
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
    async cancelPayment(orderId, reason = 'Payment modal dismissed by user') {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.post('/api/payment/cancel', {
          order_id: orderId,
          reason: reason,
        });
        
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
