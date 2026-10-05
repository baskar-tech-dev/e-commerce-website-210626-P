<template>
  <div class="sales-statement-page">
    <!-- Page Header -->
    <div class="admin-page__header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: var(--spacing-md);">
      <div class="admin-page__title-section" style="flex: 1 1 auto; min-width: 280px;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <router-link to="/admin/reports" class="btn btn--secondary btn--sm" style="padding: 4px 8px; height: 32px; display: inline-flex; align-items: center;" title="Back to Reports Hub">
            ← Reports Hub
          </router-link>
          <h1 class="admin-page__title" style="margin: 0;">Sales Statement Report</h1>
        </div>
        <span class="admin-page__subtitle" style="margin-top: 2px;">
          Comprehensive sales financial ledger, tax schedules, order accounting, and itemized product breakdowns.
        </span>
      </div>

      <div class="admin-header__actions" style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap; justify-content: flex-end; flex-shrink: 0;">
        <!-- Column Visibility Picker Trigger -->
        <div style="position: relative;">
          <button 
            @click="showColumnPicker = !showColumnPicker" 
            class="btn btn--secondary btn--sm" 
            style="height: 36px; display: inline-flex; align-items: center; gap: 5px; font-weight: 500; white-space: nowrap;"
            title="Choose which columns to show or hide and export"
          >
            <span>🎛️ Columns ({{ visibleColumnCount }}/{{ availableColumns.length }})</span>
            <span style="font-size: 0.7rem;">▼</span>
          </button>

          <!-- Column Picker Dropdown Popover -->
          <div v-if="showColumnPicker" class="column-picker-dropdown" @click.stop>
            <div class="column-picker-header">
              <span style="font-weight: 600; font-size: 0.82rem; color: #1e293b;">Visible Columns</span>
              <button @click="showColumnPicker = false" class="btn-close" style="font-size: 0.9rem;">✕</button>
            </div>
            
            <div class="column-picker-toolbar">
              <input 
                type="text" 
                v-model="columnFilterText" 
                placeholder="Filter columns..." 
                class="form-input" 
                style="padding: 0.25rem 0.5rem; font-size: 0.76rem; width: 100%; margin-bottom: 6px;"
              />
              <div style="display: flex; justify-content: space-between; font-size: 0.72rem;">
                <a href="#" @click.prevent="selectAllColumns" style="color: var(--color-primary); text-decoration: none; font-weight: 600;">Select All</a>
                <span style="color: #cbd5e1;">|</span>
                <a href="#" @click.prevent="resetDefaultColumns" style="color: #64748b; text-decoration: none;">Reset Defaults</a>
              </div>
            </div>

            <div class="column-picker-list">
              <label 
                v-for="col in filteredColumns" 
                :key="col.id" 
                class="column-picker-item"
              >
                <input 
                  type="checkbox" 
                  :checked="isColVisible(col.id)" 
                  @change="toggleColumn(col.id)" 
                  style="accent-color: var(--color-primary); cursor: pointer;"
                />
                <span style="font-size: 0.78rem; color: #334155;">{{ col.label }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Export to Excel (Downloads ALL Data Restricted to Selected Columns) -->
        <button 
          @click="exportToExcel" 
          class="btn btn--secondary btn--sm" 
          :disabled="loading || exportingExcel" 
          style="height: 36px; display: inline-flex; align-items: center; gap: 5px; font-weight: 600; color: #166534; background: #f0fdf4; border-color: #bbf7d0; white-space: nowrap;"
          title="Download all matching records into Excel with selected columns only"
        >
          <span v-if="exportingExcel">⏳ Exporting ({{ pagination.total }})...</span>
          <span v-else>📥 Excel (.xlsx)</span>
        </button>

        <!-- Export to CSV -->
        <button 
          @click="exportToCSV" 
          class="btn btn--secondary btn--sm" 
          :disabled="loading" 
          style="height: 36px; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;"
          title="Download statement as CSV stream"
        >
          📄 CSV
        </button>

        <!-- Print -->
        <button 
          @click="printStatement" 
          class="btn btn--secondary btn--sm" 
          style="height: 36px; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;" 
          title="Print Current Statement"
        >
          🖨️ Print
        </button>

        <!-- Refresh -->
        <button 
          @click="fetchStatement" 
          class="btn btn--primary btn--sm" 
          :disabled="loading" 
          style="height: 36px; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;"
          title="Refresh Statement Data"
        >
          🔄 Refresh
        </button>
      </div>
    </div>

    <!-- Backdrop to close column dropdown when clicking outside -->
    <div v-if="showColumnPicker" class="column-picker-backdrop" @click="showColumnPicker = false"></div>

    <!-- Filters & Search Toolbar -->
    <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg);">
      <!-- Row 1: Primary Search Input (Full Width for Premium Search Experience) -->
      <div style="margin-bottom: 0.75rem; position: relative;">
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search by Order #, Customer Name, Phone, Email, City, State, SKU..." 
          class="form-input" 
          style="padding-left: 2.2rem; width: 100%; height: 38px; font-size: 0.86rem; border-radius: 8px;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); font-size: 0.95rem;">🔍</span>
        <button 
          v-if="filters.search" 
          @click="filters.search = ''; onFiltersChanged()" 
          style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--color-text-muted);"
        >
          ✕
        </button>
      </div>

      <!-- Row 2: Filter Controls Grid & Reset Button (Perfect Single/Flex Alignment) -->
      <div style="display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center; justify-content: space-between;">
        <!-- Left Filter Group -->
        <div style="display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center;">
          <!-- Order Status Dropdown -->
          <div style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600; white-space: nowrap;">Status:</label>
            <select v-model="filters.order_status" @change="onFiltersChanged" class="form-input" style="min-width: 120px; height: 34px; padding: 0.25rem 0.5rem; font-size: 0.82rem;">
              <option value="all">All Statuses</option>
              <option value="order_placed">Order Placed</option>
              <option value="order_confirmed">Order Confirmed</option>
              <option value="processing">Processing</option>
              <option value="ready_to_ship">Ready to Ship</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
              <option value="returned">Returned</option>
              <option value="refunded">Refunded</option>
            </select>
          </div>

          <!-- Payment Status Dropdown -->
          <div style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600; white-space: nowrap;">Payment:</label>
            <select v-model="filters.payment_status" @change="onFiltersChanged" class="form-input" style="min-width: 115px; height: 34px; padding: 0.25rem 0.5rem; font-size: 0.82rem;">
              <option value="all">All Payments</option>
              <option value="paid">Paid / Captured</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
              <option value="refunded">Refunded</option>
            </select>
          </div>

          <!-- Payment Method Dropdown -->
          <div style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600; white-space: nowrap;">Method:</label>
            <select v-model="filters.payment_method" @change="onFiltersChanged" class="form-input" style="min-width: 110px; height: 34px; padding: 0.25rem 0.5rem; font-size: 0.82rem;">
              <option value="all">All Methods</option>
              <option value="upi">UPI</option>
              <option value="card">Cards</option>
              <option value="netbanking">Net Banking</option>
              <option value="cashfree">Online (Cashfree)</option>
              <option value="cod">Cash on Delivery</option>
            </select>
          </div>

          <!-- Sort Dropdown -->
          <div style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600; white-space: nowrap;">Sort:</label>
            <select v-model="filters.sort_by" @change="onFiltersChanged" class="form-input" style="min-width: 120px; height: 34px; padding: 0.25rem 0.5rem; font-size: 0.82rem;">
              <option value="date_desc">Newest First</option>
              <option value="date_asc">Oldest First</option>
              <option value="amount_desc">Amount: High to Low</option>
              <option value="amount_asc">Amount: Low to High</option>
              <option value="items_desc">Most Items</option>
            </select>
          </div>

          <!-- No of Records List Option -->
          <div style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600; white-space: nowrap;">Show:</label>
            <select v-model="filters.per_page" @change="onPerPageChanged" class="form-input" style="min-width: 105px; height: 34px; padding: 0.25rem 0.5rem; font-size: 0.82rem;" title="Select number of records to display per page">
              <option :value="15">15 records</option>
              <option :value="25">25 records</option>
              <option :value="50">50 records</option>
              <option :value="100">100 records</option>
              <option :value="200">200 records</option>
              <option value="all">All records</option>
            </select>
          </div>
        </div>

        <!-- Right Side: Reset Button -->
        <button @click="resetFilters" class="btn btn--secondary btn--sm" title="Clear all filters" style="height: 34px; padding: 0 0.85rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
          ↺ Reset
        </button>
      </div>

      <!-- Date Presets & Custom Pickers Row -->
      <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: var(--spacing-md); margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--color-border);">
        <!-- Date Preset Pills -->
        <div style="display: flex; gap: 0.35rem; overflow-x: auto; align-items: center; max-width: 100%; padding-bottom: 2px;">
          <span style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600; margin-right: 4px;">Period:</span>
          <button 
            v-for="preset in datePresets" 
            :key="preset.id"
            type="button" 
            :class="['btn btn--sm', filters.date_preset === preset.id ? 'btn--primary' : 'btn--secondary']"
            @click="selectDatePreset(preset.id)"
            style="border-radius: 14px; height: 26px; font-size: 0.74rem; padding: 0 9px; white-space: nowrap;"
          >
            {{ preset.label }}
          </button>
        </div>

        <!-- Custom Date Range Inputs -->
        <div v-if="filters.date_preset === 'custom'" style="display: flex; align-items: center; gap: var(--spacing-sm); flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 4px;">
            <label style="font-size: 0.75rem; color: var(--color-text-muted);">From:</label>
            <input type="date" v-model="filters.start_date" class="form-input" style="padding: 0.2rem 0.5rem; width: 135px; font-size: 0.8rem;" />
          </div>
          <div style="display: flex; align-items: center; gap: 4px;">
            <label style="font-size: 0.75rem; color: var(--color-text-muted);">To:</label>
            <input type="date" v-model="filters.end_date" class="form-input" style="padding: 0.2rem 0.5rem; width: 135px; font-size: 0.8rem;" />
          </div>
          <button class="btn btn--secondary btn--sm" @click="onFiltersChanged">Apply</button>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: var(--spacing-md); padding: 0.85rem; width: 100%; border-radius: 8px; font-size: 0.88rem; display: flex; align-items: center; justify-content: space-between;">
      <span>⚠️ {{ errorMsg }}</span>
      <button @click="errorMsg = ''" style="background: none; border: none; cursor: pointer; color: inherit; font-size: 1rem;">✕</button>
    </div>

    <!-- Loading Spinner -->
    <div v-if="loading" style="text-align: center; padding: 4rem;">
      <div class="stat-card__value" style="font-size: 1.25rem;">Compiling Sales Statement Ledger...</div>
      <span style="font-size: 0.85rem; color: var(--color-text-muted);">Calculating tax schedules and aggregating transactions</span>
    </div>

    <!-- Statement Table / Records List -->
    <div v-else class="glass-panel" style="overflow: hidden; max-width: 100%; min-width: 0;">
      
      <!-- Mobile Data Cards View -->
      <div class="mobile-data-list">
        <div class="mobile-data-card" v-for="(order, index) in orders" :key="order.id">
          <div class="mdc-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
              <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-primary); background: rgba(74, 14, 46, 0.08); border: 1px solid rgba(74, 14, 46, 0.15); padding: 2px 6px; border-radius: 4px;">
                #{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
              </span>
              <router-link :to="`/admin/orders/${order.id}`" class="mdc-title" style="color: var(--color-primary); font-weight: bold;">
                {{ order.order_number }}
              </router-link>
            </div>
            <span class="mdc-date">{{ order.created_at_display }}</span>
          </div>

          <div class="mdc-body">
            <div class="mdc-customer">
              <span class="mdc-name">{{ order.customer.name }}</span>
              <span class="mdc-email">{{ order.customer.phone }} • {{ order.customer.city }}, {{ order.customer.state }}</span>
            </div>

            <!-- Items summary badge & toggle -->
            <div style="margin-top: 0.4rem; display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 0.8rem; color: var(--color-text-secondary); font-weight: 500;">
                🛍️ {{ order.financials.total_items }} Items Sold
              </span>
              <button @click="toggleRowExpand(order.id)" class="btn btn--secondary btn--sm" style="padding: 2px 8px; font-size: 0.72rem;">
                {{ isRowExpanded(order.id) ? '▲ Hide Items' : '▼ Show Items' }}
              </button>
            </div>

            <!-- Mobile Financials Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; margin-top: 0.4rem; padding: 0.5rem; background: #faf8f5; border-radius: 6px; font-size: 0.78rem;">
              <div v-if="isColVisible('subtotal')">Subtotal: <strong>₹{{ formatCurrency(order.financials.subtotal) }}</strong></div>
              <div v-if="isColVisible('discount')">Discount: <strong style="color: var(--color-danger);">-₹{{ formatCurrency(order.financials.discount_amount) }}</strong></div>
              <div v-if="isColVisible('tax')">GST Tax: <strong>₹{{ formatCurrency(order.financials.tax_amount) }}</strong></div>
              <div v-if="isColVisible('shipping')">Shipping: <strong>₹{{ formatCurrency(order.financials.shipping_amount) }}</strong></div>
            </div>

            <div v-if="isColVisible('grand_total')" style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--color-border);">
              <span style="font-size: 0.8rem; color: var(--color-text-muted);">Grand Total:</span>
              <span style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary);">
                ₹{{ formatCurrency(order.financials.grand_total) }}
              </span>
            </div>
          </div>

          <!-- Expanded Items for Mobile -->
          <div v-if="isRowExpanded(order.id)" style="padding: 0.6rem; border-top: 1px solid var(--color-border); background: #ffffff;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.4rem; text-transform: uppercase;">
              Itemized Products Detail
            </div>
            <div v-for="item in order.items" :key="item.id" style="display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0; border-bottom: 1px dashed #f0f0f0; font-size: 0.78rem;">
              <div>
                <div style="font-weight: 600;">{{ item.product_name }}</div>
                <div style="color: var(--color-text-muted); font-size: 0.72rem;">SKU: {{ item.sku }} • Qty: {{ item.quantity }} × ₹{{ formatCurrency(item.unit_price) }}</div>
              </div>
              <div style="font-weight: 700; color: var(--color-primary);">
                ₹{{ formatCurrency(item.total_price) }}
              </div>
            </div>
          </div>

          <div class="mdc-footer">
            <div class="mdc-badges">
              <span v-if="isColVisible('order_status')" :class="['badge', getOrderStatusBadgeClass(order.status)]">
                {{ order.status_label }}
              </span>
              <span v-if="isColVisible('payment_status')" :class="['badge', getPaymentBadgeClass(order.payment_status)]">
                {{ order.payment_status }}
              </span>
              <span v-if="isColVisible('payment_method')" style="font-size: 0.7rem; color: var(--color-text-muted); text-transform: uppercase;">
                {{ order.payment_method }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="orders.length === 0" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
          No sales transactions found matching the selected filters.
        </div>
      </div>

      <!-- Desktop Statement Table View -->
      <div class="table-responsive desktop-statement-wrapper" style="width: 100%; max-width: 100%; min-width: 0; overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="data-table desktop-data-table statement-table">
          <thead>
            <tr>
              <!-- Row Expander Toggle Header -->
              <th style="width: 40px; text-align: center;"></th>

              <!-- Dynamic Visible Columns -->
              <th v-if="isColVisible('sno')" style="width: 50px; text-align: center;">#</th>
              <th v-if="isColVisible('date')">Date & Time</th>
              <th v-if="isColVisible('order_number')">Order Number</th>
              <th v-if="isColVisible('customer')">Customer</th>
              <th v-if="isColVisible('customer_phone')">Phone</th>
              <th v-if="isColVisible('customer_email')">Email</th>
              <th v-if="isColVisible('location')">Location</th>
              <th v-if="isColVisible('items')" style="text-align: center;">Items</th>
              <th v-if="isColVisible('gross_mrp')" style="text-align: right;">Gross MRP</th>
              <th v-if="isColVisible('discount')" style="text-align: right;">Discount</th>
              <th v-if="isColVisible('subtotal')" style="text-align: right;">Subtotal</th>
              <th v-if="isColVisible('tax')" style="text-align: right;" title="CGST + SGST or IGST">GST Tax</th>
              <th v-if="isColVisible('cgst')" style="text-align: right;">CGST</th>
              <th v-if="isColVisible('sgst')" style="text-align: right;">SGST</th>
              <th v-if="isColVisible('igst')" style="text-align: right;">IGST</th>
              <th v-if="isColVisible('shipping')" style="text-align: right;">Shipping</th>
              <th v-if="isColVisible('grand_total')" style="text-align: right;">Grand Total</th>
              <th v-if="isColVisible('payment_method')">Payment Method</th>
              <th v-if="isColVisible('payment_status')">Payment Status</th>
              <th v-if="isColVisible('order_status')">Order Status</th>
              <th v-if="isColVisible('courier')">Courier</th>
              <th v-if="isColVisible('tracking')">Tracking #</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(order, index) in orders" :key="order.id">
              <!-- Master Order Row -->
              <tr :class="{ 'row-expanded': isRowExpanded(order.id) }">
                <!-- Expand toggle button -->
                <td style="text-align: center; width: 40px; padding: 0.5rem 0.25rem;">
                  <button 
                    @click="toggleRowExpand(order.id)" 
                    class="btn-expand" 
                    :title="isRowExpanded(order.id) ? 'Collapse items' : 'Expand itemized list'"
                  >
                    {{ isRowExpanded(order.id) ? '−' : '+' }}
                  </button>
                </td>

                <!-- S.No -->
                <td v-if="isColVisible('sno')" style="text-align: center; font-weight: 600; color: var(--color-text-secondary); font-size: 0.82rem;">
                  {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
                </td>

                <!-- Date & Time -->
                <td v-if="isColVisible('date')" style="white-space: nowrap; font-size: 0.82rem;">
                  <div style="font-weight: 500; color: #1e293b;">{{ order.created_at_display }}</div>
                </td>

                <!-- Order Number Link -->
                <td v-if="isColVisible('order_number')" style="white-space: nowrap;">
                  <router-link :to="`/admin/orders/${order.id}`" class="order-link-badge" title="View Full Order Admin Details">
                    {{ order.order_number }}
                  </router-link>
                  <div v-if="!isColVisible('courier') && order.logistics.courier_name !== '—'" style="font-size: 0.7rem; color: var(--color-text-muted); margin-top: 2px;">
                    🚚 {{ order.logistics.courier_name }}
                  </div>
                </td>

                <!-- Customer Details -->
                <td v-if="isColVisible('customer')">
                  <div style="display: flex; flex-direction: column; min-width: 130px;">
                    <span style="font-weight: 600; color: #1e293b; font-size: 0.84rem;">{{ order.customer.name }}</span>
                    <span v-if="!isColVisible('customer_phone')" style="font-size: 0.74rem; color: var(--color-text-muted);">{{ order.customer.phone }}</span>
                  </div>
                </td>

                <!-- Customer Phone -->
                <td v-if="isColVisible('customer_phone')" style="font-size: 0.8rem; color: #334155; white-space: nowrap;">
                  {{ order.customer.phone }}
                </td>

                <!-- Customer Email -->
                <td v-if="isColVisible('customer_email')" style="font-size: 0.78rem; color: #64748b;">
                  {{ order.customer.email }}
                </td>

                <!-- Customer Location -->
                <td v-if="isColVisible('location')" style="font-size: 0.8rem; color: #475569; white-space: nowrap;">
                  {{ order.customer.city }}, {{ order.customer.state }}
                </td>

                <!-- Items Count -->
                <td v-if="isColVisible('items')" style="text-align: center;">
                  <span class="badge badge--secondary" style="font-size: 0.75rem; cursor: pointer;" @click="toggleRowExpand(order.id)" title="Click to inspect items">
                    {{ order.financials.total_items }} pcs
                  </span>
                </td>

                <!-- Gross MRP -->
                <td v-if="isColVisible('gross_mrp')" style="text-align: right; color: var(--color-text-muted); font-size: 0.82rem;">
                  ₹{{ formatCurrency(order.financials.gross_mrp) }}
                </td>

                <!-- Discount Amount -->
                <td v-if="isColVisible('discount')" style="text-align: right; color: var(--color-danger); font-size: 0.82rem; font-weight: 500;">
                  <span v-if="order.financials.discount_amount > 0">-₹{{ formatCurrency(order.financials.discount_amount) }}</span>
                  <span v-else style="color: var(--color-text-muted);">₹0.00</span>
                </td>

                <!-- Subtotal -->
                <td v-if="isColVisible('subtotal')" style="text-align: right; font-weight: 600; color: #1e293b; font-size: 0.82rem;">
                  ₹{{ formatCurrency(order.financials.subtotal) }}
                </td>

                <!-- Tax Amount with Breakdown hover -->
                <td v-if="isColVisible('tax')" style="text-align: right; font-size: 0.82rem;">
                  <span :title="`CGST: ₹${order.financials.cgst_amount} | SGST: ₹${order.financials.sgst_amount} | IGST: ₹${order.financials.igst_amount}`" style="cursor: help; border-bottom: 1px dotted #94a3b8;">
                    ₹{{ formatCurrency(order.financials.tax_amount) }}
                  </span>
                </td>

                <!-- CGST Amount -->
                <td v-if="isColVisible('cgst')" style="text-align: right; font-size: 0.82rem; color: #475569;">
                  ₹{{ formatCurrency(order.financials.cgst_amount) }}
                </td>

                <!-- SGST Amount -->
                <td v-if="isColVisible('sgst')" style="text-align: right; font-size: 0.82rem; color: #475569;">
                  ₹{{ formatCurrency(order.financials.sgst_amount) }}
                </td>

                <!-- IGST Amount -->
                <td v-if="isColVisible('igst')" style="text-align: right; font-size: 0.82rem; color: #475569;">
                  ₹{{ formatCurrency(order.financials.igst_amount) }}
                </td>

                <!-- Shipping Fee -->
                <td v-if="isColVisible('shipping')" style="text-align: right; font-size: 0.82rem; color: var(--color-text-secondary);">
                  ₹{{ formatCurrency(order.financials.shipping_amount) }}
                </td>

                <!-- Grand Total -->
                <td v-if="isColVisible('grand_total')" style="text-align: right; font-size: 0.95rem; font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                  ₹{{ formatCurrency(order.financials.grand_total) }}
                </td>

                <!-- Payment Method -->
                <td v-if="isColVisible('payment_method')" style="white-space: nowrap; font-size: 0.78rem; text-transform: uppercase; font-weight: 600; color: var(--color-text-muted);">
                  {{ order.payment_method }}
                </td>

                <!-- Payment Status -->
                <td v-if="isColVisible('payment_status')" style="white-space: nowrap;">
                  <span :class="['badge', getPaymentBadgeClass(order.payment_status)]" style="font-size: 0.72rem; padding: 2px 6px;">
                    {{ order.payment_status }}
                  </span>
                </td>

                <!-- Order Status -->
                <td v-if="isColVisible('order_status')" style="white-space: nowrap;">
                  <span :class="['badge', getOrderStatusBadgeClass(order.status)]" style="font-size: 0.72rem; padding: 2px 6px;">
                    {{ order.status_label }}
                  </span>
                </td>

                <!-- Courier Partner -->
                <td v-if="isColVisible('courier')" style="font-size: 0.78rem; color: #475569; white-space: nowrap;">
                  {{ order.logistics.courier_name }}
                </td>

                <!-- Tracking Number -->
                <td v-if="isColVisible('tracking')" style="font-size: 0.78rem; font-family: monospace; white-space: nowrap;">
                  {{ order.logistics.tracking_number }}
                </td>
              </tr>

              <!-- Detailed Itemized Row (Expandable) -->
              <tr v-if="isRowExpanded(order.id)" class="itemized-nested-row">
                <td :colspan="visibleColumnCount + 1" style="padding: 0; background: #fffcf7;">
                  <div class="nested-items-container">
                    <div class="nested-items-header">
                      <div style="font-weight: 700; font-size: 0.82rem; color: var(--color-primary); display: flex; align-items: center; gap: 6px;">
                        <span>📦</span>
                        <span>Itemized Products Breakdown for Order {{ order.order_number }}</span>
                        <span style="font-weight: normal; color: var(--color-text-muted); font-size: 0.75rem;">({{ order.items.length }} line items)</span>
                      </div>
                      <div style="font-size: 0.76rem; color: var(--color-text-muted);">
                        Shipping Address: {{ order.customer.full_address }}
                      </div>
                    </div>

                    <table class="nested-items-table">
                      <thead>
                        <tr>
                          <th style="width: 45px;"></th>
                          <th>Product Name & Description</th>
                          <th>SKU Identifier</th>
                          <th>Variant</th>
                          <th style="text-align: right;">Unit MRP</th>
                          <th style="text-align: right;">Unit Selling Price</th>
                          <th style="text-align: center;">Quantity</th>
                          <th style="text-align: right;">Discount</th>
                          <th style="text-align: right;">Tax Amount</th>
                          <th style="text-align: right;">Item Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in order.items" :key="item.id">
                          <td style="width: 45px; text-align: center;">
                            <img v-if="item.image_url" :src="item.image_url" alt="" style="width: 34px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-border);" />
                            <div v-else style="width: 34px; height: 42px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #999;">👗</div>
                          </td>
                          <td style="font-weight: 600; color: #1e293b;">
                            {{ item.product_name }}
                          </td>
                          <td>
                            <code style="font-size: 0.75rem; background: rgba(0,0,0,0.04); padding: 2px 4px; border-radius: 3px;">{{ item.sku }}</code>
                          </td>
                          <td style="color: var(--color-text-secondary); font-size: 0.78rem;">
                            {{ item.variant_name || 'Standard' }}
                          </td>
                          <td style="text-align: right; color: var(--color-text-muted);">
                            ₹{{ formatCurrency(item.unit_mrp) }}
                          </td>
                          <td style="text-align: right; font-weight: 500;">
                            ₹{{ formatCurrency(item.unit_price) }}
                          </td>
                          <td style="text-align: center; font-weight: 700; color: var(--color-primary);">
                            {{ item.quantity }}
                          </td>
                          <td style="text-align: right; color: var(--color-danger);">
                            <span v-if="item.discount > 0">-₹{{ formatCurrency(item.discount) }}</span>
                            <span v-else>₹0.00</span>
                          </td>
                          <td style="text-align: right; color: #b45309;">
                            ₹{{ formatCurrency(item.tax_amount) }}
                          </td>
                          <td style="text-align: right; font-weight: 700; color: var(--color-primary);">
                            ₹{{ formatCurrency(item.total_price) }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </td>
              </tr>
            </template>

            <!-- Empty State -->
            <tr v-if="orders.length === 0">
              <td :colspan="visibleColumnCount + 1" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📑</div>
                <div style="font-weight: 600; font-size: 1.1rem; color: #1e293b;">No Sales Transactions Found</div>
                <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0.25rem;">
                  Try adjusting the date range or clearing the filter options to view historical statements.
                </p>
                <button @click="resetFilters" class="btn btn--secondary btn--sm" style="margin-top: 1rem;">
                  Reset All Filters
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer with Number of Records per Page -->
      <div v-if="pagination.total > 0" class="statement-pagination-bar">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
          <div style="font-size: 0.82rem; color: var(--color-text-muted);">
            Showing <strong>{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</strong> to 
            <strong>{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</strong> of 
            <strong>{{ pagination.total }}</strong> sales orders (Page {{ pagination.current_page }} of {{ pagination.last_page }})
          </div>

          <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--color-text-secondary);">
            <span>Records per page:</span>
            <select v-model="filters.per_page" @change="onPerPageChanged" class="form-input" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; width: auto;">
              <option :value="15">15</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
              <option :value="200">200</option>
              <option value="all">All</option>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 0.35rem; align-items: center;">
          <button 
            class="btn btn--secondary btn--sm" 
            :disabled="pagination.current_page <= 1"
            @click="changePage(1)"
            title="First Page"
          >
            « First
          </button>
          <button 
            class="btn btn--secondary btn--sm" 
            :disabled="pagination.current_page <= 1"
            @click="changePage(pagination.current_page - 1)"
          >
            ← Previous
          </button>
          
          <button 
            v-for="page in visiblePageNumbers" 
            :key="page"
            :class="['btn btn--sm', page === pagination.current_page ? 'btn--primary' : 'btn--secondary']"
            style="min-width: 32px; padding: 0 6px;"
            @click="changePage(page)"
          >
            {{ page }}
          </button>

          <button 
            class="btn btn--secondary btn--sm" 
            :disabled="pagination.current_page >= pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
          >
            Next →
          </button>
          <button 
            class="btn btn--secondary btn--sm" 
            :disabled="pagination.current_page >= pagination.last_page"
            @click="changePage(pagination.last_page)"
            title="Last Page"
          >
            Last »
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import XLSX from 'xlsx-js-style';

// Data States
const loading = ref(false);
const exportingExcel = ref(false);
const errorMsg = ref('');
const orders = ref([]);
const expandedRowIds = ref(new Set());

// Column Visibility State
const showColumnPicker = ref(false);
const columnFilterText = ref('');

const availableColumns = [
  { id: 'sno', label: 'S.No', default: true },
  { id: 'date', label: 'Date & Time', default: true },
  { id: 'order_number', label: 'Order Number', default: true },
  { id: 'customer', label: 'Customer Name', default: true },
  { id: 'customer_phone', label: 'Customer Phone', default: true },
  { id: 'customer_email', label: 'Customer Email', default: false },
  { id: 'location', label: 'Shipping City & State', default: true },
  { id: 'items', label: 'Items Count', default: true },
  { id: 'gross_mrp', label: 'Gross MRP (₹)', default: true },
  { id: 'discount', label: 'Discount Amount (₹)', default: true },
  { id: 'subtotal', label: 'Net Subtotal (₹)', default: true },
  { id: 'tax', label: 'Total GST Tax (₹)', default: true },
  { id: 'cgst', label: 'CGST (₹)', default: false },
  { id: 'sgst', label: 'SGST (₹)', default: false },
  { id: 'igst', label: 'IGST (₹)', default: false },
  { id: 'shipping', label: 'Shipping Fee (₹)', default: true },
  { id: 'grand_total', label: 'Grand Total (₹)', default: true },
  { id: 'payment_method', label: 'Payment Method', default: true },
  { id: 'payment_status', label: 'Payment Status', default: true },
  { id: 'order_status', label: 'Order Status', default: true },
  { id: 'courier', label: 'Courier Partner', default: false },
  { id: 'tracking', label: 'Tracking Number', default: false },
];

const defaultVisibleIds = availableColumns.filter(c => c.default).map(c => c.id);
const storedCols = localStorage.getItem('msf_sales_statement_columns');
const visibleColumnIds = ref(new Set(storedCols ? JSON.parse(storedCols) : defaultVisibleIds));

const isColVisible = (id) => visibleColumnIds.value.has(id);

const toggleColumn = (id) => {
  if (visibleColumnIds.value.has(id)) {
    if (visibleColumnIds.value.size <= 1) {
      alert('At least one column must remain visible.');
      return;
    }
    visibleColumnIds.value.delete(id);
  } else {
    visibleColumnIds.value.add(id);
  }
  visibleColumnIds.value = new Set(visibleColumnIds.value);
  saveColumnPreferences();
};

const selectAllColumns = () => {
  visibleColumnIds.value = new Set(availableColumns.map(c => c.id));
  saveColumnPreferences();
};

const resetDefaultColumns = () => {
  visibleColumnIds.value = new Set(defaultVisibleIds);
  saveColumnPreferences();
};

const saveColumnPreferences = () => {
  localStorage.setItem('msf_sales_statement_columns', JSON.stringify(Array.from(visibleColumnIds.value)));
};

const visibleColumnCount = computed(() => visibleColumnIds.value.size);

const filteredColumns = computed(() => {
  if (!columnFilterText.value.trim()) return availableColumns;
  const q = columnFilterText.value.toLowerCase();
  return availableColumns.filter(c => c.label.toLowerCase().includes(q));
});

// Financial Summary Object
const summary = ref({
  total_orders: 0,
  total_units_sold: 0,
  gross_sales: 0,
  total_discounts: 0,
  net_sales: 0,
  total_tax: 0,
  total_cgst: 0,
  total_sgst: 0,
  total_igst: 0,
  total_shipping: 0,
  grand_total: 0,
  average_order_value: 0,
  paid_revenue: 0,
  paid_count: 0,
  pending_revenue: 0,
  pending_count: 0,
  cancelled_revenue: 0,
  cancelled_count: 0,
  status_breakdown: [],
  payment_breakdown: [],
});

// Pagination State
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 25,
  total: 0,
});

// Filter Criteria
const filters = reactive({
  search: '',
  date_preset: 'this_month',
  start_date: '',
  end_date: '',
  order_status: 'all',
  payment_status: 'all',
  payment_method: 'all',
  sort_by: 'date_desc',
  per_page: 25,
});

// Preset Timeframes
const datePresets = [
  { id: 'today', label: 'Today' },
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'this_week', label: 'This Week' },
  { id: 'last_7_days', label: 'Last 7 Days' },
  { id: 'this_month', label: 'This Month' },
  { id: 'last_month', label: 'Last Month' },
  { id: 'this_quarter', label: 'This Quarter' },
  { id: 'this_year', label: 'This Year' },
  { id: 'all_time', label: 'All Time' },
  { id: 'custom', label: 'Custom Range' },
];

// Debounce Timer
let searchDebounceTimeout = null;

const debounceSearch = () => {
  clearTimeout(searchDebounceTimeout);
  searchDebounceTimeout = setTimeout(() => {
    onFiltersChanged();
  }, 400);
};

const selectDatePreset = (presetId) => {
  filters.date_preset = presetId;
  if (presetId !== 'custom') {
    onFiltersChanged();
  }
};

const onFiltersChanged = () => {
  pagination.current_page = 1;
  fetchStatement();
};

const onPerPageChanged = () => {
  pagination.current_page = 1;
  fetchStatement();
};

const resetFilters = () => {
  filters.search = '';
  filters.date_preset = 'this_month';
  filters.start_date = '';
  filters.end_date = '';
  filters.order_status = 'all';
  filters.payment_status = 'all';
  filters.payment_method = 'all';
  filters.sort_by = 'date_desc';
  filters.per_page = 25;
  pagination.current_page = 1;
  fetchStatement();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    pagination.current_page = page;
    fetchStatement();
  }
};

