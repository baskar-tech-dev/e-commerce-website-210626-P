<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Orders List</h1>
      <span class="admin-page__subtitle">Track and dispatch customer purchases.</span>
    </div>
    <div class="admin-header__actions">
      <button @click="showStats = !showStats" class="btn btn--secondary btn--sm">
        {{ showStats ? '👁 Hide Stats' : '👁 Show Stats' }}
      </button>
    </div>
  </div>

  <!-- KPI Summary Cards -->
  <div v-show="showStats" class="stats-grid-4" style="margin-bottom: var(--spacing-lg);">
    <div class="stat-card-new">
      <div class="stat-card__title">Total Orders</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ pagination.total }}</div>
    </div>
    <div class="stat-card-new">
      <div class="stat-card__title">Pending Confirmations</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-warning);">{{ statusCounts.pending }}</div>
    </div>
    <div class="stat-card-new">
      <div class="stat-card__title">Delivered Orders</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-success);">{{ statusCounts.delivered }}</div>
    </div>
    <div class="stat-card-new">
      <div class="stat-card__title">Cancelled Orders</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-danger);">{{ statusCounts.cancelled }}</div>
    </div>
  </div>

  <!-- Search and Filters Bar -->
  <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg);">
    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
      <!-- Search Input -->
      <div style="flex: 1; min-width: 280px; position: relative;">
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search by Order #, customer name or email..." 
          class="form-input" 
          style="padding-left: 2rem;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🔍</span>
      </div>

      <!-- Filters Dropdowns -->
      <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Status:</label>
          <select v-model="filters.status" @change="fetchOrders(1)" class="form-input" style="width: 130px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Payment:</label>
          <select v-model="filters.payment_status" @change="fetchOrders(1)" class="form-input" style="width: 140px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Payments</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
          </select>
        </div>
      </div>
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
      <div class="mobile-data-card" v-for="order in orders" :key="order.id">
        <div class="mdc-header">
          <router-link :to="`/admin/orders/${order.id}`" class="mdc-title">
            {{ order.order_number }}
          </router-link>
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
              {{ order.status }}
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
          <router-link :to="`/admin/orders/${order.id}`" class="btn btn--secondary btn--sm">
            View
          </router-link>
        </div>
      </div>
      
      <div v-if="orders.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No customer orders found.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Date Placed</th>
          <th>Items</th>
          <th>Grand Total</th>
          <th>Payment</th>
          <th>Order Status</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="order in orders" :key="order.id">
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
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.25rem;">
              <span :class="['badge', getPaymentBadgeClass(order.payment_status)]">
                {{ order.payment_status }}
              </span>
              <span style="font-size: 0.7rem; color: var(--color-text-muted); text-transform: uppercase;">
                ({{ order.payment_method }})
              </span>
            </div>
          </td>
          <td>
            <span :class="['badge', getStatusBadgeClass(order.status)]">
              {{ order.status }}
            </span>
          </td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <router-link :to="`/admin/orders/${order.id}`" class="btn btn--secondary btn--sm">
                👁️ View Details
              </router-link>
            </div>
          </td>
        </tr>
        <tr v-if="orders.length === 0">
          <td colspan="8" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No customer orders matching the active filters were found.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <div v-if="pagination.last_page > 1" style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); border-top: 1px solid var(--color-border);">
      <span style="font-size: 0.8rem; color: var(--color-text-muted);">
        Showing Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </span>
      <div style="display: flex; gap: var(--spacing-sm);">
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === 1"
          @click="fetchOrders(pagination.current_page - 1)"
        >
          Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchOrders(pagination.current_page + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const showStats = ref(true);
const orders = ref([]);
const loading = ref(true);
const errorMsg = ref('');

const filters = ref({
  search: '',
  status: '',
  payment_status: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const statusCounts = ref({
  pending: 0,
  delivered: 0,
  cancelled: 0,
});

let searchTimeout = null;

const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchOrders(1);
  }, 350);
};

const fetchOrders = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/orders', {
      params: {
        page,
        status: filters.value.status,
        payment_status: filters.value.payment_status,
        search: filters.value.search,
      }
    });

    if (response.data && response.data.success) {
      orders.value = response.data.data;
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        total: response.data.meta.total,
      };
    }
  } catch (err) {
    console.error('Failed to fetch orders:', err);
    errorMsg.value = 'Failed to fetch customer orders from API';
  } finally {
    loading.value = false;
  }
};

const fetchKPIs = async () => {
  try {
    const response = await axios.get('/api/admin/dashboard/stats');
    if (response.data && response.data.success) {
      // Fetch statuses via another quick call or aggregate from seeder
      // We can count items in orders locally or request dashboard stats
      // Let's populate status counts from a lightweight count
      const allRes = await axios.get('/api/admin/orders', { params: { per_page: 100 } });
      if (allRes.data && allRes.data.success) {
        const list = allRes.data.data;
        statusCounts.value.pending = list.filter(o => o.status === 'pending').length;
        statusCounts.value.delivered = list.filter(o => o.status === 'delivered').length;
        statusCounts.value.cancelled = list.filter(o => o.status === 'cancelled').length;
      }
    }
  } catch (e) {
    console.error('Failed to load status summaries:', e);
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

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'pending': return 'badge--warning';
    case 'confirmed': return 'badge--secondary';
    case 'processing': return 'badge--secondary';
    case 'shipped': return 'badge--secondary';
    case 'delivered': return 'badge--success';
    case 'cancelled': return 'badge--danger';
    default: return '';
  }
};

const getPaymentBadgeClass = (status) => {
  switch (status) {
    case 'pending': return 'badge--warning';
    case 'paid': return 'badge--success';
    case 'failed': return 'badge--danger';
    default: return '';
  }
};

onMounted(() => {
  fetchOrders();
  fetchKPIs();
});
</script>
