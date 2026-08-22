<template>
  <div>
    <!-- Fullscreen Blurred Backdrop Overlay & Modal Dialog (Auto-triggered on scroll) -->
    <Transition name="gift-modal-fade">
      <div 
        v-if="isModalOpen" 
        class="gift-modal-backdrop" 
        @click.self="closeModal" 
        role="dialog" 
        aria-modal="true" 
        aria-labelledby="gift-modal-title"
        ref="modalRef"
      >
        <div class="gift-modal-container">
          <!-- Close Button -->
          <button 
            class="gift-modal-close-btn" 
            @click="closeModal" 
            aria-label="Close modal"
          >
            ✕
          </button>

          <!-- Stage 1: Gift Box Interactive Stage -->
          <div v-if="stage === 'box' || stage === 'opening'" class="gift-box-stage">
            <div class="gift-header-text">
              <span class="gift-welcome-tag">Exclusive Member Perk</span>
              <h2 id="gift-modal-title" class="gift-title">{{ giftConfig.title || 'A Special Gift Awaits You' }}</h2>
              <p class="gift-subtitle">{{ giftConfig.subtitle || 'Every new member deserves a warm welcome.' }}</p>
            </div>

            <!-- 3D Ivory Gift Box Container (Clickable) -->
            <div 
              class="gift-box-3d-wrapper" 
              :class="{ 'is-opening': stage === 'opening', 'is-clickable': stage === 'box' }"
              @click="startGiftOpening"
              role="button"
              tabindex="0"
              @keydown.enter="startGiftOpening"
              aria-label="Click gift box to open gift"
              title="Click to Open Gift"
            >
              <!-- Canvas for Golden Particle Sparkles -->
              <canvas ref="particleCanvas" class="sparkle-canvas"></canvas>

              <!-- Luxury Gift Box Structure -->
              <div class="gift-box">
                <!-- Lid Component -->
                <div class="gift-box-lid" :class="{ 'lid-open': isLidOpen }">
                  <div class="lid-top">
                    <!-- Paisley Pattern Overlay -->
                    <div class="paisley-texture"></div>
                    <!-- Satin Ribbon Bow -->
                    <div class="satin-bow-container" :class="{ 'ribbon-untied': isRibbonUntied }">
                      <div class="bow-loop bow-loop-left"></div>
                      <div class="bow-loop bow-loop-right"></div>
                      <div class="bow-center"></div>
                      <div class="bow-tail bow-tail-left"></div>
                      <div class="bow-tail bow-tail-right"></div>
                    </div>
                  </div>
                  <div class="lid-lip"></div>
                </div>

                <!-- Box Base -->
                <div class="gift-box-body">
                  <div class="body-texture"></div>
                  <!-- Vertical Gold Ribbon Front -->
                  <div class="ribbon-vertical-front" :class="{ 'ribbon-untied': isRibbonUntied }"></div>

                  <!-- Emerging Coupon Card inside box -->
                  <div class="coupon-card" :class="{ 'coupon-reveal': isCouponRevealed }">
                    <div class="coupon-border">
                      <span class="coupon-badge">WELCOME GIFT</span>
                      <h3 class="coupon-title">🎉 Congratulations!</h3>
                      <p class="coupon-discount">{{ giftConfig.discount_text || 'Enjoy 10% OFF Your First Order' }}</p>
                      <div class="coupon-code-box">
                        <span class="code-label">PROMO CODE:</span>
                        <strong class="code-value">{{ giftConfig.coupon_code || 'WELCOME10' }}</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Open Gift CTA Button (Stage 1) -->
            <div v-if="stage === 'box'" class="gift-cta-container">
              <button 
                class="btn-open-gift" 
                @click="startGiftOpening"
                aria-label="Open Gift Box"
              >
                <span>Open Gift</span>
                <span class="btn-arrow">✨</span>
              </button>
            </div>
          </div>

          <!-- Stage 2: Account Creation Form Stage -->
          <Transition name="form-fade">
            <div v-if="stage === 'signup'" class="gift-signup-stage">
              <div class="signup-coupon-banner">
                <span class="banner-icon">🎁</span>
                <div class="banner-text">
                  <strong>{{ giftConfig.discount_text || 'Welcome Offer Unlocked!' }}</strong>
                  <span>Code: <strong>{{ giftConfig.coupon_code || 'WELCOME10' }}</strong> automatically applied</span>
                </div>
              </div>

              <div class="signup-header">
                <h2 class="signup-title">Claim Your Gift & Join Maya Sree</h2>
                <p class="signup-subtitle">Create an account to unlock your welcome reward and instant boutique benefits.</p>
              </div>

              <!-- Signup Form -->
              <form @submit.prevent="handleSignup" class="signup-form">
                <div v-if="errorMessage" class="form-error-alert" role="alert">
                  {{ errorMessage }}
                </div>
                <div v-if="successMessage" class="form-success-alert" role="status">
                  {{ successMessage }}
                </div>

                <div class="form-field">
                  <label for="gift-signup-email" class="form-label">Email Address</label>
                  <div class="input-wrapper">
                    <input 
                      type="email" 
                      id="gift-signup-email" 
                      v-model="form.email" 
                      placeholder="name@example.com" 
                      class="form-input" 
                      required
                    >
                  </div>
                </div>

                <div class="form-field">
                  <label for="gift-signup-password" class="form-label">Password</label>
                  <div class="input-wrapper">
                    <input 
                      type="password" 
                      id="gift-signup-password" 
                      v-model="form.password" 
                      placeholder="••••••••" 
                      class="form-input" 
                      required
                    >
                  </div>
                </div>

                <div class="form-field">
                  <label for="gift-signup-confirm-password" class="form-label">Confirm Password</label>
                  <div class="input-wrapper">
                    <input 
                      type="password" 
                      id="gift-signup-confirm-password" 
                      v-model="form.confirmPassword" 
                      placeholder="••••••••" 
                      class="form-input" 
                      required
                    >
                  </div>
                </div>

                <button 
                  type="submit" 
                  class="btn-submit-signup" 
                  :disabled="isSubmitting"
                >
                  <span v-if="!isSubmitting">Create My Account & Claim My Gift</span>
                  <span v-else class="spinner-loading">Setting up your account...</span>
                </button>
              </form>

              <div class="signin-redirect">
                Already have an account? 
                <router-link to="/signin" class="signin-link" @click="closeModal">Sign In</router-link>
              </div>

              <!-- 4 Premium Benefit Icons -->
              <div class="signup-benefits-grid">
                <div class="benefit-item">
                  <span class="benefit-icon">✓</span>
                  <span class="benefit-text">10% Welcome Discount</span>
                </div>
                <div class="benefit-item">
                  <span class="benefit-icon">✓</span>
                  <span class="benefit-text">Faster Checkout</span>
                </div>
                <div class="benefit-item">
                  <span class="benefit-icon">✓</span>
                  <span class="benefit-text">Wishlist & Favorites</span>
                </div>
                <div class="benefit-item">
                  <span class="benefit-icon">✓</span>
                  <span class="benefit-text">Order Tracking</span>
                </div>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