// Row Expanders
const toggleRowExpand = (id) => {
  if (expandedRowIds.value.has(id)) {
    expandedRowIds.value.delete(id);
  } else {
    expandedRowIds.value.add(id);
  }
  expandedRowIds.value = new Set(expandedRowIds.value);
};

const isRowExpanded = (id) => {
  return expandedRowIds.value.has(id);
};

// Format Helpers
const formatCurrency = (val) => {
  const num = parseFloat(val);
  if (isNaN(num)) return '0.00';
  return num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getPaymentBadgeClass = (status) => {
  const s = String(status || '').toLowerCase();
  if (s === 'paid' || s === 'captured' || s === 'success') return 'badge--success';
  if (s === 'pending') return 'badge--warning';
  if (s === 'failed') return 'badge--danger';
  if (s === 'refunded') return 'badge--secondary';
  return 'badge--secondary';
};

const getOrderStatusBadgeClass = (status) => {
  const s = String(status || '').toLowerCase();
  if (s === 'delivered') return 'badge--success';
  if (s === 'shipped' || s === 'ready_to_ship') return 'badge--primary';
  if (s === 'processing' || s === 'order_confirmed') return 'badge--secondary';
  if (s === 'order_placed' || s === 'pending') return 'badge--warning';
  if (s === 'cancelled' || s === 'returned') return 'badge--danger';
  return 'badge--secondary';
};

// Visible Pagination Range
const visiblePageNumbers = computed(() => {
  const pages = [];
  const current = pagination.current_page;
  const last = pagination.last_page;
  const delta = 2;

  for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
    pages.push(i);
  }
  return pages;
});

