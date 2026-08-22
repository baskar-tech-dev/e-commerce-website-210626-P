<template>
  <Teleport to="body">
    <div v-if="authStore.authModalOpen" class="auth-modal-overlay" @click.self="closeModal">
      <div class="auth-modal-card">
        <!-- Close Button -->
        <button class="auth-modal-close" @click="closeModal" aria-label="Close modal">✕</button>

        <!-- Intended Action Context Banner -->
        <div v-if="intendedBannerMessage" class="auth-context-banner">
          <span class="auth-context-icon">{{ intendedBannerIcon }}</span>
          <div class="auth-context-text">
            <strong>{{ intendedBannerTitle }}</strong>
            <p>{{ intendedBannerMessage }}</p>
          </div>
        </div>

        <!-- Brand Header -->
        <div class="auth-brand-header">
          <img :src="'/asset/profile/logo.png'" alt="Maya Sree Fashion" class="auth-brand-logo" />
          <h2 class="auth-modal-title">
            {{ activeTab === 'login' ? 'Welcome Back' : (activeTab === 'register' ? 'Create Your Account' : 'Reset Password') }}
          </h2>
          <p class="auth-modal-subtitle">
            {{ activeTab === 'login' ? 'Sign in to access your Maya Sree account' : (activeTab === 'register' ? 'Join Maya Sree Fashion for seamless orders, wishlist & reviews' : 'Enter your email to reset your password') }}
          </p>
        </div>

        <!-- Tab Switcher (Login / Register) -->
        <div v-if="activeTab !== 'forgot'" class="auth-tab-group">
          <button 
            type="button" 
            class="auth-tab-btn" 
            :class="{ active: activeTab === 'login' }"
            @click="switchTab('login')"
          >
            Sign In
          </button>
          <button 
            type="button" 
            class="auth-tab-btn" 
            :class="{ active: activeTab === 'register' }"
            @click="switchTab('register')"
          >
            Create Account
          </button>
        </div>

        <!-- Global Error Alert -->
        <div v-if="authStore.error" class="auth-alert auth-alert--error">
          {{ authStore.error }}
        </div>

        <!-- Success Message Alert -->
        <div v-if="successMsg" class="auth-alert auth-alert--success">
          {{ successMsg }}
        </div>

        <!-- FORM 1: SIGN IN -->
        <form v-if="activeTab === 'login'" @submit.prevent="handleLogin" class="auth-form">
          <div class="form-group">
            <label class="form-label" for="modal-login-email">Email Address or Mobile Number *</label>
            <input 
              type="text" 
              id="modal-login-email" 
              v-model="loginForm.email" 
              class="form-input" 
              :class="{ 'is-invalid': authStore.validationErrors.email }" 
              placeholder="e.g. priya@example.com or 9944285102" 
              required 
            />
            <span v-if="authStore.validationErrors.email" class="error-text">
              {{ authStore.validationErrors.email[0] }}
            </span>
          </div>

          <div class="form-group">
            <div class="label-row">
              <label class="form-label" for="modal-login-password">Password *</label>
              <button type="button" class="forgot-link" @click="switchTab('forgot')">Forgot Password?</button>
            </div>
            <div class="password-input-wrapper">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                id="modal-login-password" 
                v-model="loginForm.password" 
                class="form-input" 
                :class="{ 'is-invalid': authStore.validationErrors.password }" 
                placeholder="••••••••" 
                required 
              />
              <button type="button" class="password-toggle-btn" @click="showPassword = !showPassword">
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>
            <span v-if="authStore.validationErrors.password" class="error-text">
              {{ authStore.validationErrors.password[0] }}
            </span>
          </div>

          <button type="submit" class="btn-auth-primary" :disabled="authStore.loading">
            {{ authStore.loading ? 'Signing In...' : 'Sign In' }}
          </button>

          <div class="auth-footer-prompt">
            Don't have an account? 
            <button type="button" class="auth-switch-link" @click="switchTab('register')">Create Account</button>
          </div>
        </form>

        <!-- FORM 2: REGISTER -->
        <form v-else-if="activeTab === 'register'" @submit.prevent="handleRegister" class="auth-form">
          <div class="form-group">
            <label class="form-label" for="modal-reg-name">Full Name *</label>
            <input 
              type="text" 
              id="modal-reg-name" 
              v-model="registerForm.name" 
              class="form-input" 
              :class="{ 'is-invalid': authStore.validationErrors.name }" 
              placeholder="e.g. Priya Sundaram" 
              required 
            />
            <span v-if="authStore.validationErrors.name" class="error-text">
              {{ authStore.validationErrors.name[0] }}
            </span>
          </div>

          <div class="form-group">
            <label class="form-label" for="modal-reg-email">Email Address *</label>
            <input 
              type="email" 
              id="modal-reg-email" 
              v-model="registerForm.email" 
              class="form-input" 
              :class="{ 'is-invalid': authStore.validationErrors.email }" 
              placeholder="priya@example.com" 
              required 
            />
            <span v-if="authStore.validationErrors.email" class="error-text">
              {{ authStore.validationErrors.email[0] }}
            </span>
          </div>

          <div class="form-group">
            <label class="form-label" for="modal-reg-phone">Mobile Number</label>
            <input 
              type="tel" 
              id="modal-reg-phone" 
              v-model="registerForm.phone" 
              class="form-input" 
              :class="{ 'is-invalid': authStore.validationErrors.phone }" 
              placeholder="+91 99442 85102" 
            />
            <span v-if="authStore.validationErrors.phone" class="error-text">
              {{ authStore.validationErrors.phone[0] }}
            </span>
          </div>

          <div class="form-group">
            <label class="form-label" for="modal-reg-password">Password *</label>
            <div class="password-input-wrapper">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                id="modal-reg-password" 
                v-model="registerForm.password" 
                class="form-input" 
                :class="{ 'is-invalid': authStore.validationErrors.password }" 
                placeholder="Minimum 8 characters" 
                required 
              />
              <button type="button" class="password-toggle-btn" @click="showPassword = !showPassword">
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>
            <span v-if="authStore.validationErrors.password" class="error-text">
              {{ authStore.validationErrors.password[0] }}
            </span>
          </div>

          <div class="form-group">
            <label class="form-label" for="modal-reg-confirm">Confirm Password *</label>
            <input 
              type="password" 
              id="modal-reg-confirm" 
              v-model="registerForm.password_confirmation" 
              class="form-input" 
              placeholder="Re-enter password" 
              required 
            />
          </div>

          <div class="terms-checkbox-group">
            <input type="checkbox" id="modal-reg-terms" v-model="registerForm.terms" required />
            <label for="modal-reg-terms" class="terms-label">
              I agree to the <router-link to="/terms-conditions" target="_blank" @click="closeModal">Terms & Conditions</router-link> and <router-link to="/privacy-policy" target="_blank" @click="closeModal">Privacy Policy</router-link>.
            </label>
          </div>
          <span v-if="authStore.validationErrors.terms" class="error-text">
            {{ authStore.validationErrors.terms[0] }}
          </span>

          <button type="submit" class="btn-auth-primary" :disabled="authStore.loading">
            {{ authStore.loading ? 'Creating Account...' : 'Create Account' }}
          </button>

          <div class="auth-footer-prompt">
            Already have an account? 
            <button type="button" class="auth-switch-link" @click="switchTab('login')">Sign In</button>
          </div>
        </form>

        <!-- FORM 3: FORGOT PASSWORD -->
        <form v-else-if="activeTab === 'forgot'" @submit.prevent="handleForgotPassword" class="auth-form">
          <div class="form-group">
            <label class="form-label" for="modal-forgot-email">Registered Email Address *</label>
            <input 
              type="email" 
              id="modal-forgot-email" 
              v-model="forgotForm.email" 
              class="form-input" 
              :class="{ 'is-invalid': authStore.validationErrors.email }" 
              placeholder="priya@example.com" 
              required 
            />
            <span v-if="authStore.validationErrors.email" class="error-text">
              {{ authStore.validationErrors.email[0] }}
            </span>
          </div>

          <button type="submit" class="btn-auth-primary" :disabled="authStore.loading">
            {{ authStore.loading ? 'Sending...' : 'Send Reset Link' }}
          </button>

          <div class="auth-footer-prompt">
            Remembered your password? 
            <button type="button" class="auth-switch-link" @click="switchTab('login')">Back to Sign In</button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const emit = defineEmits(['auth-success']);

