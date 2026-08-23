<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="premium-loading-state">
      <div class="loading-emblem">
        <svg viewBox="0 0 60 60" class="loading-lotus" xmlns="http://www.w3.org/2000/svg">
          <circle cx="30" cy="30" r="26" fill="none" stroke="#B68D40" stroke-width="1.5" stroke-dasharray="6 4" />
          <circle cx="30" cy="30" r="6" fill="#6E1F3A" />
        </svg>
      </div>
      <p class="loading-brand-text">MAYA SREE</p>
      <p class="loading-sub-text">Curating your collection…</p>
    </div>

    <!-- Product Layout -->
    <div v-else-if="product">
      <!-- Breadcrumbs -->
      <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: var(--spacing-lg);">
        <router-link to="/" style="color: var(--color-text-muted); text-decoration: none;">Home</router-link>
        <span style="margin: 0 0.5rem;">/</span>
        <router-link to="/shop" style="color: var(--color-text-muted); text-decoration: none;">Shop</router-link>
        <template v-if="product.category">
          <span style="margin: 0 0.5rem;">/</span>
          <router-link :to="'/shop?category_id=' + product.category_id" style="color: var(--color-text-muted); text-decoration: none;">{{ product.category.name }}</router-link>
        </template>
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
            class="glass-panel product-detail-main-img zoomable-main-img" 
            @click="openLightbox"
            title="Click to view in Ultra HD Fullscreen Zoom"
          >
            <img 
              v-protect-image
              :src="activeImagePath || 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=600&auto=format&fit=crop'" 
              style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; background: #ffffff;" 
              alt="large preview" 
              loading="lazy"
            />

            <!-- Luxury Zoom Badge Indicator -->
            <div class="luxury-zoom-indicator">
              <ZoomIn :size="15" />
              <span>Tap to Zoom & Expand</span>
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
            <div style="display: flex; gap: var(--spacing-sm); align-items: center; flex-wrap: wrap; margin-bottom: var(--spacing-xs);">
              <router-link 
                v-if="product.category" 
                :to="'/shop?category_id=' + product.category_id" 
                class="badge badge--secondary" 
                style="font-size: 0.75rem; text-decoration: none;"
              >
                {{ product.category.name }}
              </router-link>
              <router-link 
                v-if="product.occasion" 
                :to="'/shop?occasion=' + encodeURIComponent(product.occasion)" 
                class="badge" 
                style="font-size: 0.75rem; text-decoration: none; background: rgba(182, 141, 64, 0.15); color: #8A6418; border: 1px solid rgba(182, 141, 64, 0.3); font-weight: 600;"
              >
                ✨ {{ product.occasion }}
              </router-link>
              <span 
                v-if="product.badge" 
                class="badge" 
                style="font-size: 0.75rem; background: #5B163A; color: #ffffff; font-weight: 600;"
              >
                👑 {{ product.badge }}
              </span>
            </div>
            <h1 style="color: var(--color-text-primary); font-size: 2.2rem; font-weight: 800; margin: 0; line-height: 1.1;">{{ product.name }}</h1>
          </div>

          <!-- Price split -->
          <div style="display: flex; align-items: baseline; gap: var(--spacing-md); margin-top: var(--spacing-xs);">
            <span style="font-size: 2rem; font-weight: bold; color: var(--color-text-primary);" :class="{ 'price-muted': isProductSoldOut }">
              {{ selectedVariant ? `₹${Number(selectedVariant.selling_price).toFixed(2)}` : formatProductPrice(product) }}
            </span>
            <span v-if="hasDiscount" style="font-size: 1.25rem; text-decoration: line-through; color: var(--color-text-muted);">
              MRP {{ selectedVariant ? `₹${Number(selectedVariant.mrp).toFixed(2)}` : formatProductMrp(product) }}
            </span>
            <span v-if="hasDiscount && !isProductSoldOut" class="badge badge--success" style="font-size: 0.8rem; font-weight: bold; margin-left: 0.25rem;">
              SAVE {{ discountPct }}%
            </span>
          </div>

          <!-- Stock status alert boxes -->
          <div v-if="isProductSoldOut" class="luxury-sold-out-box" role="status">
            <div class="sold-out-box-header">
              <span class="sold-out-pill-tag">SOLD OUT</span>
              <span class="sold-out-box-title">Temporarily Out of Stock</span>
            </div>
            <p class="sold-out-box-desc">
              All sizes for this exclusive piece are currently reserved. Connect with our Maya Sree stylist on WhatsApp to request priority restock notice!
            </p>
            <a 
              :href="`https://wa.me/919488344773?text=Hi%20Maya%20Sree,%20I%20would%20like%20to%20enquire%20about%20restock%20for%20${encodeURIComponent(product.name)}`" 
              target="_blank" 
              class="btn-sold-out-whatsapp"
            >
              💬 Enquire on WhatsApp
            </a>
          </div>

          <div v-else-if="isProductLowStock" class="luxury-low-stock-box">
            <span class="low-stock-dot"></span>
            <span>⚡ <strong>Hurry, Only Few Left in Stock!</strong> High demand style — order soon to secure your piece.</span>
          </div>

          <!-- Description -->
          <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.6; margin: 0;">
            {{ product.description || 'No product description available.' }}
          </p>

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

          <!-- Shipping & Returns Policy Accordion Section -->
          <div class="product-info-accordion" style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <details class="glass-panel" style="padding: 1rem; border-radius: 12px; border: 1px solid var(--color-border);" open>
              <summary style="font-weight: 700; color: #6E1F3A; font-family: 'Playfair Display', serif; font-size: 1.05rem; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                <span>🚚 Shipping & Returns Policy</span>
              </summary>
              <div style="margin-top: 0.75rem;">
                <ReturnPolicyNotice />
              </div>
            </details>
          </div>
        </div>
      </div>

      <!-- Customer Reviews Section -->
      <ReviewSection v-if="product" :product="product" />

      <!-- Similar Products Section -->
      <section v-if="relatedProducts && relatedProducts.length" class="detail-recommend-section">
        <h3 class="detail-section-title">Similar Products</h3>
        <div class="detail-products-carousel">
          <div 
            v-for="p in relatedProducts" 
            :key="p.id" 
            class="detail-luxury-card"
            @click="reloadDetail(p.uuid)"
          >
            <div class="card-img-box">
              <img 
                v-protect-image
                :src="getPrimaryImage(p)" 
                class="card-img" 
                :alt="p.name"
                loading="lazy"
              />
            </div>
            <div class="card-info-box">
              <h4 class="card-title">{{ p.name }}</h4>
              <div class="card-price-row">
                <span class="price-current">{{ formatProductPrice(p) }}</span>
                <span v-if="formatProductMrp(p)" class="price-old">{{ formatProductMrp(p) }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Recently Viewed Section -->
      <section v-if="recentlyViewed && recentlyViewed.length" class="detail-recommend-section recently-viewed-margin">
        <h3 class="detail-section-title">Recently Viewed</h3>
        <div class="detail-products-carousel">
          <div 
            v-for="p in recentlyViewed" 
            :key="p.id" 
            class="detail-luxury-card"
            @click="reloadDetail(p.uuid)"
          >
            <div class="card-img-box">
              <img 
                v-protect-image
                :src="p.image" 
                class="card-img" 
                :alt="p.name"
                loading="lazy"
              />
            </div>
            <div class="card-info-box">
              <h4 class="card-title">{{ p.name }}</h4>
              <div class="card-price-row">
                <span class="price-current">{{ formatProductPrice(p) }}</span>
                <span v-if="formatProductMrp(p)" class="price-old">{{ formatProductMrp(p) }}</span>
              </div>
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
          :disabled="isProductSoldOut || (selectedVariant && selectedVariant.stock_quantity <= 0)"
          @click="handleStickyClick"
        >
          {{ isProductSoldOut || (selectedVariant && selectedVariant.stock_quantity <= 0) ? 'Sold Out' : '🛒 Add to Cart' }}
        </button>
      </div>
    </div>

    <!-- Product Not Found / Error State -->
    <div v-else class="glass-panel" style="max-width: 600px; margin: 4rem auto; padding: 3rem 2rem; text-align: center; border-radius: 16px; border: 1px solid rgba(182, 141, 64, 0.25);">
      <div style="font-size: 3rem; margin-bottom: 1rem;">👗</div>
      <h2 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; color: #5B163A; margin-bottom: 0.75rem;">Design Currently Unavailable</h2>
      <p style="color: #7A726A; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
        The requested fashion design could not be found or has been updated. Please explore our full designer catalog.
      </p>
      <router-link to="/shop" class="btn btn--primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 0.75rem 2rem; border-radius: 24px; text-decoration: none;">
        Explore Collections ➔
      </router-link>
    </div>

    <!-- Ultra-HD Fullscreen Luxury Lightbox Studio -->
    <Teleport to="body">
      <Transition name="lightbox-fade">
        <div 
          v-if="isLightboxOpen" 
          class="luxury-lightbox-overlay" 
          @click="closeLightbox"
          tabindex="0"
        >
          <!-- Top Header Bar -->
          <div class="lightbox-top-bar" @click.stop>
            <div class="lightbox-title-wrap">
              <span class="lightbox-brand">Maya Sree Luxury Studio</span>
              <h3 class="lightbox-prod-name">{{ product.name }}</h3>
            </div>

            <div class="lightbox-controls-group">
              <!-- Image Counter Badge -->
              <span class="lightbox-counter-pill" v-if="product?.images?.length">
                {{ activeImageIndex + 1 }} / {{ product.images.length }} • HD View
              </span>

              <!-- Zoom Toggle Button -->
              <button 
                class="lightbox-btn" 
                @click="toggleLightboxZoom" 
                :title="isZoomed ? 'Zoom Out (1x)' : 'Zoom In (2.5x)'"
                :class="{ active: isZoomed }"
              >
                <ZoomOut v-if="isZoomed" :size="18" />
                <ZoomIn v-else :size="18" />
                <span class="btn-text">{{ isZoomed ? '1x Normal' : '2.5x Zoom' }}</span>
              </button>

              <!-- Close Button -->
              <button 
                class="lightbox-btn lightbox-close-btn" 
                @click="closeLightbox" 
                title="Close (Esc)"
              >
                <X :size="22" />
              </button>
            </div>
          </div>

          <!-- Main Viewing Stage -->
          <div 
            class="lightbox-stage" 
            @click.stop 
            @mousemove="handleLightboxMouseMove"
            @touchstart="handleTouchStart"
            @touchend="handleTouchEnd"
          >
            <!-- Navigation Left -->
            <button 
              v-if="product?.images?.length > 1" 
              class="lightbox-nav-arrow arrow-left" 
              @click.stop="prevImage"
              title="Previous (Left Arrow)"
            >
              <ChevronLeft :size="32" />
            </button>

            <!-- Interactive Zoom Container -->
            <div 
              class="lightbox-zoom-container" 
              :class="{ 'is-zoomed': isZoomed }"
              @dblclick="toggleLightboxZoom"
            >
              <img 
                v-protect-image 
                :src="activeImagePath" 
                :alt="product.name" 
                class="lightbox-main-img" 
                :style="isZoomed ? { transformOrigin: `${zoomPos.x}% ${zoomPos.y}%`, transform: 'scale(2.5)' } : {}"
              />
            </div>

            <!-- Navigation Right -->
            <button 
              v-if="product?.images?.length > 1" 
              class="lightbox-nav-arrow arrow-right" 
              @click.stop="nextImage"
              title="Next (Right Arrow)"
            >
              <ChevronRight :size="32" />
            </button>

            <!-- Bottom Floating Guidance Hint -->
            <div class="lightbox-hint-pill" v-if="!isZoomed">
              🔍 Double-click or click Zoom button to inspect intricate fabric & embroidery
            </div>
            <div class="lightbox-hint-pill" v-else>
              ✨ Move cursor to pan across details • Double-click to reset zoom
            </div>
          </div>

          <!-- Bottom Thumbnails Bar -->
          <div 
            class="lightbox-bottom-bar" 
            v-if="product?.images?.length > 1" 
            @click.stop
          >
            <div class="lightbox-thumb-strip">
              <div 
                v-for="(img, idx) in product.images" 
                :key="img.id || idx" 
                class="lightbox-thumb-item" 
                :class="{ active: activeImagePath === img.image_path }"
                @click="activeImagePath = img.image_path"
              >
                <img v-protect-image :src="img.image_path" :alt="`Angle ${idx + 1}`" />
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Category Size Chart Modal -->
    <Teleport to="body">
      <Transition name="lightbox-fade">
        <div 
          v-if="showSizeGuideModal" 
          class="size-guide-overlay" 
          @click.self="showSizeGuideModal = false"
        >
          <div class="size-guide-modal-container">
            <!-- Modal Header -->
            <div class="size-guide-header">
              <div class="size-guide-title-box">
                <span class="size-guide-tag">Official Fit Guide</span>
                <h3 class="size-guide-title">{{ product?.category?.name || 'Garment' }} Size Chart</h3>
              </div>
              <button class="size-guide-close-btn" @click="showSizeGuideModal = false" aria-label="Close size guide">
                <X :size="20" />
              </button>
            </div>

            <!-- Modal Content Area -->
            <div class="size-guide-body">
              <!-- When Category has an uploaded size chart image -->
              <div v-if="product?.category?.size_chart_image" class="category-chart-image-wrap">
                <div class="chart-image-card">
                  <img 
                    v-protect-image 
                    :src="product.category.size_chart_image" 
                    :alt="`${product.category.name} Size Chart`" 
                    class="category-size-chart-img" 
                  />
                </div>
                <div class="chart-hint-row">
                  <span>💡 Tip: Official sizing guide tailored specifically for <strong>{{ product.category.name }}</strong>.</span>
                </div>
              </div>

              <!-- Default interactive South Indian sizing guide table when no custom image is uploaded -->
              <div v-else class="default-sizing-table-wrap">
                <div class="table-responsive-box">
                  <table class="luxury-size-table">
                    <thead>
                      <tr>
                        <th>Size</th>
                        <th>Bust (Inches)</th>
                        <th>Waist (Inches)</th>
                        <th>Length (Inches)</th>
                        <th>Fit Guide</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><strong>XS-S (32-34)</strong></td>
                        <td>32" – 34"</td>
                        <td>26" – 28"</td>
                        <td>14.5"</td>
                        <td>Petite / Slim Fit</td>
                      </tr>
                      <tr>
                        <td><strong>M-L (36-38)</strong></td>
                        <td>36" – 38"</td>
                        <td>30" – 32"</td>
                        <td>15.0"</td>
                        <td>Standard Regular Fit</td>
                      </tr>
                      <tr>
                        <td><strong>XL (40)</strong></td>
                        <td>40"</td>
                        <td>34"</td>
                        <td>15.5"</td>
                        <td>Comfort Fit</td>
                      </tr>
                      <tr>
                        <td><strong>XXL (42)</strong></td>
                        <td>42"</td>
                        <td>36"</td>
                        <td>16.0"</td>
                        <td>Plus Comfort Fit</td>
                      </tr>
                      <tr>
                        <td><strong>3XL (44)</strong></td>
                        <td>44"</td>
                        <td>38"</td>
                        <td>16.5"</td>
                        <td>Extended Plus Fit</td>
                      </tr>
                      <tr>
                        <td><strong>4XL (46)</strong></td>
                        <td>46"</td>
                        <td>40"</td>
                        <td>17.0"</td>
                        <td>Extended Plus Fit</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- How to Measure section -->
                <div class="measurement-tips-box">
                  <h4 class="tips-heading">📏 How to Measure:</h4>
                  <ul class="tips-list">
                    <li><strong>Bust:</strong> Measure around the fullest part of your chest with measuring tape level.</li>
                    <li><strong>Waist:</strong> Measure around your natural waistline, keeping tape comfortably loose.</li>
                    <li><strong>Stretch Margin:</strong> Our 4-way stretchable blouses comfortably accommodate +2 inches.</li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Modal Footer Assistance -->
            <div class="size-guide-footer">
              <div class="footer-help-text">
                Need personalized fit assistance?
              </div>
              <a 
                href="https://wa.me/919488344773?text=Hello%20Maya%20Sree%20Fashion,%20I%20need%20help%20with%20size%20and%20fit%20for%20a%20product" 
                target="_blank" 
                class="btn-whatsapp-fit-advice"
              >
                💬 Chat on WhatsApp
              </a>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { ChevronLeft, ChevronRight, ZoomIn, ZoomOut, X } from 'lucide-vue-next';
import ProductVariantSelector from '../../components/ProductVariantSelector.vue';
import ReturnPolicyNotice from '../../components/ReturnPolicyNotice.vue';
import ReviewSection from '../../components/ReviewSection.vue';

import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const emit = defineEmits(['update-cart-count']);

const showSizeGuideModal = ref(false);

const triggerSizeGuide = () => {
  showSizeGuideModal.value = true;
};

const product = ref(null);
const relatedProducts = ref([]);
const recentlyViewed = ref([]);
const loading = ref(true);

const isProductSoldOut = computed(() => {
  if (!product.value) return false;
  if (product.value.is_sold_out) return true;
  if (!product.value.variants || product.value.variants.length === 0) return true;
  return !product.value.variants.some(v => v.stock_quantity > 0);
});

const isProductLowStock = computed(() => {
  if (!product.value || isProductSoldOut.value) return false;
  if (product.value.is_low_stock) return true;
  const total = product.value.variants?.reduce((sum, v) => sum + (v.stock_quantity || 0), 0) || 0;
  return total > 0 && total <= 5;
});
const activeImagePath = ref('');
const selectedColor = ref('');
const selectedSize = ref('');
const qty = ref(1);

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

const formatProductPrice = (prod) => {
  if (!prod) return '₹0.00';
  if (prod.price_display) return prod.price_display;

  if (prod.variants && prod.variants.length > 0) {
    const prices = prod.variants
      .map(v => parseFloat(v.selling_price))
      .filter(p => !isNaN(p) && p > 0);

    if (prices.length > 0) {
      const min = Math.min(...prices);
      const max = Math.max(...prices);
      if (min < max) {
        return `₹${min.toFixed(2)} - ₹${max.toFixed(2)}`;
      }
      return `₹${min.toFixed(2)}`;
    }
  }

  const basePrice = parseFloat(prod.selling_price || 0);
  return `₹${basePrice.toFixed(2)}`;
};

const formatProductMrp = (prod) => {
  if (!prod) return null;
  if (prod.mrp_display) return prod.mrp_display;

  if (prod.variants && prod.variants.length > 0) {
    const mrps = prod.variants
      .map(v => parseFloat(v.mrp))
      .filter(m => !isNaN(m) && m > 0);
    const sellPrices = prod.variants
      .map(v => parseFloat(v.selling_price))
      .filter(p => !isNaN(p) && p > 0);

    if (mrps.length > 0) {
      const minMrp = Math.min(...mrps);
      const maxMrp = Math.max(...mrps);
      const minSell = sellPrices.length > 0 ? Math.min(...sellPrices) : parseFloat(prod.selling_price || 0);
      const maxSell = sellPrices.length > 0 ? Math.max(...sellPrices) : parseFloat(prod.selling_price || 0);

      if (maxMrp > maxSell || minMrp > minSell) {
        if (minMrp < maxMrp) {
          return `₹${minMrp.toFixed(2)} - ₹${maxMrp.toFixed(2)}`;
        }
        return `₹${maxMrp.toFixed(2)}`;
      }
    }
  }

  const baseMrp = parseFloat(prod.mrp || 0);
  const baseSell = parseFloat(prod.selling_price || 0);
  if (baseMrp > baseSell) {
    return `₹${baseMrp.toFixed(2)}`;
  }
  return null;
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

const getPrimaryImage = (prod) => {
  if (!prod) return '/asset/profile/logo.png';
  if (prod.primary_image_url) return prod.primary_image_url;
  if (prod.images && prod.images.length > 0) {
    const primary = prod.images.find(img => img.is_primary);
    return primary ? (primary.image_path || primary.url) : (prod.images[0].image_path || prod.images[0].url);
  }
  return '/asset/profile/logo.png';
};

const trackRecentlyViewed = (prod) => {
  if (!prod) return;
  try {
    let list = JSON.parse(localStorage.getItem('mayasree_recently_viewed') || '[]');
    // Remove if already exists to move to top
    list = list.filter(item => item.id !== prod.id);
    // Add current product at the beginning
    list.unshift({
      id: prod.id,
      uuid: prod.uuid,
      name: prod.name,
      selling_price: prod.selling_price,
      mrp: prod.mrp,
      image: getPrimaryImage(prod)
    });
    // Limit to 8 items
    list = list.slice(0, 8);
    localStorage.setItem('mayasree_recently_viewed', JSON.stringify(list));
  } catch (e) {
    console.error('Failed to track recently viewed product:', e);
  }
};

const loadRecentlyViewed = () => {
  try {
    const list = JSON.parse(localStorage.getItem('mayasree_recently_viewed') || '[]');
    recentlyViewed.value = list.filter(item => item.id !== product.value?.id);
  } catch (e) {
    recentlyViewed.value = [];
  }
};

const fetchDetails = async (id) => {
  if (!id) return;
  loading.value = true;
  try {
    const response = await axios.get(`/api/storefront/products/${id}`);
    if (response.data && response.data.success) {
      product.value = response.data.data;
      relatedProducts.value = response.data.related || [];
      activeImagePath.value = getPrimaryImage(product.value);
      
      // Auto select first variant if available
      if (product.value.variants && product.value.variants.length) {
        const firstVariant = product.value.variants[0];
        selectedColor.value = firstVariant.color || '';
        selectedSize.value = firstVariant.size || 'OS';
      }
      
      // Track this product & refresh recently viewed list
      trackRecentlyViewed(product.value);
      loadRecentlyViewed();
    } else {
      product.value = null;
    }
  } catch (err) {
    console.error('Failed to query product details:', err);
    product.value = null;
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

    // Progressive auth trigger: Prompt guest user to create account when adding to cart
    if (!authStore.isAuthenticated) {
      authStore.openAuthModal('register', 'cart');
    }
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

const isLightboxOpen = ref(false);
const isZoomed = ref(false);
const zoomPos = ref({ x: 50, y: 50 });
let touchStartX = 0;
let touchEndX = 0;

const activeImageIndex = computed(() => {
  if (!product.value?.images || product.value.images.length === 0) return 0;
  const idx = product.value.images.findIndex(img => img.image_path === activeImagePath.value);
  return idx >= 0 ? idx : 0;
});

const openLightbox = () => {
  isLightboxOpen.value = true;
  isZoomed.value = false;
  zoomPos.value = { x: 50, y: 50 };
  document.body.style.overflow = 'hidden';
  window.addEventListener('keydown', handleLightboxKeydown);
};

const closeLightbox = () => {
  isLightboxOpen.value = false;
  isZoomed.value = false;
  document.body.style.overflow = '';
  window.removeEventListener('keydown', handleLightboxKeydown);
};

const toggleLightboxZoom = () => {
  isZoomed.value = !isZoomed.value;
  if (!isZoomed.value) {
    zoomPos.value = { x: 50, y: 50 };
  }
};

const handleLightboxMouseMove = (e) => {
  if (!isZoomed.value) return;
  const rect = e.currentTarget.getBoundingClientRect();
  const x = ((e.clientX - rect.left) / rect.width) * 100;
  const y = ((e.clientY - rect.top) / rect.height) * 100;
  zoomPos.value = {
    x: Math.max(0, Math.min(100, x)),
    y: Math.max(0, Math.min(100, y)),
  };
};

const handleTouchStart = (e) => {
  if (e.changedTouches && e.changedTouches[0]) {
    touchStartX = e.changedTouches[0].screenX;
  }
};

const handleTouchEnd = (e) => {
  if (e.changedTouches && e.changedTouches[0]) {
    touchEndX = e.changedTouches[0].screenX;
    if (Math.abs(touchEndX - touchStartX) > 45) {
      if (touchEndX < touchStartX) {
        nextImage();
      } else {
        prevImage();
      }
    }
  }
};

const handleLightboxKeydown = (e) => {
  if (!isLightboxOpen.value) return;
  if (e.key === 'Escape') {
    closeLightbox();
  } else if (e.key === 'ArrowRight') {
    nextImage();
  } else if (e.key === 'ArrowLeft') {
    prevImage();
  } else if (e.key === '+' || e.key === '=') {
    isZoomed.value = true;
  } else if (e.key === '-') {
    isZoomed.value = false;
  }
};

onMounted(() => {
  fetchDetails(route.params.uuid);
});

watch(
  () => route.params.uuid,
  (newUuid) => {
    if (newUuid) {
      fetchDetails(newUuid);
    }
  }
);

onUnmounted(() => {
  document.body.style.overflow = '';
  window.removeEventListener('keydown', handleLightboxKeydown);
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

/* Similar & Recently Viewed Products Sections */
.detail-recommend-section {
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px solid #E8DED2;
}

.recently-viewed-margin {
  margin-top: 50px;
}

.detail-section-title {
  font-family: 'Cormorant Garamond', 'Playfair Display', serif;
  font-size: 1.8rem;
  font-weight: 600;
  color: #2F2A26;
  margin-bottom: 20px;
  letter-spacing: 1px;
}

.detail-products-carousel {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  padding-bottom: 15px;
  width: 100%;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.detail-products-carousel::-webkit-scrollbar {
  display: none;
}

.detail-luxury-card {
  width: 220px;
  flex-shrink: 0;
  background: #ffffff;
  border: 1px solid #E8DED2;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.detail-luxury-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(110, 31, 58, 0.06);
  border-color: #D8C7A3;
}

.card-img-box {
  width: 100%;
  aspect-ratio: 3 / 4;
  background: #FAF8F5;
  overflow: hidden;
  position: relative;
}

.card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.detail-luxury-card:hover .card-img {
  transform: scale(1.04);
}

.card-info-box {
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.card-title {
  font-family: 'Poppins', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  color: #2F2A26;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 2.6em;
  line-height: 1.3;
}

.card-price-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.price-current {
  font-weight: 700;
  color: #6E1F3A;
  font-size: 0.95rem;
}

.price-old {
  font-size: 0.8rem;
  color: #9c8a94;
  text-decoration: line-through;
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

/* Premium Loading State */
.premium-loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 16px;
  background-color: #FAF8F5;
}

.loading-emblem {
  animation: spin-slow 3s linear infinite;
}

.loading-lotus {
  width: 64px;
  height: 64px;
  display: block;
}

@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

.loading-brand-text {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: 6px;
  color: #6E1F3A;
  margin: 0;
}

.loading-sub-text {
  font-family: 'Poppins', sans-serif;
  font-size: 0.75rem;
  font-weight: 400;
  color: #B68D40;
  letter-spacing: 2px;
  margin: 0;
  animation: fade-pulse 1.5s ease-in-out infinite;
}

@keyframes fade-pulse {
  0%, 100% { opacity: 0.4; }
  50%       { opacity: 1; }
}

/* ==========================================================================
   Luxury Ultra-HD Lightbox & Zoom Studio
   ========================================================================== */
.zoomable-main-img {
  cursor: zoom-in;
  transition: border-color 0.25s ease, box-shadow 0.25s ease;
}

.zoomable-main-img:hover {
  border-color: #B68D40;
  box-shadow: 0 10px 25px rgba(128, 0, 32, 0.08);
}

.zoomable-main-img:hover .luxury-zoom-indicator {
  transform: translateY(-2px);
  background: rgba(128, 0, 32, 0.95);
  border-color: #B68D40;
}

.luxury-zoom-indicator {
  position: absolute;
  bottom: 12px;
  right: 12px;
  background: rgba(30, 20, 25, 0.85);
  color: #ffffff;
  backdrop-filter: blur(8px);
  border: 1px solid rgba(182, 141, 64, 0.4);
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.18);
  pointer-events: none;
  transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
  z-index: 2;
}

.luxury-lightbox-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(12, 6, 10, 0.95);
  backdrop-filter: blur(16px);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
  user-select: none;
  outline: none;
}

.lightbox-top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 28px;
  background: linear-gradient(to bottom, rgba(0,0,0,0.75), transparent);
  z-index: 10;
}

.lightbox-brand {
  font-family: 'Playfair Display', serif;
  font-size: 0.75rem;
  color: #B68D40;
  letter-spacing: 2px;
  text-transform: uppercase;
}

.lightbox-prod-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.15rem;
  color: #ffffff;
  margin: 2px 0 0 0;
  font-weight: 600;
}

.lightbox-controls-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.lightbox-counter-pill {
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.15);
  color: #e2e8f0;
  font-size: 0.75rem;
  padding: 6px 14px;
  border-radius: 20px;
  font-weight: 500;
}

.lightbox-btn {
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.22);
  color: #ffffff;
  padding: 6px 14px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 38px;
}

.lightbox-btn:hover {
  background: rgba(255,255,255,0.22);
  border-color: #B68D40;
}

.lightbox-btn.active {
  background: #B68D40;
  color: #1a0f14;
  font-weight: 700;
  border-color: #B68D40;
}

.lightbox-close-btn {
  background: rgba(255,255,255,0.15);
  border-radius: 50%;
  width: 40px;
  height: 40px;
  padding: 0;
  justify-content: center;
}

.lightbox-close-btn:hover {
  background: #800020;
  border-color: #B68D40;
  transform: rotate(90deg);
}

.lightbox-stage {
  position: relative;
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 20px;
}

.lightbox-zoom-container {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  cursor: zoom-in;
}

.lightbox-zoom-container.is-zoomed {
  cursor: crosshair;
}

.lightbox-main-img {
  max-width: 90vw;
  max-height: 75vh;
  object-fit: contain;
  border-radius: 8px;
  transition: transform 0.15s cubic-bezier(0.2, 0.8, 0.2, 1);
  box-shadow: 0 12px 40px rgba(0,0,0,0.5);
}

.lightbox-nav-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: rgba(30, 20, 25, 0.75);
  border: 1px solid rgba(255,255,255,0.25);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  transition: all 0.2s ease;
}

.lightbox-nav-arrow.arrow-left {
  left: 24px;
}

.lightbox-nav-arrow.arrow-right {
  right: 24px;
}

.lightbox-nav-arrow:hover {
  background: #800020;
  border-color: #B68D40;
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

.lightbox-hint-pill {
  position: absolute;
  bottom: 12px;
  background: rgba(0,0,0,0.65);
  backdrop-filter: blur(8px);
  color: rgba(255,255,255,0.85);
  font-size: 0.75rem;
  padding: 6px 16px;
  border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.12);
  pointer-events: none;
}

.lightbox-bottom-bar {
  padding: 14px 20px;
  background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);
  display: flex;
  justify-content: center;
  z-index: 10;
}

