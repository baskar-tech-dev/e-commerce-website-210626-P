<template>
  <div class="storefront-layout">
    <!-- Brand Splash Screen Overlay -->
    <Transition name="splash-fade">
      <div v-if="showSplash" class="brand-splash-screen">
        <div class="splash-content">
          <div class="splash-logo-container">
            <img :src="'/asset/profile/logo.png'" alt="Maya Sree Fashion Logo" class="splash-logo-img">
          </div>
          <div class="splash-text-container">
            <span class="splash-brand-name">MAYA SREE</span>
            <span class="splash-brand-sub">SOUTH INDIAN FASHION</span>
          </div>
          <div class="splash-divider"></div>
          <p class="splash-tagline">A Mother's Dream • A Fashion Legacy</p>
          <div class="splash-loader-dots">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Top Announcement Bar -->
    <div class="storefront-announcement">
      <span>🚚 Free Shipping Above ₹999</span>
      <span class="storefront-announcement__divider">|</span>
      <span>🔄 Easy Exchange & Returns</span>
    </div>

    <!-- Store Header -->
    <header class="storefront-header">
      <div class="storefront-header__container">
        <!-- Hamburger Menu (Mobile Only) -->
        <button class="mobile-hamburger" @click="mobileDrawerOpen = true" aria-label="Open navigation menu">
          ☰
        </button>

        <!-- Logo -->
        <div class="brand-logo">
          <router-link to="/" class="logo-link" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
            <img :src="'/asset/profile/logo.png'" alt="Maya Sree Fashion Logo" class="header-logo-img">
            <div class="logo-text">
              <span class="brand-name">MAYA SREE</span>
              <span class="brand-sub">SOUTH INDIAN FASHION</span>
            </div>
          </router-link>
        </div>

        <!-- Global Search Bar (Desktop Only) -->
        <div class="search-bar-container desktop-only">
          <form class="search-form" @submit.prevent="executeSearch">
            <input type="text" v-model="searchQuery" placeholder="Search Sarees, Blouses, Kurtis..." class="search-input">
            <button type="submit" class="search-btn"><Search :size="16" /></button>
          </form>
        </div>

        <!-- Header Action Icons -->
        <div class="header-actions">
          <router-link to="/my-account" class="action-item" title="Account">
            <User :size="18" />
            <span>Account</span>
          </router-link>

          <router-link to="/my-account?tab=wishlist" class="action-item" title="Wishlist">
            <div class="cart-icon-wrapper">
              <Heart :size="18" />
              <span class="cart-badge" v-if="wishlistCount">{{ wishlistCount }}</span>
            </div>
            <span>Wishlist</span>
          </router-link>

          <router-link to="/cart" class="action-item" title="Shopping Cart">
            <div class="cart-icon-wrapper">
              <ShoppingBag :size="18" />
              <span class="cart-badge" v-if="cartCount">{{ cartCount }}</span>
            </div>
            <span>Cart</span>
          </router-link>

          <a href="https://wa.me/919944285102" target="_blank" class="action-item" title="WhatsApp Chat">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 18px; height: 18px; fill: #25D366; margin-bottom: 4px;">
              <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
            </svg>
            <span>WhatsApp</span>
          </a>
        </div>
      </div>

      <!-- Sticky Mobile Search Bar -->
      <div class="mobile-search-bar-container mobile-only">
        <form class="search-form mobile-search-form" @submit.prevent="executeSearch">
          <span class="search-form-icon"><Search :size="16" /></span>
          <input type="text" v-model="searchQuery" placeholder="Search Sarees, Blouses, Kurtis..." class="search-input">
        </form>
      </div>

      <!-- Navigation Menu (Desktop Subheader) -->
      <nav class="desktop-navigation desktop-only">
        <ul class="nav-links">
          <template v-for="cat in categories" :key="cat.id">
            <li v-if="cat.children && cat.children.length > 0" class="has-dropdown">
              <router-link :to="`/shop?category_id=${cat.id}`">
                {{ cat.name }} <ChevronDown :size="14" />
              </router-link>
              <ul class="dropdown-menu">
                <li v-for="child in cat.children" :key="child.id">
                  <router-link :to="`/shop?category_id=${child.id}`">{{ child.name }}</router-link>
                </li>
              </ul>
            </li>
            <li v-else>
              <router-link :to="`/shop?category_id=${cat.id}`">{{ cat.name }}</router-link>
            </li> 
          </template>
          <li><router-link to="/shop?category=trending">Trending Now</router-link></li>
          <li><router-link to="/shop?category=wholesale">Manufacturer Wholesale</router-link></li>
          <li><router-link to="/about-us">About Us</router-link></li>
        </ul>
      </nav>
    </header>

    <!-- Mobile Drawer Overlay -->
    <div v-if="mobileDrawerOpen" class="storefront-drawer-overlay" @click="mobileDrawerOpen = false"></div>
    
    <!-- Mobile Navigation Drawer -->
    <div class="storefront-drawer" :class="{ active: mobileDrawerOpen }">
      <div class="drawer-header">
        <div class="logo-text">
          <span class="brand-name" style="font-size: 1.15rem;">MAYA SREE</span>
          <span class="brand-sub" style="font-size: 0.55rem; letter-spacing: 2px;">SOUTH INDIAN FASHION</span>
        </div>
        <button class="drawer-close" @click="mobileDrawerOpen = false">✕</button>
      </div>
      <nav class="drawer-nav">
        <router-link to="/" class="drawer-nav-link" active-class="active" exact @click="mobileDrawerOpen = false">🏠 Home</router-link>
        <router-link to="/shop" class="drawer-nav-link" active-class="active" @click="mobileDrawerOpen = false">🛍️ Shop</router-link>
        <router-link to="/my-account" class="drawer-nav-link" active-class="active" @click="mobileDrawerOpen = false">👤 My Account</router-link>
        <router-link to="/admin" class="drawer-nav-link admin-btn" @click="mobileDrawerOpen = false">⚙️ Admin Dashboard</router-link>
      </nav>
      <div class="drawer-footer">
        <p>📞 Helpline: 99442 85102</p>
        <p>📍 Tirupur, Tamil Nadu</p>
      </div>
    </div>

    <!-- Mobile Fixed Bottom Navigation Bar -->
    <div class="storefront-bottom-nav">
      <router-link to="/" class="bottom-nav-item" active-class="active" exact>
        <Home :size="20" class="nav-icon-svg" />
        <span class="nav-text">Home</span>
      </router-link>
      <router-link to="/shop" class="bottom-nav-item" active-class="active">
        <Store :size="20" class="nav-icon-svg" />
        <span class="nav-text">Shop</span>
      </router-link>
      <router-link to="/my-account?tab=wishlist" class="bottom-nav-item" active-class="active">
        <div class="cart-icon-wrapper" style="display: flex; flex-direction: column; align-items: center;">
          <Heart :size="20" class="nav-icon-svg" />
          <span class="badge" v-if="wishlistCount">{{ wishlistCount }}</span>
        </div>
        <span class="nav-text">Wishlist</span>
      </router-link>
      <router-link to="/cart" class="bottom-nav-item" active-class="active">
        <div class="cart-icon-wrapper" style="display: flex; flex-direction: column; align-items: center;">
          <ShoppingBag :size="20" class="nav-icon-svg" />
          <span class="badge" v-if="cartCount">{{ cartCount }}</span>
        </div>
        <span class="nav-text">Cart</span>
      </router-link>
      <router-link to="/my-account" class="bottom-nav-item" active-class="active">
        <User :size="20" class="nav-icon-svg" />
        <span class="nav-text">Profile</span>
      </router-link>
    </div>

    <!-- Global Floating WhatsApp Widget -->
    <a href="https://wa.me/919944285102" target="_blank" class="whatsapp-floating-bubble-global" title="Chat on WhatsApp">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="width: 24px; height: 24px; fill: #ffffff; display: block; margin: auto;">
        <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
      </svg>
    </a>

    <!-- Main View Content -->
    <main class="storefront-main">
      <div class="storefront-container">
        <router-view @update-cart-count="getCartCount" @update-wishlist-count="getWishlistCount"></router-view>
      </div>
    </main>

    <!-- Multi-Column Footer -->
    <footer class="storefront-footer">
      <div class="storefront-footer__container">
        <!-- Brand Intro -->
        <div class="storefront-footer__col">
          <div class="storefront-logo" style="margin-bottom: var(--spacing-sm);">
            <div class="logo-text">
              <span class="brand-name" style="color: #fff;">MAYA SREE</span>
              <span class="brand-sub" style="color: var(--color-secondary);">SOUTH INDIAN FASHION</span>
            </div>
          </div>
          <p style="color: #c9b1bd; font-size: 0.85rem; line-height: 1.6;">
            A Mother's Dream That Became A Fashion Legacy. Discover premium Indian ethnic wear, sarees, kurtis, and stretchable ready-made blouses designed for ultimate elegance.
          </p>
        </div>

        <!-- Shop Categories Links -->
        <div class="storefront-footer__col">
          <h4 class="storefront-footer__title">Collections</h4>
          <ul class="storefront-footer__list">
            <li><router-link to="/shop?category=womens">Womens wear</router-link></li>
            <li><router-link to="/shop?category=kids">Kids wear</router-link></li>
            <li><router-link to="/shop?category=mens">Mens wear</router-link></li>
          </ul>
        </div>

        <!-- Help Info -->
        <div class="storefront-footer__col">
          <h4 class="storefront-footer__title">Customer Support</h4>
          <ul class="storefront-footer__list">
            <li><router-link to="/my-account?tab=orders">Track Orders</router-link></li>
            <li><router-link to="/about-us">About Us</router-link></li>
            <li><router-link to="/contact-us">Contact Us</router-link></li>
            <li><router-link to="/faq">FAQs</router-link></li>
          </ul>
        </div>

        <!-- Newsletter Subscription Form -->
        <div class="storefront-footer__col">
          <h4 class="storefront-footer__title">Newsletter Join</h4>
          <p style="color: #c9b1bd; font-size: 0.82rem; margin-bottom: var(--spacing-sm);">Subscribe to get early access alerts on collection launches.</p>
          <form @submit.prevent="subscribeNewsletter" style="display: flex; gap: var(--spacing-xs);">
            <input type="email" placeholder="Enter email..." class="form-input" style="flex: 1; padding: 0.35rem var(--spacing-sm); font-size: 0.85rem;" required />
            <button type="submit" class="btn btn--primary" style="padding: 0 1rem; font-size: 0.85rem;">Join</button>
          </form>
        </div>
      </div>
      <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; padding: var(--spacing-md) 40px; border-top: 1px solid #4a1936; margin-top: var(--spacing-lg); flex-wrap: wrap; gap: var(--spacing-md);">
        <div style="font-size: 0.8rem; color: #a18a96;">
          © 2026 MAYA SREE FASHION South India. All Rights Reserved.
        </div>
        <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
          <router-link to="/privacy-policy" style="color: #a18a96; text-decoration: none; font-size: 0.78rem; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#a18a96'">Privacy Policy</router-link>
          <router-link to="/terms-conditions" style="color: #a18a96; text-decoration: none; font-size: 0.78rem; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#a18a96'">Terms & Conditions</router-link>
          <router-link to="/refund-policy" style="color: #a18a96; text-decoration: none; font-size: 0.78rem; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#a18a96'">Refund Policy</router-link>
          <router-link to="/shipping-policy" style="color: #a18a96; text-decoration: none; font-size: 0.78rem; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#a18a96'">Shipping Policy</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { Search, User, Heart, ShoppingBag, MessageCircle, ChevronDown, Home, Store } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();

