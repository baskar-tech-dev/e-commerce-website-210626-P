<template>
  <div>
    <h1 style="color: var(--color-primary); font-size: 2rem; font-weight: 800; margin-bottom: var(--spacing-lg);">Your Shopping Cart</h1>

    <div v-if="cartItems.length === 0" style="text-align: center; padding: 5rem; color: var(--color-text-muted);" class="glass-panel">
      <span style="font-size: 4rem; display: block; margin-bottom: var(--spacing-md);">🛒</span>
      <h3 style="color: var(--color-text-primary); margin-bottom: var(--spacing-xs);">Your cart is currently empty!</h3>
      <p style="margin-bottom: var(--spacing-lg);">Explore our collections to find premium fashion additions.</p>
      <router-link to="/shop" class="btn btn--primary" style="padding: 0.6rem 2rem;">Shop Now</router-link>
    </div>

    <div v-else class="cart-container-wrap">
      <!-- Free Shipping Progress Bar -->
      <div class="free-shipping-card" style="background: #fffcf7; border: 1px solid #f1e6df; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; color: #4a0e2e; margin-bottom: 8px;">
          <span>{{ freeShippingMessage }}</span>
          <span>{{ Math.min(100, Math.round((subtotal / freeShippingThreshold) * 100)) }}%</span>
        </div>
        <div style="height: 10px; background: #e2e8f0; border-radius: 6px; overflow: hidden;">
          <div :style="{ width: `${Math.min(100, Math.round((subtotal / freeShippingThreshold) * 100))}%` }" style="height: 100%; background: linear-gradient(90deg, #d4af37, #4a0e2e); transition: width 0.3s ease;"></div>
        </div>
      </div>

      <div class="cart-layout-grid">
        <!-- Left: Item rows -->
        <div class="cart-items-column">
          <div v-for="item in cartItems" :key="item.product_variant_id" class="glass-panel cart-item-card">
            <!-- Thumbnail -->
            <div class="cart-item-thumbnail">
              <img :src="item.image || '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp'" alt="item thumbnail" loading="lazy" />
            </div>

            <!-- Product meta details -->
            <div class="cart-item-details">
              <router-link :to="`/products/${item.product_uuid || item.product_id}`" class="cart-item-name">
                {{ item.name }}
              </router-link>
              <span class="cart-item-meta">
                Size: {{ item.size || 'OS' }} <span v-if="item.color">| Color: {{ item.color }}</span>
              </span>
              <span class="cart-item-price">₹{{ item.selling_price }}</span>
            </div>

            <!-- Quantity selector -->
            <div class="cart-item-qty">
              <button type="button" class="qty-btn" @click="updateQty(item, -1)">−</button>
              <span class="qty-val">{{ item.quantity }}</span>
              <button type="button" class="qty-btn" @click="updateQty(item, 1)">+</button>
            </div>

            <!-- Subtotal -->
            <div class="cart-item-subtotal">
              ₹{{ item.selling_price * item.quantity }}
            </div>

            <!-- Remove Button -->
            <button type="button" class="cart-item-remove" @click="removeItem(item)" title="Remove item">
              🗑️
            </button>
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
                placeholder="e.g. MAYASREE25" 
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

          <!-- Gift Wrap Option -->
          <div class="glass-panel" style="padding: var(--spacing-md); margin-top: 12px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: #4a0e2e; cursor: pointer;">
              <input type="checkbox" v-model="isGiftWrap" @change="toggleGiftWrap" style="accent-color: #4a0e2e;" />
              🎁 Add Premium Gift Wrap Packaging (+₹99)
            </label>
            <div v-if="isGiftWrap" style="margin-top: 8px;">
              <input 
                type="text" 
                v-model="giftMessage" 
                placeholder="Enter personal gift note / blessing message..." 
                class="form-input" 
                style="font-size: 0.8rem; padding: 6px 10px;" 
              />
            </div>
          </div>

          <!-- Summary panel -->
          <div class="glass-panel" style="padding: var(--spacing-lg); margin-top: 12px;">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Order Summary</div>

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

              <!-- Gift Wrap Fee -->
              <div v-if="isGiftWrap" style="display: flex; justify-content: space-between; color: #0f172a; font-weight: 500;">
                <span>Gift Packaging Fee</span>
                <span>+ ₹99</span>
              </div>

              <!-- Shipping -->
              <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--color-text-muted);">Shipping Delivery</span>
                <span v-if="shipping === 0" style="color: var(--color-success); font-weight: bold;">FREE</span>
                <span v-else style="color: var(--color-text-primary); font-weight: 500;">₹{{ shipping }}</span>
              </div>
            </div>

            <!-- Grand Total -->
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: var(--spacing-lg);">
              <span style="font-weight: 700; color: var(--color-text-primary); font-size: 1.05rem;">Estimated Total</span>
              <span style="font-weight: 800; color: var(--color-primary); font-size: 1.6rem;">₹{{ grandTotal }}</span>
            </div>

            <button class="btn btn--primary" style="width: 100%; padding: 0.75rem; font-weight: bold; font-size: 1.05rem; border-radius: 8px;" @click="goToCheckout">
              Proceed To Checkout ➔
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

