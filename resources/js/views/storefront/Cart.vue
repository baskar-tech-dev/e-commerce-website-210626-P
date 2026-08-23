<template>
  <div>
    <h1 style="color: var(--color-primary); font-size: 2rem; font-weight: 800; margin-bottom: var(--spacing-lg);">Your Shopping Cart</h1>

    <div v-if="cartItems.length === 0" style="text-align: center; padding: 5rem; color: var(--color-text-muted);" class="glass-panel">
      <span style="font-size: 4rem; display: block; margin-bottom: var(--spacing-md);">🛒</span>
      <h3 style="color: var(--color-text-primary); margin-bottom: var(--spacing-xs);">Your cart is currently empty!</h3>
      <p style="margin-bottom: var(--spacing-lg);">Explore our collections to find premium fashion additions.</p>
      <router-link to="/shop" class="btn btn--primary" style="padding: 0.6rem 2rem;">Shop Now</router-link>
    </div>

    <div v-else class="cart-layout-grid">
      <!-- Left: Item rows -->
      <div class="cart-items-column">
        <div v-for="item in cartItems" :key="item.product_variant_id" class="glass-panel cart-item-card">
          <!-- Thumbnail -->
          <div class="cart-item-thumbnail">
            <img :src="item.image || 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=150&auto=format&fit=crop'" alt="item thumbnail" loading="lazy" />
          </div>

          <!-- Product Details & Controls -->
          <div class="cart-item-details">
            <!-- Top Title & Remove Bin Row -->
            <div class="cart-item-title-row">
              <router-link :to="`/products/${item.product_uuid || item.product_id}`" class="cart-item-name">
                {{ item.name }}
              </router-link>
              <button 
                type="button" 
                class="cart-item-remove-btn" 
                @click="removeItem(item)" 
                title="Remove item from cart"
                aria-label="Remove item"
              >
                <Trash2 :size="16" />
              </button>
            </div>

            <!-- Size & Color Meta -->
            <div class="cart-item-meta-row">
              <span class="cart-item-meta">
                Size: <strong style="color: var(--color-text-primary);">{{ item.size || 'OS' }}</strong>
                <span v-if="item.color"> | Color: <strong style="color: var(--color-text-primary);">{{ item.color }}</strong></span>
              </span>
            </div>

            <!-- Footer: Price & Quantity Controls -->
            <div class="cart-item-footer-row">
              <div class="cart-item-price-wrap">
                <span class="cart-item-price">₹{{ item.selling_price }}</span>
              </div>

              <div class="cart-item-controls">
                <div class="cart-item-qty">
                  <button type="button" class="qty-btn" @click="updateQty(item, -1)" aria-label="Decrease quantity">−</button>
                  <span class="qty-val">{{ item.quantity }}</span>
                  <button type="button" class="qty-btn" @click="updateQty(item, 1)" aria-label="Increase quantity">+</button>
                </div>

                <div class="cart-item-subtotal">
                  ₹{{ item.selling_price * item.quantity }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Summary and Voucher -->
      <div class="cart-summary-column">
        <!-- Coupon Voucher panel -->
        <div class="glass-panel" style="padding: var(--spacing-md);">
          <div class="card-header-title" style="font-size: 0.9rem; margin-bottom: var(--spacing-sm); border: none; padding-bottom: 0;">Apply Discount Code</div>
          <form @submit.prevent="validateCoupon" style="display: flex; gap: var(--spacing-xs);">
            <input 
              type="text" 
              v-model="couponCode" 
              placeholder="e.g. VIBE10" 
              class="form-input" 
              style="padding: 0.35rem var(--spacing-sm); font-size: 0.85rem; text-transform: uppercase;"
              :disabled="appliedCoupon" 
            />
            <button 
              type="submit" 
              class="btn" 
              :class="appliedCoupon ? 'btn--secondary' : 'btn--primary'"
              style="padding: 0 var(--spacing-md); font-size: 0.85rem;"
            >
              {{ appliedCoupon ? 'Applied' : 'Apply' }}
            </button>
          </form>
          <div v-if="couponMsg" style="font-size: 0.75rem; margin-top: 0.5rem; font-weight: bold;" :style="appliedCoupon ? 'color: var(--color-success);' : 'color: var(--color-danger);'">
            {{ couponMsg }}
          </div>
          <button v-if="appliedCoupon" class="btn btn--secondary btn--sm" @click="removeCoupon" style="margin-top: 0.5rem; font-size: 0.75rem; padding: 2px 8px;">
            Remove Code
          </button>
        </div>

        <!-- Summary panel -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-sm);">Order Summary</div>

          <!-- Free Shipping Progress Tracker -->
          <div style="background: #FFFDF9; border: 1px solid #E8DDD3; border-radius: 10px; padding: 0.85rem 1rem; margin-bottom: var(--spacing-md); display: flex; flex-direction: column; gap: 6px;">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem;">
              <span v-if="freeShippingRemaining > 0" style="color: #5B163A; font-weight: 600;">
                🚚 Add <strong style="color: #800020;">₹{{ freeShippingRemaining }}</strong> more for <strong>FREE Delivery</strong>!
              </span>
              <span v-else style="color: #0E6245; font-weight: 700; display: flex; align-items: center; gap: 4px;">
                🎉 <strong>Congratulations! You unlocked FREE Delivery</strong>
              </span>
              <span style="font-weight: 700; color: #5B163A; font-size: 0.78rem;">{{ freeShippingProgress }}%</span>
            </div>
            <!-- Progress Bar Track -->
            <div style="width: 100%; height: 6px; background: #F1E9DF; border-radius: 99px; overflow: hidden;">
              <div 
                :style="{ width: freeShippingProgress + '%' }" 
                style="height: 100%; background: linear-gradient(90deg, #d4af37 0%, #5B163A 100%); border-radius: 99px; transition: width 0.4s ease;"
              ></div>
            </div>
            <div style="font-size: 0.72rem; color: #64748b; display: flex; justify-content: space-between;">
              <span>₹0</span>
              <span>Free Delivery on ₹{{ freeShippingThreshold }}+</span>
            </div>
          </div>

          <div style="display: flex; flex-direction: column; gap: var(--spacing-xs); font-size: 0.9rem; padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-border); margin-bottom: var(--spacing-md);">
            <!-- Subtotal -->
            <div style="display: flex; justify-content: space-between;">
              <span style="color: var(--color-text-muted);">Cart Subtotal</span>
              <span style="color: var(--color-text-primary); font-weight: 500;">₹{{ subtotal }}</span>
            </div>

            <!-- Coupon Discount -->
            <div v-if="discount > 0" style="display: flex; justify-content: space-between; color: var(--color-success); font-weight: 500;">
              <span>Voucher Discount</span>
              <span>− ₹{{ discount }}</span>
            </div>

            <!-- Shipping -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <span style="color: var(--color-text-muted);">Shipping Delivery</span>
                <span v-if="shipping > 0" style="display: block; font-size: 0.72rem; color: #94a3b8;">State rates applied at checkout</span>
              </div>
              <span v-if="shipping === 0" style="color: var(--color-success); font-weight: bold;">FREE</span>
              <span v-else style="color: var(--color-text-primary); font-weight: 500;">₹{{ shipping }}</span>
            </div>
          </div>

          <!-- Grand Total -->
          <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: var(--spacing-lg);">
            <span style="font-weight: 700; color: var(--color-text-primary); font-size: 1.05rem;">Estimated Total</span>
            <span style="font-weight: 800; color: var(--color-primary); font-size: 1.6rem;">₹{{ grandTotal }}</span>
          </div>
          <!-- Guest Account Creation Prompt -->
          <div v-if="!authStore.isAuthenticated" style="margin-bottom: var(--spacing-md); padding: var(--spacing-sm); background: #FAF5F0; border: 1px solid rgba(212, 175, 55, 0.35); border-radius: 8px; font-size: 0.85rem; display: flex; flex-direction: column; gap: 0.35rem;">
            <strong style="color: var(--color-primary);">Create an account to save your cart & order history!</strong>
            <button type="button" class="btn btn--secondary btn--sm" style="width: 100%; border-radius: 6px;" @click="authStore.openAuthModal('register', 'cart')">
              ✨ Create Account / Sign In
            </button>
          </div>

          <button class="btn btn--primary" style="width: 100%; padding: 0.75rem; font-weight: bold; font-size: 1.05rem; border-radius: 8px;" @click="goToCheckout">
            Proceed To Checkout ➔
          </button>

          <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #FAF5F0; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 8px; font-size: 0.825rem; color: #475569; display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 1.1rem;">📦</span>
            <span><strong>Dispatch Time:</strong> 3-5 working days</span>
          </div>

          <!-- Return Policy Notice -->
          <ReturnPolicyNotice :compact="true" style="margin-top: 1rem;" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { Trash2 } from 'lucide-vue-next';