const cartCount = ref(0);
const wishlistCount = ref(0);
const mobileDrawerOpen = ref(false);
const searchQuery = ref('');
const categories = ref([]);

const showSplash = ref(false);
const isInitialLoad = ref(true);

const triggerSplash = (duration) => {
  showSplash.value = true;
  setTimeout(() => {
    showSplash.value = false;
  }, duration);
};

// Watch route changes
watch(
  () => route.path,
  (newPath) => {
    // When opening a product detail view, trigger the splash screen as transition loader
    if (newPath && newPath.includes('/products/')) {
      triggerSplash(1000);
    }
  }
);

const getCartCount = () => {
  try {
    const items = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
    cartCount.value = items.reduce((acc, item) => acc + item.quantity, 0);
  } catch (err) {
    cartCount.value = 0;
  }
};

const getWishlistCount = () => {
  try {
    const items = JSON.parse(localStorage.getItem('vibe_wishlist_items') || '[]');
    wishlistCount.value = items.length;
  } catch (err) {
    wishlistCount.value = 0;
  }
};

const executeSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/shop', query: { search: searchQuery.value } });
  }
};

const alertAboutUs = () => {
  alert('Maya Sree South Indian Fashion - Premium traditional clothing since 2021. Designed and crafted in Tirupur, Tamil Nadu.');
};

