<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <router-link 
        to="/admin/inward" 
        style="text-decoration: none; color: #6E1F3A; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 0.5rem; transition: color 0.15s ease;"
      >
        ◀ Back to Stock Inward Directory
      </router-link>
      <h1 class="admin-page__title" style="font-family: 'Playfair Display', serif; color: #6E1F3A;">
        ➕ New Stock Inward (Goods Receipt)
      </h1>
      <span class="admin-page__subtitle" style="font-family: 'Poppins', sans-serif;">
        Record stock arrivals by category and factory, and add inventory to product colors and sizes with instant validation.
      </span>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value" style="font-size: 1.2rem; color: #6E1F3A;">⏳ Loading factory, category & product catalog...</div>
  </div>

  <div v-else style="display: flex; flex-direction: column; gap: 24px;">
    <!-- Error Alert Notification -->
    <div 
      v-if="errorMsg" 
      class="badge badge--danger" 
      style="padding: 14px 18px; border-radius: 8px; font-size: 0.9rem; width: 100%; display: flex; align-items: center; justify-content: space-between;"
    >
      <span>⚠️ {{ errorMsg }}</span>
      <button type="button" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.1rem;" @click="errorMsg = ''">✕</button>
    </div>

    <form @submit.prevent="handleSubmit" novalidate style="display: flex; flex-direction: column; gap: 24px;">
      <!-- 1. Master Section Details Card -->
      <div class="glass-panel" style="padding: 24px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 20px;">
          <div>
            <div style="font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; color: #6E1F3A;">
              1. Inward Master Section
            </div>
            <span style="font-size: 0.8rem; color: #64748b;">
              Set the shipment date, primary category, and manufacturing factory / weaving unit.
            </span>
          </div>
          <span class="badge badge--secondary" style="font-family: monospace; font-size: 0.85rem; padding: 4px 12px;">
            {{ form.inward_number || 'Auto-generated' }}
          </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
          <!-- Inward Date -->
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">
              Inward Date <span style="color: #ef4444;">*</span>
            </label>
            <input 
              type="date" 
              v-model="form.inward_date" 
              required 
              :class="['form-input', { 'input-error': touched && !form.inward_date }]"
              style="width: 100%; font-family: 'Poppins', sans-serif;"
            />
          </div>

          <!-- Category Choose Option -->
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">
              Category Filter / Master
            </label>
            <select 
              v-model="form.category_id" 
              @change="onCategoryChange"
              class="form-input" 
              style="width: 100%; font-family: 'Poppins', sans-serif;"
            >
              <option value="">-- All Categories (Show All Products) --</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                📁 {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Factory Master Choose Option -->
          <div class="form-group" style="margin: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <label class="form-label" style="font-weight: 600; color: #1e293b; margin: 0;">
                Factory / Weaving Unit Master
              </label>
              <button 
                type="button" 
                @click="showFactoryModal = true" 
                style="background: none; border: none; color: #6E1F3A; font-size: 0.78rem; font-weight: 600; cursor: pointer; padding: 0;"
              >
                ➕ New Factory
              </button>
            </div>
            <select 
              v-model="form.factory_id" 
              @change="onFactoryChange"
              class="form-input" 
              style="width: 100%; font-family: 'Poppins', sans-serif;"
            >
              <option value="">-- Select Factory / Weaving Unit --</option>
              <option v-for="fac in factories" :key="fac.id" :value="fac.id">
                🏭 {{ fac.name }} {{ fac.code ? '(' + fac.code + ')' : '' }}
              </option>
            </select>
          </div>

          <!-- Inward Reference / Number -->
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">
              Inward Ref / Tracking #
            </label>
            <input 
              type="text" 
              v-model="form.inward_number" 
              placeholder="e.g. INW-20260823-0001" 
              class="form-input" 
              style="width: 100%; font-family: monospace;"
            />
          </div>

          <!-- Batch / PO / Invoice # -->
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">
              Batch / PO / Invoice #
            </label>
            <input 
              type="text" 
              v-model="form.reference_no" 
              placeholder="e.g. BATCH-2026-08" 
              class="form-input" 
              style="width: 100%; font-family: 'Poppins', sans-serif;"
            />
          </div>
        </div>

        <!-- Notes / Remarks -->
        <div class="form-group" style="margin-top: 16px; margin-bottom: 0;">
          <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">
            Notes / Batch Remarks
          </label>
          <input 
            type="text" 
            v-model="form.notes" 
            placeholder="e.g. Silk saree shipment received in excellent condition with QC pass." 
            class="form-input" 
            style="width: 100%; font-family: 'Poppins', sans-serif;"
          />
        </div>
      </div>

      <!-- 2. Line Items Details Table Section -->
      <div class="glass-panel" style="padding: 24px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
          <div>
            <div style="font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; color: #6E1F3A;">
              2. Stock Inward Details (Product, Color, Size & Quantity)
            </div>
            <span style="font-size: 0.8rem; color: #64748b;">
              Colors and sizes are dynamically loaded for the chosen product. Each item is strictly validated.
            </span>
          </div>

          <button 
            type="button" 
            class="btn btn--secondary btn--sm" 
            @click="addRow"
            style="padding: 6px 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;"
          >
            ➕ Add Item Row
          </button>
        </div>

        <!-- Items Table -->
        <div style="overflow-x: auto; scrollbar-width: thin;">
          <table style="width: 100%; border-collapse: collapse; min-width: 720px;">
            <thead>
              <tr style="background: #FAF8F5; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.03em;">
                <th style="padding: 12px 14px; font-weight: 600; width: 38%; text-align: left;">Product <span style="color: #ef4444;">*</span></th>
                <th style="padding: 12px 10px; font-weight: 600; width: 22%; text-align: left;">Color</th>
                <th style="padding: 12px 10px; font-weight: 600; width: 20%; text-align: left;">Size Included <span style="color: #ef4444;">*</span></th>
                <th style="padding: 12px 10px; font-weight: 600; width: 14%; text-align: center;">Quantity Added <span style="color: #ef4444;">*</span></th>
                <th style="padding: 12px 12px; font-weight: 600; width: 6%; text-align: center;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="(item, index) in form.items" 
                :key="index"
                style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;"
              >
                <!-- Product Selector (Filtered by Category) -->
                <td style="padding: 10px 14px;">
                  <select 
                    v-model="item.product_id" 
                    @change="onProductSelect(item)"
                    :class="['form-input', { 'input-error': touched && !item.product_id }]"
                    style="width: 100%; padding: 8px 12px; font-size: 0.85rem;"
                  >
                    <option value="" disabled>-- Select Product --</option>
                    <option v-for="p in filteredProducts" :key="p.id" :value="p.id">
                      {{ p.name }} (Current: {{ p.stock_quantity || 0 }} pcs)
                    </option>
                  </select>
                  <span v-if="touched && !item.product_id" style="font-size: 0.72rem; color: #ef4444; display: block; margin-top: 2px;">
                    Required
                  </span>
                </td>

                <!-- Product-based Color Selector -->
                <td style="padding: 10px 10px;">
                  <select 
                    v-if="getProductColors(item.product_id).length > 0"
                    v-model="item.color" 
                    class="form-input" 
                    style="width: 100%; padding: 8px 10px; font-size: 0.85rem;"
                  >
                    <option value="">-- Choose Color --</option>
                    <option v-for="col in getProductColors(item.product_id)" :key="col" :value="col">
                      🎨 {{ col }}
                    </option>
                  </select>
                  <input 
                    v-else
                    type="text" 
                    v-model="item.color" 
                    placeholder="e.g. Maroon, Gold" 
                    class="form-input" 
                    style="width: 100%; padding: 8px 10px; font-size: 0.85rem;"
                  />
                </td>

                <!-- Product-based Included Sizes Selector (STRICT) -->
                <td style="padding: 10px 10px;">
                  <select 
                    v-model="item.size" 
                    :disabled="!item.product_id"
                    :class="['form-input', { 'input-error': touched && !item.size }]"
                    style="width: 100%; padding: 8px 10px; font-size: 0.85rem; font-weight: 600; color: #6E1F3A;"
                  >
                    <option value="" disabled>-- Choose Size --</option>
                    <option v-for="sz in getProductSizes(item.product_id)" :key="sz" :value="sz">
                      📐 {{ sz }}
                    </option>
                  </select>
                  <span v-if="touched && !item.size" style="font-size: 0.72rem; color: #ef4444; display: block; margin-top: 2px;">
                    Required
                  </span>
                </td>

                <!-- Quantity Added -->
                <td style="padding: 10px 10px; text-align: center;">
                  <input 
                    type="number" 
                    v-model.number="item.quantity" 
                    min="1" 
                    :class="['form-input', { 'input-error': touched && (item.quantity === null || item.quantity < 1) }]"
                    style="width: 100%; text-align: center; font-weight: 700; color: #16a34a; font-size: 0.95rem; padding: 8px 6px;"
                  />
                  <span v-if="touched && (item.quantity === null || item.quantity < 1)" style="font-size: 0.72rem; color: #ef4444; display: block; margin-top: 2px;">
                    Min 1
                  </span>
                </td>

                <!-- Remove Row Button -->
                <td style="padding: 10px 12px; text-align: center;">
                  <button 
                    type="button" 
                    class="btn btn--danger btn--sm" 
                    :disabled="form.items.length === 1"
                    @click="removeRow(index)"
                    title="Remove Item Row"
                    style="padding: 6px 8px; font-size: 0.82rem;"
                  >
                    🗑️
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Add Row Button & Summary Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 14px;">
          <button 
            type="button" 
            class="btn btn--secondary" 
            @click="addRow"
            style="padding: 8px 16px; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px;"
          >
            ➕ Add Another Product Item Row
          </button>

          <!-- Summary Badges -->
          <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
            <div style="font-size: 0.85rem; color: #64748b;">
              Total SKUs: <strong style="color: #1e293b;">{{ form.items.length }}</strong>
            </div>
            <div style="font-size: 0.9rem; color: #16a34a; font-weight: 700; padding: 4px 12px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
              Total Quantity: +{{ totalQuantity }} pcs
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Sticky Action Bar -->
      <div class="glass-panel" style="padding: 16px 24px; border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0; display: flex; justify-content: flex-end; align-items: center; gap: 14px; position: sticky; bottom: 16px; z-index: 20; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
        <router-link 
          to="/admin/inward" 
          class="btn btn--secondary" 
          style="padding: 0.65rem 1.5rem; text-decoration: none; font-size: 0.9rem;"
        >
          Cancel
        </router-link>

        <button 
          type="submit" 
          class="btn btn--primary" 
          :disabled="saving"
          style="padding: 0.65rem 2rem; min-height: 48px; background: #6E1F3A; color: #ffffff; font-size: 0.95rem; font-weight: 600; box-shadow: 0 4px 12px rgba(110, 31, 58, 0.25);"
        >
          {{ saving ? '⏳ Validating & Adding Stock...' : '💾 Confirm Inward & Add Stock' }}
        </button>
      </div>
    </form>
  </div>

  <!-- Quick Factory Modal -->
  <div 
    v-if="showFactoryModal" 
    class="modal-backdrop" 
    style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; display: flex; align-items: center; justify-content: center; padding: 20px;"
  >
    <div class="modal-content" style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 100%; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
      <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #FAF8F5; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-family: 'Playfair Display', serif; color: #6E1F3A; font-size: 1.15rem;">
          🏭 Add New Factory / Weaving Unit
        </h3>
        <button type="button" @click="showFactoryModal = false" style="background: none; border: none; font-size: 1.4rem; color: #94a3b8; cursor: pointer;">✕</button>
      </div>
      
      <form @submit.prevent="saveNewFactory" style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
        <div class="form-group" style="margin: 0;">
          <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Factory / Unit Name *</label>
          <input type="text" v-model="newFactory.name" required placeholder="e.g. Salem Silk Handloom Unit" class="form-input" style="width: 100%;" />
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Code</label>
            <input type="text" v-model="newFactory.code" placeholder="e.g. FAC-SLM-05" class="form-input" style="width: 100%;" />
          </div>
          <div class="form-group" style="margin: 0;">
            <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">City</label>
            <input type="text" v-model="newFactory.city" placeholder="e.g. Salem" class="form-input" style="width: 100%;" />
          </div>
        </div>
        <div class="form-group" style="margin: 0;">
          <label class="form-label" style="font-weight: 600; font-size: 0.85rem;">Phone Number</label>
          <input type="text" v-model="newFactory.phone" placeholder="+91 98400 12345" class="form-input" style="width: 100%;" />
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
          <button type="button" class="btn btn--secondary btn--sm" @click="showFactoryModal = false">Cancel</button>
          <button type="submit" class="btn btn--primary btn--sm" style="background: #6E1F3A; color: #ffffff;">Save Factory</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const loading = ref(true);
const saving = ref(false);
const errorMsg = ref('');
const touched = ref(false);

const categories = ref([]);
const factories = ref([]);
const products = ref([]);
const colors = ref([]);
const sizes = ref([]);

const showFactoryModal = ref(false);
const newFactory = ref({
  name: '',
  code: '',
  city: '',
  phone: '',
});

const form = ref({
  inward_date: '',
  category_id: '',
  factory_id: '',
  supplier_name: '',
  inward_number: '',
  reference_no: '',
  notes: '',
  items: [
    {
      product_id: '',
      color: '',
      size: '',
      quantity: 1,
    }
  ]
});

// Filter products based on selected Category in Master section
const filteredProducts = computed(() => {
  if (!form.value.category_id) {
    return products.value;
  }
  return products.value.filter(p => p.category_id === Number(form.value.category_id));
});

const totalQuantity = computed(() => {
  return form.value.items.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
});

// Get configured colors for a specific product
const getProductColors = (productId) => {
  if (!productId) return [];
  const prod = products.value.find(p => p.id === productId);
  if (prod && prod.available_colors && prod.available_colors.length > 0) {
    return prod.available_colors;
  }
  return colors.value.map(c => c.name);
};

// Get strictly included / configured sizes for a specific product
const getProductSizes = (productId) => {
  if (!productId) return ['Free Size'];
  const prod = products.value.find(p => p.id === productId);
  if (prod && prod.available_sizes && prod.available_sizes.length > 0) {
    return prod.available_sizes;
  }
  return ['Free Size', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];
};

const onCategoryChange = () => {
  // If current selected products in rows don't belong to the new category, reset them
  if (form.value.category_id) {
    const validProductIds = filteredProducts.value.map(p => p.id);
    form.value.items.forEach(item => {
      if (item.product_id && !validProductIds.includes(item.product_id)) {
        item.product_id = '';
        item.color = '';
        item.size = '';
      }
    });
  }
};

const onFactoryChange = () => {
  if (form.value.factory_id) {
    const fac = factories.value.find(f => f.id === Number(form.value.factory_id));
    if (fac) {
      form.value.supplier_name = fac.name;
    }
  }
};

const onProductSelect = (item) => {
  const prod = products.value.find(p => p.id === item.product_id);
  if (prod) {
    // Auto populate color if product has only 1 color
    const availColors = getProductColors(prod.id);
    if (availColors.length === 1) {
      item.color = availColors[0];
    } else {
      item.color = '';
    }

    // Auto populate size if product has only 1 size
    const availSizes = getProductSizes(prod.id);
    if (availSizes.length === 1) {
      item.size = availSizes[0];
    } else {
      item.size = '';
    }
  }
};

const addRow = () => {
  form.value.items.push({
    product_id: '',
    color: '',
    size: '',
    quantity: 1,
  });
};

const removeRow = (index) => {
  if (form.value.items.length > 1) {
    form.value.items.splice(index, 1);
  }
};

const saveNewFactory = async () => {
  try {
    const res = await axios.post('/api/admin/factories', newFactory.value);
    if (res.data && res.data.success) {
      factories.value.push(res.data.data);
      form.value.factory_id = res.data.data.id;
      form.value.supplier_name = res.data.data.name;
      showFactoryModal.value = false;
      newFactory.value = { name: '', code: '', city: '', phone: '' };
    }
  } catch (err) {
    console.error('Failed to create factory:', err);
    alert(err.response?.data?.message || 'Failed to create factory.');
  }
};

const fetchFormData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/inward/form-data');
    if (response.data && response.data.success) {
      const data = response.data.data;
      categories.value = data.categories || [];
      factories.value = data.factories || [];
      products.value = data.products || [];
      colors.value = data.colors || [];
      sizes.value = data.sizes || [];
      form.value.inward_date = data.today || new Date().toISOString().split('T')[0];
      form.value.inward_number = data.next_inward_number || '';
    }
  } catch (err) {
    console.error('Failed to load form metadata:', err);
    errorMsg.value = 'Failed to load product, factory & category catalog data.';
  } finally {
    loading.value = false;
  }
};

