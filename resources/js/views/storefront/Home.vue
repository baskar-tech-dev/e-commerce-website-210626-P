<template>
  <div class="storefront-home">
    <!-- 4. Hero Carousel Section -->
    <section class="hero-section">
      <div class="hero-carousel">
        <!-- Slides -->
        <div 
          v-for="(slide, index) in slides" 
          :key="index"
          class="hero-slide"
          :class="{ active: currentSlide === index }"
        >
          <div class="hero-grid">
            <div class="hero-images-wrapper">
              <div class="hero-main-card">
                <img :src="slide.leftImage" alt="Maya Sree Fashion Main Banner" class="hero-img">
              </div>
              <div class="hero-sub-card">
                <img :src="slide.rightImage" alt="Maya Sree Fashion Showcase" class="hero-img">
              </div>
              <!-- Carousel Nav buttons inside image view -->
              <button class="carousel-control prev" @click="prevSlide" aria-label="Previous Slide">
                <ChevronLeft :size="20" />
              </button>
              <button class="carousel-control next" @click="nextSlide" aria-label="Next Slide">
                <ChevronRight :size="20" />
              </button>
            </div>
            
            <div class="hero-content">
              <span class="hero-tag">{{ slide.tag }}</span>
              <h2 class="hero-script">{{ slide.script }} <span class="heart-icon">♡</span></h2>
              <h1 class="hero-title" v-html="slide.title"></h1>
              <p class="hero-desc">{{ slide.desc }}</p>
              <router-link to="/shop" class="btn-shop-now">
                SHOP NOW <ArrowRight :size="16" />
              </router-link>
              
              <div class="hero-trust-badges">
                <div class="trust-badge">
                  <div class="badge-icon"><Gem :size="16" /></div>
                  <span>PREMIUM QUALITY</span>
                </div>
                <div class="trust-badge">
                  <div class="badge-icon"><RotateCcw :size="16" /></div>
                  <span>EASY RETURNS</span>
                </div>
                <div class="trust-badge">
                  <div class="badge-icon"><Lock :size="16" /></div>
                  <span>SECURE PAYMENT</span>
                </div>
                <div class="trust-badge">
                  <div class="badge-icon"><Truck :size="16" /></div>
                  <span>FAST DELIVERY</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. Real-time Social Ticker Banner -->
    <section class="ticker-section">
      <div class="ticker-metrics">
        <div class="metric-item">
          <Eye :size="16" />
          <span><strong>{{ liveVisitors }}</strong> Customers viewing</span>
        </div>
        <div class="metric-divider"></div>
        <div class="metric-item">
          <ShoppingBag :size="16" />
          <span><strong>{{ liveOrders }}</strong> Orders today</span>
        </div>
        <div class="metric-divider"></div>
        <div class="metric-item">
          <Star :size="16" class="star-filled" />
          <span>Rated <strong>4.8★</strong> by 1000+ customers</span>
        </div>
      </div>

      <!-- Seamless infinite marquee ticker -->
      <div class="live-activity-container">
        <span class="live-title">
          <span class="live-pulse-dot"></span>
          LIVE
        </span>
        <div class="live-marquee-wrapper">
          <div class="live-activity-track">
            <div v-for="(toast, idx) in activityToasts" :key="idx" class="activity-toast">
              <span class="toast-avatar-fallback">👤</span>
              <div class="toast-content">
                <strong>{{ toast.name }}</strong> · {{ toast.city }} &nbsp;
                <span class="toast-action">{{ toast.action }}</span> 
                <span class="toast-highlight">{{ toast.item }}</span>
                <span class="toast-time">{{ toast.time }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 6. Shop by Occasion Section -->
    <section class="section occasion-section">
      <div class="section-header text-center">
        <h2 class="section-title">SHOP BY OCCASION <span class="heart-icon">♡</span></h2>
      </div>
      <div class="occasion-grid">
        <router-link 
          v-for="occ in categoriesToShow" 
          :key="occ.id" 
          :to="occ.link" 
          class="occasion-card"
        >
          <div class="occasion-img-wrapper">
            <img :src="occ.image" :alt="occ.name" class="occasion-img">
          </div>
          <div class="occasion-overlay">
            <h3 class="occasion-name">{{ occ.name }}</h3>
            <span class="btn-explore">EXPLORE ➔</span>
          </div>
        </router-link>
      </div>
    </section>

    <!-- 7. Our Story Legacy Section -->
    <section class="section story-section">
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

    <!-- 9. Dark Stats Banner -->
    <section class="dark-stats-banner">
      <div class="container dark-stats-grid">
        <div class="dark-stat-item">
          <div class="dark-stat-icon"><Award :size="36" /></div>
          <div>
            <h3>5+</h3>
            <p>Years Building A Legacy</p>
          </div>
        </div>
        <div class="dark-stat-item">
          <div class="dark-stat-icon"><Smile :size="36" /></div>
          <div>
            <h3>1000+</h3>
            <p>Happy Customers</p>
          </div>
        </div>
        <div class="dark-stat-item">
          <div class="dark-stat-icon"><Store :size="36" /></div>
          <div>
            <h3>50+</h3>
            <p>Trusted Suppliers</p>
          </div>
        </div>
        <div class="dark-stat-item">
          <div class="dark-stat-icon"><ClipboardCheck :size="36" /></div>
          <div>
            <h3>100%</h3>
            <p>Quality Checked</p>
          </div>
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
              </div>
            </div>
          </div>
        </div>
        
        
      </div>

      <!-- Insider Club Form Box -->
      <div class="insider-block">
        <div class="insider-banner-card">
          <div class="insider-info">
            <h4>BECOME A MAYA SREE INSIDER</h4>
            <div class="insider-perks">
              <div class="perk"><Coins :size="16" /> Earn Points on Every Order</div>
              <div class="perk"><Cake :size="16" /> Birthday Special Offers</div>
              <div class="perk"><Star :size="16" /> Early Access to New Collections</div>
              <div class="perk"><Tag :size="16" /> Referral Rewards & Discounts</div>
            </div>
            <button class="btn-insider-join" @click="joinInsiderMock">JOIN NOW • IT'S FREE ➔</button>
          </div>
          
          <div class="whatsapp-club-box">
            <span class="badge-discount">GET ₹150 OFF</span>
            <h5>ON YOUR FIRST ORDER</h5>
            <p>Join our WhatsApp Club & be the first to know about new launches & offers!</p>
            <form class="whatsapp-club-form" @submit.prevent="joinClub">
              <input type="tel" v-model="clubPhone" placeholder="Enter WhatsApp Number" required class="club-input">
              <button type="submit" class="club-submit-btn">JOIN NOW</button>
            </form>
            <span class="privacy-note">We respect your privacy. No spam, ever.</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 14. Interactive FAQs Section -->
    <section class="section faq-section">
      <div class="container faq-container">
        <h2 class="section-title text-center">Frequently Asked Questions</h2>
        <div class="accordion-list">
          <div 
            v-for="(faq, i) in faqs" 
            :key="i" 
            class="accordion-item"
            :class="{ active: activeFaq === i }"
          >
            <button class="accordion-header" @click="toggleFaq(i)">
              <span>{{ faq.question }}</span>
              <span class="icon-toggle">
                <Plus v-if="activeFaq !== i" :size="16" />
                <Minus v-else :size="16" />
              </span>
            </button>
            <div class="accordion-body">
              <p>{{ faq.answer }}</p>
            </div>
          </div>
        </div>
        <div class="text-center mt-3">
          <button class="btn-faq-all" @click="$router.push('/shop')">VIEW ALL FAQS</button>
        </div>
      </div>
    </section>

    <!-- Story Modal -->
    <div class="modal" :class="{ active: showStoryModal }" @click.self="showStoryModal = false">
      <div class="modal-content">
        <button class="modal-close" @click="showStoryModal = false"><X :size="18" /></button>
        <div class="modal-header">
          <h3>Our Legacy - A Mother's Gift</h3>
        </div>
        <div class="modal-body">
          <p>Maya Sree Fashion was founded with a clear dream: to create beautiful, sustainable, and comfortable clothing that celebrates Indian craftsmanship while offering contemporary layouts.</p>
          <p>Collaborating with local handloom weaving societies and India Knit Fashion, we bring you selected threads stitched by traditional dressmakers, assuring premium material and quality checked finishing.</p>
        </div>
      </div>
    </div>

    <!-- Video Modal Player -->
    <div class="modal" :class="{ active: showVideoModal }" @click.self="showVideoModal = false">
      <div class="modal-content video-modal-content">
        <button class="modal-close" @click="showVideoModal = false" style="color: #fff;"><X :size="18" /></button>
        <div class="video-aspect-wrapper">
          <div class="mock-video-player">
            <div class="mock-video-inner">
              <Play :size="48" class="play-icon-pulse" />
              <span>Playing Brand Documentary video...</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Storefront Reels Modal Player Lightbox -->
    <div class="modal reel-detail-modal" :class="{ active: showReelModal }" @click.self="closeReelModal" v-if="selectedReel">
      <div class="modal-content reel-modal-content" style="max-width: 420px; background: #000; border-radius: 16px; overflow: hidden; position: relative; aspect-ratio: 9/16; border: 1px solid rgba(255,255,255,0.15);">
        <button class="modal-close" @click="closeReelModal" style="color: #fff; background: rgba(0,0,0,0.5); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; position: absolute; right: 15px; top: 15px; z-index: 100; border: 1px solid rgba(255,255,255,0.25); font-size: 1.25rem;">
          <X :size="18" />
        </button>

        <div style="width: 100%; height: 100%; position: relative;">
          <!-- YouTube video with sound enabled by default in modal (user clicked to open) -->
          <iframe 
            v-if="selectedReel.type === 'youtube' && selectedReel.video_url"
            :src="getYouTubeEmbedUrl(selectedReel.video_url, false)" 
            frameborder="0" 
            allow="autoplay; encrypted-media"
            allowfullscreen
            class="full-modal-video"
            style="width: 100%; height: 100%; object-fit: cover;"
          ></iframe>

          <!-- HTML5 Video with sound option -->
          <video 
            v-else-if="selectedReel.type === 'file' && selectedReel.video_url"
            :src="selectedReel.video_url" 
            autoplay 
            loop 
            :muted="isReelMuted" 
            playsinline 
            class="full-modal-video"
            style="width: 100%; height: 100%; object-fit: cover;"
          ></video>

          <!-- Instagram Embed Iframe for modal (user clicked to open) -->
          <iframe 
            v-else-if="selectedReel.instagram_url"
            :src="getInstagramEmbedUrl(selectedReel.instagram_url)" 
            frameborder="0" 
            scrolling="no"
            allowtransparency="true"
            allow="autoplay; encrypted-media"
            class="full-modal-video"
            style="width: 100%; height: 100%; border: none; background: #000;"
          ></iframe>

          <!-- Fallback if neither exists -->
          <div v-else class="full-modal-video" style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--color-primary) 0%, #1e0513 100%); color: #fff; padding: var(--spacing-lg); text-align: center; gap: 15px; box-sizing: border-box;">
            <Instagram :size="48" style="color: var(--color-secondary);" />
            <h4 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--color-secondary); margin-bottom: 2px;">Exclusive Style Post</h4>
            <p style="font-size: 0.8rem; color: #cbd5e1; line-height: 1.4; max-width: 250px; margin: 0 auto;">Check out this special design showcase directly on our Instagram feed.</p>
          </div>

          <!-- Unmute/Mute Toggle overlay (only for direct files with a video URL) -->
          <button 
            v-if="selectedReel.type === 'file' && selectedReel.video_url"
            class="reel-volume-btn" 
            @click="toggleReelMute"
            style="position: absolute; right: 15px; bottom: 85px; z-index: 90; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.3); border-radius: 50%; width: 40px; height: 40px; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none;"
          >
            <component :is="isReelMuted ? VolumeX : Volume2" :size="20" />
          </button>

          <!-- Caption & Social link overlay -->
          <div class="reel-modal-info-panel" style="position: absolute; bottom: 0; left: 0; width: 100%; background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.4) 70%, transparent 100%); padding: var(--spacing-md) var(--spacing-lg) var(--spacing-lg) var(--spacing-lg); color: #fff; display: flex; flex-direction: column; gap: var(--spacing-sm); box-sizing: border-box;">
            <p style="font-size: 0.9rem; line-height: 1.4; margin-bottom: var(--spacing-xs); text-shadow: 1px 1px 2px rgba(0,0,0,0.8); font-weight: 500;">
              {{ selectedReel.caption || 'Maya Sree Premium Fashion Collection' }}
            </p>
            <a 
              v-if="selectedReel.instagram_url"
              :href="selectedReel.instagram_url" 
              target="_blank"
              class="btn btn--primary"
              style="background-color: var(--color-secondary); color: var(--color-primary); font-weight: 700; width: 100%; text-decoration: none; border-radius: 30px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.85rem; padding: 10px 0; border: none; transition: transform 0.2s;"
              onmouseover="this.style.transform='scale(1.02)'"
              onmouseout="this.style.transform='scale(1)'"
            >
              <ExternalLink :size="16" /> View Original Post ➔
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useCategoryStore } from '../../stores/category';
import axios from 'axios';
import { 
  ChevronLeft, 
  ChevronRight, 
  ArrowRight, 
  MessageCircle, 
  Eye, 
  ShoppingBag, 
  Star, 
  Gem, 
  RotateCcw, 
  Lock, 
  Truck, 
  CalendarCheck, 
  Users, 
  Handshake, 
  ShieldCheck, 
  Shirt, 
  Sparkles, 
  CheckCircle, 
  Leaf, 
  RefreshCw, 
  Headset, 
  Award, 
  Smile, 
  Store, 
  ClipboardCheck, 
  Heart, 
  Play, 
  Check, 
  Coins, 
  Cake, 
  Tag, 
  Plus, 
  Minus, 
  X,
  Volume2,
  VolumeX,
  Youtube,
  ExternalLink,
  Instagram
} from 'lucide-vue-next';

