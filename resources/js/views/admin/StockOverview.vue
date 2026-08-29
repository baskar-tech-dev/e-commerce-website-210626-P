<template>
  <div class="stock-overview-page">
    <!-- Header -->
    <div class="admin-page__header">
      <div class="admin-page__title-section">
        <h1 class="admin-page__title">Stock Overview</h1>
        <span class="admin-page__subtitle">Real-time overview of your product inventory and stock levels.</span>
      </div>
      <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <button 
          type="button" 
          class="btn btn--secondary btn--sm" 
          @click="resetFilters"
          style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;"
        >
          <RotateCcw :size="16" />
          <span>Reset Filters</span>
        </button>
      </div>
    </div>

    <!-- Alert Notifications -->
    <div v-if="successMsg" class="badge badge--success" style="margin-bottom: 1.25rem; padding: 0.85rem 1.25rem; width: 100%; border-radius: 8px; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
      <span>✓ {{ successMsg }}</span>
      <button type="button" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.1rem;" @click="successMsg = null">✕</button>
    </div>

    <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1.25rem; padding: 0.85rem 1.25rem; width: 100%; border-radius: 8px; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
      <span>⚠️ {{ errorMsg }}</span>
      <button type="button" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.1rem;" @click="errorMsg = null">✕</button>
    </div>

    <!-- 6 KPI Summary Cards Grid -->
    <div class="kpi-cards-grid">
      <!-- 1. Total Products -->
      <div class="kpi-card">
        <div class="kpi-icon-wrap kpi-icon--blue">
          <Package :size="22" />
        </div>
        <div class="kpi-info">
          <div class="kpi-label">Total Products</div>
          <div class="kpi-value">{{ formatNumber(stats.total_products) }}</div>
          <div class="kpi-subtext">Active Products</div>
        </div>
      </div>

      <!-- 2. Total Stock Qty -->
      <div class="kpi-card">
        <div class="kpi-icon-wrap kpi-icon--cyan">
          <Boxes :size="22" />
        </div>
        <div class="kpi-info">
          <div class="kpi-label">Total Stock Qty</div>
          <div class="kpi-value">{{ formatNumber(stats.total_stock_qty) }}</div>
          <div class="kpi-subtext">On Hand</div>
        </div>
      </div>

      <!-- 3. Total Order Qty -->
      <div class="kpi-card">
        <div class="kpi-icon-wrap kpi-icon--orange">
          <ShoppingCart :size="22" />
        </div>
        <div class="kpi-info">
          <div class="kpi-label">Total Order Qty</div>
          <div class="kpi-value">{{ formatNumber(stats.total_order_qty) }}</div>
          <div class="kpi-subtext">In All Orders</div>
        </div>
      </div>

      <!-- 4. Total Available Qty -->
      <div class="kpi-card">
        <div class="kpi-icon-wrap kpi-icon--green">
          <ClipboardCheck :size="22" />
        </div>
        <div class="kpi-info">
          <div class="kpi-label">Total Available Qty</div>
          <div class="kpi-value">{{ formatNumber(stats.total_available_qty) }}</div>
          <div class="kpi-subtext">Ready to Ship</div>
        </div>
      </div>

      <!-- 5. Low Stock Items -->
      <div class="kpi-card">
        <div class="kpi-icon-wrap kpi-icon--amber">
          <AlertTriangle :size="22" />
        </div>
        <div class="kpi-info">
          <div class="kpi-label">Low Stock Items</div>
          <div class="kpi-value" style="color: #d97706;">{{ formatNumber(stats.low_stock_items) }}</div>
          <div class="kpi-subtext">Need Attention</div>
        </div>
      </div>

      <!-- 6. Out of Stock Items -->
      <div class="kpi-card">
        <div class="kpi-icon-wrap kpi-icon--red">
          <Ban :size="22" />
        </div>
        <div class="kpi-info">
          <div class="kpi-label">Out of Stock Items</div>
          <div class="kpi-value" style="color: #dc2626;">{{ formatNumber(stats.out_of_stock_items) }}</div>
          <div class="kpi-subtext">Out of Stock</div>
        </div>
      </div>
    </div>

    <!-- Search & Filter Controls Toolbar -->
    <div class="glass-panel filter-toolbar">
      <div class="filter-toolbar__left">
        <!-- Search Input -->
        <div class="search-input-wrap">
          <Search :size="16" class="search-icon" />
          <input 
            type="text" 
            v-model="filters.search" 
            placeholder="Search by product name, SKU, category..." 
            class="search-input"
            @input="debouncedSearch"
          />
          <button v-if="filters.search" @click="clearSearch" class="clear-btn">✕</button>
        </div>

        <!-- Category Dropdown -->
        <div class="select-wrap">
          <select v-model="filters.category_id" @change="applyFilters" class="filter-select">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <!-- Status Dropdown -->
        <div class="select-wrap">
          <select v-model="filters.status" @change="applyFilters" class="filter-select">
            <option value="all">All Status</option>
            <option value="in_stock">In Stock</option>
            <option value="low_stock">Low Stock</option>
            <option value="out_of_stock">Out of Stock</option>
          </select>
        </div>
      </div>

      <div class="filter-toolbar__right">
        <!-- Sort by Dropdown -->
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <span style="font-size: 0.85rem; color: var(--color-text-secondary); white-space: nowrap; font-weight: 500;">Sort by:</span>
          <select v-model="filters.sort_by" @change="applyFilters" class="filter-select" style="min-width: 170px;">
            <option value="name_asc">Product Name (A-Z)</option>
            <option value="name_desc">Product Name (Z-A)</option>
            <option value="stock_high_low">Stock Qty (High to Low)</option>
            <option value="stock_low_high">Stock Qty (Low to High)</option>
            <option value="order_high_low">Order Qty (High to Low)</option>
            <option value="recent">Recently Updated</option>
          </select>
        </div>

        <!-- View Switcher -->
        <div class="view-switcher">
          <button 
            type="button" 
            :class="['view-btn', { 'view-btn--active': viewMode === 'grid' }]" 
            @click="viewMode = 'grid'" 
            title="Grid View"
          >
            <LayoutGrid :size="17" />
          </button>
          <button 
            type="button" 
            :class="['view-btn', { 'view-btn--active': viewMode === 'table' }]" 
            @click="viewMode = 'table'" 
            title="Table View"
          >
            <List :size="17" />
          </button>
        </div>
      </div>
    </div>

    <!-- Showing Result Counter -->
    <div class="results-count-bar">
      <span>
        Showing <strong>{{ pagination.total > 0 ? (pagination.current_page - 1) * pagination.per_page + 1 : 0 }}</strong> to <strong>{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</strong> of <strong>{{ pagination.total }}</strong> products
      </span>
      <span v-if="loading" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 600;">
        ⏳ Loading inventory...
      </span>
    </div>

    <!-- 1. GRID VIEW (Visual Cards) -->
    <div v-if="viewMode === 'grid'">
      <div v-if="products.length > 0" class="products-grid">
        <div v-for="product in products" :key="product.id" class="product-stock-card">
          <!-- Top Section: Image & Meta -->
          <div class="card-header-row">
            <div class="product-thumb-box">
              <img 
                v-if="product.primary_image_url" 
                :src="product.primary_image_url" 
                :alt="product.name"
                class="product-thumb-img"
                loading="lazy"
              />
              <div v-else class="product-thumb-placeholder">
                <Shirt :size="28" style="color: #94a3b8;" />
              </div>
            </div>

            <div class="product-meta-box">
              <h3 class="product-card-title" :title="product.name">
                {{ product.name }}
              </h3>
              <div class="product-sku-tag">
                SKU: <strong>{{ product.sku }}</strong>
              </div>
              <div class="product-cat-tag">
                Category: <strong>{{ product.category_name }}</strong>
              </div>
            </div>
          </div>

          <!-- Stock Metric Matrix Row -->
          <div class="stock-metrics-row">
            <div class="metric-cell">
              <div class="metric-cell-label">Stock Qty</div>
              <div class="metric-cell-val val-stock">{{ product.stock_qty }}</div>
            </div>
            <div class="metric-cell">
              <div class="metric-cell-label">Order Qty</div>
              <div class="metric-cell-val val-order">{{ product.order_qty }}</div>
            </div>
            <div class="metric-cell">
              <div class="metric-cell-label">Avail Qty</div>
              <div class="metric-cell-val val-avail">{{ product.avail_qty }}</div>
            </div>
            <div class="metric-cell metric-cell--status">
              <div class="metric-cell-label">Status</div>
              <span :class="['status-pill', getStatusClass(product.status)]">
                {{ product.status_label }}
              </span>
            </div>
          </div>

          <!-- Card Footer -->
          <div class="card-footer-row">
            <div class="updated-time-text">
              Updated: {{ product.updated_at_formatted }}
            </div>
            <div style="display: flex; gap: 0.35rem; align-items: center;">
              <button 
                type="button" 
                class="btn-adjust-stock"
                @click="openStockAdjustModal(product)"
                title="Add / Reduce Stock Color & Size Wise"
              >
                <SlidersHorizontal :size="13" />
                <span>Adjust Stock</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!loading" class="glass-panel empty-state-box">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📦</div>
        <h3>No Products Found</h3>
        <p>No inventory items match your selected filters. Try searching with a different keyword or resetting filters.</p>
        <button class="btn btn--secondary btn--sm" @click="resetFilters" style="margin-top: 1rem;">Reset Filters</button>
      </div>
    </div>

    <!-- 2. TABLE VIEW (Alternative Table Mode) -->
    <div v-else class="glass-panel" style="overflow: hidden;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Primary SKU</th>
            <th style="text-align: right;">Stock Qty</th>
            <th style="text-align: right;">Order Qty</th>
            <th style="text-align: right;">Avail Qty</th>
            <th>Status</th>
            <th>Last Updated</th>
            <th style="text-align: right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 44px; height: 44px; border-radius: 6px; overflow: hidden; background: #f8fafc; border: 1px solid var(--color-border); flex-shrink: 0;">
                  <img 
                    v-if="product.primary_image_url" 
                    :src="product.primary_image_url" 
                    :alt="product.name" 
                    style="width: 100%; height: 100%; object-fit: cover;" 
                  />
                  <div v-else style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">👗</div>
                </div>
                <div>
                  <div style="font-weight: 600; color: #1e293b;">{{ product.name }}</div>
                  <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ product.variants_count }} Variants</div>
                </div>
              </div>
            </td>
            <td><span class="badge badge--secondary">{{ product.category_name }}</span></td>
            <td><code>{{ product.sku }}</code></td>
            <td style="text-align: right; font-weight: 700; color: #2563eb;">{{ product.stock_qty }}</td>
            <td style="text-align: right; font-weight: 700; color: #ea580c;">{{ product.order_qty }}</td>
            <td style="text-align: right; font-weight: 700; color: #16a34a;">{{ product.avail_qty }}</td>
            <td>
              <span :class="['status-pill', getStatusClass(product.status)]">
                {{ product.status_label }}
              </span>
            </td>
            <td style="font-size: 0.8rem; color: var(--color-text-muted);">{{ product.updated_at_formatted }}</td>
            <td style="text-align: right;">
              <button 
                type="button" 
                class="btn-adjust-stock" 
                @click="openStockAdjustModal(product)"
              >
                <SlidersHorizontal :size="13" />
                <span>Adjust</span>
              </button>
            </td>
          </tr>
          <tr v-if="products.length === 0 && !loading">
            <td colspan="9" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
              No products found matching criteria.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Pagination Controls -->
    <div class="pagination-footer glass-panel">
      <div class="pagination-footer__per-page">
        <span>Show</span>
        <select v-model="filters.per_page" @change="applyPerPage" class="per-page-select">
          <option :value="12">12</option>
          <option :value="24">24</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <span>per page</span>
      </div>

      <div class="pagination-footer__nav">
        <button 
          class="btn-page-nav" 
          :disabled="pagination.current_page === 1" 
          @click="changePage(1)"
          title="First Page"
        >
          |◀
        </button>
        <button 
          class="btn-page-nav" 
          :disabled="pagination.current_page === 1" 
          @click="changePage(pagination.current_page - 1)"
          title="Previous Page"
        >
          ◀
        </button>

        <!-- Page Numbers List -->
        <button 
          v-for="page in visiblePages" 
          :key="page"
          :class="['btn-page-num', { 'btn-page-num--active': page === pagination.current_page }]"
          @click="changePage(page)"
        >
          {{ page }}
        </button>

        <button 
          class="btn-page-nav" 
          :disabled="pagination.current_page === pagination.last_page" 
          @click="changePage(pagination.current_page + 1)"
          title="Next Page"
        >
          ▶
        </button>
        <button 
          class="btn-page-nav" 
          :disabled="pagination.current_page === pagination.last_page" 
          @click="changePage(pagination.last_page)"
          title="Last Page"
        >
          ▶|
        </button>
      </div>
    </div>

    <!-- MODAL: Color & Size Wise Stock Adjustment -->
    <div v-if="showAdjustModal" class="modal-overlay" @click.self="closeStockAdjustModal">
      <div class="modal-container modal-adjust-stock" style="max-width: 780px;">
        <div class="modal-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 44px; height: 44px; border-radius: 8px; overflow: hidden; background: #fff; border: 1px solid var(--color-border); flex-shrink: 0;">
              <img 
                v-if="modalProduct?.primary_image_url" 
                :src="modalProduct.primary_image_url" 
                :alt="modalProduct.name" 
                style="width: 100%; height: 100%; object-fit: cover;" 
              />
              <div v-else style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">👗</div>
            </div>
            <div>
              <h3 class="modal-title" style="font-size: 1.1rem; margin-bottom: 2px;">{{ modalProduct?.name }}</h3>
              <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                SKU: <strong>{{ modalProduct?.sku }}</strong> • Category: <strong>{{ modalProduct?.category_name }}</strong>
              </div>
            </div>
          </div>
          <button class="modal-close" @click="closeStockAdjustModal">&times;</button>
        </div>

        <form @submit.prevent="submitStockAdjust">
          <div class="modal-body" style="max-height: calc(85vh - 160px); overflow-y: auto;">
            
            <!-- Operation Mode & Reason Bar -->
            <div style="background: rgba(74, 14, 46, 0.03); border: 1px solid rgba(74, 14, 46, 0.1); border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div>
                  <label style="font-size: 0.75rem; font-weight: 700; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem;">
                    Adjustment Operation Mode:
                  </label>
                  <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <button 
                      type="button" 
                      :class="['btn btn--sm', modalMode === 'add' ? 'btn--primary' : 'btn--secondary']"
                      @click="setModalMode('add')"
                      style="border-radius: 20px; font-size: 0.8rem; font-weight: 600; padding: 0.3rem 0.85rem;"
                    >
                      🟢 + Inward / Add Stock
                    </button>
                    <button 
                      type="button" 
                      :class="['btn btn--sm', modalMode === 'subtract' ? 'btn--primary' : 'btn--secondary']"
                      @click="setModalMode('subtract')"
                      style="border-radius: 20px; font-size: 0.8rem; font-weight: 600; padding: 0.3rem 0.85rem;"
                    >
                      🔴 - Outward / Reduce Stock
                    </button>
                    <button 
                      type="button" 
                      :class="['btn btn--sm', modalMode === 'set' ? 'btn--primary' : 'btn--secondary']"
                      @click="setModalMode('set')"
                      style="border-radius: 20px; font-size: 0.8rem; font-weight: 600; padding: 0.3rem 0.85rem;"
                    >
                      🔵 = Set Exact Count
                    </button>
                  </div>
                </div>

                <!-- Quick fill preset input -->
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                  <input 
                    type="number" 
                    v-model.number="quickFillQty" 
                    min="0" 
                    placeholder="Qty" 
                    style="width: 60px; height: 32px; text-align: center; border-radius: 6px; border: 1px solid var(--color-border); font-size: 0.85rem;" 
                  />
                  <button 
                    type="button" 
                    class="btn btn--secondary btn--sm" 
                    @click="applyQuickFillAll"
                    style="height: 32px; font-size: 0.75rem; border-radius: 6px;"
                  >
                    ⚡ Fill All
                  </button>
                </div>
              </div>

              <!-- Reason & Notes -->
              <div>
                <label style="font-size: 0.75rem; font-weight: 700; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem;">
                  Reason / Audit Note: *
                </label>
                <div style="display: flex; gap: 0.35rem; margin-bottom: 0.4rem; flex-wrap: wrap;">
                  <button 
                    v-for="r in reasonPresets" 
                    :key="r"
                    type="button"
                    class="preset-reason-pill"
                    @click="modalReason = r"
                  >
                    {{ r }}
                  </button>
                </div>
                <input 
                  type="text" 
                  v-model="modalReason" 
                  class="form-input" 
                  placeholder="e.g. Supplier Batch Arrival, Store Physical Count..." 
                  style="height: 36px; font-size: 0.85rem;" 
                  required 
                />
              </div>
            </div>

            <!-- Color & Size Breakdown Variant Table -->
            <div style="border: 1px solid var(--color-border); border-radius: 8px; overflow: hidden; background: #ffffff;">
              <table class="data-table" style="font-size: 0.85rem;">
                <thead style="background: #f8fafc;">
                  <tr>
                    <th>Color & Size</th>
                    <th>SKU</th>
                    <th style="text-align: right;">Current Stock</th>
                    <th style="text-align: right;">Ordered</th>
                    <th style="text-align: right;">Current Avail</th>
                    <th style="text-align: center; width: 170px;">Adjustment</th>
                    <th style="text-align: right;">Projected Stock</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="variant in modalVariants" :key="variant.id">
                    <td>
                      <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span 
                          class="color-dot-swatch" 
                          :style="{ background: variant.color_code || '#5B163A' }"
                        ></span>
                        <span style="font-weight: 600; color: #1e293b;">{{ variant.color }}</span>
                        <span class="badge badge--secondary" style="font-size: 0.7rem; padding: 2px 6px;">
                          {{ variant.size }}
                        </span>
                      </div>
                    </td>
                    <td><code>{{ variant.sku }}</code></td>
                    <td style="text-align: right; font-weight: 600;">{{ variant.stock_quantity }}</td>
                    <td style="text-align: right; color: #ea580c; font-weight: 600;">{{ variant.order_qty }}</td>
                    <td style="text-align: right; color: #16a34a; font-weight: 600;">{{ variant.avail_qty }}</td>
                    
                    <!-- Stepper Input -->
                    <td style="text-align: center;">
                      <div class="variant-stepper-wrap">
                        <button 
                          type="button" 
                          class="stepper-btn" 
                          @click="stepVariantQty(variant.id, -1)"
                        >
                          -
                        </button>
                        <input 
                          type="number" 
                          v-model.number="variantInputs[variant.id]" 
                          min="0" 
                          class="stepper-input"
                          placeholder="0"
                        />
                        <button 
                          type="button" 
                          class="stepper-btn" 
                          @click="stepVariantQty(variant.id, 1)"
                        >
                          +
                        </button>
                      </div>
                    </td>

                    <!-- Projected Stock -->
                    <td style="text-align: right;">
                      <div style="font-weight: 700; color: var(--color-primary);">
                        {{ getProjectedVariantStock(variant) }}
                      </div>
                      <div style="font-size: 0.7rem;">
                        <span :class="['badge', getVariantDelta(variant) >= 0 ? 'badge--success' : 'badge--danger']" style="padding: 1px 4px;">
                          {{ getVariantDelta(variant) >= 0 ? '+' + getVariantDelta(variant) : getVariantDelta(variant) }}
                        </span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Modal Summary Bar -->
            <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: #f8fafc; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
              <div style="font-size: 0.85rem; color: var(--color-text-secondary);">
                Current Total Stock: <strong>{{ modalProductStockTotal }}</strong>
                <span style="margin: 0 0.4rem;">➔</span>
                Projected New Stock: <strong style="color: var(--color-primary);">{{ modalProductProjectedTotal }}</strong>
              </div>
              <div>
                <span :class="['badge', modalNetDelta >= 0 ? 'badge--success' : 'badge--danger']" style="font-size: 0.8rem; font-weight: 700;">
                  Net Difference: {{ modalNetDelta >= 0 ? '+' + modalNetDelta : modalNetDelta }} units
                </span>
              </div>
            </div>

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn--secondary" @click="closeStockAdjustModal" :disabled="modalSubmitting">
              Cancel
            </button>
            <button type="submit" class="btn btn--primary" :disabled="modalSubmitting || modifiedVariantsCount === 0">
              {{ modalSubmitting ? 'Saving Stock Changes...' : `Save Stock Updates (${modifiedVariantsCount} SKUs)` }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useInventoryStore } from '../../stores/inventory';
