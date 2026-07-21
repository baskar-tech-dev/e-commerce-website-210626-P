<template>
  <section class="hero-section">

    <!-- ================================================================
         MOBILE HERO — Top Fully Rounded Arch Style Layout (≤767px)
         ================================================================ -->
    <div
      class="mh mobile-only"
      @touchstart.passive="onTouchStart"
      @touchend.passive="onTouchEnd"
    >
      <!-- DUAL ARCHED IMAGE SHOWCASE (≤767px) -->
      <div class="mh__showcase">
        <div
          v-for="(slide, idx) in slides"
          :key="idx"
          class="mh__slide"
          :class="{ 'is-active': currentSlide === idx }"
          :aria-hidden="currentSlide !== idx"
        >
          <div class="mh__images-wrapper">
            <div class="mh__main-card">
              <img
                :src="slide.leftImage || slide.banner_image || '/asset/banner-1-left.png'"
                :alt="slide.tag || 'Maya Sree Fashion'"
                class="mh__img"
              />
            </div>
            <div class="mh__sub-card">
              <img
                :src="slide.rightImage || '/asset/banner-1-right.png'"
                alt="Maya Sree Fashion Showcase"
                class="mh__img"
              />
            </div>
          </div>
        </div>

        <!-- Touch Controls -->
        <button class="mh__control prev" @click="prevSlide" aria-label="Previous Slide">
          <ChevronLeft :size="18" />
        </button>
        <button class="mh__control next" @click="nextSlide" aria-label="Next Slide">
          <ChevronRight :size="18" />
        </button>
      </div>

      <!-- Dot indicators -->
      <div v-if="slides.length > 1" class="mh__dots">
        <button
          v-for="(_, i) in slides"
          :key="i"
          class="mh__dot"
          :class="{ 'is-active': currentSlide === i }"
          @click="goToSlide(i)"
          :aria-label="`Slide ${i + 1}`"
        ></button>
      </div>

      <!-- TEXT CONTENT SECTION (Unboxed) -->
      <div class="mh__content-inner">
        <transition name="card-fade" mode="out-in">
          <div :key="currentSlide" class="mh__card-inner">
            <div class="mh__tag-wrap">
              <span class="mh__tag">{{ slides[currentSlide]?.tag || 'SOUTH INDIAN HERITAGE' }}</span>
            </div>
            <p class="mh__script" v-if="slides[currentSlide]?.script">
              {{ slides[currentSlide]?.script }} <span class="heart-icon">♡</span>
            </p>
            <h1
              class="mh__heading"
              v-html="slides[currentSlide]?.title || heroTitle"
            ></h1>
            <p class="mh__sub">
              {{ slides[currentSlide]?.desc || heroSubtitle }}
            </p>
            <router-link
              :to="slides[currentSlide]?.ctaLink || heroCtaLink"
              class="mh__cta"
            >
              {{ slides[currentSlide]?.ctaText || 'EXPLORE SHOP' }}
              <span class="mh__cta-arrow" aria-hidden="true">
                <ArrowRight :size="15" />
              </span>
            </router-link>
          </div>
        </transition>
      </div>
    </div>

    <!-- ================================================================
         DESKTOP HERO — Top Fully Rounded Arch Style Layout (≥768px)
         ================================================================ -->
    <div class="hero-carousel desktop-only">
      <div
        v-for="(slide, index) in slides"
        :key="index"
        class="hero-slide"
        :class="{ active: currentSlide === index }"
      >
        <div class="hero-grid">
          <!-- Left: Image showcase with top fully rounded arch style -->
          <div class="hero-images-container">
            <div class="hero-images-wrapper">
              <div class="hero-main-card">
                <img
                  :src="slide.leftImage || slide.banner_image || '/asset/banner-1-left.png'"
                  alt="Maya Sree Fashion Main Banner"
                  class="hero-img"
                />
              </div>
              <div class="hero-sub-card">
                <img
                  :src="slide.rightImage || '/asset/banner-1-right.png'"
                  alt="Maya Sree Fashion Showcase"
                  class="hero-img"
                />
                <div class="hero-circle-badge">
                  <div class="circle-thumb-frame">
                    <img :src="slide.circleThumb || '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg'" alt="Blouse Detail" />
                  </div>
                  <div class="badge-pill-labels">
                    <span class="pill-tag-top">{{ slide.thumbTag || 'ELASTIC WORK IN BOTTOM' }}</span>
                    <span class="pill-tag-bottom">{{ slide.thumbSub || 'Stretchable ReadyMade Blouse' }}</span>
                  </div>
                </div>
              </div>
              <button class="carousel-control prev" @click="prevSlide" aria-label="Previous Slide">
                <ChevronLeft :size="20" />
              </button>
              <button class="carousel-control next" @click="nextSlide" aria-label="Next Slide">
                <ChevronRight :size="20" />
              </button>
            </div>

            <!-- Desktop Pagination Dots -->
            <div v-if="slides.length > 1" class="hero-desktop-dots">
              <button
                v-for="(_, i) in slides"
                :key="i"
                class="h-dot"
                :class="{ active: currentSlide === i }"
                @click="goToSlide(i)"
                :aria-label="`Slide ${i + 1}`"
              ></button>
            </div>
          </div>

          <!-- Right: Text content -->
          <div class="hero-content">
            <div class="hero-tag-wrapper">
              <span class="hero-tag">{{ slide.tag || 'SOUTH INDIAN HERITAGE' }}</span>
            </div>
            <h2 class="hero-script">{{ slide.script || 'Handcrafted Perfection' }} <span class="heart-icon">♡</span></h2>
            <h1 class="hero-title" v-html="slide.title || heroTitle"></h1>
            <p class="hero-desc">{{ slide.desc || heroSubtitle }}</p>
            <div class="hero-cta-wrapper">
              <router-link :to="slide.ctaLink || heroCtaLink" class="btn-shop-now">
                {{ slide.ctaText || heroCtaText }} <ArrowRight :size="18" />
              </router-link>
            </div>
            <div class="hero-trust-badges">
              <div class="trust-badge" v-for="(b, i) in trustBadges" :key="i">
                <div class="badge-icon"><component :is="getIconComponent(b.icon)" :size="16" /></div>
                <span>{{ b.title }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { ChevronLeft, ChevronRight, ArrowRight, Gem, RotateCcw, Lock, Truck } from 'lucide-vue-next';
import { useCmsStore } from '../../stores/cms';

const cmsStore = useCmsStore();
const currentSlide  = ref(0);
let   slideTimer    = null;
let   touchStartX   = 0;

/* -------- CMS data -------- */
const heroTitle    = computed(() => cmsStore.hero?.hero_title    || 'A Legacy of Love,<br>Woven into Every Thread');
const heroSubtitle = computed(() => cmsStore.hero?.hero_subtitle || 'Handcrafted South Indian sarees & designer blouses for every occasion.');
const heroCtaText  = computed(() => cmsStore.hero?.hero_cta_text || 'SHOP THE COLLECTION');
const heroCtaLink  = computed(() => cmsStore.hero?.hero_cta_link || '/shop');

const slides = computed(() => {
  if (cmsStore.hero?.hero_slides?.length) return cmsStore.hero.hero_slides;
  return [
    {
      leftImage : '/asset/banner-1-left.png',
      rightImage: '/asset/banner-1-right.png',
      tag       : 'EXCLUSIVE SOUTH INDIAN HERITAGE',
      script    : 'Handcrafted Perfection',
      title     : 'Timeless Elegance,<br><span class="gradient-text">South Indian Craftsmanship</span>',
      desc      : 'Graceful silk sarees, breathable everyday kurtis, and stretchable ready-made blouses tailored for supreme comfort.',
      ctaText   : 'EXPLORE SHOP',
      ctaLink   : '/shop',
    },
    {
      leftImage : '/asset/banner-2-left.png',
      rightImage: '/asset/banner-2-right.png',
      tag       : 'FESTIVAL & WEDDING SPECIAL',
      script    : 'Royal Heritage',
      title     : 'Grand Festive Weaves<br><span class="gradient-text">& Bridal Blouses</span>',
      desc      : 'Radiate timeless allure during weddings and celebrations with our rich zari borders and flawless fits.',
      ctaText   : 'SHOP OCCASIONS',
      ctaLink   : '/shop?category=wedding',
    },
  ];
});

const trustBadges = computed(() => cmsStore.trustBadges?.length ? cmsStore.trustBadges : [
  { icon: 'Gem',       title: 'PREMIUM QUALITY' },
  { icon: 'RotateCcw', title: 'EASY 7-DAY RETURNS' },
  { icon: 'Lock',      title: '100% SECURE PAYMENT' },
  { icon: 'Truck',     title: 'EXPRESS DELIVERY' },
]);

const getIconComponent = (n) =>
  n === 'RotateCcw' ? RotateCcw : n === 'Lock' ? Lock : n === 'Truck' ? Truck : Gem;

/* -------- Carousel logic -------- */
const goToSlide = (i) => { currentSlide.value = i; resetTimer(); };
const nextSlide = ()  => { currentSlide.value = (currentSlide.value + 1) % slides.value.length; resetTimer(); };
const prevSlide = ()  => { currentSlide.value = (currentSlide.value - 1 + slides.value.length) % slides.value.length; resetTimer(); };

const resetTimer = () => {
  clearInterval(slideTimer);
  slideTimer = setInterval(nextSlide, 6500);
};

/* -------- Touch / swipe -------- */
const onTouchStart = (e) => { touchStartX = e.changedTouches[0].screenX; };
const onTouchEnd   = (e) => {
  const diff = touchStartX - e.changedTouches[0].screenX;
  if (Math.abs(diff) > 44) diff > 0 ? nextSlide() : prevSlide();
};

onMounted(()    => { slideTimer = setInterval(nextSlide, 6500); });
onUnmounted(()  => { clearInterval(slideTimer); });
</script>

<style scoped>
/* ------------------------------------------------------------------ */
/* SHARED UTILITY                                                       */
/* ------------------------------------------------------------------ */
.mobile-only  { display: none; }
.desktop-only { display: block; }

/* ------------------------------------------------------------------ */
/* DESKTOP HERO  (unchanged from original)                             */
/* ------------------------------------------------------------------ */
.hero-section {
  background: linear-gradient(135deg, #fffcf7 0%, #fcf7f2 100%);
  border-bottom: 1px solid #f1e6df;
  padding: 40px 24px;
  overflow: hidden;
  min-height: 560px;
  box-sizing: border-box;
}
.hero-carousel {
  position: relative;
  max-width: 1400px;
  min-height: 480px;
  margin: 0 auto;
}
.hero-slide {
  position: absolute; top: 0; left: 0;
  width: 100%; height: 100%;
  opacity: 0; visibility: hidden; pointer-events: none;
  transition: opacity .6s ease, visibility .6s ease;
  z-index: 1;
}
.hero-slide.active {
  position: relative;
  opacity: 1; visibility: visible; pointer-events: auto;
  z-index: 2;
}
.hero-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: center;
}
.hero-images-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  width: 100%;
}
.hero-images-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  max-width: 580px;
}
.hero-main-card {
  position: relative;
  z-index: 3;
  width: 55%;
  height: 475px;
  border-radius: 240px 240px 24px 24px;
  overflow: hidden;
  border: 4px solid #f4e8dc;
  box-shadow: 0 18px 40px rgba(74, 14, 46, 0.16);
  background: #fdfaf7;
  transition: transform 0.4s ease;
}
.hero-sub-card {
  position: relative;
  z-index: 2;
  width: 48%;
  height: 415px;
  margin-left: -22px;
  border-radius: 200px 200px 24px 24px;
  overflow: hidden;
  border: 4px solid #f4e8dc;
  box-shadow: 0 12px 30px rgba(74, 14, 46, 0.12);
  background: #fdfaf7;
  transition: transform 0.4s ease;
}

