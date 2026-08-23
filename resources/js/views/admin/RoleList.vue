<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title" style="font-family: 'Playfair Display', serif; color: #6E1F3A;">Access Control (RBAC)</h1>
      <span class="admin-page__subtitle" style="font-family: 'Poppins', sans-serif;">Manage security access tiers and configure menu-wise permissions (View, Create, Edit, Delete).</span>
    </div>
    <div class="admin-page__actions">
      <router-link 
        to="/admin/roles/create" 
        class="btn btn--primary"
        style="background: #6E1F3A; color: #ffffff; padding: 0.65rem 1.25rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(110, 31, 58, 0.2);"
      >
        ➕ Add New Role
      </router-link>
    </div>
  </div>

  <!-- Search Filter Bar -->
  <div class="glass-panel" style="padding: 14px 20px; margin-top: var(--spacing-md); margin-bottom: var(--spacing-lg); background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
    <div style="display: flex; gap: 14px; align-items: center;">
      <div style="flex: 1; position: relative;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search security roles by title or description..." 
          class="form-input" 
          style="padding-left: 2.25rem; width: 100%; font-family: 'Poppins', sans-serif;" 
        />
        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;">🔍</span>
      </div>
      <button 
        v-if="searchQuery" 
        @click="searchQuery = ''" 
        class="btn btn--secondary btn--sm" 
        style="padding: 8px 12px;"
      >
        Clear
      </button>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value" style="font-size: 1.2rem; color: #6E1F3A;">⏳ Loading defined roles...</div>
  </div>

  <!-- Roles Main Table Panel -->
  <div v-else class="glass-panel" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="role in filteredRoles" :key="role.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div>
              <div class="mdc-title" style="text-transform: uppercase;">
                {{ getRoleDisplay(role.name) }}
              </div>
              <div class="mdc-date" style="font-family: monospace; font-size: 0.72rem;">{{ role.name }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">{{ role.description || '—' }}</span>
          </div>
          <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between;">
            <span>Permissions: <strong>{{ role.permissions ? role.permissions.length : 0 }}</strong></span>
            <span>Staff Assigned: <strong>{{ role.users_count }}</strong></span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span v-if="role.name === 'super_admin'" class="badge badge--danger" style="font-size: 0.65rem;">
              👑 Super Admin
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <router-link 
              :to="`/admin/roles/${role.id}/edit`" 
              class="btn btn--secondary btn--sm"
              title="Edit Permissions"
              style="padding: 6px 10px; font-size: 0.85rem;"
            >
              ✏️
            </router-link>
            <button 
              class="btn btn--danger btn--sm" 
              :disabled="role.name === 'super_admin'" 
              @click="deleteRole(role.id)"
              :title="role.name === 'super_admin' ? 'Super Admin cannot be deleted' : 'Delete Role'"
              style="padding: 6px 10px; font-size: 0.85rem;"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table" style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr style="background: #FAF8F5; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">
          <th style="padding: 14px 20px; font-weight: 600; text-align: left; width: 25%;">Role Name & Key</th>
          <th style="padding: 14px 16px; font-weight: 600; text-align: left; width: 38%;">Description</th>
          <th style="padding: 14px 16px; font-weight: 600; text-align: center; width: 15%;">Active Permissions</th>
          <th style="padding: 14px 16px; font-weight: 600; text-align: center; width: 10%;">Assigned Staff</th>
          <th style="padding: 14px 20px; font-weight: 600; text-align: right; width: 12%;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr 
          v-for="role in filteredRoles" 
          :key="role.id"
          style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;"
          onmouseover="this.style.background='#FDFBF7'"
          onmouseout="this.style.background='transparent'"
        >
          <!-- Role Name & Key -->
          <td style="padding: 16px 20px;">
            <div style="display: flex; flex-direction: column; gap: 2px;">
              <span style="font-weight: 700; color: #6E1F3A; font-size: 0.95rem;">
                {{ getRoleDisplay(role.name) }}
              </span>
              <span style="font-family: monospace; font-size: 0.75rem; color: #94a3b8;">
                {{ role.name }}
              </span>
            </div>
          </td>

          <!-- Description -->
          <td style="padding: 16px 16px; color: #475569; font-size: 0.85rem; line-height: 1.4;">
            {{ role.description || '—' }}
          </td>

          <!-- Active Permissions Count Badge -->
          <td style="padding: 16px 16px; text-align: center;">
            <span 
              :class="[
                'badge', 
                role.name === 'super_admin' ? 'badge--danger' : (role.name === 'admin' ? 'badge--success' : 'badge--secondary')
              ]" 
              style="font-size: 0.82rem; padding: 4px 10px; font-weight: 600;"
            >
              {{ role.name === 'super_admin' ? '90 / 90 (All)' : `${role.permissions ? role.permissions.length : 0} CRUD` }}
            </span>
          </td>

          <!-- Assigned Staff Count -->
          <td style="padding: 16px 16px; text-align: center; font-weight: 600; color: #1e293b; font-size: 0.9rem;">
            {{ role.users_count }}
          </td>

          <!-- Action Buttons -->
          <td style="padding: 16px 20px; text-align: right;">
            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
              <router-link 
                :to="`/admin/roles/${role.id}/edit`" 
                class="btn btn--secondary btn--sm" 
                title="Edit Permissions"
                style="padding: 6px 10px; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center;"
              >
                ✏️
              </router-link>

              <button 
                class="btn btn--danger btn--sm" 
                :disabled="role.name === 'super_admin'"
                @click="deleteRole(role.id)"
                :title="role.name === 'super_admin' ? 'Super Admin cannot be deleted' : 'Delete Role'"
                style="padding: 6px 10px; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center;"
              >
                🗑️
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const roles = ref([]);
const loading = ref(true);
const searchQuery = ref('');

const fetchRoles = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/roles');
    if (response.data && response.data.success) {
      roles.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load roles:', err);
  } finally {
    loading.value = false;
  }
};

const getRoleDisplay = (name) => {
  if (name === 'super_admin') return '👑 Super Admin';
  if (name === 'admin') return '🛍️ Store Admin';
  return name.replace('_', ' ').toUpperCase();
};

const filteredRoles = computed(() => {
  if (!searchQuery.value.trim()) return roles.value;
  const q = searchQuery.value.toLowerCase();
  return roles.value.filter(r => 
    r.name.toLowerCase().includes(q) || 
    (r.description && r.description.toLowerCase().includes(q))
  );
});

const deleteRole = async (id) => {
  if (!confirm('Are you sure you want to delete this security role? Assigned staff members will lose permissions tied to this role.')) {
    return;
  }

  try {
    const response = await axios.delete(`/api/admin/roles/${id}`);
    if (response.data && response.data.success) {
      await fetchRoles();
    }
  } catch (err) {
    console.error('Failed to delete role:', err);
    alert(err.response?.data?.message || 'Failed to delete role');
  }
};

onMounted(() => {
  fetchRoles();
});
</script>
