<template>
  <div class="about-page" style="background-color: var(--color-bg); min-height: 100vh;">
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
      <router-link to="/" class="breadcrumb-link">Home</router-link>
      <span class="breadcrumb-separator">/</span>
      <span class="breadcrumb-current">About Us</span>
    </div>

    <!-- 1. Hero Section -->
    <section class="about-hero">
      <div class="about-hero__bg" style="background-image: url('/asset/about/about_hero.png')"></div>
      <div class="about-hero__overlay"></div>
      <div class="about-hero__content">
        <span class="about-hero__tagline">Since 2017</span>
        <h1 class="about-hero__title">A Legacy of Love,<br>Woven into Every Thread</h1>
        <p class="about-hero__subtitle">
          From a mother’s dream to a global tribute to South Indian heritage. We craft comfortable, premium garments designed for modern families who cherish elegance and quality.
        </p>
        <router-link to="/shop" class="about-btn about-btn--primary">
          Shop the Collection
        </router-link>
      </div>
    </section>

    <div class="about-container">
      <!-- 2. Who We Are -->
      <section class="about-section who-we-are">
        <div class="who-we-are__grid">
          <div class="who-we-are__text reveal">
            <span class="section-tag">Who We Are</span>
            <h2 class="section-title">Crafting the Modern Heritage of South India</h2>
            <p class="section-paragraph">
              Maya Sree Fashion is an elegant, family-focused apparel brand born in Erode, with our main studio in Tirupur. We specialize in high-quality traditional sarees, comfort-first kurtis, and our signature stretchable readymade blouses.
            </p>
            <p class="section-paragraph">
              By marrying time-honored weaving traditions with modern fits, we create clothes that feel like a second skin, allowing you to celebrate your roots without compromising on comfort.
            </p>
          </div>
          <div class="who-we-are__image reveal">
            <img :src="'/asset/about/about_family.png'" alt="Maya Sree Family Heritage" class="img-responsive about-img" loading="lazy">
          </div>
        </div>
      </section>

      <!-- 3. Our Story -->
      <section class="about-section our-story reveal">
        <div class="our-story__content">
          <span class="section-tag section-tag--center">Our Story</span>
          <h2 class="section-title text-center">The Dream of a Mother, The Trust of a Family</h2>
          
          <div class="story-timeline">
            <div class="story-card">
              <h3 class="story-card__title">A Gift for Tomorrow</h3>
              <p class="story-card__text">
                Maya Sree Fashion was founded by Mrs. Archana Ayyapparaj. It began not just as a business, but as a heartfelt gift to her child—a legacy built on love, purpose, and the timeless beauty of textiles.
              </p>
            </div>
            
            <div class="story-card">
              <h3 class="story-card__title">Tirupur & Erode's Textile Soul</h3>
              <p class="story-card__text">
                Rooted in Erode and Tirupur, the textile capitals of South Indian fashion, our brand bloomed from a small home boutique into an international destination for premium ethnic wear.
              </p>
            </div>
            
            <div class="story-card">
              <h3 class="story-card__title">Crafted to International Standards</h3>
              <p class="story-card__text">
                Every single piece is designed and tailored with meticulous care, combining traditional South Indian weaving art with contemporary precision to ensure unmatched comfort and lasting elegance.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- 4. Mission & Vision -->
      <section class="about-section mission-vision reveal">
        <div class="mission-vision__grid">
          <div class="mv-card">
            <div class="mv-card__icon">
              <Target :size="48" style="color: var(--color-primary);" />
            </div>
            <h3 class="mv-card__title">Our Mission</h3>
            <p class="mv-card__text">
              Preserving South Indian textile heritage by creating exceptionally comfortable, high-quality, and premium ethnic wear that is accessible to families worldwide.
            </p>
          </div>
          <div class="mv-card">
            <div class="mv-card__icon">
              <Compass :size="48" style="color: var(--color-primary);" />
            </div>
            <h3 class="mv-card__title">Our Vision</h3>
            <p class="mv-card__text">
              To become the global standard for modern South Indian fashion, celebrated for our innovation in comfort, premium fabric selections, and absolute trust.
            </p>
          </div>
        </div>
      </section>

      <!-- 5. Why Choose Maya Sree Fashion -->
      <section class="about-section why-choose reveal">
        <span class="section-tag section-tag--center">Our Value Proposition</span>
        <h2 class="section-title text-center">Why Families Choose Maya Sree</h2>
        
        <div class="why-grid">
          <div class="why-card">
            <div class="why-card__num">01</div>
            <h4 class="why-card__title">Premium Quality Fabrics</h4>
            <p class="why-card__text">Sourced from India's finest mills, ensuring breathable, soft, and long-lasting everyday wear.</p>
          </div>
          <div class="why-card">
            <div class="why-card__num">02</div>
            <h4 class="why-card__title">Trend-Inspired Collections</h4>
            <p class="why-card__text">Melding classic South Indian silhouettes with contemporary details for a fresh aesthetic.</p>
          </div>
          <div class="why-card">
            <div class="why-card__num">03</div>
            <h4 class="why-card__title">Comfort-First Engineering</h4>
            <p class="why-card__text">Specialized fits and stretchable materials engineered to move naturally with your body.</p>
          </div>
          <div class="why-card">
            <div class="why-card__num">04</div>
            <h4 class="why-card__title">Fair & Accessible Pricing</h4>
            <p class="why-card__text">Genuine luxury craftsmanship and materials made available without the premium price markup.</p>
          </div>
          <div class="why-card">
            <div class="why-card__num">05</div>
            <h4 class="why-card__title">Responsible Sourcing</h4>
            <p class="why-card__text">Committed to ethical manufacturing and directly supporting local handloom weaving clusters.</p>
          </div>
          <div class="why-card">
            <div class="why-card__num">06</div>
            <h4 class="why-card__title">Unconditional Trust</h4>
            <p class="why-card__text">Enjoy responsive customer service, 100% secure payments, and standard-setting post-purchase care.</p>
          </div>
        </div>
      </section>

      <!-- 6. Our Commitment -->
      <section class="about-section commitment reveal">
        <div class="commitment__banner">
          <div class="commitment__content">
            <span class="section-tag">Our Promise</span>
            <h2 class="section-title">Our Promise to You</h2>
            <p class="section-paragraph">
              Every garment we create is a promise of quality. We work directly with weavers and artisans, ensuring fair wages and ethical working conditions. From the initial sketch to the final quality control inspection, we obsess over the details so you can wear our collections with absolute confidence and pride.
            </p>
          </div>
        </div>
      </section>

      <!-- 7. Our Numbers -->
      <section class="about-section stats reveal">
        <div class="stats__grid">
          <div class="stat-item">
            <span class="stat-item__num">15,000+</span>
            <span class="stat-item__label">Families Served Globally</span>
            <p class="stat-item__desc">Spreading the warmth of South Indian fashion to homes across the world.</p>
          </div>
          <div class="stat-item">
            <span class="stat-item__num">500+</span>
            <span class="stat-item__label">Original Premium Designs</span>
            <p class="stat-item__desc">Curated collections ranging from daily comfort wear to festive heritage silhouettes.</p>
          </div>
          <div class="stat-item">
            <span class="stat-item__num">100%</span>
            <span class="stat-item__label">South Indian Soul</span>
            <p class="stat-item__desc">Designed in Tirupur, keeping traditional craftsmanship alive.</p>
          </div>
        </div>
      </section>

      <!-- 8. Founder Message -->
      <section class="about-section founder-message reveal">
        <div class="founder-card">
          <div class="founder-card__quote-icon">“</div>
          <blockquote class="founder-card__quote">
            Maya Sree Fashion was born out of a desire to create something beautiful, lasting, and meaningful for my child. Every fabric we choose, every design we refine, and every package we send is infused with that same motherly love and devotion. We aren't just selling clothes; we are sharing a piece of our heart with your family.
          </blockquote>
          <div class="founder-card__author">
            <span class="founder-card__name">Mrs. Archana Ayyapparaj</span>
            <span class="founder-card__role">Founder, Maya Sree Fashion</span>
          </div>
        </div>
      </section>

      <!-- 9. Final CTA -->
      <section class="about-section final-cta reveal">
        <div class="cta-banner">
          <h2 class="cta-banner__title">Ready to Experience Premium South Indian Fashion?</h2>
          <p class="cta-banner__subtitle">
            Explore our signature collection of stretchable readymade blouses, heritage sarees, and comfortable everyday wear.
          </p>
          <router-link to="/shop" class="about-btn about-btn--secondary">
            Explore Collections
          </router-link>
        </div>
      </section>

    </div>
  </div>
