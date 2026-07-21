<template>
  <section class="section reviews-section">
    <div class="section-header text-center">
      <h2 class="section-title">CUSTOMER REVIEWS <span class="heart-icon">♡</span></h2>
      <p class="section-subtitle">Real experiences shared by our valued patrons across South India.</p>
    </div>

    <div class="reviews-grid">
      <div v-for="rev in testimonials" :key="rev.id || rev.name" class="review-card">
        <div class="review-rating">
          <Star v-for="n in (rev.rating || 5)" :key="n" :size="14" class="star-filled" />
        </div>
        <p class="review-comment">"{{ rev.comment }}"</p>
        
        <div class="reviewer-meta">
          <div class="avatar-circle">
            <span v-if="!rev.avatar">👤</span>
            <img v-else :src="rev.avatar" :alt="rev.name" class="avatar-img">
          </div>
          <div>
            <h4 class="reviewer-name">{{ rev.name }}</h4>
            <span class="reviewer-city">{{ rev.city }} · <span class="verified-tag">✓ Verified Buyer</span></span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { Star } from 'lucide-vue-next';
import { useCmsStore } from '../../stores/cms';

const cmsStore = useCmsStore();

const testimonials = computed(() => {
  if (cmsStore.testimonials && cmsStore.testimonials.length > 0) {
    return cmsStore.testimonials;
  }
  return [
    { id: 1, name: 'Priya Ramachandran', city: 'Chennai', rating: 5, comment: 'The stretchable blouse quality is outstanding! No stitching hassle at all, and it fits like a glove.' },
    { id: 2, name: 'Kavitha Sundaram', city: 'Coimbatore', rating: 5, comment: 'Bought Kora Silk Sarees for a family wedding. The zari border look and soft drape exceeded my expectations.' },
    { id: 3, name: 'Anitha Venkatesh', city: 'Bengaluru', rating: 5, comment: 'Fast 2-day delivery to Bangalore! Soft breathable fabric perfect for everyday office wear.' }
  ];
});
</script>

<style scoped>
.reviews-section {
  padding: 40px 20px;
  max-width: 1400px;
  margin: 0 auto;
}
.section-header {
  margin-bottom: 24px;
}
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: var(--color-primary, #4a0e2e);
  margin-bottom: 6px;
}
.section-subtitle {
  color: #64748b;
  font-size: 0.9rem;
}
.reviews-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
@media (max-width: 1024px) {
  .reviews-grid { grid-template-columns: repeat(1, 1fr); }
}
.review-card {
  background: #ffffff;
  border: 1px solid #f1e6df;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.review-rating {
  display: flex;
  gap: 4px;
  margin-bottom: 12px;
}
.star-filled {
  fill: var(--color-secondary, #d4af37);
  color: var(--color-secondary, #d4af37);
}
.review-comment {
  font-style: italic;
  font-size: 0.92rem;
  color: #334155;
  line-height: 1.6;
  margin-bottom: 20px;
}
.reviewer-meta {
  display: flex;
  align-items: center;
  gap: 12px;
}
.avatar-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}
.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.reviewer-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}
.reviewer-city {
  font-size: 0.75rem;
  color: #64748b;
}
.verified-tag {
  color: #16a34a;
  font-weight: 500;
}
</style>
