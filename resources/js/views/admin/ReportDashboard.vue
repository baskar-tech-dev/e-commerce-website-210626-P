<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Reports & Business Analytics</h1>
      <span class="admin-page__subtitle">Track platform sales trends, inventory valuations, and customer behaviors.</span>
    </div>
  </div>

  <!-- Tab Bar Selector -->
  <div style="display: flex; gap: var(--spacing-sm); border-bottom: 1px solid var(--color-border); padding-bottom: 1px; margin-bottom: var(--spacing-lg); margin-top: var(--spacing-md);">
    <button 
      v-for="tab in ['sales', 'inventory', 'customers']" 
      :key="tab"
      @click="activeTab = tab"
      :class="['btn', activeTab === tab ? 'btn--primary' : 'btn--secondary']"
      style="border-bottom-left-radius: 0; border-bottom-right-radius: 0; text-transform: capitalize;"
    >
      <span v-if="tab === 'sales'">📊 Sales Analysis</span>
      <span v-else-if="tab === 'inventory'">📦 Warehouse Inventory</span>
      <span v-else-if="tab === 'customers'">👥 Customers Growth</span>
    </button>
  </div>

  <!-- Error Alerts -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- LOADING STATE -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Gathering data parameters...</div>
  </div>

  <div v-else>
    <!-- ================= SALES TAB ================= -->
    <div v-if="activeTab === 'sales'">
      <!-- Date Range Controls -->
      <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg);">
        <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); align-items: center; justify-content: space-between;">
          <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
              <label style="font-size: 0.8rem; color: var(--color-text-muted);">From Date:</label>
              <input type="date" v-model="salesDates.start" class="form-input" style="padding: 0.25rem var(--spacing-sm); width: 150px;" />
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
              <label style="font-size: 0.8rem; color: var(--color-text-muted);">To Date:</label>
              <input type="date" v-model="salesDates.end" class="form-input" style="padding: 0.25rem var(--spacing-sm); width: 150px;" />
            </div>
          </div>
          <div style="display: flex; gap: var(--spacing-sm);">
            <button class="btn btn--secondary btn--sm" @click="fetchSalesReport">Apply Filters</button>
            <button class="btn btn--secondary btn--sm" @click="exportCSV">Export CSV</button>
          </div>
        </div>
      </div>

      <!-- Sales KPIs -->
      <div class="stats-grid-4" style="margin-bottom: var(--spacing-lg);">
        <div class="stat-card-new">
          <div class="stat-card__title">Total Revenue</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">₹{{ salesData.kpis?.revenue?.toFixed(2) }}</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">Orders Volume</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ salesData.kpis?.orders }}</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">Average Order Value</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">₹{{ salesData.kpis?.aov?.toFixed(2) }}</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">Total Units Dispatched</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ salesData.kpis?.units_sold }} items</div>
        </div>
      </div>

      <!-- Charts & Visual Layout Split -->
      <div class="responsive-grid-2-1" style="gap: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
        <!-- Revenue Trend Line Sparkline (Inline SVG) -->
        <div class="glass-panel" style="padding: var(--spacing-lg); display: flex; flex-direction: column;">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Revenue Aggregated Trend Timeline</div>
          
          <div style="flex: 1; min-height: 220px; display: flex; align-items: center; justify-content: center; position: relative;">
            <svg v-if="salesData.revenue_trend?.length > 1" width="100%" height="200" viewBox="0 0 500 200" preserveAspectRatio="none">
              <!-- Grid lines -->
              <line x1="0" y1="50" x2="500" y2="50" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
              <line x1="0" y1="100" x2="500" y2="100" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
              <line x1="0" y1="150" x2="500" y2="150" stroke="rgba(255,255,255,0.05)" stroke-width="1" />
              
              <!-- Trend Line Path -->
              <path 
                :d="revenueTrendPath" 
                fill="none" 
                stroke="var(--color-primary)" 
                stroke-width="3" 
                stroke-linecap="round"
                stroke-linejoin="round"
              />

              <!-- Area under trend path -->
              <path 
                :d="revenueTrendAreaPath" 
                fill="url(#revenueGrad)" 
                opacity="0.15"
              />

              <!-- Gradients -->
              <defs>
                <linearGradient id="revenueGrad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="var(--color-primary)" />
                  <stop offset="100%" stop-color="var(--color-primary)" stop-opacity="0" />
                </linearGradient>
              </defs>

              <!-- Data nodes -->
              <circle 
                v-for="(pt, idx) in trendCoordinates" 
                :key="idx"
                :cx="pt.x" 
                :cy="pt.y" 
                r="4" 
                fill="var(--color-primary)" 
                stroke="#fff" 
                stroke-width="1"
              />
            </svg>
            <div v-else style="color: var(--color-text-muted); font-size: 0.9rem;">Insufficient trend timeline points to graph.</div>
          </div>

          <!-- Timeline Dates axis -->
          <div style="display: flex; justify-content: space-between; padding-top: var(--spacing-sm); border-top: 1px solid var(--color-border); font-size: 0.75rem; color: var(--color-text-muted);">
            <span v-for="(t, idx) in salesData.revenue_trend" :key="idx">
              {{ t.date }}
            </span>
          </div>
        </div>

        <!-- Sales Splits (Category and Payment method) -->
        <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
          <!-- Sales by Category -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Revenue By Categories</div>
            <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
              <div v-for="(split, idx) in salesData.category_splits" :key="idx">
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.25rem;">
                  <span style="font-weight: 500; color: #1e293b;">{{ split.category }}</span>
                  <span style="color: var(--color-text-muted);">₹{{ split.amount.toFixed(2) }}</span>
                </div>
                <!-- Progress bar -->
                <div style="width: 100%; height: 6px; border-radius: 3px; background: rgba(255,255,255,0.05); overflow: hidden;">
                  <div 
                    style="height: 100%; background: linear-gradient(90deg, var(--color-primary), #3b82f6); border-radius: 3px;"
                    :style="{ width: `${getCategoryPercentage(split.amount)}%` }"
                  ></div>
                </div>
              </div>
              <div v-if="salesData.category_splits?.length === 0" style="font-size: 0.8rem; color: var(--color-text-muted); text-align: center; padding: 1rem;">
                No category records.
              </div>
            </div>
          </div>

          <!-- Sales by Payment Method -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Gateway Split</div>
            <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <div 
                v-for="(pm, idx) in salesData.payment_splits" 
                :key="idx" 
                style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding: var(--spacing-xs) 0; border-bottom: 1px solid var(--color-border);"
              >
                <span class="badge badge--secondary" style="font-size: 0.75rem;">{{ pm.method }}</span>
                <span style="color: #1e293b; font-weight: 500;">₹{{ pm.amount.toFixed(2) }} <span style="font-size: 0.7rem; color: var(--color-text-muted);">({{ pm.count }} orders)</span></span>
              </div>
              <div v-if="salesData.payment_splits?.length === 0" style="font-size: 0.8rem; color: var(--color-text-muted); text-align: center; padding: 1rem;">
                No payments.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Selling Products Table -->
      <div class="glass-panel" style="overflow: hidden;">
        <div style="padding: var(--spacing-lg) var(--spacing-lg) var(--spacing-sm); border-bottom: 1px solid var(--color-border);">
          <div class="card-header-title">Top Selling Products</div>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Product Details</th>
              <th>SKU Identifier</th>
              <th style="text-align: right;">Units Sold</th>
              <th style="text-align: right;">Cumulative Revenue</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(product, idx) in salesData.top_products" :key="idx">
              <td style="font-weight: bold; color: var(--color-primary);">#{{ idx + 1 }}</td>
              <td style="font-weight: 500; color: #1e293b;">{{ product.name }}</td>
              <td><code>{{ product.sku }}</code></td>
              <td style="text-align: right; color: #1e293b;">{{ product.sold }}</td>
              <td style="text-align: right; font-weight: bold; color: var(--color-primary);">₹{{ product.revenue.toFixed(2) }}</td>
            </tr>
            <tr v-if="salesData.top_products?.length === 0">
              <td colspan="5" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
                No sales records found inside active filters.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ================= INVENTORY TAB ================= -->
    <div v-if="activeTab === 'inventory'">
      <!-- Inventory KPIs -->
      <div class="stats-grid-4" style="margin-bottom: var(--spacing-lg);">
        <div class="stat-card-new">
          <div class="stat-card__title">Total Stock Variants</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ inventoryData.kpis?.total_variants }}</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">Low Stock SKUs</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-warning);">{{ inventoryData.kpis?.low_stock }}</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">Out of Stock SKUs</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-danger);">{{ inventoryData.kpis?.out_of_stock }}</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">Total Warehouse Value</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-success);">₹{{ inventoryData.kpis?.valuation?.toFixed(2) }}</div>
        </div>
      </div>

      <!-- Low Stock Warnings Table -->
      <div class="glass-panel" style="overflow: hidden;">
        <div style="padding: var(--spacing-lg) var(--spacing-lg) var(--spacing-sm); border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
          <div class="card-header-title">Low Stock Reorder Alert Checklist</div>
          <router-link to="/admin/inventory" class="btn btn--secondary btn--sm">Manage Stock Adjustments</router-link>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Variant Product Description</th>
              <th>Available Stock</th>
              <th>Reorder Threshold</th>
              <th>Reorder Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in inventoryData.low_stock_details" :key="item.sku">
              <td style="font-weight: bold;"><code>{{ item.sku }}</code></td>
              <td style="color: #1e293b;">{{ item.name }}</td>
              <td style="color: var(--color-warning); font-weight: bold;">{{ item.stock }} units</td>
              <td>{{ item.threshold }} units</td>
              <td>
                <span :class="['badge', item.stock === 0 ? 'badge--danger' : 'badge--warning']">
                  {{ item.stock === 0 ? 'OUT OF STOCK' : 'REORDER LIMIT' }}
                </span>
              </td>
            </tr>
            <tr v-if="inventoryData.low_stock_details?.length === 0">
              <td colspan="5" style="text-align: center; padding: 3rem; color: var(--color-success); font-weight: 500;">
                All product stock levels are healthy! No inventory warnings.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ================= CUSTOMERS TAB ================= -->
    <div v-if="activeTab === 'customers'">
      <!-- Customer KPIs -->
      <div class="stats-grid-4" style="margin-bottom: var(--spacing-lg);">
        <div class="stat-card-new">
          <div class="stat-card__title">Total Registered Customers</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ customerData.kpis?.total_customers }}</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">New Customers (Last 30 Days)</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-primary);">{{ customerData.kpis?.new_customers }}</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">Repeat Purchase Rate</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-success);">{{ customerData.kpis?.repeat_rate?.toFixed(1) }}%</div>
        </div>
        <div class="stat-card-new">
          <div class="stat-card__title">Average Customer LTV</div>
          <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">₹{{ customerData.kpis?.average_ltv?.toFixed(2) }}</div>
        </div>
      </div>

      <!-- Registration Growth Trend (Light SVG Chart) -->
      <div class="glass-panel" style="padding: var(--spacing-lg); margin-bottom: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">New Customers Onboarded Trend (Last 30 Days)</div>
        
        <div style="min-height: 180px; display: flex; align-items: center; justify-content: center; position: relative;">
          <svg v-if="customerData.registrations_trend?.length > 1" width="100%" height="150" viewBox="0 0 500 150" preserveAspectRatio="none">
            <!-- Growth Trend Line -->
            <path 
              :d="customerTrendPath" 
              fill="none" 
              stroke="#10b981" 
              stroke-width="3" 
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <!-- Data nodes -->
            <circle 
              v-for="(pt, idx) in customerTrendCoordinates" 
              :key="idx"
              :cx="pt.x" 
              :cy="pt.y" 
              r="4" 
              fill="#10b981" 
              stroke="#fff" 
              stroke-width="1"
            />
          </svg>
          <div v-else style="color: var(--color-text-muted); font-size: 0.9rem;">Insufficient sign-up timeline points to graph.</div>
        </div>
        
        <!-- Timeline dates footer axis -->
        <div style="display: flex; justify-content: space-between; padding-top: var(--spacing-sm); border-top: 1px solid var(--color-border); font-size: 0.75rem; color: var(--color-text-muted);">
          <span v-for="(t, idx) in customerData.registrations_trend" :key="idx">
            {{ t.date }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const activeTab = ref('sales');
const loading = ref(true);
const errorMsg = ref('');

const salesDates = ref({
  start: '',
  end: '',
});

const salesData = ref({});
const inventoryData = ref({});
const customerData = ref({});

const fetchSalesReport = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/reports/sales', {
      params: {
        start_date: salesDates.value.start,
        end_date: salesDates.value.end
      }
    });

    if (response.data && response.data.success) {
      salesData.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load sales report:', err);
    errorMsg.value = 'Failed to compile Sales statistics.';
  } finally {
    loading.value = false;
  }
};