</template>

<script setup>
// SEO Setup
import { onMounted, onBeforeUnmount } from 'vue';
import { Target, Compass } from 'lucide-vue-next';

let revealOnScroll;

onMounted(() => {
  document.title = "About Us - Maya Sree South Indian Fashion";
  const metaDescription = document.querySelector('meta[name="description"]');
  if (metaDescription) {
    metaDescription.setAttribute("content", "Discover the story, mission, values, and cultural heritage of Maya Sree South Indian Fashion, based in Tirupur.");
  }

  // Simple Scroll Reveal Animation logic
  const reveals = document.querySelectorAll('.reveal');
  revealOnScroll = () => {
    const windowHeight = window.innerHeight;
    reveals.forEach(element => {
      const elementTop = element.getBoundingClientRect().top;
      const elementVisible = 100;
      if (elementTop < windowHeight - elementVisible) {
        element.classList.add('active');
      }
    });
  };

  window.addEventListener('scroll', revealOnScroll);
  // Trigger once initially
  revealOnScroll();
});

onBeforeUnmount(() => {
  if (revealOnScroll) {
    window.removeEventListener('scroll', revealOnScroll);
  }
});
</script>

<style scoped>
/* Scoped Styling for Premium About Us Page using 8-point system */

.about-page {
  color: var(--color-text-primary);
  font-family: var(--font-family-base);
}