const authStore = useAuthStore();
const router = useRouter();

const showPassword = ref(false);
const successMsg = ref('');

const activeTab = computed({
  get: () => authStore.authModalTab || 'login',
  set: (val) => {
    authStore.authModalTab = val;
  }
});

const loginForm = ref({
  email: '',
  password: ''
});

const registerForm = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  terms: false
});

const forgotForm = ref({
  email: ''
});

const intendedBannerIcon = computed(() => {
  const dest = authStore.intendedDestination;
  if (!dest) return '✨';
  if (dest === 'cart' || dest?.action === 'cart') return '🛒';
  if (dest === 'wishlist' || dest?.action === 'wishlist') return '❤️';
  if (dest === 'checkout' || dest?.action === 'checkout') return '🛍️';
  if (dest === 'write_review' || dest?.action === 'write_review') return '⭐';
  return '✨';
});

const intendedBannerTitle = computed(() => {
  const dest = authStore.intendedDestination;
  if (!dest) return 'Welcome to Maya Sree';
  if (dest === 'cart' || dest?.action === 'cart') return 'Item Added to Your Cart!';
  if (dest === 'wishlist' || dest?.action === 'wishlist') return 'Save Your Favorites';
  if (dest === 'checkout' || dest?.action === 'checkout') return 'Almost There';
  if (dest === 'write_review' || dest?.action === 'write_review') return 'Share Your Experience';
  return 'Maya Sree Customer Account';
});

