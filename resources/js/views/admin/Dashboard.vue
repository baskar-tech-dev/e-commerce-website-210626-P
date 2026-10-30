<template>
  <div class="dashboard-wrapper">
    <!-- Executive Brand Header Card -->
    <div class="brand-hero-card">
      <div class="brand-hero-main">
        <div class="brand-logo-container">
          <img 
            :src="'/asset/profile/logo.png'" 
            alt="Maya Sree Fashion" 
            class="brand-logo-img" 
          />
        </div>
        <div class="brand-text-container">
          <div class="brand-badge-row">
            <span class="brand-tag">Executive Admin Portal</span>
            <span class="brand-status-pill">
              <span class="pulse-dot"></span>
              Storefront Live
            </span>
          </div>
          <h1 class="brand-company-title">Maya Sree Fashion</h1>
          <p class="brand-tagline">
            Premium Ethnic Wear & Stretchable Blouses • Real-time Operations Dashboard
          </p>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="brand-hero-actions">
        <a 
          href="/" 
          target="_blank" 
          class="btn-hero-action" 
          title="Open Customer Storefront"
        >
          <ExternalLink :size="15" />
          <span>View Store</span>
        </a>
        <router-link 
          to="/admin/products/create" 
          class="btn-hero-action btn-hero-action--primary"
          title="Add New Catalog Product"
        >
          <PlusCircle :size="15" />
          <span>New Product</span>
        </router-link>
        <button 
          @click="fetchDashboardData" 
          class="btn-hero-action" 
          :disabled="loading"
          title="Refresh Live Data"
        >
          <RotateCcw :size="15" :class="{ 'spin-anim': loading }" />
          <span>{{ loading ? 'Updating...' : 'Refresh' }}</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !statsLoaded" class="dashboard-loading-state">
      <div class="loading-spinner"></div>
      <p class="loading-text">Loading live store statistics & sales metrics...</p>
    </div>

    <!-- Error Alert -->
    <div v-else-if="errorMsg" class="dashboard-error-banner">
      <AlertTriangle :size="20" />
      <span>{{ errorMsg }}</span>
      <button @click="fetchDashboardData" class="btn-error-retry">Retry</button>
    </div>

    <div v-else class="dashboard-body">
      <!-- =====================================================================
           SECTION 1: KEY PERFORMANCE METRICS (PAID RECORDS ONLY)
           ===================================================================== -->
      <div class="section-indicator-row">
        <div class="section-indicator-badge">
          <span class="live-dot"></span>
          <span>Verified Paid Orders & Realized Sales</span>
        </div>
        <span class="section-indicator-note">All KPIs & revenue metrics below reflect only paid transactions</span>
      </div>

      <div class="kpi-grid">
        <!-- Total Paid Revenue -->
        <div class="kpi-card kpi-card--maroon">
          <div class="kpi-card__header">
            <span class="kpi-card__title">Total Realized Revenue</span>
            <div class="kpi-card__icon-badge">
              <TrendingUp :size="18" />
            </div>
          </div>
          <div class="kpi-card__value">₹{{ formatCurrency(kpis.total_revenue) }}</div>
          <div class="kpi-card__footer">
            <span class="kpi-trend kpi-trend--up">₹{{ formatCurrency(kpis.month_revenue) }}</span>
            <span class="kpi-subtext">this month ({{ kpis.total_orders }} paid orders)</span>
          </div>
        </div>

        <!-- Today's Paid Sales -->
        <div class="kpi-card kpi-card--gold">
          <div class="kpi-card__header">
            <span class="kpi-card__title">Today's Paid Sales</span>
            <div class="kpi-card__icon-badge">
              <ShoppingBag :size="18" />
            </div>
          </div>
          <div class="kpi-card__value">₹{{ formatCurrency(kpis.today_revenue) }}</div>
          <div class="kpi-card__footer">
            <span class="kpi-highlight">{{ kpis.today_orders }} {{ kpis.today_orders === 1 ? 'paid order' : 'paid orders' }}</span>
            <span class="kpi-subtext">cleared today</span>
          </div>
        </div>

        <!-- Active Products -->
        <div class="kpi-card kpi-card--emerald">
          <div class="kpi-card__header">
            <span class="kpi-card__title">Live Catalog Items</span>
            <div class="kpi-card__icon-badge">
              <Package :size="18" />
            </div>
          </div>
          <div class="kpi-card__value">{{ kpis.active_products }}</div>
          <div class="kpi-card__footer">
            <span class="kpi-highlight">{{ kpis.total_customers }} Customers</span>
            <span class="kpi-subtext">registered in store</span>
          </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="kpi-card" :class="kpis.low_stock_count > 0 ? 'kpi-card--warning' : 'kpi-card--success'">
          <div class="kpi-card__header">
            <span class="kpi-card__title">Inventory Stock Watch</span>
            <div class="kpi-card__icon-badge">
              <AlertTriangle :size="18" />
            </div>
          </div>
          <div class="kpi-card__value">{{ kpis.low_stock_count }}</div>
          <div class="kpi-card__footer">
            <span v-if="kpis.low_stock_count > 0" class="kpi-tag kpi-tag--warning">Needs Inward Reorder</span>
            <span v-else class="kpi-tag kpi-tag--success">Healthy Stock Levels</span>
            <router-link to="/admin/inventory" class="kpi-link">View Ledger →</router-link>
          </div>
        </div>
      </div>

      <!-- =====================================================================
           SECTION 2: 9-STAGE ORDER LIFECYCLE PIPELINE (PAID ORDERS ONLY)
           ===================================================================== -->
      <div class="dashboard-card">
        <div class="dashboard-card__header">
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <h2 class="dashboard-card__title">Customer Orders Pipeline</h2>
              <span class="badge-mini badge-mini--paid">Paid Orders</span>
            </div>
            <p class="dashboard-card__subtitle">Active customer purchases with confirmed payment across fulfillment stages</p>
          </div>
          <router-link to="/admin/orders" class="dashboard-card__action-link">
            <span>Manage Orders</span>
            <ArrowRight :size="14" />
          </router-link>
        </div>

        <div class="pipeline-grid">
          <router-link 
            v-for="(stage, key) in pipeline" 
            :key="key"
            :to="`/admin/orders`" 
            class="pipeline-card"
          >
            <div class="pipeline-card__top">
              <span class="pipeline-icon">{{ stage.icon }}</span>
              <span class="pipeline-count">{{ stage.count }}</span>
            </div>
            <div class="pipeline-label">{{ stage.label }}</div>
            <div class="pipeline-amount">₹{{ formatCurrency(stage.amount) }}</div>
          </router-link>
        </div>
      </div>

      <!-- =====================================================================
           SECTION 3: RECENT PAID ORDERS & LOW STOCK WATCH
           ===================================================================== -->
      <div class="dashboard-split-grid">
        <!-- Left: Recent Paid Orders Table -->
        <div class="dashboard-card">
          <div class="dashboard-card__header">
            <div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <h2 class="dashboard-card__title">Recent Paid Orders</h2>
                <span class="badge-mini badge-mini--paid">Payment: Paid</span>
              </div>
              <p class="dashboard-card__subtitle">Latest purchases completed with successful payment</p>
            </div>
            <router-link to="/admin/orders" class="dashboard-card__action-link">
              <span>View All Paid</span>
              <ArrowRight :size="14" />
            </router-link>
          </div>

          <div v-if="recentOrders.length === 0" class="empty-state-box">
            <ShoppingBag :size="32" class="empty-icon" />
            <p>No paid customer orders recorded yet.</p>
          </div>

          <div v-else class="recent-orders-table-wrap">
            <table class="recent-orders-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Total</th>
                  <th>Payment</th>
                  <th>Status</th>
                  <th style="text-align: right; width: 50px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in recentOrders" :key="order.id">
                  <td>
                    <router-link :to="`/admin/orders/${order.id}`" class="order-num-link">
                      {{ order.order_number }}
                    </router-link>
                  </td>
                  <td>
                    <div class="customer-info-cell">
                      <span class="customer-name">{{ order.customer_name }}</span>
                      <span class="customer-email">{{ order.customer_email }}</span>
                    </div>
                  </td>
                  <td class="order-date-cell">
                    <span>{{ order.date }}</span>
                    <span class="order-time-ago">{{ order.time_ago }}</span>
                  </td>
                  <td class="order-amount-cell">
                    ₹{{ formatCurrency(order.grand_total) }}
                  </td>
                  <td>
                    <span class="badge-payment badge-payment--paid">PAID ({{ order.payment_method }})</span>
                  </td>
                  <td>
                    <span :class="['order-badge', getBadgeClass(order.status)]">
                      {{ order.status_label }}
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <router-link 
                      :to="`/admin/orders/${order.id}`" 
                      class="btn-action-icon"
                      title="View Order Details"
                    >
                      <Eye :size="15" />
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Right: Low Stock & Catalog Alerts -->
        <div class="dashboard-card">
          <div class="dashboard-card__header">
            <div>
              <h2 class="dashboard-card__title">Low Stock SKUs</h2>
              <p class="dashboard-card__subtitle">Variants approaching or below reorder threshold</p>
            </div>
            <router-link to="/admin/inventory" class="dashboard-card__action-link">
              <span>Inventory</span>
              <ArrowRight :size="14" />
            </router-link>
          </div>

          <div v-if="lowStockAlerts.length === 0" class="empty-state-box">
            <CheckCircle :size="32" class="empty-icon empty-icon--success" />
            <p>All product variants have healthy stock levels!</p>
          </div>

          <div v-else class="stock-alerts-list">
            <div v-for="item in lowStockAlerts" :key="item.id" class="stock-alert-item">
              <div class="stock-alert-info">
                <span class="stock-alert-product">{{ item.product_name }}</span>
                <div class="stock-alert-meta">
                  <span class="stock-alert-sku">SKU: {{ item.sku }}</span>
                  <span v-if="item.size" class="stock-alert-variant">• Size: {{ item.size }}</span>
                  <span v-if="item.color" class="stock-alert-variant">• Color: {{ item.color }}</span>
                </div>
              </div>
              <div class="stock-alert-qty-wrap">
                <span class="stock-qty-badge" :class="item.stock_quantity <= 0 ? 'stock-qty-badge--out' : 'stock-qty-badge--low'">
                  {{ item.stock_quantity }} in stock
                </span>
                <span class="stock-threshold-label">Threshold: {{ item.threshold }}</span>
              </div>
            </div>
          </div>

          <div class="stock-card-footer">
            <router-link to="/admin/stock-inward" class="btn-inward-link">
              <Boxes :size="14" />
              <span>Record New Inward Batch</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- =====================================================================
           SECTION 4: DEDICATED SEPARATE SECTION FOR PAYMENT PENDING, FAILED, REFUNDED
           ===================================================================== -->
      <div class="dashboard-card payment-exceptions-card">
        <div class="dashboard-card__header">
          <div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <h2 class="dashboard-card__title" style="color: #991b1b;">Payment Exceptions & Follow-ups</h2>
              <span class="badge-mini badge-mini--warning">Unpaid / Action Required</span>
            </div>
            <p class="dashboard-card__subtitle">
              Dedicated tracking for orders awaiting payment clearance, abandoned checkouts, and processed refunds.
            </p>
          </div>
          <router-link 
            :to="`/admin/orders?payment_status=${activeExceptionTab}&date_from=${exceptionDateFrom}&date_to=${exceptionDateTo}`" 
            class="dashboard-card__action-link"
          >
            <span>Orders Manager</span>
            <ArrowRight :size="14" />
          </router-link>
        </div>

        <!-- Section Date Filter & Date Range Filter Bar -->
        <div class="exception-filter-panel">
          <div class="exception-filter-row">
            <!-- Date Filter Preset Selector -->
            <div class="exception-filter-field">
              <label class="exception-filter-label">
                <Calendar :size="14" />
                <span>Date Filter:</span>
              </label>
              <select 
                v-model="exceptionDatePreset" 
                @change="onExceptionPresetChange" 
                class="exception-select"
              >
                <option value="all">All Dates</option>
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="last7">Last 7 Days</option>
                <option value="last30">Last 30 Days</option>
                <option value="this_month">This Month</option>
                <option value="custom">Custom Date Range</option>
              </select>
            </div>

            <!-- Custom Date Range Inputs (Native From / To) -->
            <div v-if="exceptionDatePreset === 'custom' || exceptionDateFrom || exceptionDateTo" class="exception-range-box">
              <div class="exception-range-item">
                <span class="exception-range-tag">From:</span>
                <input 
                  type="date" 
                  v-model="exceptionDateFrom" 
                  @change="onExceptionDateChange" 
                  class="exception-native-date" 
                  title="From Date"
                />
              </div>
              <span class="exception-range-sep">→</span>
              <div class="exception-range-item">
                <span class="exception-range-tag">To:</span>
                <input 
                  type="date" 
                  v-model="exceptionDateTo" 
                  @change="onExceptionDateChange" 
                  class="exception-native-date" 
                  title="To Date"
                />
              </div>
              <button 
                v-if="exceptionDateFrom || exceptionDateTo" 
                type="button" 
                @click="clearExceptionDate" 
                class="btn-date-clear" 
                title="Clear date range"
              >
                <X :size="13" />
              </button>
            </div>

            <!-- Active Date Indicator & Reset Button -->
            <div v-if="exceptionDateFrom || exceptionDateTo" class="exception-filter-active-info">
              <span class="exception-filter-date-tag">
                📅 {{ formatDateLabel(exceptionDateFrom) }} — {{ formatDateLabel(exceptionDateTo) }}
              </span>
              <button 
                type="button" 
                @click="clearExceptionDate" 
                class="btn-reset-exception"
                title="Reset date filter to All Dates"
              >
                <RotateCcw :size="13" />
                <span>Reset Date</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Exception Metric Cards -->
        <div class="exception-cards-grid">
          <!-- Pending Payments Card -->
          <div 
            class="exception-metric-card" 
            :class="{ 'exception-metric-card--active': activeExceptionTab === 'pending' }"
            @click="activeExceptionTab = 'pending'"
          >
            <div class="exception-metric-top">
              <div class="exception-metric-icon exception-metric-icon--amber">
                <Clock :size="18" />
              </div>
              <span class="exception-metric-count">{{ paymentExceptions.summary?.pending?.count || 0 }}</span>
            </div>
            <div class="exception-metric-title">Payment Pending</div>
            <div class="exception-metric-amount">₹{{ formatCurrency(paymentExceptions.summary?.pending?.amount || 0) }}</div>
            <div class="exception-metric-desc">Awaiting gateway capture or COD clearance</div>
          </div>

          <!-- Failed Payments Card -->
          <div 
            class="exception-metric-card" 
            :class="{ 'exception-metric-card--active': activeExceptionTab === 'failed' }"
            @click="activeExceptionTab = 'failed'"
          >
            <div class="exception-metric-top">
              <div class="exception-metric-icon exception-metric-icon--red">
                <XCircle :size="18" />
              </div>
              <span class="exception-metric-count">{{ paymentExceptions.summary?.failed?.count || 0 }}</span>
            </div>
            <div class="exception-metric-title">Payment Failed</div>
            <div class="exception-metric-amount">₹{{ formatCurrency(paymentExceptions.summary?.failed?.amount || 0) }}</div>
            <div class="exception-metric-desc">Declined transactions & abandoned checkouts</div>
          </div>

          <!-- Refunded Payments Card -->
          <div 
            class="exception-metric-card" 
            :class="{ 'exception-metric-card--active': activeExceptionTab === 'refunded' }"
            @click="activeExceptionTab = 'refunded'"
          >
            <div class="exception-metric-top">
              <div class="exception-metric-icon exception-metric-icon--gray">
                <CornerUpLeft :size="18" />
              </div>
              <span class="exception-metric-count">{{ paymentExceptions.summary?.refunded?.count || 0 }}</span>
            </div>
            <div class="exception-metric-title">Payment Refunded</div>
            <div class="exception-metric-amount">₹{{ formatCurrency(paymentExceptions.summary?.refunded?.amount || 0) }}</div>
            <div class="exception-metric-desc">Reversed charges and returned order settlements</div>
          </div>
        </div>

        <!-- Interactive Exception Tabs & Table -->
        <div class="exception-table-container">
          <div class="exception-tabs-bar">
            <button 
              type="button" 
              class="exception-tab-btn" 
              :class="{ 'exception-tab-btn--active': activeExceptionTab === 'pending' }"
              @click="activeExceptionTab = 'pending'"
            >
              <Clock :size="14" />
              <span>Pending Orders ({{ paymentExceptions.summary?.pending?.count || 0 }})</span>
            </button>
            <button 
              type="button" 
              class="exception-tab-btn" 
              :class="{ 'exception-tab-btn--active': activeExceptionTab === 'failed' }"
              @click="activeExceptionTab = 'failed'"
            >
              <XCircle :size="14" />
              <span>Failed Checkouts ({{ paymentExceptions.summary?.failed?.count || 0 }})</span>
            </button>
            <button 
              type="button" 
              class="exception-tab-btn" 
              :class="{ 'exception-tab-btn--active': activeExceptionTab === 'refunded' }"
              @click="activeExceptionTab = 'refunded'"
            >
              <CornerUpLeft :size="14" />
              <span>Refunded Transactions ({{ paymentExceptions.summary?.refunded?.count || 0 }})</span>
            </button>
          </div>

          <!-- Active Exception Orders Table -->
          <div v-if="currentExceptionOrders.length === 0" class="empty-state-box" style="padding: 2rem;">
            <CheckCircle :size="30" class="empty-icon empty-icon--success" />
            <p>No {{ activeExceptionTab }} payment records found.</p>
          </div>

          <div v-else class="recent-orders-table-wrap">
            <table class="recent-orders-table">
              <thead>
                <tr>
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Items</th>
                  <th>Grand Total</th>
                  <th>Payment Status</th>
                  <th>Order Status</th>
                  <th style="text-align: right; width: 50px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in currentExceptionOrders" :key="order.id">
                  <td>
                    <router-link :to="`/admin/orders/${order.id}`" class="order-num-link">
                      {{ order.order_number }}
                    </router-link>
                  </td>
                  <td>
                    <div class="customer-info-cell">
                      <span class="customer-name">{{ order.customer_name }}</span>
                      <span class="customer-email">{{ order.customer_email }}</span>
                    </div>
                  </td>
                  <td class="order-date-cell">
                    <span>{{ order.date }}</span>
                    <span class="order-time-ago">{{ order.time_ago }}</span>
                  </td>
                  <td>{{ order.items_count }} {{ order.items_count === 1 ? 'item' : 'items' }}</td>
                  <td class="order-amount-cell">
                    ₹{{ formatCurrency(order.grand_total) }}
                  </td>
                  <td>
                    <span 
                      class="badge-payment" 
                      :class="getPaymentBadgeClass(order.payment_status)"
                    >
                      {{ (order.payment_status || 'UNKNOWN').toUpperCase() }} ({{ order.payment_method }})
                    </span>
                  </td>
                  <td>
                    <span :class="['order-badge', getBadgeClass(order.status)]">
                      {{ order.status_label }}
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <router-link 
                      :to="`/admin/orders/${order.id}`" 
                      class="btn-action-icon"
                      title="View Order Details"
                    >
                      <Eye :size="15" />
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="exception-table-footer">
            <router-link 
              :to="`/admin/orders`" 
              class="exception-view-all-link"
            >
              <span>View all {{ activeExceptionTab }} orders in Orders List</span>
              <ArrowRight :size="14" />
            </router-link>
          </div>
        </div>
      </div>

      <!-- =====================================================================
           SECTION 5: OPERATIONAL MANAGEMENT MODULES HUB
           ===================================================================== -->
      <div class="dashboard-card">
        <div class="dashboard-card__header">
          <div>
            <h2 class="dashboard-card__title">Operational Management Modules</h2>
            <p class="dashboard-card__subtitle">Quick access to daily store operations and catalog workflows</p>
          </div>
        </div>

        <div class="modules-grid">
          <router-link to="/admin/orders" class="module-card">
            <div class="module-icon-wrap module-icon--maroon">
              <ShoppingBag :size="20" />
            </div>
            <div class="module-content">
              <h3 class="module-name">Orders & Fulfillment</h3>
              <p class="module-info">Process COD & online orders, print dispatch invoices, and manage parcel tracking.</p>
            </div>
            <div class="module-arrow">→</div>
          </router-link>

          <router-link to="/admin/products" class="module-card">
            <div class="module-icon-wrap module-icon--gold">
              <Package :size="20" />
            </div>
            <div class="module-content">
              <h3 class="module-name">Products & Catalog</h3>
              <p class="module-info">Stretchable blouses, silk sarees, sizing options, MRP, selling prices, and media galleries.</p>
            </div>
            <div class="module-arrow">→</div>
          </router-link>

          <router-link to="/admin/stock-inward" class="module-card">
            <div class="module-icon-wrap module-icon--blue">
              <Boxes :size="20" />
            </div>
            <div class="module-content">
              <h3 class="module-name">Stock Inward</h3>
              <p class="module-info">Record incoming manufacturer inventory batches, unit costs, and production quantities.</p>
            </div>
            <div class="module-arrow">→</div>
          </router-link>

          <router-link to="/admin/inventory" class="module-card">
            <div class="module-icon-wrap module-icon--emerald">
              <Layers :size="20" />
            </div>
            <div class="module-content">
              <h3 class="module-name">Inventory Ledger</h3>
              <p class="module-info">Real-time SKU quantities, warehouse adjustments, stock alerts, and audit logs.</p>
            </div>
            <div class="module-arrow">→</div>
          </router-link>

          <router-link to="/admin/reports/sales" class="module-card">
            <div class="module-icon-wrap module-icon--purple">
              <BarChart3 :size="20" />
            </div>
            <div class="module-content">
              <h3 class="module-name">Reports & Analytics</h3>
              <p class="module-info">Sales statements, revenue breakdown, customer cohorts, and Cashfree settlements.</p>
            </div>
            <div class="module-arrow">→</div>
          </router-link>

          <router-link to="/admin/settings" class="module-card">
            <div class="module-icon-wrap module-icon--slate">
              <ShieldCheck :size="20" />
            </div>
            <div class="module-content">
              <h3 class="module-name">Store & SMTP Settings</h3>
              <p class="module-info">Order notification emails, dynamic SMTP credentials, payment gateways, and banners.</p>
            </div>
            <div class="module-arrow">→</div>
          </router-link>
        </div>
      </div>

      <!-- =====================================================================
           SECTION 6: SYSTEM & STOREFRONT HEALTH BAR
           ===================================================================== -->
      <div class="system-status-card">
        <div class="status-grid">
          <div class="status-item">
            <span class="status-label">Database & Storefront</span>
            <span class="status-val status-val--online">● Operational</span>
          </div>
          <div class="status-item">
            <span class="status-label">Order Notifications</span>
            <span class="status-val status-val--online">● Active</span>
          </div>
          <div class="status-item">
            <span class="status-label">Payment Gateway</span>
            <span class="status-val status-val--online">● Cashfree & COD Ready</span>
          </div>
          <div class="status-item">
            <span class="status-label">Platform Version</span>
            <span class="status-val">Maya Sree Enterprise v2.5</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { 
  TrendingUp, 
  ShoppingBag, 
  Package, 
  AlertTriangle, 
  CheckCircle, 
  ArrowRight, 
  Eye, 
  RotateCcw, 
  ExternalLink, 
  PlusCircle, 
  Layers, 
  Boxes, 
  BarChart3, 
  ShieldCheck,
  Clock,
  XCircle,
  CornerUpLeft,
  Calendar,
  X
} from 'lucide-vue-next';

