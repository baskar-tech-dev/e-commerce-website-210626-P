<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h1>
      <span class="admin-page__subtitle">{{ isEdit ? 'Modify product details, variants, and gallery.' : 'Add a new product with variants to the catalog.' }}</span>
    </div>
    <router-link to="/admin/products" class="btn btn--secondary" style="text-decoration: none;">
      ◀️ Back to Catalog
    </router-link>
  </div>

  <!-- Validation / Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1.5rem; padding: 1rem; width: 100%; border-radius: 8px; font-size: 0.95rem; display: block; text-align: left;">
    <div style="font-weight: bold; margin-bottom: 0.25rem;">⚠️ Operation failed:</div>
    <div v-if="validationErrors.length > 0">
      <ul style="margin-left: 1.25rem;">
        <li v-for="(err, idx) in validationErrors" :key="idx">{{ err }}</li>
      </ul>
    </div>
    <div v-else>{{ errorMsg }}</div>
  </div>

  <div style="display: grid; grid-template-columns: 200px 1fr; gap: 2rem; align-items: start;">
    <!-- Tabs Nav -->
    <div class="glass-panel" style="padding: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem;">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        class="sidebar__link"
        :class="{'sidebar__link--active': activeTab === tab.id}"
        style="width: 100%; border: none; text-align: left; background: transparent; cursor: pointer; padding: 0.75rem 1rem;"
        @click="activeTab = tab.id"
      >
        {{ tab.icon }} {{ tab.label }}
      </button>
    </div>

    <!-- Tab Content Panels -->
    <form @submit.prevent="submitForm">
      <div class="glass-panel" style="padding: 2rem;">
        
        <!-- Tab 1: Basic Info -->
        <div v-show="activeTab === 'basic'">
          <h2 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; font-size: 1.25rem;">Basic Information</h2>
          
          <div class="form-group">
            <label class="form-label">Product Name *</label>
            <input type="text" v-model="form.name" required class="form-input" placeholder="e.g. Men's Slim Fit Cotton Shirt" />
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Category *</label>
              <select v-model="form.category_id" required class="form-input">
                <option value="">Select Category</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Brand</label>
              <select v-model="form.brand_id" class="form-input">
                <option value="">Select Brand</option>
                <option v-for="b in brands" :key="b.id" :value="b.id">
                  {{ b.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Slug (URL friendly, optional)</label>
            <input type="text" v-model="form.slug" class="form-input" placeholder="e.g. mens-slim-fit-cotton-shirt" />
          </div>

          <div class="form-group">
            <label class="form-label">Short Description (Summary)</label>
            <input type="text" v-model="form.short_description" class="form-input" placeholder="Brief 1-sentence description..." />
          </div>

          <div class="form-group">
            <label class="form-label">Full Description (HTML Supported)</label>
            <textarea v-model="form.description" class="form-textarea" rows="6" placeholder="Write full details about sizing, styling, and fabric..."></textarea>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Material / Fabric</label>
              <input type="text" v-model="form.material" class="form-input" placeholder="e.g. 100% Organic Cotton" />
            </div>
            <div class="form-group">
              <label class="form-label">Care Instructions</label>
              <input type="text" v-model="form.care_instructions" class="form-input" placeholder="e.g. Machine wash cold, dry flat" />
            </div>
          </div>

          <!-- Tags -->
          <div class="form-group">
            <label class="form-label" style="margin-bottom: 0.5rem;">Associated Tags</label>
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; background: rgba(0,0,0,0.1); padding: 1rem; border-radius: 8px; border: 1px solid var(--color-border);">
              <label v-for="tag in tags" :key="tag.id" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--color-text-secondary); font-size: 0.9rem;">
                <input type="checkbox" :value="tag.id" v-model="form.tag_ids" />
                {{ tag.name }}
              </label>
              <span v-if="tags.length === 0" style="color: var(--color-text-muted); font-size: 0.9rem;">No tags available.</span>
            </div>
          </div>
        </div>

        <!-- Tab 2: Pricing & Tax -->
        <div v-show="activeTab === 'pricing'">
          <h2 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; font-size: 1.25rem;">Pricing & Tax Logistics</h2>
          
          <div class="grid-3">
            <div class="form-group">
              <label class="form-label">Base MRP (₹) *</label>
              <input type="number" step="0.01" v-model.number="form.mrp" required class="form-input" placeholder="1299" />
            </div>
            <div class="form-group">
              <label class="form-label">Base Selling Price (₹) *</label>
              <input type="number" step="0.01" v-model.number="form.selling_price" required class="form-input" placeholder="999" />
            </div>
            <div class="form-group">
              <label class="form-label">Cost Price (₹, admin-only)</label>
              <input type="number" step="0.01" v-model.number="form.cost_price" class="form-input" placeholder="400" />
            </div>
          </div>

          <div class="grid-3">
            <div class="form-group">
              <label class="form-label">Tax Category</label>
              <select v-model="form.tax_category" class="form-input">
                <option value="standard">Standard (GST)</option>
                <option value="exempt">Exempt</option>
                <option value="reduced">Reduced Rate</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">GST Rate (%)</label>
              <input type="number" step="0.01" v-model.number="form.gst_rate" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">HSN Code</label>
              <input type="text" v-model="form.hsn_code" class="form-input" placeholder="e.g. 620520" />
            </div>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Weight (Grams)</label>
              <input type="number" step="0.01" v-model.number="form.weight" class="form-input" placeholder="e.g. 250" />
            </div>
            <div class="form-group">
              <label class="form-label">Return Window (Days)</label>
              <input type="number" v-model.number="form.return_window_days" class="form-input" />
            </div>
          </div>

          <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; background: rgba(0,0,0,0.15); padding: 1.25rem; border-radius: 8px; border: 1px solid var(--color-border); margin-top: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_active" />
              <span class="form-label" style="margin-bottom: 0;">Publish (Active)</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_featured" />
              <span class="form-label" style="margin-bottom: 0;">Featured Product</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_new_arrival" />
              <span class="form-label" style="margin-bottom: 0;">New Arrival</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_bestseller" />
              <span class="form-label" style="margin-bottom: 0;">Bestseller</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_returnable" />
              <span class="form-label" style="margin-bottom: 0;">Returnable</span>
            </label>
          </div>
        </div>

        <!-- Tab 3: Variants -->
        <div v-show="activeTab === 'variants'">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0;">Product Variants</h2>
            <button type="button" class="btn btn--secondary btn--sm" @click="addVariantRow">
              ➕ Add Variant Sku
            </button>
          </div>

          <div v-if="form.variants.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
            No variants created. Products require at least one variant to manage stock.
          </div>

          <div v-for="(v, index) in form.variants" :key="index" style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; position: relative;">
            <div style="position: absolute; top: 0.75rem; right: 0.75rem; display: flex; gap: 0.5rem; align-items: center;">
              <button type="button" class="btn btn--secondary btn--sm" @click="generateSKURow(index)" title="Auto-generate SKU">
                ⚙️ Gen SKU
              </button>
              <button type="button" class="btn btn--danger btn--sm" @click="removeVariantRow(index)" style="padding: 0.25rem 0.5rem;">
                ✕ Remove
              </button>
            </div>

            <h4 style="margin-bottom: 1rem; color: var(--color-primary); font-size: 0.95rem;">Variant #{{ index + 1 }}</h4>

            <div class="grid-3">
              <div class="form-group">
                <label class="form-label">SKU *</label>
                <input type="text" v-model="v.sku" required class="form-input" placeholder="e.g. M-SHIRT-WHT-S" />
              </div>
              <div class="grid-2" style="grid-column: span 2;">
                <div class="form-group">
                  <label class="form-label">Size</label>
                  <input type="text" v-model="v.size" class="form-input" placeholder="S, M, L, 32" />
                </div>
                <div class="form-group">
                  <label class="form-label">Color Name</label>
                  <input type="text" v-model="v.color" class="form-input" placeholder="White, Navy" />
                </div>
              </div>
            </div>

            <div class="grid-4">
              <div class="form-group">
                <label class="form-label">Hex Color Code</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                  <input type="color" v-model="v.color_code" class="form-input" style="width: 40px; padding: 2px;" />
                  <input type="text" v-model="v.color_code" class="form-input" placeholder="#ffffff" style="font-family: monospace; flex-grow: 1;" />
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Stock Quantity *</label>
                <input type="number" v-model.number="v.stock_quantity" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Alert Threshold</label>
                <input type="number" v-model.number="v.low_stock_threshold" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Barcode</label>
                <input type="text" v-model="v.barcode" class="form-input" placeholder="UPC/EAN" />
              </div>
            </div>

            <div class="grid-4">
              <div class="form-group">
                <label class="form-label">Price Override MRP</label>
                <input type="number" step="0.01" v-model.number="v.mrp" class="form-input" placeholder="Inherited" />
              </div>
              <div class="form-group">
                <label class="form-label">Selling Override</label>
                <input type="number" step="0.01" v-model.number="v.selling_price" class="form-input" placeholder="Inherited" />
              </div>
              <div class="form-group">
                <label class="form-label">Cost Override</label>
                <input type="number" step="0.01" v-model.number="v.cost_price" class="form-input" placeholder="Inherited" />
              </div>
              <div class="form-group" style="flex-direction: row; align-items: flex-end; justify-content: flex-end; padding-bottom: 0.5rem;">
                <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                  <input type="checkbox" v-model="v.is_active" />
                  <span class="form-label" style="margin-bottom: 0;">Variant Active</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 4: Images -->
        <div v-show="activeTab === 'images'">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0;">Image Gallery</h2>
            <button type="button" class="btn btn--secondary btn--sm" @click="addImageRow">
              ➕ Add Image Link
            </button>
          </div>

          <div v-if="form.images.length === 0" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
            No images added. Place direct image URLs below.
          </div>

          <div v-for="(img, index) in form.images" :key="index" style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; position: relative;">
            <button type="button" class="btn btn--danger btn--sm" @click="removeImageRow(index)" style="position: absolute; top: 0.75rem; right: 0.75rem; padding: 0.25rem 0.5rem;">
              ✕ Remove
            </button>

            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 1.5rem;">
              <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--color-border);">
                <img v-if="img.url" :src="img.url" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" />
                <span v-else style="font-size: 1.5rem; color: var(--color-text-muted);">🖼️</span>
              </div>

              <div style="flex-grow: 1;">
                <div class="form-group">
                  <label class="form-label">Full Image URL *</label>
                  <input type="text" v-model="img.url" required class="form-input" placeholder="https://example.com/images/product-front.jpg" />
                </div>

                <div class="grid-2">
                  <div class="form-group">
                    <label class="form-label">Alt Text</label>
                    <input type="text" v-model="img.alt_text" class="form-input" placeholder="Front view of slim shirt" />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Link to Variant SKU (Optional)</label>
                    <select v-model="img.variant_sku" class="form-input">
                      <option value="">General Product Image</option>
                      <option v-for="v in form.variants" :key="v.sku" :value="v.sku">
                        {{ v.sku }} ({{ v.size || 'No Size' }} {{ v.color || '' }})
                      </option>
                    </select>
                  </div>
                </div>

                <div class="grid-3" style="align-items: center; margin-top: 0.5rem;">
                  <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Sort Order</label>
                    <input type="number" v-model.number="img.sort_order" class="form-input" style="padding: 0.25rem 0.5rem;" />
                  </div>
                  <div class="form-group" style="flex-direction: row; margin-bottom: 0; align-items: center; gap: 0.5rem;">
                    <input type="radio" :id="'primary_' + index" :value="index" :checked="img.is_primary" @change="setPrimaryImage(index)" />
                    <label :for="'primary_' + index" class="form-label" style="cursor: pointer; margin-bottom: 0;">Set as Primary Hero Image</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 5: SEO -->
        <div v-show="activeTab === 'seo'">
          <h2 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; font-size: 1.25rem;">SEO Optimization</h2>
          
          <div class="form-group">
            <label class="form-label">Meta Title</label>
            <input type="text" v-model="form.meta_title" class="form-input" placeholder="Search engine title..." />
          </div>

          <div class="form-group">
            <label class="form-label">Meta Description</label>
            <textarea v-model="form.meta_description" class="form-textarea" rows="4" placeholder="Brief snippet shown in search results..."></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Meta Keywords</label>
            <input type="text" v-model="form.meta_keywords" class="form-input" placeholder="shirt, cotton shirt, slim fit (comma separated)" />
          </div>
        </div>

        <!-- Save Button Footer -->
        <div style="margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
          <router-link to="/admin/products" class="btn btn--secondary">
            Cancel
          </router-link>
          <button type="submit" class="btn btn--primary" :disabled="submitting">
            {{ submitting ? 'Saving Product...' : 'Save Product' }}
          </button>
        </div>

      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProductStore } from '../../stores/product';
