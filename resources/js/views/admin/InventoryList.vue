<template>
  <div class="inventory-page">
    <!-- Header -->
    <div class="admin-page__header inventory-header">
      <div class="admin-page__title-section">
        <h1 class="admin-page__title">Inventory</h1>
        <span class="admin-page__subtitle">Real-time overview of your product inventory, SKU variants, and stock control.</span>
      </div>
      <div class="header-actions-group">
        <router-link to="/admin/inward/create" class="btn-action-primary">
          <PackageCheck :size="16" />
          <span>Inward Arrival</span>
        </router-link>
        <button 
          type="button" 
          class="btn-action-secondary" 
          @click="exportCsv"
          :disabled="isExporting"
          title="Download inventory records as CSV"
        >
          <Download :size="15" />
          <span>{{ isExporting ? 'Exporting...' : 'Export CSV' }}</span>
        </button>
        <button 
          type="button" 
          class="btn-action-secondary" 
          @click="resetFilters"
          title="Reset all filters"
        >
          <RotateCcw :size="15" />
          <span>Reset</span>
        </button>
      </div>
    </div>

    <!-- Alert Notifications -->
    <div v-if="successMsg" class="badge badge--success notification-alert">
      <span>✓ {{ successMsg }}</span>
      <button type="button" class="alert-close-btn" @click="successMsg = null">✕</button>
    </div>

    <div v-if="errorMsg" class="badge badge--danger notification-alert">
      <span>⚠️ {{ errorMsg }}</span>
      <button type="button" class="alert-close-btn" @click="errorMsg = null">✕</button>
    </div>

    <!-- Top Navigation Tabs (Segmented Control) -->
    <div class="inventory-tabs-bar">
      <div class="segmented-tabs-container">
        <button 
          type="button"
          :class="['segmented-tab-btn', { 'segmented-tab-btn--active': activeTab === 'overview' }]"
          @click="setTab('overview')"
        >
          <Boxes :size="16" />
          <span class="tab-label-full">Stock Overview & Management</span>
          <span class="tab-label-mobile">Stock Overview</span>
        </button>
        <button 
          type="button"
          :class="['segmented-tab-btn', { 'segmented-tab-btn--active': activeTab === 'ledger' }]"
          @click="setTab('ledger')"
        >
          <History :size="16" />
          <span class="tab-label-full">Audit Ledger History</span>
          <span class="tab-label-mobile">Audit Ledger</span>
        </button>
      </div>
    </div>

    <!-- ================= TAB 1: STOCK OVERVIEW & MANAGEMENT ================= -->
    <div v-show="activeTab === 'overview'">
      <!-- 6 KPI Summary Cards Grid -->
      <div class="kpi-cards-grid">
        <!-- 1. Total Products -->
        <div 
          :class="['kpi-card', 'kpi-card--blue', { 'kpi-card--active': activeKpi === 'total_products' || (activeKpi === 'all' && filters.status === 'all' && filters.sort_by === 'name_asc') }]"
          @click="filterByKpi('total_products')"
          role="button"
          tabindex="0"
          title="Click to view all active products"
        >
          <div class="kpi-icon-wrap kpi-icon--blue">
            <Package :size="22" />
          </div>
          <div class="kpi-info">
            <div class="kpi-label">Total Products</div>
            <div class="kpi-value">{{ formatNumber(stats.total_products) }}</div>
            <div class="kpi-subtext">Active Products ➔</div>
          </div>
        </div>

        <!-- 2. Total Stock Qty -->
        <div 
          :class="['kpi-card', 'kpi-card--cyan', { 'kpi-card--active': activeKpi === 'total_stock' || (filters.status === 'all' && filters.sort_by === 'stock_high_low') }]"
          @click="filterByKpi('total_stock')"
          role="button"
          tabindex="0"
          title="Click to sort by highest stock on hand"
        >
          <div class="kpi-icon-wrap kpi-icon--cyan">
            <Boxes :size="22" />
          </div>
          <div class="kpi-info">
            <div class="kpi-label">Total Stock Qty</div>
            <div class="kpi-value">{{ formatNumber(stats.total_stock_qty) }}</div>
            <div class="kpi-subtext">On Hand (High➔Low)</div>
          </div>
        </div>

        <!-- 3. Total Order Qty -->
        <div 
          :class="['kpi-card', 'kpi-card--orange', { 'kpi-card--active': activeKpi === 'ordered' || filters.status === 'ordered' || filters.sort_by === 'order_high_low' }]"
          @click="filterByKpi('ordered')"
          role="button"
          tabindex="0"
          title="Click to filter products with active orders"
        >
          <div class="kpi-icon-wrap kpi-icon--orange">
            <ShoppingCart :size="22" />
          </div>
          <div class="kpi-info">
            <div class="kpi-label">Total Order Qty</div>
            <div class="kpi-value">{{ formatNumber(stats.total_order_qty) }}</div>
            <div class="kpi-subtext">In All Orders ➔</div>
          </div>
        </div>

        <!-- 4. Total Available Qty -->
        <div 
          :class="['kpi-card', 'kpi-card--green', { 'kpi-card--active': activeKpi === 'in_stock' || filters.status === 'in_stock' }]"
          @click="filterByKpi('in_stock')"
          role="button"
          tabindex="0"
          title="Click to filter products ready to ship"
        >
          <div class="kpi-icon-wrap kpi-icon--green">
            <ClipboardCheck :size="22" />
          </div>
          <div class="kpi-info">
            <div class="kpi-label">Total Available Qty</div>
            <div class="kpi-value">{{ formatNumber(stats.total_available_qty) }}</div>
            <div class="kpi-subtext">Ready to Ship ➔</div>
          </div>
        </div>

        <!-- 5. Low Stock Items -->
        <div 
          :class="['kpi-card', 'kpi-card--amber', { 'kpi-card--active': activeKpi === 'low_stock' || filters.status === 'low_stock' }]"
          @click="filterByKpi('low_stock')"
          role="button"
          tabindex="0"
          title="Click to filter low stock items"
        >
          <div class="kpi-icon-wrap kpi-icon--amber">
            <AlertTriangle :size="22" />
          </div>
          <div class="kpi-info">
            <div class="kpi-label">Low Stock Items</div>
            <div class="kpi-value" style="color: #d97706;">{{ formatNumber(stats.low_stock_items) }}</div>
            <div class="kpi-subtext">Need Attention ➔</div>
          </div>
        </div>

        <!-- 6. Out of Stock Items -->
        <div 
          :class="['kpi-card', 'kpi-card--red', { 'kpi-card--active': activeKpi === 'out_of_stock' || filters.status === 'out_of_stock' }]"
          @click="filterByKpi('out_of_stock')"
          role="button"
          tabindex="0"
          title="Click to filter out-of-stock items"
        >
          <div class="kpi-icon-wrap kpi-icon--red">
            <Ban :size="22" />
          </div>
          <div class="kpi-info">
            <div class="kpi-label">Out of Stock Items</div>
            <div class="kpi-value" style="color: #dc2626;">{{ formatNumber(stats.out_of_stock_items) }}</div>
            <div class="kpi-subtext">Out of Stock ➔</div>
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
            <button v-if="filters.search" @click="clearSearch" class="clear-btn" type="button">✕</button>
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
            <select v-model="filters.status" @change="onStatusFilterChange" class="filter-select">
              <option value="all">All Status</option>
              <option value="in_stock">In Stock (Available)</option>
              <option value="low_stock">Low Stock (Attention)</option>
              <option value="out_of_stock">Out of Stock</option>
              <option value="ordered">Ordered / Reserved</option>
            </select>
          </div>

          <!-- Color Dropdown -->
          <div class="select-wrap">
            <select v-model="filters.color" @change="applyFilters" class="filter-select">
              <option value="">All Colors</option>
              <option v-for="c in availableColors" :key="c.id || c.name" :value="c.name">
                🎨 {{ c.name }}
              </option>
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
        <span v-if="overviewLoading" style="font-size: 0.8rem; color: var(--color-primary); font-weight: 600;">
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
                  :src="product.primary_image_url || DEFAULT_PLACEHOLDER_IMAGE" 
                  :alt="product.name" 
                  class="product-thumb-img" 
                  loading="lazy" 
                  @error="onImageError"
                />
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
        <div v-else-if="!overviewLoading" class="glass-panel empty-state-box">
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
                      :src="product.primary_image_url || DEFAULT_PLACEHOLDER_IMAGE" 
                      :alt="product.name" 
                      style="width: 100%; height: 100%; object-fit: cover;" 
                      loading="lazy"
                      @error="onImageError"
                    />
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
            <tr v-if="products.length === 0 && !overviewLoading">
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
    </div>

    <!-- ================= TAB 2: AUDIT LEDGER HISTORY ================= -->
    <div v-show="activeTab === 'ledger'">
      <!-- Ledger Filters panel -->
      <div class="glass-panel" style="padding: 1.25rem 1.5rem; margin-bottom: 1.25rem; border-radius: 12px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: end;">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Transaction Type</label>
            <select v-model="ledgerFilters.type" class="form-input" @change="applyLedgerFilters">
              <option value="">All Movement Types</option>
              <option value="PURCHASE">PURCHASE</option>
              <option value="SALE">SALE</option>
              <option value="RETURN">RETURN</option>
              <option value="DAMAGE">DAMAGE</option>
              <option value="ADJUSTMENT">ADJUSTMENT</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Direction</label>
            <select v-model="ledgerFilters.direction" class="form-input" @change="applyLedgerFilters">
              <option value="">All Directions</option>
              <option value="IN">IN (Stock Additions)</option>
              <option value="OUT">OUT (Stock Deductions)</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">From Date</label>
            <input type="date" v-model="ledgerFilters.date_from" class="form-input" @change="applyLedgerFilters" />
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">To Date</label>
            <input type="date" v-model="ledgerFilters.date_to" class="form-input" @change="applyLedgerFilters" />
          </div>
        </div>
      </div>

      <!-- Ledger Table -->
      <div class="glass-panel" style="overflow: hidden; border-radius: 12px;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Timestamp</th>
              <th>Product SKU</th>
              <th>Type</th>
              <th>Dir</th>
              <th style="text-align: right;">Qty</th>
              <th style="text-align: right;">Stock Before</th>
              <th style="text-align: right;">Stock After</th>
              <th>Reference</th>
              <th>Audit Notes</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in ledger" :key="log.id">
              <td style="font-size: 0.8rem; color: var(--color-text-secondary); white-space: nowrap;">
                {{ formatDate(log.created_at) }}
              </td>
              <td>
                <div style="font-weight: 600; color: #1e293b;">{{ log.variant?.sku }}</div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ log.variant?.product?.name }}</div>
              </td>
              <td>
                <span class="badge" :class="getLedgerTypeClass(log.type)">
                  {{ log.type }}
                </span>
              </td>
              <td>
                <span :style="{ color: log.direction === 'IN' ? 'var(--color-success)' : 'var(--color-danger)', fontWeight: 'bold', fontSize: '0.85rem' }">
                  {{ log.direction }}
                </span>
              </td>
              <td style="text-align: right; font-weight: 700; color: #1e293b;">{{ log.quantity }}</td>
              <td style="text-align: right; color: var(--color-text-muted);">{{ log.stock_before }}</td>
              <td style="text-align: right; font-weight: 700; color: #1e293b;">{{ log.stock_after }}</td>
              <td style="font-size: 0.8rem; color: var(--color-text-secondary);">
                {{ log.reference_type ? log.reference_type.split('\\').pop() + ' #' + log.reference_id : '—' }}
              </td>
              <td style="font-size: 0.8rem; color: var(--color-text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" :title="log.notes">
                {{ log.notes || '—' }}
              </td>
            </tr>
            <tr v-if="ledger.length === 0 && !inventoryStore.loading">
              <td colspan="9" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
                No stock movement ledger records found.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Ledger Pagination -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem var(--spacing-lg); border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.02);">
          <div style="font-size: 0.85rem; color: var(--color-text-muted);">
            Showing records <strong>{{ ledgerPagination.current_page }}</strong> of <strong>{{ ledgerPagination.last_page }}</strong> (Total: {{ ledgerPagination.total }} logs)
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn--secondary btn--sm" :disabled="ledgerPagination.current_page === 1" @click="changeLedgerPage(ledgerPagination.current_page - 1)">
              ◀️ Prev
            </button>
            <button class="btn btn--secondary btn--sm" :disabled="ledgerPagination.current_page === ledgerPagination.last_page" @click="changeLedgerPage(ledgerPagination.current_page + 1)">
              Next ▶️
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= MODAL: Color & Size Wise Stock Adjustment (Easy Update View) ================= -->
    <div v-if="showAdjustModal" class="modal-overlay" @click.self="closeStockAdjustModal">
      <div class="modal-container modal-adjust-stock">
        <!-- Modal Header -->
        <div class="modal-header adjust-modal-header">
          <div class="adjust-header-left">
            <div class="adjust-header-thumb">
              <img 
                :src="modalProduct?.primary_image_url || DEFAULT_PLACEHOLDER_IMAGE" 
                :alt="modalProduct?.name" 
                @error="onImageError"
              />
            </div>
            <div class="adjust-header-info">
              <div class="adjust-header-badge-row">
                <span class="easy-update-pill">⚡ Easy Update</span>
                <span class="adjust-header-cat" :title="modalProduct?.category_name">{{ modalProduct?.category_name || 'Apparel' }}</span>
              </div>
              <h3 class="adjust-header-title" :title="modalProduct?.name">{{ modalProduct?.name }}</h3>
              <div class="adjust-header-sub">
                <span class="sku-meta-tag">SKU: <code class="sku-code-pill">{{ modalProduct?.sku }}</code></span>
                <span class="stock-meta-tag">Total Stock: <strong class="stock-highlight">{{ modalProductStockTotal }} units</strong></span>
              </div>
            </div>
          </div>
          <button class="modal-close adjust-modal-close-btn" @click="closeStockAdjustModal" type="button" title="Close modal">&times;</button>
        </div>

        <form @submit.prevent="submitStockAdjust">
          <div class="modal-body adjust-modal-body">
            
            <!-- Operation Mode & Quick Presets Toolbar -->
            <div class="adjust-toolbar-card">
              <div class="adjust-toolbar-row">
                <!-- Mode Selector -->
                <div class="adjust-mode-section">
                  <span class="adjust-section-label">Adjustment Mode:</span>
                  <div class="adjust-mode-toggle-group">
                    <button 
                      type="button" 
                      :class="['mode-toggle-btn', modalMode === 'add' ? 'mode-toggle-btn--add-active' : '']" 
                      @click="setModalMode('add')" 
                    >
                      <span class="mode-icon">➕</span>
                      <span>Inward / Add</span>
                    </button>
                    <button 
                      type="button" 
                      :class="['mode-toggle-btn', modalMode === 'subtract' ? 'mode-toggle-btn--sub-active' : '']" 
                      @click="setModalMode('subtract')" 
                    >
                      <span class="mode-icon">➖</span>
                      <span>Outward / Deduct</span>
                    </button>
                    <button 
                      type="button" 
                      :class="['mode-toggle-btn', modalMode === 'set' ? 'mode-toggle-btn--set-active' : '']" 
                      @click="setModalMode('set')" 
                    >
                      <span class="mode-icon">🔢</span>
                      <span>Set Exact Count</span>
                    </button>
                  </div>
                </div>

                <!-- View Switcher -->
                <div class="adjust-view-section">
                  <span class="adjust-section-label">Layout:</span>
                  <div class="modal-view-toggle-pill-group">
                    <button 
                      type="button" 
                      :class="['view-toggle-pill-btn', modalViewMode === 'cards' ? 'view-toggle-pill-btn--active' : '']" 
                      @click="modalViewMode = 'cards'"
                      title="Mobile Adaptive Cards View"
                    >
                      📱 Cards
                    </button>
                    <button 
                      type="button" 
                      :class="['view-toggle-pill-btn', modalViewMode === 'table' ? 'view-toggle-pill-btn--active' : '']" 
                      @click="modalViewMode = 'table'"
                      title="Dense Table View"
                    >
                      📋 Table
                    </button>
                  </div>
                </div>
              </div>

              <!-- Quick Presets Row -->
              <div class="adjust-presets-row">
                <div class="adjust-quickfill-section">
                  <span class="adjust-section-label">⚡ Quick Fill All SKUs:</span>
                  <div class="quick-preset-chips-wrap">
                    <button type="button" class="preset-chip" @click="applyQuickPresetAll(5)">+5</button>
                    <button type="button" class="preset-chip" @click="applyQuickPresetAll(10)">+10</button>
                    <button type="button" class="preset-chip" @click="applyQuickPresetAll(25)">+25</button>
                    <button type="button" class="preset-chip" @click="applyQuickPresetAll(50)">+50</button>
                    <button type="button" class="preset-chip preset-chip--clear" @click="clearAllAdjustments" title="Reset all input quantities to 0">Clear</button>
                  </div>
                </div>
                <div class="quick-custom-input-group">
                  <input 
                    type="number" 
                    v-model.number="quickFillQty" 
                    min="0" 
                    class="quick-fill-number-input" 
                    placeholder="Qty"
                  />
                  <button 
                    type="button" 
                    class="btn-quick-fill-apply"
                    @click="applyQuickPresetAll(quickFillQty)"
                  >
                    ⚡ Apply All
                  </button>
                </div>
              </div>

              <!-- Reason & Notes Section -->
              <div class="adjust-reason-section">
                <div class="reason-label-row">
                  <span class="adjust-section-label">Reason / Audit Note: *</span>
                  <span class="reason-hint">Select a preset or type a custom note</span>
                </div>
                <div class="reason-presets-row">
                  <button 
                    v-for="r in reasonPresets" 
                    :key="r" 
                    type="button" 
                    :class="['preset-reason-pill', { 'preset-reason-pill--active': modalReason === r }]" 
                    @click="modalReason = r"
                  >
                    {{ r }}
                  </button>
                </div>
                <input 
                  type="text" 
                  v-model="modalReason" 
                  class="adjust-reason-input" 
                  placeholder="e.g. Supplier Batch Arrival, Store Physical Count..." 
                  required 
                />
              </div>
            </div>

            <!-- Desktop Table View -->
            <div v-if="modalViewMode === 'table'" class="adjust-table-container">
              <table class="adjust-variants-table">
                <thead>
                  <tr>
                    <th class="col-color-size">Color & Size</th>
                    <th class="col-sku">SKU</th>
                    <th class="col-stat text-center">Stock</th>
                    <th class="col-stat text-center">Ordered</th>
                    <th class="col-stat text-center">Available</th>
                    <th class="col-adjust text-center">Adjustment (Qty)</th>
                    <th class="col-projected text-right">Projected Stock</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="variant in modalVariants" 
                    :key="variant.id"
                    :class="['variant-table-row', { 'variant-table-row--modified': getVariantDelta(variant) !== 0 }]"
                  >
                    <!-- Color & Size -->
                    <td class="col-color-size">
                      <div class="variant-color-badge-group">
                        <span 
                          class="variant-color-circle" 
                          :style="{ background: variant.color_code || '#5B163A' }"
                          :title="variant.color"
                        ></span>
                        <strong class="variant-color-name">{{ variant.color }}</strong>
                        <span class="variant-size-pill">{{ variant.size }}</span>
                      </div>
                    </td>

                    <!-- SKU -->
                    <td class="col-sku">
                      <code class="variant-sku-chip">{{ variant.sku }}</code>
                    </td>

                    <!-- Current Stock -->
                    <td class="col-stat text-center">
                      <span class="stat-number stat-number--blue">{{ variant.stock_quantity }}</span>
                    </td>

                    <!-- Ordered Qty -->
                    <td class="col-stat text-center">
                      <span class="stat-number stat-number--orange">{{ variant.order_qty }}</span>
                    </td>

                    <!-- Current Avail -->
                    <td class="col-stat text-center">
                      <span class="stat-number stat-number--green">{{ variant.avail_qty }}</span>
                    </td>
                    
                    <!-- Stepper Input & Mini Shortcuts -->
                    <td class="col-adjust text-center">
                      <div class="adjust-input-cell-wrap">
                        <div class="variant-stepper-wrap">
                          <button 
                            type="button" 
                            class="stepper-btn stepper-btn--minus" 
                            @click="stepVariantQty(variant.id, -1)"
                            title="Decrease quantity"
                          >
                            –
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
                            class="stepper-btn stepper-btn--plus" 
                            @click="stepVariantQty(variant.id, 1)"
                            title="Increase quantity"
                          >
                            +
                          </button>
                        </div>
                        <div class="mini-stepper-shortcuts">
                          <button type="button" class="mini-chip-btn" @click="stepVariantQty(variant.id, 5)">+5</button>
                          <button type="button" class="mini-chip-btn" @click="stepVariantQty(variant.id, 10)">+10</button>
                        </div>
                      </div>
                    </td>

                    <!-- Projected Stock -->
                    <td class="col-projected text-right">
                      <div class="projected-stock-wrap">
                        <span class="projected-val-number">{{ getProjectedVariantStock(variant) }}</span>
                        <span 
                          :class="['delta-badge', getVariantDelta(variant) > 0 ? 'delta-badge--pos' : getVariantDelta(variant) < 0 ? 'delta-badge--neg' : 'delta-badge--zero']"
                        >
                          {{ getVariantDelta(variant) > 0 ? '+' + getVariantDelta(variant) : getVariantDelta(variant) }}
                        </span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Mobile Adaptive Cards List -->
            <div v-else class="adjust-mobile-cards-list">
              <div 
                v-for="variant in modalVariants" 
                :key="variant.id"
                :class="['mobile-variant-card', { 'mobile-variant-card--modified': getVariantDelta(variant) !== 0 }]"
              >
                <!-- Card Top: Color & Size + SKU -->
                <div class="mobile-variant-card-header">
                  <div class="mobile-variant-color-wrap">
                    <span 
                      class="variant-color-circle" 
                      :style="{ background: variant.color_code || '#5B163A' }"
                    ></span>
                    <strong class="variant-color-name">{{ variant.color }}</strong>
                    <span class="variant-size-pill">{{ variant.size }}</span>
                  </div>
                  <code class="variant-sku-chip">{{ variant.sku }}</code>
                </div>

                <!-- Stats 3-column pill strip -->
                <div class="mobile-variant-stats-grid">
                  <div class="m-stat-box">
                    <span class="m-stat-label">Stock</span>
                    <strong class="m-stat-val val-stock">{{ variant.stock_quantity }}</strong>
                  </div>
                  <div class="m-stat-box">
                    <span class="m-stat-label">Ordered</span>
                    <strong class="m-stat-val val-order">{{ variant.order_qty }}</strong>
                  </div>
                  <div class="m-stat-box">
                    <span class="m-stat-label">Avail</span>
                    <strong class="m-stat-val val-avail">{{ variant.avail_qty }}</strong>
                  </div>
                </div>

                <!-- Action Controls: Stepper + Shortcuts + Projected -->
                <div class="mobile-variant-controls-row">
                  <div class="mobile-stepper-and-shortcuts">
                    <div class="variant-stepper-wrap variant-stepper-wrap--mobile">
                      <button 
                        type="button" 
                        class="stepper-btn stepper-btn--mobile" 
                        @click="stepVariantQty(variant.id, -1)"
                      >
                        –
                      </button>
                      <input 
                        type="number" 
                        v-model.number="variantInputs[variant.id]" 
                        min="0" 
                        class="stepper-input stepper-input--mobile" 
                        placeholder="0" 
                      />
                      <button 
                        type="button" 
                        class="stepper-btn stepper-btn--mobile" 
                        @click="stepVariantQty(variant.id, 1)"
                      >
                        +
                      </button>
                    </div>
                    <div class="mobile-mini-chips">
                      <button type="button" class="mini-chip-btn mini-chip-btn--m" @click="stepVariantQty(variant.id, 5)">+5</button>
                      <button type="button" class="mini-chip-btn mini-chip-btn--m" @click="stepVariantQty(variant.id, 10)">+10</button>
                    </div>
                  </div>

                  <!-- Projected -->
                  <div class="mobile-projected-result">
                    <span class="m-proj-title">Projected:</span>
                    <div class="m-proj-val-wrap">
                      <span class="projected-val-number">{{ getProjectedVariantStock(variant) }}</span>
                      <span 
                        :class="['delta-badge', getVariantDelta(variant) > 0 ? 'delta-badge--pos' : getVariantDelta(variant) < 0 ? 'delta-badge--neg' : 'delta-badge--zero']"
                      >
                        {{ getVariantDelta(variant) > 0 ? '+' + getVariantDelta(variant) : getVariantDelta(variant) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Impact Summary Bar -->
            <div class="adjust-summary-bar">
              <div class="summary-stock-transition">
                <span class="summary-label">Total Stock Transformation:</span>
                <span class="summary-current-val">{{ modalProductStockTotal }}</span>
                <span class="summary-arrow">➔</span>
                <span class="summary-projected-val">{{ modalProductProjectedTotal }}</span>
              </div>
              <div class="summary-badges-group">
                <span class="summary-sku-count">
                  <strong>{{ modifiedVariantsCount }}</strong> of {{ modalVariants.length }} SKUs updated
                </span>
                <span :class="['summary-delta-pill', modalNetDelta > 0 ? 'summary-delta-pill--pos' : modalNetDelta < 0 ? 'summary-delta-pill--neg' : 'summary-delta-pill--neutral']">
                  Net Change: {{ modalNetDelta > 0 ? '+' + modalNetDelta : modalNetDelta }} units
                </span>
              </div>
            </div>

          </div>

          <!-- Modal Footer Actions -->
          <div class="modal-footer adjust-modal-footer">
            <button type="button" class="btn btn--secondary btn-adjust-cancel" @click="closeStockAdjustModal" :disabled="modalSubmitting">
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn--primary btn-adjust-save" 
              :disabled="modalSubmitting || modifiedVariantsCount === 0"
            >
              <span v-if="modalSubmitting">⏳ Saving Stock Changes...</span>
              <span v-else>💾 Save Stock Updates ({{ modifiedVariantsCount }} SKUs)</span>
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { useInventoryStore } from '../../stores/inventory';
import { useCategoryStore } from '../../stores/category';
import { useColorStore } from '../../stores/color';
import { 
  Package, Boxes, ShoppingCart, ClipboardCheck, AlertTriangle, Ban, 
  Search, LayoutGrid, List, SlidersHorizontal, Download, RotateCcw, 
  Shirt, PackageCheck, History 
} from 'lucide-vue-next';

const inventoryStore = useInventoryStore();
const categoryStore = useCategoryStore();
const colorStore = useColorStore();

const isExporting = ref(false);
const DEFAULT_PLACEHOLDER_IMAGE = '/asset/product-placeholder.jpg';

function onImageError(event) {
  if (event && event.target && event.target.src !== DEFAULT_PLACEHOLDER_IMAGE) {
    event.target.src = DEFAULT_PLACEHOLDER_IMAGE;
  }
}

const activeTab = ref('overview'); // 'overview' | 'ledger'
const viewMode = ref('grid'); // 'grid' | 'table'
const successMsg = ref(null);
const errorMsg = ref(null);
const activeKpi = ref('all');

// Tab 1 Filters State
const filters = reactive({
  search: '',
  category_id: '',
  status: 'all',
  color: '',
  sort_by: 'name_asc',
  page: 1,
  per_page: 24,
});

// Tab 2 Ledger Filters State
const ledgerFilters = reactive({
  type: '',
  direction: '',
  date_from: '',
  date_to: '',
  page: 1,
});

// Modal Adjustment State
const showAdjustModal = ref(false);
const modalProduct = ref(null);
const modalMode = ref('add'); // 'add' | 'subtract' | 'set'
const modalReason = ref('Supplier Shipment Arrival');
const modalSubmitting = ref(false);
const modalViewMode = ref('cards'); // 'cards' | 'table'
const quickFillQty = ref(10);
const variantInputs = reactive({});

const reasonPresets = [
  'Supplier Shipment Arrival',
  'Store Physical Audit',
  'Factory Batch Production',
  'Damage / Return Adjustment',
  'Showroom Transfer',
];

// Computed State
const overviewLoading = computed(() => inventoryStore.overviewLoading);
const stats = computed(() => inventoryStore.overviewStats);
const products = computed(() => inventoryStore.overviewProducts);
const pagination = computed(() => inventoryStore.overviewPagination);
const ledger = computed(() => inventoryStore.ledger);
const ledgerPagination = computed(() => inventoryStore.ledgerPagination);
const categories = computed(() => categoryStore.categories);
const availableColors = computed(() => colorStore.activeColors.length ? colorStore.activeColors : colorStore.colors);

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

function filterByKpi(kpiType) {
  if (activeKpi.value === kpiType && kpiType !== 'all' && kpiType !== 'total_products') {
    // Clicking the same active KPI card toggles back to all products
    activeKpi.value = 'all';
    filters.status = 'all';
    filters.sort_by = 'name_asc';
  } else {
    activeKpi.value = kpiType;
    switch (kpiType) {
      case 'total_products':
      case 'all':
        filters.status = 'all';
        filters.sort_by = 'name_asc';
        break;
      case 'total_stock':
        filters.status = 'all';
        filters.sort_by = 'stock_high_low';
        break;
      case 'ordered':
        filters.status = 'ordered';
        filters.sort_by = 'order_high_low';
        break;
      case 'in_stock':
        filters.status = 'in_stock';
        break;
      case 'low_stock':
        filters.status = 'low_stock';
        break;
      case 'out_of_stock':
        filters.status = 'out_of_stock';
        break;
    }
  }
  filters.page = 1;
  loadOverview();
}

function onStatusFilterChange() {
  if (filters.status === 'in_stock') activeKpi.value = 'in_stock';
  else if (filters.status === 'low_stock') activeKpi.value = 'low_stock';
  else if (filters.status === 'out_of_stock') activeKpi.value = 'out_of_stock';
  else if (filters.status === 'ordered') activeKpi.value = 'ordered';
  else activeKpi.value = 'all';
  applyFilters();
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
  activeKpi.value = 'all';
  filters.search = '';
  filters.category_id = '';
  filters.status = 'all';
  filters.color = '';
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

function applyLedgerFilters() {
  inventoryStore.fetchLedger(ledgerFilters);
}

function changeLedgerPage(page) {
  ledgerFilters.page = page;
  applyLedgerFilters();
}

function setTab(tab) {
  activeTab.value = tab;
  if (tab === 'overview') {
    loadOverview();
  } else if (tab === 'ledger') {
    applyLedgerFilters();
  }
}

async function exportCsv() {
  if (isExporting.value) return;
  isExporting.value = true;
  try {
    const params = { ...filters };
    delete params.page;
    delete params.per_page;

    const response = await axios.get('/api/admin/inventory/export-overview-csv', {
      params,
      responseType: 'blob'
    });

    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    const dateStr = new Date().toISOString().slice(0, 10);
    link.setAttribute('download', `inventory_stock_overview_${dateStr}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);

    successMsg.value = 'Inventory CSV exported successfully!';
    setTimeout(() => { successMsg.value = null; }, 4000);
  } catch (error) {
    console.error('Export CSV failed:', error);
    errorMsg.value = 'Failed to export inventory CSV. Please try again.';
    setTimeout(() => { errorMsg.value = null; }, 5000);
  } finally {
    isExporting.value = false;
  }
}

function formatNumber(num) {
  if (num === undefined || num === null) return '0';
  return Number(num).toLocaleString('en-IN');
}

function formatDate(dtStr) {
  if (!dtStr) return '';
  const date = new Date(dtStr);
  return date.toLocaleString();
}

function getStatusClass(status) {
  switch (status) {
    case 'in_stock': return 'status-pill--success';
    case 'low_stock': return 'status-pill--warning';
    case 'out_of_stock': return 'status-pill--danger';
    default: return 'status-pill--secondary';
  }
}

function getLedgerTypeClass(type) {
  switch (type) {
    case 'PURCHASE': return 'badge--success';
    case 'SALE': return 'badge--secondary';
    case 'DAMAGE': return 'badge--danger';
    case 'ADJUSTMENT': return 'badge--warning';
    default: return 'badge--secondary';
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
  modalViewMode.value = window.innerWidth > 1024 ? 'table' : 'cards';
  
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

function applyQuickPresetAll(qty) {
  quickFillQty.value = qty;
  for (const v of modalVariants.value) {
    variantInputs[v.id] = qty;
  }
}

function clearAllAdjustments() {
  quickFillQty.value = 0;
  for (const v of modalVariants.value) {
    variantInputs[v.id] = 0;
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
    if (val !== undefined && val !== '' && (modalMode.value === 'set' || val > 0)) {
      items.push({
        variant_id: v.id,
        quantity: parseInt(val, 10),
      });
    }
  }

  if (items.length === 0) {
    errorMsg.value = 'Please specify adjustment quantities for at least one SKU.';
    return;
  }

  modalSubmitting.value = true;
  errorMsg.value = null;

  try {
    const res = await inventoryStore.quickAdjustStock({
      product_id: modalProduct.value.id,
      mode: modalMode.value,
      reason: modalReason.value,
      items: items,
    });

    successMsg.value = res.message || 'Stock updated successfully!';
    closeStockAdjustModal();
    loadOverview();

    setTimeout(() => {
      successMsg.value = null;
    }, 4000);
  } catch (err) {
    errorMsg.value = err.message || err.response?.data?.message || 'Failed to update stock.';
  } finally {
    modalSubmitting.value = false;
  }
}

onMounted(() => {
  categoryStore.fetchCategories();
  colorStore.fetchActiveColors();
  loadOverview();
});
</script>

<style scoped>
.inventory-page {
  padding-bottom: 4rem;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
  overflow-x: hidden;
}

.inventory-header {
  margin-bottom: 1.25rem;
}

.header-actions-group {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.btn-action-primary {
  background: var(--color-primary);
  color: #ffffff !important;
  padding: 0.55rem 1.15rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  text-decoration: none;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(91, 22, 58, 0.25);
  white-space: nowrap;
}

.btn-action-primary:hover {
  background: #460f2b;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(91, 22, 58, 0.35);
}

.btn-action-secondary {
  background: #ffffff;
  border: 1px solid var(--color-border);
  color: #334155;
  padding: 0.55rem 0.85rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-action-secondary:hover {
  background: #f8fafc;
  color: #0f172a;
  border-color: #cbd5e1;
}

.notification-alert {
  margin-bottom: 1.25rem;
  padding: 0.85rem 1.25rem;
  width: 100%;
  border-radius: 8px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.alert-close-btn {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  font-size: 1.1rem;
}

/* Tabs Bar (Segmented Control) */
.inventory-tabs-bar {
  display: flex;
  align-items: center;
  margin-bottom: 1.25rem;
}

.segmented-tabs-container {
  display: inline-flex;
  background: #f1f5f9;
  padding: 3px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  gap: 3px;
}

.segmented-tab-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  padding: 0.5rem 1.15rem;
  border-radius: 8px;
  border: none;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  background: transparent;
  color: #64748b;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.segmented-tab-btn:hover {
  color: #1e293b;
}

.segmented-tab-btn--active {
  background: #ffffff !important;
  color: var(--color-primary) !important;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  font-weight: 700;
}

.tab-label-full {
  display: inline;
}

.tab-label-mobile {
  display: none;
}

@media (max-width: 768px) {
  .header-actions-group {
    display: grid;
    grid-template-columns: 1.4fr 1fr 0.9fr;
    gap: 0.4rem;
    width: 100%;
    margin-top: 0.75rem;
  }

  .btn-action-primary,
  .btn-action-secondary {
    height: 38px;
    padding: 0 0.4rem;
    font-size: 0.75rem;
  }

  .segmented-tabs-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    width: 100%;
  }

  .segmented-tab-btn {
    padding: 0.5rem 0.25rem;
    font-size: 0.78rem;
    height: 38px;
  }

  .tab-label-full {
    display: none;
  }

  .tab-label-mobile {
    display: inline;
  }
}

/* KPI Summary Cards Grid */
.kpi-cards-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 0.85rem;
  margin-bottom: 1.25rem;
}

@media (max-width: 1400px) {
  .kpi-cards-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
  }
}

@media (max-width: 768px) {
  .kpi-cards-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
    margin-bottom: 1rem;
  }
}

@media (max-width: 380px) {
  .kpi-cards-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.35rem;
  }
}

.kpi-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 0.85rem 0.95rem;
  border: 2px solid transparent;
  outline: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  user-select: none;
  position: relative;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
  min-width: 0;
}

@media (max-width: 768px) {
  .kpi-card {
    padding: 0.6rem 0.65rem;
    gap: 0.5rem;
    border-radius: 10px;
  }
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.kpi-card:active {
  transform: translateY(0);
}

/* Individual KPI Card Active & Hover Themes */
.kpi-card--blue:hover {
  border-color: rgba(37, 99, 235, 0.4);
}
.kpi-card--blue.kpi-card--active {
  border-color: #2563eb;
  background: #eff6ff;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
  outline: none;
}

.kpi-card--cyan:hover {
  border-color: rgba(8, 145, 178, 0.4);
}
.kpi-card--cyan.kpi-card--active {
  border-color: #0891b2;
  background: #ecfeff;
  box-shadow: 0 4px 12px rgba(8, 145, 178, 0.15);
  outline: none;
}

.kpi-card--orange:hover {
  border-color: rgba(234, 88, 12, 0.4);
}
.kpi-card--orange.kpi-card--active {
  border-color: #ea580c;
  background: #fff7ed;
  box-shadow: 0 4px 12px rgba(234, 88, 12, 0.15);
  outline: none;
}

.kpi-card--green:hover {
  border-color: rgba(22, 163, 74, 0.4);
}
.kpi-card--green.kpi-card--active {
  border-color: #16a34a;
  background: #f0fdf4;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
  outline: none;
}

.kpi-card--amber:hover {
  border-color: rgba(217, 119, 6, 0.4);
}
.kpi-card--amber.kpi-card--active {
  border-color: #d97706;
  background: #fffbeb;
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);
  outline: none;
}

.kpi-card--red:hover {
  border-color: rgba(220, 38, 38, 0.4);
}
.kpi-card--red.kpi-card--active {
  border-color: #dc2626;
  background: #fef2f2;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
  outline: none;
}

.kpi-icon-wrap {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .kpi-icon-wrap {
    width: 32px;
    height: 32px;
    border-radius: 8px;
  }

  .kpi-icon-wrap svg {
    width: 17px !important;
    height: 17px !important;
  }
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
  overflow: hidden;
}

.kpi-label {
  font-size: 0.72rem;
  color: var(--color-text-muted);
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.2;
}

@media (max-width: 768px) {
  .kpi-label {
    font-size: 0.65rem;
  }
}

.kpi-value {
  font-size: 1.25rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1.2;
  margin: 2px 0;
  font-family: 'Poppins', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (max-width: 768px) {
  .kpi-value {
    font-size: 1.05rem;
    margin: 1px 0;
  }
}

.kpi-subtext {
  font-size: 0.68rem;
  color: var(--color-text-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (max-width: 768px) {
  .kpi-subtext {
    font-size: 0.58rem;
  }
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

/* Products Grid Layout */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
  gap: 1.15rem;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

@media (min-width: 1680px) {
  .products-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (min-width: 1240px) and (max-width: 1679px) {
  .products-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (min-width: 820px) and (max-width: 1239px) {
  .products-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 819px) {
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
  padding: 1.1rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 0.85rem;
  min-width: 0;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
  overflow: hidden;
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
  min-width: 0;
  width: 100%;
}

.product-thumb-box {
  width: 60px;
  height: 60px;
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
  overflow: hidden;
}

.product-card-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 4px 0;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0;
}

.product-sku-tag {
  font-size: 0.72rem;
  color: var(--color-text-muted);
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-cat-tag {
  font-size: 0.72rem;
  color: var(--color-text-secondary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* 4-column Metric Row */
.stock-metrics-row {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.25rem;
  background: #f8fafc;
  border-radius: 8px;
  padding: 0.65rem 0.35rem;
  text-align: center;
  align-items: center;
  border: 1px solid #f1f5f9;
  min-width: 0;
  width: 100%;
  box-sizing: border-box;
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
  color: #2563eb;
}

.val-order {
  color: #ea580c;
}

.val-avail {
  color: #16a34a;
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
  min-width: 0;
  width: 100%;
  gap: 0.5rem;
  flex-wrap: wrap;
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

/* ================= Easy Stock Update Modal Styling ================= */
.modal-adjust-stock {
  max-width: 980px !important;
  width: 94vw !important;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  background: #ffffff;
}

.adjust-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.15rem 1.35rem;
  background: #ffffff;
  border-bottom: 1px solid var(--color-border);
  gap: 0.75rem;
  position: relative;
}

.adjust-header-left {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
  min-width: 0;
  flex: 1;
}

.adjust-header-thumb {
  width: 52px;
  height: 52px;
  border-radius: 10px;
  overflow: hidden;
  background: #f8fafc;
  border: 1px solid var(--color-border);
  flex-shrink: 0;
  margin-top: 2px;
}

.adjust-header-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.adjust-header-info {
  min-width: 0;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.adjust-header-badge-row {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.easy-update-pill {
  display: inline-flex;
  align-items: center;
  background: #fef3c7;
  color: #b45309;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 20px;
  letter-spacing: 0.3px;
  white-space: nowrap;
  flex-shrink: 0;
}

.adjust-header-cat {
  display: inline-flex;
  align-items: center;
  background: #f1f5f9;
  color: #475569;
  font-size: 0.68rem;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 20px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
  flex-shrink: 0;
}

.adjust-header-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0;
  line-height: 1.35;
  font-family: 'Playfair Display', serif;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.adjust-header-sub {
  font-size: 0.76rem;
  color: var(--color-text-muted);
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
  margin-top: 2px;
}

.sku-meta-tag,
.stock-meta-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  white-space: nowrap;
}

.sku-code-pill {
  font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
  font-size: 0.72rem;
  background: #f1f5f9;
  padding: 1px 6px;
  border-radius: 4px;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.stock-highlight {
  color: #2563eb;
  font-weight: 700;
}

.adjust-modal-close-btn {
  background: none;
  border: none;
  font-size: 1.4rem;
  line-height: 1;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 6px;
  transition: all 0.15s ease;
  flex-shrink: 0;
}

.adjust-modal-close-btn:hover {
  background: #f1f5f9;
  color: #1e293b;
}

/* Modal Body & Toolbar */
.adjust-modal-body {
  padding: 1.25rem 1.5rem;
  max-height: calc(85vh - 160px);
  overflow-y: auto;
}

.adjust-toolbar-card {
  background: #faf8f5;
  border: 1px solid #f0e6da;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-bottom: 1.25rem;
}

.adjust-toolbar-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 0.85rem;
}

.adjust-presets-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
  flex-wrap: wrap;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px dashed #e2e8f0;
}

.adjust-section-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
  margin-bottom: 0.4rem;
}

.adjust-mode-toggle-group {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.mode-toggle-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.42rem 0.95rem;
  border-radius: 20px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  transition: all 0.15s ease;
}

.mode-toggle-btn:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.mode-toggle-btn--add-active {
  background: #16a34a !important;
  color: #ffffff !important;
  border-color: #16a34a !important;
  box-shadow: 0 2px 8px rgba(22, 163, 74, 0.25);
}

.mode-toggle-btn--sub-active {
  background: #dc2626 !important;
  color: #ffffff !important;
  border-color: #dc2626 !important;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
}

.mode-toggle-btn--set-active {
  background: #2563eb !important;
  color: #ffffff !important;
  border-color: #2563eb !important;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
}

/* Quick Fill Presets */
.quick-preset-chips-wrap {
  display: flex;
  gap: 0.35rem;
  align-items: center;
  margin-bottom: 0.4rem;
  flex-wrap: wrap;
}

.preset-chip {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 14px;
  padding: 2px 9px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #334155;
  cursor: pointer;
  transition: all 0.15s ease;
}

.preset-chip:hover {
  background: var(--color-primary);
  color: #ffffff;
  border-color: var(--color-primary);
}

.preset-chip--clear {
  background: #fee2e2;
  border-color: #fca5a5;
  color: #b91c1c;
}

.preset-chip--clear:hover {
  background: #dc2626;
  color: #ffffff;
  border-color: #dc2626;
}

.quick-custom-input-group {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.quick-fill-number-input {
  width: 70px;
  height: 32px;
  text-align: center;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  font-size: 0.85rem;
  font-weight: 700;
  background: #ffffff;
  outline: none;
}

.btn-quick-fill-apply {
  height: 32px;
  padding: 0 0.85rem;
  font-size: 0.78rem;
  font-weight: 700;
  border-radius: 6px;
  background: #334155;
  color: #ffffff;
  border: none;
  cursor: pointer;
  transition: background 0.15s ease;
}

.btn-quick-fill-apply:hover {
  background: #0f172a;
}

/* Reason Section */
.adjust-reason-section {
  border-top: 1px dashed #e2e8f0;
  padding-top: 0.75rem;
}

.reason-label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.35rem;
}

.reason-hint {
  font-size: 0.7rem;
  color: var(--color-text-muted);
}

.reason-presets-row {
  display: flex;
  gap: 0.35rem;
  flex-wrap: wrap;
  margin-bottom: 0.45rem;
}

.preset-reason-pill {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 14px;
  padding: 3px 10px;
  font-size: 0.72rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.15s ease;
}

.preset-reason-pill:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.preset-reason-pill--active {
  background: var(--color-primary) !important;
  color: #ffffff !important;
  border-color: var(--color-primary) !important;
  font-weight: 700;
}

.adjust-reason-input {
  width: 100%;
  height: 38px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  padding: 0 0.85rem;
  font-size: 0.85rem;
  background: #ffffff;
  outline: none;
  transition: border-color 0.15s ease;
}

.adjust-reason-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(91, 22, 58, 0.1);
}

/* Variant Stock Table */
.adjust-table-container {
  border: 1px solid var(--color-border);
  border-radius: 12px;
  overflow-x: auto;
  background: #ffffff;
  box-shadow: var(--shadow-sm);
  margin-bottom: 1.25rem;
}

.adjust-variants-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}

.adjust-variants-table thead th {
  background: #fbf9f6;
  color: #475569;
  font-weight: 700;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid var(--color-border);
  white-space: nowrap;
}

.adjust-variants-table tbody td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  white-space: nowrap;
}

.variant-table-row {
  transition: background 0.15s ease;
}

.variant-table-row:hover {
  background: #f8fafc;
}

.variant-table-row--modified {
  background: rgba(22, 163, 74, 0.04) !important;
  border-left: 3px solid #16a34a;
}

.variant-color-badge-group {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  white-space: nowrap;
}

.variant-color-circle {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid #ffffff;
  box-shadow: 0 1px 3px rgba(0,0,0,0.25);
  flex-shrink: 0;
}

.variant-color-name {
  font-size: 0.88rem;
  color: #1e293b;
  white-space: nowrap;
}

.variant-size-pill {
  display: inline-block;
  background: #f1f5f9;
  color: #1e293b;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  white-space: nowrap;
  flex-shrink: 0;
}

.variant-sku-chip {
  font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
  font-size: 0.78rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #475569;
  padding: 3px 8px;
  border-radius: 6px;
  white-space: nowrap;
}

.stat-number {
  font-size: 0.95rem;
  font-weight: 700;
  font-family: 'Poppins', sans-serif;
}

.stat-number--blue {
  color: #2563eb;
}

.stat-number--orange {
  color: #ea580c;
}

.stat-number--green {
  color: #16a34a;
}

/* Stepper Input & Mini Shortcuts */
.adjust-input-cell-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.variant-stepper-wrap {
  display: inline-flex;
  align-items: center;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #ffffff;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.stepper-btn {
  width: 32px;
  height: 32px;
  background: #f8fafc;
  border: none;
  font-size: 1.1rem;
  font-weight: 800;
  cursor: pointer;
  color: #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s ease;
}

.stepper-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.stepper-input {
  width: 54px;
  height: 32px;
  border: none;
  border-left: 1px solid #cbd5e1;
  border-right: 1px solid #cbd5e1;
  text-align: center;
  font-size: 0.92rem;
  font-weight: 800;
  color: #0f172a;
  outline: none;
  background: #ffffff;
}

.mini-stepper-shortcuts {
  display: flex;
  gap: 2px;
}

.mini-chip-btn {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  font-size: 0.68rem;
  font-weight: 700;
  color: #475569;
  padding: 2px 5px;
  cursor: pointer;
  transition: all 0.1s ease;
}

.mini-chip-btn:hover {
  background: #e2e8f0;
  color: #0f172a;
}

/* Projected Stock */
.projected-stock-wrap {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.projected-val-number {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--color-primary);
  font-family: 'Poppins', sans-serif;
}

.delta-badge {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 10px;
}

.delta-badge--pos {
  background: #dcfce7;
  color: #15803d;
}

.delta-badge--neg {
  background: #fee2e2;
  color: #b91c1c;
}

.delta-badge--zero {
  background: #f1f5f9;
  color: #94a3b8;
}

/* Impact Summary Bar */
.adjust-summary-bar {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.85rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.summary-stock-transition {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.88rem;
}

.summary-label {
  color: var(--color-text-secondary);
  font-weight: 600;
}

.summary-current-val {
  font-weight: 800;
  color: #334155;
  font-size: 1.05rem;
}

.summary-arrow {
  color: #94a3b8;
  font-weight: 700;
}

.summary-projected-val {
  font-weight: 800;
  color: var(--color-primary);
  font-size: 1.15rem;
}

.summary-badges-group {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.summary-sku-count {
  font-size: 0.82rem;
  color: var(--color-text-secondary);
}

.summary-delta-pill {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.82rem;
  font-weight: 700;
}

.summary-delta-pill--pos {
  background: #dcfce7;
  color: #166534;
}

.summary-delta-pill--neg {
  background: #fee2e2;
  color: #991b1b;
}

.summary-delta-pill--neutral {
  background: #f1f5f9;
  color: #475569;
}

/* Modal Footer */
.adjust-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.15rem 1.5rem;
  background: #ffffff;
  border-top: 1px solid var(--color-border);
}

.btn-adjust-cancel {
  padding: 0.65rem 1.25rem;
  font-weight: 600;
  border-radius: 8px;
  font-size: 0.9rem;
}

.btn-adjust-save {
  padding: 0.65rem 1.75rem;
  font-weight: 700;
  border-radius: 8px;
  font-size: 0.92rem;
  background: var(--color-primary);
  color: #ffffff;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(91, 22, 58, 0.25);
}

.btn-adjust-save:hover:not(:disabled) {
  background: #460f2b;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(91, 22, 58, 0.35);
}

.btn-adjust-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Header Right Actions & View Toggle */
.adjust-header-right-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.modal-view-toggle-pill-group {
  display: inline-flex;
  align-items: center;
  background: #f1f5f9;
  border-radius: 20px;
  padding: 2px;
  border: 1px solid #e2e8f0;
}

.view-toggle-pill-btn {
  padding: 4px 10px;
  font-size: 0.75rem;
  font-weight: 700;
  border: none;
  background: transparent;
  color: #64748b;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.view-toggle-pill-btn:hover {
  color: #1e293b;
}

.view-toggle-pill-btn--active {
  background: #ffffff;
  color: var(--color-primary);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Mobile Adaptive Variant Cards Layout */
.adjust-mobile-cards-list {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  margin-bottom: 1.25rem;
}

.mobile-variant-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 0.95rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  box-shadow: var(--shadow-sm);
  transition: all 0.15s ease;
}

.mobile-variant-card--modified {
  background: rgba(22, 163, 74, 0.04) !important;
  border-color: #16a34a !important;
  border-left: 4px solid #16a34a !important;
}

.mobile-variant-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.mobile-variant-color-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.mobile-variant-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.4rem;
  background: #f8fafc;
  border-radius: 8px;
  padding: 0.5rem;
  border: 1px solid #f1f5f9;
  text-align: center;
}

.m-stat-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1px;
}

.m-stat-label {
  font-size: 0.65rem;
  color: var(--color-text-muted);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.m-stat-val {
  font-size: 0.95rem;
  font-weight: 800;
  font-family: 'Poppins', sans-serif;
}

.mobile-variant-controls-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
  padding-top: 0.65rem;
  border-top: 1px dashed #e2e8f0;
}

.mobile-stepper-and-shortcuts {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.variant-stepper-wrap--mobile .stepper-btn--mobile {
  width: 40px;
  height: 40px;
  font-size: 1.2rem;
  min-width: 40px;
}

.variant-stepper-wrap--mobile .stepper-input--mobile {
  width: 60px;
  height: 40px;
  font-size: 1rem;
}

.mobile-mini-chips {
  display: flex;
  gap: 4px;
}

.mini-chip-btn--m {
  height: 40px;
  padding: 0 8px;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.mobile-projected-result {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.m-proj-title {
  font-size: 0.68rem;
  color: var(--color-text-muted);
  font-weight: 600;
}

.m-proj-val-wrap {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

/* Media Query: Tablet & Mobile Responsiveness (<= 991px) */
@media (max-width: 991px) {
  /* Fullscreen fluid modal on mobile / tablet */
  .modal-overlay {
    padding: 0;
    align-items: flex-end;
  }

  .modal-adjust-stock {
    width: 100vw !important;
    max-width: 100vw !important;
    height: 100vh !important;
    max-height: 100vh !important;
    border-radius: 0 !important;
    margin: 0 !important;
    display: flex;
    flex-direction: column;
  }

  .modal-adjust-stock form {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
  }

  .adjust-modal-header {
    padding: 0.85rem 1rem;
    position: sticky;
    top: 0;
    z-index: 10;
    background: #ffffff;
  }

  .adjust-modal-body {
    padding: 0.85rem 1rem;
    flex: 1;
    max-height: none !important;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }

  .adjust-modal-footer {
    padding: 0.85rem 1rem;
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: #ffffff;
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.08);
  }

  .btn-adjust-save {
    flex: 2;
    min-height: 48px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .btn-adjust-cancel {
    flex: 1;
    min-height: 48px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .adjust-toolbar-card {
    padding: 0.85rem;
  }

  .adjust-toolbar-row {
    flex-direction: column;
    gap: 1rem;
  }

  .adjust-mode-section,
  .adjust-quickfill-section {
    width: 100%;
  }

  .adjust-mode-toggle-group {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.35rem;
  }

  .mode-toggle-btn {
    padding: 0.5rem 0.25rem;
    font-size: 0.75rem;
    min-height: 44px;
    justify-content: center;
    flex-direction: column;
    gap: 2px;
    text-align: center;
  }

  .quick-preset-chips-wrap {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.35rem;
    width: 100%;
  }

  .preset-chip {
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    font-size: 0.78rem;
  }

  .quick-custom-input-group {
    width: 100%;
    margin-top: 0.4rem;
  }

  .quick-fill-number-input {
    flex: 1;
    min-height: 42px;
    font-size: 0.95rem;
  }

  .btn-quick-fill-apply {
    flex: 2;
    min-height: 42px;
    font-size: 0.85rem;
  }

  .adjust-reason-input {
    min-height: 44px;
  }

  .summary-stock-transition {
    width: 100%;
    justify-content: space-between;
  }

  .summary-badges-group {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
