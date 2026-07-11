<template>
  <!-- Welcome and Date Filter Header -->
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Welcome back, John! 👋</h1>
      <span class="admin-page__subtitle">Here's what's happening with your business today.</span>
    </div>
    <div class="admin-page__actions">
      <div class="date-picker-select">
        <span>📅</span>
        <span>May 12, 2024 - Jun 12, 2024</span>
        <span style="font-size: 0.75rem; color: var(--color-text-muted);">▼</span>
      </div>
    </div>
  </div>

  <!-- 4-Column Stats Grid -->
  <div class="stats-grid-4">
    <!-- Total Revenue -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__icon-wrap stat-card__icon-wrap--purple">
          💵
        </div>
        <button class="stat-card__menu-btn">•••</button>
      </div>
      <div>
        <div class="stat-card__title">Total Revenue</div>
        <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ kpis.revenue.value }}</div>
      </div>
      <div class="stat-card__trend-row">
        <div :class="['stat-card__trend-info', kpis.revenue.trend_up ? 'stat-card__trend-info--up' : 'stat-card__trend-info--down']">
          <span>{{ kpis.revenue.trend }}</span>
          <span class="stat-card__trend-label">{{ kpis.revenue.label }}</span>
        </div>
        <!-- Sparkline SVG -->
        <svg viewBox="0 0 100 24" width="70" height="20" style="overflow: visible;">
          <path d="M 0 20 Q 15 5 30 15 T 60 5 T 90 12 T 100 2" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round"></path>
          <path d="M 0 20 Q 15 5 30 15 T 60 5 T 90 12 T 100 2 L 100 24 L 0 24 Z" fill="url(#grad-revenue)" opacity="0.15"></path>
          <defs>
            <linearGradient id="grad-revenue" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#6366f1"></stop>
              <stop offset="100%" stop-color="transparent"></stop>
            </linearGradient>
          </defs>
        </svg>
      </div>
    </div>

    <!-- New Customers -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__icon-wrap stat-card__icon-wrap--green">
          👥
        </div>
        <button class="stat-card__menu-btn">•••</button>
      </div>
      <div>
        <div class="stat-card__title">New Customers</div>
        <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ kpis.customers.value }}</div>
      </div>
      <div class="stat-card__trend-row">
        <div :class="['stat-card__trend-info', kpis.customers.trend_up ? 'stat-card__trend-info--up' : 'stat-card__trend-info--down']">
          <span>{{ kpis.customers.trend }}</span>
          <span class="stat-card__trend-label">{{ kpis.customers.label }}</span>
        </div>
        <!-- Sparkline SVG -->
        <svg viewBox="0 0 100 24" width="70" height="20" style="overflow: visible;">
          <path d="M 0 18 Q 20 8 40 12 T 80 5 T 100 15" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round"></path>
          <path d="M 0 18 Q 20 8 40 12 T 80 5 T 100 15 L 100 24 L 0 24 Z" fill="url(#grad-customers)" opacity="0.15"></path>
          <defs>
            <linearGradient id="grad-customers" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#10b981"></stop>
              <stop offset="100%" stop-color="transparent"></stop>
            </linearGradient>
          </defs>
        </svg>
      </div>
    </div>

    <!-- Total Orders -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__icon-wrap stat-card__icon-wrap--orange">
          🛍️
        </div>
        <button class="stat-card__menu-btn">•••</button>
      </div>
      <div>
        <div class="stat-card__title">Total Orders</div>
        <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ kpis.orders.value }}</div>
      </div>
      <div class="stat-card__trend-row">
        <div :class="['stat-card__trend-info', kpis.orders.trend_up ? 'stat-card__trend-info--up' : 'stat-card__trend-info--down']">
          <span>{{ kpis.orders.trend }}</span>
          <span class="stat-card__trend-label">{{ kpis.orders.label }}</span>
        </div>
        <!-- Sparkline SVG -->
        <svg viewBox="0 0 100 24" width="70" height="20" style="overflow: visible;">
          <path d="M 0 20 L 20 15 L 40 18 L 60 8 L 80 12 L 100 5" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"></path>
          <path d="M 0 20 L 20 15 L 40 18 L 60 8 L 80 12 L 100 5 L 100 24 L 0 24 Z" fill="url(#grad-orders)" opacity="0.15"></path>
          <defs>
            <linearGradient id="grad-orders" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#f59e0b"></stop>
              <stop offset="100%" stop-color="transparent"></stop>
            </linearGradient>
          </defs>
        </svg>
      </div>
    </div>

    <!-- Conversion Rate -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__icon-wrap stat-card__icon-wrap--red">
          📈
        </div>
        <button class="stat-card__menu-btn">•••</button>
      </div>
      <div>
        <div class="stat-card__title">Conversion Rate</div>
        <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ kpis.conversion.value }}</div>
      </div>
      <div class="stat-card__trend-row">
        <div :class="['stat-card__trend-info', kpis.conversion.trend_up ? 'stat-card__trend-info--up' : 'stat-card__trend-info--down']">
          <span>{{ kpis.conversion.trend }}</span>
          <span class="stat-card__trend-label">{{ kpis.conversion.label }}</span>
        </div>
        <!-- Sparkline SVG -->
        <svg viewBox="0 0 100 24" width="70" height="20" style="overflow: visible;">
          <path d="M 0 5 Q 30 10 50 18 T 100 22" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round"></path>
          <path d="M 0 5 Q 30 10 50 18 T 100 22 L 100 24 L 0 24 Z" fill="url(#grad-conversion)" opacity="0.15"></path>
          <defs>
            <linearGradient id="grad-conversion" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#ef4444"></stop>
              <stop offset="100%" stop-color="transparent"></stop>
            </linearGradient>
          </defs>
        </svg>
      </div>
    </div>
  </div>

  <!-- Primary Middle Dashboard Row (Sales Overview & Top Products) -->
  <div class="dashboard-grid">
    <!-- Sales Overview Card -->
    <div class="glass-panel" style="padding: var(--spacing-lg);">
      <div class="card-header-flex">
        <div class="card-header-title">Sales Overview</div>
        <div class="date-picker-select" style="padding: 0.25rem 0.75rem; font-size: 0.75rem;">
          <span>Weekly</span>
          <span style="font-size: 0.6rem; color: var(--color-text-muted);">▼</span>
        </div>
      </div>

      <!-- Line Chart Graphic (Pure SVG) -->
      <div class="sales-chart-wrapper">
        <svg viewBox="0 0 600 280" width="100%" height="100%" style="overflow: visible;">
          <!-- Horizontal Grid Lines -->
          <line x1="30" y1="20" x2="570" y2="20" stroke="var(--color-border)" stroke-width="1" stroke-dasharray="2,2"></line>
          <line x1="30" y1="75" x2="570" y2="75" stroke="var(--color-border)" stroke-width="1" stroke-dasharray="2,2"></line>
          <line x1="30" y1="130" x2="570" y2="130" stroke="var(--color-border)" stroke-width="1" stroke-dasharray="2,2"></line>
          <line x1="30" y1="185" x2="570" y2="185" stroke="var(--color-border)" stroke-width="1" stroke-dasharray="2,2"></line>
          <line x1="30" y1="240" x2="570" y2="240" stroke="var(--color-border)" stroke-width="1"></line>

          <!-- Y-Axis Labels -->
          <text x="0" y="24" fill="var(--color-text-muted)" font-size="10" alignment-baseline="middle">$40K</text>
          <text x="0" y="79" fill="var(--color-text-muted)" font-size="10" alignment-baseline="middle">$30K</text>
          <text x="0" y="134" fill="var(--color-text-muted)" font-size="10" alignment-baseline="middle">$20K</text>
          <text x="0" y="189" fill="var(--color-text-muted)" font-size="10" alignment-baseline="middle">$10K</text>
          <text x="5" y="244" fill="var(--color-text-muted)" font-size="10" alignment-baseline="middle">$0</text>

          <!-- Chart Area Gradient Defs -->
          <defs>
            <linearGradient id="grad-sales" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#6366f1" stop-opacity="0.3"></stop>
              <stop offset="100%" stop-color="#6366f1" stop-opacity="0"></stop>
            </linearGradient>
          </defs>

          <!-- Last Period (Dashed Line) -->
          <path d="M 40 210 Q 110 190 145 150 T 250 140 T 355 105 T 460 135 T 560 120" fill="none" stroke="#818cf8" stroke-width="2" stroke-linecap="round" stroke-dasharray="4,4" opacity="0.6"></path>

          <!-- This Period Gradient Area Fill -->
          <path d="M 40 240 L 40 180 C 90 170, 115 70, 145 120 C 175 170, 220 180, 250 145 C 280 110, 315 50, 360 80 C 405 110, 430 180, 460 115 C 490 50, 530 120, 560 100 L 560 240 Z" fill="url(#grad-sales)"></path>

          <!-- This Period (Solid Line) -->
          <path d="M 40 180 C 90 170, 115 70, 145 120 C 175 170, 220 180, 250 145 C 280 110, 315 50, 360 80 C 405 110, 430 180, 460 115 C 490 50, 530 120, 560 100" fill="none" stroke="#6366f1" stroke-width="3" stroke-linecap="round"></path>

          <!-- Active Dots and Guides -->
          <line x1="360" y1="20" x2="360" y2="240" stroke="var(--color-primary)" stroke-width="1" stroke-opacity="0.3"></line>
          <circle cx="360" cy="80" r="6" fill="#6366f1" stroke="#ffffff" stroke-width="2"></circle>
          <circle cx="460" cy="115" r="4" fill="#6366f1" stroke="#ffffff" stroke-width="1.5"></circle>
          <circle cx="145" cy="120" r="4" fill="#6366f1" stroke="#ffffff" stroke-width="1.5"></circle>

          <!-- X-Axis Labels -->
          <text x="40" y="260" fill="var(--color-text-muted)" font-size="10" text-anchor="middle">May 12</text>
          <text x="145" y="260" fill="var(--color-text-muted)" font-size="10" text-anchor="middle">May 19</text>
          <text x="250" y="260" fill="var(--color-text-muted)" font-size="10" text-anchor="middle">May 26</text>
          <text x="360" y="260" fill="var(--color-text-muted)" font-size="10" text-anchor="middle">Jun 02</text>
          <text x="460" y="260" fill="var(--color-text-muted)" font-size="10" text-anchor="middle">Jun 09</text>
          <text x="560" y="260" fill="var(--color-text-muted)" font-size="10" text-anchor="middle">Jun 12</text>
        </svg>
      </div>

      <!-- Legend Indicator -->
      <div style="display: flex; align-items: center; justify-content: center; gap: 2rem; margin-top: 1rem;">
        <div style="display: flex; align-items: center; gap: var(--spacing-sm); font-size: 0.8rem; color: var(--color-text-secondary);">
          <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #6366f1;"></span>
          <span>This Period</span>
        </div>
        <div style="display: flex; align-items: center; gap: var(--spacing-sm); font-size: 0.8rem; color: var(--color-text-secondary);">
          <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; border: 2px dashed #818cf8; background: transparent;"></span>
          <span>Last Period</span>
        </div>
      </div>
    </div>

    <!-- Top Products Card -->
    <div class="glass-panel" style="padding: var(--spacing-lg);">
      <div class="card-header-flex">
        <div class="card-header-title">Top Products</div>
        <a href="#" class="card-header-link" @click.prevent>View All</a>
      </div>

      <div class="top-products-list">
        <div class="product-list-item">
          <div class="product-list-item__left">
            <div class="product-list-item__img">🎧</div>
            <div>
              <div class="product-list-item__name">Wireless Headphones</div>
              <div class="product-list-item__category">Electronics</div>
            </div>
          </div>
          <div class="product-list-item__right">
            <div class="product-list-item__price">$4,950</div>
            <div class="product-list-item__trend" style="color: #ef4444;">↓ 3.2%</div>
          </div>
        </div>

        <div class="product-list-item">
          <div class="product-list-item__left">
            <div class="product-list-item__img">⌚</div>
            <div>
              <div class="product-list-item__name">Smart Watch Series 5</div>
              <div class="product-list-item__category">Electronics</div>
            </div>
          </div>
          <div class="product-list-item__right">
            <div class="product-list-item__price">$4,120</div>
            <div class="product-list-item__trend" style="color: #10b981;">↑ 5.7%</div>
          </div>
        </div>

        <div class="product-list-item">
          <div class="product-list-item__left">
            <div class="product-list-item__img">🎒</div>
            <div>
              <div class="product-list-item__name">Leather Backpack</div>
              <div class="product-list-item__category">Accessories</div>
            </div>
          </div>
          <div class="product-list-item__right">
            <div class="product-list-item__price">$2,850</div>
            <div class="product-list-item__trend" style="color: #10b981;">↑ 2.4%</div>
          </div>
        </div>

        <div class="product-list-item">
          <div class="product-list-item__left">
            <div class="product-list-item__img">👟</div>
            <div>
              <div class="product-list-item__name">Minimalist Sneakers</div>
              <div class="product-list-item__category">Footwear</div>
            </div>
          </div>
          <div class="product-list-item__right">
            <div class="product-list-item__price">$2,450</div>
            <div class="product-list-item__trend" style="color: #ef4444;">↓ 1.1%</div>
          </div>
        </div>

        <div class="product-list-item">
          <div class="product-list-item__left">
            <div class="product-list-item__img">🕶️</div>
            <div>
              <div class="product-list-item__name">Sunglasses UV Protect</div>
              <div class="product-list-item__category">Accessories</div>
            </div>
          </div>
          <div class="product-list-item__right">
            <div class="product-list-item__price">$1,850</div>
            <div class="product-list-item__trend" style="color: #10b981;">↑ 4.3%</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Secondary Bottom Dashboard Row (Recent Orders & Traffic Sources) -->
  <div class="dashboard-grid">
    <!-- Recent Orders Card -->
    <div class="glass-panel" style="padding: var(--spacing-lg);">
      <div class="card-header-flex">
        <div class="card-header-title">Recent Orders</div>
        <a href="#" class="card-header-link" @click.prevent>View All</a>
      </div>

      <div style="overflow-x: auto; width: 100%;">
        <!-- Mobile Cards View -->
        <div class="mobile-data-list">
          <div class="mobile-data-card" v-for="order in recentOrders" :key="order.id">
            <div class="mdc-header">
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: bold; flex-shrink: 0;">
                  {{ order.customer_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() }}
                </div>
                <div>
                  <div class="mdc-title">{{ order.order_id }}</div>
                  <div class="mdc-date">{{ order.date }}</div>
                </div>
              </div>
            </div>
            
            <div class="mdc-body">
              <div class="mdc-customer">
                <span class="mdc-name">{{ order.customer_name }}</span>
              </div>
              <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between;">
                <span>Total: <strong>₹{{ parseFloat(order.amount).toFixed(2) }}</strong></span>
              </div>
            </div>
            
            <div class="mdc-footer">
              <div class="mdc-badges">
                <span :class="['badge', order.status === 'Completed' ? 'badge--success' : '']" 
                      :style="order.status !== 'Completed' ? 'background: rgba(99, 102, 241, 0.1); color: #6366f1;' : ''">
                  {{ order.status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table View -->
        <table class="data-table desktop-data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in recentOrders" :key="order.id">
              <td><strong>{{ order.order_id }}</strong></td>
              <td>
                <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                  <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: bold;">
                    {{ order.customer_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() }}
                  </div>
                  <span>{{ order.customer_name }}</span>
                </div>
              </td>
              <td><strong>₹{{ parseFloat(order.amount).toFixed(2) }}</strong></td>
              <td>
                <span :class="['badge', order.status === 'Completed' ? 'badge--success' : '']" 
                      :style="order.status !== 'Completed' ? 'background: rgba(99, 102, 241, 0.1); color: #6366f1;' : ''">
                  {{ order.status }}
                </span>
              </td>
              <td>{{ order.date }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Low Stock Alerts Card -->
    <div class="glass-panel" style="padding: var(--spacing-lg);">
      <div class="card-header-flex">
        <div class="card-header-title">Low Stock Alerts</div>
        <router-link to="/admin/inventory" class="card-header-link">View Inventory</router-link>
      </div>

      <div class="top-products-list" v-if="lowStockAlerts.length > 0">
        <div class="product-list-item" v-for="item in lowStockAlerts" :key="item.id">
          <div class="product-list-item__left">
            <div class="product-list-item__img" style="color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2);">⚠️</div>
            <div>
              <div class="product-list-item__name">{{ item.product_name }}</div>
              <div class="product-list-item__category" style="font-family: monospace;">SKU: {{ item.sku }} | Size: {{ item.size }} | Color: {{ item.color }}</div>
            </div>
          </div>
          <div class="product-list-item__right">
            <div class="product-list-item__price" style="color: var(--color-danger);">{{ item.stock_quantity }} left</div>
            <div class="product-list-item__trend" style="color: var(--color-text-muted);">Limit: {{ item.threshold }}</div>
          </div>
        </div>
      </div>
      <div v-else style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        ✅ All inventory stocks are healthy.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Reactive states for stats data
const loading = ref(true);
const error = ref(null);

const kpis = ref({
  revenue: { value: '$24,750.00', trend: '↑ 12.5%', trend_up: true, label: 'vs last month' },
  customers: { value: '0', trend: '↑ 8.2%', trend_up: true, label: 'vs last month' },
  orders: { value: '0', trend: '↑ 15.3%', trend_up: true, label: 'vs last month' },
  conversion: { value: '2.43%', trend: '↓ 2.1%', trend_up: false, label: 'vs last month' }
});

const lowStockAlerts = ref([]);
const recentOrders = ref([]);

const fetchStats = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/admin/dashboard/stats');
    if (response.data && response.data.success) {
      const payload = response.data.data;
      if (payload.kpis) kpis.value = payload.kpis;
      if (payload.low_stock_alerts) lowStockAlerts.value = payload.low_stock_alerts;
      if (payload.recent_orders) recentOrders.value = payload.recent_orders;
    }
  } catch (err) {
    console.error('Failed to load dashboard stats:', err);
    error.value = err.response?.data?.message || 'Failed to load dashboard statistics';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStats();
});
</script>