import { useCategoryStore } from '../../stores/category';
import { useTagStore } from '../../stores/tag';
import { useBrandStore } from '../../stores/brand';

const route = useRoute();
const router = useRouter();

const productStore = useProductStore();
const categoryStore = useCategoryStore();
const brandStore = useBrandStore();
const tagStore = useTagStore();

const isEdit = ref(false);
const submitting = ref(false);
const errorMsg = ref(null);
const validationErrors = ref([]);
const activeTab = ref('basic');

const tabs = [
  { id: 'basic', label: 'Basic Info', icon: '📝' },
  { id: 'pricing', label: 'Pricing & Tax', icon: '🏷️' },
  { id: 'variants', label: 'Variants', icon: '🧵' },
  { id: 'images', label: 'Images', icon: '🖼️' },
  { id: 'seo', label: 'SEO Spec', icon: '🔍' },
];

const form = ref({
  category_id: '',
  brand_id: '',
  name: '',
  slug: '',
  short_description: '',
  description: '',
  material: '',
  care_instructions: '',
  mrp: 0,
  selling_price: 0,
  cost_price: 0,
  tax_category: 'standard',
  gst_rate: 18.00,
  hsn_code: '',
  weight: 0,
  is_active: true,
  is_featured: false,
  is_new_arrival: false,
  is_bestseller: false,
  is_returnable: true,
  return_window_days: 7,
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  tag_ids: [],
  variants: [],
  images: [],
});

