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
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: var(--spacing-xs); flex-wrap: wrap; gap: 0.5rem;">
              <div class="card-header-title" style="margin: 0; border: none; padding: 0;">1. Delivery Address</div>
              <button 
                v-if="addressBook.length && !showNewAddressForm" 
                type="button" 
                class="btn btn--outline btn--sm" 
                @click="startNewAddress"
                style="font-size: 0.8rem; padding: 4px 12px;"
              >
                ➕ Add New Address
              </button>
              <button 
                v-else-if="addressBook.length && showNewAddressForm" 
                type="button" 
                class="btn btn--secondary btn--sm" 
                @click="cancelNewAddress"
                style="font-size: 0.8rem; padding: 4px 12px;"
              >
                ✕ Pick Saved Address
              </button>
            </div>

            <!-- Saved address cards multi-select grid -->
            <div v-if="addressBook.length && !showNewAddressForm" class="checkout-addresses-grid">
              <div 
                v-for="addr in addressBook" 
                :key="addr.id" 
                class="checkout-addr-card"
                :class="{ 'checkout-addr-card--selected': selectedAddressId === addr.id }"
                @click="selectSavedAddress(addr)"
              >
                <div class="checkout-addr-card__header">
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <input 
                      type="radio" 
                      name="delivery_address" 
                      :checked="selectedAddressId === addr.id" 
                      style="accent-color: var(--color-primary); cursor: pointer; width: 16px; height: 16px;" 
                    />
                    <span class="checkout-addr-pill">
                      <span v-if="addr.label?.toLowerCase() === 'home'">🏠</span>
                      <span v-else-if="addr.label?.toLowerCase() === 'office'">🏢</span>
                      <span v-else-if="addr.label?.toLowerCase() === 'boutique'">👗</span>
                      <span v-else>📍</span>
                      {{ addr.label || 'Home' }}
                    </span>
                  </div>
                  <span v-if="addr.is_default_shipping" class="address-default-badge" style="font-size: 0.68rem;">
                    ★ Default
                  </span>
                </div>

                <div style="font-weight: 700; color: #1e293b; font-size: 0.92rem; margin-bottom: 2px;">
                  {{ addr.first_name }} {{ addr.last_name }}
                </div>
                <div style="font-size: 0.82rem; color: #475569; line-height: 1.45;">
                  {{ addr.address_line_1 }}<span v-if="addr.address_line_2">, {{ addr.address_line_2 }}</span><br />
                  <span v-if="addr.landmark" style="color: #64748b; font-size: 0.78rem;">🚩 {{ addr.landmark }}<br /></span>
                  {{ addr.city }}, {{ addr.state }} - <strong>{{ addr.postal_code }}</strong><br />
                  📞 {{ addr.phone }}
                </div>
              </div>

              <!-- '+ Add New Delivery Address' Card Tile -->
              <div class="checkout-addr-card checkout-addr-card--add-new" @click="startNewAddress">
                <div style="font-size: 1.8rem; margin-bottom: 4px;">➕</div>
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--color-primary);">Add New Address</div>
                <div style="font-size: 0.78rem; color: #64748b; text-align: center; margin-top: 2px;">
                  Deliver to another location or family member
                </div>
              </div>
            </div>

            <!-- Custom / New Address Form -->
            <form v-show="!addressBook.length || showNewAddressForm" id="shipping-form" @submit.prevent style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <!-- Address Type selector if authenticated -->
              <div v-if="authStore.isAuthenticated" class="form-group" style="margin-bottom: 4px;">
                <label class="form-label" style="font-size: 0.82rem; font-weight: 600; color: #334155;">Address Type / Label</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                  <button 
                    type="button" 
                    v-for="lbl in ['Home', 'Office', 'Boutique', 'Other']" 
                    :key="lbl"
                    @click="form.address_label = lbl"
                    class="label-chip-btn"
                    :class="{ 'label-chip-btn--active': form.address_label === lbl }"
                    style="padding: 4px 12px; font-size: 0.78rem;"
                  >
                    <span v-if="lbl === 'Home'">🏠</span>
                    <span v-else-if="lbl === 'Office'">🏢</span>
                    <span v-else-if="lbl === 'Boutique'">👗</span>
                    <span v-else>📍</span>
                    {{ lbl }}
                  </button>
                </div>
              </div>

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

              <div class="checkout-form-row-2">
                <div class="form-group">
                  <label class="form-label">Mobile Number *</label>
                  <input 
                    type="tel" 
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
                  <label class="form-label">Email Address *</label>
                  <input 
                    type="email" 
                    v-model="form.shipping_email" 
                    @blur="validateField('shipping_email')"
                    @input="validateField('shipping_email')"
                    placeholder="you@example.com" 
                    class="form-input" 
                    :class="{ 'form-input--error': errors.shipping_email }"
                  />
                  <span v-if="errors.shipping_email" class="form-error-msg">
                    {{ errors.shipping_email }}
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Address Line 1 (Flat, House no., Building, Street) *</label>
                <input 
                  type="text" 
                  v-model="form.shipping_address_line_1" 
                  @blur="validateField('shipping_address_line_1')"
                  @input="validateField('shipping_address_line_1')"
                  placeholder="Flat No, Wing, Building Name, Street" 
                  class="form-input" 
                  :class="{ 'form-input--error': errors.shipping_address_line_1 }"
                />
                <span v-if="errors.shipping_address_line_1" class="form-error-msg">
                  {{ errors.shipping_address_line_1 }}
                </span>
              </div>

              <div class="form-group">
                <label class="form-label">Address Line 2 (Area, Colony, Landmark)</label>
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
                    placeholder="641604" 
                    maxlength="6"
                    class="form-input" 
                    :class="{ 'form-input--error': errors.shipping_postal_code }"
                  />
                  <span v-if="errors.shipping_postal_code" class="form-error-msg">
                    {{ errors.shipping_postal_code }}
                  </span>
                </div>
              </div>

              <!-- Save to Address Book Checkbox if logged in -->
              <div v-if="authStore.isAuthenticated" class="form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                <input 
                  type="checkbox" 
                  id="save_addr_checkout" 
                  v-model="form.save_to_address_book" 
                  style="width: 16px; height: 16px; accent-color: var(--color-primary); cursor: pointer;"
                />
                <label for="save_addr_checkout" style="font-size: 0.82rem; color: #334155; font-weight: 600; cursor: pointer;">
                  Save this address to my account for future orders
                </label>
              </div>
            </form>
          </div>

          <!-- Step 2: Payment Method -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md); border: none; padding-bottom: 0;">2. Payment Method</div>
            
            <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <!-- Cashfree Secure Payment Gateway (All-in-One) -->
              <label 
                style="display: flex; flex-direction: column; gap: 0.85rem; color: var(--color-text-primary); cursor: pointer; padding: 1.25rem; border: 1.5px solid #5B163A; border-radius: 12px; background: #FAF5F0; box-shadow: 0 4px 14px rgba(91, 22, 58, 0.08); transition: all 0.2s;"
              >
                <div style="display: flex; align-items: center; justify-content: space-between;">
                  <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <input type="radio" value="online" v-model="form.payment_method" checked style="cursor: pointer; accent-color: #5B163A; width: 18px; height: 18px;" />
                    <span style="font-weight: 700; font-size: 1.05rem; color: #5B163A;">💳 Online Payment (Cashfree Gateway)</span>
                  </div>
                  <span style="display: inline-flex; align-items: center; gap: 4px; background: rgba(37, 211, 102, 0.15); color: #15803d; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 20px;">
                    🔒 100% Secure
                  </span>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center; padding-left: 1.8rem;">
                  <span class="payment-badge-pill">📲 UPI</span>
                  <span class="payment-badge-pill">Google Pay</span>
                  <span class="payment-badge-pill">PhonePe</span>
                  <span class="payment-badge-pill">Paytm</span>
                  <span class="payment-badge-pill">💳 Debit / Credit Cards</span>
                  <span class="payment-badge-pill">NetBanking</span>
                </div>

                <div style="font-size: 0.78rem; color: #7A726A; padding-left: 1.8rem; line-height: 1.4;">
                  Fast, secure instant checkout powered by Cashfree Payments with 256-bit SSL encryption.
                </div>
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
                <span style="color: var(--color-text-muted);">Items Total</span>
                <span>₹{{ subtotal }}</span>
              </div>
              <div v-if="discount > 0" style="display: flex; justify-content: space-between; color: var(--color-success);">
                <span>Voucher Code</span>
                <span>− ₹{{ discount }}</span>
              </div>
              <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #64748b; padding-left: 0.5rem;">
                <span>↳ Includes Estimated 5% GST</span>
                <span>₹{{ estimatedGstAmount.toFixed(2) }}</span>
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
  shipping_email: '',
  shipping_address_line_1: '',
  shipping_address_line_2: '',
  shipping_city: '',
  shipping_state: 'Tamil Nadu',
  shipping_postal_code: '',
  shipping_map_url: '',
  save_to_address_book: true,
  address_label: 'Home',
  payment_method: 'online',
  coupon_code: '',
});

