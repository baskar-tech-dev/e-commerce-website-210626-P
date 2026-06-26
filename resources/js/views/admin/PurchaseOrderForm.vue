<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">{{ pageTitle }}</h1>
      <span class="admin-page__subtitle">{{ pageSubtitle }}</span>
    </div>
    <router-link to="/admin/purchase-orders" class="btn btn--secondary" style="text-decoration: none;">
      ◀️ Back to POs
    </router-link>
  </div>

  <!-- Error Alerts -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1.5rem; padding: 1rem; width: 100%; border-radius: 8px; font-size: 0.95rem; display: block; text-align: left;">
    <div style="font-weight: bold; margin-bottom: 0.25rem;">⚠️ Operation failed:</div>
    <div v-if="validationErrors.length > 0">
      <ul style="margin-left: 1.25rem;">
        <li v-for="(err, idx) in validationErrors" :key="idx">{{ err }}</li>
      </ul>
    </div>
    <div v-else>{{ errorMsg }}</div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
    <!-- PO Form Panel -->
    <form @submit.prevent="submitForm">
      <div class="glass-panel" style="padding: 2rem;">
        
        <!-- PO Header Info -->
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; color: #fff;">
          Purchase Order Summary
          <span v-if="form.po_number" style="color: var(--color-text-secondary); float: right; font-size: 0.95rem; font-family: monospace;">
            {{ form.po_number }}
          </span>
        </h3>

        <div class="grid-3">
          <div class="form-group">
            <label class="form-label">Supplier Name *</label>
            <input 
              type="text" 
              v-model="form.supplier_name" 
              required 
              class="form-input" 
              :disabled="isReadOnly" 
              placeholder="e.g. Acme Textiles" 
            />
          </div>

          <div class="form-group">
            <label class="form-label">Supplier Contact (Email/Phone)</label>
            <input 
              type="text" 
              v-model="form.supplier_contact" 
              class="form-input" 
              :disabled="isReadOnly" 
              placeholder="e.g. orders@acme.com" 
            />
          </div>

          <div class="form-group">
            <label class="form-label">PO Status</label>
            <div style="padding: 0.5rem 0;">
              <span :class="['badge', getPOStatusClass(form.status)]">
                {{ form.status.toUpperCase() }}
              </span>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Operator Notes</label>
          <textarea 
            v-model="form.notes" 
            class="form-textarea" 
            rows="2" 
            :disabled="isReadOnly" 
            placeholder="Delivery requests, invoice refs..."
          ></textarea>
        </div>

        <!-- Ordered Items Section -->
        <div style="margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem;">
          
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="color: #fff; font-size: 1.15rem; margin: 0;">Ordered Items</h3>
            
            <button 
              v-if="isCreateOrDraft && !isReceiveMode"
              type="button" 
              class="btn btn--secondary btn--sm" 
              @click="addItemRow"
            >
              ➕ Add Item
            </button>
          </div>

          <div v-if="form.items.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
            No items added. Add at least one variant item.
          </div>

          <!-- Items Table -->
          <table v-if="form.items.length > 0" class="data-table">
            <thead>
              <tr v-if="!isReceiveMode">
                <th>Product Variant SKU</th>
                <th style="width: 150px; text-align: right;">Quantity Ordered</th>
                <th style="width: 150px; text-align: right;">Unit Cost (₹)</th>
                <th style="width: 150px; text-align: right;">Total Cost (₹)</th>
                <th v-if="isCreateOrDraft" style="width: 80px; text-align: right;">Remove</th>
              </tr>
              <tr v-else>
                <th>Product Variant SKU</th>
                <th style="width: 150px; text-align: right;">Ordered</th>
                <th style="width: 150px; text-align: right;">Previously Received</th>
                <th style="width: 180px; text-align: right;">Receive Qty Now</th>
                <th style="width: 150px; text-align: right;">Total Received</th>
              </tr>
            </thead>
            <tbody>
              <!-- Edit/Create/View Item Rows -->
              <tr v-if="!isReceiveMode" v-for="(item, index) in form.items" :key="index">
                <td>
                  <select 
                    v-if="isCreateOrDraft"
                    v-model="item.product_variant_id" 
                    required 
                    class="form-input"
                  >
                    <option value="">Select Variant SKU</option>
                    <option v-for="v in allVariants" :key="v.id" :value="v.id">
                      {{ v.sku }} ({{ v.product?.name }} - {{ v.size || 'No Size' }} {{ v.color || '' }})
                    </option>
                  </select>
                  <span v-else style="color: #fff; font-weight: 500;">
                    {{ item.variant?.sku || 'Variant ID: ' + item.product_variant_id }}
                    <span style="display: block; font-size: 0.8rem; color: var(--color-text-secondary); font-weight: normal;">
                      {{ item.variant?.product?.name }}
                    </span>
                  </span>
                </td>
                <td style="text-align: right;">
                  <input 
                    v-if="isCreateOrDraft"
                    type="number" 
                    v-model.number="item.quantity_ordered" 
                    min="1" 
                    required 
                    class="form-input" 
                    style="text-align: right; width: 100px; display: inline-block;" 
                  />
                  <span v-else>{{ item.quantity_ordered }}</span>
                </td>
                <td style="text-align: right;">
                  <input 
                    v-if="isCreateOrDraft"
                    type="number" 
                    step="0.01" 
                    v-model.number="item.unit_cost" 
                    min="0" 
                    required 
                    class="form-input" 
                    style="text-align: right; width: 100px; display: inline-block;" 
                  />
                  <span v-else>₹{{ parseFloat(item.unit_cost).toFixed(2) }}</span>
                </td>
                <td style="text-align: right; font-weight: 600; color: #fff;">
                  ₹{{ getLineTotal(item).toFixed(2) }}
                </td>
                <td v-if="isCreateOrDraft" style="text-align: right;">
                  <button type="button" class="btn btn--danger btn--sm" @click="removeItemRow(index)" style="padding: 0.25rem 0.5rem;">
                    ✕
                  </button>
                </td>
              </tr>

              <!-- Receive Items Rows -->
              <tr v-if="isReceiveMode" v-for="item in receiveItems" :key="item.id">
                <td>
                  <span style="color: #fff; font-weight: 500;">
                    {{ item.variant?.sku }}
                  </span>
                  <span style="display: block; font-size: 0.8rem; color: var(--color-text-secondary);">
                    {{ item.variant?.product?.name }}
                  </span>
                </td>
                <td style="text-align: right;">{{ item.quantity_ordered }}</td>
                <td style="text-align: right; color: var(--color-text-secondary);">{{ item.quantity_received }}</td>
                <td style="text-align: right;">
                  <input 
                    type="number" 
                    v-model.number="item.qty_now" 
                    min="0" 
                    class="form-input" 
                    style="text-align: right; width: 100px; display: inline-block;" 
                  />
                </td>
                <td style="text-align: right; font-weight: 600; color: #fff;">
                  {{ item.quantity_received + (item.qty_now || 0) }}
                </td>
              </tr>
            </tbody>
          </table>

          <!-- PO Totals Summary -->
          <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem; padding: 1.5rem; background: rgba(0,0,0,0.15); border-radius: 8px; border: 1px solid var(--color-border);">
            <div style="text-align: right;">
              <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">PO TOTAL COST</div>
              <div style="font-size: 1.75rem; font-weight: 700; color: var(--color-primary);">
                ₹{{ getPOTotal().toFixed(2) }}
              </div>
            </div>
          </div>

        </div>

        <!-- Actions Buttons -->
        <div style="margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
          
          <!-- Submit for Draft update/creation -->
          <button 
            v-if="isCreateOrDraft && !isReceiveMode"
            type="submit" 
            class="btn btn--primary" 
            :disabled="submitting"
          >
            {{ submitting ? 'Saving...' : 'Save Draft PO' }}
          </button>

          <!-- Submit to place PO (Ordered Status) -->
          <button 
            v-if="isEdit && form.status === 'draft' && !isReceiveMode"
            type="button" 
            class="btn btn--secondary" 
            style="background: var(--color-secondary);"
            @click="submitPurchaseOrder"
            :disabled="submitting"
          >
            🚀 Submit PO (Place Order)
          </button>

          <!-- Submit Received Stock -->
          <button 
            v-if="isReceiveMode"
            type="button" 
            class="btn btn--primary" 
            @click="submitReceiveStock"
            :disabled="submitting"
          >
            📥 Confirm Receipt of Stock
          </button>

          <router-link to="/admin/purchase-orders" class="btn btn--secondary">
            Close
          </router-link>
        </div>

      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { usePurchaseOrderStore } from '../../stores/purchaseOrder';