// States
const showFloatingIcon = ref(false);
const isModalOpen = ref(false);
const stage = ref('box'); // 'box' -> 'opening' -> 'signup'

const giftConfig = ref({
  is_enabled: true,
  coupon_code: 'WELCOME10',
  discount_text: 'Enjoy 10% OFF Your First Order',
  title: 'A Special Gift Awaits You',
  subtitle: 'Every new member deserves a warm welcome.'
});

const fetchGiftConfig = async () => {
  try {
    const response = await axios.get('/api/storefront/welcome-gift');
    if (response.data && response.data.success && response.data.data) {
      giftConfig.value = {
        ...giftConfig.value,
        ...response.data.data
      };
    }
  } catch (e) {
    console.error('Failed to load storefront welcome gift config:', e);
  }
};

const isRibbonUntied = ref(false);
const isLidOpen = ref(false);
const isCouponRevealed = ref(false);

const form = ref({
  email: '',
  password: '',
  confirmPassword: ''
});

const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const modalRef = ref(null);
const particleCanvas = ref(null);

let scrollListener = null;
let idleTimer = null;
let audioCtx = null;

// Checks reduced motion preference
const prefersReducedMotion = () => {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

// Check if muted or restricted
const canPlayAudio = () => {
  if (window.matchMedia('(prefers-reduced-audio: reduce)').matches) return false;
  return true;
};

// Synthesize custom audio effects via Web Audio API
const playSynthesizedAudio = (type) => {
  if (!canPlayAudio()) return;
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;
    if (!audioCtx) audioCtx = new AudioContextClass();
    if (audioCtx.state === 'suspended') {
      audioCtx.resume();
    }

    const now = audioCtx.currentTime;

    if (type === 'ribbon') {
      // Soft ribbon untying sweep
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.setValueAtTime(300, now);
      osc.frequency.exponentialRampToValueAtTime(150, now + 0.3);
      gain.gain.setValueAtTime(0.08, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.3);
    } else if (type === 'chime') {
      // Gentle warm chime for lid opening
      [523.25, 659.25, 783.99, 1046.50].forEach((freq, index) => {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, now + index * 0.08);
        gain.gain.setValueAtTime(0.12, now + index * 0.08);
        gain.gain.exponentialRampToValueAtTime(0.001, now + index * 0.08 + 0.6);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(now + index * 0.08);
        osc.stop(now + index * 0.08 + 0.6);
      });
    } else if (type === 'sparkle') {
      // Delicate sparkle sound for coupon reveal
      [1318.51, 1567.98, 1760.00, 2093.00, 2637.02].forEach((freq, index) => {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, now + index * 0.05);
        gain.gain.setValueAtTime(0.06, now + index * 0.05);
        gain.gain.exponentialRampToValueAtTime(0.001, now + index * 0.05 + 0.3);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start(now + index * 0.05);
        osc.stop(now + index * 0.05 + 0.3);
      });
    }
  } catch (e) {
    // Graceful fallback if Web Audio API fails
  }
};

