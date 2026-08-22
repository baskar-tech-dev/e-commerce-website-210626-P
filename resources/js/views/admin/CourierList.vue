<template>
  <div class="courier-list-view">
    <!-- Header -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg); flex-wrap: wrap; gap: 1rem;">
      <div>
        <h1 class="page-title" style="margin: 0; font-size: 1.6rem; font-weight: 700; color: #1e293b;">Courier Partners</h1>
        <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748b;">
          Manage shipping couriers, tracking page URL templates, and internal contact information.
        </p>
      </div>
      <button class="btn btn--primary" @click="openCreateModal" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
        <span>➕ Add Courier Partner</span>
      </button>
    </div>

    <!-- Filter & Search Bar -->
    <div class="glass-panel" style="padding: 1rem 1.25rem; margin-bottom: var(--spacing-md); display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; border: 1px solid #ede4ea; background: #ffffff; border-radius: 10px;">
      <div style="flex: 1; min-width: 240px; position: relative;">
        <input 
          type="text" 
          v-model="searchQuery" 
          @input="debounceSearch"
          placeholder="Search by courier name, code, contact person or phone..." 
          class="form-input" 
          style="width: 100%; padding-left: 2.25rem; background: #faf8f9;"
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem;">🔍</span>
      </div>

      <div style="min-width: 150px;">
        <select v-model="statusFilter" @change="fetchCouriers(1)" class="form-input" style="background: #faf8f9;">
          <option value="">All Statuses</option>
          <option value="true">Active Only</option>
          <option value="false">Inactive Only</option>
        </select>
      </div>

      <button class="btn btn--secondary" @click="resetFilters" style="font-size: 0.85rem;">
        Reset
      </button>
    </div>

    <!-- Alert Messages -->
    <div v-if="successMsg" class="form-success-alert" style="background: #F3FAF7; border: 1px solid #84E1BC; color: #0E6245; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; margin-bottom: var(--spacing-md); display: flex; justify-content: space-between; align-items: center;">
      <span>{{ successMsg }}</span>
      <button type="button" @click="successMsg = ''" style="background: none; border: none; font-size: 1rem; cursor: pointer; color: #0E6245;">✕</button>
    </div>

    <div v-if="errorMsg" class="form-error-alert" style="background: #FDF2F2; border: 1px solid #F8B4B4; color: #9B1C1C; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; margin-bottom: var(--spacing-md); display: flex; justify-content: space-between; align-items: center;">
      <span>{{ errorMsg }}</span>
      <button type="button" @click="errorMsg = ''" style="background: none; border: none; font-size: 1rem; cursor: pointer; color: #9B1C1C;">✕</button>
    </div>

    <!-- Couriers Table Panel -->
    <div class="glass-panel" style="border: 1px solid #ede4ea; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(45, 5, 28, 0.03);">
      <div style="overflow-x: auto;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
          <thead>
            <tr style="background: #fdfafb; border-bottom: 2px solid #ede4ea; color: #64748b; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">
              <th style="padding: 14px 16px;">Courier Name</th>
              <th style="padding: 14px 16px;">Tracking URL Template</th>
              <th style="padding: 14px 16px;">Admin Contact Details</th>
              <th style="padding: 14px 16px; text-align: center;">Orders</th>
              <th style="padding: 14px 16px; text-align: center;">Status</th>
              <th style="padding: 14px 16px; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" style="border-bottom: 1px solid #f1f5f9;">
              <td colspan="6" style="text-align: center; padding: 3rem; color: #64748b;">
                Loading courier partners...
              </td>
            </tr>
            <tr v-else-if="couriers.length === 0" style="border-bottom: 1px solid #f1f5f9;">
              <td colspan="6" style="text-align: center; padding: 3rem; color: #64748b;">
                No courier partners found matching your search.
              </td>
            </tr>
            <tr v-for="c in couriers" :key="c.id" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#faf8f9'" onmouseout="this.style.background='transparent'">
              <!-- Courier Name & Code -->
              <td style="padding: 14px 16px;">
                <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">{{ c.name }}</div>
                <div style="font-size: 0.75rem; color: #94a3b8; font-family: monospace; margin-top: 2px;">Code: {{ c.code || '—' }}</div>
              </td>

              <!-- Tracking URL Link Template -->
              <td style="padding: 14px 16px; max-width: 320px;">
                <div v-if="c.tracking_page_link" style="display: flex; flex-direction: column; gap: 4px;">
                  <code style="font-size: 0.75rem; color: #6E1F3A; background: #faf0f4; padding: 4px 8px; border-radius: 4px; word-break: break-all; border: 1px solid #f3d7e2;">
                    {{ c.tracking_page_link }}
                  </code>
                  <a 
                    :href="testUrl(c.tracking_page_link)" 
                    target="_blank" 
                    style="font-size: 0.75rem; color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;"
                    title="Test with dummy tracking ID"
                  >
                    <span>Test sample tracking URL ↗</span>
                  </a>
                </div>
                <span v-else style="color: #94a3b8; font-size: 0.8rem;">No tracking link configured</span>
              </td>

              <!-- Admin Internal Contact Details -->
              <td style="padding: 14px 16px;">
                <div v-if="c.contact_person || c.contact_number || c.contact_email">
                  <div v-if="c.contact_person" style="font-weight: 600; color: #334155; font-size: 0.85rem;">{{ c.contact_person }}</div>
                  <div v-if="c.contact_number" style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">
                    📞 <a :href="`tel:${c.contact_number}`" style="color: #64748b; text-decoration: none;">{{ c.contact_number }}</a>
                  </div>
                  <div v-if="c.contact_email" style="font-size: 0.78rem; color: #94a3b8; margin-top: 1px;">
                    ✉️ {{ c.contact_email }}
                  </div>
                </div>
                <span v-else style="color: #cbd5e1; font-size: 0.8rem;">—</span>
              </td>

              <!-- Total Assigned Orders -->
              <td style="padding: 14px 16px; text-align: center;">
                <span class="badge badge--secondary" style="font-size: 0.8rem; font-weight: 600;">
                  {{ c.orders_count || 0 }}
                </span>
              </td>

              <!-- Status Badge -->
              <td style="padding: 14px 16px; text-align: center;">
                <button 
                  type="button"
                  @click="toggleCourierStatus(c)"
                  class="badge" 
                  :class="c.is_active ? 'badge--success' : 'badge--secondary'"
                  style="cursor: pointer; border: none; font-size: 0.75rem; padding: 4px 10px; text-transform: uppercase; font-weight: 700;"
                  :title="c.is_active ? 'Click to deactivate' : 'Click to activate'"
                >
                  {{ c.is_active ? 'Active' : 'Inactive' }}
                </button>
              </td>

              <!-- Action Buttons -->
              <td style="padding: 14px 16px; text-align: right;">
                <div style="display: inline-flex; gap: 8px; justify-content: flex-end;">
                  <button 
                    type="button"
                    class="btn btn--secondary btn--sm" 
                    @click="openEditModal(c)" 
                    style="padding: 4px 10px; font-size: 0.8rem;"
                  >
                    ✏️ Edit
                  </button>
                  <button 
                    type="button"
                    class="btn btn--danger btn--sm" 
                    @click="confirmDelete(c)" 
                    style="padding: 4px 10px; font-size: 0.8rem;"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="meta.total > meta.per_page" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #fdfafb; border-top: 1px solid #ede4ea; font-size: 0.85rem;">
        <span style="color: #64748b;">Showing {{ (meta.current_page - 1) * meta.per_page + 1 }} to {{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }} couriers</span>
        <div style="display: flex; gap: 6px;">
          <button class="btn btn--secondary btn--sm" :disabled="meta.current_page <= 1" @click="fetchCouriers(meta.current_page - 1)">Previous</button>
          <button class="btn btn--secondary btn--sm" :disabled="meta.current_page >= meta.last_page" @click="fetchCouriers(meta.current_page + 1)">Next</button>
        </div>
      </div>
    </div>

    <!-- ADD / EDIT COURIER MODAL -->
    <div v-if="showModal" class="modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;" @click.self="closeModal">
      <div class="modal-content" style="background: #ffffff; width: 100%; max-width: 620px; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border: 1px solid #ede4ea;">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; background: #fdfafb; border-bottom: 1px solid #ede4ea;">
          <div>
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700; color: #1e293b;">
              {{ isEditing ? 'Edit Courier Partner' : 'Add Courier Partner' }}
            </h3>
            <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: #64748b;">
              Configure courier details and tracking page template for order dispatches.
            </p>
          </div>
          <button type="button" @click="closeModal" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #94a3b8;">✕</button>
        </div>

        <!-- Modal Form -->
        <form @submit.prevent="saveCourier" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.1rem; max-height: 80vh; overflow-y: auto;">
          <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem;">
            <!-- Courier Name -->
            <div class="form-group">
              <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 4px; display: block;">
                Courier Partner Name *
              </label>
              <input 
                type="text" 
                v-model="formData.name" 
                required 
                placeholder="e.g. Delhivery, Blue Dart, DTDC" 
                class="form-input" 
                @input="autoGenerateCode"
              />
            </div>

            <!-- Code / Slug -->
            <div class="form-group">
              <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 4px; display: block;">
                Code / Slug
              </label>
              <input 
                type="text" 
                v-model="formData.code" 
                placeholder="e.g. delhivery" 
                class="form-input" 
              />
            </div>
          </div>

          <!-- Tracking Page URL Template -->
          <div class="form-group">
            <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 4px; display: block;">
              Tracking Page URL Link Template
            </label>
            <input 
              type="text" 
              v-model="formData.tracking_page_link" 
              placeholder="e.g. https://www.delhivery.com/track/package/{tracking_number}" 
              class="form-input" 
            />
            <p style="margin: 4px 0 0 0; font-size: 0.76rem; color: #64748b;">
              💡 Use <code>{tracking_number}</code> as placeholder. When dispatching, it will automatically link to the package tracking page.
            </p>
            <div v-if="formData.tracking_page_link" style="margin-top: 6px; font-size: 0.78rem; background: #f8fafc; padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
              <strong>Live Preview:</strong> <span style="color: #6E1F3A;">{{ testUrl(formData.tracking_page_link) }}</span>
            </div>
          </div>

          <!-- Admin Contact Information (Internal Only) -->
          <div style="background: #faf8f9; padding: 12px 14px; border-radius: 8px; border: 1px solid #ede4ea; display: flex; flex-direction: column; gap: 0.85rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 0.85rem; font-weight: 700; color: #6E1F3A;">📞 Admin Logistics Contact (Internal Reference)</span>
              <span style="font-size: 0.72rem; color: #64748b; background: #ffffff; padding: 2px 6px; border-radius: 4px; border: 1px solid #ede4ea;">Not shown to customers</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">
                  Contact Person / Executive
                </label>
                <input 
                  type="text" 
                  v-model="formData.contact_person" 
                  placeholder="e.g. Ramesh (Area Lead)" 
                  class="form-input" 
                  style="background: #ffffff;"
                />
              </div>

              <div class="form-group">
                <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">
                  Phone Number
                </label>
                <input 
                  type="tel" 
                  v-model="formData.contact_number" 
                  placeholder="e.g. +91 98765 43210" 
                  class="form-input" 
                  style="background: #ffffff;"
                />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">
                Support Email
              </label>
              <input 
                type="email" 
                v-model="formData.contact_email" 
                placeholder="e.g. support@courier.com" 
                class="form-input" 
                style="background: #ffffff;"
              />
            </div>
          </div>

          <!-- Sort Order & Active Status -->
          <div style="display: flex; gap: 1.5rem; align-items: center;">
            <div class="form-group" style="width: 120px;">
              <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 4px; display: block;">
                Sort Order
              </label>
              <input 
                type="number" 
                v-model.number="formData.sort_order" 
                class="form-input" 
                min="0"
              />
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-top: 1.25rem;">
              <input 
                type="checkbox" 
                id="courierActiveToggle"
                v-model="formData.is_active" 
                style="width: 18px; height: 18px; cursor: pointer; accent-color: #6E1F3A;"
              />
              <label for="courierActiveToggle" style="font-weight: 600; color: #1e293b; cursor: pointer; font-size: 0.9rem;">
                Active & Available for Dispatch
              </label>
            </div>
          </div>

          <!-- Internal Notes -->
          <div class="form-group">
            <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 4px; display: block;">
              Internal Notes / SLA
            </label>
            <textarea 
              v-model="formData.notes" 
              rows="2" 
              placeholder="e.g. Cut-off time 4 PM. Standard 2-3 day transit time." 
              class="form-input"
            ></textarea>
          </div>

          <!-- Modal Actions -->
          <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 0.5rem; border-top: 1px solid #ede4ea; padding-top: 1rem;">
            <button type="button" class="btn btn--secondary" @click="closeModal" :disabled="saving">
              Cancel
            </button>
            <button type="submit" class="btn btn--primary" :disabled="saving">
              {{ saving ? 'Saving...' : (isEditing ? 'Save Changes' : 'Create Courier') }}
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

