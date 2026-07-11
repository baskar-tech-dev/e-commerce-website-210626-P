<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" style="text-align: center; padding: 6rem;">
      <div class="stat-card__value" style="font-size: 1.2rem;">Loading streetwear details...</div>
    </div>

    <!-- Product Layout -->
    <div v-else-if="product">
      <!-- Breadcrumbs -->
      <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: var(--spacing-lg);">
        <router-link to="/" style="color: var(--color-text-muted); text-decoration: none;">Home</router-link>
        <span style="margin: 0 0.5rem;">/</span>
        <router-link to="/shop" style="color: var(--color-text-muted); text-decoration: none;">Shop</router-link>
        <span style="margin: 0 0.5rem;">/</span>
        <span style="color: var(--color-text-primary);">{{ product.name }}</span>
      </div>

      <!-- Main Layout -->
      <div class="product-detail-layout">
        <!-- Left: Image Gallery -->
        <div class="product-detail-gallery">
          <!-- Thumbnails list -->
          <div 
            v-if="product.images && product.images.length > 1" 
            class="product-detail-thumbnails"
          >
            <div 
              v-for="img in product.images" 
              :key="img.id" 
              class="glass-panel" 
              style="width: 70px; height: 70px; flex-shrink: 0; padding: 2px; border-radius: 8px; cursor: pointer; border: 1px solid var(--color-border);"
              :style="activeImagePath === img.image_path ? 'border-color: var(--color-primary); border-width: 2px;' : ''"
              @click="activeImagePath = img.image_path"
            >
              <img v-protect-image :src="img.image_path" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;" alt="thumbnail" loading="lazy" />
            </div>
          </div>

          <!-- Large display image -->
          <div class="glass-panel product-detail-main-img">
            <img 
              v-protect-image
              :src="activeImagePath || 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=600&auto=format&fit=crop'" 
              style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; background: #ffffff;" 
              alt="large preview" 
              loading="lazy"
            />
            <!-- Left & Right Switch Controls -->
            <template v-if="product && product.images && product.images.length > 1">
              <button 
                class="gallery-nav-btn prev-btn" 
                @click.stop="prevImage" 
                aria-label="Previous Image"
              >
                <ChevronLeft :size="24" />
              </button>
              <button 
                class="gallery-nav-btn next-btn" 
                @click.stop="nextImage" 
                aria-label="Next Image"
              >
                <ChevronRight :size="24" />
              </button>
            </template>
          </div>
        </div>

        <!-- Right: Parameters and actions -->
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div>
            <div style="display: flex; gap: 0.5rem; margin-bottom: var(--spacing-xs);">
              <span class="badge badge--secondary" style="font-size: 0.75rem;">{{ product.category?.name }}</span>
            </div>
            <h1 style="color: var(--color-text-primary); font-size: 2.2rem; font-weight: 800; margin: 0; line-height: 1.1;">{{ product.name }}</h1>
          </div>

          <!-- Price split -->
          <div style="display: flex; align-items: baseline; gap: var(--spacing-md); margin-top: var(--spacing-xs);">
            <span style="font-size: 2rem; font-weight: bold; color: var(--color-text-primary);">₹{{ selectedVariant?.selling_price || product.selling_price }}</span>
            <span v-if="hasDiscount" style="font-size: 1.25rem; text-decoration: line-through; color: var(--color-text-muted);">
              MRP ₹{{ selectedVariant?.mrp || product.mrp }}
            </span>
            <span v-if="hasDiscount" class="badge badge--success" style="font-size: 0.8rem; font-weight: bold; margin-left: 0.25rem;">
              SAVE {{ discountPct }}%
            </span>
          </div>

          <!-- Description -->
          <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin: 0;">
            {{ product.description || 'No product description available.' }}
          </p>

          <!-- Variant Selectors (Attributes: Size, Color) -->
          <div class="glass-panel" style="padding: var(--spacing-md); display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div class="card-header-title" style="font-size: 0.95rem; border-bottom: none; padding-bottom: 0;">Configure Style</div>
            
            <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <label class="form-label" style="font-size: 0.8rem; margin: 0;">Available Variants *</label>
              <div style="display: flex; gap: var(--spacing-sm); flex-wrap: wrap;">
                <button 
                  v-for="v in product.variants" 
                  :key="v.id"
                  type="button"
                  class="btn btn--sm"
                  :class="selectedVariant?.id === v.id ? 'btn--primary' : 'btn--secondary'"
                  style="border-radius: 6px; padding: 0.5rem 1rem;"
                  @click="selectVariant(v)"
                >
                  <span style="text-transform: uppercase; font-weight: bold;">
                    Size: {{ v.size || 'OS' }} <span v-if="v.color">| {{ v.color }}</span>
                  </span>
                </button>
              </div>
            </div>

            <!-- Stock status indicator -->
            <div v-if="selectedVariant" style="font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 0.25rem;">
              <span v-if="selectedVariant.stock_quantity > 5" style="color: var(--color-success);">
                🟢 In Stock ({{ selectedVariant.stock_quantity }} units available)
              </span>
              <span v-else-if="selectedVariant.stock_quantity > 0" style="color: var(--color-warning);">
                🟡 Low Stock! Only {{ selectedVariant.stock_quantity }} units left!
              </span>
              <span v-else style="color: var(--color-danger);">
                🔴 Out of Stock!
              </span>
            </div>
          </div>

          <!-- Add to Cart counter actions -->
          <div style="display: flex; gap: var(--spacing-md); align-items: center;">
            <!-- Counter -->
            <div class="glass-panel" style="display: flex; align-items: center; border-radius: 8px; overflow: hidden; padding: 0;">
              <button 
                type="button" 
                class="btn btn--secondary" 
                style="padding: 0.6rem var(--spacing-md); border-radius: 0; background: none; border: none; font-size: 1rem; color: var(--color-text-primary);"
                @click="qty > 1 ? qty-- : null"
                :disabled="!selectedVariant"
              >
                −
              </button>
              <span style="font-weight: bold; width: 40px; text-align: center; color: var(--color-text-primary); font-size: 1.1rem;">{{ qty }}</span>
              <button 
                type="button" 
                class="btn btn--secondary" 
                style="padding: 0.6rem var(--spacing-md); border-radius: 0; background: none; border: none; font-size: 1rem; color: var(--color-text-primary);"
                @click="qty < selectedVariant.stock_quantity ? qty++ : null"
                :disabled="!selectedVariant || qty >= selectedVariant.stock_quantity"
              >
                +
              </button>
            </div>

            <button 
              class="btn btn--primary" 
              style="flex: 1; padding: 0.75rem; font-size: 1.05rem; font-weight: bold; border-radius: 8px;"
              :disabled="!selectedVariant || selectedVariant.stock_quantity === 0"
              @click="addToCart"
            >
              🛒 Add to Cart
            </button>
          </div>
        </div>
      </div>

      <!-- Related Recommendations Carousel -->
      <section v-if="relatedProducts && relatedProducts.length" style="margin-top: var(--spacing-xl);">
        <h3 style="color: var(--color-text-primary); font-size: 1.4rem; font-weight: bold; margin-bottom: var(--spacing-lg);">Trending Products</h3>
        <div class="trending-products-scroll">
            <div 
              v-for="p in relatedProducts" 
              :key="p.id" 
              class="product-card"
              style="cursor: pointer;"
              @click="reloadDetail(p.uuid)"
            >
              <div class="product-card__image-container">
                <img 
                  v-protect-image
                  :src="getPrimaryImage(p)" 
                  class="product-card__image" 
                  alt="recommend cover" 
                  loading="lazy"
                />
              </div>
            
            <div class="product-card__details">
              <span class="product-card__title">{{ p.name }}</span>
              <span class="product-card__price">₹{{ p.selling_price }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Mobile Sticky Bottom Purchase Bar -->
      <div class="mobile-sticky-action-bar mobile-only">
        <div style="flex: 1; display: flex; flex-direction: column;">
          <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-primary);">
            {{ selectedVariant ? 'Size: ' + (selectedVariant.size || 'OS') : 'Select Variant' }}
          </span>
          <span style="font-size: 1.05rem; font-weight: bold; color: var(--color-primary);">
            ₹{{ selectedVariant?.selling_price || product.selling_price }}
          </span>
        </div>
        <button 
          class="btn btn--primary" 
          style="padding: 0 1.25rem; font-size: 0.9rem; font-weight: bold; border-radius: 8px; height: 44px; border: none; flex-shrink: 0;"
          :disabled="product.variants && product.variants.filter(v => v.stock_quantity > 0).length === 0"
          @click="handleStickyClick"
        >
          🛒 Add to Cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const emit = defineEmits(['update-cart-count']);

