<template>
  <section class="section festival-banner-section" v-if="festivalBanner && festivalBanner.is_active !== false">
    <div class="festival-banner-card" :style="{ backgroundImage: `linear-gradient(to right, rgba(74,14,46,0.92), rgba(74,14,46,0.7)), url(${festivalBanner.banner_image || '/asset/festive-collection.jpg'})` }">
      <div class="festival-banner-content">
        <span class="festival-tag">FESTIVE EXCLUSIVE</span>
        <h2 class="festival-title">{{ festivalBanner.title || 'South Indian Festive Season Special' }}</h2>
        <p class="festival-desc">{{ festivalBanner.subtitle || 'Get up to 25% Off on Selected Silk Sarees & Designer Blouse Ensembles' }}</p>
        
        <div class="coupon-pill-wrap" v-if="festivalBanner.coupon_code">
          <span>USE CODE: <strong>{{ festivalBanner.coupon_code }}</strong></span>
        </div>

        <router-link :to="festivalBanner.cta_link || '/shop'" class="btn-claim-offer">
          {{ festivalBanner.cta_text || 'CLAIM FESTIVE OFFER' }} ➔
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { useCmsStore } from '../../stores/cms';

const cmsStore = useCmsStore();

const festivalBanner = computed(() => cmsStore.promotions?.festival_banner || {
  title: 'South Indian Festive Season Special',
  subtitle: 'Get up to 25% Off on Selected Silk Sarees & Designer Blouse Ensembles',
  coupon_code: 'MAYASREE25',
  cta_text: 'CLAIM FESTIVE OFFER',
  cta_link: '/shop',
  banner_image: '/asset/festive-collection.jpg',
  is_active: true
});
</script>

<style scoped>
.festival-banner-section {
  padding: 40px 20px;
  max-width: 1400px;
  margin: 0 auto;
}
.festival-banner-card {
  border-radius: 16px;
  background-size: cover;
  background-position: center;
  padding: 60px 40px;
  color: #ffffff;
  box-shadow: 0 12px 24px rgba(74, 14, 46, 0.15);
}
@media (max-width: 768px) {
  .festival-banner-card { padding: 36px 20px; }
}
.festival-banner-content {
  max-width: 650px;
}
.festival-tag {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 2px;
  color: var(--color-secondary, #d4af37);
  background: rgba(212, 175, 55, 0.15);
  padding: 4px 10px;
  border-radius: 4px;
  display: inline-block;
  margin-bottom: 12px;
}
.festival-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  line-height: 1.2;
  margin-bottom: 12px;
}
@media (max-width: 768px) {
  .festival-title { font-size: 1.6rem; }
}
.festival-desc {
  font-size: 1rem;
  opacity: 0.9;
  margin-bottom: 20px;
  line-height: 1.5;
}
.coupon-pill-wrap {
  display: inline-block;
  border: 1px dashed var(--color-secondary, #d4af37);
  padding: 6px 16px;
  border-radius: 20px;
  margin-bottom: 20px;
  font-size: 0.85rem;
}
.btn-claim-offer {
  display: inline-block;
  background-color: var(--color-secondary, #d4af37);
  color: #4a0e2e;
  font-weight: 700;
  font-size: 0.85rem;
  padding: 12px 28px;
  border-radius: 24px;
  text-decoration: none;
  transition: transform 0.2s ease, background-color 0.2s ease;
}
.btn-claim-offer:hover {
  background-color: #f3c748;
  transform: translateY(-2px);
}
</style>
