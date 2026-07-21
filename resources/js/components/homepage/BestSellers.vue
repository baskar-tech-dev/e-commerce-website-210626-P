<template>
  <section class="section best-sellers-section">
    <SectionHeader
      label="Most Loved"
      title="Best Sellers"
      subtitle="Our most cherished and highest-rated South Indian fashion pieces."
      view-more-to="/shop?sort_by=popular"
      view-more-label="View All Best Sellers"
    />
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
import SectionHeader from './SectionHeader.vue';

const props = defineProps({
  products:    { type: Array, default: () => [] },
  wishlistIds: { type: Array, default: () => [] },
});
defineEmits(['toggle-wishlist', 'quick-add']);

const items        = computed(() => props.products.slice(0, 4));
const isWishlisted = (id) => props.wishlistIds.includes(id);
</script>

<style scoped>
.best-sellers-section {
  padding: 48px 20px;
  max-width: 1400px;
  margin: 0 auto;
}
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
@media (max-width: 1024px) { .products-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px)  { .products-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } }
</style>
