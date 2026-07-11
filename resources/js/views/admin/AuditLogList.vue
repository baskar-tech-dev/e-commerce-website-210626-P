<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">System Audit Logs</h1>
      <span class="admin-page__subtitle">Track polymorphic data modifications, attribute changesets, client IPs, and administrator actions.</span>
    </div>
  </div>

  <!-- Search & Filter Bar -->
  <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg); margin-top: var(--spacing-md);">
    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
      <!-- Search Input -->
      <div style="flex: 1; min-width: 250px; position: relative;">
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search by user name, email, IP..." 
          class="form-input" 
          style="padding-left: 2rem;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🔍</span>
      </div>

      <!-- Filters Dropdowns -->
      <div style="display: flex; gap: var(--spacing-md); align-items: center; flex-wrap: wrap;">
        <!-- Model Target Type -->
        <div style="display: flex; gap: var(--spacing-xs); align-items: center;">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Model:</label>
          <select v-model="filters.target_type" @change="fetchLogs(1)" class="form-input" style="width: 140px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Models</option>
            <option value="Product">Product</option>
            <option value="Order">Order</option>
          </select>
        </div>

        <!-- Action Type -->
        <div style="display: flex; gap: var(--spacing-xs); align-items: center;">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Action:</label>
          <select v-model="filters.action" @change="fetchLogs(1)" class="form-input" style="width: 140px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Actions</option>
            <option value="create">Create</option>
            <option value="update">Update</option>
            <option value="delete">Delete</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Loading system audit logs...</div>
  </div>

  <!-- Audit Logs Grid Table -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="log in logs" :key="log.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div>
              <div class="mdc-title">{{ simplifyModelName(log.auditable_type) }} <code>#{{ log.auditable_id }}</code></div>
              <div class="mdc-date">{{ formatDate(log.created_at) }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">
              By: 
              <span v-if="log.user">{{ log.user.first_name }} {{ log.user.last_name }}</span>
              <span v-else style="color: var(--color-text-muted); font-style: italic;">System / Guest</span>
            </span>
            <span class="mdc-email">IP: {{ log.ip_address || '—' }}</span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', getActionBadgeClass(log.action)]" style="font-weight: bold; text-transform: uppercase;">
              {{ log.action }}
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn--secondary btn--sm" @click="openDiffModal(log)">🔍 Details</button>
          </div>
        </div>
      </div>
      
      <div v-if="logs.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No audit logs match the selected filters.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th>Date & Time</th>
          <th>User / Operator</th>
          <th>Action</th>
          <th>Target Entity</th>
          <th>Target ID</th>
          <th>IP Address</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="log in logs" :key="log.id">
          <td style="font-weight: 500;">{{ formatDate(log.created_at) }}</td>
          <td>
            <div v-if="log.user">
              <span style="font-weight: bold; display: block;">{{ log.user.first_name }} {{ log.user.last_name }}</span>
              <span style="font-size: 0.75rem; color: var(--color-text-muted);">{{ log.user.email }}</span>
            </div>
            <span v-else style="color: var(--color-text-muted); font-style: italic;">System / Guest</span>
          </td>
          <td>
            <span :class="['badge', getActionBadgeClass(log.action)]" style="font-weight: bold; text-transform: uppercase;">
              {{ log.action }}
            </span>
          </td>
          <td>
            <span style="font-weight: 600; color: #1e293b;">{{ simplifyModelName(log.auditable_type) }}</span>
          </td>
          <td><code>#{{ log.auditable_id }}</code></td>
          <td>{{ log.ip_address || '—' }}</td>
          <td style="text-align: right;">
            <button class="btn btn--secondary btn--sm" @click="openDiffModal(log)">
              🔍 View Details
            </button>
          </td>
        </tr>
        <tr v-if="logs.length === 0">
          <td colspan="7" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            No audit logs match the selected filters.
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
          @click="fetchLogs(pagination.current_page - 1)"
        >
          Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchLogs(pagination.current_page + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>

  <!-- Comparative Diff Modal -->
  <div v-if="activeLog" class="modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999;">
    <div class="glass-panel" style="width: 90%; max-width: 900px; padding: var(--spacing-lg); max-height: 85vh; overflow-y: auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: var(--spacing-md); margin-bottom: var(--spacing-md);">
        <div>
          <h3 style="color: #1e293b; margin: 0; font-size: 1.25rem;">Audit Log Details</h3>
          <span style="font-size: 0.8rem; color: var(--color-text-muted);">
            Target: {{ simplifyModelName(activeLog.auditable_type) }} #{{ activeLog.auditable_id }} | Action: {{ activeLog.action.toUpperCase() }}
          </span>
        </div>
        <button @click="closeDiffModal" style="background: none; border: none; color: #1e293b; font-size: 1.5rem; cursor: pointer;">✕</button>
      </div>

      <!-- Context info -->
      <div class="responsive-grid-1-1" style="gap: var(--spacing-md); margin-bottom: var(--spacing-lg); background: rgba(255,255,255,0.02); padding: var(--spacing-sm); border-radius: 6px; border: 1px solid var(--color-border); font-size: 0.85rem;">
        <div>
          <strong>Operator:</strong> {{ activeLog.user ? `${activeLog.user.first_name} ${activeLog.user.last_name} (${activeLog.user.email})` : 'System / Guest' }}
        </div>
        <div>
          <strong>IP Address:</strong> {{ activeLog.ip_address || '—' }}
        </div>
        <div>
          <strong>Date Recorded:</strong> {{ formatDate(activeLog.created_at) }}
        </div>
        <div>
          <strong>Browser User Agent:</strong> {{ activeLog.user_agent || '—' }}
        </div>
      </div>

      <!-- JSON Diff Section -->
      <div class="responsive-grid-1-1" style="gap: var(--spacing-md);">
        <!-- Old Values -->
        <div>
          <h4 style="color: var(--color-text-muted); margin-top: 0; font-size: 0.9rem; margin-bottom: 0.5rem;">Old / Original Values</h4>
          <pre style="background: rgba(0,0,0,0.3); border: 1px solid var(--color-border); border-radius: 6px; padding: var(--spacing-md); font-family: monospace; font-size: 0.8rem; overflow-x: auto; color: #fca5a5; max-height: 250px;">{{ activeLog.old_values ? JSON.stringify(activeLog.old_values, null, 2) : '—' }}</pre>
        </div>

        <!-- New Values -->
        <div>
          <h4 style="color: var(--color-text-muted); margin-top: 0; font-size: 0.9rem; margin-bottom: 0.5rem;">New / Changed Values</h4>
          <pre style="background: rgba(0,0,0,0.3); border: 1px solid var(--color-border); border-radius: 6px; padding: var(--spacing-md); font-family: monospace; font-size: 0.8rem; overflow-x: auto; color: #86efac; max-height: 250px;">{{ activeLog.new_values ? JSON.stringify(activeLog.new_values, null, 2) : '—' }}</pre>
        </div>
      </div>

      <div style="margin-top: var(--spacing-lg); border-top: 1px solid var(--color-border); padding-top: var(--spacing-md); text-align: right;">
        <button class="btn btn--secondary" @click="closeDiffModal">Close Viewer</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const logs = ref([]);
const loading = ref(true);

const filters = ref({
  search: '',
  target_type: '',
  action: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const activeLog = ref(null);
let searchTimeout = null;

const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchLogs(1);
  }, 350);
};

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/audit-logs', {
      params: {
        page,
        search: filters.value.search,
        target_type: filters.value.target_type,
        action: filters.value.action,
      }
    });

    if (response.data && response.data.success) {
      logs.value = response.data.data;
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        total: response.data.meta.total,
      };
    }
  } catch (err) {
    console.error('Failed to query system audit logs:', err);
  } finally {
    loading.value = false;
  }
};

const simplifyModelName = (fullname) => {
  if (!fullname) return '—';
  const parts = fullname.split('\\');
  return parts[parts.length - 1];
};

const getActionBadgeClass = (action) => {
  switch (action) {
    case 'create': return 'badge--success';
    case 'update': return 'badge--warning';
    case 'delete': return 'badge--danger';
    default: return 'badge--secondary';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
};

const openDiffModal = (log) => {
  activeLog.value = log;
};

const closeDiffModal = () => {
  activeLog.value = null;
};

onMounted(() => {
  fetchLogs();
});
</script>