const emit = defineEmits(['update-wishlist-count', 'update-cart-count']);

const categoryStore = useCategoryStore();

const products = ref([]);
const loading = ref(true);
const currentSlide = ref(0);
const liveVisitors = ref(23);
const liveOrders = ref(8);
const activeVideoTab = ref(0);
const activeFaq = ref(null);
const showStoryModal = ref(false);
const showVideoModal = ref(false);
const clubPhone = ref('');

const activeReels = ref([]);
const showReelModal = ref(false);
const selectedReel = ref(null);
const isReelMuted = ref(true);

const openReelModal = (reel) => {
  selectedReel.value = reel;
  isReelMuted.value = false;
  showReelModal.value = true;
};

const closeReelModal = () => {
  showReelModal.value = false;
  selectedReel.value = null;
};

const toggleReelMute = () => {
  isReelMuted.value = !isReelMuted.value;
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

// YouTube helper to get embeddable iframe URL with autoplay parameters
const getYouTubeEmbedUrl = (url, isMutedPreview = false) => {
  if (!url) return '';
  let videoId = '';
  
  if (url.includes('youtube.com/shorts/')) {
    videoId = url.split('youtube.com/shorts/')[1]?.split('?')[0]?.split('&')[0];
  } else if (url.includes('youtube.com/watch')) {
    const urlParams = new URLSearchParams(new URL(url).search);
    videoId = urlParams.get('v');
  } else if (url.includes('youtu.be/')) {
    videoId = url.split('youtu.be/')[1]?.split('?')[0]?.split('&')[0];
  } else if (url.includes('youtube.com/embed/')) {
    videoId = url.split('youtube.com/embed/')[1]?.split('?')[0]?.split('&')[0];
  } else {
    videoId = url;
  }

  if (!videoId) return '';
  const autoplay = isMutedPreview ? '1' : '0';
  const mute = isMutedPreview ? '1' : '0';
  return `https://www.youtube.com/embed/${videoId}?autoplay=${autoplay}&mute=${mute}&loop=1&playlist=${videoId}&controls=0&modestbranding=1&playsinline=1&enablejsapi=1`;
};

const getInstagramEmbedUrl = (url) => {
  if (!url) return '';
  let cleanUrl = url.trim();
  if (cleanUrl.includes('?')) {
    cleanUrl = cleanUrl.split('?')[0];
  }
  if (!cleanUrl.endsWith('/')) {
    cleanUrl += '/';
  }
  return `${cleanUrl}embed`;
};

const productsGridContainer = ref(null);
let slideInterval = null;
let visitorInterval = null;

const slides = [
  {
    leftImage: '/asset/banner-1-left.png',
    rightImage: '/asset/banner-1-right.png',
    tag: 'DIRECT MANUFACTURER',
    script: 'Wholesale Fashion',
    title: 'Direct from<br>Manufacturer –<br>your <span class="highlight">savings</span> is here. <span class="heart-icon">♡</span>',
    desc: 'Offering the best quality at true wholesale prices, exclusively for retailers and bulk fashion buyers.',
    btnText: 'SHOP NOW'
  },
  {
    leftImage: '/asset/banner-2-left.png',
    rightImage: '/asset/banner-2-right.png',
    tag: 'TRENDING NOW',
    script: 'Stretchable Blouses',
    title: 'Ready-Made Elegance –<br>crafted for <span class="highlight">comfort</span>. <span class="heart-icon">♡</span>',
    desc: 'Explore premium stretchable readymade blouses in various colors and designer finishes, styled for traditional fits.',
    btnText: 'EXPLORE SHOP'
  }
];

const occasions = [
  { id: 1, name: 'Daily Wear', image: 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=400&h=533&q=80', slug: 'womens' },
  { id: 2, name: 'Office Wear', image: 'https://images.unsplash.com/photo-1608748010899-18f300247112?auto=format&fit=crop&w=400&h=533&q=80', slug: 'womens' },
  { id: 3, name: 'Wedding Collection', image: 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=400&h=533&q=80', slug: 'womens' },
  { id: 4, name: 'Festival Collection', image: 'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?auto=format&fit=crop&w=400&h=533&q=80', slug: 'womens' },
  { id: 5, name: 'Kids Collection', image: 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?auto=format&fit=crop&w=400&h=533&q=80', slug: 'kids' },
  { id: 6, name: 'New Arrivals', image: 'https://images.unsplash.com/photo-1596783074918-c84cb06531ca?auto=format&fit=crop&w=400&h=533&q=80', slug: 'shop' }
];

const categoriesToShow = computed(() => {
  const activeCats = categoryStore.categories.filter(c => c.is_active);
  if (activeCats.length > 0) {
    return activeCats.slice(0, 6).map(c => ({
      id: c.id,
      name: c.name,
      image: c.image || 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?auto=format&fit=crop&w=400&h=533&q=80',
      link: `/shop?category_id=${c.id}`
    }));
  }
  return occasions.map(o => ({
    id: o.id,
    name: o.name,
    image: o.image,
    link: o.slug === 'shop' ? '/shop' : `/shop?category_id=${o.slug === 'womens' ? 6 : 7}`
  }));
});

const videoTabs = ['Process & Fabrics', 'Skilled Craftsmanship', 'Healthy Wash/Dye', 'Made with Love'];

const reviews = [
  { name: 'Priya S.', city: 'Chennai', product: 'Festive Anarkali' },
  { name: 'Anitha R.', city: 'Madurai', product: 'Straight Kurta' },
  { name: 'Kavya M.', city: 'Tirupur', product: 'Wedding Collection' },
  { name: 'Sneha V.', city: 'Erode', product: 'A-Line Kurta' },
  { name: 'Meera K.', city: 'Salem', product: 'Designer Set' }
];

const faqs = [
  { question: 'What is your return/exchange policy?', answer: 'We offer a hassle-free 7-day exchange and return policy. Outfits must be unworn, unwashed, and in their original packaging with tags intact. Reverse pick-up is arranged automatically.' },
  { question: 'How long will my order take to arrive?', answer: 'Standard delivery takes 3 to 5 business days for major metro cities and 5 to 7 business days for other regions across India.' },
  { question: 'Do you offer cash on delivery (COD)?', answer: 'Yes, we offer Cash on Delivery across most pin codes in India for orders below ₹10,000.' },
  { question: 'How do I choose the right size?', answer: 'Please check our detailed Sizing Guide available on every product page. If you are between sizes, we recommend selecting one size up for a more comfortable ethnic wear fit.' },
  { question: 'How can I contact support?', answer: 'You can contact our support team via email at support@mayasreefashion.com, call us at +91 99442 85102, or chat directly via the WhatsApp Floating Widget.' }
];

const activityToasts = ref([
  { name: 'Priya', city: 'Chennai', action: 'purchased', item: 'Festive Kurti', time: '2m ago' },
  { name: 'Anitha', city: 'Madurai', action: 'ordered', item: 'Wedding Saree', time: '5m ago' },
  { name: 'Kavya', city: 'Tirupur', action: 'added', item: '4 items to cart', time: '8m ago' },
  { name: 'Sneha', city: 'Salem', action: 'purchased', item: 'Kids Wear Set', time: '10m ago' },
  { name: 'Deepa', city: 'Tiruppur', action: 'purchased', item: 'Bridal Lehenga', time: '14m ago' }
]);

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/storefront/products', {
      params: { per_page: 8 }
    });
    if (response.data && response.data.success) {
      products.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load boutique products:', err);
  } finally {
    loading.value = false;
  }
};

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % slides.length;
};