import { useCategoryStore } from '../../stores/category';
import { 
  Package, Boxes, ShoppingCart, ClipboardCheck, AlertTriangle, Ban, 
  Search, LayoutGrid, List, SlidersHorizontal, Download, RotateCcw, 
  Shirt 
} from 'lucide-vue-next';

const inventoryStore = useInventoryStore();
const categoryStore = useCategoryStore();

const viewMode = ref('grid'); // 'grid' | 'table'
const successMsg = ref(null);
const errorMsg = ref(null);

// Filters State
const filters = reactive({
  search: '',
  category_id: '',
  status: 'all',
  sort_by: 'name_asc',
  page: 1,
  per_page: 24,
});

// Modal Adjustment State
const showAdjustModal = ref(false);
const modalProduct = ref(null);
const modalMode = ref('add'); // 'add' | 'subtract' | 'set'
const modalReason = ref('Supplier Shipment Arrival');
const modalSubmitting = ref(false);
const quickFillQty = ref(10);
const variantInputs = reactive({});

const reasonPresets = [
  'Supplier Shipment Arrival',
  'Store Physical Audit',
  'Factory Batch Production',
  'Damage / Return Adjustment',
  'Showroom Transfer',
];

const loading = computed(() => inventoryStore.overviewLoading);
const stats = computed(() => inventoryStore.overviewStats);
const products = computed(() => inventoryStore.overviewProducts);
const pagination = computed(() => inventoryStore.overviewPagination);
const categories = computed(() => categoryStore.categories);

