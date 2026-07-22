<template>
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <h1 class="admin-page__title">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h1>
      <span class="admin-page__subtitle">{{ isEdit ? 'Modify product details, variants, and gallery.' : 'Add a new product with variants to the catalog.' }}</span>
    </div>
    <router-link to="/admin/products" class="btn btn--secondary" style="text-decoration: none; border-radius: 20px; height: 40px; display: inline-flex; align-items: center; padding: 0 1.25rem;">
      ◀️ Back to Catalog
    </router-link>
  </div>

  <!-- Validation / Error Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1.5rem; padding: 1rem; width: 100%; border-radius: 8px; font-size: 0.95rem; display: block; text-align: left; text-transform: none;">
    <div style="font-weight: bold; margin-bottom: 0.25rem;">⚠️ Operation failed:</div>
    <div v-if="validationErrors.length > 0">
      <ul style="margin-left: 1.25rem;">
        <li v-for="(err, idx) in validationErrors" :key="idx">{{ err }}</li>
      </ul>
    </div>
    <div v-else>{{ errorMsg }}</div>
  </div>

  <div class="responsive-grid-200-1" style="gap: 2rem; align-items: start;">
    <!-- Tabs Nav -->
    <div class="glass-panel tabs-nav-container" style="padding: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem;">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        class="sidebar__link"
        :class="{'sidebar__link--active': activeTab === tab.id}"
        :style="{
          width: '100%',
          border: 'none',
          textAlign: 'left',
          cursor: 'pointer',
          padding: '0.75rem 1rem',
          borderRadius: '8px',
          fontFamily: 'var(--font-family-base)',
          fontWeight: activeTab === tab.id ? '600' : '500',
          background: activeTab === tab.id ? 'var(--color-primary)' : 'transparent',
          color: activeTab === tab.id ? '#ffffff' : 'var(--color-text-secondary)',
          boxShadow: activeTab === tab.id ? 'var(--shadow-sm)' : 'none',
          transition: 'all 0.2s ease-in-out'
        }"
        @click="activeTab = tab.id"
      >
        <span style="margin-right: 0.5rem;">{{ tab.icon }}</span> {{ tab.label }}
      </button>
    </div>

    <!-- Tab Content Panels -->
    <form @submit.prevent="submitForm">
      <div class="glass-panel" style="padding: 2rem;">
        
        <!-- Tab 1: Basic Info -->
        <div v-show="activeTab === 'basic'">
          <h2 style="margin-bottom: 2rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; font-size: 1.25rem; color: var(--color-primary);">Basic Information</h2>
          
          <div class="floating-label-group">
            <input type="text" v-model="form.name" :class="{'has-value': !!form.name}" required class="form-input" placeholder=" " id="input_name" />
            <label for="input_name" class="form-label">Product Name *</label>
          </div>

          <div class="floating-label-group">
            <select v-model="form.category_id" required :class="{'has-value': !!form.category_id}" class="form-select" id="select_category">
              <option value=""></option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <label for="select_category" class="form-label">Category *</label>
          </div>

          <div class="floating-label-group">
            <input type="text" v-model="form.slug" :class="{'has-value': !!form.slug}" class="form-input" placeholder=" " id="input_slug" />
            <label for="input_slug" class="form-label">Slug (URL friendly, optional)</label>
          </div>

          <div class="floating-label-group">
            <input type="text" v-model="form.short_description" :class="{'has-value': !!form.short_description}" class="form-input" placeholder=" " id="input_short_description" />
            <label for="input_short_description" class="form-label">Short Description (Summary)</label>
          </div>

          <div class="floating-label-group">
            <textarea v-model="form.description" :class="{'has-value': !!form.description}" class="form-textarea" rows="6" placeholder=" " id="textarea_description"></textarea>
            <label for="textarea_description" class="form-label">Full Description (HTML Supported)</label>
          </div>

          <div class="grid-2">
            <div class="floating-label-group">
              <input type="text" v-model="form.material" :class="{'has-value': !!form.material}" class="form-input" placeholder=" " id="input_material" />
              <label for="input_material" class="form-label">Material / Fabric</label>
            </div>
            <div class="floating-label-group">
              <select v-model="form.occasion" :class="{'has-value': !!form.occasion}" class="form-select" id="select_occasion">
                <option value="">Select Occasion (Optional)</option>
                <option value="Bridal">👰 Bridal</option>
                <option value="Wedding Guest">💍 Wedding Guest</option>
                <option value="Festive">🎉 Festive</option>
                <option value="Party Wear">🎊 Party Wear</option>
                <option value="Family Functions">👨‍👩‍👧 Family Functions</option>
                <option value="Temple Wear">🙏 Temple Wear</option>
                <option value="Office Wear">💼 Office Wear</option>
                <option value="Daily Wear">🌿 Daily Wear</option>
              </select>
              <label for="select_occasion" class="form-label">Occasion / Wear Type</label>
            </div>
          </div>

          <div class="floating-label-group">
            <input type="text" v-model="form.care_instructions" :class="{'has-value': !!form.care_instructions}" class="form-input" placeholder=" " id="input_care_instructions" />
            <label for="input_care_instructions" class="form-label">Care Instructions</label>
          </div>

          <!-- Tags -->
          <div class="form-group" style="margin-top: 0.5rem;">
            <label class="form-label" style="margin-bottom: 0.5rem; font-weight: 600;">Associated Tags</label>
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; background: rgba(0,0,0,0.02); padding: 1.25rem; border-radius: 8px; border: 1px solid var(--color-border);">
              <label v-for="tag in tags" :key="tag.id" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--color-text-secondary); font-size: 0.9rem;">
                <input type="checkbox" :value="tag.id" v-model="form.tag_ids" style="cursor: pointer; width: 16px; height: 16px;" />
                {{ tag.name }}
              </label>
              <span v-if="tags.length === 0" style="color: var(--color-text-muted); font-size: 0.9rem;">No tags available.</span>
            </div>
          </div>
        </div>

        <!-- Tab 2: Pricing & Tax -->
        <div v-show="activeTab === 'pricing'">
          <h2 style="margin-bottom: 2rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; font-size: 1.25rem; color: var(--color-primary);">Pricing & Tax Logistics</h2>
          
          <div class="grid-3">
            <div class="floating-label-group">
              <input type="number" step="0.01" v-model.number="form.mrp" :class="{'has-value': form.mrp !== undefined && form.mrp !== ''}" required class="form-input" placeholder=" " id="input_mrp" />
              <label for="input_mrp" class="form-label">Base MRP (₹) *</label>
            </div>
            <div class="floating-label-group">
              <input type="number" step="0.01" v-model.number="form.selling_price" :class="{'has-value': form.selling_price !== undefined && form.selling_price !== ''}" required class="form-input" placeholder=" " id="input_selling_price" />
              <label for="input_selling_price" class="form-label">Base Selling Price (₹) *</label>
            </div>
            <div class="floating-label-group">
              <input type="number" step="0.01" v-model.number="form.cost_price" :class="{'has-value': form.cost_price !== undefined && form.cost_price !== '' && form.cost_price !== null}" class="form-input" placeholder=" " id="input_cost_price" />
              <label for="input_cost_price" class="form-label">Cost Price (₹, admin-only)</label>
            </div>
          </div>

          <div class="grid-3">
            <div class="floating-label-group">
              <select v-model="form.tax_category" :class="{'has-value': !!form.tax_category}" class="form-select" id="select_tax_category">
                <option value="standard">Standard (GST)</option>
                <option value="exempt">Exempt</option>
                <option value="reduced">Reduced Rate</option>
              </select>
              <label for="select_tax_category" class="form-label">Tax Category</label>
            </div>
            <div class="floating-label-group">
              <input type="number" step="0.01" v-model.number="form.gst_rate" :class="{'has-value': form.gst_rate !== undefined && form.gst_rate !== '' && form.gst_rate !== null}" class="form-input" placeholder=" " id="input_gst_rate" />
              <label for="input_gst_rate" class="form-label">GST Rate (%)</label>
            </div>
            <div class="floating-label-group">
              <input type="text" v-model="form.hsn_code" :class="{'has-value': !!form.hsn_code}" class="form-input" placeholder=" " id="input_hsn_code" />
              <label for="input_hsn_code" class="form-label">HSN Code</label>
            </div>
          </div>

          <div class="grid-2">
            <div class="floating-label-group">
              <input type="number" step="0.01" v-model.number="form.weight" :class="{'has-value': form.weight !== undefined && form.weight !== '' && form.weight !== null}" class="form-input" placeholder=" " id="input_weight" />
              <label for="input_weight" class="form-label">Weight (Grams)</label>
            </div>
            <div class="floating-label-group">
              <input type="number" v-model.number="form.return_window_days" :class="{'has-value': form.return_window_days !== undefined && form.return_window_days !== '' && form.return_window_days !== null}" class="form-input" placeholder=" " id="input_return_window_days" />
              <label for="input_return_window_days" class="form-label">Return Window (Days)</label>
            </div>
          </div>

          <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; background: rgba(0,0,0,0.02); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--color-border); margin-top: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_active" style="width: 16px; height: 16px; cursor: pointer;" />
              <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Publish (Active)</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_featured" style="width: 16px; height: 16px; cursor: pointer;" />
              <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Featured Product</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_new_arrival" style="width: 16px; height: 16px; cursor: pointer;" />
              <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">New Arrival</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_bestseller" style="width: 16px; height: 16px; cursor: pointer;" />
              <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Bestseller</span>
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
              <input type="checkbox" v-model="form.is_returnable" style="width: 16px; height: 16px; cursor: pointer;" />
              <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Returnable</span>
            </label>
          </div>
        </div>

        <!-- Tab 3: Variants -->
        <div v-show="activeTab === 'variants'">
          <!-- Matrix Generator Block -->
          <div style="background: rgba(74, 14, 46, 0.01); padding: 1.5rem; border-radius: 12px; border: 1px dashed var(--color-primary); margin-bottom: 2.5rem;">
            <h3 style="font-size: 1.05rem; color: var(--color-primary); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
              ⚡ Quick Multi-Variant Matrix Generator
            </h3>
            <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 1.5rem;">
              Select or enter comma-separated colors and sizes. Clicking "Generate Matrix" computes all possible SKU combinations instantly.
            </p>
            <div class="grid-2">
              <div class="floating-label-group">
                <input type="text" v-model="matrixGenerator.colors" :class="{'has-value': !!matrixGenerator.colors}" class="form-input" placeholder=" " id="input_gen_colors" />
                <label for="input_gen_colors" class="form-label">Colors (comma separated)</label>
              </div>
              <div class="floating-label-group">
                <input type="text" v-model="matrixGenerator.sizes" :class="{'has-value': !!matrixGenerator.sizes}" class="form-input" placeholder=" " id="input_gen_sizes" />
                <label for="input_gen_sizes" class="form-label">Sizes (comma separated)</label>
              </div>
            </div>
            <div class="grid-4" style="margin-top: 1rem; align-items: flex-end;">
              <div class="floating-label-group">
                <input type="text" v-model="matrixGenerator.skuPrefix" :class="{'has-value': !!matrixGenerator.skuPrefix}" class="form-input" placeholder=" " id="input_gen_sku_prefix" />
                <label for="input_gen_sku_prefix" class="form-label">SKU Prefix</label>
              </div>
              <div class="floating-label-group">
                <input type="number" v-model.number="matrixGenerator.stock" :class="{'has-value': matrixGenerator.stock !== undefined && matrixGenerator.stock !== ''}" class="form-input" placeholder=" " id="input_gen_stock" />
                <label for="input_gen_stock" class="form-label">Default Stock</label>
              </div>
              <div class="floating-label-group">
                <input type="number" step="0.01" v-model.number="matrixGenerator.selling_price" :class="{'has-value': matrixGenerator.selling_price !== undefined && matrixGenerator.selling_price !== '' && matrixGenerator.selling_price !== null}" class="form-input" placeholder=" " id="input_gen_selling_price" />
                <label for="input_gen_selling_price" class="form-label">Base Selling Price</label>
              </div>
              <div class="form-group" style="margin-bottom: var(--spacing-lg);">
                <button type="button" @click="generateVariantMatrix" class="btn btn--primary" style="width: 100%; justify-content: center; height: 52px; border-radius: 8px;">
                  Generate Matrix
                </button>
              </div>
            </div>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0; color: var(--color-primary);">Product Variants</h2>
            <button type="button" class="btn btn--secondary btn--sm" @click="addVariantRow" style="height: 36px; border-radius: 18px; padding: 0 1rem;">
              ➕ Add Custom Variant SKU
            </button>
          </div>

          <div v-if="form.variants.length === 0" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
            No variants created. Add custom SKUs or use the generator above to manage variant stocks.
          </div>

          <div v-for="(v, index) in form.variants" :key="index" style="background: rgba(0,0,0,0.01); border: 1px solid var(--color-border); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px dashed var(--color-border); padding-bottom: 0.75rem;">
              <h4 style="color: var(--color-primary); font-size: 1rem; font-weight: 600; margin: 0;">Variant #{{ index + 1 }}</h4>
              <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button type="button" class="btn btn--secondary btn--sm" @click="generateSKURow(index)" title="Auto-generate SKU" style="height: 32px; border-radius: 16px;">
                  ⚙️ Gen SKU
                </button>
                <button type="button" class="btn btn--danger btn--sm" @click="removeVariantRow(index)" style="padding: 0 0.75rem; height: 32px; border-radius: 16px;">
                  ✕ Remove
                </button>
              </div>
            </div>

            <div class="grid-3">
              <div class="floating-label-group">
                <input type="text" v-model="v.sku" :class="{'has-value': !!v.sku}" required class="form-input" placeholder=" " :id="'input_sku_' + index" />
                <label :for="'input_sku_' + index" class="form-label">SKU *</label>
              </div>
              <div class="grid-2" style="grid-column: span 2;">
                <div class="floating-label-group">
                  <input type="text" v-model="v.size" :class="{'has-value': !!v.size}" class="form-input" placeholder=" " :id="'input_size_' + index" />
                  <label :for="'input_size_' + index" class="form-label">Size</label>
                </div>
                <div class="floating-label-group">
                  <input type="text" v-model="v.color" :class="{'has-value': !!v.color}" class="form-input" placeholder=" " :id="'input_color_' + index" />
                  <label :for="'input_color_' + index" class="form-label">Color Name</label>
                </div>
              </div>
            </div>

            <div class="grid-4">
              <div class="form-group" style="margin-bottom: var(--spacing-lg);">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 2px;">Hex Color Code</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                  <input type="color" v-model="v.color_code" class="form-input" style="width: 44px; height: 44px; padding: 2px; border-radius: 8px;" />
                  <input type="text" v-model="v.color_code" class="form-input" placeholder="#ffffff" style="font-family: monospace; flex-grow: 1; min-height: 44px;" />
                </div>
              </div>
              <div class="floating-label-group">
                <input type="number" v-model.number="v.stock_quantity" :class="{'has-value': v.stock_quantity !== undefined && v.stock_quantity !== ''}" required class="form-input" placeholder=" " :id="'input_stock_' + index" />
                <label :for="'input_stock_' + index" class="form-label">Stock Quantity *</label>
              </div>
              <div class="floating-label-group">
                <input type="number" v-model.number="v.low_stock_threshold" :class="{'has-value': v.low_stock_threshold !== undefined && v.low_stock_threshold !== '' && v.low_stock_threshold !== null}" class="form-input" placeholder=" " :id="'input_threshold_' + index" />
                <label :for="'input_threshold_' + index" class="form-label">Alert Threshold</label>
              </div>
              <div class="floating-label-group">
                <input type="text" v-model="v.barcode" :class="{'has-value': !!v.barcode}" class="form-input" placeholder=" " :id="'input_barcode_' + index" />
                <label :for="'input_barcode_' + index" class="form-label">Barcode</label>
              </div>
            </div>

            <div class="grid-4">
              <div class="floating-label-group">
                <input type="number" step="0.01" v-model.number="v.mrp" :class="{'has-value': v.mrp !== undefined && v.mrp !== '' && v.mrp !== null}" class="form-input" placeholder=" " :id="'input_v_mrp_' + index" />
                <label :for="'input_v_mrp_' + index" class="form-label">Price Override MRP</label>
              </div>
              <div class="floating-label-group">
                <input type="number" step="0.01" v-model.number="v.selling_price" :class="{'has-value': v.selling_price !== undefined && v.selling_price !== '' && v.selling_price !== null}" class="form-input" placeholder=" " :id="'input_v_selling_' + index" />
                <label :for="'input_v_selling_' + index" class="form-label">Selling Override</label>
              </div>
              <div class="floating-label-group">
                <input type="number" step="0.01" v-model.number="v.cost_price" :class="{'has-value': v.cost_price !== undefined && v.cost_price !== '' && v.cost_price !== null}" class="form-input" placeholder=" " :id="'input_v_cost_' + index" />
                <label :for="'input_v_cost_' + index" class="form-label">Cost Override</label>
              </div>
              <div class="form-group" style="flex-direction: row; align-items: center; justify-content: flex-end; height: 52px; margin-bottom: var(--spacing-lg);">
                <label style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                  <input type="checkbox" v-model="v.is_active" style="width: 16px; height: 16px;" />
                  <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Active</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 4: Images -->
        <div v-show="activeTab === 'images'">
          <div style="margin-bottom: 2rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0; color: var(--color-primary);">World-Class Product Media Manager</h2>
            <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin-top: 4px;">
              Upload general product images or drag directly into color-specific sections for color-wise grouping. WebP auto-conversion and sizing runs in the background.
            </p>
          </div>

          <!-- Upload Status / Queue -->
          <div v-if="uploadsInProgress.length > 0" class="progress-list">
            <div v-for="item in uploadsInProgress" :key="item.id" class="progress-item">
              <span style="font-size: 1.25rem;">📤</span>
              <div style="flex-grow: 1; min-width: 150px;">
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.25rem;">
                  <span>{{ item.name }}</span>
                  <span>{{ item.progress }}%</span>
                </div>
                <div class="progress-bar-wrap">
                  <div class="progress-bar" :style="{ width: item.progress + '%' }"></div>
                </div>
              </div>
              <span :class="'badge badge--' + (item.status === 'completed' ? 'success' : item.status === 'failed' ? 'danger' : 'primary')" style="font-size: 0.75rem;">
                {{ item.status }}
              </span>
            </div>
          </div>

          <!-- Section 1: General Upload Zone -->
          <div style="margin-bottom: 3rem;">
            <h3 style="font-size: 1.05rem; margin-bottom: 1rem; color: var(--color-primary);">🖼️ General Product Gallery</h3>
            
            <div 
              class="upload-zone"
              :class="{'upload-zone--active': dragActive.general}"
              @dragover.prevent="dragActive.general = true"
              @dragleave.prevent="dragActive.general = false"
              @drop.prevent="handleDrop($event, null)"
              @click="triggerBrowse(null)"
              style="padding: 2.5rem; border-radius: 12px;"
            >
              <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                <span style="font-size: 2.5rem;">📥</span>
                <span style="font-weight: 600; font-family: var(--font-family-base);">Drag & Drop files here or click to browse</span>
                <span style="font-size: 0.85rem; color: var(--color-text-muted);">Supports JPG, PNG, WEBP, AVIF (Max 10MB)</span>
              </div>
              <input 
                type="file" 
                multiple 
                data-color-group="general"
                @change="handleFileSelect($event, null)" 
                style="display: none;" 
              />
            </div>

            <!-- General Image Grid -->
            <div class="media-grid">
              <div 
                v-for="(img, idx) in form.images.filter(i => !i.color_group)" 
                :key="idx" 
                class="media-card"
                style="border-radius: 10px; box-shadow: var(--shadow-sm);"
              >
                <div class="media-card__img-wrap">
                  <img :src="img.url" class="media-card__img" alt="Product image" />
                  <span v-if="img.is_primary" class="media-card__badge" style="background: var(--color-primary); color: #ffffff; font-weight: bold; border-radius: 4px;">PRIMARY HERO</span>
                </div>
                
                <div style="padding: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem;">
                  <input type="text" v-model="img.alt_text" class="form-input" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; min-height: 32px;" placeholder="Alt / SEO text..." />
                  
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; cursor: pointer; color: var(--color-text-primary);">
                      <input type="radio" name="hero_image" :checked="img.is_primary" @change="setPrimaryImage(form.images.indexOf(img))" style="width: 14px; height: 14px;" />
                      <span>Set Hero</span>
                    </label>
                    <button type="button" @click="deleteImage(form.images.indexOf(img))" class="btn btn--danger btn--sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px; height: auto;">
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: Color Specific Upload Galleries -->
          <div v-if="uniqueColors.length > 0">
            <h3 style="font-size: 1.15rem; margin-bottom: 1.5rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem; color: var(--color-primary);">
              🎨 Color-Wise Variant Galleries
            </h3>

            <div v-for="color in uniqueColors" :key="color" style="margin-bottom: 2rem; background: rgba(0,0,0,0.01); border: 1px solid var(--color-border); padding: 1.5rem; border-radius: 12px;">
              <h4 style="font-size: 1rem; color: var(--color-primary); margin-bottom: 1rem; text-transform: uppercase; font-weight: bold; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; border: 1px solid var(--color-border); background: var(--color-primary);"></span> {{ color }} Gallery
              </h4>
              
              <div 
                class="upload-zone"
                :class="{'upload-zone--active': dragActive[color]}"
                @dragover.prevent="dragActive[color] = true"
                @dragleave.prevent="dragActive[color] = false"
                @drop.prevent="handleDrop($event, color)"
                @click="triggerBrowse(color)"
                style="padding: 1.5rem; border-radius: 10px;"
              >
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                  <span style="font-size: 1.5rem;">📥</span>
                  <span style="font-size: 0.9rem; font-weight: 500;">Drop or click to add images specifically for <strong>{{ color }}</strong> variants</span>
                </div>
                <input 
                  type="file" 
                  multiple 
                  :data-color-group="color"
                  @change="handleFileSelect($event, color)" 
                  style="display: none;" 
                />
              </div>

              <!-- Grid of color-specific images -->
              <div class="media-grid">
                <div 
                  v-for="(img, idx) in form.images.filter(i => i.color_group === color)" 
                  :key="idx" 
                  class="media-card"
                  style="border-radius: 10px; box-shadow: var(--shadow-sm);"
                >
                  <div class="media-card__img-wrap">
                    <img :src="img.url" class="media-card__img" alt="Product variant image" />
                  </div>
                  
                  <div style="padding: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem;">
                    <input type="text" v-model="img.alt_text" class="form-input" style="font-size: 0.8rem; padding: 0.25rem 0.5rem; min-height: 32px;" placeholder="Alt / SEO text..." />
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                      <span style="font-size: 0.75rem; color: var(--color-text-secondary); font-weight: 500;">
                        Mapped: {{ color }}
                      </span>
                      <button type="button" @click="deleteImage(form.images.indexOf(img))" class="btn btn--danger btn--sm" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; border-radius: 4px; height: auto;">
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 5: SEO -->
        <div v-show="activeTab === 'seo'">
          <h2 style="margin-bottom: 2rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; font-size: 1.25rem; color: var(--color-primary);">SEO Optimization</h2>
          
          <div class="floating-label-group">
            <input type="text" v-model="form.meta_title" :class="{'has-value': !!form.meta_title}" class="form-input" placeholder=" " id="input_meta_title" />
            <label for="input_meta_title" class="form-label">Meta Title</label>
          </div>

          <div class="floating-label-group">
            <textarea v-model="form.meta_description" :class="{'has-value': !!form.meta_description}" class="form-textarea" rows="4" placeholder=" " id="textarea_meta_description"></textarea>
            <label for="textarea_meta_description" class="form-label">Meta Description</label>
          </div>

          <div class="floating-label-group">
            <input type="text" v-model="form.meta_keywords" :class="{'has-value': !!form.meta_keywords}" class="form-input" placeholder=" " id="input_meta_keywords" />
            <label for="input_meta_keywords" class="form-label">Meta Keywords</label>
          </div>
        </div>

        <!-- Save Button Footer -->
        <div class="form-actions-footer" style="margin-top: 3rem; border-top: 1px solid var(--color-border); padding-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
          <router-link to="/admin/products" class="btn btn--secondary btn-action-footer">
            Cancel
          </router-link>
          <button type="submit" class="btn btn--primary btn-action-footer" :disabled="submitting" style="box-shadow: var(--shadow-md);">
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
import axios from 'axios';
import { useProductStore } from '../../stores/product';
import { useCategoryStore } from '../../stores/category';
import { useTagStore } from '../../stores/tag';