const intendedBannerMessage = computed(() => {
  const dest = authStore.intendedDestination;
  if (!dest) return null;
  if (dest === 'cart' || dest?.action === 'cart') {
    return 'Create a customer account to save your cart items, track orders in real-time, and get exclusive rewards!';
  }
  if (dest === 'wishlist' || dest?.action === 'wishlist') {
    return 'Create an account or sign in to save your favorite products and access them anytime.';
  }
  if (dest === 'checkout' || dest?.action === 'checkout') {
    return 'Sign in or create an account to continue with your order and track shipping status.';
  }
  if (dest === 'write_review' || dest?.action === 'write_review') {
    return 'Please sign in to share your verified purchase experience with this product.';
  }
  return null;
});

const switchTab = (tab) => {
  successMsg.value = '';
  authStore.error = null;
  authStore.validationErrors = {};
  activeTab.value = tab;
};

const closeModal = () => {
  successMsg.value = '';
  authStore.closeAuthModal();
};

const handleLogin = async () => {
  successMsg.value = '';
  try {
    await authStore.login({
      email: loginForm.value.email,
      password: loginForm.value.password
    });
    emit('auth-success');
    dispatchIntendedAction();
  } catch (err) {
    // Error handled in store
  }
};

const handleRegister = async () => {
  successMsg.value = '';
  try {
    await authStore.register({
      name: registerForm.value.name,
      email: registerForm.value.email,
      phone: registerForm.value.phone,
      password: registerForm.value.password,
      password_confirmation: registerForm.value.password_confirmation,
      terms: registerForm.value.terms
    });
    emit('auth-success');
    dispatchIntendedAction();
  } catch (err) {
    // Error handled in store
  }
};

const handleForgotPassword = async () => {
  successMsg.value = '';
  try {
    const res = await authStore.forgotPassword(forgotForm.value.email);
    successMsg.value = res.message || 'Password reset link sent to your email!';
  } catch (err) {
    // Error handled in store
  }
};

const dispatchIntendedAction = () => {
  const dest = authStore.intendedDestination;
  authStore.intendedDestination = null;

  if (!dest) return;

  if (typeof dest === 'function') {
    dest();
  } else if (typeof dest === 'string') {
    if (dest === 'checkout') {
      router.push('/checkout');
    } else if (dest === 'wishlist') {
      router.push('/my-account?tab=wishlist');
    }
  } else if (dest.name || dest.path) {
    router.push(dest);
  }
};
</script>

