<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Access Control (RBAC)</h1>
      <span class="admin-page__subtitle">Configure security roles and manage granular permissions assigned to back-office staff.</span>
    </div>
  </div>

  <div class="responsive-grid-3-2" style="gap: var(--spacing-lg); margin-top: var(--spacing-md);">
    <!-- Left Panel: Defined Roles list -->
    <div class="glass-panel" style="overflow: hidden; padding: var(--spacing-md);">
      <div style="padding: 0.5rem 0.5rem 1rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-md);">
        <div class="card-header-title">Defined Security Roles</div>
        <button class="btn btn--primary btn--sm" @click="initNewRole">➕ Add New Role</button>
      </div>

      <div v-if="loading" style="text-align: center; padding: 3rem;">
        <div class="stat-card__value" style="font-size: 1.2rem;">Loading role parameters...</div>
      </div>

      <div v-else>
        <!-- Mobile Cards View -->
        <div class="mobile-data-list">
          <div class="mobile-data-card" v-for="role in roles" :key="role.id" :class="{'table-row--selected': selectedRole?.id === role.id}">
            <div class="mdc-header">
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div>
                  <div class="mdc-title" style="text-transform: uppercase;">{{ role.name.replace('_', ' ') }}</div>
                </div>
              </div>
            </div>
            
            <div class="mdc-body">
              <div class="mdc-customer">
                <span class="mdc-name">{{ role.description || '—' }}</span>
              </div>
              <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between;">
                <span>Permissions: <strong>{{ role.permissions ? role.permissions.length : 0 }}</strong></span>
                <span>Staff: <strong>{{ role.users_count }}</strong></span>
              </div>
            </div>
            
            <div class="mdc-footer">
              <div class="mdc-badges">
              </div>
              <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn--secondary btn--sm" @click="selectRoleForEdit(role)">⚙️ Edit</button>
                <button class="btn btn--danger btn--sm" :disabled="role.name === 'super_admin'" @click="deleteRole(role.id)">🗑️ Delete</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table View -->
        <table class="data-table desktop-data-table">
          <thead>
            <tr>
              <th>Role Name</th>
              <th>Description</th>
              <th style="text-align: right;">Permissions</th>
              <th style="text-align: right;">Assigned Users</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in roles" :key="role.id" :class="{'table-row--selected': selectedRole?.id === role.id}">
              <td style="font-weight: bold; color: var(--color-primary); text-transform: uppercase;">
                {{ role.name.replace('_', ' ') }}
              </td>
              <td>{{ role.description || '—' }}</td>
              <td style="text-align: right; font-weight: bold; color: #1e293b;">
                {{ role.permissions ? role.permissions.length : 0 }}
              </td>
              <td style="text-align: right;">
                {{ role.users_count }} staff
              </td>
              <td style="text-align: right;">
                <div style="display: flex; gap: var(--spacing-xs); justify-content: flex-end;">
                  <button class="btn btn--secondary btn--sm" @click="selectRoleForEdit(role)">
                    ⚙️ Edit Permissions
                  </button>
                  <button 
                    class="btn btn--danger btn--sm" 
                    :disabled="role.name === 'super_admin'"
                    @click="deleteRole(role.id)"
                  >
                    🗑️ Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Right Panel: Edit Permissions panel -->
    <div class="glass-panel" style="padding: var(--spacing-lg);">
      <div v-if="!selectedRole && !isCreating" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
        <span style="font-size: 3rem; display: block; margin-bottom: var(--spacing-md);">🔑</span>
        Select a role or add a new one to manage granular permissions logs.
      </div>

      <div v-else>
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">
          {{ isCreating ? 'Create New Security Role' : `Edit Permissions for: ${selectedRole.name.replace('_', ' ').toUpperCase()}` }}
        </div>

        <form @submit.prevent="saveRole">
          <!-- Role Name -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Role Name *</label>
            <input 
              type="text" 
              v-model="roleForm.name" 
              placeholder="e.g. sales_manager" 
              class="form-input" 
              :disabled="!isCreating && selectedRole?.name === 'super_admin'"
              required 
            />
          </div>

          <!-- Description -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Description</label>
            <input 
              type="text" 
              v-model="roleForm.description" 
              placeholder="Describe access privileges..." 
              class="form-input" 
            />
          </div>

          <!-- Permissions Checkbox lists -->
          <div class="form-group" style="margin-bottom: var(--spacing-lg);">
            <label class="form-label" style="margin-bottom: 0.5rem;">Configure Access Permissions</label>
            
            <div 
              v-if="!isCreating && selectedRole?.name === 'super_admin'" 
              style="color: var(--color-success); font-size: 0.85rem; font-weight: 500;"
            >
              ⚠️ Super Admins always possess all permissions. Checkboxes are locked.
            </div>

            <div v-else style="display: flex; flex-direction: column; gap: var(--spacing-xs); background: rgba(0,0,0,0.1); border-radius: 6px; padding: var(--spacing-sm); border: 1px solid var(--color-border);">
              <label 
                v-for="p in allPermissions" 
                :key="p.id" 
                style="display: flex; align-items: flex-start; gap: var(--spacing-xs); color: #1e293b; font-size: 0.85rem; padding: 0.25rem 0; cursor: pointer;"
              >
                <input 
                  type="checkbox" 
                  :value="p.id" 
                  v-model="roleForm.permissions" 
                  style="margin-top: 3px; cursor: pointer;" 
                />
                <div>
                  <div style="font-weight: bold;">{{ p.name.replace('_', ' ').toUpperCase() }}</div>
                  <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ p.description }}</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Actions -->
          <div style="display: flex; gap: var(--spacing-sm);">
            <button 
              type="submit" 
              class="btn btn--primary" 
              style="flex: 1;"
              :disabled="saving || (!isCreating && selectedRole?.name === 'super_admin')"
            >
              {{ saving ? 'Saving Changes...' : '💾 Save Role' }}
            </button>
            <button 
              type="button" 
              class="btn btn--secondary" 
              style="flex: 1;"
              @click="cancelEdit"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const roles = ref([]);
