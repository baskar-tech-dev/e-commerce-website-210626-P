<template>
  <div 
    class="festive-icon-root" 
    :class="[`festive-icon--${computedSize}`, { 'festive-icon--emoji': isEmoji }]"
    aria-hidden="true"
  >
    <!-- 1. Diya / Deepam (Default Festive Tradition) -->
    <svg 
      v-if="normalizedIcon === 'diya'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-diya-svg"
    >
      <defs>
        <linearGradient :id="`fDiyaBowl-${uid}`" x1="4" y1="34" x2="44" y2="48" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FCE7B0"/>
          <stop offset="0.4" stop-color="#C8A15A"/>
          <stop offset="1" stop-color="#6E1F3A"/>
        </linearGradient>
        <linearGradient :id="`fDiyaFlame-${uid}`" x1="24" y1="5" x2="24" y2="30" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FFFFFF"/>
          <stop offset="0.25" stop-color="#FEF08A"/>
          <stop offset="0.65" stop-color="#F59E0B"/>
          <stop offset="1" stop-color="#DC2626"/>
        </linearGradient>
        <radialGradient :id="`fFlameAura-${uid}`" cx="0.5" cy="0.5" r="0.5">
          <stop stop-color="#FBBF24" stop-opacity="0.8"/>
          <stop offset="1" stop-color="#FBBF24" stop-opacity="0"/>
        </radialGradient>
      </defs>
      <!-- Base Brass Bowl -->
      <path 
        d="M5 34 C5 34 10 47 24 47 C38 47 43 34 43 34 C43 34 34 37.5 24 37.5 C14 37.5 5 34 5 34 Z" 
        :fill="`url(#fDiyaBowl-${uid})`" 
        stroke="#C8A15A" 
        stroke-width="1.3"
      />
      <!-- Rim -->
      <ellipse cx="24" cy="34" rx="19" ry="4.2" fill="#FDF4DC" stroke="#A88234" stroke-width="1"/>
      <ellipse cx="24" cy="34" rx="14" ry="2.6" fill="#7A561E" opacity="0.6"/>
      <!-- Flame Aura -->
      <circle cx="24" cy="18" r="14" :fill="`url(#fFlameAura-${uid})`" class="festive-flame-aura"/>
      <!-- Flickering Flame -->
      <path 
        d="M24 5 C24 5 16 16.5 16 22 C16 26.4 19.6 30 24 30 C28.4 30 32 26.4 32 22 C32 16.5 24 5 24 5 Z" 
        :fill="`url(#fDiyaFlame-${uid})`" 
        class="festive-flame-flicker"
      />
      <!-- Inner White Hot Core -->
      <ellipse cx="24" cy="23" rx="3.2" ry="5.2" fill="#FFFFFF" opacity="0.95"/>
    </svg>

    <!-- 2. Hanging Moroccan / Festive Brass Lantern -->
    <svg 
      v-else-if="normalizedIcon === 'lantern'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-lantern-svg"
    >
      <defs>
        <linearGradient :id="`fLanternGold-${uid}`" x1="0" y1="0" x2="48" y2="52" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FDE047"/>
          <stop offset="0.5" stop-color="#C8A15A"/>
          <stop offset="1" stop-color="#78350F"/>
        </linearGradient>
        <radialGradient :id="`fLanternGlass-${uid}`" cx="50%" cy="50%" r="50%">
          <stop offset="0%" stop-color="#FEF08A" stop-opacity="0.95"/>
          <stop offset="60%" stop-color="#F59E0B" stop-opacity="0.7"/>
          <stop offset="100%" stop-color="#B45309" stop-opacity="0.4"/>
        </radialGradient>
      </defs>
      <!-- Top Ring -->
      <circle cx="24" cy="6" r="3.5" stroke="#C8A15A" stroke-width="1.4" fill="none"/>
      <!-- Top Cap / Dome -->
      <path d="M18 10 L30 10 L33 16 L15 16 Z" :fill="`url(#fLanternGold-${uid})`" stroke="#9A7B38" stroke-width="1"/>
      <path d="M21 8 L27 8 L28 10 L20 10 Z" fill="#FDE047"/>
      <!-- Glass Body -->
      <polygon points="15,16 33,16 30,38 18,38" :fill="`url(#fLanternGlass-${uid})`" stroke="#C8A15A" stroke-width="1.2"/>
      <!-- Glass Bevel Struts -->
      <line x1="24" y1="16" x2="24" y2="38" stroke="#FDE047" stroke-width="1" opacity="0.8"/>
      <line x1="19" y1="16" x2="21" y2="38" stroke="#9A7B38" stroke-width="0.8" opacity="0.6"/>
      <line x1="29" y1="16" x2="27" y2="38" stroke="#9A7B38" stroke-width="0.8" opacity="0.6"/>
      <!-- Inner Candle Flame -->
      <circle cx="24" cy="27" r="6" fill="#FBBF24" opacity="0.5" class="festive-flame-aura"/>
      <ellipse cx="24" cy="27" rx="2.5" ry="4" fill="#FFFBEB"/>
      <ellipse cx="24" cy="28" rx="1.5" ry="2.2" fill="#F59E0B"/>
      <!-- Bottom Base & Finial -->
      <path d="M17 38 L31 38 L28 43 L20 43 Z" :fill="`url(#fLanternGold-${uid})`" stroke="#9A7B38" stroke-width="1"/>
      <path d="M24 43 L24 49" stroke="#C8A15A" stroke-width="1.5" stroke-linecap="round"/>
      <circle cx="24" cy="49" r="1.8" fill="#FDE047"/>
    </svg>

    <!-- 3. Royal Crescent Moon & Radiant Star (Eid / Royal) -->
    <svg 
      v-else-if="normalizedIcon === 'crescent'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-crescent-svg"
    >
      <defs>
        <linearGradient :id="`fCrescentGold-${uid}`" x1="6" y1="6" x2="42" y2="46" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FFFBEB"/>
          <stop offset="0.3" stop-color="#FCD34D"/>
          <stop offset="0.75" stop-color="#C8A15A"/>
          <stop offset="1" stop-color="#78350F"/>
        </linearGradient>
      </defs>
      <!-- Crescent -->
      <path 
        d="M26 8 C15.5 8 7 16.5 7 27 C7 37.5 15.5 46 26 46 C32.5 46 38.2 42.7 41.5 37.8 C34 39.5 25.5 35 23 27 C21 20.5 25 13 32 9.5 C30.1 8.5 28.1 8 26 8 Z" 
        :fill="`url(#fCrescentGold-${uid})`" 
        stroke="#C8A15A" 
        stroke-width="1.2"
      />
      <!-- Radiant Star -->
      <g transform="translate(32, 23)" class="festive-star-shimmer">
        <path d="M0 -7 L1.8 -2.2 L6.6 -2.2 L2.7 1 L4.2 5.8 L0 2.8 L-4.2 5.8 L-2.7 1 L-6.6 -2.2 L-1.8 -2.2 Z" fill="#FDE047" stroke="#C8A15A" stroke-width="0.8"/>
        <circle cx="0" cy="0" r="1.8" fill="#FFFFFF"/>
      </g>
    </svg>

    <!-- 4. Auspicious Brass Kalash / Pongal Pot (Pongal / Sankranti / Traditional) -->
    <svg 
      v-else-if="normalizedIcon === 'pongal_pot'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-pongal-svg"
    >
      <defs>
        <linearGradient :id="`fPotGold-${uid}`" x1="8" y1="18" x2="40" y2="48" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FFFBEB"/>
          <stop offset="0.35" stop-color="#FBBF24"/>
          <stop offset="0.75" stop-color="#C8A15A"/>
          <stop offset="1" stop-color="#6E1F3A"/>
        </linearGradient>
      </defs>
      <!-- Coconut Topper -->
      <path d="M24 6 C20 10 20 15 24 18 C28 15 28 10 24 6 Z" fill="#78350F" stroke="#9A7B38" stroke-width="1"/>
      <!-- Mango Leaves -->
      <path d="M24 16 C17 12 11 16 12 21 C17 21 21 19 24 16 Z" fill="#15803D" stroke="#166534" stroke-width="0.8"/>
      <path d="M24 16 C31 12 37 16 36 21 C31 21 27 19 24 16 Z" fill="#15803D" stroke="#166534" stroke-width="0.8"/>
      <!-- Pot Neck -->
      <ellipse cx="24" cy="20" rx="9" ry="2.5" fill="#E6C887" stroke="#A88234" stroke-width="1"/>
      <!-- Overflowing Sacred Froth / Rice Milk -->
      <path d="M16 20 C16 25 19 26 21 23 C23 27 26 27 28 23 C30 26 32 24 32 20 Z" fill="#FFFFFF" stroke="#FDE047" stroke-width="0.8"/>
      <!-- Pot Belly -->
      <path 
        d="M15 20 C10 26 10 37 15 43 C18 46 30 46 33 43 C38 37 38 26 33 20 Z" 
        :fill="`url(#fPotGold-${uid})`" 
        stroke="#C8A15A" 
        stroke-width="1.3"
      />
      <!-- Decorative Tilak / Swastik Stripe -->
      <ellipse cx="24" cy="33" rx="10" ry="2.2" stroke="#DC2626" stroke-width="1.2" fill="none"/>
      <circle cx="24" cy="33" r="2.2" fill="#DC2626"/>
      <!-- Base Stand -->
      <path d="M18 45 L30 45 L28 48 L20 48 Z" fill="#C8A15A" stroke="#9A7B38" stroke-width="0.8"/>
    </svg>

    <!-- 5. Golden Sparkles / 8-Point Wonder Star (Universal Celebration) -->
    <svg 
      v-else-if="normalizedIcon === 'sparkle'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-sparkle-svg"
    >
      <defs>
        <linearGradient :id="`fSparkleGold-${uid}`" x1="0" y1="0" x2="48" y2="52" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FFFFFF"/>
          <stop offset="0.3" stop-color="#FEF08A"/>
          <stop offset="0.7" stop-color="#C8A15A"/>
          <stop offset="1" stop-color="#B45309"/>
        </linearGradient>
      </defs>
      <!-- Center Radiant Star (8-point faceted) -->
      <path 
        d="M24 6 Q24 24 6 26 Q24 26 24 46 Q24 26 42 26 Q24 24 24 6 Z" 
        :fill="`url(#fSparkleGold-${uid})`" 
        stroke="#EAB308" 
        stroke-width="1"
      />
      <!-- Diagonal Shorter Rays -->
      <path 
        d="M24 16 Q24 26 14 26 Q24 26 24 36 Q24 26 34 26 Q24 26 24 16 Z" 
        fill="#FFFFFF" 
        opacity="0.85"
      />
      <circle cx="24" cy="26" r="3.5" fill="#FFFFFF"/>
      <!-- Small Companion Sparkles -->
      <circle cx="10" cy="12" r="1.8" fill="#FDE047"/>
      <circle cx="38" cy="14" r="1.5" fill="#FDE047"/>
      <circle cx="37" cy="40" r="1.8" fill="#FDE047"/>
    </svg>

    <!-- 6. Luxury Gift Box (Shopping / Welcome Gift) -->
    <svg 
      v-else-if="normalizedIcon === 'gift'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-gift-svg"
    >
      <defs>
        <linearGradient :id="`fGiftBox-${uid}`" x1="0" y1="18" x2="48" y2="48" gradientUnits="userSpaceOnUse">
          <stop stop-color="#831843"/>
          <stop offset="0.5" stop-color="#6E1F3A"/>
          <stop offset="1" stop-color="#4A044E"/>
        </linearGradient>
        <linearGradient :id="`fGiftRibbon-${uid}`" x1="10" y1="10" x2="38" y2="48" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FFFBEB"/>
          <stop offset="0.5" stop-color="#FBBF24"/>
          <stop offset="1" stop-color="#C8A15A"/>
        </linearGradient>
      </defs>
      <!-- Bow Loops -->
      <path d="M24 16 C18 9 14 11 16 16 C18 20 23 18 24 16 Z" :fill="`url(#fGiftRibbon-${uid})`" stroke="#C8A15A" stroke-width="0.8"/>
      <path d="M24 16 C30 9 34 11 32 16 C30 20 25 18 24 16 Z" :fill="`url(#fGiftRibbon-${uid})`" stroke="#C8A15A" stroke-width="0.8"/>
      <circle cx="24" cy="16" r="2.2" fill="#FDE047"/>
      <!-- Box Lid -->
      <rect x="8" y="16" width="32" height="7" rx="2" :fill="`url(#fGiftBox-${uid})`" stroke="#C8A15A" stroke-width="1.2"/>
      <rect x="22" y="16" width="4" height="7" :fill="`url(#fGiftRibbon-${uid})`"/>
      <!-- Box Base -->
      <rect x="11" y="23" width="26" height="23" rx="2" :fill="`url(#fGiftBox-${uid})`" stroke="#C8A15A" stroke-width="1.2"/>
      <rect x="22" y="23" width="4" height="23" :fill="`url(#fGiftRibbon-${uid})`"/>
      <rect x="11" y="32" width="26" height="4" :fill="`url(#fGiftRibbon-${uid})`"/>
    </svg>

    <!-- 7. Sacred Royal Lotus (Auspicious / Navratri / Lakshmi) -->
    <svg 
      v-else-if="normalizedIcon === 'lotus'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-lotus-svg"
    >
      <defs>
        <linearGradient :id="`fLotusGrad-${uid}`" x1="10" y1="10" x2="38" y2="44" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FFF1F2"/>
          <stop offset="0.4" stop-color="#F472B6"/>
          <stop offset="0.8" stop-color="#C8A15A"/>
          <stop offset="1" stop-color="#6E1F3A"/>
        </linearGradient>
      </defs>
      <!-- Outer Petals -->
      <path d="M24 40 C14 36 6 28 8 20 C14 20 20 26 24 34 Z" :fill="`url(#fLotusGrad-${uid})`" stroke="#C8A15A" stroke-width="1"/>
      <path d="M24 40 C34 36 42 28 40 20 C34 20 28 26 24 34 Z" :fill="`url(#fLotusGrad-${uid})`" stroke="#C8A15A" stroke-width="1"/>
      <!-- Intermediate Petals -->
      <path d="M24 38 C17 32 12 21 16 14 C21 17 23 26 24 34 Z" :fill="`url(#fLotusGrad-${uid})`" stroke="#C8A15A" stroke-width="1"/>
      <path d="M24 38 C31 32 36 21 32 14 C27 17 25 26 24 34 Z" :fill="`url(#fLotusGrad-${uid})`" stroke="#C8A15A" stroke-width="1"/>
      <!-- Central Lotus Petal -->
      <path d="M24 10 C21 16 19 26 24 36 C29 26 27 16 24 10 Z" fill="#FFFBEB" stroke="#FBBF24" stroke-width="1.2"/>
      <circle cx="24" cy="24" r="2" fill="#FDE047"/>
    </svg>

    <!-- 8. Festive Firecracker / Sparkler Burst -->
    <svg 
      v-else-if="normalizedIcon === 'firecracker'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-firecracker-svg"
    >
      <defs>
        <linearGradient :id="`fCrackerGrad-${uid}`" x1="0" y1="0" x2="48" y2="52" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FDE047"/>
          <stop offset="0.5" stop-color="#DC2626"/>
          <stop offset="1" stop-color="#6E1F3A"/>
        </linearGradient>
      </defs>
      <!-- Spark Trails -->
      <line x1="24" y1="6" x2="24" y2="12" stroke="#FDE047" stroke-width="1.8" stroke-linecap="round"/>
      <line x1="38" y1="12" x2="33" y2="17" stroke="#FBBF24" stroke-width="1.8" stroke-linecap="round"/>
      <line x1="42" y1="26" x2="36" y2="26" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round"/>
      <line x1="10" y1="12" x2="15" y2="17" stroke="#FDE047" stroke-width="1.8" stroke-linecap="round"/>
      <line x1="6" y1="26" x2="12" y2="26" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round"/>
      <!-- Rocket Body -->
      <polygon points="24,14 28,24 20,24" fill="#DC2626" stroke="#C8A15A" stroke-width="1"/>
      <rect x="21" y="24" width="6" height="18" rx="1" :fill="`url(#fCrackerGrad-${uid})`" stroke="#C8A15A" stroke-width="1"/>
      <line x1="24" y1="42" x2="24" y2="50" stroke="#78350F" stroke-width="1.5" stroke-linecap="round"/>
      <circle cx="24" cy="14" r="3.5" fill="#FFFFFF" class="festive-flame-aura"/>
    </svg>

    <!-- 9. Modern Festive Tree (Christmas & Holiday) -->
    <svg 
      v-else-if="normalizedIcon === 'tree'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-tree-svg"
    >
      <defs>
        <linearGradient :id="`fTreeGrad-${uid}`" x1="12" y1="12" x2="36" y2="44" gradientUnits="userSpaceOnUse">
          <stop stop-color="#4ADE80"/>
          <stop offset="0.5" stop-color="#15803D"/>
          <stop offset="1" stop-color="#064E3B"/>
        </linearGradient>
      </defs>
      <!-- Star on Top -->
      <polygon points="24,5 25.5,9 29.5,9 26.5,11.5 27.5,15.5 24,13 20.5,15.5 21.5,11.5 18.5,9 22.5,9" fill="#FDE047" stroke="#C8A15A" stroke-width="0.8"/>
      <!-- Tree Tiers -->
      <polygon points="24,13 32,22 16,22" :fill="`url(#fTreeGrad-${uid})`" stroke="#C8A15A" stroke-width="1"/>
      <polygon points="24,20 35,31 13,31" :fill="`url(#fTreeGrad-${uid})`" stroke="#C8A15A" stroke-width="1"/>
      <polygon points="24,28 38,41 10,41" :fill="`url(#fTreeGrad-${uid})`" stroke="#C8A15A" stroke-width="1"/>
      <!-- Trunk -->
      <rect x="21" y="41" width="6" height="7" fill="#78350F" stroke="#9A7B38" stroke-width="0.8"/>
      <!-- Baubles -->
      <circle cx="24" cy="21" r="1.5" fill="#DC2626"/>
      <circle cx="19" cy="30" r="1.5" fill="#FDE047"/>
      <circle cx="29" cy="29" r="1.5" fill="#DC2626"/>
      <circle cx="17" cy="39" r="1.5" fill="#FDE047"/>
      <circle cx="31" cy="39" r="1.5" fill="#FDE047"/>
    </svg>

    <!-- 10. Royal Heritage Crown -->
    <svg 
      v-else-if="normalizedIcon === 'crown'" 
      viewBox="0 0 48 52" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg" 
      class="festive-icon-svg festive-crown-svg"
    >
      <defs>
        <linearGradient :id="`fCrownGrad-${uid}`" x1="6" y1="14" x2="42" y2="44" gradientUnits="userSpaceOnUse">
          <stop stop-color="#FFFBEB"/>
          <stop offset="0.4" stop-color="#FBBF24"/>
          <stop offset="0.75" stop-color="#C8A15A"/>
          <stop offset="1" stop-color="#78350F"/>
        </linearGradient>
      </defs>
      <!-- Crown Base Band -->
      <rect x="9" y="37" width="30" height="6" rx="2" :fill="`url(#fCrownGrad-${uid})`" stroke="#C8A15A" stroke-width="1.2"/>
      <circle cx="15" cy="40" r="1.2" fill="#DC2626"/>
      <circle cx="24" cy="40" r="1.4" fill="#15803D"/>
      <circle cx="33" cy="40" r="1.2" fill="#DC2626"/>
      <!-- Crown Spikes -->
      <path 
        d="M9 37 L9 22 L16 30 L24 14 L32 30 L39 22 L39 37 Z" 
        :fill="`url(#fCrownGrad-${uid})`" 
        stroke="#C8A15A" 
        stroke-width="1.3"
      />
      <!-- Jewels on Spikes -->
      <circle cx="9" cy="21" r="2.2" fill="#FDE047" stroke="#9A7B38" stroke-width="0.8"/>
      <circle cx="24" cy="13" r="2.8" fill="#FFFBEB" stroke="#9A7B38" stroke-width="1"/>
      <circle cx="39" cy="21" r="2.2" fill="#FDE047" stroke="#9A7B38" stroke-width="0.8"/>
      <circle cx="24" cy="26" r="2" fill="#DC2626"/>
    </svg>

    <!-- 11. Generic Emoji / Custom Character Fallback -->
    <div v-else class="festive-emoji-wrapper">
      <span class="festive-emoji-text">{{ displayEmoji }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  name: {
    type: String,
    default: 'diya'
  },
  size: {
    type: String,
    default: 'constant' // 'mini' | 'hero' | 'constant' | 'revealed' | 'preview'
  }
});