const categories = computed(() => categoryStore.categories);
const brands = computed(() => brandStore.brands);
const tags = computed(() => tagStore.tags);

onMounted(async () => {
  categoryStore.fetchCategories();
  brandStore.fetchBrands();
  tagStore.fetchTags();

  if (route.params.id) {
    isEdit.value = true;
    try {
      const prod = await productStore.fetchProduct(route.params.id);
      if (prod) {
        form.value = {
          category_id: prod.category_id || '',
          brand_id: prod.brand_id || '',
          name: prod.name || '',
          slug: prod.slug || '',
          short_description: prod.short_description || '',
          description: prod.description || '',
          material: prod.material || '',
          care_instructions: prod.care_instructions || '',
          mrp: parseFloat(prod.mrp) || 0,
          selling_price: parseFloat(prod.selling_price) || 0,
          cost_price: parseFloat(prod.cost_price) || 0,
          tax_category: prod.tax_category || 'standard',
          gst_rate: parseFloat(prod.gst_rate) || 18.00,
          hsn_code: prod.hsn_code || '',
          weight: parseFloat(prod.weight) || 0,
          is_active: prod.is_active,
          is_featured: prod.is_featured,
          is_new_arrival: prod.is_new_arrival,
          is_bestseller: prod.is_bestseller,
          is_returnable: prod.is_returnable,
          return_window_days: prod.return_window_days || 7,
          meta_title: prod.meta_title || '',
          meta_description: prod.meta_description || '',
          meta_keywords: prod.meta_keywords || '',
          tag_ids: prod.tags ? prod.tags.map(t => t.id) : [],
          variants: prod.variants ? prod.variants.map(v => ({
            id: v.id,
            sku: v.sku,
            size: v.size || '',
            color: v.color || '',
            color_code: v.color_code || '#ffffff',
            mrp: v.mrp ? parseFloat(v.mrp) : null,
            selling_price: v.selling_price ? parseFloat(v.selling_price) : null,
            cost_price: v.cost_price ? parseFloat(v.cost_price) : null,
            stock_quantity: v.stock_quantity || 0,
            low_stock_threshold: v.low_stock_threshold || 5,
            barcode: v.barcode || '',
            is_active: v.is_active,
            sort_order: v.sort_order || 0,
          })) : [],
          images: prod.images ? prod.images.map(img => ({
            id: img.id,
            url: img.url,
            thumbnail_url: img.thumbnail_url || '',
            alt_text: img.alt_text || '',
            sort_order: img.sort_order || 0,
            is_primary: img.is_primary,
            variant_sku: getVariantSkuFromId(prod.variants, img.variant_id)
          })) : [],
        };
      }
    } catch (err) {
      errorMsg.value = 'Failed to load product details.';
    }
  } else {
    // Default one empty variant row
    addVariantRow();
  }
});