import ReturnPolicyNotice from '../../components/ReturnPolicyNotice.vue';

const router = useRouter();
const authStore = useAuthStore();
const emit = defineEmits(['update-cart-count']);

const cartItems = ref([]);
const couponCode = ref('');
const appliedCoupon = ref(null);
const couponMsg = ref('');

const loadCart = () => {
  try {
    cartItems.value = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
  } catch (e) {
    cartItems.value = [];
  }
};

const updateQty = (item, diff) => {
  const index = cartItems.value.findIndex(i => i.product_variant_id === item.product_variant_id);
  if (index >= 0) {
    const newQty = cartItems.value[index].quantity + diff;
    if (newQty > 0 && newQty <= item.stock_quantity) {
      cartItems.value[index].quantity = newQty;
      localStorage.setItem('vibe_cart_items', JSON.stringify(cartItems.value));
      emit('update-cart-count');
    }
  }
};

const removeItem = (item) => {
  const index = cartItems.value.findIndex(i => i.product_variant_id === item.product_variant_id);
  if (index >= 0) {
    cartItems.value.splice(index, 1);
    localStorage.setItem('vibe_cart_items', JSON.stringify(cartItems.value));
    emit('update-cart-count');
  }
};

const subtotal = computed(() => {
  return cartItems.value.reduce((acc, item) => acc + item.selling_price * item.quantity, 0);
});

