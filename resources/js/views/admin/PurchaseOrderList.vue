<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Purchase Orders</h1>
      <span class="admin-page__subtitle">Replenish catalog stock, create drafts, and receive batch supplies.</span>
    </div>
    <router-link to="/admin/purchase-orders/create" class="btn btn--primary" style="text-decoration: none;">
      <span>➕</span> Create PO
    </router-link>
  </div>

  <!-- Filters panel -->
  <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Supplier Name</label>
        <input 
          type="text" 
          v-model="filters.supplier_name" 
          class="form-input" 
          placeholder="Filter supplier..." 
          @input="debouncedSearch" 
        />
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Status</label>
        <select v-model="filters.status" class="form-input" @change="applyFilters">
          <option value="">All Statuses</option>
          <option value="draft">Drafts</option>
          <option value="ordered">Ordered</option>
          <option value="received">Fully Received</option>
          <option value="partial">Partially Received</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Created From</label>
        <input type="date" v-model="filters.date_from" class="form-input" @change="applyFilters" />
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Created To</label>
        <input type="date" v-model="filters.date_to" class="form-input" @change="applyFilters" />
      </div>
    </div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- PO Table -->
  <div class="glass-panel" style="overflow: hidden;">
    <table class="data-table">
      <thead>
        <tr>
          <th>PO Number</th>
          <th>Supplier Name</th>
          <th>Created By</th>
          <th>Date Created</th>
          <th>Total Value</th>
          <th>Status</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="po in purchaseOrders" :key="po.id">
          <td><strong style="color: #fff;">{{ po.po_number }}</strong></td>
          <td>
            <div>{{ po.supplier_name }}</div>
            <div style="font-size: 0.8rem; color: var(--color-text-muted);">{{ po.supplier_contact || 'No Contact' }}</div>
          </td>
          <td>{{ po.creator?.name || 'Operator' }}</td>
          <td style="font-size: 0.85rem; color: var(--color-text-secondary);">{{ formatDate(po.created_at) }}</td>
          <td style="font-weight: 600; color: #fff;">₹{{ parseFloat(po.total_amount).toFixed(2) }}</td>
          <td>
            <span :class="['badge', getPOStatusClass(po.status)]">
              {{ getPOStatusLabel(po.status) }}
            </span>
          </td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <router-link 
                v-if="po.status === 'draft'"
                :to="`/admin/purchase-orders/${po.id}/edit`" 
                class="btn btn--secondary btn--sm" 
                style="text-decoration: none;"
              >
                ✏️ Edit
              </router-link>
              <router-link 
                v-else
                :to="`/admin/purchase-orders/${po.id}/edit`" 
                class="btn btn--secondary btn--sm" 
                style="text-decoration: none;"
              >
                👁️ View
              </router-link>

              <router-link 
                v-if="po.status === 'ordered' || po.status === 'partial'"
                :to="`/admin/purchase-orders/${po.id}/receive`" 
                class="btn btn--primary btn--sm" 
                style="text-decoration: none;"
              >
                📥 Receive
              </router-link>

              <button 
                v-if="po.status === 'draft'"
                class="btn btn--danger btn--sm" 
                @click="deletePO(po.id)"
              >
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="purchaseOrders.length === 0 && !poStore.loading">
          <td colspan="7" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            No purchase orders found matching filters.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem var(--spacing-lg); border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.1);">
      <div style="font-size: 0.85rem; color: var(--color-text-muted);">
        Showing PO <strong>{{ pagination.current_page }}</strong> of <strong>{{ pagination.last_page }}</strong> (Total: {{ pagination.total }} POs)
      </div>
      <div style="display: flex; gap: 0.5rem;">
        <button class="btn btn--secondary btn--sm" :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)">
          ◀️ Prev
        </button>
        <button class="btn btn--secondary btn--sm" :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)">
          Next ▶️
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePurchaseOrderStore } from '../../stores/purchaseOrder';

const poStore = usePurchaseOrderStore();

const errorMsg = ref(null);

const filters = ref({
  supplier_name: '',
  status: '',
  date_from: '',
  date_to: '',
  page: 1,
});

const purchaseOrders = computed(() => poStore.purchaseOrders);
const pagination = computed(() => poStore.pagination);

let searchTimeout = null;
function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    filters.value.page = 1;
    applyFilters();
  }, 400);
}

function applyFilters() {
  poStore.fetchPurchaseOrders(filters.value);
}

function changePage(page) {
  filters.value.page = page;
  applyFilters();
}

function formatDate(dtStr) {
  if (!dtStr) return '';
  const date = new Date(dtStr);
  return date.toLocaleDateString();
}

function getPOStatusClass(status) {
  switch (status) {
    case 'draft': return 'badge--secondary';
    case 'ordered': return 'badge--primary';
    case 'partial': return 'badge--warning';
    case 'received': return 'badge--success';
    case 'cancelled': return 'badge--danger';
    default: return 'badge--secondary';
  }
}

function getPOStatusLabel(status) {
  switch (status) {
    case 'draft': return 'Draft';
    case 'ordered': return 'Ordered';
    case 'partial': return 'Partial Received';
    case 'received': return 'Fully Received';
    case 'cancelled': return 'Cancelled';
    default: return status;
  }
}

async function deletePO(id) {
  if (confirm('Are you sure you want to delete this draft purchase order?')) {
    errorMsg.value = null;
    try {
      await poStore.deletePurchaseOrder(id);
      applyFilters();
    } catch (err) {
      errorMsg.value = err.message || 'Failed to delete PO';
    }
  }
}

onMounted(() => {
  applyFilters();
});
</script>