.lightbox-thumb-strip {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  max-width: 90vw;
  padding: 4px 8px;
}

.lightbox-thumb-item {
  width: 56px;
  height: 56px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid transparent;
  cursor: pointer;
  opacity: 0.6;
  transition: all 0.2s ease;
  flex-shrink: 0;
  background: #ffffff;
}

.lightbox-thumb-item:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.lightbox-thumb-item.active {
  border-color: #B68D40;
  opacity: 1;
  transform: scale(1.08);
  box-shadow: 0 0 12px rgba(182, 141, 64, 0.6);
}

.lightbox-thumb-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.lightbox-fade-enter-active,
.lightbox-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.lightbox-fade-enter-from,
.lightbox-fade-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

@media (max-width: 768px) {
  .lightbox-top-bar {
    padding: 12px 16px;
  }
  .lightbox-prod-name {
    font-size: 0.95rem;
  }
  .lightbox-btn .btn-text {
    display: none;
  }
  .lightbox-nav-arrow {
    width: 40px;
    height: 40px;
  }
  .lightbox-nav-arrow.arrow-left {
    left: 10px;
  }
  .lightbox-nav-arrow.arrow-right {
    right: 10px;
  }
  .lightbox-thumb-item {
    width: 44px;
    height: 44px;
  }
}

