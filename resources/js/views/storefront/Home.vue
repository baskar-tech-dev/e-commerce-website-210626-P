<template>
  <div class="storefront-home">
    <!-- Skeleton Loading state -->
    <div v-if="loading" style="max-width: 1400px; margin: 0 auto; padding: 20px;">
      <SkeletonLoader type="banner" style="margin-bottom: 30px;" />
      <SkeletonLoader type="grid" :count="4" style="margin-bottom: 30px;" />
    </div>

    <div class="container story-grid">
        <div class="story-image-block">
          <div class="story-img-frame">
            <img :src="'/asset/mother.jpg'" alt="Founder Mrs. Archana Ayyapparaj" class="story-founder-img">
          </div>
        </div>
        
        <div class="story-content-block">
          <span class="story-tag">OUR STORY</span>
          <h2 class="story-title">A Mother's Dream That Became A Fashion Legacy <span class="heart-icon">♡</span></h2>
          <div class="story-body">
            <p><strong>Maya Sree Fashion</strong> was born from a heartfelt vision by <strong>Mrs. Archana Ayyapparaj</strong> - a meaningful future gift for her child.</p>
            <p>What began as a mother's dream has grown into a fashion brand built on love, craftsmanship, quality, and purpose.</p>
            <p>Supported by <strong>India Knit Fashion</strong>, every collection reflects our commitment to comfort, elegance, and timeless style.</p>
          </div>
          <button class="btn-story-read" @click="showStoryModal = true">READ OUR STORY ➔</button>
        </div>
        
        <div class="story-stats-block">
          <div class="stat-card">
            <div class="stat-icon"><CalendarCheck :size="24" /></div>
            <h3>5+</h3>
            <p>Years of Journey</p>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><Users :size="24" /></div>
            <h3>1000+</h3>
            <p>Happy Customers</p>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><Handshake :size="24" /></div>
            <h3>50+</h3>
            <p>Trusted Suppliers</p>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><ShieldCheck :size="24" /></div>
            <h3>100%</h3>
            <p>Quality Checked</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 8. Why Customers Love Mayasree -->
    <section class="section values-section">
      <div class="section-header text-center">
        <h2 class="section-title">WHY CUSTOMERS LOVE MAYASREE</h2>
      </div>
      <div class="container values-grid">
        <div class="value-item">
          <div class="value-icon"><Shirt :size="24" /></div>
          <h4>Premium Fabrics</h4>
          <p>Soft, durable & skin-friendly</p>
        </div>
        <div class="value-item">
          <div class="value-icon"><Sparkles :size="24" /></div>
          <h4>Latest Fashion</h4>
          <p>Trendy styles, always updated</p>
        </div>
        <div class="value-item">
          <div class="value-icon"><CheckCircle :size="24" /></div>
          <h4>100% Quality</h4>
          <p>Checked before every delivery</p>
        </div>
        <div class="value-item">
          <div class="value-icon"><Leaf :size="24" /></div>
          <h4>Eco Friendly</h4>
          <p>Sustainable fashion for a better tomorrow</p>
        </div>
        <div class="value-item">
          <div class="value-icon"><RefreshCw :size="24" /></div>
          <h4>Easy Returns</h4>
          <p>7-Day easy exchange</p>
        </div>
        <div class="value-item">
          <div class="value-icon"><Headset :size="24" /></div>
          <h4>Dedicated Support</h4>
          <p>We're here to help you</p>
        </div>
      </div>
    </section>


    <!-- 10. Best Sellers Showcase -->
    <section class="section best-sellers-section">
      <div class="section-header text-center">
        <h2 class="section-title">BEST SELLERS</h2>
      </div>
      
      <div class="best-sellers-carousel-wrapper">
        <button class="scroll-control left" @click="scrollProducts(-1)" aria-label="Scroll left">
          <ChevronLeft :size="20" />
        </button>
        
        <div class="products-grid" ref="productsGridContainer">
          <div 
            v-for="product in products" 
            :key="product.id" 
            class="product-card"
          >
            <span class="product-tag selling-fast">Selling Fast</span>
            <div class="product-image-container">
              <router-link :to="`/products/${product.uuid}`" class="product-img-link" style="display: block; width: 100%; height: 100%;">
                <img 
                  v-protect-image
                  :src="getPrimaryImage(product)" 
                  :alt="product.name" 
                  class="product-image"
                />
              </router-link>
              <button class="btn-wishlist" @click.stop="toggleWishlist(product)" aria-label="Toggle Wishlist">
                <Heart :size="16" :class="{ 'heart-filled': isInWishlist(product.id) }" />
              </button>
              <div class="quick-add">
                <span class="quick-add-title">Quick Add</span>
                <div class="size-options">
                  <span @click.stop="quickAdd(product, 'Free Size')">Free Size</span>
                  <span @click.stop="quickAdd(product, 'L')">L</span>
                  <span @click.stop="quickAdd(product, 'XL')">XL</span>
                </div>
              </div>
            </div>
            <div class="product-info">
              <h4 class="product-title">
                <router-link :to="`/products/${product.uuid}`">{{ product.name }}</router-link>
              </h4>
              <div class="product-price">
                <span class="current-price">₹{{ product.selling_price }}</span>
                <span v-if="product.mrp > product.selling_price" class="old-price">₹{{ product.mrp }}</span>
              </div>
              <div class="product-rating">
                <span class="stars"><Star :size="12" class="star-filled inline" /> 4.8</span>
                <span class="reviews-count">({{ Math.floor(Math.random() * 40) + 20 }})</span>
                <span class="product-live-view"><Eye :size="12" /> {{ Math.floor(Math.random() * 15) + 10 }} viewing</span>
              </div>
            </div>
          </div>
        </div>
        
        <button class="scroll-control right" @click="scrollProducts(1)" aria-label="Scroll right">
          <ChevronRight :size="20" />
        </button>
      </div>
      
      <div class="text-center mt-3">
        <router-link to="/shop" class="btn-view-all">VIEW ALL BEST SELLERS ➔</router-link>
      </div>
    </section>

    <!-- 11. Customer Social Proof & Video Brand Section -->
    <!-- <section class="section media-reviews-section">
      <div class="media-reviews-grid"> 
        <div class="customer-gallery-block">
          <h3 class="block-title">REAL WOMEN. REAL MOMENTS.</h3>
          <div class="customer-cards-grid">
            <div v-for="(rev, idx) in reviews" :key="idx" class="customer-review-card">
              <span class="avatar-fallback-reviewer">👤</span>
              <div class="review-meta">
                <strong>{{ rev.name }}</strong>
                <span class="review-city">{{ rev.city }}</span>
                <span class="stars">
                  <Star v-for="n in 5" :key="n" :size="12" class="star-filled inline" />
                </span>
                <span class="review-product">{{ rev.product }}</span>
              </div>
            </div>
          </div>
          <div class="text-center mt-3">
            <button class="btn-customer-photos" @click="$router.push('/shop')">SEE MORE CUSTOMER PHOTOS ➔</button>
          </div>
        </div>
 
        <div class="video-brand-block">
          <h3 class="block-title">SEE MAYA SREE FASHION COME TO LIFE</h3>
          <div class="video-interactive-container">
            <div class="video-thumbnail-wrapper" @click="showVideoModal = true">
              <img src="https://images.unsplash.com/photo-1608962714026-af9a76d88f6a?auto=format&fit=crop&w=800&q=80" alt="Video cover" class="video-cover-img">
              <div class="play-overlay">
                <div class="play-circle"><Play :size="24" class="play-icon-svg" /></div>
              </div>
              <div class="video-vertical-tabs">
                <span 
                  v-for="(tab, i) in videoTabs" 
                  :key="i" 
                  class="v-tab"
                  :class="{ active: activeVideoTab === i }"
                  @click.stop="activeVideoTab = i"
                >
                  {{ tab }}
                </span>
              </div>
            </div>
          </div>
          <div class="text-center mt-3">
            <button class="btn-watch-story" @click="showVideoModal = true">WATCH OUR STORY ➔</button>
          </div>
        </div>
      </div>
    </section>  -->

    <!-- 12. Complete the Look, Trending Collections & WhatsApp Assistant -->
    <!-- <section class="section mix-match-section">
      <div class="container mix-match-grid"> 
        <div class="complete-look-block">
          <h3 class="block-title">COMPLETE THE LOOK</h3>
          <div class="look-card">
            <div class="look-product-showcase">
              <div class="look-item-thumb" title="Kurti" @click="$router.push('/shop?category=kurtis')">
                <img src="https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=150&q=80" alt="Kurti">
                <span>Kurti</span>
              </div>
              <span class="plus-sign">+</span>
              <div class="look-item-thumb" title="Dupatta" @click="$router.push('/shop')">
                <img src="https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?auto=format&fit=crop&w=150&q=80" alt="Dupatta">
                <span>Dupatta</span>
              </div>
              <span class="plus-sign">+</span>
              <div class="look-item-thumb" title="Handbag" @click="$router.push('/shop')">
                <img src="https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=150&q=80" alt="Handbag">
                <span>Handbag</span>
              </div>
              <span class="plus-sign">+</span>
              <div class="look-item-thumb" title="Accessories" @click="$router.push('/shop')">
                <img src="https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=150&q=80" alt="Accessories">
                <span>Accessories</span>
              </div>
            </div>
            <div class="look-summary">
              <p>Buy this styled ensemble and get a flat 15% discount!</p>
              <button class="btn-shop-look" @click="$router.push('/shop')">SHOP THE LOOK ➔</button>
            </div>
          </div>
        </div>
 
        <div class="trending-collections-block">
          <h3 class="block-title">TRENDING COLLECTIONS</h3>
          <div class="trending-bubbles-container">
            <div class="bubble-item" @click="$router.push('/shop')">
              <img src="https://images.unsplash.com/photo-1609357605129-26f69add5d6e?auto=format&fit=crop&w=150&q=80" alt="Wedding Wear">
              <span>Wedding Wear</span>
            </div>
            <div class="bubble-item" @click="$router.push('/shop')">
              <img :src="'/asset/festive-collection.jpg'" alt="Festive Collection">
              <span>Festive Collection</span>
            </div>
            <div class="bubble-item" @click="$router.push('/shop')">
              <img src="https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?auto=format&fit=crop&w=150&q=80" alt="Summer Collection">
              <span>Summer Collection</span>
            </div>
            <div class="bubble-item" @click="$router.push('/shop')">
              <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=150&q=80" alt="Office Wear">
              <span>Office Wear</span>
            </div>
            <div class="bubble-item" @click="$router.push('/shop')">
              <img src="https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=150&q=80" alt="Premium Elites">
              <span>Premium Elites</span>
            </div>
          </div>
          <div class="text-center mt-3">
            <button class="btn-explore-collections" @click="$router.push('/shop')">EXPLORE COLLECTIONS ➔</button>
          </div>
        </div>
 
        <div class="whatsapp-assistant-block">
          <div class="expert-chat-card">
            <div class="expert-header">
              <h4>NEED HELP CHOOSING?</h4>
              <p>Chat directly with our Fashion Expert</p>
            </div>
            <div class="expert-body">
              <ul class="chat-features">
                <li><span class="check-icon"><Check :size="14" /></span> Size Selection</li>
                <li><span class="check-icon"><Check :size="14" /></span> Style Suggestions</li>
                <li><span class="check-icon"><Check :size="14" /></span> Product Availability</li>
                <li><span class="check-icon"><Check :size="14" /></span> Order Tracking</li>
                <li><span class="check-icon"><Check :size="14" /></span> Exchange Support</li>
              </ul>
              <a href="https://wa.me/919944285102" target="_blank" class="btn-whatsapp-chat">
                <MessageCircle :size="16" /> CHAT ON WHATSAPP
              </a>
            </div>
            <div class="expert-phone-mock">
              <div class="mock-screen">
                <div class="mock-header"><MessageCircle :size="12" /> Maya Sree Expert</div>
                <div class="mock-chat-bubble received">Hello! How can I help you choose your outfit today?</div>
                <div class="mock-chat-bubble sent">I'm looking for a Festive Kurti in size L.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section> -->

    <!-- 13. Follow Us & Become Insider Banner -->
    <section class="section social-insider-section">
      <!-- Instagram / Reels Feed Grid -->
      <div class="insta-section">
        <h3 class="social-title">FOLLOW OUR JOURNEY</h3>
        <a href="https://www.instagram.com/mayasreefashion/" target="_blank" class="social-handle">@mayasreefashion</a>
        
        <!-- Dynamic Autoplay Reels/Videos if available -->
        <div v-if="activeReels.length > 0" class="reels-container-wrapper">
          <div class="reels-scroll-row">
            <div 
              v-for="reel in activeReels" 
              :key="reel.id" 
              class="reel-card"
              @click="openReelModal(reel)"
            >
              <div class="reel-video-wrapper">
                <!-- Autoplay YouTube Iframe -->
                <iframe 
                  v-if="reel.type === 'youtube' && reel.video_url"
                  :src="getYouTubeEmbedUrl(reel.video_url, true)" 
                  frameborder="0" 
                  allow="autoplay; encrypted-media"
                  allowfullscreen
                  class="reel-youtube-element"
                ></iframe>

                <!-- Direct HTML5 video autoplay muted loop playsinline -->
                <video 
                  v-else-if="reel.type === 'file' && reel.video_url"
                  :src="reel.video_url" 
                  autoplay 
                  loop 
                  muted 
                  playsinline 
                  class="reel-video-element"
                ></video>

                <!-- Instagram Embed Iframe -->
                <iframe 
                  v-else-if="reel.instagram_url"
                  :src="getInstagramEmbedUrl(reel.instagram_url)" 
                  frameborder="0" 
                  scrolling="no"
                  allowtransparency="true"
                  allow="autoplay; encrypted-media"
                  class="reel-insta-element"
                  style="width: 100%; height: 100%; border: none;"
                ></iframe>

                <!-- Fallback Placeholder -->
                <div v-else class="reel-placeholder-card" style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--color-primary) 0%, #1e0513 100%); color: #fff; padding: var(--spacing-md); text-align: center; box-sizing: border-box; gap: 8px;">
                  <Instagram :size="32" style="color: var(--color-secondary);" />
                  <span style="font-size: 0.7rem; font-weight: 700; color: var(--color-secondary); letter-spacing: 0.1em; text-transform: uppercase;">View Post</span>
                </div>
              </div>
              <div class="reel-card-overlay">
                <div class="reel-icon-badge">
                  <Play v-if="reel.type === 'file' && reel.video_url" :size="14" style="fill: currentColor;" />
                  <Youtube v-else-if="reel.type === 'youtube' && reel.video_url" :size="14" />
                  <Instagram v-else :size="14" />
                </div>
                <div class="reel-caption-overlay" v-if="reel.caption">
                  {{ reel.caption }}
                </div>
    <SkeletonLoader v-if="loading" />

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
        :products="products"
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
        :products="products"
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
        :products="products"
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
    const response = await axios.get('/api/storefront/products', { params: { per_page: 8 } });
    if (response.data && response.data.success) {
      products.value = response.data.data;
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