const subscribeNewsletter = () => {
  alert('✓ Thank you for subscribing! Check your email inbox for discount codes.');
};

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/storefront/categories');
    if (response.data.success) {
      categories.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to fetch categories:', err);
  }
};

const handleContextMenu = (e) => {
  e.preventDefault();
};

onMounted(() => {
  if (isInitialLoad.value) {
    triggerSplash(1500); // 1.5s splash on initial load
    isInitialLoad.value = false;
  }
  getCartCount();
  getWishlistCount();
  fetchCategories();
  window.addEventListener('contextmenu', handleContextMenu);
});

onUnmounted(() => {
  window.removeEventListener('contextmenu', handleContextMenu);
});
</script>

<style>
/* Storefront Base Layout System Rules */
.storefront-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--cream-bg);
  color: var(--color-text-primary); 
}
.storefront-announcement {
  background: var(--color-primary);
  color: var(--blush-bg);
  font-size: 0.8rem;
  font-weight: 500;
  text-align: center;
  padding: 0.4rem 1rem;
  display: flex;
  justify-content: center;
  gap: 0.8rem;
  letter-spacing: 0.03em;
}
.storefront-announcement__divider {
  opacity: 0.5;
}
.storefront-header {
  background: rgba(255, 252, 247, 0.9);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--color-border);
  position: sticky;
  top: 0;
  z-index: 1000;
  padding: var(--spacing-sm) 0;
}
.storefront-header__container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
/* Brand Logo */
.brand-logo .logo-link {
    display: flex;
    align-items: center;
    gap: 12px;
}
.header-logo-img {
    height: 48px;
    width: 48px;
    object-fit: contain;
    border-radius: 50%;
    border: 1.5px solid var(--color-secondary);
    background-color: #ffffff;
}
.logo-text {
    display: flex;
    flex-direction: column;
}
.brand-name {
    font-family: var(--font-family-heading);
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary);
    letter-spacing: 2px;
    line-height: 1.1;
}
.brand-sub {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--color-secondary);
    letter-spacing: 4px;
}

