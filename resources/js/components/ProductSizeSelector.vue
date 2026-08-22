<template>
  <div class="selection-section">
    <h3 class="section-title">Choose Size</h3>
    <div class="size-chip-list" role="radiogroup" aria-label="Choose Size">
      <button
        v-for="size in availableSizes"
        :key="size"
        type="button"
        class="size-chip-btn"
        :class="{ selected: modelValue === size }"
        :disabled="isSizeDisabled(size)"
        @click="selectSize(size)"
        role="radio"
        :aria-checked="modelValue === size"
      >
        <Check v-if="modelValue === size" :size="12" stroke-width="3" />
        <span>{{ size }}</span>
      </button>
    </div>
    
    <!-- Size Guide Link Underneath -->
    <div class="size-guide-row">
      <span class="size-guide-prefix">📏 Not sure about your size?</span>
      <a href="#" class="size-guide-link" @click.prevent="openSizeGuide">View Size Guide</a>
    </div>
  </div>
</template>

<script setup>
import { Check } from 'lucide-vue-next';

const props = defineProps({
  availableSizes: {
    type: Array,
    required: true
  },
  modelValue: {
    type: String,
    required: true
  },
  isSizeDisabled: {
    type: Function,
    required: true
  }
});

const emit = defineEmits(['update:modelValue', 'open-size-guide']);

const selectSize = (size) => {
  emit('update:modelValue', size);
};

const openSizeGuide = () => {
  emit('open-size-guide');
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
}

.size-chip-list {
  display: flex;
  gap: 8px; /* Spacing between chips */
  flex-wrap: wrap;
}

.size-chip-btn {
  height: 38px;
  padding: 0 16px; /* Horizontal padding */
  border-radius: 8px; /* Sleek 8px radius */
  background-color: #F8F5F1; /* cream background */
  border: 1px solid #E8DDD3; /* thin border */
  color: #2D2D2D; /* dark text */
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  cursor: pointer;
  transition: all 200ms ease;
  position: relative;
  overflow: hidden;
}

.size-chip-btn:hover:not(:disabled) {
  border-color: #5B163A;
  box-shadow: 0 3px 8px rgba(91, 22, 58, 0.08);
  transform: translateY(-1px);
}

.size-chip-btn.selected {
  background-color: #5B163A;
  border-color: #5B163A;
  color: #FFFDF9;
  box-shadow: 0 4px 12px rgba(91, 22, 58, 0.2);
  transform: scale(1.02);
}

.size-chip-btn.selected:hover {
  transform: translateY(-1px) scale(1.02);
}

.size-chip-btn:focus-visible {
  outline: 2px solid #5B163A;
  outline-offset: 2px;
}

.size-chip-btn:disabled {
  background-color: #f3f3f3;
  border-color: #E8DDD3;
  color: #2D2D2D;
  opacity: 0.4;
  cursor: not-allowed;
}

.size-chip-btn:disabled::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to top right, transparent 48%, #2D2D2D 49%, #2D2D2D 51%, transparent 52%);
  pointer-events: none;
}

.size-guide-row {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 500;
  margin-top: 2px;
}

.size-guide-prefix {
  color: #7A756F;
}

.size-guide-link {
  color: #5B163A;
  text-decoration: none;
  font-weight: 600;
  border-bottom: 1px solid transparent;
  transition: border-color 200ms ease;
}

.size-guide-link:hover {
  border-bottom-color: #5B163A;
}
</style>
