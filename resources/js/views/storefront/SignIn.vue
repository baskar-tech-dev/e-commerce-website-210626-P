<template>
  <div class="signin-container">
    <div class="signin-card glass-panel">
      <!-- Header -->
      <div class="signin-header">
        <div class="signin-logo">
          <img :src="'/asset/profile/logo.png'" alt="Maya Sree Logo">
        </div>
        <h1 class="signin-title">Welcome back</h1>
        <p class="signin-subtitle">Enter your credentials to access your account</p>
      </div>

      

      

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="signin-form">
        <!-- Error Message -->
        <div v-if="authStore.error" style="color: red; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">
          {{ authStore.error }}
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email address</label>
          <input type="email" id="email" v-model="form.email" class="form-input" :class="{ 'is-invalid': authStore.validationErrors.email }" placeholder="name@example.com" required>
          <span v-if="authStore.validationErrors.email" class="error-text">
            {{ authStore.validationErrors.email[0] }}
          </span>
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <label class="form-label" for="password">Password</label>
            <a href="#" class="forgot-password-link">Forgot password?</a>
          </div>
          <input type="password" id="password" v-model="form.password" class="form-input" :class="{ 'is-invalid': authStore.validationErrors.password }" placeholder="••••••••" required>
          <span v-if="authStore.validationErrors.password" class="error-text">
            {{ authStore.validationErrors.password[0] }}
          </span>
        </div>
 
        <button type="submit" class="btn-submit" :disabled="authStore.loading" style="background-color: var(--color-primary, #4A1936); color: white; border: none; cursor: pointer; font-weight: 600;">
          {{ authStore.loading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>

      
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: ''
});

const handleLogin = async () => {
  try {
    await authStore.login({ email: form.value.email, password: form.value.password });
    router.push('/admin');
  } catch (e) {
    // Error is handled in the store and displayed via authStore.error
  }
};
</script>

<style scoped>
.signin-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 1rem; /* Ensures it looks good on mobile devices without spilling */
  box-sizing: border-box;
}

.signin-card {
  width: 100%;
  max-width: 420px;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.signin-header {
  text-align: center;
  margin-bottom: 2rem;
}

.signin-logo img {
  height: 48px;
  width: 48px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid var(--color-border);
  margin: 0 auto 1rem;
}

.signin-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text-primary);
  margin-bottom: 0.5rem;
}

.signin-subtitle {
  font-size: 0.9rem;
  color: var(--color-text-secondary);
}

.social-login-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.btn-social {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: transparent;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 0.6rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--color-text-primary);
  cursor: pointer;
  transition: background-color 0.2s, border-color 0.2s;
}

.btn-social:hover {
  background: var(--color-surface-hover);
  border-color: var(--color-primary);
}

.icon-social {
  width: 1.2rem;
  height: 1.2rem;
}

.signin-divider {
  position: relative;
  text-align: center;
  margin: 1.5rem 0;
}

.signin-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: var(--color-border);
  z-index: 1;
}

.signin-divider-text {
  position: relative;
  z-index: 2;
  background: var(--color-surface); /* match card bg */
  padding: 0 0.75rem;
  color: var(--color-text-secondary);
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.signin-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 0.4rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background: transparent;
  color: var(--color-text-primary);
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(74, 25, 54, 0.1); /* Primary color shadow */
}

.is-invalid {
  border-color: #ef4444 !important;
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 0.8rem;
  margin-top: 0.4rem;
}

.forgot-password-link {
  font-size: 0.8rem;
  color: var(--color-primary);
  text-decoration: none;
  font-weight: 500;
}

.forgot-password-link:hover {
  text-decoration: underline;
}

.btn-submit {
  width: 100%;
  padding: 0.75rem;
  font-size: 1rem;
  border-radius: 8px;
  margin-top: 0.5rem;
}

.signin-footer {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.9rem;
  color: var(--color-text-secondary);
}

.signup-link {
  color: var(--color-primary);
  font-weight: 600;
  text-decoration: none;
}

.signup-link:hover {
  text-decoration: underline;
}

/* Fix for dark mode overrides from app.css if needed */
.glass-panel {
  background: #ffffff !important; 
}
.signin-title, .form-label, .btn-social {
  color: #1a1a1a !important;
}
.signin-subtitle, .signin-divider-text, .signin-footer {
  color: #666666 !important;
}
.form-input {
  color: #1a1a1a !important;
  background: #ffffff !important;
  border: 1px solid #e5e5e5 !important;
}
.form-input:focus {
  border-color: var(--color-primary) !important;
}
.signin-divider::before {
  background: #e5e5e5 !important;
}
.btn-social {
  border: 1px solid #e5e5e5 !important;
}
</style>
