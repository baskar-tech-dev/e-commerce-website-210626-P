<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">🎨 Color Master</h1>
      <span class="admin-page__subtitle">Manage standardized brand colors, hex codes, and visual swatches for product variants.</span>
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
      <button class="btn btn--primary" @click="openCreateModal" style="border-radius: 8px; font-weight: 600;">
        <span>➕</span> Add New Color
      </button>
    </div>
  </div>

  <!-- Search & Stats Bar -->
  <div class="glass-panel" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; align-items: center; gap: 0.75rem; flex-grow: 1; max-width: 400px;">
      <input 
        type="text" 
        v-model="searchQuery" 
        @input="handleSearch" 
        placeholder="Search by color name or #hex code..." 
        class="form-input" 
        style="height: 40px; font-size: 0.85rem;"
      />
    </div>
    <div style="display: flex; gap: 0.75rem; align-items: center;">
      <span class="badge badge--secondary">{{ colors.length }} Total Colors</span>
      <span class="badge badge--success">{{ activeCount }} Active</span>
    </div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Success Notification -->
  <div v-if="successMsg" class="badge badge--success" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ✓ {{ successMsg }}
  </div>

  <!-- Loading State -->
  <div v-if="colorStore.loading && colors.length === 0" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading Colors...</div>
  </div>

  <!-- Colors Table & Grid -->
  <div class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="color in colors" :key="color.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; border: 2px solid #ffffff; box-shadow: 0 1px 4px rgba(0,0,0,0.2);" :style="{ background: color.code }"></span>
            <div>
              <div class="mdc-title">{{ color.name }}</div>
              <div class="mdc-date" style="font-family: monospace;">{{ color.code }}</div>
            </div>
          </div>
          <span :class="['badge', color.is_active ? 'badge--success' : 'badge--secondary']">
            {{ color.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
        
        <div class="mdc-footer" style="margin-top: 0.75rem;">
          <span style="font-size: 0.75rem; color: var(--color-text-muted);">Sort Order: {{ color.sort_order }}</span>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn--secondary btn--sm" @click="openEditModal(color)">Edit</button>
            <button class="btn btn--danger btn--sm" @click="deleteColor(color.id)">Delete</button>
          </div>
        </div>
      </div>
      
      <div v-if="colors.length === 0 && !colorStore.loading" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No colors found. Click "Add New Color" to create one.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 80px;">Swatch</th>
          <th>Color Name</th>
          <th>Hex Code</th>
          <th>Status</th>
          <th>Sort Order</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="color in colors" :key="color.id">
          <td>
            <div style="display: flex; align-items: center; justify-content: center;">
              <span 
                style="display: inline-block; width: 32px; height: 32px; border-radius: 50%; border: 2px solid #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.15);" 
                :style="{ background: color.code }"
              ></span>
            </div>
          </td>
          <td style="font-weight: 600; color: #1e293b;">
            {{ color.name }}
          </td>
          <td>
            <code style="font-family: monospace; font-size: 0.85rem; padding: 2px 6px; background: rgba(0,0,0,0.04); border-radius: 4px;">
              {{ color.code }}
            </code>
          </td>
          <td>
            <span :class="['badge', color.is_active ? 'badge--success' : 'badge--secondary']">
              {{ color.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td>{{ color.sort_order }}</td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <button class="btn btn--secondary btn--sm" @click="openEditModal(color)">
                ✏️ Edit
              </button>
              <button class="btn btn--danger btn--sm" @click="deleteColor(color.id)">
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="colors.length === 0 && !colorStore.loading">
          <td colspan="6" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No colors found. Click "Add New Color" to get started.
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Create/Edit Modal -->
  <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container" style="max-width: 440px;">
      <div class="modal-header">
        <h3 class="modal-title">{{ isEdit ? 'Edit Color' : 'Add New Color' }}</h3>
        <button class="modal-close" @click="closeModal">&times;</button>
      </div>
      <form @submit.prevent="saveColor">
        <div class="modal-body">
          <div v-if="modalError" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.5rem; width: 100%; border-radius: 6px;">
            ⚠️ {{ modalError }}
          </div>

          <div class="floating-label-group" style="margin-bottom: 1.25rem;">
            <input 
              type="text" 
              v-model="form.name" 
              class="form-input" 
              :class="{'has-value': !!form.name}" 
              placeholder=" " 
              id="color_name_input"
              required 
            />
            <label for="color_name_input" class="form-label">Color Name * (e.g. Crimson Maroon)</label>
          </div>

          <!-- Color Swatch & Hex Input -->
          <div style="margin-bottom: 1.25rem;">
            <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.35rem; display: block;">
              Color Hex Code & Picker *
            </label>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
              <input 
                type="color" 
                v-model="form.code" 
                style="width: 44px; height: 44px; border: none; border-radius: 8px; cursor: pointer; padding: 0;" 
              />
              <input 
                type="text" 
                v-model="form.code" 
                class="form-input" 
                placeholder="#6B1124" 
                style="font-family: monospace; font-weight: 600; text-transform: uppercase;" 
                required 
              />
            </div>
          </div>

          <div class="grid-2" style="margin-bottom: 1rem;">
            <div class="floating-label-group">
              <input 
                type="number" 
                v-model.number="form.sort_order" 
                class="form-input" 
                :class="{'has-value': form.sort_order !== undefined}" 
                placeholder=" " 
                id="color_sort_order"
              />
              <label for="color_sort_order" class="form-label">Sort Order</label>
            </div>
            <div style="display: flex; align-items: center; justify-content: flex-end;">
              <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="form.is_active" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--color-primary);" />
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-primary);">Active Status</span>
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn--secondary" @click="closeModal">Cancel</button>
          <button type="submit" class="btn btn--primary" :disabled="submitting">
            {{ submitting ? 'Saving...' : (isEdit ? 'Update Color' : 'Save Color') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useColorStore } from '../../stores/color';

const colorStore = useColorStore();

const searchQuery = ref('');
const showModal = ref(false);
const isEdit = ref(false);
const editId = ref(null);
const submitting = ref(false);
const errorMsg = ref(null);
const successMsg = ref(null);
const modalError = ref(null);

const form = ref({
  name: '',
  code: '#6B1124',
  is_active: true,
  sort_order: 0,
});

const colors = computed(() => colorStore.colors);
const activeCount = computed(() => colorStore.colors.filter(c => c.is_active).length);

let searchTimeout = null;
function handleSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    colorStore.fetchColors(searchQuery.value);
  }, 300);
}

