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

      <!-- Frequently Bought Together Section (Bundle & Save) -->
      <section v-if="boughtTogetherList && boughtTogetherList.length" class="bought-together-section">
        <div class="section-header-wrap">
          <div class="section-badge-pill">
            <Sparkles :size="14" />
            <span>Curated Ensemble</span>
          </div>
          <h3 class="bought-together-title">Frequently Bought Together</h3>
          <p class="bought-together-subtitle">Pair this design with matching ethnic styles and complete your perfect look.</p>
        </div>

        <div class="bought-together-card">
          <!-- Products Row / Bundle Grid -->
          <div class="bought-together-items-wrap">
            
            <!-- Item 1: Main Product (Currently Viewed) -->
            <div class="bundle-item-card bundle-item-card--main">
              <div class="bundle-item-checkbox">
                <input type="checkbox" checked disabled id="bt-main" class="custom-checkbox" />
                <label for="bt-main" class="bundle-tag-main">This Item</label>
              </div>
              <div class="bundle-img-wrap">
                <img 
                  v-protect-image
                  :src="activeImagePath || getPrimaryImage(product)" 
                  :alt="product.name" 
                  class="bundle-img" 
                  loading="lazy"
                />
              </div>
              <div class="bundle-info">
                <h4 class="bundle-product-name" :title="product.name">{{ product.name }}</h4>
                <div class="bundle-variant-tag">
                  {{ selectedVariant ? `Size: ${selectedVariant.size || 'Free Size'}${selectedVariant.color ? ' • ' + selectedVariant.color : ''}` : 'Selected Size' }}
                </div>
                <div class="bundle-price-row">
                  <span class="bundle-price-current">₹{{ selectedVariant?.selling_price || product.selling_price }}</span>
                  <span v-if="selectedVariant?.mrp || product.mrp" class="bundle-price-old">MRP ₹{{ selectedVariant?.mrp || product.mrp }}</span>
                </div>
              </div>
            </div>

            <!-- Loop Paired Items with '+' Connector -->
            <template v-for="item in boughtTogetherList" :key="item.id">
              <div class="bundle-plus-connector">
                <Plus :size="18" />
              </div>

              <div 
                class="bundle-item-card" 
                :class="{ 'bundle-item-card--inactive': !bundleItemStates[item.id]?.checked }"
              >
                <div class="bundle-item-checkbox">
                  <input 
                    type="checkbox" 
                    :id="'bt-' + item.id" 
                    v-model="bundleItemStates[item.id].checked" 
                    class="custom-checkbox" 
                  />
                  <label :for="'bt-' + item.id" class="bundle-checkbox-label">Include</label>
                </div>
                <div class="bundle-img-wrap" @click="reloadDetail(item.uuid)">
                  <img 
                    v-protect-image
                    :src="getPrimaryImage(item)" 
                    :alt="item.name" 
                    class="bundle-img" 
                    loading="lazy"
                  />
                </div>
                <div class="bundle-info">
                  <h4 class="bundle-product-name" @click="reloadDetail(item.uuid)" :title="item.name">{{ item.name }}</h4>
                  
                  <!-- Variant Selector for paired item -->
                  <div v-if="item.variants && item.variants.length > 1" class="bundle-variant-select-wrap">
                    <select 
                      v-model="bundleItemStates[item.id].selectedVariantId" 
                      class="bundle-select-input"
                    >
                      <option v-for="v in item.variants" :key="v.id" :value="v.id">
                        Size: {{ v.size || 'OS' }} {{ v.color ? `(${v.color})` : '' }} - ₹{{ v.selling_price }}
                      </option>
                    </select>
                  </div>
                  <div v-else class="bundle-variant-tag">
                    {{ item.variants?.[0]?.size ? `Size: ${item.variants[0].size}` : 'Standard Fit' }}
                  </div>

                  <div class="bundle-price-row">
                    <span class="bundle-price-current">₹{{ getBundleItemPrice(item) }}</span>
                    <span v-if="getBundleItemMrp(item)" class="bundle-price-old">MRP ₹{{ getBundleItemMrp(item) }}</span>
                  </div>
                </div>
              </div>
            </template>

          </div>

          <!-- Bundle Summary Action Box -->
          <div class="bundle-action-box">
            <div class="bundle-summary-header">
              <span class="bundle-summary-label">Bundle Total ({{ bundleSelectedCount }} Items)</span>
              <div class="bundle-total-price-wrap">
                <span class="bundle-total-price">₹{{ bundleTotalPrice }}</span>
                <span v-if="parseFloat(bundleTotalMrp) > parseFloat(bundleTotalPrice)" class="bundle-total-mrp">MRP ₹{{ bundleTotalMrp }}</span>
              </div>
              <div v-if="parseFloat(bundleSavings) > 0" class="bundle-savings-badge">
                🎉 You Save ₹{{ bundleSavings }} with this Combo
              </div>
            </div>

            <button 
              type="button"
              class="btn-add-bundle"
              :disabled="bundleAdding || isProductSoldOut"
              @click="addBundleToCart"
            >
              <ShoppingBag :size="18" />
              <span>{{ bundleAdding ? 'Adding Bundle...' : `Add Bundle to Cart (${bundleSelectedCount} Items)` }}</span>
            </button>

            <div v-if="bundleAddedSuccess" class="bundle-success-toast">
              <Check :size="15" />
              <span>{{ bundleSelectedCount }} items successfully added to cart!</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Customer Reviews Section -->
      <ReviewSection v-if="product" :product="product" />

      <!-- More Products For You Section (Curated Recommendation Grid) -->
      <section v-if="moreForYouProducts && moreForYouProducts.length" class="more-for-you-section">
        <div class="section-header-wrap">
          <div class="section-badge-pill">
            <Sparkles :size="14" />
            <span>Curated For You</span>
          </div>
          <h3 class="more-for-you-title">More Products For You</h3>
          <p class="more-for-you-subtitle">Handpicked traditional & modern ethnic styles tailored to elevate your wardrobe.</p>
        </div>

        <div class="more-for-you-grid">
          <div 
            v-for="prod in moreForYouProducts" 
            :key="prod.id" 
            class="more-product-card"
            @click="reloadDetail(prod.uuid)"
          >
            <!-- Image Wrap -->
            <div class="more-card-img-wrap">
              <img 
                v-protect-image
                :src="getPrimaryImage(prod)" 
                class="more-card-img" 
                :alt="prod.name"
                loading="lazy"
              />

              <!-- Wishlist Floating Button -->
              <button 
                type="button" 
                class="more-card-wishlist-btn"
                :class="{ 'is-active': isInWishlist(prod.id) }"
                @click.stop="toggleWishlist(prod)"
                aria-label="Wishlist"
              >
                <Heart :size="17" :fill="isInWishlist(prod.id) ? '#B91C1C' : 'none'" :stroke="isInWishlist(prod.id) ? '#B91C1C' : '#5B163A'" />
              </button>

              <!-- Discount / Status Badge -->
              <span v-if="getProductDiscountPct(prod) > 0" class="more-card-discount-badge">
                {{ getProductDiscountPct(prod) }}% OFF
              </span>
              <span v-else-if="prod.badge" class="more-card-tag-badge">
                {{ prod.badge }}
              </span>
            </div>

            <!-- Product Card Info -->
            <div class="more-card-info">
              <span v-if="prod.category" class="more-card-category">{{ prod.category.name }}</span>
              <h4 class="more-card-title" :title="prod.name">{{ prod.name }}</h4>

              <div class="more-card-rating" v-if="prod.avg_rating || prod.rating">
                <span class="rating-stars">★ {{ Number(prod.avg_rating || prod.rating || 4.8).toFixed(1) }}</span>
                <span class="rating-count">({{ prod.reviews_count || 18 }})</span>
              </div>

              <div class="more-card-price-row">
                <span class="more-price-current">{{ formatProductPrice(prod) }}</span>
                <span v-if="formatProductMrp(prod)" class="more-price-old">{{ formatProductMrp(prod) }}</span>
              </div>

              <!-- Quick View / Explore Button -->
              <button type="button" class="btn-more-card-action">
                View Design &rarr;
              </button>
            </div>
          </div>
        </div>
      </section>

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

              <!-- Default interactive sizing guide table when no custom image is uploaded -->
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
import { ChevronLeft, ChevronRight, ZoomIn, ZoomOut, X, Heart, Check, Plus, ShoppingBag, Sparkles, Star } from 'lucide-vue-next';
import ProductVariantSelector from '../../components/ProductVariantSelector.vue';
import ReturnPolicyNotice from '../../components/ReturnPolicyNotice.vue';
import ReviewSection from '../../components/ReviewSection.vue';