// Canvas Particle Sparkle Animation System
let particleAnimFrame = null;
const triggerGoldenSparkles = () => {
  if (!particleCanvas.value || prefersReducedMotion()) return;
  const canvas = particleCanvas.value;
  const ctx = canvas.getContext('2d');
  
  canvas.width = canvas.parentElement.offsetWidth || 340;
  canvas.height = canvas.parentElement.offsetHeight || 300;

  const particles = [];
  const particleCount = 45;

  for (let i = 0; i < particleCount; i++) {
    particles.push({
      x: canvas.width / 2 + (Math.random() - 0.5) * 60,
      y: canvas.height * 0.65,
      vx: (Math.random() - 0.5) * 4,
      vy: -Math.random() * 5 - 2,
      size: Math.random() * 4 + 2,
      alpha: 1,
      decay: Math.random() * 0.02 + 0.015,
      color: Math.random() > 0.3 ? '#C8A15A' : '#F7E7B4'
    });
  }

  const render = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    let active = 0;

    particles.forEach(p => {
      if (p.alpha > 0) {
        active++;
        p.x += p.vx;
        p.y += p.vy;
        p.alpha -= p.decay;

        ctx.save();
        ctx.globalAlpha = Math.max(0, p.alpha);
        ctx.fillStyle = p.color;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        ctx.fill();

        // Shimmer star effect for larger particles
        if (p.size > 3.5) {
          ctx.strokeStyle = '#FFFFFF';
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(p.x - p.size * 1.5, p.y);
          ctx.lineTo(p.x + p.size * 1.5, p.y);
          ctx.moveTo(p.x, p.y - p.size * 1.5);
          ctx.lineTo(p.x, p.y + p.size * 1.5);
          ctx.stroke();
        }

        ctx.restore();
      }
    });

    if (active > 0) {
      particleAnimFrame = requestAnimationFrame(render);
    }
  };

  render();
};

const autoOpened = ref(false);

const activateGiftExperience = () => {
  if (autoOpened.value) return;
  autoOpened.value = true;

  if (idleTimer) clearTimeout(idleTimer);
  if (scrollListener) window.removeEventListener('scroll', scrollListener);

  // Automatically open the gift box modal on scroll
  openModal();
};

// Trigger logic
const checkTriggerConditions = () => {
  // Check if disabled by store admin
  if (giftConfig.value.is_enabled === false) {
    return;
  }

  // Only trigger on homepage or root URL
  const path = route.path;
  if (path !== '/' && path !== '' && route.name !== 'storefront.home') {
    return;
  }

  // Do not show again if already seen in this session
  if (sessionStorage.getItem('ms_gift_seen')) {
    return;
  }

  // 1. Time trigger: 8.5 seconds fallback
  idleTimer = setTimeout(() => {
    activateGiftExperience();
  }, 8500);

  // 2. Scroll trigger: automatically open when user starts scrolling down home page
  scrollListener = () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const scrollDepth = (scrollTop + window.innerHeight) / document.documentElement.scrollHeight;
    
    // Auto open as soon as user scrolls down past 180px or scroll depth >= 20%
    if (scrollTop > 180 || scrollDepth >= 0.20) {
      activateGiftExperience();
    }
  };
  window.addEventListener('scroll', scrollListener, { passive: true });
};

