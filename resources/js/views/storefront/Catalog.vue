<template>
  <div class="catalog-layout">
    <!-- Mobile Sticky Filter & Sort Bar -->
    <div class="mobile-filter-bar mobile-only">
      <button @click="showMobileFiltersSheet = true" class="filter-bar-btn">
        <Sliders :size="16" />
        <span>Filter</span>
      </button>
      <div class="filter-bar-divider"></div>
      <button @click="showMobileSortSheet = true" class="filter-bar-btn">
        <ArrowUpDown :size="16" />
        <span>Sort</span>
      </button>
      <div class="filter-bar-divider"></div>
      <button @click="scrollToCategories" class="filter-bar-btn">
        <FolderOpen :size="16" />
        <span>Categories</span>
      </button>
    </div>

    <div class="catalog-container">
      <!-- Desktop Sidebar: Filters -->
      <aside class="desktop-sidebar desktop-only glass-panel">
        <div class="sidebar-header">
          <h3 class="sidebar-title">Filters</h3>
          <button @click="resetFilters" class="clear-filters-btn">Clear All</button>
        </div>

        <!-- Keyword Search -->
        <div class="filter-card">
          <h4 class="filter-card-title">Keyword Search</h4>
          <div class="search-input-wrapper">
            <input 
              type="text" 
              v-model="filters.search" 
              @input="debounceSearch"
              placeholder="Search - Trending Blouses, ReadyMade Blouses, Stretchable Blouses..." 
              class="form-input search-field" 
            />
            <Search :size="16" class="search-field-icon" />
          </div>
        </div>

        <!-- Collapsible Filter Sections -->
        <!-- Category Section -->
        <div class="filter-card">
          <div class="filter-card-header" @click="toggleSection('category')">
            <span class="filter-section-name">Category</span>
            <ChevronUp v-if="openSections.category" :size="16" />
            <ChevronDown v-else :size="16" />
          </div>
          <div v-show="openSections.category" class="filter-card-body">
            <div class="filter-options-list">
              <label class="custom-radio-option">
                <input type="radio" value="" v-model="filters.category_id" @change="fetchProducts(1)" />
                <span class="radio-indicator"></span>
                <span class="option-label" style="font-weight: 600;">All Categories</span>
              </label>
              <label class="custom-radio-option" v-for="cat in categories" :key="cat.id">
                <input type="radio" :value="cat.id" v-model="filters.category_id" @change="fetchProducts(1)" />
                <span class="radio-indicator"></span>
                <span class="option-label">{{ cat.name }}</span>
              </label>
            </div>
          </div>
        </div>




        <!-- Price Range Section -->
        <div class="filter-card">
          <div class="filter-card-header" @click="toggleSection('price')">
            <span class="filter-section-name">Price Range</span>
            <ChevronUp v-if="openSections.price" :size="16" />
            <ChevronDown v-else :size="16" />
          </div>
          <div v-show="openSections.price" class="filter-card-body">
            <div class="price-range-wrap">
              <!-- Live price display -->
              <div class="price-range-display">
                <span class="price-range-val">₹{{ priceRangeMin }}</span>
                <span class="price-range-sep">—</span>
                <span class="price-range-val">₹{{ priceRangeMax }}</span>
              </div>
              <!-- Dual Range Slider Track -->
              <div class="dual-range-track-wrap">
                <div
                  class="dual-range-fill"
                  :style="{
                    left: ((priceRangeMin - PRICE_MIN) / (PRICE_MAX - PRICE_MIN) * 100) + '%',
                    width: ((priceRangeMax - priceRangeMin) / (PRICE_MAX - PRICE_MIN) * 100) + '%'
                  }"
                ></div>
                <!-- Min thumb -->
                <input
                  type="range"
                  class="dual-range-input dual-range-min"
                  :min="PRICE_MIN"
                  :max="PRICE_MAX"
                  :step="PRICE_STEP"
                  v-model.number="priceRangeMin"
                  @input="onPriceRangeChange"
                />
                <!-- Max thumb -->
                <input
                  type="range"
                  class="dual-range-input dual-range-max"
                  :min="PRICE_MIN"
                  :max="PRICE_MAX"
                  :step="PRICE_STEP"
                  v-model.number="priceRangeMax"
                  @input="onPriceRangeChange"
                />
              </div>
              <!-- Min/Max labels -->
              <div class="price-range-labels">
                <span>₹{{ PRICE_MIN }}</span>
                <span>₹{{ PRICE_MAX.toLocaleString() }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Size Section (Mock) -->
        <div class="filter-card">
          <div class="filter-card-header" @click="toggleSection('size')">
            <span class="filter-section-name">Size</span>
            <ChevronUp v-if="openSections.size" :size="16" />
            <ChevronDown v-else :size="16" />
          </div>
          <div v-show="openSections.size" class="filter-card-body">
            <div class="size-pill-grid">
              <span 
                v-for="s in ['XS', 'S', 'M', 'L', 'XL', 'XXL']" 
                :key="s" 
                class="size-pill" 
                :class="{ active: mockFilters.size === s }" 
                @click="toggleMockSize(s)"
              >
                {{ s }}
              </span>
            </div>
          </div>
        </div>

        <!-- Color Section (Mock) -->
        <div class="filter-card">
          <div class="filter-card-header" @click="toggleSection('color')">
            <span class="filter-section-name">Color</span>
            <ChevronUp v-if="openSections.color" :size="16" />
            <ChevronDown v-else :size="16" />
          </div>
          <div v-show="openSections.color" class="filter-card-body">
            <div class="color-dot-grid">
              <span 
                v-for="c in ['Maroon', 'Gold', 'Charcoal', 'Emerald', 'Cream', 'Plum']" 
                :key="c" 
                class="color-dot-wrap" 
                :class="{ active: mockFilters.color === c }" 
                @click="toggleMockColor(c)"
                :title="c"
              >
                <span class="color-dot" :style="{ backgroundColor: getColorCode(c) }"></span>
              </span>
            </div>
          </div>
        </div>

        <!-- Fabric Section (Mock) -->
        <div class="filter-card">
          <div class="filter-card-header" @click="toggleSection('fabric')">
            <span class="filter-section-name">Fabric</span>
            <ChevronUp v-if="openSections.fabric" :size="16" />
            <ChevronDown v-else :size="16" />
          </div>
          <div v-show="openSections.fabric" class="filter-card-body">
            <div class="filter-options-list">
              <label class="custom-checkbox-option" v-for="f in ['Kora Silk', 'Cotton Lycra', 'Organza', 'Pure Cotton', 'Georgette']" :key="f">
                <input type="checkbox" :value="f" v-model="mockFilters.fabric" />
                <span class="checkbox-indicator"></span>
                <span class="option-label">{{ f }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Availability Section (Mock) -->
        <div class="filter-card">
          <div class="filter-card-header" @click="toggleSection('availability')">
            <span class="filter-section-name">Availability</span>
            <ChevronUp v-if="openSections.availability" :size="16" />
            <ChevronDown v-else :size="16" />
          </div>
          <div v-show="openSections.availability" class="filter-card-body">
            <label class="custom-checkbox-option">
              <input type="checkbox" v-model="mockFilters.inStockOnly" />
              <span class="checkbox-indicator"></span>
              <span class="option-label">In Stock Only</span>
            </label>
          </div>
        </div>
      </aside>

      <!-- Main Content: Catalog Grid -->
      <div class="catalog-main-content">
        <!-- Subcategories Circle Carousel -->
        <div id="subcategories-section" v-if="selectedCategoryWithChildren" class="subcategories-carousel-container">
          <h3 class="section-title-premium">Shop by Subcategory</h3>
          <div class="subcategories-scroll">
            <!-- Parent "All" Option -->
            <div 
              @click="selectSubcategory(selectedCategoryWithChildren.id)"
              class="subcategory-bubble-item"
            >
              <div 
                class="bubble-image-wrap"
                :class="{ active: filters.category_id == selectedCategoryWithChildren.id }"
              >
                <div class="bubble-icon-all">🛍️</div>
              </div>
              <span class="bubble-label" :class="{ active: filters.category_id == selectedCategoryWithChildren.id }">All</span>
            </div>

            <!-- Children Options -->
            <div 
              v-for="sub in selectedCategoryWithChildren.children" 
              :key="sub.id"
              @click="selectSubcategory(sub.id)"
              class="subcategory-bubble-item"
            >
              <div 
                class="bubble-image-wrap"
                :class="{ active: filters.category_id == sub.id }"
              >
                <img 
                  :src="sub.image || '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp'" 
                  class="bubble-image" 
                  alt="subcategory"
                />
              </div>
              <span class="bubble-label" :class="{ active: filters.category_id == sub.id }">{{ sub.name }}</span>
            </div>
          </div>
        </div>



        <!-- Products List Header Controls -->
        <div class="catalog-grid-header">
          <span class="products-count-label">
            Showing <strong>{{ totalProducts }}</strong> premium styles
          </span>

          <!-- Sort Dropdown (Desktop Only) -->
          <div class="sort-dropdown-desktop desktop-only">
            <span class="sort-label-text">Sort By:</span>
            <select v-model="filters.sort_by" @change="fetchProducts(1)" class="form-input compact-select">
              <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="skeleton-grid-container">
          <div v-for="i in 8" :key="i" class="skeleton-card">
            <div class="skeleton-image"></div>
            <div class="skeleton-text line-1"></div>
            <div class="skeleton-text line-2"></div>
            <div class="skeleton-text line-3"></div>
          </div>
        </div>

        <!-- Grid -->
        <div v-else>
          <div class="products-grid-redesign">
            <div 
              v-for="product in products" 
              :key="product.id" 
              class="premium-product-card"
              @click="routeToProduct(product.uuid)"
            >
              <!-- Card Top Section (Images & Badges) -->
              <div class="card-image-wrap">
                <img 
                  v-protect-image
                  :src="getPrimaryImage(product)" 
                  class="card-image" 
                  alt="product cover" 
                  loading="lazy"
                />

                <!-- Badges -->
                <div class="card-badge-top-left" v-if="product.mrp > product.selling_price">
                  {{ Math.round(((product.mrp - product.selling_price) / product.mrp) * 100) }}% OFF
                </div>
                <div class="card-badge-top-left best-seller" v-else-if="product.avg_rating >= 4.5">
                  Best Seller
                </div>

                <!-- Wishlist Heart Trigger -->
                <button 
                  class="card-wishlist-btn" 
                  @click.stop="toggleWishlist(product)"
                  :aria-label="isInWishlist(product.id) ? 'Remove from wishlist' : 'Add to wishlist'"
                >
                  <Heart :size="18" :class="{ filled: isInWishlist(product.id) }" />
                </button>

                <!-- Actions Overlay Panel (Desktop Only) -->
                <div class="desktop-hover-overlay desktop-only" @click.stop>
                  <span class="overlay-sizes-title">SELECT SIZE</span>
                  <div class="overlay-sizes-row">
                    <button 
                      v-for="v in product.variants.filter(v => v.stock_quantity > 0)" 
                      :key="v.id"
                      class="overlay-size-btn"
                      @click="addVariantToCart(product, v)"
                      :title="`Add ${v.size || 'OS'} to Cart`"
                    >
                      {{ v.size || 'OS' }}
                    </button>
                    <div v-if="!product.variants || product.variants.filter(v => v.stock_quantity > 0).length === 0" class="overlay-no-variants">
                      Sold Out
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Card Details Section -->
              <div class="card-details-wrap">
                <h4 class="card-product-title">{{ product.name }}</h4>
                
                <!-- Included Sizes List -->
                <div class="card-sizes-info" v-if="product.variants && product.variants.some(v => v.stock_quantity > 0)">
                  <span class="sizes-info-label">Sizes:</span>
                  <span class="sizes-info-list">
                    {{ product.variants.filter(v => v.stock_quantity > 0).map(v => v.size || 'OS').filter((v, i, self) => self.indexOf(v) === i).join(', ') }}
                  </span>
                </div>

                <div class="card-price-row">
                  <div class="price-split-wrap">
                    <span class="card-selling-price">₹{{ product.selling_price }}</span>
                    <span v-if="product.mrp > product.selling_price" class="card-mrp-price">
                      ₹{{ product.mrp }}
                    </span>
                  </div>
                  <!-- Mobile Direct Quick Add CTA -->
                  <button 
                    class="mobile-only mobile-quick-add-btn" 
                    @click.stop="openMobileSizeSelector(product)"
                    title="Add directly to cart"
                  >
                    <Plus :size="16" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="products.length === 0" class="glass-panel empty-catalog-panel">
            <span class="empty-icon">📂</span>
            <p class="empty-message">No matching luxury styles found. Please adjust your filters or search keywords.</p>
            <button @click="resetFilters" class="btn btn--primary">Reset Filters</button>
          </div>

          <!-- Pagination -->
          <div v-if="lastPage > 1" class="catalog-pagination-wrap">
            <button 
              class="btn btn--secondary btn-arrow-nav" 
              :disabled="currentPage === 1"
              @click="fetchProducts(currentPage - 1)"
            >
              Prev
            </button>
            <span class="pagination-indicator-text">
              Page {{ currentPage }} of {{ lastPage }}
            </span>
            <button 
              class="btn btn--secondary btn-arrow-nav" 
              :disabled="currentPage === lastPage"
              @click="fetchProducts(currentPage + 1)"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Filter Bottom Sheet -->
    <div v-if="showMobileFiltersSheet" class="bottom-sheet-overlay" @click="showMobileFiltersSheet = false">
      <div class="bottom-sheet-panel" @click.stop>
        <div class="bottom-sheet-header">
          <h3 class="bottom-sheet-title">Refine Collection</h3>
          <button class="bottom-sheet-close-btn" @click="showMobileFiltersSheet = false">✕</button>
        </div>
        <div class="bottom-sheet-body">
          <!-- Collapsible Sections -->
          <!-- Category Section -->
          <div class="sheet-filter-group">
            <div class="sheet-group-header" @click="toggleSection('category')">
              <span>Category</span>
              <span>{{ openSections.category ? '−' : '+' }}</span>
            </div>
            <div v-show="openSections.category" class="sheet-group-body">
              <label class="sheet-radio-label" v-for="cat in categories" :key="cat.id">
                <input type="radio" :value="cat.id" v-model="filters.category_id" @change="fetchProducts(1)" />
                <span class="option-title-text">{{ cat.name }}</span>
              </label>
            </div>
          </div>


          <!-- Price Section -->
          <div class="sheet-filter-group">
            <div class="sheet-group-header" @click="toggleSection('price')">
              <span>Price Range</span>
              <span>{{ openSections.price ? '−' : '+' }}</span>
            </div>
            <div v-show="openSections.price" class="sheet-group-body">
              <div class="price-range-wrap" style="padding: 8px 0;">
                <div class="price-range-display">
                  <span class="price-range-val">₹{{ priceRangeMin }}</span>
                  <span class="price-range-sep">—</span>
                  <span class="price-range-val">₹{{ priceRangeMax }}</span>
                </div>
                <div class="dual-range-track-wrap">
                  <div
                    class="dual-range-fill"
                    :style="{
                      left: ((priceRangeMin - PRICE_MIN) / (PRICE_MAX - PRICE_MIN) * 100) + '%',
                      width: ((priceRangeMax - priceRangeMin) / (PRICE_MAX - PRICE_MIN) * 100) + '%'
                    }"
                  ></div>
                  <input
                    type="range"
                    class="dual-range-input dual-range-min"
                    :min="PRICE_MIN"
                    :max="PRICE_MAX"
                    :step="PRICE_STEP"
                    v-model.number="priceRangeMin"
                    @input="onPriceRangeChange"
                  />
                  <input
                    type="range"
                    class="dual-range-input dual-range-max"
                    :min="PRICE_MIN"
                    :max="PRICE_MAX"
                    :step="PRICE_STEP"
                    v-model.number="priceRangeMax"
                    @input="onPriceRangeChange"
                  />
                </div>
                <div class="price-range-labels">
                  <span>₹{{ PRICE_MIN }}</span>
                  <span>₹{{ PRICE_MAX.toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Size Section (Mock) -->
          <div class="sheet-filter-group">
            <div class="sheet-group-header" @click="toggleSection('size')">
              <span>Size</span>
              <span>{{ openSections.size ? '−' : '+' }}</span>
            </div>
            <div v-show="openSections.size" class="sheet-group-body">
              <div class="size-pill-grid" style="padding: 10px 0;">
                <span 
                  v-for="s in ['XS', 'S', 'M', 'L', 'XL', 'XXL']" 
                  :key="s" 
                  class="size-pill" 
                  :class="{ active: mockFilters.size === s }" 
                  @click="toggleMockSize(s)"
                >
                  {{ s }}
                </span>
              </div>
            </div>
          </div>

          <!-- Color Section (Mock) -->
          <div class="sheet-filter-group">
            <div class="sheet-group-header" @click="toggleSection('color')">
              <span>Color</span>
              <span>{{ openSections.color ? '−' : '+' }}</span>
            </div>
            <div v-show="openSections.color" class="sheet-group-body">
              <div class="color-dot-grid" style="padding: 10px 0;">
                <span 
                  v-for="c in ['Maroon', 'Gold', 'Charcoal', 'Emerald', 'Cream', 'Plum']" 
                  :key="c" 
                  class="color-dot-wrap" 
                  :class="{ active: mockFilters.color === c }" 
                  @click="toggleMockColor(c)"
                  :title="c"
                >
                  <span class="color-dot" :style="{ backgroundColor: getColorCode(c) }"></span>
                </span>
              </div>
            </div>
          </div>

          <!-- Fabric Section (Mock) -->
          <div class="sheet-filter-group">
            <div class="sheet-group-header" @click="toggleSection('fabric')">
              <span>Fabric</span>
              <span>{{ openSections.fabric ? '−' : '+' }}</span>
            </div>
            <div v-show="openSections.fabric" class="sheet-group-body">
              <label class="sheet-checkbox-label" v-for="f in ['Kora Silk', 'Cotton Lycra', 'Organza', 'Pure Cotton', 'Georgette']" :key="f">
                <input type="checkbox" :value="f" v-model="mockFilters.fabric" />
                <span class="option-title-text">{{ f }}</span>
              </label>
            </div>
          </div>

          <!-- Availability Section (Mock) -->
          <div class="sheet-filter-group">
            <div class="sheet-group-header" @click="toggleSection('availability')">
              <span>Availability</span>
              <span>{{ openSections.availability ? '−' : '+' }}</span>
            </div>
            <div v-show="openSections.availability" class="sheet-group-body">
              <label class="sheet-checkbox-label">
                <input type="checkbox" v-model="mockFilters.inStockOnly" />
                <span class="option-title-text">In Stock Only</span>
              </label>
            </div>
          </div>
        </div>
        <div class="bottom-sheet-footer">
          <button class="btn btn--secondary" style="flex: 1; border-radius: 24px; height: 48px;" @click="resetFilters">Clear All</button>
          <button class="btn btn--primary" style="flex: 1; border-radius: 24px; height: 48px;" @click="showMobileFiltersSheet = false">Apply Filters</button>
        </div>
      </div>
    </div>

    <!-- Mobile Sort Bottom Sheet -->
    <div v-if="showMobileSortSheet" class="bottom-sheet-overlay" @click="showMobileSortSheet = false">
      <div class="bottom-sheet-panel" @click.stop>
        <div class="bottom-sheet-header">
          <h3 class="bottom-sheet-title">Sort Collection</h3>
          <button class="bottom-sheet-close-btn" @click="showMobileSortSheet = false">✕</button>
        </div>
        <div class="bottom-sheet-body" style="padding: 1rem 0;">
          <div 
            v-for="opt in sortOptions" 
            :key="opt.value" 
            class="mobile-sort-row"
            :class="{ active: filters.sort_by === opt.value }"
            @click="selectSortOption(opt.value)"
          >
            <span>{{ opt.label }}</span>
            <span v-if="filters.sort_by === opt.value" class="sort-tick">✓</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Size Bottom Sheet -->
    <div v-if="showMobileSizeSheet && sizeSelectProduct" class="bottom-sheet-overlay" @click="showMobileSizeSheet = false">
      <div class="bottom-sheet-panel" @click.stop>
        <div class="bottom-sheet-header">
          <h3 class="bottom-sheet-title">Select Size</h3>
          <button class="bottom-sheet-close-btn" @click="showMobileSizeSheet = false">✕</button>
        </div>
        <div class="bottom-sheet-body" style="padding: 1.5rem 1.25rem;">
          <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 1.25rem;">Select size to add to your cart:</p>
          <div class="size-pill-grid">
            <button 
              v-for="v in sizeSelectProduct.variants.filter(v => v.stock_quantity > 0)" 
              :key="v.id"
              class="size-pill"
              style="width: calc(33.33% - 8px); height: 44px; display: inline-flex; align-items: center; justify-content: center; font-weight: bold;"
              @click="selectMobileSize(v)"
            >
              {{ v.size || 'OS' }}
            </button>
            <div v-if="!sizeSelectProduct.variants || sizeSelectProduct.variants.filter(v => v.stock_quantity > 0).length === 0" style="width: 100%; text-align: center;">
              <span class="overlay-no-variants" style="color: var(--color-text-secondary);">Sold Out</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Premium Quick View Modal -->
    <div v-if="quickViewProduct" class="modal-overlay" @click="quickViewProduct = null">
      <div class="modal-panel" @click.stop>
        <button class="modal-close-btn" @click="quickViewProduct = null">✕</button>
        <div class="modal-grid-layout">
          <div class="modal-gallery-wrap">
            <img v-protect-image :src="getPrimaryImage(quickViewProduct)" alt="product cover" class="modal-preview-image" />
          </div>
          <div class="modal-details-wrap">
            <span class="modal-category-label">{{ quickViewProduct.category?.name }}</span>
            <h3 class="modal-product-title">{{ quickViewProduct.name }}</h3>
            
            <div class="modal-price-row">
              <span class="modal-sell-price">₹{{ quickViewProduct.selling_price }}</span>
              <span v-if="quickViewProduct.mrp > quickViewProduct.selling_price" class="modal-mrp-price">
                MRP ₹{{ quickViewProduct.mrp }}
              </span>
              <span v-if="quickViewProduct.mrp > quickViewProduct.selling_price" class="modal-discount-tag">
                {{ Math.round(((quickViewProduct.mrp - quickViewProduct.selling_price) / quickViewProduct.mrp) * 100) }}% OFF
              </span>
            </div>

            <p class="modal-description-text">
              {{ quickViewProduct.description || 'Premium handpicked South Indian designer wear. Crafted with high-grade fabrics for elegant draping and custom styling.' }}
            </p>

            <div class="modal-actions-group">
              <button @click="addToCartDirect(quickViewProduct)" class="btn btn--primary modal-primary-cta">
                🛒 Add to Cart
              </button>
              <button @click="routeToProduct(quickViewProduct.uuid)" class="btn btn--secondary modal-secondary-cta">
                View Details
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { 
  Sliders, 
  ArrowUpDown, 
  FolderOpen, 
  Search, 
  ChevronDown, 
  ChevronUp, 
  Heart, 
  Eye, 
  ShoppingBag, 
  Scale, 
  Plus 
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const emit = defineEmits(['update-wishlist-count', 'update-cart-count']);

const products = ref([]);
const categories = ref([]);
const loading = ref(true);

const totalProducts = ref(0);
const currentPage = ref(1);
const lastPage = ref(1);

const filters = ref({
  search: '',
  category_id: '',
  category: '',
  occasion: '',
  collection: '',
  price_range: '',
  min_price: '',
  max_price: '',
  sort_by: 'newest',
});

// Price Range Slider Constants
const PRICE_MIN = 0;
const PRICE_MAX = 10000;
const PRICE_STEP = 100;

const priceRangeMin = ref(PRICE_MIN);
const priceRangeMax = ref(PRICE_MAX);

let priceDebounceTimer = null;

const onPriceRangeChange = () => {
  // Prevent thumbs crossing each other
  if (priceRangeMin.value >= priceRangeMax.value) {
    if (priceRangeMin.value >= PRICE_MAX) {
      priceRangeMin.value = PRICE_MAX - PRICE_STEP;
    } else {
      priceRangeMax.value = priceRangeMin.value + PRICE_STEP;
    }
  }
  // Sync to filters and debounce fetch
  filters.value.min_price = priceRangeMin.value === PRICE_MIN ? '' : priceRangeMin.value;
  filters.value.max_price = priceRangeMax.value === PRICE_MAX ? '' : priceRangeMax.value;

  clearTimeout(priceDebounceTimer);
  priceDebounceTimer = setTimeout(() => {
    fetchProducts(1);
  }, 450);
};

// Mock Filters State
const mockFilters = ref({
  size: '',
  color: '',
  fabric: [],
  inStockOnly: false,
});

// Section collapsibility states
const openSections = ref({
  category: true,
  price: false,
  size: false,
  color: false,
  fabric: false,
  availability: false,
});

const toggleSection = (section) => {
  openSections.value[section] = !openSections.value[section];
};

const toggleMockSize = (size) => {
  mockFilters.value.size = mockFilters.value.size === size ? '' : size;
};

const toggleMockColor = (color) => {
  mockFilters.value.color = mockFilters.value.color === color ? '' : color;
};

const getColorCode = (colorName) => {
  const colors = {
    'Maroon': '#6b0c22',
    'Gold': '#d4af37',
    'Charcoal': '#2e2e2e',
    'Emerald': '#046307',
    'Cream': '#f5ecdb',
    'Plum': '#4a0e2e'
  };
  return colors[colorName] || '#cccccc';
};

const showMobileFiltersSheet = ref(false);
const showMobileSortSheet = ref(false);
const quickViewProduct = ref(null);
const activeQuickFilter = ref('');

const sortOptions = [
  { value: 'newest', label: 'New Arrivals' },
  { value: 'popularity', label: 'Popularity' },
  { value: 'price_low_high', label: 'Price: Low to High' },
  { value: 'price_high_low', label: 'Price: High to Low' },
];

const selectSortOption = (value) => {
  filters.value.sort_by = value;
  showMobileSortSheet.value = false;
  fetchProducts(1);
};

const openQuickView = (prod) => {
  quickViewProduct.value = prod;
};

const routeToProduct = (uuid) => {
  router.push(`/products/${uuid}`);
};

const scrollToCategories = () => {
  const el = document.getElementById('subcategories-section');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth' });
  }
};

const applyQuickFilter = (pill) => {
  if (activeQuickFilter.value === pill) {
    activeQuickFilter.value = '';
    filters.value.sort_by = 'newest';
    filters.value.search = '';
  } else {
    activeQuickFilter.value = pill;
    if (pill === 'New Arrival') {
      filters.value.sort_by = 'newest';
      filters.value.search = '';
    } else if (pill === 'Best Seller') {
      filters.value.sort_by = 'popularity';
      filters.value.search = '';
    } else {
      filters.value.search = pill;
    }
  }
  fetchProducts(1);
};

const selectedCategoryWithChildren = computed(() => {
  if (!filters.value.category_id) return null;
  
  const parentCat = categories.value.find(c => c.id == filters.value.category_id);
  if (parentCat && parentCat.children && parentCat.children.length > 0) {
    return parentCat;
  }
  
  for (let parent of categories.value) {
    if (parent.children && parent.children.some(c => c.id == filters.value.category_id)) {
      return parent;
    }
  }
  return null;
});

const selectSubcategory = (subId) => {
  filters.value.category_id = subId;
  fetchProducts(1);
};

let searchTimeout = null;
const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchProducts(1);
  }, 350);
};

