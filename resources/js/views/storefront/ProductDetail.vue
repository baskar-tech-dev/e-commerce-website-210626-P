<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" style="padding: 2rem 0;">
      <SkeletonLoader type="pdp" />
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
          <div 
            class="glass-panel product-detail-main-img" 
            style="cursor: zoom-in;"
            @click="openLightbox"
          >
            <img 
              v-protect-image
              :src="activeImagePath || '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp'" 
              style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; background: #ffffff;" 
              alt="large preview" 
              loading="lazy"
            />
            <!-- Watermark Overlay -->
            <div class="product-image-watermark">
              <img :src="'/asset/profile/logo.png'" alt="Maya Sree Watermark" />
            </div>

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

          <!-- Subtitle -->
          <p v-if="product.subtitle" style="color: var(--color-primary, #4a0e2e); font-weight: 600; font-size: 0.95rem; margin: 0;">
            {{ product.subtitle }}
          </p>

          <!-- Description -->
          <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin: 0;">
            {{ product.description || 'No product description available.' }}
          </p>

          <!-- Available Offers & Coupons Card -->
          <div style="background: #fffdf5; border: 1px dashed #d4af37; border-radius: 12px; padding: 14px; margin: 4px 0;">
            <div style="font-size: 0.85rem; font-weight: 700; color: #4a0e2e; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
              <span>🏷️</span> EXCLUSIVE OFFERS AVAILABLE
            </div>
            <div style="font-size: 0.8rem; color: #334155; display: flex; align-items: center; justify-content: space-between;">
              <span>Use Code <strong>MAYASREE25</strong> for 25% Off on orders above ₹1,999</span>
              <button @click="copyCoupon('MAYASREE25')" style="background: #4a0e2e; color: #fff; border: none; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; cursor: pointer;">
                COPY
              </button>
            </div>
          </div>

          <!-- Delivery Pincode Checker -->
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
            <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
              🚚 Delivery & Availability Check
            </div>
            <div style="display: flex; gap: 8px;">
              <input 
                type="text" 
                v-model="pincodeInput" 
                placeholder="Enter 6-digit Pincode (e.g. 600001)" 
                maxlength="6"
                style="flex: 1; padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 0.85rem; outline: none;" 
              />
              <button 
                @click="checkPincode" 
                style="background: #4a0e2e; color: #ffffff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer;"
              >
                CHECK
              </button>
            </div>
            <div v-if="pincodeResult" style="margin-top: 8px; font-size: 0.8rem; color: #16a34a; font-weight: 600;">
              ✓ {{ pincodeResult }}
            </div>
          </div>

          <!-- Product CMS Specifications Table/Grid -->
          <div style="background: #fffcf7; border: 1px solid #f1e6df; border-radius: 12px; padding: 16px; margin: 8px 0;">
            <h4 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--color-primary, #4a0e2e); margin-bottom: 12px;">PRODUCT SPECIFICATIONS</h4>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 0.85rem;">
              <div v-if="product.collection_name"><strong>Collection:</strong> {{ product.collection_name }}</div>
              <div v-if="product.occasion"><strong>Occasion:</strong> {{ product.occasion }}</div>
              <div v-if="product.fabric || product.material"><strong>Fabric/Material:</strong> {{ product.fabric || product.material }}</div>
              <div v-if="product.pattern"><strong>Pattern:</strong> {{ product.pattern }}</div>
              <div v-if="product.style"><strong>Style:</strong> {{ product.style }}</div>
              <div v-if="product.fit"><strong>Fit:</strong> {{ product.fit }}</div>
              <div v-if="product.sleeve"><strong>Sleeve:</strong> {{ product.sleeve }}</div>
              <div v-if="product.neck"><strong>Neckline:</strong> {{ product.neck }}</div>
              <div v-if="product.season"><strong>Season:</strong> {{ product.season }}</div>
              <div v-if="product.care_instructions"><strong>Care:</strong> {{ product.care_instructions }}</div>
            </div>
          </div>

          <!-- Reusable Product Variant Selection System (Color, Size, Summary, Quantity, Add to Cart) -->
          <ProductVariantSelector 
            v-if="product"
            :product="product"
            v-model:selected-color="selectedColor"
            v-model:selected-size="selectedSize"
            v-model:qty="qty"
            :selected-variant="selectedVariant"
            :available-colors="availableColors"
            :available-sizes="availableSizes"
            :is-size-disabled="isSizeDisabled"
            :add-to-cart-error="addToCartError"
            @open-size-guide="triggerSizeGuide"
            @add-to-cart="addToCart"
          />
        </div>
      </div>

      <!-- 1. Trending Products Section -->
      <section v-if="trendingProducts && trendingProducts.length" style="margin-top: 48px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 18px;">
          <div>
            <h3 style="font-family: 'Playfair Display', serif; color: var(--color-primary, #4a0e2e); font-size: 1.6rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
              <span>🔥</span> Trending Products
            </h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 4px 0 0 0;">Handpicked customer favorites trending this season.</p>
          </div>
          <router-link to="/shop" style="color: var(--color-primary, #4a0e2e); font-size: 0.85rem; font-weight: 700; text-decoration: none;">View All ➔</router-link>
        </div>
        
        <div class="product-cards-scroll-track">
          <div 
            v-for="p in trendingProducts" 
            :key="p.id" 
            class="product-card-scroll-item"
          >
            <ProductCard :product="p" @quick-add="handleQuickAdd" />
          </div>
        </div>
      </section>

      <!-- 2. Similar Products Section -->
      <section v-if="relatedProducts && relatedProducts.length" style="margin-top: 48px;">
        <div style="margin-bottom: 18px;">
          <h3 style="font-family: 'Playfair Display', serif; color: var(--color-primary, #4a0e2e); font-size: 1.6rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
            <span>✨</span> Similar Products
          </h3>
          <p style="font-size: 0.85rem; color: #64748b; margin: 4px 0 0 0;">More styles from {{ product.category?.name || 'this collection' }}.</p>
        </div>

        <div class="similar-products-grid">
          <ProductCard 
            v-for="p in relatedProducts" 
            :key="p.id" 
            :product="p"
            @quick-add="handleQuickAdd"
          />
        </div>
      </section>

      <!-- 3. Shop by Category (beautiful image cards) -->
      <section style="margin-top: 48px; background: #fffdf9; border: 1px solid #f1e6df; border-radius: 18px; padding: 24px 16px;">
        <CategoryGrid />
      </section>

      <!-- 4. Recently Viewed Section -->
      <section v-if="recentlyViewedProducts && recentlyViewedProducts.length" style="margin-top: 48px; margin-bottom: 48px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 18px;">
          <div>
            <h3 style="font-family: 'Playfair Display', serif; color: var(--color-primary, #4a0e2e); font-size: 1.6rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
              <span>🕒</span> Recently Viewed
            </h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 4px 0 0 0;">Products you recently explored.</p>
          </div>
          <button @click="clearRecentlyViewed" style="background: none; border: none; color: #94a3b8; font-size: 0.8rem; cursor: pointer; text-decoration: underline;">Clear History</button>
        </div>

        <div class="product-cards-scroll-track">
          <div 
            v-for="p in recentlyViewedProducts" 
            :key="p.id" 
            class="product-card-scroll-item"
          >
            <ProductCard :product="p" @quick-add="handleQuickAdd" />
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
      <!-- Fullscreen Lightbox Overlay -->
      <div v-if="isLightboxOpen" class="lightbox-overlay" @click="closeLightbox">
        <!-- Close Button -->
        <button class="lightbox-close-btn" @click.stop="closeLightbox" aria-label="Close fullscreen view">
          <span style="font-size: 2rem; line-height: 1;">&times;</span>
        </button>

        <!-- Left arrow for Desktop -->
        <button 
          v-if="product.images && product.images.length > 1"
          class="lightbox-nav-btn lightbox-prev-btn" 
          @click.stop="navigateLightbox('prev')" 
          aria-label="Previous Image"
        >
          <ChevronLeft :size="32" />
        </button>

        <!-- Scrollable Slides Container -->
        <div 
          class="lightbox-scroll-container" 
          ref="lightboxContainer" 
          @scroll="handleLightboxScroll"
          @click.stop
        >
          <div 
            v-for="img in product.images" 
            :key="img.id" 
            class="lightbox-slide"
          >
            <div class="lightbox-image-wrapper">
              <img 
                v-protect-image
                :src="img.image_path" 
                alt="fullscreen product preview" 
                class="lightbox-img" 
              />
              <!-- Watermark on lightbox image -->
              <div class="lightbox-watermark">
                <img :src="'/asset/profile/logo.png'" alt="Maya Sree Watermark" />
              </div>
            </div>
          </div>
        </div>

        <!-- Right arrow for Desktop -->
        <button 
          v-if="product.images && product.images.length > 1"
          class="lightbox-nav-btn lightbox-next-btn" 
          @click.stop="navigateLightbox('next')" 
          aria-label="Next Image"
        >
          <ChevronRight :size="32" />
        </button>

        <!-- Bottom Thumbnails / Indicators -->
        <div v-if="product.images && product.images.length > 1" class="lightbox-indicators" @click.stop>
          <div 
            v-for="img in product.images" 
            :key="'dot-' + img.id" 
            class="lightbox-dot"
            :class="{ 'active': activeImagePath === img.image_path }"
            @click="selectLightboxImage(img.image_path)"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import ProductVariantSelector from '../../components/ProductVariantSelector.vue';
