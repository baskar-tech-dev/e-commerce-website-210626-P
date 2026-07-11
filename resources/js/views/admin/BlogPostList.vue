<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Blog & Content Management</h1>
      <span class="admin-page__subtitle">Author and publish articles, fashion guides, and lookbooks.</span>
    </div>
    <div class="admin-page__actions">
      <router-link to="/admin/blog/posts/create" class="btn btn--primary">
        ➕ Create New Post
      </router-link>
    </div>
  </div>

  <!-- Search and Filters Bar -->
  <div class="glass-panel" style="padding: var(--spacing-md); margin-bottom: var(--spacing-lg); margin-top: var(--spacing-md);">
    <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-md); justify-content: space-between; align-items: center;">
      <!-- Search Input -->
      <div style="flex: 1; min-width: 280px; position: relative;">
        <input 
          type="text" 
          v-model="filters.search" 
          @input="debounceSearch"
          placeholder="Search articles by title or keywords..." 
          class="form-input" 
          style="padding-left: 2rem;" 
        />
        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);">🔍</span>
      </div>

      <!-- Filters Dropdowns -->
      <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Category:</label>
          <select v-model="filters.category_id" @change="fetchPosts(1)" class="form-input" style="width: 170px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
          <label style="font-size: 0.8rem; color: var(--color-text-muted);">Status:</label>
          <select v-model="filters.status" @change="fetchPosts(1)" class="form-input" style="width: 140px; padding: 0.25rem var(--spacing-md);">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Loading State -->
  <div v-if="loading" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Loading blog articles...</div>
  </div>

  <!-- Posts Table Grid -->
  <div v-else class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="post in posts" :key="post.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 50px; height: 50px; border-radius: 6px; overflow: hidden; background: rgba(255,255,255,0.03); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <img v-if="post.featured_image" :src="post.featured_image" style="width: 100%; height: 100%; object-fit: cover;" />
              <span v-else style="font-size: 1.2rem;">📝</span>
            </div>
            <div>
              <router-link :to="`/admin/blog/posts/${post.id}/edit`" class="mdc-title">
                {{ post.title }}
              </router-link>
              <div class="mdc-date">{{ formatDate(post.published_at || post.created_at) }}</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">{{ post.category?.name || 'Uncategorized' }} • By {{ post.author?.first_name }}</span>
            <span class="mdc-email">Slug: /blog/{{ post.slug }}</span>
          </div>
          <div class="mdc-totals" style="margin-top: 0.5rem; display: flex; justify-content: space-between;">
            <span>Views: <strong>{{ post.views_count }}</strong></span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges">
            <span :class="['badge', getStatusBadgeClass(post.status)]">
              {{ post.status.toUpperCase() }}
            </span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <router-link :to="`/admin/blog/posts/${post.id}/edit`" class="btn btn--secondary btn--sm">Edit</router-link>
            <button class="btn btn--danger btn--sm" @click="deletePost(post.id)">Delete</button>
          </div>
        </div>
      </div>
      
      <div v-if="posts.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No blog articles found.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th style="width: 70px;">Cover</th>
          <th>Article Title</th>
          <th>Category</th>
          <th>Author</th>
          <th>Status</th>
          <th style="text-align: right;">Views</th>
          <th>Published Date</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="post in posts" :key="post.id">
          <td>
            <div style="width: 50px; height: 50px; border-radius: 6px; overflow: hidden; background: rgba(255,255,255,0.03); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center;">
              <img 
                v-if="post.featured_image" 
                :src="post.featured_image" 
                style="width: 100%; height: 100%; object-fit: cover;" 
              />
              <span v-else style="font-size: 1.2rem;">📝</span>
            </div>
          </td>
          <td>
            <div style="display: flex; flex-direction: column;">
              <router-link :to="`/admin/blog/posts/${post.id}/edit`" style="color: var(--color-primary); text-decoration: none; font-weight: bold; font-size: 0.95rem;">
                {{ post.title }}
              </router-link>
              <span style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.125rem;">
                Slug: /blog/{{ post.slug }}
              </span>
            </div>
          </td>
          <td>{{ post.category?.name || 'Uncategorized' }}</td>
          <td>{{ post.author?.first_name }} {{ post.author?.last_name }}</td>
          <td>
            <span :class="['badge', getStatusBadgeClass(post.status)]">
              {{ post.status.toUpperCase() }}
            </span>
          </td>
          <td style="text-align: right; font-weight: 500;">{{ post.views_count }}</td>
          <td>{{ formatDate(post.published_at || post.created_at) }}</td>
          <td style="text-align: right;">
            <div style="display: flex; gap: var(--spacing-xs); justify-content: flex-end;">
              <router-link :to="`/admin/blog/posts/${post.id}/edit`" class="btn btn--secondary btn--sm">
                ✏️ Edit
              </router-link>
              <button class="btn btn--danger btn--sm" @click="deletePost(post.id)">
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="posts.length === 0">
          <td colspan="8" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            No blog articles found matching the selected filters.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <div v-if="pagination.last_page > 1" style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); border-top: 1px solid var(--color-border);">
      <span style="font-size: 0.8rem; color: var(--color-text-muted);">
        Showing Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </span>
      <div style="display: flex; gap: var(--spacing-sm);">
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === 1"
          @click="fetchPosts(pagination.current_page - 1)"
        >
          Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page"
          @click="fetchPosts(pagination.current_page + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const posts = ref([]);
const categories = ref([]);
const loading = ref(true);
const errorMsg = ref('');

const filters = ref({
  search: '',
  category_id: '',
  status: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

let searchTimeout = null;

const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchPosts(1);
  }, 350);
};

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get('/api/admin/blog/posts', {
      params: {
        page,
        search: filters.value.search,
        blog_category_id: filters.value.category_id,
        status: filters.value.status,
      }
    });

    if (response.data && response.data.success) {
      posts.value = response.data.data;
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        total: response.data.meta.total,
      };
    }
  } catch (err) {
    console.error('Failed to fetch posts:', err);
    errorMsg.value = 'Failed to load blog posts';
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/admin/blog/categories');
    if (response.data && response.data.success) {
      categories.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to fetch blog categories:', err);
  }
};

const deletePost = async (id) => {
  if (!confirm('Are you sure you want to delete this blog post? This will move it to the recycle bin.')) {
    return;
  }

  try {
    const response = await axios.delete(`/api/admin/blog/posts/${id}`);
    if (response.data && response.data.success) {
      await fetchPosts(pagination.value.current_page);
    }
  } catch (err) {
    console.error('Failed to delete blog post:', err);
    alert('Failed to delete blog post');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'draft': return 'badge--warning';
    case 'published': return 'badge--success';
    case 'archived': return 'badge--danger';
    default: return '';
  }
};

onMounted(() => {
  fetchPosts();
  fetchCategories();
});
</script>
