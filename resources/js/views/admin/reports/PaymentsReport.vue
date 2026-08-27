<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <router-link to="/admin/reports" class="btn btn--secondary btn--sm" style="padding: 4px 8px;" title="Back to Reports Hub">
          ← Reports Hub
        </router-link>
        <h1 class="admin-page__title">Cashfree Payments Report</h1>
      </div>
      <span class="admin-page__subtitle">
        Monitor real-time payments, transaction statuses, payment methods, and daily collections.
      </span>
    </div>
    <div class="admin-header__actions">
      <button @click="showStats = !showStats" class="btn btn--secondary btn--sm">
        {{ showStats ? '👁 Hide KPI Cards' : '👁 Show KPI Cards' }}
      </button>
      <button @click="refreshAll" class="btn btn--primary btn--sm" :disabled="loading">
        🔄 Refresh Data
      </button>
    </div>
  </div>

  <!-- Navigation Sub-tabs -->
  <div style="display: flex; gap: var(--spacing-sm); border-bottom: 1px solid var(--color-border); padding-bottom: 1px; margin-bottom: var(--spacing-lg); margin-top: var(--spacing-xs); flex-wrap: wrap;">
    <router-link v-if="canViewAnalytics" to="/admin/reports" class="btn btn--secondary btn--sm" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
      📊 Business Analytics
    </router-link>
    <router-link to="/admin/reports/payments" class="btn btn--primary btn--sm" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
      💳 Cashfree Payments
    </router-link>
    <router-link v-if="canViewSettlements" to="/admin/reports/settlements" class="btn btn--secondary btn--sm" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
      🏦 Payout Settlements
    </router-link>
  </div>

  <!-- Summary KPI Cards -->
  <div v-show="showStats" class="stats-grid-5" style="margin-bottom: var(--spacing-lg);">
    <!-- 1. Today's Total Payments -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__title">Today's Total Payments</div>
        <div class="stat-card__icon-wrap stat-card__icon-wrap--purple">💳</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">
        {{ summaryData.today_total_payments || 0 }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-text-muted);">All initiated attempts today</span>
    </div>

    <!-- 2. Successful Payments -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__title">Successful Payments</div>
        <div class="stat-card__icon-wrap stat-card__icon-wrap--green">✓</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-success);">
        {{ summaryData.successful_payments || 0 }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-success); font-weight: 600;">Confirmed & Captured</span>
    </div>

    <!-- 3. Pending Payments -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__title">Pending Payments</div>
        <div class="stat-card__icon-wrap stat-card__icon-wrap--orange">⏳</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-warning);">
        {{ summaryData.pending_payments || 0 }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-warning);">Awaiting bank confirmation</span>
    </div>

    <!-- 4. Failed Payments -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__title">Failed Payments</div>
        <div class="stat-card__icon-wrap stat-card__icon-wrap--red">✕</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-danger);">
        {{ summaryData.failed_payments || 0 }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-danger);">Dropped or Declined</span>
    </div>

    <!-- 5. Today's Total Collection -->
    <div class="stat-card-new" style="border-color: rgba(74, 14, 46, 0.25); background: linear-gradient(135deg, #ffffff, #fffcf7);">
      <div class="stat-card__top">
        <div class="stat-card__title" style="color: var(--color-primary); font-weight: 700;">Today's Total Collection</div>
        <div class="stat-card__icon-wrap" style="background: rgba(74, 14, 46, 0.1); color: var(--color-primary);">₹</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-primary);">
        ₹{{ formatCurrency(summaryData.today_total_collection) }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-text-secondary);">Net settled collection volume</span>
    </div>
  </div>

  <!-- Filters & Search Toolbar -->
  <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg);">
    <!-- Primary Search & Dropdowns -->
    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
      <!-- Search Input -->
      <div style="flex: 1; min-width: 280px; position: relative;">
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search by Order ID, Cashfree Order ID, Payment ID, Customer name or email..." 
          class="form-input" 
          style="padding-left: 2.2rem;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); font-size: 0.95rem;">🔍</span>
        <button 
          v-if="filters.search" 
          @click="filters.search = ''; fetchPayments(1)" 
          style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--color-text-muted);"
        >
          ✕
        </button>
      </div>

      <!-- Filters Dropdowns -->
      <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; align-items: center;">
        <!-- Payment Status Dropdown -->
        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted); font-weight: 600;">Status:</label>
          <select v-model="filters.payment_status" @change="fetchPayments(1)" class="form-input" style="min-width: 140px; padding: 0.35rem var(--spacing-md);">
            <option value="">All Statuses</option>
            <option value="captured">Captured / Paid</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>

        <!-- Payment Method Dropdown -->
        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted); font-weight: 600;">Method:</label>
          <select v-model="filters.payment_method" @change="fetchPayments(1)" class="form-input" style="min-width: 140px; padding: 0.35rem var(--spacing-md);">
            <option value="">All Methods</option>
            <option value="upi">Instant UPI</option>
            <option value="card">Credit / Debit Card</option>
            <option value="netbanking">Net Banking</option>
            <option value="wallet">Digital Wallet</option>
            <option value="cod">Cash on Delivery</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Quick Date Filter Presets & Custom Date Pickers -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: var(--spacing-md); margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--color-border);">
      <!-- Date Preset Pills -->
      <div style="display: flex; gap: 0.4rem; overflow-x: auto; align-items: center;">
        <span style="font-size: 0.8rem; color: var(--color-text-muted); font-weight: 600; margin-right: 4px;">Timeframe:</span>
        <button 
          v-for="preset in datePresets" 
          :key="preset.id"
          type="button" 
          :class="['btn btn--sm', filters.date_preset === preset.id ? 'btn--primary' : 'btn--secondary']"
          @click="selectDatePreset(preset.id)"
          style="border-radius: 14px; height: 26px; font-size: 0.75rem; padding: 0 10px; white-space: nowrap;"
        >
          {{ preset.label }}
        </button>
      </div>

      <!-- Custom Date Inputs -->
      <div v-if="filters.date_preset === 'custom'" style="display: flex; align-items: center; gap: var(--spacing-sm); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 4px;">
          <label style="font-size: 0.75rem; color: var(--color-text-muted);">From:</label>
          <input type="date" v-model="filters.start_date" class="form-input" style="padding: 0.2rem 0.5rem; width: 140px; font-size: 0.8rem;" />
        </div>
        <div style="display: flex; align-items: center; gap: 4px;">
          <label style="font-size: 0.75rem; color: var(--color-text-muted);">To:</label>
          <input type="date" v-model="filters.end_date" class="form-input" style="padding: 0.2rem 0.5rem; width: 140px; font-size: 0.8rem;" />
        </div>
        <button class="btn btn--secondary btn--sm" @click="fetchPayments(1)">Apply</button>
      </div>
    </div>
  </div>

  <!-- Error Notification Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: var(--spacing-md); padding: 0.85rem; width: 100%; border-radius: 8px; font-size: 0.88rem; display: flex; align-items: center; justify-content: space-between;">
    <span>⚠️ {{ errorMsg }}</span>
    <button @click="errorMsg = ''" style="background: none; border: none; cursor: pointer; color: inherit; font-size: 1rem;">✕</button>
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value" style="font-size: 1.25rem;">Fetching Cashfree Payments...</div>
    <span style="font-size: 0.85rem; color: var(--color-text-muted);">Synchronizing transaction records</span>
  </div>

  <!-- Payments Report Data List -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="(payment, index) in payments" :key="payment.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-primary); background: rgba(74, 14, 46, 0.08); border: 1px solid rgba(74, 14, 46, 0.15); padding: 2px 6px; border-radius: 4px; flex-shrink: 0;">
              #{{ (pagination.current_page - 1) * (pagination.per_page || 15) + index + 1 }}
            </span>
            <router-link :to="`/admin/orders/${payment.order_db_id}`" class="mdc-title">
              {{ payment.order_id }}
            </router-link>
          </div>
          <span class="mdc-date">{{ formatDate(payment.payment_date) }}</span>
        </div>

        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">{{ payment.customer_name }}</span>
            <span class="mdc-email">{{ payment.customer_email }}</span>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.25rem; font-size: 0.8rem;">
            <div>
              <span style="color: var(--color-text-muted);">Payment ID:</span><br />
              <code>{{ payment.payment_id }}</code>
            </div>
            <div>
              <span style="color: var(--color-text-muted);">CF Order ID:</span><br />
              <code>{{ payment.cashfree_order_id }}</code>
            </div>
          </div>

          <div class="mdc-totals" style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--color-border);">
            <span>Order Total: <strong>₹{{ formatCurrency(payment.order_amount) }}</strong></span>
            <span>Paid: <strong style="color: var(--color-primary);">₹{{ formatCurrency(payment.payment_amount) }}</strong></span>
          </div>
        </div>

        <div class="mdc-footer">
          <div class="mdc-badges">
            <div style="display: flex; gap: 4px; align-items: center;">
              <span :class="['badge', getPaymentBadgeClass(payment.payment_status)]">
                {{ payment.payment_status }}
              </span>
              <span :class="['badge', getCashfreeBadgeClass(payment.cashfree_status)]" title="Cashfree Gateway Status">
                CF: {{ payment.cashfree_status }}
              </span>
            </div>
            <span style="font-size: 0.7rem; color: var(--color-text-muted); text-transform: uppercase;">
              Method: {{ payment.payment_method }}
            </span>
          </div>

          <div style="display: flex; gap: 0.4rem; align-items: center;">
            <router-link :to="`/admin/orders/${payment.order_db_id}`" class="btn btn--secondary btn--sm">
              View Order
            </router-link>
            <button 
              @click="syncPayment(payment)" 
              class="btn btn--secondary btn--sm" 
              :disabled="verifyingId === payment.id"
              title="Live verify with Cashfree Gateway"
            >
              {{ verifyingId === payment.id ? '...' : '⚡ Sync' }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="payments.length === 0" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
        No payment transactions found matching the selected criteria.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 50px; text-align: center;">S.No</th>
          <th>Order ID</th>
          <th>Cashfree Order ID</th>
          <th>Payment ID</th>
          <th>Customer</th>
          <th style="text-align: right;">Order Amt</th>
          <th style="text-align: right;">Paid Amt</th>
          <th>Method</th>
          <th>Local Status</th>
          <th>Cashfree Status</th>
          <th>Payment Date</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(payment, index) in payments" :key="payment.id">
          <td style="width: 50px; text-align: center; font-weight: 600; color: var(--color-text-secondary); font-size: 0.85rem;">
            {{ (pagination.current_page - 1) * (pagination.per_page || 15) + index + 1 }}
          </td>

          <!-- Order ID Link -->
          <td style="font-weight: bold; color: var(--color-primary);">
            <router-link :to="`/admin/orders/${payment.order_db_id}`" style="color: var(--color-primary); text-decoration: none;">
              {{ payment.order_id }}
            </router-link>
          </td>

          <!-- Cashfree Order ID -->
          <td>
            <code style="font-size: 0.8rem; background: rgba(0,0,0,0.03); padding: 2px 5px; border-radius: 4px;">
              {{ payment.cashfree_order_id }}
            </code>
          </td>

          <!-- Cashfree Payment ID -->
          <td>
            <code style="font-size: 0.8rem; background: rgba(0,0,0,0.03); padding: 2px 5px; border-radius: 4px;">
              {{ payment.payment_id }}
            </code>
          </td>

          <!-- Customer Info -->
          <td>
            <div style="display: flex; flex-direction: column;">
              <span style="font-weight: 500; color: #1e293b;">{{ payment.customer_name }}</span>
              <span style="font-size: 0.75rem; color: var(--color-text-muted);">{{ payment.customer_email }}</span>
            </div>
          </td>

          <!-- Order Amount -->
          <td style="text-align: right; color: var(--color-text-secondary); font-weight: 500;">
            ₹{{ formatCurrency(payment.order_amount) }}
          </td>

          <!-- Payment Amount -->
          <td style="text-align: right; font-weight: 700; color: var(--color-primary);">
            ₹{{ formatCurrency(payment.payment_amount) }}
          </td>

          <!-- Payment Method -->
          <td>
            <span class="badge badge--secondary" style="font-size: 0.75rem;">
              {{ payment.payment_method }}
            </span>
          </td>

          <!-- Local Payment Status -->
          <td>
            <span :class="['badge', getPaymentBadgeClass(payment.payment_status)]" style="font-weight: 600; font-size: 0.75rem;">
              {{ payment.payment_status }}
            </span>
          </td>

          <!-- Cashfree Gateway Status -->
          <td>
            <span :class="['badge', getCashfreeBadgeClass(payment.cashfree_status)]" style="font-weight: 600; font-size: 0.75rem;">
              {{ payment.cashfree_status }}
            </span>
          </td>

          <!-- Payment Date -->
          <td style="font-size: 0.82rem; color: var(--color-text-secondary); white-space: nowrap;">
            {{ formatDate(payment.payment_date) }}
          </td>

          <!-- Actions -->
          <td style="text-align: right; white-space: nowrap;">
            <div style="display: inline-flex; gap: 0.35rem;">
              <router-link :to="`/admin/orders/${payment.order_db_id}`" class="btn btn--secondary btn--sm" title="View Full Order Details">
                👁️ Order
              </router-link>
              <button 
                @click="syncPayment(payment)" 
                class="btn btn--secondary btn--sm" 
                :disabled="verifyingId === payment.id"
                title="Verify live with Cashfree PG"
              >
                {{ verifyingId === payment.id ? 'Syncing...' : '⚡ Sync' }}
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="payments.length === 0">
          <td colspan="12" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            No Cashfree payment transactions match the active filter criteria.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md) var(--spacing-lg); border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.01);">
      <div style="font-size: 0.85rem; color: var(--color-text-muted);">
        Showing page <strong>{{ pagination.current_page }}</strong> of <strong>{{ pagination.last_page }}</strong> (Total: {{ pagination.total }} payments)
      </div>

      <div style="display: flex; gap: var(--spacing-sm); align-items: center;">
        <select v-model="pagination.per_page" @change="fetchPayments(1)" class="form-input" style="width: 80px; padding: 0.2rem 0.5rem; font-size: 0.8rem;">
          <option :value="10">10 / pg</option>
          <option :value="15">15 / pg</option>
          <option :value="25">25 / pg</option>
          <option :value="50">50 / pg</option>
        </select>

        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === 1 || loading"
          @click="fetchPayments(pagination.current_page - 1)"
        >
          ◀️ Prev
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page || loading"
          @click="fetchPayments(pagination.current_page + 1)"
        >
          Next ▶️
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const isSuperAdmin = computed(() => {
  return authStore.user?.roles?.some(r => r.name === 'super_admin') || 
         authStore.user?.role?.name === 'super_admin' || 
         authStore.user?.role_id === 1;
});