/* Search Bar */
.search-bar-container {
    flex: 1;
    max-width: 600px;
    margin: 0 var(--spacing-md);
}
.search-form {
    display: flex;
    border: 1px solid var(--color-border);
    border-radius: 30px;
    overflow: hidden;
    width:100%;
    background-color: var(--blush-bg);
}
.search-input {
    flex: 1;
    border: none;
    padding: 10px 20px;
    font-size: 0.9rem;
    background: transparent;
    outline: none;
    font-family: inherit;
    color: var(--charcoal);
}
.search-btn {
    padding: 10px 20px;
    color: var(--color-primary);
    font-size: 1rem;
    border-radius: 30px;
}
.search-btn:hover {
    color: var(--color-primary-hover);
}

/* Header Actions */
.header-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}
.action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 0.72rem;
    color: var(--color-text-secondary);
    font-weight: 600;
    text-decoration: none;
}
.action-item i {
    font-size: 1.3rem;
    margin-bottom: 4px;
    color: var(--color-primary);
}
.action-item:hover i {
    color: var(--color-primary-hover);
}
.cart-icon-wrapper {
    position: relative;
    display: inline-block;
}
.cart-badge {
    position: absolute;
    top: -5px;
    right: -10px;
    background-color: var(--color-primary);
    color: #ffffff;
    font-size: 0.65rem;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    border: 1px solid #ffffff;
}
.whatsapp-btn-header {
    background-color: #25d366;
    color: #ffffff;
    padding: 10px 18px;
    border-radius: 30px;
    font-size: 0.82rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 4px 10px rgba(37, 211, 102, 0.2);
    transition: transform 0.2s, background-color 0.2s;
}
.whatsapp-btn-header:hover {
    background-color: #20ba59;
    transform: translateY(-2px);
}