// Generate pseudo unique id for SVG gradients to prevent DOM id collisions
const uid = Math.random().toString(36).substring(2, 8);

const computedSize = computed(() => props.size || 'constant');

const normalizedIcon = computed(() => {
  const raw = (props.name || 'diya').toString().trim().toLowerCase();
  if (raw === 'diya' || raw === '🪔' || raw === 'diyas_toran' || raw === 'deepam') return 'diya';
  if (raw === 'lantern' || raw === '🏮' || raw === 'lanterns') return 'lantern';
  if (raw === 'crescent' || raw === '🌙' || raw === 'moon' || raw === 'eid') return 'crescent';
  if (raw === 'pongal_pot' || raw === '🌾' || raw === '🏺' || raw === 'pongal' || raw === 'kalash') return 'pongal_pot';
  if (raw === 'sparkle' || raw === '✨' || raw === 'star' || raw === 'stars') return 'sparkle';
  if (raw === 'gift' || raw === '🎁' || raw === 'box') return 'gift';
  if (raw === 'lotus' || raw === '🪷') return 'lotus';
  if (raw === 'firecracker' || raw === '🎆' || raw === 'cracker') return 'firecracker';
  if (raw === 'tree' || raw === '🎄' || raw === 'xmas') return 'tree';
  if (raw === 'crown' || raw === '👑') return 'crown';
  return 'custom';
});