const allPermissions = ref([]);
const loading = ref(true);
const saving = ref(false);

const selectedRole = ref(null);
const isCreating = ref(false);

const roleForm = ref({
  name: '',
  description: '',
  permissions: [],
});

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

const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/admin/permissions');
    if (response.data && response.data.success) {
      allPermissions.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load system permissions:', err);
  }
};

const initNewRole = () => {
  selectedRole.value = null;
  isCreating.value = true;
  roleForm.value = {
    name: '',
    description: '',
    permissions: [],
  };
};

const selectRoleForEdit = (role) => {
  isCreating.value = false;
  selectedRole.value = role;
  roleForm.value = {
    name: role.name,
    description: role.description || '',
    permissions: role.permissions ? role.permissions.map(p => p.id) : [],
  };
};

const cancelEdit = () => {
  selectedRole.value = null;
  isCreating.value = false;
};

const saveRole = async () => {
  saving.value = true;
  try {
    const payload = { ...roleForm.value };
    payload.name = payload.name.trim().toLowerCase().replace(/[^a-z0-9_]+/g, '_');
    
    let response;
    if (isCreating.value) {
      response = await axios.post('/api/admin/roles', payload);
    } else {
      response = await axios.put(`/api/admin/roles/${selectedRole.value.id}`, payload);
    }

    if (response.data && response.data.success) {
      selectedRole.value = null;
      isCreating.value = false;
      await fetchRoles();
    }
  } catch (err) {
    console.error('Failed to save role:', err);
    alert(err.response?.data?.message || 'Error occurred while saving role configuration');
  } finally {
    saving.value = false;
  }
};

const deleteRole = async (id) => {
  if (!confirm('Are you sure you want to delete this security role? Assigned staff users will lose this role access.')) {
    return;
  }

  try {
    const response = await axios.delete(`/api/admin/roles/${id}`);
    if (response.data && response.data.success) {
      selectedRole.value = null;
      isCreating.value = false;
      await fetchRoles();
    }
  } catch (err) {
    console.error('Failed to delete role:', err);
    alert(err.response?.data?.message || 'Failed to delete role');
  }
};

onMounted(() => {
  fetchRoles();
  fetchPermissions();
});
</script>

<style scoped>
.table-row--selected {
  background: rgba(var(--color-primary-rgb), 0.1) !important;
  border-left: 3px solid var(--color-primary);
}
</style>
