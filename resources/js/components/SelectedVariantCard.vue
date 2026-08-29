<template>
  <div class="selected-variant-summary" v-if="selectedColor || selectedSize">
    <div class="summary-bar">
      <span class="summary-heading">Selected:</span>
      <div class="summary-badges">
        <span class="badge-item" v-if="selectedColor">
          <span class="badge-label">Color:</span> {{ selectedColor }}
        </span>
        <span class="badge-item" v-if="selectedSize">
          <span class="badge-label">Size:</span> {{ selectedSize }}
        </span>
        <span class="badge-item stock-badge" :class="stockStatusClass">
          <span class="status-indicator-dot" :class="stockStatusClass"></span>
          {{ stockStatusLabel }}
        </span>
        <span class="badge-item sku-badge" v-if="selectedVariant && selectedVariant.sku">
          SKU: {{ selectedVariant.sku }}
        </span>
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
  const threshold = props.selectedVariant.low_stock_threshold || 5;
  if (props.selectedVariant.stock_quantity <= threshold) return 'low-stock';
  return 'in-stock';
});

const stockStatusLabel = computed(() => {
  if (!props.selectedVariant || props.selectedVariant.stock_quantity <= 0) return 'Out of Stock';
  const threshold = props.selectedVariant.low_stock_threshold || 5;
  if (props.selectedVariant.stock_quantity <= threshold) {
    return `Only ${props.selectedVariant.stock_quantity} Left in Stock`;
  }
  return 'In Stock';
});
</script>

<style scoped>
.selected-variant-summary {
  font-family: 'Poppins', sans-serif;
  margin-top: 2px;
}

.summary-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  background-color: #FAF5F0;
  border: 1px solid #E8DDD3;
  border-radius: 8px;
  padding: 6px 12px;
  font-size: 12px;
}

.summary-heading {
  font-weight: 700;
  color: #5B163A;
  font-size: 12px;
  letter-spacing: 0.02em;
}

.summary-badges {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.badge-item {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #2D2D2D;
  font-weight: 600;
  font-size: 12px;
  text-transform: capitalize;
}

.badge-label {
  color: #7A756F;
  font-weight: 500;
}

.status-indicator-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}

.status-indicator-dot.in-stock {
  background-color: #16A34A;
  box-shadow: 0 0 4px rgba(22, 163, 74, 0.4);
}

.status-indicator-dot.low-stock {
  background-color: #F97316;
  box-shadow: 0 0 4px rgba(249, 115, 22, 0.4);
}

.status-indicator-dot.out-of-stock {
  background-color: #dc2626;
  box-shadow: 0 0 4px rgba(220, 38, 38, 0.4);
}

.stock-badge.in-stock {
  color: #16A34A;
}

.stock-badge.low-stock {
  color: #F97316;
}

.stock-badge.out-of-stock {
  color: #dc2626;
}

.sku-badge {
  font-family: monospace;
  font-size: 11px;
  color: #5B163A;
}
</style>
