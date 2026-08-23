<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title" style="font-family: 'Playfair Display', serif; color: #6E1F3A;">Stock Inward (Goods Receipt)</h1>
      <span class="admin-page__subtitle" style="font-family: 'Poppins', sans-serif;">Record factory stock arrivals by category and increment product variant inventory.</span>
    </div>
    <div class="admin-page__actions">
      <router-link 
        to="/admin/inward/create" 
        class="btn btn--primary"
        style="background: #6E1F3A; color: #ffffff; padding: 0.65rem 1.25rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(110, 31, 58, 0.2);"
      >
        ➕ New Stock Inward
      </router-link>
    </div>
  </div>

  <!-- Search & Filter Bar -->
  <div class="glass-panel" style="padding: 16px 20px; margin-top: var(--spacing-md); margin-bottom: var(--spacing-lg); background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
    <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
      <!-- Search Input -->
      <div style="flex: 1; min-width: 240px; position: relative;">
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search by Inward #, factory, reference or notes..." 
          class="form-input" 
          style="padding-left: 2.25rem; width: 100%; font-family: 'Poppins', sans-serif;" 
        />
        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;">🔍</span>
      </div>

      <!-- Category Filter -->
      <div style="min-width: 180px;">
        <select 
          v-model="filters.category_id" 
          @change="fetchInwards(1)" 
          class="form-input" 
          style="width: 100%; padding: 6px 10px; font-size: 0.82rem;"
        >
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">📁 {{ cat.name }}</option>
        </select>
      </div>

      <!-- Factory Filter -->
      <div style="min-width: 180px;">
        <select 
          v-model="filters.factory_id" 
          @change="fetchInwards(1)" 
          class="form-input" 
          style="width: 100%; padding: 6px 10px; font-size: 0.82rem;"
        >
          <option value="">All Factories</option>
          <option v-for="fac in factories" :key="fac.id" :value="fac.id">🏭 {{ fac.name }}</option>
        </select>
      </div>

      <!-- Date Filters -->
      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 6px;">
          <label style="font-size: 0.8rem; color: #64748b; font-weight: 500;">From:</label>
          <input 
            type="date" 
            v-model="filters.date_from" 
            @change="fetchInwards(1)" 
            class="form-input" 
            style="padding: 6px 10px; font-size: 0.82rem;"
          />
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
          <label style="font-size: 0.8rem; color: #64748b; font-weight: 500;">To:</label>
          <input 
            type="date" 
            v-model="filters.date_to" 
            @change="fetchInwards(1)" 
            class="form-input" 
            style="padding: 6px 10px; font-size: 0.82rem;"
          />
        </div>

        <button 
          v-if="filters.search || filters.category_id || filters.factory_id || filters.date_from || filters.date_to" 
          @click="clearFilters" 
          class="btn btn--secondary btn--sm" 
          style="padding: 6px 12px; font-size: 0.82rem;"
        >
          Clear
        </button>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value" style="font-size: 1.2rem; color: #6E1F3A;">⏳ Loading stock inward records...</div>
  </div>

  <!-- Inwards Table Panel -->
  <div v-else class="glass-panel" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="inward in inwards" :key="inward.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <div>
              <div class="mdc-title" style="font-family: monospace; font-weight: 700; color: #6E1F3A;">
                {{ inward.inward_number }}
              </div>
              <div class="mdc-date" style="font-size: 0.78rem; color: #64748b;">
                📅 {{ formatDate(inward.inward_date) }}
              </div>
            </div>
            <span class="badge badge--success" style="font-size: 0.7rem;">
              +{{ inward.total_quantity }} pcs
            </span>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name" style="font-weight: 600;">
              🏭 {{ inward.factory?.name || inward.supplier_name || 'Direct Factory Production' }}
            </span>
            <span v-if="inward.category" style="font-size: 0.75rem; color: #6E1F3A; display: block; font-weight: 500;">
              📁 {{ inward.category.name }}
            </span>
            <span v-if="inward.reference_no" style="font-size: 0.75rem; color: #94a3b8; display: block;">
              Ref: {{ inward.reference_no }}
            </span>
          </div>
          <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between; font-size: 0.8rem;">
            <span>Items: <strong>{{ inward.total_items }} SKUs</strong></span>
            <span>By: <strong>{{ inward.creator?.name || 'Staff' }}</strong></span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges"></div>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn--secondary btn--sm" @click="viewDetails(inward)">
              👁️ Details
            </button>
            <button class="btn btn--danger btn--sm" @click="deleteInward(inward.id)">
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
          <th style="padding: 14px 20px; font-weight: 600; text-align: left; width: 18%;">Inward # & Ref</th>
          <th style="padding: 14px 16px; font-weight: 600; text-align: left; width: 12%;">Inward Date</th>
          <th style="padding: 14px 16px; font-weight: 600; text-align: left; width: 16%;">Category</th>
          <th style="padding: 14px 16px; font-weight: 600; text-align: left; width: 22%;">Factory / Supplier</th>
          <th style="padding: 14px 16px; font-weight: 600; text-align: center; width: 12%;">Total Quantity</th>
          <th style="padding: 14px 16px; font-weight: 600; text-align: left; width: 10%;">Created By</th>
          <th style="padding: 14px 20px; font-weight: 600; text-align: right; width: 10%;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr 
          v-for="inward in inwards" 
          :key="inward.id"
          style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;"
          onmouseover="this.style.background='#FDFBF7'"
          onmouseout="this.style.background='transparent'"
        >
          <!-- Inward Number & Ref -->
          <td style="padding: 16px 20px;">
            <div style="display: flex; flex-direction: column; gap: 2px;">
              <span style="font-weight: 700; color: #6E1F3A; font-family: monospace; font-size: 0.92rem;">
                {{ inward.inward_number }}
              </span>
              <span v-if="inward.reference_no" style="font-size: 0.75rem; color: #94a3b8;">
                Ref: {{ inward.reference_no }}
              </span>
            </div>
          </td>

          <!-- Inward Date -->
          <td style="padding: 16px 16px; color: #1e293b; font-size: 0.88rem; font-weight: 500;">
            {{ formatDate(inward.inward_date) }}
          </td>

          <!-- Category -->
          <td style="padding: 16px 16px;">
            <span v-if="inward.category" class="badge badge--secondary" style="font-size: 0.78rem;">
              📁 {{ inward.category.name }}
            </span>
            <span v-else style="color: #94a3b8; font-size: 0.82rem;">
              All Categories
            </span>
          </td>

          <!-- Factory / Supplier -->
          <td style="padding: 16px 16px;">
            <div style="display: flex; flex-direction: column;">
              <span style="font-weight: 600; color: #1e293b; font-size: 0.88rem;">
                🏭 {{ inward.factory?.name || inward.supplier_name || 'Direct Production' }}
              </span>
              <span v-if="inward.notes" style="font-size: 0.75rem; color: #64748b; margin-top: 2px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ inward.notes }}
              </span>
            </div>
          </td>

          <!-- Total Quantity & Items -->
          <td style="padding: 16px 16px; text-align: center;">
            <div style="display: inline-flex; flex-direction: column; align-items: center;">
              <span class="badge badge--success" style="font-size: 0.82rem; padding: 4px 10px; font-weight: 700;">
                +{{ inward.total_quantity }} pcs
              </span>
              <span style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
                {{ inward.total_items }} SKUs
              </span>
            </div>
          </td>

          <!-- Created By -->
          <td style="padding: 16px 16px; color: #475569; font-size: 0.85rem;">
            {{ inward.creator?.name || 'Staff' }}
          </td>

          <!-- Actions -->
          <td style="padding: 16px 20px; text-align: right;">
            <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
              <button 
                class="btn btn--secondary btn--sm" 
                @click="viewDetails(inward)"
                title="View Inward Line Items"
                style="padding: 6px 10px; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center;"
              >
                👁️
              </button>

              <button 
                class="btn btn--danger btn--sm" 
                @click="deleteInward(inward.id)"
                title="Delete Inward Record"
                style="padding: 6px 10px; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center;"
              >
                🗑️
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="inwards.length === 0">
          <td colspan="7" style="text-align: center; padding: 4rem; color: #94a3b8;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📦</div>
            <div>No stock inward shipments recorded matching your filters.</div>
            <router-link 
              to="/admin/inward/create" 
              class="btn btn--primary btn--sm" 
              style="margin-top: 1rem; display: inline-block;"
            >
              ➕ Record First Stock Inward
            </router-link>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #faf8f5;">
      <span style="font-size: 0.82rem; color: #64748b;">
        Showing page {{ pagination.current_page }} of {{ pagination.last_page }} ({{ pagination.total }} records)
      </span>
      <div style="display: flex; gap: 8px;">
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === 1"
          @click="fetchInwards(pagination.current_page - 1)"
        >
          Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchInwards(pagination.current_page + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>

  <!-- Inward Details Modal Dialog -->
  <div 
    v-if="selectedInward" 
    class="modal-backdrop" 
    style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; display: flex; align-items: center; justify-content: center; padding: 20px;"
  >
    <div 
      class="modal-content" 
      style="background: #ffffff; border-radius: 12px; max-width: 820px; width: 100%; max-height: calc(100vh - 40px); display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);"
    >
      <!-- Modal Header -->
      <div style="padding: 16px 24px; border-bottom: 1px solid #e2e8f0; background: #FAF8F5; display: flex; justify-content: space-between; align-items: center;">
        <div>
          <h3 style="margin: 0; font-family: 'Playfair Display', serif; color: #6E1F3A; font-size: 1.25rem;">
            Stock Inward #{{ selectedInward.inward_number }}
          </h3>
          <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">
            📅 {{ formatDate(selectedInward.inward_date) }} | 
            🏭 Factory: {{ selectedInward.factory?.name || selectedInward.supplier_name || 'Direct Production' }}
            <span v-if="selectedInward.category"> | 📁 Category: {{ selectedInward.category.name }}</span>
          </div>
        </div>
        <button 
          type="button" 
          @click="selectedInward = null" 
          style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1;"
        >
          ✕
        </button>
      </div>

      <!-- Modal Body (Scrollable) -->
      <div style="padding: 20px 24px; overflow-y: auto; scrollbar-width: thin; flex: 1;">
        <div v-if="selectedInward.notes" style="padding: 10px 14px; background: #f8fafc; border-radius: 8px; font-size: 0.85rem; color: #475569; margin-bottom: 16px;">
          <strong>Notes:</strong> {{ selectedInward.notes }}
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
          <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 0.75rem; text-transform: uppercase;">
              <th style="padding: 8px 12px; text-align: left;">Product</th>
              <th style="padding: 8px 10px; text-align: center;">Color</th>
              <th style="padding: 8px 10px; text-align: center;">Size Included</th>
              <th style="padding: 8px 10px; text-align: center;">SKU</th>
              <th style="padding: 8px 12px; text-align: right;">Quantity Added</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="item in selectedInward.items" 
              :key="item.id"
              style="border-bottom: 1px solid #f1f5f9;"
            >
              <td style="padding: 10px 12px; font-weight: 600; color: #1e293b;">
                {{ item.product?.name || 'Product #' + item.product_id }}
              </td>
              <td style="padding: 10px 10px; text-align: center; color: #475569;">
                {{ item.color || '—' }}
              </td>
              <td style="padding: 10px 10px; text-align: center; font-weight: 600; color: #6E1F3A;">
                {{ item.size }}
              </td>
              <td style="padding: 10px 10px; text-align: center; font-family: monospace; font-size: 0.78rem; color: #94a3b8;">
                {{ item.sku || '—' }}
              </td>
              <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #16a34a;">
                +{{ item.quantity }} pcs
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr style="background: #FAF8F5; font-weight: 700; border-top: 2px solid #e2e8f0;">
              <td colspan="4" style="padding: 10px 12px; text-align: right;">Total Quantity Added:</td>
              <td style="padding: 10px 12px; text-align: right; color: #6E1F3A; font-size: 1rem;">
                +{{ selectedInward.total_quantity }} pcs
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Modal Footer -->
      <div style="padding: 14px 24px; border-top: 1px solid #e2e8f0; background: #FAF8F5; display: flex; justify-content: flex-end;">
        <button 
          type="button" 
          class="btn btn--secondary" 
          @click="selectedInward = null"
          style="padding: 6px 18px;"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const inwards = ref([]);
const categories = ref([]);
const factories = ref([]);
const loading = ref(true);
const selectedInward = ref(null);

const filters = ref({
  search: '',
  category_id: '',
  factory_id: '',
  date_from: '',
  date_to: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

let searchDebounceTimer = null;
const debounceSearch = () => {
  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    fetchInwards(1);
  }, 350);
};

const fetchInwards = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: filters.value.search || undefined,
      category_id: filters.value.category_id || undefined,
      factory_id: filters.value.factory_id || undefined,
      date_from: filters.value.date_from || undefined,
      date_to: filters.value.date_to || undefined,
    };

    const response = await axios.get('/api/admin/inward', { params });
    if (response.data && response.data.success) {
      inwards.value = response.data.data;
      if (response.data.meta) {
        pagination.value = response.data.meta;
      }
    }
  } catch (err) {
    console.error('Failed to load stock inwards:', err);
  } finally {
    loading.value = false;
  }
};