const fetchProducts = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: filters.value.search,
      category_id: filters.value.category_id,
      category: filters.value.category,
      occasion: filters.value.occasion,
      collection: filters.value.collection,
      price_range: filters.value.price_range,
      min_price: filters.value.min_price,
      max_price: filters.value.max_price,
      sort_by: filters.value.sort_by === 'popularity' ? 'rating' : filters.value.sort_by,
    };

    const response = await axios.get('/api/storefront/products', { params });
    if (response.data && response.data.success) {
      products.value = response.data.data;
      currentPage.value = response.data.meta.current_page;
      lastPage.value = response.data.meta.last_page;
      totalProducts.value = response.data.meta.total;
    }
  } catch (err) {
    console.error('Failed to load products:', err);
  } finally {
    loading.value = false;
  }
};

const fetchFilterMetadata = async () => {
  try {
    const catRes = await axios.get('/api/storefront/categories?all=1');
    if (catRes.data && catRes.data.success) {
      categories.value = catRes.data.data;
    }
  } catch (err) {
    console.error('Failed to load filter metrics:', err);
  }
};

const resetFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    min_price: '',
    max_price: '',
    sort_by: 'newest',
  };
  mockFilters.value = {
    size: '',
    color: '',
    fabric: [],
    inStockOnly: false,
  };
  tempMinPrice.value = 0;
  tempMaxPrice.value = 15000;
  activeQuickFilter.value = '';
  fetchProducts(1);
};