const prevSlide = () => {
  currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length;
};

const toggleFaq = (index) => {
  activeFaq.value = activeFaq.value === index ? null : index;
};

const scrollProducts = (direction) => {
  if (productsGridContainer.value) {
    productsGridContainer.value.scrollBy({ left: direction * 280, behavior: 'smooth' });
  }
};

const joinInsiderMock = () => {
  alert('Thank you for your interest! Maya Sree Insider program registration is successful.');
};

const joinClub = () => {
  alert(`Thank you for joining our WhatsApp Club! Code: MSF150 sent to ${clubPhone.value}.`);
  clubPhone.value = '';
};

// Wishlist
const wishlist = ref([]);
const loadWishlist = () => {
  try {
    wishlist.value = JSON.parse(localStorage.getItem('vibe_wishlist_items') || '[]');
  } catch (e) {
    wishlist.value = [];
  }
};

const isInWishlist = (id) => {
  return wishlist.value.some(item => item.id === id);
};

const toggleWishlist = (product) => {
  const index = wishlist.value.findIndex(item => item.id === product.id);
  if (index >= 0) {
    wishlist.value.splice(index, 1);
  } else {
    wishlist.value.push({
      id: product.id,
      uuid: product.uuid,
      name: product.name,
      selling_price: product.selling_price,
      image: getPrimaryImage(product)
    });
  }
  localStorage.setItem('vibe_wishlist_items', JSON.stringify(wishlist.value));
  emit('update-wishlist-count');
};

// Quick Add
const quickAdd = (product, size) => {
  try {
    const cartItems = JSON.parse(localStorage.getItem('vibe_cart_items') || '[]');
    const primaryVariant = product.variants && product.variants.length ? product.variants[0] : null;
    
    const existingIndex = cartItems.findIndex(item => item.product_id === product.id && item.size === size);
    if (existingIndex >= 0) {
      cartItems[existingIndex].quantity += 1;
    } else {
      cartItems.push({
        product_id: product.id,
        product_uuid: product.uuid,
        product_variant_id: primaryVariant ? primaryVariant.id : 1,
        name: product.name,
        size: size,
        color: primaryVariant ? primaryVariant.color : null,
        selling_price: product.selling_price,
        mrp: product.mrp,
        image: getPrimaryImage(product),
        quantity: 1,
        stock_quantity: primaryVariant ? primaryVariant.stock_quantity : 99
      });
    }
    localStorage.setItem('vibe_cart_items', JSON.stringify(cartItems));
    emit('update-cart-count');
    alert(`✓ Added ${product.name} (Size: ${size}) to Cart successfully.`);
  } catch (err) {
    console.error('Failed to quick add to cart:', err);
  }
};

