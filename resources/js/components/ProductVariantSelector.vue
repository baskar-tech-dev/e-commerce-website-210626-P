<template>
  <div class="product-variant-selector-wrapper">
    <!-- Color Selector Section -->
    <ProductColorSelector 
      v-if="availableColors.length > 0"
      :available-colors="availableColors"
      :modelValue="selectedColor"
      @update:modelValue="val => emit('update:selectedColor', val)"
    />

    <!-- Size Selector Section -->
    <ProductSizeSelector 
      v-if="availableSizes.length > 0"
      :available-sizes="availableSizes"
      :modelValue="selectedSize"
      @update:modelValue="val => emit('update:selectedSize', val)"
      :is-size-disabled="isSizeDisabled"
      @open-size-guide="emit('open-size-guide')"
    />

    <!-- Selection Summary Section -->
    <SelectedVariantCard 
      :selected-variant="selectedVariant"
      :selected-color="selectedColor"
      :selected-size="selectedSize"
    />

    <!-- Add to Cart Error Helper Text -->
    <div v-if="addToCartError" class="add-to-cart-helper-text" role="alert">
      <span>⚠️ {{ addToCartError }}</span>
    </div>

    <!-- Actions Section: Quantity Selector & Add to Cart Button -->
    <div class="actions-row">
      <QuantitySelector 
        :modelValue="qty"
        @update:modelValue="val => emit('update:qty', val)"
        :max="selectedVariant?.stock_quantity || 1"
        :disabled="!!addToCartError"
      />

      <button 
        class="btn boutique-add-to-cart-btn" 
        :disabled="!!addToCartError"
        @click="emit('add-to-cart')"
        aria-label="Add selected variant to shopping cart"
      >
        🛒 Add to Cart
      </button>
    </div>
  </div>
</template>

<script setup>
import ProductColorSelector from './ProductColorSelector.vue';
import ProductSizeSelector from './ProductSizeSelector.vue';
import SelectedVariantCard from './SelectedVariantCard.vue';
import QuantitySelector from './QuantitySelector.vue';

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  selectedColor: {
    type: String,
    required: true
  },
  selectedSize: {
    type: String,
    required: true
  },
  selectedVariant: {
    type: Object,
    default: null
  },
  qty: {
    type: Number,
    required: true
  },
  availableColors: {
    type: Array,
    required: true
  },
  availableSizes: {
    type: Array,
    required: true
  },
  isSizeDisabled: {
    type: Function,
    required: true
  },
  addToCartError: {
    type: String,
    default: ''
  }
});

const emit = defineEmits([
  'update:selectedColor',
  'update:selectedSize',
  'update:qty',
  'add-to-cart',
  'open-size-guide'
]);
</script>

<style scoped>
.product-variant-selector-wrapper {
  display: flex;
  flex-direction: column;
  gap: 24px; /* Generous 24px spacing between sections conforming to 8px spacing grid */
  background-color: #FFFDF9; /* Warm White */
  border: 1px solid #E8DDD3; /* Border color */
  border-radius: 16px;
  padding: 24px;
  font-family: 'Poppins', sans-serif;
}

@media (max-width: 480px) {
  .product-variant-selector-wrapper {
    padding: 16px;
    gap: 20px;
    border-radius: 12px;
  }
}

.actions-row {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-top: 8px;
}

@media (max-width: 480px) {
  .actions-row {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
  }
  
  :deep(.quantity-selector-container) {
    width: 100% !important;
  }
}

.boutique-add-to-cart-btn {
  flex: 1;
  height: 48px; /* Touch friendly height target */
  border-radius: 12px;
  background-color: #5B163A; /* Maroon primary */
  color: #FFFDF9;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(91, 22, 58, 0.12);
  transition: all 200ms ease;
}

.boutique-add-to-cart-btn:hover:not(:disabled) {
  background-color: #4a0e2e;
  transform: translateY(-2px); /* Slight lift */
  box-shadow: 0 8px 20px rgba(91, 22, 58, 0.2);
}

.boutique-add-to-cart-btn:active:not(:disabled) {
  transform: translateY(0) scale(0.98);
}

.boutique-add-to-cart-btn:disabled {
  background-color: #e2e8f0;
  color: #94a3b8;
  cursor: not-allowed;
  box-shadow: none;
}

.add-to-cart-helper-text {
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: #dc2626; /* Warning soft red */
  display: flex;
  align-items: center;
  gap: 6px;
  animation: fadeIn 200ms ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