watch(
  () => route.query,
  (newQuery) => {
    if (newQuery.category_id) filters.value.category_id = newQuery.category_id;
    if (newQuery.category) filters.value.category = newQuery.category;
    if (newQuery.occasion) filters.value.occasion = newQuery.occasion;
    if (newQuery.collection) filters.value.collection = newQuery.collection;
    if (newQuery.price_range) filters.value.price_range = newQuery.price_range;
    if (newQuery.search) filters.value.search = newQuery.search;
    if (newQuery.min_price) {
      filters.value.min_price = Number(newQuery.min_price);
      priceRangeMin.value = Number(newQuery.min_price);
    }
    if (newQuery.max_price) {
      filters.value.max_price = Number(newQuery.max_price);
      priceRangeMax.value = Number(newQuery.max_price);
    }
    fetchProducts(1);
  },
  { immediate: true }
);

const wishlist = ref([]);
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

const toggleWishlist = (product) => {
  const index = wishlist.value.findIndex(item => item.id === product.id);
  if (index >= 0) {
    wishlist.value.splice(index, 1);
  } else {
    wishlist.value.push({
      id: product.id,
      uuid: product.uuid,
      name: product.name,
      selling_price: product.selling_price,
      image: getPrimaryImage(product)
    });
  }
  localStorage.setItem('vibe_wishlist_items', JSON.stringify(wishlist.value));
  emit('update-wishlist-count');
};

