<template>
  <div class="selected-variant-summary" v-if="selectedColor || selectedSize">
    <h4 class="summary-title">Selected</h4>
    <div class="summary-grid">
      <div class="summary-item" v-if="selectedColor">
        <span class="summary-label">Color</span>
        <span class="summary-value">{{ selectedColor }}</span>
      </div>
      <div class="summary-item" v-if="selectedSize">
        <span class="summary-label">Size</span>
        <span class="summary-value">{{ selectedSize }}</span>
      </div>
      <div class="summary-item">
        <span class="summary-label">Stock</span>
        <div class="stock-status">
          <span class="status-indicator-dot" :class="stockStatusClass"></span>
          <span class="stock-text" :class="stockStatusClass">{{ stockStatusLabel }}</span>
        </div>
      </div>
      <div class="summary-item" v-if="selectedVariant && selectedVariant.sku">
        <span class="summary-label">SKU</span>
        <span class="summary-value sku-text">{{ selectedVariant.sku }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  selectedVariant: {
    type: Object,
    default: null
  },
  selectedColor: {
    type: String,
    required: true
  },
  selectedSize: {
    type: String,
    required: true
  }
});

const stockStatusClass = computed(() => {
  if (!props.selectedVariant || props.selectedVariant.stock_quantity <= 0) return 'out-of-stock';
  if (props.selectedVariant.stock_quantity <= 5) return 'low-stock';
  return 'in-stock';
});

const stockStatusLabel = computed(() => {
  if (!props.selectedVariant || props.selectedVariant.stock_quantity <= 0) return 'Out of Stock';
  if (props.selectedVariant.stock_quantity <= 5) {
    return `Only ${props.selectedVariant.stock_quantity} Left`;
  }
  return '✔ In Stock';
});
</script>

<style scoped>
.selected-variant-summary {
  display: flex;
  flex-direction: column;
  gap: 12px;
  font-family: 'Poppins', sans-serif;
  margin-top: 8px;
}

.summary-title {
  font-size: 16px;
  font-weight: 600;
  color: #2D2D2D;
  margin: 0;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-top: 8px;
}

@media (max-width: 480px) {
  .summary-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.summary-label {
  font-size: 13px;
  font-weight: 500;
  color: #7A756F; /* Muted label color */
}

.summary-value {
  font-size: 14px;
  font-weight: 600;
  color: #2D2D2D; /* Dark Charcoal text */
  text-transform: capitalize;
}

.stock-status {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.status-indicator-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.status-indicator-dot.in-stock {
  background-color: #16A34A; /* Success green */
  box-shadow: 0 0 6px rgba(22, 163, 74, 0.4);
}

.status-indicator-dot.low-stock {
  background-color: #F97316; /* Amber warning */
  box-shadow: 0 0 6px rgba(249, 115, 22, 0.4);
}

.status-indicator-dot.out-of-stock {
  background-color: #dc2626; /* Soft Red/Error */
  box-shadow: 0 0 6px rgba(220, 38, 38, 0.4);
}

.stock-text {
  font-size: 14px;
  font-weight: 500;
  color: #2D2D2D;
}

.stock-text.in-stock {
  color: #16A34A;
}

.stock-text.low-stock {
  color: #F97316;
}

.stock-text.out-of-stock {
  color: #dc2626;
}

.sku-text {
  font-family: monospace;
  font-size: 13px;
  color: #5B163A;
  font-weight: 600;
}
</style>
