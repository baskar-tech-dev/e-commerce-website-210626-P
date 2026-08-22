<template>
  <div class="admin-review-list-page">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Product Reviews & Moderation</h1>
        <p class="page-subtitle">Manage customer reviews, verify purchases, and moderate uploaded customer photos.</p>
      </div>
      <div>
        <router-link to="/admin/settings" class="btn btn--outline">
          ⚙ Review Settings
        </router-link>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="status-tabs glass-panel">
      <button 
        class="tab-btn" 
        :class="{ active: filters.status === '' }" 
        @click="setStatusFilter('')"
      >
        All Reviews
      </button>
      <button 
        class="tab-btn pending-tab" 
        :class="{ active: filters.status === 'pending' }" 
        @click="setStatusFilter('pending')"
      >
        Pending Approval
        <span v-if="pendingCount > 0" class="badge-count">{{ pendingCount }}</span>
      </button>
      <button 
        class="tab-btn approved-tab" 
        :class="{ active: filters.status === 'approved' }" 
        @click="setStatusFilter('approved')"
      >
        Approved
      </button>
      <button 
        class="tab-btn rejected-tab" 
        :class="{ active: filters.status === 'rejected' }" 
        @click="setStatusFilter('rejected')"
      >
        Rejected
      </button>
    </div>

    <!-- Filter Bar -->
    <div class="filters-bar glass-panel">
      <div class="search-input-wrap">
        <input 
          v-model="filters.search" 
          type="text" 
          placeholder="Search customer name, email, product or review text..."
          class="form-input"
          @input="debouncedFetch"
        />
      </div>

      <div class="select-filter-wrap">
        <select v-model="filters.rating" class="form-select" @change="fetchReviews(1)">
          <option value="">All Ratings</option>
          <option value="5">5 Stars</option>
          <option value="4">4 Stars</option>
          <option value="3">3 Stars</option>
          <option value="2">2 Stars</option>
          <option value="1">1 Star</option>
        </select>
      </div>

      <div class="select-filter-wrap">
        <select v-model="filters.is_verified_purchase" class="form-select" @change="fetchReviews(1)">
          <option value="">All Purchases</option>
          <option value="true">Verified Purchases Only</option>
          <option value="false">Non-verified Only</option>
        </select>
      </div>

      <button class="btn btn--outline" @click="resetFilters">Reset</button>
    </div>

    <!-- Review Table -->
    <div class="table-container glass-panel">
      <div v-if="loading" class="table-loading">
        <div class="spinner"></div>
        <p>Loading reviews...</p>
      </div>

      <table v-else-if="reviews.length > 0" class="admin-table">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Product</th>
            <th>Rating</th>
            <th>Review Text</th>
            <th>Photos</th>
            <th>Verified</th>
            <th>Status</th>
            <th>Date</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rev in reviews" :key="rev.id">
            <!-- Customer -->
            <td>
              <div class="customer-info">
                <div class="customer-name">{{ rev.user?.first_name }} {{ rev.user?.last_name }}</div>
                <div class="customer-email">{{ rev.user?.email }}</div>
              </div>
            </td>

            <!-- Product -->
            <td>
              <div class="product-info">
                <router-link :to="`/products/${rev.product?.slug}`" target="_blank" class="product-link">
                  {{ rev.product?.name || 'Deleted Product' }}
                </router-link>
              </div>
            </td>

            <!-- Rating -->
            <td>
              <div class="rating-stars">
                <span v-for="s in 5" :key="s" class="star" :class="{ filled: s <= rev.rating }">★</span>
                <span class="rating-num">({{ rev.rating }})</span>
              </div>
            </td>

            <!-- Review snippet -->
            <td>
              <div class="review-snippet" :title="rev.review">
                {{ rev.review.length > 100 ? rev.review.substring(0, 100) + '...' : rev.review }}
              </div>
            </td>

            <!-- Images -->
            <td>
              <div v-if="rev.images && rev.images.length > 0" class="image-previews-list">
                <div 
                  v-for="img in rev.images" 
                  :key="img.id" 
                  class="img-thumb"
                  @click="openModal(rev)"
                >
                  <img :src="img.image_path" alt="photo" />
                </div>
              </div>
              <span v-else class="text-muted">—</span>
            </td>

            <!-- Verified -->
            <td>
              <span v-if="rev.is_verified_purchase" class="badge badge--success">
                ✓ Verified
              </span>
              <span v-else class="badge badge--secondary">
                Standard
              </span>
            </td>

            <!-- Status -->
            <td>
              <span 
                class="badge" 
                :class="{
                  'badge--warning': rev.status === 'pending',
                  'badge--success': rev.status === 'approved',
                  'badge--danger': rev.status === 'rejected'
                }"
              >
                {{ rev.status.toUpperCase() }}
              </span>
            </td>

            <!-- Date -->
            <td>
              <span class="text-date">{{ formatDate(rev.created_at) }}</span>
            </td>

            <!-- Actions -->
            <td class="text-right">
              <div class="action-buttons">
                <button 
                  class="btn-action view" 
                  title="View Details" 
                  @click="openModal(rev)"
                >
                  👁️
                </button>

                <button 
                  v-if="rev.status !== 'approved'" 
                  class="btn-action approve" 
                  title="Approve Review"
                  @click="updateStatus(rev, 'approved')"
                >
                  ✓
                </button>

                <button 
                  v-if="rev.status !== 'rejected'" 
                  class="btn-action reject" 
                  title="Reject Review"
                  @click="updateStatus(rev, 'rejected')"
                >
                  ✕
                </button>

                <button 
                  class="btn-action delete" 
                  title="Delete Review"
                  @click="confirmDelete(rev)"
                >
                  🗑️
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-else class="empty-state">
        <p>No customer reviews found matching your criteria.</p>
      </div>

      <!-- Pagination Footer -->
      <div v-if="meta.total > 0" class="pagination-footer">
        <div class="meta-info">
          Showing {{ ((meta.current_page - 1) * meta.per_page) + 1 }} to {{ Math.min(meta.current_page * meta.per_page, meta.total) }} of {{ meta.total }} reviews
        </div>
        <div class="pagination-buttons">
          <button 
            class="btn btn--outline btn--sm" 
            :disabled="meta.current_page === 1"
            @click="fetchReviews(meta.current_page - 1)"
          >
            Previous
          </button>
          <span class="page-num">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
          <button 
            class="btn btn--outline btn--sm" 
            :disabled="meta.current_page === meta.last_page"
            @click="fetchReviews(meta.current_page + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Review Details Modal -->
    <div v-if="selectedReview" class="modal-overlay" @click.self="selectedReview = null">
      <div class="modal-card glass-panel">
        <div class="modal-header">
          <h3>Review Details #{{ selectedReview.id }}</h3>
          <button class="close-modal-btn" @click="selectedReview = null">✕</button>
        </div>

        <div class="modal-body">
          <div class="modal-grid">
            <div>
              <label class="info-label">Customer:</label>
              <div>{{ selectedReview.user?.first_name }} {{ selectedReview.user?.last_name }} ({{ selectedReview.user?.email }})</div>
            </div>

            <div>
              <label class="info-label">Product:</label>
              <div>{{ selectedReview.product?.name }}</div>
            </div>

            <div>
              <label class="info-label">Rating:</label>
              <div class="rating-stars">
                <span v-for="s in 5" :key="s" class="star" :class="{ filled: s <= selectedReview.rating }">★</span>
                <span>({{ selectedReview.rating }}/5)</span>
              </div>
            </div>

            <div>
              <label class="info-label">Verified Purchase:</label>
              <div>
                <span v-if="selectedReview.is_verified_purchase" class="badge badge--success">✓ Verified (Order #{{ selectedReview.order?.order_number || 'N/A' }})</span>
                <span v-else class="badge badge--secondary">Standard Review</span>
              </div>
            </div>
          </div>

          <div class="modal-section">
            <label class="info-label">Customer Review:</label>
            <div class="modal-review-text">{{ selectedReview.review }}</div>
          </div>

          <div v-if="selectedReview.images && selectedReview.images.length > 0" class="modal-section">
            <label class="info-label">Uploaded Customer Photos ({{ selectedReview.images.length }}):</label>
            <div class="modal-gallery">
              <div v-for="img in selectedReview.images" :key="img.id" class="gallery-photo">
                <a :href="img.image_path" target="_blank">
                  <img :src="img.image_path" alt="Customer review photo" />
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button 
            v-if="selectedReview.status !== 'approved'" 
            class="btn btn--success" 
            @click="updateStatus(selectedReview, 'approved'); selectedReview = null"
          >
            ✓ Approve Review
          </button>
          <button 
            v-if="selectedReview.status !== 'rejected'" 
            class="btn btn--warning" 
            @click="updateStatus(selectedReview, 'rejected'); selectedReview = null"
          >
            ✕ Reject Review
          </button>
          <button class="btn btn--outline" @click="selectedReview = null">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const reviews = ref([]);
const loading = ref(true);
const selectedReview = ref(null);
const pendingCount = ref(0);

const filters = reactive({
  status: 'pending',
  rating: '',
  is_verified_purchase: '',
  search: '',
});

const meta = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
});