// Fetch Statement API
const fetchStatement = async () => {
  loading.value = true;
  errorMsg.value = '';

  try {
    const params = {
      search: filters.search || undefined,
      date_preset: filters.date_preset,
      start_date: filters.date_preset === 'custom' ? filters.start_date : undefined,
      end_date: filters.date_preset === 'custom' ? filters.end_date : undefined,
      order_status: filters.order_status,
      payment_status: filters.payment_status,
      payment_method: filters.payment_method,
      sort_by: filters.sort_by,
      page: pagination.current_page,
      per_page: filters.per_page,
    };

    const res = await axios.get('/api/admin/reports/sales-statement', { params });
    if (res.data?.success) {
      orders.value = res.data.data || [];
      if (res.data.summary) {
        summary.value = res.data.summary;
      }
      if (res.data.meta) {
        pagination.current_page = res.data.meta.current_page;
        pagination.last_page = res.data.meta.last_page;
        pagination.per_page = res.data.meta.per_page;
        pagination.total = res.data.meta.total;
      }
    } else {
      errorMsg.value = res.data?.message || 'Failed to load sales statement report.';
    }
  } catch (err) {
    console.error('fetchStatement error:', err);
    errorMsg.value = err.response?.data?.message || 'Server error while fetching sales statement report.';
  } finally {
    loading.value = false;
  }
};