import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const emit = defineEmits(['update-cart-count', 'update-wishlist-count']);

const showSizeGuideModal = ref(false);

const triggerSizeGuide = () => {
  showSizeGuideModal.value = true;
};

const product = ref(null);
const relatedProducts = ref([]);
const boughtTogetherList = ref([]);
const moreForYouProducts = ref([]);
const bundleItemStates = ref({});
const bundleAdding = ref(false);
const bundleAddedSuccess = ref(false);
const wishlist = ref([]);
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

const loadWishlist = () => {
  try {
    wishlist.value = JSON.parse(localStorage.getItem('vibe_wishlist_items') || '[]');
  } catch (e) {
    wishlist.value = [];
  }
};

const isInWishlist = (id) => {
  return wishlist.value.some(item => item.id === id);
};

const toggleWishlist = async (prod) => {
  const index = wishlist.value.findIndex(item => item.id === prod.id);
  const isAdding = index < 0;

  if (index >= 0) {
    wishlist.value.splice(index, 1);
    if (authStore.isAuthenticated) {
      try {
        await axios.delete(`/api/customer/wishlist/${prod.uuid || prod.id}`);
      } catch (err) {}
    }
  } else {
    wishlist.value.push({
      id: prod.id,
      uuid: prod.uuid,
      name: prod.name,
      selling_price: prod.selling_price,
      image: getPrimaryImage(prod)
    });
    if (authStore.isAuthenticated) {
      try {
        await axios.post('/api/customer/wishlist', { product_id: prod.id });
      } catch (err) {}
    }
  }

  localStorage.setItem('vibe_wishlist_items', JSON.stringify(wishlist.value));
  emit('update-wishlist-count');

  if (isAdding && !authStore.isAuthenticated) {
    authStore.openAuthModal('register', 'wishlist');
  }
};

