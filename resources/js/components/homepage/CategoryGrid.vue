<template>
  <section class="section categories-section">
    <div class="section-header-wrap">
      <div class="section-header text-center">
        <h2 class="section-title">EXPLORE CATEGORIES <span class="heart-icon">♡</span></h2>
        <p class="section-subtitle">Curated South Indian fashion collections designed for elegance and comfort.</p>
      </div>

      <!-- Desktop Scroll Buttons -->
      <div class="scroll-controls" v-if="categories.length > 2">
        <button class="scroll-btn prev" @click="scroll('left')" aria-label="Scroll left">
          <ChevronLeft :size="18" />
        </button>
        <button class="scroll-btn next" @click="scroll('right')" aria-label="Scroll right">
          <ChevronRight :size="18" />
        </button>
      </div>
    </div>

    <!-- Horizontally Scrollable Categories Container -->
    <div class="category-scroll-container" ref="scrollContainer">
      <div class="category-scroll-track">
        <router-link 
          v-for="cat in categories" 
          :key="cat.id" 
          :to="cat.link" 
          class="luxury-category-card"
        >
          <!-- Left Curved Arch Image Stage -->
          <div class="arch-image-stage">
            <img :src="cat.image" :alt="cat.name" class="arch-img" loading="lazy" />
          </div>

          <!-- Right Content & Editorial Badges -->
          <div class="luxury-card-content">
            <!-- Top Gold Floral Motif -->
            <div class="gold-crown-motif">
              <span class="motif-ornament">⚜</span>
            </div>

            <!-- Title -->
            <div class="title-wrapper">
              <h3 class="luxury-cat-title" v-html="formatCategoryTitle(cat.name)"></h3>
              <div class="gold-divider">
                <span class="divider-diamond">◆</span>
              </div>
            </div>

            <!-- Bottom Circular Feature Badges -->
            <div class="feature-badges-grid">
              <div class="feature-badge">
                <div class="badge-circle"><Award :size="12" /></div>
                <span class="badge-text">PREMIUM<br>QUALITY</span>
              </div>
              <div class="feature-badge">
                <div class="badge-circle"><Feather :size="12" /></div>
                <span class="badge-text">SOFT<br>FABRIC</span>
              </div>
              <div class="feature-badge">
                <div class="badge-circle"><Sparkles :size="12" /></div>
                <span class="badge-text">GENTLE<br>ON SKIN</span>
              </div>
              <div class="feature-badge">
                <div class="badge-circle"><Heart :size="12" /></div>
                <span class="badge-text">COMFORT<br>WEAR</span>
              </div>
            </div>

            <!-- Explore Action -->
            <div class="explore-row">
              <span class="btn-explore-gold">EXPLORE ➔</span>
            </div>
          </div>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ChevronLeft, ChevronRight, Award, Feather, Sparkles, Heart } from 'lucide-vue-next';
import { useCategoryStore } from '../../stores/category';

const categoryStore = useCategoryStore();
const scrollContainer = ref(null);

onMounted(() => {
  categoryStore.fetchPublicCategories({ featured: 1 });
});

const scroll = (direction) => {
  if (!scrollContainer.value) return;
  const isMobile = window.innerWidth <= 767;
  const amount = isMobile ? (direction === 'left' ? -180 : 180) : (direction === 'left' ? -340 : 340);
  scrollContainer.value.scrollBy({ left: amount, behavior: 'smooth' });
};

const formatCategoryTitle = (name) => {
  const n = (name || '').toUpperCase();
  if (n.includes('BLOUSE')) return 'READY MADE<br>BLOUSE';
  if (n.includes('WOMEN') || n.includes('WOMENS')) return 'WOMENS<br>WEAR';
  if (n.includes('KID') || n.includes('KIDS')) return 'KIDS<br>WEAR';
  if (n.includes('MEN') || n.includes('MENS')) return 'MENS<br>WEAR';
  return n.replace(' ', '<br>');
};

const fallbackCategories = [
  { id: 1, name: 'ReadyMade Blouse', image: '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg', link: '/shop?category=readymade-blouse' },
  { id: 2, name: 'Womens', image: '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp', link: '/shop?category=womens' },
  { id: 3, name: 'Kids', image: '/asset/Baby-Girls-Floral-Print-Cotton.jpeg', link: '/shop?category=kids' },
  { id: 4, name: 'Mens', image: '/asset/Baby-Girl-Sleeveless-Striped-Dress.jpeg', link: '/shop?category=mens' }
];

