<template>
  <div>
    <h1 style="color: #4a0e2e; font-size: 2rem; font-weight: 800; margin-bottom: var(--spacing-lg);">My Account Dashboard</h1>

    <div class="account-layout-grid">
      <!-- Left: Navigation Pills -->
      <div class="glass-panel account-nav-pills">
        <button 
          v-for="t in ['profile', 'addresses', 'orders', 'wishlist']" 
          :key="t"
          type="button"
          class="btn account-nav-btn"
          :class="activeTab === t ? 'btn--primary' : 'btn--secondary'"
          @click="changeTab(t)"
        >
          <span v-if="t === 'profile'">
            <span class="tab-icon">👤</span><span class="tab-text"> Personal Profile</span>
          </span>
          <span v-else-if="t === 'addresses'">
            <span class="tab-icon">📍</span><span class="tab-text"> Address Book</span>
          </span>
          <span v-else-if="t === 'orders'">
            <span class="tab-icon">📦</span><span class="tab-text"> Order History</span>
          </span>
          <span v-else-if="t === 'wishlist'">
            <span class="tab-icon">❤️</span><span class="tab-text"> My Wishlist</span>
          </span>
        </button>
      </div>

      <!-- Right: Sub-tab panel views -->
      <div class="glass-panel" style="padding: var(--spacing-lg); background: #ffffff; border: 1px solid #f1e6df; border-radius: 16px;">
        <div v-if="loading" style="padding: 1rem 0;">
          <SkeletonLoader type="table" :count="4" />
        </div>

        <div v-else>
          <!-- Tab 1: Profile Details -->
          <div v-if="activeTab === 'profile'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-xs); color: #4a0e2e;">Personal Profile Details</div>
            
            <div class="account-form-row-2">
              <div class="form-group">
                <label class="form-label" style="color: #0f172a; font-weight: 600;">First Name</label>
                <div class="form-input" style="background: #fffcf7; color: #0f172a; border: 1px solid #e2e8f0; font-weight: 500;">{{ profile.first_name || '—' }}</div>
              </div>
              <div class="form-group">
                <label class="form-label" style="color: #0f172a; font-weight: 600;">Last Name</label>
                <div class="form-input" style="background: #fffcf7; color: #0f172a; border: 1px solid #e2e8f0; font-weight: 500;">{{ profile.last_name || '—' }}</div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" style="color: #0f172a; font-weight: 600;">Email Address</label>
              <div class="form-input" style="background: #fffcf7; color: #0f172a; border: 1px solid #e2e8f0; font-weight: 500;">{{ profile.email }}</div>
            </div>

            <div class="form-group">
              <label class="form-label" style="color: #0f172a; font-weight: 600;">Helpline Phone</label>
              <div class="form-input" style="background: #fffcf7; color: #0f172a; border: 1px solid #e2e8f0; font-weight: 500;">{{ profile.phone || '—' }}</div>
            </div>
          </div>

          <!-- Tab 2: Addresses CRUD -->
          <div v-if="activeTab === 'addresses'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: var(--spacing-md);">
              <div class="card-header-title" style="border: none; margin: 0; padding: 0; color: #4a0e2e;">Address Book</div>
              <button class="btn btn--primary btn--sm" @click="showAddForm = !showAddForm">
                {{ showAddForm ? 'Cancel Form' : '➕ Add Address' }}
              </button>
            </div>

            <!-- Add Address Form -->
            <div v-if="showAddForm" class="glass-panel" style="padding: var(--spacing-md); border: 1px solid var(--color-primary); background: #fffcf7; margin-bottom: var(--spacing-lg); border-radius: 12px;">
              <form @submit.prevent="saveAddress" style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                <div class="account-form-row-2col">
                  <div class="form-group">
                    <label class="form-label" style="color: #0f172a;">Label (e.g. Home, Office) *</label>
                    <input type="text" v-model="addressForm.label" class="form-input" placeholder="Home" required />
                  </div>
                  <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.8rem;">
                    <input type="checkbox" id="is_default" v-model="addressForm.is_default_shipping" style="accent-color: #4a0e2e;" />
                    <label for="is_default" style="color: #0f172a; font-size: 0.85rem; font-weight: bold; cursor: pointer;">Set as Default Shipping Address</label>
                  </div>
                </div>

                <div class="account-form-row-2">
                  <div class="form-group">
                    <label class="form-label" style="color: #0f172a;">First Name *</label>
                    <input type="text" v-model="addressForm.first_name" class="form-input" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label" style="color: #0f172a;">Last Name *</label>
                    <input type="text" v-model="addressForm.last_name" class="form-input" required />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label" style="color: #0f172a;">Phone *</label>
                  <input type="text" v-model="addressForm.phone" class="form-input" required />
                </div>

                <div class="form-group">
                  <label class="form-label" style="color: #0f172a;">Address Line 1 *</label>
                  <input type="text" v-model="addressForm.address_line_1" class="form-input" required />
                </div>

                <div class="form-group">
                  <label class="form-label" style="color: #0f172a;">Address Line 2</label>
                  <input type="text" v-model="addressForm.address_line_2" class="form-input" />
                </div>

                <div class="account-form-row-3">
                  <div class="form-group">
                    <label class="form-label" style="color: #0f172a;">City *</label>
                    <input type="text" v-model="addressForm.city" class="form-input" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label" style="color: #0f172a;">State *</label>
                    <input type="text" v-model="addressForm.state" class="form-input" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label" style="color: #0f172a;">Postal Pincode *</label>
                    <input type="text" v-model="addressForm.postal_code" class="form-input" required />
                  </div>
                </div>

                <div style="display: flex; gap: var(--spacing-sm); justify-content: flex-end; margin-top: 0.5rem;">
                  <button type="submit" class="btn btn--primary btn--sm" :disabled="savingAddress">
                    Save Address
                  </button>
                </div>
              </form>
            </div>

            <!-- List Addresses -->
            <div class="addresses-list-grid">
              <div v-for="addr in profile.addresses" :key="addr.id" class="glass-panel" style="padding: var(--spacing-md); border: 1px solid #f1e6df; background: #fffcf7; border-radius: 12px; position: relative;">
                <span class="badge" :class="addr.is_default_shipping ? 'badge--primary' : 'badge--secondary'" style="font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.5rem;">
                  {{ addr.label }} {{ addr.is_default_shipping ? '(Default)' : '' }}
                </span>
                
                <div style="font-weight: bold; color: #0f172a; margin-bottom: 0.25rem;">{{ addr.first_name }} {{ addr.last_name }}</div>
                <div style="font-size: 0.85rem; color: #475569; line-height: 1.4; margin-bottom: var(--spacing-md);">
                  {{ addr.address_line_1 }}<br />
                  <span v-if="addr.address_line_2">{{ addr.address_line_2 }}<br /></span>
                  {{ addr.city }}, {{ addr.state }} - {{ addr.postal_code }}<br />
                  📞 {{ addr.phone }}
                </div>

                <div style="display: flex; gap: var(--spacing-sm); justify-content: flex-end; border-top: 1px solid #f1e6df; padding-top: 0.5rem;">
                  <button class="btn btn--secondary btn--sm" @click="editAddress(addr)" style="font-size: 0.75rem; padding: 2px 8px;">✏️ Edit</button>
                  <button class="btn btn--danger btn--sm" @click="deleteAddress(addr.id)" style="font-size: 0.75rem; padding: 2px 8px;">🗑️ Delete</button>
                </div>
              </div>
              <div v-if="profile.addresses?.length === 0 && !showAddForm" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                Your shipping address book is empty. Add an address to speed up checkout.
              </div>
            </div>
          </div>

          <!-- Tab 3: Order History -->
          <div v-if="activeTab === 'orders'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-xs); color: #4a0e2e;">Order History Log</div>

            <div v-for="order in profile.orders" :key="order.id" class="glass-panel" style="padding: var(--spacing-md); border: 1px solid #f1e6df; background: #fffcf7; border-radius: 12px; display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <!-- Top meta bar -->
              <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; font-size: 0.85rem;">
                <div>
                  <strong style="color: #0f172a;">Order No:</strong> <span style="font-family: monospace; color: #4a0e2e; font-weight: 700;">{{ order.order_number }}</span>
                  <span style="color: #64748b; margin-left: 1rem;">Placed on: {{ formatDate(order.created_at) }}</span>
                </div>
                <div>
                  <span :class="['badge', getStatusBadgeClass(order.status)]" style="text-transform: uppercase; font-weight: bold;">
                    {{ order.status }}
                  </span>
                </div>
              </div>

              <!-- Content and items list summary -->
              <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.25rem 0;">
                <div>
                  <div style="font-size: 0.85rem; color: #475569;">
                    Grand Total: <strong style="color: #4a0e2e; font-size: 1.1rem;">₹{{ order.grand_total }}</strong>
                  </div>
                  <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">
                    Payment: {{ order.payment_method }} ({{ order.payment_status.toUpperCase() }})
                  </div>
                </div>
                
                <div style="display: flex; gap: var(--spacing-xs);">
                  <button class="btn btn--secondary btn--sm" @click="reorder(order)" title="Add all items from this order to cart">
                    🔄 Reorder All
                  </button>
                  <button 
                    v-if="order.payment_method && order.payment_method.toLowerCase() === 'razorpay' && order.payment_status !== 'paid' && order.status !== 'cancelled'"
                    class="btn btn--primary btn--sm"
                    :disabled="retryingOrderId === order.id"
                    @click="retryPayment(order)"
                  >
                    {{ retryingOrderId === order.id ? 'Loading...' : '💳 Pay Now' }}
                  </button>
                  <button class="btn btn--secondary btn--sm" @click="toggleOrderDetails(order.id)">
                    {{ expandedOrderNo === order.id ? 'Hide Details' : '🔍 View Items & Tracking' }}
                  </button>
                </div>
              </div>

              <!-- Expanded tracking timeline & items list grid -->
              <div v-if="expandedOrderNo === order.id" style="background: #ffffff; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 16px;">
                <!-- Tracking Timeline -->
                <div style="background: #fffcf7; border-radius: 8px; padding: 12px; border: 1px solid #f1e6df;">
                  <div style="font-size: 0.85rem; font-weight: bold; color: #4a0e2e; margin-bottom: 12px;">📍 ORDER DISPATCH TIMELINE</div>
                  
                  <div style="display: flex; justify-content: space-between; font-size: 0.75rem; text-align: center; position: relative;">
                    <div :style="{ color: getTimelineStepActive(order.status, 1) ? '#16a34a' : '#94a3b8', fontWeight: getTimelineStepActive(order.status, 1) ? 'bold' : 'normal' }">
                      <div>✓</div><div>Order Placed</div>
                    </div>
                    <div :style="{ color: getTimelineStepActive(order.status, 2) ? '#16a34a' : '#94a3b8', fontWeight: getTimelineStepActive(order.status, 2) ? 'bold' : 'normal' }">
                      <div>✓</div><div>Confirmed</div>
                    </div>
                    <div :style="{ color: getTimelineStepActive(order.status, 3) ? '#16a34a' : '#94a3b8', fontWeight: getTimelineStepActive(order.status, 3) ? 'bold' : 'normal' }">
                      <div>✓</div><div>Processing</div>
                    </div>
                    <div :style="{ color: getTimelineStepActive(order.status, 4) ? '#16a34a' : '#94a3b8', fontWeight: getTimelineStepActive(order.status, 4) ? 'bold' : 'normal' }">
                      <div>✓</div><div>Shipped</div>
                    </div>
                    <div :style="{ color: getTimelineStepActive(order.status, 5) ? '#16a34a' : '#94a3b8', fontWeight: getTimelineStepActive(order.status, 5) ? 'bold' : 'normal' }">
                      <div>✓</div><div>Delivered</div>
                    </div>
                  </div>

                  <div v-if="order.tracking_number" style="margin-top: 10px; font-size: 0.8rem; color: #0f172a;">
                    🚚 Courier: <strong>{{ order.courier_name || 'Express Dispatch' }}</strong> | AWB: <strong style="font-family: monospace; color: #4a0e2e;">{{ order.tracking_number }}</strong>
                  </div>
                </div>

                <!-- Items Purchased -->
                <div v-for="item in order.items" :key="item.id" style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding: var(--spacing-xs) 0; border-bottom: 1px dashed #cbd5e1;">
                  <div>
                    <span style="color: #0f172a; font-weight: bold;">{{ item.product_name }}</span>
                    <span style="font-size: 0.75rem; color: #64748b; margin-left: 0.5rem;">SKU: {{ item.variant_sku }}</span>
                  </div>
                  <div>
                    <span style="color: #475569;">{{ item.quantity }} x ₹{{ item.price }}</span>
                    <strong style="color: #4a0e2e; margin-left: 1rem;">₹{{ item.subtotal }}</strong>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="profile.orders?.length === 0" style="text-align: center; padding: 4rem; color: #64748b;">
              You haven't placed any orders yet.
            </div>
          </div>

          <!-- Tab 4: Wishlist -->
          <div v-if="activeTab === 'wishlist'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-xs); color: #4a0e2e;">My Wishlist</div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
              <div v-for="w in wishlist" :key="w.id" class="glass-panel" style="padding: var(--spacing-md); display: flex; gap: var(--spacing-md); align-items: center; border: 1px solid #f1e6df; background: #fffcf7; border-radius: 12px;">
                <!-- Cover -->
                <div style="width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 1px solid #f1e6df; flex-shrink: 0;">
                  <img v-protect-image :src="w.image || '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp'" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" />
                </div>
                
                <!-- Info -->
                <div style="flex: 1; overflow: hidden;">
                  <router-link :to="`/products/${w.uuid || w.id}`" style="color: #0f172a; font-weight: bold; font-size: 0.85rem; text-decoration: none; display: block; margin-bottom: 2px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                    {{ w.name }}
                  </router-link>
                  <span style="font-size: 0.85rem; font-weight: bold; color: #4a0e2e; display: block; margin-bottom: var(--spacing-xs);">₹{{ w.selling_price }}</span>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: var(--spacing-xs);">
                  <router-link :to="`/products/${w.uuid || w.id}`" class="btn btn--primary btn--sm" style="padding: 0.35rem; font-size: 0.75rem;">Buy</router-link>
                  <button class="btn btn--danger btn--sm" @click="removeWishlist(w.id)" style="padding: 0.35rem; font-size: 0.75rem;">🗑️</button>
                </div>
              </div>

              <div v-if="wishlist.length === 0" style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: #64748b;">
                Your wishlist is empty. Tap ❤️ on product cards to add products here.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import SkeletonLoader from '../../components/common/SkeletonLoader.vue';