const product = ref(null);
const relatedProducts = ref([]);
const loading = ref(true);
const activeImagePath = ref('');
const selectedVariant = ref(null);
const qty = ref(1);

const selectVariant = (v) => {
  selectedVariant.value = v;
  qty.value = 1;
};

const hasDiscount = computed(() => {
  const sell = selectedVariant.value?.selling_price || product.value?.selling_price;
  const mrp = selectedVariant.value?.mrp || product.value?.mrp;
  return mrp > sell;
});

const discountPct = computed(() => {
  const sell = selectedVariant.value?.selling_price || product.value?.selling_price;
  const mrp = selectedVariant.value?.mrp || product.value?.mrp;
  if (!mrp) return 0;
  return Math.round(((mrp - sell) / mrp) * 100);
});

const fetchDetails = async (id) => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/storefront/products/${id}`);
    if (response.data && response.data.success) {
      product.value = response.data.data;
      relatedProducts.value = response.data.related;
      activeImagePath.value = getPrimaryImage(product.value);
      
      // Auto select first variant if available
      if (product.value.variants && product.value.variants.length) {
        selectedVariant.value = product.value.variants[0];
      }
    }
  } catch (err) {
    console.error('Failed to query product details:', err);
    alert('Product details not available');
    router.push('/shop');
  } finally {
    loading.value = false;
  }
};

const reloadDetail = (uuid) => {
  router.push(`/products/${uuid}`);
  fetchDetails(uuid);
};

const addToCart = () => {
  if (!selectedVariant.value) return;
  
  try {
    const cart = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
    
    // Check if variant already in cart
    const existingIndex = cart.findIndex(item => item.product_variant_id === selectedVariant.value.id);
    if (existingIndex >= 0) {
      cart[existingIndex].quantity += qty.value;
      // Cap at stock quantity
      if (cart[existingIndex].quantity > selectedVariant.value.stock_quantity) {
        cart[existingIndex].quantity = selectedVariant.value.stock_quantity;
      }
    } else {
      cart.push({
        product_id: product.value.id,
        product_uuid: product.value.uuid,
        product_variant_id: selectedVariant.value.id,
        sku: selectedVariant.value.sku,
        name: product.value.name,
        size: selectedVariant.value.size,
        color: selectedVariant.value.color,
        image: activeImagePath.value,
        selling_price: selectedVariant.value.selling_price || product.value.selling_price,
        mrp: selectedVariant.value.mrp || product.value.mrp,
        quantity: qty.value,
        stock_quantity: selectedVariant.value.stock_quantity,
      });
    }

    localStorage.setItem('vibe_cart_items', JSON.stringify(cart));
    emit('update-cart-count');
    alert('✓ Product variant successfully added to shopping cart!');
  } catch (e) {
    console.error('Cart operation failed', e);
  }
};

const handleStickyClick = () => {
  if (!selectedVariant.value) {
    const selector = document.querySelector('.card-header-title');
    if (selector) {
      selector.scrollIntoView({ behavior: 'smooth' });
    }
  } else {
    addToCart();
  }
};

const prevImage = () => {
  if (!product.value || !product.value.images || product.value.images.length <= 1) return;
  const images = product.value.images;
  const currentIndex = images.findIndex(img => img.image_path === activeImagePath.value);
  const nextIndex = (currentIndex - 1 + images.length) % images.length;
  activeImagePath.value = images[nextIndex].image_path;
};

const nextImage = () => {
  if (!product.value || !product.value.images || product.value.images.length <= 1) return;
  const images = product.value.images;
  const currentIndex = images.findIndex(img => img.image_path === activeImagePath.value);
  const nextIndex = (currentIndex + 1) % images.length;
  activeImagePath.value = images[nextIndex].image_path;
};

const getPrimaryImage = (p) => {
  if (!p || !p.images || p.images.length === 0) {
    return 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=300&auto=format&fit=crop';
  }
  const primary = p.images.find(img => img.is_primary);
  return primary ? (primary.image_path || primary.url) : (p.images[0].image_path || p.images[0].url);
};

onMounted(() => {
  fetchDetails(route.params.uuid);
});
</script>

<style scoped>
.mobile-only {
  display: none !important;
}

.product-detail-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-xl);
  margin-bottom: var(--spacing-xl);
}

.product-detail-gallery {
  display: flex;
  gap: var(--spacing-md);
  align-items: flex-start;
}

.product-detail-thumbnails {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
  max-height: 480px;
  overflow-y: auto;
  width: 80px;
  flex-shrink: 0;
  padding-right: 0.25rem;
}

/* Custom scrollbar styling for thumbnails list */
.product-detail-thumbnails::-webkit-scrollbar {
  width: 4px;
  height: 4px;
}
.product-detail-thumbnails::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: 4px;
}

.product-detail-main-img {
  position: relative;
  overflow: hidden;
  flex-grow: 1;
  aspect-ratio: 1 / 1;
  border-radius: 12px;
  border: 1px solid var(--color-border);
  width: 100%;
}

@media (max-width: 768px) {
  .product-detail-layout {
    grid-template-columns: 1fr;
    gap: var(--spacing-lg);
  }

  .product-detail-gallery {
    flex-direction: column-reverse;
    gap: var(--spacing-md);
    width: 100%;
  }

  .product-detail-thumbnails {
    flex-direction: row;
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    max-height: none;
    padding-bottom: 0.5rem;
    padding-right: 0;
  }

  .mobile-only {
    display: flex !important;
  }

  .mobile-sticky-action-bar {
    position: fixed;
    bottom: 64px;
    left: 0;
    right: 0;
    background: rgba(255, 252, 247, 0.96);
    backdrop-filter: blur(10px);
    border-top: 1px solid var(--color-border);
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    z-index: 998;
    box-shadow: 0 -4px 15px rgba(74, 14, 46, 0.08);
  }
}

.trending-products-scroll {
  display: flex;
  gap: var(--spacing-md);
  overflow-x: auto;
  padding-bottom: var(--spacing-md);
  width: 100%;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.trending-products-scroll::-webkit-scrollbar {
  height: 6px;
}

.trending-products-scroll::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: 4px;
}

.trending-products-scroll .product-card {
  width: 200px;
  flex-shrink: 0;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
}

.product-card__image-container {
  width: 100%;
  aspect-ratio: 3 / 4;
  background: var(--blush-bg);
  overflow: hidden;
  position: relative;
}

.product-card__image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.trending-products-scroll .product-card:hover .product-card__image {
  transform: scale(1.05);
}

.product-card__details {
  padding: var(--spacing-sm);
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.product-card__title {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-primary);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 2.6em;
  line-height: 1.3;
}

.product-card__price {
  font-weight: 700;
  color: var(--color-primary);
  font-size: 0.95rem;
}

/* Gallery Navigation Arrows */
.gallery-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(4px);
  border: 1px solid var(--color-border);
  color: var(--color-primary);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  box-shadow: var(--shadow-sm);
  transition: background-color 0.2s, transform 0.2s, color 0.2s;
}

.gallery-nav-btn:hover {
  background: var(--color-primary);
  color: #ffffff;
  transform: translateY(-50%) scale(1.05);
}

.prev-btn {
  left: 12px;
}

.next-btn {
  right: 12px;
}
</style>