/* Desktop sub-navigation */
.desktop-navigation {
    border-top: 1px solid var(--color-border);
    border-bottom: 1px solid var(--color-border);
    background-color: #ffffff;
    width: 100%;
}
.nav-links {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 12px 40px;
    list-style: none;
}
.nav-links > li {
    position: relative;
    padding: 0 18px;
}
.nav-links > li > a {
    font-weight: 500;
    font-size: 0.9rem;
    color: var(--color-text-primary);
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
}
.nav-links > li > a:hover {
    color: var(--color-primary);
}
.dropdown-menu {
    position: absolute;
    top: calc(100% + 12px);
    left: 50%;
    transform: translateX(-50%) translateY(10px);
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--color-border);
    padding: 16px;
    min-width: 380px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 100;
    list-style: none;
}
.dropdown-menu::before {
    content: '';
    position: absolute;
    top: -6px;
    left: 50%;
    transform: translateX(-50%) rotate(45deg);
    width: 12px;
    height: 12px;
    background-color: #ffffff;
    border-top: 1px solid var(--color-border);
    border-left: 1px solid var(--color-border);
}
.has-dropdown:hover .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}
.dropdown-menu li a {
    display: block;
    padding: 8px 20px;
    font-size: 0.85rem;
    color: var(--color-text-primary);
    text-decoration: none;
    transition: background-color 0.2s, padding-left 0.2s;
    text-align: left;
}
.dropdown-menu li a:hover {
    background-color: var(--blush-bg);
    color: var(--color-primary);
    padding-left: 25px;
}
.storefront-action-btn .badge {
  position: absolute;
  top: -8px;
  right: -8px;
  font-size: 0.6rem;
  padding: 2px 5px;
  border-radius: 50%;
  color: #fff;
  font-weight: bold;
}
.storefront-main {
    flex: 1;
    padding: var(--spacing-lg) 40px;
}
/* .storefront-container-fluid {
    max-width: 1400px;
    margin: 0 auto;
} */
.storefront-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
}
.storefront-footer {
  background: #2d051c; /* Deep Maroon / Plum */
  border-top: 1px solid #4a1936;
  padding: var(--spacing-xl) 0 var(--spacing-md);
  color: var(--blush-bg);
}
.storefront-footer__container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 2fr;
  gap: var(--spacing-xl);
}
.storefront-footer__col {
  display: flex;
  flex-direction: column;
}
.storefront-footer__title {
  color: #fff;
  font-size: 0.95rem;
  font-weight: 700;
  margin-bottom: var(--spacing-md);
  letter-spacing: 0.05em;
  text-transform: uppercase;
}
.storefront-footer__list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}
.storefront-footer__list a {
  color: #c9b1bd;
  text-decoration: none;
  font-size: 0.85rem;
  transition: color 0.2s;
}
.storefront-footer__list a:hover {
  color: #fff;
}