let debounceTimer = null;

const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchReviews(1);
  }, 350);
};

const setStatusFilter = (status) => {
  filters.status = status;
  fetchReviews(1);
};

const resetFilters = () => {
  filters.status = '';
  filters.rating = '';
  filters.is_verified_purchase = '';
  filters.search = '';
  fetchReviews(1);
};

const fetchReviews = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      status: filters.status,
      rating: filters.rating,
      is_verified_purchase: filters.is_verified_purchase,
      search: filters.search,
    };

    const res = await axios.get('/api/admin/reviews', { params });
    if (res.data && res.data.success) {
      reviews.value = res.data.data;
      meta.current_page = res.data.meta.current_page;
      meta.last_page = res.data.meta.last_page;
      meta.per_page = res.data.meta.per_page;
      meta.total = res.data.meta.total;
    }
  } catch (err) {
    console.error('Failed to fetch admin reviews:', err);
  } finally {
    loading.value = false;
  }
};

const fetchPendingCount = async () => {
  try {
    const res = await axios.get('/api/admin/reviews?status=pending&per_page=1');
    if (res.data && res.data.meta) {
      pendingCount.value = res.data.meta.total || 0;
    }
  } catch (err) {
    // Ignore count error
  }
};

const updateStatus = async (rev, newStatus) => {
  try {
    const res = await axios.patch(`/api/admin/reviews/${rev.id}/status`, { status: newStatus });
    if (res.data && res.data.success) {
      rev.status = newStatus;
      fetchPendingCount();
    }
  } catch (err) {
    console.error('Failed to update status:', err);
    alert('Failed to update review status.');
  }
};