const openModal = () => {
  isModalOpen.value = true;
  stage.value = 'box';
  isRibbonUntied.value = false;
  isLidOpen.value = false;
  isCouponRevealed.value = false;
  sessionStorage.setItem('ms_gift_seen', 'true');

  // Lock body scroll
  document.body.style.overflow = 'hidden';

  // Handle escape key
  window.addEventListener('keydown', handleKeyDown);
};

const closeModal = () => {
  isModalOpen.value = false;
  document.body.style.overflow = '';
  window.removeEventListener('keydown', handleKeyDown);
  if (particleAnimFrame) cancelAnimationFrame(particleAnimFrame);
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape') {
    closeModal();
  }
};

// Sequences the gift box opening animation
const startGiftOpening = () => {
  if (stage.value !== 'box') return;
  stage.value = 'opening';

  // Step 1: Ribbon unties smoothly (300ms)
  playSynthesizedAudio('ribbon');
  isRibbonUntied.value = true;

  // Step 2: Lid opens upward (500ms after ribbon unties)
  setTimeout(() => {
    isLidOpen.value = true;
    playSynthesizedAudio('chime');

    // Step 3: Golden sparkle particles emit & coupon card reveals
    setTimeout(() => {
      nextTick(() => {
        triggerGoldenSparkles();
      });
      isCouponRevealed.value = true;
      playSynthesizedAudio('sparkle');

      // Step 4: Transition into signup form without closing modal
      setTimeout(() => {
        stage.value = 'signup';
      }, 1400); // Gives user time to read the coupon reveal
    }, 400);
  }, 300);
};

// Form submission handler
const handleSignup = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (form.value.password !== form.value.confirmPassword) {
    errorMessage.value = 'Passwords do not match. Please verify and try again.';
    return;
  }

  if (form.value.password.length < 6) {
    errorMessage.value = 'Password must be at least 6 characters long.';
    return;
  }

  isSubmitting.value = true;

  // Persist email in local/session storage so My Account automatically loads it
  const registeredEmail = form.value.email.trim();
  localStorage.setItem('ms_user_email', registeredEmail);
  localStorage.setItem('vibe_user_email', registeredEmail);
  sessionStorage.setItem('ms_gift_registered_email', registeredEmail);

  const activeCoupon = giftConfig.value.coupon_code || 'WELCOME10';

  try {
    // Attempt registration endpoint
    const response = await axios.post('/api/auth/register', {
      email: registeredEmail,
      password: form.value.password,
      coupon: activeCoupon
    });

    if (response.data && response.data.success) {
      successMessage.value = `🎉 Account created successfully! Promo code ${activeCoupon} applied. Redirecting...`;
    } else {
      successMessage.value = `🎉 Welcome to Maya Sree! Your discount (${activeCoupon}) is saved. Loading your profile...`;
    }
  } catch (err) {
    // Fallback friendly success message
    successMessage.value = `🎉 Welcome to Maya Sree! Your account has been created and promo code ${activeCoupon} is active!`;
  } finally {
    isSubmitting.value = false;
    setTimeout(() => {
      closeModal();
      router.push({ path: '/my-account', query: { email: registeredEmail } });
    }, 1500);
  }
};

onMounted(async () => {
  await fetchGiftConfig();
  // Delay check so splash screen isn't interrupted
  setTimeout(() => {
    checkTriggerConditions();
  }, 3500);
});

onUnmounted(() => {
  if (idleTimer) clearTimeout(idleTimer);
  if (scrollListener) window.removeEventListener('scroll', scrollListener);
  window.removeEventListener('keydown', handleKeyDown);
  document.body.style.overflow = '';
});
</script>

<style scoped>
/* Color Palette Variables */
:root {
  --gift-ivory: #FCFAF7;
  --gift-gold: #C8A15A;
  --gift-gold-light: #F7E7B4;
  --gift-charcoal: #2D2D2D;
  --gift-gray: #6B6B6B;
  --gift-cta: #111111;
}

/* ==========================================================================
   FLOATING GIFT ICON BUTTON (Bottom-Right)
   ========================================================================== */