// Search debouncing
let searchDebounceTimeout = null;
function debouncedSearch() {
  clearTimeout(searchDebounceTimeout);
  searchDebounceTimeout = setTimeout(() => {
    filters.page = 1;
    loadOverview();
  }, 350);
}

function clearSearch() {
  filters.search = '';
  filters.page = 1;
  loadOverview();
}

function applyFilters() {
  filters.page = 1;
  loadOverview();
}

function applyPerPage() {
  filters.page = 1;
  loadOverview();
}

function changePage(page) {
  if (page < 1 || page > pagination.value.last_page) return;
  filters.page = page;
  loadOverview();
}

function resetFilters() {
  filters.search = '';
  filters.category_id = '';
  filters.status = 'all';
  filters.sort_by = 'name_asc';
  filters.page = 1;
  loadOverview();
}

async function loadOverview() {
  try {
    errorMsg.value = null;
    await inventoryStore.fetchOverview(filters);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || err.message || 'Failed to load stock overview data';
  }
}

function exportCsv() {
  const queryParams = new URLSearchParams(filters).toString();
  window.open(`/api/admin/inventory/export-overview-csv?${queryParams}`, '_blank');
}

function formatNumber(num) {
  if (num === undefined || num === null) return '0';
  return Number(num).toLocaleString('en-IN');
}