const discount = computed(() => {
  if (!appliedCoupon.value) return 0;
  const coupon = appliedCoupon.value;
  const amt = subtotal.value;
  
  if (amt < (coupon.min_order_value ?? 0)) return 0;

  if (coupon.type === 'percentage') {
    let disc = (amt * coupon.value) / 100;
    if (coupon.max_discount) {
      disc = Math.min(disc, coupon.max_discount);
    }
    return disc;
  } else {
    return Math.min(coupon.value, amt);
  }
});

const freeShippingThreshold = ref(1999);
const defaultShippingFee = ref(100);

const fetchShippingConfig = async () => {
  try {
    const res = await axios.get('/api/storefront/shipping-rates');
    if (res.data && res.data.success && res.data.data) {
      freeShippingThreshold.value = Number(res.data.data.free_shipping_threshold || 1999);
      defaultShippingFee.value = Number(res.data.data.default_shipping_fee || 100);
    }
  } catch (err) {
    // fallback 1999
  }
};

const effectiveAmount = computed(() => {
  return Math.max(0, subtotal.value - discount.value);
});

const freeShippingRemaining = computed(() => {
  const diff = freeShippingThreshold.value - effectiveAmount.value;
  return diff > 0 ? diff : 0;
});

const freeShippingProgress = computed(() => {
  if (freeShippingThreshold.value <= 0) return 100;
  const pct = Math.round((effectiveAmount.value / freeShippingThreshold.value) * 100);
  return Math.min(100, Math.max(0, pct));
});

const shipping = computed(() => {
  if (cartItems.value.length === 0) return 0;
  return (effectiveAmount.value >= freeShippingThreshold.value) ? 0 : defaultShippingFee.value;
});

const grandTotal = computed(() => {
  return subtotal.value - discount.value + shipping.value;
});

const validateCoupon = async () => {
  if (!couponCode.value) return;
  couponMsg.value = '';
  
  try {
    const code = couponCode.value.trim().toUpperCase();
    const response = await axios.get(`/api/storefront/coupons/${code}`);
    if (response.data && response.data.success) {
      const match = response.data.data;

      // Check min amount
      if (subtotal.value < (match.min_order_value ?? 0)) {
        couponMsg.value = `⚠️ Minimum order value of ₹${match.min_order_value} required to use this code`;
        appliedCoupon.value = null;
        return;
      }

      appliedCoupon.value = match;
      couponMsg.value = `✓ Applied! ${match.type === 'percentage' ? match.value + '%' : '₹' + match.value} Discount`;
      
      // Save coupon parameters to localStorage to read during checkout
      localStorage.setItem('vibe_applied_coupon', JSON.stringify(match));
    }
  } catch (err) {
    console.error('Failed to validate voucher:', err);
    couponMsg.value = '⚠️ Coupon code is invalid or inactive';
    appliedCoupon.value = null;
  }
};

