<template>
  <Transition name="splash-fade">
    <div 
      v-if="show" 
      class="brand-splash-screen" 
      :class="{ 'brand-splash-screen--festive': festivalStore.isFestivalActive && festivalStore.splashEnabled }"
      role="dialog" 
      aria-modal="true" 
      aria-label="Maya Sree Cinematic Brand Splash Screen"
    >
      <!-- 4K Flowing White Silk Fabric Texture Background (#FCFAF7 Ivory Base) -->
      <div class="splash-silk-backdrop"></div>
      <div class="splash-silk-overlay"></div>

      <!-- Center Rose-Gold / Festive Warm Lighting Bloom -->
      <div class="splash-center-glow" aria-hidden="true"></div>

      <!-- Floating Gold Dust Particles Concentrated Around Logo -->
      <div class="gold-particles-container" aria-hidden="true">
        <div 
          v-for="n in 26" 
          :key="n" 
          class="gold-particle" 
          :class="{ 'gold-particle--festive': festivalStore.isFestivalActive && n % 3 === 0 }"
          :style="getParticleStyle(n)"
        ></div>
      </div>

      <!-- Minimalist 'Enter Store' Skip Button for Quick Entry -->
      <button 
        type="button" 
        class="splash-skip-btn" 
        @click="$emit('close')"
        aria-label="Enter storefront"
        title="Enter Store"
      >
        <span>Enter Store</span>
        <span class="skip-arrow">→</span>
      </button>

      <!-- Main Cinematic Splash Content Container -->
      <div class="splash-content">
        <!-- Logo Wrapper with Shimmer & Scale-in (90% to 100%) -->
        <div class="splash-logo-wrapper">
          <div class="splash-logo-shimmer"></div>
          <div class="splash-logo-container">
            <img :src="'/asset/profile/logo.png'" alt="Maya Sree Fashion Logo" class="splash-logo-img">
            <!-- Shimmer Light Sweep across Logo -->
            <div class="splash-logo-shimmer-sweep" aria-hidden="true"></div>
          </div>
        </div>

        <!-- Luxury Editorial Typography -->
        <div class="splash-text-container">
          <h1 class="splash-brand-name">MAYA SREE FASHION</h1>
        </div>

        <!-- Thin Rose-Gold Expanding Divider Line -->
        <div class="splash-divider" aria-hidden="true"></div>

        <!-- Delicate Brand Tagline -->
        <p class="splash-tagline">A Mother's Dream • A Fashion Legacy</p>

        <!-- Festive Greeting Highlight (When Festival Mode Active) -->
        <div 
          v-if="festivalStore.isFestivalActive && festivalStore.splashEnabled" 
          class="splash-festive-greeting"
          :class="`splash-festive-greeting--${festivalStore.splashStyle || 'maroon_gold'}`"
        >
          <span class="festive-sparkle">✨</span>
          <span class="festive-greeting-text">{{ festivalStore.splashGreeting }}</span>
          <span class="festive-sparkle">✨</span>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useFestivalStore } from '../stores/festival';

defineProps({
  show: {
    type: Boolean,
    default: true
  }
});

defineEmits(['close']);

const festivalStore = useFestivalStore();

// Particle generation with organic delays and radial drift around logo center
const getParticleStyle = (index) => {
  const size = 2 + (index % 4) * 0.9; // 2px to 4.7px
  // Position particles near center area (30% to 70% left) for focused dust effect
  const left = 32 + ((index * 5.3 + 7) % 36); 
  const bottom = 40 + ((index * 7.1) % 25); // Start height near logo center %
  const duration = 2.8 + (index % 5) * 0.4; // 2.8s to 4.4s float speed
  const delay = 0.8 + (index % 8) * 0.22; // Staggered delays starting after silk settles
  const driftX = (index % 2 === 0 ? 1 : -1) * (12 + (index % 5) * 8);

  return {
    width: `${size}px`,
    height: `${size}px`,
    left: `${left}%`,
    bottom: `${bottom}%`,
    '--particle-duration': `${duration}s`,
    '--particle-delay': `${delay}s`,
    '--drift-x': `${driftX}px`
  };
};
</script>

<style scoped>
/* Main Cinematic Splash Screen Overlay - Soft Warm Ivory #FCFAF7 */
.brand-splash-screen {
  position: fixed;
  inset: 0;
  background-color: #FCFAF7; /* Clean ivory white background */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
  overflow: hidden;
  user-select: none;
  will-change: opacity;
}