const emit = defineEmits(['update-wishlist-count']);

const route = useRoute();
const router = useRouter();

const activeTab = ref(route.query.tab || 'profile');
const loading = ref(true);
const profile = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  addresses: [],
  orders: [],
});

const wishlist = ref([]);
const showAddForm = ref(false);
const savingAddress = ref(false);
const expandedOrderNo = ref(null);

const getTimelineStepActive = (status, step) => {
  const s = (status || '').toLowerCase();
  const map = {
    'pending': 1,
    'confirmed': 2,
    'processing': 3,
    'shipped': 4,
    'delivered': 5,
  };
  const currentStep = map[s] || 1;
  return currentStep >= step;
};

const reorder = (order) => {
  if (!order || !order.items || order.items.length === 0) return;
  try {
    let cart = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
    order.items.forEach(item => {
      const existing = cart.find(c => c.id === item.product_id && c.size === item.variant_sku);
      if (existing) {
        existing.quantity += item.quantity;
      } else {
        cart.push({
          id: item.product_id,
          product_variant_id: item.product_variant_id,
          name: item.product_name,
          price: item.price,
          selling_price: item.price,
          size: item.variant_sku || 'OS',
          quantity: item.quantity,
          image: '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp'
        });
      }
    });
    localStorage.setItem('vibe_cart_items', JSON.stringify(cart));
    alert('✓ All items from this order have been added to your cart!');
    router.push('/cart');
  } catch (err) {
    console.error('Failed to reorder items:', err);
  }
};