function getStatusClass(status) {
  switch (status) {
    case 'in_stock': return 'status-pill--success';
    case 'low_stock': return 'status-pill--warning';
    case 'out_of_stock': return 'status-pill--danger';
    default: return 'status-pill--secondary';
  }
}

const visiblePages = computed(() => {
  const current = pagination.value.current_page || 1;
  const last = pagination.value.last_page || 1;
  const delta = 2;
  const range = [];

  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i);
  }

  if (current - delta > 2) {
    range.unshift('...');
  }
  if (current + delta < last - 1) {
    range.push('...');
  }

  range.unshift(1);
  if (last > 1 && !range.includes(last)) {
    range.push(last);
  }

  return range.filter(p => typeof p === 'number');
});

// Modal Logic
const modalVariants = computed(() => modalProduct.value?.variants || []);

const modalProductStockTotal = computed(() => {
  if (!modalProduct.value) return 0;
  return modalVariants.value.reduce((acc, v) => acc + (v.stock_quantity || 0), 0);
});

function openStockAdjustModal(product) {
  modalProduct.value = product;
  modalMode.value = 'add';
  modalReason.value = 'Supplier Shipment Arrival';
  
  // Clear previous inputs
  for (const key in variantInputs) {
    delete variantInputs[key];
  }

  showAdjustModal.value = true;
}