const getPrimaryImage = (product) => {
  if (!product.images || product.images.length === 0) {
    const prodId = product.id ? ((product.id - 1) % 23) + 1 : 1;
    return `/storage/products/${prodId}/webp/${prodId}.webp`;
  }
  const primary = product.images.find(img => img.is_primary);
  return primary ? (primary.image_path || primary.url) : (product.images[0].image_path || product.images[0].url);
};

const addVariantToCart = (product, variant) => {
  try {
    const cart = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
    const existingIndex = cart.findIndex(item => item.product_variant_id === variant.id);
    if (existingIndex >= 0) {
      cart[existingIndex].quantity += 1;
      if (cart[existingIndex].quantity > variant.stock_quantity) {
        cart[existingIndex].quantity = variant.stock_quantity;
      }
    } else {
      cart.push({
        product_id: product.id,
        product_uuid: product.uuid,
        product_variant_id: variant.id,
        sku: variant.sku,
        name: product.name,
        size: variant.size,
        color: variant.color,
        image: getPrimaryImage(product),
        selling_price: variant.selling_price || product.selling_price,
        mrp: variant.mrp || product.mrp,
        quantity: 1,
        stock_quantity: variant.stock_quantity,
      });
    }
    localStorage.setItem('vibe_cart_items', JSON.stringify(cart));
    emit('update-cart-count');
    alert(`✓ Added Size ${variant.size || 'OS'} of ${product.name} to your cart!`);
  } catch (e) {
    console.error('Cart operation failed', e);
  }
};

