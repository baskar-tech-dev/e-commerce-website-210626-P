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

  <div v-else class="responsive-grid-2-1" style="gap: var(--spacing-lg);">
    <!-- Left Column: Core transactional properties -->
    <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
      <!-- Status Controls Card -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--spacing-md);">
          <div>
            <div class="card-header-title">Update Order Status</div>
            <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.25rem;">
              Current Status: 
              <span :class="['badge', getStatusBadgeClass(order.status)]" style="font-size: 0.8rem;">
                {{ order.status }}
              </span>
            </div>
          </div>

          <div style="display: flex; gap: var(--spacing-sm); align-items: center;">
            <select v-model="nextStatus" class="form-input" style="width: 160px;">
              <option value="" disabled>Select Status...</option>
              <option 
                v-for="status in allowedTransitions" 
                :key="status" 
                :value="status"
              >
                {{ status.toUpperCase() }}
              </option>
            </select>
            <button 
              class="btn btn--primary" 
              :disabled="!nextStatus || statusUpdating"
              @click="submitStatusChange"
            >
              Update
            </button>
          </div>
        </div>
      </div>

      <!-- Items List -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Order Items</div>
        
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div 
            v-for="item in order.items" 
            :key="item.id" 
            style="display: flex; justify-content: space-between; align-items: center; padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-border);"
          >
            <div style="display: flex; align-items: center; gap: var(--spacing-md);">
              <div style="width: 50px; height: 50px; border-radius: 6px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                👕
              </div>
              <div>
                <div style="font-weight: bold; color: #1e293b;">{{ item.product_name }}</div>
                <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.125rem;">
                  SKU: {{ item.sku }} <span v-if="item.variant_name">| {{ item.variant_name }}</span>
                </div>
              </div>
            </div>
            
            <div style="text-align: right;">
              <div style="font-weight: bold; color: #1e293b;">₹{{ parseFloat(item.total_price).toFixed(2) }}</div>
              <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.125rem;">
                ₹{{ parseFloat(item.unit_price).toFixed(2) }} × {{ item.quantity }}
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
            <span style="color: var(--color-text-muted);">CGST (9%):</span>
            <span style="color: #1e293b; font-weight: 500;">₹{{ (parseFloat(order.tax_amount) / 2).toFixed(2) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; width: 280px; font-size: 0.9rem;">
            <span style="color: var(--color-text-muted);">SGST (9%):</span>
            <span style="color: #1e293b; font-weight: 500;">₹{{ (parseFloat(order.tax_amount) / 2).toFixed(2) }}</span>
          </div>
          <div style="display: flex; justify-content: space-between; width: 280px; font-size: 1.1rem; border-top: 1px solid var(--color-border); padding-top: var(--spacing-sm); font-weight: bold; margin-top: var(--spacing-xs);">
            <span style="color: #1e293b;">Grand Total:</span>
            <span style="color: var(--color-primary);">₹{{ parseFloat(order.grand_total).toFixed(2) }}</span>
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

      <!-- Shipping Tracking Details Update -->
      <div class="glass-panel" style="padding: var(--spacing-lg);" v-if="['confirmed', 'processing', 'shipped', 'delivered'].includes(order.status)">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Courier & Tracking Info</div>
        <form @submit.prevent="submitShippingUpdate" class="responsive-grid-1-1" style="gap: var(--spacing-md);">
          <div class="form-group">
            <label class="form-label">Courier Name</label>
            <input type="text" v-model="shippingForm.courier_name" placeholder="e.g. Delhivery, Bluedart" class="form-input" required />
          </div>
          <div class="form-group">
            <label class="form-label">Tracking Number</label>
            <input type="text" v-model="shippingForm.tracking_number" placeholder="Tracking ID..." class="form-input" required />
          </div>
          <div class="form-group" style="grid-column: 1 / -1;">
            <button type="submit" class="btn btn--secondary btn--sm" :disabled="shippingUpdating">
              Update Shipping details
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Right Column: Context details (Customer, Logs, Admin Notes) -->
    <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
      <!-- Customer Information Card -->
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
          </div>
        </div>
        <router-link :to="`/admin/customers/${order.user_id}`" class="btn btn--secondary btn--sm" style="display: block; text-align: center; text-decoration: none;">
          View Customer Profile
        </router-link>
      </div>

      <!-- Admin Notes Card -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Administrative Comments</div>
        
        <div style="max-height: 200px; overflow-y: auto; margin-bottom: var(--spacing-md); display: flex; flex-direction: column; gap: var(--spacing-sm);">
          <div 
            v-for="(note, idx) in formattedNotes" 
            :key="idx" 
            style="font-size: 0.8rem; background: rgba(255,255,255,0.03); border: 1px solid var(--color-border); border-radius: 6px; padding: var(--spacing-sm);"
          >
            {{ note }}
          </div>
          <div v-if="formattedNotes.length === 0" style="font-size: 0.8rem; color: var(--color-text-muted); text-align: center; padding: var(--spacing-md);">
            No admin notes added.
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
          <textarea 
            v-model="newNote" 
            placeholder="Add internal comment..." 
            class="form-input" 
            style="font-size: 0.8rem; height: 60px; resize: none;"
          ></textarea>
          <button 
            class="btn btn--secondary btn--sm" 
            :disabled="!newNote.trim() || noteAdding"
            @click="submitAddNote"
          >
            Post Note
          </button>
        </div>
      </div>

      <!-- Timeline Logs -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Order Timeline Logs</div>
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md); position: relative; padding-left: var(--spacing-md);">
          <!-- Center line -->
          <div style="position: absolute; left: 6px; top: 8px; bottom: 8px; width: 2px; background: var(--color-border);"></div>
          
          <div 
            v-for="log in order.status_history" 
            :key="log.id" 
            style="position: relative; font-size: 0.8rem;"
          >
            <!-- Timeline dot -->
            <div style="position: absolute; left: -19px; top: 4px; width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary); border: 2px solid var(--color-bg);"></div>
            
            <div style="font-weight: bold; color: #1e293b;">
              Status changed to: 
              <span :class="['badge', getStatusBadgeClass(log.to_status)]" style="font-size: 0.65rem; padding: 0px 4px;">
                {{ log.to_status }}
              </span>
            </div>
            <div style="color: var(--color-text-muted); font-size: 0.75rem; margin-top: 0.125rem;">
              {{ log.comment }}
            </div>
            <div style="font-size: 0.7rem; color: var(--color-text-muted); margin-top: 0.125rem;">
              {{ formatDate(log.created_at) }}
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
const statusUpdating = ref(false);

const shippingForm = ref({
  courier_name: '',
  tracking_number: '',
});
const shippingUpdating = ref(false);

const newNote = ref('');
const noteAdding = ref(false);

const fetchOrder = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/admin/orders/${orderId}`);
    if (response.data && response.data.success) {
      order.value = response.data.data;
      shippingForm.value.courier_name = order.value.courier_name || '';
      shippingForm.value.tracking_number = order.value.tracking_number || '';
    }
  } catch (err) {
    console.error('Failed to load order:', err);
    error.value = 'Failed to load customer order details';
  } finally {
    loading.value = false;
  }
};

const customerInitials = computed(() => {
  const f = order.value.shipping_first_name || '';
  const l = order.value.shipping_last_name || '';
  return (f.charAt(0) + l.charAt(0)).toUpperCase() || 'JD';
});

const formattedNotes = computed(() => {
  if (!order.value.admin_notes) return [];
  return order.value.admin_notes.split('\n').filter(n => n.trim() !== '');
});

const allowedTransitions = computed(() => {
  const current = order.value.status;
  switch (current) {
    case 'pending': return ['confirmed', 'cancelled'];
    case 'confirmed': return ['processing', 'cancelled'];
    case 'processing': return ['shipped', 'cancelled'];
    case 'shipped': return ['delivered'];
    case 'delivered': return ['returned'];
    default: return [];
  }
});

const submitStatusChange = async () => {
  if (!nextStatus.value) return;
  statusUpdating.value = true;
  try {
    const response = await axios.put(`/api/admin/orders/${orderId}/status`, {
      status: nextStatus.value,
      comment: `Status updated via Admin Panel controls to ${nextStatus.value}.`
    });

    if (response.data && response.data.success) {
      nextStatus.value = '';
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
      courier_name: shippingForm.value.courier_name,
      tracking_number: shippingForm.value.tracking_number,
    });

    if (response.data && response.data.success) {
      await fetchOrder();
      alert('Courier tracking information updated successfully');
    }
  } catch (err) {
    console.error('Failed to update shipping information:', err);
    alert('Failed to update tracking details');
  } finally {
    shippingUpdating.value = false;
  }
};

const submitAddNote = async () => {
  if (!newNote.value.trim()) return;
  noteAdding.value = true;
  try {
    const response = await axios.post(`/api/admin/orders/${orderId}/notes`, {
      note: newNote.value.trim()
    });

    if (response.data && response.data.success) {
      newNote.value = '';
      await fetchOrder();
    }
  } catch (err) {
    console.error('Failed to append internal note:', err);
    alert('Failed to add internal note');
  } finally {
    noteAdding.value = false;
  }
};

const printMockInvoice = () => {
  const content = `
    INVOICE MASTER (MOCK PRINT)
    --------------------------
    Order Number: ${order.value.order_number}
    Placed Date: ${formatDate(order.value.created_at)}
    
    Bill To:
    ${order.value.billing_first_name} ${order.value.billing_last_name}
    ${order.value.billing_address_line_1}
    ${order.value.billing_city}, ${order.value.billing_state}
    Phone: ${order.value.billing_phone}
    
    Items ordered:
    ${order.value.items.map(i => `- ${i.product_name} [${i.sku}] (x${i.quantity}) - ₹${parseFloat(i.total_price).toFixed(2)}`).join('\n')}
    
    Subtotal: ₹${parseFloat(order.value.subtotal).toFixed(2)}
    GST Tax (18%): ₹${parseFloat(order.value.tax_amount).toFixed(2)}
    Grand Total: ₹${parseFloat(order.value.grand_total).toFixed(2)}
    
    --------------------------
    Thank you for shopping with Vibe Clothing Company!
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

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'pending': return 'badge--warning';
    case 'confirmed': return 'badge--secondary';
    case 'processing': return 'badge--secondary';
    case 'shipped': return 'badge--secondary';
    case 'delivered': return 'badge--success';
    case 'cancelled': return 'badge--danger';
    default: return '';
  }
};

onMounted(() => {
  fetchOrder();
});
</script>
