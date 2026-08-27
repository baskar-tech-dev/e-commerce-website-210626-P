<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <router-link to="/admin/reports" class="btn btn--secondary btn--sm" style="padding: 4px 8px;" title="Back to Reports Hub">
          ← Reports Hub
        </router-link>
        <h1 class="admin-page__title">Cashfree Settlements Report</h1>
      </div>
      <span class="admin-page__subtitle">
        Track payout settlements, UTR bank transaction references, batch cycles, and reconciliation.
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
    <router-link v-if="canViewPayments" to="/admin/reports/payments" class="btn btn--secondary btn--sm" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
      💳 Cashfree Payments
    </router-link>
    <router-link to="/admin/reports/settlements" class="btn btn--primary btn--sm" style="border-bottom-left-radius: 0; border-bottom-right-radius: 0;">
      🏦 Payout Settlements
    </router-link>
  </div>

  <!-- Summary KPI Cards -->
  <div v-show="showStats" class="stats-grid-4" style="margin-bottom: var(--spacing-lg);">
    <!-- 1. Total Settled Amount -->
    <div class="stat-card-new" style="border-color: rgba(16, 185, 129, 0.3); background: linear-gradient(135deg, #ffffff, #f0fdf4);">
      <div class="stat-card__top">
        <div class="stat-card__title" style="color: #065f46; font-weight: 700;">Total Settled Amount</div>
        <div class="stat-card__icon-wrap stat-card__icon-wrap--green">₹</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: #047857;">
        ₹{{ formatCurrency(summaryData.total_settled_amount) }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-text-muted);">Total payout credited to merchant bank</span>
    </div>

    <!-- 2. Number of Settlements -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__title">Number of Settlements</div>
        <div class="stat-card__icon-wrap stat-card__icon-wrap--purple">🏦</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem; color: var(--color-primary);">
        {{ summaryData.settlements_count || 0 }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-text-muted);">Processed batch cycles</span>
    </div>

    <!-- 3. Latest Settlement Amount -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__title">Latest Settlement</div>
        <div class="stat-card__icon-wrap stat-card__icon-wrap--orange">💰</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.6rem; margin-top: 0.25rem;">
        ₹{{ formatCurrency(summaryData.latest_settlement) }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-text-muted);">Most recent payout batch</span>
    </div>

    <!-- 4. Latest Settlement Date -->
    <div class="stat-card-new">
      <div class="stat-card__top">
        <div class="stat-card__title">Latest Settlement Date</div>
        <div class="stat-card__icon-wrap" style="background: rgba(74, 14, 46, 0.1); color: var(--color-primary);">📅</div>
      </div>
      <div class="stat-card__value" style="font-size: 1.35rem; margin-top: 0.25rem; font-weight: 600; color: #1e293b;">
        {{ summaryData.latest_settlement_date || '—' }}
      </div>
      <span style="font-size: 0.75rem; color: var(--color-text-muted);">Last transfer timestamp</span>
    </div>
  </div>

  <!-- Filters Toolbar -->
  <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg);">
    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
      <!-- Search inputs (Settlement ID & UTR) -->
      <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; flex: 1; min-width: 320px;">
        <div style="position: relative; flex: 1; min-width: 180px;">
          <input 
            type="text" 
            v-model="filters.settlement_id" 
            @input="debounceSearch"
            placeholder="Search by Settlement ID..." 
            class="form-input" 
            style="padding-left: 2rem;" 
          />
          <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🔍</span>
        </div>

        <div style="position: relative; flex: 1; min-width: 180px;">
          <input 
            type="text" 
            v-model="filters.utr" 
            @input="debounceSearch"
            placeholder="Search by Bank UTR..." 
            class="form-input" 
            style="padding-left: 2rem;" 
          />
          <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🏛️</span>
        </div>
      </div>

      <div style="display: flex; gap: var(--spacing-sm);">
        <button class="btn btn--secondary btn--sm" @click="clearFilters">Clear Filters</button>
        <button class="btn btn--primary btn--sm" @click="fetchSettlements">Apply</button>
      </div>
    </div>

    <!-- Date Preset Buttons & Custom Date Pickers -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: var(--spacing-md); margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--color-border);">
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

      <!-- Custom Date Pickers -->
      <div v-if="filters.date_preset === 'custom'" style="display: flex; align-items: center; gap: var(--spacing-sm); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 4px;">
          <label style="font-size: 0.75rem; color: var(--color-text-muted);">From:</label>
          <input type="date" v-model="filters.start_date" class="form-input" style="padding: 0.2rem 0.5rem; width: 140px; font-size: 0.8rem;" />
        </div>
        <div style="display: flex; align-items: center; gap: 4px;">
          <label style="font-size: 0.75rem; color: var(--color-text-muted);">To:</label>
          <input type="date" v-model="filters.end_date" class="form-input" style="padding: 0.2rem 0.5rem; width: 140px; font-size: 0.8rem;" />
        </div>
        <button class="btn btn--secondary btn--sm" @click="fetchSettlements">Apply Dates</button>
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
    <div class="stat-card__value" style="font-size: 1.25rem;">Retrieving Cashfree Settlements...</div>
    <span style="font-size: 0.85rem; color: var(--color-text-muted);">Querying bank payout transactions</span>
  </div>

  <!-- Settlements Data List -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="(settlement, index) in settlements" :key="settlement.settlement_id || index">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--color-primary); background: rgba(74, 14, 46, 0.08); border: 1px solid rgba(74, 14, 46, 0.15); padding: 2px 6px; border-radius: 4px; flex-shrink: 0;">
              #{{ index + 1 }}
            </span>
            <span class="mdc-title" style="font-size: 0.95rem;">
              {{ settlement.settlement_id }}
            </span>
          </div>
          <span class="mdc-date">{{ formatDate(settlement.settlement_date) }}</span>
        </div>

        <div class="mdc-body">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
            <span style="font-size: 0.85rem; color: var(--color-text-muted);">Amount Settled:</span>
            <span style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary);">₹{{ formatCurrency(settlement.settlement_amount) }}</span>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.8rem; background: rgba(0,0,0,0.02); padding: 0.5rem; border-radius: 6px;">
            <div>
              <span style="color: var(--color-text-muted);">Bank UTR:</span><br />
              <code style="font-weight: 600;">{{ settlement.utr || '—' }}</code>
            </div>
            <div>
              <span style="color: var(--color-text-muted);">Type:</span><br />
              <span>{{ settlement.settlement_type }}</span>
            </div>
          </div>
        </div>

        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', getSettlementBadgeClass(settlement.settlement_status)]">
              {{ settlement.settlement_status }}
            </span>
            <span v-if="settlement.settlement_reference && settlement.settlement_reference !== '—'" style="font-size: 0.7rem; color: var(--color-text-muted);">
              Ref: {{ settlement.settlement_reference }}
            </span>
          </div>
        </div>
      </div>

      <div v-if="settlements.length === 0" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
        No settlement payouts recorded for the selected date range.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 50px; text-align: center;">S.No</th>
          <th>Settlement ID</th>
          <th>Settlement Date</th>
          <th style="text-align: right;">Settlement Amount</th>
          <th>Settlement Status</th>
          <th>UTR (Bank Reference)</th>
          <th>Settlement Type</th>
          <th>Settlement Reference</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(settlement, index) in settlements" :key="settlement.settlement_id || index">
          <td style="width: 50px; text-align: center; font-weight: 600; color: var(--color-text-secondary); font-size: 0.85rem;">
            {{ index + 1 }}
          </td>

          <!-- Settlement ID -->
          <td style="font-weight: 600; color: var(--color-primary);">
            <code style="font-size: 0.85rem; background: rgba(74, 14, 46, 0.05); padding: 2px 6px; border-radius: 4px; color: var(--color-primary);">
              {{ settlement.settlement_id }}
            </code>
          </td>

          <!-- Settlement Date -->
          <td style="font-size: 0.85rem; color: var(--color-text-secondary); white-space: nowrap;">
            {{ formatDate(settlement.settlement_date) }}
          </td>

          <!-- Settlement Amount -->
          <td style="text-align: right; font-weight: 700; color: #047857; font-size: 0.95rem;">
            ₹{{ formatCurrency(settlement.settlement_amount) }}
          </td>

          <!-- Settlement Status -->
          <td>
            <span :class="['badge', getSettlementBadgeClass(settlement.settlement_status)]" style="font-weight: 600; font-size: 0.75rem;">
              {{ settlement.settlement_status }}
            </span>
          </td>

          <!-- UTR -->
          <td>
            <code style="font-size: 0.82rem; font-weight: 600; color: #1e293b;">
              {{ settlement.utr || '—' }}
            </code>
          </td>

          <!-- Settlement Type -->
          <td>
            <span class="badge badge--secondary" style="font-size: 0.75rem;">
              {{ settlement.settlement_type || 'STANDARD' }}
            </span>
          </td>

          <!-- Settlement Reference -->
          <td style="font-size: 0.8rem; color: var(--color-text-muted);">
            <code>{{ settlement.settlement_reference || '—' }}</code>
          </td>
        </tr>

        <tr v-if="settlements.length === 0">
          <td colspan="8" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            No Cashfree settlement payouts found for the active filter range.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Summary Footer -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md) var(--spacing-lg); border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.01);">
      <div style="font-size: 0.85rem; color: var(--color-text-muted);">
        Displaying <strong>{{ settlements.length }}</strong> settlement records
      </div>

      <div style="display: flex; gap: var(--spacing-sm);">
        <button 
          v-if="pagination.cursor" 
          class="btn btn--secondary btn--sm" 
          @click="loadMore"
          :disabled="loading"
        >
          Load Next Batch ▶️
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
  if (isSuperAdmin.value) return true;
  const perms = userPermissions.value;
  return perms.includes(moduleName) ||
         perms.includes(`${moduleName}.view`) ||
         perms.includes('reports') ||
         perms.includes('manage_reports') ||
         perms.some(p => p.startsWith(`${moduleName}.`));
};

