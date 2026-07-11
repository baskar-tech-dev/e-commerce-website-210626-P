<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <router-link to="/admin/blog/posts" style="text-decoration: none; color: var(--color-primary); font-size: 0.85rem; font-weight: bold; display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.5rem;">
        ◀ Back to Articles
      </router-link>
      <h1 class="admin-page__title">{{ isEdit ? 'Edit Blog Article' : 'Write New Blog Article' }}</h1>
      <span class="admin-page__subtitle">{{ isEdit ? 'Modify post details, metadata and tags.' : 'Author a new editorial piece for the customer storefront.' }}</span>
    </div>
  </div>

  <!-- Loading Details State -->
  <div v-if="loadingDetails" style="text-align: center; padding: 4rem;">
    <div class="stat-card__value">Loading article parameters...</div>
  </div>

  <div v-else>
    <!-- Main Form Grid layout -->
    <form @submit.prevent="saveBlogPost" class="responsive-grid-2-1" style="gap: var(--spacing-lg);">
      <!-- Left Column: Core content, titles, content bodies -->
      <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <!-- Article Title -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Article Title *</label>
            <input 
              type="text" 
              v-model="form.title" 
              @input="onTitleInput"
              placeholder="e.g. 10 Essential Denim Fits for the Season" 
              class="form-input" 
              required 
            />
          </div>

          <!-- URL Slug -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">URL Slug *</label>
            <input 
              type="text" 
              v-model="form.slug" 
              placeholder="e.g. essential-denim-fits" 
              class="form-input" 
              required 
            />
            <span style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.25rem; display: block;">
              Accessible at: <code>/blog/{{ form.slug || '[slug]' }}</code>
            </span>
          </div>

          <!-- Excerpt -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Brief Excerpt / Summary</label>
            <textarea 
              v-model="form.excerpt" 
              placeholder="Write a catchy 1-2 sentence meta summary describing the post..." 
              class="form-input" 
              style="height: 70px; resize: vertical;"
            ></textarea>
          </div>

          <!-- Content Body -->
          <div class="form-group">
            <label class="form-label">Article Body Content (HTML Supported) *</label>
            <textarea 
              v-model="form.content" 
              placeholder="Write the full content of the post here. You can use standard HTML markup tags..." 
              class="form-input" 
              style="height: 320px; font-family: monospace; resize: vertical;"
              required
            ></textarea>
          </div>
        </div>

        <!-- SEO Metadata Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Search Engine Optimization (SEO)</div>
          
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">SEO Meta Title</label>
            <input 
              type="text" 
              v-model="form.meta_title" 
              placeholder="e.g. Essential Denim Styles Guide | Brand Name" 
              class="form-input" 
            />
          </div>

          <div class="form-group">
            <label class="form-label">SEO Meta Description</label>
            <textarea 
              v-model="form.meta_description" 
              placeholder="Search engine snippet description context (max 160 characters suggested)..." 
              class="form-input" 
              style="height: 80px; resize: vertical;"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Right Column: Settings, categories, tags, status actions -->
      <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
        <!-- Publication Settings Panel -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Publishing Settings</div>

          <!-- Status Selector -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Workflow Status *</label>
            <select v-model="form.status" class="form-input" required>
              <option value="draft">Draft (Private)</option>
              <option value="published">Published (Live)</option>
              <option value="archived">Archived (Hidden)</option>
            </select>
          </div>

          <!-- Featured Toggle -->
          <div class="form-group" style="margin-bottom: var(--spacing-md); display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <input type="checkbox" id="is_featured" v-model="form.is_featured" style="cursor: pointer;" />
            <label for="is_featured" style="color: #1e293b; font-size: 0.85rem; font-weight: 500; cursor: pointer;">
              ⭐ Feature on Blog Front
            </label>
          </div>

          <!-- Category Selector -->
          <div class="form-group" style="margin-bottom: var(--spacing-md);">
            <label class="form-label">Article Category</label>
            <select v-model="form.blog_category_id" class="form-input">
              <option :value="null">Uncategorized</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Cover Image Link -->
          <div class="form-group">
            <label class="form-label">Featured Cover Image URL</label>
            <input 
              type="text" 
              v-model="form.featured_image" 
              placeholder="https://images.unsplash.com/..." 
              class="form-input" 
            />
            <div 
              v-if="form.featured_image" 
              style="margin-top: 0.5rem; width: 100%; height: 120px; border-radius: 6px; overflow: hidden; border: 1px solid var(--color-border); background: rgba(255,255,255,0.03);"
            >
              <img :src="form.featured_image" style="width: 100%; height: 100%; object-fit: cover;" />
            </div>
          </div>
        </div>

        <!-- Tags Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg);">
          <div class="card-header-title" style="margin-bottom: var(--spacing-md);">Article Tags</div>
          
          <div style="max-height: 200px; overflow-y: auto; display: flex; flex-direction: column; gap: var(--spacing-xs);">
            <label 
              v-for="tag in tags" 
              :key="tag.id" 
              style="display: flex; align-items: center; gap: var(--spacing-xs); color: #1e293b; font-size: 0.85rem; cursor: pointer;"
            >
              <input 
                type="checkbox" 
                :value="tag.id" 
                v-model="form.tags" 
                style="cursor: pointer;" 
              />
              <span>#{{ tag.name }}</span>
            </label>
            <div v-if="tags.length === 0" style="font-size: 0.8rem; color: var(--color-text-muted); text-align: center; padding: 1rem;">
              No tag records found.
            </div>
          </div>
        </div>

        <!-- Save/Actions Card -->
        <div class="glass-panel" style="padding: var(--spacing-lg); text-align: center;">
          <button 
            type="submit" 
            class="btn btn--primary" 
            style="width: 100%; margin-bottom: var(--spacing-sm);"
            :disabled="saving"
          >
            {{ saving ? 'Saving Changes...' : '💾 Save Article' }}
          </button>
          
          <router-link to="/admin/blog/posts" class="btn btn--secondary" style="width: 100%; display: block; text-decoration: none;">
            Cancel
          </router-link>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const isEdit = ref(false);