const addressForm = ref({
  id: null,
  label: '',
  first_name: '',
  last_name: '',
  phone: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  state: '',
  postal_code: '',
  is_default_shipping: false,
});

const fetchProfileDetails = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/customer/profile');
    if (response.data && response.data.success) {
      profile.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load profile params:', err);
  } finally {
    loading.value = false;
  }
};

const saveAddress = async () => {
  savingAddress.value = true;
  try {
    const response = await axios.post('/api/customer/addresses', addressForm.value);
    if (response.data && response.data.success) {
      showAddForm.value = false;
      resetAddressForm();
      await fetchProfileDetails();
    }
  } catch (err) {
    console.error('Failed to save address details:', err);
    alert(err.response?.data?.message || 'Failed to save address details');
  } finally {
    savingAddress.value = false;
  }
};

const resetAddressForm = () => {
  addressForm.value = {
    id: null,
    label: '',
    first_name: '',
    last_name: '',
    phone: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    postal_code: '',
    is_default_shipping: false,
  };
};

const editAddress = (addr) => {
  showAddForm.value = true;
  addressForm.value = {
    id: addr.id,
    label: addr.label,
    first_name: addr.first_name,
    last_name: addr.last_name,
    phone: addr.phone,
    address_line_1: addr.address_line_1,
    address_line_2: addr.address_line_2 || '',
    city: addr.city,
    state: addr.state,
    postal_code: addr.postal_code,
    is_default_shipping: !!addr.is_default_shipping,
  };
};