const userPermissions = computed(() => {
  return authStore.user?.roles?.flatMap(r => r.permissions?.map(p => p.name) || []) || [];
});

const hasPermission = (moduleName) => {
  if (isSuperAdmin.value || authStore.isAdminUser) return true;
  const perms = userPermissions.value;
  if (!perms || perms.length === 0) return true;
  return perms.includes(moduleName) ||
         perms.includes(`${moduleName}.view`) ||
         perms.includes('reports') ||
         perms.includes('manage_reports') ||
         perms.some(p => p.startsWith(`${moduleName}.`));
};

const canViewAnalytics = computed(() => hasPermission('reports') || hasPermission('reports_sales') || hasPermission('reports_inventory') || hasPermission('reports_customers'));
const canViewSettlements = computed(() => hasPermission('settlements'));

const showStats = ref(true);
const loading = ref(false);
const errorMsg = ref('');
const verifyingId = ref(null);
const payments = ref([]);

const summaryData = reactive({
  today_total_payments: 0,
  successful_payments: 0,
  pending_payments: 0,
  failed_payments: 0,
  today_total_collection: 0,
  range_total_collection: 0,
  range_total_payments: 0,
});

const filters = reactive({
  search: '',
  payment_status: '',
  payment_method: '',
  date_preset: 'today',
  start_date: '',
  end_date: '',
});

