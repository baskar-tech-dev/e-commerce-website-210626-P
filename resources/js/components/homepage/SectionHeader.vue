<template>
  <!-- ================================================================
       Elegant reusable section header with optional "View More" link
       Inspired by: Suta · Aachho · Nykaa Fashion · Hay Clothing
       ================================================================ -->
  <div class="sh" :class="{ 'sh--center': center }">

    <!-- Top eyebrow line with decorative motif -->
    <div class="sh__eyebrow" aria-hidden="true">
      <span class="sh__line"></span>
      <span class="sh__motif">✦</span>
      <span class="sh__line"></span>
    </div>

    <!-- Label / category tag -->
    <p v-if="label" class="sh__label">{{ label }}</p>

    <!-- Main heading -->
    <h2 class="sh__title">{{ title }}</h2>

    <!-- Subtitle description -->
    <p v-if="subtitle" class="sh__sub">{{ subtitle }}</p>

    <!-- View More CTA — inline horizontal layout -->
    <div v-if="viewMoreTo" class="sh__footer">
      <!-- Left ornamental rule -->
      <span class="sh__rule" aria-hidden="true"></span>

      <router-link :to="viewMoreTo" class="sh__cta" :aria-label="`View more ${title}`">
        <span class="sh__cta-text">{{ viewMoreLabel || 'View All' }}</span>
        <span class="sh__cta-icon" aria-hidden="true">
          <!-- Elegant arrow with animated underline -->
          <svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 5h16M12 1l5 4-5 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
      </router-link>

      <!-- Right ornamental rule -->
      <span class="sh__rule" aria-hidden="true"></span>
    </div>

  </div>
</template>

<script setup>
defineProps({
  /** Small uppercase eyebrow text above the title */
  label: { type: String, default: '' },
  /** Main section heading */
  title: { type: String, required: true },
  /** Supporting subtitle text */
  subtitle: { type: String, default: '' },
  /** Vue Router path for the View More link */
  viewMoreTo: { type: String, default: '' },
  /** Custom text for the CTA button */
  viewMoreLabel: { type: String, default: 'View All' },
  /** Center-align the text (default: true) */
  center: { type: Boolean, default: true },
});
</script>

<style scoped>
/* ================================================================
   Section Header Component
   ================================================================ */
.sh {
  padding: 0 0 28px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 6px;
}

/* Center variant */
.sh--center {
  align-items: center;
  text-align: center;
}

/* ---- Eyebrow rule + motif ---- */
.sh__eyebrow {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  max-width: 200px;
}

.sh--center .sh__eyebrow {
  justify-content: center;
}

.sh__line {
  flex: 1;
  height: 1px;
  background: linear-gradient(90deg, transparent, #d4af37 50%, transparent);
  opacity: 0.6;
}

.sh__motif {
  font-size: 0.6rem;
  color: #d4af37;
  letter-spacing: 0;
  line-height: 1;
  flex-shrink: 0;
}

/* ---- Label ---- */
.sh__label {
  font-family: 'Poppins', sans-serif;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: #9c8a94;
  margin: 0;
}

/* ---- Title ---- */
.sh__title {
  font-family: 'Playfair Display', serif;
  font-size: 1.9rem;
  font-weight: 800;
  color: #2d1420;
  line-height: 1.2;
  margin: 0;
  letter-spacing: -0.02em;
}

@media (max-width: 767px) {
  .sh__title { font-size: 1.45rem; }
}

/* ---- Subtitle ---- */
.sh__sub {
  font-family: 'Poppins', sans-serif;
  font-size: 0.875rem;
  color: #72626a;
  line-height: 1.6;
  margin: 2px 0 0;
  max-width: 480px;
}

/* ---- Footer row with View More ---- */
.sh__footer {
  display: flex;
  align-items: center;
  gap: 16px;
  width: 100%;
  margin-top: 12px;
}

.sh--center .sh__footer {
  justify-content: center;
}

.sh__rule {
  flex: 1;
  height: 1px;
  max-width: 80px;
  background: linear-gradient(90deg, transparent, #e9d5da);
  display: block;
}

/* ---- View All CTA ---- */
.sh__cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  white-space: nowrap;
  padding: 0 4px;
  position: relative;
}

.sh__cta-text {
  font-family: 'Poppins', sans-serif;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: #4a0e2e;
  position: relative;
}

/* Animated underline on hover */
.sh__cta-text::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 1.5px;
  background: #d4af37;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sh__cta:hover .sh__cta-text::after {
  width: 100%;
}

.sh__cta-icon {
  color: #4a0e2e;
  display: flex;
  align-items: center;
  transition: transform 0.25s ease;
}

.sh__cta:hover .sh__cta-icon {
  transform: translateX(5px);
}

/* ---- Responsive ---- */
@media (max-width: 767px) {
  .sh {
    padding-bottom: 20px;
    gap: 5px;
  }
  .sh__sub {
    font-size: 0.80rem;
  }
  .sh__rule {
    max-width: 48px;
  }
}
</style>