/* 4K Realistic Flowing White Silk Backdrop */
.splash-silk-backdrop {
  position: absolute;
  inset: -8%;
  background-image: url('/asset/profile/white_silk_bg.png');
  background-size: cover;
  background-position: center;
  opacity: 0;
  filter: contrast(1.02) brightness(1.03);
  animation: 
    silkFadeIn 1.4s ease-out forwards,
    silkCinematicFlow 26s cubic-bezier(0.4, 0, 0.2, 1) infinite alternate;
  will-change: transform, opacity;
}

@keyframes silkFadeIn {
  0% {
    opacity: 0;
    transform: scale(1.04);
  }
  100% {
    opacity: 0.9;
    transform: scale(1);
  }
}

@keyframes silkCinematicFlow {
  0% {
    transform: scale(1) translate(0, 0) rotate(0deg);
  }
  50% {
    transform: scale(1.04) translate(-14px, -10px) rotate(0.4deg);
  }
  100% {
    transform: scale(1.02) translate(12px, 8px) rotate(-0.3deg);
  }
}

/* Soft Natural Lighting Radial Bloom */
.splash-silk-overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(
    circle at 50% 48%, 
    rgba(252, 250, 247, 0.65) 0%, 
    rgba(250, 245, 238, 0.82) 50%, 
    rgba(245, 238, 228, 0.94) 100%
  );
  pointer-events: none;
}

/* Center Rose-Gold Warm Ambient Lighting Expansion */
.splash-center-glow {
  position: absolute;
  width: 340px;
  height: 340px;
  border-radius: 50%;
  background: radial-gradient(
    circle,
    rgba(229, 195, 185, 0.55) 0%,
    rgba(212, 160, 143, 0.22) 45%,
    rgba(252, 250, 247, 0) 75%
  );
  opacity: 0;
  transform: scale(0.75);
  animation: centerGlowExpand 1.6s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards;
  pointer-events: none;
}

@keyframes centerGlowExpand {
  0% {
    opacity: 0;
    transform: scale(0.75);
  }
  100% {
    opacity: 0.85;
    transform: scale(1.22);
  }
}

/* Gold Dust Particles Container */
.gold-particles-container {
  position: absolute;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
  z-index: 1;
}

.gold-particle {
  position: absolute;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(240, 200, 145, 0.95) 0%, rgba(212, 175, 55, 0.6) 55%, rgba(212, 175, 55, 0) 100%);
  box-shadow: 0 0 7px rgba(224, 185, 120, 0.75);
  opacity: 0;
  animation: particleCinematicFloat var(--particle-duration, 3.2s) cubic-bezier(0.25, 1, 0.5, 1) var(--particle-delay, 0.8s) forwards;
  will-change: transform, opacity;
}

@keyframes particleCinematicFloat {
  0% {
    transform: translateY(25px) translateX(0) scale(0.5);
    opacity: 0;
  }
  25% {
    opacity: 0.9;
  }
  70% {
    opacity: 0.65;
  }
  100% {
    transform: translateY(-90px) translateX(var(--drift-x, 20px)) scale(1.2);
    opacity: 0;
  }
}

/* Main Splash Content Wrapper */
.splash-content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2.5rem 1.5rem;
  max-width: 440px;
  width: 90%;
}

/* Logo Wrapper */
.splash-logo-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.85rem;
}

/* Ambient Rose-Gold Glow Ring */
.splash-logo-shimmer {
  position: absolute;
  width: 145px;
  height: 145px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(229, 195, 185, 0.5) 0%, rgba(212, 160, 143, 0.18) 55%, transparent 75%);
  opacity: 0;
  animation: roseGoldShimmerPulse 3.5s ease-in-out 0.6s forwards infinite alternate;
  pointer-events: none;
}

@keyframes roseGoldShimmerPulse {
  0% {
    transform: scale(0.92);
    opacity: 0.2;
  }
  50% {
    opacity: 0.75;
  }
  100% {
    transform: scale(1.3);
    opacity: 0.85;
  }
}