const datePresets = [
  { id: 'today', label: 'Today' },
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'last_7_days', label: 'Last 7 Days' },
  { id: 'last_30_days', label: 'Last 30 Days' },
  { id: 'custom', label: 'Custom Range' },
];

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

let debounceTimer = null;
const debounceSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchPayments(1);
  }, 350);
};

const selectDatePreset = (presetId) => {
  filters.date_preset = presetId;
  if (presetId !== 'custom') {
    filters.start_date = '';
    filters.end_date = '';
    fetchPayments(1);
    fetchSummary();
  }
};

const fetchPayments = async (page = 1) => {
  loading.value = true;
  errorMsg.value = '';
  try {
    const params = {
      page: page,
      per_page: pagination.per_page,
      search: filters.search,
      payment_status: filters.payment_status,
      payment_method: filters.payment_method,
      date_preset: filters.date_preset,
      start_date: filters.start_date,
      end_date: filters.end_date,
    };

    const response = await axios.get('/api/admin/reports/payments', { params });
    if (response.data && response.data.success) {
      payments.value = response.data.data;
      if (response.data.meta) {
        pagination.current_page = response.data.meta.current_page;
        pagination.last_page = response.data.meta.last_page;
        pagination.per_page = response.data.meta.per_page;
        pagination.total = response.data.meta.total;
      }
    }
  } catch (err) {
    console.error('Failed to load payments report:', err);
    errorMsg.value = err.response?.data?.message || 'Unable to fetch Cashfree payment data at the moment. Please try again.';
  } finally {
    loading.value = false;
  }
};

