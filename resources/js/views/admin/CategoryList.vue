<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Category Masters</h1>
      <span class="admin-page__subtitle">Organize and manage the product taxonomy tree.</span>
    </div>
    <button class="btn btn--primary" @click="openCreateModal">
      <span>➕</span> Add Category
    </button>
  </div>

  <!-- Loading State -->
  <div v-if="categoryStore.loading && categories.length === 0" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading...</div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Categories Table -->
  <div class="glass-panel" style="overflow: hidden; margin-top: 1rem;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="category in categories" :key="category.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <img v-if="category.image" :src="category.image" style="width: 40px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-border);" />
            <div v-else style="width: 40px; height: 50px; border-radius: 4px; border: 1px dashed var(--color-border); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 1.25rem;">
              🖼️
            </div>
            <div>
              <div class="mdc-title">{{ category.name }}</div>
              <div class="mdc-date">{{ category.slug }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">
              Parent: 
              <span v-if="category.parent_id" style="color: #1e293b; font-weight: 600;">{{ getParentName(category.parent_id) }}</span>
              <span v-else style="color: var(--color-text-muted);">— Root</span>
            </span>
          </div>
          <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between;">
            <span>Sort Order: <strong>{{ category.sort_order }}</strong></span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', category.is_active ? 'badge--success' : 'badge--danger']">
              {{ category.is_active ? 'Active' : 'Inactive' }}
            </span>
            <span :class="['badge', category.is_featured ? 'badge--success' : 'badge--danger']">
              {{ category.is_featured ? 'Featured' : 'Standard' }}
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn--secondary btn--sm" @click="openEditModal(category)">Edit</button>
            <button class="btn btn--danger btn--sm" @click="deleteCategory(category.id)">Delete</button>
          </div>
        </div>
      </div>
      
      <div v-if="categories.length === 0 && !categoryStore.loading" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No categories found.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 50px; text-align: center;">S.No</th>
          <th>Cover Image</th>
          <th>Size Chart</th>
          <th>Name</th>
          <th>Slug</th>
          <th>Parent</th>
          <th>Sort Order</th>
          <th>Status</th>
          <th>Featured</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(category, index) in categories" :key="category.id">
          <td style="width: 50px; text-align: center; font-weight: 600; color: var(--color-text-secondary); font-size: 0.85rem;">
            {{ index + 1 }}
          </td>
          <td>
            <img v-if="category.image" :src="category.image" style="width: 40px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-border);" />
            <div v-else style="width: 40px; height: 50px; border-radius: 4px; border: 1px dashed var(--color-border); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 1.25rem;">
              🖼️
            </div>
          </td>
          <td>
            <div v-if="category.size_chart_image" @click="previewChart(category)" style="cursor: pointer; position: relative; width: 45px; height: 45px; border-radius: 6px; overflow: hidden; border: 1px solid var(--color-primary); box-shadow: 0 2px 6px rgba(0,0,0,0.08);" title="Click to preview size chart">
              <img :src="category.size_chart_image" style="width: 100%; height: 100%; object-fit: cover;" />
              <span style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(128,0,32,0.85); color: #fff; font-size: 8px; text-align: center; padding: 1px 0; font-weight: bold;">VIEW</span>
            </div>
            <span v-else style="font-size: 0.75rem; color: var(--color-text-muted);">No Chart</span>
          </td>
          <td style="font-weight: 500; color: #1e293b;">{{ category.name }}</td>
          <td><code>{{ category.slug }}</code></td>
          <td>
            <span v-if="category.parent_id" class="badge" style="background: rgba(255,255,255,0.05); color: #1e293b;">
              {{ getParentName(category.parent_id) }}
            </span>
            <span v-else style="color: var(--color-text-muted); font-size: 0.85rem;">— Root</span>
          </td>
          <td>{{ category.sort_order }}</td>
          <td>
            <span :class="['badge', category.is_active ? 'badge--success' : 'badge--danger']">
              {{ category.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td>
            <span :class="['badge', category.is_featured ? 'badge--success' : 'badge--danger']">
              {{ category.is_featured ? 'Yes' : 'No' }}
            </span>
          </td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <button class="btn btn--secondary btn--sm" @click="openEditModal(category)">
                ✏️ Edit
              </button>
              <button class="btn btn--danger btn--sm" @click="deleteCategory(category.id)">
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="categories.length === 0 && !categoryStore.loading">
          <td colspan="10" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No categories found. Click "Add Category" to create one.
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Create/Edit Modal -->
  <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container" style="max-height: 90vh; display: flex; flex-direction: column;">
      <div class="modal-header">
        <h3 class="modal-title">{{ isEdit ? 'Edit Category' : 'Create Category' }}</h3>
        <button class="modal-close" @click="closeModal">&times;</button>
      </div>
      <form @submit.prevent="saveCategory" style="display: flex; flex-direction: column; overflow: hidden; flex-grow: 1;">
        <div class="modal-body" style="overflow-y: auto; flex-grow: 1; padding: var(--spacing-md) var(--spacing-lg);">
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Category Name</label>
              <input type="text" v-model="form.name" required class="form-input" placeholder="e.g., Stretchable Blouses" />
            </div>
            <div class="form-group">
              <label class="form-label">Slug (Optional)</label>
              <input type="text" v-model="form.slug" class="form-input" placeholder="e.g., stretchable-blouses" />
            </div>
          </div>

          <!-- Category Cover Image -->
          <div class="form-group">
            <label class="form-label">Category Cover Image</label>
            <div style="display: flex; align-items: center; gap: 1rem;">
              <div v-if="form.image" style="position: relative; width: 80px; height: 100px; border-radius: 8px; overflow: hidden; border: 1px solid var(--color-border); flex-shrink: 0;">
                <img :src="form.image" style="width: 100%; height: 100%; object-fit: cover;" />
                <button type="button" @click="removeImage" style="position: absolute; top: 2px; right: 2px; background: rgba(239, 68, 68, 0.8); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 10px;">&times;</button>
              </div>
              <div v-else style="width: 80px; height: 100px; border-radius: 8px; border: 2px dashed var(--color-border); display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 1.5rem; flex-shrink: 0;">
                🖼️
              </div>
              <div style="flex: 1;">
                <input type="file" accept="image/*" @change="handleImageUpload" style="display: none;" ref="fileInput" />
                <button type="button" class="btn btn--secondary btn--sm" @click="$refs.fileInput.click()" :disabled="imageUploading">
                  {{ imageUploading ? 'Uploading...' : (form.image ? 'Change Cover' : 'Upload Cover') }}
                </button>
                <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.25rem;">
                  Recommended banner/card cover image (4:5 ratio).
                </div>
              </div>
            </div>
          </div>

          <!-- Category Size Chart Image Upload -->
          <div class="form-group" style="background: rgba(182, 141, 64, 0.05); padding: 1rem; border-radius: 8px; border: 1px dashed rgba(182, 141, 64, 0.4);">
            <label class="form-label" style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--color-primary);">
              📐 Category Size Chart / Measurement Guide
            </label>
            <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem;">
              <div v-if="form.size_chart_image" style="position: relative; width: 100px; height: 100px; border-radius: 8px; overflow: hidden; border: 2px solid var(--color-primary); flex-shrink: 0; background: #fff;">
                <img :src="form.size_chart_image" style="width: 100%; height: 100%; object-fit: contain;" />
                <button type="button" @click="removeSizeChart" style="position: absolute; top: 2px; right: 2px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px; font-weight: bold;">&times;</button>
              </div>
              <div v-else style="width: 100px; height: 100px; border-radius: 8px; border: 2px dashed rgba(182, 141, 64, 0.4); display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--color-text-muted); flex-shrink: 0; background: #fff;">
                <span style="font-size: 1.5rem;">📐</span>
                <span style="font-size: 0.65rem; color: #7A726A; margin-top: 2px;">No Chart</span>
              </div>
              <div style="flex: 1;">
                <input type="file" accept="image/*" @change="handleSizeChartUpload" style="display: none;" ref="sizeChartInput" />
                <button type="button" class="btn btn--secondary btn--sm" @click="$refs.sizeChartInput.click()" :disabled="sizeChartUploading">
                  {{ sizeChartUploading ? 'Uploading Chart...' : (form.size_chart_image ? 'Change Size Chart' : 'Upload Size Chart Image') }}
                </button>
                <div style="font-size: 0.75rem; color: #7A726A; margin-top: 0.35rem; line-height: 1.4;">
                  Upload specific measurement table (inches/cm) for this category. Displayed to shoppers when they click "Size Guide" on product pages.
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Parent Category</label>
            <select v-model="form.parent_id" class="form-select">
              <option :value="null">None (Root Category)</option>
              <option v-for="cat in eligibleParents" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea v-model="form.description" class="form-textarea" placeholder="Describe the category..."></textarea>
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
              <div class="form-group--row">
                <input type="checkbox" id="is_featured" v-model="form.is_featured" class="form-input" />
                <label for="is_featured" class="form-label" style="cursor: pointer; margin-bottom: 0;">Featured</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="flex-shrink: 0;">
          <button type="button" class="btn btn--secondary" @click="closeModal" :disabled="submitting">
            Cancel
          </button>
          <button type="submit" class="btn btn--primary" :disabled="submitting">
            {{ submitting ? 'Saving...' : 'Save Category' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Admin Size Chart Fullscreen Preview Modal -->
  <div v-if="previewChartModal" class="modal-overlay" @click.self="previewChartModal = null">
    <div class="modal-container" style="max-width: 650px; text-align: center; padding: var(--spacing-lg);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="margin: 0; font-family: 'Playfair Display', serif; color: var(--color-primary);">
          Size Chart: {{ previewChartModal.name }}
        </h3>
        <button class="modal-close" @click="previewChartModal = null">&times;</button>
      </div>
      <div style="background: #fff; border-radius: 8px; padding: 0.5rem; border: 1px solid var(--color-border); max-height: 70vh; overflow-y: auto;">
        <img :src="previewChartModal.size_chart_image" alt="Size Chart" style="max-width: 100%; height: auto; border-radius: 6px;" />
      </div>
      <div style="margin-top: 1rem; display: flex; justify-content: flex-end;">
        <button class="btn btn--secondary btn--sm" @click="previewChartModal = null">Close</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useCategoryStore } from '../../stores/category';
import axios from 'axios';

const categoryStore = useCategoryStore();
const showModal = ref(false);
const isEdit = ref(false);
const submitting = ref(false);
const errorMsg = ref(null);
const currentId = ref(null);
const imageUploading = ref(false);
const sizeChartUploading = ref(false);
const sizeChartInput = ref(null);
const previewChartModal = ref(null);

const form = ref({
  parent_id: null,
  name: '',
  slug: '',
  description: '',
  image: '',
  size_chart_image: '',
  sort_order: 0,
  is_active: true,
  is_featured: false,
});

const categories = computed(() => categoryStore.categories);

// Filter parents to avoid self-reference or circular references
const eligibleParents = computed(() => {
  if (!isEdit.value) return categories.value;
  return categories.value.filter(c => c.id !== currentId.value);
});

onMounted(() => {
  categoryStore.fetchCategories();
});

function getParentName(parentId) {
  const parent = categories.value.find(c => c.id === parentId);
  return parent ? parent.name : 'Unknown';
}

function openCreateModal() {
  isEdit.value = false;
  currentId.value = null;
  form.value = {
    parent_id: null,
    name: '',
    slug: '',
    description: '',
    image: '',
    size_chart_image: '',
    sort_order: 0,
    is_active: true,
    is_featured: false,
  };
  errorMsg.value = null;
  showModal.value = true;
}

function openEditModal(category) {
  isEdit.value = true;
  currentId.value = category.id;
  form.value = {
    parent_id: category.parent_id,
    name: category.name,
    slug: category.slug,
    description: category.description,
    image: category.image || '',
    size_chart_image: category.size_chart_image || '',
    sort_order: category.sort_order,
    is_active: category.is_active,
    is_featured: category.is_featured,
  };
  errorMsg.value = null;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

function previewChart(cat) {
  previewChartModal.value = cat;
}

async function saveCategory() {
  submitting.value = true;
  errorMsg.value = null;

  const payload = {
    name: form.value.name ? form.value.name.trim() : '',
    slug: form.value.slug ? form.value.slug.trim() : null,
    parent_id: form.value.parent_id ? Number(form.value.parent_id) : null,
    description: form.value.description || null,
    image: form.value.image || null,
    size_chart_image: form.value.size_chart_image || null,
    sort_order: Number(form.value.sort_order || 0),
    is_active: !!form.value.is_active,
    is_featured: !!form.value.is_featured,
  };

  try {
    if (isEdit.value) {
      await categoryStore.updateCategory(currentId.value, payload);
    } else {
      await categoryStore.createCategory(payload);
    }
    showModal.value = false;
  } catch (err) {
    errorMsg.value = err.message || (typeof err === 'string' ? err : 'Operation failed');
  } finally {
    submitting.value = false;
  }
}

async function deleteCategory(id) {
  if (confirm('Are you sure you want to delete this category?')) {
    errorMsg.value = null;
    try {
      await categoryStore.deleteCategory(id);
    } catch (err) {
      errorMsg.value = err.message || 'Failed to delete category';
    }
  }
}

async function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);

  imageUploading.value = true;
  try {
    const res = await axios.post('/api/admin/media/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    if (res.data.success) {
      form.value.image = res.data.data.url;
    } else {
      alert('Upload failed: ' + res.data.message);
    }
  } catch (err) {
    alert('Upload error: ' + (err.response?.data?.message || err.message));
  } finally {
    imageUploading.value = false;
    if (event.target) event.target.value = '';
  }
}

function removeImage() {
  form.value.image = '';
}

async function handleSizeChartUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);

  sizeChartUploading.value = true;
  try {
    const res = await axios.post('/api/admin/media/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    if (res.data.success) {
      form.value.size_chart_image = res.data.data.url;
    } else {
      alert('Upload failed: ' + res.data.message);
    }
  } catch (err) {
    alert('Upload error: ' + (err.response?.data?.message || err.message));
  } finally {
    sizeChartUploading.value = false;
    if (event.target) event.target.value = '';
  }
}

function removeSizeChart() {
  form.value.size_chart_image = '';
}
</script>