const handleSubmit = async () => {
  touched.value = true;
  errorMsg.value = '';

  // 1. Validate Master section
  if (!form.value.inward_date) {
    errorMsg.value = 'Please select a valid Inward Date in the master section.';
    return;
  }

  // 2. Validate Items
  if (!form.value.items || form.value.items.length === 0) {
    errorMsg.value = 'Please add at least one product item row.';
    return;
  }

  for (let i = 0; i < form.value.items.length; i++) {
    const it = form.value.items[i];
    const rowNum = i + 1;

    if (!it.product_id) {
      errorMsg.value = `Row #${rowNum}: Please select a product.`;
      return;
    }

    if (!it.size || it.size.trim() === '') {
      errorMsg.value = `Row #${rowNum}: Please select a valid size for the product.`;
      return;
    }

    if (it.quantity === null || it.quantity === undefined || Number(it.quantity) < 1) {
      errorMsg.value = `Row #${rowNum}: Quantity added must be at least 1 piece.`;
      return;
    }
  }

  saving.value = true;

  try {
    const payload = {
      inward_date: form.value.inward_date,
      category_id: form.value.category_id || null,
      factory_id: form.value.factory_id || null,
      supplier_name: form.value.supplier_name || null,
      inward_number: form.value.inward_number || undefined,
      reference_no: form.value.reference_no || null,
      notes: form.value.notes || null,
      items: form.value.items.map(it => ({
        product_id: it.product_id,
        color: it.color || null,
        size: it.size.trim(),
        quantity: Number(it.quantity),
      }))
    };

    const response = await axios.post('/api/admin/inward', payload);
    if (response.data && response.data.success) {
      router.push('/admin/inward');
    }
  } catch (err) {
    console.error('Failed to record stock inward:', err);
    errorMsg.value = err.response?.data?.message || 'Error occurred while saving stock inward.';
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchFormData();
});
</script>

<style scoped>
.input-error {
  border-color: #ef4444 !important;
  background-color: #fef2f2 !important;
}
</style>