const confirmDelete = async (rev) => {
  if (!confirm(`Are you sure you want to permanently delete review #${rev.id}?`)) return;
  try {
    const res = await axios.delete(`/api/admin/reviews/${rev.id}`);
    if (res.data && res.data.success) {
      fetchReviews(meta.current_page);
      fetchPendingCount();
    }
  } catch (err) {
    console.error('Failed to delete review:', err);
    alert('Failed to delete review.');
  }
};

const openModal = (rev) => {
  selectedReview.value = rev;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-IN', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};

onMounted(() => {
  fetchReviews(1);
  fetchPendingCount();
});
</script>

<style scoped>
.admin-review-list-page {
  padding: 1.5rem;
  font-family: 'Poppins', sans-serif;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: #6E1F3A;
  margin: 0 0 4px 0;
}

.page-subtitle {
  color: #7A726A;
  font-size: 0.9rem;
  margin: 0;
}

/* Status Tabs */
.status-tabs {
  display: flex;
  gap: 8px;
  background: #ffffff;
  padding: 8px;
  border-radius: 12px;
  border: 1px solid #E8DED2;
  margin-bottom: 1rem;
}

.tab-btn {
  background: transparent;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  color: #7A726A;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
}

.tab-btn.active {
  background: #6E1F3A;
  color: #ffffff;
}

.badge-count {
  background: #d9534f;
  color: #ffffff;
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 10px;
}

/* Filters bar */
.filters-bar {
  display: flex;
  gap: 12px;
  background: #ffffff;
  padding: 1rem;
  border-radius: 12px;
  border: 1px solid #E8DED2;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-input-wrap {
  flex-grow: 1;
  min-width: 250px;
}

.form-input, .form-select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #D8C7A3;
  border-radius: 6px;
  font-family: inherit;
  font-size: 0.85rem;
  box-sizing: border-box;
}

