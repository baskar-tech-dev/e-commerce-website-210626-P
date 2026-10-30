<template>
  <div v-if="loading && !customer" class="loading-state">
    <div class="stat-card__value">Loading Customer Profile...</div>
  </div>

  <div v-else-if="error" class="badge badge--danger error-banner">
    <AlertCircle :size="18" />
    <span>{{ error }}</span>
    <button @click="$router.push({ name: 'admin.customers' })" class="btn btn--secondary btn--sm" style="margin-left: 1rem;">
      Back to Directory
    </button>
  </div>

  <div v-else-if="customer" class="customer-profile-page">
    <!-- Header -->
    <div class="admin-page__header">
      <div class="admin-page__title-section">
        <router-link :to="{ name: 'admin.customers' }" class="back-link">
          <ArrowLeft :size="16" />
          <span>Back to Customer Directory</span>
        </router-link>
        <div class="header-name-row">
          <h1 class="admin-page__title">{{ customer.name }}</h1>
          <span :class="['badge', customer.is_active ? 'badge--success' : 'badge--danger']">
            {{ customer.is_active ? 'Active Account' : 'Inactive Account' }}
          </span>
        </div>
        <span class="admin-page__subtitle">Customer Account, Order Records & Financial History</span>
      </div>
      
      <div class="header-actions">
        <button class="btn btn--secondary" @click="toggleEditProfile">
          <Edit3 :size="16" />
          <span>{{ isEditingProfile ? 'Cancel Edit' : 'Edit Profile' }}</span>
        </button>
        <button 
          class="btn" 
          :class="customer.is_active ? 'btn--danger' : 'btn--primary'" 
          @click="toggleCustomerStatus" 
          :disabled="submitting"
        >
          <ShieldAlert v-if="customer.is_active" :size="16" />
          <ShieldCheck v-else :size="16" />
          <span>{{ customer.is_active ? 'Deactivate Account' : 'Activate Account' }}</span>
        </button>
      </div>
    </div>

    <!-- Error Alert Message -->
    <div v-if="errorMsg" class="badge badge--danger error-banner">
      <AlertCircle :size="16" />
      <span>{{ errorMsg }}</span>
    </div>

    <!-- 4 Key Purchase & Financial Summary Cards -->
    <div class="stats-overview-grid">
      <!-- Total Purchases -->
      <div class="stat-summary-card">
        <div class="stat-summary-card__top">
          <span class="stat-summary-card__label">Total Purchases</span>
          <div class="stat-summary-card__icon stat-summary-card__icon--primary">
            <ShoppingBag :size="20" />
          </div>
        </div>
        <div class="stat-summary-card__value">
          {{ customer.stats?.total_purchases ?? customer.profile?.total_orders ?? 0 }}
        </div>
        <div class="stat-summary-card__meta">
          <span>All orders placed by customer</span>
        </div>
      </div>

      <!-- Paid Purchases -->
      <div class="stat-summary-card stat-summary-card--highlight">
        <div class="stat-summary-card__top">
          <span class="stat-summary-card__label">Paid Purchases</span>
          <div class="stat-summary-card__icon stat-summary-card__icon--success">
            <CheckCircle2 :size="20" />
          </div>
        </div>
        <div class="stat-summary-card__value stat-summary-card__value--success">
          {{ customer.stats?.paid_count ?? customer.profile?.paid_count ?? 0 }}
        </div>
        <div class="stat-summary-card__meta">
          <span class="badge-paid-pill">Successfully Paid</span>
        </div>
      </div>

      <!-- Paid Revenue (Realized Spent) -->
      <div class="stat-summary-card">
        <div class="stat-summary-card__top">
          <span class="stat-summary-card__label">Paid Revenue</span>
          <div class="stat-summary-card__icon stat-summary-card__icon--gold">
            <CreditCard :size="20" />
          </div>
        </div>
        <div class="stat-summary-card__value stat-summary-card__value--amount">
          ₹{{ formatCurrency(customer.stats?.paid_spent ?? customer.profile?.paid_spent ?? 0) }}
        </div>
        <div class="stat-summary-card__meta">
          <span>Net revenue from completed payments</span>
        </div>
      </div>

      <!-- Pending / Unpaid Orders -->
      <div class="stat-summary-card">
        <div class="stat-summary-card__top">
          <span class="stat-summary-card__label">Pending / Unpaid</span>
          <div class="stat-summary-card__icon stat-summary-card__icon--warning">
            <Clock :size="20" />
          </div>
        </div>
        <div class="stat-summary-card__value stat-summary-card__value--warning">
          {{ (customer.stats?.pending_count || 0) + (customer.stats?.failed_count || 0) }}
        </div>
        <div class="stat-summary-card__meta">
          <span>{{ customer.stats?.pending_count || 0 }} pending, {{ customer.stats?.failed_count || 0 }} failed</span>
        </div>
      </div>
    </div>

    <!-- 2 Column Profile & Notes Section -->
    <div class="grid-2 responsive-grid-1-1 profile-meta-grid">
      <!-- Column 1: Customer Profile Details -->
      <div class="glass-panel profile-panel">
        <div class="panel-header">
          <h2 class="panel-title">Account Details</h2>
          <span class="panel-tag">Profile Information</span>
        </div>

        <!-- View Mode -->
        <div v-if="!isEditingProfile" class="profile-view">
          <div class="profile-avatar-row">
            <div class="profile-avatar">
              <img v-if="customer.avatar" :src="customer.avatar" :alt="customer.name" />
              <span v-else class="profile-avatar-initial">{{ getInitials(customer.name) }}</span>
            </div>
            <div>
              <div class="profile-name">{{ customer.name }}</div>
              <div class="profile-subtext">Member since {{ formatDate(customer.created_at) }}</div>
            </div>
          </div>

          <div class="grid-2 responsive-grid-1-1 info-grid">
            <div class="info-item">
              <span class="info-label"><Mail :size="13" /> Email Address</span>
              <span class="info-val">{{ customer.email }}</span>
            </div>
            <div class="info-item">
              <span class="info-label"><Phone :size="13" /> Phone Number</span>
              <span class="info-val">{{ customer.phone || 'Not provided' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label"><Calendar :size="13" /> Date of Birth</span>
              <span class="info-val">{{ formatDate(customer.profile?.date_of_birth) || 'Not provided' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label"><User :size="13" /> Gender</span>
              <span class="info-val">{{ customer.profile?.gender || 'Not specified' }}</span>
            </div>
          </div>

          <div class="subscription-section">
            <div class="sub-badge-group">
              <span class="sub-label">Email Marketing:</span>
              <span :class="['badge', customer.profile?.email_subscribed ? 'badge--success' : 'badge--secondary']">
                {{ customer.profile?.email_subscribed ? 'Subscribed' : 'Not Subscribed' }}
              </span>
            </div>
            <div class="sub-badge-group">
              <span class="sub-label">SMS Marketing:</span>
              <span :class="['badge', customer.profile?.sms_subscribed ? 'badge--success' : 'badge--secondary']">
                {{ customer.profile?.sms_subscribed ? 'Subscribed' : 'Not Subscribed' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Edit Profile Form -->
        <form v-else @submit.prevent="saveProfile" class="profile-edit-form">
          <div class="grid-2 responsive-grid-1-1" style="gap: 1rem;">
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

          <div class="grid-2 responsive-grid-1-1" style="gap: 1rem;">
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

          <div class="form-group checkbox-row">
            <label class="checkbox-label">
              <input type="checkbox" id="email_sub" v-model="profileForm.email_subscribed" />
              <span>Email Marketing</span>
            </label>
            <label class="checkbox-label">
              <input type="checkbox" id="sms_sub" v-model="profileForm.sms_subscribed" />
              <span>SMS Marketing</span>
            </label>
          </div>

          <div class="form-actions-row">
            <button type="button" class="btn btn--secondary btn--sm" @click="toggleEditProfile">
              Cancel
            </button>
            <button type="submit" class="btn btn--primary btn--sm" :disabled="submitting">
              {{ submitting ? 'Saving...' : 'Save Profile' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Column 2: Account Notes & Summary -->
      <div class="glass-panel profile-panel">
        <div class="panel-header">
          <h2 class="panel-title">Administrative Notes</h2>
          <span class="panel-tag">Internal Info</span>
        </div>

        <div class="account-meta-box">
          <div class="account-meta-item">
            <span class="meta-label">Customer ID:</span>
            <span class="meta-val">#{{ customer.id }}</span>
          </div>
          <div class="account-meta-item">
            <span class="meta-label">Total Order Value:</span>
            <span class="meta-val" style="color: #6E1F3A; font-weight: 700;">
              ₹{{ formatCurrency(customer.stats?.total_spent ?? customer.profile?.total_spent ?? 0) }}
            </span>
          </div>
        </div>

        <!-- Notes Editor -->
        <div class="notes-container">
          <label class="form-label" style="font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">
            Private Staff Notes
          </label>
          <textarea 
            v-model="notes" 
            class="form-textarea notes-textarea" 
            placeholder="Enter private internal notes about this customer account, special handling requests, or preferences..."
          ></textarea>
          <div style="display: flex; justify-content: flex-end; margin-top: 0.75rem;">
            <button class="btn btn--secondary btn--sm btn-save-notes" @click="saveNotes" :disabled="submitting">
              <Save :size="14" />
              <span>{{ submitting ? 'Saving...' : 'Save Notes' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Address Book -->
    <div class="glass-panel section-panel">
      <div class="panel-header">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <MapPin :size="20" style="color: #6E1F3A;" />
          <h2 class="panel-title">Address Book ({{ customer.addresses?.length || 0 }}/5)</h2>
        </div>
        <button class="btn btn--primary btn--sm" @click="openAddAddressModal" :disabled="customer.addresses?.length >= 5">
          <Plus :size="14" />
          <span>Add New Address</span>
        </button>
      </div>

      <!-- Address Grid -->
      <div v-if="customer.addresses && customer.addresses.length > 0" class="address-grid">
        <div 
          v-for="address in customer.addresses" 
          :key="address.id" 
          class="address-card"
        >
          <!-- Badges -->
          <div class="address-badges">
            <span v-if="address.label" class="badge badge-label">
              {{ address.label }}
            </span>
            <span v-if="address.is_default_shipping" class="badge badge--success">
              Default Shipping
            </span>
            <span v-if="address.is_default_billing" class="badge badge--secondary">
              Default Billing
            </span>
          </div>

          <div class="address-recipient">
            {{ address.first_name }} {{ address.last_name }}
          </div>
          <div class="address-body">
            <div>{{ address.address_line_1 }}</div>
            <div v-if="address.address_line_2">{{ address.address_line_2 }}</div>
            <div>{{ address.city }}, {{ address.state }} - {{ address.postal_code }}</div>
            <div>{{ address.country }} {{ address.landmark ? `(Landmark: ${address.landmark})` : '' }}</div>
          </div>
          <div class="address-phone">
            <Phone :size="12" /> {{ address.phone }}
          </div>

          <!-- Address Actions -->
          <div class="address-actions">
            <button class="btn btn--secondary btn--sm" style="flex: 1; padding: 0.35rem;" @click="openEditAddressModal(address)">
              <Edit3 :size="13" /> Edit
            </button>
            <button class="btn btn--danger btn--sm" style="padding: 0.35rem 0.6rem;" @click="deleteAddress(address.id)" title="Delete Address">
              <Trash2 :size="14" />
            </button>
          </div>
        </div>
      </div>
      <div v-else class="empty-notice">
        No addresses registered for this customer.
      </div>
    </div>

    <!-- Order History Section -->
    <div class="glass-panel section-panel">
      <div class="panel-header">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <ShoppingBag :size="20" style="color: #6E1F3A;" />
          <h2 class="panel-title">Order History</h2>
          <span class="badge badge--secondary" style="font-size: 0.78rem; font-weight: 700;">
            {{ customer.orders?.length || 0 }} Orders
          </span>
        </div>
      </div>

      <!-- Real Orders Table -->
      <div v-if="customer.orders && customer.orders.length > 0" class="order-table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order Number</th>
              <th>Date & Time</th>
              <th>Items</th>
              <th>Grand Total</th>
              <th>Payment Status</th>
              <th>Fulfillment Status</th>
              <th style="text-align: right; width: 70px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in customer.orders" :key="order.id">
              <td>
                <router-link 
                  :to="{ name: 'admin.orders.show', params: { id: order.id } }" 
                  class="order-link"
                >
                  #{{ order.order_number }}
                </router-link>
              </td>
              <td class="order-date-col">
                {{ order.date }}
              </td>
              <td>
                <span class="items-count">{{ order.total_items }} item(s)</span>
              </td>
              <td class="order-total-col">
                ₹{{ formatCurrency(order.grand_total) }}
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span :class="['badge', getPaymentBadgeClass(order.payment_status)]">
                    {{ order.payment_status }}
                  </span>
                  <span class="payment-method-tag">
                    {{ order.payment_method }}
                  </span>
                </div>
              </td>
              <td>
                <span :class="['badge', getStatusBadgeClass(order.status)]">
                  {{ order.status_label || getStatusLabel(order.status) }}
                </span>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <router-link 
                  :to="{ name: 'admin.orders.show', params: { id: order.id } }" 
                  class="btn-action-icon btn-action-icon--view"
                  title="View Order Details"
                  aria-label="View Order Details"
                >
                  <Eye :size="16" />
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div v-else class="empty-notice">
        No orders placed by this customer yet.
      </div>
    </div>

    <!-- Address Modal Form -->
    <div v-if="showAddressModal" class="modal-overlay" @click.self="closeAddressModal">
      <div class="modal-container" style="max-width: 550px;">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditingAddress ? 'Edit Address' : 'Add New Address' }}</h3>
          <button class="modal-close" @click="closeAddressModal">&times;</button>
        </div>
        <form @submit.prevent="saveAddress">
          <div class="modal-body">
            <div class="grid-2 responsive-grid-1-1" style="gap: 1rem;">
              <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" v-model="addressForm.first_name" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" v-model="addressForm.last_name" required class="form-input" />
              </div>
            </div>

            <div class="grid-2 responsive-grid-1-1" style="gap: 1rem;">
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

            <div class="grid-2 responsive-grid-1-1" style="gap: 1rem;">
              <div class="form-group">
                <label class="form-label">City</label>
                <input type="text" v-model="addressForm.city" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">State</label>
                <input type="text" v-model="addressForm.state" required class="form-input" />
              </div>
            </div>

            <div class="grid-2 responsive-grid-1-1" style="gap: 1rem;">
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

            <div class="form-group checkbox-row" style="margin-top: 1rem;">
              <label class="checkbox-label">
                <input type="checkbox" id="def_shipping" v-model="addressForm.is_default_shipping" />
                <span>Default Shipping</span>
              </label>
              <label class="checkbox-label">
                <input type="checkbox" id="def_billing" v-model="addressForm.is_default_billing" />
                <span>Default Billing</span>
              </label>
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
import {
  ArrowLeft,
  ShoppingBag,
  CheckCircle2,
  CreditCard,
  Clock,
  User,
  Mail,
  Phone,
  Calendar,
  MapPin,
  AlertCircle,
  Trash2,
  Edit3,
  Plus,
  Eye,
  ShieldCheck,
  ShieldAlert,
  Save,
} from 'lucide-vue-next';

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

function getInitials(name) {
  if (!name) return 'C';
  const parts = name.trim().split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return parts[0][0].toUpperCase();
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

function formatCurrency(val) {
  const num = parseFloat(val) || 0;
  return num.toLocaleString('en-IN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function getPaymentBadgeClass(status) {
  switch (status) {
    case 'paid':
    case 'captured':
      return 'badge--success';
    case 'pending':
      return 'badge--warning';
    case 'failed':
      return 'badge--danger';
    case 'refunded':
    case 'partial_refunded':
      return 'badge--secondary';
    default:
      return 'badge--secondary';
  }
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'delivered':
      return 'badge--success';
    case 'shipped':
    case 'out_for_delivery':
      return 'badge--info';
    case 'processing':
    case 'packed':
      return 'badge--warning';
    case 'cancelled':
    case 'failed':
      return 'badge--danger';
    default:
      return 'badge--secondary';
  }
}

function getStatusLabel(status) {
  if (!status) return '—';
  return status
    .split('_')
    .map(w => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ');
}
</script>

<style scoped>
.customer-profile-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.loading-state {
  text-align: center;
  padding: 5rem;
}

.error-banner {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.85rem 1.25rem;
  border-radius: 10px;
  width: 100%;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  color: #64748B;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  transition: color 0.2s ease;
}

.back-link:hover {
  color: #6E1F3A;
}

.header-name-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

/* 4 Stats Cards Grid */
.stats-overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}

.stat-summary-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-summary-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.stat-summary-card--highlight {
  border-color: rgba(16, 185, 129, 0.35);
  background: linear-gradient(180deg, #ffffff 0%, rgba(16, 185, 129, 0.03) 100%);
}

.stat-summary-card__top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.stat-summary-card__label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.stat-summary-card__icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-summary-card__icon--primary {
  background: rgba(110, 31, 58, 0.08);
  color: #6E1F3A;
}

.stat-summary-card__icon--success {
  background: rgba(16, 185, 129, 0.12);
  color: #047857;
}

.stat-summary-card__icon--gold {
  background: rgba(212, 175, 55, 0.15);
  color: #92400e;
}

.stat-summary-card__icon--warning {
  background: rgba(245, 158, 11, 0.12);
  color: #d97706;
}

.stat-summary-card__value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
  margin-bottom: 0.4rem;
}

.stat-summary-card__value--success {
  color: #047857;
}

.stat-summary-card__value--amount {
  color: #6E1F3A;
}

.stat-summary-card__value--warning {
  color: #d97706;
}

.stat-summary-card__meta {
  font-size: 0.78rem;
  color: #64748B;
}

.badge-paid-pill {
  display: inline-block;
  background: rgba(16, 185, 129, 0.12);
  color: #047857;
  padding: 2px 8px;
  border-radius: 10px;
  font-size: 0.72rem;
  font-weight: 600;
}

/* Profile Panels & Layout */
.profile-meta-grid {
  gap: 1.5rem;
  align-items: stretch;
}

.profile-panel {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.section-panel {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f1f5f9;
}

.panel-title {
  color: #0f172a;
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
}

.panel-tag {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
}

.profile-avatar-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.profile-avatar {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: rgba(110, 31, 58, 0.08);
  border: 2px solid rgba(110, 31, 58, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-avatar-initial {
  font-size: 1.25rem;
  font-weight: 700;
  color: #6E1F3A;
}

.profile-name {
  font-size: 1.15rem;
  font-weight: 700;
  color: #0f172a;
}

.profile-subtext {
  font-size: 0.82rem;
  color: #64748B;
}

.info-grid {
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.info-label {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.78rem;
  color: #64748B;
  font-weight: 500;
}

.info-val {
  font-size: 0.92rem;
  font-weight: 600;
  color: #1e293b;
}

.subscription-section {
  display: flex;
  gap: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
}

.sub-badge-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.sub-label {
  font-size: 0.78rem;
  color: #64748B;
}

/* Account Meta Box */
.account-meta-box {
  display: flex;
  justify-content: space-between;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.85rem 1.25rem;
  margin-bottom: 1.25rem;
}

.account-meta-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.meta-label {
  font-size: 0.76rem;
  color: #64748B;
}

.meta-val {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1e293b;
}

.notes-container {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.notes-textarea {
  flex: 1;
  min-height: 120px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  padding: 0.75rem;
  font-family: inherit;
  font-size: 0.88rem;
  resize: vertical;
}

.btn-save-notes {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* Address Book */
.address-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.address-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.address-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin-bottom: 0.35rem;
}

.badge-label {
  background: #6E1F3A;
  color: #ffffff;
  font-size: 0.72rem;
  font-weight: 600;
}

.address-recipient {
  font-weight: 700;
  color: #0f172a;
  font-size: 0.95rem;
}

.address-body {
  font-size: 0.85rem;
  color: #475569;
  line-height: 1.4;
}

.address-phone {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.82rem;
  color: #64748B;
  font-weight: 500;
  margin-top: 0.25rem;
}

.address-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid #e2e8f0;
}

/* Order History Table */
.order-table-container {
  overflow-x: auto;
}

.order-link {
  font-weight: 700;
  color: #6E1F3A;
  text-decoration: none;
  transition: color 0.15s ease;
}

.order-link:hover {
  text-decoration: underline;
  color: #8c274a;
}

.order-date-col {
  font-size: 0.84rem;
  color: #64748B;
}

.items-count {
  font-size: 0.85rem;
  color: #475569;
}

.order-total-col {
  font-weight: 700;
  color: #0f172a;
}

.payment-method-tag {
  font-size: 0.68rem;
  font-weight: 600;
  color: #64748B;
  text-transform: uppercase;
  background: #f1f5f9;
  padding: 1px 6px;
  border-radius: 4px;
}

.empty-notice {
  text-align: center;
  padding: 2.5rem;
  color: #94a3b8;
  font-style: italic;
  font-size: 0.9rem;
}

/* Action Icons */
.btn-action-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  padding: 0;
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  box-sizing: border-box;
  user-select: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-action-icon:active {
  transform: scale(0.92);
}

.btn-action-icon svg {
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.btn-action-icon:hover svg {
  transform: scale(1.15);
}

.btn-action-icon--view {
  background: rgba(110, 31, 58, 0.06);
  color: #6E1F3A;
  border: 1.5px solid rgba(110, 31, 58, 0.28);
  box-shadow: 0 1px 3px rgba(110, 31, 58, 0.05);
}

.btn-action-icon--view:hover {
  background: #6E1F3A;
  color: #ffffff;
  border-color: #6E1F3A;
  box-shadow: 0 4px 12px rgba(110, 31, 58, 0.28);
  transform: translateY(-1px);
}

.checkbox-row {
  display: flex;
  gap: 1.5rem;
  align-items: center;
  padding-top: 0.5rem;
}

.checkbox-label {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.88rem;
  color: #475569;
  cursor: pointer;
}

.form-actions-row {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
}
</style>