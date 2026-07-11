<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Returns & Refunds</h1>
      <span class="admin-page__subtitle">Manage customer product return requests and credit refunds.</span>
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
      <div class="stat-card__title">Total Requests</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">{{ pagination.total }}</div>
    </div>
    <div class="stat-card-new">
      <div class="stat-card__title">Pending Review</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-warning);">{{ metrics.pending }}</div>
    </div>
    <div class="stat-card-new">
      <div class="stat-card__title">QC Passed</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-primary);">{{ metrics.qc_passed }}</div>
    </div>
    <div class="stat-card-new">
      <div class="stat-card__title">Refunded</div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-success);">{{ metrics.refunded }}</div>
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
          placeholder="Search by Return #, Order # or customer name..." 
          class="form-input" 
          style="padding-left: 2rem;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🔍</span>
      </div>

      <!-- Filters Dropdowns -->
      <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Status:</label>
          <select v-model="filters.status" @change="fetchReturns(1)" class="form-input" style="width: 155px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Statuses</option>
            <option value="requested">Requested</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="pickup_scheduled">Pickup Scheduled</option>
            <option value="picked_up">Picked Up</option>
            <option value="qc_passed">QC Passed</option>
            <option value="qc_failed">QC Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>

        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Reason:</label>
          <select v-model="filters.reason" @change="fetchReturns(1)" class="form-input" style="width: 160px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Reasons</option>
            <option value="wrong_size">Wrong Size</option>
            <option value="defective">Defective Product</option>
            <option value="not_as_described">Not as Described</option>
            <option value="wrong_item">Wrong Item Sent</option>
            <option value="other">Other Reason</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading return requests...</div>
  </div>

  <!-- Returns Table -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="ret in returns" :key="ret.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div>
              <router-link :to="`/admin/returns/${ret.id}`" class="mdc-title">
                {{ ret.return_number }}
              </router-link>
              <div class="mdc-date">{{ formatDate(ret.created_at) }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">{{ ret.user?.first_name }} {{ ret.user?.last_name }}</span>
            <span class="mdc-email">Order: <router-link :to="`/admin/orders/${ret.order_id}`" style="color: var(--color-primary); text-decoration: none;">{{ ret.order?.order_number || '—' }}</router-link></span>
          </div>
          <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between;">
            <span style="text-transform: capitalize;">Reason: <strong>{{ ret.reason.replace('_', ' ') }}</strong></span>
            <span>Refund: <strong style="color: #1e293b;">₹{{ ret.refund_amount ? parseFloat(ret.refund_amount).toFixed(2) : '—' }}</strong></span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', getStatusBadgeClass(ret.status)]">
              {{ ret.status.replace('_', ' ') }}
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <router-link :to="`/admin/returns/${ret.id}`" class="btn btn--secondary btn--sm">
              Manage
            </router-link>
          </div>
        </div>
      </div>
      
      <div v-if="returns.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No product return requests matched the active search filters.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th>Return Request #</th>
          <th>Order #</th>
          <th>Customer</th>
          <th>Reason</th>
          <th>Tentative Refund</th>
          <th>Date Placed</th>
          <th>Status</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ret in returns" :key="ret.id">
          <td style="font-weight: bold; color: var(--color-primary);">
            <router-link :to="`/admin/returns/${ret.id}`" style="color: var(--color-primary); text-decoration: none;">
              {{ ret.return_number }}
            </router-link>
          </td>
          <td>
            <router-link :to="`/admin/orders/${ret.order_id}`" style="color: #1e293b; text-decoration: none; font-weight: 500;">
              {{ ret.order?.order_number || '—' }}
            </router-link>
          </td>
          <td>
            <div style="display: flex; flex-direction: column;">
              <span style="font-weight: 500; color: #1e293b;">{{ ret.user?.first_name }} {{ ret.user?.last_name }}</span>
              <span style="font-size: 0.75rem; color: var(--color-text-muted);">{{ ret.user?.email }}</span>
            </div>
          </td>
          <td style="text-transform: capitalize;">{{ ret.reason.replace('_', ' ') }}</td>
          <td style="font-weight: bold; color: #1e293b;">
            ₹{{ ret.refund_amount ? parseFloat(ret.refund_amount).toFixed(2) : '—' }}
          </td>
          <td>{{ formatDate(ret.created_at) }}</td>
          <td>
            <span :class="['badge', getStatusBadgeClass(ret.status)]">
              {{ ret.status.replace('_', ' ') }}
            </span>
          </td>
          <td style="text-align: right;">
            <router-link :to="`/admin/returns/${ret.id}`" class="btn btn--secondary btn--sm">
              👁️ Manage Return
            </router-link>
          </td>
        </tr>
        <tr v-if="returns.length === 0">
          <td colspan="8" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No product return requests matched the active search filters.
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
          @click="fetchReturns(pagination.current_page - 1)"
        >
          Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchReturns(pagination.current_page + 1)"
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
const returns = ref([]);
const loading = ref(true);
const errorMsg = ref('');

const filters = ref({
  search: '',
  status: '',
  reason: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const metrics = ref({
  pending: 0,
  qc_passed: 0,
  refunded: 0,
});

let searchTimeout = null;

const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchReturns(1);
  }, 350);
};

const fetchReturns = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/returns', {
      params: {
        page,
        status: filters.value.status,
        reason: filters.value.reason,
        search: filters.value.search,
      }
    });

    if (response.data && response.data.success) {
      returns.value = response.data.data;
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        total: response.data.meta.total,
      };
    }
  } catch (err) {
    console.error('Failed to fetch return requests:', err);
    errorMsg.value = 'Failed to load return requests';
  } finally {
    loading.value = false;
  }
};

const fetchMetrics = async () => {
  try {
    const response = await axios.get('/api/admin/returns', { params: { per_page: 100 } });
    if (response.data && response.data.success) {
      const list = response.data.data;
      metrics.value.pending = list.filter(r => r.status === 'requested').length;
      metrics.value.qc_passed = list.filter(r => r.status === 'qc_passed').length;
      metrics.value.refunded = list.filter(r => r.status === 'refunded').length;
    }
  } catch (e) {
    console.error('Failed to load metrics:', e);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'requested': return 'badge--warning';
    case 'approved': return 'badge--secondary';
    case 'rejected': return 'badge--danger';
    case 'pickup_scheduled': return 'badge--secondary';
    case 'picked_up': return 'badge--secondary';
    case 'qc_passed': return 'badge--primary';
    case 'qc_failed': return 'badge--danger';
    case 'refunded': return 'badge--success';
    default: return '';
  }
};

onMounted(() => {
  fetchReturns();
  fetchMetrics();
});
</script>
