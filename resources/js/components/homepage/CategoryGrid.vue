<template>
  <section class="section categories-section">
    <div class="section-header text-center">
      <h2 class="section-title">EXPLORE CATEGORIES <span class="heart-icon">♡</span></h2>
      <p class="section-subtitle">Curated South Indian fashion collections designed for elegance and comfort.</p>
    </div>
    <div class="category-grid">
      <router-link 
        v-for="cat in categories" 
        :key="cat.id" 
        :to="cat.link" 
        class="category-card"
      >
        <div class="category-img-wrapper">
          <img :src="cat.image" :alt="cat.name" class="category-img" loading="lazy">
        </div>
        <div class="category-overlay">
          <h3 class="category-name">{{ cat.name }}</h3>
          <span class="btn-explore">EXPLORE ➔</span>
        </div>
      </router-link>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { useCategoryStore } from '../../stores/category';

const categoryStore = useCategoryStore();

const fallbackCategories = [
  { id: 1, name: 'Sarees & Drapes', image: '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp', link: '/shop?category=sarees' },
  { id: 2, name: 'Readymade Blouses', image: '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg', link: '/shop?category=blouses' },
  { id: 3, name: 'Kurtis & Sets', image: '/storage/products/3/webp/35ec359a-7767-493f-9c88-ec3ed914275e.webp', link: '/shop?category=kurtis' },
  { id: 4, name: 'Dupattas & Stoles', image: '/storage/products/4/webp/029bbc54-5a9c-4467-b7d7-af9778fad24f.webp', link: '/shop?category=dupatta' }
];

const categories = computed(() => {
  const active = categoryStore.categories.filter(c => c.is_active);
  if (active.length > 0) {
    return active.slice(0, 4).map((c, idx) => ({
      id: c.id,
      name: c.name,
      image: c.image || `/storage/products/${idx + 1}/webp/${idx + 1}.webp`,
      link: `/shop?category_id=${c.id}`
    }));
  }
  return fallbackCategories;
});
</script>

<style scoped>
.categories-section {
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
.category-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
@media (max-width: 1024px) {
  .category-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .category-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
}
.category-card {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  aspect-ratio: 3/4;
  display: block;
  text-decoration: none;
}
.category-img-wrapper {
  width: 100%;
  height: 100%;
}
.category-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.category-card:hover .category-img {
  transform: scale(1.06);
}
.category-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(74, 14, 46, 0.85) 0%, rgba(0, 0, 0, 0.1) 60%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 20px;
  color: #ffffff;
}
.category-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  margin-bottom: 4px;
}
.btn-explore {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1px;
  color: var(--color-secondary, #d4af37);
}
</style>