// Simulation activities
const runSimulations = () => {
  slideInterval = setInterval(nextSlide, 6000);

  visitorInterval = setInterval(() => {
    const diff = Math.floor(Math.random() * 5) - 2;
    let nextVal = liveVisitors.value + diff;
    if (nextVal < 15) nextVal = 18;
    if (nextVal > 40) nextVal = 35;
    liveVisitors.value = nextVal;

    if (Math.random() > 0.8) {
      liveOrders.value += 1;
      triggerMockToast();
    }
  }, 5000);
};

const triggerMockToast = () => {
  const buyers = [
    { name: 'Meenakshi', city: 'Trichy', item: 'Erode Cotton Saree' },
    { name: 'Divya', city: 'Salem', item: 'Temple Border Silk Saree' },
    { name: 'Abirami', city: 'Thanjavur', item: 'Girls Pattu Pavadai Set' }
  ];
  const b = buyers[Math.floor(Math.random() * buyers.length)];
  activityToasts.value.unshift({
    name: b.name,
    city: b.city,
    action: 'ordered',
    item: b.item,
    time: '1m ago'
  });
  if (activityToasts.value.length > 5) {
    activityToasts.value.pop();
  }
};

const getPrimaryImage = (product) => {
  if (!product.images || product.images.length === 0) {
    return 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=300&auto=format&fit=crop';
  }
  const primary = product.images.find(img => img.is_primary);
  return primary ? (primary.image_path || primary.url) : (product.images[0].image_path || product.images[0].url);
};

onMounted(() => {
  fetchProducts();
  loadWishlist();
  runSimulations();
  categoryStore.fetchPublicCategories();
  fetchActiveReels();
});

onUnmounted(() => {
  clearInterval(slideInterval);
  clearInterval(visitorInterval);
});
</script>

<style scoped>
/* ==========================================================================
   Maya Sree Style System
   ========================================================================== */
.storefront-home {
  font-family: 'Poppins', sans-serif;
  color: var(--color-text-primary);
  background-color: #ffffff;
}

.section {
  padding: 60px 0;
}

.section-header {
  margin-bottom: 30px;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: var(--color-primary);
  letter-spacing: 1px;
}

.text-center {
  text-align: center;
}

.heart-icon {
  color: var(--color-secondary);
  font-size: 1.1rem;
  display: inline-block;
  vertical-align: middle;
}

/* 4. Hero Carousel Section */
.hero-section {
  position: relative;
  background-color: var(--blush-bg);
}

.hero-carousel {
  position: relative;
  width: 100%;
}

.hero-slide {
  display: none;
  animation: fadeEffect 0.8s ease-in-out;
}

.hero-slide.active {
  display: block;
}

@keyframes fadeEffect {
  from { opacity: 0.4; }
  to { opacity: 1; }
}

.hero-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  min-height: 520px;
  align-items: center;
  justify-items: center;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

.hero-images-wrapper {
  position: relative;
  width: 100%;
  max-width: 520px;
  padding: 40px 0;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto;
}

.hero-main-card {
  width: 280px;
  height: 420px;
  border-radius: 120px 120px 8px 8px;
  overflow: hidden;
  box-shadow: var(--shadow-lg);
  border: 6px solid #ffffff;
  transform: rotate(-3deg);
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
  z-index: 2;
}

.hero-sub-card {
  width: 220px;
  height: 330px;
  border-radius: 100px 100px 8px 8px;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  border: 5px solid #ffffff;
  position: absolute;
  bottom: 20px;
  right: 40px;
  transform: rotate(4deg);
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
  z-index: 1;
}

.hero-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
}

.hero-images-wrapper:hover .hero-main-card {
  transform: rotate(0deg) scale(1.02);
}

.hero-images-wrapper:hover .hero-sub-card {
  transform: rotate(0deg) translate(-20px, -10px);
}

/* Slider Controls */
.carousel-control {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background-color: var(--color-primary);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.7;
  z-index: 5;
  transition: opacity 0.2s, background-color 0.2s;
  border: none;
}

.carousel-control:hover {
  opacity: 1;
  background-color: var(--color-secondary);
}

.carousel-control.prev {
  left: 20px;
}

.carousel-control.next {
  right: 20px;
}

/* Hero Content Styling */
.hero-content {
  padding: 40px 60px 40px 20px;
  text-align: left;
}

.hero-tag {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--color-primary);
  letter-spacing: 3px;
  text-transform: uppercase;
}

.hero-script {
  font-family: 'Playfair Display', serif;
  font-size: 2.5rem;
  color: var(--color-secondary);
  font-style: italic;
  font-weight: 400;
  margin-top: -10px;
  margin-bottom: 10px;
}

.hero-title {
  font-family: 'Playfair Display', serif;
  font-size: 3rem;
  color: var(--color-primary);
  line-height: 1.15;
  font-weight: 700;
  margin-bottom: 20px;
}

.hero-title :deep(.highlight) {
  color: var(--color-primary);
  border-bottom: 3px solid var(--color-secondary);
}

.hero-desc {
  color: var(--color-text-secondary);
  font-size: 1.05rem;
  margin-bottom: 30px;
  max-width: 460px;
}

.btn-shop-now {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background-color: var(--color-primary);
  color: #ffffff;
  padding: 15px 35px;
  border-radius: 30px;
  font-weight: 600;
  letter-spacing: 1px;
  box-shadow: 0 10px 20px rgba(74, 14, 46, 0.15);
  transition: background-color 0.2s, transform 0.2s, box-shadow 0.2s;
}

.btn-shop-now:hover {
  background-color: var(--color-primary-hover);
  transform: translateY(-3px);
  box-shadow: 0 15px 30px rgba(74, 14, 46, 0.25);
}

/* Trust Badges inside hero */
.hero-trust-badges {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
  margin-top: 40px;
  border-top: 1px solid var(--color-border);
  padding-top: 25px;
}

.trust-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.badge-icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background-color: #ffffff;
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  margin-bottom: 8px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--color-border);
}

.trust-badge span {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  color: var(--color-text-primary);
}

/* 5. Real-time Social Ticker Banner */
.ticker-section {
  background: linear-gradient(135deg, var(--blush-bg) 0%, #fdf6ec 100%);
  border-top: 1px solid var(--color-border);
  border-bottom: 2px solid var(--color-border);
  padding: 14px 20px 18px;
}

.ticker-metrics {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 0;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 2px;
}

.metric-item {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 0 22px;
  color: var(--color-text-primary);
}

.metric-item .star-filled {
  color: var(--color-secondary);
}

.metric-item strong {
  color: var(--color-primary);
  font-weight: 700;
}

.metric-divider {
  width: 1px;
  height: 20px;
  background-color: var(--color-border);
}

.live-activity-container {
  margin: 12px auto 0;
  border-top: 1px dashed var(--color-border);
  padding-top: 12px;
  display: flex;
  align-items: center;
  gap: 14px;
  overflow: hidden;
  max-width: 1200px;
}

.live-title {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 1.5px;
  color: #ffffff;
  background-color: var(--color-primary);
  padding: 5px 12px 5px 10px;
  border-radius: 20px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 2px 8px rgba(74, 14, 46, 0.2);
}

.live-pulse-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #ff4d4d;
  display: inline-block;
  flex-shrink: 0;
  animation: pulse-dot-anim 1.5s ease-in-out infinite;
}

@keyframes pulse-dot-anim {
  0%, 100% { transform: scale(1); opacity: 1; }
  50%       { transform: scale(1.5); opacity: 0.6; }
}

.live-marquee-wrapper {
  flex: 1;
  width: 100%;
  overflow: hidden;
  -webkit-mask-image: linear-gradient(to right, transparent 0%, black 6%, black 94%, transparent 100%);
  mask-image: linear-gradient(to right, transparent 0%, black 6%, black 94%, transparent 100%);
}