const loading = ref(false);
const statsLoaded = ref(false);
const errorMsg = ref('');

const activeExceptionTab = ref('pending');

// Date filter state for Payment Exceptions section
const exceptionDatePreset = ref('all');
const exceptionDateFrom = ref('');
const exceptionDateTo = ref('');

const formatDateToYMD = (d) => {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const formatDateLabel = (str) => {
  if (!str) return '—';
  try {
    const parts = str.split('-');
    if (parts.length === 3) {
      const d = new Date(parts[0], parts[1] - 1, parts[2]);
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    return str;
  } catch (e) {
    return str;
  }
};

const onExceptionPresetChange = () => {
  const today = new Date();
  if (exceptionDatePreset.value === 'today') {
    const str = formatDateToYMD(today);
    exceptionDateFrom.value = str;
    exceptionDateTo.value = str;
  } else if (exceptionDatePreset.value === 'yesterday') {
    const yest = new Date(today);
    yest.setDate(yest.getDate() - 1);
    const str = formatDateToYMD(yest);
    exceptionDateFrom.value = str;
    exceptionDateTo.value = str;
  } else if (exceptionDatePreset.value === 'last7') {
    const past = new Date(today);
    past.setDate(past.getDate() - 6);
    exceptionDateFrom.value = formatDateToYMD(past);
    exceptionDateTo.value = formatDateToYMD(today);
  } else if (exceptionDatePreset.value === 'last30') {
    const past = new Date(today);
    past.setDate(past.getDate() - 29);
    exceptionDateFrom.value = formatDateToYMD(past);
    exceptionDateTo.value = formatDateToYMD(today);
  } else if (exceptionDatePreset.value === 'this_month') {
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    exceptionDateFrom.value = formatDateToYMD(firstDay);
    exceptionDateTo.value = formatDateToYMD(today);
  } else if (exceptionDatePreset.value === 'all') {
    exceptionDateFrom.value = '';
    exceptionDateTo.value = '';
  }
  fetchDashboardData();
};

const onExceptionDateChange = () => {
  exceptionDatePreset.value = 'custom';
  fetchDashboardData();
};

const clearExceptionDate = () => {
  exceptionDatePreset.value = 'all';
  exceptionDateFrom.value = '';
  exceptionDateTo.value = '';
  fetchDashboardData();
};

const kpis = reactive({
  total_revenue: 0,
  total_orders: 0,
  today_revenue: 0,
  today_orders: 0,
  month_revenue: 0,
  month_orders: 0,
  active_products: 0,
  total_customers: 0,
  low_stock_count: 0,
});

const pipeline = ref({});
const lowStockAlerts = ref([]);
const recentOrders = ref([]);

const paymentExceptions = reactive({
  summary: {
    pending: { count: 0, amount: 0 },
    failed: { count: 0, amount: 0 },
    refunded: { count: 0, amount: 0 },
  },
  pending_orders: [],
  failed_orders: [],
  refunded_orders: [],
});

const currentExceptionOrders = computed(() => {
  if (activeExceptionTab.value === 'pending') {
    return paymentExceptions.pending_orders || [];
  }
  if (activeExceptionTab.value === 'failed') {
    return paymentExceptions.failed_orders || [];
  }
  if (activeExceptionTab.value === 'refunded') {
    return paymentExceptions.refunded_orders || [];
  }
  return [];
});

const formatCurrency = (val) => {
  const num = parseFloat(val) || 0;
  return num.toLocaleString('en-IN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const getBadgeClass = (status) => {
  switch (status) {
    case 'order_placed':
    case 'pending':
      return 'order-badge--warning';
    case 'order_confirmed':
    case 'processing':
    case 'ready_to_ship':
      return 'order-badge--primary';
    case 'shipped':
      return 'order-badge--info';
    case 'delivered':
      return 'order-badge--success';
    case 'cancelled':
    case 'returned':
      return 'order-badge--danger';
    default:
      return 'order-badge--secondary';
  }
};

const getPaymentBadgeClass = (paymentStatus) => {
  switch (paymentStatus) {
    case 'paid':
      return 'badge-payment--paid';
    case 'pending':
      return 'badge-payment--pending';
    case 'failed':
      return 'badge-payment--failed';
    case 'refunded':
    case 'partial_refunded':
      return 'badge-payment--refunded';
    default:
      return 'badge-payment--secondary';
  }
};

const fetchDashboardData = async () => {
  loading.value = true;
  errorMsg.value = '';
  try {
    const params = {};
    if (exceptionDateFrom.value) {
      params.exception_date_from = exceptionDateFrom.value;
    }
    if (exceptionDateTo.value) {
      params.exception_date_to = exceptionDateTo.value;
    }
    const res = await axios.get('/api/admin/dashboard/stats', { params });
    if (res.data && res.data.success && res.data.data) {
      const data = res.data.data;
      if (data.kpis) {
        Object.assign(kpis, data.kpis);
      }
      if (data.pipeline) {
        pipeline.value = data.pipeline;
      }
      if (data.low_stock_alerts) {
        lowStockAlerts.value = data.low_stock_alerts;
      }
      if (data.recent_orders) {
        recentOrders.value = data.recent_orders;
      }
      if (data.payment_exceptions) {
        Object.assign(paymentExceptions, data.payment_exceptions);
      }
      statsLoaded.value = true;
    }
  } catch (err) {
    console.error('Failed to load dashboard data:', err);
    errorMsg.value = 'Failed to load live store statistics. Please check server connectivity.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboardData();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap');

.dashboard-wrapper {
  display: flex;
  flex-direction: column;
  gap: 24px;
  max-width: 1320px;
  margin: 0 auto;
  padding: 8px 0 40px 0;
  font-family: 'Poppins', sans-serif;
}

/* ==========================================================================
   BRAND HERO SECTION
   ========================================================================== */
.brand-hero-card {
  background: linear-gradient(135deg, #FAF8F5 0%, #FFFFFF 100%);
  border: 1px solid rgba(212, 175, 55, 0.28);
  border-radius: 16px;
  padding: 24px 28px;
  box-shadow: 0 8px 24px rgba(110, 31, 58, 0.04), 0 2px 6px rgba(0, 0, 0, 0.02);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}

.brand-hero-main {
  display: flex;
  align-items: center;
  gap: 20px;
}

.brand-logo-container {
  width: 76px;
  height: 76px;
  flex-shrink: 0;
  background: #FFFFFF;
  border: 1.5px solid rgba(212, 175, 55, 0.35);
  border-radius: 16px;
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 6px 16px rgba(110, 31, 58, 0.08);
}

.brand-logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.brand-text-container {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.brand-badge-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 2px;
}

.brand-tag {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: #6E1F3A;
}

.brand-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border: 1px solid rgba(16, 185, 129, 0.25);
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.72rem;
  font-weight: 600;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10B981;
  animation: dotPulse 2s infinite ease-in-out;
}

@keyframes dotPulse {
  0%, 100% { opacity: 0.4; transform: scale(0.9); }
  50% { opacity: 1; transform: scale(1.2); }
}

.brand-company-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.9rem;
  font-weight: 700;
  color: #6E1F3A;
  margin: 0;
  line-height: 1.2;
  letter-spacing: -0.3px;
}

.brand-tagline {
  font-size: 0.86rem;
  color: #64748B;
  margin: 0;
}

.brand-hero-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-hero-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0 14px;
  height: 38px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #334155;
  background: #ffffff;
  border: 1.5px solid #cbd5e1;
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.btn-hero-action:hover {
  color: #6E1F3A;
  border-color: #6E1F3A;
  background: rgba(110, 31, 58, 0.04);
  transform: translateY(-1px);
}

.btn-hero-action--primary {
  background: #6E1F3A;
  color: #ffffff;
  border-color: #6E1F3A;
}

.btn-hero-action--primary:hover {
  background: #52152b;
  color: #ffffff;
  border-color: #52152b;
  box-shadow: 0 4px 12px rgba(110, 31, 58, 0.28);
}

.spin-anim {
  animation: spin 1s infinite linear;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ==========================================================================
   SECTION INDICATOR ROW
   ========================================================================== */
.section-indicator-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: -10px;
  flex-wrap: wrap;
  gap: 8px;
}

.section-indicator-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(16, 185, 129, 0.1);
  color: #047857;
  border: 1px solid rgba(16, 185, 129, 0.3);
  padding: 4px 12px;
  border-radius: 14px;
  font-size: 0.76rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

.live-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10B981;
}

.section-indicator-note {
  font-size: 0.78rem;
  color: #64748B;
  font-style: italic;
}

/* ==========================================================================
   LOADING & ERROR STATES
   ========================================================================== */
.dashboard-loading-state {
  text-align: center;
  padding: 4rem 2rem;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid var(--color-border);
}

.loading-spinner {
  width: 36px;
  height: 36px;
  border: 3px solid rgba(110, 31, 58, 0.15);
  border-top-color: #6E1F3A;
  border-radius: 50%;
  animation: spin 0.8s infinite linear;
  margin: 0 auto 1rem auto;
}

.loading-text {
  font-size: 0.92rem;
  color: #64748B;
  font-weight: 500;
}

.dashboard-error-banner {
  background: #fef2f2;
  border: 1px solid #fca5a5;
  color: #dc2626;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-error-retry {
  margin-left: auto;
  background: #dc2626;
  color: #ffffff;
  border: none;
  padding: 4px 12px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.dashboard-body {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* ==========================================================================
   KPI GRID
   ========================================================================== */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 16px;
}

.kpi-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px 22px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  gap: 6px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  position: relative;
  overflow: hidden;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
}

.kpi-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.kpi-card__title {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #64748B;
}

.kpi-card__icon-badge {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  color: #475569;
}

.kpi-card--maroon .kpi-card__icon-badge {
  background: rgba(110, 31, 58, 0.08);
  color: #6E1F3A;
}

.kpi-card--gold .kpi-card__icon-badge {
  background: rgba(212, 175, 55, 0.12);
  color: #b45309;
}

.kpi-card--emerald .kpi-card__icon-badge {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.kpi-card--warning .kpi-card__icon-badge {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.kpi-card--success .kpi-card__icon-badge {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.kpi-card__value {
  font-size: 1.85rem;
  font-weight: 700;
  color: #1E293B;
  letter-spacing: -0.5px;
  line-height: 1.1;
  margin-top: 4px;
}

.kpi-card__footer {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
  font-size: 0.78rem;
  flex-wrap: wrap;
}

.kpi-trend {
  font-weight: 700;
}

.kpi-trend--up {
  color: #059669;
}

.kpi-highlight {
  font-weight: 700;
  color: #6E1F3A;
}

.kpi-subtext {
  color: #64748B;
}

.kpi-tag {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 10px;
}

.kpi-tag--warning {
  background: #fee2e2;
  color: #dc2626;
}

.kpi-tag--success {
  background: #dcfce7;
  color: #15803d;
}

.kpi-link {
  margin-left: auto;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6E1F3A;
  text-decoration: none;
}

.kpi-link:hover {
  text-decoration: underline;
}

/* ==========================================================================
   DASHBOARD CARD SHARED STYLES
   ========================================================================== */
.dashboard-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.dashboard-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 12px;
}

.dashboard-card__title {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1E293B;
  margin: 0;
}

.dashboard-card__subtitle {
  font-size: 0.8rem;
  color: #64748B;
  margin: 2px 0 0 0;
}

.dashboard-card__action-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #6E1F3A;
  text-decoration: none;
  padding: 4px 10px;
  border-radius: 6px;
  background: rgba(110, 31, 58, 0.04);
  transition: all 0.15s ease;
}

.dashboard-card__action-link:hover {
  background: rgba(110, 31, 58, 0.1);
  transform: translateX(2px);
}

.badge-mini {
  font-size: 0.68rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-mini--paid {
  background: rgba(16, 185, 129, 0.12);
  color: #059669;
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.badge-mini--warning {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* ==========================================================================
   PIPELINE GRID
   ========================================================================== */
.pipeline-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
}

.pipeline-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 14px;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  gap: 4px;
  transition: all 0.2s ease;
}

.pipeline-card:hover {
  border-color: #6E1F3A;
  background: #ffffff;
  box-shadow: 0 4px 12px rgba(110, 31, 58, 0.1);
  transform: translateY(-2px);
}

.pipeline-card__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.pipeline-icon {
  font-size: 1.25rem;
}

.pipeline-count {
  font-size: 1.35rem;
  font-weight: 700;
  color: #1E293B;
}

.pipeline-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #475569;
}

.pipeline-amount {
  font-size: 0.8rem;
  font-weight: 700;
  color: #6E1F3A;
}

/* ==========================================================================
   TWO-COLUMN SPLIT GRID
   ========================================================================== */
.dashboard-split-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr);
  gap: 24px;
}

@media (max-width: 960px) {
  .dashboard-split-grid {
    grid-template-columns: 1fr;
  }
}

/* Recent Orders Table */
.recent-orders-table-wrap {
  overflow-x: auto;
}

.recent-orders-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
}

