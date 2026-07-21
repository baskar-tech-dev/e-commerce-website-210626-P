<template>
  <section class="section newsletter-section">
    <div class="newsletter-card">
      <h2 class="newsletter-title">{{ title }}</h2>
      <p class="newsletter-subtitle">{{ subtitle }}</p>

      <form @submit.prevent="handleSubscribe" class="newsletter-form">
        <input 
          type="email" 
          v-model="email" 
          placeholder="Enter your email address..." 
          class="newsletter-input" 
          required 
        />
        <button type="submit" class="newsletter-btn">SUBSCRIBE ➔</button>
      </form>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useCmsStore } from '../../stores/cms';

const cmsStore = useCmsStore();
const email = ref('');

const title = computed(() => cmsStore.newsletter?.newsletter_title || 'Join The Maya Sree Family');
const subtitle = computed(() => cmsStore.newsletter?.newsletter_subtitle || 'Subscribe for early access to new collection drops, festive offer codes, and blouse styling guides.');

const handleSubscribe = () => {
  if (email.value) {
    alert('✓ Thank you for subscribing! Check your email inbox for exclusive discount vouchers.');
    email.value = '';
  }
};
</script>

<style scoped>
.newsletter-section {
  padding: 40px 20px;
  max-width: 1400px;
  margin: 0 auto;
}
.newsletter-card {
  background: linear-gradient(135deg, #4a0e2e 0%, #2e081c 100%);
  border-radius: 16px;
  padding: 48px 24px;
  text-align: center;
  color: #ffffff;
}
.newsletter-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  margin-bottom: 8px;
  color: #ffffff;
}
.newsletter-subtitle {
  font-size: 0.95rem;
  color: #c9b1bd;
  max-width: 600px;
  margin: 0 auto 24px auto;
  line-height: 1.6;
}
.newsletter-form {
  display: flex;
  max-width: 500px;
  margin: 0 auto;
  gap: 8px;
}
@media (max-width: 640px) {
  .newsletter-form { flex-direction: column; }
}
.newsletter-input {
  flex: 1;
  padding: 12px 18px;
  border-radius: 24px;
  border: 1px solid rgba(255,255,255,0.2);
  outline: none;
  background: rgba(255,255,255,0.1);
  color: #ffffff;
  font-size: 0.9rem;
}
.newsletter-input::placeholder {
  color: rgba(255,255,255,0.6);
}
.newsletter-btn {
  background-color: var(--color-secondary, #d4af37);
  color: #4a0e2e;
  font-weight: 700;
  font-size: 0.85rem;
  padding: 12px 24px;
  border-radius: 24px;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s ease;
}
.newsletter-btn:hover {
  background-color: #f3c748;
}
</style>