const printStatement = () => {
  window.print();
};

// Export to CSV directly via backend stream
const exportToCSV = () => {
  const params = new URLSearchParams();
  if (filters.search) params.append('search', filters.search);
  params.append('date_preset', filters.date_preset);
  if (filters.date_preset === 'custom') {
    if (filters.start_date) params.append('start_date', filters.start_date);
    if (filters.end_date) params.append('end_date', filters.end_date);
  }
  if (filters.order_status) params.append('order_status', filters.order_status);
  if (filters.payment_status) params.append('payment_status', filters.payment_status);
  if (filters.payment_method) params.append('payment_method', filters.payment_method);
  if (filters.sort_by) params.append('sort_by', filters.sort_by);

  const exportUrl = `/api/admin/reports/sales-statement/export?${params.toString()}`;

  axios({
    url: exportUrl,
    method: 'GET',
    responseType: 'blob',
  }).then((response) => {
    const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Sales_Statement_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  }).catch((err) => {
    console.error('CSV Export failed:', err);
    alert('Failed to download CSV export. Please try again.');
  });
};

// Export to Rich Multi-Sheet Excel Workbook (.xlsx)
// Downloads ALL Data, restricted to selected/visible columns only!
const exportToExcel = async () => {
  exportingExcel.value = true;
  try {
    // 1. Fetch ALL records for active filters (bypassing page limit)
    const params = {
      search: filters.search || undefined,
      date_preset: filters.date_preset,
      start_date: filters.date_preset === 'custom' ? filters.start_date : undefined,
      end_date: filters.date_preset === 'custom' ? filters.end_date : undefined,
      order_status: filters.order_status,
      payment_status: filters.payment_status,
      payment_method: filters.payment_method,
      sort_by: filters.sort_by,
      per_page: 'all', // Instructs backend to fetch all matching records
    };

    const res = await axios.get('/api/admin/reports/sales-statement', { params });
    const allOrders = res.data?.data || orders.value;
    const sm = res.data?.summary || summary.value;

    const wb = XLSX.utils.book_new();

    // ================= SHEET 1: Executive Statement Summary =================
    const summaryRows = [
      ['MAYA SREE FASHION - SALES STATEMENT REPORT'],
      ['Generated On', new Date().toLocaleString()],
      ['Timeframe Preset', sm.date_preset || filters.date_preset],
      ['Date Range', `${sm.start_date || 'Start'} to ${sm.end_date || 'Present'}`],
      ['Order Status Filter', filters.order_status],
      ['Payment Status Filter', filters.payment_status],
      ['Payment Method Filter', filters.payment_method],
      ['Search Query', filters.search || 'None'],
      ['Total Records in Statement', allOrders.length],
      [],
      ['FINANCIAL & OPERATIONAL SUMMARY'],
      ['Metric Name', 'Value (INR / Count)'],
      ['Total Orders Volume', sm.total_orders || 0],
      ['Total Units / Items Sold', sm.total_units_sold || 0],
      ['Gross Sales (MRP Total)', sm.gross_sales || 0],
      ['Total Discounts & Coupons', sm.total_discounts || 0],
      ['Taxable Net Sales (Subtotal)', sm.net_sales || 0],
      ['Central GST (CGST)', sm.total_cgst || 0],
      ['State GST (SGST)', sm.total_sgst || 0],
      ['Integrated GST (IGST)', sm.total_igst || 0],
      ['Total GST Taxes Collected', sm.total_tax || 0],
      ['Total Shipping Fee Collected', sm.total_shipping || 0],
      ['GRAND TOTAL NET REVENUE', sm.grand_total || 0],
      ['Average Order Value (AOV)', sm.average_order_value || 0],
      ['Confirmed Paid Collections', sm.paid_revenue || 0],
      ['Paid Orders Volume', sm.paid_count || 0],
      ['Pending Collections', sm.pending_revenue || 0],
      ['Pending Orders Volume', sm.pending_count || 0],
      ['Cancelled Orders Volume', sm.cancelled_count || 0],
      ['Cancelled Revenue Value', sm.cancelled_revenue || 0],
    ];

    const wsSummary = XLSX.utils.aoa_to_sheet(summaryRows);
    wsSummary['!cols'] = [{ wch: 32 }, { wch: 28 }];
    if (wsSummary['A1']) wsSummary['A1'].s = { font: { bold: true, sz: 12, color: { rgb: '800020' } } };
    if (wsSummary['A11']) wsSummary['A11'].s = { font: { bold: true, sz: 11, color: { rgb: '1E293B' } } };
    if (wsSummary['A12']) wsSummary['A12'].s = { font: { bold: true, sz: 10, color: { rgb: '1E293B' } }, fill: { fgColor: { rgb: 'E2E8F0' } } };
    if (wsSummary['B12']) wsSummary['B12'].s = { font: { bold: true, sz: 10, color: { rgb: '1E293B' } }, fill: { fgColor: { rgb: 'E2E8F0' } } };
    if (wsSummary['A24']) wsSummary['A24'].s = { font: { bold: true, sz: 11, color: { rgb: '000000' } }, fill: { fgColor: { rgb: 'F1F5F9' } }, border: { top: { style: 'thin' }, bottom: { style: 'double' } } };
    if (wsSummary['B24']) wsSummary['B24'].s = { font: { bold: true, sz: 11, color: { rgb: '000000' } }, fill: { fgColor: { rgb: 'F1F5F9' } }, border: { top: { style: 'thin' }, bottom: { style: 'double' } } };
    XLSX.utils.book_append_sheet(wb, wsSummary, 'Statement Summary');

    // ================= SHEET 2: Orders Statement Ledger (RESTRICTED TO SELECTED COLUMNS) =================
    // Filter active export columns based on what the user has currently selected/visible!
    const activeExportCols = availableColumns.filter(c => isColVisible(c.id));
    const orderHeaders = activeExportCols.map(c => c.label);

    const getColValue = (order, colId, idx) => {
      switch (colId) {
        case 'sno': return idx + 1;
        case 'date': return order.created_at;
        case 'order_number': return order.order_number;
        case 'customer': return order.customer.name;
        case 'customer_phone': return order.customer.phone;
        case 'customer_email': return order.customer.email;
        case 'location': return `${order.customer.city}, ${order.customer.state}`;
        case 'items': return order.financials.total_items;
        case 'gross_mrp': return order.financials.gross_mrp;
        case 'discount': return order.financials.discount_amount;
        case 'subtotal': return order.financials.subtotal;
        case 'tax': return order.financials.tax_amount;
        case 'cgst': return order.financials.cgst_amount;
        case 'sgst': return order.financials.sgst_amount;
        case 'igst': return order.financials.igst_amount;
        case 'shipping': return order.financials.shipping_amount;
        case 'grand_total': return order.financials.grand_total;
        case 'payment_method': return order.payment_method;
        case 'payment_status': return order.payment_status;
        case 'order_status': return order.status_label;
        case 'courier': return order.logistics.courier_name;
        case 'tracking': return order.logistics.tracking_number;
        default: return '';
      }
    };

    const orderRows = allOrders.map((o, idx) => {
      return activeExportCols.map(c => getColValue(o, c.id, idx));
    });

    // Calculate Ledger Grand Totals
    const numericTotals = {
      items: allOrders.reduce((sum, o) => sum + (parseInt(o.financials?.total_items) || 0), 0),
      gross_mrp: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.gross_mrp) || 0), 0) * 100) / 100,
      discount: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.discount_amount) || 0), 0) * 100) / 100,
      subtotal: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.subtotal) || 0), 0) * 100) / 100,
      tax: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.tax_amount) || 0), 0) * 100) / 100,
      cgst: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.cgst_amount) || 0), 0) * 100) / 100,
      sgst: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.sgst_amount) || 0), 0) * 100) / 100,
      igst: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.igst_amount) || 0), 0) * 100) / 100,
      shipping: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.shipping_amount) || 0), 0) * 100) / 100,
      grand_total: Math.round(allOrders.reduce((sum, o) => sum + (parseFloat(o.financials?.grand_total) || 0), 0) * 100) / 100,
    };

    let grandTotalLabelPlaced = false;
    const grandTotalRow = activeExportCols.map((c) => {
      if (numericTotals[c.id] !== undefined) {
        return numericTotals[c.id];
      }
      if (!grandTotalLabelPlaced) {
        grandTotalLabelPlaced = true;
        return 'GRAND TOTAL';
      }
      return '';
    });

    const wsOrders = XLSX.utils.aoa_to_sheet([orderHeaders, ...orderRows, grandTotalRow]);
    wsOrders['!cols'] = activeExportCols.map(c => {
      if (['sno', 'items'].includes(c.id)) return { wch: 8 };
      if (['order_number', 'date', 'customer_phone'].includes(c.id)) return { wch: 18 };
      if (['customer', 'customer_email', 'location'].includes(c.id)) return { wch: 24 };
      if (['gross_mrp', 'discount', 'subtotal', 'tax', 'grand_total', 'cgst', 'sgst', 'igst'].includes(c.id)) return { wch: 16 };
      return { wch: 16 };
    });

    // Apply Bold Styling to Header Row (Row 0) and Grand Total Row (Bottom Row) in Sheet 2
    const orderTotalRowIdx = orderRows.length + 1;
    for (let cIdx = 0; cIdx < activeExportCols.length; cIdx++) {
      // Header styling
      const headerCellRef = XLSX.utils.encode_cell({ r: 0, c: cIdx });
      if (wsOrders[headerCellRef]) {
        wsOrders[headerCellRef].s = {
          font: { bold: true, sz: 10, color: { rgb: '1E293B' } },
          fill: { fgColor: { rgb: 'E2E8F0' } },
          alignment: { vertical: 'center' }
        };
      }

      // Grand Total styling: Bold text, light gray background, top border & double bottom border
      const totalCellRef = XLSX.utils.encode_cell({ r: orderTotalRowIdx, c: cIdx });
      if (!wsOrders[totalCellRef]) {
        wsOrders[totalCellRef] = { t: 's', v: '' };
      }
      wsOrders[totalCellRef].s = {
        font: { bold: true, sz: 11, color: { rgb: '000000' } },
        fill: { fgColor: { rgb: 'F1F5F9' } },
        border: {
          top: { style: 'thin', color: { rgb: '94A3B8' } },
          bottom: { style: 'double', color: { rgb: '0F172A' } }
        },
        alignment: {
          horizontal: ['items', 'gross_mrp', 'discount', 'subtotal', 'tax', 'cgst', 'sgst', 'igst', 'shipping', 'grand_total'].includes(activeExportCols[cIdx].id) ? 'right' : 'left',
          vertical: 'center'
        }
      };
    }

    XLSX.utils.book_append_sheet(wb, wsOrders, 'Orders Statement Ledger');

    // ================= SHEET 3: Itemized Sales Breakdown =================
    const itemHeaders = [
      'S.No', 'Order Number', 'Order Date', 'Customer Name', 'Customer Phone',
      'SKU Identifier', 'Product Name', 'Variant', 'Quantity', 'Unit MRP (₹)',
      'Unit Price (₹)', 'Item Discount (₹)', 'Item Tax (₹)', 'Item Total Price (₹)',
      'Order Status', 'Payment Status'
    ];

    const itemRows = [];
    let itemIndex = 1;
    allOrders.forEach(o => {
      (o.items || []).forEach(item => {
        itemRows.push([
          itemIndex++,
          o.order_number,
          o.created_at,
          o.customer.name,
          o.customer.phone,
          item.sku,
          item.product_name,
          item.variant_name || 'Standard',
          item.quantity,
          item.unit_mrp,
          item.unit_price,
          item.discount,
          item.tax_amount,
          item.total_price,
          o.status_label,
          o.payment_status
        ]);
      });
    });

    // Grand Total row for Itemized Breakdown
    const totalItemQty = allOrders.reduce((sum, o) => sum + (o.items || []).reduce((isum, item) => isum + (parseInt(item.quantity) || 0), 0), 0);
    const totalItemDiscount = Math.round(allOrders.reduce((sum, o) => sum + (o.items || []).reduce((isum, item) => isum + (parseFloat(item.discount) || 0), 0), 0) * 100) / 100;
    const totalItemTax = Math.round(allOrders.reduce((sum, o) => sum + (o.items || []).reduce((isum, item) => isum + (parseFloat(item.tax_amount) || 0), 0), 0) * 100) / 100;
    const totalItemPrice = Math.round(allOrders.reduce((sum, o) => sum + (o.items || []).reduce((isum, item) => isum + (parseFloat(item.total_price) || 0), 0), 0) * 100) / 100;

    const itemTotalRow = [
      'GRAND TOTAL', '', '', '', '', '', '', '',
      totalItemQty, '', '',
      totalItemDiscount,
      totalItemTax,
      totalItemPrice,
      '', ''
    ];

    const wsItems = XLSX.utils.aoa_to_sheet([itemHeaders, ...itemRows, itemTotalRow]);
    wsItems['!cols'] = [
      { wch: 14 }, { wch: 18 }, { wch: 19 }, { wch: 22 }, { wch: 15 },
      { wch: 16 }, { wch: 32 }, { wch: 16 }, { wch: 10 }, { wch: 12 },
      { wch: 12 }, { wch: 14 }, { wch: 12 }, { wch: 16 }, { wch: 14 }, { wch: 14 }
    ];

    // Apply Bold Styling to Header Row (Row 0) and Grand Total Row (Bottom Row) in Sheet 3
    const itemTotalRowIdx = itemRows.length + 1;
    for (let cIdx = 0; cIdx < itemHeaders.length; cIdx++) {
      const headerCellRef = XLSX.utils.encode_cell({ r: 0, c: cIdx });
      if (wsItems[headerCellRef]) {
        wsItems[headerCellRef].s = {
          font: { bold: true, sz: 10, color: { rgb: '1E293B' } },
          fill: { fgColor: { rgb: 'E2E8F0' } },
          alignment: { vertical: 'center' }
        };
      }

      const totalCellRef = XLSX.utils.encode_cell({ r: itemTotalRowIdx, c: cIdx });
      if (!wsItems[totalCellRef]) {
        wsItems[totalCellRef] = { t: 's', v: '' };
      }
      wsItems[totalCellRef].s = {
        font: { bold: true, sz: 11, color: { rgb: '000000' } },
        fill: { fgColor: { rgb: 'F1F5F9' } },
        border: {
          top: { style: 'thin', color: { rgb: '94A3B8' } },
          bottom: { style: 'double', color: { rgb: '0F172A' } }
        },
        alignment: {
          horizontal: [8, 11, 12, 13].includes(cIdx) ? 'right' : 'left',
          vertical: 'center'
        }
      };
    }

    XLSX.utils.book_append_sheet(wb, wsItems, 'Itemized Sales Breakdown');

    // Trigger Excel File Download
    const fileDate = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(wb, `Maya_Sree_Sales_Statement_${fileDate}.xlsx`);
  } catch (err) {
    console.error('Excel Export failed:', err);
    alert('Failed to export Excel file. Please try again.');
  } finally {
    exportingExcel.value = false;
  }
};

