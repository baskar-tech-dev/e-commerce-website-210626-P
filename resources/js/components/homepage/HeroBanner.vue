<template>
  <section class="hero-section">

    <!-- ================================================================
         MOBILE HERO — Editorial luxury layout (≤767px)
         Image occupies ~68% viewport. Text in cream card below.
         ================================================================ -->
    <div
      class="mh mobile-only"
      @touchstart.passive="onTouchStart"
      @touchend.passive="onTouchEnd"
    >

      <!-- IMAGE STAGE ------------------------------------------------- -->
      <div class="mh__stage">

        <!-- Slides -->
        <div
          v-for="(slide, idx) in slides"
          :key="idx"
          class="mh__slide"
          :class="{ 'is-active': currentSlide === idx }"
          :aria-hidden="currentSlide !== idx"
        >
          <img
            :src="slide.leftImage || slide.banner_image || '/asset/banner-1-left.png'"
            :alt="slide.tag || 'Maya Sree Fashion'"
            class="mh__image"
            loading="lazy"
            draggable="false"
          />
        </div>

        <!-- Very subtle bottom vignette — not a heavy overlay -->
        <div class="mh__vignette" aria-hidden="true"></div>

        <!-- Dot indicators — top-right, inside image -->
        <div
          v-if="slides.length > 1"
          class="mh__dots"
          role="tablist"
          aria-label="Slide navigation"
        >
          <button
            v-for="(_, i) in slides"
            :key="i"
            class="mh__dot"
            :class="{ 'is-active': currentSlide === i }"
            @click="goToSlide(i)"
            :aria-label="`Slide ${i + 1}`"
            :aria-selected="currentSlide === i"
            role="tab"
          ></button>
        </div>
      </div>

      <!-- TEXT CARD ---------------------------------------------------- -->
      <!-- Floats slightly over the bottom of the image via negative margin -->
      <div class="mh__card">
        <!-- Transition wrapper for text content -->
        <transition name="card-fade" mode="out-in">
          <div :key="currentSlide" class="mh__card-inner">

            <!-- Collection badge -->
            <p class="mh__badge">
              <span class="mh__badge-line"></span>
              {{ slides[currentSlide]?.tag || 'SOUTH INDIAN HERITAGE' }}
              <span class="mh__badge-line"></span>
            </p>

            <!-- Headline — serif, strong -->
            <h1
              class="mh__heading"
              v-html="slides[currentSlide]?.title || heroTitle"
            ></h1>

            <!-- Description — one concise line -->
            <p class="mh__sub">
              {{ slides[currentSlide]?.desc || heroSubtitle }}
            </p>

            <!-- CTA -->
            <router-link
              :to="slides[currentSlide]?.ctaLink || heroCtaLink"
              class="mh__cta"
            >
              {{ slides[currentSlide]?.ctaText || 'Explore Collection' }}
              <span class="mh__cta-arrow" aria-hidden="true">
                <ArrowRight :size="15" />
              </span>
            </router-link>
          </div>
        </transition>
      </div>
    </div>

    <!-- ================================================================
         DESKTOP HERO — Unchanged two-column grid (≥768px)
         ================================================================ -->
    <div class="hero-carousel desktop-only">
      <div
        v-for="(slide, index) in slides"
        :key="index"
        class="hero-slide"
        :class="{ active: currentSlide === index }"
      >
        <div class="hero-grid">
          <!-- Left: Image showcase -->
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
            </div>
            <button class="carousel-control prev" @click="prevSlide" aria-label="Previous Slide">
              <ChevronLeft :size="20" />
            </button>
            <button class="carousel-control next" @click="nextSlide" aria-label="Next Slide">
              <ChevronRight :size="20" />
            </button>
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
.hero-images-wrapper {
  position: relative;
  display: flex; gap: 16px;
  align-items: center; justify-content: center;
}
.hero-main-card {
  flex: 1; border-radius: 20px; overflow: hidden;
  box-shadow: 0 15px 35px rgba(74,14,46,.12);
  border: 2px solid #f1e6df; height: 460px;
}
.hero-sub-card {
  width: 45%; border-radius: 16px; overflow: hidden;
  box-shadow: 0 10px 25px rgba(74,14,46,.1);
  border: 2px solid #f1e6df; height: 360px;
}
.hero-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.carousel-control {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(255,255,255,.85); border: 1px solid #e2e8f0;
  color: #4a0e2e; width: 40px; height: 40px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,.1);
  transition: all .2s; z-index: 10;
}
.carousel-control:hover { background: #4a0e2e; color: #fff; }
.carousel-control.prev { left: -12px; }
.carousel-control.next { right: -12px; }
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
/* MOBILE HERO  — Editorial luxury layout                              */
/* ================================================================== */
@media (max-width: 767px) {

  .mobile-only  { display: block; }
  .desktop-only { display: none;  }

  /* Strip section padding — hero manages its own spacing */
  .hero-section {
    padding: 0;
    background: #fffcf7;
    border-bottom: none;
    min-height: unset;
    overflow: visible;
  }

  /* ── Outer wrapper — bleeds past container padding ── */
  .mh {
    width: calc(100% + 32px);
    margin-left: -16px;
    margin-right: -16px;
    margin-top: -24px;
    background: #fffcf7;
    position: relative;
    user-select: none;
  }

  /* ── IMAGE STAGE ─────────────────────────────────── */
  .mh__stage {
    position: relative;
    width: 100%;
    height: 62vw;            /* ~62% of viewport width ≈ 68–72% vh on most phones */
    min-height: 320px;
    max-height: 520px;
    overflow: hidden;
    border-radius: 0 0 28px 28px;
    background: #f5ece4;
  }

  /* Each slide */
  .mh__slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity .95s cubic-bezier(.4,0,.2,1);
  }
  .mh__slide.is-active {
    opacity: 1;
    z-index: 1;
  }

  /* The fashion photo */
  .mh__image {
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: center top;
    display: block;
    will-change: transform;
    animation: kenBurns 8s ease-in-out infinite alternate;
  }
  /* Pause animation on inactive slides */
  .mh__slide:not(.is-active) .mh__image {
    animation: none;
  }

  @keyframes kenBurns {
    from { transform: scale(1);    object-position: center 20%; }
    to   { transform: scale(1.06); object-position: center 30%; }
  }

  /* Subtle bottom vignette — only lowest 30% of image */
  .mh__vignette {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 40%;
    background: linear-gradient(
      to bottom,
      transparent 0%,
      rgba(255,252,247,.18) 60%,
      rgba(255,252,247,.72) 100%
    );
    pointer-events: none;
    z-index: 2;
  }

  /* Elegant dots — bottom centre of stage */
  .mh__dots {
    position: absolute;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    z-index: 5;
  }

  /* Each dot is an accessible button with visual dot via ::after */
  .mh__dot {
    width: 44px; height: 44px;           /* 44×44 touch target */
    background: transparent; border: none;
    cursor: pointer; padding: 0;
    display: flex; align-items: center; justify-content: center;
    -webkit-tap-highlight-color: transparent;
  }
  .mh__dot::after {
    content: '';
    display: block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: rgba(74,14,46,.28);
    transition: all .35s cubic-bezier(.4,0,.2,1);
  }
  .mh__dot.is-active::after {
    background: #4a0e2e;
    width: 22px;
    border-radius: 4px;
  }

  /* ── TEXT CARD ───────────────────────────────────── */
  /*  Floats 24px over the bottom rounded edge of the stage */
  .mh__card {
    position: relative;
    z-index: 10;
    margin-top: -24px;
    margin-left: 12px;
    margin-right: 12px;
    background: #ffffff;
    border-radius: 20px;
    padding: 24px 20px 28px;
    box-shadow:
      0 -4px 20px rgba(74,14,46,.05),
      0  8px 32px rgba(74,14,46,.08);
    border: 1px solid rgba(212,175,55,.18);
    min-height: 170px;
  }

  /* Fade-in transition when slide changes */
  .card-fade-enter-active { transition: opacity .4s ease, transform .4s ease; }
  .card-fade-leave-active { transition: opacity .25s ease, transform .25s ease; }
  .card-fade-enter-from   { opacity: 0; transform: translateY(8px); }
  .card-fade-leave-to     { opacity: 0; transform: translateY(-6px); }

  .mh__card-inner {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  /* Collection badge */
  .mh__badge {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Poppins', sans-serif;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #9c8a94;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .mh__badge-line {
    display: inline-block;
    height: 1px;
    flex: 1;
    background: linear-gradient(90deg, transparent, #d4af37, transparent);
    opacity: .55;
    min-width: 20px;
    max-width: 36px;
  }

  /* Serif headline */
  .mh__heading {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.25;
    color: #2d1420;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Gradient text inside headline */
  :deep(.mh__heading .grad),
  :deep(.mh__heading .gradient-text) {
    background: linear-gradient(120deg, #4a0e2e 0%, #b59226 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  /* Description */
  .mh__sub {
    font-family: 'Poppins', sans-serif;
    font-size: .80rem;
    color: #72626a;
    line-height: 1.55;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* CTA Button */
  .mh__cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    align-self: flex-start;
    margin-top: 4px;
    padding: 13px 24px;
    min-height: 48px;
    background: #4a0e2e;
    color: #f5e1a4;
    font-family: 'Poppins', sans-serif;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    text-decoration: none;
    border-radius: 30px;
    border: 1px solid rgba(212,175,55,.45);
    box-shadow: 0 6px 18px rgba(74,14,46,.22);
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    -webkit-tap-highlight-color: transparent;
  }
  .mh__cta:active {
    transform: scale(.97);
    box-shadow: 0 2px 8px rgba(74,14,46,.2);
  }

  .mh__cta-arrow {
    display: flex;
    align-items: center;
    color: rgba(245,225,164,.8);
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
