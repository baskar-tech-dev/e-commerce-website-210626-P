<template>
  <div>
    <!-- Success confirmation view -->
    <div v-if="orderPlaced" class="glass-panel" style="max-width: 600px; margin: 2rem auto; padding: var(--spacing-xl); text-align: center; border: 1px solid var(--color-success);">
      <span style="font-size: 5rem; display: block; margin-bottom: var(--spacing-md);">🎉</span>
      <h2 style="color: var(--color-success); font-weight: 800; margin-bottom: var(--spacing-xs);">Order Placed Successfully!</h2>
      <p style="margin-bottom: 0.25rem; font-weight: 600; color: #1e293b;">Thank you for shopping with Maya Sree Fashion!</p>
      <p style="font-size: 0.85rem; color: #64748b; margin-bottom: var(--spacing-lg);">Your order has been registered. An order confirmation message along with our unboxing video policy guidelines has been sent to your email and registered phone number via WhatsApp.</p>
      
      <div style="background: var(--blush-bg); border-radius: 8px; padding: var(--spacing-md); border: 1px solid var(--color-border); margin-bottom: var(--spacing-md); text-align: left; font-family: monospace; font-size: 0.95rem;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
          <span style="color: var(--color-text-secondary);">Order Number:</span>
          <span style="color: var(--color-text-primary); font-weight: bold;">{{ createdOrderNo }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span style="color: var(--color-text-secondary);">Amount Charged:</span>
          <span style="color: var(--color-primary); font-weight: bold;">₹{{ createdOrderTotal }}</span>
        </div>
      </div>

      <!-- Order Confirmation Return Policy Notice -->
      <ReturnPolicyNotice style="margin-bottom: var(--spacing-xl); text-align: left;" />

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
          <!-- Progressive Auth Banner for Guests -->
          <div v-if="!authStore.isAuthenticated" class="glass-panel checkout-auth-banner" style="padding: var(--spacing-md); background: #FAF5F0; border: 1px solid rgba(212, 175, 55, 0.35); display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div>
              <strong style="color: var(--color-primary); font-size: 0.95rem; display: block; margin-bottom: 2px;">⚡ Express Checkout with Maya Sree Account</strong>
              <span style="font-size: 0.82rem; color: #555;">Sign in or create an account to save addresses and track your orders.</span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <button type="button" class="btn btn--primary btn--sm" @click="authStore.openAuthModal('login', 'checkout')">Sign In</button>
              <button type="button" class="btn btn--secondary btn--sm" @click="authStore.openAuthModal('register', 'checkout')">Create Account</button>
            </div>
          </div>

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
                  <input 
                    type="text" 
                    v-model="form.shipping_first_name" 
                    @blur="validateField('shipping_first_name')"
                    @input="validateField('shipping_first_name')"
                    class="form-input" 
                    :class="{ 'form-input--error': errors.shipping_first_name }"
                  />
                  <span v-if="errors.shipping_first_name" class="form-error-msg">
                    {{ errors.shipping_first_name }}
                  </span>
                </div>
                <div class="form-group">
                  <label class="form-label">Last Name *</label>
                  <input 
                    type="text" 
                    v-model="form.shipping_last_name" 
                    @blur="validateField('shipping_last_name')"
                    @input="validateField('shipping_last_name')"
                    class="form-input" 
                    :class="{ 'form-input--error': errors.shipping_last_name }"
                  />
                  <span v-if="errors.shipping_last_name" class="form-error-msg">
                    {{ errors.shipping_last_name }}
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Mobile Number *</label>
                <input 
                  type="text" 
                  v-model="form.shipping_phone" 
                  @blur="validateField('shipping_phone')"
                  @input="validateField('shipping_phone')"
                  placeholder="9876543210" 
                  class="form-input" 
                  :class="{ 'form-input--error': errors.shipping_phone }"
                />
                <span v-if="errors.shipping_phone" class="form-error-msg">
                  {{ errors.shipping_phone }}
                </span>
              </div>

              <div class="form-group">
                <label class="form-label">Address Line 1 *</label>
                <input 
                  type="text" 
                  v-model="form.shipping_address_line_1" 
                  @blur="validateField('shipping_address_line_1')"
                  @input="validateField('shipping_address_line_1')"
                  placeholder="Flat No, Wing, Building Name" 
                  class="form-input" 
                  :class="{ 'form-input--error': errors.shipping_address_line_1 }"
                />
                <span v-if="errors.shipping_address_line_1" class="form-error-msg">
                  {{ errors.shipping_address_line_1 }}
                </span>
              </div>

              <div class="form-group">
                <label class="form-label">Address Line 2</label>
                <input 
                  type="text" 
                  v-model="form.shipping_address_line_2" 
                  placeholder="Street, Area, Landmark" 
                  class="form-input" 
                />
              </div>

              <div class="checkout-form-row-3">
                <div class="form-group">
                  <label class="form-label">City *</label>
                  <input 
                    type="text" 
                    v-model="form.shipping_city" 
                    @blur="validateField('shipping_city')"
                    @input="validateField('shipping_city')"
                    class="form-input" 
                    :class="{ 'form-input--error': errors.shipping_city }"
                  />
                  <span v-if="errors.shipping_city" class="form-error-msg">
                    {{ errors.shipping_city }}
                  </span>
                </div>
                <div class="form-group">
                  <label class="form-label">State *</label>
                  <select 
                    v-model="form.shipping_state" 
                    @change="validateField('shipping_state')"
                    class="form-input" 
                    :class="{ 'form-input--error': errors.shipping_state }"
                  >
                    <option value="" disabled>-- Select State / UT --</option>
                    <option v-for="st in indianStates" :key="st" :value="st">
                      {{ st }}
                    </option>
                  </select>
                  <span v-if="errors.shipping_state" class="form-error-msg">
                    {{ errors.shipping_state }}
                  </span>
                </div>
                <div class="form-group">
                  <label class="form-label">Pincode *</label>
                  <input 
                    type="text" 
                    v-model="form.shipping_postal_code" 
                    @blur="validateField('shipping_postal_code')"
                    @input="validateField('shipping_postal_code')"
                    placeholder="400001" 
                    class="form-input" 
                    :class="{ 'form-input--error': errors.shipping_postal_code }"
                  />
                  <span v-if="errors.shipping_postal_code" class="form-error-msg">
                    {{ errors.shipping_postal_code }}
                  </span>
                </div>
              </div>
            </form>
          </div>

          <!-- Step 2: Payment Toggles -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md); border: none; padding-bottom: 0;">2. Payment Method</div>
            
            <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <!-- COD option -->
              <label style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text-primary); font-size: 0.95rem; font-weight: bold; cursor: pointer; padding: var(--spacing-sm); border: 1px solid var(--color-border); border-radius: 6px; background: var(--blush-bg);">
                <input type="radio" value="cod" v-model="form.payment_method" style="cursor: pointer;" />
                💲 Cash on Delivery (COD)
              </label>

              <!-- Online option -->
              <label style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text-primary); font-size: 0.95rem; font-weight: bold; cursor: pointer; padding: var(--spacing-sm); border: 1px solid var(--color-border); border-radius: 6px; background: var(--blush-bg);">
                <input type="radio" value="online" v-model="form.payment_method" style="cursor: pointer;" />
                💳 Secure Online Checkout (Cards, NetBanking, Wallets)
              </label>

              <!-- UPI option -->
              <label style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text-primary); font-size: 0.95rem; font-weight: bold; cursor: pointer; padding: var(--spacing-sm); border: 1px solid var(--color-border); border-radius: 6px; background: var(--blush-bg);">
                <input type="radio" value="upi" v-model="form.payment_method" style="cursor: pointer;" />
                📲 Instant UPI (Google Pay, PhonePe, Paytm, BHIM)
              </label>
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
                <img :src="item.image || 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=150&auto=format&fit=crop'" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover; border: 1px solid var(--color-border);" loading="lazy" />
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

            <!-- Submit -->
            <button 
              class="btn btn--primary" 
              style="width: 100%; padding: 0.75rem; font-weight: bold; font-size: 1.05rem; border-radius: 8px;"
              :disabled="submitting || cartItems.length === 0"
              @click="submitCheckout"
            >
              {{ submitting ? 'Processing Checkout...' : '🛍️ Place Secure Order' }}
            </button>

            <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #FAF5F0; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 8px; font-size: 0.825rem; color: #475569; display: flex; align-items: center; gap: 0.5rem;">
              <span style="font-size: 1.1rem;">📦</span>
              <span><strong>Dispatch Time:</strong> {{ dispatchTimeText }}</span>
            </div>

            <!-- Return Policy Notice -->
            <ReturnPolicyNotice :compact="true" style="margin-top: 1rem;" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { usePaymentStore } from '../../stores/payment';