const addToCartDirect = (prod) => {
  if (!prod.variants || prod.variants.length === 0) {
    alert('This product does not have any variants available.');
    return;
  }
  const variant = prod.variants[0];
  addVariantToCart(prod, variant);
};

const showMobileSizeSheet = ref(false);
const sizeSelectProduct = ref(null);

const openMobileSizeSelector = (product) => {
  sizeSelectProduct.value = product;
  showMobileSizeSheet.value = true;
};

const selectMobileSize = (variant) => {
  if (sizeSelectProduct.value && variant) {
    addVariantToCart(sizeSelectProduct.value, variant);
    showMobileSizeSheet.value = false;
  }
};

const compareProduct = (prod) => {
  alert('⚖️ Added ' + prod.name + ' to compare list!');
};

onMounted(() => {
  document.title = "Shop - Maya Sree South Indian Fashion";
  const metaDescription = document.querySelector('meta[name="description"]');
  if (metaDescription) {
    metaDescription.setAttribute("content", "Explore our premium collections of South Indian sarees, designer kurtis, and stretchable ready-made blouses at Maya Sree Fashion.");
  }
  fetchFilterMetadata();
  loadWishlist();
});
</script>

<style scoped>
/* Redesigned Premium E-Commerce Layout */
.catalog-layout {
  display: flex;
  flex-direction: column;
}

