<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">Product Catalog</h1>
      <span class="admin-page__subtitle">Manage products, pricing, and variant stock levels.</span>
    </div>
    <router-link to="/admin/products/create" class="btn btn--primary" style="text-decoration: none;">
      <span>➕</span> Add Product
    </router-link>
  </div>

  <!-- Filters panel -->
  <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
      
      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Search</label>
        <input 
          type="text" 
          v-model="filters.search" 
          class="form-input" 
          placeholder="Search name, slug, SKU..." 
          @input="debouncedSearch" 
        />
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Category</label>
        <select v-model="filters.category_id" class="form-input" @change="applyFilters">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
      </div>


      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Status</label>
        <select v-model="filters.is_active" class="form-input" @change="applyFilters">
          <option value="">All Statuses</option>
          <option value="true">Active Only</option>
          <option value="false">Inactive Only</option>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label class="form-label">Sort By</label>
        <select v-model="filters.sort_by" class="form-input" @change="applyFilters">
          <option value="created_at_desc">Newest First</option>
          <option value="price_asc">Price: Low to High</option>
          <option value="price_desc">Price: High to Low</option>
          <option value="name_asc">Name: A to Z</option>
          <option value="name_desc">Name: Z to A</option>
        </select>
      </div>

    </div>
  </div>

  <!-- Loading State -->
  <div v-if="productStore.loading" style="text-align: center; padding: 3rem;">
    <div class="stat-card__value" style="font-size: 1.5rem; color: var(--color-text-secondary);">Loading catalog...</div>
  </div>

  <!-- Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.75rem; width: 100%; border-radius: 8px;">
    ⚠️ {{ errorMsg }}
  </div>

  <!-- Products List -->
  <div v-if="!productStore.loading" class="glass-panel" style="overflow: hidden;">
    
    <!-- Mobile Cards View -->
    <div class="mobile-data-list">
      <div class="mobile-data-card" v-for="prod in products" :key="prod.id">
        <div class="mdc-header">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 40px; height: 40px; border-radius: 6px; overflow: hidden; border: 1px solid var(--color-border); flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
              <img v-if="getPrimaryImage(prod)" :src="getPrimaryImage(prod)" :alt="prod.name" style="width: 100%; height: 100%; object-fit: cover;" />
              <span v-else style="font-weight: 700; color: var(--color-text-muted);">🛍️</span>
            </div>
            <div>
              <div class="mdc-title">{{ prod.name }}</div>
              <div class="mdc-date">₹{{ parseFloat(prod.selling_price).toFixed(2) }} (MRP: ₹{{ parseFloat(prod.mrp).toFixed(2) }})</div>
            </div>
          </div>
        </div>
        
        <div class="mdc-body">
          <div class="mdc-customer">
            <span class="mdc-name">{{ prod.category?.name || 'Uncategorized' }}</span>
            <span class="mdc-email">Slug: {{ prod.slug }}</span>
          </div>
          
          <div class="mdc-totals" style="margin-top: 0.5rem;">
            <div v-if="prod.variants && prod.variants.length > 0" style="display: flex; flex-wrap: wrap; gap: 4px;">
              <span v-for="v in prod.variants" :key="v.id" class="badge" :class="v.stock_quantity <= v.low_stock_threshold ? 'badge--danger' : 'badge--secondary'" style="font-size: 0.65rem; padding: 1px 4px;">
                {{ v.size || '' }}{{ v.color ? '-' + v.color : '' }} ({{ v.stock_quantity }})
              </span>
            </div>
            <span v-else style="color: var(--color-danger); font-size: 0.8rem;">⚠️ No Variants</span>
          </div>
        </div>
        
        <div class="mdc-footer">
          <div class="mdc-badges" style="flex-direction: row; flex-wrap: wrap;">
            <span :class="['badge', prod.is_active ? 'badge--success' : 'badge--danger']">{{ prod.is_active ? 'Active' : 'Inactive' }}</span>
            <span v-if="prod.is_featured" class="badge badge--success">Featured</span>
            <span v-if="prod.is_bestseller" class="badge badge--warning">Bestseller</span>
          </div>
          <div style="display: flex; gap: 0.5rem;">
            <button v-if="!prod.is_active" class="btn btn--success btn--sm" @click="activateProduct(prod.id)">Activate</button>
            <router-link :to="`/admin/products/${prod.id}/edit`" class="btn btn--secondary btn--sm">Edit</router-link>
          </div>
        </div>
      </div>
      
      <div v-if="products.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
        No products found matching filters.
      </div>
    </div>

    <!-- Desktop Table View -->
    <table class="data-table desktop-data-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Slug & Info</th>
          <th>Category</th>
          <th>Base Price (MRP)</th>
          <th>Variants (Stock)</th>
          <th>Status</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="prod in products" :key="prod.id">
          <td>
            <div style="display: flex; align-items: center; gap: 1rem;">
              <!-- Thumbnail -->
              <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.05); border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--color-border); flex-shrink: 0;">
                <img v-if="getPrimaryImage(prod)" :src="getPrimaryImage(prod)" :alt="prod.name" style="width: 100%; height: 100%; object-fit: cover;" />
                <span v-else style="font-weight: 700; color: var(--color-text-muted);">🛍️</span>
              </div>
              <div>
                <div style="font-weight: 600; color: #1e293b; font-size: 0.95rem;">{{ prod.name }}</div>
                <div style="font-size: 0.8rem; color: var(--color-text-muted);">UUID: {{ prod.uuid.substring(0, 8) }}...</div>
              </div>
            </div>
          </td>
          <td>
            <div style="font-size: 0.85rem; font-family: monospace; color: var(--color-text-secondary);">{{ prod.slug }}</div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.25rem;">
              <span v-if="prod.is_featured" class="badge badge--success" style="font-size: 0.7rem; padding: 1px 6px; margin-right: 4px;">Featured</span>
              <span v-if="prod.is_new_arrival" class="badge badge--success" style="font-size: 0.7rem; padding: 1px 6px; margin-right: 4px; background-color: var(--color-secondary);">New</span>
              <span v-if="prod.is_bestseller" class="badge badge--warning" style="font-size: 0.7rem; padding: 1px 6px;">Bestseller</span>
            </div>
          </td>
          <td>
            <div style="color: var(--color-text-primary); font-size: 0.9rem;">{{ prod.category?.name || '—' }}</div>
          </td>
          <td>
            <div style="font-weight: 600; color: #1e293b;">₹{{ parseFloat(prod.selling_price).toFixed(2) }}</div>
            <div style="text-decoration: line-through; font-size: 0.8rem; color: var(--color-text-muted);">₹{{ parseFloat(prod.mrp).toFixed(2) }}</div>
          </td>
          <td>
            <div v-if="prod.variants && prod.variants.length > 0">
              <div style="font-size: 0.85rem; color: var(--color-text-primary);">
                <strong>{{ prod.variants.length }}</strong> variant(s)
              </div>
              <div style="font-size: 0.75rem; color: var(--color-text-secondary); margin-top: 0.25rem; display: flex; flex-wrap: wrap; gap: 4px;">
                <span 
                  v-for="v in prod.variants" 
                  :key="v.id" 
                  class="badge" 
                  :class="v.stock_quantity <= v.low_stock_threshold ? 'badge--danger' : 'badge--secondary'"
                  style="font-size: 0.7rem; padding: 1px 4px;"
                >
                  {{ v.size || '' }}{{ v.color ? '-' + v.color : '' }} ({{ v.stock_quantity }})
                </span>
              </div>
            </div>
            <span v-else style="color: var(--color-danger); font-size: 0.85rem;">⚠️ No Variants</span>
          </td>
          <td>
            <span :class="['badge', prod.is_active ? 'badge--success' : 'badge--danger']">
              {{ prod.is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td style="text-align: right;">
            <div style="display: inline-flex; gap: 0.5rem;">
              <button v-if="!prod.is_active" class="btn btn--success btn--sm" @click="activateProduct(prod.id)">
                🟢 Activate
              </button>
              <router-link :to="`/admin/products/${prod.id}/edit`" class="btn btn--secondary btn--sm" style="text-decoration: none;">
                ✏️ Edit
              </router-link>
              <button class="btn btn--danger btn--sm" @click="deleteProduct(prod.id)">
                🗑️ Delete
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="products.length === 0 && !productStore.loading">
          <td colspan="7" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            No products found matching filters.
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem var(--spacing-lg); border-top: 1px solid var(--color-border); background: rgba(0,0,0,0.1);">
      <div style="font-size: 0.85rem; color: var(--color-text-muted);">
        Showing page <strong>{{ pagination.current_page }}</strong> of <strong>{{ pagination.last_page }}</strong> (Total: {{ pagination.total }} products)
      </div>
      <div style="display: flex; gap: 0.5rem;">
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === 1" 
          @click="changePage(pagination.current_page - 1)"
        >
          ◀️ Previous
        </button>
        <button 
          class="btn btn--secondary btn--sm" 
          :disabled="pagination.current_page === pagination.last_page" 
          @click="changePage(pagination.current_page + 1)"
        >
          Next ▶️
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useProductStore } from '../../stores/product';
import { useCategoryStore } from '../../stores/category';

const productStore = useProductStore();
const categoryStore = useCategoryStore();

const errorMsg = ref(null);

const filters = ref({
  search: '',
  category_id: '',
  is_active: '',
  sort_by: 'created_at_desc',
  page: 1,
});

const products = computed(() => productStore.products);
const pagination = computed(() => productStore.pagination);
const categories = computed(() => categoryStore.categories);

// Debouncing search inputs
let searchTimeout = null;
function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    filters.value.page = 1;
    applyFilters();
  }, 400);
}