const getProductDiscountPct = (p) => {
  const sell = parseFloat(p.selling_price || 0);
  const mrp = parseFloat(p.mrp || 0);
  if (mrp > sell && mrp > 0) {
    return Math.round(((mrp - sell) / mrp) * 100);
  }
  return 0;
};

const getBundleItemPrice = (item) => {
  const vId = bundleItemStates.value[item.id]?.selectedVariantId;
  if (vId && item.variants) {
    const matched = item.variants.find(v => v.id === vId);
    if (matched && matched.selling_price) return parseFloat(matched.selling_price).toFixed(2);
  }
  return parseFloat(item.selling_price || 0).toFixed(2);
};

const getBundleItemMrp = (item) => {
  const vId = bundleItemStates.value[item.id]?.selectedVariantId;
  if (vId && item.variants) {
    const matched = item.variants.find(v => v.id === vId);
    if (matched && matched.mrp) return parseFloat(matched.mrp).toFixed(2);
  }
  return item.mrp ? parseFloat(item.mrp).toFixed(2) : null;
};

const bundleSelectedCount = computed(() => {
  let count = 1; // Main product
  boughtTogetherList.value.forEach(item => {
    if (bundleItemStates.value[item.id]?.checked) {
      count++;
    }
  });
  return count;
});