const couriers = ref([]);
const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const statusFilter = ref('');
const successMsg = ref('');
const errorMsg = ref('');

const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const formData = ref({
  name: '',
  code: '',
  tracking_page_link: '',
  contact_person: '',
  contact_number: '',
  contact_email: '',
  is_active: true,
  sort_order: 0,
  notes: '',
});

let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchCouriers(1);
  }, 350);
};

const resetFilters = () => {
  searchQuery.value = '';
  statusFilter.value = '';
  fetchCouriers(1);
};

const fetchCouriers = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value || undefined,
      is_active: statusFilter.value !== '' ? statusFilter.value : undefined,
    };
    const response = await axios.get('/api/admin/couriers', { params });
    if (response.data && response.data.success) {
      couriers.value = response.data.data;
      if (response.data.meta) {
        meta.value = response.data.meta;
      }
    }
  } catch (err) {
    console.error('Failed to load couriers:', err);
    errorMsg.value = 'Failed to load courier partners. Please refresh the page.';
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  formData.value = {
    name: '',
    code: '',
    tracking_page_link: '',
    contact_person: '',
    contact_number: '',
    contact_email: '',
    is_active: true,
    sort_order: (couriers.value.length || 0) + 1,
    notes: '',
  };
  showModal.value = true;
};