import { useAuthStore } from '../../stores/auth';
import { useIndianStates } from '../../constants/indianStates';
import ReturnPolicyNotice from '../../components/ReturnPolicyNotice.vue';

const router = useRouter();
const paymentStore = usePaymentStore();
const authStore = useAuthStore();
const { indianStates, fetchIndianStates, normalizeState } = useIndianStates();
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
  shipping_state: 'Tamil Nadu',
  shipping_postal_code: '',
  payment_method: 'cod',
  coupon_code: '',
});

const errors = ref({
  shipping_first_name: '',
  shipping_last_name: '',
  shipping_phone: '',
  shipping_address_line_1: '',
  shipping_city: '',
  shipping_state: '',
  shipping_postal_code: ''
});

const validateField = (fieldName) => {
  errors.value[fieldName] = '';
  
  if (fieldName === 'shipping_first_name') {
    if (!form.value.shipping_first_name || !form.value.shipping_first_name.trim()) {
      errors.value.shipping_first_name = 'First name is required';
    }
  }
  
  if (fieldName === 'shipping_last_name') {
    if (!form.value.shipping_last_name || !form.value.shipping_last_name.trim()) {
      errors.value.shipping_last_name = 'Last name is required';
    }
  }
  
  if (fieldName === 'shipping_phone') {
    const phone = form.value.shipping_phone ? form.value.shipping_phone.toString().trim() : '';
    if (!phone) {
      errors.value.shipping_phone = 'Mobile number is required';
    } else if (!/^\d{10}$/.test(phone)) {
      errors.value.shipping_phone = 'Please enter a valid 10-digit mobile number';
    }
  }
  
  if (fieldName === 'shipping_address_line_1') {
    if (!form.value.shipping_address_line_1 || !form.value.shipping_address_line_1.trim()) {
      errors.value.shipping_address_line_1 = 'Address line 1 is required';
    }
  }
  
  if (fieldName === 'shipping_city') {
    if (!form.value.shipping_city || !form.value.shipping_city.trim()) {
      errors.value.shipping_city = 'City is required';
    }
  }
  
  if (fieldName === 'shipping_state') {
    if (!form.value.shipping_state || !form.value.shipping_state.trim()) {
      errors.value.shipping_state = 'State is required';
    }
  }
  
  if (fieldName === 'shipping_postal_code') {
    const pin = form.value.shipping_postal_code ? form.value.shipping_postal_code.toString().trim() : '';
    if (!pin) {
      errors.value.shipping_postal_code = 'Pincode is required';
    } else if (!/^\d{6}$/.test(pin)) {
      errors.value.shipping_postal_code = 'Please enter a valid 6-digit pincode';
    }
  }
};