const deleteAddress = async (id) => {
  if (!confirm('Are you sure you want to remove this address?')) return;
  try {
    const response = await axios.delete(`/api/customer/addresses/${id}`);
    if (response.data && response.data.success) {
      await fetchProfileDetails();
    }
  } catch (err) {
    console.error('Failed to delete address:', err);
  }
};

const loadWishlist = () => {
  try {
    wishlist.value = JSON.parse(localStorage.getItem('vibe_wishlist_items') || '[]');
  } catch (e) {
    wishlist.value = [];
  }
};

const removeWishlist = (id) => {
  const index = wishlist.value.findIndex(item => item.id === id);
  if (index >= 0) {
    wishlist.value.splice(index, 1);
    localStorage.setItem('vibe_wishlist_items', JSON.stringify(wishlist.value));
    loadWishlist();
    emit('update-wishlist-count');
  }
};

const toggleOrderDetails = (orderId) => {
  if (expandedOrderNo.value === orderId) {
    expandedOrderNo.value = null;
  } else {
    expandedOrderNo.value = orderId;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'delivered': return 'badge--success';
    case 'cancelled': return 'badge--danger';
    case 'processing':
    case 'shipped': return 'badge--warning';
    default: return 'badge--secondary';
  }
};

const retryingOrderId = ref(null);

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