const openEditModal = (courier) => {
  isEditing.value = true;
  editingId.value = courier.id;
  formData.value = {
    name: courier.name,
    code: courier.code || '',
    tracking_page_link: courier.tracking_page_link || '',
    contact_person: courier.contact_person || '',
    contact_number: courier.contact_number || '',
    contact_email: courier.contact_email || '',
    is_active: !!courier.is_active,
    sort_order: courier.sort_order || 0,
    notes: courier.notes || '',
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const autoGenerateCode = () => {
  if (!isEditing.value && formData.value.name) {
    formData.value.code = formData.value.name
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '_')
      .replace(/^_+|_+$/g, '');
  }
};

const testUrl = (template) => {
  if (!template) return '#';
  return template.replace(/\{tracking_number\}|\{tracking_id\}|\{awb\}/g, 'SAMPLE123456');
};

const saveCourier = async () => {
  saving.value = true;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    if (isEditing.value) {
      const response = await axios.put(`/api/admin/couriers/${editingId.value}`, formData.value);
      if (response.data && response.data.success) {
        successMsg.value = '✓ Courier partner updated successfully!';
        closeModal();
        await fetchCouriers(meta.value.current_page);
      }
    } else {
      const response = await axios.post('/api/admin/couriers', formData.value);
      if (response.data && response.data.success) {
        successMsg.value = '✓ Courier partner created successfully!';
        closeModal();
        await fetchCouriers(1);
      }
    }
  } catch (err) {
    console.error('Failed to save courier:', err);
    errorMsg.value = err.response?.data?.message || 'Failed to save courier details.';
  } finally {
    saving.value = false;
  }
};