const fetchInventoryReport = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/reports/inventory');
    if (response.data && response.data.success) {
      inventoryData.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load inventory report:', err);
    errorMsg.value = 'Failed to compile Warehouse Inventory valuation.';
  } finally {
    loading.value = false;
  }
};

const fetchCustomerReport = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/reports/customers');
    if (response.data && response.data.success) {
      customerData.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load customer report:', err);
    errorMsg.value = 'Failed to compile Customers Growth statistics.';
  } finally {
    loading.value = false;
  }
};

// Watch tabs
watch(activeTab, (newTab) => {
  errorMsg.value = '';
  if (newTab === 'sales') {
    fetchSalesReport();
  } else if (newTab === 'inventory') {
    fetchInventoryReport();
  } else if (newTab === 'customers') {
    fetchCustomerReport();
  }
});

// Category percentage calculation
const getCategoryPercentage = (amount) => {
  const splits = salesData.value.category_splits || [];
  if (splits.length === 0) return 0;
  const maxVal = Math.max(...splits.map(s => s.amount));
  return maxVal > 0 ? ((amount / maxVal) * 100) : 0;
};

// Trend coordinate mapper for SVG line chart
const trendCoordinates = computed(() => {
  const trend = salesData.value.revenue_trend || [];
  if (trend.length <= 1) return [];

  const maxVal = Math.max(...trend.map(t => t.amount)) || 1;
  const stepsCount = trend.length - 1;
  
  return trend.map((pt, idx) => {
    const x = (idx / stepsCount) * 500;
    // Y maps inverted because SVG (0,0) is top-left
    const y = 180 - ((pt.amount / maxVal) * 140); 
    return { x, y };
  });
});

