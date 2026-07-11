<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Staff & Users Directory</h1>
      <span class="admin-page__subtitle">Manage back-office administrative staff profiles and assign security access roles.</span>
    </div>
    <div class="admin-page__actions">
      <router-link to="/admin/users/create" class="btn btn--primary">
        ➕ Add Staff User
      </router-link>
    </div>
  </div>

  <!-- Search & Filter Bar -->
  <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg); margin-top: var(--spacing-md);">
    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
      <!-- Search Input -->
      <div style="flex: 1; min-width: 280px; position: relative;">
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search staff by name, email or phone..." 
          class="form-input" 
          style="padding-left: 2rem;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🔍</span>
      </div>

      <!-- Filters Dropdowns -->
      <div style="display: flex; gap: var(--spacing-md); align-items: center;">
        <label style="font-size: 0.8rem; color: var(--color-text-muted);">Access Role:</label>
        <select v-model="filters.role_id" @change="fetchUsers(1)" class="form-input" style="width: 180px; padding: 0.25rem var(--spacing-md);">
          <option value="">All Security Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name.replace('_', ' ').toUpperCase() }}
          </option>
        </select>
      </div>
    </div>
  </div>

  <!-- Error Alerts -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Loading staff users directory...</div>
  </div>

  <!-- Grid Table of Staff Users -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="user in users" :key="user.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: bold; flex-shrink: 0;">
              {{ user.first_name ? user.first_name.charAt(0).toUpperCase() : 'U' }}{{ user.last_name ? user.last_name.charAt(0).toUpperCase() : '' }}
            </div>
            <div>
              <router-link :to="`/admin/users/${user.id}/edit`" class="mdc-title">
                {{ user.first_name }} {{ user.last_name }}
              </router-link>
              <div class="mdc-date">{{ user.email }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">Phone: {{ user.phone || '—' }}</span>
          </div>
          
          <div class="mdc-totals" style="margin-top: 0.5rem;">
            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
              <span v-for="role in user.roles" :key="role.id" class="badge badge--secondary" style="font-size: 0.65rem; text-transform: uppercase;">
                {{ role.name.replace('_', ' ') }}
              </span>
              <span v-if="user.roles?.length === 0" style="font-size: 0.8rem; color: var(--color-text-muted);">
                No roles
              </span>
            </div>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', user.is_active ? 'badge--success' : 'badge--danger']">
              {{ user.is_active ? 'ACTIVE' : 'INACTIVE' }}
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <router-link :to="`/admin/users/${user.id}/edit`" class="btn btn--secondary btn--sm">Edit</router-link>
          </div>
        </div>
      </div>
      
      <div v-if="users.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No matching staff users found.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 50px;">Avatar</th>
          <th>Full Name</th>
          <th>Email Address</th>
          <th>Phone Number</th>
          <th>Assigned Roles</th>
          <th>Status</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>
            <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: bold;">
              {{ user.first_name ? user.first_name.charAt(0).toUpperCase() : 'U' }}{{ user.last_name ? user.last_name.charAt(0).toUpperCase() : '' }}
            </div>
          </td>
          <td>
            <router-link :to="`/admin/users/${user.id}/edit`" style="color: var(--color-primary); text-decoration: none; font-weight: bold; font-size: 0.95rem;">
              {{ user.first_name }} {{ user.last_name }}
            </router-link>
          </td>
          <td>{{ user.email }}</td>
          <td>{{ user.phone || '—' }}</td>
          <td>
            <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
              <span 
                v-for="role in user.roles" 
                :key="role.id" 
                class="badge badge--secondary" 
                style="font-size: 0.7rem; text-transform: uppercase;"
              >
                {{ role.name.replace('_', ' ') }}
              </span>
              <span v-if="user.roles?.length === 0" style="font-size: 0.8rem; color: var(--color-text-muted);">
                No roles
              </span>
            </div>
          </td>
          <td>
            <span :class="['badge', user.is_active ? 'badge--success' : 'badge--danger']">
              {{ user.is_active ? 'ACTIVE' : 'INACTIVE' }}
            </span>
          </td>
          <td style="text-align: right;">
            <div style="display: flex; gap: var(--spacing-xs); justify-content: flex-end;">
              <router-link :to="`/admin/users/${user.id}/edit`" class="btn btn--secondary btn--sm">
                ✏️ Edit
              </router-link>
              <button 
                class="btn btn--danger btn--sm" 
                :disabled="isSuperAdmin(user)"
                @click="deleteUser(user.id)"
              >
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="users.length === 0">
          <td colspan="7" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            No administrative staff members matched the current filters.
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
          @click="fetchUsers(pagination.current_page - 1)"
        >
          Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchUsers(pagination.current_page + 1)"
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

const users = ref([]);
const roles = ref([]);
const loading = ref(true);
const errorMsg = ref('');

const filters = ref({
  search: '',
  role_id: '',
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
    fetchUsers(1);
  }, 350);
};

const fetchUsers = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/users', {
      params: {
        page,
        search: filters.value.search,
        role_id: filters.value.role_id,
      }
    });

    if (response.data && response.data.success) {
      users.value = response.data.data;
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        total: response.data.meta.total,
      };
    }
  } catch (err) {
    console.error('Failed to fetch staff users:', err);
    errorMsg.value = 'Failed to load staff users directory';
  } finally {
    loading.value = false;
  }
};

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/admin/roles');
    if (response.data && response.data.success) {
      roles.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to fetch roles:', err);
  }
};

const isSuperAdmin = (user) => {
  return user.roles && user.roles.some(r => r.name === 'super_admin');
};

const deleteUser = async (id) => {
  if (!confirm('Are you sure you want to remove this staff user? This is final.')) {
    return;
  }

  try {
    const response = await axios.delete(`/api/admin/users/${id}`);
    if (response.data && response.data.success) {
      await fetchUsers(pagination.value.current_page);
    }
  } catch (err) {
    console.error('Failed to delete staff user:', err);
    alert(err.response?.data?.message || 'Failed to delete staff user');
  }
};

onMounted(() => {
  fetchUsers();
  fetchRoles();
});
</script>