const loadingDetails = ref(false);
const saving = ref(false);

const categories = ref([]);
const tags = ref([]);

const form = ref({
  blog_category_id: null,
  title: '',
  slug: '',
  excerpt: '',
  content: '',
  featured_image: '',
  status: 'draft',
  is_featured: false,
  meta_title: '',
  meta_description: '',
  tags: [],
});

const onTitleInput = () => {
  if (!isEdit.value) {
    form.value.slug = form.value.title
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)+/g, '');
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/admin/blog/categories');
    if (response.data && response.data.success) {
      categories.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load blog categories:', err);
  }
};

const fetchTags = async () => {
  try {
    const response = await axios.get('/api/admin/blog/tags');
    if (response.data && response.data.success) {
      tags.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load blog tags:', err);
  }
};

const fetchPostDetails = async () => {
  if (!route.params.id) return;
  
  isEdit.value = true;
  loadingDetails.value = true;
  try {
    const response = await axios.get(`/api/admin/blog/posts/${route.params.id}`);
    if (response.data && response.data.success) {
      const data = response.data.data;
      form.value = {
        blog_category_id: data.blog_category_id,
        title: data.title,
        slug: data.slug,
        excerpt: data.excerpt || '',
        content: data.content,
        featured_image: data.featured_image || '',
        status: data.status,
        is_featured: !!data.is_featured,
        meta_title: data.meta_title || '',
        meta_description: data.meta_description || '',
        tags: data.tags ? data.tags.map(t => t.id) : [],
      };
    }
  } catch (err) {
    console.error('Failed to load post details:', err);
    alert('Failed to load blog post details');
    router.push('/admin/blog/posts');
  } finally {
    loadingDetails.value = false;
  }
};

const saveBlogPost = async () => {
  saving.value = true;
  try {
    const payload = { ...form.value };
    let response;

    if (isEdit.value) {
      response = await axios.put(`/api/admin/blog/posts/${route.params.id}`, payload);
    } else {
      response = await axios.post('/api/admin/blog/posts', payload);
    }

    if (response.data && response.data.success) {
      router.push('/admin/blog/posts');
    }
  } catch (err) {
    console.error('Failed to save blog post:', err);
    alert(err.response?.data?.message || 'Validation error while saving post');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchCategories();
  fetchTags();
  fetchPostDetails();
});
</script>