/* ==========================================================================
   Luxury Category Size Guide Modal
   ========================================================================== */
.size-guide-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(12, 6, 10, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.size-guide-modal-container {
  background: #ffffff;
  border-radius: 16px;
  max-width: 680px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(182, 141, 64, 0.3);
  overflow: hidden;
  animation: slide-up 0.25s ease-out;
}

@keyframes slide-up {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.size-guide-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1ece4;
  background: #fdfbf7;
}

.size-guide-tag {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: #B68D40;
  font-weight: 700;
}

.size-guide-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem;
  color: #6E1F3A;
  margin: 2px 0 0 0;
  font-weight: 700;
}

.size-guide-close-btn {
  background: #f3eee6;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #6E1F3A;
  transition: all 0.2s ease;
}

.size-guide-close-btn:hover {
  background: #6E1F3A;
  color: #ffffff;
}

.size-guide-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex-grow: 1;
}

.category-chart-image-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.chart-image-card {
  width: 100%;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #e8dfd2;
  box-shadow: 0 4px 16px rgba(110, 31, 58, 0.06);
  background: #ffffff;
  text-align: center;
}

.category-size-chart-img {
  max-width: 100%;
  height: auto;
  display: block;
  margin: 0 auto;
}

.chart-hint-row {
  font-size: 0.8rem;
  color: #7A726A;
  background: #fdfbf7;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #f1ece4;
  width: 100%;
  text-align: center;
}