.catalog-container {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: var(--spacing-xl);
  align-items: start;
}

/* Sticky Desktop Sidebar Filters */
.desktop-sidebar {
  position: sticky;
  top: 90px;
  max-height: calc(100vh - 120px);
  overflow-y: auto;
  padding: var(--spacing-md);
  border-radius: 16px;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.sidebar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--color-border);
  padding-bottom: 0.5rem;
}

.sidebar-title {
  font-family: var(--font-family-heading);
  font-size: 1.2rem;
  color: var(--color-primary);
  font-weight: 700;
  margin: 0;
}

.clear-filters-btn {
  background: none;
  border: none;
  color: var(--color-text-secondary);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: color 0.2s;
}

.clear-filters-btn:hover {
  color: var(--color-primary);
}

/* Filter Cards */
.filter-card {
  border-bottom: 1px solid var(--color-border);
  padding-bottom: var(--spacing-md);
}

.filter-card:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.filter-card-title {
  font-size: 0.85rem;
  text-transform: uppercase;
  color: var(--color-text-secondary);
  font-weight: 700;
  margin-bottom: var(--spacing-sm);
}

.search-input-wrapper {
  position: relative;
}

.search-field {
  width: 100%;
  padding-right: 32px;
  font-size: 0.85rem;
}

.search-field-icon {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  color: var(--color-text-secondary);
}

.filter-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  padding: var(--spacing-xs) 0;
}

.filter-section-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-text-primary);
}

.filter-card-body {
  margin-top: var(--spacing-sm);
}

.filter-options-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

/* Custom Checkboxes / Radios */
.custom-radio-option, .custom-checkbox-option {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  font-size: 0.85rem;
  color: var(--color-text-secondary);
  padding: 4px 0;
  user-select: none;
}

.custom-radio-option input, .custom-checkbox-option input {
  display: none;
}

.radio-indicator {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 1.5px solid var(--color-border);
  display: inline-block;
  position: relative;
  transition: all 0.2s;
}

.custom-radio-option input:checked ~ .radio-indicator {
  border-color: var(--color-primary);
  background: var(--color-primary);
}

.custom-radio-option input:checked ~ .radio-indicator::after {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #ffffff;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.checkbox-indicator {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 1.5px solid var(--color-border);
  display: inline-block;
  position: relative;
  transition: all 0.2s;
}

.custom-checkbox-option input:checked ~ .checkbox-indicator {
  border-color: var(--color-primary);
  background: var(--color-primary);
}

.custom-checkbox-option input:checked ~ .checkbox-indicator::after {
  content: '✓';
  color: #ffffff;
  font-size: 0.7rem;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-weight: bold;
}

.compact-input {
  font-size: 0.8rem;
  padding: 6px 12px;
}

/* ===== Dual Range Price Slider ===== */
.price-range-wrap {
  padding: 4px 2px 8px;
}

.price-range-display {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 14px;
}

.price-range-val {
  background: #4a0e2e;
  color: #ffffff;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  letter-spacing: 0.5px;
  font-family: 'Poppins', sans-serif;
}

.price-range-sep {
  color: var(--color-text-muted);
  font-size: 0.8rem;
}

.dual-range-track-wrap {
  position: relative;
  height: 6px;
  background: #e2e8f0;
  border-radius: 6px;
  margin: 10px 0 6px;
}

.dual-range-fill {
  position: absolute;
  top: 0;
  height: 100%;
  background: linear-gradient(90deg, #d4af37, #4a0e2e);
  border-radius: 6px;
  pointer-events: none;
}

.dual-range-input {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  pointer-events: auto;
  margin: 0;
}

/* Stack the two thumbs properly */
.dual-range-min { z-index: 3; }
.dual-range-max { z-index: 4; }

/* Show real-looking thumb via a pseudo-overlay  */
.dual-range-track-wrap::before,
.dual-range-track-wrap::after {
  content: '';
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #ffffff;
  border: 2.5px solid #4a0e2e;
  box-shadow: 0 2px 6px rgba(74,14,46,0.2);
  pointer-events: none;
  z-index: 2;
}

.price-range-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.72rem;
  color: var(--color-text-muted);
  margin-top: 10px;
  padding: 0 2px;
}



/* Size Pills */
.size-pill-grid {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.size-pill {
  border: 1px solid var(--color-border);
  padding: 6px 12px;
  font-size: 0.8rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  background-color: var(--color-surface);
  font-weight: 500;
}

.size-pill:hover, .size-pill.active {
  border-color: var(--color-primary);
  background-color: var(--color-primary);
  color: #ffffff;
}

/* Color dots */
.color-dot-grid {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.color-dot-wrap {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 1.5px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.color-dot-wrap.active {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(74, 14, 46, 0.15);
}

.color-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
}

/* Catalog Main Content */
.catalog-main-content {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
}

/* Subcategories Circle Navigation */
.subcategories-carousel-container {
  padding: 0 4px;
}

.section-title-premium {
  font-family: var(--font-family-heading);
  font-size: 1.15rem;
  color: var(--color-primary);
  font-weight: 700;
  margin-bottom: var(--spacing-md);
}

.subcategories-scroll {
  display: flex;
  gap: var(--spacing-md);
  overflow-x: auto;
  padding-bottom: 4px;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.subcategories-scroll::-webkit-scrollbar {
  display: none;
}

.subcategory-bubble-item {
  flex: 0 0 80px;
  text-align: center;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.bubble-image-wrap {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  overflow: hidden;
  border: 2px solid var(--color-border);
  transition: all 0.25s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-surface);
}

.bubble-image-wrap.active {
  border-color: var(--color-primary);
  box-shadow: var(--shadow-sm);
  transform: scale(1.05);
}

.bubble-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.bubble-icon-all {
  font-size: 1.3rem;
}

.bubble-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--color-text-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
}

.bubble-label.active {
  color: var(--color-primary);
  font-weight: 700;
}

/* Quick Filters */
.quick-filters-container {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 4px;
}

.quick-filter-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-secondary);
}