.live-activity-track {
  display: flex;
  gap: 16px;
  width: max-content;
  min-width: 100%;
  will-change: transform;
  animation: marquee-anim 35s linear infinite;
}

.live-activity-track:hover {
  animation-play-state: paused;
}

@keyframes marquee-anim {
  0%   { transform: translate3d(0, 0, 0); }
  100% { transform: translate3d(-30%, 0, 0); }
}

.activity-toast {
  display: flex;
  align-items: center;
  gap: 10px;
  background-color: #ffffff;
  padding: 7px 16px 7px 12px;
  border-radius: 40px;
  box-shadow: var(--shadow-sm);
  white-space: nowrap;
  border: 1px solid var(--color-border);
  border-left: 3px solid var(--color-secondary);
}

.toast-avatar-fallback {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: var(--blush-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  border: 2px solid var(--color-border);
}

.toast-content {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 4px;
}

.toast-action {
  color: var(--color-text-secondary);
}

.toast-highlight {
  color: var(--color-primary);
  font-weight: 700;
}

.toast-time {
  color: var(--color-text-secondary);
  font-size: 0.72rem;
  margin-left: 4px;
}

/* 6. Shop by Category Section */
.occasion-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 15px;
  padding: 0 var(--spacing-md);
  max-width: 1400px;
  margin: 0 auto;
}

@media (min-width: 992px) and (max-width: 1200px) {
  .occasion-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.occasion-card {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  aspect-ratio: 4/5;
  background-color: var(--blush-bg);
}

.occasion-img-wrapper {
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.occasion-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.occasion-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  padding: 25px 15px;
  background: linear-gradient(to top, rgba(74, 14, 46, 0.85) 0%, rgba(74, 14, 46, 0.4) 60%, transparent 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  color: #ffffff;
  text-align: center;
  height: 60%;
}

.occasion-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 8px;
  letter-spacing: 0.5px;
}

.btn-explore {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 1px;
  color: #f5e1a4;
  transition: transform 0.2s, color 0.2s;
}

.occasion-card:hover .occasion-img {
  transform: scale(1.08);
}

.occasion-card:hover .btn-explore {
  color: #ffffff;
  transform: translateX(4px);
}

/* 7. Our Story Legacy Section */
.story-section {
  background-color: var(--blush-bg);
  border-top: 1px solid var(--color-border);
  border-bottom: 1px solid var(--color-border);
}

.story-grid {
  display: grid;
  grid-template-columns: 1fr 1.3fr 1fr;
  gap: 40px;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

.story-image-block {
  display: flex;
  justify-content: center;
}

.story-img-frame {
  width: 100%;
  max-width: 280px;
  aspect-ratio: 4/5;
  border-radius: 100px 100px 8px 8px;
  overflow: hidden;
  border: 5px solid #ffffff;
  box-shadow: var(--shadow-md);
}

.story-founder-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.story-content-block {
  padding: 0 10px;
  text-align: left;
}

.story-tag {
  font-size: 0.85rem;
  font-weight: 800;
  color: var(--color-secondary);
  letter-spacing: 3px;
  display: block;
  margin-bottom: 10px;
}

.story-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  color: var(--color-primary);
  line-height: 1.25;
  margin-bottom: 20px;
}

.story-body p {
  color: var(--color-text-secondary);
  font-size: 0.95rem;
  margin-bottom: 15px;
}

.story-body p strong {
  color: var(--color-text-primary);
}

.btn-story-read {
  margin-top: 15px;
  color: var(--color-primary);
  font-weight: 700;
  font-size: 0.9rem;
  border: none;
  background: none;
  border-bottom: 2px solid var(--color-secondary);
  padding-bottom: 4px;
  cursor: pointer;
  transition: color 0.2s, border-color 0.2s;
}

.btn-story-read:hover {
  color: var(--color-primary-hover);
  border-color: var(--color-primary);
}

.story-stats-block {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
}

.stat-card {
  background-color: #ffffff;
  padding: 20px 15px;
  border-radius: 8px;
  text-align: center;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--color-border);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
  border-color: var(--color-secondary);
}

.stat-icon {
  font-size: 1.5rem;
  color: var(--color-secondary);
  margin-bottom: 10px;
  display: flex;
  justify-content: center;
}

.stat-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: var(--color-primary);
  margin-bottom: 5px;
}

.stat-card p {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-secondary);
  text-transform: uppercase;
}

/* 8. Why Customers Love Mayasree Values Grid */
.values-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

.value-item {
  text-align: center;
  padding: 10px;
}

.value-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: var(--blush-bg);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin: 0 auto 15px auto;
  border: 1px solid var(--color-border);
  transition: background-color 0.2s, color 0.2s, transform 0.2s;
}

.value-item:hover .value-icon {
  background-color: var(--color-primary);
  color: #ffffff;
  transform: rotate(360deg);
}

.value-item h4 {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 6px;
}

.value-item p {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  line-height: 1.4;
}

/* 9. Dark Stats Banner */
.dark-stats-banner {
  background-color: var(--color-primary);
  color: #ffffff;
  padding: 40px 0;
}

.dark-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

.dark-stat-item {
  display: flex;
  align-items: center;
  gap: 20px;
}

.dark-stat-icon {
  font-size: 2.2rem;
  color: #f5e1a4;
}

.dark-stat-item h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  font-weight: 700;
  line-height: 1.1;
  text-align: left;
  color: #ffffff !important;
}

.dark-stat-item p {
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--blush-bg);
  opacity: 0.85;
  text-align: left;
}

/* 10. Best Sellers Showcase Grid */
.best-sellers-carousel-wrapper {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 50px;
}

.products-grid {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  scroll-behavior: smooth;
  padding: 10px 0;
}

.products-grid::-webkit-scrollbar {
  display: none;
}

.scroll-control {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #ffffff;
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary);
  z-index: 5;
  transition: background-color 0.2s, color 0.2s;
  border: none;
  cursor: pointer;
}

.scroll-control:hover {
  background-color: var(--color-primary);
  color: #ffffff;
}

.scroll-control.left {
  left: 10px;
}

.scroll-control.right {
  right: 10px;
}

/* Product Card design */
.product-card {
  position: relative;
  background-color: #ffffff;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  transition: transform 0.3s, box-shadow 0.3s, border-color 0.3s;
  width: 210px;
  flex-shrink: 0;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
  border-color: var(--color-secondary);
}

.product-tag {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 3;
  font-size: 0.7rem;
  font-weight: 700;
  color: #ffffff;
  padding: 4px 10px;
  border-radius: 4px;
  text-transform: uppercase;
}

.product-tag.selling-fast { background-color: #e21b5a; }

.product-image-container {
  position: relative;
  width: 100%;
  aspect-ratio: 3/4;
  overflow: hidden;
  background-color: var(--blush-bg);
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .product-image {
  transform: scale(1.06);
}

.btn-wishlist {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.9);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-sm);
  z-index: 3;
  transition: background-color 0.2s, color 0.2s;
  border: none;
  cursor: pointer;
}

.btn-wishlist .heart-filled {
  color: #ef4444;
  fill: #ef4444;
}

.btn-wishlist:hover {
  background-color: var(--color-primary);
  color: #ffffff;
}

/* Quick Add Overlay on Card hover */
.quick-add {
  position: absolute;
  bottom: -60px;
  left: 0;
  width: 100%;
  background-color: rgba(74, 14, 46, 0.9);
  padding: 10px;
  text-align: center;
  transition: bottom 0.3s ease;
  z-index: 4;
}

.product-card:hover .quick-add {
  bottom: 0;
}

.quick-add-title {
  font-size: 0.75rem;
  font-weight: 700;
  color: #f5e1a4;
  display: block;
  margin-bottom: 6px;
  text-transform: uppercase;
}