const toggleCourierStatus = async (courier) => {
  try {
    const response = await axios.patch(`/api/admin/couriers/${courier.id}/toggle`);
    if (response.data && response.data.success) {
      courier.is_active = !courier.is_active;
      successMsg.value = `✓ ${courier.name} status updated to ${courier.is_active ? 'Active' : 'Inactive'}.`;
    }
  } catch (err) {
    console.error('Failed to toggle status:', err);
    errorMsg.value = 'Failed to update courier status.';
  }
};

const confirmDelete = async (courier) => {
  if (confirm(`Are you sure you want to delete courier partner "${courier.name}"?`)) {
    try {
      const response = await axios.delete(`/api/admin/couriers/${courier.id}`);
      if (response.data && response.data.success) {
        successMsg.value = `✓ Courier partner "${courier.name}" deleted successfully.`;
        await fetchCouriers(meta.value.current_page);
      }
    } catch (err) {
      console.error('Failed to delete courier:', err);
      errorMsg.value = 'Failed to delete courier partner.';
    }
  }
};

onMounted(() => {
  fetchCouriers(1);
});
</script>

<style scoped>
.courier-list-view {
  padding: var(--spacing-lg);
}

.page-title {
  font-family: 'Playfair Display', serif;
}

.admin-table th,
.admin-table td {
  border-bottom: 1px solid #f1f5f9;
}
</style>
