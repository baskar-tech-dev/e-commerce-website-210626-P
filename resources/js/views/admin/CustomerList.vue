<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Customer Directory</h1>
      <span class="admin-page__subtitle">View customer profiles, purchase history, and account details.</span>
    </div>
    <div class="admin-header__actions">
      <span class="badge-purchased-count">
        🛒 Showing Customers with Purchases
      </span>
    </div>
  </div>

  <!-- Customer Summary Section -->
  <div class="stats-grid-4" style="margin-bottom: 1.5rem;">
    <!-- Total Customers Card -->
    <div class="stat-card-new">
      <div class="stat-card__title">Total Customers</div>
      <div class="stat-card__content">
        <div class="stat-card__value" style="font-size: 1.65rem;">{{ summary.total_customers || 0 }}</div>
      </div>
      <div class="stat-card__meta">
        <span>All Registered Accounts</span>
      </div>
    </div>

    <!-- Purchasing Customers Card -->
    <div class="stat-card-new">
      <div class="stat-card__title">Purchasing Customers</div>
      <div class="stat-card__content">
        <div class="stat-card__value" style="font-size: 1.65rem; color: #6E1F3A;">{{ summary.purchased_customers || 0 }}</div>
      </div>
      <div class="stat-card__meta">
        <span>Placed 1 or More Orders</span>
      </div>
    </div>

    <!-- Paid Customers Card -->
    <div class="stat-card-new stat-card-new--highlight">
      <div class="stat-card__title">Paid Customers</div>
      <div class="stat-card__content">
        <div class="stat-card__value" style="font-size: 1.65rem; color: #047857;">{{ summary.paid_customers || 0 }}</div>
      </div>
      <div class="stat-card__meta">
        <span class="badge-paid-pill">Completed Payments</span>
      </div>
    </div>

    <!-- Paid Revenue Card -->
    <div class="stat-card-new">
      <div class="stat-card__title">Customer Paid Revenue</div>
      <div class="stat-card__content">
        <div class="stat-card__value" style="font-size: 1.55rem; color: #6E1F3A;">
          ₹{{ formatCurrency(summary.paid_revenue || 0) }}
        </div>
      </div>
      <div class="stat-card__meta">
        <span>Lifetime Net Paid Sales</span>
      </div>
    </div>
  </div>

  <!-- Filters Panel -->
  <div class="glass-panel" style="padding: 1.25rem; margin-bottom: 1.5rem; background: #ffffff; border: 1px solid var(--color-border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
      <!-- Search Input -->
      <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
        <label class="form-label" style="font-weight: 600; color: #475569; font-size: 0.82rem;">Search Customers</label>
        <div style="position: relative;">
          <input 
            type="text" 
            v-model="filters.search" 
            @input="debounceSearch"
            class="form-input" 
            style="padding-left: 2.2rem; height: 38px; border-radius: 8px;"
            placeholder="Search by name, email, phone..." 
          />
          <Search :size="15" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8;" />
        </div>
      </div>

      <!-- Purchase Filter Dropdown -->
      <div class="form-group" style="width: 230px; margin-bottom: 0;">
        <label class="form-label" style="font-weight: 600; color: #475569; font-size: 0.82rem;">Purchase Filter</label>
        <select v-model="filters.purchase_filter" @change="applyFilters" class="form-select" style="height: 38px; border-radius: 8px; font-weight: 500;">
          <option value="purchased">🛒 Actually Purchased (Orders > 0)</option>
          <option value="paid">✅ Paid Purchases Only</option>
          <option value="all">👥 All Registered Accounts</option>
        </select>
      </div>

      <!-- Account Status Dropdown -->
      <div class="form-group" style="width: 140px; margin-bottom: 0;">
        <label class="form-label" style="font-weight: 600; color: #475569; font-size: 0.82rem;">Status</label>
        <select v-model="filters.status" @change="applyFilters" class="form-select" style="height: 38px; border-radius: 8px; font-weight: 500;">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>

      <!-- Sort Dropdown -->
      <div class="form-group" style="width: 170px; margin-bottom: 0;">
        <label class="form-label" style="font-weight: 600; color: #475569; font-size: 0.82rem;">Sort By</label>
        <select v-model="filters.sort_by" @change="applyFilters" class="form-select" style="height: 38px; border-radius: 8px; font-weight: 500;">
          <option value="total_spent">Highest Paid Spend</option>
          <option value="paid_orders">Most Paid Orders</option>
          <option value="created_at">Joined Date</option>
        </select>
      </div>

      <!-- Reset Button -->
      <button class="btn btn-reset" @click="resetFilters" title="Reset all filters">
        <RotateCcw :size="13" />
        <span>Reset</span>
      </button>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="customerStore.loading && customers.length === 0" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading Customers...</div>
  </div>

  <!-- Customers List Grid -->
  <div v-else class="glass-panel" style="overflow: hidden; background: #ffffff; border-radius: 12px; border: 1px solid var(--color-border);">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="(customer, index) in customers" :key="customer.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span class="sno-badge">#{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</span>
            <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(110, 31, 58, 0.08); color: #6E1F3A; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1.5px solid rgba(110, 31, 58, 0.2); overflow: hidden; flex-shrink: 0;">
              <img v-if="customer.avatar" :src="customer.avatar" style="width: 100%; height: 100%; object-fit: cover;" alt="" />
              <User v-else :size="18" />
            </div>
            <div>
              <div class="mdc-title">{{ customer.name }}</div>
              <div class="mdc-date">Joined: {{ formatDate(customer.created_at) }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">{{ customer.email }}</span>
            <span class="mdc-email">Phone: {{ customer.phone || 'No phone' }}</span>
          </div>
          <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <span>Paid Orders: <strong style="color: #047857;">{{ customer.paid_orders_count || 0 }}</strong></span>
            <span>Total Spend (Paid): <strong style="color: #6E1F3A;">₹{{ formatCurrency(customer.total_spent || 0) }}</strong></span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', customer.is_active ? 'badge--success' : 'badge--danger']">
              {{ customer.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <router-link 
              :to="{ name: 'admin.customers.show', params: { id: customer.id } }" 
              class="btn-action-icon btn-action-icon--view"
              title="View Customer Profile"
              aria-label="View Customer Profile"
            >
              <Eye :size="16" />
            </router-link>
          </div>
        </div>
      </div>
      
      <div v-if="customers.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No purchasing customers found matching the criteria.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 55px; text-align: center;">S.No</th>
          <th>Customer</th>
          <th>Contact</th>
          <th style="text-align: center;">Paid Orders</th>
          <th>Total Spend (Paid)</th>
          <th>Status</th>
          <th>Joined</th>
          <th style="text-align: center; width: 80px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(customer, index) in customers" :key="customer.id">
          <!-- S.No Column -->
          <td style="text-align: center; font-weight: 600; color: #64748B; font-size: 0.85rem;">
            {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
          </td>
          <td>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
              <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(110, 31, 58, 0.06); color: #6E1F3A; display: flex; align-items: center; justify-content: center; border: 1.5px solid rgba(110, 31, 58, 0.2); overflow: hidden; flex-shrink: 0;">
                <img v-if="customer.avatar" :src="customer.avatar" style="width: 100%; height: 100%; object-fit: cover;" alt="" />
                <User v-else :size="18" />
              </div>
              <div>
                <div style="font-weight: 600; color: #1e293b;">{{ customer.name }}</div>
                <div style="font-size: 0.78rem; color: var(--color-text-muted);">ID: {{ customer.id }}</div>
              </div>
            </div>
          </td>
          <td>
            <div style="color: #1e293b; font-size: 0.88rem;">{{ customer.email }}</div>
            <div v-if="customer.phone" style="font-size: 0.78rem; color: var(--color-text-muted);">{{ customer.phone }}</div>
            <div v-else style="font-size: 0.78rem; color: var(--color-text-muted); font-style: italic;">No phone</div>
          </td>
          <td style="text-align: center;">
            <span class="badge-paid-count">
              {{ customer.paid_orders_count || 0 }} Paid
            </span>
          </td>
          <td style="font-weight: 700; color: #6E1F3A;">
            ₹{{ formatCurrency(customer.total_spent || 0) }}
          </td>
          <td>
            <span :class="['badge', customer.is_active ? 'badge--success' : 'badge--danger']">
              {{ customer.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td style="font-size: 0.86rem; color: #64748B;">{{ formatDate(customer.created_at) }}</td>
          <td style="text-align: center; width: 80px; white-space: nowrap;">
            <router-link 
              :to="{ name: 'admin.customers.show', params: { id: customer.id } }" 
              class="btn-action-icon btn-action-icon--view"
              title="View Customer Profile"
              aria-label="View Customer Profile"
            >
              <Eye :size="16" />
            </router-link>
          </td>
        </tr>
        <tr v-if="customers.length === 0">
          <td colspan="8" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No purchasing customers found matching the criteria.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Footer -->
    <div class="pagination-footer">
      <div class="pagination-info">
        Showing 
        <strong>{{ pagination.total ? (pagination.current_page - 1) * (pagination.per_page || 15) + 1 : 0 }}</strong> 
        to 
        <strong>{{ Math.min(pagination.current_page * (pagination.per_page || 15), pagination.total || 0) }}</strong> 
        of 
        <strong>{{ pagination.total || 0 }}</strong> customers
      </div>

      <div class="pagination-controls">
        <!-- Per Page Dropdown -->
        <div class="per-page-selector">
          <label class="per-page-label">Show:</label>
          <select v-model="filters.per_page" @change="onPerPageChange" class="per-page-select">
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>

        <!-- Page Navigation Buttons -->
        <div class="pagination-pages">
          <button 
            class="btn-pagination" 
            :disabled="pagination.current_page <= 1" 
            @click="changePage(pagination.current_page - 1)"
            title="Previous Page"
          >
            <ChevronLeft :size="15" />
            <span>Prev</span>
          </button>

          <!-- Numbered Page Pills -->
          <template v-for="(p, i) in visiblePages" :key="i">
            <span v-if="p === '...'" class="pagination-ellipsis">...</span>
            <button 
              v-else 
              class="btn-page-number" 
              :class="{ 'btn-page-number--active': p === pagination.current_page }"
              @click="changePage(p)"
            >
              {{ p }}
            </button>
          </template>

          <button 
            class="btn-pagination" 
            :disabled="pagination.current_page >= pagination.last_page" 
            @click="changePage(pagination.current_page + 1)"
            title="Next Page"
          >
            <span>Next</span>
            <ChevronRight :size="15" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useCustomerStore } from '../../stores/customer';
import { 
  Search, 
  RotateCcw, 
  Eye, 
  User, 
  Users, 
  ShoppingBag, 
  CheckCircle2, 
  CreditCard,
  ChevronLeft,
  ChevronRight,
} from 'lucide-vue-next';

const customerStore = useCustomerStore();
const customers = computed(() => customerStore.customers);
const summary = computed(() => customerStore.summary || {});
const pagination = computed(() => customerStore.pagination || { current_page: 1, last_page: 1, per_page: 15, total: 0 });

// Default to 'purchased': only customers who actually purchased products
const filters = ref({
  search: '',
  purchase_filter: 'purchased',
  status: '',
  sort_by: 'total_spent',
  page: 1,
  per_page: 15,
});

let searchTimeout = null;

onMounted(() => {
  fetchData();
});

function fetchData() {
  customerStore.fetchCustomers(filters.value);
}

function debounceSearch() {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    filters.value.page = 1;
    applyFilters();
  }, 300);
}

function applyFilters() {
  filters.value.page = 1;
  fetchData();
}

function resetFilters() {
  filters.value = {
    search: '',
    purchase_filter: 'purchased',
    status: '',
    sort_by: 'total_spent',
    page: 1,
    per_page: 15,
  };
  fetchData();
}

const visiblePages = computed(() => {
  const current = pagination.value.current_page || 1;
  const last = pagination.value.last_page || 1;
  if (last <= 1) return [1];

  const delta = 2;
  const range = [];
  const rangeWithDots = [];
  let l;

  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
      range.push(i);
    }
  }

  for (let i of range) {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push('...');
      }
    }
    rangeWithDots.push(i);
    l = i;
  }

  return rangeWithDots;
});