.size-options {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.size-options span {
  padding: 4px 8px;
  border-radius: 4px;
  background-color: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s, color 0.2s;
  cursor: pointer;
}

.size-options span:hover {
  background-color: var(--color-secondary);
  color: var(--color-primary);
}

.product-info {
  padding: 15px;
  text-align: left;
}

.product-title a {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-text-primary);
  margin-bottom: 6px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}

.product-price {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.current-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-primary);
}

.old-price {
  font-size: 0.85rem;
  text-decoration: line-through;
  color: var(--color-text-secondary);
}

.product-rating {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.75rem;
  color: var(--color-text-secondary);
}

.product-rating .stars {
  font-weight: 700;
  color: var(--color-secondary);
}

.star-filled {
  fill: currentColor;
}

.product-live-view {
  color: #e21b5a;
  font-weight: 500;
}

.btn-view-all {
  background-color: transparent;
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
  padding: 12px 30px;
  border-radius: 30px;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 1px;
  transition: background-color 0.2s, color 0.2s;
  display: inline-block;
}

.btn-view-all:hover {
  background-color: var(--color-primary);
  color: #ffffff;
}

/* 11. Customer Reviews & Video Brand Section */
.media-reviews-section {
  background-color: #fffcf7;
  padding: 80px 40px;
}

.media-reviews-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 50px;
}

.block-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem;
  color: var(--color-primary);
  margin-bottom: 25px;
  letter-spacing: 1px;
  border-bottom: 1px dashed var(--color-secondary);
  padding-bottom: 12px;
  text-align: left;
}

.customer-cards-grid {
  display: flex;
  flex-direction: column;
  gap: 15px;
  max-height: 420px;
  overflow-y: auto;
  padding-right: 15px;
}

.customer-cards-grid::-webkit-scrollbar {
  width: 6px;
}

.customer-cards-grid::-webkit-scrollbar-thumb {
  background-color: var(--color-border);
  border-radius: 3px;
}

.customer-review-card {
  display: flex;
  align-items: center;
  gap: 20px;
  background-color: #ffffff;
  padding: 15px 20px;
  border-radius: 8px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--color-border);
}

.avatar-fallback-reviewer {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: var(--blush-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  border: 2px solid var(--color-border);
}

.review-meta {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.review-meta strong {
  font-size: 0.95rem;
  color: var(--color-text-primary);
}

.review-city {
  font-size: 0.8rem;
  color: var(--color-text-secondary);
}

.review-meta .stars {
  color: var(--color-secondary);
  font-size: 0.8rem;
  margin: 4px 0;
}

.review-product {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-primary);
  background-color: var(--blush-bg);
  padding: 2px 8px;
  border-radius: 4px;
  display: inline-block;
  align-self: flex-start;
}

.btn-customer-photos {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--color-primary);
  border: none;
  background: none;
  border-bottom: 2px solid var(--color-secondary);
  padding-bottom: 3px;
  cursor: pointer;
}

.video-brand-block {
  display: flex;
  flex-direction: column;
}

.video-interactive-container {
  width: 100%;
  flex: 1;
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  aspect-ratio: 16/9;
}

.video-thumbnail-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.video-cover-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.play-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(74, 14, 46, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.play-circle {
  width: 68px;
  height: 68px;
  border-radius: 50%;
  background-color: #ffffff;
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-lg);
  transition: transform 0.3s, background-color 0.3s, color 0.3s;
}

.play-icon-svg {
  fill: currentColor;
}

.video-thumbnail-wrapper:hover .play-overlay {
  background-color: rgba(74, 14, 46, 0.45);
}

.video-thumbnail-wrapper:hover .play-circle {
  transform: scale(1.1);
  background-color: var(--color-secondary);
  color: var(--color-primary);
}

.video-vertical-tabs {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: space-around;
  padding: 10px 0;
  backdrop-filter: blur(5px);
}

.v-tab {
  font-size: 0.75rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  transition: color 0.2s;
}

.v-tab.active, .v-tab:hover {
  color: #f5e1a4;
}

.btn-watch-story {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--color-primary);
  border: none;
  background: none;
  border-bottom: 2px solid var(--color-secondary);
  padding-bottom: 3px;
  cursor: pointer;
}

/* 12. Complete Look / Trending Collections / WhatsApp Assistant */
.mix-match-section {
  background-color: #ffffff;
}

.mix-match-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr 1fr;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

.look-card {
  background-color: var(--blush-bg);
  border-radius: 16px;
  padding: 25px;
  border: 1px dashed var(--color-secondary);
  box-shadow: var(--shadow-sm);
}

.look-product-showcase {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 25px;
}

.look-item-thumb {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 20%;
  cursor: pointer;
}

.look-item-thumb img {
  border-radius: 50%;
  aspect-ratio: 1/1;
  object-fit: cover;
  border: 2px solid #ffffff;
  box-shadow: var(--shadow-sm);
  transition: transform 0.2s, border-color 0.2s;
  width: 100%;
}

.look-item-thumb:hover img {
  transform: scale(1.1);
  border-color: var(--color-secondary);
}

.look-item-thumb span {
  font-size: 0.7rem;
  font-weight: 600;
  margin-top: 6px;
  color: var(--color-text-secondary);
}

.plus-sign {
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--color-secondary);
}

.look-summary p {
  font-size: 0.85rem;
  color: var(--color-text-primary);
  margin-bottom: 15px;
  font-weight: 500;
}

.btn-shop-look {
  background-color: var(--color-primary);
  color: #ffffff;
  padding: 12px 25px;
  border-radius: 30px;
  font-size: 0.85rem;
  font-weight: 700;
  box-shadow: var(--shadow-sm);
  transition: background-color 0.2s, transform 0.2s;
  border: none;
  cursor: pointer;
}

.btn-shop-look:hover {
  background-color: var(--color-primary-hover);
  transform: translateY(-2px);
}

.trending-bubbles-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
  padding: 10px 0;
}

.bubble-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 80px;
  text-align: center;
  cursor: pointer;
}

.bubble-item img {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--blush-bg);
  box-shadow: var(--shadow-sm);
  transition: transform 0.3s, border-color 0.3s;
}

.bubble-item span {
  font-size: 0.7rem;
  font-weight: 700;
  margin-top: 8px;
  color: var(--color-text-primary);
  line-height: 1.2;
}

.bubble-item:hover img {
  transform: rotate(10deg) scale(1.08);
  border-color: var(--color-secondary);
}

.btn-explore-collections {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--color-primary);
  border: none;
  background: none;
  border-bottom: 2px solid var(--color-secondary);
  padding-bottom: 3px;
  cursor: pointer;
}

.expert-chat-card {
  background-color: #ffffff;
  border-radius: 16px;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--color-border);
  overflow: hidden;
  text-align: left;
}

.expert-header {
  background-color: var(--color-primary);
  color: #ffffff;
  padding: 20px;
  text-align: center;
}

.expert-header h4 {
  font-size: 0.95rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  color: #f5e1a4;
  margin: 0;
}

.expert-header p {
  font-size: 0.75rem;
  opacity: 0.85;
  margin: 4px 0 0 0;
}

.expert-body {
  padding: 20px;
}

.chat-features {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 20px;
}