onMounted(() => {
  fetchStatement();
});
</script>

<style scoped>
.sales-statement-page {
  width: 100%;
}

/* Expand Button */
.btn-expand {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 4px;
  border: 1px solid var(--color-border);
  background: #ffffff;
  color: var(--color-primary);
  font-weight: bold;
  font-size: 1rem;
  line-height: 1;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-expand:hover {
  background: var(--color-primary);
  color: #ffffff;
  border-color: var(--color-primary);
}

.order-link-badge {
  font-weight: 700;
  color: var(--color-primary);
  text-decoration: none;
  font-size: 0.85rem;
}

.order-link-badge:hover {
  text-decoration: underline;
}

/* Row highlight when expanded */
.row-expanded {
  background-color: #fdfbf7 !important;
  border-bottom: none !important;
}

/* Nested Items Container */
.nested-items-container {
  padding: var(--spacing-md);
  background: #fffcf7;
  border-top: 1px dashed var(--color-border);
  border-bottom: 2px solid var(--color-border);
}

.nested-items-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--spacing-sm);
  margin-bottom: var(--spacing-sm);
  padding-bottom: var(--spacing-xs);
  border-bottom: 1px solid rgba(74, 14, 46, 0.1);
}

.nested-items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.78rem;
  background: #ffffff;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
}