const isEmoji = computed(() => normalizedIcon.value === 'custom');

const displayEmoji = computed(() => {
  return props.name || '✨';
});
</script>

<style scoped>
.festive-icon-root {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  position: relative;
  line-height: 1;
}

.festive-icon-svg {
  width: 100%;
  height: 100%;
  display: block;
}

/* Size presets */
.festive-icon--mini {
  width: 26px;
  height: 26px;
}
.festive-icon--mini .festive-icon-svg {
  filter: drop-shadow(0 2px 4px rgba(200, 161, 90, 0.45));
}

.festive-icon--revealed {
  width: 34px;
  height: 34px;
}
.festive-icon--revealed .festive-icon-svg {
  filter: drop-shadow(0 2px 8px rgba(200, 161, 90, 0.55));
}

.festive-icon--hero {
  width: 44px;
  height: 48px;
}
.festive-icon--hero .festive-icon-svg {
  filter: drop-shadow(0 4px 12px rgba(200, 161, 90, 0.6));
}

.festive-icon--constant {
  width: 44px;
  height: 48px;
}
.festive-icon--constant .festive-icon-svg {
  filter: drop-shadow(0 4px 14px rgba(200, 161, 90, 0.5));
}

.festive-icon--preview {
  width: 38px;
  height: 38px;
}
.festive-icon--preview .festive-icon-svg {
  filter: drop-shadow(0 2px 6px rgba(200, 161, 90, 0.4));
}

