<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">📐 Size Group & Size Master</h1>
      <span class="admin-page__subtitle">Manage size families (e.g. Stretchable Blouses, Saree Blouses, Alpha sizes) and their standard measurement values.</span>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
      <button class="btn btn--secondary" @click="openGroupModal" style="border-radius: 8px; font-weight: 600;">
        📁 Add Size Group
      </button>
      <button class="btn btn--primary" @click="openSizeModal()" style="border-radius: 8px; font-weight: 600;">
        ➕ Add New Size
      </button>
    </div>
  </div>

  <!-- Notification Badges -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>
  <div v-if="successMsg" class="badge badge--success" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ✓ {{ successMsg }}
  </div>

  <!-- Size Groups Nav Tabs Bar -->
  <div class="glass-panel" style="padding: 1rem; margin-bottom: 1.5rem; overflow-x: auto;">
    <div style="display: flex; gap: 0.75rem; align-items: center; min-width: max-content;">
      <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-secondary); margin-right: 0.5rem; letter-spacing: 0.5px;">
        Size Groups:
      </span>
      <button 
        v-for="group in sizeGroups" 
        :key="group.id"
        type="button"
        :class="['btn btn--sm', selectedGroupId === group.id ? 'btn--primary' : 'btn--secondary']"
        @click="selectGroup(group.id)"
        style="border-radius: 20px; padding: 0.4rem 1rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;"
      >
        <span>{{ group.name }}</span>
        <span style="font-size: 0.75rem; opacity: 0.85; background: rgba(0,0,0,0.15); padding: 1px 6px; border-radius: 10px;">
          {{ group.sizes?.length || 0 }}
        </span>
      </button>
    </div>
  </div>

  <!-- Selected Group Detail & Sizes List -->
  <div v-if="currentGroup" class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
      <div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <h2 style="font-size: 1.25rem; color: var(--color-primary); margin: 0; font-family: 'Playfair Display', serif;">
            {{ currentGroup.name }}
          </h2>
          <span :class="['badge', currentGroup.is_active ? 'badge--success' : 'badge--secondary']">
            {{ currentGroup.is_active ? 'Active Group' : 'Inactive Group' }}
          </span>
          <code style="font-size: 0.75rem; background: rgba(0,0,0,0.04); padding: 2px 6px; border-radius: 4px;">{{ currentGroup.code }}</code>
        </div>
        <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin: 0.25rem 0 0 0;">
          {{ currentGroup.description || 'No description provided.' }}
        </p>
      </div>

      <div style="display: flex; gap: 0.5rem; align-items: center;">
        <button class="btn btn--secondary btn--sm" @click="editGroup(currentGroup)">
          ✏️ Edit Group
        </button>
        <button class="btn btn--primary btn--sm" @click="openSizeModal(currentGroup.id)">
          ➕ Add Size to this Group
        </button>
        <button class="btn btn--danger btn--sm" @click="deleteGroup(currentGroup.id)">
          🗑️ Delete Group
        </button>
      </div>
    </div>

    <!-- Sizes Table -->
    <div style="overflow-x: auto;">
      <table class="data-table" style="margin-bottom: 0;">
        <thead>
          <tr>
            <th style="width: 50px; text-align: center;">#</th>
            <th style="width: 140px;">Size Name / Label</th>
            <th style="width: 120px;">Code</th>
            <th>Measurement / Fit Hint</th>
            <th style="width: 100px;">Status</th>
            <th style="width: 100px;">Sort Order</th>
            <th style="width: 120px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(size, idx) in currentGroup.sizes" :key="size.id">
            <td style="text-align: center; color: var(--color-text-muted); font-weight: 500;">
              {{ idx + 1 }}
            </td>
            <td>
              <span class="badge badge--secondary" style="font-size: 0.9rem; font-weight: 700; color: var(--color-primary);">
                {{ size.name }}
              </span>
            </td>
            <td><code>{{ size.code || size.name }}</code></td>
            <td style="color: var(--color-text-secondary); font-size: 0.85rem;">
              {{ size.measurement_hint || '—' }}
            </td>
            <td>
              <span :class="['badge', size.is_active ? 'badge--success' : 'badge--secondary']">
                {{ size.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td>{{ size.sort_order }}</td>
            <td style="text-align: right;">
              <div style="display: inline-flex; gap: 0.4rem;">
                <button class="btn btn--secondary btn--sm" @click="editSize(size)" style="padding: 2px 8px; font-size: 0.75rem;">
                  ✏️
                </button>
                <button class="btn btn--danger btn--sm" @click="deleteSize(size.id)" style="padding: 2px 8px; font-size: 0.75rem;">
                  🗑️
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!currentGroup.sizes || currentGroup.sizes.length === 0">
            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--color-text-muted);">
              No sizes in this group yet. Click "Add Size to this Group" to get started.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 1. Size Group Create/Edit Modal -->
  <div v-if="showGroupModal" class="modal-overlay" @click.self="closeGroupModal">
    <div class="modal-container" style="max-width: 480px;">
      <div class="modal-header">
        <h3 class="modal-title">{{ isEditGroup ? 'Edit Size Group' : 'Create Size Group' }}</h3>
        <button class="modal-close" @click="closeGroupModal">&times;</button>
      </div>
      <form @submit.prevent="saveGroup">
        <div class="modal-body">
          <div v-if="groupModalError" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.5rem; width: 100%; border-radius: 6px;">
            ⚠️ {{ groupModalError }}
          </div>

          <div class="floating-label-group" style="margin-bottom: 1.25rem;">
            <input 
              type="text" 
              v-model="groupForm.name" 
              class="form-input" 
              :class="{'has-value': !!groupForm.name}" 
              placeholder=" " 
              id="group_name_input"
              required 
            />
            <label for="group_name_input" class="form-label">Group Name * (e.g. Stretchable Blouse Sizes)</label>
          </div>

          <div class="floating-label-group" style="margin-bottom: 1.25rem;">
            <input 
              type="text" 
              v-model="groupForm.code" 
              class="form-input" 
              :class="{'has-value': !!groupForm.code}" 
              placeholder=" " 
              id="group_code_input"
              style="text-transform: uppercase;"
            />
            <label for="group_code_input" class="form-label">Group Code (e.g. STRETCH_BLOUSE)</label>
          </div>

          <div class="floating-label-group" style="margin-bottom: 1.25rem;">
            <input 
              type="text" 
              v-model="groupForm.description" 
              class="form-input" 
              :class="{'has-value': !!groupForm.description}" 
              placeholder=" " 
              id="group_desc_input"
            />
            <label for="group_desc_input" class="form-label">Description (e.g. 4-Way Stretch Blouse ranges)</label>
          </div>

          <div class="grid-2">
            <div class="floating-label-group">
              <input 
                type="number" 
                v-model.number="groupForm.sort_order" 
                class="form-input" 
                :class="{'has-value': groupForm.sort_order !== undefined}" 
                placeholder=" " 
                id="group_sort_order"
              />
              <label for="group_sort_order" class="form-label">Sort Order</label>
            </div>
            <div style="display: flex; align-items: center; justify-content: flex-end;">
              <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="groupForm.is_active" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--color-primary);" />
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-primary);">Active Status</span>
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn--secondary" @click="closeGroupModal">Cancel</button>
          <button type="submit" class="btn btn--primary" :disabled="submittingGroup">
            {{ submittingGroup ? 'Saving...' : (isEditGroup ? 'Update Group' : 'Save Group') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- 2. Individual Size Create/Edit Modal -->
  <div v-if="showSizeModal" class="modal-overlay" @click.self="closeSizeModal">
    <div class="modal-container" style="max-width: 480px;">
      <div class="modal-header">
        <h3 class="modal-title">{{ isEditSize ? 'Edit Size' : 'Add New Size' }}</h3>
        <button class="modal-close" @click="closeSizeModal">&times;</button>
      </div>
      <form @submit.prevent="saveSize">
        <div class="modal-body">
          <div v-if="sizeModalError" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.5rem; width: 100%; border-radius: 6px;">
            ⚠️ {{ sizeModalError }}
          </div>

          <div class="floating-label-group" style="margin-bottom: 1.25rem;">
            <select v-model="sizeForm.size_group_id" class="form-select has-value" id="size_group_select" required>
              <option v-for="g in sizeGroups" :key="g.id" :value="g.id">
                {{ g.name }}
              </option>
            </select>
            <label for="size_group_select" class="form-label">Size Group *</label>
          </div>

          <div class="grid-2" style="margin-bottom: 1.25rem;">
            <div class="floating-label-group">
              <input 
                type="text" 
                v-model="sizeForm.name" 
                class="form-input" 
                :class="{'has-value': !!sizeForm.name}" 
                placeholder=" " 
                id="size_name_input"
                required 
              />
              <label for="size_name_input" class="form-label">Size Label * (e.g. 34-37)</label>
            </div>
            <div class="floating-label-group">
              <input 
                type="text" 
                v-model="sizeForm.code" 
                class="form-input" 
                :class="{'has-value': !!sizeForm.code}" 
                placeholder=" " 
                id="size_code_input"
              />
              <label for="size_code_input" class="form-label">SKU Code (e.g. 3437)</label>
            </div>
          </div>

          <div class="floating-label-group" style="margin-bottom: 1.25rem;">
            <input 
              type="text" 
              v-model="sizeForm.measurement_hint" 
              class="form-input" 
              :class="{'has-value': !!sizeForm.measurement_hint}" 
              placeholder=" " 
              id="size_hint_input"
            />
            <label for="size_hint_input" class="form-label">Measurement Hint (e.g. Fits Bust 34" to 37")</label>
          </div>

          <div class="grid-2">
            <div class="floating-label-group">
              <input 
                type="number" 
                v-model.number="sizeForm.sort_order" 
                class="form-input" 
                :class="{'has-value': sizeForm.sort_order !== undefined}" 
                placeholder=" " 
                id="size_sort_order"
              />
              <label for="size_sort_order" class="form-label">Sort Order</label>
            </div>
            <div style="display: flex; align-items: center; justify-content: flex-end;">
              <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="sizeForm.is_active" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--color-primary);" />
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-primary);">Active Status</span>
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn--secondary" @click="closeSizeModal">Cancel</button>
          <button type="submit" class="btn btn--primary" :disabled="submittingSize">
            {{ submittingSize ? 'Saving...' : (isEditSize ? 'Update Size' : 'Save Size') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSizeStore } from '../../stores/size';

const sizeStore = useSizeStore();

const selectedGroupId = ref(null);
const errorMsg = ref(null);
const successMsg = ref(null);

// Group Modal State
const showGroupModal = ref(false);
const isEditGroup = ref(false);
const editGroupId = ref(null);
const submittingGroup = ref(false);
const groupModalError = ref(null);
const groupForm = ref({
  name: '',
  code: '',
  description: '',
  is_active: true,
  sort_order: 0,
});

// Size Modal State
const showSizeModal = ref(false);
const isEditSize = ref(false);
const editSizeId = ref(null);
const submittingSize = ref(false);
const sizeModalError = ref(null);
const sizeForm = ref({
  size_group_id: '',
  name: '',
  code: '',
  measurement_hint: '',
  is_active: true,
  sort_order: 0,
});

const sizeGroups = computed(() => sizeStore.sizeGroups);
const currentGroup = computed(() => {
  if (!selectedGroupId.value) return sizeGroups.value[0] || null;
  return sizeGroups.value.find(g => g.id === selectedGroupId.value) || sizeGroups.value[0] || null;
});

function selectGroup(groupId) {
  selectedGroupId.value = groupId;
}

// Group Actions
function openGroupModal() {
  isEditGroup.value = false;
  editGroupId.value = null;
  groupForm.value = {
    name: '',
    code: '',
    description: '',
    is_active: true,
    sort_order: sizeGroups.value.length + 1,
  };
  groupModalError.value = null;
  showGroupModal.value = true;
}

function editGroup(group) {
  isEditGroup.value = true;
  editGroupId.value = group.id;
  groupForm.value = {
    name: group.name,
    code: group.code,
    description: group.description,
    is_active: group.is_active,
    sort_order: group.sort_order,
  };
  groupModalError.value = null;
  showGroupModal.value = true;
}

function closeGroupModal() {
  showGroupModal.value = false;
}

async function saveGroup() {
  if (!groupForm.value.name.trim()) {
    groupModalError.value = 'Group name is required.';
    return;
  }

  submittingGroup.value = true;
  groupModalError.value = null;
  try {
    if (isEditGroup.value) {
      const updated = await sizeStore.updateSizeGroup(editGroupId.value, groupForm.value);
      successMsg.value = `Size Group "${updated.name}" updated successfully.`;
    } else {
      const created = await sizeStore.createSizeGroup(groupForm.value);
      selectedGroupId.value = created.id;
      successMsg.value = `Size Group "${created.name}" created successfully.`;
    }
    closeGroupModal();
    setTimeout(() => { successMsg.value = null; }, 3000);
  } catch (err) {
    groupModalError.value = err.response?.data?.message || 'Failed to save size group.';
  } finally {
    submittingGroup.value = false;
  }
}

async function deleteGroup(id) {
  if (!confirm('Are you sure you want to delete this size group and all its associated sizes?')) return;
  try {
    await sizeStore.deleteSizeGroup(id);
    successMsg.value = 'Size group deleted successfully.';
    if (selectedGroupId.value === id && sizeGroups.value.length > 0) {
      selectedGroupId.value = sizeGroups.value[0].id;
    }
    setTimeout(() => { successMsg.value = null; }, 3000);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to delete size group.';
    setTimeout(() => { errorMsg.value = null; }, 3000);
  }
}

// Size Actions
function openSizeModal(groupId = null) {
  isEditSize.value = false;
  editSizeId.value = null;
  const targetGroupId = groupId || selectedGroupId.value || (sizeGroups.value[0]?.id ?? '');
  sizeForm.value = {
    size_group_id: targetGroupId,
    name: '',
    code: '',
    measurement_hint: '',
    is_active: true,
    sort_order: (currentGroup.value?.sizes?.length || 0) + 1,
  };
  sizeModalError.value = null;
  showSizeModal.value = true;
}

function editSize(size) {
  isEditSize.value = true;
  editSizeId.value = size.id;
  sizeForm.value = {
    size_group_id: size.size_group_id,
    name: size.name,
    code: size.code,
    measurement_hint: size.measurement_hint,
    is_active: size.is_active,
    sort_order: size.sort_order,
  };
  sizeModalError.value = null;
  showSizeModal.value = true;
}

function closeSizeModal() {
  showSizeModal.value = false;
}

async function saveSize() {
  if (!sizeForm.value.name.trim()) {
    sizeModalError.value = 'Size label is required.';
    return;
  }

  submittingSize.value = true;
  sizeModalError.value = null;
  try {
    if (isEditSize.value) {
      await sizeStore.updateSize(editSizeId.value, sizeForm.value);
      successMsg.value = `Size "${sizeForm.value.name}" updated successfully.`;
    } else {
      await sizeStore.createSize(sizeForm.value);
      successMsg.value = `Size "${sizeForm.value.name}" added successfully.`;
    }
    await sizeStore.fetchSizeGroups();
    closeSizeModal();
    setTimeout(() => { successMsg.value = null; }, 3000);
  } catch (err) {
    sizeModalError.value = err.response?.data?.message || 'Failed to save size.';
  } finally {
    submittingSize.value = false;
  }
}

async function deleteSize(id) {
  if (!confirm('Are you sure you want to delete this size?')) return;
  try {
    await sizeStore.deleteSize(id);
    await sizeStore.fetchSizeGroups();
    successMsg.value = 'Size deleted successfully.';
    setTimeout(() => { successMsg.value = null; }, 3000);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to delete size.';
    setTimeout(() => { errorMsg.value = null; }, 3000);
  }
}

onMounted(async () => {
  await sizeStore.fetchSizeGroups();
  if (sizeGroups.value.length > 0 && !selectedGroupId.value) {
    selectedGroupId.value = sizeGroups.value[0].id;
  }
});
</script>