const route = useRoute();
const router = useRouter();

const productStore = useProductStore();
const categoryStore = useCategoryStore();
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
  collection_name: '',
  name: '',
  display_name: '',
  subtitle: '',
  slug: '',
  short_description: '',
  description: '',
  occasion: '',
  season: '',
  style: '',
  material: '',
  fabric: '',
  pattern: '',
  sleeve: '',
  neck: '',
  fit: '',
  care_instructions: '',
  alt_text: '',
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
  is_premium: false,
  is_trending: false,
  is_editors_pick: false,
  sort_order: 0,
  is_returnable: true,
  return_window_days: 7,
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  tag_ids: [],
  variants: [],
  images: [],
});

const matrixGenerator = ref({
  colors: '',
  sizes: '',
  skuPrefix: '',
  stock: 10,
  mrp: null,
  selling_price: null,
});

const uploadsInProgress = ref([]);
const dragActive = ref({});

const categories = computed(() => categoryStore.categories);
const tags = computed(() => tagStore.tags);

const uniqueColors = computed(() => {
  const colors = form.value.variants
    .map(v => v.color ? v.color.trim() : '')
    .filter(c => c !== '');
  return [...new Set(colors)];
});

onMounted(async () => {
  categoryStore.fetchCategories();
  tagStore.fetchTags();

  if (route.params.id) {
    isEdit.value = true;
    try {
      const prod = await productStore.fetchProduct(route.params.id);
      if (prod) {
        form.value = {
          category_id: prod.category_id || '',
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
            color_group: img.color_group || null,
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
    sort_order: form.value.variants.length,
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

function generateVariantMatrix() {
  const colorsInput = matrixGenerator.value.colors.split(',').map(c => c.trim()).filter(c => c !== '');
  const sizesInput = matrixGenerator.value.sizes.split(',').map(s => s.trim()).filter(s => s !== '');
  const prefix = matrixGenerator.value.skuPrefix.toUpperCase().replace(/[^A-Z0-9-]/g, '') || 'PROD';

  if (colorsInput.length === 0 && sizesInput.length === 0) {
    alert('Please enter at least one size or color to generate variants.');
    return;
  }

  const generated = [];
  const colors = colorsInput.length > 0 ? colorsInput : [''];
  const sizes = sizesInput.length > 0 ? sizesInput : [''];

  colors.forEach((color, cIdx) => {
    sizes.forEach((size, sIdx) => {
      const colorPart = color ? color.substring(0, 3).toUpperCase() : 'GEN';
      const sizePart = size ? size.toUpperCase() : 'ALL';
      const sku = `${prefix}-${colorPart}-${sizePart}-${Math.floor(100 + Math.random() * 900)}`;

      generated.push({
        sku,
        size: size || '',
        color: color || '',
        color_code: '#ffffff',
        mrp: matrixGenerator.value.mrp || null,
        selling_price: matrixGenerator.value.selling_price || null,
        cost_price: null,
        stock_quantity: matrixGenerator.value.stock || 0,
        low_stock_threshold: 5,
        barcode: '',
        is_active: true,
        sort_order: form.value.variants.length + generated.length,
      });
    });
  });

  form.value.variants = [...form.value.variants, ...generated];
  matrixGenerator.value.colors = '';
  matrixGenerator.value.sizes = '';
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

// Media upload helpers
function triggerBrowse(colorGroup) {
  const refName = colorGroup ? `file_browse_${colorGroup}` : 'file_browse_general';
  const fileInputs = document.querySelectorAll(`input[type="file"]`);
  for (let input of fileInputs) {
    if (input.dataset.colorGroup === (colorGroup || 'general')) {
      input.click();
      break;
    }
  }
}

function handleFileSelect(e, colorGroup) {
  const files = e.target.files;
  if (!files || files.length === 0) return;
  Array.from(files).forEach(file => uploadFile(file, colorGroup));
}

function handleDrop(e, colorGroup) {
  dragActive.value[colorGroup || 'general'] = false;
  const files = e.dataTransfer.files;
  if (!files || files.length === 0) return;
  Array.from(files).forEach(file => uploadFile(file, colorGroup));
}

async function uploadFile(file, colorGroup) {
  // Validate file
  const valFormData = new FormData();
  valFormData.append('file', file);

  try {
    const valRes = await axios.post('/api/admin/media/validate', valFormData);
    if (!valRes.data.success) {
      alert(`Validation error: ${valRes.data.message}`);
      return;
    }
  } catch (err) {
    alert(`File validation failed: ${err.response?.data?.message || err.message}`);
    return;
  }

  // Upload file
  const uploadId = Math.random().toString(36).substring(7);
  const uploadItem = ref({
    id: uploadId,
    name: file.name,
    progress: 0,
    status: 'uploading',
  });
  uploadsInProgress.value.push(uploadItem.value);

  const fd = new FormData();
  fd.append('file', file);
  if (colorGroup) fd.append('color_group', colorGroup);
  if (route.params.id) fd.append('product_id', route.params.id);

  try {
    const res = await axios.post('/api/admin/media/upload', fd, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        const active = uploadsInProgress.value.find(item => item.id === uploadId);
        if (active) active.progress = percentCompleted;
      }
    });

    if (res.data.success) {
      const active = uploadsInProgress.value.find(item => item.id === uploadId);
      if (active) active.status = 'completed';

      form.value.images.push({
        id: res.data.data.id || null,
        url: res.data.data.url,
        thumbnail_url: res.data.data.thumbnail_url,
        temp_path: res.data.data.temp_path || null,
        alt_text: file.name.split('.')[0].replace(/-/g, ' '),
        sort_order: form.value.images.length,
        is_primary: form.value.images.length === 0,
        color_group: colorGroup || null,
        variant_sku: '',
      });
    }
  } catch (err) {
    const active = uploadsInProgress.value.find(item => item.id === uploadId);
    if (active) {
      active.status = 'failed';
      active.error = err.response?.data?.message || 'Upload failed';
    }
    alert(`Failed to upload ${file.name}: ${err.response?.data?.message || err.message}`);
  } finally {
    // Clear completed uploads after delay
    setTimeout(() => {
      uploadsInProgress.value = uploadsInProgress.value.filter(item => item.id !== uploadId);
    }, 3000);
  }
}

async function deleteImage(index) {
  const img = form.value.images[index];
  if (img.id) {
    if (!confirm('Are you sure you want to permanently delete this image from the server?')) return;
    try {
      await axios.delete(`/api/admin/media/${img.id}`);
    } catch (err) {
      alert(`Failed to delete image: ${err.message}`);
      return;
    }
  }
  removeImageRow(index);
}

const getTabForErrorKey = (key) => {
  if (!key) return 'basic';
  const k = key.toLowerCase();
  
  if (k.startsWith('variants')) {
    return 'variants';
  }
  if (k.startsWith('images') || k.startsWith('media')) {
    return 'images';
  }
  
  const pricingKeys = [
    'mrp', 'selling_price', 'cost_price', 'tax_category', 
    'gst_rate', 'hsn_code', 'weight', 'return_window_days',
    'is_active', 'is_featured', 'is_new_arrival', 'is_bestseller', 'is_returnable'
  ];
  if (pricingKeys.some(pk => k.includes(pk))) {
    return 'pricing';
  }
  
  const seoKeys = ['meta_title', 'meta_description', 'meta_keywords'];
  if (seoKeys.some(sk => k.includes(sk))) {
    return 'seo';
  }
  
  return 'basic';
};

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
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  // Ensure Mrp >= selling price for product
  if (form.value.selling_price > form.value.mrp) {
    errorMsg.value = 'Validation failed';
    validationErrors.value.push('Selling price cannot exceed the Maximum Retail Price (MRP).');
    activeTab.value = 'pricing';
    submitting.value = false;
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
      window.scrollTo({ top: 0, behavior: 'smooth' });
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
    let firstErrorKey = null;
    if (err.errors) {
      // Flatten errors map
      Object.keys(err.errors).forEach((key, index) => {
        if (index === 0) {
          firstErrorKey = key;
        }
        const errArray = err.errors[key];
        if (Array.isArray(errArray)) {
          errArray.forEach(msg => validationErrors.value.push(msg));
        } else {
          validationErrors.value.push(errArray);
        }
      });
    }
    // Switch to the tab of the first error (FIFO tracking)
    activeTab.value = getTabForErrorKey(firstErrorKey);
    // Scroll to the top of the page so the user can see the error panel
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
.upload-zone {
  border: 2px dashed var(--color-border);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  background: rgba(255, 255, 255, 0.01);
  transition: all 0.2s ease-in-out;
  cursor: pointer;
  margin-bottom: 1.5rem;
}
.upload-zone:hover, .upload-zone--active {
  border-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.03);
}
.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1.25rem;
  margin-top: 1rem;
}
.media-card {
  position: relative;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.media-card__img-wrap {
  position: relative;
  aspect-ratio: 1;
  background: rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.media-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.media-card__badge {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  background: rgba(0,0,0,0.65);
  color: #fff;
  font-size: 0.7rem;
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
  backdrop-filter: blur(2px);
}
.media-card__actions {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  display: flex;
  gap: 0.25rem;
}
.media-card__btn {
  background: rgba(0, 0, 0, 0.65);
  color: #fff;
  border: none;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.8rem;
  backdrop-filter: blur(2px);
}
.media-card__btn:hover {
  background: var(--color-danger);
}
.progress-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}
.progress-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: rgba(255,255,255,0.03);
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--color-border);
}
.progress-bar-wrap {
  flex-grow: 1;
  height: 6px;
  background: rgba(255,255,255,0.1);
  border-radius: 3px;
  overflow: hidden;
}
.progress-bar {
  height: 100%;
  background: var(--color-primary);
  transition: width 0.1s ease;
}

/* Scoped Responsive overrides */
.btn-action-footer {
  height: 48px;
  border-radius: 24px;
  padding: 0 2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 900px) {
  .tabs-nav-container {
    flex-direction: row !important;
    overflow-x: auto !important;
    white-space: nowrap !important;
    padding: 0.5rem !important;
    background: #ffffff !important;
    border-radius: 8px !important;
    border: 1px solid var(--color-border) !important;
    box-shadow: var(--shadow-sm) !important;
  }
  .tabs-nav-container button {
    width: auto !important;
    flex: 0 0 auto !important;
  }
}

@media (max-width: 600px) {
  .form-actions-footer {
    flex-direction: column-reverse !important;
    gap: 0.75rem !important;
  }
  .btn-action-footer {
    width: 100% !important;
  }
}
</style>
