<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Stock Control & Ledger</h1>
      <span class="admin-page__subtitle">Monitor stock levels, execute adjustments, and view double-entry audits.</span>
    </div>
  </div>

  <!-- Tabs Navigation -->
  <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
    <button 
      class="btn" 
      :class="activeView === 'stock' ? 'btn--primary' : 'btn--secondary'"
      @click="setView('stock')"
    >
      📦 Current Stock Levels
    </button>
    <button 
      class="btn" 
      :class="activeView === 'ledger' ? 'btn--primary' : 'btn--secondary'"
      @click="setView('ledger')"
    >
      📜 Audit Ledger History
    </button>
  </div>

  <!-- Tab 1: Current Stock Levels -->
  <div v-show="activeView === 'stock'">
    <!-- Filters panel -->
    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">Search</label>
          <input 
            type="text" 
            v-model="stockFilters.search" 
            class="form-input" 
            placeholder="Search product, SKU, barcode..." 
            @input="debouncedStockSearch" 
          />
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">Category</label>
          <select v-model="stockFilters.category_id" class="form-input" @change="applyStockFilters">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">Stock Status</label>
          <select v-model="stockFilters.status" class="form-input" @change="applyStockFilters">
            <option value="">All Levels</option>
            <option value="ok">Ok Stock</option>
            <option value="low">Low Stock Only</option>
            <option value="out_of_stock">Out of Stock Only</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
      ⚠️ {{ errorMsg }}
    </div>

    <!-- Variants Table -->
    <div class="glass-panel" style="overflow: hidden;">
      
      <!-- Mobile Cards View -->
      <div class="mobile-data-list">
        <div class="mobile-data-card" v-for="v in variants" :key="v.id">
          <div class="mdc-header">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
              <div>
                <div class="mdc-title">{{ v.product?.name }}</div>
                <div class="mdc-date">{{ v.sku }}</div>
              </div>
            </div>
          </div>
          
          <div class="mdc-body">
            <div class="mdc-customer">
              <span class="mdc-name">{{ v.product?.category?.name }}</span>
              <span class="mdc-email">
                <span v-if="v.size" class="badge badge--secondary" style="font-size: 0.75rem; margin-right: 4px;">Size: {{ v.size }}</span>
                <span v-if="v.color" class="badge badge--secondary" style="font-size: 0.75rem;">Color: {{ v.color }}</span>
              </span>
            </div>
            <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between;">
              <span>Stock: <strong>{{ v.stock_quantity }}</strong> (Res: {{ v.reserved_quantity }})</span>
              <span>Low: <strong>{{ v.low_stock_threshold }}</strong></span>
            </div>
          </div>
          
          <div class="mdc-footer">
            <div class="mdc-badges">
              <span :class="['badge', getStockStatusClass(v)]">
                {{ getStockStatusLabel(v) }}
              </span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn--primary btn--sm" @click="openAdjustModal(v)">⚙️ Adjust</button>
            </div>
          </div>
        </div>
        
        <div v-if="variants.length === 0 && !inventoryStore.loading" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
          No variant stock records found.
        </div>
      </div>

      <!-- Desktop Table View -->
      <table class="data-table desktop-data-table">
        <thead>
          <tr>
            <th>Product Name</th>
            <th>SKU</th>
            <th>Size / Color</th>
            <th style="text-align: right;">Quantity</th>
            <th style="text-align: right;">Reserved</th>
            <th style="text-align: right;">Threshold</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="v in variants" :key="v.id">
            <td>
              <div style="font-weight: 600; color: #1e293b;">{{ v.product?.name }}</div>
              <div style="font-size: 0.8rem; color: var(--color-text-muted);">{{ v.product?.category?.name }}</div>
            </td>
            <td><code>{{ v.sku }}</code></td>
            <td>
              <span v-if="v.size" class="badge badge--secondary" style="font-size: 0.75rem; margin-right: 4px;">Size: {{ v.size }}</span>
              <span v-if="v.color" class="badge badge--secondary" style="font-size: 0.75rem;">Color: {{ v.color }}</span>
            </td>
            <td style="text-align: right; font-weight: 700; color: #1e293b;">
              {{ v.stock_quantity }}
            </td>
            <td style="text-align: right; color: var(--color-text-secondary);">
              {{ v.reserved_quantity }}
            </td>
            <td style="text-align: right; color: var(--color-text-muted);">
              {{ v.low_stock_threshold }}
            </td>
            <td>
              <span :class="['badge', getStockStatusClass(v)]">
                {{ getStockStatusLabel(v) }}
              </span>
            </td>
            <td style="text-align: right;">
              <button class="btn btn--primary btn--sm" @click="openAdjustModal(v)">
                ⚙️ Adjust
              </button>
            </td>
          </tr>
          <tr v-if="variants.length === 0 && !inventoryStore.loading">
            <td colspan="8" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
              No variant stock records found.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem var(--spacing-lg); border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.1);">
        <div style="font-size: 0.85rem; color: var(--color-text-muted);">
          Showing variant <strong>{{ pagination.current_page }}</strong> of <strong>{{ pagination.last_page }}</strong> (Total: {{ pagination.total }} items)
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <button class="btn btn--secondary btn--sm" :disabled="pagination.current_page === 1" @click="changeStockPage(pagination.current_page - 1)">
            ◀️ Prev
          </button>
          <button class="btn btn--secondary btn--sm" :disabled="pagination.current_page === pagination.last_page" @click="changeStockPage(pagination.current_page + 1)">
            Next ▶️
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Tab 2: Ledger History -->
  <div v-show="activeView === 'ledger'">
    <!-- Filters panel -->
    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">Type</label>
          <select v-model="ledgerFilters.type" class="form-input" @change="applyLedgerFilters">
            <option value="">All Types</option>
            <option value="PURCHASE">PURCHASE</option>
            <option value="SALE">SALE</option>
            <option value="RETURN">RETURN</option>
            <option value="DAMAGE">DAMAGE</option>
            <option value="ADJUSTMENT">ADJUSTMENT</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">Direction</label>
          <select v-model="ledgerFilters.direction" class="form-input" @change="applyLedgerFilters">
            <option value="">All Directions</option>
            <option value="IN">IN (Additions)</option>
            <option value="OUT">OUT (Deductions)</option>
          </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">From Date</label>
          <input type="date" v-model="ledgerFilters.date_from" class="form-input" @change="applyLedgerFilters" />
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label">To Date</label>
          <input type="date" v-model="ledgerFilters.date_to" class="form-input" @change="applyLedgerFilters" />
        </div>
      </div>
    </div>

    <!-- Ledger Table -->
    <div class="glass-panel" style="overflow: hidden;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Product SKU</th>
            <th>Type</th>
            <th>Dir</th>
            <th style="text-align: right;">Qty</th>
            <th style="text-align: right;">Stock Before</th>
            <th style="text-align: right;">Stock After</th>
            <th>Reference</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in ledger" :key="log.id">
            <td style="font-size: 0.8rem; color: var(--color-text-secondary);">
              {{ formatDate(log.created_at) }}
            </td>
            <td>
              <div style="font-weight: 500; color: #1e293b;">{{ log.variant?.sku }}</div>
              <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ log.variant?.product?.name }}</div>
            </td>
            <td>
              <span class="badge" :class="getLedgerTypeClass(log.type)">
                {{ log.type }}
              </span>
            </td>
            <td>
              <span :style="{ color: log.direction === 'IN' ? 'var(--color-success)' : 'var(--color-danger)', fontWeight: 'bold' }">
                {{ log.direction }}
              </span>
            </td>
            <td style="text-align: right; font-weight: 600; color: #1e293b;">{{ log.quantity }}</td>
            <td style="text-align: right; color: var(--color-text-muted);">{{ log.stock_before }}</td>
            <td style="text-align: right; font-weight: 500; color: #1e293b;">{{ log.stock_after }}</td>
            <td style="font-size: 0.8rem; color: var(--color-text-secondary);">
              {{ log.reference_type ? log.reference_type.split('\\').pop() + ' #' + log.reference_id : '—' }}
            </td>
            <td style="font-size: 0.8rem; color: var(--color-text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" :title="log.notes">
              {{ log.notes || '—' }}
            </td>
          </tr>
          <tr v-if="ledger.length === 0 && !inventoryStore.loading">
            <td colspan="9" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
              No stock movements recorded.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Ledger Pagination -->
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem var(--spacing-lg); border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.1);">
        <div style="font-size: 0.85rem; color: var(--color-text-muted);">
          Showing records <strong>{{ ledgerPagination.current_page }}</strong> of <strong>{{ ledgerPagination.last_page }}</strong> (Total: {{ ledgerPagination.total }} logs)
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <button class="btn btn--secondary btn--sm" :disabled="ledgerPagination.current_page === 1" @click="changeLedgerPage(ledgerPagination.current_page - 1)">
            ◀️ Prev
          </button>
          <button class="btn btn--secondary btn--sm" :disabled="ledgerPagination.current_page === ledgerPagination.last_page" @click="changeLedgerPage(ledgerPagination.current_page + 1)">
            Next ▶️
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Stock Adjustment Modal -->
  <div v-if="showAdjustModal" class="modal-overlay" @click.self="closeAdjustModal">
    <div class="modal-container" style="max-width: 500px;">
      <div class="modal-header">
        <h3 class="modal-title">Manual Stock Adjustment</h3>
        <button class="modal-close" @click="closeAdjustModal">&times;</button>
      </div>
      <form @submit.prevent="submitAdjustment">
        <div class="modal-body">
          <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--color-border); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
            <div style="font-size: 0.8rem; color: var(--color-text-secondary); margin-bottom: 0.25rem;">PRODUCT SKU</div>
            <div style="font-weight: bold; color: #1e293b; font-size: 1rem;">{{ selectedVariant?.sku }}</div>
            <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0.25rem;">{{ selectedVariant?.product?.name }}</div>
            <div style="font-size: 0.85rem; color: var(--color-text-primary); margin-top: 0.5rem;">
              Current Stock Level: <strong>{{ selectedVariant?.stock_quantity }}</strong> units
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Adjustment Type</label>
            <select v-model="adjustForm.type" class="form-input" required>
              <option value="adjustment_in">Adjustment In (Stock Increase)</option>
              <option value="adjustment_out">Adjustment Out (Stock Deduction)</option>
              <option value="damage">Damage Write-off</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Adjustment Quantity</label>
            <input type="number" v-model.number="adjustForm.quantity" class="form-input" min="1" required />
          </div>

          <div class="form-group">
            <label class="form-label">Reason for Adjustment</label>
            <input type="text" v-model="adjustForm.reason" class="form-input" placeholder="e.g. Supplier sample, stock take correction..." required />
          </div>

          <div class="form-group">
            <label class="form-label">Additional Notes</label>
            <textarea v-model="adjustForm.notes" class="form-textarea" rows="3" placeholder="Notes..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn--secondary" @click="closeAdjustModal" :disabled="submitting">
            Cancel
          </button>
          <button type="submit" class="btn btn--primary" :disabled="submitting">
            {{ submitting ? 'Updating Stock...' : 'Apply Stock Change' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useInventoryStore } from '../../stores/inventory';
import { useCategoryStore } from '../../stores/category';

const inventoryStore = useInventoryStore();
const categoryStore = useCategoryStore();

const activeView = ref('stock');
const errorMsg = ref(null);
const showAdjustModal = ref(false);
const submitting = ref(false);
const selectedVariant = ref(null);

const adjustForm = ref({
  product_variant_id: null,
  type: 'adjustment_in',
  quantity: 1,
  reason: '',
  notes: '',
});

const stockFilters = ref({
  search: '',
  category_id: '',
  status: '',
  page: 1,
});

const ledgerFilters = ref({
  type: '',
  direction: '',
  date_from: '',
  date_to: '',
  page: 1,
});

const variants = computed(() => inventoryStore.variants);
const pagination = computed(() => inventoryStore.pagination);
const ledger = computed(() => inventoryStore.ledger);
const ledgerPagination = computed(() => inventoryStore.ledgerPagination);
const categories = computed(() => categoryStore.categories);

// Debouncing search inputs
let searchTimeout = null;
function debouncedStockSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    stockFilters.value.page = 1;
    applyStockFilters();
  }, 400);
}