function closeStockAdjustModal() {
  showAdjustModal.value = false;
  modalProduct.value = null;
}

function setModalMode(mode) {
  modalMode.value = mode;
  // If setting to 'set', preload current quantities
  if (mode === 'set' && modalVariants.value.length > 0) {
    for (const v of modalVariants.value) {
      variantInputs[v.id] = v.stock_quantity;
    }
  }
}

function stepVariantQty(variantId, delta) {
  const current = parseInt(variantInputs[variantId]) || 0;
  variantInputs[variantId] = Math.max(0, current + delta);
}

function applyQuickFillAll() {
  const qty = parseInt(quickFillQty.value) || 0;
  for (const v of modalVariants.value) {
    variantInputs[v.id] = qty;
  }
}

function getProjectedVariantStock(variant) {
  const inputVal = parseInt(variantInputs[variant.id]) || 0;
  const current = variant.stock_quantity;

  if (modalMode.value === 'set') {
    return variantInputs[variant.id] !== undefined && variantInputs[variant.id] !== '' ? inputVal : current;
  } else if (modalMode.value === 'add') {
    return current + inputVal;
  } else if (modalMode.value === 'subtract') {
    return Math.max(0, current - inputVal);
  }
  return current;
}

function getVariantDelta(variant) {
  const projected = getProjectedVariantStock(variant);
  return projected - variant.stock_quantity;
}