/* Table */
.table-container {
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E8DED2;
  overflow: hidden;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.85rem;
}

.admin-table th {
  background: #FAF8F5;
  padding: 12px 16px;
  font-weight: 700;
  color: #4A423A;
  border-bottom: 1px solid #E8DED2;
}

.admin-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #F3ECE2;
  vertical-align: middle;
}

.customer-name {
  font-weight: 600;
  color: #2F2A26;
}

.customer-email {
  font-size: 0.75rem;
  color: #7A726A;
}

.product-link {
  color: #6E1F3A;
  font-weight: 600;
  text-decoration: none;
}

.product-link:hover {
  text-decoration: underline;
}

.rating-stars .star {
  color: #E2D9CE;
  font-size: 0.9rem;
}

.rating-stars .star.filled {
  color: #B68D40;
}

.rating-num {
  font-size: 0.75rem;
  margin-left: 4px;
  color: #7A726A;
}

.review-snippet {
  max-width: 250px;
  color: #4A423A;
}

.image-previews-list {
  display: flex;
  gap: 4px;
}

.img-thumb {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid #E8DED2;
  cursor: pointer;
}

.img-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.badge {
  padding: 3px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge--success { background: #e6f4ea; color: #137333; }
.badge--warning { background: #fef7e0; color: #b06000; }
.badge--danger { background: #fce8e6; color: #c5221f; }
.badge--secondary { background: #f1f3f4; color: #5f6368; }

.action-buttons {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
}

.btn-action {
  background: #FAF8F5;
  border: 1px solid #E8DED2;
  border-radius: 4px;
  padding: 4px 8px;
  cursor: pointer;
  font-size: 0.85rem;
}

.btn-action:hover {
  background: #E8DED2;
}

.btn-action.approve:hover { background: #e6f4ea; color: #137333; }
.btn-action.reject:hover { background: #fce8e6; color: #c5221f; }
.btn-action.delete:hover { background: #fce8e6; color: #c5221f; }

.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: #FAF8F5;
  border-top: 1px solid #E8DED2;
  font-size: 0.8rem;
  color: #7A726A;
}

.pagination-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-card {
  background: #ffffff;
  border-radius: 16px;
  max-width: 600px;
  width: 100%;
  padding: 1.5rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #E8DED2;
  padding-bottom: 1rem;
  margin-bottom: 1rem;
}

.modal-header h3 {
  font-family: 'Playfair Display', serif;
  color: #6E1F3A;
  margin: 0;
}

.close-modal-btn {
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
}

.modal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
  font-size: 0.85rem;
}

.info-label {
  font-weight: 700;
  color: #4A423A;
  display: block;
  margin-bottom: 4px;
}

.modal-review-text {
  background: #FAF8F5;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #E8DED2;
  font-size: 0.9rem;
  white-space: pre-line;
  color: #2F2A26;
  margin-top: 4px;
}

.modal-gallery {
  display: flex;
  gap: 10px;
  margin-top: 8px;
}

.gallery-photo img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #E8DED2;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  border-top: 1px solid #E8DED2;
  padding-top: 1rem;
  margin-top: 1.5rem;
}

.btn--success { background: #137333; color: #ffffff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn--warning { background: #c5221f; color: #ffffff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn--outline { background: transparent; border: 1px solid #D8C7A3; padding: 6px 14px; border-radius: 6px; cursor: pointer; }
</style>