.nested-items-table th {
  background: #faf8f5;
  color: #475569;
  font-weight: 600;
  padding: 0.5rem 0.6rem;
  border-bottom: 1px solid var(--color-border);
}

.nested-items-table td {
  padding: 0.5rem 0.6rem;
  border-bottom: 1px solid #f1f5f9;
}

.nested-items-table tbody tr:last-child td {
  border-bottom: none;
}

/* Pagination Bar */
.statement-pagination-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-md);
  border-top: 1px solid var(--color-border);
  flex-wrap: wrap;
  gap: var(--spacing-sm);
}

/* Statement Table Horizontal Scroll & Styling */
.desktop-statement-wrapper {
  width: 100%;
  max-width: 100%;
  min-width: 0;
  overflow-x: auto !important;
  overflow-y: visible;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f8fafc;
}

.desktop-statement-wrapper::-webkit-scrollbar {
  height: 8px;
}

.desktop-statement-wrapper::-webkit-scrollbar-track {
  background: #f8fafc;
  border-radius: 4px;
}

.desktop-statement-wrapper::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.desktop-statement-wrapper::-webkit-scrollbar-thumb:hover {
  background: var(--color-primary);
}

.statement-table {
  width: 100%;
  min-width: max-content;
  border-collapse: collapse;
}