function getVariantSkuFromId(variants, variantId) {
  if (!variants || !variantId) return '';
  const match = variants.find(v => v.id === variantId);
  return match ? match.sku : '';
}

function addVariantRow() {
  form.value.variants.push({
    sku: '',
    size: '',
    color: '',
    color_code: '#ffffff',
    mrp: null,
    selling_price: null,
    cost_price: null,
    stock_quantity: 0,
    low_stock_threshold: 5,
    barcode: '',
    is_active: true,
    sort_order: 0,
  });
}

function removeVariantRow(index) {
  form.value.variants.splice(index, 1);
}

function generateSKURow(index) {
  const v = form.value.variants[index];
  const prodName = form.value.name ? form.value.name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, 'PRD') : 'PRD';
  const size = v.size ? v.size.substring(0, 3).toUpperCase() : 'ALL';
  const color = v.color ? v.color.substring(0, 3).toUpperCase() : 'ALL';
  const rand = Math.floor(1000 + Math.random() * 9000);
  v.sku = `${prodName}-${color}-${size}-${rand}`;
}

function addImageRow() {
  form.value.images.push({
    url: '',
    thumbnail_url: '',
    alt_text: '',
    sort_order: 0,
    is_primary: form.value.images.length === 0,
    variant_sku: '',
  });
}

