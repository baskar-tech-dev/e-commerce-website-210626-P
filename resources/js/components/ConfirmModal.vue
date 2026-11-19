<template>
  <Teleport to="body">
    <Transition name="confirm-dialog-fade">
      <div 
        v-if="isOpen" 
        class="confirm-modal-overlay"
        @click.self="handleCancel"
        role="dialog"
        aria-modal="true"
      >
        <div class="confirm-modal-card" :class="`confirm-modal-card--${type}`">
          <!-- Close button in top-right -->
          <button 
            type="button" 
            class="confirm-modal-close-btn" 
            @click="handleCancel"
            aria-label="Close dialog"
          >
            ✕
          </button>

          <!-- Luxury Icon Badge -->
          <div class="confirm-icon-badge" :class="`confirm-icon-badge--${type}`">
            <Trash2 v-if="type === 'danger'" :size="32" stroke-width="2.2" />
            <AlertTriangle v-else-if="type === 'warning'" :size="32" stroke-width="2.2" />
            <HelpCircle v-else :size="32" stroke-width="2.2" />
          </div>

          <!-- Dialog Content -->
          <div class="confirm-modal-content">
            <h3 class="confirm-modal-title">{{ computedTitle }}</h3>
            <p class="confirm-modal-message">{{ message }}</p>
            <p v-if="subMessage" class="confirm-modal-submessage">{{ subMessage }}</p>
          </div>

          <!-- Actions: Two balanced luxury buttons -->
          <div class="confirm-modal-actions">
            <button 
              type="button" 
              class="confirm-btn confirm-btn--cancel"
              @click="handleCancel"
              :disabled="loading"
            >
              {{ cancelText }}
            </button>
            <button 
              type="button" 
              class="confirm-btn"
              :class="`confirm-btn--${type}`"
              @click="handleConfirm"
              :disabled="loading"
              ref="confirmBtn"
            >
              <span v-if="loading" class="btn-spinner"></span>
              <span v-else>{{ confirmText }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, ref, watch, nextTick } from 'vue';
import { Trash2, AlertTriangle, HelpCircle } from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'danger', // 'danger' | 'warning' | 'primary'
    validator: (val) => ['danger', 'warning', 'primary'].includes(val)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  subMessage: {
    type: String,
    default: ''
  },
  confirmText: {
    type: String,
    default: 'Yes, Remove'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['confirm', 'cancel', 'update:isOpen']);

const confirmBtn = ref(null);

const computedTitle = computed(() => {
  if (props.title) return props.title;
  switch (props.type) {
    case 'danger': return 'Are You Sure?';
    case 'warning': return 'Please Confirm';
    default: return 'Confirm Action';
  }
});

const handleCancel = () => {
  if (props.loading) return;
  emit('update:isOpen', false);
  emit('cancel');
};

const handleConfirm = () => {
  emit('confirm');
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
.confirm-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.68);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  z-index: 10050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
}

.confirm-modal-card {
  position: relative;
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 440px;
  padding: 2.25rem 2rem 1.75rem 2rem;
  box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(212, 175, 55, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1.25rem;
}

.confirm-modal-close-btn {
  position: absolute;
  top: 16px;
  right: 16px;
  background: transparent;
  border: none;
  font-size: 1.1rem;
  color: #94a3b8;
  cursor: pointer;
  padding: 6px;
  line-height: 1;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.confirm-modal-close-btn:hover {
  color: #1e293b;
  background: #f1f5f9;
}

/* Luxury Icon Badge */
.confirm-icon-badge {
  width: 68px;
  height: 68px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.confirm-icon-badge--danger {
  background: #fff1f2;
  color: #e11d48;
  border: 2px solid #fecdd3;
  box-shadow: 0 8px 20px rgba(225, 29, 72, 0.15);
}

.confirm-icon-badge--warning {
  background: #fffbeb;
  color: #d97706;
  border: 2px solid #fde68a;
  box-shadow: 0 8px 20px rgba(217, 119, 6, 0.15);
}

.confirm-icon-badge--primary {
  background: #faf5f0;
  color: var(--color-primary, #4a0e2e);
  border: 2px solid rgba(212, 175, 55, 0.3);
  box-shadow: 0 8px 20px rgba(74, 14, 46, 0.15);
}

/* Dialog Content */
.confirm-modal-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.confirm-modal-title {
  margin: 0;
  font-family: var(--font-family-heading, 'Playfair Display', serif);
  font-size: 1.45rem;
  font-weight: 700;
  color: var(--color-primary, #4a0e2e);
  line-height: 1.3;
}

.confirm-modal-message {
  margin: 0;
  font-family: var(--font-family-base, 'Poppins', sans-serif);
  font-size: 0.95rem;
  color: #334155;
  line-height: 1.55;
}

.confirm-modal-submessage {
  margin: 0;
  font-family: var(--font-family-base, 'Poppins', sans-serif);
  font-size: 0.82rem;
  color: #94a3b8;
  line-height: 1.45;
}

/* Dialog Actions */
.confirm-modal-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  width: 100%;
  margin-top: 6px;
}

.confirm-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 48px;
  padding: 0 1.25rem;
  border-radius: 12px;
  font-family: var(--font-family-base, 'Poppins', sans-serif);
  font-size: 0.92rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  border: none;
  text-decoration: none;
}

.confirm-btn--cancel {
  background: #f8fafc;
  color: #475569;
  border: 1.5px solid #cbd5e1;
}

.confirm-btn--cancel:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #94a3b8;
  transform: translateY(-1px);
}

.confirm-btn--danger {
  background: #e11d48;
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(225, 29, 72, 0.25);
}

.confirm-btn--danger:hover {
  background: #be123c;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(225, 29, 72, 0.35);
}

.confirm-btn--warning {
  background: #d97706;
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(217, 119, 6, 0.25);
}

.confirm-btn--warning:hover {
  background: #b45309;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(217, 119, 6, 0.35);
}

.confirm-btn--primary {
  background: var(--color-primary, #4a0e2e);
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(74, 14, 46, 0.25);
}

.confirm-btn--primary:hover {
  background: #350820;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(74, 14, 46, 0.35);
}

.confirm-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

/* Spinner */
.btn-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Transition Animations */
.confirm-dialog-fade-enter-active,
.confirm-dialog-fade-leave-active {
  transition: opacity 0.25s ease;
}

.confirm-dialog-fade-enter-from,
.confirm-dialog-fade-leave-to {
  opacity: 0;
}

.confirm-dialog-fade-enter-active .confirm-modal-card {
  animation: confirmPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes confirmPop {
  0% {
    opacity: 0;
    transform: scale(0.92) translateY(8px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
</style>