const modalProductProjectedTotal = computed(() => {
  return modalVariants.value.reduce((acc, v) => acc + getProjectedVariantStock(v), 0);
});

const modalNetDelta = computed(() => {
  return modalProductProjectedTotal.value - modalProductStockTotal.value;
});

const modifiedVariantsCount = computed(() => {
  let count = 0;
  for (const v of modalVariants.value) {
    const val = variantInputs[v.id];
    if (val !== undefined && val !== '' && (modalMode.value === 'set' || val > 0)) {
      count++;
    }
  }
  return count;
});

async function submitStockAdjust() {
  if (!modalReason.value.trim()) {
    errorMsg.value = 'Please provide an audit reason.';
    return;
  }

  const items = [];
  for (const v of modalVariants.value) {
    const val = variantInputs[v.id];
    if (val !== undefined && val !== '') {
      items.push({
        variant_id: v.id,
        quantity: parseInt(val) || 0,
      });
    }
  }

  if (items.length === 0) {
    errorMsg.value = 'No variant quantities entered to adjust.';
    return;
  }

  modalSubmitting.value = true;
  errorMsg.value = null;

  try {
    const payload = {
      product_id: modalProduct.value.id,
      mode: modalMode.value,
      reason: modalReason.value.trim(),
      items: items,
    };

    const res = await inventoryStore.quickAdjustStock(payload);
    successMsg.value = res.message || `Successfully adjusted stock for ${modalProduct.value.name}!`;
    closeStockAdjustModal();
    await loadOverview();
  } catch (err) {
    errorMsg.value = err.message || 'Failed to update stock.';
  } finally {
    modalSubmitting.value = false;
  }
}