.quick-pills-list {
  display: flex;
  gap: 8px;
}

.quick-pill-btn {
  border: 1px solid var(--color-border);
  background: var(--color-surface);
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 500;
}

.quick-pill-btn:hover, .quick-pill-btn.active {
  border-color: var(--color-primary);
  background: var(--color-primary);
  color: #ffffff;
}

/* Grid Headers */
.catalog-grid-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 4px;
  border-bottom: 1px solid var(--color-border);
  padding-bottom: var(--spacing-sm);
}

.products-count-label {
  font-size: 0.9rem;
  color: var(--color-text-secondary);
}

.sort-dropdown-desktop {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sort-label-text {
  font-size: 0.85rem;
  color: var(--color-text-secondary);
}

.compact-select {
  font-size: 0.85rem;
  padding: 6px 12px;
  border-radius: 20px;
}

/* Skeleton loader */
.skeleton-grid-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--spacing-lg);
}

.skeleton-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 12px;
  border: 1px solid var(--color-border);
}

.skeleton-image {
  aspect-ratio: 4/5;
  background: #f0f0f0;
  border-radius: 12px;
  margin-bottom: 12px;
}

.skeleton-text {
  background: #f0f0f0;
  border-radius: 4px;
  margin-bottom: 8px;
}

.skeleton-text.line-1 { height: 16px; width: 80%; }
.skeleton-text.line-2 { height: 14px; width: 60%; }
.skeleton-text.line-3 { height: 18px; width: 40%; }

/* Redesigned Responsive Grid */
.products-grid-redesign {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--spacing-lg);
}

@media (max-width: 1200px) {
  .products-grid-redesign {
    grid-template-columns: repeat(3, 1fr);
  }
  .skeleton-grid-container {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 900px) {
  .products-grid-redesign {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  .skeleton-grid-container {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
}

/* Redesigned Product Card */
.premium-product-card {
  background: var(--color-surface);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
  border: 1px solid var(--color-border);
  transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.3s ease, border-color 0.3s ease;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.premium-product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 25px rgba(74, 14, 46, 0.06);
  border-color: var(--color-primary);
}

.card-image-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 4/5;
  overflow: hidden;
  background-color: var(--blush-bg);
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.premium-product-card:hover .card-image {
  transform: scale(1.05);
}

/* Badges */
.card-badge-top-left {
  position: absolute;
  top: 10px;
  left: 10px;
  background-color: #25d366; /* Green discount label */
  color: #ffffff;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 4px;
  box-shadow: var(--shadow-sm);
  z-index: 2;
}

.card-badge-top-left.best-seller {
  background-color: var(--color-primary);
}

/* Wishlist Heart */
.card-wishlist-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.95);
  color: var(--color-text-secondary);
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-sm);
  z-index: 3;
  transition: all 0.2s ease;
  cursor: pointer;
}

.card-wishlist-btn:hover {
  transform: scale(1.1);
  color: var(--color-primary);
  background-color: #ffffff;
}

.card-wishlist-btn svg.filled {
  fill: var(--color-primary);
  color: var(--color-primary);
}

/* Desktop Hover Overlay sizes */
.desktop-hover-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(45, 5, 28, 0.9); /* Dark Maroon Transparent */
  backdrop-filter: blur(4px);
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transform: translateY(100%);
  opacity: 0;
  transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1), opacity 0.2s ease;
  z-index: 4;
}

.premium-product-card:hover .desktop-hover-overlay {
  transform: translateY(0);
  opacity: 1;
}

.overlay-sizes-title {
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 1px;
  margin-bottom: 0.5rem;
  text-align: center;
  display: block;
}

.overlay-sizes-row {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: center;
}

.overlay-size-btn {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: #ffffff;
  font-size: 0.75rem;
  font-weight: 700;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.overlay-size-btn:hover {
  background: #ffffff;
  color: var(--color-primary);
  transform: scale(1.1);
}

.overlay-size-btn:disabled {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.3);
  cursor: not-allowed;
  text-decoration: line-through;
}

.overlay-no-variants {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.8rem;
  font-weight: 600;
}

/* Card details styling */
.card-details-wrap {
  padding: 14px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.card-product-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--color-text-primary);
  line-height: 1.35;
  margin: 0 0 8px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 2.45rem;
}

.card-price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
}

.price-split-wrap {
  display: flex;
  align-items: baseline;
  gap: 8px;
}

.card-selling-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-primary);
}

.card-mrp-price {
  font-size: 0.82rem;
  text-decoration: line-through;
  color: var(--color-text-muted);
}

/* Mobile Quick Add */
.mobile-quick-add-btn {
  background-color: var(--color-primary);
  color: #ffffff;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  transition: background-color 0.2s;
}

.mobile-quick-add-btn:active {
  background-color: var(--color-primary-hover);
  transform: scale(0.95);
}

/* Empty Panel */
.empty-catalog-panel {
  text-align: center;
  padding: 5rem 2rem;
  color: var(--color-text-secondary);
}

.empty-icon {
  font-size: 3rem;
  display: block;
  margin-bottom: var(--spacing-sm);
}

.empty-message {
  margin-bottom: var(--spacing-md);
  font-size: 0.95rem;
}

/* Pagination controls */
.catalog-pagination-wrap {
  display: flex;
  justify-content: center;
  gap: var(--spacing-md);
  margin-top: var(--spacing-xl);
}

.btn-arrow-nav {
  height: 40px;
  border-radius: 20px;
  padding: 0 1.25rem;
  font-weight: 600;
}

.pagination-indicator-text {
  align-self: center;
  font-size: 0.9rem;
  color: var(--color-text-secondary);
}

/* --- Mobile Filter Bar & Redesign overrides --- */
.mobile-only {
  display: none;
}

@media (max-width: 900px) {
  .catalog-container {
    grid-template-columns: 1fr;
  }
  
  .desktop-only {
    display: none !important;
  }
  
  .mobile-only {
    display: flex !important;
  }

  .catalog-grid-header {
    border-bottom: none;
    padding-bottom: 0;
  }

  /* Mobile sticky top filter bar */
  .mobile-filter-bar {
    position: sticky;
    top: 103px;
    margin: -1rem -16px 1.5rem -16px;
    padding: 0 8px;
    display: flex;
    background: rgba(255, 252, 247, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--color-border);
    z-index: 99;
    height: 48px;
    align-items: center;
  }

  .filter-bar-btn {
    flex: 1;
    background: none;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: var(--color-text-secondary);
    font-size: 0.85rem;
    font-weight: 600;
    height: 100%;
    cursor: pointer;
  }

  .filter-bar-btn:active {
    background: var(--blush-bg);
  }

  .filter-bar-divider {
    width: 1px;
    height: 20px;
    background-color: var(--color-border);
  }
}