const bundleTotalPrice = computed(() => {
  let total = parseFloat(selectedVariant.value?.selling_price || product.value?.selling_price || 0);
  boughtTogetherList.value.forEach(item => {
    if (bundleItemStates.value[item.id]?.checked) {
      total += parseFloat(getBundleItemPrice(item));
    }
  });
  return total.toFixed(2);
});

const bundleTotalMrp = computed(() => {
  let total = parseFloat(selectedVariant.value?.mrp || product.value?.mrp || selectedVariant.value?.selling_price || product.value?.selling_price || 0);
  boughtTogetherList.value.forEach(item => {
    if (bundleItemStates.value[item.id]?.checked) {
      const mrp = getBundleItemMrp(item);
      total += (mrp ? parseFloat(mrp) : parseFloat(getBundleItemPrice(item)));
    }
  });
  return total.toFixed(2);
});

const bundleSavings = computed(() => {
  const diff = parseFloat(bundleTotalMrp.value) - parseFloat(bundleTotalPrice.value);
  return diff > 0 ? diff.toFixed(2) : '0.00';
});

const addBundleToCart = () => {
  if (!product.value) return;
  bundleAdding.value = true;

  try {
    const cart = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');

    // 1. Add Main product
    const mainVariant = selectedVariant.value || product.value.variants?.[0];
    if (mainVariant) {
      const existMain = cart.findIndex(i => i.product_variant_id === mainVariant.id);
      if (existMain >= 0) {
        cart[existMain].quantity += 1;
      } else {
        cart.push({
          product_id: product.value.id,
          product_uuid: product.value.uuid,
          product_variant_id: mainVariant.id,
          sku: mainVariant.sku,
          name: product.value.name,
          size: mainVariant.size,
          color: mainVariant.color,
          image: activeImagePath.value || getPrimaryImage(product.value),
          selling_price: mainVariant.selling_price || product.value.selling_price,
          mrp: mainVariant.mrp || product.value.mrp,
          quantity: 1,
          stock_quantity: mainVariant.stock_quantity,
        });
      }
    }

    // 2. Add checked paired items
    boughtTogetherList.value.forEach(item => {
      if (bundleItemStates.value[item.id]?.checked) {
        const vId = bundleItemStates.value[item.id]?.selectedVariantId;
        const matchedVariant = item.variants?.find(v => v.id === vId) || item.variants?.[0];
        if (matchedVariant) {
          const existIdx = cart.findIndex(i => i.product_variant_id === matchedVariant.id);
          if (existIdx >= 0) {
            cart[existIdx].quantity += 1;
          } else {
            cart.push({
              product_id: item.id,
              product_uuid: item.uuid,
              product_variant_id: matchedVariant.id,
              sku: matchedVariant.sku,
              name: item.name,
              size: matchedVariant.size,
              color: matchedVariant.color,
              image: getPrimaryImage(item),
              selling_price: matchedVariant.selling_price || item.selling_price,
              mrp: matchedVariant.mrp || item.mrp,
              quantity: 1,
              stock_quantity: matchedVariant.stock_quantity,
            });
          }
        }
      }
    });

    localStorage.setItem('vibe_cart_items', JSON.stringify(cart));
    emit('update-cart-count');
    bundleAddedSuccess.value = true;
    setTimeout(() => {
      bundleAddedSuccess.value = false;
    }, 4000);

    if (!authStore.isAuthenticated) {
      authStore.openAuthModal('register', 'cart');
    }
  } catch (e) {
    console.error('Bundle add to cart error:', e);
  } finally {
    bundleAdding.value = false;
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
      boughtTogetherList.value = response.data.bought_together || [];
      moreForYouProducts.value = response.data.more_for_you || [];
      activeImagePath.value = getPrimaryImage(product.value);
      
      // Initialize bundle states for each bought together item
      const states = {};
      boughtTogetherList.value.forEach(item => {
        const firstVariantId = item.variants?.[0]?.id || null;
        states[item.id] = {
          checked: true,
          selectedVariantId: firstVariantId
        };
      });
      bundleItemStates.value = states;

      // Auto select first variant if available
      if (product.value.variants && product.value.variants.length) {
        const firstVariant = product.value.variants[0];
        selectedColor.value = firstVariant.color || '';
        selectedSize.value = firstVariant.size || 'OS';
      }
      
      // Track this product & refresh recently viewed list
      trackRecentlyViewed(product.value);
      loadRecentlyViewed();
      loadWishlist();
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

/* ==========================================================================
   Frequently Bought Together (Curated Ensemble) Styles
   ========================================================================== */
.bought-together-section {
  margin: 2.5rem 0;
  padding: 2rem;
  background: #FAF8F5;
  border: 1px solid rgba(182, 141, 64, 0.25);
  border-radius: 16px;
  box-shadow: 0 6px 24px rgba(91, 22, 58, 0.04);
}

.section-header-wrap {
  margin-bottom: 1.5rem;
}

.section-badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(182, 141, 64, 0.12);
  color: #8A6418;
  border: 1px solid rgba(182, 141, 64, 0.25);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 20px;
  margin-bottom: 0.5rem;
}

.bought-together-title,
.more-for-you-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem;
  font-weight: 800;
  color: #1E293B;
  margin: 0 0 0.35rem 0;
  letter-spacing: -0.3px;
}

