<template>
  <div class="quantity-selector-container" :class="{ disabled: disabled }">
    <button 
      type="button" 
      class="qty-btn dec-btn" 
      @click="decrement"
      :disabled="disabled || modelValue <= 1"
      aria-label="Decrease quantity"
    >
      −
    </button>
    <span class="qty-display">{{ modelValue }}</span>
    <button 
      type="button" 
      class="qty-btn inc-btn" 
      @click="increment"
      :disabled="disabled || modelValue >= max"
      aria-label="Increase quantity"
    >
      +
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Number,
    required: true
  },
  max: {
    type: Number,
    default: 1
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const increment = () => {
  if (props.modelValue < props.max) {
    emit('update:modelValue', props.modelValue + 1);
  }
};

const decrement = () => {
  if (props.modelValue > 1) {
    emit('update:modelValue', props.modelValue - 1);
  }
};
</script>

<style scoped>
.quantity-selector-container {
  display: flex;
  align-items: center;
  border-radius: 12px; /* 12px border radius */
  border: 1px solid #E8DDD3; /* Border color */
  background-color: #F8F5F1;
  height: 44px; /* 44px height */
  overflow: hidden;
  width: 120px;
  justify-content: space-between;
  padding: 0 4px;
  font-family: 'Poppins', sans-serif;
  transition: opacity 200ms ease;
}

.quantity-selector-container.disabled {
  opacity: 0.5;
  pointer-events: none;
}

.qty-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: transparent;
  border: none;
  font-size: 1.2rem;
  color: #2D2D2D;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 200ms ease, transform 200ms ease, color 200ms ease;
}

.qty-btn:hover:not(:disabled) {
  background-color: #5B163A; /* Primary color */
  color: #FFFDF9;
  transform: scale(1.08);
}

.qty-btn:active:not(:disabled) {
  transform: scale(0.95);
}

.qty-btn:disabled {
  color: #c0c0c0;
  cursor: not-allowed;
}

.qty-display {
  font-weight: 600;
  font-size: 1rem;
  color: #2D2D2D;
  text-align: center;
  user-select: none;
  min-width: 24px;
}
</style>
