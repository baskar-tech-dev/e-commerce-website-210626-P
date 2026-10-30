<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Orders List</h1>
      <span class="admin-page__subtitle">Track, process, pack, and dispatch customer purchases through the 9-stage lifecycle.</span>
    </div>
    <div class="admin-header__actions">
      <button @click="showStats = !showStats" class="btn-header-toggle" :title="showStats ? 'Hide KPI Stats' : 'Show KPI Stats'">
        <component :is="showStats ? EyeOff : Eye" :size="15" />
        <span>{{ showStats ? 'Hide Stats' : 'Show Stats' }}</span>
      </button>
    </div>
  </div>

  <!-- KPI Summary Cards with Order Amounts -->
  <div v-show="showStats" class="stats-grid-4" style="margin-bottom: var(--spacing-lg);">
    <!-- Total Orders Card -->
    <div class="stat-card-new">
      <div class="stat-card__title">Total Orders</div>
      <div class="stat-card__content">
        <div class="stat-card__value" style="font-size: 1.65rem;">{{ summary.total_orders }}</div>
        <div class="stat-card__amount" style="color: #6E1F3A;">
          ₹{{ formatCurrency(summary.total_amount) }}
        </div>
      </div>
      <div class="stat-card__meta">
        <span>Total Sales Value</span>
      </div>
    </div>

    <!-- Order Placed / Pending Card -->
    <div class="stat-card-new">
      <div class="stat-card__title">Order Placed / Pending</div>
      <div class="stat-card__content">
        <div class="stat-card__value" style="font-size: 1.65rem; color: var(--color-warning);">{{ summary.order_placed_count }}</div>
        <div class="stat-card__amount" style="color: #b45309;">
          ₹{{ formatCurrency(summary.order_placed_amount) }}
        </div>
      </div>
      <div class="stat-card__meta">
        <span>Awaiting Processing</span>
      </div>
    </div>

    <!-- Processing & Ready Card -->
    <div class="stat-card-new">
      <div class="stat-card__title">Processing & Ready</div>
      <div class="stat-card__content">
        <div class="stat-card__value" style="font-size: 1.65rem; color: var(--color-primary);">{{ summary.processing_count }}</div>
        <div class="stat-card__amount" style="color: #6E1F3A;">
          ₹{{ formatCurrency(summary.processing_amount) }}
        </div>
      </div>
      <div class="stat-card__meta">
        <span>In Fulfillment</span>
      </div>
    </div>

    <!-- Delivered Orders Card -->
    <div class="stat-card-new">
      <div class="stat-card__title">Delivered Orders</div>
      <div class="stat-card__content">
        <div class="stat-card__value" style="font-size: 1.65rem; color: var(--color-success);">{{ summary.delivered_count }}</div>
        <div class="stat-card__amount" style="color: #059669;">
          ₹{{ formatCurrency(summary.delivered_amount) }}
        </div>
      </div>
      <div class="stat-card__meta">
        <span>Completed Revenue</span>
      </div>
    </div>
  </div>

  <!-- Search and Filters Bar -->
  <div class="glass-panel order-filter-panel">
    <!-- Main Filter Controls Row -->
    <div class="order-filter-row">
      <!-- Search Input -->
      <div class="order-search-box">
        <Search class="order-search-icon" :size="17" />
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search by Order #, customer name, email or phone..." 
          class="form-input order-search-input" 
        />
        <button 
          v-if="filters.search" 
          type="button"
          @click="filters.search = ''; debounceSearch()" 
          class="btn-search-clear"
          title="Clear search"
        >
          <X :size="14" />
        </button>
      </div>

      <!-- Filters Dropdowns & Date Controls -->
      <div class="order-controls-wrap">
        <!-- Date Filter Group -->
        <div class="filter-field">
          <label class="filter-field__label">
            <Calendar :size="14" />
            <span>Date:</span>
          </label>
          <select 
            v-model="selectedDatePreset" 
            @change="onPresetChange" 
            class="form-input filter-field__select filter-field__select--date"
          >
            <option value="all">All Dates</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="last7">Last 7 Days</option>
            <option value="last30">Last 30 Days</option>
            <option value="this_month">This Month</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>

        <!-- Custom Date Range Picker -->
        <div v-if="selectedDatePreset === 'custom' || filters.date_from || filters.date_to" class="date-range-picker">
          <div class="date-range-field">
            <span class="date-range-tag">From:</span>
            <input 
              type="date" 
              v-model="filters.date_from" 
              @change="onDateInputChange"
              class="date-native-input" 
              title="From Date"
            />
          </div>
          <span class="date-range-sep">→</span>
          <div class="date-range-field">
            <span class="date-range-tag">To:</span>
            <input 
              type="date" 
              v-model="filters.date_to" 
              @change="onDateInputChange"
              class="date-native-input" 
              title="To Date"
            />
          </div>
          <button 
            v-if="filters.date_from || filters.date_to" 
            type="button" 
            @click="clearDateFilter" 
            class="btn-date-clear" 
            title="Clear date filter"
          >
            <X :size="13" />
          </button>
        </div>

        <!-- Status Dropdown -->
        <div class="filter-field">
          <label class="filter-field__label">Status:</label>
          <select 
            v-model="filters.status" 
            @change="fetchOrders(1)" 
            class="form-input filter-field__select filter-field__select--status"
          >
            <option value="">All Statuses (9 Stages)</option>
            <option v-for="st in statusDefinitions" :key="st.code" :value="st.code">
              {{ st.step }}. {{ st.label }}
            </option>
          </select>
        </div>

        <!-- Payment Dropdown -->
        <div class="filter-field">
          <label class="filter-field__label">Payment:</label>
          <select 
            v-model="filters.payment_status" 
            @change="fetchOrders(1)" 
            class="form-input filter-field__select filter-field__select--payment"
          >
            <option value="">All Payments</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>

        <!-- Reset All Filters Button -->
        <button 
          v-if="hasActiveFilters" 
          type="button" 
          @click="resetFilters" 
          class="btn-reset-filters" 
          title="Reset all active filters"
        >
          <RotateCcw :size="13" />
          <span>Reset</span>
        </button>
      </div>
    </div>

    <!-- Quick Status Filter Pills -->
    <div class="status-pills-bar">
      <button 
        type="button" 
        :class="['status-pill', !filters.status ? 'status-pill--active' : '']"
        @click="filters.status = ''; fetchOrders(1)"
      >
        All
      </button>
      <button 
        v-for="st in statusDefinitions" 
        :key="st.code" 
        type="button" 
        :class="['status-pill', filters.status === st.code ? 'status-pill--active' : '']"
        @click="filters.status = st.code; fetchOrders(1)"
      >
        <span>{{ st.icon }} {{ st.label }}</span>
      </button>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading Orders...</div>
  </div>

  <!-- Orders List -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="(order, index) in orders" :key="order.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-primary); background: rgba(128, 0, 32, 0.08); border: 1px solid rgba(128, 0, 32, 0.15); padding: 2px 6px; border-radius: 4px; flex-shrink: 0;">
              #{{ (pagination.current_page - 1) * (pagination.per_page || 15) + index + 1 }}
            </span>
            <router-link :to="`/admin/orders/${order.id}`" class="mdc-title">
              {{ order.order_number }}
            </router-link>
          </div>
          <span class="mdc-date">{{ formatDate(order.created_at) }}</span>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">{{ order.shipping_first_name }} {{ order.shipping_last_name }}</span>
            <span class="mdc-email">{{ order.user?.email }}</span>
          </div>
          <div class="mdc-totals">
            {{ order.total_items }} {{ order.total_items === 1 ? 'item' : 'items' }} • <strong>₹{{ parseFloat(order.grand_total).toFixed(2) }}</strong>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', getStatusBadgeClass(order.status)]">
              {{ getStatusLabel(order.status) }}
            </span>
            <div style="display: flex; align-items: center; gap: 4px;">
              <span :class="['badge', getPaymentBadgeClass(order.payment_status)]">
                {{ order.payment_status }}
              </span>
              <span style="font-size: 0.65rem; color: var(--color-text-muted); text-transform: uppercase;">
                ({{ order.payment_method }})
              </span>
            </div>
          </div>
          <div class="actions-group">
            <router-link 
              :to="`/admin/orders/${order.id}`" 
              class="btn-action-icon btn-action-icon--view"
              title="View Order Details"
              aria-label="View Order Details"
            >
              <Eye :size="16" />
            </router-link>
            <button 
              v-if="authStore.isSuperAdmin"
              type="button" 
              @click="confirmDeleteOrder(order)" 
              class="btn-action-icon btn-action-icon--delete" 
              title="Delete Order"
              aria-label="Delete Order"
            >
              <Trash2 :size="16" />
            </button>
          </div>
        </div>
      </div>
      
      <div v-if="orders.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No customer orders found matching your criteria.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 55px; text-align: center;">S.No</th>
          <th>Order #</th>
          <th>Customer</th>
          <th>Date Placed</th>
          <th>Items</th>
          <th>Grand Total</th>
          <th>Payment</th>
          <th>Order Status</th>
          <th style="width: 100px; min-width: 90px; text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(order, index) in orders" :key="order.id">
          <td style="width: 55px; text-align: center; font-weight: 600; color: var(--color-text-secondary); font-size: 0.85rem;">
            {{ (pagination.current_page - 1) * (pagination.per_page || 15) + index + 1 }}
          </td>
          <td style="font-weight: bold; color: var(--color-primary);">
            <router-link :to="`/admin/orders/${order.id}`" style="color: var(--color-primary); text-decoration: none;">
              {{ order.order_number }}
            </router-link>
          </td>
          <td>
            <div style="display: flex; flex-direction: column;">
              <span style="font-weight: 500; color: #1e293b;">{{ order.shipping_first_name }} {{ order.shipping_last_name }}</span>
              <span style="font-size: 0.75rem; color: var(--color-text-muted);">{{ order.user?.email }}</span>
            </div>
          </td>
          <td>{{ formatDate(order.created_at) }}</td>
          <td>{{ order.total_items }} {{ order.total_items === 1 ? 'item' : 'items' }}</td>
          <td style="font-weight: bold; color: #1e293b;">₹{{ parseFloat(order.grand_total).toFixed(2) }}</td>
          <td>
            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 0.25rem;">
              <span :class="['badge', getPaymentBadgeClass(order.payment_status)]">
                {{ order.payment_status }}
              </span>
              <span style="font-size: 0.7rem; color: var(--color-text-muted); text-transform: uppercase;">
                ({{ order.payment_method }})
              </span>
            </div>
          </td>
          <td>
            <span :class="['badge', getStatusBadgeClass(order.status)]" style="font-weight: 600; font-size: 0.78rem;">
              {{ getStatusLabel(order.status) }}
            </span>
          </td>
          <td style="width: 100px; min-width: 90px; text-align: right; white-space: nowrap;">
            <div class="actions-group">
              <router-link 
                :to="`/admin/orders/${order.id}`" 
                class="btn-action-icon btn-action-icon--view" 
                title="View Order Details"
                aria-label="View Order Details"
              >
                <Eye :size="16" />
              </router-link>
              <button 
                v-if="authStore.isSuperAdmin"
                type="button" 
                @click="confirmDeleteOrder(order)" 
                class="btn-action-icon btn-action-icon--delete" 
                title="Delete Order"
                aria-label="Delete Order"
              >
                <Trash2 :size="16" />
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="orders.length === 0">
          <td colspan="9" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            No customer orders matching the selected filter criteria.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="pagination-footer">
      <div style="font-size: 0.85rem; color: var(--color-text-muted);">
        Showing page <strong>{{ pagination.current_page }}</strong> of <strong>{{ pagination.last_page }}</strong> (Total: {{ pagination.total }} orders)
      </div>
      <div style="display: flex; gap: var(--spacing-sm);">
        <button 
          class="btn-pagination" 
          :disabled="pagination.current_page === 1"
          @click="fetchOrders(pagination.current_page - 1)"
        >
          <ChevronLeft :size="15" />
          <span>Prev</span>
        </button>
        <button 
          class="btn-pagination" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchOrders(pagination.current_page + 1)"
        >
          <span>Next</span>
          <ChevronRight :size="15" />
        </button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div v-if="orderToDelete" class="admin-modal-backdrop" @click="orderToDelete = null">
    <div class="admin-modal-card" @click.stop style="max-width: 480px; padding: 1.5rem; background: #ffffff; border-radius: 14px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 1px solid #fee2e2;">
      <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1rem; color: #dc2626;">
        <span style="font-size: 1.6rem;">⚠️</span>
        <h3 style="margin: 0; font-family: 'Playfair Display', serif; font-size: 1.35rem; color: #991b1b;">Delete Customer Order</h3>
      </div>
      <p style="color: #475569; font-size: 0.92rem; line-height: 1.5; margin-bottom: 1.25rem;">
        Are you sure you want to delete <strong>Order #{{ orderToDelete.order_number }}</strong>? This will remove the order from the store's order tracking pipeline.
      </p>
      <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
        <button type="button" class="btn btn--secondary btn--sm" @click="orderToDelete = null" :disabled="isDeleting">
          Cancel
        </button>
        <button 
          type="button" 
          class="btn btn--danger btn--sm" 
          @click="executeDeleteOrder" 
          :disabled="isDeleting" 
          style="background: #dc2626; color: #ffffff; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;"
        >
          <Trash2 :size="14" />
          <span>{{ isDeleting ? 'Deleting...' : 'Yes, Delete Order' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { 
  Eye, 
  EyeOff, 
  Trash2, 
  Search, 
  Calendar, 
  RotateCcw, 
  ChevronLeft, 
  ChevronRight, 
  X 
} from 'lucide-vue-next';

const authStore = useAuthStore();
const showStats = ref(true);
const loading = ref(false);
const orders = ref([]);
const errorMsg = ref('');
const orderToDelete = ref(null);
const isDeleting = ref(false);

const confirmDeleteOrder = (order) => {
  orderToDelete.value = order;
};

const executeDeleteOrder = async () => {
  if (!orderToDelete.value) return;
  isDeleting.value = true;
  try {
    const res = await axios.delete(`/api/admin/orders/${orderToDelete.value.id}`);
    if (res.data && res.data.success) {
      alert(`Order #${orderToDelete.value.order_number} has been deleted.`);
      orderToDelete.value = null;
      await fetchOrders(pagination.current_page);
    }
  } catch (err) {
    console.error('Delete order failed:', err);
    alert(err.response?.data?.message || 'Failed to delete order. Please try again.');
  } finally {
    isDeleting.value = false;
  }
};

const filters = reactive({
  search: '',
  status: '',
  payment_status: '',
  date_from: '',
  date_to: '',
});

const selectedDatePreset = ref('all');

// Summary data containing counts and amounts
const summary = reactive({
  total_orders: 0,
  total_amount: 0,
  order_placed_count: 0,
  order_placed_amount: 0,
  processing_count: 0,
  processing_amount: 0,
  delivered_count: 0,
  delivered_amount: 0,
});

const formatCurrency = (val) => {
  const num = parseFloat(val) || 0;
  return num.toLocaleString('en-IN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const formatDateToYMD = (d) => {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const onPresetChange = () => {
  const today = new Date();
  if (selectedDatePreset.value === 'today') {
    const str = formatDateToYMD(today);
    filters.date_from = str;
    filters.date_to = str;
  } else if (selectedDatePreset.value === 'yesterday') {
    const yest = new Date(today);
    yest.setDate(yest.getDate() - 1);
    const str = formatDateToYMD(yest);
    filters.date_from = str;
    filters.date_to = str;
  } else if (selectedDatePreset.value === 'last7') {
    const past = new Date(today);
    past.setDate(past.getDate() - 6);
    filters.date_from = formatDateToYMD(past);
    filters.date_to = formatDateToYMD(today);
  } else if (selectedDatePreset.value === 'last30') {
    const past = new Date(today);
    past.setDate(past.getDate() - 29);
    filters.date_from = formatDateToYMD(past);
    filters.date_to = formatDateToYMD(today);
  } else if (selectedDatePreset.value === 'this_month') {
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    filters.date_from = formatDateToYMD(firstDay);
    filters.date_to = formatDateToYMD(today);
  } else if (selectedDatePreset.value === 'all') {
    filters.date_from = '';
    filters.date_to = '';
  }
  fetchOrders(1);
};

const onDateInputChange = () => {
  selectedDatePreset.value = 'custom';
  fetchOrders(1);
};

const clearDateFilter = () => {
  filters.date_from = '';
  filters.date_to = '';
  selectedDatePreset.value = 'all';
  fetchOrders(1);
};

const hasActiveFilters = computed(() => {
  return !!(filters.search || filters.status || filters.payment_status || filters.date_from || filters.date_to);
});

const resetFilters = () => {
  filters.search = '';
  filters.status = '';
  filters.payment_status = '';
  filters.date_from = '';
  filters.date_to = '';
  selectedDatePreset.value = 'all';
  fetchOrders(1);
};

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

// Official 9 Order Status Definitions
const statusDefinitions = [
  { step: 1, code: 'order_placed', label: 'Order Placed', badge: 'badge--warning', icon: '📝' },
  { step: 2, code: 'order_confirmed', label: 'Order Confirmed', badge: 'badge--primary', icon: '✓' },
  { step: 3, code: 'processing', label: 'Processing', badge: 'badge--secondary', icon: '⚙️' },
  { step: 4, code: 'ready_to_ship', label: 'Ready to Ship', badge: 'badge--secondary', icon: '📦' },
  { step: 5, code: 'shipped', label: 'Shipped', badge: 'badge--warning', icon: '🚚' },
  { step: 6, code: 'delivered', label: 'Delivered', badge: 'badge--success', icon: '🎉' },
  { step: 7, code: 'cancelled', label: 'Cancelled', badge: 'badge--danger', icon: '✕' },
  { step: 8, code: 'returned', label: 'Returned', badge: 'badge--danger', icon: '↩️' },
  { step: 9, code: 'refunded', label: 'Refunded', badge: 'badge--secondary', icon: '💸' },
];

let debounceTimeout = null;
const debounceSearch = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    fetchOrders(1);
  }, 350);
};