/* --- Mobile Responsive Additions --- */
.mobile-only {
  display: none;
}
.mobile-hamburger {
  display: none;
  font-size: 1.6rem;
  color: var(--color-primary);
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
}

.desktop-only {
  display: inline-flex;
}

/* Mobile Bottom Navigation Bar */
.storefront-bottom-nav {
  display: none;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 64px;
  background: rgba(255, 252, 247, 0.95);
  backdrop-filter: blur(10px);
  border-top: 1px solid var(--color-border);
  box-shadow: 0 -4px 20px rgba(74, 14, 46, 0.05);
  z-index: 999;
  justify-content: space-around;
  align-items: center;
  padding: 0 8px;
}

.bottom-nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  color: var(--color-text-secondary);
  font-size: 0.7rem;
  font-weight: 500;
  position: relative;
  flex: 1;
  height: 100%;
  transition: color var(--transition-fast), transform var(--transition-fast);
}

.bottom-nav-item.active {
  color: var(--color-primary);
  font-weight: 600;
}

.nav-icon-svg {
  margin-bottom: 3px;
  transition: transform 0.2s ease;
}

.bottom-nav-item.active .nav-icon-svg {
  transform: translateY(-2px);
}

.bottom-nav-item .badge {
  position: absolute;
  top: -4px;
  right: -8px;
  font-size: 0.65rem;
  background: var(--color-primary);
  color: #fff;
  font-weight: 700;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #fff;
}

/* Global Floating WhatsApp Bubble */
.whatsapp-floating-bubble-global {
  position: fixed;
  bottom: 76px; /* High enough to clear the 64px bottom nav */
  right: 16px;
  background-color: #25d366;
  color: #ffffff;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 15px rgba(37, 211, 102, 0.2);
  z-index: 998;
  transition: transform 0.2s, background-color 0.2s;
}

.whatsapp-floating-bubble-global:hover {
  transform: scale(1.1) rotate(10deg);
  background-color: #20ba59;
}

/* Mobile Drawer Styles */
.storefront-drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1001;
  backdrop-filter: blur(2px);
}

.storefront-drawer {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 280px;
  background: #ffffff;
  z-index: 1002;
  box-shadow: 4px 0 20px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  transform: translateX(-100%);
  transition: transform 0.3s ease;
}

.storefront-drawer.active {
  transform: translateX(0);
}

.drawer-header {
  padding: var(--spacing-md);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--blush-bg);
}

.drawer-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: var(--color-primary);
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  border: 1px solid var(--color-border);
}

.drawer-nav {
  display: flex;
  flex-direction: column;
  padding: var(--spacing-md) 0;
  flex: 1;
}

.drawer-nav-link {
  padding: var(--spacing-sm) var(--spacing-md);
  color: var(--color-text-primary);
  text-decoration: none;
  font-size: 1rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  transition: background-color 0.2s, color 0.2s;
}

.drawer-nav-link:hover, .drawer-nav-link.active {
  background: var(--blush-bg);
  color: var(--color-primary);
  font-weight: 600;
}

.drawer-nav-link.admin-btn {
  margin-top: auto;
  border-top: 1px solid var(--color-border);
  padding-top: var(--spacing-md);
  opacity: 0.9;
}

.drawer-footer {
  padding: var(--spacing-md);
  border-top: 1px solid var(--color-border);
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  background: var(--blush-bg);
}