.chat-features li {
  font-size: 0.8rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.check-icon {
  color: #25d366;
  font-weight: 700;
  display: flex;
  align-items: center;
}

.btn-whatsapp-chat {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background-color: #25d366;
  color: #ffffff;
  padding: 12px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  box-shadow: 0 4px 10px rgba(37, 211, 102, 0.2);
  transition: background-color 0.2s, transform 0.2s;
}

.btn-whatsapp-chat:hover {
  background-color: #20ba59;
  transform: translateY(-2px);
}

.expert-phone-mock {
  background-color: var(--blush-bg);
  padding: 15px;
  border-top: 1px solid var(--color-border);
}

.mock-screen {
  background-color: #efeae2;
  border-radius: 8px;
  padding: 10px;
  box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
}

.mock-header {
  background-color: #075e54;
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 4px 8px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 10px;
}

.mock-chat-bubble {
  font-size: 0.75rem;
  padding: 6px 10px;
  border-radius: 6px;
  margin-bottom: 8px;
  max-width: 85%;
  line-height: 1.3;
}

.mock-chat-bubble.received {
  background-color: #ffffff;
  color: var(--color-text-primary);
  align-self: flex-start;
  text-align: left;
}

.mock-chat-bubble.sent {
  background-color: #dcf8c6;
  color: var(--color-text-primary);
  margin-left: auto;
  text-align: right;
}

/* 13. Follow Us & Become Insider Banner */
.social-insider-section {
  background-color: var(--blush-bg);
  padding: 80px 0;
}

.social-title {
  text-align: center;
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem;
  color: var(--color-primary);
  margin-bottom: 5px;
}

.social-handle {
  display: block;
  text-align: center;
  font-size: 0.9rem;
  color: var(--color-secondary);
  font-weight: 700;
  margin-top: -5px;
  margin-bottom: 30px;
  text-decoration: none;
  transition: opacity 0.2s;
}

.social-handle:hover {
  text-decoration: underline;
  opacity: 0.85;
}

.insta-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
  max-width: 1200px;
  margin: 0 auto 50px auto;
  padding: 0 20px;
}

.insta-item {
  position: relative;
  aspect-ratio: 1/1;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
}

.insta-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.insta-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(74, 14, 46, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  color: #ffffff;
  font-size: 1.8rem;
  transition: opacity 0.2s;
}

.insta-item:hover img {
  transform: scale(1.08);
}

.insta-item:hover .insta-overlay {
  opacity: 1;
}

.insider-block {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.insider-banner-card {
  background-color: var(--color-primary);
  border-radius: 16px;
  padding: 40px;
  color: #ffffff;
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 40px;
  align-items: center;
  border: 2px solid var(--color-secondary);
  box-shadow: var(--shadow-lg);
  text-align: left;
}

.insider-info h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: #f5e1a4;
  margin: 0 0 20px 0;
  letter-spacing: 1px;
}

.insider-perks {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 25px;
}

.perk {
  font-size: 0.9rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-insider-join {
  background-color: var(--color-secondary);
  color: var(--color-primary);
  padding: 14px 30px;
  border-radius: 30px;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 1px;
  transition: background-color 0.2s, transform 0.2s;
  box-shadow: 0 4px 15px rgba(197, 160, 89, 0.3);
  border: none;
  cursor: pointer;
  align-self: flex-start;
  max-width: fit-content;
}

.btn-insider-join:hover {
  background-color: #ffffff;
  color: var(--color-primary);
  transform: translateY(-2px);
}

.whatsapp-club-box {
  background-color: #ffffff;
  color: var(--color-text-primary);
  border-radius: 8px;
  padding: 30px;
  text-align: center;
  box-shadow: var(--shadow-md);
  position: relative;
  border: 1px solid var(--color-border);
}

.badge-discount {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #e21b5a;
  color: #ffffff;
  font-size: 0.75rem;
  font-weight: 800;
  padding: 4px 14px;
  border-radius: 20px;
  box-shadow: 0 2px 6px rgba(226, 27, 90, 0.3);
}

.whatsapp-club-box h5 {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-primary);
  margin-top: 5px;
  margin-bottom: 10px;
}

.whatsapp-club-box p {
  font-size: 0.8rem;
  color: var(--color-text-secondary);
  margin-bottom: 20px;
}

.whatsapp-club-form {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.club-input {
  flex: 1;
  padding: 10px 15px;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  font-size: 0.85rem;
  outline: none;
}

.club-submit-btn {
  background-color: var(--color-primary);
  color: #ffffff;
  padding: 10px 20px;
  font-size: 0.8rem;
  font-weight: 700;
  border-radius: 4px;
  transition: background-color 0.2s;
  border: none;
  cursor: pointer;
}

.club-submit-btn:hover {
  background-color: var(--color-primary-hover);
}

.privacy-note {
  font-size: 0.7rem;
  color: var(--color-text-secondary);
}

/* 15. Sizing & FAQs Accordions */
.faq-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

.accordion-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 30px;
}

.accordion-item {
  background-color: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.accordion-header {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 25px;
  background-color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--color-primary);
  text-align: left;
  transition: background-color 0.2s;
  border: none;
  cursor: pointer;
}

.accordion-header:hover {
  background-color: var(--blush-bg);
}

.icon-toggle {
  font-size: 0.9rem;
  color: var(--color-secondary);
  display: flex;
  align-items: center;
}

.accordion-body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease-out;
  background-color: #ffffff;
}

.accordion-body p {
  padding: 18px 25px;
  font-size: 0.85rem;
  color: var(--color-text-secondary);
  line-height: 1.5;
  margin: 0;
  text-align: left;
}

.accordion-item.active .accordion-body {
  max-height: 200px;
  border-top: 1px solid var(--color-border);
}

.btn-faq-all {
  background-color: transparent;
  color: var(--color-primary);
  border: 2px solid var(--color-primary);
  padding: 10px 25px;
  border-radius: 30px;
  font-size: 0.8rem;
  font-weight: 700;
  transition: background-color 0.2s, color 0.2s;
  cursor: pointer;
}

.btn-faq-all:hover {
  background-color: var(--color-primary);
  color: #ffffff;
}

/* Modals */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 10001;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s, visibility 0.3s;
}

.modal.active {
  opacity: 1;
  visibility: visible;
}

.modal-content {
  background-color: #ffffff;
  width: 90%;
  max-width: 550px;
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
  padding: 30px;
  position: relative;
  transform: translateY(20px);
  transition: transform 0.3s;
}

.modal.active .modal-content {
  transform: translateY(0);
}

.modal-close {
  position: absolute;
  top: 15px;
  right: 15px;
  font-size: 1.3rem;
  color: var(--color-text-secondary);
  background: none;
  border: none;
  cursor: pointer;
}

.modal-header h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.4rem;
  color: var(--color-primary);
  margin-bottom: 15px;
  border-bottom: 1px solid var(--color-border);
  padding-bottom: 10px;
  margin-top: 0;
  text-align: left;
}

.modal-body p {
  font-size: 0.9rem;
  color: var(--color-text-secondary);
  margin-bottom: 15px;
  line-height: 1.5;
  text-align: left;
}

.video-modal-content {
  max-width: 800px;
  padding: 10px;
  background-color: #000000;
}

.video-aspect-wrapper {
  position: relative;
  width: 100%;
  padding-top: 56.25%;
}

.mock-video-player {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #1a1a1a;
}

.mock-video-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #ffffff;
  gap: 15px;
}

.play-icon-pulse {
  color: var(--color-secondary);
  animation: pulse-icon-anim 1.5s infinite;
}

@keyframes pulse-icon-anim {
  0% { transform: scale(1); opacity: 0.8; }
  50% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(1); opacity: 0.8; }
}

/* ==========================================================================
   Responsive Breakpoints
   ========================================================================== */