onMounted(() => {
  categoryStore.fetchCategories();
  loadOverview();
});
</script>

<style scoped>
.stock-overview-page {
  padding-bottom: 4rem;
}

/* KPI Summary Cards Grid */
.kpi-cards-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (max-width: 1280px) {
  .kpi-cards-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .kpi-cards-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .kpi-cards-grid {
    grid-template-columns: 1fr;
  }
}

.kpi-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 1.15rem 1rem;
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  display: flex;
  align-items: center;
  gap: 0.85rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.kpi-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icon--blue {
  background: rgba(37, 99, 235, 0.1);
  color: #2563eb;
}

.kpi-icon--cyan {
  background: rgba(6, 182, 212, 0.1);
  color: #0891b2;
}

.kpi-icon--orange {
  background: rgba(249, 115, 22, 0.1);
  color: #ea580c;
}

.kpi-icon--green {
  background: rgba(22, 163, 74, 0.1);
  color: #16a34a;
}

.kpi-icon--amber {
  background: rgba(217, 119, 6, 0.1);
  color: #d97706;
}

.kpi-icon--red {
  background: rgba(220, 38, 38, 0.1);
  color: #dc2626;
}

.kpi-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.kpi-label {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.kpi-value {
  font-size: 1.35rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1.2;
  margin: 2px 0;
  font-family: 'Poppins', sans-serif;
}

.kpi-subtext {
  font-size: 0.7rem;
  color: var(--color-text-secondary);
}

/* Filter Toolbar */
.filter-toolbar {
  padding: 1rem 1.25rem;
  margin-bottom: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  border-radius: 12px;
}

.filter-toolbar__left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
  flex: 1;
}

.search-input-wrap {
  position: relative;
  min-width: 280px;
  flex: 1;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-text-muted);
}

.search-input {
  width: 100%;
  height: 40px;
  padding-left: 36px;
  padding-right: 32px;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  background: #ffffff;
  font-size: 0.85rem;
  color: var(--color-text-primary);
  outline: none;
  transition: border-color 0.15s ease;
}

.search-input:focus {
  border-color: var(--color-primary);
}

.clear-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--color-text-muted);
}

.filter-select {
  height: 40px;
  padding: 0 0.85rem;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  background: #ffffff;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--color-text-primary);
  outline: none;
}

