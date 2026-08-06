<template>
  <div :class="['return-policy-notice', { 'return-policy-notice--compact': compact }]">
    <div class="notice-header">
      <span class="notice-icon">📦</span>
      <div class="notice-title-group">
        <h4 class="notice-title">Important Return & Replacement Policy</h4>
        <span class="notice-subtitle">Please read before unboxing your order</span>
      </div>
    </div>
    
    <div class="notice-content">
      <ul class="notice-list">
        <li>
          <span class="list-bullet">🔄</span>
          <span>Returns are accepted <strong>only for damaged products</strong>.</span>
        </li>
        <li>
          <span class="list-bullet">🎥</span>
          <span>An <strong>uninterrupted unboxing video is mandatory</strong> to process any return or replacement request.</span>
        </li>
        <li v-if="!compact">
          <span class="list-bullet">📦</span>
          <span>The video must clearly show the package being opened from its sealed condition and the damaged product.</span>
        </li>
        <li>
          <span class="list-bullet">❌</span>
          <span class="highlight-error">Returns or replacement requests without a valid unboxing video will not be accepted.</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
defineProps({
  compact: {
    type: Boolean,
    default: false
  }
});
</script>

<style scoped>
.return-policy-notice {
  background: #FFFDF9;
  border: 1px solid rgba(212, 175, 55, 0.35);
  border-left: 4px solid #6E1F3A;
  border-radius: 12px;
  padding: 16px 20px;
  font-family: 'Poppins', sans-serif;
  box-shadow: 0 2px 10px rgba(110, 31, 58, 0.04);
  transition: all 0.2s ease;
}

.return-policy-notice--compact {
  padding: 12px 14px;
  border-radius: 8px;
}

.notice-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}

.return-policy-notice--compact .notice-header {
  gap: 8px;
  margin-bottom: 8px;
}

.notice-icon {
  font-size: 1.4rem;
  line-height: 1;
  flex-shrink: 0;
}

.return-policy-notice--compact .notice-icon {
  font-size: 1.1rem;
}

.notice-title-group {
  display: flex;
  flex-direction: column;
}

.notice-title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #6E1F3A;
  font-family: 'Playfair Display', serif;
  letter-spacing: 0.01em;
}

.return-policy-notice--compact .notice-title {
  font-size: 0.85rem;
}

.notice-subtitle {
  font-size: 0.725rem;
  color: #64748b;
  font-weight: 500;
}

.notice-content {
  color: #334155;
  font-size: 0.875rem;
  line-height: 1.5;
}

.return-policy-notice--compact .notice-content {
  font-size: 0.8rem;
  line-height: 1.4;
}

.notice-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.return-policy-notice--compact .notice-list {
  gap: 6px;
}

.notice-list li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.return-policy-notice--compact .notice-list li {
  gap: 6px;
}

.list-bullet {
  flex-shrink: 0;
  font-size: 1rem;
  line-height: 1.3;
}

.return-policy-notice--compact .list-bullet {
  font-size: 0.85rem;
}

.highlight-error {
  color: #991b1b;
  font-weight: 600;
}

@media (max-width: 480px) {
  .return-policy-notice {
    padding: 12px 14px;
  }
  .notice-title {
    font-size: 0.875rem;
  }
  .notice-content {
    font-size: 0.8rem;
  }
}
</style>