const router = useRouter();
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

const isGiftWrap = ref(false);
const giftMessage = ref('');
const freeShippingThreshold = 999;

const freeShippingMessage = computed(() => {
  if (subtotal.value >= freeShippingThreshold) {
    return '🎉 Congratulations! You have unlocked FREE Delivery!';
  }
  const remaining = freeShippingThreshold - subtotal.value;
  return `Add ₹${remaining} more to unlock FREE Express Delivery!`;
});

const toggleGiftWrap = () => {
  localStorage.setItem('vibe_gift_wrap', JSON.stringify({
    enabled: isGiftWrap.value,
    message: giftMessage.value,
    price: 99
  }));
};

const shipping = computed(() => {
  return (subtotal.value - discount.value >= freeShippingThreshold) ? 0 : 100;
});

const grandTotal = computed(() => {
  const giftFee = isGiftWrap.value ? 99 : 0;
  return Math.max(0, subtotal.value - discount.value + shipping.value + giftFee);
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
.cart-item-card {
  padding: var(--spacing-md);
  display: flex;
  gap: var(--spacing-md);
  align-items: center;
  border: 1px solid var(--color-border);
}
.cart-item-thumbnail {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid var(--color-border);
  flex-shrink: 0;
}
.cart-item-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.cart-item-details {
  flex: 1;
}
.cart-item-name {
  color: var(--color-text-primary);
  font-weight: bold;
  font-size: 0.95rem;
  text-decoration: none;
  display: block;
  margin-bottom: 0.25rem;
}
.cart-item-name:hover {
  color: var(--color-primary);
}
.cart-item-meta {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  display: block;
  margin-bottom: 0.25rem;
}
.cart-item-price {
  font-weight: 500;
  font-size: 0.9rem;
  color: var(--color-text-primary);
}
.cart-item-qty {
  display: flex;
  align-items: center;
  background: var(--blush-bg);
  border-radius: 6px;
  border: 1px solid var(--color-border);
  overflow: hidden;
  margin-right: var(--spacing-md);
}
.qty-btn {
  background: none;
  border: none;
  padding: 0.35rem 0.65rem;
  color: var(--color-text-primary);
  font-size: 0.9rem;
  cursor: pointer;
  transition: background-color 0.2s;
}
.qty-btn:hover {
  background-color: var(--color-border);
}
.qty-val {
  width: 25px;
  text-align: center;
  font-size: 0.85rem;
  font-weight: bold;
  color: var(--color-text-primary);
}
.cart-item-subtotal {
  text-align: right;
  min-width: 80px;
  font-weight: bold;
  color: var(--color-text-primary);
}
.cart-item-remove {
  background: none;
  border: none;
  font-size: 1.15rem;
  color: var(--color-danger);
  cursor: pointer;
  padding: 0.25rem var(--spacing-sm);
  transition: transform 0.2s;
}
.cart-item-remove:hover {
  transform: scale(1.1);
}

@media (max-width: 768px) {
  .cart-layout-grid {
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
  }
}

@media (max-width: 576px) {
  .cart-item-card {
    flex-wrap: wrap;
    position: relative;
    align-items: flex-start;
    padding: var(--spacing-md);
  }
  .cart-item-details {
    min-width: 140px;
    margin-right: 24px;
  }
  .cart-item-remove {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 0;
  }
  .cart-item-qty {
    margin-top: var(--spacing-xs);
    margin-right: 0;
  }
  .cart-item-subtotal {
    margin-top: var(--spacing-xs);
    flex: 1;
    text-align: right;
    font-size: 1rem;
  }
}
</style>
