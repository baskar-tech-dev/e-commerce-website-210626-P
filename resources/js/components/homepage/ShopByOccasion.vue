<template>
  <section class="section occasion-section">
    <div class="section-header text-center">
      <h2 class="section-title">SHOP BY OCCASION <span class="heart-icon">♡</span></h2>
      <p class="section-subtitle">Tailored outfits for every festive celebration and daily moment.</p>
    </div>
    <div class="occasion-grid">
      <router-link 
        v-for="occ in occasions" 
        :key="occ.id" 
        :to="occ.link" 
        class="occasion-card"
      >
        <div class="occasion-img-wrapper">
          <img :src="occ.image" :alt="occ.name" class="occasion-img" loading="lazy">
        </div>
        <div class="occasion-overlay">
          <h3 class="occasion-name">{{ occ.name }}</h3>
          <span class="btn-explore">EXPLORE ➔</span>
        </div>
      </router-link>
    </div>
  </section>
</template>

<script setup>
const occasions = [
  { id: 1, name: 'Daily Wear', image: '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp', link: '/shop?occasion=daily' },
  { id: 2, name: 'Office Wear', image: '/asset/Mustard-Yellow-Stretchable-Readymade-Blouse.jpeg', link: '/shop?occasion=office' },
  { id: 3, name: 'Wedding Collection', image: '/asset/festive-collection.jpg', link: '/shop?occasion=wedding' },
  { id: 4, name: 'Festival Collection', image: '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg', link: '/shop?occasion=festival' }
];
</script>

<style scoped>
.occasion-section {
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
.occasion-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
@media (max-width: 1024px) {
  .occasion-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .occasion-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
}
.occasion-card {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  aspect-ratio: 3/4;
  display: block;
  text-decoration: none;
}
.occasion-img-wrapper {
  width: 100%;
  height: 100%;
}
.occasion-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.occasion-card:hover .occasion-img {
  transform: scale(1.06);
}
.occasion-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(74, 14, 46, 0.85) 0%, rgba(0, 0, 0, 0.1) 60%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 20px;
  color: #ffffff;
}
.occasion-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem;
  margin-bottom: 4px;
}
.btn-explore {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 1px;
  color: var(--color-secondary, #d4af37);
}
</style>