import SkeletonLoader from '../../components/common/SkeletonLoader.vue';
import CategoryGrid from '../../components/homepage/CategoryGrid.vue';
import ProductCard from '../../components/ProductCard.vue';

const route = useRoute();
const router = useRouter();
const emit = defineEmits(['update-cart-count']);

const triggerSizeGuide = () => {
  alert("Our design team is preparing the size guide table. Please contact us on WhatsApp support for direct fit advice!");
};

const product = ref(null);
const relatedProducts = ref([]);
const trendingProducts = ref([]);
const recentlyViewedProducts = ref([]);
const loading = ref(true);
const activeImagePath = ref('');
const selectedColor = ref('');
const selectedSize = ref('');
const qty = ref(1);
const pincodeInput = ref('');
const pincodeResult = ref('');

// Lightbox state and navigation
const isLightboxOpen = ref(false);
const lightboxContainer = ref(null);

const openLightbox = () => {
  if (!product.value || !product.value.images || product.value.images.length === 0) return;
  isLightboxOpen.value = true;
  document.body.style.overflow = 'hidden';
  
  // Wait for the container to render, then scroll to active image
  setTimeout(() => {
    if (lightboxContainer.value) {
      const idx = product.value.images.findIndex(img => img.image_path === activeImagePath.value);
      if (idx !== -1) {
        lightboxContainer.value.scrollTo({
          left: lightboxContainer.value.clientWidth * idx,
          behavior: 'auto'
        });
      }
    }
  }, 50);
};