const fetchOrders = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page: page,
      search: filters.search || undefined,
      status: filters.status || undefined,
      payment_status: filters.payment_status || undefined,
      date_from: filters.date_from || undefined,
      date_to: filters.date_to || undefined,
    };

    const response = await axios.get('/api/admin/orders', { params });
    if (response.data && response.data.success) {
      orders.value = response.data.data;
      pagination.current_page = response.data.meta.current_page;
      pagination.last_page = response.data.meta.last_page;
      pagination.per_page = response.data.meta.per_page;
      pagination.total = response.data.meta.total;

      // Update summary counts and amounts
      if (response.data.summary) {
        Object.assign(summary, response.data.summary);
      }
    }
  } catch (err) {
    console.error('Failed to fetch orders:', err);
    errorMsg.value = 'Failed to fetch customer orders from API';
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getStatusLabel = (status) => {
  const norm = status === 'pending' ? 'order_placed' : (status === 'confirmed' ? 'order_confirmed' : status);
  const def = statusDefinitions.find(s => s.code === norm);
  return def ? `${def.icon} ${def.label}` : (status || '').toUpperCase();
};

const getStatusBadgeClass = (status) => {
  const norm = status === 'pending' ? 'order_placed' : (status === 'confirmed' ? 'order_confirmed' : status);
  const def = statusDefinitions.find(s => s.code === norm);
  return def ? def.badge : 'badge--secondary';
};

const getPaymentBadgeClass = (status) => {
  switch (status) {
    case 'pending': return 'badge--warning';
    case 'paid':
    case 'captured': return 'badge--success';
    case 'failed': return 'badge--danger';
    case 'refunded': return 'badge--secondary';
    default: return 'badge--secondary';
  }
};

onMounted(() => {
  fetchOrders();
});
</script>

<style scoped>
/* Page Header Toggle Button */
.btn-header-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0 14px;
  height: 38px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #475569;
  background: #ffffff;
  border: 1.5px solid #cbd5e1;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.btn-header-toggle:hover {
  color: #6e1f3a;
  border-color: #6e1f3a;
  background: rgba(110, 31, 58, 0.04);
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(110, 31, 58, 0.12);
}

