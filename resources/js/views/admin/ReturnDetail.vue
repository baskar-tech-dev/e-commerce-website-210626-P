<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <router-link to="/admin/returns" style="text-decoration: none; color: var(--color-primary); font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
        ◀ Back to Returns
      </router-link>
      <h1 class="admin-page__title">Return Details: {{ returnRequest.return_number }}</h1>
      <span class="admin-page__subtitle">Initiated on {{ formatDate(returnRequest.created_at) }}</span>
    </div>
  </div>

  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Loading return request details...</div>
  </div>

  <div v-else-if="error" class="badge badge--danger" style="padding: 1rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ error }}
  </div>

  <div v-else class="responsive-grid-2-1" style="gap: var(--spacing-lg);">
    <!-- Left Column: Return info, items, and status workflow controls -->
    <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
      <!-- Core Return Properties Card -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
          <div>
            <div class="card-header-title">Request Status</div>
            <span :class="['badge', getStatusBadgeClass(returnRequest.status)]" style="font-size: 0.9rem; margin-top: 0.25rem;">
              {{ returnRequest.status.replace('_', ' ').toUpperCase() }}
            </span>
          </div>

          <div>
            <div class="card-header-title" style="text-align: right;">Return Reason</div>
            <span style="font-weight: bold; color: #1e293b; text-transform: capitalize;">
              {{ returnRequest.reason.replace('_', ' ') }}
            </span>
          </div>
        </div>

        <div style="border-top: 1px solid var(--color-border); padding-top: var(--spacing-md); margin-top: var(--spacing-md);" v-if="returnRequest.description">
          <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">Customer Statement:</div>
          <p style="color: #1e293b; line-height: 1.5; font-size: 0.9rem; font-style: italic;">
            "{{ returnRequest.description }}"
          </p>
        </div>
      </div>

      <!-- Workflow Action Panel -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Return Lifecycle Actions</div>

        <!-- 1. Requested State: Approve or Reject -->
        <div v-if="returnRequest.status === 'requested'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div style="display: flex; gap: var(--spacing-md);">
            <button class="btn btn--primary" @click="handleStatusChange('approved')" :disabled="statusUpdating">
              ✅ Approve Return
            </button>
            <button class="btn btn--danger" @click="showRejectionInput = !showRejectionInput" :disabled="statusUpdating">
              ❌ Reject Return
            </button>
          </div>

          <div v-if="showRejectionInput" style="margin-top: var(--spacing-sm); display: flex; flex-direction: column; gap: var(--spacing-sm);">
            <label class="form-label">Rejection Reason *</label>
            <input type="text" v-model="rejectionReason" placeholder="Describe why return is rejected..." class="form-input" required />
            <button class="btn btn--danger btn--sm" style="align-self: flex-start;" @click="submitRejection" :disabled="!rejectionReason.trim() || statusUpdating">
              Confirm Rejection
            </button>
          </div>
        </div>

        <!-- 2. Approved State: Schedule Pickup -->
        <div v-else-if="returnRequest.status === 'approved'">
          <button class="btn btn--primary" @click="handleStatusChange('pickup_scheduled')" :disabled="statusUpdating">
            📅 Schedule Logistics Pickup
          </button>
        </div>

        <!-- 3. Pickup Scheduled: Mark Picked Up -->
        <div v-else-if="returnRequest.status === 'pickup_scheduled'">
          <button class="btn btn--primary" @click="handleStatusChange('picked_up')" :disabled="statusUpdating">
            🚚 Confirm Shipment Picked Up
          </button>
        </div>

        <!-- 4. Picked Up: QC Check Passed or Failed -->
        <div v-else-if="returnRequest.status === 'picked_up'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div>
            <div style="font-weight: 500; color: #1e293b; margin-bottom: 0.5rem;">Configure QC Result for each item:</div>
            
            <div v-for="item in returnRequest.items" :key="item.id" style="background: rgba(255,255,255,0.03); border: 1px solid var(--color-border); border-radius: 6px; padding: var(--spacing-md); margin-bottom: var(--spacing-sm);">
              <div style="font-weight: bold; color: #1e293b;">{{ item.order_item?.product_name }}</div>
              <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-bottom: 0.5rem;">SKU: {{ item.order_item?.sku }} | Qty: {{ item.quantity }}</div>
              
              <div style="display: flex; gap: var(--spacing-lg); align-items: center; margin-bottom: var(--spacing-xs);">
                <label style="display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer; color: #1e293b; font-size: 0.85rem;">
                  <input type="radio" :name="`qc_${item.id}`" value="passed" v-model="qcStates[item.id]" />
                  QC Passed (Restock variant)
                </label>
                <label style="display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer; color: #1e293b; font-size: 0.85rem;">
                  <input type="radio" :name="`qc_${item.id}`" value="failed" v-model="qcStates[item.id]" />
                  QC Failed (Damaged variant)
                </label>
              </div>
              <input type="text" v-model="qcNotes[item.id]" placeholder="QC inspector comments (optional)..." class="form-input" style="font-size: 0.8rem; margin-top: 0.25rem;" />
            </div>
          </div>

          <div style="display: flex; gap: var(--spacing-md);">
            <button class="btn btn--primary" @click="submitQC('qc_passed')" :disabled="statusUpdating">
              ✅ Log QC Complete
            </button>
          </div>
        </div>

        <!-- 5. QC Passed / Approved: Process Refund trigger -->
        <div v-else-if="['qc_passed', 'approved'].includes(returnRequest.status)" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md);">
            <div style="font-weight: bold; color: #1e293b; margin-bottom: var(--spacing-md);">Process Financial Refund</div>
            
            <div class="responsive-grid-1-2" style="gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
              <div class="form-group">
                <label class="form-label">Refund Amount (INR) *</label>
                <input type="number" step="0.01" v-model="refundForm.amount" class="form-input" required />
              </div>
              <div class="form-group">
                <label class="form-label">Reason for Refund *</label>
                <input type="text" v-model="refundForm.reason" placeholder="e.g. Return QC approved refund" class="form-input" required />
              </div>
            </div>
            
            <button class="btn btn--primary" @click="submitRefund" :disabled="refundUpdating || !refundForm.amount || !refundForm.reason.trim()">
              💸 Issue Refund Payment
            </button>
          </div>
        </div>

        <div v-else-if="returnRequest.status === 'refunded'" style="color: var(--color-success); font-weight: 500; display: flex; align-items: center; gap: var(--spacing-xs);">
          <span>🎉</span> Refund issued and return workflow completed.
        </div>

        <div v-else-if="returnRequest.status === 'rejected'" style="color: var(--color-danger); font-weight: 500;">
          🚫 Return request has been rejected. Reason: {{ returnRequest.rejection_reason }}
        </div>
      </div>

      <!-- Return Items List -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Items Selected For Return</div>
        
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div 
            v-for="item in returnRequest.items" 
            :key="item.id" 
            style="display: flex; justify-content: space-between; align-items: center; padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--color-border);"
          >
            <div style="display: flex; align-items: center; gap: var(--spacing-md);">
              <div style="width: 50px; height: 50px; border-radius: 6px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                📦
              </div>
              <div>
                <div style="font-weight: bold; color: #1e293b;">{{ item.order_item?.product_name || 'Product Variant' }}</div>
                <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.125rem;">
                  SKU: {{ item.order_item?.sku }} | Return Qty: {{ item.quantity }}
                </div>
                <div v-if="item.qc_status" style="margin-top: 0.25rem;">
                  <span :class="['badge', item.qc_status === 'passed' ? 'badge--success' : 'badge--danger']" style="font-size: 0.65rem;">
                    QC: {{ item.qc_status.toUpperCase() }}
                  </span>
                  <span style="font-size: 0.75rem; color: var(--color-text-muted); margin-left: 0.25rem;" v-if="item.qc_notes">
                    ({{ item.qc_notes }})
                  </span>
                </div>
              </div>
            </div>
            
            <div style="text-align: right;">
              <div style="font-weight: bold; color: #1e293b;">₹{{ (item.order_item?.unit_price * item.quantity).toFixed(2) }}</div>
              <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.125rem;">
                ₹{{ parseFloat(item.order_item?.unit_price).toFixed(2) }} × {{ item.quantity }}
              </div>
            </div>
          </div>
        </div>

        <!-- Photos Upload Checklist -->
        <div v-if="customerPhotos.length > 0" style="margin-top: var(--spacing-lg);">
          <div style="font-weight: 500; color: #1e293b; margin-bottom: 0.5rem;">Customer Uploaded Images:</div>
          <div style="display: flex; gap: var(--spacing-sm); flex-wrap: wrap;">
            <a v-for="(photo, idx) in customerPhotos" :key="idx" :href="photo" target="_blank" style="width: 80px; height: 80px; border-radius: 6px; overflow: hidden; border: 1px solid var(--color-border); background: rgba(255,255,255,0.03); display: flex; align-items: center; justify-content: center;">
              <img :src="photo" style="max-width: 100%; max-height: 100%; object-fit: cover;" />
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Info blocks, logs, notes -->
    <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
      <!-- Customer Information Card -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Customer Info</div>
        <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
          <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: bold;">
            {{ customerInitials }}
          </div>
          <div>
            <div style="font-weight: bold; color: #1e293b;">
              {{ returnRequest.user?.first_name }} {{ returnRequest.user?.last_name }}
            </div>
            <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.125rem;">
              {{ returnRequest.user?.email }}
            </div>
          </div>
        </div>
        <router-link :to="`/admin/customers/${returnRequest.user_id}`" class="btn btn--secondary btn--sm" style="display: block; text-align: center; text-decoration: none;">
          View Customer Profile
        </router-link>
      </div>

      <!-- Related Order Context Card -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Related Order</div>
        <div style="display: flex; flex-direction: column; gap: var(--spacing-xs); font-size: 0.85rem; color: #1e293b;">
          <div style="display: flex; justify-content: space-between;">
            <span style="color: var(--color-text-muted);">Order Number:</span>
            <router-link :to="`/admin/orders/${returnRequest.order_id}`" style="color: var(--color-primary); text-decoration: none; font-weight: bold;">
              {{ returnRequest.order?.order_number }}
            </router-link>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span style="color: var(--color-text-muted);">Order Total:</span>
            <span>₹{{ returnRequest.order ? parseFloat(returnRequest.order.grand_total).toFixed(2) : '—' }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span style="color: var(--color-text-muted);">Payment Method:</span>
            <span style="text-transform: uppercase;">{{ returnRequest.order?.payment_method }}</span>
          </div>
        </div>
      </div>

      <!-- Administrative Comments Card -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Internal Staff Comments</div>
        
        <div style="max-height: 200px; overflow-y: auto; margin-bottom: var(--spacing-md); display: flex; flex-direction: column; gap: var(--spacing-sm);">
          <div 
            v-for="(note, idx) in formattedNotes" 
            :key="idx" 
            style="font-size: 0.8rem; background: rgba(255,255,255,0.03); border: 1px solid var(--color-border); border-radius: 6px; padding: var(--spacing-sm);"
          >
            {{ note }}
          </div>
          <div v-if="formattedNotes.length === 0" style="font-size: 0.8rem; color: var(--color-text-muted); text-align: center; padding: var(--spacing-md);">
            No administrative notes logged.
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
          <textarea 
            v-model="newNote" 
            placeholder="Add internal Staff notes..." 
            class="form-input" 
            style="font-size: 0.8rem; height: 60px; resize: none;"
          ></textarea>
          <button 
            class="btn btn--secondary btn--sm" 
            :disabled="!newNote.trim() || noteAdding"
            @click="submitAddNote"
          >
            Post Comment
          </button>
        </div>
      </div>

      <!-- Timeline Logs Card -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Workflow Timeline</div>
        <div style="display: flex; flex-direction: column; gap: var(--spacing-sm); font-size: 0.8rem; color: #1e293b;">
          <div v-if="returnRequest.created_at">
            📅 <strong>Requested:</strong> {{ formatDate(returnRequest.created_at) }}
          </div>
          <div v-if="returnRequest.approved_at">
            ✅ <strong>Approved:</strong> {{ formatDate(returnRequest.approved_at) }}
          </div>
          <div v-if="returnRequest.pickup_scheduled_at">
            📅 <strong>Pickup Booked:</strong> {{ formatDate(returnRequest.pickup_scheduled_at) }}
          </div>
          <div v-if="returnRequest.picked_up_at">
            🚚 <strong>Picked Up:</strong> {{ formatDate(returnRequest.picked_up_at) }}
          </div>
          <div v-if="returnRequest.qc_completed_at">
            🔬 <strong>QC Completed:</strong> {{ formatDate(returnRequest.qc_completed_at) }}
          </div>
          <div v-if="returnRequest.rejected_at">
            🚫 <strong>Rejected:</strong> {{ formatDate(returnRequest.rejected_at) }}
          </div>
          <div v-if="returnRequest.status === 'refunded' && returnRequest.updated_at">
            💸 <strong>Refunded:</strong> {{ formatDate(returnRequest.updated_at) }}
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
const returnId = route.params.id;

const returnRequest = ref({});
const loading = ref(true);
const error = ref('');

const statusUpdating = ref(false);
const showRejectionInput = ref(false);
const rejectionReason = ref('');

const qcStates = ref({});
const qcNotes = ref({});

const refundForm = ref({
  amount: 0.00,
  reason: 'Return QC approved refund'
});
const refundUpdating = ref(false);

const newNote = ref('');
const noteAdding = ref(false);

const fetchReturnRequest = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/admin/returns/${returnId}`);
    if (response.data && response.data.success) {
      returnRequest.value = response.data.data;
      
      // Pre-fill refund amount
      refundForm.value.amount = returnRequest.value.refund_amount || 0.00;
      
      // Initialize QC states
      returnRequest.value.items.forEach(item => {
        qcStates.value[item.id] = item.qc_status || 'passed';
        qcNotes.value[item.id] = item.qc_notes || '';
      });
    }
  } catch (err) {
    console.error('Failed to load return details:', err);
    error.value = 'Failed to load product return details';
  } finally {
    loading.value = false;
  }
};

const customerInitials = computed(() => {
  const f = returnRequest.value.user?.first_name || '';
  const l = returnRequest.value.user?.last_name || '';
  return (f.charAt(0) + l.charAt(0)).toUpperCase() || 'JD';
});

const formattedNotes = computed(() => {
  if (!returnRequest.value.admin_notes) return [];
  return returnRequest.value.admin_notes.split('\n').filter(n => n.trim() !== '');
});

const customerPhotos = computed(() => {
  if (!returnRequest.value.items) return [];
  const photos = [];
  returnRequest.value.items.forEach(item => {
    if (item.photos && Array.isArray(item.photos)) {
      photos.push(...item.photos);
    }
  });
  return photos;
});

const handleStatusChange = async (nextStatus) => {
  statusUpdating.value = true;
  try {
    const response = await axios.put(`/api/admin/returns/${returnId}/status`, {
      status: nextStatus
    });

    if (response.data && response.data.success) {
      await fetchReturnRequest();
    }
  } catch (err) {
    console.error('Failed to update status:', err);
    alert(err.response?.data?.message || 'Failed to update return status');
  } finally {
    statusUpdating.value = false;
  }
};

const submitRejection = async () => {
  if (!rejectionReason.value.trim()) return;
  statusUpdating.value = true;
  try {
    const response = await axios.put(`/api/admin/returns/${returnId}/status`, {
      status: 'rejected',
      rejection_reason: rejectionReason.value.trim()
    });

    if (response.data && response.data.success) {
      showRejectionInput.value = false;
      rejectionReason.value = '';
      await fetchReturnRequest();
    }
  } catch (err) {
    console.error('Failed to reject return:', err);
    alert(err.response?.data?.message || 'Failed to reject return request');
  } finally {
    statusUpdating.value = false;
  }
};

const submitQC = async (targetStatus) => {
  statusUpdating.value = true;
  const qcItems = returnRequest.value.items.map(item => {
    return {
      item_id: item.id,
      qc_status: qcStates.value[item.id],
      qc_notes: qcNotes.value[item.id]
    };
  });

  try {
    const response = await axios.put(`/api/admin/returns/${returnId}/status`, {
      status: targetStatus,
      qc_items: qcItems
    });

    if (response.data && response.data.success) {
      await fetchReturnRequest();
    }
  } catch (err) {
    console.error('Failed to submit QC details:', err);
    alert(err.response?.data?.message || 'Failed to log QC inspections');
  } finally {
    statusUpdating.value = false;
  }
};

const submitRefund = async () => {
  if (!refundForm.value.amount || !refundForm.value.reason.trim()) return;
  
  if (!confirm('Are you sure you want to process the refund payment? This is final.')) {
    return;
  }

  refundUpdating.value = true;
  try {
    const response = await axios.post(`/api/admin/returns/${returnId}/refund`, {
      amount: refundForm.value.amount,
      reason: refundForm.value.reason,
    });

    if (response.data && response.data.success) {
      await fetchReturnRequest();
      alert('Credit refund payment triggered and completed successfully');
    }
  } catch (err) {
    console.error('Failed to issue refund payment:', err);
    alert(err.response?.data?.message || 'Failed to issue refund payment');
  } finally {
    refundUpdating.value = false;
  }
};

const submitAddNote = async () => {
  if (!newNote.value.trim()) return;
  noteAdding.value = true;
  try {
    const response = await axios.post(`/api/admin/returns/${returnId}/notes`, {
      note: newNote.value.trim()
    });

    if (response.data && response.data.success) {
      newNote.value = '';
      await fetchReturnRequest();
    }
  } catch (err) {
    console.error('Failed to append admin note:', err);
    alert('Failed to log admin comment');
  } finally {
    noteAdding.value = false;
  }
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
    case 'requested': return 'badge--warning';
    case 'approved': return 'badge--secondary';
    case 'rejected': return 'badge--danger';
    case 'pickup_scheduled': return 'badge--secondary';
    case 'picked_up': return 'badge--secondary';
    case 'qc_passed': return 'badge--primary';
    case 'qc_failed': return 'badge--danger';
    case 'refunded': return 'badge--success';
    default: return '';
  }
};

onMounted(() => {
  fetchReturnRequest();
});
</script>