const closeLightbox = () => {
  isLightboxOpen.value = false;
  document.body.style.overflow = '';
};

const handleLightboxScroll = (event) => {
  if (!product.value || !product.value.images) return;
  const container = event.target;
  const scrollLeft = container.scrollLeft;
  const width = container.clientWidth;
  if (width === 0) return;
  
  const idx = Math.round(scrollLeft / width);
  if (idx >= 0 && idx < product.value.images.length) {
    const targetImage = product.value.images[idx];
    if (activeImagePath.value !== targetImage.image_path) {
      activeImagePath.value = targetImage.image_path;
    }
  }
};

const navigateLightbox = (direction) => {
  if (!product.value || !product.value.images || !lightboxContainer.value) return;
  const images = product.value.images;
  const currentIndex = images.findIndex(img => img.image_path === activeImagePath.value);
  let targetIndex = currentIndex;
  
  if (direction === 'next') {
    targetIndex = (currentIndex + 1) % images.length;
  } else if (direction === 'prev') {
    targetIndex = (currentIndex - 1 + images.length) % images.length;
  }
  
  const container = lightboxContainer.value;
  container.scrollTo({
    left: container.clientWidth * targetIndex,
    behavior: 'smooth'
  });
  
  activeImagePath.value = images[targetIndex].image_path;
};

const selectLightboxImage = (imagePath) => {
  if (!product.value || !product.value.images || !lightboxContainer.value) return;
  activeImagePath.value = imagePath;
  const idx = product.value.images.findIndex(img => img.image_path === imagePath);
  if (idx !== -1) {
    lightboxContainer.value.scrollTo({
      left: lightboxContainer.value.clientWidth * idx,
      behavior: 'smooth'
    });
  }
};

const checkPincode = () => {
  if (!pincodeInput.value || pincodeInput.value.length < 6) {
    pincodeResult.value = 'Please enter a valid 6-digit Indian pincode';
    return;
  }
  const pin = pincodeInput.value.trim();
  if (pin.startsWith('600') || pin.startsWith('603') || pin.startsWith('641') || pin.startsWith('560') || pin.startsWith('500')) {
    pincodeResult.value = `Fast 2-Day Delivery available to ${pin} (COD Available)`;
  } else {
    pincodeResult.value = `Standard 3-5 Days Delivery available to ${pin} (Prepaid & COD Available)`;
  }
};