/* Detail Circle Badge Overlay */
.hero-circle-badge {
  position: absolute;
  bottom: 12px;
  left: 12px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
  z-index: 5;
}
.circle-thumb-frame {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  border: 2px solid #ffffff;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  background: #ffffff;
}
.circle-thumb-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.badge-pill-labels {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 2px;
}
.pill-tag-top {
  background: #046307;
  color: #ffffff;
  font-size: 0.58rem;
  font-weight: 800;
  letter-spacing: 0.5px;
  padding: 2px 7px;
  border-radius: 4px;
  text-transform: uppercase;
  box-shadow: 0 2px 5px rgba(0,0,0,0.18);
  font-family: 'Poppins', sans-serif;
}
.pill-tag-bottom {
  background: #ffffff;
  color: #1e293b;
  font-size: 0.6rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 4px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.18);
  font-family: 'Poppins', sans-serif;
}
.hero-img { width: 100%; height: 100%; object-fit: cover; display: block; }

/* Desktop Controls */
.carousel-control {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
  border: none;
}
.carousel-control.prev {
  left: -18px;
  background: #ffffff;
  color: #2d1420;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
  border: 1px solid #f1e6df;
}
.carousel-control.prev:hover {
  background: #4a0e2e;
  color: #ffffff;
}
.carousel-control.next {
  right: -18px;
  background: #350920;
  color: #ffffff;
  box-shadow: 0 6px 18px rgba(53, 9, 32, 0.3);
}
.carousel-control.next:hover {
  background: #4a0e2e;
  transform: translateY(-50%) scale(1.05);
}