.recent-orders-table th {
  text-align: left;
  padding: 10px 12px;
  font-weight: 600;
  color: #64748B;
  border-bottom: 1px solid #e2e8f0;
  text-transform: uppercase;
  font-size: 0.72rem;
  letter-spacing: 0.5px;
}

.recent-orders-table td {
  padding: 12px 12px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.recent-orders-table tbody tr:hover {
  background: #faf8f5;
}

.order-num-link {
  font-weight: 700;
  color: #6E1F3A;
  text-decoration: none;
}

.order-num-link:hover {
  text-decoration: underline;
}

.customer-info-cell {
  display: flex;
  flex-direction: column;
}

.customer-name {
  font-weight: 600;
  color: #1E293B;
}

.customer-email {
  font-size: 0.72rem;
  color: #94A3B8;
}

.order-date-cell {
  display: flex;
  flex-direction: column;
  white-space: nowrap;
}

.order-time-ago {
  font-size: 0.7rem;
  color: #94A3B8;
}

.order-amount-cell {
  font-weight: 700;
  color: #1E293B;
  white-space: nowrap;
}

.badge-payment {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  white-space: nowrap;
}

.badge-payment--paid {
  background: rgba(16, 185, 129, 0.12);
  color: #047857;
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.badge-payment--pending {
  background: rgba(245, 158, 11, 0.12);
  color: #b45309;
  border: 1px solid rgba(245, 158, 11, 0.25);
}

.badge-payment--failed {
  background: rgba(239, 68, 68, 0.12);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.25);
}

.badge-payment--refunded {
  background: rgba(100, 116, 139, 0.12);
  color: #475569;
  border: 1px solid rgba(100, 116, 139, 0.25);
}

.order-badge {
  display: inline-block;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 600;
  white-space: nowrap;
}

.order-badge--warning {
  background: #fef3c7;
  color: #b45309;
}

.order-badge--primary {
  background: rgba(110, 31, 58, 0.08);
  color: #6E1F3A;
}

.order-badge--info {
  background: #e0f2fe;
  color: #0369a1;
}

.order-badge--success {
  background: #dcfce7;
  color: #15803d;
}

.order-badge--danger {
  background: #fee2e2;
  color: #dc2626;
}

.order-badge--secondary {
  background: #f1f5f9;
  color: #475569;
}

/* Action Icon Button */
.btn-action-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  border-radius: 8px;
  background: rgba(110, 31, 58, 0.06);
  color: #6E1F3A;
  border: 1.5px solid rgba(110, 31, 58, 0.25);
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-action-icon:hover {
  background: #6E1F3A;
  color: #ffffff;
  border-color: #6E1F3A;
  box-shadow: 0 3px 8px rgba(110, 31, 58, 0.25);
  transform: translateY(-1px);
}

/* Empty States */
.empty-state-box {
  text-align: center;
  padding: 2.5rem 1rem;
  color: #94A3B8;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.empty-icon {
  color: #cbd5e1;
}

.empty-icon--success {
  color: #10b981;
}

/* Stock Alerts List */
.stock-alerts-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.stock-alert-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border: 1px solid #f1f5f9;
  border-radius: 10px;
  background: #fafaf9;
  gap: 12px;
}

