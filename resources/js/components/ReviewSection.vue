<template>
  <section class="review-section" id="customer-reviews">
    <div class="review-container">
      <!-- Section Title -->
      <div class="review-header">
        <h2 class="review-title">{{ summary.total_reviews > 0 ? 'Customer Reviews' : 'Product Reviews' }}</h2>
        <p class="review-subtitle">
          {{ summary.total_reviews > 0 ? 'Real experiences from Maya Sree patrons' : 'Be the first to share your experience with this product' }}
        </p>
      </div>

      <!-- Rating Summary Card (Displayed when reviews exist) -->
      <div v-if="summary.total_reviews > 0" class="review-summary-card glass-panel">
        <div class="summary-left">
          <div class="avg-rating-number">
            {{ summary.avg_rating > 0 ? summary.avg_rating.toFixed(1) : '0.0' }}
            <span class="avg-star">★</span>
          </div>
          <div class="avg-rating-stars">
            <span 
              v-for="s in 5" 
              :key="s" 
              class="star-icon"
              :class="{ filled: s <= Math.round(summary.avg_rating) }"
            >★</span>
          </div>
          <div class="total-review-count">
            Based on {{ summary.total_reviews }} {{ summary.total_reviews === 1 ? 'review' : 'reviews' }}
          </div>
        </div>

        <!-- Rating Distribution Bars -->
        <div class="summary-right">
          <div 
            v-for="star in [5, 4, 3, 2, 1]" 
            :key="star" 
            class="distribution-row"
          >
            <span class="star-label">{{ star }} ★</span>
            <div class="progress-bar-bg">
              <div 
                class="progress-bar-fill"
                :style="{ width: (summary.rating_distribution[star]?.percentage || 0) + '%' }"
              ></div>
            </div>
            <span class="pct-label">{{ summary.rating_distribution[star]?.percentage || 0 }}%</span>
          </div>
        </div>

        <!-- Integrated Action Button inside Summary Card -->
        <div class="summary-action-box">
          <button v-if="!isLoggedIn" class="btn btn--primary login-btn" @click="redirectToLogin">
            🔒 Sign in to Review
          </button>
          <button v-else-if="eligibility.has_reviewed" class="btn btn--outline-gold" @click="toggleEditForm">
            {{ showForm ? 'Cancel Editing' : '✓ Edit Your Review' }}
          </button>
          <button v-else-if="!showForm" class="btn btn--primary write-review-btn" @click="showForm = true">
            ★ Write a Review
          </button>
        </div>
      </div>

      <!-- Empty State Hero (Displayed when 0 reviews exist & form is hidden) -->
      <div v-else-if="!showForm" class="empty-reviews-hero glass-panel">
        <div class="empty-hero-icon">✨</div>
        <h3 class="empty-hero-title">No reviews yet</h3>
        <p class="empty-hero-subtitle">Be the first customer to share your experience with this saree.</p>
        <div class="empty-hero-actions">
          <button v-if="!isLoggedIn" class="btn btn--primary login-btn" @click="redirectToLogin">
            🔒 Sign in to Write a Review
          </button>
          <button v-else-if="eligibility.has_reviewed" class="btn btn--outline-gold" @click="toggleEditForm">
            ✓ Edit Your Review
          </button>
          <button v-else class="btn btn--primary write-review-btn" @click="showForm = true">
            ★ Write a Review
          </button>
        </div>
      </div>

      <!-- Review Form (Write or Edit) -->
      <div v-if="showForm" class="review-form-card glass-panel">
        <h3 class="form-title">
          {{ isEditing ? 'Edit Your Review' : 'Share Your Experience' }}
        </h3>
        
        <!-- Submission Status Alert -->
        <div v-if="formAlert.message" :class="['alert', formAlert.type === 'error' ? 'alert--error' : 'alert--success']">
          {{ formAlert.message }}
        </div>

        <form @submit.prevent="submitReview">
          <!-- Step 1: Star Rating Selector -->
          <div class="form-group">
            <label class="form-label">Overall Rating <span class="required">*</span></label>
            <div class="star-rating-selector">
              <button 
                v-for="star in 5" 
                :key="star"
                type="button"
                class="star-select-btn"
                :class="{ active: star <= (hoverRating || form.rating) }"
                @mouseenter="hoverRating = star"
                @mouseleave="hoverRating = 0"
                @click="form.rating = star"
              >
                ★
              </button>
              <span class="rating-text-label" v-if="form.rating > 0">
                {{ ratingLabels[form.rating] }}
              </span>
            </div>
            <span v-if="formErrors.rating" class="field-error">{{ formErrors.rating }}</span>
          </div>

          <!-- Step 2: Review Text -->
          <div class="form-group">
            <label class="form-label" for="review-text">Your Review <span class="required">*</span></label>
            <textarea 
              id="review-text" 
              v-model="form.review"
              class="form-textarea" 
              rows="4" 
              placeholder="What did you love about the fabric, fit, color, and finish? Help other fashion lovers make the right choice."
              maxlength="2000"
            ></textarea>
            <div class="char-counter">
              {{ form.review.length }} / 2000 characters
            </div>
            <span v-if="formErrors.review" class="field-error">{{ formErrors.review }}</span>
          </div>

          <!-- Step 3: Photo Upload Zone -->
          <div class="form-group">
            <label class="form-label">Customer Photos <span class="sub-label">(Up to 4 images, automatically compressed ≤ 200 KB)</span></label>
            
            <!-- Existing Images preview (when editing) -->
            <div v-if="existingImages.length > 0" class="existing-images-preview">
              <p class="section-subheading">Current Photos:</p>
              <div class="photo-grid">
                <div v-for="img in existingImages" :key="img.id" class="photo-preview-card">
                  <img :src="img.image_path" alt="Existing review photo" />
                  <button 
                    type="button" 
                    class="remove-photo-btn" 
                    title="Remove Photo"
                    @click="removeExistingImage(img.id)"
                  >
                    ✕
                  </button>
                </div>
              </div>
            </div>

            <!-- Upload Zone -->
            <div 
              v-if="totalImagesCount < 4" 
              class="upload-dropzone" 
              @click="triggerFileInput"
              @dragover.prevent="dragOver = true"
              @dragleave.prevent="dragOver = false"
              @drop.prevent="handleFileDrop"
              :class="{ 'drag-over': dragOver }"
            >
              <input 
                ref="fileInputRef" 
                type="file" 
                accept="image/jpeg,image/jpg,image/png,image/webp" 
                multiple 
                class="hidden-file-input"
                @change="handleFileSelect"
              />
              <div class="dropzone-content">
                <div class="upload-icon">📷</div>
                <div class="upload-title">Click or drag photos here to upload</div>
                <div class="upload-subtitle">JPG, PNG, WebP supported. Max {{ 4 - totalImagesCount }} more photo(s).</div>
              </div>
            </div>

            <!-- New Image Previews with live compression indicators -->
            <div v-if="newImageFiles.length > 0" class="photo-grid new-photos-grid">
              <div v-for="(img, idx) in newImageFiles" :key="idx" class="photo-preview-card">
                <img :src="img.previewUrl" alt="New photo preview" @error="handlePreviewError($event, img)" />
                <div class="compression-badge" title="Optimized for fast loading">
                  {{ img.originalSizeFormatted }} → {{ img.compressedSizeFormatted }} ✓
                </div>
                <button 
                  type="button" 
                  class="remove-photo-btn" 
                  title="Remove Photo"
                  @click="removeNewImage(idx)"
                >
                  ✕
                </button>
              </div>
            </div>
            
            <span v-if="formErrors.images" class="field-error">{{ formErrors.images }}</span>
          </div>

          <!-- Actions -->
          <div class="form-actions">
            <button 
              type="button" 
              class="btn btn--outline" 
              @click="showForm = false"
              :disabled="submitting"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn--primary submit-btn" 
              :disabled="submitting || form.rating === 0 || !form.review.trim()"
            >
              <span v-if="submitting" class="button-spinner"></span>
              <span>{{ submitting ? (isEditing ? 'Updating...' : 'Submitting...') : (isEditing ? 'Update Review' : 'Submit Review') }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Review List -->
      <div class="reviews-list-container">
        <!-- Loading State -->
        <div v-if="loadingReviews" class="reviews-loading">
          <div class="mini-spinner"></div>
          <p>Loading customer reviews...</p>
        </div>

        <!-- Review Cards -->
        <div v-else-if="reviews.length > 0" class="review-cards">
          <div 
            v-for="rev in reviews" 
            :key="rev.id" 
            class="review-card glass-panel"
          >
            <!-- Card Header -->
            <div class="card-header">
              <div class="user-info">
                <div class="user-avatar">
                  {{ rev.user_display_name ? rev.user_display_name.charAt(0).toUpperCase() : 'C' }}
                </div>
                <div>
                  <div class="user-name-row">
                    <span class="user-name">{{ rev.user_display_name }}</span>
                    <span v-if="rev.is_verified_purchase" class="verified-badge">
                      <span class="check-icon">✓</span> Verified Purchase
                    </span>
                  </div>
                  <div class="review-date">{{ formatDate(rev.created_at) }}</div>
                </div>
              </div>

              <!-- Rating Stars -->
              <div class="card-rating-stars">
                <span 
                  v-for="s in 5" 
                  :key="s" 
                  class="star-icon"
                  :class="{ filled: s <= rev.rating }"
                >★</span>
              </div>
            </div>

            <!-- Review Body -->
            <p class="review-text">{{ rev.review }}</p>

            <!-- Review Photos Gallery -->
            <div v-if="rev.images && rev.images.length > 0" class="review-images-gallery">
              <div 
                v-for="(img, idx) in rev.images" 
                :key="img.id" 
                class="review-thumb-box"
                @click="openLightbox(rev.images, idx)"
              >
                <img :src="img.image_path" alt="Customer review photo" loading="lazy" />
              </div>
            </div>

            <!-- Card Footer & Helpful Action -->
            <div class="card-footer">
              <button 
                class="helpful-btn"
                :class="{ active: rev.is_voted_helpful }"
                @click="handleHelpfulVote(rev)"
              >
                <span class="heart-icon">{{ rev.is_voted_helpful ? '♥' : '♡' }}</span>
                <span>Helpful</span>
                <span v-if="rev.helpful_count > 0" class="helpful-count">({{ rev.helpful_count }})</span>
              </button>

              <!-- Edit/Delete links for own review -->
              <div v-if="isLoggedIn && currentUserId === rev.user_id" class="own-review-actions">
                <button class="action-link" @click="startEdit(rev)">Edit</button>
                <span class="divider">•</span>
                <button class="action-link text-danger" @click="deleteReview(rev)">Delete</button>
              </div>
            </div>
          </div>

          <!-- Pagination / Load More Button -->
          <div v-if="hasMoreReviews" class="load-more-container">
            <button 
              class="btn btn--outline load-more-btn"
              :disabled="loadingMore"
              @click="loadMoreReviews"
            >
              <span v-if="loadingMore" class="mini-spinner"></span>
              <span v-else>Load More Reviews</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Lightbox Modal for Fullsize Images -->
    <div v-if="lightbox.show" class="lightbox-overlay" @click.self="closeLightbox">
      <div class="lightbox-content">
        <button class="lightbox-close-btn" @click="closeLightbox">✕</button>
        
        <button 
          v-if="lightbox.images.length > 1" 
          class="lightbox-nav-btn prev-btn" 
          @click.stop="prevLightboxImage"
        >
          ‹
        </button>

        <div class="lightbox-image-container">
          <img :src="lightbox.images[lightbox.currentIndex]?.image_path" alt="Full view review photo" />
        </div>

        <button 
          v-if="lightbox.images.length > 1" 
          class="lightbox-nav-btn next-btn" 
          @click.stop="nextLightboxImage"
        >
          ›
        </button>

        <div class="lightbox-counter">
          {{ lightbox.currentIndex + 1 }} / {{ lightbox.images.length }}
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { compressImage } from '../utils/imageCompressor.js';
import { useAuthStore } from '../stores/auth.js';

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const router = useRouter();
const authStore = useAuthStore();

// State
const isLoggedIn = computed(() => authStore.isAuthenticated);
const currentUserId = computed(() => authStore.user?.id);
const eligibility = ref({
  can_review: false,
  reason: null,
  has_reviewed: false,
  is_verified_purchase: false,
  message: null,
});

const summary = reactive({
  avg_rating: 0,
  total_reviews: 0,
  rating_distribution: {
    5: { count: 0, percentage: 0 },
    4: { count: 0, percentage: 0 },
    3: { count: 0, percentage: 0 },
    2: { count: 0, percentage: 0 },
    1: { count: 0, percentage: 0 },
  },
});

const reviews = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const loadingReviews = ref(true);
const loadingMore = ref(false);

const showForm = ref(false);
const isEditing = ref(false);
const editingReviewId = ref(null);
const submitting = ref(false);

const hoverRating = ref(0);
const form = reactive({
  rating: 0,
  review: '',
});

const existingImages = ref([]);
const removedImageIds = ref([]);
const newImageFiles = ref([]);

const fileInputRef = ref(null);
const dragOver = ref(false);

const formErrors = reactive({
  rating: '',
  review: '',
  images: '',
});

const formAlert = reactive({
  type: '',
  message: '',
});

const ratingLabels = {
  1: '1 - Disappointing',
  2: '2 - Below Expectations',
  3: '3 - Average',
  4: '4 - Very Good',
  5: '5 - Exceptional / Loved It!',
};

// Lightbox state
const lightbox = reactive({
  show: false,
  images: [],
  currentIndex: 0,
});

const hasMoreReviews = computed(() => currentPage.value < lastPage.value);

const totalImagesCount = computed(() => {
  return existingImages.value.length + newImageFiles.value.length;
});

// Check Auth & Fetch Data
const checkAuthStatus = () => {
  const token = localStorage.getItem('auth_token');
  isLoggedIn.value = !!token;
  
  if (token) {
    try {
      const userRaw = localStorage.getItem('user_data');
      if (userRaw) {
        const u = JSON.parse(userRaw);
        currentUserId.value = u.id;
      }
    } catch (e) {
      console.error('Failed to parse user storage:', e);
    }
  }
};

const fetchEligibility = async () => {
  if (!props.product?.id) return;
  try {
    const res = await axios.get(`/api/products/${props.product.id}/review-eligibility`);
    if (res.data && res.data.success) {
      eligibility.value = res.data.data;
      if (res.data.data.review) {
        // Pre-fill form for editing
        editingReviewId.value = res.data.data.review.id;
      }
    }
  } catch (err) {
    console.error('Failed to fetch eligibility:', err);
  }
};

const fetchReviews = async (page = 1) => {
  if (!props.product?.id) return;
  if (page === 1) {
    loadingReviews.value = true;
  } else {
    loadingMore.value = true;
  }

  try {
    const res = await axios.get(`/api/products/${props.product.id}/reviews?page=${page}`);
    if (res.data && res.data.success) {
      if (page === 1) {
        reviews.value = res.data.data;
      } else {
        reviews.value = [...reviews.value, ...res.data.data];
      }

      if (res.data.summary) {
        summary.avg_rating = res.data.summary.avg_rating || 0;
        summary.total_reviews = res.data.summary.total_reviews || 0;
        summary.rating_distribution = res.data.summary.rating_distribution || summary.rating_distribution;
      }

      currentPage.value = res.data.meta.current_page;
      lastPage.value = res.data.meta.last_page;
    }
  } catch (err) {
    console.error('Failed to fetch reviews:', err);
  } finally {
    loadingReviews.value = false;
    loadingMore.value = false;
  }
};

const loadMoreReviews = () => {
  if (hasMoreReviews.value) {
    fetchReviews(currentPage.value + 1);
  }
};

const redirectToLogin = () => {
  authStore.openAuthModal('login', 'write_review');
};

watch(() => authStore.isAuthenticated, (newVal) => {
  if (newVal) {
    fetchEligibility();
  }
});

const toggleEditForm = () => {
  if (!showForm.value && eligibility.value.review) {
    startEdit(eligibility.value.review);
  } else {
    showForm.value = !showForm.value;
  }
};

const triggerFileInput = () => {
  fileInputRef.value?.click();
};

const handleFileSelect = async (e) => {
  const files = Array.from(e.target.files || []);
  await processUploadedFiles(files);
  e.target.value = '';
};

const handleFileDrop = async (e) => {
  dragOver.value = false;
  const files = Array.from(e.dataTransfer.files || []);
  await processUploadedFiles(files);
};

const processUploadedFiles = async (files) => {
  formErrors.images = '';
  const remainingSlot = 4 - totalImagesCount.value;

  if (remainingSlot <= 0) {
    formErrors.images = 'You can upload a maximum of 4 photos.';
    return;
  }

  const selectedFiles = files.slice(0, remainingSlot);

  for (const file of selectedFiles) {
    try {
      // Client-side image compression (≤ 200 KB)
      const compressedObj = await compressImage(file, 204800);
      newImageFiles.value.push(compressedObj);
    } catch (err) {
      console.error('Compression failed:', err);
      formErrors.images = err.message || 'Failed to process image.';
    }
  }
};

const handlePreviewError = (e, img) => {
  if (img && img.originalFile) {
    try {
      e.target.src = URL.createObjectURL(img.originalFile);
    } catch (err) {
      console.error('Failed to set fallback preview URL:', err);
    }
  }
};

const removeNewImage = (index) => {
  const removed = newImageFiles.value.splice(index, 1)[0];
  if (removed && removed.previewUrl && typeof removed.previewUrl === 'string' && removed.previewUrl.startsWith('blob:')) {
    try { URL.revokeObjectURL(removed.previewUrl); } catch (e) {}
  }
};

const removeExistingImage = (id) => {
  existingImages.value = existingImages.value.filter(i => i.id !== id);
  removedImageIds.value.push(id);
};

// Form submission
const submitReview = async () => {
  formErrors.rating = '';
  formErrors.review = '';
  formErrors.images = '';
  formAlert.message = '';

  if (form.rating < 1 || form.rating > 5) {
    formErrors.rating = 'Please select a rating star.';
    return;
  }

  if (!form.review.trim() || form.review.trim().length < 5) {
    formErrors.review = 'Please write at least 5 characters.';
    return;
  }

  submitting.value = true;

  try {
    const formData = new FormData();
    formData.append('rating', form.rating);
    formData.append('review', form.review);

    if (isEditing.value) {
      // Update payload
      removedImageIds.value.forEach(id => {
        formData.append('removed_image_ids[]', id);
      });

      newImageFiles.value.forEach(img => {
        formData.append('new_images[]', img.file);
      });

      formData.append('_method', 'PUT');

      const res = await axios.post(`/api/reviews/${editingReviewId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      if (res.data && res.data.success) {
        formAlert.type = 'success';
        formAlert.message = res.data.message;
        setTimeout(() => {
          showForm.value = false;
          resetForm();
          fetchEligibility();
          fetchReviews(1);
        }, 1500);
      }
    } else {
      // Create payload
      newImageFiles.value.forEach(img => {
        formData.append('images[]', img.file);
      });

      const res = await axios.post(`/api/products/${props.product.id}/reviews`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      if (res.data && res.data.success) {
        formAlert.type = 'success';
        formAlert.message = res.data.message;
        setTimeout(() => {
          showForm.value = false;
          resetForm();
          fetchEligibility();
          fetchReviews(1);
        }, 1500);
      }
    }
  } catch (err) {
    console.error('Failed to submit review:', err);
    const msg = err.response?.data?.message || 'Something went wrong while submitting your review.';
    formAlert.type = 'error';
    formAlert.message = msg;
  } finally {
    submitting.value = false;
  }
};

const startEdit = (rev) => {
  isEditing.value = true;
  editingReviewId.value = rev.id;
  form.rating = rev.rating;
  form.review = rev.review;
  existingImages.value = [...(rev.images || [])];
  removedImageIds.value = [];
  newImageFiles.value = [];
  showForm.value = true;
};

const resetForm = () => {
  newImageFiles.value.forEach(img => {
    if (img && img.previewUrl && typeof img.previewUrl === 'string' && img.previewUrl.startsWith('blob:')) {
      try { URL.revokeObjectURL(img.previewUrl); } catch (e) {}
    }
  });
  form.rating = 0;
  form.review = '';
  isEditing.value = false;
  editingReviewId.value = null;
  existingImages.value = [];
  removedImageIds.value = [];
  newImageFiles.value = [];
  formAlert.message = '';
};

onUnmounted(() => {
  newImageFiles.value.forEach(img => {
    if (img && img.previewUrl && typeof img.previewUrl === 'string' && img.previewUrl.startsWith('blob:')) {
      try { URL.revokeObjectURL(img.previewUrl); } catch (e) {}
    }
  });
});

const deleteReview = async (rev) => {
  if (!confirm('Are you sure you want to delete your review?')) return;
  try {
    const res = await axios.delete(`/api/reviews/${rev.id}`);
    if (res.data && res.data.success) {
      alert('Review deleted successfully.');
      fetchEligibility();
      fetchReviews(1);
    }
  } catch (err) {
    console.error('Failed to delete review:', err);
    alert(err.response?.data?.message || 'Could not delete review.');
  }
};

const handleHelpfulVote = async (rev) => {
  if (!isLoggedIn.value) {
    redirectToLogin();
    return;
  }
  try {
    const res = await axios.post(`/api/reviews/${rev.id}/helpful`);
    if (res.data && res.data.success) {
      rev.helpful_count = res.data.helpful_count;
      rev.is_voted_helpful = res.data.is_voted;
    }
  } catch (err) {
    console.error('Failed helpful vote:', err);
  }
};

// Lightbox logic
const openLightbox = (imagesList, index) => {
  lightbox.images = imagesList;
  lightbox.currentIndex = index;
  lightbox.show = true;
};

const closeLightbox = () => {
  lightbox.show = false;
};

const prevLightboxImage = () => {
  if (lightbox.images.length <= 1) return;
  lightbox.currentIndex = (lightbox.currentIndex - 1 + lightbox.images.length) % lightbox.images.length;
};

const nextLightboxImage = () => {
  if (lightbox.images.length <= 1) return;
  lightbox.currentIndex = (lightbox.currentIndex + 1) % lightbox.images.length;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

watch(() => props.product?.id, (newId) => {
  if (newId) {
    checkAuthStatus();
    fetchEligibility();
    fetchReviews(1);
  }
}, { immediate: true });

onMounted(() => {
  checkAuthStatus();
});
</script>

<style scoped>
.review-section {
  margin-top: 3.5rem;
  padding-top: 2.5rem;
  border-top: 1px solid var(--color-border, #E8DED2);
  font-family: 'Poppins', sans-serif;
}

.review-container {
  max-width: 1000px;
  margin: 0 auto;
}

.review-header {
  text-align: center;
  margin-bottom: 2rem;
}

.review-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  font-weight: 700;
  color: #6E1F3A;
  margin: 0 0 0.5rem 0;
}

.review-subtitle {
  color: var(--color-text-muted, #7A726A);
  font-size: 0.95rem;
  margin: 0;
}

/* Empty State Hero */
.empty-reviews-hero {
  background: #ffffff;
  border: 1px solid #E8DED2;
  border-radius: 16px;
  padding: 3rem 2rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(91, 22, 58, 0.04);
  margin-bottom: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.empty-hero-icon {
  font-size: 2.5rem;
  margin-bottom: 0.75rem;
}

.empty-hero-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #5B163A;
  margin: 0 0 0.5rem 0;
}

.empty-hero-subtitle {
  color: #7A726A;
  font-size: 0.95rem;
  margin: 0 0 1.5rem 0;
  max-width: 480px;
}

.empty-hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.summary-action-box {
  grid-column: 1 / -1;
  display: flex;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #F3ECE2;
  margin-top: 0.5rem;
}

/* Summary Card */
.review-summary-card {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 2rem;
  background: #ffffff;
  border: 1px solid var(--color-border, #E8DED2);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(110, 31, 58, 0.04);
  margin-bottom: 2rem;
  align-items: center;
}

.summary-left {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-right: 1px solid var(--color-border, #E8DED2);
  padding-right: 1.5rem;
}

.avg-rating-number {
  font-family: 'Playfair Display', serif;
  font-size: 3.5rem;
  font-weight: 800;
  color: #6E1F3A;
  line-height: 1;
  display: flex;
  align-items: center;
  gap: 4px;
}

.avg-star {
  color: #B68D40;
  font-size: 2.5rem;
}

.avg-rating-stars {
  display: flex;
  gap: 3px;
  margin: 8px 0 6px 0;
}

.star-icon {
  font-size: 1.25rem;
  color: #E2D9CE;
}

.star-icon.filled {
  color: #B68D40;
}

.total-review-count {
  font-size: 0.85rem;
  color: #7A726A;
  font-weight: 500;
}

/* Rating Distribution */
.summary-right {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.distribution-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.star-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #4A423A;
  width: 40px;
}

.progress-bar-bg {
  flex-grow: 1;
  height: 8px;
  background: #F3ECE2;
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #6E1F3A 0%, #B68D40 100%);
  border-radius: 4px;
  transition: width 0.4s ease;
}

.pct-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #7A726A;
  width: 42px;
  text-align: right;
}

/* Eligibility Notices */
.eligibility-container {
  margin-bottom: 2rem;
}

.eligibility-card {
  padding: 1.25rem 1.5rem;
  border-radius: 12px;
  background: #FAF8F5;
  border: 1px solid #E8DED2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.notice-text {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.95rem;
  color: #4A423A;
  font-weight: 500;
}

.notice-text .icon {
  font-size: 1.2rem;
}

.btn--primary {
  background: #6E1F3A;
  color: #ffffff;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn--primary:hover {
  background: #52162B;
  transform: translateY(-1px);
}

.btn--outline-gold {
  background: transparent;
  color: #B68D40;
  border: 1.5px solid #B68D40;
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
}

.btn--outline-gold:hover {
  background: #B68D40;
  color: #ffffff;
}

.write-review-btn {
  font-size: 1rem;
  padding: 0.85rem 2rem;
}

/* Review Form */
.review-form-card {
  background: #ffffff;
  border: 1px solid #E8DED2;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2.5rem;
  box-shadow: 0 8px 30px rgba(110, 31, 58, 0.06);
}

.form-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: #6E1F3A;
  margin: 0 0 1.5rem 0;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  font-weight: 600;
  font-size: 0.9rem;
  color: #2F2A26;
  margin-bottom: 0.5rem;
}

.required {
  color: #d9534f;
}

.sub-label {
  font-weight: 400;
  color: #7A726A;
  font-size: 0.8rem;
  margin-left: 4px;
}

.star-rating-selector {
  display: flex;
  align-items: center;
  gap: 6px;
}

.star-select-btn {
  background: none;
  border: none;
  font-size: 2.2rem;
  color: #E2D9CE;
  cursor: pointer;
  padding: 0 2px;
  transition: color 0.15s ease, transform 0.15s ease;
}

.star-select-btn.active,
.star-select-btn:hover {
  color: #B68D40;
  transform: scale(1.15);
}

.rating-text-label {
  margin-left: 12px;
  font-weight: 600;
  font-size: 0.9rem;
  color: #6E1F3A;
}

.form-textarea {
  width: 100%;
  padding: 0.85rem 1rem;
  border: 1px solid #D8C7A3;
  border-radius: 8px;
  font-family: inherit;
  font-size: 0.95rem;
  color: #2F2A26;
  background: #FAF8F5;
  box-sizing: border-box;
  resize: vertical;
}

.form-textarea:focus {
  outline: none;
  border-color: #6E1F3A;
  background: #ffffff;
}

.char-counter {
  text-align: right;
  font-size: 0.75rem;
  color: #9C8A94;
  margin-top: 4px;
}

.field-error {
  color: #d9534f;
  font-size: 0.8rem;
  margin-top: 4px;
  display: block;
}

/* Upload Dropzone */
.upload-dropzone {
  border: 2px dashed #D8C7A3;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  background: #FAF8F5;
  cursor: pointer;
  transition: all 0.2s ease;
}

.upload-dropzone:hover,
.upload-dropzone.drag-over {
  border-color: #6E1F3A;
  background: #F5EFEB;
}

.hidden-file-input {
  display: none;
}

.upload-icon {
  font-size: 2rem;
  margin-bottom: 6px;
}

.upload-title {
  font-weight: 600;
  font-size: 0.9rem;
  color: #4A423A;
}

.upload-subtitle {
  font-size: 0.8rem;
  color: #7A726A;
  margin-top: 4px;
}

/* Photo Previews */
.photo-grid {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.photo-preview-card {
  position: relative;
  width: 90px;
  height: 90px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #D8C7A3;
}

.photo-preview-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.compression-badge {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(110, 31, 58, 0.85);
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 600;
  padding: 2px 4px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.remove-photo-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(0, 0, 0, 0.6);
  color: #ffffff;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.remove-photo-btn:hover {
  background: #d9534f;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.submit-btn {
  min-height: 48px;
  min-width: 150px;
  padding: 0.75rem 1.75rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  color: #ffffff !important;
  background-color: #6E1F3A !important;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(110, 31, 58, 0.2);
}

.submit-btn:hover:not(:disabled) {
  background-color: #52162B !important;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(110, 31, 58, 0.3);
}

.submit-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.button-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: #ffffff;
  animation: spin 0.8s linear infinite;
}

.btn--outline {
  min-height: 48px;
  background: transparent;
  border: 1px solid #D8C7A3;
  color: #4A423A;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn--outline:hover {
  background: #FAF8F5;
  border-color: #B68D40;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.alert--success {
  background: #e6f4ea;
  color: #137333;
  border: 1px solid #ceead6;
}

.alert--error {
  background: #fce8e6;
  color: #c5221f;
  border: 1px solid #fad2cf;
}

/* Reviews List */
.reviews-list-container {
  margin-top: 2rem;
}

.reviews-loading {
  text-align: center;
  padding: 3rem;
  color: #7A726A;
}

.mini-spinner {
  display: inline-block;
  width: 24px;
  height: 24px;
  border: 3px solid rgba(110, 31, 58, 0.2);
  border-radius: 50%;
  border-top-color: #6E1F3A;
  animation: spin 0.8s linear infinite;
  margin-bottom: 8px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-reviews-card {
  text-align: center;
  padding: 3rem 2rem;
  background: #ffffff;
  border: 1px solid #E8DED2;
  border-radius: 16px;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 10px;
}

.empty-reviews-card h3 {
  font-family: 'Playfair Display', serif;
  color: #6E1F3A;
  margin: 0 0 6px 0;
  font-size: 1.4rem;
}

.empty-reviews-card p {
  color: #7A726A;
  font-size: 0.9rem;
  margin: 0;
}

/* Review Cards */
.review-cards {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.review-card {
  background: #ffffff;
  border: 1px solid #E8DED2;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.review-card:hover {
  box-shadow: 0 6px 20px rgba(110, 31, 58, 0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6E1F3A 0%, #B68D40 100%);
  color: #ffffff;
  font-weight: 700;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.user-name {
  font-weight: 700;
  color: #2F2A26;
  font-size: 0.95rem;
}

.verified-badge {
  font-size: 0.75rem;
  font-weight: 600;
  color: #15803d;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  padding: 2px 8px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 3px;
}

.review-date {
  font-size: 0.8rem;
  color: #9C8A94;
  margin-top: 2px;
}

.card-rating-stars {
  display: flex;
  gap: 2px;
}

.review-text {
  font-size: 0.95rem;
  line-height: 1.65;
  color: #4A423A;
  margin: 0 0 1rem 0;
  white-space: pre-line;
}

/* Image Thumbnails */
.review-images-gallery {
  display: flex;
  gap: 10px;
  margin-bottom: 1rem;
  overflow-x: auto;
  padding-bottom: 4px;
}

.review-thumb-box {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 1px solid #E8DED2;
  flex-shrink: 0;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.review-thumb-box:hover {
  transform: scale(1.05);
  border-color: #6E1F3A;
}

.review-thumb-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Card Footer */
.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 0.85rem;
  border-top: 1px solid #F3ECE2;
}

.helpful-btn {
  background: transparent;
  border: 1px solid #E2D9CE;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #7A726A;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: all 0.2s ease;
}

.helpful-btn:hover,
.helpful-btn.active {
  border-color: #6E1F3A;
  color: #6E1F3A;
  background: #FAF3F5;
}

.heart-icon {
  font-size: 0.95rem;
}

.own-review-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
}

.action-link {
  background: none;
  border: none;
  color: #6E1F3A;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}

.action-link:hover {
  text-decoration: underline;
}

.text-danger {
  color: #d9534f;
}

.divider {
  color: #CCC;
}

/* Load More */
.load-more-container {
  text-align: center;
  margin-top: 1.5rem;
}

.load-more-btn {
  padding: 0.75rem 2rem;
  border-radius: 24px;
}

/* Lightbox Modal */
.lightbox-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.9);
  backdrop-filter: blur(8px);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.lightbox-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.lightbox-image-container img {
  max-width: 85vw;
  max-height: 80vh;
  object-fit: contain;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.lightbox-close-btn {
  position: absolute;
  top: -40px;
  right: 0;
  background: transparent;
  border: none;
  color: #ffffff;
  font-size: 1.8rem;
  cursor: pointer;
}

.lightbox-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  border: none;
  font-size: 2rem;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s ease;
}

.lightbox-nav-btn:hover {
  background: rgba(255, 255, 255, 0.4);
}

.lightbox-nav-btn.prev-btn {
  left: -55px;
}

.lightbox-nav-btn.next-btn {
  right: -55px;
}

.lightbox-counter {
  position: absolute;
  bottom: -30px;
  color: #ffffff;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .review-summary-card {
    grid-template-columns: 1fr;
    gap: 1.5rem;
    padding: 1.5rem;
  }

  .summary-left {
    border-right: none;
    border-bottom: 1px solid var(--color-border, #E8DED2);
    padding-right: 0;
    padding-bottom: 1.25rem;
  }

  .lightbox-nav-btn.prev-btn {
    left: 10px;
  }

  .lightbox-nav-btn.next-btn {
    right: 10px;
  }
}
</style>