const revenueTrendPath = computed(() => {
  const coords = trendCoordinates.value;
  if (coords.length === 0) return '';
  return `M ${coords.map(c => `${c.x} ${c.y}`).join(' L ')}`;
});

const revenueTrendAreaPath = computed(() => {
  const coords = trendCoordinates.value;
  if (coords.length === 0) return '';
  const first = coords[0];
  const last = coords[coords.length - 1];
  return `M ${first.x} 200 L ${coords.map(c => `${c.x} ${c.y}`).join(' L ')} L ${last.x} 200 Z`;
});

// Customer registration coordinations
const customerTrendCoordinates = computed(() => {
  const trend = customerData.value.registrations_trend || [];
  if (trend.length <= 1) return [];

  const maxVal = Math.max(...trend.map(t => t.count)) || 1;
  const stepsCount = trend.length - 1;
  
  return trend.map((pt, idx) => {
    const x = (idx / stepsCount) * 500;
    const y = 130 - ((pt.count / maxVal) * 100); 
    return { x, y };
  });
});

const customerTrendPath = computed(() => {
  const coords = customerTrendCoordinates.value;
  if (coords.length === 0) return '';
  return `M ${coords.map(c => `${c.x} ${c.y}`).join(' L ')}`;
});

const exportCSV = () => {
  const top = salesData.value.top_products || [];
  if (top.length === 0) {
    alert('No data points to export');
    return;
  }
  let csv = 'Product Name,SKU,Quantity Sold,Cumulative Revenue\n';
  top.forEach(p => {
    csv += `"${p.name}",${p.sku},${p.sold},${p.revenue.toFixed(2)}\n`;
  });
  
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', 'top_products_report.csv');
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(() => {
  // Set default dates to last 30 days
  const now = new Date();
  const sub30 = new Date();
  sub30.setDate(now.getDate() - 30);
  
  const formatDateStr = (d) => d.toISOString().split('T')[0];
  salesDates.value.start = formatDateStr(sub30);
  salesDates.value.end = formatDateStr(now);

  fetchSalesReport();
});
</script>