@media (max-width: 1024px) {
  .hero-grid {
    grid-template-columns: 1fr;
    gap: 30px;
    min-height: auto;
    padding-bottom: 40px;
  }
  
  .hero-images-wrapper {
    order: 1;
    padding: 20px;
  }
  
  .hero-content {
    order: 2;
    padding: 0 20px;
    text-align: center;
  }
  
  .hero-desc {
    margin-left: auto;
    margin-right: auto;
  }
  
  .hero-trust-badges {
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
  }
  
  .occasion-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .story-grid {
    grid-template-columns: 1fr;
    gap: 30px;
  }
  
  .story-image-block {
    order: 1;
  }
  
  .story-content-block {
    order: 2;
    text-align: center;
  }
  
  .story-stats-block {
    order: 3;
    max-width: 500px;
    margin: 0 auto;
    width: 100%;
  }
  
  .values-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .dark-stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
  }
  
  .products-grid {
    padding: 10px 15px;
  }
  
  .media-reviews-grid {
    grid-template-columns: 1fr;
  }
  
  .mix-match-grid {
    grid-template-columns: 1fr;
  }
  
  .insta-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .insider-banner-card {
    grid-template-columns: 1fr;
    text-align: center !important;
    padding: 30px 16px !important;
  }

  .insider-info {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    text-align: center !important;
  }

  .insider-perks {
    align-items: center !important;
    text-align: center !important;
    width: 100% !important;
  }

  .perk {
    justify-content: center !important;
    text-align: center !important;
  }

  .btn-insider-join {
    align-self: center !important;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2.2rem;
  }
  
  .hero-main-card {
    width: 180px !important;
    height: 270px !important;
    margin: 0 !important;
    position: relative !important;
    left: 10px !important;
    right: auto !important;
    bottom: auto !important;
    transform: rotate(-3deg) !important;
    border-radius: 80px 80px 8px 8px !important;
    border: 4px solid #ffffff !important;
    z-index: 2 !important;
  }
  
  .hero-sub-card {
    width: 180px !important;
    height: 270px !important;
    margin: 0 !important;
    position: absolute !important;
    left: 130px !important;
    bottom: 20px !important;
    right: auto !important;
    transform: rotate(4deg) !important;
    border-radius: 80px 80px 8px 8px !important;
    border: 4px solid #ffffff !important;
    z-index: 1 !important;
  }

  .hero-images-wrapper {
    position: relative !important;
    width: 100% !important;
    max-width: 320px !important;
    margin: 0 auto !important;
    padding: 20px 0 !important;
    display: block !important;
  }

  .carousel-control {
    top: 50%;
    transform: translateY(-50%);
  }

  .carousel-control.prev {
    left: 12px;
  }

  .carousel-control.next {
    right: 12px;
  }
  
  .hero-trust-badges {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 12px 10px;
    margin-top: 25px;
    padding-top: 15px;
  }

  .trust-badge {
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .badge-icon {
    width: 30px;
    height: 30px;
    margin-bottom: 0;
    font-size: 0.8rem;
    flex-shrink: 0;
  }

  .trust-badge span {
    font-size: 0.65rem;
    text-align: left;
  }

  .ticker-metrics {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px 16px;
    font-size: 0.8rem;
  }

  .metric-item {
    padding: 0 4px;
    font-size: 0.8rem;
    gap: 5px;
  }

  .metric-divider {
    display: none;
  }
  
  .occasion-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .values-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .dark-stats-grid {
    grid-template-columns: repeat(2, 1fr);
    justify-items: center;
    gap: 20px 15px;
  }

  .dark-stat-item {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
  }

  .dark-stat-item h3,
  .dark-stat-item p {
    text-align: center !important;
  }

  .dark-stat-icon {
    margin-bottom: 8px;
  }
  
  .whatsapp-club-form {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .hero-content {
    padding: 0 15px;
  }

  .dark-stats-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 20px 10px;
  }

  .dark-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    padding: 12px 10px;
  }

  .dark-stat-icon {
    font-size: 2.6rem;
    margin-bottom: 4px;
  }

  .dark-stat-item h3 {
    font-size: 1.4rem;
    margin: 0;
    line-height: 1.1;
    text-align: center !important;
  }

  .dark-stat-item p {
    font-size: 0.85rem;
    margin: 0;
    color: var(--blush-bg);
    opacity: 0.95;
    text-align: center !important;
  }

  .hero-title {
    font-size: 1.85rem;
  }

  .hero-desc {
    font-size: 0.95rem;
    max-width: 100%;
  }

  .hero-trust-badges {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 12px 8px;
    margin-top: 20px;
    padding-top: 15px;
  }

  .trust-badge {
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }

  .badge-icon {
    width: 26px;
    height: 26px;
    margin-bottom: 0;
    font-size: 0.75rem;
    flex-shrink: 0;
  }

  .trust-badge span {
    font-size: 0.62rem;
    text-align: left;
  }

  .ticker-metrics {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px 12px;
    font-size: 0.75rem;
    margin-bottom: 0;
  }

  .metric-item {
    padding: 0 2px;
    font-size: 0.75rem;
    gap: 4px;
  }

  .metric-divider {
    display: none;
  }

  .hero-main-card {
    width: 160px !important;
    height: 240px !important;
    border-radius: 70px 70px 8px 8px !important;
    border: 3px solid #ffffff !important;
  }

  .hero-sub-card {
    width: 160px !important;
    height: 240px !important;
    left: 110px !important;
    bottom: 15px !important;
    border-radius: 70px 70px 8px 8px !important;
    border: 3px solid #ffffff !important;
  }

  .hero-images-wrapper {
    max-width: 320px !important;
  }

  .occasion-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 0 8px;
  }

  .occasion-name {
    font-size: 0.85rem;
    margin-bottom: 4px;
  }

  .btn-explore {
    font-size: 0.65rem;
  }

  .occasion-overlay {
    padding: 12px 6px;
  }

  .values-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px 8px;
    padding: 0 var(--spacing-sm);
  }

  .value-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    background-color: var(--blush-bg);
    border-radius: 40px;
    border: 1px solid var(--color-border);
  }

  .value-icon {
    width: 28px !important;
    height: 28px !important;
    font-size: 0.95rem !important;
    margin: 0 !important;
    flex-shrink: 0;
    background-color: #ffffff !important;
  }

  .value-item h4 {
    font-size: 0.72rem !important;
    margin-bottom: 0 !important;
    text-align: left;
    color: var(--color-text-primary);
  }

  .value-item p {
    display: none !important;
  }

  .live-activity-container {
    padding: 12px 10px;
  }

  .live-marquee-wrapper {
    max-width: 100%;
  }
}

/* ==========================================================================
   Storefront Reels Styles
   ========================================================================== */
.reels-container-wrapper {
  max-width: 1200px;
  margin: 0 auto 50px auto;
  padding: 0 20px;
  overflow: hidden;
}

.reels-scroll-row {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  padding-bottom: 15px;
  scroll-behavior: smooth;
  scrollbar-width: thin;
  scrollbar-color: var(--color-primary) transparent;
}

.reels-scroll-row::-webkit-scrollbar {
  height: 6px;
}
.reels-scroll-row::-webkit-scrollbar-track {
  background: transparent;
}
.reels-scroll-row::-webkit-scrollbar-thumb {
  background-color: rgba(74, 14, 46, 0.3);
  border-radius: 10px;
}
.reels-scroll-row::-webkit-scrollbar-thumb:hover {
  background-color: var(--color-primary);
}

.reel-card {
  flex: 0 0 calc(25% - 15px); /* 4 reels side-by-side on desktop */
  min-width: 220px;
  aspect-ratio: 9/16;
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  cursor: pointer;
  background-color: #000;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

@media (max-width: 1024px) {
  .reel-card {
    flex: 0 0 calc(33.333% - 14px); /* 3 reels on tablet */
    min-width: 200px;
  }
}

@media (max-width: 768px) {
  .reel-card {
    flex: 0 0 calc(55% - 10px); /* 1.8 reels visible on mobile */
    min-width: 160px;
  }
  .reels-container-wrapper {
    padding: 0 10px;
  }
}

.reel-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 20px rgba(74, 14, 46, 0.15);
}

.reel-video-wrapper {
  width: 100%;
  height: 100%;
  position: relative;
}

.reel-video-element,
.reel-youtube-element {
  width: 100%;
  height: 100%;
  object-fit: cover;
  pointer-events: none;
}

.reel-card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.1) 50%, rgba(0, 0, 0, 0.2) 100%);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 12px;
  box-sizing: border-box;
  opacity: 0.9;
  transition: opacity 0.3s ease;
}

.reel-card:hover .reel-card-overlay {
  opacity: 1;
}

.reel-icon-badge {
  align-self: flex-end;
  background: rgba(74, 14, 46, 0.85);
  color: #fff;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, 0.2);
  font-size: 0.8rem;
  box-shadow: var(--shadow-sm);
}

.reel-caption-overlay {
  color: #ffffff;
  font-size: 0.8rem;
  font-weight: 500;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
}

.full-modal-video {
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.reel-volume-btn:hover {
  transform: scale(1.1);
  background: var(--color-primary) !important;
  border-color: var(--color-secondary) !important;
}
</style>
