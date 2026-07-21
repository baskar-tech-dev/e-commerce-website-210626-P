<template>
  <div class="skeleton-wrapper">
    <!-- Type: Product Card Grid Skeleton -->
    <div v-if="type === 'product-card' || type === 'grid'" class="skeleton-product-grid">
      <div v-for="i in (count || 4)" :key="i" class="skeleton-product-card">
        <div class="skeleton-pulse skeleton-img"></div>
        <div class="skeleton-body">
          <div class="skeleton-pulse skeleton-line line-title"></div>
          <div class="skeleton-pulse skeleton-line line-subtitle"></div>
          <div class="skeleton-pulse skeleton-line line-price"></div>
        </div>
      </div>
    </div>

    <!-- Type: PDP (Product Details Page) Skeleton -->
    <div v-else-if="type === 'pdp'" class="skeleton-pdp-container">
      <div class="skeleton-pdp-gallery">
        <div class="skeleton-pulse skeleton-main-img"></div>
        <div class="skeleton-thumbs">
          <div v-for="t in 4" :key="t" class="skeleton-pulse skeleton-thumb"></div>
        </div>
      </div>
      <div class="skeleton-pdp-info">
        <div class="skeleton-pulse skeleton-line" style="width: 30%; height: 14px;"></div>
        <div class="skeleton-pulse skeleton-line" style="width: 75%; height: 28px; margin: 12px 0;"></div>
        <div class="skeleton-pulse skeleton-line" style="width: 40%; height: 24px; margin-bottom: 20px;"></div>
        <div class="skeleton-pulse skeleton-box" style="height: 120px; border-radius: 12px; margin-bottom: 20px;"></div>
        <div class="skeleton-pulse skeleton-box" style="height: 48px; border-radius: 8px;"></div>
      </div>
    </div>

    <!-- Type: Banner / Hero Skeleton -->
    <div v-else-if="type === 'banner'" class="skeleton-banner">
      <div class="skeleton-pulse skeleton-banner-card"></div>
    </div>

    <!-- Type: Form / Table / Account Skeleton -->
    <div v-else class="skeleton-block-container">
      <div v-for="i in (count || 3)" :key="i" class="skeleton-pulse skeleton-block"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'grid'
  },
  count: {
    type: Number,
    default: 4
  }
});
</script>

<style scoped>
.skeleton-wrapper {
  width: 100%;
}

.skeleton-pulse {
  background: linear-gradient(90deg, #f1e6df 25%, #fffbf7 37%, #f1e6df 63%);
  background-size: 400% 100%;
  animation: skeleton-shimmer 1.4s ease infinite;
}

@keyframes skeleton-shimmer {
  0% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0 50%;
  }
}

/* Product Card Grid */
.skeleton-product-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.skeleton-product-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 12px;
  border: 1px solid #f1e6df;
}

.skeleton-img {
  aspect-ratio: 4/5;
  border-radius: 12px;
  margin-bottom: 12px;
  width: 100%;
}

.skeleton-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.skeleton-line {
  border-radius: 4px;
}

.line-title { height: 16px; width: 85%; }
.line-subtitle { height: 14px; width: 60%; }
.line-price { height: 18px; width: 40%; margin-top: 4px; }

/* PDP Skeleton */
.skeleton-pdp-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
}

.skeleton-main-img {
  aspect-ratio: 4/5;
  border-radius: 16px;
  width: 100%;
  margin-bottom: 12px;
}

.skeleton-thumbs {
  display: flex;
  gap: 12px;
}

.skeleton-thumb {
  width: 64px;
  height: 64px;
  border-radius: 8px;
}

/* Banner Skeleton */
.skeleton-banner-card {
  width: 100%;
  height: 480px;
  border-radius: 20px;
}

.skeleton-block {
  height: 80px;
  border-radius: 12px;
  margin-bottom: 12px;
}

@media (max-width: 992px) {
  .skeleton-product-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  .skeleton-pdp-container {
    grid-template-columns: 1fr;
  }
}
</style>