function onPerPageChange() {
  filters.value.page = 1;
  fetchData();
}

function changePage(page) {
  if (page === '...' || page < 1 || page > (pagination.value.last_page || 1)) return;
  filters.value.page = page;
  fetchData();
}

function formatDate(dateString) {
  if (!dateString) return '—';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
}

function formatCurrency(val) {
  const num = parseFloat(val) || 0;
  return num.toLocaleString('en-IN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}
</script>

<style scoped>
.badge-purchased-count {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(110, 31, 58, 0.08);
  color: #6E1F3A;
  border: 1px solid rgba(110, 31, 58, 0.25);
  padding: 4px 12px;
  border-radius: 14px;
  font-size: 0.78rem;
  font-weight: 600;
}

/* 4 Column Stats KPI Grid */
.stats-grid-4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

@media (max-width: 1024px) {
  .stats-grid-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-grid-4 {
    grid-template-columns: 1fr;
  }
}

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

.stat-card-new--highlight {
  border-color: rgba(16, 185, 129, 0.35);
  background: linear-gradient(180deg, #ffffff 0%, rgba(16, 185, 129, 0.02) 100%);
}

.stat-card__title {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.stat-card__content {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.5rem;
  margin-top: 0.15rem;
}

.stat-card__value {
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
}

.stat-card__meta {
  font-size: 0.76rem;
  color: #64748B;
}

.badge-paid-pill {
  display: inline-block;
  background: rgba(16, 185, 129, 0.12);
  color: #047857;
  padding: 1px 8px;
  border-radius: 10px;
  font-size: 0.72rem;
  font-weight: 600;
}

.btn-reset {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0 14px;
  height: 38px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #64748B;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reset:hover {
  color: #dc2626;
  border-color: #fca5a5;
  background: #fef2f2;
}

.badge-paid-count {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.74rem;
  font-weight: 700;
  background: rgba(16, 185, 129, 0.12);
  color: #047857;
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.sno-badge {
  font-size: 0.75rem;
  font-weight: 700;
  color: #64748B;
  background: #f1f5f9;
  padding: 2px 6px;
  border-radius: 6px;
}

/* Icon-Only Action Buttons */
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

.btn-action-icon--view {
  background: rgba(110, 31, 58, 0.06);
  color: #6E1F3A;
  border: 1.5px solid rgba(110, 31, 58, 0.28);
  box-shadow: 0 1px 3px rgba(110, 31, 58, 0.05);
}

.btn-action-icon--view:hover {
  background: #6E1F3A;
  color: #ffffff;
  border-color: #6E1F3A;
  box-shadow: 0 4px 12px rgba(110, 31, 58, 0.28);
  transform: translateY(-1px);
}

/* Pagination Footer Controls */
.pagination-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: #ffffff;
  border-top: 1px solid var(--color-border);
}

.pagination-info {
  font-size: 0.85rem;
  color: #64748B;
}

.pagination-info strong {
  color: #1e293b;
  font-weight: 600;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.per-page-selector {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.82rem;
  color: #64748B;
}

.per-page-select {
  height: 32px;
  padding: 0 0.5rem;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #1e293b;
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
}

.pagination-pages {
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-pagination {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0 10px;
  height: 32px;
  min-width: 32px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #334155;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  cursor: pointer;
  justify-content: center;
  transition: all 0.2s ease;
  user-select: none;
}

.btn-pagination:hover:not(:disabled) {
  color: #6E1F3A;
  border-color: #6E1F3A;
  background: rgba(110, 31, 58, 0.04);
}

.btn-pagination:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  background: #f8fafc;
}

.btn-page-number {
  min-width: 32px;
  height: 32px;
  padding: 0 6px;
  border-radius: 6px;
  font-size: 0.82rem;
  font-weight: 600;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #475569;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s ease;
}

.btn-page-number:hover {
  border-color: #6E1F3A;
  color: #6E1F3A;
  background: rgba(110, 31, 58, 0.04);
}

.btn-page-number--active {
  background: #6E1F3A !important;
  color: #ffffff !important;
  border-color: #6E1F3A !important;
  box-shadow: 0 2px 6px rgba(110, 31, 58, 0.25);
}

.pagination-ellipsis {
  padding: 0 4px;
  color: #94a3b8;
  font-weight: 600;
  font-size: 0.85rem;
}
</style>