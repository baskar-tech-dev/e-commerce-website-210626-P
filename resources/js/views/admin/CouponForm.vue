<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <router-link to="/admin/coupons" style="text-decoration: none; color: var(--color-primary); font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
        ◀ Back to Coupons
      </router-link>
      <h1 class="admin-page__title">{{ isEdit ? 'Edit Coupon' : 'Create Coupon' }}</h1>
      <span class="admin-page__subtitle">{{ isEdit ? 'Modify coupon discount rules and conditions.' : 'Configure a new promotional code.' }}</span>
    </div>
  </div>

  <div v-if="loadingOrder" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Loading coupon details...</div>
  </div>

  <div v-else>
    <!-- Error Alert -->
    <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: var(--spacing-lg); padding: 0.75rem; width: 100%; border-radius: 8px;">
      ⚠️ {{ errorMsg }}
    </div>

    <form @submit.prevent="saveCoupon" class="responsive-grid-2-1" style="gap: var(--spacing-lg);">
      <!-- Left Column: Core Configuration Rules -->
      <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        <!-- Basic Info Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Basic Information</div>
          <div class="responsive-grid-1-2" style="gap: var(--spacing-md);">
            <div class="form-group">
              <label class="form-label">Coupon Code *</label>
              <input 
                type="text" 
                v-model="form.code" 
                placeholder="e.g. SUMMER25" 
                class="form-input" 
                required 
                style="text-transform: uppercase;"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Internal Display Name *</label>
              <input 
                type="text" 
                v-model="form.name" 
                placeholder="e.g. Mid-Season Summer Discount" 
                class="form-input" 
                required 
              />
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Description (Customer facing)</label>
              <input 
                type="text" 
                v-model="form.description" 
                placeholder="Get flat Rs. 500 off on ordering above Rs. 2000..." 
                class="form-input" 
              />
            </div>
          </div>
        </div>

        <!-- Discount Rules Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Discount Rules</div>
          <div class="responsive-grid-1-1" style="gap: var(--spacing-md);">
            <div class="form-group">
              <label class="form-label">Discount Type *</label>
              <select v-model="form.type" class="form-input" required>
                <option value="percentage">Percentage off (%)</option>
                <option value="flat">Flat Amount (INR)</option>
                <option value="free_shipping">Free Shipping</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Discount Value *</label>
              <input 
                type="number" 
                step="0.01" 
                v-model="form.value" 
                class="form-input" 
                required 
                :disabled="form.type === 'free_shipping'"
                min="0"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Max Discount Cap (INR)</label>
              <input 
                type="number" 
                step="0.01" 
                v-model="form.max_discount" 
                placeholder="No cap limit" 
                class="form-input" 
                :disabled="form.type === 'free_shipping'"
                min="0"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Minimum Order Value (INR)</label>
              <input 
                type="number" 
                step="0.01" 
                v-model="form.min_order_value" 
                placeholder="No minimum requirement" 
                class="form-input" 
                min="0"
              />
            </div>
          </div>
        </div>

        <!-- Usage Limits Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Usage & Frequency Limits</div>
          <div class="responsive-grid-1-1" style="gap: var(--spacing-md);">
            <div class="form-group">
              <label class="form-label">Total Uses Allowed (Globally)</label>
              <input 
                type="number" 
                v-model="form.max_uses_total" 
                placeholder="Unlimited uses" 
                class="form-input" 
                min="1"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Uses Allowed Per Customer *</label>
              <input 
                type="number" 
                v-model="form.max_uses_per_user" 
                class="form-input" 
                required 
                min="1"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Settings, Validity & Action items -->
      <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        <!-- Validity Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Validity Duration</div>
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Start Date *</label>
            <input 
              type="date" 
              v-model="form.starts_at" 
              class="form-input" 
              required 
            />
          </div>
          <div class="form-group">
            <label class="form-label">End Date (Expiry)</label>
            <input 
              type="date" 
              v-model="form.expires_at" 
              class="form-input" 
            />
          </div>
        </div>

        <!-- Toggle Rules Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg); display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div class="card-header-title">Rules & Options</div>
          
          <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-size: 0.9rem; cursor: pointer;">
            <input type="checkbox" v-model="form.is_active" />
            <span>Active (Available for checkout)</span>
          </label>

          <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-size: 0.9rem; cursor: pointer;">
            <input type="checkbox" v-model="form.first_order_only" />
            <span>New Customers Only</span>
          </label>

          <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-size: 0.9rem; cursor: pointer;">
            <input type="checkbox" v-model="form.is_auto_apply" />
            <span>Auto Apply when eligible</span>
          </label>

          <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-size: 0.9rem; cursor: pointer;">
            <input type="checkbox" v-model="form.is_combinable" />
            <span>Combinable (Can stack with others)</span>
          </label>
        </div>

        <!-- Action Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg); display: flex; flex-direction: column; gap: var(--spacing-sm);">
          <button type="submit" class="btn btn--primary" style="width: 100%; justify-content: center;" :disabled="saving">
            {{ isEdit ? 'Save Changes' : 'Create Coupon' }}
          </button>
          <router-link to="/admin/coupons" class="btn btn--secondary" style="display: block; text-align: center; text-decoration: none;">
            Cancel
          </router-link>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const isEdit = ref(false);