const copyCoupon = (code) => {
  navigator.clipboard.writeText(code);
  alert(`✓ Coupon code '${code}' copied to clipboard! Apply it at checkout.`);
};

const isSizeDisabledForColor = (size, color) => {
  if (!product.value?.variants) return true;
  const v = product.value.variants.find(v => 
    v.color?.toLowerCase() === color.toLowerCase() && 
    (v.size || 'OS') === size
  );
  return !v || v.stock_quantity <= 0;
};

const isSizeDisabled = (size) => {
  if (!product.value?.variants) return true;
  if (availableColors.value.length === 0) {
    const v = product.value.variants.find(v => (v.size || 'OS') === size);
    return !v || v.stock_quantity <= 0;
  }
  if (selectedColor.value) {
    return isSizeDisabledForColor(size, selectedColor.value);
  }
  return !product.value.variants.some(v => (v.size || 'OS') === size && v.stock_quantity > 0);
};

const availableColors = computed(() => {
  if (!product.value?.variants) return [];
  const colors = [];
  const seen = new Set();
  product.value.variants.forEach(v => {
    if (v.color) {
      const normalized = v.color.trim();
      if (!seen.has(normalized.toLowerCase())) {
        seen.add(normalized.toLowerCase());
        colors.push(normalized);
      }
    }
  });
  return colors;
});

const availableSizes = computed(() => {
  if (!product.value?.variants) return [];
  const sizes = [];
  const seen = new Set();
  product.value.variants.forEach(v => {
    const sizeName = v.size || 'OS';
    if (!seen.has(sizeName)) {
      seen.add(sizeName);
      sizes.push(sizeName);
    }
  });
  return sizes;
});

const selectedVariant = computed(() => {
  if (!product.value?.variants) return null;
  if (availableColors.value.length === 0) {
    return product.value.variants.find(v => (v.size || 'OS') === selectedSize.value) || null;
  }
  return product.value.variants.find(v => 
    v.color?.toLowerCase() === selectedColor.value?.toLowerCase() && 
    (v.size || 'OS') === selectedSize.value
  ) || null;
});

const addToCartError = computed(() => {
  if (availableColors.value.length > 0 && !selectedColor.value) {
    return "Please select a color";
  }
  if (availableSizes.value.length > 0 && !selectedSize.value) {
    return "Please select a size";
  }
  if (!selectedVariant.value || selectedVariant.value.stock_quantity <= 0) {
    return "Out of Stock";
  }
  return "";
});

const updateActiveImage = () => {
  if (!product.value?.images) return;
  
  // 1. Try to find image matching the selected variant
  if (selectedVariant.value) {
    const matchImg = product.value.images.find(img => 
      img.variant_id && img.variant_id === selectedVariant.value.id
    );
    if (matchImg) {
      activeImagePath.value = matchImg.image_path;
      return;
    }
  }

  // 2. Try to find image matching the selected color_group
  if (selectedColor.value) {
    const matchImg = product.value.images.find(img => 
      img.color_group?.toLowerCase() === selectedColor.value.toLowerCase()
    );
    if (matchImg) {
      activeImagePath.value = matchImg.image_path;
      return;
    }
  }
};

watch(selectedColor, (newColor) => {
  if (newColor) {
    // Auto-select first available size for this color if current size is invalid/disabled for the color
    if (selectedSize.value && isSizeDisabledForColor(selectedSize.value, newColor)) {
      const firstAvailableSize = availableSizes.value.find(size => !isSizeDisabledForColor(size, newColor));
      if (firstAvailableSize) {
        selectedSize.value = firstAvailableSize;
      } else {
        selectedSize.value = '';
      }
    }
    qty.value = 1;
    updateActiveImage();
  }
});

watch(selectedSize, () => {
  qty.value = 1;
  updateActiveImage();
});