const validateForm = () => {
  validateField('shipping_first_name');
  validateField('shipping_last_name');
  validateField('shipping_phone');
  validateField('shipping_address_line_1');
  validateField('shipping_city');
  validateField('shipping_state');
  validateField('shipping_postal_code');
  
  return !Object.values(errors.value).some(err => err !== '');
};

const loadCart = () => {
  try {
    cartItems.value = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
  } catch (e) {
    cartItems.value = [];
  }
};

const fetchAddresses = async () => {
  if (!authStore.isAuthenticated) return;
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

watch(() => authStore.isAuthenticated, (isAuth) => {
  if (isAuth) {
    fetchAddresses();
  }
});

const applyAddress = (addr) => {
  form.value.shipping_first_name = addr.first_name || '';
  form.value.shipping_last_name = addr.last_name || '';
  form.value.shipping_phone = addr.phone || '';
  form.value.shipping_address_line_1 = addr.address_line_1 || '';
  form.value.shipping_address_line_2 = addr.address_line_2 || '';
  form.value.shipping_city = addr.city || '';
  form.value.shipping_state = normalizeState(addr.state || 'Tamil Nadu');
  form.value.shipping_postal_code = addr.postal_code || '';
  
  // Clear all errors
  Object.keys(errors.value).forEach(k => errors.value[k] = '');
};

const applySavedAddress = (event) => {
  const id = event.target.value;
  if (!id) {
    // Reset custom
    form.value.shipping_address_line_1 = '';
    form.value.shipping_address_line_2 = '';
    form.value.shipping_city = '';
    form.value.shipping_state = 'Tamil Nadu';
    form.value.shipping_postal_code = '';
    // Clear all errors
    Object.keys(errors.value).forEach(k => errors.value[k] = '');
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

const freeShippingThreshold = ref(1999);
const defaultShippingFee = ref(100);
const stateRates = ref({});
const dispatchTimeText = ref('3-5 working days');

const fetchShippingConfig = async () => {
  try {
    const res = await axios.get('/api/storefront/shipping-rates');
    if (res.data && res.data.success && res.data.data) {
      freeShippingThreshold.value = Number(res.data.data.free_shipping_threshold || 1999);
      defaultShippingFee.value = Number(res.data.data.default_shipping_fee || 100);
      stateRates.value = res.data.data.state_rates || {};
      dispatchTimeText.value = res.data.data.dispatch_time_text || '3-5 working days';
    }
  } catch (err) {
    // fallback 1999
  }
};

const shipping = computed(() => {
  if (cartItems.value.length === 0) return 0;
  const effectiveAmt = subtotal.value - discount.value;
  if (effectiveAmt >= freeShippingThreshold.value) {
    return 0;
  }
  if (form.value.shipping_state) {
    const selectedState = form.value.shipping_state.trim();
    const matchedKey = Object.keys(stateRates.value).find(
      k => k.toLowerCase() === selectedState.toLowerCase()
    );
    if (matchedKey && stateRates.value[matchedKey] !== undefined) {
      return Number(stateRates.value[matchedKey]);
    }
  }
  return defaultShippingFee.value;
});

const grandTotal = computed(() => {
  return subtotal.value - discount.value + shipping.value;
});

const loadCashfreeScript = () => {
  return new Promise((resolve) => {
    if (window.Cashfree) {
      resolve(true);
      return;
    }
    const script = document.createElement('script');
    script.src = 'https://sdk.cashfree.com/js/v3/cashfree.js';
    script.async = true;
    script.onload = () => resolve(true);
    script.onerror = () => resolve(false);
    document.body.appendChild(script);
  });
};

const submitCheckout = async () => {
  if (cartItems.value.length === 0) return;
  
  // Validation
  if (!validateForm()) {
    // Focus and scroll the first invalid input field into view
    const firstError = document.querySelector('.form-input--error');
    if (firstError) {
      firstError.focus();
      firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
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

      // Automatically log in the customer session if access_token is returned
      if (response.data.access_token && response.data.user) {
        authStore.token = response.data.access_token;
        authStore.user = response.data.user;
        localStorage.setItem('auth_token', response.data.access_token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
      }

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

      // 2. Cashfree Online Payment Flow
      const scriptLoaded = await loadCashfreeScript();
      if (!scriptLoaded || !window.Cashfree) {
        alert('Failed to load Cashfree Payment Gateway SDK. Please check your internet connection.');
        localSubmitting.value = false;
        return;
      }

      // Create Cashfree Payment Session using PaymentStore
      let cfData;
      try {
        cfData = await paymentStore.createPaymentSession(localOrderId);
      } catch (err) {
        alert(err.response?.data?.message || err.message || 'Failed to initiate Cashfree payment session.');
        localSubmitting.value = false;
        return;
      }

      if (!cfData.payment_session_id) {
        alert('Payment session could not be established. Please try again.');
        localSubmitting.value = false;
        return;
      }

      const cashfree = window.Cashfree({
        mode: cfData.environment === 'production' ? 'production' : 'sandbox',
      });

      const checkoutOptions = {
        paymentSessionId: cfData.payment_session_id,
        redirectTarget: '_modal',
      };

      cashfree.checkout(checkoutOptions).then(async (result) => {
        if (result.error) {
          console.warn('Cashfree payment interaction dropped or failed:', result.error);
          try {
            await paymentStore.cancelPayment(localOrderId, result.error.message || 'Payment modal dismissed by user');
          } catch (e) {
            console.error('Cancellation sync error:', e);
          }
          alert(result.error.message || 'Payment cancelled or dismissed.');
          localSubmitting.value = false;
          return;
        }

        if (result.paymentDetails || result.redirect) {
          localSubmitting.value = true;
          try {
            // Verify payment status server-side
            const verifyResponse = await paymentStore.verifyPayment({
              order_id: localOrderId,
              cashfree_order_id: cfData.order_id || cfData.order_number,
            });

            if (verifyResponse && verifyResponse.success) {
              orderPlaced.value = true;
              localStorage.removeItem('vibe_cart_items');
              localStorage.removeItem('vibe_applied_coupon');
              emit('update-cart-count');
            } else {
              alert('Payment verification could not be confirmed automatically. Please check your order history.');
              router.push('/my-account?tab=orders');
            }
          } catch (err) {
            console.error('Payment verification failed:', err);
            alert(err.response?.data?.message || err.message || 'Payment verification failed. Please check your order history.');
            router.push('/my-account?tab=orders');
          } finally {
            localSubmitting.value = false;
          }
        }
      });
    }
  } catch (err) {
    console.error('Checkout failed:', err);
    alert(err.response?.data?.message || 'Checkout verification failed. Please try again.');
    localSubmitting.value = false;
  }
};

onMounted(() => {
  fetchShippingConfig();
  loadCart();
  fetchAddresses();
  calculateDiscount();
  fetchIndianStates();
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

.form-input--error {
  border-color: #E11D48 !important; /* Soft Red */
  box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.15) !important;
}

.form-error-msg {
  color: #E11D48; /* Soft Red */
  font-size: 0.75rem;
  font-weight: 500;
  margin-top: 4px;
  display: block;
}
</style>