/* Animations */
.festive-flame-flicker {
  transform-origin: 24px 22px;
  animation: festiveFlicker 1.8s ease-in-out infinite alternate;
}

.festive-flame-aura {
  animation: festivePulseAura 2s ease-in-out infinite alternate;
}

.festive-star-shimmer {
  transform-origin: 0 0;
  animation: festiveShimmer 2.4s ease-in-out infinite alternate;
}

.festive-emoji-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(200, 161, 90, 0.25) 0%, transparent 70%);
}

.festive-emoji-text {
  font-size: 1.5rem;
  line-height: 1;
  filter: drop-shadow(0 2px 6px rgba(200, 161, 90, 0.5));
}

.festive-icon--mini .festive-emoji-text {
  font-size: 1.15rem;
}
.festive-icon--hero .festive-emoji-text {
  font-size: 1.6rem;
}
.festive-icon--constant .festive-emoji-text {
  font-size: 1.7rem;
}

@keyframes festiveFlicker {
  0% { transform: scale(1) rotate(0deg); opacity: 0.95; }
  50% { transform: scale(1.05, 0.96) rotate(-1.5deg); opacity: 1; }
  100% { transform: scale(0.97, 1.04) rotate(1.5deg); opacity: 0.92; }
}

@keyframes festivePulseAura {
  0% { transform: scale(0.92); opacity: 0.55; }
  100% { transform: scale(1.15); opacity: 0.95; }
}

@keyframes festiveShimmer {
  0% { transform: scale(0.9) rotate(-4deg); opacity: 0.8; }
  100% { transform: scale(1.12) rotate(4deg); opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
  .festive-flame-flicker,
  .festive-flame-aura,
  .festive-star-shimmer {
    animation: none !important;
  }
}
</style>