.table-responsive-box {
  overflow-x: auto;
  border-radius: 10px;
  border: 1px solid #ece4d8;
  margin-bottom: 1.25rem;
}

.luxury-size-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
  text-align: left;
}

.luxury-size-table th {
  background: #6E1F3A;
  color: #ffffff;
  padding: 10px 14px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.luxury-size-table td {
  padding: 10px 14px;
  border-bottom: 1px solid #f1ece4;
  color: #2D2424;
}

.luxury-size-table tr:nth-child(even) td {
  background: #faf7f2;
}

.measurement-tips-box {
  background: #fdfbf7;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  border: 1px dashed #B68D40;
}

.tips-heading {
  font-size: 0.85rem;
  color: #6E1F3A;
  margin: 0 0 0.5rem 0;
  font-weight: 700;
}

.tips-list {
  margin: 0;
  padding-left: 1.2rem;
  font-size: 0.8rem;
  color: #554D47;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.size-guide-footer {
  padding: 1rem 1.5rem;
  background: #fdfbf7;
  border-top: 1px solid #f1ece4;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.footer-help-text {
  font-size: 0.82rem;
  color: #7A726A;
  font-weight: 500;
}

.btn-whatsapp-fit-advice {
  background: #25D366;
  color: #ffffff;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-whatsapp-fit-advice:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);
}

/* ==========================================================================
   Luxury Sold Out & Low Stock Alerts
   ========================================================================== */
.luxury-sold-out-box {
  background: linear-gradient(135deg, #FFF5F5 0%, #FEF2F2 100%);
  border: 1px solid rgba(220, 38, 38, 0.3);
  border-radius: 12px;
  padding: 1.25rem 1.5rem;
  margin: 0.75rem 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  box-shadow: 0 4px 16px rgba(220, 38, 38, 0.05);
}

.sold-out-box-header {
  display: flex;
  align-items: center;
  gap: 10px;
}

.sold-out-pill-tag {
  background: #1a0f14;
  color: #FDFBF7;
  border: 1px solid #B68D40;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 1.5px;
  padding: 4px 10px;
  border-radius: 6px;
  text-transform: uppercase;
}

.sold-out-box-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.15rem;
  color: #991b1b;
  font-weight: 700;
}

.sold-out-box-desc {
  font-size: 0.85rem;
  color: #554D47;
  line-height: 1.5;
  margin: 0;
}

.btn-sold-out-whatsapp {
  align-self: flex-start;
  background: #25D366;
  color: #ffffff;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 600;
  padding: 10px 20px;
  border-radius: 24px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-sold-out-whatsapp:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(37, 211, 102, 0.45);
}

.luxury-low-stock-box {
  background: #FFFBEB;
  border: 1px solid #FDE68A;
  border-radius: 10px;
  padding: 10px 16px;
  margin: 0.5rem 0;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  color: #92400E;
  font-weight: 500;
  animation: pulse-light 2s ease-in-out infinite;
}

.low-stock-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #D97706;
  flex-shrink: 0;
}

@keyframes pulse-light {
  0%, 100% { opacity: 0.9; }
  50% { opacity: 1; }
}

.price-muted {
  opacity: 0.6;
}
</style>