.btn-header-toggle:active {
  transform: scale(0.97);
}

/* KPI Summary Cards */
.stat-card-new {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 1.1rem 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card-new:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
}

.stat-card__content {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.5rem;
  margin-top: 0.15rem;
}

.stat-card__amount {
  font-size: 1.15rem;
  font-weight: 700;
  letter-spacing: -0.01em;
  white-space: nowrap;
}

.stat-card__meta {
  font-size: 0.72rem;
  color: var(--color-text-muted);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

/* Filter Panel Styling */
.order-filter-panel {
  padding: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid var(--color-border);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.order-filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-md);
  justify-content: space-between;
  align-items: center;
}

.order-search-box {
  flex: 1;
  min-width: 280px;
  position: relative;
}

.order-search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  pointer-events: none;
}

.order-search-input {
  width: 100%;
  padding-left: 2.4rem;
  padding-right: 2rem;
  height: 40px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  font-size: 0.88rem;
  background: #ffffff;
  transition: all 0.2s ease;
}

.order-search-input:focus {
  border-color: #6e1f3a;
  box-shadow: 0 0 0 3px rgba(110, 31, 58, 0.12);
  background: #ffffff;
}

.btn-search-clear {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: #f1f5f9;
  border: none;
  color: #64748b;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  transition: all 0.15s ease;
}

