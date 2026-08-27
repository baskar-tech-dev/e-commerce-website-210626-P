<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <router-link 
        to="/admin/roles" 
        style="text-decoration: none; color: #6E1F3A; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.5rem; transition: color 0.15s ease;"
      >
        ◀ Back to Roles Directory
      </router-link>
      <h1 class="admin-page__title" style="font-family: 'Playfair Display', serif; color: #6E1F3A;">
        {{ isEdit ? (isSuperAdmin ? '👑 Super Admin Permissions' : `⚙️ Edit Role: ${form.name.replace('_', ' ').toUpperCase()}`) : '➕ Create New Security Role' }}
      </h1>
      <span class="admin-page__subtitle" style="font-family: 'Poppins', sans-serif;">
        {{ isEdit ? 'Configure granular menu-wise permissions (View, Create, Edit, Delete) for this role.' : 'Define a new security role and assign granular menu-wise access control.' }}
      </span>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value" style="font-size: 1.2rem; color: #6E1F3A;">⏳ Loading role & permission matrix...</div>
  </div>

  <div v-else style="display: flex; flex-direction: column; gap: 24px;">
    <!-- Error Alerts -->
    <div v-if="errorMsg" class="badge badge--danger" style="padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; width: 100%;">
      ⚠️ {{ errorMsg }}
    </div>

    <form @submit.prevent="saveRole" style="display: flex; flex-direction: column; gap: 24px;">
      <!-- 1. Role Basic Information Card -->
      <div class="glass-panel" style="padding: 24px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 18px;">
          <div style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #6E1F3A;">
            Role Details
          </div>
          <span v-if="isSuperAdmin" class="badge badge--danger" style="font-size: 0.75rem; padding: 4px 10px;">
            👑 System Super Admin (Locked)
          </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
          <!-- Role Name Key -->
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">
              Role Key / Identifier *
            </label>
            <input 
              type="text" 
              v-model="form.name" 
              placeholder="e.g. catalog_manager, finance_admin, orders_staff" 
              class="form-input" 
              :disabled="isSuperAdmin"
              required 
              style="width: 100%; font-family: 'Poppins', sans-serif;"
            />
            <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">
              Unique internal identifier (lowercase letters and underscores).
            </span>
          </div>

          <!-- Description -->
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">
              Role Description
            </label>
            <input 
              type="text" 
              v-model="form.description" 
              placeholder="e.g. Full operational access to Orders, Returns, and Product catalog." 
              class="form-input" 
              style="width: 100%; font-family: 'Poppins', sans-serif;"
            />
            <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">
              Human-readable summary of permissions assigned to this staff tier.
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Menu-Wise Granular Permissions Matrix Card -->
      <div class="glass-panel" style="padding: 24px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 18px; gap: 12px;">
          <div>
            <div style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #6E1F3A;">
              Menu-Wise Access Control Matrix
            </div>
            <span style="font-size: 0.8rem; color: #64748b;">
              Configure specific <strong>View</strong>, <strong>Create</strong>, <strong>Edit</strong>, and <strong>Delete</strong> rights per store module.
            </span>
          </div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <span class="badge badge--secondary" style="font-size: 0.85rem; padding: 6px 12px; font-weight: 600;">
              {{ form.permissions.length }} Permissions Selected
            </span>
          </div>
        </div>

        <!-- Super Admin Notice -->
        <div 
          v-if="isSuperAdmin" 
          style="padding: 16px 20px; background: #fdf2f4; border: 1px solid #fecdd3; border-radius: 10px; color: #9f1239; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;"
        >
          <span style="font-size: 1.5rem;">👑</span>
          <div>
            <strong>Super Admin Privilege:</strong> Super Admins possess universal bypass with unrestricted View, Create, Edit, and Delete permissions across all present and future modules.
          </div>
        </div>

        <!-- Quick Preset Action Buttons Toolbar -->
        <div v-if="!isSuperAdmin" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; padding: 12px 16px; background: #FAF8F5; border-radius: 10px; border: 1px solid #EAE4DC;">
          <button 
            type="button" 
            class="btn btn--secondary btn--sm" 
            @click="selectAllPermissions"
            style="padding: 6px 14px; font-size: 0.82rem; background: #ffffff; cursor: pointer;"
          >
            ✅ Select All ({{ allPermissions.length }})
          </button>
          
          <button 
            type="button" 
            class="btn btn--secondary btn--sm" 
            @click="selectOperationalPermissions"
            style="padding: 6px 14px; font-size: 0.82rem; background: #ffffff; border-color: #6E1F3A; color: #6E1F3A; font-weight: 600; cursor: pointer;"
          >
            🛍️ Store Admin Preset (Orders & Products)
          </button>

          <button 
            type="button" 
            class="btn btn--secondary btn--sm" 
            @click="selectReadOnlyPermissions"
            style="padding: 6px 14px; font-size: 0.82rem; background: #ffffff; cursor: pointer;"
          >
            👁️ View Only (Read-Only)
          </button>

          <button 
            type="button" 
            class="btn btn--secondary btn--sm" 
            @click="clearAllPermissions"
            style="padding: 6px 14px; font-size: 0.82rem; background: #ffffff; color: #ef4444; border-color: #fecdd3; cursor: pointer; margin-left: auto;"
          >
            ✕ Clear All
          </button>
        </div>

        <!-- Matrix Group Tables -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
          <div 
            v-for="(modules, groupName) in permissionMatrix" 
            :key="groupName" 
            style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);"
          >
            <!-- Group Header Bar -->
            <div style="background: #FAF8F5; padding: 10px 18px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
              <span style="font-weight: 700; font-size: 0.88rem; color: #6E1F3A; text-transform: uppercase; letter-spacing: 0.05em;">
                {{ groupName }}
              </span>
              <div style="display: flex; gap: 8px; align-items: center;">
                <span v-if="groupName.includes('Confidential')" class="badge badge--danger" style="font-size: 0.68rem; padding: 2px 8px;">
                  🔒 Confidential Section
                </span>
                <span style="font-size: 0.75rem; color: #64748b;">
                  {{ countGroupSelected(modules) }} / {{ countGroupTotal(modules) }} active
                </span>
              </div>
            </div>

            <!-- Matrix Table -->
            <div style="overflow-x: auto; scrollbar-width: thin;">
              <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                  <tr style="border-bottom: 1px solid #e2e8f0; background: #F8FAFC; color: #475569; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.03em;">
                    <th style="padding: 10px 18px; font-weight: 600; width: 35%;">Menu / Module</th>
                    <th style="padding: 10px 12px; font-weight: 600; text-align: center; width: 14%;">View 👁️</th>
                    <th style="padding: 10px 12px; font-weight: 600; text-align: center; width: 14%;">Create ➕</th>
                    <th style="padding: 10px 12px; font-weight: 600; text-align: center; width: 14%;">Edit ✏️</th>
                    <th style="padding: 10px 12px; font-weight: 600; text-align: center; width: 14%;">Delete 🗑️</th>
                    <th style="padding: 10px 14px; font-weight: 600; text-align: center; width: 9%;">All</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="mod in modules" 
                    :key="mod.key" 
                    style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;"
                    onmouseover="this.style.background='#FDFBF7'"
                    onmouseout="this.style.background='transparent'"
                  >
                    <!-- Module Label & Key -->
                    <td style="padding: 12px 18px;">
                      <div style="display: flex; flex-direction: column;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                          <span style="font-weight: 600; color: #1e293b; font-size: 0.88rem;">{{ mod.label }}</span>
                          <span v-if="mod.view_only" class="badge badge--secondary" style="font-size: 0.65rem; padding: 1px 6px; font-weight: 600; background: #f1f5f9; color: #475569;">
                            👁️ View Only
                          </span>
                        </div>
                        <span style="font-size: 0.72rem; color: #94a3b8; font-family: monospace;">{{ mod.key }}</span>
                      </div>
                    </td>

                    <!-- View Action Checkbox -->
                    <td style="text-align: center; padding: 10px 12px;">
                      <label v-if="mod.actions.view" style="display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 36px; cursor: pointer;" :title="mod.actions.view.description">
                        <input 
                          type="checkbox" 
                          :value="mod.actions.view.id" 
                          v-model="form.permissions"
                          :disabled="isSuperAdmin"
                          style="cursor: pointer; transform: scale(1.2); accent-color: #6E1F3A;"
                        />
                      </label>
                      <span v-else style="color: #cbd5e1; font-size: 0.85rem;">—</span>
                    </td>

                    <!-- Create Action Checkbox -->
                    <td style="text-align: center; padding: 10px 12px;">
                      <label v-if="mod.actions.create" style="display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 36px; cursor: pointer;" :title="mod.actions.create.description">
                        <input 
                          type="checkbox" 
                          :value="mod.actions.create.id" 
                          v-model="form.permissions"
                          :disabled="isSuperAdmin"
                          style="cursor: pointer; transform: scale(1.2); accent-color: #6E1F3A;"
                        />
                      </label>
                      <span v-else style="color: #cbd5e1; font-size: 0.85rem;">—</span>
                    </td>

                    <!-- Edit Action Checkbox -->
                    <td style="text-align: center; padding: 10px 12px;">
                      <label v-if="mod.actions.edit" style="display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 36px; cursor: pointer;" :title="mod.actions.edit.description">
                        <input 
                          type="checkbox" 
                          :value="mod.actions.edit.id" 
                          v-model="form.permissions"
                          :disabled="isSuperAdmin"
                          style="cursor: pointer; transform: scale(1.2); accent-color: #6E1F3A;"
                        />
                      </label>
                      <span v-else style="color: #cbd5e1; font-size: 0.85rem;">—</span>
                    </td>

                    <!-- Delete Action Checkbox -->
                    <td style="text-align: center; padding: 10px 12px;">
                      <label v-if="mod.actions.delete" style="display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 36px; cursor: pointer;" :title="mod.actions.delete.description">
                        <input 
                          type="checkbox" 
                          :value="mod.actions.delete.id" 
                          v-model="form.permissions"
                          :disabled="isSuperAdmin"
                          style="cursor: pointer; transform: scale(1.2); accent-color: #6E1F3A;"
                        />
                      </label>
                      <span v-else style="color: #cbd5e1; font-size: 0.85rem;">—</span>
                    </td>

                    <!-- Toggle All in Module -->
                    <td style="text-align: center; padding: 10px 14px;">
                      <label style="display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 36px; cursor: pointer;" title="Toggle all CRUD actions for this module">
                        <input 
                          type="checkbox" 
                          :checked="isModuleFullySelected(mod)"
                          @change="toggleModuleAll(mod, $event.target.checked)"
                          :disabled="isSuperAdmin"
                          style="cursor: pointer; transform: scale(1.25); accent-color: #B68D40;"
                        />
                      </label>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Form Bottom Action Buttons Bar -->
      <div class="glass-panel" style="padding: 16px 24px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; display: flex; justify-content: flex-end; align-items: center; gap: 14px; position: sticky; bottom: 16px; z-index: 20; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
        <router-link 
          to="/admin/roles" 
          class="btn btn--secondary" 
          style="padding: 0.65rem 1.5rem; text-decoration: none; font-size: 0.9rem;"
        >
          Cancel
        </router-link>

        <button 
          type="submit" 
          class="btn btn--primary" 
          :disabled="saving || isSuperAdmin"
          style="padding: 0.65rem 2rem; min-height: 48px; background: #6E1F3A; color: #ffffff; font-size: 0.95rem; font-weight: 600; box-shadow: 0 4px 12px rgba(110, 31, 58, 0.25);"
        >
          {{ saving ? '⏳ Saving Permissions...' : (isEdit ? '💾 Update Role Permissions' : '➕ Create Security Role') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const isEdit = computed(() => !!route.params.id);
const isSuperAdmin = computed(() => isEdit.value && form.value.name === 'super_admin');

const loading = ref(true);
const saving = ref(false);
const errorMsg = ref('');

const allPermissions = ref([]);
const permissionMatrix = ref({});

const form = ref({
  name: '',
  description: '',
  permissions: [],
});

const fetchPermissions = async () => {
  try {
    const response = await axios.get('/api/admin/permissions');
    if (response.data && response.data.success) {
      allPermissions.value = response.data.data || [];
      permissionMatrix.value = response.data.matrix || {};
    }
  } catch (err) {
    console.error('Failed to load permissions matrix:', err);
    errorMsg.value = 'Failed to load permissions matrix.';
  }
};

const fetchRoleDetails = async () => {
  if (!isEdit.value) return;
  try {
    const response = await axios.get(`/api/admin/roles/${route.params.id}`);
    if (response.data && response.data.success) {
      const role = response.data.data;
      form.value = {
        name: role.name,
        description: role.description || '',
        permissions: role.permissions ? role.permissions.map(p => p.id) : [],
      };
    }
  } catch (err) {
    console.error('Failed to load role details:', err);
    errorMsg.value = err.response?.data?.message || 'Failed to load security role details.';
  }
};

const countGroupSelected = (modules) => {
  let count = 0;
  modules.forEach(mod => {
    Object.values(mod.actions).forEach(a => {
      if (form.value.permissions.includes(a.id)) {
        count++;
      }
    });
  });
  return count;
};

const countGroupTotal = (modules) => {
  let count = 0;
  modules.forEach(mod => {
    count += Object.keys(mod.actions).length;
  });
  return count;
};

const isModuleFullySelected = (mod) => {
  const ids = Object.values(mod.actions).map(a => a.id);
  if (ids.length === 0) return false;
  return ids.every(id => form.value.permissions.includes(id));
};

const toggleModuleAll = (mod, isChecked) => {
  const ids = Object.values(mod.actions).map(a => a.id);
  if (isChecked) {
    ids.forEach(id => {
      if (!form.value.permissions.includes(id)) {
        form.value.permissions.push(id);
      }
    });
  } else {
    form.value.permissions = form.value.permissions.filter(id => !ids.includes(id));
  }
};

const selectAllPermissions = () => {
  form.value.permissions = allPermissions.value.map(p => p.id);
};

const clearAllPermissions = () => {
  form.value.permissions = [];
};

const selectReadOnlyPermissions = () => {
  const viewIds = allPermissions.value
    .filter(p => p.action === 'view' || p.name.endsWith('.view'))
    .map(p => p.id);
  form.value.permissions = viewIds;
};

const selectOperationalPermissions = () => {
  const operationalModules = [
    'dashboard', 'orders', 'returns', 'couriers', 'purchase_orders',
    'products', 'categories', 'tags', 'colors', 'sizes', 'inventory',
    'reviews', 'occasions', 'section_badges', 'coupons', 'reels', 'blog'
  ];
  const ids = allPermissions.value
    .filter(p => operationalModules.includes(p.module) || operationalModules.some(m => p.name.startsWith(m + '.')))
    .map(p => p.id);
  form.value.permissions = ids;
};

const saveRole = async () => {
  saving.value = true;
  errorMsg.value = '';
  try {
    const payload = { ...form.value };
    payload.name = payload.name.trim().toLowerCase().replace(/[^a-z0-9_]+/g, '_');

    let response;
    if (isEdit.value) {
      response = await axios.put(`/api/admin/roles/${route.params.id}`, payload);
    } else {
      response = await axios.post('/api/admin/roles', payload);
    }

    if (response.data && response.data.success) {
      router.push('/admin/roles');
    }
  } catch (err) {
    console.error('Failed to save security role:', err);
    errorMsg.value = err.response?.data?.message || 'Error occurred while saving role configuration';
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  loading.value = true;
  await fetchPermissions();
  if (isEdit.value) {
    await fetchRoleDetails();
  }
  loading.value = false;
});
</script>
