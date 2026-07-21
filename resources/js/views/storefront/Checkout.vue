<template>
  <div>
    <!-- Success confirmation view -->
    <div v-if="orderPlaced" class="glass-panel" style="max-width: 600px; margin: 2rem auto; padding: var(--spacing-xl); text-align: center; border: 1px solid var(--color-success);">
      <span style="font-size: 5rem; display: block; margin-bottom: var(--spacing-md);">🎉</span>
      <h2 style="color: var(--color-success); font-weight: 800; margin-bottom: var(--spacing-xs);">Order Placed Successfully!</h2>
      <p style="margin-bottom: var(--spacing-lg);">Thank you for shopping with Vibe Clothings. Your order has been registered.</p>
      
      <div style="background: var(--blush-bg); border-radius: 8px; padding: var(--spacing-md); border: 1px solid var(--color-border); margin-bottom: var(--spacing-xl); text-align: left; font-family: monospace; font-size: 0.95rem;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
          <span style="color: var(--color-text-secondary);">Order Number:</span>
          <span style="color: var(--color-text-primary); font-weight: bold;">{{ createdOrderNo }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-text-secondary);">Amount Charged:</span>
          <span style="color: var(--color-primary); font-weight: bold;">₹{{ createdOrderTotal }}</span>
        </div>
      </div>

      <div style="display: flex; gap: var(--spacing-md); justify-content: center;">
        <router-link to="/my-account?tab=orders" class="btn btn--primary">📦 Track Order</router-link>
        <router-link to="/" class="btn btn--secondary">Continue Shopping</router-link>
      </div>
    </div>

    <!-- Regular checkout view -->
    <div v-else>
      <h1 style="color: var(--color-primary); font-size: 2rem; font-weight: 800; margin-bottom: var(--spacing-lg);">Checkout Checkout</h1>

      <div class="checkout-layout-grid">
        <!-- Left: Form details -->
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <!-- Step 1: Shipping Details -->
          <div class="glass-panel" style="padding: var(--spacing-lg); display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-xs); border: none; padding-bottom: 0;">1. Delivery Address</div>

            <!-- Saved address book dropdown -->
            <div v-if="addressBook.length" class="form-group" style="margin-bottom: var(--spacing-sm); background: rgba(0,0,0,0.1); border-radius: 6px; padding: var(--spacing-sm); border: 1px solid var(--color-border);">
              <label class="form-label" style="font-size: 0.8rem; margin-bottom: var(--spacing-xs);">Select Saved Address</label>
              <select class="form-input" @change="applySavedAddress($event)" style="font-size: 0.85rem;">
                <option value="">-- Use a new custom address --</option>
                <option v-for="addr in addressBook" :key="addr.id" :value="addr.id">
                  [{{ addr.label }}] {{ addr.first_name }} {{ addr.last_name }} - {{ addr.address_line_1 }}, {{ addr.city }}
                </option>
              </select>
            </div>

            <!-- Custom address inputs -->
            <form id="shipping-form" @submit.prevent style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <div class="checkout-form-row-2">
                <div class="form-group">
                  <label class="form-label">First Name *</label>
                  <input type="text" v-model="form.shipping_first_name" class="form-input" required />
                </div>
                <div class="form-group">
                  <label class="form-label">Last Name *</label>
                  <input type="text" v-model="form.shipping_last_name" class="form-input" required />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Mobile Number *</label>
                <input type="text" v-model="form.shipping_phone" placeholder="9876543210" class="form-input" required />
              </div>

              <div class="form-group">
                <label class="form-label">Address Line 1 *</label>
                <input type="text" v-model="form.shipping_address_line_1" placeholder="Flat No, Wing, Building Name" class="form-input" required />
              </div>

              <div class="form-group">
                <label class="form-label">Address Line 2</label>
                <input type="text" v-model="form.shipping_address_line_2" placeholder="Street, Area, Landmark" class="form-input" />
              </div>

              <div class="checkout-form-row-3">
                <div class="form-group">
                  <label class="form-label">City *</label>
                  <input type="text" v-model="form.shipping_city" class="form-input" required />
                </div>
                <div class="form-group">
                  <label class="form-label">State *</label>
                  <input type="text" v-model="form.shipping_state" class="form-input" required />
                </div>
                <div class="form-group">
                  <label class="form-label">Pincode *</label>
                  <input type="text" v-model="form.shipping_postal_code" placeholder="400001" class="form-input" required />
                </div>
              </div>
            </form>
          </div>

          <!-- Step 2: Shipping Method -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md); border: none; padding-bottom: 0;">2. Shipping Method</div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600;">
                  <input type="radio" value="standard" v-model="form.shipping_method" style="accent-color: #4a0e2e;" />
                  🚛 Standard Express Delivery (3-5 Business Days)
                </div>
                <span style="font-weight: 700; color: #16a34a;">{{ shipping === 0 ? 'FREE' : '₹100' }}</span>
              </label>

              <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600;">
                  <input type="radio" value="express" v-model="form.shipping_method" style="accent-color: #4a0e2e;" />
                  ⚡ Air Priority Express (1-2 Days Guaranteed)
                </div>
                <span style="font-weight: 700; color: #4a0e2e;">+ ₹150</span>
              </label>
            </div>
          </div>

          <!-- Step 3: Payment Toggles -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md); border: none; padding-bottom: 0;">3. Payment Method</div>
            
            <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <!-- COD option -->
              <label style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text-primary); font-size: 0.95rem; font-weight: bold; cursor: pointer; padding: var(--spacing-sm); border: 1px solid var(--color-border); border-radius: 6px; background: var(--blush-bg);">
                <input type="radio" value="cod" v-model="form.payment_method" style="cursor: pointer;" />
                💲 Cash on Delivery (COD)
              </label>

              <!-- Online option -->
              <label style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text-primary); font-size: 0.95rem; font-weight: bold; cursor: pointer; padding: var(--spacing-sm); border: 1px solid var(--color-border); border-radius: 6px; background: var(--blush-bg);">
                <input type="radio" value="razorpay" v-model="form.payment_method" style="cursor: pointer;" />
                💳 Secure Online Checkout (Credit/Debit Card)
              </label>

              <!-- UPI option -->
              <label style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text-primary); font-size: 0.95rem; font-weight: bold; cursor: pointer; padding: var(--spacing-sm); border: 1px solid var(--color-border); border-radius: 6px; background: var(--blush-bg);">
                <input type="radio" value="upi" v-model="form.payment_method" style="cursor: pointer;" />
                📲 UPI (Google Pay, PhonePe, Paytm, etc.)
              </label>
            </div>
          </div>

          <!-- Step 4: Additional Options (Order Notes & GSTIN) -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: 12px; border: none; padding-bottom: 0;">4. Order Notes & Corporate GST</div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
              <div>
                <label class="form-label" style="font-size: 0.8rem;">Delivery Notes / Instructions</label>
                <textarea v-model="form.order_notes" placeholder="e.g. Leave package with guard or call before delivery..." class="form-textarea" rows="2" style="font-size: 0.85rem;"></textarea>
              </div>

              <div>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: #4a0e2e; cursor: pointer;">
                  <input type="checkbox" v-model="form.is_gst_order" style="accent-color: #4a0e2e;" />
                  🏢 Request GST Tax Invoice for Business Purchase
                </label>
                <div v-if="form.is_gst_order" style="display: flex; gap: 8px; margin-top: 8px;">
                  <input type="text" v-model="form.company_name" placeholder="Company Legal Name" class="form-input" style="font-size: 0.85rem;" />
                  <input type="text" v-model="form.gstin" placeholder="GSTIN (e.g. 33AAAAA0000A1Z5)" class="form-input" style="font-size: 0.85rem; text-transform: uppercase;" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Summary and Place Order button -->
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Order Summary</div>

            <!-- Items list preview -->
            <div style="max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: var(--spacing-sm); margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-border);">
              <div v-for="item in cartItems" :key="item.product_variant_id" style="display: flex; gap: var(--spacing-sm); align-items: center;">
                <img :src="item.image || '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp'" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover; border: 1px solid var(--color-border);" loading="lazy" />
                <div style="flex: 1; font-size: 0.8rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <strong style="color: var(--color-text-primary);">{{ item.name }}</strong>
                  <div style="color: var(--color-text-muted);">Size: {{ item.size || 'OS' }} x {{ item.quantity }}</div>
                </div>
                <span style="font-size: 0.85rem; font-weight: bold;">₹{{ item.selling_price * item.quantity }}</span>
              </div>
            </div>

            <!-- Financials -->
            <div style="display: flex; flex-direction: column; gap: var(--spacing-xs); font-size: 0.9rem; padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-border); margin-bottom: var(--spacing-md);">
              <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--color-text-muted);">Subtotal</span>
                <span>₹{{ subtotal }}</span>
              </div>
              <div v-if="discount > 0" style="display: flex; justify-content: space-between; color: var(--color-success);">
                <span>Voucher Code</span>
                <span>− ₹{{ discount }}</span>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--color-text-muted);">Delivery Shipping</span>
                <span v-if="shipping === 0" style="color: var(--color-success); font-weight: bold;">FREE</span>
                <span v-else>₹{{ shipping }}</span>
              </div>
            </div>

            <!-- Grand Total -->
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: var(--spacing-lg);">
              <span style="font-weight: 700; color: var(--color-text-primary); font-size: 1.05rem;">Total Amount</span>
              <span style="font-weight: 800; color: var(--color-primary); font-size: 1.6rem;">₹{{ grandTotal }}</span>
            </div>

            <!-- Terms Acceptance -->
            <div style="margin-bottom: 16px;">
              <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.78rem; color: #64748b; cursor: pointer;">
                <input type="checkbox" v-model="form.terms_accepted" style="margin-top: 2px; accent-color: #4a0e2e;" />
                <span>I agree to Maya Sree Fashion's <router-link to="/privacy-policy" style="color: #4a0e2e; text-decoration: underline;">Terms & Conditions</router-link> and <router-link to="/return-policy" style="color: #4a0e2e; text-decoration: underline;">7-Day Return Policy</router-link>.</span>
              </label>
            </div>

            <!-- Submit -->
            <button 
              class="btn btn--primary" 
              style="width: 100%; padding: 0.75rem; font-weight: bold; font-size: 1.05rem; border-radius: 8px;"
              :disabled="submitting || cartItems.length === 0 || !form.terms_accepted"
              @click="submitCheckout"
            >
              {{ submitting ? 'Processing Checkout...' : '🛍️ Place Secure Order' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { usePaymentStore } from '../../stores/payment';

const router = useRouter();
const paymentStore = usePaymentStore();
const emit = defineEmits(['update-cart-count']);

const cartItems = ref([]);
const addressBook = ref([]);
const localSubmitting = ref(false);
const submitting = computed(() => localSubmitting.value || paymentStore.loading || paymentStore.processing);

const orderPlaced = ref(false);
const createdOrderNo = ref('');
const createdOrderTotal = ref(0);

const form = ref({
  shipping_first_name: '',
  shipping_last_name: '',
  shipping_phone: '',
  shipping_address_line_1: '',
  shipping_address_line_2: '',
  shipping_city: '',
  shipping_state: '',
  shipping_postal_code: '',
  shipping_method: 'standard',
  payment_method: 'cod',
  coupon_code: '',
  order_notes: '',
  is_gst_order: false,
  company_name: '',
  gstin: '',
  terms_accepted: true,
});

const loadCart = () => {
  try {
    cartItems.value = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
  } catch (e) {
    cartItems.value = [];
  }
};

const fetchAddresses = async () => {
  try {
    const response = await axios.get('/api/customer/profile');
    if (response.data && response.data.success) {
      addressBook.value = response.data.data.addresses || [];
      
      // Auto pre-fill first name and last name
      form.value.shipping_first_name = response.data.data.first_name || '';
      form.value.shipping_last_name = response.data.data.last_name || '';
      form.value.shipping_phone = response.data.data.phone || '';
      
      // Auto-apply default shipping address if exists
      const def = addressBook.value.find(a => a.is_default_shipping);
      if (def) {
        applyAddress(def);
      }
    }
  } catch (err) {
    console.error('Failed to load profile address books:', err);
  }
};

const applyAddress = (addr) => {
  form.value.shipping_first_name = addr.first_name || '';
  form.value.shipping_last_name = addr.last_name || '';
  form.value.shipping_phone = addr.phone || '';
  form.value.shipping_address_line_1 = addr.address_line_1 || '';
  form.value.shipping_address_line_2 = addr.address_line_2 || '';
  form.value.shipping_city = addr.city || '';
  form.value.shipping_state = addr.state || '';
  form.value.shipping_postal_code = addr.postal_code || '';
};

const applySavedAddress = (event) => {
  const id = event.target.value;
  if (!id) {
    // Reset custom
    form.value.shipping_address_line_1 = '';
    form.value.shipping_address_line_2 = '';
    form.value.shipping_city = '';
    form.value.shipping_state = '';
    form.value.shipping_postal_code = '';
    return;
  }
  const match = addressBook.value.find(a => a.id === parseInt(id));
  if (match) {
    applyAddress(match);
  }
};

const subtotal = computed(() => {
  return cartItems.value.reduce((acc, item) => acc + item.selling_price * item.quantity, 0);
});

const discount = ref(0);

const calculateDiscount = () => {
  discount.value = 0;
  try {
    const coupon = JSON.parse(localStorage.getItem('vibe_applied_coupon'));
    if (coupon) {
      form.value.coupon_code = coupon.code;
      const amt = subtotal.value;
      if (amt >= (coupon.min_order_value ?? 0)) {
        if (coupon.type === 'percentage') {
          let disc = (amt * coupon.value) / 100;
          if (coupon.max_discount) {
            disc = Math.min(disc, coupon.max_discount);
          }
          discount.value = disc;
        } else {
          discount.value = Math.min(coupon.value, amt);
        }
      }
    }
  } catch (e) {
    discount.value = 0;
  }
};

const shipping = computed(() => {
  return (subtotal.value - discount.value >= 999) ? 0 : 100;
});

const grandTotal = computed(() => {
  return subtotal.value - discount.value + shipping.value;
});

const loadRazorpayScript = () => {
  return new Promise((resolve) => {
    if (window.Razorpay) {
      resolve(true);
      return;
    }
    const script = document.createElement('script');
    script.src = 'https://checkout.razorpay.com/v1/checkout.js';
    script.async = true;
    script.onload = () => resolve(true);
    script.onerror = () => resolve(false);
    document.body.appendChild(script);
  });
};

const submitCheckout = async () => {
  if (cartItems.value.length === 0) return;
  
  // Validation
  if (
    !form.value.shipping_first_name || 
    !form.value.shipping_last_name || 
    !form.value.shipping_phone || 
    !form.value.shipping_address_line_1 || 
    !form.value.shipping_city || 
    !form.value.shipping_state || 
    !form.value.shipping_postal_code
  ) {
    alert('Please fill out all required shipping fields');
    return;
  }

  localSubmitting.value = true;
  let localOrderId = null;

  try {
    const payload = {
      ...form.value,
      items: cartItems.value.map(i => ({
        product_variant_id: i.product_variant_id,
        quantity: i.quantity,
      })),
    };

    // 1. Create order in Laravel database
    const response = await axios.post('/api/storefront/checkout', payload);
    if (response.data && response.data.success) {
      localOrderId = response.data.data.order_id;
      createdOrderNo.value = response.data.data.order_number;
      createdOrderTotal.value = response.data.data.grand_total;

      // If COD, complete order immediately
      if (form.value.payment_method.toLowerCase() === 'cod') {
        orderPlaced.value = true;
        // Clear cart
        localStorage.removeItem('vibe_cart_items');
        localStorage.removeItem('vibe_applied_coupon');
        emit('update-cart-count');
        localSubmitting.value = false;
        return;
      }

      // 2. Razorpay Online Payment Flow
      const scriptLoaded = await loadRazorpayScript();
      if (!scriptLoaded) {
        alert('Failed to load Razorpay Payment Gateway script. Please check your connection.');
        localSubmitting.value = false;
        return;
      }

      // Create Razorpay Order using PaymentStore
      let rzpData;
      try {
        rzpData = await paymentStore.createRazorpayOrder(localOrderId);
      } catch (err) {
        alert(err.response?.data?.message || err.message || 'Failed to initiate Razorpay order.');
        localSubmitting.value = false;
        return;
      }

      const options = {
        key: rzpData.key_id,
        amount: rzpData.amount,
        currency: rzpData.currency,
        name: 'Maya Sree South Indian Fashion',
        description: 'Order Payment #' + rzpData.order_number,
        order_id: rzpData.razorpay_order_id,
        handler: async function (paymentResponse) {
          localSubmitting.value = true;
          try {
            // Verify signature on backend using PaymentStore
            const verifyResponse = await paymentStore.verifyPayment({
              order_id: localOrderId,
              razorpay_order_id: paymentResponse.razorpay_order_id,
              razorpay_payment_id: paymentResponse.razorpay_payment_id,
              razorpay_signature: paymentResponse.razorpay_signature,
            });

            if (verifyResponse.success) {
              orderPlaced.value = true;
              // Clear cart
              localStorage.removeItem('vibe_cart_items');
              localStorage.removeItem('vibe_applied_coupon');
              emit('update-cart-count');
            } else {
              alert('Payment verification failed.');
            }
          } catch (err) {
            console.error('Payment verification failed:', err);
            alert(err.response?.data?.message || err.message || 'Payment verification failed. Please try again.');
          } finally {
            localSubmitting.value = false;
          }
        },
        prefill: {
          name: rzpData.customer.name,
          contact: rzpData.customer.phone,
          email: rzpData.customer.email,
          method: form.value.payment_method === 'upi' ? 'upi' : undefined,
        },
        theme: {
          color: '#2d051c', // Deep Maroon
        },
        modal: {
          ondismiss: async function () {
            localSubmitting.value = true;
            try {
              // Send cancellation to release stock using PaymentStore
              await paymentStore.cancelPayment(localOrderId, 'Payment modal dismissed by user');
              alert('Payment cancelled. Your order has been registered as Cancelled.');
              router.push('/my-account?tab=orders');
            } catch (err) {
              console.error('Cancellation error:', err);
            } finally {
              localSubmitting.value = false;
            }
          }
        }
      };

      const rzp = new window.Razorpay(options);
      rzp.open();
    }
  } catch (err) {
    console.error('Checkout failed:', err);
    alert(err.response?.data?.message || 'Checkout verification failed. Please try again.');
    localSubmitting.value = false;
  }
};

onMounted(() => {
  loadCart();
  fetchAddresses();
  calculateDiscount();
});
</script>

<style scoped>
.checkout-layout-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: var(--spacing-lg);
}

.checkout-form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

.checkout-form-row-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: var(--spacing-md);
}

@media (max-width: 768px) {
  .checkout-layout-grid {
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
  }
}

@media (max-width: 576px) {
  .checkout-form-row-2,
  .checkout-form-row-3 {
    grid-template-columns: 1fr;
    gap: var(--spacing-sm);
  }
}
</style>