.statement-table th {
  font-size: 0.76rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 0.75rem 0.85rem;
  white-space: nowrap !important;
  color: var(--color-text-secondary);
  background: #fdfbf7;
  border-bottom: 1px solid var(--color-border);
}

.statement-table td {
  padding: 0.75rem 0.85rem;
  white-space: nowrap !important;
}

/* Column Visibility Picker Dropdown */
.column-picker-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 6px;
  width: 250px;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
  z-index: 1050;
  padding: 0.75rem;
}

.column-picker-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
  padding-bottom: 4px;
  border-bottom: 1px solid #f1f5f9;
}

.column-picker-toolbar {
  margin-bottom: 8px;
  padding-bottom: 6px;
  border-bottom: 1px dashed #e2e8f0;
}

.column-picker-list {
  max-height: 240px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.column-picker-item {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 2px 4px;
  border-radius: 4px;
  transition: background 0.15s ease;
}

.column-picker-item:hover {
  background: #f8fafc;
}

.column-picker-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1040;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1rem;
  cursor: pointer;
  color: var(--color-text-muted);
}

.btn-close:hover {
  color: var(--color-danger);
}

/* Responsive Table / Cards */
@media (min-width: 992px) {
  .mobile-data-list {
    display: none;
  }
  .desktop-statement-wrapper {
    display: block;
  }
}

@media (max-width: 991px) {
  .desktop-statement-wrapper {
    display: none;
  }
  .mobile-data-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
  }
  .mobile-data-card {
    background: #ffffff;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: var(--spacing-md);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  }
  .mdc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--color-border);
  }
  .mdc-date {
    font-size: 0.72rem;
    color: var(--color-text-muted);
  }
  .mdc-name {
    font-weight: 600;
    font-size: 0.88rem;
    color: #1e293b;
    display: block;
  }
  .mdc-email {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    display: block;
  }
  .mdc-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.6rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--color-border);
  }
  .mdc-badges {
    display: flex;
    gap: 4px;
    align-items: center;
    flex-wrap: wrap;
  }
}

/* Print Styles */
@media print {
  .admin-header, .sidebar, .statement-pagination-bar, .admin-page__header, .glass-panel, .btn, .btn-close, .column-picker-dropdown {
    display: none !important;
  }
  body {
    background: #ffffff !important;
    color: #000000 !important;
  }
}
</style>
