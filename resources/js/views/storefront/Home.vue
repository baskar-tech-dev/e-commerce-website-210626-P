<template>
  <div class="storefront-home">
    <!-- Skeleton Loading state -->
    <div v-if="loading" style="max-width: 1400px; margin: 0 auto; padding: 20px;">
      <SkeletonLoader type="banner" style="margin-bottom: 30px;" />
      <SkeletonLoader type="grid" :count="4" style="margin-bottom: 30px;" />
    </div>

    <template v-else-if="sortedSections && sortedSections.length">
      <template v-for="section in sortedSections" :key="section.id">
        <!-- Hero Banner Section -->
        <HeroBanner 
          v-if="section.id === 'hero_carousel' && isSectionEnabled(section)" 
        />

        <!-- Categories Section -->
        <CategoryGrid 
          v-if="section.id === 'categories' && isSectionEnabled(section)" 
        />

        <!-- Featured Collections Section -->
        <FeaturedCollection 
          v-if="section.id === 'featured_collections' && isSectionEnabled(section)" 
          :products="featuredProducts.length > 0 ? featuredProducts : products"
          :wishlist-ids="wishlistIds"
          @toggle-wishlist="toggleWishlist"
          @quick-add="quickAdd"
        />

        <!-- Shop By Occasion Section -->
        <ShopByOccasion 
          v-if="section.id === 'shop_by_occasion' && isSectionEnabled(section)" 
        />

        <!-- New Arrivals Section -->
        <NewArrivals 
          v-if="section.id === 'new_arrivals' && isSectionEnabled(section)" 
          :products="newArrivalsProducts"
          :wishlist-ids="wishlistIds"
          @toggle-wishlist="toggleWishlist"
          @quick-add="quickAdd"
        />

        <!-- Trending Collections Section -->
        <TrendingCollections 
          v-if="section.id === 'trending_collections' && isSectionEnabled(section)" 
        />

        <!-- Best Sellers Section -->
        <BestSellers 
          v-if="section.id === 'best_sellers' && isSectionEnabled(section)" 
          :products="bestSellerProducts"
          :wishlist-ids="wishlistIds"
          @toggle-wishlist="toggleWishlist"
          @quick-add="quickAdd"
        />

        <!-- Premium Collection Section -->
        <PremiumCollection 
          v-if="section.id === 'premium_collection' && isSectionEnabled(section)" 
          :products="products"
          :wishlist-ids="wishlistIds"
          @toggle-wishlist="toggleWishlist"
          @quick-add="quickAdd"
        />

        <!-- Offers Section -->
        <OffersBanner 
          v-if="section.id === 'festival_banner' && isSectionEnabled(section)" 
        />

        <!-- Customer Reviews Section -->
        <CustomerReviews 
          v-if="section.id === 'customer_reviews' && isSectionEnabled(section)" 
        />

        <!-- Instagram Gallery Section -->
        <InstagramGallery 
          v-if="section.id === 'instagram_gallery' && isSectionEnabled(section)" 
          :reels="activeReels"
          @select-reel="openReelModal"
        />

        <!-- Newsletter Section -->
        <NewsletterSection 
          v-if="section.id === 'newsletter' && isSectionEnabled(section)" 
        />
      </template>
    </template>

    <!-- Instagram Video/Reel Modal -->
    <div v-if="showReelModal && selectedReel" class="modal-overlay" @click="closeReelModal">
      <div class="modal-content reel-modal-content" @click.stop>
        <button class="modal-close-btn" @click="closeReelModal">✕</button>
        <div class="reel-modal-body">
          <iframe 
            v-if="selectedReel.video_url" 
            :src="getEmbedUrl(selectedReel.video_url)" 
            class="reel-iframe" 
            allow="autoplay; encrypted-media" 
            allowfullscreen
          ></iframe>
          <div class="reel-details" v-if="selectedReel.title">
            <h3>{{ selectedReel.title }}</h3>
            <p v-if="selectedReel.description">{{ selectedReel.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useCategoryStore } from '../../stores/category';
import { useCmsStore } from '../../stores/cms';

import HeroBanner from '../../components/homepage/HeroBanner.vue';
import CategoryGrid from '../../components/homepage/CategoryGrid.vue';
import FeaturedCollection from '../../components/homepage/FeaturedCollection.vue';
import ShopByOccasion from '../../components/homepage/ShopByOccasion.vue';
import NewArrivals from '../../components/homepage/NewArrivals.vue';
import TrendingCollections from '../../components/homepage/TrendingCollections.vue';
import BestSellers from '../../components/homepage/BestSellers.vue';
import PremiumCollection from '../../components/homepage/PremiumCollection.vue';
import OffersBanner from '../../components/homepage/OffersBanner.vue';
import CustomerReviews from '../../components/homepage/CustomerReviews.vue';
import InstagramGallery from '../../components/homepage/InstagramGallery.vue';
import NewsletterSection from '../../components/homepage/NewsletterSection.vue';
import SkeletonLoader from '../../components/common/SkeletonLoader.vue';

const emit = defineEmits(['update-wishlist-count', 'update-cart-count']);

const categoryStore = useCategoryStore();
const cmsStore = useCmsStore();

const loading = ref(true);
const products = ref([]);
const featuredProducts = ref([]);
const newArrivalsProducts = ref([]);
const bestSellerProducts = ref([]);
const activeReels = ref([]);
const wishlistIds = ref([]);
const showReelModal = ref(false);
const selectedReel = ref(null);

const defaultSections = [
  { id: 'hero_carousel', name: 'Hero Carousel', enabled: true, order: 1 },
  { id: 'categories', name: 'Categories', enabled: true, order: 2 },
  { id: 'featured_collections', name: 'Featured Collections', enabled: true, order: 3 },
  { id: 'shop_by_occasion', name: 'Shop By Occasion', enabled: true, order: 4 },
  { id: 'new_arrivals', name: 'New Arrivals', enabled: true, order: 5 },
  { id: 'trending_collections', name: 'Trending Collections', enabled: true, order: 6 },
  { id: 'best_sellers', name: 'Best Sellers', enabled: true, order: 7 },
  { id: 'premium_collection', name: 'Premium Collection', enabled: true, order: 8 },
  { id: 'festival_banner', name: 'Offers Banner', enabled: true, order: 9 },
  { id: 'customer_reviews', name: 'Customer Reviews', enabled: true, order: 10 },
  { id: 'instagram_gallery', name: 'Instagram Gallery', enabled: true, order: 11 },
  { id: 'newsletter', name: 'Newsletter', enabled: true, order: 12 }
];

const sortedSections = computed(() => {
  const sections = cmsStore.homepageSections && cmsStore.homepageSections.length > 0
    ? cmsStore.homepageSections
    : defaultSections;
  return [...sections].sort((a, b) => (a.order || 0) - (b.order || 0));
});

const isSectionEnabled = (sec) => {
  return sec.enabled !== false;
};

const fetchProducts = async () => {
  try {
    const [allRes, featRes, newRes, bestRes] = await Promise.all([
      axios.get('/api/storefront/products', { params: { per_page: 16 } }),
      axios.get('/api/storefront/products', { params: { featured: 1, per_page: 8 } }),
      axios.get('/api/storefront/products', { params: { sort_by: 'newest', per_page: 8 } }),
      axios.get('/api/storefront/products', { params: { sort_by: 'popular', per_page: 8 } })
    ]);

    if (allRes.data && allRes.data.success) {
      products.value = allRes.data.data;
    }
    if (featRes.data && featRes.data.success && featRes.data.data.length > 0) {
      featuredProducts.value = featRes.data.data;
    } else {
      featuredProducts.value = products.value.slice(0, 4);
    }
    if (newRes.data && newRes.data.success && newRes.data.data.length > 0) {
      newArrivalsProducts.value = newRes.data.data;
    } else {
      newArrivalsProducts.value = products.value.slice(0, 4);
    }
    if (bestRes.data && bestRes.data.success && bestRes.data.data.length > 0) {
      bestSellerProducts.value = bestRes.data.data;
    } else {
      bestSellerProducts.value = products.value.slice(4, 8);
    }
  } catch (err) {
    console.error('Failed to load products:', err);
  }
};

const fetchActiveReels = async () => {
  try {
    const response = await axios.get('/api/storefront/instagram-reels');
    if (response.data && response.data.success) {
      activeReels.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load active reels:', err);
  }
};

const loadWishlist = () => {
  try {
    const items = JSON.parse(localStorage.getItem('vibe_wishlist_items') || '[]');
    wishlistIds.value = items.map(item => item.id);
  } catch (err) {
    wishlistIds.value = [];
  }
};

const toggleWishlist = (product) => {
  try {
    let items = JSON.parse(localStorage.getItem('vibe_wishlist_items') || '[]');
    const index = items.findIndex(item => item.id === product.id);
    if (index > -1) {
      items.splice(index, 1);
    } else {
      items.push({ id: product.id, name: product.name, price: product.selling_price || product.price });
    }
    localStorage.setItem('vibe_wishlist_items', JSON.stringify(items));
    wishlistIds.value = items.map(item => item.id);
    emit('update-wishlist-count');
  } catch (err) {
    console.error('Failed to toggle wishlist:', err);
  }
};

const quickAdd = (product, size) => {
  try {
    let items = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
    const existingIndex = items.findIndex(item => item.id === product.id && item.size === size);
    if (existingIndex > -1) {
      items[existingIndex].quantity += 1;
    } else {
      items.push({
        id: product.id,
        name: product.name,
        price: product.selling_price || product.price,
        size: size,
        quantity: 1,
        image: product.image_url || 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=400&q=80'
      });
    }
    localStorage.setItem('vibe_cart_items', JSON.stringify(items));
    emit('update-cart-count');
    alert(`✓ Added ${product.name} (${size}) to cart!`);
  } catch (err) {
    console.error('Failed to quick add to cart:', err);
  }
};

const openReelModal = (reel) => {
  selectedReel.value = reel;
  showReelModal.value = true;
};

const closeReelModal = () => {
  showReelModal.value = false;
  selectedReel.value = null;
};

const getEmbedUrl = (url) => {
  if (!url) return '';
  if (url.includes('youtube.com') || url.includes('youtu.be')) {
    let id = url.includes('shorts/') ? url.split('shorts/')[1] : url.split('v=')[1];
    return `https://www.youtube.com/embed/${id}?autoplay=1`;
  }
  return url;
};

onMounted(async () => {
  try {
    await Promise.all([
      cmsStore.fetchCmsSettings(),
      categoryStore.fetchPublicCategories(),
      fetchProducts(),
      fetchActiveReels()
    ]);
  } catch (err) {
    console.error('Home initialization failed:', err);
  } finally {
    loading.value = false;
  }
  loadWishlist();
});
</script>

<style scoped>
.storefront-home {
  font-family: 'Poppins', sans-serif;
  color: var(--color-text-primary, #2d2d2d);
  background-color: #ffffff;
}

/* Modal Styling */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}
.modal-content {
  background: #ffffff;
  border-radius: 16px;
  max-width: 500px;
  width: 100%;
  position: relative;
  overflow: hidden;
}
.modal-close-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  background: rgba(0,0,0,0.5);
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  cursor: pointer;
  z-index: 10;
}
.reel-iframe {
  width: 100%;
  aspect-ratio: 9/16;
  border: none;
}
.reel-details {
  padding: 16px;
}
.reel-details h3 {
  font-size: 1rem;
  margin-bottom: 4px;
}
.reel-details p {
  font-size: 0.85rem;
  color: #64748b;
}
</style>
