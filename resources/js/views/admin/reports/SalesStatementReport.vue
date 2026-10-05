<template>
  <div class="sales-statement-page">
    <!-- Page Header -->
    <div class="admin-page__header">
      <div class="admin-page__title-section">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <router-link to="/admin/reports" class="btn btn--secondary btn--sm" style="padding: 4px 8px;" title="Back to Reports Hub">
            ← Reports Hub
          </router-link>
          <h1 class="admin-page__title">Sales Statement Report</h1>
        </div>
        <span class="admin-page__subtitle">
          Comprehensive sales financial ledger, tax schedules, order accounting, and itemized product breakdowns.
        </span>
      </div>
      <div class="admin-header__actions" style="display: flex; gap: var(--spacing-xs); flex-wrap: wrap;">
        <button @click="exportToExcel" class="btn btn--secondary btn--sm" :disabled="loading || exportingExcel" style="font-weight: 600; color: #166534; background: #f0fdf4; border-color: #bbf7d0;">
          <span v-if="exportingExcel">⏳ Exporting...</span>
          <span v-else>📥 Export Excel (.xlsx)</span>
        </button>
        <button @click="exportToCSV" class="btn btn--secondary btn--sm" :disabled="loading">
          📄 Export CSV
        </button>
        <button @click="printStatement" class="btn btn--secondary btn--sm" title="Print Current Statement">
          🖨️ Print
        </button>
        <button @click="fetchStatement" class="btn btn--primary btn--sm" :disabled="loading">
          🔄 Refresh
        </button>
      </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg);">
      <!-- Primary Search and Core Dropdowns Row -->
      <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
        <!-- Search Input -->
        <div style="flex: 1; min-width: 280px; position: relative;">
          <input 
            type="text" 
            v-model="filters.search" 
            @input="debounceSearch"
            placeholder="Search by Order #, Customer Name, Phone, Email, City, State, SKU..." 
            class="form-input" 
            style="padding-left: 2.2rem;" 
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

        <!-- Filter Dropdowns Grid -->
        <div style="display: flex; gap: var(--spacing-sm); flex-wrap: wrap; align-items: center;">
          <!-- Order Status Dropdown -->
          <div style="display: flex; align-items: center; gap: 4px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600;">Order Status:</label>
            <select v-model="filters.order_status" @change="onFiltersChanged" class="form-input" style="min-width: 130px; padding: 0.35rem 0.6rem; font-size: 0.82rem;">
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
          <div style="display: flex; align-items: center; gap: 4px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600;">Payment:</label>
            <select v-model="filters.payment_status" @change="onFiltersChanged" class="form-input" style="min-width: 120px; padding: 0.35rem 0.6rem; font-size: 0.82rem;">
              <option value="all">All Payments</option>
              <option value="paid">Paid / Captured</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
              <option value="refunded">Refunded</option>
            </select>
          </div>

          <!-- Payment Method Dropdown -->
          <div style="display: flex; align-items: center; gap: 4px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600;">Method:</label>
            <select v-model="filters.payment_method" @change="onFiltersChanged" class="form-input" style="min-width: 120px; padding: 0.35rem 0.6rem; font-size: 0.82rem;">
              <option value="all">All Methods</option>
              <option value="upi">UPI</option>
              <option value="card">Cards</option>
              <option value="netbanking">Net Banking</option>
              <option value="cashfree">Online (Cashfree)</option>
              <option value="cod">Cash on Delivery</option>
            </select>
          </div>

          <!-- Sort Dropdown -->
          <div style="display: flex; align-items: center; gap: 4px;">
            <label style="font-size: 0.78rem; color: var(--color-text-muted); font-weight: 600;">Sort:</label>
            <select v-model="filters.sort_by" @change="onFiltersChanged" class="form-input" style="min-width: 125px; padding: 0.35rem 0.6rem; font-size: 0.82rem;">
              <option value="date_desc">Newest First</option>
              <option value="date_asc">Oldest First</option>
              <option value="amount_desc">Amount: High to Low</option>
              <option value="amount_asc">Amount: Low to High</option>
              <option value="items_desc">Most Items</option>
            </select>
          </div>

          <!-- Reset Button -->
          <button @click="resetFilters" class="btn btn--secondary btn--sm" title="Clear all filters" style="padding: 0.35rem 0.6rem;">
            ↺ Reset
          </button>
        </div>
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
    <div v-else class="glass-panel" style="overflow: hidden;">
      
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
              <div>Subtotal: <strong>₹{{ formatCurrency(order.financials.subtotal) }}</strong></div>
              <div>Discount: <strong style="color: var(--color-danger);">-₹{{ formatCurrency(order.financials.discount_amount) }}</strong></div>
              <div>GST Tax: <strong>₹{{ formatCurrency(order.financials.tax_amount) }}</strong></div>
              <div>Shipping: <strong>₹{{ formatCurrency(order.financials.shipping_amount) }}</strong></div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--color-border);">
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
              <span :class="['badge', getOrderStatusBadgeClass(order.status)]">
                {{ order.status_label }}
              </span>
              <span :class="['badge', getPaymentBadgeClass(order.payment_status)]">
                {{ order.payment_status }}
              </span>
              <span style="font-size: 0.7rem; color: var(--color-text-muted); text-transform: uppercase;">
                {{ order.payment_method }}
              </span>
            </div>
            <button @click="openSlipModal(order)" class="btn btn--secondary btn--sm" style="padding: 2px 8px; font-size: 0.72rem;">
              Slip 📄
            </button>
          </div>
        </div>

        <div v-if="orders.length === 0" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
          No sales transactions found matching the selected filters.
        </div>
      </div>

      <!-- Desktop Statement Table View -->
      <div class="table-responsive desktop-statement-wrapper">
        <table class="data-table desktop-data-table statement-table">
          <thead>
            <tr>
              <th style="width: 40px; text-align: center;"></th>
              <th style="width: 50px; text-align: center;">#</th>
              <th>Date & Time</th>
              <th>Order Number</th>
              <th>Customer</th>
              <th style="text-align: center;">Items</th>
              <th style="text-align: right;">Gross MRP</th>
              <th style="text-align: right;">Discount</th>
              <th style="text-align: right;">Subtotal</th>
              <th style="text-align: right;" title="CGST + SGST or IGST">GST Tax</th>
              <th style="text-align: right;">Shipping</th>
              <th style="text-align: right;">Grand Total</th>
              <th>Payment</th>
              <th>Order Status</th>
              <th style="text-align: center;">Actions</th>
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
                <td style="text-align: center; font-weight: 600; color: var(--color-text-secondary); font-size: 0.82rem;">
                  {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
                </td>

                <!-- Date & Time -->
                <td style="white-space: nowrap; font-size: 0.82rem;">
                  <div style="font-weight: 500; color: #1e293b;">{{ order.created_at_display }}</div>
                </td>

                <!-- Order Number Link -->
                <td style="white-space: nowrap;">
                  <router-link :to="`/admin/orders/${order.id}`" class="order-link-badge" title="View Full Order Admin Details">
                    {{ order.order_number }}
                  </router-link>
                  <div v-if="order.logistics.courier_name !== '—'" style="font-size: 0.7rem; color: var(--color-text-muted); margin-top: 2px;">
                    🚚 {{ order.logistics.courier_name }}
                  </div>
                </td>

                <!-- Customer Details -->
                <td>
                  <div style="display: flex; flex-direction: column; min-width: 140px;">
                    <span style="font-weight: 600; color: #1e293b; font-size: 0.84rem;">{{ order.customer.name }}</span>
                    <span style="font-size: 0.74rem; color: var(--color-text-muted);">{{ order.customer.phone }}</span>
                    <span style="font-size: 0.72rem; color: #64748b;">{{ order.customer.city }}, {{ order.customer.state }}</span>
                  </div>
                </td>

                <!-- Items Count -->
                <td style="text-align: center;">
                  <span class="badge badge--secondary" style="font-size: 0.75rem; cursor: pointer;" @click="toggleRowExpand(order.id)" title="Click to inspect items">
                    {{ order.financials.total_items }} pcs
                  </span>
                </td>

                <!-- Gross MRP -->
                <td style="text-align: right; color: var(--color-text-muted); font-size: 0.82rem;">
                  ₹{{ formatCurrency(order.financials.gross_mrp) }}
                </td>

                <!-- Discount Amount -->
                <td style="text-align: right; color: var(--color-danger); font-size: 0.82rem; font-weight: 500;">
                  <span v-if="order.financials.discount_amount > 0">-₹{{ formatCurrency(order.financials.discount_amount) }}</span>
                  <span v-else style="color: var(--color-text-muted);">₹0.00</span>
                </td>

                <!-- Subtotal -->
                <td style="text-align: right; font-weight: 600; color: #1e293b; font-size: 0.82rem;">
                  ₹{{ formatCurrency(order.financials.subtotal) }}
                </td>

                <!-- Tax Amount with Breakdown hover -->
                <td style="text-align: right; font-size: 0.82rem;">
                  <span :title="`CGST: ₹${order.financials.cgst_amount} | SGST: ₹${order.financials.sgst_amount} | IGST: ₹${order.financials.igst_amount}`" style="cursor: help; border-bottom: 1px dotted #94a3b8;">
                    ₹{{ formatCurrency(order.financials.tax_amount) }}
                  </span>
                </td>

                <!-- Shipping Fee -->
                <td style="text-align: right; font-size: 0.82rem; color: var(--color-text-secondary);">
                  ₹{{ formatCurrency(order.financials.shipping_amount) }}
                </td>

                <!-- Grand Total -->
                <td style="text-align: right; font-size: 0.95rem; font-weight: 700; color: var(--color-primary); white-space: nowrap;">
                  ₹{{ formatCurrency(order.financials.grand_total) }}
                </td>

                <!-- Payment Status & Method -->
                <td style="white-space: nowrap;">
                  <div style="display: flex; flex-direction: column; gap: 2px;">
                    <span :class="['badge', getPaymentBadgeClass(order.payment_status)]" style="font-size: 0.72rem; padding: 2px 6px;">
                      {{ order.payment_status }}
                    </span>
                    <span style="font-size: 0.7rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                      {{ order.payment_method }}
                    </span>
                  </div>
                </td>

                <!-- Order Status -->
                <td style="white-space: nowrap;">
                  <span :class="['badge', getOrderStatusBadgeClass(order.status)]" style="font-size: 0.72rem; padding: 2px 6px;">
                    {{ order.status_label }}
                  </span>
                </td>

                <!-- Actions -->
                <td style="text-align: center; white-space: nowrap;">
                  <div style="display: flex; gap: 4px; justify-content: center;">
                    <button @click="openSlipModal(order)" class="btn btn--secondary btn--sm" style="padding: 3px 8px; font-size: 0.74rem;" title="View Tax Statement Slip">
                      Slip 📄
                    </button>
                    <router-link :to="`/admin/orders/${order.id}`" class="btn btn--secondary btn--sm" style="padding: 3px 8px; font-size: 0.74rem;" title="Order Management">
                      👁️
                    </router-link>
                  </div>
                </td>
              </tr>

              <!-- Detailed Itemized Row (Expandable) -->
              <tr v-if="isRowExpanded(order.id)" class="itemized-nested-row">
                <td colspan="15" style="padding: 0; background: #fffcf7;">
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
              <td colspan="15" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
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

      <!-- Pagination Footer -->
      <div v-if="pagination.total > 0" class="statement-pagination-bar">
        <div style="font-size: 0.82rem; color: var(--color-text-muted);">
          Showing <strong>{{ (pagination.current_page - 1) * pagination.per_page + 1 }}</strong> to 
          <strong>{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</strong> of 
          <strong>{{ pagination.total }}</strong> sales orders
        </div>

        <div style="display: flex; gap: 0.35rem; align-items: center;">
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
        </div>
      </div>
    </div>

    <!-- Sales Statement Slip Modal -->
    <div v-if="activeSlipOrder" class="modal-backdrop" @click="activeSlipOrder = null">
      <div class="modal-box statement-slip-modal" @click.stop>
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: var(--spacing-sm);">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.3rem;">📑</span>
            <div>
              <h2 style="font-size: 1.1rem; color: var(--color-primary); margin: 0;">Sales Statement Slip</h2>
              <span style="font-size: 0.75rem; color: var(--color-text-muted);">Tax Invoice & Financial Statement Record</span>
            </div>
          </div>
          <button @click="activeSlipOrder = null" class="btn-close">✕</button>
        </div>

        <div class="modal-body printable-slip-content" id="printable-statement-slip" style="padding: var(--spacing-md) 0;">
          <!-- Slip Header -->
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--color-primary);">
            <div>
              <h3 style="font-family: var(--font-family-heading); font-size: 1.35rem; color: var(--color-primary); margin: 0;">
                MAYA SREE FASHION
              </h3>
              <p style="font-size: 0.78rem; color: var(--color-text-secondary); margin-top: 2px;">
                Premium International Ethnic Fashion<br />
                GSTIN: 32AAAAA0000A1Z5 | Support: contact@mayasree.com
              </p>
            </div>
            <div style="text-align: right;">
              <div style="font-weight: 700; font-size: 1rem; color: var(--color-primary);">{{ activeSlipOrder.order_number }}</div>
              <div style="font-size: 0.78rem; color: var(--color-text-muted);">Dated: {{ activeSlipOrder.created_at_display }}</div>
              <div style="font-size: 0.78rem; color: #166534; font-weight: 600;">Status: {{ activeSlipOrder.status_label }}</div>
            </div>
          </div>

          <!-- Customer & Dispatch Info -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; font-size: 0.8rem; background: #faf8f5; padding: 0.75rem; border-radius: 6px;">
            <div>
              <strong style="color: var(--color-primary); font-size: 0.82rem;">Billed & Shipped To:</strong>
              <div style="font-weight: 600; margin-top: 2px;">{{ activeSlipOrder.customer.name }}</div>
              <div>{{ activeSlipOrder.customer.full_address }}</div>
              <div>Phone: {{ activeSlipOrder.customer.phone }}</div>
              <div>Email: {{ activeSlipOrder.customer.email }}</div>
            </div>
            <div>
              <strong style="color: var(--color-primary); font-size: 0.82rem;">Payment & Shipment:</strong>
              <div style="margin-top: 2px;">Payment Method: <strong>{{ activeSlipOrder.payment_method }}</strong></div>
              <div>Payment Status: <strong>{{ activeSlipOrder.payment_status }}</strong></div>
              <div v-if="activeSlipOrder.gateway_payment_id">Gateway Ref: <code>{{ activeSlipOrder.gateway_payment_id }}</code></div>
              <div v-if="activeSlipOrder.logistics.courier_name !== '—'">
                Courier: {{ activeSlipOrder.logistics.courier_name }} (AWB: {{ activeSlipOrder.logistics.tracking_number }})
              </div>
            </div>
          </div>

          <!-- Itemized Table in Slip -->
          <table class="data-table" style="width: 100%; font-size: 0.8rem; margin-bottom: 1rem;">
            <thead>
              <tr style="background: #f8fafc;">
                <th>#</th>
                <th>Item Description</th>
                <th>SKU</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Unit Rate</th>
                <th style="text-align: right;">GST</th>
                <th style="text-align: right;">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in activeSlipOrder.items" :key="item.id">
                <td style="text-align: center;">{{ idx + 1 }}</td>
                <td style="font-weight: 600;">
                  {{ item.product_name }}
                  <span v-if="item.variant_name" style="font-weight: normal; color: var(--color-text-muted);">({{ item.variant_name }})</span>
                </td>
                <td><code>{{ item.sku }}</code></td>
                <td style="text-align: center; font-weight: bold;">{{ item.quantity }}</td>
                <td style="text-align: right;">₹{{ formatCurrency(item.unit_price) }}</td>
                <td style="text-align: right;">₹{{ formatCurrency(item.tax_amount) }}</td>
                <td style="text-align: right; font-weight: 700;">₹{{ formatCurrency(item.total_price) }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Financial Calculation Breakdown -->
          <div style="display: flex; justify-content: flex-end; margin-top: 0.75rem;">
            <div style="width: 280px; font-size: 0.82rem; display: flex; flex-direction: column; gap: 4px;">
              <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--color-text-muted);">Gross Value:</span>
                <span>₹{{ formatCurrency(activeSlipOrder.financials.gross_mrp) }}</span>
              </div>
              <div v-if="activeSlipOrder.financials.discount_amount > 0" style="display: flex; justify-content: space-between; color: var(--color-danger);">
                <span>Discount / Promo:</span>
                <span>-₹{{ formatCurrency(activeSlipOrder.financials.discount_amount) }}</span>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--color-text-muted);">Taxable Subtotal:</span>
                <span style="font-weight: 600;">₹{{ formatCurrency(activeSlipOrder.financials.subtotal) }}</span>
              </div>
              <div style="display: flex; justify-content: space-between; font-size: 0.76rem; color: #b45309;">
                <span>Total Taxes (GST):</span>
                <span>₹{{ formatCurrency(activeSlipOrder.financials.tax_amount) }}</span>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--color-text-muted);">Shipping Charges:</span>
                <span>₹{{ formatCurrency(activeSlipOrder.financials.shipping_amount) }}</span>
              </div>
              <div style="display: flex; justify-content: space-between; font-size: 1.05rem; font-weight: 700; color: var(--color-primary); padding-top: 6px; border-top: 2px solid var(--color-primary); margin-top: 4px;">
                <span>Total Amount:</span>
                <span>₹{{ formatCurrency(activeSlipOrder.financials.grand_total) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--color-border); padding-top: var(--spacing-sm); margin-top: var(--spacing-sm);">
          <span style="font-size: 0.75rem; color: var(--color-text-muted);">Computer generated tax invoice statement.</span>
          <div style="display: flex; gap: 8px;">
            <button @click="printModalSlip" class="btn btn--secondary btn--sm">
              🖨️ Print Slip
            </button>
            <button @click="activeSlipOrder = null" class="btn btn--primary btn--sm">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import * as XLSX from 'xlsx';

// Data States
const loading = ref(false);
const exportingExcel = ref(false);
const errorMsg = ref('');
const orders = ref([]);
const activeSlipOrder = ref(null);
const expandedRowIds = ref(new Set());

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
  // Trigger reactivity
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

// Modal Operations
const openSlipModal = (order) => {
  activeSlipOrder.value = order;
};

const printModalSlip = () => {
  window.print();
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

  const token = localStorage.getItem('auth_token');
  const exportUrl = `/api/admin/reports/sales-statement/export?${params.toString()}`;

  // Download via authenticated fetch or anchor
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

// Export to Rich Multi-Sheet Excel Workbook (.xlsx) via SheetJS
const exportToExcel = async () => {
  exportingExcel.value = true;
  try {
    // 1. Fetch full records for the active filters (up to 2000 records)
    const params = {
      search: filters.search || undefined,
      date_preset: filters.date_preset,
      start_date: filters.date_preset === 'custom' ? filters.start_date : undefined,
      end_date: filters.date_preset === 'custom' ? filters.end_date : undefined,
      order_status: filters.order_status,
      payment_status: filters.payment_status,
      payment_method: filters.payment_method,
      sort_by: filters.sort_by,
      per_page: 'all',
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
      ['Search Filter', filters.search || 'None'],
      [],
      ['FINANCIAL & OPERATIONAL KPI SUMMARY'],
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
    XLSX.utils.book_append_sheet(wb, wsSummary, 'Statement Summary');

    // ================= SHEET 2: Orders Statement Ledger =================
    const orderHeaders = [
      'S.No',
      'Order Number',
      'Date & Time',
      'Customer Name',
      'Customer Email',
      'Customer Phone',
      'Shipping City',
      'Shipping State',
      'Postal Code',
      'Payment Method',
      'Payment Status',
      'Order Status',
      'Total Items',
      'Gross MRP (₹)',
      'Discount (₹)',
      'Subtotal (₹)',
      'CGST (₹)',
      'SGST (₹)',
      'IGST (₹)',
      'Total Tax (₹)',
      'Shipping (₹)',
      'Grand Total (₹)',
      'Courier Partner',
      'Tracking Number',
      'Full Shipping Address'
    ];

    const orderRows = allOrders.map((o, idx) => [
      idx + 1,
      o.order_number,
      o.created_at,
      o.customer.name,
      o.customer.email,
      o.customer.phone,
      o.customer.city,
      o.customer.state,
      o.customer.postal_code,
      o.payment_method,
      o.payment_status,
      o.status_label,
      o.financials.total_items,
      o.financials.gross_mrp,
      o.financials.discount_amount,
      o.financials.subtotal,
      o.financials.cgst_amount,
      o.financials.sgst_amount,
      o.financials.igst_amount,
      o.financials.tax_amount,
      o.financials.shipping_amount,
      o.financials.grand_total,
      o.logistics.courier_name,
      o.logistics.tracking_number,
      o.customer.full_address
    ]);

    const wsOrders = XLSX.utils.aoa_to_sheet([orderHeaders, ...orderRows]);
    wsOrders['!cols'] = [
      { wch: 6 },  // S.No
      { wch: 18 }, // Order #
      { wch: 19 }, // Date
      { wch: 22 }, // Customer
      { wch: 24 }, // Email
      { wch: 15 }, // Phone
      { wch: 14 }, // City
      { wch: 14 }, // State
      { wch: 10 }, // Postal Code
      { wch: 14 }, // Payment Method
      { wch: 14 }, // Payment Status
      { wch: 16 }, // Order Status
      { wch: 10 }, // Total Items
      { wch: 14 }, // Gross MRP
      { wch: 12 }, // Discount
      { wch: 14 }, // Subtotal
      { wch: 10 }, // CGST
      { wch: 10 }, // SGST
      { wch: 10 }, // IGST
      { wch: 12 }, // Total Tax
      { wch: 12 }, // Shipping
      { wch: 16 }, // Grand Total
      { wch: 16 }, // Courier
      { wch: 16 }, // Tracking
      { wch: 35 }, // Address
    ];
    XLSX.utils.book_append_sheet(wb, wsOrders, 'Orders Statement Ledger');

    // ================= SHEET 3: Itemized Sales Details =================
    const itemHeaders = [
      'S.No',
      'Order Number',
      'Order Date',
      'Customer Name',
      'Customer Phone',
      'SKU Identifier',
      'Product Name',
      'Variant',
      'Quantity',
      'Unit MRP (₹)',
      'Unit Price (₹)',
      'Item Discount (₹)',
      'Item Tax (₹)',
      'Item Total Price (₹)',
      'Order Status',
      'Payment Status'
    ];

    const itemRows = [];
    let itemIndex = 1;
    allOrders.forEach((o) => {
      (o.items || []).forEach((item) => {
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

    const wsItems = XLSX.utils.aoa_to_sheet([itemHeaders, ...itemRows]);
    wsItems['!cols'] = [
      { wch: 6 },  // S.No
      { wch: 18 }, // Order #
      { wch: 19 }, // Date
      { wch: 22 }, // Customer
      { wch: 15 }, // Phone
      { wch: 16 }, // SKU
      { wch: 32 }, // Product Name
      { wch: 16 }, // Variant
      { wch: 8 },  // Qty
      { wch: 12 }, // MRP
      { wch: 12 }, // Unit Price
      { wch: 14 }, // Discount
      { wch: 12 }, // Tax
      { wch: 15 }, // Total
      { wch: 14 }, // Order Status
      { wch: 14 }, // Payment Status
    ];
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

/* Statement Stats Grid */
.statement-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: var(--spacing-md);
}

.statement-card-highlight {
  border-color: rgba(74, 14, 46, 0.35);
  background: linear-gradient(135deg, #ffffff, #fffcf7);
  box-shadow: 0 4px 20px rgba(74, 14, 46, 0.06);
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

/* Statement Table Tweaks */
.statement-table th {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 0.65rem 0.6rem;
}

.statement-table td {
  padding: 0.65rem 0.6rem;
}

/* Modal Styling */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-md);
}

.modal-box {
  background: #ffffff;
  border-radius: 12px;
  width: 100%;
  max-width: 740px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  padding: var(--spacing-lg);
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.2rem;
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
  .admin-header, .sidebar, .statement-pagination-bar, .admin-page__header, .glass-panel, .btn, .modal-footer, .btn-close {
    display: none !important;
  }
  .modal-backdrop {
    position: static;
    background: none;
    padding: 0;
  }
  .modal-box {
    max-width: 100%;
    box-shadow: none;
    padding: 0;
  }
  body {
    background: #ffffff !important;
    color: #000000 !important;
  }
}
</style>