const getColorHex = (colorName) => {
  if (!colorName) return '#ccc';
  const name = colorName.trim().toLowerCase();
  const map = {
    'mustard yellow': '#e1ad01',
    'mustard': '#e1ad01',
    'deep maroon': '#5B163A',
    'maroon': '#800000',
    'zari gold': '#d4af37',
    'gold': '#d4af37',
    'warm white': '#fffcf7',
    'cream': '#f8f5f1',
    'dark charcoal': '#2d2d2d',
    'charcoal': '#36454F',
    'black': '#000000',
    'white': '#ffffff',
    'red': '#b91c1c',
    'blue': '#1d4ed8',
    'navy': '#1e3a8a',
    'green': '#15803d',
    'olive': '#556b2f',
    'pink': '#db2777',
    'yellow': '#facc15',
    'orange': '#f97316',
    'purple': '#7e22ce',
    'grey': '#6b7280',
    'gray': '#6b7280',
    'beige': '#f5f5dc',
    'mustard gold': '#e1ad01',
    'plum': '#4d002b',
    'wine': '#722f37',
  };
  return map[name] || name;
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

const fetchTrendingProducts = async () => {
  try {
    const res = await axios.get('/api/storefront/products?featured=1');
    if (res.data && res.data.data) {
      trendingProducts.value = res.data.data.slice(0, 8);
    }
  } catch (e) {
    console.error('Failed to fetch trending products:', e);
  }
};

const saveToRecentlyViewed = (prod) => {
  if (!prod || !prod.id) return;
  try {
    const list = JSON.parse(localStorage.getItem('maya_recently_viewed') || '[]');
    const filtered = list.filter(item => item.id !== prod.id && item.uuid !== prod.uuid);
    filtered.unshift({
      id: prod.id,
      uuid: prod.uuid,
      name: prod.name,
      selling_price: prod.selling_price || prod.price,
      mrp: prod.mrp,
      images: prod.images || [],
      primary_image: getPrimaryImage(prod),
      category: prod.category
    });
    localStorage.setItem('maya_recently_viewed', JSON.stringify(filtered.slice(0, 10)));
    loadRecentlyViewed();
  } catch (e) {
    console.error('Failed to save to recently viewed:', e);
  }
};

const loadRecentlyViewed = () => {
  try {
    const list = JSON.parse(localStorage.getItem('maya_recently_viewed') || '[]');
    if (product.value) {
      recentlyViewedProducts.value = list.filter(item => item.id !== product.value.id && item.uuid !== product.value.uuid);
    } else {
      recentlyViewedProducts.value = list;
    }
  } catch (e) {
    recentlyViewedProducts.value = [];
  }
};

const clearRecentlyViewed = () => {
  localStorage.removeItem('maya_recently_viewed');
  recentlyViewedProducts.value = [];
};

const handleQuickAdd = (prod, size) => {
  try {
    const cart = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
    cart.push({
      product_id: prod.id,
      product_uuid: prod.uuid,
      name: prod.name,
      size: size || 'Free Size',
      image: prod.primary_image || getPrimaryImage(prod),
      selling_price: prod.selling_price || prod.price,
      mrp: prod.mrp,
      quantity: 1,
    });
    localStorage.setItem('vibe_cart_items', JSON.stringify(cart));
    emit('update-cart-count');
    alert(`✓ Added ${prod.name} (${size || 'Free Size'}) to cart!`);
  } catch (e) {
    console.error('Quick add failed:', e);
  }
};

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
        const firstVariant = product.value.variants[0];
        selectedColor.value = firstVariant.color || '';
        selectedSize.value = firstVariant.size || 'OS';
      }

      // Save product to recently viewed list
      saveToRecentlyViewed(product.value);
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
    const prodId = p && p.id ? ((p.id - 1) % 23) + 1 : 1;
    return `/storage/products/${prodId}/webp/${prodId}.webp`;
  }
  const primary = p.images.find(img => img.is_primary);
  return primary ? (primary.image_path || primary.url) : (p.images[0].image_path || p.images[0].url);
};