/* Breadcrumbs */
.breadcrumbs {
  padding: 1.5rem 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: var(--font-family-base);
  font-size: 0.85rem;
  color: var(--color-text-secondary);
}

.breadcrumb-link {
  color: var(--color-text-secondary);
  text-decoration: none;
  transition: color var(--transition-fast);
}

.breadcrumb-link:hover {
  color: var(--color-primary);
}

.breadcrumb-separator {
  margin: 0 8px;
}

.breadcrumb-current {
  color: var(--color-primary);
  font-weight: 500;
}

/* Hero Section */
.about-hero {
  position: relative;
  height: 60vh;
  min-height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #ffffff;
  overflow: hidden;
  margin-bottom: 3.5rem;
}

.about-hero__bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  transition: transform 6s ease;
}

.about-hero:hover .about-hero__bg {
  transform: scale(1.05);
}

.about-hero__overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(180deg, rgba(74, 14, 46, 0.45) 0%, rgba(74, 14, 46, 0.75) 100%);
}

.about-hero__content {
  position: relative;
  z-index: 1;
  max-width: 750px;
  padding: 0 24px;
}

.about-hero__tagline {
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  color: var(--color-secondary);
  font-weight: 600;
  margin-bottom: 0.75rem;
  display: block;
}

.about-hero__title {
  font-family: var(--font-family-heading);
  font-size: 3rem;
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: 1.25rem;
  color: #fffcf7 !important;
}

.about-hero__subtitle {
  font-size: 1.05rem;
  line-height: 1.6;
  color: #ebd2dd;
  margin-bottom: 2rem;
}

/* Buttons */
.about-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 2rem;
  height: 48px;
  border-radius: 24px;
  font-weight: 600;
  font-size: 0.95rem;
  text-decoration: none;
  transition: all var(--transition-normal);
  cursor: pointer;
  box-shadow: var(--shadow-sm);
}

.about-btn--primary {
  background-color: var(--color-secondary);
  color: #2d051c;
}

.about-btn--primary:hover {
  background-color: var(--color-primary-hover);
  color: #ffffff;
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.about-btn--secondary {
  background-color: var(--color-secondary);
  color: #2d051c;
}

.about-btn--secondary:hover {
  background-color: #ffffff;
  color: var(--color-primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

/* Container */
.about-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

/* Sections Base */
.about-section {
  margin-bottom: 5.5rem;
}

.section-tag {
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: var(--color-secondary);
  margin-bottom: 0.5rem;
  display: block;
}

.section-tag--center {
  text-align: center;
}

.section-title {
  font-family: var(--font-family-heading);
  font-size: 2.25rem;
  color: var(--color-primary);
  margin-bottom: 1.5rem;
  line-height: 1.3;
}

.text-center {
  text-align: center;
}

.section-paragraph {
  font-size: 1rem;
  line-height: 1.7;
  color: var(--color-text-secondary);
  margin-bottom: 1.25rem;
}

/* Who We Are */
.who-we-are__grid {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 3.5rem;
  align-items: center;
}

.about-img {
  border-radius: 16px;
  box-shadow: var(--shadow-md);
  object-fit: cover;
  width: 100%;
  height: auto;
  max-height: 480px;
  transition: transform var(--transition-normal);
}

.about-img:hover {
  transform: scale(1.02);
}

.img-responsive {
  max-width: 100%;
  height: auto;
}

/* Our Story */
.our-story {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 3rem 2.5rem;
  box-shadow: var(--shadow-sm);
}

.story-timeline {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin-top: 2.5rem;
}

.story-card {
  position: relative;
  padding: 1.5rem;
  background-color: var(--cream-bg);
  border-radius: 12px;
  border-left: 4px solid var(--color-primary);
}

.story-card__title {
  font-family: var(--font-family-heading);
  font-size: 1.25rem;
  color: var(--color-primary);
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.story-card__text {
  font-size: 0.92rem;
  line-height: 1.6;
  color: var(--color-text-secondary);
}

/* Mission & Vision */
.mission-vision__grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2.5rem;
}

.mv-card {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2.5rem;
  text-align: center;
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-normal), box-shadow var(--transition-normal);
}

.mv-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
}

.mv-card__icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.mv-card__title {
  font-family: var(--font-family-heading);
  font-size: 1.5rem;
  color: var(--color-primary);
  margin-bottom: 1rem;
  font-weight: 600;
}

.mv-card__text {
  font-size: 0.95rem;
  line-height: 1.65;
  color: var(--color-text-secondary);
}

/* Why Choose */
.why-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin-top: 2.5rem;
}

