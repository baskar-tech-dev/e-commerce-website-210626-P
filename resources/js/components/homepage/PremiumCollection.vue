<template>
  <section class="section premium-section">
    <div class="section-header text-center">
      <span class="luxury-badge">LUXURY LINE</span>
      <h2 class="section-title">THE PREMIUM COLLECTION</h2>
      <p class="section-subtitle">Exquisite silk drapes & hand-embroidered blouses crafted for royalty.</p>
    </div>

    <div class="products-grid">
      <ProductCard 
        v-for="product in items" 
        :key="product.id"
        :product="product"
        :is-wishlisted="isWishlisted(product.id)"
        @toggle-wishlist="$emit('toggle-wishlist', product)"
        @quick-add="(p, s) => $emit('quick-add', p, s)"
      />
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import ProductCard from '../ProductCard.vue';

const props = defineProps({
  products: {
    type: Array,
    default: () => []
  },
  wishlistIds: {
    type: Array,
    default: () => []
  }
});

defineEmits(['toggle-wishlist', 'quick-add']);

const items = computed(() => props.products.slice(0, 4));
const isWishlisted = (id) => props.wishlistIds.includes(id);
</script>

<style scoped>
.premium-section {
  padding: 48px 20px;
  background: #fffcf7;
  border-top: 1px solid #f1e6df;
  border-bottom: 1px solid #f1e6df;
  max-width: 1400px;
  margin: 0 auto;
}
.section-header {
  margin-bottom: 24px;
}
.luxury-badge {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 2px;
  color: var(--color-secondary, #d4af37);
  text-transform: uppercase;
  margin-bottom: 4px;
  display: block;
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
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
@media (max-width: 1024px) {
  .products-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .products-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
}
</style>