const selectedAddressId = ref(null);
const showNewAddressForm = ref(false);

const errors = ref({
  shipping_first_name: '',
  shipping_last_name: '',
  shipping_phone: '',
  shipping_email: '',
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
    } else if (!/^\d{10}$/.test(phone.replace(/[^0-9]/g, '').slice(-10))) {
      errors.value.shipping_phone = 'Please enter a valid 10-digit mobile number';
    }
  }

  if (fieldName === 'shipping_email') {
    const email = form.value.shipping_email ? form.value.shipping_email.toString().trim() : '';
    if (!email) {
      errors.value.shipping_email = 'Email address is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      errors.value.shipping_email = 'Please enter a valid email address';
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
  validateField('shipping_email');
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
      
      // Auto pre-fill customer info
      form.value.shipping_first_name = response.data.data.first_name || '';
      form.value.shipping_last_name = response.data.data.last_name || '';
      form.value.shipping_phone = response.data.data.phone || '';
      form.value.shipping_email = response.data.data.email || '';
      
      // Auto-apply default shipping address if exists
      if (addressBook.value.length > 0) {
        const def = addressBook.value.find(a => a.is_default_shipping) || addressBook.value[0];
        if (def) {
          selectedAddressId.value = def.id;
          showNewAddressForm.value = false;
          applyAddress(def);
        }
      } else {
        selectedAddressId.value = 'new';
        showNewAddressForm.value = true;
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
  form.value.shipping_map_url = addr.map_url || '';
  form.value.address_label = addr.label || 'Home';
  
  // Clear all errors
  Object.keys(errors.value).forEach(k => errors.value[k] = '');
};

const selectSavedAddress = (addr) => {
  selectedAddressId.value = addr.id;
  showNewAddressForm.value = false;
  applyAddress(addr);
};

const startNewAddress = () => {
  selectedAddressId.value = 'new';
  showNewAddressForm.value = true;
  form.value.shipping_address_line_1 = '';
  form.value.shipping_address_line_2 = '';
  form.value.shipping_city = '';
  form.value.shipping_state = 'Tamil Nadu';
  form.value.shipping_postal_code = '';
  form.value.shipping_map_url = '';
  form.value.address_label = 'Home';
  form.value.save_to_address_book = true;
  Object.keys(errors.value).forEach(k => errors.value[k] = '');
};

const cancelNewAddress = () => {
  if (addressBook.value.length > 0) {
    showNewAddressForm.value = false;
    const def = addressBook.value.find(a => a.id === selectedAddressId.value) || addressBook.value[0];
    if (def) {
      selectSavedAddress(def);
    }
  }
};

const subtotal = computed(() => {
  return cartItems.value.reduce((acc, item) => acc + item.selling_price * item.quantity, 0);
});

const estimatedGstAmount = computed(() => {
  const net = Math.max(0, subtotal.value - discount.value);
  return Math.round((net - (net / 1.05)) * 100) / 100;
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

.payment-badge-pill {
  font-size: 0.72rem;
  font-weight: 600;
  color: #5B163A;
  background: #ffffff;
  border: 1px solid rgba(91, 22, 58, 0.2);
  padding: 3px 8px;
  border-radius: 6px;
  letter-spacing: 0.02em;
}

/* ========================================================
   CHECKOUT MULTI-ADDRESS CARDS & MAP EMBED STYLES
   ======================================================== */
.checkout-addresses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 12px;
  margin-bottom: var(--spacing-sm);
}

.checkout-addr-card {
  background: #ffffff;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  position: relative;
}

.checkout-addr-card:hover {
  border-color: rgba(74, 14, 46, 0.4);
  box-shadow: 0 4px 12px rgba(74, 14, 46, 0.06);
}

.checkout-addr-card--selected {
  border-color: var(--color-primary);
  background: #fffdfc;
  box-shadow: 0 4px 14px rgba(74, 14, 46, 0.1);
}

.checkout-addr-card--add-new {
  border: 1.5px dashed #cbd5e1;
  background: #fafaf9;
  align-items: center;
  justify-content: center;
  min-height: 140px;
  text-align: center;
  padding: 20px;
}

.checkout-addr-card--add-new:hover {
  border-color: var(--color-primary);
  background: #fff8f5;
}

.checkout-addr-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.checkout-addr-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #f1f5f9;
  color: #334155;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 2px 8px;
  border-radius: 10px;
}

.address-default-badge {
  background: rgba(212, 175, 55, 0.15);
  color: #854d0e;
  border: 1px solid rgba(212, 175, 55, 0.4);
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 12px;
}

.checkout-addr-card__map {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 6px;
  margin-top: 6px;
}

.embedded-map-wrapper {
  position: relative;
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
  background: #f1f5f9;
}

.embedded-map-wrapper--checkout {
  height: 130px;
  border: 1px solid rgba(74, 14, 46, 0.12);
}

.embedded-map-iframe {
  width: 100%;
  height: 100%;
  border: 0;
  display: block;
}

.label-chip-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 20px;
  color: #334155;
  cursor: pointer;
  transition: all 0.2s ease;
}

.label-chip-btn:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.label-chip-btn--active {
  background: var(--color-primary) !important;
  color: #ffffff !important;
  border-color: var(--color-primary) !important;
}
</style>