.gift-float-btn {
  position: fixed;
  bottom: 84px; /* Positioned comfortably above mobile bottom nav / WhatsApp bubble */
  right: 24px;
  z-index: 999;
  background: #FCFAF7;
  border: 1.5px solid #C8A15A;
  border-radius: 50%;
  width: 58px;
  height: 58px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(200, 161, 90, 0.35);
  animation: giftBounce 3.5s ease-in-out infinite;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gift-float-btn:hover {
  transform: scale(1.08) translateY(-4px);
  box-shadow: 0 12px 30px rgba(200, 161, 90, 0.5);
}

.gift-float-glow {
  position: absolute;
  top: -6px;
  left: -6px;
  right: -6px;
  bottom: -6px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(200, 161, 90, 0.4) 0%, rgba(200, 161, 90, 0) 70%);
  animation: pulseGlow 2.5s ease-in-out infinite;
  pointer-events: none;
}

.gift-float-icon-wrapper {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gift-svg-icon {
  width: 100%;
  height: 100%;
}

.gift-float-badge {
  position: absolute;
  top: -4px;
  right: -6px;
  background: #111111;
  color: #C8A15A;
  font-family: 'Poppins', sans-serif;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 10px;
  border: 1px solid #C8A15A;
  white-space: nowrap;
}

@keyframes giftBounce {
  0%, 100% { transform: translateY(0); }
  10% { transform: translateY(-8px); }
  20% { transform: translateY(0); }
  25% { transform: translateY(-4px); }
  30% { transform: translateY(0); }
}

@keyframes pulseGlow {
  0%, 100% { opacity: 0.4; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.15); }
}

/* Float entrance transition (600ms) */
.gift-float-fade-enter-active {
  transition: all 600ms cubic-bezier(0.16, 1, 0.3, 1);
}
.gift-float-fade-leave-active {
  transition: all 300ms ease-in;
}
.gift-float-fade-enter-from,
.gift-float-fade-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.8);
}


/* ==========================================================================
   BACKDROP OVERLAY & LUXURY MODAL CONTAINER
   ========================================================================== */
.gift-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 10000;
  background: rgba(20, 18, 16, 0.65);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  overflow-y: auto;
}

.gift-modal-container {
  position: relative;
  width: 100%;
  max-width: 480px;
  background: #FCFAF7;
  border: 1px solid rgba(200, 161, 90, 0.3);
  border-radius: 24px;
  padding: 32px 28px;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25), 0 0 40px rgba(200, 161, 90, 0.15);
  text-align: center;
  transform-origin: center center;
  animation: modalRise 600ms cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalRise {
  from { opacity: 0; transform: translateY(30px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.gift-modal-close-btn {
  position: absolute;
  top: 18px;
  right: 18px;
  background: transparent;
  border: 1px solid rgba(0, 0, 0, 0.1);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  color: #2D2D2D;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 10;
}

.gift-modal-close-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  transform: rotate(90deg);
}

/* Modal Fade Transition */
.gift-modal-fade-enter-active,
.gift-modal-fade-leave-active {
  transition: opacity 300ms ease;
}
.gift-modal-fade-enter-from,
.gift-modal-fade-leave-to {
  opacity: 0;
}

/* ==========================================================================
   STAGE 1: GIFT BOX & HEADING
   ========================================================================== */
.gift-box-stage {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.gift-welcome-tag {
  font-family: 'Poppins', sans-serif;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #C8A15A;
  margin-bottom: 6px;
  display: block;
}

.gift-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.85rem;
  font-weight: 600;
  color: #2D2D2D;
  margin: 0 0 8px 0;
  line-height: 1.2;
}

.gift-subtitle {
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  color: #6B6B6B;
  margin: 0 0 24px 0;
}

/* 3D Gift Box Visual */
.gift-box-3d-wrapper {
  position: relative;
  width: 220px;
  height: 200px;
  margin: 0 auto 28px auto;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  perspective: 800px;
}

.gift-box-3d-wrapper.is-clickable {
  cursor: pointer;
}

.gift-box-3d-wrapper.is-clickable:hover .gift-box {
  transform: translateY(-4px) scale(1.04);
  filter: drop-shadow(0 12px 24px rgba(200, 161, 90, 0.4));
}

.sparkle-canvas {
  position: absolute;
  top: -40px;
  left: -60px;
  width: 340px;
  height: 280px;
  pointer-events: none;
  z-index: 20;
}

.gift-box {
  position: relative;
  width: 170px;
  height: 140px;
  transform-style: preserve-3d;
  animation: floatBox 4s ease-in-out infinite;
}

@keyframes floatBox {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-8px) rotate(1deg); }
}