.stock-alert-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.stock-alert-product {
  font-weight: 600;
  font-size: 0.82rem;
  color: #1E293B;
}

.stock-alert-meta {
  font-size: 0.72rem;
  color: #64748B;
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

.stock-alert-qty-wrap {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.stock-qty-badge {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  white-space: nowrap;
}

.stock-qty-badge--out {
  background: #fee2e2;
  color: #dc2626;
}

.stock-qty-badge--low {
  background: #fef3c7;
  color: #b45309;
}

.stock-threshold-label {
  font-size: 0.68rem;
  color: #94A3B8;
}

.stock-card-footer {
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1px dashed #e2e8f0;
}

.btn-inward-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 100%;
  height: 36px;
  border-radius: 8px;
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  color: #475569;
  font-size: 0.8rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-inward-link:hover {
  background: #ffffff;
  color: #6E1F3A;
  border-color: #6E1F3A;
}

/* ==========================================================================
   PAYMENT EXCEPTIONS SECTION (PENDING, FAILED, REFUNDED)
   ========================================================================== */
.payment-exceptions-card {
  border: 1.5px solid #fee2e2;
  background: #ffffff;
}

/* Section Date Filter Panel */
.exception-filter-panel {
  background: #fdf8f6;
  border: 1px solid #fee2e2;
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 20px;
}

.exception-filter-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
  width: 100%;
}

.exception-filter-field {
  display: flex;
  align-items: center;
  gap: 6px;
}

.exception-filter-label {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.8rem;
  font-weight: 700;
  color: #7f1d1d;
  white-space: nowrap;
}

.exception-select {
  height: 38px;
  padding: 0 28px 0 10px;
  font-size: 0.82rem;
  font-weight: 600;
  border-radius: 8px;
  border: 1.5px solid #fca5a5;
  background-color: #ffffff;
  color: #1e293b;
  cursor: pointer;
  outline: none;
  transition: all 0.2s ease;
}

.exception-select:focus {
  border-color: #991b1b;
  box-shadow: 0 0 0 3px rgba(153, 27, 27, 0.12);
}

.exception-range-box {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ffffff;
  border: 1.5px solid #fca5a5;
  border-radius: 8px;
  padding: 2px 8px;
  height: 38px;
  box-sizing: border-box;
}

.exception-range-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.exception-range-tag {
  font-size: 0.7rem;
  font-weight: 700;
  color: #7f1d1d;
  text-transform: uppercase;
}

.exception-native-date {
  border: none;
  background: transparent;
  padding: 2px 2px;
  font-size: 0.8rem;
  font-family: inherit;
  color: #1e293b;
  outline: none;
  cursor: pointer;
}

.exception-native-date:focus {
  outline: none;
}

.exception-range-sep {
  color: #991b1b;
  font-size: 0.82rem;
  font-weight: 700;
}

.btn-date-clear {
  background: #fee2e2;
  border: none;
  color: #991b1b;
  cursor: pointer;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  transition: all 0.15s ease;
}

.btn-date-clear:hover {
  background: #ef4444;
  color: #ffffff;
}

.exception-filter-active-info {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: auto;
  flex-wrap: wrap;
}

.exception-filter-date-tag {
  font-size: 0.76rem;
  font-weight: 600;
  color: #991b1b;
  background: rgba(153, 27, 27, 0.08);
  border: 1px solid rgba(153, 27, 27, 0.2);
  padding: 4px 10px;
  border-radius: 6px;
  white-space: nowrap;
}

.btn-reset-exception {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0 12px;
  height: 34px;
  font-size: 0.78rem;
  font-weight: 600;
  color: #7f1d1d;
  background: #fee2e2;
  border: 1px solid #fca5a5;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-reset-exception:hover {
  background: #ef4444;
  color: #ffffff;
  border-color: #ef4444;
  transform: translateY(-1px);
}

.exception-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 14px;
  margin-bottom: 20px;
}