/* Tablet & Mobile Media Queries */
@media (max-width: 767px) {
  .storefront-announcement {
    flex-direction: column;
    gap: 4px;
    padding: 6px 12px;
  }
  .storefront-announcement__divider {
    display: none;
  }
  .mobile-hamburger {
    display: block;
  }
  .storefront-nav {
    display: none;
  }
  .desktop-only {
    display: none;
  }
  .mobile-only {
    display: block;
  }
  .storefront-bottom-nav {
    display: flex;
  }
  .storefront-layout {
    padding-bottom: 64px; /* Space for bottom nav */
  }
  .nav-links,
  .storefront-main,
  .storefront-container,
  .storefront-footer__container {
    padding-left: 16px;
    padding-right: 16px;
  }
  .storefront-header__container {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-left: 16px;
    padding-right: 16px;
    height: 56px;
  }
  .header-actions {
    gap: 16px;
  }
  .action-item span {
    display: none;
  }
  .brand-logo {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
  }
  .brand-logo .brand-name {
    font-size: 1.1rem;
  }
  .brand-logo .brand-sub {
    font-size: 0.55rem;
    letter-spacing: 2px;
  }
  .header-logo-img {
    height: 36px;
    width: 36px;
  }
  .storefront-footer__container {
    grid-template-columns: 1fr;
    gap: var(--spacing-lg);
  }
  .storefront-bottom-nav {
    left: 0;
    right: 0;
  }

  /* Sticky search styles inside media query */
  .mobile-search-bar-container {
    padding: 0 16px var(--spacing-sm) 16px;
    width: 100%;
  }
  .mobile-search-form {
    display: flex;
    align-items: center;
    background: var(--blush-bg);
    border: 1px solid var(--color-border);
    border-radius: 30px;
    padding: 0 var(--spacing-sm);
    width: 100%;
    height: 40px;
  }
  .search-form-icon {
    color: var(--color-text-secondary);
    display: flex;
    align-items: center;
    padding-left: 8px;
  }
  .search-addon-icons {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-right: 8px;
    color: var(--color-text-secondary);
  }
}

/* Premium Brand Splash Screen */
.brand-splash-screen {
  position: fixed;
  inset: 0;
  background: var(--color-primary); /* Deep Maroon */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
}

.splash-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2rem;
  max-width: 400px;
  animation: splashScaleIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.splash-logo-container {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: #ffffff;
  padding: var(--spacing-xs);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 3px solid var(--color-secondary); /* Zari Gold border */
  margin-bottom: var(--spacing-md);
  position: relative;
  animation: logoPulse 2s infinite ease-in-out;
}

.splash-logo-img {
  width: 90%;
  height: 90%;
  object-fit: contain;
}

.splash-text-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.splash-brand-name {
  font-family: var(--font-family-heading);
  font-size: 2.5rem;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: 4px;
  line-height: 1.1;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.splash-brand-sub {
  font-family: var(--font-family-base);
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-secondary); /* Zari Gold */
  letter-spacing: 6px;
  text-transform: uppercase;
}

.splash-divider {
  width: 60px;
  height: 1.5px;
  background: linear-gradient(90deg, transparent, var(--color-secondary), transparent);
  margin: var(--spacing-md) 0;
}

.splash-tagline {
  font-family: var(--font-family-base);
  font-size: 0.85rem;
  color: rgba(255, 252, 247, 0.8);
  font-style: italic;
  letter-spacing: 1px;
  margin: 0 0 var(--spacing-md) 0;
}

.splash-loader-dots {
  display: flex;
  gap: 8px;
  margin-top: var(--spacing-xs);
}

.splash-loader-dots span {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: var(--color-secondary);
  animation: splashDotPulse 1.2s infinite ease-in-out both;
}

.splash-loader-dots span:nth-child(1) {
  animation-delay: -0.32s;
}

.splash-loader-dots span:nth-child(2) {
  animation-delay: -0.16s;
}

/* Animations */
@keyframes splashScaleIn {
  0% {
    opacity: 0;
    transform: scale(0.92);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes logoPulse {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  }
  50% {
    transform: scale(1.03);
    box-shadow: 0 15px 40px rgba(212, 175, 55, 0.3); /* Zari Gold glow */
  }
}

@keyframes splashDotPulse {
  0%, 80%, 100% { 
    transform: scale(0.6);
    opacity: 0.3;
  } 
  40% { 
    transform: scale(1);
    opacity: 1;
  }
}

/* Vue Transition styling */
.splash-fade-enter-active,
.splash-fade-leave-active {
  transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.splash-fade-enter-from,
.splash-fade-leave-to {
  opacity: 0;
}
</style>