.btn-search-clear:hover {
  background: #fee2e2;
  color: #dc2626;
}

.order-controls-wrap {
  display: flex;
  gap: var(--spacing-sm);
  flex-wrap: wrap;
  align-items: center;
}

.filter-field {
  display: flex;
  align-items: center;
  gap: 6px;
}

.filter-field__label {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.82rem;
  color: #475569;
  font-weight: 600;
  white-space: nowrap;
}

.filter-field__select {
  height: 40px;
  padding: 0 28px 0 10px;
  font-size: 0.84rem;
  font-weight: 500;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background-color: #ffffff;
  color: #1e293b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-field__select:focus {
  border-color: #6e1f3a;
  box-shadow: 0 0 0 3px rgba(110, 31, 58, 0.12);
}

.filter-field__select--date {
  min-width: 140px;
}

.filter-field__select--status {
  min-width: 180px;
}

.filter-field__select--payment {
  min-width: 135px;
}

/* Custom Date Range Picker */
.date-range-picker {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f8fafc;
  border: 1.5px solid #cbd5e1;
  border-radius: 8px;
  padding: 2px 8px;
  height: 40px;
  box-sizing: border-box;
}

.date-range-field {
  display: flex;
  align-items: center;
  gap: 4px;
}

.date-range-tag {
  font-size: 0.72rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
}

