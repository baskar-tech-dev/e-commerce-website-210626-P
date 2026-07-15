<template>
  <div class="selection-section">
    <h3 class="section-title">Choose Color</h3>
    <div class="color-swatch-list" role="radiogroup" aria-label="Choose Color">
      <div 
        v-for="color in availableColors" 
        :key="color"
        class="color-swatch-wrapper"
        :class="{ selected: modelValue === color }"
        @click="selectColor(color)"
        :title="color"
        role="radio"
        :aria-checked="modelValue === color"
        tabindex="0"
        @keydown.space.prevent="selectColor(color)"
        @keydown.enter.prevent="selectColor(color)"
      >
        <div 
          class="color-swatch" 
          :style="{ backgroundColor: getColorHex(color) }"
        >
          <Check v-if="modelValue === color" :size="10" stroke-width="3.5" style="color: #ffffff; mix-blend-mode: difference;" />
        </div>
        <span class="color-tooltip">{{ color }}</span>
      </div>
    </div>
    <!-- Color Name Label Underneath -->
    <div class="color-name-label" v-if="modelValue">
      {{ modelValue }}
    </div>
  </div>
</template>

<script setup>
import { Check } from 'lucide-vue-next';

const props = defineProps({
  availableColors: {
    type: Array,
    required: true
  },
  modelValue: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['update:modelValue']);

const selectColor = (color) => {
  emit('update:modelValue', color);
};

const getColorHex = (colorName) => {
  if (!colorName) return '#ccc';
  const name = colorName.trim().toLowerCase();
  const map = {
    'mustard yellow': '#e1ad01',
    'mustard': '#e1ad01',
    'deep maroon': '#5B163A',
    'maroon': '#800000',
    'zari gold': '#d4af37',
    'gold': '#d4af37',
    'warm white': '#fffcf7',
    'cream': '#f8f5f1',
    'dark charcoal': '#2d2d2d',
    'charcoal': '#36454F',
    'black': '#000000',
    'white': '#ffffff',
    'red': '#b91c1c',
    'blue': '#1d4ed8',
    'navy': '#1e3a8a',
    'green': '#15803d',
    'olive': '#556b2f',
    'olive green': '#556b2f',
    'pink': '#db2777',
    'yellow': '#facc15',
    'orange': '#f97316',
    'purple': '#7e22ce',
    'grey': '#6b7280',
    'gray': '#6b7280',
    'beige': '#f5f5dc',
    'mustard gold': '#e1ad01',
    'plum': '#4d002b',
    'wine': '#722f37',
  };
  return map[name] || name;
};
</script>

<style scoped>
.selection-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  font-family: 'Poppins', sans-serif;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #2D2D2D;
  margin: 0;
}

.color-swatch-list {
  display: flex;
  gap: 16px; /* Spacing conforming to 8-point system */
  flex-wrap: wrap;
  align-items: center;
}

.color-swatch-wrapper {
  width: 40px; /* 40px diameter */
  height: 40px; /* 40px diameter */
  border-radius: 50%;
  border: 1px solid #E8DDD3; /* Subtle border */
  padding: 3px; /* Padding for double border effect */
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 200ms ease; /* 200ms ease transition */
  position: relative;
}

.color-swatch-wrapper:hover {
  transform: translateY(-2px); /* Hover translate animation */
  border-color: #5B163A; /* Border changes to primary */
}

.color-swatch-wrapper.selected {
  border-color: #5B163A; /* Maroon border */
  border-width: 2px;
  transform: scale(1.03); /* selected scale animation */
}

.color-swatch-wrapper.selected:hover {
  transform: translateY(-2px) scale(1.03);
}

.color-swatch-wrapper:focus-visible {
  outline: 2px solid #5B163A;
  outline-offset: 2px;
}

.color-swatch {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 1px solid rgba(0, 0, 0, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
}

.color-name-label {
  font-size: 14px;
  font-weight: 500;
  color: #7A756F;
  margin-top: 4px;
  text-transform: capitalize;
}

/* Tooltip style */
.color-tooltip {
  position: absolute;
  bottom: -32px;
  left: 50%;
  transform: translateX(-50%) translateY(4px);
  background-color: #2D2D2D;
  color: #FFFDF9;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 200ms ease;
  z-index: 20;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
}

.color-swatch-wrapper:hover .color-tooltip {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(0);
}
</style>
