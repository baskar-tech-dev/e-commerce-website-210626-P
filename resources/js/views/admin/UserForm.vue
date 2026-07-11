<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <router-link to="/admin/users" style="text-decoration: none; color: var(--color-primary); font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
        ◀ Back to Staff Directory
      </router-link>
      <h1 class="admin-page__title">{{ isEdit ? 'Edit Staff Member' : 'Register New Staff Member' }}</h1>
      <span class="admin-page__subtitle">{{ isEdit ? 'Modify admin login details and update security roles.' : 'Create back-office credentials and assign roles.' }}</span>
    </div>
  </div>

  <!-- Loading State -->
  <div v-if="loadingDetails" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Loading user profile parameters...</div>
  </div>

  <div v-else>
    <form @submit.prevent="saveUser" class="responsive-grid-2-1" style="gap: var(--spacing-lg);">
      <!-- Left Column: User details, email, pass -->
      <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="responsive-grid-1-1" style="gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
            <!-- First Name -->
            <div class="form-group">
              <label class="form-label">First Name *</label>
              <input type="text" v-model="form.first_name" placeholder="John" class="form-input" required />
            </div>

            <!-- Last Name -->
            <div class="form-group">
              <label class="form-label">Last Name *</label>
              <input type="text" v-model="form.last_name" placeholder="Doe" class="form-input" required />
            </div>
          </div>

          <!-- Email -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Email Address *</label>
            <input type="email" v-model="form.email" placeholder="staff@vibe.com" class="form-input" required />
          </div>

          <!-- Phone -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Phone Number</label>
            <input type="text" v-model="form.phone" placeholder="9876543210" class="form-input" />
          </div>

          <!-- Password -->
          <div class="form-group">
            <label class="form-label">
              {{ isEdit ? 'New Password (leave blank to keep current)' : 'Password *' }}
            </label>
            <input 
              type="password" 
              v-model="form.password" 
              placeholder="••••••••" 
              class="form-input" 
              :required="!isEdit" 
            />
          </div>
        </div>
      </div>

      <!-- Right Column: Roles settings and Save buttons -->
      <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        <!-- Active Status and Roles Panel -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Account & Security Access</div>

          <!-- Active Toggle -->
          <div class="form-group" style="margin-bottom: var(--spacing-lg); display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <input type="checkbox" id="is_active" v-model="form.is_active" style="cursor: pointer;" />
            <label for="is_active" style="color: #1e293b; font-size: 0.85rem; font-weight: 500; cursor: pointer;">
              🟢 Active Login Permission
            </label>
          </div>

          <!-- Roles Selection -->
          <div class="form-group">
            <label class="form-label" style="margin-bottom: 0.5rem;">Assign Roles *</label>
            <div style="display: flex; flex-direction: column; gap: var(--spacing-xs);">
              <label 
                v-for="role in roles" 
                :key="role.id" 
                style="display: flex; align-items: center; gap: var(--spacing-xs); color: #1e293b; font-size: 0.85rem; cursor: pointer;"
              >
                <input 
                  type="checkbox" 
                  :value="role.id" 
                  v-model="form.roles" 
                  style="cursor: pointer;" 
                />
                <span style="text-transform: capitalize;">{{ role.name.replace('_', ' ') }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Actions Panel -->
        <div class="glass-panel" style="padding: var(--spacing-lg); text-align: center;">
          <button 
            type="submit" 
            class="btn btn--primary" 
            style="width: 100%; margin-bottom: var(--spacing-sm);"
            :disabled="saving || form.roles.length === 0"
          >
            {{ saving ? 'Saving Profile...' : '💾 Save Profile' }}
          </button>
          
          <router-link to="/admin/users" class="btn btn--secondary" style="width: 100%; display: block; text-decoration: none;">
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
const loadingDetails = ref(false);
const saving = ref(false);

const roles = ref([]);

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  phone: '',
  is_active: true,
  roles: [],
});

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/admin/roles');
    if (response.data && response.data.success) {
      roles.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load roles list:', err);
  }
};

const fetchUserDetails = async () => {
  if (!route.params.id) return;
  
  isEdit.value = true;
  loadingDetails.value = true;
  try {
    const response = await axios.get(`/api/admin/users/${route.params.id}`);
    if (response.data && response.data.success) {
      const data = response.data.data;
      form.value = {
        first_name: data.first_name || '',
        last_name: data.last_name || '',
        email: data.email || '',
        password: '', // do not prefill password
        phone: data.phone || '',
        is_active: !!data.is_active,
        roles: data.roles ? data.roles.map(r => r.id) : [],
      };
    }
  } catch (err) {
    console.error('Failed to load user profile details:', err);
    alert('Failed to load user profile details');
    router.push('/admin/users');
  } finally {
    loadingDetails.value = false;
  }
};

const saveUser = async () => {
  if (form.value.roles.length === 0) {
    alert('Please assign at least one role to the staff user');
    return;
  }

  saving.value = true;
  try {
    const payload = { ...form.value };
    // if editing and password is empty, remove it from payload
    if (isEdit.value && !payload.password) {
      delete payload.password;
    }

    let response;
    if (isEdit.value) {
      response = await axios.put(`/api/admin/users/${route.params.id}`, payload);
    } else {
      response = await axios.post('/api/admin/users', payload);
    }

    if (response.data && response.data.success) {
      router.push('/admin/users');
    }
  } catch (err) {
    console.error('Failed to save staff user:', err);
    alert(err.response?.data?.message || 'Validation error while saving staff profile');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchRoles();
  fetchUserDetails();
});
</script>
