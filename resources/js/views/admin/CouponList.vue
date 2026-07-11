<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Coupons Management master</h1>
      <span class="admin-page__subtitle">Configure customer promotional codes and rules.</span>
    </div>
    <router-link to="/admin/coupons/create" class="btn btn--primary">
      <span>➕</span> Create Coupon
    </router-link>
  </div>

  <!-- Search and Filters Bar -->
  <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg); margin-top: var(--spacing-md);">
    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
      <!-- Search Input -->
      <div style="flex: 1; min-width: 280px; position: relative;">
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search by Code or Name..." 
          class="form-input" 
          style="padding-left: 2rem;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🔍</span>
      </div>

      <!-- Filters Dropdowns -->
      <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Status:</label>
          <select v-model="filters.status" @change="fetchCoupons(1)" class="form-input" style="width: 130px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Alert -->
  <div v-if="successMsg" class="badge badge--success" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ✅ {{ successMsg }}
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading Coupons...</div>
  </div>

  <!-- Coupons Table -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="coupon in coupons" :key="coupon.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div>
              <div class="mdc-title"><code>{{ coupon.code }}</code></div>
              <div class="mdc-date">{{ coupon.name }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">{{ formatDiscountValue(coupon) }} ({{ coupon.type.replace('_', ' ') }})</span>
            <span class="mdc-email">Usages: {{ coupon.times_used }} / {{ coupon.max_uses_total || '∞' }}</span>
          </div>
          <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; flex-direction: column; font-size: 0.8rem; color: var(--color-text-muted);">
            <span>Start: {{ formatDate(coupon.starts_at) }}</span>
            <span v-if="coupon.expires_at">End: {{ formatDate(coupon.expires_at) }}</span>
            <span v-else>End: Never expires</span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', getStatusBadgeClass(coupon)]">
              {{ getStatusLabel(coupon) }}
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <router-link :to="`/admin/coupons/${coupon.id}/edit`" class="btn btn--secondary btn--sm">Edit</router-link>
            <button class="btn btn--danger btn--sm" @click="deleteCoupon(coupon.id)">Delete</button>
          </div>
        </div>
      </div>
      
      <div v-if="coupons.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No promo coupons found. Click "Create Coupon" to add one.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>Type</th>
          <th>Discount Value</th>
          <th>Usages</th>
          <th>Validity Range</th>
          <th>Status</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="coupon in coupons" :key="coupon.id">
          <td style="font-weight: bold; color: var(--color-primary);">
            <code>{{ coupon.code }}</code>
          </td>
          <td>
            <div style="display: flex; flex-direction: column;">
              <span style="font-weight: 500; color: #1e293b;">{{ coupon.name }}</span>
              <span style="font-size: 0.75rem; color: var(--color-text-muted);" v-if="coupon.description">{{ coupon.description }}</span>
            </div>
          </td>
          <td style="text-transform: capitalize;">{{ coupon.type.replace('_', ' ') }}</td>
          <td style="font-weight: bold; color: #1e293b;">
            {{ formatDiscountValue(coupon) }}
          </td>
          <td>
            {{ coupon.times_used }} / {{ coupon.max_uses_total || '∞' }}
          </td>
          <td>
            <div style="font-size: 0.8rem;">
              <span>Start: {{ formatDate(coupon.starts_at) }}</span><br/>
              <span v-if="coupon.expires_at">End: {{ formatDate(coupon.expires_at) }}</span>
              <span v-else style="color: var(--color-text-muted);">End: Never expires</span>
            </div>
          </td>
          <td>
            <span :class="['badge', getStatusBadgeClass(coupon)]">
              {{ getStatusLabel(coupon) }}
            </span>
          </td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <router-link :to="`/admin/coupons/${coupon.id}/edit`" class="btn btn--secondary btn--sm">
                ✏️ Edit
              </router-link>
              <button class="btn btn--danger btn--sm" @click="deleteCoupon(coupon.id)">
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="coupons.length === 0">
          <td colspan="8" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No promo coupons found. Click "Create Coupon" to add one.
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
          @click="fetchCoupons(pagination.current_page - 1)"
        >
          Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchCoupons(pagination.current_page + 1)"
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

const coupons = ref([]);
const loading = ref(true);
const successMsg = ref('');
const errorMsg = ref('');

const filters = ref({
  search: '',
  status: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

let searchTimeout = null;

const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchCoupons(1);
  }, 350);
};

const fetchCoupons = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/coupons', {
      params: {
        page,
        status: filters.value.status,
        search: filters.value.search,
      }
    });

    if (response.data && response.data.success) {
      coupons.value = response.data.data;
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        total: response.data.meta.total,
      };
    }
  } catch (err) {
    console.error('Failed to fetch coupons:', err);
    errorMsg.value = 'Failed to load coupon configurations';
  } finally {
    loading.value = false;
  }
};

const deleteCoupon = async (id) => {
  if (!confirm('Are you sure you want to delete this coupon? This action cannot be undone.')) {
    return;
  }
  try {
    const response = await axios.delete(`/api/admin/coupons/${id}`);
    if (response.data && response.data.success) {
      successMsg.value = 'Coupon deleted successfully';
      setTimeout(() => successMsg.value = '', 4000);
      fetchCoupons(pagination.value.current_page);
    }
  } catch (err) {
    console.error('Failed to delete coupon:', err);
    errorMsg.value = 'Failed to delete coupon configuration';
    setTimeout(() => errorMsg.value = '', 4000);
  }
};

const formatDiscountValue = (coupon) => {
  if (coupon.type === 'percentage') {
    return `${parseFloat(coupon.value)}%`;
  } else if (coupon.type === 'flat') {
    return `₹${parseFloat(coupon.value).toFixed(2)}`;
  } else if (coupon.type === 'free_shipping') {
    return 'Free Shipping';
  }
  return '—';
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const getStatusLabel = (coupon) => {
  if (!coupon.is_active) return 'Inactive';
  const now = new Date();
  const starts = new Date(coupon.starts_at);
  const expires = coupon.expires_at ? new Date(coupon.expires_at) : null;
  
  if (now < starts) return 'Scheduled';
  if (expires && now > expires) return 'Expired';
  if (coupon.max_uses_total && coupon.times_used >= coupon.max_uses_total) return 'Full';
  
  return 'Active';
};

const getStatusBadgeClass = (coupon) => {
  const label = getStatusLabel(coupon);
  switch (label) {
    case 'Active': return 'badge--success';
    case 'Scheduled': return 'badge--warning';
    case 'Expired': return 'badge--danger';
    case 'Full': return 'badge--danger';
    default: return 'badge--danger';
  }
};

onMounted(() => {
  fetchCoupons();
});
</script>