const fetchFilterMetadata = async () => {
  try {
    const res = await axios.get('/api/admin/inward/form-data');
    if (res.data && res.data.success) {
      categories.value = res.data.data.categories || [];
      factories.value = res.data.data.factories || [];
    }
  } catch (err) {
    console.error('Failed to load filter metadata:', err);
  }
};

const clearFilters = () => {
  filters.value = {
    search: '',
    category_id: '',
    factory_id: '',
    date_from: '',
    date_to: '',
  };
  fetchInwards(1);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const viewDetails = async (inward) => {
  try {
    const res = await axios.get(`/api/admin/inward/${inward.id}`);
    if (res.data && res.data.success) {
      selectedInward.value = res.data.data;
    }
  } catch (err) {
    console.error('Failed to fetch inward details:', err);
    selectedInward.value = inward;
  }
};

const deleteInward = async (id) => {
  if (!confirm('Are you sure you want to delete this stock inward record?')) {
    return;
  }

  try {
    const response = await axios.delete(`/api/admin/inward/${id}`);
    if (response.data && response.data.success) {
      await fetchInwards(pagination.value.current_page);
    }
  } catch (err) {
    console.error('Failed to delete inward:', err);
    alert(err.response?.data?.message || 'Failed to delete inward record');
  }
};

onMounted(() => {
  fetchFilterMetadata();
  fetchInwards(1);
});
</script>