const canViewAnalytics = computed(() => hasPermission('reports') || hasPermission('reports_sales') || hasPermission('reports_inventory') || hasPermission('reports_customers'));
const canViewPayments = computed(() => hasPermission('payments'));

const showStats = ref(true);
const loading = ref(false);
const errorMsg = ref('');
const settlements = ref([]);

const summaryData = reactive({
  total_settled_amount: 0,
  settlements_count: 0,
  latest_settlement: 0,
  latest_settlement_date: '—',
});

const filters = reactive({
  settlement_id: '',
  utr: '',
  date_preset: 'today',
  start_date: '',
  end_date: '',
});

const datePresets = [
  { id: 'today', label: 'Today' },
  { id: 'last_7_days', label: 'Last 7 Days' },
  { id: 'last_30_days', label: 'Last 30 Days' },
  { id: 'custom', label: 'Custom Range' },
];

const pagination = reactive({
  cursor: null,
  limit: 20,
});

let debounceTimer = null;
const debounceSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchSettlements();
  }, 350);
};

const selectDatePreset = (presetId) => {
  filters.date_preset = presetId;
  if (presetId !== 'custom') {
    filters.start_date = '';
    filters.end_date = '';
    fetchSettlements();
    fetchSummary();
  }
};

const clearFilters = () => {
  filters.settlement_id = '';
  filters.utr = '';
  filters.date_preset = 'today';
  filters.start_date = '';
  filters.end_date = '';
  fetchSettlements();
  fetchSummary();
};

