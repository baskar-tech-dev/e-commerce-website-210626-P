<template>
  <div v-if="loading && !customer" style="text-align: center; padding: 5rem;">
    <div class="stat-card__value">Loading Customer Profile...</div>
  </div>

  <div v-else-if="error" class="badge badge--danger" style="margin: 2rem; padding: 1rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ error }}
    <button @click="$router.push({ name: 'admin.customers' })" class="btn btn--secondary btn--sm" style="margin-left: 1rem;">
      Back to List
    </button>
  </div>

  <div v-else-if="customer">
    <!-- Header -->
    <div class="admin-page__header">
      <div class="admin-page__title-section">
        <router-link :to="{ name: 'admin.customers' }" style="text-decoration: none; color: var(--color-text-muted); font-size: 0.9rem; display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
          ⬅️ Back to Customer Directory
        </router-link>
        <h1 class="admin-page__title">{{ customer.name }}</h1>
        <span class="admin-page__subtitle">Customer Account & Address Management</span>
      </div>
      
      <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn--secondary" @click="toggleEditProfile">
          {{ isEditingProfile ? 'Cancel Edit' : '✏️ Edit Profile' }}
        </button>
        <button class="btn" :class="customer.is_active ? 'btn--danger' : 'btn--primary'" @click="toggleCustomerStatus" :disabled="submitting">
          {{ customer.is_active ? '🚫 Deactivate Account' : '✅ Activate Account' }}
        </button>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1.5rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
      ⚠️ {{ errorMsg }}
    </div>

    <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; align-items: stretch;">
      <!-- Column 1: Customer Profile Details -->
      <div class="glass-panel" style="padding: 1.5rem; display: flex; flex-direction: column;">
        <h2 style="color: #fff; font-size: 1.25rem; margin-top: 0; margin-bottom: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem;">
          Account Details
        </h2>

        <!-- View Mode -->
        <div v-if="!isEditingProfile" style="display: flex; flex-direction: column; gap: 1rem; flex: 1;">
          <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: center; font-size: 2rem; border: 1px solid rgba(255, 255, 255, 0.15); overflow: hidden;">
              <img v-if="customer.avatar" :src="customer.avatar" style="width: 100%; height: 100%; object-fit: cover;" alt="" />
              <span v-else>👤</span>
            </div>
            <div>
              <div style="font-size: 1.15rem; font-weight: 600; color: #fff;">{{ customer.name }}</div>
              <div style="font-size: 0.85rem; color: var(--color-text-muted);">Joined: {{ formatDate(customer.created_at) }}</div>
            </div>
          </div>

          <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
            <div>
              <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block;">Email</span>
              <span style="color: #fff; font-size: 0.95rem;">{{ customer.email }}</span>
            </div>
            <div>
              <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block;">Phone</span>
              <span style="color: #fff; font-size: 0.95rem;">{{ customer.phone || 'Not provided' }}</span>
            </div>
            <div>
              <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block;">Date of Birth</span>
              <span style="color: #fff; font-size: 0.95rem;">{{ formatDate(customer.profile?.date_of_birth) || 'Not provided' }}</span>
            </div>
            <div>
              <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block;">Gender</span>
              <span style="color: #fff; font-size: 0.95rem;">{{ customer.profile?.gender || 'Not specified' }}</span>
            </div>
          </div>

          <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem; display: flex; gap: 1.5rem;">
            <div>
              <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block;">Email Subscription</span>
              <span :class="['badge', customer.profile?.email_subscribed ? 'badge--success' : 'badge--secondary']">
                {{ customer.profile?.email_subscribed ? 'Subscribed' : 'Not Subscribed' }}
              </span>
            </div>
            <div>
              <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block;">SMS Subscription</span>
              <span :class="['badge', customer.profile?.sms_subscribed ? 'badge--success' : 'badge--secondary']">
                {{ customer.profile?.sms_subscribed ? 'Subscribed' : 'Not Subscribed' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Edit Profile Form -->
        <form v-else @submit.prevent="saveProfile" style="display: flex; flex-direction: column; gap: 1rem; flex: 1;">
          <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label class="form-label">First Name</label>
              <input type="text" v-model="profileForm.first_name" required class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Last Name</label>
              <input type="text" v-model="profileForm.last_name" required class="form-input" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" v-model="profileForm.phone" class="form-input" placeholder="e.g. +91 9988776655" />
          </div>

          <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label class="form-label">Date of Birth</label>
              <input type="date" v-model="profileForm.date_of_birth" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Gender</label>
              <select v-model="profileForm.gender" class="form-select">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
                <option value="Prefer not to say">Prefer not to say</option>
              </select>
            </div>
          </div>

          <div class="form-group" style="flex-direction: row; gap: 1.5rem; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem;">
            <div class="form-group--row">
              <input type="checkbox" id="email_sub" v-model="profileForm.email_subscribed" />
              <label for="email_sub" class="form-label" style="margin-bottom: 0; cursor: pointer;">Email Marketing</label>
            </div>
            <div class="form-group--row">
              <input type="checkbox" id="sms_sub" v-model="profileForm.sms_subscribed" />
              <label for="sms_sub" class="form-label" style="margin-bottom: 0; cursor: pointer;">SMS Marketing</label>
            </div>
          </div>

          <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1rem;">
            <button type="button" class="btn btn--secondary btn--sm" @click="toggleEditProfile">
              Cancel
            </button>
            <button type="submit" class="btn btn--primary btn--sm" :disabled="submitting">
              {{ submitting ? 'Saving...' : 'Save Profile' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Column 2: Account Stats & Notes -->
      <div class="glass-panel" style="padding: 1.5rem; display: flex; flex-direction: column;">
        <h2 style="color: #fff; font-size: 1.25rem; margin-top: 0; margin-bottom: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem;">
          Account Notes & Summary
        </h2>

        <!-- Stats Boxes -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
          <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 1rem; text-align: center;">
            <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block; margin-bottom: 0.25rem;">Total Orders</span>
            <span style="font-size: 1.5rem; font-weight: 700; color: #fff;">{{ customer.profile?.total_orders || 0 }}</span>
          </div>
          <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 1rem; text-align: center;">
            <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block; margin-bottom: 0.25rem;">Total Spent</span>
            <span style="font-size: 1.5rem; font-weight: 700; color: #fff;">₹{{ parseFloat(customer.profile?.total_spent || 0).toFixed(2) }}</span>
          </div>
        </div>

        <!-- Notes Editor -->
        <div style="display: flex; flex-direction: column; flex: 1;">
          <label class="form-label" style="font-weight: 600; color: #fff; margin-bottom: 0.5rem;">Administrative Notes</label>
          <textarea 
            v-model="notes" 
            class="form-textarea" 
            style="flex: 1; min-height: 120px; margin-bottom: 1rem; resize: none;" 
            placeholder="Enter private internal notes about this customer account..."
          ></textarea>
          <button class="btn btn--secondary btn--sm" style="align-self: flex-end;" @click="saveNotes" :disabled="submitting">
            {{ submitting ? 'Saving...' : '💾 Save Notes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Address Book & Order History Tabs -->
    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem;">
        <h2 style="color: #fff; font-size: 1.25rem; margin: 0;">
          🏠 Address Book ({{ customer.addresses?.length || 0 }}/5)
        </h2>
        <button class="btn btn--primary btn--sm" @click="openAddAddressModal" :disabled="customer.addresses?.length >= 5">
          ➕ Add New Address
        </button>
      </div>

      <!-- Address Grid -->
      <div v-if="customer.addresses && customer.addresses.length > 0" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
        <div 
          v-for="address in customer.addresses" 
          :key="address.id" 
          style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 1.25rem; position: relative; display: flex; flex-direction: column; gap: 0.5rem;"
        >
          <!-- Badges -->
          <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; margin-bottom: 0.25rem;">
            <span v-if="address.label" class="badge" style="background: var(--color-primary); color: #fff; font-size: 0.75rem;">
              {{ address.label }}
            </span>
            <span v-if="address.is_default_shipping" class="badge badge--success" style="font-size: 0.75rem;">
              Default Shipping
            </span>
            <span v-if="address.is_default_billing" class="badge" style="background: rgba(255,255,255,0.15); color: #fff; font-size: 0.75rem;">
              Default Billing
            </span>
          </div>

          <div style="font-weight: 600; color: #fff; font-size: 0.95rem;">
            {{ address.first_name }} {{ address.last_name }}
          </div>
          <div style="font-size: 0.85rem; color: var(--color-text-muted);">
            {{ address.address_line_1 }}
            <div v-if="address.address_line_2">{{ address.address_line_2 }}</div>
            <div>{{ address.city }}, {{ address.state }} - {{ address.postal_code }}</div>
            <div>{{ address.country }} {{ address.landmark ? `(Landmark: ${address.landmark})` : '' }}</div>
          </div>
          <div style="font-size: 0.85rem; color: var(--color-text-muted); font-weight: 500;">
            📞 {{ address.phone }}
          </div>

          <!-- Address Actions -->
          <div style="display: flex; gap: 0.5rem; margin-top: auto; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 0.75rem;">
            <button class="btn btn--secondary btn--sm" style="flex: 1; padding: 0.25rem;" @click="openEditAddressModal(address)">
              ✏️ Edit
            </button>
            <button class="btn btn--danger btn--sm" style="padding: 0.25rem;" @click="deleteAddress(address.id)">
              🗑️
            </button>
          </div>
        </div>
      </div>
      <div v-else style="text-align: center; padding: 2.5rem; color: var(--color-text-muted); font-style: italic;">
        No addresses registered for this customer.
      </div>
    </div>

    <!-- Orders History Integration Notice -->
    <div class="glass-panel" style="padding: 1.5rem;">
      <h2 style="color: #fff; font-size: 1.25rem; margin-top: 0; margin-bottom: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 0.5rem;">
        🛍️ Order History
      </h2>
      <div style="text-align: center; padding: 2rem; background: rgba(255,255,255,0.02); border-radius: 8px; border: 1px dashed rgba(255,255,255,0.08); color: var(--color-text-muted);">
        📋 Order history tracking is coming soon. The database order engine will be fully integrated under <strong>Module 07</strong>.
      </div>
    </div>

    <!-- Address Modal Form -->
    <div v-if="showAddressModal" class="modal-overlay" @click.self="closeAddressModal">
      <div class="modal-container" style="max-width: 550px;">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditingAddress ? 'Edit Address' : 'Add Address' }}</h3>
          <button class="modal-close" @click="closeAddressModal">&times;</button>
        </div>
        <form @submit.prevent="saveAddress">
          <div class="modal-body">
            <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" v-model="addressForm.first_name" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" v-model="addressForm.last_name" required class="form-input" />
              </div>
            </div>

            <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label">Label (e.g. Home, Office)</label>
                <input type="text" v-model="addressForm.label" class="form-input" placeholder="Home" />
              </div>
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" v-model="addressForm.phone" required class="form-input" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Address Line 1</label>
              <input type="text" v-model="addressForm.address_line_1" required class="form-input" />
            </div>

            <div class="form-group">
              <label class="form-label">Address Line 2 (Optional)</label>
              <input type="text" v-model="addressForm.address_line_2" class="form-input" />
            </div>

            <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label">City</label>
                <input type="text" v-model="addressForm.city" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">State</label>
                <input type="text" v-model="addressForm.state" required class="form-input" />
              </div>
            </div>

            <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div class="form-group">
                <label class="form-label">PIN / Postal Code</label>
                <input type="text" v-model="addressForm.postal_code" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Country Code (2 char)</label>
                <input type="text" v-model="addressForm.country" required class="form-input" maxlength="2" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Landmark (Optional)</label>
              <input type="text" v-model="addressForm.landmark" class="form-input" />
            </div>

            <div class="form-group" style="flex-direction: row; gap: 1.5rem; align-items: center; margin-top: 1rem;">
              <div class="form-group--row">
                <input type="checkbox" id="def_shipping" v-model="addressForm.is_default_shipping" />
                <label for="def_shipping" class="form-label" style="margin-bottom: 0; cursor: pointer;">Default Shipping</label>
              </div>
              <div class="form-group--row">
                <input type="checkbox" id="def_billing" v-model="addressForm.is_default_billing" />
                <label for="def_billing" class="form-label" style="margin-bottom: 0; cursor: pointer;">Default Billing</label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn--secondary" @click="closeAddressModal" :disabled="submittingAddress">
              Cancel
            </button>
            <button type="submit" class="btn btn--primary" :disabled="submittingAddress">
              {{ submittingAddress ? 'Saving...' : 'Save Address' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useCustomerStore } from '../../stores/customer';

const route = useRoute();
const customerStore = useCustomerStore();

const customer = computed(() => customerStore.customer);
const loading = computed(() => customerStore.loading);
const error = computed(() => customerStore.error);

const isEditingProfile = ref(false);
const submitting = ref(false);
const errorMsg = ref(null);

const notes = ref('');

const profileForm = ref({
  first_name: '',
  last_name: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  email_subscribed: false,
  sms_subscribed: false,
});

// Address Modal State
const showAddressModal = ref(false);
const isEditingAddress = ref(false);
const selectedAddressId = ref(null);
const submittingAddress = ref(false);
const addressForm = ref({
  label: '',
  first_name: '',
  last_name: '',
  phone: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  state: '',
  postal_code: '',
  country: 'IN',
  landmark: '',
  is_default_shipping: false,
  is_default_billing: false,
});

onMounted(async () => {
  await loadCustomer();
});

async function loadCustomer() {
  try {
    const data = await customerStore.fetchCustomer(route.params.id);
    notes.value = data.profile?.notes || '';
  } catch (err) {
    // handled by store
  }
}

function toggleEditProfile() {
  if (isEditingProfile.value) {
    isEditingProfile.value = false;
  } else {
    profileForm.value = {
      first_name: customer.value.first_name || '',
      last_name: customer.value.last_name || '',
      phone: customer.value.phone || '',
      date_of_birth: customer.value.profile?.date_of_birth || '',
      gender: customer.value.profile?.gender || '',
      email_subscribed: customer.value.profile?.email_subscribed || false,
      sms_subscribed: customer.value.profile?.sms_subscribed || false,
    };
    isEditingProfile.value = true;
  }
}

async function saveProfile() {
  submitting.value = true;
  errorMsg.value = null;
  try {
    await customerStore.updateCustomer(customer.value.id, profileForm.value);
    isEditingProfile.value = false;
  } catch (err) {
    errorMsg.value = err.message || 'Failed to update customer profile';
  } finally {
    submitting.value = false;
  }
}

async function toggleCustomerStatus() {
  if (confirm(`Are you sure you want to ${customer.value.is_active ? 'deactivate' : 'activate'} this customer's account?`)) {
    submitting.value = true;
    errorMsg.value = null;
    try {
      await customerStore.updateCustomer(customer.value.id, {
        is_active: !customer.value.is_active,
        notes: notes.value
      });
    } catch (err) {
      errorMsg.value = err.message || 'Failed to update customer status';
    } finally {
      submitting.value = false;
    }
  }
}

async function saveNotes() {
  submitting.value = true;
  errorMsg.value = null;
  try {
    await customerStore.updateCustomer(customer.value.id, {
      notes: notes.value
    });
    alert('Notes saved successfully!');
  } catch (err) {
    errorMsg.value = err.message || 'Failed to save notes';
  } finally {
    submitting.value = false;
  }
}

// Address Book Operations
function openAddAddressModal() {
  isEditingAddress.value = false;
  selectedAddressId.value = null;
  addressForm.value = {
    label: '',
    first_name: customer.value.first_name || '',
    last_name: customer.value.last_name || '',
    phone: customer.value.phone || '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'IN',
    landmark: '',
    is_default_shipping: false,
    is_default_billing: false,
  };
  showAddressModal.value = true;
}

function openEditAddressModal(address) {
  isEditingAddress.value = true;
  selectedAddressId.value = address.id;
  addressForm.value = {
    label: address.label || '',
    first_name: address.first_name,
    last_name: address.last_name,
    phone: address.phone,
    address_line_1: address.address_line_1,
    address_line_2: address.address_line_2 || '',
    city: address.city,
    state: address.state,
    postal_code: address.postal_code,
    country: address.country,
    landmark: address.landmark || '',
    is_default_shipping: address.is_default_shipping,
    is_default_billing: address.is_default_billing,
  };
  showAddressModal.value = true;
}

function closeAddressModal() {
  showAddressModal.value = false;
}

async function saveAddress() {
  submittingAddress.value = true;
  errorMsg.value = null;
  try {
    if (isEditingAddress.value) {
      await customerStore.updateAddress(customer.value.id, selectedAddressId.value, addressForm.value);
    } else {
      await customerStore.addAddress(customer.value.id, addressForm.value);
    }
    showAddressModal.value = false;
  } catch (err) {
    errorMsg.value = err.message || 'Failed to save address';
  } finally {
    submittingAddress.value = false;
  }
}

async function deleteAddress(addressId) {
  if (confirm('Are you sure you want to remove this address?')) {
    errorMsg.value = null;
    try {
      await customerStore.deleteAddress(customer.value.id, addressId);
    } catch (err) {
      errorMsg.value = err.message || 'Failed to delete address';
    }
  }
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  });
}
</script>