const retryPayment = async (order) => {
  retryingOrderId.value = order.id;
  try {
    const scriptLoaded = await loadRazorpayScript();
    if (!scriptLoaded) {
      alert('Failed to load Razorpay Payment Gateway script.');
      retryingOrderId.value = null;
      return;
    }

    const rzpOrderResponse = await axios.post('/api/payment/create-order', {
      order_id: order.id,
    });

    if (!rzpOrderResponse.data.success) {
      alert('Failed to initiate payment.');
      retryingOrderId.value = null;
      return;
    }

    const rzpData = rzpOrderResponse.data.data;
    
    const options = {
      key: rzpData.key_id,
      amount: rzpData.amount,
      currency: rzpData.currency,
      name: 'Maya Sree South Indian Fashion',
      description: 'Order Payment #' + rzpData.order_number,
      order_id: rzpData.razorpay_order_id,
      handler: async function (paymentResponse) {
        retryingOrderId.value = order.id;
        try {
          const verifyResponse = await axios.post('/api/payment/verify', {
            order_id: order.id,
            razorpay_order_id: paymentResponse.razorpay_order_id,
            razorpay_payment_id: paymentResponse.razorpay_payment_id,
            razorpay_signature: paymentResponse.razorpay_signature,
          });

          if (verifyResponse.data.success) {
            alert('Payment received and verified successfully!');
            await fetchProfileDetails();
          } else {
            alert('Payment verification failed.');
          }
        } catch (err) {
          console.error(err);
          alert(err.response?.data?.message || 'Payment verification failed.');
        } finally {
          retryingOrderId.value = null;
        }
      },
      prefill: {
        name: rzpData.customer.name,
        contact: rzpData.customer.phone,
        email: rzpData.customer.email,
      },
      theme: {
        color: '#2d051c',
      },
      modal: {
        ondismiss: function () {
          retryingOrderId.value = null;
        }
      }
    };

    const rzp = new window.Razorpay(options);
    rzp.open();

  } catch (err) {
    console.error('Failed to retry payment:', err);
    alert(err.response?.data?.message || 'Failed to initiate payment. Please try again.');
    retryingOrderId.value = null;
  }
};

