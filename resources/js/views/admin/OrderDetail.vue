<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <router-link to="/admin/orders" style="text-decoration: none; color: var(--color-primary); font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
        ◀ Back to Orders
      </router-link>
      <h1 class="admin-page__title">Order Details: {{ order.order_number }}</h1>
      <span class="admin-page__subtitle">Placed on {{ formatDate(order.created_at) }}</span>
    </div>
    <div style="display: flex; gap: var(--spacing-sm);">
      <button class="btn btn--secondary" @click="printMockInvoice">
        🖨️ Print Invoice
      </button>
    </div>
  </div>

  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Loading order information...</div>
  </div>

  <div v-else-if="error" class="badge badge--danger" style="padding: 1rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ error }}
  </div>

  <div v-else style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
    
    <!-- 9-STAGE ORDER PROGRESS STEPPER -->
    <div class="glass-panel" style="padding: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
        <div>
          <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
            Order Lifecycle Progress
          </span>
          <div style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary); margin-top: 2px;">
            Current: {{ currentStatusDetails.label }}
          </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <span :class="['badge', currentStatusDetails.badge]" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
            {{ currentStatusDetails.icon }} {{ currentStatusDetails.label }}
          </span>
          <span v-if="currentStatusDetails.meaning" style="font-size: 0.8rem; color: var(--color-text-muted);">
            ({{ currentStatusDetails.meaning }})
          </span>
        </div>
      </div>

      <!-- Linear Stepper for Standard 6 Flow Steps -->
      <div v-if="!isTerminalSpecialStatus" class="order-stepper-container">
        <div 
          v-for="(st, idx) in standardFlowSteps" 
          :key="st.code"
          class="stepper-step"
          :class="{
            'step--completed': currentStepIndex > idx,
            'step--active': currentStepIndex === idx,
            'step--upcoming': currentStepIndex < idx
          }"
        >
          <div class="step-circle">
            <span v-if="currentStepIndex > idx">✓</span>
            <span v-else>{{ st.step }}</span>
          </div>
          <div class="step-info">
            <div class="step-name">{{ st.label }}</div>
            <div class="step-desc">{{ st.meaning }}</div>
          </div>
          <div v-if="idx < standardFlowSteps.length - 1" class="step-connector"></div>
        </div>
      </div>

      <!-- Special State Banner (Cancelled / Returned / Refunded) -->
      <div v-else :class="['special-status-alert', currentStatusDetails.code]">
        <div style="font-size: 2rem;">{{ currentStatusDetails.icon }}</div>
        <div>
          <div style="font-weight: 700; font-size: 1.05rem;">
            Order is {{ currentStatusDetails.label }}
          </div>
          <div style="font-size: 0.85rem; opacity: 0.9;">
            {{ currentStatusDetails.meaning }}
            <span v-if="order.cancellation_reason">• Reason: {{ order.cancellation_reason }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="responsive-grid-2-1" style="gap: var(--spacing-lg);">
      
      <!-- Left Column: Core transactional properties -->
      <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        
        <!-- Status Controls Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--spacing-md); margin-bottom: 1rem;">
            <div>
              <div class="card-header-title">Update Order Lifecycle Status</div>
              <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.25rem;">
                Select next workflow transition or assign courier.
              </div>
            </div>
          </div>

          <!-- Status Dropdown & Action Controls -->
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; align-items: start; background: rgba(0,0,0,0.02); padding: 1rem; border-radius: 8px; border: 1px solid var(--color-border);">
            <div>
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.35rem; display: block;">
                Next Status (From 9 Standard Stages):
              </label>
              <select v-model="nextStatus" class="form-select has-value" style="height: 44px; font-weight: 600; font-size: 0.9rem;">
                <option value="" disabled>-- Select New Status --</option>
                <optgroup label="Recommended Next Steps">
                  <option 
                    v-for="st in allowedTransitionsList" 
                    :key="st.code" 
                    :value="st.code"
                  >
                    👉 {{ st.step }}. {{ st.label }} ({{ st.meaning }})
                  </option>
                </optgroup>
                <optgroup label="All 9 Standard Stages">
                  <option 
                    v-for="st in allStatuses" 
                    :key="st.code" 
                    :value="st.code"
                  >
                    {{ st.step }}. {{ st.label }} — {{ st.meaning }}
                  </option>
                </optgroup>
              </select>
            </div>

            <div>
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.35rem; display: block;">
                Audit Note / Reason:
              </label>
              <input 
                type="text" 
                v-model="statusComment" 
                placeholder="e.g. Verified payment / Packed in warehouse..." 
                class="form-input" 
                style="height: 44px; font-size: 0.85rem;"
              />
            </div>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem; align-items: center;">
            <button 
              class="btn btn--primary" 
              :disabled="!nextStatus || statusUpdating"
              @click="submitStatusChange"
              style="border-radius: 8px; height: 44px; padding: 0 1.5rem; font-weight: 700;"
            >
              {{ statusUpdating ? 'Updating Status...' : `Change Status to ${getSelectedStatusLabel()}` }}
            </button>
          </div>
        </div>

        <!-- Items List -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Order Items ({{ order.items?.length || 0 }} Items)</div>
          
          <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div 
              v-for="item in order.items" 
              :key="item.id" 
              style="display: flex; justify-content: space-between; align-items: center; padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-border);"
            >
              <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                <div style="width: 54px; height: 54px; border-radius: 8px; background: rgba(0,0,0,0.03); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; overflow: hidden;">
                  <img 
                    v-if="item.variant?.product?.primary_image_url" 
                    :src="item.variant.product.primary_image_url" 
                    style="width: 100%; height: 100%; object-fit: cover;" 
                  />
                  <span v-else>👗</span>
                </div>
                <div>
                  <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">{{ item.product_name }}</div>
                  <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.125rem; display: flex; gap: 0.5rem; align-items: center;">
                    <code>{{ item.sku }}</code>
                    <span v-if="item.variant?.color">• Color: {{ item.variant.color }}</span>
                    <span v-if="item.variant?.size">• Size: {{ item.variant.size }}</span>
                  </div>
                </div>
              </div>
              
              <div style="text-align: right;">
                <div style="font-weight: 700; color: #1e293b; font-size: 1rem;">₹{{ parseFloat(item.total_price).toFixed(2) }}</div>
                <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.125rem;">
                  ₹{{ parseFloat(item.unit_price).toFixed(2) }} × {{ item.quantity }} units
                </div>
              </div>
            </div>
          </div>

          <!-- Summary Totals -->
          <div style="margin-top: var(--spacing-lg); display: flex; flex-direction: column; gap: var(--spacing-sm); align-items: flex-end;">
            <div style="display: flex; justify-content: space-between; width: 280px; font-size: 0.9rem;">
              <span style="color: var(--color-text-muted);">Subtotal:</span>
              <span style="color: #1e293b; font-weight: 500;">₹{{ parseFloat(order.subtotal).toFixed(2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; width: 280px; font-size: 0.9rem;" v-if="parseFloat(order.discount_amount) > 0">
              <span style="color: var(--color-text-muted);">Coupon Discount:</span>
              <span style="color: var(--color-danger); font-weight: 500;">-₹{{ parseFloat(order.discount_amount).toFixed(2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; width: 280px; font-size: 0.9rem;">
              <span style="color: var(--color-text-muted);">Shipping Amount:</span>
              <span style="color: #1e293b; font-weight: 500;">₹{{ parseFloat(order.shipping_amount || 0).toFixed(2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; width: 280px; font-size: 1.1rem; border-top: 1px solid var(--color-border); padding-top: var(--spacing-sm); font-weight: bold; margin-top: var(--spacing-xs);">
              <span style="color: #1e293b;">Grand Total:</span>
              <span style="color: var(--color-primary); font-family: 'Playfair Display', serif; font-size: 1.3rem;">
                ₹{{ parseFloat(order.grand_total).toFixed(2) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Address Grid -->
        <div class="responsive-grid-1-1" style="gap: var(--spacing-lg);">
          <!-- Shipping Address -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Shipping Address</div>
            <div style="line-height: 1.5; font-size: 0.9rem; color: #1e293b;">
              <strong>{{ order.shipping_first_name }} {{ order.shipping_last_name }}</strong><br />
              {{ order.shipping_address_line_1 }}<br />
              <span v-if="order.shipping_address_line_2">{{ order.shipping_address_line_2 }}<br /></span>
              {{ order.shipping_city }}, {{ order.shipping_state }} - {{ order.shipping_postal_code }}<br />
              {{ order.shipping_country }}<br />
              <span style="color: var(--color-text-muted);">📞 Phone: {{ order.shipping_phone }}</span>
            </div>
          </div>

          <!-- Billing Address -->
          <div class="glass-panel" style="padding: var(--spacing-lg);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Billing Address</div>
            <div style="line-height: 1.5; font-size: 0.9rem; color: #1e293b;">
              <strong>{{ order.billing_first_name }} {{ order.billing_last_name }}</strong><br />
              {{ order.billing_address_line_1 }}<br />
              <span v-if="order.billing_address_line_2">{{ order.billing_address_line_2 }}<br /></span>
              {{ order.billing_city }}, {{ order.billing_state }} - {{ order.billing_postal_code }}<br />
              {{ order.billing_country }}<br />
              <span style="color: var(--color-text-muted);">📞 Phone: {{ order.billing_phone }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Sidebar metadata, Courier dispatch, Logs -->
      <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        
        <!-- Courier Partner & Shipping Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Courier & Dispatch Details</div>
          
          <form @submit.prevent="submitShippingUpdate" style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
            <div>
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.25rem; display: block;">
                Courier Partner:
              </label>
              <select 
                v-model="shippingForm.courier_id" 
                @change="onCourierSelected" 
                class="form-select has-value"
                style="height: 38px; font-size: 0.85rem;"
              >
                <option value="">-- Select Courier Partner --</option>
                <option v-for="c in activeCouriers" :key="c.id" :value="c.id">
                  {{ c.name }} {{ c.code ? `(${c.code})` : '' }}
                </option>
              </select>
            </div>

            <div>
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.25rem; display: block;">
                AWB / Tracking Number:
              </label>
              <input 
                type="text" 
                v-model="shippingForm.tracking_number" 
                @input="updateGeneratedTrackingUrl"
                placeholder="e.g. ST123456789IN" 
                class="form-input" 
                style="height: 38px; font-size: 0.85rem;" 
              />
            </div>

            <div v-if="shippingForm.courier_tracking_url" style="margin-top: 0.25rem;">
              <a 
                :href="shippingForm.courier_tracking_url" 
                target="_blank" 
                style="font-size: 0.8rem; color: var(--color-primary); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;"
              >
                🔗 Test Tracking Link ↗
              </a>
            </div>

            <div>
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.25rem; display: block;">
                Est. Delivery Date:
              </label>
              <input 
                type="date" 
                v-model="shippingForm.estimated_delivery_at" 
                class="form-input" 
                style="height: 38px; font-size: 0.85rem;" 
              />
            </div>

            <button 
              type="submit" 
              class="btn btn--secondary btn--sm" 
              :disabled="shippingUpdating"
              style="margin-top: 0.5rem; height: 38px; font-weight: 600;"
            >
              {{ shippingUpdating ? 'Saving...' : '💾 Save Dispatch Details' }}
            </button>
          </form>
        </div>

        <!-- Payment Details Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Payment Information</div>
          <div style="display: flex; flex-direction: column; gap: var(--spacing-sm); font-size: 0.85rem;">
            <div style="display: flex; justify-content: space-between;">
              <span style="color: var(--color-text-muted);">Payment Method:</span>
              <span style="font-weight: 600; text-transform: uppercase;">{{ order.payment_method }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span style="color: var(--color-text-muted);">Payment Status:</span>
              <span :class="['badge', getPaymentBadgeClass(order.payment_status)]">
                {{ order.payment_status }}
              </span>
            </div>
            <div style="display: flex; justify-content: space-between;" v-if="order.paid_at">
              <span style="color: var(--color-text-muted);">Paid At:</span>
              <span>{{ formatDate(order.paid_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Customer Profile Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Customer Information</div>
          <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: bold;">
              {{ customerInitials }}
            </div>
            <div>
              <div style="font-weight: bold; color: #1e293b; font-size: 1.05rem;">
                {{ order.shipping_first_name }} {{ order.shipping_last_name }}
              </div>
              <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.125rem;">
                {{ order.user?.email }}
              </div>
              <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                {{ order.shipping_phone }}
              </div>
            </div>
          </div>
          <router-link :to="`/admin/customers/${order.user_id}`" class="btn btn--secondary btn--sm" style="display: block; text-align: center; text-decoration: none;">
            View Customer Profile
          </router-link>
        </div>

        <!-- Timeline Logs -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Audit Timeline Logs</div>
          <div style="display: flex; flex-direction: column; gap: var(--spacing-md); position: relative; padding-left: var(--spacing-md);">
            <div style="position: absolute; left: 6px; top: 8px; bottom: 8px; width: 2px; background: var(--color-border);"></div>
            
            <div 
              v-for="log in order.status_history" 
              :key="log.id" 
              style="position: relative; font-size: 0.8rem;"
            >
              <div style="position: absolute; left: -19px; top: 4px; width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary); border: 2px solid var(--color-bg);"></div>
              
              <div style="font-weight: bold; color: #1e293b;">
                Status changed to: 
                <span :class="['badge', getStatusBadgeClass(log.to_status)]" style="font-size: 0.7rem; padding: 2px 6px;">
                  {{ getStatusLabel(log.to_status) }}
                </span>
              </div>
              <div v-if="log.comment" style="color: var(--color-text-secondary); font-size: 0.78rem; margin-top: 0.2rem;">
                {{ log.comment }}
              </div>
              <div style="font-size: 0.7rem; color: var(--color-text-muted); margin-top: 0.2rem;">
                {{ formatDate(log.created_at) }}
                <span v-if="log.changed_by_user">• By {{ log.changed_by_user.name }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const orderId = route.params.id;

const order = ref({});
const loading = ref(true);
const error = ref('');

const nextStatus = ref('');
const statusComment = ref('');
const statusUpdating = ref(false);

const activeCouriers = ref([]);

const shippingForm = ref({
  courier_id: '',
  courier_name: '',
  tracking_number: '',
  courier_tracking_url: '',
  courier_contact_number: '',
  courier_person_name: '',
  estimated_delivery_at: '',
});
const shippingUpdating = ref(false);

// 9 Standard Order Status Definitions
const allStatuses = [
  { step: 1, code: 'order_placed', label: 'Order Placed', meaning: 'Customer successfully placed the order', badge: 'badge--warning', icon: '📝' },
  { step: 2, code: 'order_confirmed', label: 'Order Confirmed', meaning: 'Admin accepted/confirmed the order', badge: 'badge--primary', icon: '✓' },
  { step: 3, code: 'processing', label: 'Processing', meaning: 'Order is being prepared', badge: 'badge--secondary', icon: '⚙️' },
  { step: 4, code: 'ready_to_ship', label: 'Ready to Ship', meaning: 'Product is packed and ready', badge: 'badge--secondary', icon: '📦' },
  { step: 5, code: 'shipped', label: 'Shipped', meaning: 'Order handed over to courier', badge: 'badge--warning', icon: '🚚' },
  { step: 6, code: 'delivered', label: 'Delivered', meaning: 'Customer received the order', badge: 'badge--success', icon: '🎉' },
  { step: 7, code: 'cancelled', label: 'Cancelled', meaning: 'Order was cancelled', badge: 'badge--danger', icon: '✕' },
  { step: 8, code: 'returned', label: 'Returned', meaning: 'Product was returned', badge: 'badge--danger', icon: '↩' },
  { step: 9, code: 'refunded', label: 'Refunded', meaning: 'Refund completed', badge: 'badge--secondary', icon: '💰' },
];

const standardFlowSteps = [
  allStatuses[0], // 1. Order Placed
  allStatuses[1], // 2. Order Confirmed
  allStatuses[2], // 3. Processing
  allStatuses[3], // 4. Ready to Ship
  allStatuses[4], // 5. Shipped
  allStatuses[5], // 6. Delivered
];

const currentNormStatus = computed(() => {
  const st = order.value.status;
  if (st === 'pending') return 'order_placed';
  if (st === 'confirmed') return 'order_confirmed';
  return st || 'order_placed';
});

const currentStatusDetails = computed(() => {
  const norm = currentNormStatus.value;
  return allStatuses.find(s => s.code === norm) || {
    step: 0,
    code: norm,
    label: norm.toUpperCase(),
    meaning: '',
    badge: 'badge--secondary',
    icon: '📦',
  };
});

const isTerminalSpecialStatus = computed(() => {
  return ['cancelled', 'returned', 'refunded'].includes(currentNormStatus.value);
});

const currentStepIndex = computed(() => {
  const idx = standardFlowSteps.findIndex(s => s.code === currentNormStatus.value);
  return idx !== -1 ? idx : 0;
});

const allowedTransitionsList = computed(() => {
  const cur = currentNormStatus.value;
  const map = {
    order_placed: ['order_confirmed', 'processing', 'cancelled'],
    order_confirmed: ['processing', 'ready_to_ship', 'cancelled'],
    processing: ['ready_to_ship', 'shipped', 'cancelled'],
    ready_to_ship: ['shipped', 'cancelled'],
    shipped: ['delivered', 'returned', 'cancelled'],
    delivered: ['returned', 'refunded'],
    returned: ['refunded'],
    cancelled: ['refunded'],
    refunded: [],
  };
  const nextCodes = map[cur] || [];
  return allStatuses.filter(s => nextCodes.includes(s.code));
});

function getSelectedStatusLabel() {
  const st = allStatuses.find(s => s.code === nextStatus.value);
  return st ? `"${st.label}"` : '';
}

function getStatusLabel(status) {
  const norm = status === 'pending' ? 'order_placed' : (status === 'confirmed' ? 'order_confirmed' : status);
  const def = allStatuses.find(s => s.code === norm);
  return def ? def.label : status;
}

function getStatusBadgeClass(status) {
  const norm = status === 'pending' ? 'order_placed' : (status === 'confirmed' ? 'order_confirmed' : status);
  const def = allStatuses.find(s => s.code === norm);
  return def ? def.badge : 'badge--secondary';
}

const customerInitials = computed(() => {
  const f = order.value.shipping_first_name || '';
  const l = order.value.shipping_last_name || '';
  return (f.charAt(0) + l.charAt(0)).toUpperCase() || 'JD';
});

const fetchActiveCouriers = async () => {
  try {
    const res = await axios.get('/api/admin/couriers/active');
    if (res.data && res.data.success) {
      activeCouriers.value = res.data.data;
    }
  } catch (err) {
    console.error('Failed to load active couriers:', err);
  }
};

const onCourierSelected = () => {
  if (!shippingForm.value.courier_id) return;
  const selected = activeCouriers.value.find(c => c.id === shippingForm.value.courier_id);
  if (selected) {
    shippingForm.value.courier_name = selected.name;
    shippingForm.value.courier_person_name = selected.contact_person || '';
    shippingForm.value.courier_contact_number = selected.contact_number || '';
    updateGeneratedTrackingUrl();
  }
};

const updateGeneratedTrackingUrl = () => {
  if (!shippingForm.value.courier_id) return;
  const selected = activeCouriers.value.find(c => c.id === shippingForm.value.courier_id);
  if (selected && selected.tracking_page_link && shippingForm.value.tracking_number) {
    const template = selected.tracking_page_link;
    shippingForm.value.courier_tracking_url = template.replace(/\{tracking_number\}|\{tracking_id\}|\{awb\}/g, encodeURIComponent(shippingForm.value.tracking_number.trim()));
  }
};

const fetchOrder = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/admin/orders/${orderId}`);
    if (response.data && response.data.success) {
      order.value = response.data.data;
      shippingForm.value.courier_id = order.value.courier_id || '';
      shippingForm.value.courier_name = order.value.courier_name || '';
      shippingForm.value.tracking_number = order.value.tracking_number || '';
      shippingForm.value.courier_tracking_url = order.value.courier_tracking_url || order.value.tracking_url || '';
      shippingForm.value.courier_contact_number = order.value.courier_contact_number || '';
      shippingForm.value.courier_person_name = order.value.courier_person_name || '';
      shippingForm.value.estimated_delivery_at = order.value.estimated_delivery_at ? order.value.estimated_delivery_at.substring(0, 10) : '';
    }
  } catch (err) {
    console.error('Failed to load order:', err);
    error.value = 'Failed to load customer order details';
  } finally {
    loading.value = false;
  }
};

const submitStatusChange = async () => {
  if (!nextStatus.value) return;
  statusUpdating.value = true;
  try {
    const response = await axios.put(`/api/admin/orders/${orderId}/status`, {
      status: nextStatus.value,
      comment: statusComment.value.trim() || `Status updated to ${getStatusLabel(nextStatus.value)}.`
    });

    if (response.data && response.data.success) {
      nextStatus.value = '';
      statusComment.value = '';
      await fetchOrder();
    }
  } catch (err) {
    console.error('Failed to update status:', err);
    alert(err.response?.data?.message || 'Failed to update order status');
  } finally {
    statusUpdating.value = false;
  }
};

const submitShippingUpdate = async () => {
  shippingUpdating.value = true;
  try {
    const response = await axios.put(`/api/admin/orders/${orderId}/shipping`, {
      courier_id: shippingForm.value.courier_id || null,
      courier_name: shippingForm.value.courier_name,
      tracking_number: shippingForm.value.tracking_number,
      courier_tracking_url: shippingForm.value.courier_tracking_url || null,
      courier_contact_number: shippingForm.value.courier_contact_number || null,
      courier_person_name: shippingForm.value.courier_person_name || null,
      estimated_delivery_at: shippingForm.value.estimated_delivery_at || null,
    });

    if (response.data && response.data.success) {
      await fetchOrder();
      alert('Courier tracking information updated successfully');
    }
  } catch (err) {
    console.error('Failed to update shipping information:', err);
    alert(err.response?.data?.message || 'Failed to update tracking details');
  } finally {
    shippingUpdating.value = false;
  }
};

const printMockInvoice = () => {
  const content = `
    INVOICE MASTER
    --------------------------
    Order Number: ${order.value.order_number}
    Placed Date: ${formatDate(order.value.created_at)}
    Status: ${currentStatusDetails.value.label}
    
    Bill To:
    ${order.value.billing_first_name} ${order.value.billing_last_name}
    ${order.value.billing_address_line_1}
    ${order.value.billing_city}, ${order.value.billing_state}
    Phone: ${order.value.billing_phone}
    
    Items:
    ${(order.value.items || []).map(i => `- ${i.product_name} [${i.sku}] (x${i.quantity}) - ₹${parseFloat(i.total_price).toFixed(2)}`).join('\n')}
    
    Subtotal: ₹${parseFloat(order.value.subtotal || 0).toFixed(2)}
    Grand Total: ₹${parseFloat(order.value.grand_total || 0).toFixed(2)}
  `;
  const win = window.open('', '_blank');
  win.document.write(`<pre>${content}</pre>`);
  win.document.close();
  win.print();
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getPaymentBadgeClass = (status) => {
  switch (status) {
    case 'paid':
    case 'captured':
      return 'badge--success';
    case 'pending':
    case 'authorized':
      return 'badge--warning';
    case 'failed':
    case 'cancelled':
      return 'badge--danger';
    case 'refunded':
      return 'badge--secondary';
    default:
      return 'badge--secondary';
  }
};

onMounted(() => {
  fetchOrder();
  fetchActiveCouriers();
});
</script>

<style scoped>
.order-stepper-container {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  position: relative;
  overflow-x: auto;
  padding: 1rem 0;
  gap: 0.5rem;
}

.stepper-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  position: relative;
  flex: 1;
  min-width: 100px;
}

.step-circle {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #f1f5f9;
  border: 2px solid #cbd5e1;
  color: #64748b;
  font-weight: 700;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
  transition: all 0.3s ease;
  z-index: 2;
}

.step--completed .step-circle {
  background: var(--color-success);
  border-color: var(--color-success);
  color: #ffffff;
}

.step--active .step-circle {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: #ffffff;
  box-shadow: 0 0 0 4px rgba(74, 14, 46, 0.15);
}

.step-info {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.step-name {
  font-size: 0.8rem;
  font-weight: 700;
  color: #64748b;
}

.step--completed .step-name {
  color: var(--color-text-primary);
}

.step--active .step-name {
  color: var(--color-primary);
}

.step-desc {
  font-size: 0.7rem;
  color: var(--color-text-muted);
  max-width: 120px;
  margin-top: 2px;
}

.step-connector {
  position: absolute;
  top: 19px;
  left: 50%;
  width: 100%;
  height: 3px;
  background: #e2e8f0;
  z-index: 1;
}

.step--completed .step-connector {
  background: var(--color-success);
}

.special-status-alert {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-radius: 8px;
  background: #f8fafc;
}

.special-status-alert.cancelled {
  background: rgba(220, 38, 38, 0.08);
  border: 1px solid rgba(220, 38, 38, 0.2);
  color: #b91c1c;
}

.special-status-alert.returned {
  background: rgba(234, 88, 12, 0.08);
  border: 1px solid rgba(234, 88, 12, 0.2);
  color: #c2410c;
}

.special-status-alert.refunded {
  background: rgba(13, 148, 136, 0.08);
  border: 1px solid rgba(13, 148, 136, 0.2);
  color: #0f766e;
}
</style>