const categories = computed(() => {
  const active = categoryStore.categories.filter(c => c.is_active);
  if (active.length > 0) {
    const featured = active.filter(c => c.is_featured);
    const listToDisplay = featured.length > 0 ? featured : active;

    return listToDisplay.map((c, idx) => ({
      id: c.id,
      name: c.name,
      image: c.image || `/storage/products/${(idx % 4) + 1}/webp/default.webp`,
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
.section-header-wrap {
  position: relative;
  margin-bottom: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.section-header {
  margin-bottom: 0;
}
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: var(--color-primary, #4a0e2e);
  margin-bottom: 6px;
}
.heart-icon {
  color: var(--color-secondary, #d4af37);
}
.section-subtitle {
  color: #64748b;
  font-size: 0.9rem;
}

/* Scroll Controls */
.scroll-controls {
  position: absolute;
  right: 0;
  bottom: 0;
  display: flex;
  gap: 8px;
  z-index: 10;
}
.scroll-btn {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  border: 1px solid rgba(212, 175, 55, 0.4);
  background: #ffffff;
  color: #4a0e2e;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(74, 14, 46, 0.08);
  transition: all 0.25s ease;
  -webkit-tap-highlight-color: transparent;
}
.scroll-btn:hover, .scroll-btn:active {
  background: #4a0e2e;
  color: #ffffff;
  border-color: #4a0e2e;
}

/* Scroll Container & Track */
.category-scroll-container {
  width: 100%;
  overflow-x: auto;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  -ms-overflow-style: none;
  padding: 10px 4px 20px;
}
.category-scroll-container::-webkit-scrollbar {
  display: none;
}

.category-scroll-track {
  display: flex;
  gap: 20px;
  width: max-content;
}

/* Luxury Category Card */
.luxury-category-card {
  position: relative;
  width: 380px;
  height: 360px;
  border-radius: 20px;
  overflow: hidden;
  background: linear-gradient(135deg, #FAF6F0 0%, #F5ECE0 100%);
  border: 1px solid rgba(212, 175, 55, 0.35);
  box-shadow: 0 10px 28px rgba(74, 14, 46, 0.08);
  display: flex;
  flex-direction: row;
  text-decoration: none;
  flex-shrink: 0;
  transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.luxury-category-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 36px rgba(74, 14, 46, 0.16);
}

/* Left Curved Arch Image Stage */
.arch-image-stage {
  position: relative;
  width: 53%;
  height: 100%;
  border-top-right-radius: 170px 100%;
  border-bottom-right-radius: 170px 100%;
  overflow: hidden;
  border-right: 3px solid #d4af37;
  box-shadow: 4px 0 16px rgba(181, 140, 39, 0.22);
  flex-shrink: 0;
  z-index: 2;
}

.arch-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
  transition: transform 0.6s ease;
}

.luxury-category-card:hover .arch-img {
  transform: scale(1.08);
}

/* Right Content Area */
.luxury-card-content {
  position: relative;
  width: 47%;
  height: 100%;
  padding: 22px 12px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  box-sizing: border-box;
  text-align: center;
  z-index: 1;
  background: radial-gradient(circle at top right, rgba(255,255,255,0.7), transparent);
}

.gold-crown-motif {
  color: #c59b27;
  font-size: 1.2rem;
  line-height: 1;
}

.title-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.luxury-cat-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.45rem;
  font-weight: 800;
  line-height: 1.15;
  color: #4a0e2e;
  letter-spacing: 1.5px;
  margin: 0;
  background: linear-gradient(135deg, #4a0e2e 0%, #8b1d42 50%, #b8860b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.gold-divider {
  position: relative;
  width: 75%;
  height: 1px;
  background: linear-gradient(90deg, transparent, #d4af37, transparent);
  margin: 2px 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.divider-diamond {
  font-size: 0.45rem;
  color: #d4af37;
  background: #f7eee2;
  padding: 0 4px;
}

/* 4 Feature Badges Grid */
.feature-badges-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px 4px;
  width: 100%;
}

.feature-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
}

.badge-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1px solid #d4af37;
  background: #ffffff;
  color: #4a0e2e;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 6px rgba(212,175,55,0.2);
}

.badge-text {
  font-family: 'Poppins', sans-serif;
  font-size: 0.52rem;
  font-weight: 700;
  line-height: 1.1;
  color: #4a1525;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

.explore-row {
  margin-top: 2px;
}

.btn-explore-gold {
  font-family: 'Poppins', sans-serif;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 1px;
  color: #b8860b;
  text-transform: uppercase;
  transition: color 0.25s ease;
}

.luxury-category-card:hover .btn-explore-gold {
  color: #4a0e2e;
}

/* Responsive adjustments for Mobile (Showing 2 cards side-by-side) */
@media (max-width: 767px) {
  .categories-section {
    padding: 24px 10px;
  }

  .section-header-wrap {
    margin-bottom: 16px;
    align-items: flex-start;
  }

  .section-header {
    text-align: left !important;
    padding-right: 70px;
  }

  .section-title {
    font-size: 1.35rem;
  }

  .section-subtitle {
    font-size: 0.78rem;
  }

  .scroll-controls {
    position: absolute;
    right: 4px;
    top: 4px;
    bottom: auto;
    display: flex;
    gap: 6px;
  }

  .scroll-btn {
    width: 30px;
    height: 30px;
  }

  .category-scroll-container {
    padding: 6px 2px 14px;
  }

  .category-scroll-track {
    gap: 10px;
  }

  /* 2 Cards visible side-by-side on mobile */
  .luxury-category-card {
    width: calc(47vw - 6px);
    min-width: 162px;
    max-width: 200px;
    height: 220px;
    border-radius: 14px;
  }

  .arch-image-stage {
    width: 50%;
    border-top-right-radius: 90px 100%;
    border-bottom-right-radius: 90px 100%;
    border-right-width: 2px;
  }

  .luxury-card-content {
    width: 50%;
    padding: 8px 4px 6px;
  }

  .gold-crown-motif {
    font-size: 0.85rem;
  }

  .luxury-cat-title {
    font-size: 0.82rem;
    letter-spacing: 0.5px;
  }

  .gold-divider {
    width: 85%;
  }

  .feature-badges-grid {
    gap: 4px 2px;
  }

  .badge-circle {
    width: 18px;
    height: 18px;
  }

  .badge-circle :deep(svg) {
    width: 9px;
    height: 9px;
  }

  .badge-text {
    font-size: 0.38rem;
    letter-spacing: 0.1px;
  }

  .btn-explore-gold {
    font-size: 0.5rem;
    letter-spacing: 0.5px;
  }
}
</style>