/* Bottom Sheet Filter System for Mobile */
.bottom-sheet-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1050;
  display: flex;
  align-items: flex-end;
  backdrop-filter: blur(2px);
}

.bottom-sheet-panel {
  background: #ffffff;
  width: 100%;
  max-height: 85vh;
  border-top-left-radius: 20px;
  border-top-right-radius: 20px;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
  box-shadow: 0 -8px 30px rgba(0,0,0,0.15);
}

@keyframes slideUp {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

.bottom-sheet-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.bottom-sheet-title {
  font-family: var(--font-family-heading);
  color: var(--color-primary);
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
}

.bottom-sheet-close-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--color-text-secondary);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--color-border);
}

.bottom-sheet-body {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.bottom-sheet-footer {
  padding: 1rem 1.25rem;
  border-top: 1px solid var(--color-border);
  display: flex;
  gap: var(--spacing-md);
  background: #fafafa;
}

.sheet-filter-group {
  border-bottom: 1px solid var(--color-border);
  padding-bottom: var(--spacing-sm);
}

.sheet-group-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  font-weight: 600;
  color: var(--color-text-primary);
  font-size: 0.95rem;
  cursor: pointer;
}

.sheet-group-body {
  margin-top: 8px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sheet-radio-label, .sheet-checkbox-label {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 0;
  font-size: 0.9rem;
  color: var(--color-text-secondary);
}

.option-title-text {
  user-select: none;
}

/* Mobile Sort row list */
.mobile-sort-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 1.5rem;
  font-size: 0.95rem;
  color: var(--color-text-primary);
  border-bottom: 1px solid #f9f9f9;
  cursor: pointer;
}

.mobile-sort-row.active {
  color: var(--color-primary);
  font-weight: 700;
  background-color: var(--blush-bg);
}

.sort-tick {
  font-weight: 700;
}

/* Premium Quick View Modal Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.modal-panel {
  background: #ffffff;
  width: 100%;
  max-width: 800px;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  position: relative;
  overflow: hidden;
  animation: fadeInModal 0.3s ease-out forwards;
}

@keyframes fadeInModal {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.modal-close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: #ffffff;
  border: 1px solid var(--color-border);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  color: var(--color-text-secondary);
}

.modal-grid-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
}

@media (max-width: 768px) {
  .modal-grid-layout {
    grid-template-columns: 1fr;
  }
  .modal-overlay {
    padding: 1rem;
  }
}

.modal-gallery-wrap {
  aspect-ratio: 4/5;
  overflow: hidden;
  background-color: var(--blush-bg);
}

.modal-preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.modal-details-wrap {
  padding: var(--spacing-xl);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
  justify-content: center;
}

.modal-category-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 1px;
  color: var(--color-secondary);
}

.modal-product-title {
  font-family: var(--font-family-heading);
  font-size: 1.4rem;
  color: var(--color-primary);
  font-weight: 700;
  margin: 0;
}

.modal-price-row {
  display: flex;
  align-items: baseline;
  gap: 10px;
}

.modal-sell-price {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-primary);
}

.modal-mrp-price {
  font-size: 0.95rem;
  text-decoration: line-through;
  color: var(--color-text-muted);
}

.modal-discount-tag {
  color: #25d366;
  font-weight: 700;
  font-size: 0.85rem;
}

.modal-description-text {
  font-size: 0.88rem;
  color: var(--color-text-secondary);
  line-height: 1.6;
  margin-top: 10px;
}

.modal-actions-group {
  display: flex;
  gap: 12px;
  margin-top: var(--spacing-md);
}

.modal-primary-cta {
  flex: 1.5;
  height: 48px;
  border-radius: 24px;
  justify-content: center;
}

.modal-secondary-cta {
  flex: 1;
  height: 48px;
  border-radius: 24px;
  justify-content: center;
  align-items: center;
  display: inline-flex;
}

/* Card sizes info styles */
.card-sizes-info {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  margin-bottom: var(--spacing-sm);
  display: flex;
  gap: 4px;
  align-items: center;
  line-height: 1.2;
}

.sizes-info-label {
  font-weight: 600;
  color: var(--color-text-secondary);
}

.sizes-info-list {
  font-weight: 500;
}

/* Trendy Price Range Slider Styles */
.price-slider-wrapper {
  padding: 12px 4px 4px 4px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

.slider-track-container {
  position: relative;
  height: 6px;
  background: #E6DED5; /* Border line color */
  border-radius: 999px;
  margin: 10px 0;
}

.slider-track {
  position: absolute;
  height: 100%;
  background: #5B163A; /* Deep maroon */
  border-radius: 999px;
}

.slider-input {
  position: absolute;
  width: 100%;
  height: 6px;
  top: -1px;
  left: 0;
  background: none;
  pointer-events: none;
  -webkit-appearance: none;
  appearance: none;
  margin: 0;
}

/* Chrome, Safari, Opera, Edge */
.slider-input::-webkit-slider-thumb {
  pointer-events: auto;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #ffffff;
  border: 2.5px solid #5B163A; /* Maroon border */
  cursor: pointer;
  -webkit-appearance: none;
  appearance: none;
  box-shadow: 0 3px 8px rgba(91, 22, 58, 0.2);
  transition: transform 0.15s ease, background-color 0.15s ease;
  z-index: 10;
  position: relative;
}

.slider-input::-webkit-slider-thumb:hover {
  transform: scale(1.15);
  background-color: #FFFDF9;
}

.slider-input::-webkit-slider-thumb:active {
  transform: scale(1.25);
  background-color: #5B163A;
}

/* Firefox */
.slider-input::-moz-range-thumb {
  pointer-events: auto;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #ffffff;
  border: 2.5px solid #5B163A;
  cursor: pointer;
  box-shadow: 0 3px 8px rgba(91, 22, 58, 0.2);
  transition: transform 0.15s ease, background-color 0.15s ease;
  z-index: 10;
}

.slider-input::-moz-range-thumb:hover {
  transform: scale(1.15);
  background-color: #FFFDF9;
}

.slider-input::-moz-range-thumb:active {
  transform: scale(1.25);
  background-color: #5B163A;
}

.price-range-labels {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #2C2C2C; /* Dark charcoal */
}

.price-label-badge {
  background-color: #FCFAF7; /* Soft cream */
  border: 1px solid #E6DED5;
  padding: 4px 10px;
  border-radius: 8px;
  font-family: monospace;
}
</style>