.date-native-input {
  border: none;
  background: transparent;
  padding: 2px 2px;
  font-size: 0.82rem;
  font-family: inherit;
  color: #1e293b;
  outline: none;
  cursor: pointer;
}

.date-native-input:focus {
  outline: none;
}

.date-range-sep {
  color: #94a3b8;
  font-size: 0.85rem;
  font-weight: 600;
}

.btn-date-clear {
  background: #e2e8f0;
  border: none;
  color: #475569;
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
  background: #fee2e2;
  color: #dc2626;
}

/* Reset Button */
.btn-reset-filters {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0 12px;
  height: 40px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #64748b;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-reset-filters:hover {
  color: #dc2626;
  border-color: #fca5a5;
  background: #fef2f2;
  transform: translateY(-1px);
}

.btn-reset-filters:active {
  transform: scale(0.97);
}

/* Status Pills Bar */
.status-pills-bar {
  display: flex;
  gap: 0.4rem;
  margin-top: 0.75rem;
  overflow-x: auto;
  padding-top: 0.65rem;
  border-top: 1px dashed var(--color-border);
}

.status-pill {
  border-radius: 16px;
  height: 28px;
  font-size: 0.75rem;
  font-weight: 500;
  padding: 0 12px;
  white-space: nowrap;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #475569;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.15s ease;
  user-select: none;
}