function applyStockFilters() {
  inventoryStore.fetchVariants(stockFilters.value);
}

function applyLedgerFilters() {
  inventoryStore.fetchLedger(ledgerFilters.value);
}

function setView(view) {
  activeView.value = view;
  if (view === 'stock') {
    applyStockFilters();
  } else {
    applyLedgerFilters();
  }
}

function changeStockPage(page) {
  stockFilters.value.page = page;
  applyStockFilters();
}

function changeLedgerPage(page) {
  ledgerFilters.value.page = page;
  applyLedgerFilters();
}

function getStockStatusClass(v) {
  if (v.stock_quantity === 0) return 'badge--danger';
  if (v.stock_quantity <= v.low_stock_threshold) return 'badge--warning';
  return 'badge--success';
}

function getStockStatusLabel(v) {
  if (v.stock_quantity === 0) return 'Out of Stock';
  if (v.stock_quantity <= v.low_stock_threshold) return 'Low Stock';
  return 'OK Stock';
}

function getLedgerTypeClass(type) {
  switch (type) {
    case 'PURCHASE': return 'badge--success';
    case 'SALE': return 'badge--secondary';
    case 'DAMAGE': return 'badge--danger';
    case 'ADJUSTMENT': return 'badge--warning';
    default: return 'badge--secondary';
  }
}

function formatDate(dtStr) {
  if (!dtStr) return '';
  const date = new Date(dtStr);
  return date.toLocaleString();
}

function openAdjustModal(v) {
  selectedVariant.value = v;
  adjustForm.value = {
    product_variant_id: v.id,
    type: 'adjustment_in',
    quantity: 1,
    reason: '',
    notes: '',
  };
  errorMsg.value = null;
  showAdjustModal.value = true;
}

function closeAdjustModal() {
  showAdjustModal.value = false;
}

async function submitAdjustment() {
  submitting.value = true;
  errorMsg.value = null;
  try {
    await inventoryStore.adjustStock(adjustForm.value);
    showAdjustModal.value = false;
    applyStockFilters();
  } catch (err) {
    errorMsg.value = err.message || 'Stock adjustment failed.';
  } finally {
    submitting.value = false;
  }
}

onMounted(() => {
  categoryStore.fetchCategories();
  applyStockFilters();
});
</script>
