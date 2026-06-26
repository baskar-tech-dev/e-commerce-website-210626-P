<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Brand Masters</h1>
      <span class="admin-page__subtitle">Manage fashion labels and suppliers.</span>
    </div>
    <button class="btn btn--primary" @click="openCreateModal">
      <span>➕</span> Add Brand
    </button>
  </div>

  <!-- Loading State -->
  <div v-if="brandStore.loading && brands.length === 0" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading...</div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Brands Table -->
  <div class="glass-panel" style="overflow: hidden; margin-top: 1rem;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Logo</th>
          <th>Name</th>
          <th>Slug</th>
          <th>Website</th>
          <th>Sort Order</th>
          <th>Status</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="brand in brands" :key="brand.id">
          <td>
            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.05); border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--color-border);">
              <img v-if="brand.logo" :src="brand.logo" :alt="brand.name" style="max-width: 100%; max-height: 100%; object-fit: contain;" />
              <span v-else style="font-weight: 700; color: var(--color-text-muted);">{{ brand.name.charAt(0) }}</span>
            </div>
          </td>
          <td style="font-weight: 500; color: #fff;">{{ brand.name }}</td>
          <td><code>{{ brand.slug }}</code></td>
          <td>
            <a v-if="brand.website" :href="brand.website" target="_blank" style="color: var(--color-primary); text-decoration: none;">
              {{ brand.website }}
            </a>
            <span v-else style="color: var(--color-text-muted);">—</span>
          </td>
          <td>{{ brand.sort_order }}</td>
          <td>
            <span :class="['badge', brand.is_active ? 'badge--success' : 'badge--danger']">
              {{ brand.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <button class="btn btn--secondary btn--sm" @click="openEditModal(brand)">
                ✏️ Edit
              </button>
              <button class="btn btn--danger btn--sm" @click="deleteBrand(brand.id)">
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="brands.length === 0 && !brandStore.loading">
          <td colspan="7" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No brands found. Click "Add Brand" to create one.
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Create/Edit Modal -->
  <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title">{{ isEdit ? 'Edit Brand' : 'Create Brand' }}</h3>
        <button class="modal-close" @click="closeModal">&times;</button>
      </div>
      <form @submit.prevent="saveBrand">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Brand Name</label>
            <input type="text" v-model="form.name" required class="form-input" placeholder="e.g., Nike, Zara" />
          </div>

          <div class="form-group">
            <label class="form-label">Slug (Optional)</label>
            <input type="text" v-model="form.slug" class="form-input" placeholder="e.g., nike" />
          </div>

          <div class="form-group">
            <label class="form-label">Website URL</label>
            <input type="url" v-model="form.website" class="form-input" placeholder="https://example.com" />
          </div>

          <div class="form-group">
            <label class="form-label">Logo Image URL</label>
            <input type="text" v-model="form.logo" class="form-input" placeholder="https://example.com/logo.png" />
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea v-model="form.description" class="form-textarea" placeholder="Describe the brand..."></textarea>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Sort Order</label>
              <input type="number" v-model.number="form.sort_order" class="form-input" />
            </div>
            <div class="form-group" style="justify-content: flex-end;">
              <div class="form-group--row">
                <input type="checkbox" id="is_active" v-model="form.is_active" class="form-input" />
                <label for="is_active" class="form-label" style="cursor: pointer; margin-bottom: 0;">Active</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn--secondary" @click="closeModal" :disabled="submitting">
            Cancel
          </button>
          <button type="submit" class="btn btn--primary" :disabled="submitting">
            {{ submitting ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useBrandStore } from '../../stores/brand';

const brandStore = useBrandStore();
const showModal = ref(false);
const isEdit = ref(false);
const submitting = ref(false);
const errorMsg = ref(null);
const currentId = ref(null);

const form = ref({
  name: '',
  slug: '',
  description: '',
  logo: '',
  website: '',
  sort_order: 0,
  is_active: true,
});

const brands = computed(() => brandStore.brands);

onMounted(() => {
  brandStore.fetchBrands();
});

function openCreateModal() {
  isEdit.value = false;
  currentId.value = null;
  form.value = {
    name: '',
    slug: '',
    description: '',
    logo: '',
    website: '',
    sort_order: 0,
    is_active: true,
  };
  errorMsg.value = null;
  showModal.value = true;
}

function openEditModal(brand) {
  isEdit.value = true;
  currentId.value = brand.id;
  form.value = {
    name: brand.name,
    slug: brand.slug,
    description: brand.description,
    logo: brand.logo,
    website: brand.website,
    sort_order: brand.sort_order,
    is_active: brand.is_active,
  };
  errorMsg.value = null;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

async function saveBrand() {
  submitting.value = true;
  errorMsg.value = null;
  try {
    if (isEdit.value) {
      await brandStore.updateBrand(currentId.value, form.value);
    } else {
      await brandStore.createBrand(form.value);
    }
    showModal.value = false;
  } catch (err) {
    errorMsg.value = err.message || 'Operation failed';
  } finally {
    submitting.value = false;
  }
}

async function deleteBrand(id) {
  if (confirm('Are you sure you want to delete this brand?')) {
    errorMsg.value = null;
    try {
      await brandStore.deleteBrand(id);
    } catch (err) {
      errorMsg.value = err.message || 'Failed to delete brand';
    }
  }
}
</script>