const removeCoupon = () => {
  appliedCoupon.value = null;
  couponCode.value = '';
  couponMsg.value = '';
  localStorage.removeItem('vibe_applied_coupon');
};

const goToCheckout = () => {
  router.push('/checkout');
};

onMounted(() => {
  fetchShippingConfig();
  loadCart();
  
  // Reload applied coupon from storage if present
  try {
    const saved = JSON.parse(localStorage.getItem('vibe_applied_coupon'));
    if (saved) {
      appliedCoupon.value = saved;
      couponCode.value = saved.code;
      couponMsg.value = `✓ Applied! ${saved.type === 'percentage' ? saved.value + '%' : '₹' + saved.value} Discount`;
    }
  } catch (e) {}

  // Push guest user with items in cart to create a new customer account
  if (!authStore.isAuthenticated && cartItems.value.length > 0 && !sessionStorage.getItem('guest_cart_prompt_shown')) {
    sessionStorage.setItem('guest_cart_prompt_shown', 'true');
    setTimeout(() => {
      authStore.openAuthModal('register', 'cart');
    }, 400);
  }
});
</script>

<style scoped>
.cart-layout-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: var(--spacing-lg);
}
.cart-items-column {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}
.cart-summary-column {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}
.cart-items-column {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}
.cart-summary-column {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}
.cart-item-card {
  padding: 1.15rem;
  display: flex;
  gap: 1.15rem;
  align-items: flex-start;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  background: #ffffff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.cart-item-card:hover {
  border-color: rgba(91, 22, 58, 0.2);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}
.cart-item-thumbnail {
  width: 84px;
  height: 104px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid var(--color-border);
  flex-shrink: 0;
  background: #FAF5F0;
}
.cart-item-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.cart-item-details {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.cart-item-title-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
}
.cart-item-name {
  color: var(--color-text-primary);
  font-weight: 700;
  font-size: 0.95rem;
  text-decoration: none;
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.cart-item-name:hover {
  color: var(--color-primary);
}
.cart-item-remove-btn {
  background: #FDF2F4;
  border: 1px solid rgba(225, 29, 72, 0.2);
  color: #E11D48;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  padding: 0;
}
.cart-item-remove-btn:hover {
  background: #E11D48;
  color: #ffffff;
  border-color: #E11D48;
  transform: scale(1.08);
  box-shadow: 0 2px 8px rgba(225, 29, 72, 0.25);
}
.cart-item-remove-btn:active {
  transform: scale(0.92);
}
.cart-item-meta-row {
  margin: 2px 0 4px;
}
.cart-item-meta {
  font-size: 0.8rem;
  color: #7A726A;
  letter-spacing: 0.01em;
}
.cart-item-footer-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
  flex-wrap: wrap;
  gap: 10px;
}
.cart-item-price {
  font-size: 0.9rem;
  color: #7A726A;
  font-weight: 500;
}
.cart-item-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}
.cart-item-qty {
  display: inline-flex;
  align-items: center;
  background: #FAF5F0;
  border-radius: 20px;
  border: 1px solid #E5DCD3;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.qty-btn {
  background: none;
  border: none;
  width: 28px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #5B163A;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease;
  padding: 0;
}
.qty-btn:hover {
  background-color: rgba(91, 22, 58, 0.1);
  color: #5B163A;
}
.qty-val {
  min-width: 22px;
  text-align: center;
  font-size: 0.85rem;
  font-weight: 700;
  color: #2D2424;
}
.cart-item-subtotal {
  font-size: 1.05rem;
  font-weight: 800;
  color: #5B163A;
  min-width: 55px;
  text-align: right;
}

@media (max-width: 768px) {
  .cart-layout-grid {
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
  }
}

@media (max-width: 480px) {
  .cart-item-card {
    padding: 0.9rem;
    gap: 0.85rem;
  }
  .cart-item-thumbnail {
    width: 72px;
    height: 90px;
  }
  .cart-item-name {
    font-size: 0.88rem;
  }
  .cart-item-footer-row {
    margin-top: 4px;
    gap: 8px;
  }
  .cart-item-controls {
    gap: 8px;
  }
  .cart-item-subtotal {
    font-size: 0.95rem;
  }
}
</style>