import { useInventoryStore } from '../../stores/inventory';

const route = useRoute();
const router = useRouter();

const poStore = usePurchaseOrderStore();
const inventoryStore = useInventoryStore();

const isEdit = ref(false);
const isReceiveMode = ref(false);
const submitting = ref(false);
const errorMsg = ref(null);
const validationErrors = ref([]);

const form = ref({
  po_number: '',
  supplier_name: '',
  supplier_contact: '',
  notes: '',
  status: 'draft',
  items: [],
});

// Spec for Receive mode items listing
const receiveItems = ref([]);

const allVariants = computed(() => inventoryStore.variants);

const isCreateOrDraft = computed(() => {
  return !isEdit.value || form.value.status === 'draft';
});

const isReadOnly = computed(() => {
  return isEdit.value && form.value.status !== 'draft';
});

const pageTitle = computed(() => {
  if (isReceiveMode.value) return 'Receive PO Stock';
  if (isEdit.value) return isReadOnly.value ? 'View Purchase Order' : 'Edit Purchase Order';
  return 'Create Purchase Order';
});

const pageSubtitle = computed(() => {
  if (isReceiveMode.value) return 'Record batch supplier stock arrival and log ledger entries.';
  if (isEdit.value) return 'Modify draft settings, PO lines, or review order history details.';
  return 'Initiate a new restock request draft.';
});

