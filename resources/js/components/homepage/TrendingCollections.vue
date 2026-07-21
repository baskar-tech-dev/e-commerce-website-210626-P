<template>
  <section class="section trending-section">
    <div class="section-header text-center">
      <h2 class="section-title">TRENDING COLLECTIONS</h2>
      <p class="section-subtitle">Discover what South Indian fashion enthusiasts are loving right now.</p>
    </div>
    <div class="trending-grid">
      <router-link 
        v-for="item in collections" 
        :key="item.id" 
        :to="item.link" 
        class="trending-card"
      >
        <div class="trending-img-wrapper">
          <img :src="item.image" :alt="item.title" class="trending-img" loading="lazy">
        </div>
        <div class="trending-info">
          <span class="trending-badge">{{ item.badge }}</span>
          <h3 class="trending-card-title">{{ item.title }}</h3>
          <span class="btn-shop">SHOP NOW ➔</span>
        </div>
      </router-link>
    </div>
  </section>
</template>

<script setup>
const collections = [
  { id: 1, title: 'Cotton Lycra Stretchable Blouses', badge: 'MOST POPULAR', image: '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg', link: '/shop?category=blouses' },
  { id: 2, title: 'Traditional Kora Silk Sarees', badge: 'HERITAGE WEAVE', image: '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp', link: '/shop?category=sarees' },
  { id: 3, title: 'Festive Anarkalis & Kurtis', badge: 'TRENDING', image: '/storage/products/3/webp/35ec359a-7767-493f-9c88-ec3ed914275e.webp', link: '/shop?category=kurtis' }
];
</script>

<style scoped>
.trending-section {
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
.trending-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
@media (max-width: 1024px) {
  .trending-grid { grid-template-columns: repeat(1, 1fr); }
}
.trending-card {
  display: flex;
  background: #fffcf7;
  border: 1px solid #f1e6df;
  border-radius: 12px;
  overflow: hidden;
  text-decoration: none;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.trending-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px rgba(74, 14, 46, 0.08);
}
.trending-img-wrapper {
  width: 40%;
  aspect-ratio: 3/4;
}
.trending-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.trending-info {
  width: 60%;
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
}
.trending-badge {
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--color-secondary, #d4af37);
  background: rgba(212, 175, 55, 0.1);
  padding: 4px 8px;
  border-radius: 4px;
  margin-bottom: 8px;
}
.trending-card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.15rem;
  color: var(--color-primary, #4a0e2e);
  margin-bottom: 12px;
  line-height: 1.3;
}
.btn-shop {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-primary, #4a0e2e);
}
</style>