function openCreateModal() {
  isEdit.value = false;
  editId.value = null;
  form.value = {
    name: '',
    code: '#6B1124',
    is_active: true,
    sort_order: colors.value.length + 1,
  };
  modalError.value = null;
  showModal.value = true;
}

function openEditModal(color) {
  isEdit.value = true;
  editId.value = color.id;
  form.value = {
    name: color.name,
    code: color.code,
    is_active: color.is_active,
    sort_order: color.sort_order,
  };
  modalError.value = null;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

async function saveColor() {
  if (!form.value.name.trim()) {
    modalError.value = 'Color name is required.';
    return;
  }

  submitting.value = true;
  modalError.value = null;
  try {
    if (isEdit.value) {
      await colorStore.updateColor(editId.value, form.value);
      successMsg.value = `Color "${form.value.name}" updated successfully.`;
    } else {
      await colorStore.createColor(form.value);
      successMsg.value = `Color "${form.value.name}" created successfully.`;
    }
    closeModal();
    setTimeout(() => { successMsg.value = null; }, 3000);
  } catch (err) {
    modalError.value = err.response?.data?.message || 'Failed to save color.';
  } finally {
    submitting.value = false;
  }
}

async function deleteColor(id) {
  if (!confirm('Are you sure you want to delete this color?')) return;
  try {
    await colorStore.deleteColor(id);
    successMsg.value = 'Color deleted successfully.';
    setTimeout(() => { successMsg.value = null; }, 3000);
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to delete color.';
    setTimeout(() => { errorMsg.value = null; }, 3000);
  }
}

onMounted(() => {
  colorStore.fetchColors();
});
</script>