.status-pill:hover {
  border-color: #6e1f3a;
  color: #6e1f3a;
  background: rgba(110, 31, 58, 0.04);
}

.status-pill--active {
  background: #6e1f3a !important;
  color: #ffffff !important;
  border-color: #6e1f3a !important;
  font-weight: 600;
  box-shadow: 0 2px 6px rgba(110, 31, 58, 0.22);
}

/* Action Icon Buttons - Modern, Compact & Luxury Aesthetic */
.actions-group {
  display: inline-flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  white-space: nowrap;
}

.btn-action-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  padding: 0;
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  box-sizing: border-box;
  user-select: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-action-icon:active {
  transform: scale(0.92);
}

.btn-action-icon svg {
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.btn-action-icon:hover svg {
  transform: scale(1.15);
}

/* View Details Action Button - Deep Maroon Theme */
.btn-action-icon--view {
  background: rgba(110, 31, 58, 0.06);
  color: #6e1f3a;
  border: 1.5px solid rgba(110, 31, 58, 0.28);
  box-shadow: 0 1px 3px rgba(110, 31, 58, 0.05);
}

.btn-action-icon--view:hover {
  background: #6e1f3a;
  color: #ffffff;
  border-color: #6e1f3a;
  box-shadow: 0 4px 12px rgba(110, 31, 58, 0.28);
  transform: translateY(-1px);
}

/* Delete Action Button - Soft Crimson Theme */
.btn-action-icon--delete {
  background: rgba(220, 38, 38, 0.06);
  color: #dc2626;
  border: 1.5px solid rgba(220, 38, 38, 0.28);
  box-shadow: 0 1px 3px rgba(220, 38, 38, 0.05);
}

.btn-action-icon--delete:hover {
  background: #dc2626;
  color: #ffffff;
  border-color: #dc2626;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.28);
  transform: translateY(-1px);
}

/* Pagination Footer */
.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-md) var(--spacing-lg);
  border-top: 1px solid var(--color-border);
  background: rgba(0, 0, 0, 0.01);
}

.btn-pagination {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0 14px;
  height: 36px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #334155;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}

.btn-pagination:hover:not(:disabled) {
  color: #6e1f3a;
  border-color: #6e1f3a;
  background: rgba(110, 31, 58, 0.04);
  transform: translateY(-1px);
}

.btn-pagination:active:not(:disabled) {
  transform: scale(0.97);
}

.btn-pagination:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  background: #f8fafc;
}

/* Delete Modal Backdrop */
.admin-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}
</style>