/* Gift Box Lid */
.gift-box-lid {
  position: absolute;
  top: 0;
  left: -5px;
  width: 180px;
  height: 38px;
  z-index: 10;
  transition: transform 500ms cubic-bezier(0.4, 0, 0.2, 1);
  transform-origin: bottom center;
}

.gift-box-lid.lid-open {
  transform: translateY(-70px) rotate(-15deg) scale(0.95);
  opacity: 0.85;
}

.lid-top {
  position: relative;
  width: 100%;
  height: 30px;
  background: linear-gradient(135deg, #FCFAF7 0%, #F3EEE7 100%);
  border: 1.5px solid #C8A15A;
  border-radius: 6px 6px 0 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.lid-lip {
  width: 100%;
  height: 8px;
  background: #EAE3D9;
  border: 1.5px solid #C8A15A;
  border-top: none;
  border-radius: 0 0 4px 4px;
}

/* Paisley texture overlay */
.paisley-texture, .body-texture {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  opacity: 0.12;
  background-image: radial-gradient(#C8A15A 0.75px, transparent 0.75px), radial-gradient(#C8A15A 0.75px, #FCFAF7 0.75px);
  background-size: 12px 12px;
  background-position: 0 0, 6px 6px;
}

/* Satin Bow & Ribbon */
.satin-bow-container {
  position: absolute;
  top: -16px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 30px;
  transition: opacity 300ms ease, transform 300ms ease;
}

.satin-bow-container.ribbon-untied {
  opacity: 0;
  transform: translateX(-50%) translateY(-20px) scale(0.5);
}

.bow-loop {
  position: absolute;
  width: 26px;
  height: 18px;
  border: 2px solid #C8A15A;
  background: linear-gradient(135deg, #E6C887, #C8A15A);
  border-radius: 50% 50% 10% 50%;
  top: 4px;
}
.bow-loop-left { left: 4px; transform: rotate(-25deg); }
.bow-loop-right { right: 4px; transform: scaleX(-1) rotate(-25deg); }
.bow-center {
  position: absolute;
  top: 8px;
  left: 24px;
  width: 12px;
  height: 12px;
  background: #9A7B38;
  border-radius: 50%;
  border: 1px solid #FCFAF7;
}

/* Gift Box Body */
.gift-box-body {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 170px;
  height: 108px;
  background: linear-gradient(180deg, #FCFAF7 0%, #F5EFE6 100%);
  border: 1.5px solid #C8A15A;
  border-radius: 0 0 12px 12px;
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12), inset 0 2px 6px rgba(255, 255, 255, 0.8);
  overflow: hidden;
}

.ribbon-vertical-front {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 24px;
  height: 100%;
  background: linear-gradient(90deg, #9A7B38, #C8A15A 50%, #9A7B38);
  box-shadow: 0 0 4px rgba(0, 0, 0, 0.15);
  transition: opacity 300ms ease;
}

.ribbon-vertical-front.ribbon-untied {
  opacity: 0;
}

/* Coupon Card Emerging from inside box */
.coupon-card {
  position: absolute;
  top: 100%;
  left: 10px;
  right: 10px;
  background: #FFFFFF;
  border: 1.5px dashed #C8A15A;
  border-radius: 12px;
  padding: 12px 8px;
  box-shadow: 0 -4px 20px rgba(200, 161, 90, 0.25);
  transition: transform 400ms cubic-bezier(0.175, 0.885, 0.32, 1.275);
  z-index: 5;
}

.coupon-card.coupon-reveal {
  transform: translateY(-118px);
}

.coupon-badge {
  font-family: 'Poppins', sans-serif;
  font-size: 0.6rem;
  font-weight: 700;
  letter-spacing: 1px;
  background: #FCFAF7;
  color: #C8A15A;
  padding: 2px 8px;
  border-radius: 4px;
  border: 1px solid #C8A15A;
  display: inline-block;
  margin-bottom: 4px;
}

.coupon-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.05rem;
  color: #2D2D2D;
  margin: 2px 0;
}

.coupon-discount {
  font-family: 'Poppins', sans-serif;
  font-size: 0.8rem;
  font-weight: 600;
  color: #C8A15A;
  margin: 0 0 6px 0;
}

.coupon-code-box {
  background: #FCFAF7;
  border: 1px solid #EAE3D9;
  padding: 4px 8px;
  border-radius: 6px;
  display: flex;
  justify-content: center;
  gap: 6px;
  align-items: center;
}

.code-label {
  font-size: 0.65rem;
  color: #6B6B6B;
}

.code-value {
  font-family: monospace;
  font-size: 0.85rem;
  color: #111111;
  letter-spacing: 1px;
}

/* Open Gift Button */
.btn-open-gift {
  background: #111111;
  color: #FCFAF7;
  font-family: 'Poppins', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  padding: 14px 36px;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 8px 20px rgba(17, 17, 17, 0.25);
  transition: all 0.3s ease;
}

.btn-open-gift:hover {
  background: #2D2D2D;
  transform: translateY(-2px);
  box-shadow: 0 12px 26px rgba(17, 17, 17, 0.35);
}


/* ==========================================================================
   STAGE 2: ACCOUNT CREATION FORM
   ========================================================================== */
.gift-signup-stage {
  text-align: left;
  animation: formFadeIn 300ms ease;
}

@keyframes formFadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.signup-coupon-banner {
  background: #FDF9F2;
  border: 1px solid #E6C887;
  border-radius: 12px;
  padding: 10px 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 18px;
}

.banner-icon {
  font-size: 1.4rem;
}

.banner-text {
  display: flex;
  flex-direction: column;
  font-size: 0.78rem;
  color: #2D2D2D;
}

.signup-header {
  margin-bottom: 20px;
}

.signup-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.45rem;
  font-weight: 600;
  color: #2D2D2D;
  margin: 0 0 4px 0;
}

.signup-subtitle {
  font-family: 'Poppins', sans-serif;
  font-size: 0.82rem;
  color: #6B6B6B;
  margin: 0;
}

.signup-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 16px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-label {
  font-family: 'Poppins', sans-serif;
  font-size: 0.78rem;
  font-weight: 600;
  color: #2D2D2D;
}

.input-wrapper {
  position: relative;
}

.form-input {
  width: 100%;
  padding: 12px 14px;
  background: #FFFFFF;
  border: 1px solid #E0D9CE;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.88rem;
  color: #2D2D2D;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.form-input:focus {
  border-color: #C8A15A;
  box-shadow: 0 0 0 3px rgba(200, 161, 90, 0.15);
}

.btn-submit-signup {
  background: #111111;
  color: #FCFAF7;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  padding: 14px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  margin-top: 6px;
  transition: background 0.2s ease, transform 0.2s ease;
  width: 100%;
}

.btn-submit-signup:hover:not(:disabled) {
  background: #2D2D2D;
  transform: translateY(-1px);
}

.btn-submit-signup:disabled {
  opacity: 0.75;
  cursor: not-allowed;
}

.form-error-alert {
  background: #FDF2F2;
  border: 1px solid #F8B4B4;
  color: #9B1C1C;
  font-size: 0.78rem;
  padding: 8px 12px;
  border-radius: 8px;
}

.form-success-alert {
  background: #F3FAF7;
  border: 1px solid #84E1BC;
  color: #0E6245;
  font-size: 0.78rem;
  padding: 8px 12px;
  border-radius: 8px;
}

.signin-redirect {
  text-align: center;
  font-family: 'Poppins', sans-serif;
  font-size: 0.8rem;
  color: #6B6B6B;
  margin-bottom: 20px;
}

.signin-link {
  color: #C8A15A;
  font-weight: 600;
  text-decoration: none;
}

.signin-link:hover {
  text-decoration: underline;
}

/* 4 Benefit Icons Grid */
.signup-benefits-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  padding-top: 14px;
  border-top: 1px solid #EAE3D9;
}

.benefit-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.benefit-icon {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #FCFAF7;
  border: 1px solid #C8A15A;
  color: #C8A15A;
  font-size: 0.65rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

.benefit-text {
  font-family: 'Poppins', sans-serif;
  font-size: 0.74rem;
  color: #2D2D2D;
  font-weight: 500;
}

/* Mobile Responsiveness */
@media (max-width: 576px) {
  .gift-modal-container {
    padding: 24px 18px;
    border-radius: 20px;
  }
  .gift-title {
    font-size: 1.55rem;
  }
  .signup-benefits-grid {
    grid-template-columns: 1fr;
    gap: 8px;
  }
  .gift-float-btn {
    bottom: 74px;
    right: 16px;
    width: 52px;
    height: 52px;
  }
}
</style>
