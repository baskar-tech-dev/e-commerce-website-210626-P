<template>
  <div class="selection-section">
    <h3 class="section-title">Choose Color: <span class="selected-color-name" v-if="modelValue">{{ modelValue }}</span></h3>
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
  gap: 6px;
  font-family: 'Poppins', sans-serif;
}

.section-title {
  font-size: 14px;
  font-weight: 600;
  color: #2D2D2D;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 6px;
}

.selected-color-name {
  font-weight: 500;
  color: #7A756F;
  text-transform: capitalize;
}

.color-swatch-list {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.color-swatch-wrapper {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 1px solid #E8DDD3;
  padding: 2px;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 200ms ease;
  position: relative;
}

.color-swatch-wrapper:hover {
  transform: translateY(-1px);
  border-color: #5B163A;
}

.color-swatch-wrapper.selected {
  border-color: #5B163A;
  border-width: 2px;
  transform: scale(1.03);
}

.color-swatch-wrapper.selected:hover {
  transform: translateY(-1px) scale(1.03);
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

/* Tooltip style */
.color-tooltip {
  position: absolute;
  bottom: -28px;
  left: 50%;
  transform: translateX(-50%) translateY(4px);
  background-color: #2D2D2D;
  color: #FFFDF9;
  padding: 3px 6px;
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