onMounted(() => {
  fetchDetails(route.params.uuid);
  fetchTrendingProducts();
  loadRecentlyViewed();
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
/* Product Variant Redesign Styles */
.variant-selection-container {
  display: flex;
  flex-direction: column;
  gap: 24px; /* Generous 24px spacing between sections */
  background-color: #FFFDF9; /* Cream background */
  border: 1px solid #E6DED5; /* Border */
  border-radius: 16px;
  padding: 24px;
  font-family: 'Poppins', sans-serif;
}

.boutique-add-to-cart-btn {
  flex: 1;
  height: 56px; /* 56px height */
  border-radius: 14px; /* 14px radius */
  background-color: #5B163A; /* Maroon */
  color: #ffffff;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-size: 1.05rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(91, 22, 58, 0.12);
  transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
}

.boutique-add-to-cart-btn:hover:not(:disabled) {
  background-color: #4a0e2e;
  transform: translateY(-2px); /* Slight lift */
  box-shadow: 0 8px 20px rgba(91, 22, 58, 0.2); /* Soft shadow */
}

.boutique-add-to-cart-btn:active:not(:disabled) {
  transform: translateY(0) scale(0.98);
}

.boutique-add-to-cart-btn:disabled {
  background-color: #e2e8f0;
  color: #94a3b8;
  cursor: not-allowed;
  box-shadow: none;
}

/* Image Watermark Style */
.product-image-watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 45%;
  opacity: 0.15;
  pointer-events: none;
  user-select: none;
  z-index: 2;
  display: flex;
  justify-content: center;
  align-items: center;
}

.product-image-watermark img {
  width: 100%;
  height: auto;
  max-height: 100%;
  object-fit: contain;
  filter: grayscale(1) contrast(1.2);
}

/* Lightbox Overlay Styles */
.lightbox-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.25s ease-out;
}

/* Scroll Container */
.lightbox-scroll-container {
  display: flex;
  width: 100%;
  height: 80vh;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE 10+ */
  -webkit-overflow-scrolling: touch;
}

.lightbox-scroll-container::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Opera */
}

/* Lightbox Slides */
.lightbox-slide {
  width: 100vw;
  height: 100%;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  scroll-snap-align: center;
  padding: var(--spacing-md);
}

/* Image Wrapper */
.lightbox-image-wrapper {
  position: relative;
  max-width: 90%;
  max-height: 90%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  border-radius: 12px;
  background-color: #ffffff;
  overflow: hidden;
}

.lightbox-img {
  max-width: 100%;
  max-height: 80vh;
  object-fit: contain;
  border-radius: 12px;
  user-select: none;
  -webkit-user-drag: none;
}

/* Lightbox Watermark */
.lightbox-watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 35%;
  opacity: 0.12;
  pointer-events: none;
  user-select: none;
  z-index: 3;
}

.lightbox-watermark img {
  width: 100%;
  height: auto;
  object-fit: contain;
  filter: grayscale(1) contrast(1.2);
}

/* Lightbox Close Button */
.lightbox-close-btn {
  position: absolute;
  top: 24px;
  right: 24px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 100001;
  transition: all 0.2s ease;
}

.lightbox-close-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.05);
}

/* Lightbox Navigation Buttons */
.lightbox-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.25);
  color: #ffffff;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 100000;
  transition: all 0.2s ease;
}

.lightbox-nav-btn:hover {
  background: var(--color-secondary, #d4af37);
  color: var(--color-primary, #4a0e2e);
  border-color: var(--color-secondary, #d4af37);
  transform: translateY(-50%) scale(1.1);
}

.lightbox-prev-btn {
  left: 32px;
}

.lightbox-next-btn {
  right: 32px;
}

/* Lightbox Indicators / Dots */
.lightbox-indicators {
  position: absolute;
  bottom: 24px;
  display: flex;
  gap: 12px;
  z-index: 100000;
  background: rgba(15, 23, 42, 0.6);
  padding: 8px 16px;
  border-radius: 20px;
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.lightbox-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  transition: all 0.25s ease;
}

.lightbox-dot.active {
  background: var(--color-secondary, #d4af37);
  transform: scale(1.2);
}

/* Fade In Animation */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Responsive Hide Arrows on Mobile for full swipe layout */
@media (max-width: 768px) {
  .lightbox-nav-btn {
    display: none; /* On mobile, pure swipe/scroll gestures are preferred */
  }
  .lightbox-close-btn {
    top: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
  }
  .lightbox-indicators {
    bottom: 16px;
  }
  .lightbox-image-wrapper {
    max-width: 95%;
  }
}

/* Product Cards Scroll Track & Similar Products Grid */
.product-cards-scroll-track {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  padding-bottom: 12px;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
}

.product-cards-scroll-track::-webkit-scrollbar {
  height: 6px;
}

.product-cards-scroll-track::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 4px;
}

.product-card-scroll-item {
  width: 240px;
  flex-shrink: 0;
}

.similar-products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
}

@media (max-width: 1024px) {
  .similar-products-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .similar-products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  .product-card-scroll-item {
    width: 175px;
  }
}
</style>