const changeTab = (t) => {
  activeTab.value = t;
  router.replace({ query: { ...route.query, tab: t } });
  scrollActiveTabIntoView();
};

const scrollActiveTabIntoView = () => {
  nextTick(() => {
    const activeEl = document.querySelector('.account-nav-pills .btn--primary');
    if (activeEl) {
      activeEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
  });
};

watch(() => route.query.tab, (newTab) => {
  if (newTab && newTab !== activeTab.value) {
    activeTab.value = newTab;
    scrollActiveTabIntoView();
  }
});

onMounted(() => {
  fetchProfileDetails();
  loadWishlist();
  scrollActiveTabIntoView();
});
</script>

<style scoped>
.account-layout-grid {
  display: grid;
  grid-template-columns: 1fr 3fr;
  gap: var(--spacing-lg);
}

.account-nav-pills {
  padding: var(--spacing-md);
  height: fit-content;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.account-nav-btn {
  text-align: left;
  justify-content: flex-start;
  text-transform: capitalize;
  width: 100%;
}

.account-form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

.account-form-row-2col {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: var(--spacing-md);
}

.account-form-row-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: var(--spacing-md);
}

.addresses-list-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

@media (max-width: 768px) {
  .account-layout-grid {
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
  }

  .account-nav-pills {
    flex-direction: row;
    overflow-x: auto;
    white-space: nowrap;
    padding: var(--spacing-sm);
    scrollbar-width: none;
  }

  .account-nav-pills::-webkit-scrollbar {
    display: none;
  }

  .account-nav-pills button {
    flex-shrink: 0;
  }
}

@media (max-width: 576px) {
  .tab-text {
    display: none !important;
  }

  .account-nav-pills {
    justify-content: center !important;
    gap: 16px !important;
  }

  .account-nav-btn {
    width: 48px !important;
    height: 48px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    font-size: 1.25rem !important;
  }

  .account-form-row-2,
  .account-form-row-2col,
  .account-form-row-3,
  .addresses-list-grid {
    grid-template-columns: 1fr;
    gap: var(--spacing-sm);
  }

  .account-form-row-2col div[style*="margin-top"] {
    margin-top: 0 !important;
  }
}
</style>