.exception-metric-card {
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px 18px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.exception-metric-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.exception-metric-card--active {
  background: #ffffff;
  border-color: #6E1F3A;
  box-shadow: 0 4px 14px rgba(110, 31, 58, 0.12);
}

.exception-metric-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.exception-metric-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.exception-metric-icon--amber { background: #fef3c7; color: #b45309; }
.exception-metric-icon--red { background: #fee2e2; color: #dc2626; }
.exception-metric-icon--gray { background: #f1f5f9; color: #475569; }

.exception-metric-count {
  font-size: 1.45rem;
  font-weight: 700;
  color: #1E293B;
}

.exception-metric-title {
  font-size: 0.84rem;
  font-weight: 700;
  color: #334155;
  margin-top: 2px;
}

.exception-metric-amount {
  font-size: 1.15rem;
  font-weight: 700;
  color: #6E1F3A;
}

.exception-metric-desc {
  font-size: 0.72rem;
  color: #64748B;
  margin-top: 2px;
}

.exception-table-container {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}

.exception-tabs-bar {
  display: flex;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  overflow-x: auto;
}

.exception-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 12px 18px;
  font-size: 0.8rem;
  font-weight: 600;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  color: #64748B;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s ease;
}

.exception-tab-btn:hover {
  color: #6E1F3A;
}

.exception-tab-btn--active {
  color: #6E1F3A;
  background: #ffffff;
  border-bottom-color: #6E1F3A;
  font-weight: 700;
}

.exception-table-footer {
  padding: 10px 16px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
}

.exception-view-all-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.78rem;
  font-weight: 600;
  color: #6E1F3A;
  text-decoration: none;
}

.exception-view-all-link:hover {
  text-decoration: underline;
}

/* ==========================================================================
   MODULES GRID
   ========================================================================== */
.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.module-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.module-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(110, 31, 58, 0.08);
  border-color: rgba(110, 31, 58, 0.35);
}

.module-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.module-icon--maroon { background: rgba(110, 31, 58, 0.08); color: #6E1F3A; }
.module-icon--gold { background: rgba(212, 175, 55, 0.12); color: #b45309; }
.module-icon--blue { background: rgba(2, 132, 199, 0.1); color: #0284c7; }
.module-icon--emerald { background: rgba(16, 185, 129, 0.1); color: #059669; }
.module-icon--purple { background: rgba(147, 51, 234, 0.1); color: #9333ea; }
.module-icon--slate { background: rgba(100, 116, 139, 0.1); color: #475569; }

.module-content {
  flex: 1;
}

.module-name {
  font-size: 0.92rem;
  font-weight: 700;
  color: #1E293B;
  margin: 0 0 2px 0;
}

.module-info {
  font-size: 0.75rem;
  color: #64748B;
  margin: 0;
  line-height: 1.35;
}

.module-arrow {
  color: #94A3B8;
  font-size: 1.1rem;
  transition: transform 0.2s ease, color 0.2s ease;
}

.module-card:hover .module-arrow {
  transform: translateX(3px);
  color: #6E1F3A;
}

/* ==========================================================================
   SYSTEM STATUS FOOTER
   ========================================================================== */
.system-status-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 20px;
}

.status-grid {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.status-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.76rem;
}

.status-label {
  color: #64748B;
  font-weight: 500;
}

.status-val {
  font-weight: 600;
  color: #334155;
}

.status-val--online {
  color: #16a34a;
}
</style>
