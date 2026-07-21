<template>
  <section class="section instagram-section" v-if="reels && reels.length > 0">
    <div class="section-header text-center">
      <h2 class="section-title">FOLLOW US ON INSTAGRAM</h2>
      <p class="section-subtitle">Tag @mayasreefashion to be featured in our style gallery.</p>
    </div>

    <div class="reels-scroll-row">
      <div 
        v-for="reel in reels" 
        :key="reel.id" 
        class="reel-card"
        @click="$emit('select-reel', reel)"
      >
        <div class="reel-video-wrapper">
          <img :src="reel.thumbnail_url || '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp'" :alt="reel.title" class="reel-thumbnail" loading="lazy">
          <div class="reel-card-overlay">
            <span class="reel-icon-badge">▶</span>
            <p class="reel-caption">{{ reel.title }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  reels: {
    type: Array,
    default: () => []
  }
});

defineEmits(['select-reel']);
</script>

<style scoped>
.instagram-section {
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
.reels-scroll-row {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  padding-bottom: 12px;
}
.reel-card {
  flex: 0 0 calc(25% - 12px);
  min-width: 200px;
  aspect-ratio: 9/16;
  border-radius: 12px;
  overflow: hidden;
  position: relative;
  cursor: pointer;
}
@media (max-width: 768px) {
  .reel-card { flex: 0 0 160px; }
}
.reel-video-wrapper {
  width: 100%;
  height: 100%;
  position: relative;
}
.reel-thumbnail {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.reel-card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent 60%);
  padding: 12px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: #ffffff;
}
.reel-icon-badge {
  align-self: flex-end;
  background: rgba(74, 14, 46, 0.85);
  color: #fff;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
}
.reel-caption {
  font-size: 0.8rem;
  font-weight: 500;
  line-height: 1.3;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
