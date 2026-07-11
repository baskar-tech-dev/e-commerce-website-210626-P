<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Store & System Settings</h1>
      <span class="admin-page__subtitle">Configure general business variables, active payment gateway toggles, and SMTP email parameters.</span>
    </div>
  </div>

  <div class="responsive-grid-1-3" style="gap: var(--spacing-lg); margin-top: var(--spacing-md);">
    <!-- Left Column: Tab list -->
    <div class="glass-panel" style="padding: var(--spacing-md); height: fit-content; display: flex; flex-direction: column; gap: var(--spacing-xs);">
      <button 
        v-for="tab in ['general', 'payment', 'email']" 
        :key="tab"
        type="button"
        @click="activeTab = tab"
        :class="['btn', activeTab === tab ? 'btn--primary' : 'btn--secondary']"
        style="text-align: left; text-transform: capitalize; justify-content: flex-start;"
      >
        <span v-if="tab === 'general'">🏪 General Store</span>
        <span v-else-if="tab === 'payment'">💳 Payment Gateways</span>
        <span v-else-if="tab === 'email'">✉️ Notifications (SMTP)</span>
      </button>
    </div>

    <!-- Right Column: Settings Tab Pane Form -->
    <div class="glass-panel" style="padding: var(--spacing-lg);">
      <div v-if="loading" style="text-align: center; padding: 4rem;">
        <div class="stat-card__value" style="font-size: 1.2rem;">Loading store settings...</div>
      </div>

      <form v-else @submit.prevent="saveSettings">
        <!-- Tab 1: General Details -->
        <div v-if="activeTab === 'general'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-xs);">General Details</div>
          
          <div class="form-group">
            <label class="form-label">Store Profile Name *</label>
            <input type="text" v-model="settings.general.store_name" class="form-input" required />
          </div>

          <div class="responsive-grid-1-1" style="gap: var(--spacing-md);">
            <div class="form-group">
              <label class="form-label">Customer Support Email *</label>
              <input type="email" v-model="settings.general.contact_email" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">Contact Helpline Phone *</label>
              <input type="text" v-model="settings.general.contact_phone" class="form-input" required />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Store Currency Setting *</label>
            <select v-model="settings.general.currency" class="form-input" required>
              <option value="INR">INR (₹) - Indian Rupee</option>
              <option value="USD">USD ($) - US Dollar</option>
              <option value="EUR">EUR (€) - Euro</option>
              <option value="GBP">GBP (£) - British Pound</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Primary Warehouse Address</label>
            <textarea v-model="settings.general.store_address" class="form-input" style="height: 80px; resize: vertical;"></textarea>
          </div>
        </div>

        <!-- Tab 2: Payment Gateways -->
        <div v-if="activeTab === 'payment'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-xs);">Active Gateways Toggles</div>

          <!-- COD Active -->
          <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer;">
              <input type="checkbox" v-model="settings.payment.cod_active" />
              Cash on Delivery (COD)
            </label>
            <span style="font-size: 0.75rem; color: var(--color-text-muted); margin-left: 1.5rem;">
              Enables buyers to pay at their doorstep in cash upon receiving package shipments.
            </span>
          </div>

          <!-- Razorpay Active -->
          <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
              <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer;">
                <input type="checkbox" v-model="settings.payment.razorpay_active" />
                Razorpay Payment Gateway API
              </label>
              <span style="font-size: 0.75rem; color: var(--color-text-muted); margin-left: 1.5rem;">
                Enables online credit/debit card, netbanking, and UPI checkout options.
              </span>
            </div>

            <!-- Razorpay Config Inputs -->
            <div v-if="settings.payment.razorpay_active" class="responsive-grid-1-1" style="gap: var(--spacing-md); margin-left: 1.5rem;">
              <div class="form-group">
                <label class="form-label">Razorpay Key ID *</label>
                <input type="text" v-model="settings.payment.razorpay_key" class="form-input" required />
              </div>
              <div class="form-group">
                <label class="form-label">Razorpay Key Secret *</label>
                <input type="password" v-model="settings.payment.razorpay_secret" class="form-input" required />
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 3: Notification (SMTP) -->
        <div v-if="activeTab === 'email'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-xs);">Notifications (SMTP Host)</div>

          <div class="responsive-grid-3-1" style="gap: var(--spacing-md);">
            <div class="form-group">
              <label class="form-label">SMTP Mailer Hostserver *</label>
              <input type="text" v-model="settings.email.smtp_host" placeholder="smtp.mailgun.org" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">SMTP Port *</label>
              <input type="number" v-model="settings.email.smtp_port" placeholder="587" class="form-input" required />
            </div>
          </div>

          <div class="responsive-grid-1-1" style="gap: var(--spacing-md);">
            <div class="form-group">
              <label class="form-label">SMTP Username *</label>
              <input type="text" v-model="settings.email.smtp_username" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">SMTP Password *</label>
              <input type="password" v-model="settings.email.smtp_password" class="form-input" required />
            </div>
          </div>

          <div class="responsive-grid-1-1" style="gap: var(--spacing-md);">
            <div class="form-group">
              <label class="form-label">Sender Name (display) *</label>
              <input type="text" v-model="settings.email.sender_name" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">Sender Email Address *</label>
              <input type="email" v-model="settings.email.sender_email" class="form-input" required />
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div style="margin-top: var(--spacing-lg); padding-top: var(--spacing-md); border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: var(--spacing-md); align-items: center;">
          <span v-if="successMsg" style="color: var(--color-success); font-size: 0.9rem; font-weight: bold;">
            {{ successMsg }}
          </span>
          <button type="submit" class="btn btn--primary" :disabled="saving">
            {{ saving ? 'Saving configurations...' : '💾 Save Settings' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const activeTab = ref('general');
const loading = ref(true);
const saving = ref(false);
const successMsg = ref('');

const settings = ref({
  general: {
    store_name: '',
    contact_email: '',
    contact_phone: '',
    currency: 'INR',
    store_address: '',
  },
  payment: {
    cod_active: true,
    razorpay_active: false,
    razorpay_key: '',
    razorpay_secret: '',
  },
  email: {
    smtp_host: '',
    smtp_port: 587,
    smtp_username: '',
    smtp_password: '',
    sender_name: '',
    sender_email: '',
  }
});

const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/settings');
    if (response.data && response.data.success) {
      const data = response.data.data;
      
      // Merge with defaults to prevent null crashes
      if (data.general) settings.value.general = { ...settings.value.general, ...data.general };
      if (data.payment) {
        settings.value.payment = {
          ...settings.value.payment,
          ...data.payment,
          cod_active: filterBool(data.payment.cod_active),
          razorpay_active: filterBool(data.payment.razorpay_active)
        };
      }
      if (data.email) settings.value.email = { ...settings.value.email, ...data.email };
    }
  } catch (err) {
    console.error('Failed to load store settings:', err);
  } finally {
    loading.value = false;
  }
};

const filterBool = (val) => {
  if (typeof val === 'boolean') return val;
  return val === '1' || val === 1 || val === 'true';
};

const saveSettings = async () => {
  saving.value = true;
  successMsg.value = '';
  try {
    const payload = {
      settings: {
        general: { ...settings.value.general },
        payment: {
          ...settings.value.payment,
          cod_active: settings.value.payment.cod_active ? '1' : '0',
          razorpay_active: settings.value.payment.razorpay_active ? '1' : '0',
        },
        email: { ...settings.value.email }
      }
    };

    const response = await axios.post('/api/admin/settings/batch', payload);
    if (response.data && response.data.success) {
      successMsg.value = '✓ Store settings updated successfully!';
      setTimeout(() => {
        successMsg.value = '';
      }, 4000);
      await fetchSettings();
    }
  } catch (err) {
    console.error('Failed to save settings:', err);
    alert(err.response?.data?.message || 'Failed to save store configurations');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
