<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Orders List</h1>
      <span class="admin-page__subtitle">Track, process, pack, and dispatch customer purchases through the 9-stage lifecycle.</span>
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
      <div class="stat-card__title">Order Placed / Pending</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-warning);">{{ statusCounts.order_placed }}</div>
    </div>
    <div class="stat-card-new">
      <div class="stat-card__title">Processing & Ready</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-primary);">{{ statusCounts.processing }}</div>
    </div>
    <div class="stat-card-new">
      <div class="stat-card__title">Delivered Orders</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-success);">{{ statusCounts.delivered }}</div>
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
          placeholder="Search by Order #, customer name, email or phone..." 
          class="form-input" 
          style="padding-left: 2rem;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🔍</span>
      </div>

      <!-- Filters Dropdowns -->
      <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; align-items: center;">
        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted); font-weight: 600;">Status:</label>
          <select v-model="filters.status" @change="fetchOrders(1)" class="form-input" style="min-width: 170px; padding: 0.25rem var(--spacing-md); font-weight: 500;">
            <option value="">All Statuses (9 Stages)</option>
            <option v-for="st in statusDefinitions" :key="st.code" :value="st.code">
              {{ st.step }}. {{ st.label }}
            </option>
          </select>
        </div>

        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted); font-weight: 600;">Payment:</label>
          <select v-model="filters.payment_status" @change="fetchOrders(1)" class="form-input" style="width: 140px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Payments</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Quick Status Filter Pills -->
    <div style="display: flex; gap: 0.4rem; margin-top: 0.75rem; overflow-x: auto; padding-top: 0.5rem; border-top: 1px dashed var(--color-border);">
      <button 
        type="button" 
        :class="['btn btn--sm', !filters.status ? 'btn--primary' : 'btn--secondary']"
        @click="filters.status = ''; fetchOrders(1)"
        style="border-radius: 14px; height: 26px; font-size: 0.75rem; padding: 0 10px; white-space: nowrap;"
      >
        All
      </button>
      <button 
        v-for="st in statusDefinitions" 
        :key="st.code"
        type="button" 
        :class="['btn btn--sm', filters.status === st.code ? 'btn--primary' : 'btn--secondary']"
        @click="filters.status = st.code; fetchOrders(1)"
        style="border-radius: 14px; height: 26px; font-size: 0.75rem; padding: 0 10px; white-space: nowrap;"
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
          <div style="display: flex; gap: 0.4rem; align-items: center;">
            <router-link :to="`/admin/orders/${order.id}`" class="btn btn--secondary btn--sm">
              View
            </router-link>
            <button 
              type="button" 
              @click="confirmDeleteOrder(order)" 
              class="btn btn--danger btn--sm" 
              style="background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.3); padding: 0 8px; height: 32px;" 
              title="Delete Order"
            >
              🗑️
            </button>
          </div>
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
          <th style="width: 55px; text-align: center;">S.No</th>
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
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.4rem;">
              <router-link :to="`/admin/orders/${order.id}`" class="btn btn--secondary btn--sm">
                👁️ View Details
              </router-link>
              <button 
                type="button" 
                @click="confirmDeleteOrder(order)" 
                class="btn btn--danger btn--sm" 
                style="background: rgba(220, 38, 38, 0.1); color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.3);" 
                title="Delete Order"
              >
                🗑️ Delete
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
    <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md) var(--spacing-lg); border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.01);">
      <div style="font-size: 0.85rem; color: var(--color-text-muted);">
        Showing page <strong>{{ pagination.current_page }}</strong> of <strong>{{ pagination.last_page }}</strong> (Total: {{ pagination.total }} orders)
      </div>
      <div style="display: flex; gap: var(--spacing-sm);">
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === 1"
          @click="fetchOrders(pagination.current_page - 1)"
        >
          ◀️ Prev
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchOrders(pagination.current_page + 1)"
        >
          Next ▶️
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
          style="background: #dc2626; color: #ffffff; font-weight: 700;"
        >
          {{ isDeleting ? 'Deleting...' : 'Yes, Delete Order' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

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
      await fetchKPIs();
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
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

const statusCounts = ref({
  order_placed: 0,
  processing: 0,
  delivered: 0,
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
      search: filters.search,
      status: filters.status,
      payment_status: filters.payment_status,
    };

    const response = await axios.get('/api/admin/orders', { params });
    if (response.data && response.data.success) {
      orders.value = response.data.data;
      pagination.current_page = response.data.meta.current_page;
      pagination.last_page = response.data.meta.last_page;
      pagination.per_page = response.data.meta.per_page;
      pagination.total = response.data.meta.total;
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
    const allRes = await axios.get('/api/admin/orders', { params: { per_page: 100 } });
    if (allRes.data && allRes.data.success) {
      const list = allRes.data.data;
      statusCounts.value.order_placed = list.filter(o => o.status === 'order_placed' || o.status === 'pending').length;
      statusCounts.value.processing = list.filter(o => ['processing', 'ready_to_ship', 'order_confirmed', 'confirmed'].includes(o.status)).length;
      statusCounts.value.delivered = list.filter(o => o.status === 'delivered').length;
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
  fetchKPIs();
});
</script>

<style scoped>
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
