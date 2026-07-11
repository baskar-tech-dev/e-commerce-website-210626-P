<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Customer Directory</h1>
      <span class="admin-page__subtitle">View customer profiles, update accounts, and manage addresses.</span>
    </div>
  </div>

  <!-- Filters Panel -->
  <div class="glass-panel" style="padding: 1.25rem; margin-bottom: 1.5rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
      <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
        <label class="form-label">Search</label>
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          class="form-input" 
          placeholder="Search by name, email, phone..." 
        />
      </div>

      <div class="form-group" style="width: 150px; margin-bottom: 0;">
        <label class="form-label">Status</label>
        <select v-model="filters.status" @change="applyFilters" class="form-select">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>

      <div class="form-group" style="width: 180px; margin-bottom: 0;">
        <label class="form-label">Sort By</label>
        <select v-model="filters.sort_by" @change="applyFilters" class="form-select">
          <option value="created_at">Joined Date</option>
          <option value="total_orders">Orders Count</option>
          <option value="total_spent">Total Spent</option>
        </select>
      </div>

      <button class="btn btn--secondary" @click="resetFilters">
        Reset
      </button>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="customerStore.loading && customers.length === 0" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading Customers...</div>
  </div>

  <!-- Customers List Grid -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="customer in customers" :key="customer.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1px solid rgba(255, 255, 255, 0.15); overflow: hidden; flex-shrink: 0;">
              <img v-if="customer.avatar" :src="customer.avatar" style="width: 100%; height: 100%; object-fit: cover;" alt="" />
              <span v-else>👤</span>
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
          <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between;">
            <span>Orders: <strong>{{ customer.total_orders }}</strong></span>
            <span>Spent: <strong style="color: #1e293b;">₹{{ parseFloat(customer.total_spent).toFixed(2) }}</strong></span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', customer.is_active ? 'badge--success' : 'badge--danger']">
              {{ customer.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <router-link :to="{ name: 'admin.customers.show', params: { id: customer.id } }" class="btn btn--secondary btn--sm">View Profile</router-link>
          </div>
        </div>
      </div>
      
      <div v-if="customers.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No customers found matching the criteria.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Contact</th>
          <th>Total Orders</th>
          <th>Total Spent</th>
          <th>Status</th>
          <th>Joined</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="customer in customers" :key="customer.id">
          <td>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
              <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1px solid rgba(255, 255, 255, 0.15); overflow: hidden;">
                <img v-if="customer.avatar" :src="customer.avatar" style="width: 100%; height: 100%; object-fit: cover;" alt="" />
                <span v-else>👤</span>
              </div>
              <div>
                <div style="font-weight: 600; color: #1e293b;">{{ customer.name }}</div>
                <div style="font-size: 0.8rem; color: var(--color-text-muted);">ID: {{ customer.id }}</div>
              </div>
            </div>
          </td>
          <td>
            <div style="color: #1e293b; font-size: 0.9rem;">{{ customer.email }}</div>
            <div v-if="customer.phone" style="font-size: 0.8rem; color: var(--color-text-muted);">{{ customer.phone }}</div>
            <div v-else style="font-size: 0.8rem; color: var(--color-text-muted); font-style: italic;">No phone</div>
          </td>
          <td>{{ customer.total_orders }}</td>
          <td style="font-weight: 500; color: #1e293b;">₹{{ parseFloat(customer.total_spent).toFixed(2) }}</td>
          <td>
            <span :class="['badge', customer.is_active ? 'badge--success' : 'badge--danger']">
              {{ customer.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td style="font-size: 0.9rem;">{{ formatDate(customer.created_at) }}</td>
          <td style="text-align: right;">
            <router-link :to="{ name: 'admin.customers.show', params: { id: customer.id } }" class="btn btn--secondary btn--sm">
              👁️ View Profile
            </router-link>
          </td>
        </tr>
        <tr v-if="customers.length === 0">
          <td colspan="7" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No customers found matching the criteria.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Footer -->
    <div v-if="pagination.last_page > 1" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem; background: rgba(255,255,255,0.02); border-top: 1px solid rgba(255,255,255,0.05);">
      <span style="font-size: 0.85rem; color: var(--color-text-muted);">
        Showing page {{ pagination.current_page }} of {{ pagination.last_page }}
      </span>
      <div style="display: flex; gap: 0.5rem;">
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === 1" 
          @click="changePage(pagination.current_page - 1)"
        >
          Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page" 
          @click="changePage(pagination.current_page + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useCustomerStore } from '../../stores/customer';

const customerStore = useCustomerStore();
const customers = computed(() => customerStore.customers);
const pagination = computed(() => customerStore.pagination);

const filters = ref({
  search: '',
  status: '',
  sort_by: 'created_at',
  page: 1,
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
  fetchData();
}

function resetFilters() {
  filters.value = {
    search: '',
    status: '',
    sort_by: 'created_at',
    page: 1,
  };
  fetchData();
}

function changePage(page) {
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
</script>