.bought-together-subtitle,
.more-for-you-subtitle {
  font-size: 0.88rem;
  color: #64748B;
  margin: 0;
}

.bought-together-card {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 2rem;
  align-items: center;
}

@media (max-width: 960px) {
  .bought-together-card {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}

.bought-together-items-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.bundle-plus-connector {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ffffff;
  border: 1.5px solid #D4AF37;
  color: #5B163A;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(212, 175, 55, 0.2);
}

.bundle-item-card {
  flex: 1;
  min-width: 170px;
  max-width: 220px;
  background: #ffffff;
  border: 1.5px solid var(--color-border);
  border-radius: 12px;
  padding: 0.85rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  transition: all 0.25s ease;
  position: relative;
}

.bundle-item-card:hover {
  border-color: #5B163A;
  box-shadow: 0 6px 18px rgba(91, 22, 58, 0.08);
  transform: translateY(-2px);
}

.bundle-item-card--inactive {
  opacity: 0.5;
  filter: grayscale(0.6);
  border-style: dashed;
}

.bundle-item-checkbox {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: #475569;
  font-weight: 600;
}

.custom-checkbox {
  accent-color: #5B163A;
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.bundle-tag-main {
  background: #5B163A;
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 4px;
  letter-spacing: 0.4px;
  text-transform: uppercase;
}

.bundle-img-wrap {
  width: 100%;
  aspect-ratio: 3/4;
  border-radius: 8px;
  overflow: hidden;
  background: #f8f8f8;
  cursor: pointer;
}

.bundle-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.bundle-item-card:hover .bundle-img {
  transform: scale(1.04);
}

.bundle-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.bundle-product-name {
  font-size: 0.82rem;
  font-weight: 700;
  color: #1E293B;
  margin: 0;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  cursor: pointer;
}

.bundle-variant-tag {
  font-size: 0.72rem;
  color: #64748B;
  background: #FAF5F0;
  padding: 2px 6px;
  border-radius: 4px;
  width: fit-content;
}

.bundle-select-input {
  font-size: 0.75rem;
  padding: 4px 6px;
  border-radius: 6px;
  border: 1px solid var(--color-border);
  background: #ffffff;
  color: #1E293B;
  width: 100%;
  cursor: pointer;
  outline: none;
}

.bundle-select-input:focus {
  border-color: #5B163A;
}

.bundle-price-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
  margin-top: 2px;
}

.bundle-price-current {
  font-size: 0.95rem;
  font-weight: 800;
  color: #5B163A;
}

.bundle-price-old {
  font-size: 0.75rem;
  color: #94A3B8;
  text-decoration: line-through;
}

.bundle-action-box {
  background: #ffffff;
  border: 1.5px solid rgba(91, 22, 58, 0.15);
  border-radius: 14px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  box-shadow: 0 4px 16px rgba(91, 22, 58, 0.05);
}

.bundle-summary-label {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748B;
  font-weight: 700;
}

.bundle-total-price-wrap {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin: 4px 0;
}

.bundle-total-price {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  font-weight: 800;
  color: #5B163A;
}

.bundle-total-mrp {
  font-size: 0.95rem;
  color: #94A3B8;
  text-decoration: line-through;
}

.bundle-savings-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.76rem;
  font-weight: 700;
  color: #15803D;
  background: #DCFCE7;
  padding: 3px 8px;
  border-radius: 6px;
  width: fit-content;
}