onMounted(async () => {
  // Load variants dropdown
  inventoryStore.fetchVariants({ per_page: 200 });

  isReceiveMode.value = route.path.includes('/receive');

  if (route.params.id) {
    isEdit.value = true;
    try {
      const po = await poStore.fetchPurchaseOrder(route.params.id);
      if (po) {
        form.value = {
          po_number: po.po_number,
          supplier_name: po.supplier_name,
          supplier_contact: po.supplier_contact || '',
          notes: po.notes || '',
          status: po.status,
          items: po.items || [],
        };

        if (isReceiveMode.value) {
          // Initialize receiving items with qty_now input helpers
          receiveItems.value = po.items.map(item => ({
            ...item,
            qty_now: 0
          }));
        }
      }
    } catch (err) {
      errorMsg.value = 'Failed to load purchase order details.';
    }
  } else {
    // Add default row
    addItemRow();
  }
});

function addItemRow() {
  form.value.items.push({
    product_variant_id: '',
    quantity_ordered: 1,
    unit_cost: 0,
  });
}

function removeItemRow(index) {
  form.value.items.splice(index, 1);
}

function getLineTotal(item) {
  const qty = item.quantity_ordered || 0;
  const cost = item.unit_cost || 0;
  return qty * cost;
}

function getPOTotal() {
  if (isReceiveMode.value) {
    return receiveItems.value.reduce((acc, item) => acc + (item.quantity_ordered * item.unit_cost), 0);
  }
  return form.value.items.reduce((acc, item) => acc + getLineTotal(item), 0);
}

function getPOStatusClass(status) {
  switch (status) {
    case 'draft': return 'badge--secondary';
    case 'ordered': return 'badge--primary';
    case 'partial': return 'badge--warning';
    case 'received': return 'badge--success';
    case 'cancelled': return 'badge--danger';
    default: return 'badge--secondary';
  }
}

async function submitForm() {
  submitting.value = true;
  errorMsg.value = null;
  validationErrors.value = [];

  if (form.value.items.length === 0) {
    errorMsg.value = 'Validation failed';
    validationErrors.value.push('At least one item line is required.');
    submitting.value = false;
    return;
  }

  try {
    if (isEdit.value) {
      await poStore.updatePurchaseOrder(route.params.id, form.value);
    } else {
      await poStore.createPurchaseOrder(form.value);
    }
    router.push('/admin/purchase-orders');
  } catch (err) {
    errorMsg.value = err.message || 'Operation failed';
    if (err.errors) {
      Object.keys(err.errors).forEach(k => {
        const arr = err.errors[k];
        if (Array.isArray(arr)) arr.forEach(m => validationErrors.value.push(m));
        else validationErrors.value.push(arr);
      });
    }
  } finally {
    submitting.value = false;
  }
}

async function submitPurchaseOrder() {
  if (confirm('Are you sure you want to submit this PO? You will lock PO edits and allow stock receiving.')) {
    submitting.value = true;
    errorMsg.value = null;
    try {
      const payload = {
        ...form.value,
        status: 'ordered'
      };
      await poStore.updatePurchaseOrder(route.params.id, payload);
      router.push('/admin/purchase-orders');
    } catch (err) {
      errorMsg.value = err.message || 'Failed to submit PO.';
    } finally {
      submitting.value = false;
    }
  }
}

async function submitReceiveStock() {
  const receivePayload = {
    items: receiveItems.value
      .filter(item => item.qty_now > 0)
      .map(item => ({
        item_id: item.id,
        quantity_received: item.qty_now
      }))
  };

  if (receivePayload.items.length === 0) {
    alert('Please enter at least one quantity to receive stock.');
    return;
  }

  if (confirm('Confirm receipt of these items? Product stocks will increase immediately.')) {
    submitting.value = true;
    errorMsg.value = null;
    try {
      await poStore.receiveStock(route.params.id, receivePayload);
      router.push('/admin/purchase-orders');
    } catch (err) {
      errorMsg.value = err.message || 'Failed to receive stock.';
    } finally {
      submitting.value = false;
    }
  }
}
</script>
