<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Tag Masters</h1>
      <span class="admin-page__subtitle">Add descriptive tags for search indexing and grouping.</span>
    </div>
    <button class="btn btn--primary" @click="openCreateModal">
      <span>➕</span> Add Tag
    </button>
  </div>

  <!-- Loading State -->
  <div v-if="tagStore.loading && tags.length === 0" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value">Loading...</div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Tags Table -->
  <div class="glass-panel" style="overflow: hidden; margin-top: 1rem;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="tag in tags" :key="tag.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div>
              <div class="mdc-title">{{ tag.name }}</div>
              <div class="mdc-date">{{ tag.slug }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">Created: {{ formatDate(tag.created_at) }}</span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn--secondary btn--sm" @click="openEditModal(tag)">Edit</button>
            <button class="btn btn--danger btn--sm" @click="deleteTag(tag.id)">Delete</button>
          </div>
        </div>
      </div>
      
      <div v-if="tags.length === 0 && !tagStore.loading" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No tags found. Click "Add Tag" to create one.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Slug</th>
          <th>Created At</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="tag in tags" :key="tag.id">
          <td style="font-weight: 500; color: #1e293b;">{{ tag.name }}</td>
          <td><code>{{ tag.slug }}</code></td>
          <td>{{ formatDate(tag.created_at) }}</td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <button class="btn btn--secondary btn--sm" @click="openEditModal(tag)">
                ✏️ Edit
              </button>
              <button class="btn btn--danger btn--sm" @click="deleteTag(tag.id)">
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="tags.length === 0 && !tagStore.loading">
          <td colspan="4" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No tags found. Click "Add Tag" to create one.
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Create/Edit Modal -->
  <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
    <div class="modal-container" style="max-width: 400px;">
      <div class="modal-header">
        <h3 class="modal-title">{{ isEdit ? 'Edit Tag' : 'Create Tag' }}</h3>
        <button class="modal-close" @click="closeModal">&times;</button>
      </div>
      <form @submit.prevent="saveTag">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Tag Name</label>
            <input type="text" v-model="form.name" required class="form-input" placeholder="e.g., Summerwear, Off-shoulder" />
          </div>

          <div class="form-group">
            <label class="form-label">Slug (Optional)</label>
            <input type="text" v-model="form.slug" class="form-input" placeholder="e.g., summerwear" />
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
import { useTagStore } from '../../stores/tag';

const tagStore = useTagStore();
const showModal = ref(false);
const isEdit = ref(false);
const submitting = ref(false);
const errorMsg = ref(null);
const currentId = ref(null);

const form = ref({
  name: '',
  slug: '',
});

const tags = computed(() => tagStore.tags);

onMounted(() => {
  tagStore.fetchTags();
});

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function openCreateModal() {
  isEdit.value = false;
  currentId.value = null;
  form.value = {
    name: '',
    slug: '',
  };
  errorMsg.value = null;
  showModal.value = true;
}

function openEditModal(tag) {
  isEdit.value = true;
  currentId.value = tag.id;
  form.value = {
    name: tag.name,
    slug: tag.slug,
  };
  errorMsg.value = null;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

async function saveTag() {
  submitting.value = true;
  errorMsg.value = null;
  try {
    if (isEdit.value) {
      await tagStore.updateTag(currentId.value, form.value);
    } else {
      await tagStore.createTag(form.value);
    }
    showModal.value = false;
  } catch (err) {
    errorMsg.value = err.message || 'Operation failed';
  } finally {
    submitting.value = false;
  }
}

async function deleteTag(id) {
  if (confirm('Are you sure you want to delete this tag?')) {
    errorMsg.value = null;
    try {
      await tagStore.deleteTag(id);
    } catch (err) {
      errorMsg.value = err.message || 'Failed to delete tag';
    }
  }
}
</script>
