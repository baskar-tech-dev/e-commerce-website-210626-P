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
        v-for="tab in ['general', 'payment', 'email', 'announcement']" 
        :key="tab"
        type="button"
        @click="activeTab = tab"
        :class="['btn', activeTab === tab ? 'btn--primary' : 'btn--secondary']"
        style="text-align: left; text-transform: capitalize; justify-content: flex-start;"
      >
        <span v-if="tab === 'general'">🏪 General Store</span>
        <span v-else-if="tab === 'payment'">💳 Payment Gateways</span>
        <span v-else-if="tab === 'email'">✉️ Notifications (SMTP)</span>
        <span v-else-if="tab === 'announcement'">📢 Announcement Ticker</span>
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

        <!-- Tab 4: Announcement Ticker Master -->
        <div v-if="activeTab === 'announcement'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-xs);">Storefront Announcement Ticker</div>

          <!-- Live Preview Banner Block -->
          <div style="margin-bottom: var(--spacing-md);">
            <label class="form-label" style="font-weight: bold; display: flex; align-items: center; gap: 8px;">
              ✨ Live Interactive Preview (Unsaved Changes)
            </label>
            <div style="border: 1px solid var(--color-border); border-radius: 8px; overflow: hidden; background: #f8fafc; padding: 10px;">
              <StorefrontAnnouncementBar 
                :preview-items="settings.announcement.items" 
                :preview-config="settings.announcement.config" 
              />
            </div>
          </div>

          <!-- Enable & Sticky Toggle -->
          <div class="responsive-grid-1-1" style="gap: var(--spacing-md); grid-template-columns: 1fr 1fr;">
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
              <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer; user-select: none;">
                <input type="checkbox" v-model="settings.announcement.config.is_enabled" />
                Enable Announcement Bar
              </label>
              <span style="font-size: 0.75rem; color: var(--color-text-muted);">
                Toggles the visibility of the announcement bar on the storefront header.
              </span>
            </div>

            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-md); display: flex; flex-direction: column; gap: 0.25rem;">
              <label style="display: flex; align-items: center; gap: 0.5rem; color: #1e293b; font-weight: bold; cursor: pointer; user-select: none;">
                <input type="checkbox" v-model="settings.announcement.config.is_sticky" />
                Sticky Position
              </label>
              <span style="font-size: 0.75rem; color: var(--color-text-muted);">
                Keeps the announcement bar fixed at the top of the viewport when scrolling.
              </span>
            </div>
          </div>

          <!-- Style Configuration (Mode, Speed, Colors) -->
          <div class="responsive-grid-1-1" style="gap: var(--spacing-md); grid-template-columns: 1fr 1fr;">
            <!-- Animation Mode & Speed -->
            <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
              <div class="form-group">
                <label class="form-label">Ticker Scrolling Mode</label>
                <select v-model="settings.announcement.config.mode" class="form-input">
                  <option value="marquee">Seamless Continuous Ticker (Marquee)</option>
                  <option value="slide">One-by-One Slide Carousel</option>
                  <option value="fade">One-by-One Fade Carousel</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">
                  Animation Speed / Slide Interval: 
                  <strong>{{ settings.announcement.config.speed }}s</strong>
                </label>
                <input 
                  type="range" 
                  v-model.number="settings.announcement.config.speed" 
                  min="2" 
                  max="30" 
                  step="1"
                  class="form-input" 
                  style="cursor: pointer; height: 10px; padding: 0;"
                />
                <span style="font-size: 0.75rem; color: var(--color-text-muted);">
                  Sets transition timer for slider/fade mode, or duration factor for marquee.
                </span>
              </div>
            </div>

            <!-- Custom Colors -->
            <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
              <div class="form-group">
                <label class="form-label">Background Color</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                  <input type="color" v-model="settings.announcement.config.background_color" style="width: 44px; height: 44px; border: 1px solid var(--color-border); border-radius: 4px; padding: 2px; cursor: pointer;" />
                  <input type="text" v-model="settings.announcement.config.background_color" class="form-input" style="flex: 1;" placeholder="#493b54" />
                </div>
                <!-- Color Presets -->
                <div style="display: flex; gap: var(--spacing-xs); margin-top: 6px;">
                  <button 
                    v-for="color in ['#493b54', '#D4AF37', '#1C1C1C', '#FFFDF9', '#E53E3E']" 
                    :key="color"
                    type="button"
                    @click="settings.announcement.config.background_color = color"
                    style="width: 20px; height: 20px; border-radius: 50%; border: 1px solid #cbd5e1; cursor: pointer;"
                    :style="{ backgroundColor: color }"
                    :title="color"
                  ></button>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Text & Icon Color</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                  <input type="color" v-model="settings.announcement.config.text_color" style="width: 44px; height: 44px; border: 1px solid var(--color-border); border-radius: 4px; padding: 2px; cursor: pointer;" />
                  <input type="text" v-model="settings.announcement.config.text_color" class="form-input" style="flex: 1;" placeholder="#FFFDF9" />
                </div>
                <!-- Color Presets -->
                <div style="display: flex; gap: var(--spacing-xs); margin-top: 6px;">
                  <button 
                    v-for="color in ['#FFFDF9', '#D4AF37', '#1C1C1C', '#493b54', '#FFFFFF']" 
                    :key="color"
                    type="button"
                    @click="settings.announcement.config.text_color = color"
                    style="width: 20px; height: 20px; border-radius: 50%; border: 1px solid #cbd5e1; cursor: pointer;"
                    :style="{ backgroundColor: color }"
                    :title="color"
                  ></button>
                </div>
              </div>
            </div>
          </div>

          <!-- Announcements CRUD Master Manager -->
          <div style="border-top: 1px solid var(--color-border); padding-top: var(--spacing-md); display: flex; flex-direction: column; gap: var(--spacing-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span class="card-header-title" style="font-size: 1.1rem; margin: 0;">Announcement Messages</span>
              <button 
                type="button" 
                @click="addAnnouncementItem" 
                class="btn btn--secondary" 
                style="padding: 0.5rem 1rem; font-size: 0.85rem;"
              >
                ➕ Add Item
              </button>
            </div>

            <!-- List of Announcement Items -->
            <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
              <div 
                v-for="(item, idx) in settings.announcement.items" 
                :key="item.id || idx" 
                style="border: 1px solid var(--color-border); border-radius: 8px; padding: var(--spacing-sm); background: rgba(255,255,255,0.01); display: flex; flex-direction: column; gap: var(--spacing-xs);"
              >
                <div style="display: flex; justify-content: space-between; align-items: center; gap: var(--spacing-md);">
                  <span style="font-weight: bold; color: #493b54; font-size: 0.9rem;">
                    Announcement #{{ idx + 1 }}
                  </span>
                  
                  <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <!-- Active Checkbox -->
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 0.8rem; cursor: pointer; user-select: none;">
                      <input type="checkbox" v-model="item.is_active" />
                      Active
                    </label>

                    <!-- Move Up/Down Buttons -->
                    <button 
                      type="button" 
                      @click="moveItemUp(idx)" 
                      :disabled="idx === 0" 
                      style="padding: 2px 6px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; font-size: 0.75rem; cursor: pointer;"
                    >
                      ▲
                    </button>
                    <button 
                      type="button" 
                      @click="moveItemDown(idx)" 
                      :disabled="idx === settings.announcement.items.length - 1" 
                      style="padding: 2px 6px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; font-size: 0.75rem; cursor: pointer;"
                    >
                      ▼
                    </button>

                    <!-- Delete Button -->
                    <button 
                      type="button" 
                      @click="deleteAnnouncementItem(idx)" 
                      style="padding: 2px 6px; border: 1px solid #fca5a5; background: #fee2e2; color: #b91c1c; border-radius: 4px; font-size: 0.75rem; cursor: pointer;"
                    >
                      🗑️ Delete
                    </button>
                  </div>
                </div>

                <div class="responsive-grid-1-1" style="gap: var(--spacing-sm); grid-template-columns: 2fr 1fr;">
                  <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Display Text *</label>
                    <input 
                      type="text" 
                      v-model="item.text" 
                      class="form-input" 
                      style="padding: 0.4rem 0.6rem; font-size: 0.85rem;" 
                      placeholder="e.g. 🚚 Free Shipping above ₹999!" 
                      required 
                    />
                  </div>
                  <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Redirect URL (Optional)</label>
                    <input 
                      type="text" 
                      v-model="item.link" 
                      class="form-input" 
                      style="padding: 0.4rem 0.6rem; font-size: 0.85rem;" 
                      placeholder="e.g. /shop or /products/123" 
                    />
                  </div>
                </div>
              </div>

              <div 
                v-if="settings.announcement.items.length === 0" 
                style="text-align: center; color: var(--color-text-muted); padding: var(--spacing-md); border: 1px dashed var(--color-border); border-radius: 8px;"
              >
                No announcement messages configured. Click "Add Item" to create one.
              </div>
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
import StorefrontAnnouncementBar from '../../components/StorefrontAnnouncementBar.vue';

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
  },
  announcement: {
    items: [],
    config: {
      is_enabled: true,
      mode: 'marquee',
      speed: 10,
      background_color: '#493b54',
      text_color: '#FFFDF9',
      is_sticky: true
    }
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
      
      if (data.announcement) {
        settings.value.announcement = {
          items: data.announcement.items || [],
          config: {
            is_enabled: data.announcement.config?.is_enabled !== false,
            mode: data.announcement.config?.mode || 'marquee',
            speed: data.announcement.config?.speed !== undefined ? parseInt(data.announcement.config.speed) : 10,
            background_color: data.announcement.config?.background_color || '#493b54',
            text_color: data.announcement.config?.text_color || '#FFFDF9',
            is_sticky: data.announcement.config?.is_sticky !== false
          }
        };
      } else {
        settings.value.announcement = {
          items: [
            { id: 1, text: '🚚 Free Shipping Above ₹999 across South India', link: '/shop', is_active: true },
            { id: 2, text: '🔄 Easy 7-Day Exchange & Hassle-free Returns', link: '/refund-policy', is_active: true },
            { id: 3, text: '✨ Special Festive Discount: Use Code MAYASREE10 for 10% Off!', link: '/shop', is_active: true }
          ],
          config: {
            is_enabled: true,
            mode: 'marquee',
            speed: 10,
            background_color: '#493b54',
            text_color: '#FFFDF9',
            is_sticky: true
          }
        };
      }
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

const addAnnouncementItem = () => {
  const nextId = settings.value.announcement.items.length > 0 
    ? Math.max(...settings.value.announcement.items.map(i => i.id || 0)) + 1 
    : 1;
  settings.value.announcement.items.push({
    id: nextId,
    text: '✨ Special Announcement Message',
    link: '',
    is_active: true
  });
};

const deleteAnnouncementItem = (index) => {
  settings.value.announcement.items.splice(index, 1);
};

const moveItemUp = (index) => {
  if (index <= 0) return;
  const items = settings.value.announcement.items;
  const temp = items[index];
  items[index] = items[index - 1];
  items[index - 1] = temp;
};

const moveItemDown = (index) => {
  const items = settings.value.announcement.items;
  if (index >= items.length - 1) return;
  const temp = items[index];
  items[index] = items[index + 1];
  items[index + 1] = temp;
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
        email: { ...settings.value.email },
        announcement: {
          items: settings.value.announcement.items,
          config: { ...settings.value.announcement.config }
        }
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