function applyFilters() {
  productStore.fetchProducts(filters.value);
}

function changePage(page) {
  filters.value.page = page;
  applyFilters();
}

function getPrimaryImage(product) {
  if (!product.images || product.images.length === 0) return null;
  const primary = product.images.find(img => img.is_primary);
  return primary ? primary.url : product.images[0].url;
}

async function deleteProduct(id) {
  if (confirm('are you sure to Delete ?')) {
    errorMsg.value = null;
    try {
      await productStore.deleteProduct(id);
      applyFilters();
    } catch (err) {
      if (err.error_code === 'PRODUCT_USED') {
        if (confirm(err.message || 'This product is used in orders. Would you like to deactivate it instead?')) {
          try {
            await productStore.updateProduct(id, { is_active: false });
            applyFilters();
          } catch (updateErr) {
            errorMsg.value = updateErr.message || 'Failed to deactivate product';
          }
        }
      } else {
        errorMsg.value = err.message || 'Failed to delete product';
      }
    }
  }
}

async function activateProduct(id) {
  errorMsg.value = null;
  try {
    await productStore.updateProduct(id, { is_active: true });
    applyFilters();
  } catch (err) {
    errorMsg.value = err.message || 'Failed to activate product';
  }
}

onMounted(() => {
  categoryStore.fetchCategories();
  applyFilters();
});
</script>
