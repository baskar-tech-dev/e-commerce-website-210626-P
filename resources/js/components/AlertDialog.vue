<template>
  <Teleport to="body">
    <Transition name="dialog-fade">
      <div 
        v-if="isOpen" 
        class="dialog-overlay"
        @click.self="handleClose"
        role="dialog"
        aria-modal="true"
      >
        <div class="dialog-card" :class="`dialog-card--${type}`">
          <!-- Icon Container -->
          <div class="dialog-icon-badge" :class="`dialog-icon-badge--${type}`">
            <AlertTriangle v-if="type === 'error'" :size="32" />
            <AlertCircle v-else-if="type === 'warning'" :size="32" />
            <CheckCircle v-else-if="type === 'success'" :size="32" />
            <Info v-else :size="32" />
          </div>

          <!-- Dialog Header & Content -->
          <div class="dialog-content">
            <h3 class="dialog-title">{{ computedTitle }}</h3>
            <p class="dialog-message">{{ message }}</p>
          </div>

          <!-- Actions -->
          <div class="dialog-actions">
            <button 
              type="button" 
              class="btn-dialog-action"
              :class="`btn-dialog-action--${type}`"
              @click="handleClose"
              ref="confirmBtn"
            >
              {{ buttonText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, ref, watch, nextTick } from 'vue';
import { AlertTriangle, AlertCircle, CheckCircle, Info } from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'error', // 'error' | 'success' | 'warning' | 'info'
    validator: (val) => ['error', 'success', 'warning', 'info'].includes(val)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  buttonText: {
    type: String,
    default: 'OK'
  }
});

const emit = defineEmits(['close', 'update:isOpen']);

const confirmBtn = ref(null);

const computedTitle = computed(() => {
  if (props.title) return props.title;
  switch (props.type) {
    case 'error': return 'Validation Error';
    case 'warning': return 'Warning';
    case 'success': return 'Success';
    default: return 'Notification';
  }
});

const handleClose = () => {
  emit('update:isOpen', false);
  emit('close');
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    nextTick(() => {
      confirmBtn.value?.focus();
    });
  }
});
</script>

<style scoped>
.dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
  z-index: 10050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.dialog-card {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 440px;
  padding: 28px 24px 24px 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 18px;
  transform: scale(1);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.dialog-icon-badge {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.dialog-icon-badge--error {
  background: #fef2f2;
  color: #ef4444;
  border: 2px solid #fecaca;
}

.dialog-icon-badge--warning {
  background: #fffbeb;
  color: #f59e0b;
  border: 2px solid #fde68a;
}

.dialog-icon-badge--success {
  background: #f0fdf4;
  color: #16a34a;
  border: 2px solid #bbf7d0;
}

.dialog-icon-badge--info {
  background: #eff6ff;
  color: #3b82f6;
  border: 2px solid #bfdbfe;
}

.dialog-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.dialog-title {
  margin: 0;
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem;
  font-weight: 700;
  color: #1e293b;
}

.dialog-message {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  color: #64748b;
  line-height: 1.5;
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.dialog-actions {
  width: 100%;
  margin-top: 6px;
}

.btn-dialog-action {
  width: 100%;
  padding: 12px;
  border-radius: 12px;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-dialog-action--error {
  background: #ef4444;
  color: #ffffff;
}

.btn-dialog-action--error:hover {
  background: #dc2626;
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
}

.btn-dialog-action--warning {
  background: #f59e0b;
  color: #ffffff;
}

.btn-dialog-action--warning:hover {
  background: #d97706;
}

.btn-dialog-action--success {
  background: #16a34a;
  color: #ffffff;
}

.btn-dialog-action--success:hover {
  background: #15803d;
}

.btn-dialog-action--info {
  background: #3b82f6;
  color: #ffffff;
}

.btn-dialog-action--info:hover {
  background: #2563eb;
}

/* Animations */
.dialog-fade-enter-active,
.dialog-fade-leave-active {
  transition: opacity 0.25s ease;
}

.dialog-fade-enter-from,
.dialog-fade-leave-to {
  opacity: 0;
}

.dialog-fade-enter-active .dialog-card {
  animation: dialogPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes dialogPop {
  0% {
    opacity: 0;
    transform: scale(0.9) translateY(10px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
</style>