function removeImageRow(index) {
  const wasPrimary = form.value.images[index].is_primary;
  form.value.images.splice(index, 1);
  if (wasPrimary && form.value.images.length > 0) {
    form.value.images[0].is_primary = true;
  }
}

function setPrimaryImage(selectedIndex) {
  form.value.images.forEach((img, idx) => {
    img.is_primary = (idx === selectedIndex);
  });
}

async function submitForm() {
  submitting.value = true;
  errorMsg.value = null;
  validationErrors.value = [];

  // Client-side validations
  if (form.value.variants.length === 0) {
    errorMsg.value = 'Validation failed';
    validationErrors.value.push('At least one product variant is required.');
    activeTab.value = 'variants';
    submitting.value = false;
    return;
  }

  // Ensure Mrp >= selling price for product
  if (form.value.selling_price > form.value.mrp) {
    errorMsg.value = 'Validation failed';
    validationErrors.value.push('Selling price cannot exceed the Maximum Retail Price (MRP).');
    activeTab.value = 'pricing';
    submitting.value = false;
    return;
  }

  // Ensure Mrp >= selling price for variants if override is set
  for (let idx = 0; idx < form.value.variants.length; idx++) {
    const v = form.value.variants[idx];
    if (v.mrp && v.selling_price && v.selling_price > v.mrp) {
      errorMsg.value = 'Validation failed';
      validationErrors.value.push(`Variant #${idx + 1} (${v.sku || 'unnamed'}): Selling price override cannot exceed MRP override.`);
      activeTab.value = 'variants';
      submitting.value = false;
      return;
    }
  }

  try {
    if (isEdit.value) {
      await productStore.updateProduct(route.params.id, form.value);
    } else {
      await productStore.createProduct(form.value);
    }
    router.push('/admin/products');
  } catch (err) {
    errorMsg.value = err.message || 'Failed to save product details.';
    if (err.errors) {
      // Flatten errors map
      Object.keys(err.errors).forEach(key => {
        const errArray = err.errors[key];
        if (Array.isArray(errArray)) {
          errArray.forEach(msg => validationErrors.value.push(msg));
        } else {
          validationErrors.value.push(errArray);
        }
      });
    }
  } finally {
    submitting.value = false;
  }
}
</script>
