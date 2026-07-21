<template>
  <div class="product-card">
    <!-- Badges -->
    <div class="product-badges">
      <span v-if="product.is_new || product.new_arrival" class="product-tag new-arrival">New</span>
      <span v-else-if="product.is_trending" class="product-tag trending">Trending</span>
      <span v-else-if="product.is_best_seller" class="product-tag selling-fast">Best Seller</span>
      <span v-else-if="discountPercentage > 0" class="product-tag discount">-{{ discountPercentage }}%</span>
    </div>

    <!-- Image Container -->
    <div class="product-image-container">
      <router-link :to="`/products/${product.uuid || product.id}`" class="product-img-link">
        <img 
          :src="primaryImage" 
          :alt="product.name" 
          class="product-image"
          loading="lazy"
        />
      </router-link>
      
      <!-- Wishlist Toggle -->
      <button 
        class="btn-wishlist" 
        @click.stop="$emit('toggle-wishlist', product)" 
        :title="isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist'"
      >
        <Heart :size="18" :class="{ 'heart-filled': isWishlisted }" />
      </button>

      <!-- Quick Add Overlay -->
      <div class="quick-add">
        <span class="quick-add-title">Quick Add</span>
        <div class="size-options">
          <span 
            v-for="size in availableSizes" 
            :key="size" 
            @click.stop="$emit('quick-add', product, size)"
          >
            {{ size }}
          </span>
        </div>
      </div>
    </div>

    <!-- Info Section -->
    <div class="product-info">
      <h4 class="product-title">
        <router-link :to="`/products/${product.uuid || product.id}`">{{ product.name }}</router-link>
      </h4>
      
      <div class="product-price">
        <span class="current-price">₹{{ product.selling_price || product.price }}</span>
        <span v-if="product.mrp && product.mrp > (product.selling_price || product.price)" class="old-price">
          ₹{{ product.mrp }}
        </span>
      </div>

      <div class="product-rating">
        <span class="stars">
          <Star :size="12" class="star-filled inline" /> {{ ratingScore }}
        </span>
        <span class="reviews-count">({{ reviewCount }})</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Heart, Star } from 'lucide-vue-next';

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  isWishlisted: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle-wishlist', 'quick-add']);

const primaryImage = computed(() => {
  if (props.product.primary_image) return props.product.primary_image;
  if (props.product.images && props.product.images.length > 0) {
    const primary = props.product.images.find(img => img.is_primary);
    return primary ? (primary.image_path || primary.url) : (props.product.images[0].image_path || props.product.images[0].url);
  }
  if (props.product.image_url) return props.product.image_url;
  const prodId = props.product.id ? ((props.product.id - 1) % 23) + 1 : 1;
  return `/storage/products/${prodId}/webp/${prodId}.webp`;
});

const discountPercentage = computed(() => {
  if (!props.product.mrp || props.product.mrp <= (props.product.selling_price || props.product.price)) return 0;
  return Math.round(((props.product.mrp - (props.product.selling_price || props.product.price)) / props.product.mrp) * 100);
});

const availableSizes = computed(() => {
  if (props.product.variants && props.product.variants.length > 0) {
    const sizes = props.product.variants.map(v => v.size).filter(Boolean);
    if (sizes.length > 0) return [...new Set(sizes)].slice(0, 4);
  }
  return ['Free Size', 'L', 'XL'];
});

const ratingScore = computed(() => props.product.rating || '4.8');
const reviewCount = computed(() => props.product.review_count || (Math.floor(Math.random() * 30) + 15));
</script>

<style scoped>
.product-card {
  background: #ffffff;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #f1e6df;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
  display: flex;
  flex-direction: column;
}
.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px rgba(74, 14, 46, 0.08);
}
.product-badges {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 2;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.product-tag {
  font-size: 0.7rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 4px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.product-tag.selling-fast {
  background-color: var(--color-primary, #4a0e2e);
  color: #ffffff;
}
.product-tag.new-arrival {
  background-color: var(--color-secondary, #d4af37);
  color: #4a0e2e;
}
.product-tag.trending {
  background-color: #e11d48;
  color: #ffffff;
}
.product-tag.discount {
  background-color: #15803d;
  color: #ffffff;
}
.product-image-container {
  position: relative;
  aspect-ratio: 3/4;
  overflow: hidden;
  background-color: #fcf8f5;
}
.product-img-link {
  display: block;
  width: 100%;
  height: 100%;
}
.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.product-card:hover .product-image {
  transform: scale(1.05);
}
.btn-wishlist {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(4px);
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--color-primary, #4a0e2e);
  transition: all 0.2s ease;
}
.btn-wishlist:hover {
  background: #ffffff;
  transform: scale(1.1);
}
.heart-filled {
  fill: #e11d48;
  color: #e11d48 !important;
}
.quick-add {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(74, 14, 46, 0.92);
  color: #ffffff;
  padding: 8px 12px;
  transform: translateY(100%);
  transition: transform 0.3s ease;
  text-align: center;
}
.product-card:hover .quick-add {
  transform: translateY(0);
}
.quick-add-title {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  display: block;
  margin-bottom: 4px;
  opacity: 0.8;
}
.size-options {
  display: flex;
  justify-content: center;
  gap: 8px;
}
.size-options span {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 8px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.size-options span:hover {
  background: #ffffff;
  color: var(--color-primary, #4a0e2e);
}
.product-info {
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.product-title {
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 500;
  line-height: 1.3;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.product-title a {
  color: #2d2d2d;
  text-decoration: none;
}
.product-price {
  display: flex;
  align-items: center;
  gap: 8px;
}
.current-price {
  font-weight: 700;
  color: var(--color-primary, #4a0e2e);
  font-size: 1rem;
}
.old-price {
  font-size: 0.8rem;
  color: #94a3b8;
  text-decoration: line-through;
}
.product-rating {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: #64748b;
}
.star-filled {
  fill: var(--color-secondary, #d4af37);
  color: var(--color-secondary, #d4af37);
}
</style>