.filter-toolbar__right {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.view-switcher {
  display: inline-flex;
  background: #f1f5f9;
  padding: 3px;
  border-radius: 8px;
}

.view-btn {
  background: none;
  border: none;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
}

.view-btn--active {
  background: #ffffff;
  color: var(--color-primary);
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.results-count-bar {
  font-size: 0.85rem;
  color: var(--color-text-muted);
  margin-bottom: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* Products Grid Layout (Matching Reference Image) */
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

@media (max-width: 1400px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 1024px) {
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
}

/* Product Stock Card */
.product-stock-card {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  padding: 1rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 0.85rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-stock-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
  border-color: rgba(91, 22, 58, 0.2);
}

.card-header-row {
  display: flex;
  gap: 0.85rem;
  align-items: flex-start;
}

.product-thumb-box {
  width: 64px;
  height: 64px;
  border-radius: 8px;
  overflow: hidden;
  background: #f8fafc;
  border: 1px solid var(--color-border);
  flex-shrink: 0;
}

.product-thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-thumb-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-meta-box {
  min-width: 0;
  flex: 1;
}

.product-card-title {
  font-size: 0.92rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-sku-tag {
  font-size: 0.72rem;
  color: var(--color-text-muted);
  margin-bottom: 2px;
}

.product-cat-tag {
  font-size: 0.72rem;
  color: var(--color-text-secondary);
}

/* 4-column Metric Row */
.stock-metrics-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.35rem;
  background: #f8fafc;
  border-radius: 8px;
  padding: 0.65rem 0.5rem;
  text-align: center;
  align-items: center;
  border: 1px solid #f1f5f9;
}

.metric-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.metric-cell-label {
  font-size: 0.65rem;
  color: var(--color-text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.metric-cell-val {
  font-size: 0.95rem;
  font-weight: 800;
  line-height: 1.1;
  font-family: 'Poppins', sans-serif;
}

.val-stock {
  color: #2563eb; /* Blue */
}

.val-order {
  color: #ea580c; /* Orange */
}

.val-avail {
  color: #16a34a; /* Green */
}

/* Status Pill */
.status-pill {
  display: inline-block;
  padding: 2px 6px;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-pill--success {
  background: #dcfce7;
  color: #166534;
}

.status-pill--warning {
  background: #fef3c7;
  color: #92400e;
}

.status-pill--danger {
  background: #fee2e2;
  color: #991b1b;
}

.status-pill--secondary {
  background: #f1f5f9;
  color: #475569;
}

/* Card Footer */
.card-footer-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 0.5rem;
  border-top: 1px solid #f1f5f9;
}

.updated-time-text {
  font-size: 0.7rem;
  color: var(--color-text-muted);
}

.btn-adjust-stock {
  background: rgba(91, 22, 58, 0.08);
  color: var(--color-primary);
  border: 1px solid rgba(91, 22, 58, 0.15);
  border-radius: 6px;
  padding: 4px 8px;
  font-size: 0.72rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-adjust-stock:hover {
  background: var(--color-primary);
  color: #ffffff;
}

/* Empty State */
.empty-state-box {
  text-align: center;
  padding: 4rem 1rem;
  color: var(--color-text-muted);
  border-radius: 12px;
}

/* Pagination Footer */
.pagination-footer {
  margin-top: 1.5rem;
  padding: 0.85rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  border-radius: 12px;
}

.pagination-footer__per-page {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

.per-page-select {
  height: 32px;
  padding: 0 0.5rem;
  border-radius: 6px;
  border: 1px solid var(--color-border);
  background: #ffffff;
  font-size: 0.85rem;
}

.pagination-footer__nav {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.btn-page-nav,
.btn-page-num {
  height: 34px;
  min-width: 34px;
  padding: 0 6px;
  border-radius: 6px;
  border: 1px solid var(--color-border);
  background: #ffffff;
  color: var(--color-text-primary);
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
}

.btn-page-nav:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-page-num--active {
  background: var(--color-primary);
  color: #ffffff;
  border-color: var(--color-primary);
}

/* Modal Styling */
.color-dot-swatch {
  display: inline-block;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 1px solid #ffffff;
  box-shadow: 0 1px 2px rgba(0,0,0,0.2);
  flex-shrink: 0;
}

.preset-reason-pill {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 2px 8px;
  font-size: 0.7rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.preset-reason-pill:hover {
  background: var(--color-primary);
  color: #ffffff;
}

.variant-stepper-wrap {
  display: inline-flex;
  align-items: center;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  background: #ffffff;
  overflow: hidden;
}

.stepper-btn {
  background: #f8fafc;
  border: none;
  width: 28px;
  height: 28px;
  cursor: pointer;
  font-weight: 700;
  color: #1e293b;
}

.stepper-btn:hover {
  background: #e2e8f0;
}

.stepper-input {
  width: 48px;
  height: 28px;
  border: none;
  border-left: 1px solid var(--color-border);
  border-right: 1px solid var(--color-border);
  text-align: center;
  font-weight: 700;
  font-size: 0.85rem;
  outline: none;
}
</style>