/* Circular Logo Container: Fades in + Scales from 90% to 100% */
.splash-logo-container {
  width: 124px;
  height: 124px;
  border-radius: 50%;
  background: #FFFFFF;
  padding: 6px;
  box-shadow: 
    0 14px 38px rgba(197, 143, 129, 0.22),
    0 0 25px rgba(212, 160, 143, 0.35),
    inset 0 0 12px rgba(245, 225, 218, 0.5);
  border: 1.5px solid rgba(212, 160, 143, 0.65); /* Rose Gold Outline */
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transform: scale(0.90);
  overflow: hidden;
  animation: 
    logoCinematicEntrance 1.2s cubic-bezier(0.16, 1, 0.3, 1) 0.6s forwards,
    logoFloating 4.5s ease-in-out 1.8s infinite alternate;
  will-change: transform, opacity, box-shadow;
}

@keyframes logoCinematicEntrance {
  0% {
    opacity: 0;
    transform: scale(0.90);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes logoFloating {
  0% {
    transform: scale(1) translateY(0);
  }
  100% {
    transform: scale(1) translateY(-6px);
  }
}

.splash-logo-img {
  width: 90%;
  height: 90%;
  object-fit: contain;
  border-radius: 50%;
}

/* Rose-Gold Shimmer Light Sweep across Logo */
.splash-logo-shimmer-sweep {
  position: absolute;
  top: 0;
  left: -150%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    105deg,
    transparent 0%,
    rgba(255, 255, 255, 0) 25%,
    rgba(240, 210, 200, 0.65) 50%,
    rgba(255, 255, 255, 0) 75%,
    transparent 100%
  );
  transform: skewX(-22deg);
  animation: shimmerSweepOnce 1.5s cubic-bezier(0.16, 1, 0.3, 1) 1.5s forwards;
  pointer-events: none;
  z-index: 4;
}

@keyframes shimmerSweepOnce {
  0% {
    left: -150%;
  }
  100% {
    left: 200%;
  }
}

/* Typography Container & Staggered Animations */
.splash-text-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

/* Brand Name "MAYA SREE" - Luxury Serif Fade-Up */
.splash-brand-name {
  font-family: var(--font-family-heading), 'Playfair Display', Georgia, serif;
  font-size: 2.5rem;
  font-weight: 600;
  color: #2D2424; /* Refined dark charcoal tone */
  letter-spacing: 0.22em;
  line-height: 1.15;
  text-transform: uppercase;
  margin: 0;
  opacity: 0;
  transform: translateY(18px);
  animation: typographyFadeUp 1.1s cubic-bezier(0.16, 1, 0.3, 1) 1.6s forwards;
  will-change: transform, opacity;
}

/* Subtitle Brand Tagline - Generous Spacing Fade-Up */
.splash-brand-sub {
  font-family: var(--font-family-base), 'Poppins', sans-serif;
  font-size: 0.72rem;
  font-weight: 500;
  color: #C58F81; /* Rose Gold */
  letter-spacing: 0.45em;
  text-transform: uppercase;
  margin-top: 2px;
  opacity: 0;
  transform: translateY(12px);
  animation: typographyFadeUp 1.1s cubic-bezier(0.16, 1, 0.3, 1) 1.9s forwards;
  will-change: transform, opacity;
}

/* Thin Rose-Gold Expanding Divider Line */
.splash-divider {
  width: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent 0%, #C58F81 50%, transparent 100%);
  margin: 1.35rem 0 1.1rem 0;
  opacity: 0;
  animation: dividerExpand 1.0s cubic-bezier(0.16, 1, 0.3, 1) 2.2s forwards;
}

@keyframes dividerExpand {
  0% {
    width: 0;
    opacity: 0;
  }
  100% {
    width: 75px;
    opacity: 1;
  }
}

/* Tagline Fade-Up */
.splash-tagline {
  font-family: var(--font-family-base), 'Poppins', sans-serif;
  font-size: 0.8rem;
  color: #8C6D63; /* Subtle rose-bronze */
  font-style: italic;
  letter-spacing: 0.08em;
  font-weight: 300;
  margin: 0;
  opacity: 0;
  transform: translateY(10px);
  animation: typographyFadeUp 1.1s cubic-bezier(0.16, 1, 0.3, 1) 2.4s forwards;
  will-change: transform, opacity;
}

/* Common Typography Fade-Up Keyframe */
@keyframes typographyFadeUp {
  0% {
    opacity: 0;
    transform: translateY(18px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Festive Golden Bloom for Center Lighting */
.brand-splash-screen--festive .splash-center-glow {
  background: radial-gradient(
    circle,
    rgba(212, 175, 55, 0.45) 0%,
    rgba(245, 158, 11, 0.22) 42%,
    rgba(252, 250, 247, 0) 75%
  );
  box-shadow: 0 0 60px rgba(212, 175, 55, 0.2);
}

.gold-particle--festive {
  box-shadow: 0 0 10px rgba(212, 175, 55, 0.95), 0 0 4px #FFFFFF !important;
}

/* Minimalist Skip 'Enter Store' Button */
.splash-skip-btn {
  position: absolute;
  top: 24px;
  right: 24px;
  z-index: 30;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(212, 175, 55, 0.45);
  color: #6E1F3A;
  font-family: var(--font-family-base), 'Poppins', sans-serif;
  font-size: 0.76rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  padding: 6px 16px;
  border-radius: 9999px;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
  transition: all 0.25s ease;
  opacity: 0;
  animation: skipBtnFadeIn 0.8s ease-out 1.0s forwards;
}

.splash-skip-btn:hover {
  background: #6E1F3A;
  color: #F7E7A9;
  border-color: #D4AF37;
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(110, 31, 58, 0.28);
}

.splash-skip-btn .skip-arrow {
  transition: transform 0.2s ease;
}

.splash-skip-btn:hover .skip-arrow {
  transform: translateX(3px);
}

@keyframes skipBtnFadeIn {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Festive Greeting Banner on Splash Screen */
.splash-festive-greeting {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-top: 1.15rem;
  padding: 7px 22px;
  border-radius: 9999px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  opacity: 0;
  transform: translateY(8px);
  animation: typographyFadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 1.2s forwards;
  transition: all 0.25s ease;
}

/* Theme 1: Royal Maroon & Gold (Primary Brand Identity) */
.splash-festive-greeting--maroon_gold {
  background: linear-gradient(135deg, rgba(110, 31, 58, 0.94) 0%, rgba(74, 18, 36, 0.98) 100%) !important;
  border: 1.2px solid rgba(212, 175, 55, 0.85) !important;
  box-shadow: 0 8px 24px rgba(110, 31, 58, 0.32), 0 0 16px rgba(212, 175, 55, 0.28) !important;
}

.splash-festive-greeting--maroon_gold .festive-greeting-text {
  color: #F7E7A9 !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Theme 2: Radiant Gold & Amber Glass */
.splash-festive-greeting--gold_amber {
  background: linear-gradient(135deg, rgba(254, 243, 199, 0.92) 0%, rgba(253, 230, 138, 0.96) 100%) !important;
  border: 1.2px solid rgba(212, 175, 55, 0.75) !important;
  box-shadow: 0 8px 22px rgba(212, 175, 55, 0.28) !important;
}

.splash-festive-greeting--gold_amber .festive-greeting-text {
  color: #6E1F3A !important;
}

/* Theme 3: Festive Emerald & Gold */
.splash-festive-greeting--emerald_gold {
  background: linear-gradient(135deg, rgba(6, 95, 70, 0.94) 0%, rgba(6, 78, 59, 0.98) 100%) !important;
  border: 1.2px solid rgba(212, 175, 55, 0.85) !important;
  box-shadow: 0 8px 24px rgba(6, 95, 70, 0.32), 0 0 16px rgba(212, 175, 55, 0.25) !important;
}

.splash-festive-greeting--emerald_gold .festive-greeting-text {
  color: #F7E7A9 !important;
}

.festive-greeting-text {
  font-family: var(--font-family-base), 'Poppins', sans-serif;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: capitalize;
}

.festive-sparkle {
  font-size: 0.88rem;
  color: #D4AF37;
  display: inline-block;
  animation: sparkleTwinkle 2s ease-in-out infinite alternate;
}

@keyframes sparkleTwinkle {
  0% { transform: scale(0.9); opacity: 0.7; }
  100% { transform: scale(1.2); opacity: 1; filter: drop-shadow(0 0 5px rgba(212, 175, 55, 0.9)); }
}

/* Vue Fade Transition */
.splash-fade-enter-active,
.splash-fade-leave-active {
  transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1);
}

.splash-fade-enter-from,
.splash-fade-leave-to {
  opacity: 0;
}

/* Responsive Mobile 9:16 Tweaks */
@media (max-width: 480px) {
  .splash-brand-name {
    font-size: 2.15rem;
    letter-spacing: 0.18em;
  }
  .splash-brand-sub {
    font-size: 0.65rem;
    letter-spacing: 0.38em;
  }
  .splash-logo-container {
    width: 110px;
    height: 110px;
  }
  .splash-logo-shimmer {
    width: 130px;
    height: 130px;
  }
}
</style>