/* Desktop Pagination Dots */
.hero-desktop-dots {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 6px;
}
.h-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #d8cbd0;
  border: none;
  cursor: pointer;
  padding: 0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.h-dot.active {
  width: 22px;
  border-radius: 4px;
  background: #4a0e2e;
}
.hero-content { display: flex; flex-direction: column; align-items: flex-start; }
.hero-tag-wrapper { margin-bottom: 12px; }
.hero-tag {
  background: #4a0e2e; color: #d4af37;
  padding: 6px 16px; border-radius: 20px;
  font-size: .75rem; font-weight: 700;
  letter-spacing: 1px; text-transform: uppercase;
}
.hero-script {
  font-family: 'Playfair Display', serif;
  font-style: italic; font-size: 1.3rem;
  color: #4a0e2e; margin: 0 0 8px;
}
.heart-icon { color: #d4af37; }
.hero-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.8rem; font-weight: 800;
  color: #4a0e2e; line-height: 1.2; margin: 0 0 16px;
}
:deep(.gradient-text), :deep(.grad) {
  background: linear-gradient(135deg, #d4af37 0%, #aa7c11 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  display: inline-block;
}
.hero-desc {
  font-family: 'Poppins', sans-serif;
  font-size: 1.05rem; color: #475569;
  line-height: 1.6; max-width: 540px; margin: 0 0 28px;
}
.btn-shop-now {
  background: linear-gradient(135deg,#4a0e2e 0%,#350920 100%);
  color: #fff; padding: 14px 34px; border-radius: 30px;
  font-weight: 700; font-size: .95rem; letter-spacing: .5px;
  box-shadow: 0 8px 20px rgba(74,14,46,.25);
  border: 1px solid #d4af37; transition: all .3s;
  display: inline-flex; align-items: center; gap: 10px;
  text-decoration: none;
}
.btn-shop-now:hover { transform: translateY(-2px); color: #d4af37; }
.hero-trust-badges {
  display: grid; grid-template-columns: repeat(4,1fr);
  gap: 16px; margin-top: 36px; padding-top: 24px;
  border-top: 1px dashed #e2e8f0; width: 100%;
}
.trust-badge {
  display: flex; align-items: center; gap: 8px;
  font-size: .75rem; font-weight: 700; color: #334155; letter-spacing: .5px;
}
.badge-icon {
  width: 32px; height: 32px; border-radius: 50%;
  background: #fffcf7; border: 1px solid #d4af37;
  display: flex; align-items: center; justify-content: center;
  color: #4a0e2e; flex-shrink: 0;
}

/* ================================================================== */
/* MOBILE HERO — Top Fully Rounded Arch Style Layout (≤767px)         */
/* ================================================================== */
@media (max-width: 767px) {

  .mobile-only  { display: block; }
  .desktop-only { display: none;  }

  .hero-section {
    padding: 0;
    background: #fffcf7;
    border-bottom: none;
    min-height: unset;
    overflow: visible;
  }

  .mh {
    width: 100%;
    background: #fffcf7;
    position: relative;
    padding: 16px 12px 24px;
    box-sizing: border-box;
    overflow: hidden;
  }

  /* ── MOBILE ARCHED IMAGE SHOWCASE ───────────────── */
  .mh__showcase {
    position: relative;
    width: 100%;
    min-height: 380px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
  }

  .mh__slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.5s ease, visibility 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .mh__slide.is-active {
    position: relative;
    opacity: 1;
    visibility: visible;
  }

  .mh__images-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 440px;
  }

  .mh__main-card {
    position: relative;
    z-index: 3;
    width: 56%;
    height: 360px;
    border-radius: 175px 175px 22px 22px;
    overflow: hidden;
    border: 3px solid #f4e8dc;
    box-shadow: 0 12px 28px rgba(74, 14, 46, 0.16);
    background: #fdfaf7;
  }

  .mh__sub-card {
    position: relative;
    z-index: 2;
    width: 49%;
    height: 315px;
    margin-left: -22px;
    border-radius: 150px 150px 22px 22px;
    overflow: hidden;
    border: 3px solid #f4e8dc;
    box-shadow: 0 10px 24px rgba(74, 14, 46, 0.12);
    background: #fdfaf7;
  }

  .mh__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  /* Mobile Carousel Controls */
  .mh__control {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    border: none;
    transition: all 0.2s ease;
  }

  .mh__control.prev {
    left: -4px;
    background: #ffffff;
    color: #2d1420;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: 1px solid #f1e6df;
  }

  .mh__control.next {
    right: -4px;
    background: #350920;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(53, 9, 32, 0.3);
  }

  /* Mobile Dots */
  .mh__dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 4px;
    margin-bottom: 10px;
    z-index: 5;
  }

  .mh__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #d8cbd0;
    border: none;
    padding: 0;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .mh__dot.is-active {
    width: 18px;
    border-radius: 4px;
    background: #4a0e2e;
  }

  /* Mobile Content Section Inside Box */
  .mh__content-inner {
    width: 100%;
    text-align: center;
    padding-top: 4px;
  }

  .mh__card-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }

  .mh__tag-wrap {
    margin-bottom: 2px;
  }

  .mh__tag {
    background: #4a0e2e;
    color: #d4af37;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    display: inline-block;
  }

  .mh__script {
    font-family: 'Playfair Display', serif;
    font-style: italic;
    font-size: 0.95rem;
    color: #4a0e2e;
    margin: 0;
  }

  .mh__heading {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1.25;
    color: #2d1420;
    margin: 0;
  }

  :deep(.mh__heading .grad),
  :deep(.mh__heading .gradient-text) {
    background: linear-gradient(120deg, #4a0e2e 0%, #b59226 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .mh__sub {
    font-family: 'Poppins', sans-serif;
    font-size: 0.82rem;
    color: #64748b;
    line-height: 1.5;
    margin: 0;
  }

  .mh__cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
    padding: 11px 24px;
    background: linear-gradient(135deg, #4a0e2e 0%, #350920 100%);
    color: #ffffff;
    font-family: 'Poppins', sans-serif;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    text-decoration: none;
    border-radius: 24px;
    border: 1px solid #d4af37;
    box-shadow: 0 4px 14px rgba(74, 14, 46, 0.2);
  }

  .mh__cta-arrow {
    display: flex;
    align-items: center;
    color: #d4af37;
  }

}

/* ── Tablet: keep desktop columns but single-col stack ── */
@media (min-width: 768px) and (max-width: 992px) {
  .hero-grid { grid-template-columns: 1fr; gap: 32px; text-align: center; }
  .hero-content { align-items: center; }
  .hero-title { font-size: 2rem; }
  .hero-desc  { font-size: .95rem; }
  .hero-sub-card { display: none; }
  .hero-trust-badges { grid-template-columns: repeat(2,1fr); gap: 12px; }
}
</style>