const loadingOrder = ref(false);
const saving = ref(false);
const errorMsg = ref('');

const form = ref({
  code: '',
  name: '',
  description: '',
  type: 'percentage',
  value: 0.00,
  max_discount: null,
  min_order_value: null,
  max_uses_total: null,
  max_uses_per_user: 1,
  starts_at: '',
  expires_at: null,
  is_active: true,
  first_order_only: false,
  is_auto_apply: false,
  is_combinable: false,
});

const loadCoupon = async () => {
  const id = route.params.id;
  if (!id) return;
  
  isEdit.value = true;
  loadingOrder.value = true;
  try {
    const response = await axios.get(`/api/admin/coupons/${id}`);
    if (response.data && response.data.success) {
      const data = response.data.data;
      
      // Formatting date stamps to YYYY-MM-DD for date inputs
      const formatDateInput = (isoString) => {
        if (!isoString) return '';
        return isoString.split('T')[0];
      };

      form.value = {
        code: data.code,
        name: data.name,
        description: data.description || '',
        type: data.type,
        value: parseFloat(data.value),
        max_discount: data.max_discount ? parseFloat(data.max_discount) : null,
        min_order_value: data.min_order_value ? parseFloat(data.min_order_value) : null,
        max_uses_total: data.max_uses_total || null,
        max_uses_per_user: data.max_uses_per_user,
        starts_at: formatDateInput(data.starts_at),
        expires_at: formatDateInput(data.expires_at),
        is_active: !!data.is_active,
        first_order_only: !!data.first_order_only,
        is_auto_apply: !!data.is_auto_apply,
        is_combinable: !!data.is_combinable,
      };
    }
  } catch (err) {
    console.error('Failed to load coupon details:', err);
    errorMsg.value = 'Failed to load coupon settings';
  } finally {
    loadingOrder.value = false;
  }
};

const saveCoupon = async () => {
  saving.value = true;
  errorMsg.value = '';
  
  // Format code to uppercase on submit
  form.value.code = form.value.code.toUpperCase().trim();

  // If free shipping, value must be 0
  if (form.value.type === 'free_shipping') {
    form.value.value = 0;
    form.value.max_discount = null;
  }

  try {
    const id = route.params.id;
    let response;
    if (isEdit.value) {
      response = await axios.put(`/api/admin/coupons/${id}`, form.value);
    } else {
      response = await axios.post('/api/admin/coupons', form.value);
    }

    if (response.data && response.data.success) {
      router.push('/admin/coupons');
    }
  } catch (err) {
    console.error('Failed to save coupon:', err);
    if (err.response?.data?.errors) {
      // Collect Laravel validation messages
      const errors = err.response.data.errors;
      errorMsg.value = Object.values(errors).flat().join(' | ');
    } else {
      errorMsg.value = err.response?.data?.message || 'Failed to save coupon configuration rules';
    }
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  // Set default start date to today
  form.value.starts_at = new Date().toISOString().split('T')[0];
  loadCoupon();
});
</script>
