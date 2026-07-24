<template>
  <div 
    v-if="isEnabled && activeItems.length > 0"
    class="announcement-bar"
    :class="{ 'announcement-bar--sticky': config.is_sticky }"
    :style="{
      backgroundColor: '#6E1F3A',
      color: '#FFFFFF'
    }"
    role="region"
    aria-label="Announcement Banner"
  >
    <div class="announcement-bar__container">
      <div 
        class="carousel-wrapper"
        @mouseenter="stopAutoPlay"
        @mouseleave="startAutoPlay"
      >
        <button 
          v-if="activeItems.length > 1" 
          @click="prevSlide" 
          class="carousel-btn carousel-btn--prev"
          aria-label="Previous announcement"
        >
          ‹
        </button>
        
        <div class="carousel-content-area">
          <Transition name="announcement-luxury-transition" mode="out-in">
            <component 
              :is="activeItems[currentIndex].link ? 'router-link' : 'div'"
              :to="activeItems[currentIndex].link ? activeItems[currentIndex].link : undefined"
              :key="currentIndex" 
              class="announcement-item-carousel"
              :class="{ 'announcement-item-carousel--linked': activeItems[currentIndex].link }"
            >
              <span class="announcement-content">
                {{ activeItems[currentIndex].text }}
              </span>
            </component>
          </Transition>
        </div>

        <button 
          v-if="activeItems.length > 1" 
          @click="nextSlide" 
          class="carousel-btn carousel-btn--next"
          aria-label="Next announcement"
        >
          ›
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  // Allows injecting local settings (e.g. for the live preview dashboard)
  previewItems: {
    type: Array,
    default: null
  },
  previewConfig: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['config-loaded']);

const items = ref([]);
const config = ref({
  is_enabled: true,
  mode: 'slide',
  speed: 3,
  background_color: '#6E1F3A',
  text_color: '#FFFFFF',
  is_sticky: true
});

const loading = ref(false);
const currentIndex = ref(0);
let autoPlayInterval = null;

// Fetch settings from API
const fetchAnnouncements = async () => {
  if (props.previewItems || props.previewConfig) {
    // If preview props are supplied, use them instead of API
    return;
  }
  loading.value = true;
  try {
    const response = await axios.get('/api/storefront/announcements');
    if (response.data && response.data.success) {
      items.value = response.data.items || [];
      config.value = { ...config.value, ...response.data.config };
    }
  } catch (err) {
    console.error('Failed to load announcements:', err);
  } finally {
    loading.value = false;
  }
};

// Compute effective values based on preview or fetched data
const effectiveItems = computed(() => {
  return props.previewItems ? props.previewItems : items.value;
});

const effectiveConfig = computed(() => {
  return props.previewConfig ? props.previewConfig : config.value;
});

const isEnabled = computed(() => {
  return effectiveConfig.value.is_enabled !== false;
});

const activeItems = computed(() => {
  return effectiveItems.value.filter(item => item.is_active);
});

// Auto-slide mechanisms for luxury slide mode
const startAutoPlay = () => {
  stopAutoPlay();
  if (activeItems.value.length <= 1) return;

  const intervalTime = 3500; // Stay visible for exactly 3.5 seconds
  autoPlayInterval = setInterval(() => {
    nextSlide();
  }, intervalTime);
};

const stopAutoPlay = () => {
  if (autoPlayInterval) {
    clearInterval(autoPlayInterval);
    autoPlayInterval = null;
  }
};

const nextSlide = () => {
  if (activeItems.value.length === 0) return;
  currentIndex.value = (currentIndex.value + 1) % activeItems.value.length;
};

const prevSlide = () => {
  if (activeItems.value.length === 0) return;
  currentIndex.value = (currentIndex.value - 1 + activeItems.value.length) % activeItems.value.length;
};

// Reset index on items change
watch(activeItems, () => {
  currentIndex.value = 0;
  startAutoPlay();
}, { deep: true });

onMounted(() => {
  fetchAnnouncements();
  
  if (props.previewItems) {
    watch(() => props.previewItems, () => {
      // triggers recalculation
    }, { deep: true });
  }
  if (props.previewConfig) {
    watch(() => props.previewConfig, (newVal) => {
      config.value = { ...config.value, ...newVal };
      startAutoPlay();
    }, { deep: true, immediate: true });
  }

  startAutoPlay();
});

watch([isEnabled, activeItems, effectiveConfig], () => {
  emit('config-loaded', {
    is_enabled: isEnabled.value,
    is_sticky: effectiveConfig.value.is_sticky,
    active_count: activeItems.value.length
  });
}, { deep: true, immediate: true });

onUnmounted(() => {
  stopAutoPlay();
});
</script>

<style scoped>
.announcement-bar {
  width: 100%;
  overflow: hidden;
  font-family: 'Poppins', sans-serif;
  font-size: 0.85rem;
  font-weight: 500;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  z-index: 1050;
  background-color: #6E1F3A !important; /* Deep maroon background */
  color: #FFFFFF !important; /* White typography */
  transition: background-color 0.3s ease, color 0.3s ease;
}

.announcement-bar--sticky {
  position: sticky;
  top: 0;
}

.announcement-bar__container {
  max-width: 1400px;
  margin: 0 auto;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 0 1rem;
}

.carousel-wrapper {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 40px;
}

.carousel-content-area {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  height: 100%;
  position: relative;
}

.announcement-item-carousel {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 0.4rem 1rem;
  width: 100%;
  height: 100%;
  text-decoration: none;
  color: inherit;
  /* GPU acceleration for 60fps */
  will-change: transform, opacity;
  backface-visibility: hidden;
}

.announcement-item-carousel--linked {
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.announcement-item-carousel--linked:hover {
  opacity: 0.85;
}

.announcement-content {
  color: inherit;
  text-decoration: none;
  outline: none;
}

.carousel-btn {
  background: transparent;
  border: none;
  color: inherit;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  opacity: 0.6;
  transition: opacity 0.2s ease, transform 0.1s ease;
  user-select: none;
  z-index: 10;
}

.carousel-btn:hover {
  opacity: 1;
  transform: scale(1.15);
}

.carousel-btn:active {
  transform: scale(0.9);
}

/* --- PREMIUM LUXURY TRANSITIONS --- */
/* Power4 Out on Entry: enters from left (-100%) to center (0%) quickly */
.announcement-luxury-transition-enter-active {
  transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), opacity 0.4s ease-out;
}

/* Power4 In on Exit: exits smoothly from center (0%) to right (100%) */
.announcement-luxury-transition-leave-active {
  transition: transform 0.4s cubic-bezier(0.895, 0.03, 0.685, 0.22), opacity 0.4s ease-in;
  position: absolute;
}

.announcement-luxury-transition-enter-from {
  transform: translateX(-100%);
  opacity: 0;
}

.announcement-luxury-transition-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