.btn-add-bundle {
  background: #5B163A;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  min-height: 48px;
  padding: 0 1.25rem;
  font-size: 0.92rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(91, 22, 58, 0.25);
  transition: all 0.25s ease;
}

.btn-add-bundle:hover:not(:disabled) {
  background: #3D0E26;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(91, 22, 58, 0.35);
}

.btn-add-bundle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.bundle-success-toast {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  color: #15803D;
  background: #F0FDF4;
  border: 1px solid #BBF7D0;
  padding: 6px 10px;
  border-radius: 6px;
}

/* ==========================================================================
   More Products For You (Curated Grid) Styles
   ========================================================================== */
.more-for-you-section {
  margin: 3.5rem 0 2rem 0;
}

.more-for-you-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
  margin-top: 1.25rem;
}

@media (max-width: 1100px) {
  .more-for-you-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
  }
}

@media (max-width: 768px) {
  .more-for-you-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.85rem;
  }
}

.more-product-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.2, 0, 0, 1);
}

.more-product-card:hover {
  transform: translateY(-4px);
  border-color: #5B163A;
  box-shadow: 0 10px 25px rgba(91, 22, 58, 0.1);
}

.more-card-img-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 3/4;
  overflow: hidden;
  background: #F8F6F2;
}

.more-card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.more-product-card:hover .more-card-img {
  transform: scale(1.06);
}

.more-card-wishlist-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 2;
}

.more-card-wishlist-btn:hover {
  transform: scale(1.1);
  background: #ffffff;
}

.more-card-wishlist-btn.is-active {
  background: #FEE2E2;
  border-color: #FCA5A5;
}

.more-card-discount-badge {
  position: absolute;
  bottom: 10px;
  left: 10px;
  background: #5B163A;
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 4px;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.more-card-tag-badge {
  position: absolute;
  bottom: 10px;
  left: 10px;
  background: #D4AF37;
  color: #1a0f14;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 4px;
}

.more-card-info {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  flex: 1;
}

.more-card-category {
  font-size: 0.72rem;
  color: #8A6418;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.more-card-title {
  font-family: 'Playfair Display', serif;
  font-size: 0.98rem;
  font-weight: 700;
  color: #1E293B;
  margin: 0;
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.more-card-rating {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
  color: #F59E0B;
  font-weight: 700;
}

.rating-count {
  color: #94A3B8;
  font-weight: 400;
}

.more-card-price-row {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-top: 0.25rem;
}

.more-price-current {
  font-size: 1.05rem;
  font-weight: 800;
  color: #5B163A;
}

.more-price-old {
  font-size: 0.8rem;
  color: #94A3B8;
  text-decoration: line-through;
}

.btn-more-card-action {
  margin-top: auto;
  padding-top: 0.5rem;
  border: none;
  background: transparent;
  color: #5B163A;
  font-size: 0.82rem;
  font-weight: 700;
  text-align: left;
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: gap 0.2s ease;
}

.more-product-card:hover .btn-more-card-action {
  gap: 8px;
  color: #8A6418;
}
</style>