const fetchSettlements = async (cursor = null) => {
  loading.value = true;
  errorMsg.value = '';
  try {
    const params = {
      settlement_id: filters.settlement_id,
      utr: filters.utr,
      date_preset: filters.date_preset,
      start_date: filters.start_date,
      end_date: filters.end_date,
      limit: pagination.limit,
      cursor: cursor,
    };

    const response = await axios.get('/api/admin/reports/settlements', { params });
    if (response.data && response.data.success) {
      settlements.value = response.data.data;
      pagination.cursor = response.data.pagination?.cursor || null;
    }
  } catch (err) {
    console.error('Failed to load settlements report:', err);
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
    const response = await axios.get('/api/admin/reports/settlements/summary', { params });
    if (response.data && response.data.success) {
      Object.assign(summaryData, response.data.data);
    }
  } catch (err) {
    console.error('Failed to load settlements summary:', err);
  }
};

const loadMore = async () => {
  if (pagination.cursor) {
    await fetchSettlements(pagination.cursor);
  }
};

const refreshAll = async () => {
  await Promise.all([fetchSettlements(), fetchSummary()]);
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

const getSettlementBadgeClass = (status) => {
  switch (status?.toUpperCase()) {
    case 'SETTLED':
    case 'SUCCESS':
      return 'badge--success';
    case 'PENDING':
    case 'INITIATED':
      return 'badge--warning';
    case 'FAILED':
    case 'CANCELLED':
      return 'badge--danger';
    default:
      return 'badge--secondary';
  }
};

onMounted(() => {
  fetchSettlements();
  fetchSummary();
});
</script>