<style scoped>
.auth-modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(17, 17, 17, 0.65);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  animation: fadeIn 0.25s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.auth-modal-card {
  position: relative;
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border-radius: 20px;
  padding: 2.25rem 2rem;
  box-shadow: 0 20px 50px rgba(74, 25, 54, 0.2);
  border: 1px solid rgba(212, 175, 55, 0.25);
  max-height: 90vh;
  overflow-y: auto;
  scrollbar-width: none;
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.auth-modal-card::-webkit-scrollbar {
  display: none;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.auth-modal-close {
  position: absolute;
  top: 1rem;
  right: 1.2rem;
  background: #faf5f0;
  border: 1px solid rgba(74, 25, 54, 0.15);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 1rem;
  color: #4a1936;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.auth-modal-close:hover {
  background: #4a1936;
  color: #ffffff;
}

.auth-context-banner {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  background: #faf5f0;
  border: 1px solid rgba(212, 175, 55, 0.35);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  margin-bottom: 1.25rem;
}

.auth-context-icon {
  font-size: 1.3rem;
}

.auth-context-text strong {
  display: block;
  font-size: 0.88rem;
  color: #4a1936;
  margin-bottom: 2px;
}

.auth-context-text p {
  font-size: 0.78rem;
  color: #555555;
  margin: 0;
  line-height: 1.35;
}

.auth-brand-header {
  text-align: center;
  margin-bottom: 1.25rem;
}

.auth-brand-logo {
  height: 48px;
  width: 48px;
  object-fit: cover;
  border-radius: 50%;
  border: 1.5px solid #d4af37;
  margin-bottom: 0.5rem;
}

.auth-modal-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #4a1936;
  margin-bottom: 0.25rem;
}

.auth-modal-subtitle {
  font-family: 'Poppins', sans-serif;
  font-size: 0.825rem;
  color: #666666;
  line-height: 1.4;
  margin: 0;
}

.auth-tab-group {
  display: flex;
  background: #faf5f0;
  border-radius: 30px;
  padding: 4px;
  margin-bottom: 1.25rem;
  border: 1px solid rgba(74, 25, 54, 0.1);
}

.auth-tab-btn {
  flex: 1;
  padding: 0.5rem 0;
  border: none;
  background: transparent;
  border-radius: 25px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.825rem;
  font-weight: 600;
  color: #666666;
  cursor: pointer;
  transition: all 0.2s ease;
}

.auth-tab-btn.active {
  background: #4a1936;
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(74, 25, 54, 0.25);
}

.auth-alert {
  padding: 0.65rem 1rem;
  border-radius: 10px;
  font-size: 0.825rem;
  margin-bottom: 1rem;
  text-align: center;
}

.auth-alert--error {
  background: #fdf2f2;
  border: 1px solid #f8b4b4;
  color: #9b1c1c;
}

.auth-alert--success {
  background: #f3faf7;
  border: 1px solid #84e1bc;
  color: #0e6245;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-label {
  font-family: 'Poppins', sans-serif;
  font-size: 0.8rem;
  font-weight: 600;
  color: #333333;
  margin-bottom: 0.35rem;
}

.forgot-link {
  background: none;
  border: none;
  font-size: 0.75rem;
  color: #4a1936;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}

.forgot-link:hover {
  text-decoration: underline;
}

.form-input {
  width: 100%;
  padding: 0.65rem 0.9rem;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.875rem;
  color: #1a1a1a;
  background: #ffffff;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.form-input:focus {
  border-color: #4a1936;
  box-shadow: 0 0 0 3px rgba(74, 25, 54, 0.1);
}

.is-invalid {
  border-color: #e53e3e !important;
}

.error-text {
  font-size: 0.75rem;
  color: #e53e3e;
  margin-top: 0.25rem;
}

.password-input-wrapper {
  position: relative;
  width: 100%;
}

.password-toggle-btn {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.9rem;
  padding: 0;
}

.terms-checkbox-group {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.terms-checkbox-group input {
  margin-top: 3px;
}

.terms-label {
  font-size: 0.78rem;
  color: #555555;
  line-height: 1.35;
}

.terms-label a {
  color: #4a1936;
  font-weight: 600;
  text-decoration: none;
}

.terms-label a:hover {
  text-decoration: underline;
}

.btn-auth-primary {
  width: 100%;
  padding: 0.75rem;
  background: #4a1936;
  color: #ffffff;
  border: none;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 0.5rem;
  transition: background-color 0.2s, transform 0.1s;
  box-shadow: 0 4px 12px rgba(74, 25, 54, 0.2);
}

.btn-auth-primary:hover {
  background: #361126;
  transform: translateY(-1px);
}

.btn-auth-primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.auth-footer-prompt {
  text-align: center;
  font-size: 0.825rem;
  color: #666666;
  margin-top: 0.75rem;
}

.auth-switch-link {
  background: none;
  border: none;
  color: #4a1936;
  font-weight: 700;
  cursor: pointer;
  padding: 0 2px;
}

.auth-switch-link:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-modal-card {
    padding: 1.75rem 1.25rem;
    border-radius: 16px;
  }
}
</style>