const fetchSummary = async () => {
  try {
    const params = {
      date_preset: filters.date_preset,
      start_date: filters.start_date,
      end_date: filters.end_date,
    };
    const response = await axios.get('/api/admin/reports/payments/summary', { params });
    if (response.data && response.data.success) {
      Object.assign(summaryData, response.data.data);
    }
  } catch (err) {
    console.error('Failed to load payments summary:', err);
  }
};

const syncPayment = async (payment) => {
  verifyingId.value = payment.id;
  try {
    const response = await axios.post(`/api/admin/reports/payments/${payment.id}/verify-cashfree`);
    if (response.data && response.data.success) {
      payment.payment_status = response.data.data.status;
      payment.cashfree_status = response.data.data.cashfree_status;
      payment.payment_id = response.data.data.gateway_payment_id || payment.payment_id;
      alert(`Payment #${payment.id} verified with Cashfree: ${response.data.data.cashfree_status}`);
      await fetchSummary();
    }
  } catch (err) {
    console.error('Sync payment error:', err);
    alert(err.response?.data?.message || 'Failed to sync with Cashfree gateway.');
  } finally {
    verifyingId.value = null;
  }
};

const refreshAll = async () => {
  await Promise.all([fetchPayments(pagination.current_page), fetchSummary()]);
};

const formatCurrency = (val) => {
  const num = parseFloat(val) || 0;
  return num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return new Date(dateString).toLocaleDateString('en-IN', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getPaymentBadgeClass = (status) => {
  switch (status?.toLowerCase()) {
    case 'captured':
    case 'paid':
      return 'badge--success';
    case 'pending':
      return 'badge--warning';
    case 'failed':
      return 'badge--danger';
    case 'refunded':
      return 'badge--secondary';
    default:
      return 'badge--secondary';
  }
};

const getCashfreeBadgeClass = (status) => {
  switch (status?.toUpperCase()) {
    case 'SUCCESS':
      return 'badge--success';
    case 'PENDING':
    case 'ACTIVE':
      return 'badge--warning';
    case 'FAILED':
    case 'USER_DROPPED':
    case 'CANCELLED':
      return 'badge--danger';
    default:
      return 'badge--secondary';
  }
};

onMounted(() => {
  fetchPayments(1);
  fetchSummary();
});
</script>

<style scoped>
.stats-grid-5 {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-lg);
  margin-bottom: var(--spacing-xl);
}

@media (max-width: 768px) {
  .stats-grid-5 {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 0.75rem !important;
  }
}
</style>