.why-card {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2rem;
  position: relative;
  transition: all var(--transition-normal);
  box-shadow: var(--shadow-sm);
}

.why-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
  border-color: var(--color-secondary);
}

.why-card__num {
  font-size: 2.5rem;
  font-family: var(--font-family-heading);
  font-weight: 700;
  color: rgba(74, 14, 46, 0.08);
  position: absolute;
  top: 1rem;
  right: 1.5rem;
}

.why-card__title {
  font-size: 1.15rem;
  font-family: var(--font-family-heading);
  font-weight: 600;
  color: var(--color-primary);
  margin-bottom: 0.75rem;
  margin-top: 0.5rem;
}

.why-card__text {
  font-size: 0.9rem;
  line-height: 1.6;
  color: var(--color-text-secondary);
}

/* Commitment */
.commitment__banner {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 3.5rem 2.5rem;
  box-shadow: var(--shadow-sm);
  position: relative;
  overflow: hidden;
}

.commitment__banner::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 6px;
  height: 100%;
  background-color: var(--color-secondary);
}

.commitment__content {
  max-width: 850px;
}

/* Stats */
.stats__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  text-align: center;
}

.stat-item {
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 2.5rem 1.5rem;
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-normal);
}

.stat-item:hover {
  transform: scale(1.02);
}

.stat-item__num {
  font-size: 3rem;
  font-weight: 700;
  color: var(--color-primary);
  font-family: var(--font-family-heading);
  display: block;
  margin-bottom: 0.5rem;
}

.stat-item__label {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-primary);
  display: block;
  margin-bottom: 0.75rem;
}

.stat-item__desc {
  font-size: 0.88rem;
  color: var(--color-text-secondary);
  line-height: 1.5;
}

/* Founder Message */
.founder-message {
  display: flex;
  justify-content: center;
}

.founder-card {
  max-width: 850px;
  background-color: var(--blush-bg);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  padding: 3.5rem 3rem;
  text-align: center;
  position: relative;
  box-shadow: var(--shadow-sm);
}

.founder-card__quote-icon {
  font-family: var(--font-family-heading);
  font-size: 6rem;
  line-height: 1;
  color: var(--color-primary);
  opacity: 0.15;
  position: absolute;
  top: 1rem;
  left: 50%;
  transform: translateX(-50%);
}

.founder-card__quote {
  font-family: var(--font-family-heading);
  font-size: 1.35rem;
  font-style: italic;
  line-height: 1.7;
  color: var(--color-primary);
  position: relative;
  z-index: 1;
  margin-bottom: 1.75rem;
  font-weight: 500;
}

.founder-card__author {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.founder-card__name {
  font-weight: 600;
  font-size: 1.1rem;
  color: var(--color-text-primary);
}

.founder-card__role {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--color-text-secondary);
  margin-top: 0.25rem;
}

/* Final CTA */
.cta-banner {
  background: linear-gradient(135deg, var(--color-primary) 0%, #30081d 100%);
  border-radius: 16px;
  padding: 4.5rem 2rem;
  text-align: center;
  color: #ffffff;
  box-shadow: var(--shadow-lg);
}

.cta-banner__title {
  font-family: var(--font-family-heading);
  font-size: 2.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--accent-gold-light) !important;
}

.cta-banner__subtitle {
  font-size: 1.05rem;
  max-width: 600px;
  margin: 0 auto 2rem auto;
  color: #ebd2dd;
  line-height: 1.6;
}

/* Scroll Reveal classes */
.reveal {
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.6s ease-out;
}

.reveal.active {
  opacity: 1;
  transform: translateY(0);
}

/* Responsive Styles */

@media (max-width: 992px) {
  .who-we-are__grid {
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }
  
  .who-we-are__image {
    order: -1;
  }
  
  .story-timeline {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .why-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .about-hero__title {
    font-size: 2.25rem;
  }
  
  .section-title {
    font-size: 1.85rem;
  }
  
  .mission-vision__grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .why-grid {
    grid-template-columns: 1fr;
  }
  
  .stats__grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .founder-card {
    padding: 2.5rem 1.5rem;
  }
  
  .founder-card__quote {
    font-size: 1.15rem;
  }
}
</style>
