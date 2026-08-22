<template>
  <!-- Page Header -->
  <div class="admin-page__header">
    <div class="admin-page__title-section">
      <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
        <h1 class="admin-page__title">{{ isEdit ? 'Edit Product' : 'Create Product' }}</h1>
        <span 
          :class="['badge', form.is_active ? 'badge--success' : 'badge--warning']" 
          style="font-size: 0.8rem; padding: 0.25rem 0.75rem; border-radius: 12px;"
        >
          {{ form.is_active ? '🟢 Published / Active' : '📝 Draft Mode' }}
        </span>
      </div>
      <span class="admin-page__subtitle">
        {{ isEdit ? 'Update product specifications, SKU variants, galleries, and launch readiness.' : 'Step-by-step guided product creation with inventory matrix and quality verification.' }}
      </span>
    </div>
    
    <!-- Top Action Buttons -->
    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
      <router-link to="/admin/products" class="btn btn--secondary" style="text-decoration: none; border-radius: 24px; height: 44px; display: inline-flex; align-items: center; padding: 0 1.25rem;">
        ◀️ Catalog
      </router-link>
      <button 
        type="button" 
        class="btn btn--secondary" 
        @click="saveAsDraft" 
        :disabled="submitting"
        style="border-radius: 24px; height: 44px; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0 1.25rem;"
      >
        <span>💾</span> {{ submitting && saveMode === 'draft' ? 'Saving Draft...' : 'Save Draft' }}
      </button>
      <button 
        type="button" 
        class="btn btn--primary" 
        @click="saveAndPublish" 
        :disabled="submitting"
        style="border-radius: 24px; height: 44px; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0 1.5rem; box-shadow: var(--shadow-md);"
      >
        <span>🚀</span> {{ submitting && saveMode === 'publish' ? 'Publishing...' : (isEdit ? 'Save Changes' : 'Publish Product') }}
      </button>
    </div>
  </div>

  <!-- Toast / Feedback Alert -->
  <div v-if="feedbackMsg" class="badge badge--success" style="margin-bottom: 1.5rem; padding: 1rem; width: 100%; border-radius: 8px; font-size: 0.95rem; display: block; text-align: left; text-transform: none; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
    <div style="display: flex; align-items: center; justify-content: space-between;">
      <div style="font-weight: 600;">✨ {{ feedbackMsg }}</div>
      <button type="button" @click="feedbackMsg = null" style="background: none; border: none; cursor: pointer; color: #065f46; font-size: 1rem;">✕</button>
    </div>
  </div>

  <!-- Top Error Summary Alert -->
  <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1.5rem; padding: 1rem; width: 100%; border-radius: 8px; font-size: 0.95rem; display: block; text-align: left; text-transform: none;">
    <div style="font-weight: bold; margin-bottom: 0.25rem; display: flex; align-items: center; justify-content: space-between;">
      <span>⚠️ Please resolve the field errors below to proceed:</span>
      <button type="button" @click="errorMsg = null" style="background: none; border: none; cursor: pointer; color: inherit; font-size: 1rem;">✕</button>
    </div>
    <div v-if="validationErrors.length > 0">
      <ul style="margin-left: 1.25rem; margin-top: 0.25rem;">
        <li v-for="(err, idx) in validationErrors" :key="idx">{{ err }}</li>
      </ul>
    </div>
    <div v-else style="margin-top: 0.25rem;">{{ errorMsg }}</div>
  </div>

  <!-- Step Progress Stepper Bar -->
  <div class="glass-panel stepper-container" style="padding: 1.25rem; margin-bottom: 2rem; border-radius: 12px;">
    <div class="stepper-track">
      <div 
        v-for="(step, index) in steps" 
        :key="step.id"
        class="stepper-item"
        :class="{
          'stepper-item--active': activeTab === step.id,
          'stepper-item--completed': isStepCompleted(step.id),
          'stepper-item--invalid': hasStepErrors(step.id)
        }"
        @click="goToStep(step.id)"
      >
        <div class="stepper-badge">
          <span v-if="hasStepErrors(step.id)" class="stepper-err-icon">!</span>
          <span v-else-if="isStepCompleted(step.id)" class="stepper-check">✓</span>
          <span v-else>{{ index + 1 }}</span>
        </div>
        <div class="stepper-content">
          <div class="stepper-title">{{ step.icon }} {{ step.label }}</div>
          <div class="stepper-subtitle">{{ step.sublabel }}</div>
        </div>
        <div v-if="index < steps.length - 1" class="stepper-line"></div>
      </div>
    </div>
    <!-- Step Progress Line -->
    <div class="progress-bar-wrap" style="height: 4px; margin-top: 1rem;">
      <div 
        class="progress-bar" 
        :style="{ width: `${((currentStepIndex + 1) / steps.length) * 100}%`, background: 'var(--color-primary)' }"
      ></div>
    </div>
  </div>

  <div class="responsive-grid-200-1" style="gap: 2rem; align-items: start;">
    <!-- Left Navigation Tabs (Desktop & Tablet) -->
    <div class="glass-panel tabs-nav-container" style="padding: 0.75rem; display: flex; flex-direction: column; gap: 0.35rem; border-radius: 12px;">
      <button 
        v-for="step in steps" 
        :key="step.id"
        type="button"
        class="sidebar__link"
        :class="{'sidebar__link--active': activeTab === step.id}"
        :style="{
          width: '100%',
          border: 'none',
          textAlign: 'left',
          cursor: 'pointer',
          padding: '0.85rem 1rem',
          borderRadius: '8px',
          fontFamily: 'var(--font-family-base)',
          fontWeight: activeTab === step.id ? '600' : '500',
          background: activeTab === step.id ? 'var(--color-primary)' : 'transparent',
          color: activeTab === step.id ? '#ffffff' : 'var(--color-text-secondary)',
          boxShadow: activeTab === step.id ? 'var(--shadow-sm)' : 'none',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          transition: 'all 0.2s ease-in-out'
        }"
        @click="goToStep(step.id)"
      >
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <span style="font-size: 1.1rem;">{{ step.icon }}</span>
          <span>{{ step.label }}</span>
        </div>
        <span 
          v-if="hasStepErrors(step.id)" 
          style="font-size: 0.72rem; background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 10px; font-weight: bold;"
        >
          ⚠️ Error
        </span>
        <span 
          v-else-if="isStepCompleted(step.id)" 
          style="font-size: 0.75rem; background: rgba(16, 185, 129, 0.2); color: #059669; padding: 2px 6px; border-radius: 10px; font-weight: bold;"
          :style="activeTab === step.id ? 'background: #ffffff; color: var(--color-primary);' : ''"
        >
          ✓ Ready
        </span>
      </button>

      <!-- Readiness Widget in Sidebar -->
      <div style="margin-top: 1.5rem; padding: 1rem; background: var(--blush-bg); border-radius: 8px; border: 1px solid var(--color-border); text-align: center;">
        <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-primary); margin-bottom: 0.25rem;">
          Readiness Score
        </div>
        <div style="font-size: 1.5rem; font-family: var(--font-family-heading); font-weight: bold;" :style="{ color: readinessScore === 100 ? '#059669' : 'var(--color-primary)' }">
          {{ readinessScore }}%
        </div>
        <div style="font-size: 0.75rem; color: var(--color-text-secondary); margin-top: 0.25rem;">
          {{ readinessScore === 100 ? '🎉 All checks passed' : `${pendingVerificationCount} item(s) pending` }}
        </div>
        <button 
          type="button" 
          class="btn btn--secondary btn--sm" 
          @click="goToStep('verify')" 
          style="margin-top: 0.75rem; width: 100%; border-radius: 16px; font-size: 0.75rem; height: 32px;"
        >
          ✨ Verify Details
        </button>
      </div>
    </div>

    <!-- Main Step Form Content -->
    <form @submit.prevent="submitForm">
      <div class="glass-panel" style="padding: 2.25rem; border-radius: 12px;">
        
        <!-- STEP 1: Basic Information -->
        <div v-show="activeTab === 'basic'">
          <div class="step-header">
            <div>
              <h2 class="step-heading">📝 Step 1: Basic Information</h2>
              <p class="step-desc">Enter the foundational details of the garment, including title, category, fabrics, and descriptions.</p>
            </div>
            <span class="step-indicator-pill">Step 1 of 6</span>
          </div>
          
          <!-- Product Name Field -->
          <div class="field-wrapper" style="margin-bottom: 1.5rem;">
            <div class="floating-label-group" :class="{'has-field-error': !!fieldErrors.name}">
              <input 
                type="text" 
                v-model="form.name" 
                :class="{'has-value': !!form.name, 'form-input--error': !!fieldErrors.name}" 
                class="form-input" 
                placeholder=" " 
                id="input_name"
                @input="clearFieldError('name')"
                @blur="validateField('name')"
              />
              <label for="input_name" class="form-label">Product Name * (e.g., Readymade Stretchable Saree Blouse with Gold Zari Border)</label>
            </div>
            <span v-if="fieldErrors.name" class="field-error-text">
              ⚠️ {{ fieldErrors.name }}
            </span>
          </div>

          <!-- Category Field -->
          <div class="field-wrapper" style="margin-bottom: 1.5rem;">
            <div class="floating-label-group" :class="{'has-field-error': !!fieldErrors.category_id}">
              <select 
                v-model="form.category_id" 
                :class="{'has-value': !!form.category_id, 'form-select--error': !!fieldErrors.category_id}" 
                class="form-select" 
                id="select_category"
                @change="clearFieldError('category_id')"
                @blur="validateField('category_id')"
              >
                <option value=""></option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <label for="select_category" class="form-label">Category *</label>
            </div>
            <span v-if="fieldErrors.category_id" class="field-error-text">
              ⚠️ {{ fieldErrors.category_id }}
            </span>
          </div>

          <!-- Slug Field -->
          <div class="field-wrapper" style="margin-bottom: 1.5rem;">
            <div class="floating-label-group" :class="{'has-field-error': !!fieldErrors.slug}">
              <input 
                type="text" 
                v-model="form.slug" 
                :class="{'has-value': !!form.slug, 'form-input--error': !!fieldErrors.slug}" 
                class="form-input" 
                placeholder=" " 
                id="input_slug" 
                @input="clearFieldError('slug')"
              />
              <label for="input_slug" class="form-label">URL Slug (e.g., readymade-stretchable-saree-blouse)</label>
            </div>
            <span v-if="fieldErrors.slug" class="field-error-text">
              ⚠️ {{ fieldErrors.slug }}
            </span>
          </div>

          <!-- Short Description -->
          <div class="field-wrapper" style="margin-bottom: 1.5rem;">
            <div class="floating-label-group">
              <input 
                type="text" 
                v-model="form.short_description" 
                :class="{'has-value': !!form.short_description}" 
                class="form-input" 
                placeholder=" " 
                id="input_short_description" 
              />
              <label for="input_short_description" class="form-label">Short Summary (e.g., Premium 4-way stretchable cotton lycra readymade blouse with elbow sleeves)</label>
            </div>
          </div>

          <!-- Description -->
          <div class="field-wrapper" style="margin-bottom: 1.5rem;">
            <div class="floating-label-group">
              <textarea 
                v-model="form.description" 
                :class="{'has-value': !!form.description}" 
                class="form-textarea" 
                rows="5" 
                placeholder=" " 
                id="textarea_description"
              ></textarea>
              <label for="textarea_description" class="form-label">Detailed Description & Weave Specifications (HTML Supported)</label>
            </div>
          </div>

          <div class="grid-2">
            <div class="floating-label-group">
              <input type="text" v-model="form.material" :class="{'has-value': !!form.material}" class="form-input" placeholder=" " id="input_material" />
              <label for="input_material" class="form-label">Material / Fabric (e.g., Cotton Lycra 4-Way Stretch, Brocade Silk Stretch)</label>
            </div>
            <div class="floating-label-group">
              <input type="text" v-model="form.care_instructions" :class="{'has-value': !!form.care_instructions}" class="form-input" placeholder=" " id="input_care_instructions" />
              <label for="input_care_instructions" class="form-label">Care Instructions (e.g., Gentle Hand Wash in Cold Water, Do Not Bleach)</label>
            </div>
          </div>

          <!-- Tags -->
          <div class="form-group" style="margin-top: 0.5rem;">
            <label class="form-label" style="margin-bottom: 0.5rem; font-weight: 600;">Occasion & Festival Tags</label>
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; background: rgba(0,0,0,0.02); padding: 1.25rem; border-radius: 8px; border: 1px solid var(--color-border);">
              <label v-for="tag in tags" :key="tag.id" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--color-text-secondary); font-size: 0.9rem;">
                <input type="checkbox" :value="tag.id" v-model="form.tag_ids" style="cursor: pointer; width: 16px; height: 16px;" />
                {{ tag.name }}
              </label>
              <span v-if="tags.length === 0" style="color: var(--color-text-muted); font-size: 0.9rem;">No tags available.</span>
            </div>
          </div>

          <!-- Step 1 Navigation Footer -->
          <div class="section-nav-footer">
            <router-link to="/admin/products" class="btn btn--secondary btn-nav-action">
              Cancel
            </router-link>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
              <button type="button" class="btn btn--secondary btn-nav-action" @click="saveAsDraft" :disabled="submitting">
                💾 Save as Draft
              </button>
              <button type="button" class="btn btn--primary btn-nav-action" @click="validateAndNext('basic')">
                Next: Pricing & Tax ➔
              </button>
            </div>
          </div>
        </div>

        <!-- STEP 2: Pricing & Tax -->
        <div v-show="activeTab === 'pricing'">
          <div class="step-header">
            <div>
              <h2 class="step-heading">🏷️ Step 2: Pricing, Tax & Logistics</h2>
              <p class="step-desc">Define retail pricing, tax rates, GST classification, shipping weight, and storefront promotion badges.</p>
            </div>
            <span class="step-indicator-pill">Step 2 of 6</span>
          </div>

          <!-- Pricing Calculations Banner -->
          <div v-if="form.mrp > 0 && form.selling_price > 0" class="pricing-calc-card">
            <div class="pricing-calc-item">
              <span class="calc-label">Maximum Retail Price (MRP)</span>
              <span class="calc-val">₹{{ parseFloat(form.mrp).toFixed(2) }}</span>
            </div>
            <div class="pricing-calc-item">
              <span class="calc-label">Selling Price</span>
              <span class="calc-val" style="color: var(--color-primary);">₹{{ parseFloat(form.selling_price).toFixed(2) }}</span>
            </div>
            <div class="pricing-calc-item">
              <span class="calc-label">Customer Discount</span>
              <span class="calc-val" style="color: #059669;">
                {{ calculatedDiscountPercent }}% OFF (Saved ₹{{ (form.mrp - form.selling_price).toFixed(2) }})
              </span>
            </div>
            <div v-if="form.cost_price > 0" class="pricing-calc-item">
              <span class="calc-label">Gross Margin</span>
              <span class="calc-val" style="color: var(--accent-gold-dark);">
                ₹{{ (form.selling_price - form.cost_price).toFixed(2) }} ({{ (((form.selling_price - form.cost_price) / form.selling_price) * 100).toFixed(1) }}%)
              </span>
            </div>
          </div>
          
          <div class="grid-3">
            <!-- Base MRP -->
            <div class="field-wrapper" style="margin-bottom: 1.5rem;">
              <div class="floating-label-group" :class="{'has-field-error': !!fieldErrors.mrp}">
                <input 
                  type="number" 
                  step="0.01" 
                  min="0" 
                  v-model.number="form.mrp" 
                  :class="{'has-value': form.mrp !== undefined && form.mrp !== '', 'form-input--error': !!fieldErrors.mrp}" 
                  class="form-input" 
                  placeholder=" " 
                  id="input_mrp" 
                  @input="clearFieldError('mrp')"
                  @blur="validateField('mrp')"
                />
                <label for="input_mrp" class="form-label">Base MRP (₹) *</label>
              </div>
              <span v-if="fieldErrors.mrp" class="field-error-text">
                ⚠️ {{ fieldErrors.mrp }}
              </span>
            </div>

            <!-- Base Selling Price -->
            <div class="field-wrapper" style="margin-bottom: 1.5rem;">
              <div class="floating-label-group" :class="{'has-field-error': !!fieldErrors.selling_price}">
                <input 
                  type="number" 
                  step="0.01" 
                  min="0" 
                  v-model.number="form.selling_price" 
                  :class="{'has-value': form.selling_price !== undefined && form.selling_price !== '', 'form-input--error': !!fieldErrors.selling_price}" 
                  class="form-input" 
                  placeholder=" " 
                  id="input_selling_price" 
                  @input="clearFieldError('selling_price')"
                  @blur="validateField('selling_price')"
                />
                <label for="input_selling_price" class="form-label">Base Selling Price (₹) *</label>
              </div>
              <span v-if="fieldErrors.selling_price" class="field-error-text">
                ⚠️ {{ fieldErrors.selling_price }}
              </span>
            </div>

            <!-- Cost Price -->
            <div class="field-wrapper" style="margin-bottom: 1.5rem;">
              <div class="floating-label-group">
                <input 
                  type="number" 
                  step="0.01" 
                  min="0" 
                  v-model.number="form.cost_price" 
                  :class="{'has-value': form.cost_price !== undefined && form.cost_price !== '' && form.cost_price !== null}" 
                  class="form-input" 
                  placeholder=" " 
                  id="input_cost_price" 
                />
                <label for="input_cost_price" class="form-label">Cost Price (₹, admin-only)</label>
              </div>
            </div>
          </div>

          <div class="grid-3">
            <div class="floating-label-group">
              <select v-model="form.tax_category" @change="handleTaxCategoryChange" :class="{'has-value': !!form.tax_category}" class="form-select" id="select_tax_category">
                <option value="standard">Standard GST</option>
                <option value="reduced">Reduced Rate (5%)</option>
                <option value="exempt">Exempt / Nil Rated (0%)</option>
              </select>
              <label for="select_tax_category" class="form-label">Tax Classification</label>
            </div>
            
            <!-- Standard GST Percentage Selector -->
            <div class="field-wrapper">
              <div class="floating-label-group" :class="{'has-field-error': !!fieldErrors.gst_rate}">
                <select 
                  v-model.number="form.gst_rate" 
                  :class="{'has-value': form.gst_rate !== undefined && form.gst_rate !== '' && form.gst_rate !== null, 'form-select--error': !!fieldErrors.gst_rate}" 
                  class="form-select" 
                  id="select_gst_rate" 
                  @change="clearFieldError('gst_rate')"
                >
                  <option v-for="slab in standardGstSlabs" :key="slab.value" :value="slab.value">
                    {{ slab.label }}
                  </option>
                </select>
                <label for="select_gst_rate" class="form-label">Standard GST Rate (%) *</label>
              </div>
              <span v-if="fieldErrors.gst_rate" class="field-error-text">
                ⚠️ {{ fieldErrors.gst_rate }}
              </span>
            </div>

            <div class="floating-label-group">
              <input type="text" v-model="form.hsn_code" :class="{'has-value': !!form.hsn_code}" class="form-input" placeholder=" " id="input_hsn_code" />
              <label for="input_hsn_code" class="form-label">HSN Code (e.g. 6211, 6109)</label>
            </div>
          </div>

          <div class="grid-2">
            <div class="floating-label-group">
              <input type="number" step="0.01" min="0" v-model.number="form.weight" :class="{'has-value': form.weight !== undefined && form.weight !== '' && form.weight !== null}" class="form-input" placeholder=" " id="input_weight" />
              <label for="input_weight" class="form-label">Weight (Grams)</label>
            </div>
            <div class="floating-label-group">
              <input type="number" min="0" v-model.number="form.return_window_days" :class="{'has-value': form.return_window_days !== undefined && form.return_window_days !== '' && form.return_window_days !== null}" class="form-input" placeholder=" " id="input_return_window_days" />
              <label for="input_return_window_days" class="form-label">Return Window (Days)</label>
            </div>
          </div>

          <!-- Storefront Badges & Visibility -->
          <div style="background: rgba(0,0,0,0.02); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--color-border); margin-top: 1.5rem;">
            <div style="font-size: 0.9rem; font-weight: 600; color: var(--color-primary); margin-bottom: 1rem;">
              Product Visibility & Badging
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="form.is_active" style="width: 18px; height: 18px; cursor: pointer;" />
                <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 600;">Publish to Storefront (Active)</span>
              </label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="form.is_featured" style="width: 18px; height: 18px; cursor: pointer;" />
                <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Featured Collection</span>
              </label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="form.is_new_arrival" style="width: 18px; height: 18px; cursor: pointer;" />
                <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">New Arrival</span>
              </label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="form.is_bestseller" style="width: 18px; height: 18px; cursor: pointer;" />
                <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Bestseller</span>
              </label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="form.is_returnable" style="width: 18px; height: 18px; cursor: pointer;" />
                <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Easy Returns</span>
              </label>
              <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" v-model="form.reviews_enabled" style="width: 18px; height: 18px; cursor: pointer;" />
                <span class="form-label" style="margin-bottom: 0; color: var(--color-text-primary); font-weight: 500;">Enable Customer Reviews</span>
              </label>
            </div>
          </div>

          <!-- Step 2 Navigation Footer -->
          <div class="section-nav-footer">
            <button type="button" class="btn btn--secondary btn-nav-action" @click="goToStep('basic')">
              ⬅ Previous: Basic Info
            </button>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
              <button type="button" class="btn btn--secondary btn-nav-action" @click="saveAsDraft" :disabled="submitting">
                💾 Save as Draft
              </button>
              <button type="button" class="btn btn--primary btn-nav-action" @click="validateAndNext('pricing')">
                Next: Variants & Stock ➔
              </button>
            </div>
          </div>
        </div>

        <!-- STEP 3: Variants & SKUs -->
        <div v-show="activeTab === 'variants'">
          <div class="step-header">
            <div>
              <h2 class="step-heading">🧵 Step 3: Variants & Inventory Stock</h2>
              <p class="step-desc">Quickly select colors and sizes with 1-click presets, or configure your SKU inventory in the table below.</p>
            </div>
            <span class="step-indicator-pill">Step 3 of 6</span>
          </div>

          <!-- Variants Error Alert -->
          <div v-if="fieldErrors.variants" class="badge badge--danger" style="margin-bottom: 1.5rem; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.9rem;">
            ⚠️ {{ fieldErrors.variants }}
          </div>

          <!-- Fast 1-Click Preset Variant Builder -->
          <div style="background: var(--blush-bg); border: 1px solid var(--color-border); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
            <div style="font-size: 1rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
              <span>⚡ Fast 1-Click Variant Creator</span>
              <span v-if="selectedColors.length > 0 && selectedSizes.length > 0" style="font-size: 0.82rem; background: var(--color-primary); color: #ffffff; padding: 3px 10px; border-radius: 12px; font-weight: 600;">
                {{ selectedColors.length }} Colors × {{ selectedSizes.length }} Sizes = {{ selectedColors.length * selectedSizes.length }} SKUs
              </span>
            </div>

            <!-- 1. Color Master Swatches -->
            <div style="margin-bottom: 1.25rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-text-secondary); letter-spacing: 0.5px;">
                  1. Select Colors from Color Master (Tap to toggle)
                </div>
                <button type="button" class="btn btn--sm btn--secondary" @click="openQuickColorModal" style="border-radius: 12px; height: 26px; padding: 0 8px; font-size: 0.75rem;">
                  🎨 + Add New Color to Master
                </button>
              </div>
              <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                <button 
                  v-for="color in activeColors" 
                  :key="color.id || color.name"
                  type="button"
                  :class="['color-chip', { 'color-chip--active': isColorSelected(color.name) }]"
                  @click="toggleColor(color)"
                >
                  <span class="color-chip__dot" :style="{ background: color.code }"></span>
                  <span>{{ color.name }}</span>
                  <span v-if="isColorSelected(color.name)" class="color-chip__check">✓</span>
                </button>
              </div>
            </div>

            <!-- 2. Size Group Master & Size Presets -->
            <div style="margin-bottom: 1.5rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-text-secondary); letter-spacing: 0.5px;">
                  2. Select Sizes (Choose Size Group)
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                  <!-- Size Group Selector Tabs -->
                  <div v-if="activeSizeGroups && activeSizeGroups.length > 1" style="display: inline-flex; gap: 0.25rem; background: #ffffff; padding: 2px; border-radius: 14px; border: 1px solid var(--color-border);">
                    <button 
                      v-for="grp in activeSizeGroups" 
                      :key="grp.id"
                      type="button"
                      :class="['btn btn--sm', selectedSizeGroupId === grp.id ? 'btn--primary' : 'btn--secondary']"
                      @click="selectedSizeGroupId = grp.id"
                      style="border-radius: 12px; height: 24px; padding: 0 8px; font-size: 0.72rem; border: none;"
                    >
                      {{ grp.name }}
                    </button>
                  </div>
                  <button type="button" class="btn btn--sm btn--secondary" @click="openQuickSizeModal" style="border-radius: 12px; height: 26px; padding: 0 8px; font-size: 0.75rem;">
                    📐 + Add Size to Master
                  </button>
                </div>
              </div>

              <!-- Dynamic Sizes Chips for Selected Group -->
              <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                <button 
                  v-for="size in currentSizeGroupSizes" 
                  :key="size.id || size.name"
                  type="button"
                  :class="['size-chip', { 'size-chip--active': isSizeSelected(size.name) }]"
                  @click="toggleSize(size.name)"
                  :title="size.measurement_hint || ''"
                >
                  <span>{{ size.name }}</span>
                  <span v-if="size.measurement_hint" style="font-size: 0.7rem; opacity: 0.75;">({{ size.measurement_hint }})</span>
                  <span v-if="isSizeSelected(size.name)" class="size-chip__check">✓</span>
                </button>
              </div>
            </div>

            <!-- 3. Generator Controls Bar -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-top: 1px dashed var(--color-border); padding-top: 1.25rem;">
              <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <label style="font-size: 0.82rem; font-weight: 600; color: var(--color-text-secondary); margin: 0;">Initial Stock per SKU:</label>
                  <input type="number" v-model.number="defaultStockAmount" min="0" class="form-input" style="width: 75px; height: 36px; padding: 0.25rem 0.5rem; text-align: center;" />
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <label style="font-size: 0.82rem; font-weight: 600; color: var(--color-text-secondary); margin: 0;">SKU Prefix:</label>
                  <input type="text" v-model="matrixSkuPrefix" class="form-input" style="width: 120px; height: 36px; padding: 0.25rem 0.5rem; text-transform: uppercase;" />
                </div>
              </div>

              <div style="display: flex; gap: 0.75rem; align-items: center;">
                <button 
                  v-if="selectedColors.length > 0 || selectedSizes.length > 0"
                  type="button" 
                  class="btn btn--secondary btn--sm" 
                  @click="clearSelectedChips" 
                  style="height: 40px; border-radius: 20px; padding: 0 1rem;"
                >
                  Clear Selection
                </button>
                <button 
                  type="button" 
                  class="btn btn--primary" 
                  @click="generateSimpleVariants"
                  :disabled="selectedColors.length === 0 && selectedSizes.length === 0"
                  style="height: 40px; border-radius: 20px; padding: 0 1.5rem; font-weight: 600; box-shadow: var(--shadow-sm);"
                >
                  ⚡ Generate {{ selectedColors.length * selectedSizes.length > 0 ? (selectedColors.length * selectedSizes.length) + ' Variants' : 'Variants' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Variants Spreadsheet Table Section -->
          <div style="margin-bottom: 2rem;">
            <!-- Table Header Toolbar -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
              <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <h3 style="font-size: 1.15rem; margin: 0; color: var(--color-primary);">Configured Variants</h3>
                <span class="badge badge--secondary" style="font-size: 0.8rem;">{{ form.variants.length }} SKU(s)</span>
                <span class="badge badge--success" style="font-size: 0.8rem;">Total Stock: {{ totalVariantStock }} units</span>
              </div>

              <!-- Fast Bulk Stock & Price Tool -->
              <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; background: #ffffff; border: 1px solid var(--color-border); border-radius: 20px; padding: 2px 4px;">
                  <input type="number" v-model.number="bulkStockValue" min="0" placeholder="Stock" style="width: 60px; border: none; outline: none; font-size: 0.8rem; text-align: center; padding: 4px;" />
                  <button type="button" class="btn btn--sm btn--secondary" @click="applyBulkStock" style="border-radius: 16px; height: 28px; padding: 0 8px; font-size: 0.75rem;">
                    Apply Stock
                  </button>
                </div>
                <button type="button" class="btn btn--secondary btn--sm" @click="addVariantRow" style="height: 34px; border-radius: 17px; padding: 0 1rem;">
                  ➕ Add Single Row
                </button>
                <button v-if="form.variants.length > 0" type="button" class="btn btn--danger btn--sm" @click="clearAllVariants" style="height: 34px; border-radius: 17px; padding: 0 0.85rem;" title="Remove all variants">
                  🗑️ Clear All
                </button>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="form.variants.length === 0" style="text-align: center; padding: 3rem; color: var(--color-text-muted); background: rgba(0,0,0,0.01); border-radius: 10px; border: 1px dashed var(--color-border);">
              <div style="font-size: 2.25rem; margin-bottom: 0.5rem;">🧵</div>
              <div style="font-weight: 600; color: var(--color-text-primary); font-size: 1rem;">No variants configured yet</div>
              <div style="font-size: 0.85rem; margin-top: 0.25rem;">Tap any Color & Size chips above and click "Generate Variants", or click "Add Single Row".</div>
            </div>

            <!-- Compact Spreadsheet Table -->
            <div v-else class="table-container" style="background: #ffffff; border: 1px solid var(--color-border); border-radius: 10px; overflow-x: auto; box-shadow: var(--shadow-sm);">
              <table class="table" style="margin-bottom: 0; min-width: 720px;">
                <thead>
                  <tr style="background: #f8fafc;">
                    <th style="width: 40px; text-align: center;">#</th>
                    <th style="width: 170px;">Color</th>
                    <th style="width: 120px;">Size</th>
                    <th>SKU Code *</th>
                    <th style="width: 110px;">Stock *</th>
                    <th style="width: 130px;">Price (₹)</th>
                    <th style="width: 80px; text-align: center;">Active</th>
                    <th style="width: 50px; text-align: center;"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(v, index) in form.variants" :key="index" :style="{ background: index % 2 === 1 ? 'rgba(0,0,0,0.01)' : '#ffffff' }">
                    <!-- Row index -->
                    <td style="text-align: center; font-size: 0.8rem; color: var(--color-text-muted); font-weight: 500;">
                      {{ index + 1 }}
                    </td>

                    <!-- Color Column -->
                    <td>
                      <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <input type="color" v-model="v.color_code" style="width: 24px; height: 24px; border: none; border-radius: 50%; cursor: pointer; padding: 0;" />
                        <input 
                          type="text" 
                          v-model="v.color" 
                          placeholder="e.g. Maroon" 
                          class="form-input" 
                          style="height: 34px; font-size: 0.82rem; padding: 0.25rem 0.5rem;" 
                        />
                      </div>
                    </td>

                    <!-- Size Column -->
                    <td>
                      <input 
                        type="text" 
                        v-model="v.size" 
                        placeholder="e.g. 34-37" 
                        class="form-input" 
                        style="height: 34px; font-size: 0.82rem; padding: 0.25rem 0.5rem; text-align: center; font-weight: 500;" 
                      />
                    </td>

                    <!-- SKU Column -->
                    <td>
                      <div style="display: flex; gap: 0.35rem; align-items: center;">
                        <input 
                          type="text" 
                          v-model="v.sku" 
                          :class="{'form-input--error': !!fieldErrors[`variants.${index}.sku`]}" 
                          class="form-input" 
                          style="height: 34px; font-size: 0.82rem; padding: 0.25rem 0.5rem; font-family: monospace;" 
                          placeholder="SKU Code"
                          @input="clearFieldError(`variants.${index}.sku`)" 
                        />
                        <button type="button" class="btn btn--secondary btn--sm" @click="generateSKURow(index)" title="Auto-generate SKU" style="height: 34px; padding: 0 0.5rem; font-size: 0.75rem; border-radius: 4px;">
                          ⚙️
                        </button>
                      </div>
                      <span v-if="fieldErrors[`variants.${index}.sku`]" style="font-size: 0.72rem; color: #dc2626; display: block; margin-top: 2px;">
                        {{ fieldErrors[`variants.${index}.sku`] }}
                      </span>
                    </td>

                    <!-- Stock Column -->
                    <td>
                      <input 
                        type="number" 
                        min="0" 
                        v-model.number="v.stock_quantity" 
                        :class="{'form-input--error': !!fieldErrors[`variants.${index}.stock_quantity`]}" 
                        class="form-input" 
                        style="height: 34px; font-size: 0.82rem; padding: 0.25rem 0.5rem; text-align: center; font-weight: 600;" 
                        @input="clearFieldError(`variants.${index}.stock_quantity`)" 
                      />
                    </td>

                    <!-- Price Column -->
                    <td>
                      <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        v-model.number="v.selling_price" 
                        class="form-input" 
                        :placeholder="form.selling_price ? '₹' + form.selling_price : 'Default'" 
                        style="height: 34px; font-size: 0.82rem; padding: 0.25rem 0.5rem;" 
                      />
                    </td>

                    <!-- Active Column -->
                    <td style="text-align: center;">
                      <input type="checkbox" v-model="v.is_active" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--color-primary);" />
                    </td>

                    <!-- Delete Column -->
                    <td style="text-align: center;">
                      <button type="button" @click="removeVariantRow(index)" class="btn btn--danger btn--sm" style="padding: 4px 8px; border-radius: 4px; height: 30px;" title="Remove this SKU">
                        ✕
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Step 3 Navigation Footer -->
          <div class="section-nav-footer">
            <button type="button" class="btn btn--secondary btn-nav-action" @click="goToStep('pricing')">
              ⬅ Previous: Pricing & Tax
            </button>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
              <button type="button" class="btn btn--secondary btn-nav-action" @click="saveAsDraft" :disabled="submitting">
                💾 Save as Draft
              </button>
              <button type="button" class="btn btn--primary btn-nav-action" @click="validateAndNext('variants')">
                Next: Media & Photos ➔
              </button>
            </div>
          </div>
        </div>

        <!-- STEP 4: Images & Media -->
        <div v-show="activeTab === 'images'">
          <div class="step-header">
            <div>
              <h2 class="step-heading">🖼️ Step 4: Product Photography & Media</h2>
              <p class="step-desc">Upload high-resolution garment imagery, set the primary hero cover photo, and organize color-specific variant galleries.</p>
            </div>
            <span class="step-indicator-pill">Step 4 of 6</span>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
              <h3 style="font-size: 1.1rem; color: var(--color-primary); margin: 0;">🖼️ Primary Hero & General Catalog Images</h3>
              <span class="badge badge--secondary" style="font-size: 0.8rem;">{{ form.images.length }} image(s) total</span>
            </div>
            
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
                <span style="font-weight: 600; font-family: var(--font-family-base);">Drag & Drop photos here or click to browse</span>
                <span style="font-size: 0.85rem; color: var(--color-text-muted);">Supports JPG, PNG, WEBP, AVIF (Max 10MB per file)</span>
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
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; border: 1px solid var(--color-border); background: var(--color-primary);"></span> {{ color }} Dedicated Gallery
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
                  <span style="font-size: 0.9rem; font-weight: 500;">Drop or click to add photos for <strong>{{ color }}</strong> variants</span>
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

          <!-- Step 4 Navigation Footer -->
          <div class="section-nav-footer">
            <button type="button" class="btn btn--secondary btn-nav-action" @click="goToStep('variants')">
              ⬅ Previous: Variants & Stock
            </button>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
              <button type="button" class="btn btn--secondary btn-nav-action" @click="saveAsDraft" :disabled="submitting">
                💾 Save as Draft
              </button>
              <button type="button" class="btn btn--primary btn-nav-action" @click="validateAndNext('images')">
                Next: SEO Spec ➔
              </button>
            </div>
          </div>
        </div>

        <!-- STEP 5: SEO & Search -->
        <div v-show="activeTab === 'seo'">
          <div class="step-header">
            <div>
              <h2 class="step-heading">🔍 Step 5: SEO & Search Discovery</h2>
              <p class="step-desc">Optimize meta titles, descriptions, and keywords to rank on Google and social search engines.</p>
            </div>
            <span class="step-indicator-pill">Step 5 of 6</span>
          </div>

          <!-- SERP Google Preview Card -->
          <div style="background: #ffffff; border: 1px solid var(--color-border); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-muted); margin-bottom: 0.75rem; text-transform: uppercase;">
              Google Search Engine Result Preview
            </div>
            <div style="font-size: 0.85rem; color: #202124; margin-bottom: 4px; display: flex; align-items: center; gap: 0.5rem;">
              <span style="background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem;">mayasree.com</span>
              <span style="color: #5f6368; font-size: 0.8rem;">› products › {{ form.slug || 'readymade-stretchable-saree-blouse' }}</span>
            </div>
            <div style="color: #1a0dab; font-size: 1.15rem; font-weight: 500; text-decoration: underline; margin-bottom: 4px; cursor: pointer;">
              {{ form.meta_title || form.name || 'Readymade Stretchable Saree Blouse | Maya Sree South Indian Fashion' }}
            </div>
            <div style="color: #4d5156; font-size: 0.85rem; line-height: 1.4;">
              {{ form.meta_description || form.short_description || 'Shop premium 4-way cotton lycra readymade stretchable saree blouses with comfortable fit and rich Indian festive styling at Maya Sree.' }}
            </div>
          </div>
          
          <div class="floating-label-group">
            <input type="text" v-model="form.meta_title" :class="{'has-value': !!form.meta_title}" class="form-input" placeholder=" " id="input_meta_title" />
            <label for="input_meta_title" class="form-label">Meta Title (Recommended 50–60 characters)</label>
          </div>

          <div class="floating-label-group">
            <textarea v-model="form.meta_description" :class="{'has-value': !!form.meta_description}" class="form-textarea" rows="4" placeholder=" " id="textarea_meta_description"></textarea>
            <label for="textarea_meta_description" class="form-label">Meta Description (Recommended 140–160 characters)</label>
          </div>

          <div class="floating-label-group">
            <input type="text" v-model="form.meta_keywords" :class="{'has-value': !!form.meta_keywords}" class="form-input" placeholder=" " id="input_meta_keywords" />
            <label for="input_meta_keywords" class="form-label">Meta Keywords (Comma separated, e.g., readymade stretchable blouse, stretch blouse 34-37, saree blouse online)</label>
          </div>

          <!-- Step 5 Navigation Footer -->
          <div class="section-nav-footer">
            <button type="button" class="btn btn--secondary btn-nav-action" @click="goToStep('images')">
              ⬅ Previous: Media Gallery
            </button>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
              <button type="button" class="btn btn--secondary btn-nav-action" @click="saveAsDraft" :disabled="submitting">
                💾 Save as Draft
              </button>
              <button type="button" class="btn btn--primary btn-nav-action" @click="validateAndNext('seo')">
                Next: Review & Verify ➔
              </button>
            </div>
          </div>
        </div>

        <!-- STEP 6: Review & Quality Verification (The Pre-Listing Quality Hub) -->
        <div v-show="activeTab === 'verify'">
          <div class="step-header">
            <div>
              <h2 class="step-heading">✨ Step 6: Pre-Listing Review & Verification</h2>
              <p class="step-desc">Audit all garment specifications, inventory stocks, and media assets before publishing to the live catalog.</p>
            </div>
            <span class="step-indicator-pill" style="background: var(--color-primary); color: #ffffff;">Final Step</span>
          </div>

          <!-- Product Readiness Score Banner -->
          <div class="readiness-banner" :class="readinessScore === 100 ? 'readiness-banner--ready' : 'readiness-banner--warning'">
            <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
              <div class="readiness-circle">
                <span style="font-size: 1.5rem; font-weight: bold;">{{ readinessScore }}%</span>
                <span style="font-size: 0.65rem; text-transform: uppercase;">Ready</span>
              </div>
              <div>
                <h3 style="font-size: 1.2rem; margin: 0; color: inherit; font-family: var(--font-family-heading);">
                  {{ readinessScore === 100 ? '🎉 Product is Fully Verified & Ready for Listing!' : '⚠️ Pre-Listing Quality Verification Checklist' }}
                </h3>
                <p style="font-size: 0.85rem; margin: 0.25rem 0 0 0; opacity: 0.9;">
                  {{ readinessScore === 100 ? 'All essential details, variant SKUs, pricing margins, and imagery have passed validation.' : 'Review the checklist below. You can save as Draft at any time, or resolve the pending items to publish live.' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Verification Audit Grid -->
          <div class="verification-grid" style="margin-bottom: 2.5rem;">
            
            <!-- Audit Item 1: Basic Info -->
            <div class="verify-card" :class="stepStatus.basic.valid ? 'verify-card--pass' : 'verify-card--fail'">
              <div class="verify-card__header">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-size: 1.2rem;">📝</span>
                  <span style="font-weight: 600;">Basic Details</span>
                </div>
                <span :class="['badge', stepStatus.basic.valid ? 'badge--success' : 'badge--danger']">
                  {{ stepStatus.basic.valid ? 'Passed' : 'Action Needed' }}
                </span>
              </div>
              <div class="verify-card__body">
                <div class="verify-detail-row">
                  <span class="v-label">Title:</span>
                  <span class="v-val">{{ form.name || '❌ Not entered' }}</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">Category:</span>
                  <span class="v-val">{{ getCategoryName(form.category_id) || '❌ Not selected' }}</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">Fabric & Care:</span>
                  <span class="v-val">{{ form.material ? `${form.material}` : '⚠️ Not specified' }}</span>
                </div>
              </div>
              <button type="button" class="verify-card__jump-btn" @click="goToStep('basic')">
                ✏️ Edit Basic Info
              </button>
            </div>

            <!-- Audit Item 2: Pricing & Margins -->
            <div class="verify-card" :class="stepStatus.pricing.valid ? 'verify-card--pass' : 'verify-card--fail'">
              <div class="verify-card__header">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-size: 1.2rem;">🏷️</span>
                  <span style="font-weight: 600;">Pricing & GST</span>
                </div>
                <span :class="['badge', stepStatus.pricing.valid ? 'badge--success' : 'badge--danger']">
                  {{ stepStatus.pricing.valid ? 'Passed' : 'Action Needed' }}
                </span>
              </div>
              <div class="verify-card__body">
                <div class="verify-detail-row">
                  <span class="v-label">Selling Price:</span>
                  <span class="v-val" style="font-weight: bold; color: var(--color-primary);">₹{{ parseFloat(form.selling_price || 0).toFixed(2) }}</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">Base MRP:</span>
                  <span class="v-val">₹{{ parseFloat(form.mrp || 0).toFixed(2) }}</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">GST Rate:</span>
                  <span class="v-val">{{ form.gst_rate }}% (HSN: {{ form.hsn_code || 'N/A' }})</span>
                </div>
              </div>
              <button type="button" class="verify-card__jump-btn" @click="goToStep('pricing')">
                ✏️ Edit Pricing
              </button>
            </div>

            <!-- Audit Item 3: Variants & Stock -->
            <div class="verify-card" :class="stepStatus.variants.valid ? 'verify-card--pass' : 'verify-card--fail'">
              <div class="verify-card__header">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-size: 1.2rem;">🧵</span>
                  <span style="font-weight: 600;">Variants & SKUs</span>
                </div>
                <span :class="['badge', stepStatus.variants.valid ? 'badge--success' : 'badge--danger']">
                  {{ stepStatus.variants.valid ? 'Passed' : 'Action Needed' }}
                </span>
              </div>
              <div class="verify-card__body">
                <div class="verify-detail-row">
                  <span class="v-label">Total SKUs:</span>
                  <span class="v-val">{{ form.variants.length }} configured</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">Total Inventory:</span>
                  <span class="v-val" style="font-weight: bold;">{{ totalVariantStock }} units in stock</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">Color Swatches:</span>
                  <span class="v-val">{{ uniqueColors.join(', ') || 'Standard' }}</span>
                </div>
              </div>
              <button type="button" class="verify-card__jump-btn" @click="goToStep('variants')">
                ✏️ Edit Variants
              </button>
            </div>

            <!-- Audit Item 4: Photography & Media -->
            <div class="verify-card" :class="stepStatus.images.valid ? 'verify-card--pass' : 'verify-card--warning'">
              <div class="verify-card__header">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-size: 1.2rem;">🖼️</span>
                  <span style="font-weight: 600;">Media & Cover</span>
                </div>
                <span :class="['badge', stepStatus.images.valid ? 'badge--success' : 'badge--warning']">
                  {{ stepStatus.images.valid ? 'Passed' : 'Optional / Rec' }}
                </span>
              </div>
              <div class="verify-card__body">
                <div class="verify-detail-row">
                  <span class="v-label">Total Photos:</span>
                  <span class="v-val">{{ form.images.length }} uploaded</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">Hero Cover:</span>
                  <span class="v-val">{{ primaryImage ? '✅ Designated' : '⚠️ Missing Primary Hero' }}</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">Color Galleries:</span>
                  <span class="v-val">{{ uniqueColors.length }} colors mapped</span>
                </div>
              </div>
              <button type="button" class="verify-card__jump-btn" @click="goToStep('images')">
                ✏️ Edit Media
              </button>
            </div>

            <!-- Audit Item 5: SEO Spec -->
            <div class="verify-card" :class="stepStatus.seo.valid ? 'verify-card--pass' : 'verify-card--warning'">
              <div class="verify-card__header">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <span style="font-size: 1.2rem;">🔍</span>
                  <span style="font-weight: 600;">SEO Discovery</span>
                </div>
                <span :class="['badge', stepStatus.seo.valid ? 'badge--success' : 'badge--secondary']">
                  {{ stepStatus.seo.valid ? 'Optimized' : 'Basic' }}
                </span>
              </div>
              <div class="verify-card__body">
                <div class="verify-detail-row">
                  <span class="v-label">Meta Title:</span>
                  <span class="v-val">{{ form.meta_title ? '✅ Set' : 'Auto from Name' }}</span>
                </div>
                <div class="verify-detail-row">
                  <span class="v-label">Meta Description:</span>
                  <span class="v-val">{{ form.meta_description ? '✅ Set' : 'Auto from Summary' }}</span>
                </div>
              </div>
              <button type="button" class="verify-card__jump-btn" @click="goToStep('seo')">
                ✏️ Edit SEO
              </button>
            </div>

          </div>

          <!-- Storefront Live Preview Card -->
          <div style="background: var(--blush-bg); border: 1px solid var(--color-border); border-radius: 12px; padding: 1.75rem; margin-bottom: 2.5rem;">
            <h3 style="font-size: 1.1rem; color: var(--color-primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
              🛍️ Storefront Product Card Preview
            </h3>
            
            <div style="max-width: 320px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid var(--color-border); box-shadow: var(--shadow-md);">
              <div style="aspect-ratio: 3/4; position: relative; background: #f5f5f5; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <img v-if="primaryImage" :src="primaryImage.url" style="width: 100%; height: 100%; object-fit: cover;" alt="Preview" />
                <span v-else style="font-size: 3rem; color: var(--color-text-muted);">🛍️</span>
                
                <span v-if="calculatedDiscountPercent > 0" class="badge badge--success" style="position: absolute; top: 10px; left: 10px; font-weight: bold;">
                  {{ calculatedDiscountPercent }}% OFF
                </span>
                <span v-if="form.is_new_arrival" class="badge" style="position: absolute; top: 10px; right: 10px; background: var(--color-secondary); color: #ffffff;">
                  NEW
                </span>
              </div>
              
              <div style="padding: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-muted); font-weight: 600; letter-spacing: 0.5px;">
                  {{ getCategoryName(form.category_id) || 'MAYA SREE COLLECTION' }}
                </div>
                <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-text-primary); margin: 0.25rem 0 0.5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  {{ form.name || 'Readymade Stretchable Saree Blouse (34-37)' }}
                </div>
                <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-bottom: 0.75rem;">
                  <span style="font-weight: 700; font-size: 1.15rem; color: var(--color-primary);">₹{{ parseFloat(form.selling_price || 0).toFixed(2) }}</span>
                  <span v-if="form.mrp > form.selling_price" style="font-size: 0.85rem; text-decoration: line-through; color: var(--color-text-muted);">
                    ₹{{ parseFloat(form.mrp || 0).toFixed(2) }}
                  </span>
                </div>

                <!-- Color Swatches Preview -->
                <div v-if="uniqueColors.length > 0" style="display: flex; gap: 4px; margin-bottom: 0.5rem;">
                  <span 
                    v-for="v in form.variants.slice(0, 5)" 
                    :key="v.sku" 
                    style="width: 14px; height: 14px; border-radius: 50%; border: 1px solid #ccc;" 
                    :style="{ background: v.color_code || '#ffffff' }"
                    :title="v.color"
                  ></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 6 Final Launchpad Navigation Footer -->
          <div class="section-nav-footer">
            <button type="button" class="btn btn--secondary btn-nav-action" @click="goToStep('seo')">
              ⬅ Previous: SEO Spec
            </button>
            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
              <button 
                type="button" 
                class="btn btn--secondary btn-nav-action" 
                @click="saveAsDraft" 
                :disabled="submitting"
              >
                💾 Save as Draft
              </button>
              <button 
                type="button" 
                class="btn btn--primary btn-nav-action" 
                @click="saveAndPublish" 
                :disabled="submitting"
                style="box-shadow: var(--shadow-lg); min-width: 220px; font-weight: 600;"
              >
                🚀 {{ submitting ? 'Saving...' : (isEdit ? 'Save Changes' : 'Publish & List Product') }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </form>

    <!-- Quick Add Color Modal -->
    <div v-if="showQuickColorModal" class="modal-overlay" @click.self="showQuickColorModal = false">
      <div class="modal-container" style="max-width: 420px;">
        <div class="modal-header">
          <h3 class="modal-title">🎨 Add New Color to Master</h3>
          <button class="modal-close" @click="showQuickColorModal = false">&times;</button>
        </div>
        <form @submit.prevent="saveQuickColor">
          <div class="modal-body">
            <div v-if="quickColorError" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.5rem; width: 100%; border-radius: 6px;">
              ⚠️ {{ quickColorError }}
            </div>
            <div class="floating-label-group" style="margin-bottom: 1.25rem;">
              <input 
                type="text" 
                v-model="quickColorName" 
                class="form-input" 
                :class="{'has-value': !!quickColorName}" 
                placeholder=" " 
                id="quick_color_name"
                required 
              />
              <label for="quick_color_name" class="form-label">Color Name * (e.g. Peacock Blue)</label>
            </div>
            <div style="margin-bottom: 1rem;">
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.35rem; display: block;">
                Color Hex Code & Swatch
              </label>
              <div style="display: flex; gap: 0.5rem; align-items: center;">
                <input 
                  type="color" 
                  v-model="quickColorCode" 
                  style="width: 42px; height: 42px; border: none; border-radius: 8px; cursor: pointer; padding: 0;" 
                />
                <input 
                  type="text" 
                  v-model="quickColorCode" 
                  class="form-input" 
                  placeholder="#D4AF37" 
                  style="font-family: monospace; font-weight: 600; text-transform: uppercase;" 
                  required 
                />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn--secondary" @click="showQuickColorModal = false">Cancel</button>
            <button type="submit" class="btn btn--primary" :disabled="quickColorSubmitting">
              {{ quickColorSubmitting ? 'Saving...' : 'Add Color & Select' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Quick Add Size Modal -->
    <div v-if="showQuickSizeModal" class="modal-overlay" @click.self="showQuickSizeModal = false">
      <div class="modal-container" style="max-width: 440px;">
        <div class="modal-header">
          <h3 class="modal-title">📐 Add New Size to Master</h3>
          <button class="modal-close" @click="showQuickSizeModal = false">&times;</button>
        </div>
        <form @submit.prevent="saveQuickSize">
          <div class="modal-body">
            <div v-if="quickSizeError" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.5rem; width: 100%; border-radius: 6px;">
              ⚠️ {{ quickSizeError }}
            </div>
            <div class="floating-label-group" style="margin-bottom: 1.25rem;">
              <select v-model="selectedSizeGroupId" class="form-select has-value" id="quick_size_group" required>
                <option v-for="grp in activeSizeGroups" :key="grp.id" :value="grp.id">
                  {{ grp.name }}
                </option>
              </select>
              <label for="quick_size_group" class="form-label">Size Group *</label>
            </div>
            <div class="floating-label-group" style="margin-bottom: 1.25rem;">
              <input 
                type="text" 
                v-model="quickSizeName" 
                class="form-input" 
                :class="{'has-value': !!quickSizeName}" 
                placeholder=" " 
                id="quick_size_name"
                required 
              />
              <label for="quick_size_name" class="form-label">Size Label * (e.g. 44-46 or 4XL)</label>
            </div>
            <div class="floating-label-group" style="margin-bottom: 1rem;">
              <input 
                type="text" 
                v-model="quickSizeHint" 
                class="form-input" 
                :class="{'has-value': !!quickSizeHint}" 
                placeholder=" " 
                id="quick_size_hint"
              />
              <label for="quick_size_hint" class="form-label">Measurement / Fit Hint (e.g. Fits Bust 44" to 46")</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn--secondary" @click="showQuickSizeModal = false">Cancel</button>
            <button type="submit" class="btn btn--primary" :disabled="quickSizeSubmitting">
              {{ quickSizeSubmitting ? 'Saving...' : 'Add Size & Select' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useProductStore } from '../../stores/product';
import { useCategoryStore } from '../../stores/category';
import { useTagStore } from '../../stores/tag';
import { useColorStore } from '../../stores/color';
import { useSizeStore } from '../../stores/size';

const route = useRoute();
const router = useRouter();

const productStore = useProductStore();
const categoryStore = useCategoryStore();
const tagStore = useTagStore();
const colorStore = useColorStore();
const sizeStore = useSizeStore();

const isEdit = ref(false);
const submitting = ref(false);
const saveMode = ref('publish'); // 'publish' or 'draft'
const errorMsg = ref(null);
const feedbackMsg = ref(null);
const validationErrors = ref([]);
const fieldErrors = ref({});
const activeTab = ref('basic');

const steps = [
  { id: 'basic', label: 'Basic Info', sublabel: 'Title, category & fabric', icon: '📝' },
  { id: 'pricing', label: 'Pricing & Tax', sublabel: 'MRP, GST & Logistics', icon: '🏷️' },
  { id: 'variants', label: 'Variants & SKUs', sublabel: 'Sizes, colors & stock', icon: '🧵' },
  { id: 'images', label: 'Media & Photos', sublabel: 'Cover hero & galleries', icon: '🖼️' },
  { id: 'seo', label: 'SEO Spec', sublabel: 'Search meta & ranking', icon: '🔍' },
  { id: 'verify', label: 'Review & Verify', sublabel: 'Quality audit & launch', icon: '✨' },
];

const currentStepIndex = computed(() => {
  return steps.findIndex(s => s.id === activeTab.value);
});

const form = ref({
  category_id: '',
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
  gst_rate: 5.00,
  hsn_code: '',
  weight: 0,
  is_active: true,
  is_featured: false,
  is_new_arrival: false,
  is_bestseller: false,
  is_returnable: true,
  reviews_enabled: true,
  return_window_days: 7,
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  tag_ids: [],
  variants: [],
  images: [],
});

const standardGstSlabs = [
  { value: 5.00, label: '5% (Apparel & Blouses ≤ ₹1,000)' },
  { value: 12.00, label: '12% (Readymade Garments > ₹1,000)' },
  { value: 18.00, label: '18% (Standard Rate / Synthetic)' },
  { value: 0.00, label: '0% (Nil Rated / Exempt)' },
  { value: 28.00, label: '28% (Luxury / Special)' },
];

function selectStandardGst(rate) {
  form.value.gst_rate = rate;
  clearFieldError('gst_rate');
  if (rate === 0) {
    form.value.tax_category = 'exempt';
  } else if (rate === 5) {
    form.value.tax_category = 'reduced';
  } else {
    form.value.tax_category = 'standard';
  }
}

function handleTaxCategoryChange() {
  if (form.value.tax_category === 'exempt') {
    form.value.gst_rate = 0.00;
  } else if (form.value.tax_category === 'reduced') {
    form.value.gst_rate = 5.00;
  } else if (form.value.tax_category === 'standard' && form.value.gst_rate === 0) {
    form.value.gst_rate = parseFloat(form.value.selling_price) > 1000 ? 12.00 : 5.00;
  }
  clearFieldError('gst_rate');
}

const presetColors = [
  { name: 'Maroon', code: '#6b1124' },
  { name: 'Mustard Gold', code: '#d4af37' },
  { name: 'Bottle Green', code: '#14532d' },
  { name: 'Black', code: '#18181b' },
  { name: 'Navy Blue', code: '#1e3a8a' },
  { name: 'Rani Pink', code: '#db2777' },
  { name: 'Kasavu Cream', code: '#fff8e7' },
  { name: 'Royal Purple', code: '#581c87' },
  { name: 'Peacock Teal', code: '#0f766e' },
  { name: 'Ruby Red', code: '#b91c1c' },
];

const presetSizes = [
  '32-34', '34-37', '38-40', '40-42', 'Free Size', 'S', 'M', 'L', 'XL', '2XL'
];

// Color & Size Masters Integration
const selectedSizeGroupId = ref(null);

const activeColors = computed(() => {
  if (colorStore.activeColors && colorStore.activeColors.length > 0) {
    return colorStore.activeColors;
  }
  return presetColors;
});

const activeSizeGroups = computed(() => sizeStore.activeSizeGroups);

const currentSizeGroupSizes = computed(() => {
  if (!activeSizeGroups.value || activeSizeGroups.value.length === 0) {
    return presetSizes.map(s => ({ name: s, measurement_hint: '' }));
  }
  const group = activeSizeGroups.value.find(g => g.id === selectedSizeGroupId.value) || activeSizeGroups.value[0];
  if (group && group.active_sizes && group.active_sizes.length > 0) {
    return group.active_sizes;
  }
  if (group && group.sizes && group.sizes.length > 0) {
    return group.sizes.filter(s => s.is_active);
  }
  return presetSizes.map(s => ({ name: s, measurement_hint: '' }));
});

const selectedColors = ref([]);
const selectedSizes = ref([]);
const defaultStockAmount = ref(10);
const matrixSkuPrefix = ref('STR-BLOUSE');
const bulkStockValue = ref(10);

// Quick Add Color Modal State
const showQuickColorModal = ref(false);
const quickColorName = ref('');
const quickColorCode = ref('#D4AF37');
const quickColorSubmitting = ref(false);
const quickColorError = ref(null);

function openQuickColorModal() {
  quickColorName.value = '';
  quickColorCode.value = '#D4AF37';
  quickColorError.value = null;
  showQuickColorModal.value = true;
}

async function saveQuickColor() {
  if (!quickColorName.value.trim()) {
    quickColorError.value = 'Color name is required';
    return;
  }
  quickColorSubmitting.value = true;
  quickColorError.value = null;
  try {
    const created = await colorStore.createColor({
      name: quickColorName.value.trim(),
      code: quickColorCode.value,
      is_active: true
    });
    toggleColor(created);
    showQuickColorModal.value = false;
  } catch (err) {
    quickColorError.value = err.response?.data?.message || 'Failed to add color';
  } finally {
    quickColorSubmitting.value = false;
  }
}

// Quick Add Size Modal State
const showQuickSizeModal = ref(false);
const quickSizeName = ref('');
const quickSizeHint = ref('');
const quickSizeSubmitting = ref(false);
const quickSizeError = ref(null);

function openQuickSizeModal() {
  quickSizeName.value = '';
  quickSizeHint.value = '';
  quickSizeError.value = null;
  if (activeSizeGroups.value && activeSizeGroups.value.length > 0 && !selectedSizeGroupId.value) {
    selectedSizeGroupId.value = activeSizeGroups.value[0].id;
  }
  showQuickSizeModal.value = true;
}

async function saveQuickSize() {
  if (!quickSizeName.value.trim()) {
    quickSizeError.value = 'Size label is required';
    return;
  }
  const targetGroupId = selectedSizeGroupId.value || (activeSizeGroups.value[0]?.id ?? null);
  if (!targetGroupId) {
    quickSizeError.value = 'Please select a Size Group first';
    return;
  }
  quickSizeSubmitting.value = true;
  quickSizeError.value = null;
  try {
    const created = await sizeStore.createSize({
      size_group_id: targetGroupId,
      name: quickSizeName.value.trim(),
      measurement_hint: quickSizeHint.value.trim() || null,
      is_active: true
    });
    toggleSize(created.name);
    showQuickSizeModal.value = false;
  } catch (err) {
    quickSizeError.value = err.response?.data?.message || 'Failed to add size';
  } finally {
    quickSizeSubmitting.value = false;
  }
}

const uploadsInProgress = ref([]);
const dragActive = ref({});

const categories = computed(() => categoryStore.categories);
const tags = computed(() => tagStore.tags);

const primaryImage = computed(() => {
  return form.value.images.find(img => img.is_primary) || form.value.images[0] || null;
});

const totalVariantStock = computed(() => {
  return form.value.variants.reduce((acc, v) => acc + (parseInt(v.stock_quantity) || 0), 0);
});

const calculatedDiscountPercent = computed(() => {
  const mrp = parseFloat(form.value.mrp);
  const selling = parseFloat(form.value.selling_price);
  if (mrp > 0 && selling > 0 && mrp > selling) {
    return Math.round(((mrp - selling) / mrp) * 100);
  }
  return 0;
});

const uniqueColors = computed(() => {
  const colors = form.value.variants
    .map(v => v.color ? v.color.trim() : '')
    .filter(c => c !== '');
  return [...new Set(colors)];
});

// Step validation state for progress & audit
const stepStatus = computed(() => {
  const basicValid = !!form.value.name && form.value.name.trim().length >= 3 && !!form.value.category_id;
  const pricingValid = parseFloat(form.value.mrp) > 0 && 
                       parseFloat(form.value.selling_price) > 0 && 
                       parseFloat(form.value.selling_price) <= parseFloat(form.value.mrp);
  const variantsValid = form.value.variants.length > 0 && 
                        form.value.variants.every(v => !!v.sku && v.sku.trim() !== '' && v.stock_quantity !== undefined && v.stock_quantity !== '');
  const imagesValid = form.value.images.length > 0;
  const seoValid = !!form.value.meta_title || !!form.value.name;

  return {
    basic: { valid: basicValid },
    pricing: { valid: pricingValid },
    variants: { valid: variantsValid },
    images: { valid: imagesValid },
    seo: { valid: seoValid },
    verify: { valid: basicValid && pricingValid && variantsValid }
  };
});

const readinessScore = computed(() => {
  let score = 0;
  if (stepStatus.value.basic.valid) score += 30;
  if (stepStatus.value.pricing.valid) score += 25;
  if (stepStatus.value.variants.valid) score += 25;
  if (stepStatus.value.images.valid) score += 15;
  if (stepStatus.value.seo.valid) score += 5;
  return score;
});

const pendingVerificationCount = computed(() => {
  let count = 0;
  if (!stepStatus.value.basic.valid) count++;
  if (!stepStatus.value.pricing.valid) count++;
  if (!stepStatus.value.variants.valid) count++;
  if (!stepStatus.value.images.valid) count++;
  return count;
});

function isStepCompleted(stepId) {
  if (stepId === 'verify') {
    return readinessScore.value === 100;
  }
  return stepStatus.value[stepId]?.valid || false;
}

function hasStepErrors(stepId) {
  if (stepId === 'basic') {
    return !!fieldErrors.value.name || !!fieldErrors.value.category_id || !!fieldErrors.value.slug;
  }
  if (stepId === 'pricing') {
    return !!fieldErrors.value.mrp || !!fieldErrors.value.selling_price || !!fieldErrors.value.gst_rate;
  }
  if (stepId === 'variants') {
    if (fieldErrors.value.variants) return true;
    return Object.keys(fieldErrors.value).some(k => k.startsWith('variants.'));
  }
  return false;
}

function getCategoryName(categoryId) {
  if (!categoryId) return '';
  const match = categories.value.find(c => c.id === categoryId);
  return match ? match.name : '';
}

function clearFieldError(field) {
  if (fieldErrors.value[field]) {
    delete fieldErrors.value[field];
  }
  if (Object.keys(fieldErrors.value).length === 0) {
    errorMsg.value = null;
    validationErrors.value = [];
  }
}

function validateField(field) {
  if (field === 'name') {
    if (!form.value.name || !form.value.name.trim()) {
      fieldErrors.value.name = 'Product Name is required.';
    } else if (form.value.name.trim().length < 3) {
      fieldErrors.value.name = 'Product Name must be at least 3 characters.';
    } else {
      clearFieldError('name');
    }
  } else if (field === 'category_id') {
    if (!form.value.category_id) {
      fieldErrors.value.category_id = 'Please select a product category.';
    } else {
      clearFieldError('category_id');
    }
  } else if (field === 'mrp') {
    if (form.value.mrp === undefined || form.value.mrp === '' || parseFloat(form.value.mrp) <= 0) {
      fieldErrors.value.mrp = 'Base MRP is required and must be greater than ₹0.';
    } else {
      clearFieldError('mrp');
      if (form.value.selling_price > 0 && parseFloat(form.value.selling_price) <= parseFloat(form.value.mrp)) {
        clearFieldError('selling_price');
      }
    }
  } else if (field === 'selling_price') {
    if (form.value.selling_price === undefined || form.value.selling_price === '' || parseFloat(form.value.selling_price) <= 0) {
      fieldErrors.value.selling_price = 'Selling Price is required and must be greater than ₹0.';
    } else if (parseFloat(form.value.selling_price) > parseFloat(form.value.mrp)) {
      fieldErrors.value.selling_price = `Selling Price cannot exceed MRP (₹${parseFloat(form.value.mrp || 0).toFixed(2)}).`;
    } else {
      clearFieldError('selling_price');
    }
  }
}

function goToStep(stepId) {
  activeTab.value = stepId;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function focusFirstErrorField(fieldId) {
  nextTick(() => {
    const el = document.getElementById(fieldId);
    if (el) {
      el.focus();
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
}

function validateAndNext(currentStepId) {
  errorMsg.value = null;
  validationErrors.value = [];

  if (currentStepId === 'basic') {
    let hasError = false;
    if (!form.value.name || !form.value.name.trim()) {
      fieldErrors.value.name = 'Product Name is required.';
      hasError = true;
    } else if (form.value.name.trim().length < 3) {
      fieldErrors.value.name = 'Product Name must be at least 3 characters.';
      hasError = true;
    }

    if (!form.value.category_id) {
      fieldErrors.value.category_id = 'Please select a Category from the dropdown.';
      hasError = true;
    }

    if (hasError) {
      errorMsg.value = 'Please correct the highlighted fields in Basic Information.';
      const firstId = !form.value.name ? 'input_name' : 'select_category';
      focusFirstErrorField(firstId);
      return;
    }

    goToStep('pricing');
  } else if (currentStepId === 'pricing') {
    let hasError = false;
    if (form.value.mrp === undefined || form.value.mrp === '' || parseFloat(form.value.mrp) <= 0) {
      fieldErrors.value.mrp = 'Please enter a valid Base MRP greater than ₹0.';
      hasError = true;
    }

    if (form.value.selling_price === undefined || form.value.selling_price === '' || parseFloat(form.value.selling_price) <= 0) {
      fieldErrors.value.selling_price = 'Please enter a valid Selling Price greater than ₹0.';
      hasError = true;
    } else if (parseFloat(form.value.selling_price) > parseFloat(form.value.mrp)) {
      fieldErrors.value.selling_price = `Selling price cannot exceed MRP (₹${parseFloat(form.value.mrp || 0).toFixed(2)}).`;
      hasError = true;
    }

    if (hasError) {
      errorMsg.value = 'Please correct the highlighted pricing fields.';
      const firstId = !form.value.mrp ? 'input_mrp' : 'input_selling_price';
      focusFirstErrorField(firstId);
      return;
    }

    goToStep('variants');
  } else if (currentStepId === 'variants') {
    let hasError = false;
    if (form.value.variants.length === 0) {
      fieldErrors.value.variants = 'At least one product variant SKU is required.';
      hasError = true;
    }

    form.value.variants.forEach((v, idx) => {
      if (!v.sku || !v.sku.trim()) {
        fieldErrors.value[`variants.${idx}.sku`] = 'SKU code is required.';
        hasError = true;
      }
      if (v.stock_quantity === undefined || v.stock_quantity === '' || v.stock_quantity < 0) {
        fieldErrors.value[`variants.${idx}.stock_quantity`] = 'Stock quantity is required (>= 0).';
        hasError = true;
      }
      if (v.mrp && v.selling_price && parseFloat(v.selling_price) > parseFloat(v.mrp)) {
        fieldErrors.value[`variants.${idx}.selling_price`] = 'Variant selling price cannot exceed variant MRP.';
        hasError = true;
      }
    });

    if (hasError) {
      errorMsg.value = 'Please resolve the highlighted variant errors.';
      const firstKey = Object.keys(fieldErrors.value).find(k => k.startsWith('variants.'));
      if (firstKey) {
        const parts = firstKey.split('.');
        const id = parts[2] === 'sku' ? `input_sku_${parts[1]}` : `input_stock_${parts[1]}`;
        focusFirstErrorField(id);
      }
      return;
    }

    goToStep('images');
  } else if (currentStepId === 'images') {
    goToStep('seo');
  } else if (currentStepId === 'seo') {
    goToStep('verify');
  }
}

onMounted(async () => {
  categoryStore.fetchCategories();
  tagStore.fetchTags();
  colorStore.fetchActiveColors();
  await sizeStore.fetchActiveSizeGroups();
  if (sizeStore.activeSizeGroups && sizeStore.activeSizeGroups.length > 0 && !selectedSizeGroupId.value) {
    selectedSizeGroupId.value = sizeStore.activeSizeGroups[0].id;
  }

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
          gst_rate: prod.gst_rate !== undefined && prod.gst_rate !== null ? parseFloat(prod.gst_rate) : 5.00,
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
  Object.keys(fieldErrors.value).forEach(k => {
    if (k.startsWith(`variants.${index}.`)) {
      delete fieldErrors.value[k];
    }
  });
}

function generateSKURow(index) {
  const v = form.value.variants[index];
  const prodName = form.value.name ? form.value.name.substring(0, 3).toUpperCase().replace(/[^A-Z]/g, 'STR') : 'STR';
  const size = v.size ? v.size.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '') : 'ALL';
  const color = v.color ? v.color.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '') : 'ALL';
  const rand = Math.floor(1000 + Math.random() * 9000);
  v.sku = `${prodName}-${color}-${size}-${rand}`;
  clearFieldError(`variants.${index}.sku`);
}

function toggleColor(colorObj) {
  const index = selectedColors.value.findIndex(c => c.name.toLowerCase() === colorObj.name.toLowerCase());
  if (index >= 0) {
    selectedColors.value.splice(index, 1);
  } else {
    selectedColors.value.push({ ...colorObj });
  }
}

function isColorSelected(colorName) {
  return selectedColors.value.some(c => c.name.toLowerCase() === colorName.toLowerCase());
}

function addCustomColor() {
  if (!customColorName.value.trim()) return;
  const name = customColorName.value.trim();
  if (!isColorSelected(name)) {
    selectedColors.value.push({ name, code: customColorCode.value });
  }
  customColorName.value = '';
}

function toggleSize(size) {
  const index = selectedSizes.value.indexOf(size);
  if (index >= 0) {
    selectedSizes.value.splice(index, 1);
  } else {
    selectedSizes.value.push(size);
  }
}

function isSizeSelected(size) {
  return selectedSizes.value.includes(size);
}

function addCustomSize() {
  if (!customSizeName.value.trim()) return;
  const size = customSizeName.value.trim();
  if (!isSizeSelected(size)) {
    selectedSizes.value.push(size);
  }
  customSizeName.value = '';
}

function clearSelectedChips() {
  selectedColors.value = [];
  selectedSizes.value = [];
}

function generateSimpleVariants() {
  if (selectedColors.value.length === 0 && selectedSizes.value.length === 0) {
    alert('Please tap at least one Color or Size to generate variants.');
    return;
  }

  const colors = selectedColors.value.length > 0 ? selectedColors.value : [{ name: '', code: '#ffffff' }];
  const sizes = selectedSizes.value.length > 0 ? selectedSizes.value : [''];
  const prefix = (matrixSkuPrefix.value || 'STR-BLOUSE').toUpperCase().replace(/[^A-Z0-9-]/g, '');

  const newVariants = [];
  colors.forEach((c) => {
    sizes.forEach((s) => {
      const colorPart = c.name ? c.name.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '') : 'GEN';
      const sizePart = s ? s.toUpperCase().replace(/[^A-Z0-9]/g, '') : 'ALL';
      const rand = Math.floor(100 + Math.random() * 900);
      const sku = `${prefix}-${colorPart}-${sizePart}-${rand}`;

      newVariants.push({
        id: null,
        sku,
        size: s || '',
        color: c.name || '',
        color_code: c.code || '#ffffff',
        mrp: form.value.mrp ? parseFloat(form.value.mrp) : null,
        selling_price: form.value.selling_price ? parseFloat(form.value.selling_price) : null,
        cost_price: null,
        stock_quantity: defaultStockAmount.value ?? 10,
        low_stock_threshold: 5,
        barcode: '',
        is_active: true,
        sort_order: form.value.variants.length + newVariants.length,
      });
    });
  });

  form.value.variants = [...form.value.variants, ...newVariants];
  clearFieldError('variants');
}

function applyBulkStock() {
  if (bulkStockValue.value === undefined || bulkStockValue.value === null || bulkStockValue.value < 0) return;
  form.value.variants.forEach(v => {
    v.stock_quantity = bulkStockValue.value;
  });
}

function clearAllVariants() {
  if (confirm('Are you sure you want to clear all configured variant SKUs?')) {
    form.value.variants = [];
    selectedColors.value = [];
    selectedSizes.value = [];
  }
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
  
  if (k.startsWith('variants')) return 'variants';
  if (k.startsWith('images') || k.startsWith('media')) return 'images';
  
  const pricingKeys = [
    'mrp', 'selling_price', 'cost_price', 'tax_category', 
    'gst_rate', 'hsn_code', 'weight', 'return_window_days',
    'is_active', 'is_featured', 'is_new_arrival', 'is_bestseller', 'is_returnable'
  ];
  if (pricingKeys.some(pk => k.includes(pk))) return 'pricing';
  
  const seoKeys = ['meta_title', 'meta_description', 'meta_keywords'];
  if (seoKeys.some(sk => k.includes(sk))) return 'seo';
  
  return 'basic';
};

// Save as Draft Trigger
async function saveAsDraft() {
  saveMode.value = 'draft';
  form.value.is_active = false;
  await submitForm(false);
}

// Publish Trigger
async function saveAndPublish() {
  saveMode.value = 'publish';
  form.value.is_active = true;
  await submitForm(true);
}

async function submitForm(requireFullValidation = true) {
  submitting.value = true;
  errorMsg.value = null;
  validationErrors.value = [];
  fieldErrors.value = {};

  let hasErrors = false;

  // Minimum draft validation
  if (!form.value.name || !form.value.name.trim()) {
    fieldErrors.value.name = 'Product Name is required.';
    hasErrors = true;
  } else if (form.value.name.trim().length < 3) {
    fieldErrors.value.name = 'Product Name must be at least 3 characters.';
    hasErrors = true;
  }

  if (!form.value.category_id) {
    fieldErrors.value.category_id = 'Category selection is required.';
    hasErrors = true;
  }

  // Full validation before live publish
  if (requireFullValidation) {
    if (form.value.mrp === undefined || form.value.mrp === '' || parseFloat(form.value.mrp) <= 0) {
      fieldErrors.value.mrp = 'Base MRP is required and must be > ₹0.';
      hasErrors = true;
    }

    if (form.value.selling_price === undefined || form.value.selling_price === '' || parseFloat(form.value.selling_price) <= 0) {
      fieldErrors.value.selling_price = 'Base Selling Price is required and must be > ₹0.';
      hasErrors = true;
    } else if (parseFloat(form.value.selling_price) > parseFloat(form.value.mrp)) {
      fieldErrors.value.selling_price = 'Selling price cannot exceed the Maximum Retail Price (MRP).';
      hasErrors = true;
    }

    if (form.value.variants.length === 0) {
      fieldErrors.value.variants = 'At least one product variant SKU is required to publish.';
      hasErrors = true;
    }

    form.value.variants.forEach((v, idx) => {
      if (!v.sku || !v.sku.trim()) {
        fieldErrors.value[`variants.${idx}.sku`] = 'SKU code is required.';
        hasErrors = true;
      }
      if (v.stock_quantity === undefined || v.stock_quantity === '' || v.stock_quantity < 0) {
        fieldErrors.value[`variants.${idx}.stock_quantity`] = 'Stock units required.';
        hasErrors = true;
      }
      if (v.mrp && v.selling_price && parseFloat(v.selling_price) > parseFloat(v.mrp)) {
        fieldErrors.value[`variants.${idx}.selling_price`] = 'Selling override cannot exceed MRP override.';
        hasErrors = true;
      }
    });
  }

  if (hasErrors) {
    submitting.value = false;
    errorMsg.value = 'Validation failed. Please review the highlighted fields below.';
    Object.values(fieldErrors.value).forEach(msg => validationErrors.value.push(msg));
    
    const firstKey = Object.keys(fieldErrors.value)[0];
    activeTab.value = getTabForErrorKey(firstKey);

    if (firstKey === 'name') focusFirstErrorField('input_name');
    else if (firstKey === 'category_id') focusFirstErrorField('select_category');
    else if (firstKey === 'mrp') focusFirstErrorField('input_mrp');
    else if (firstKey === 'selling_price') focusFirstErrorField('input_selling_price');
    else if (firstKey && firstKey.startsWith('variants.')) {
      const parts = firstKey.split('.');
      const id = parts[2] === 'sku' ? `input_sku_${parts[1]}` : `input_stock_${parts[1]}`;
      focusFirstErrorField(id);
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
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
      Object.keys(err.errors).forEach((key, index) => {
        if (index === 0) firstErrorKey = key;
        const errArray = err.errors[key];
        const msg = Array.isArray(errArray) ? errArray[0] : errArray;
        fieldErrors.value[key] = msg;
        validationErrors.value.push(msg);
      });
    }
    activeTab.value = getTabForErrorKey(firstErrorKey);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
/* Stepper Component */
.stepper-container {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
}

.stepper-track {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  gap: 0.5rem;
}

.stepper-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  position: relative;
  flex: 1;
  transition: all 0.2s ease-in-out;
}

.stepper-item:hover {
  background: var(--blush-bg);
}

.stepper-badge {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--color-border);
  color: var(--color-text-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.85rem;
  flex-shrink: 0;
  transition: all 0.2s ease-in-out;
}

.stepper-item--active .stepper-badge {
  background: var(--color-primary);
  color: #ffffff;
  box-shadow: 0 0 10px rgba(74, 14, 46, 0.3);
}

.stepper-item--completed .stepper-badge {
  background: #10b981;
  color: #ffffff;
}

.stepper-item--invalid .stepper-badge {
  background: #ef4444;
  color: #ffffff;
}

.stepper-err-icon {
  font-weight: 900;
  font-size: 1rem;
}

.stepper-check {
  font-weight: bold;
}

.stepper-content {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.stepper-title {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stepper-item--active .stepper-title {
  color: var(--color-primary);
}

.stepper-subtitle {
  font-size: 0.7rem;
  color: var(--color-text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stepper-line {
  display: none;
}

/* Step Header */
.step-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  border-bottom: 1px solid var(--color-border);
  padding-bottom: 1rem;
  gap: 1rem;
}

.step-heading {
  font-size: 1.35rem;
  color: var(--color-primary);
  margin-bottom: 0.25rem;
}

.step-desc {
  font-size: 0.85rem;
  color: var(--color-text-secondary);
  margin: 0;
}

.step-indicator-pill {
  background: var(--blush-bg);
  color: var(--color-primary);
  padding: 0.35rem 0.85rem;
  border-radius: 16px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid var(--color-border);
  white-space: nowrap;
}

/* Field Level Error & Highlights */
.field-wrapper {
  display: flex;
  flex-direction: column;
}

.form-input--error,
.form-select--error,
.form-textarea--error {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
  background-color: #fffafb !important;
}

.has-field-error .form-label {
  color: #dc2626 !important;
  font-weight: 600 !important;
}

.field-error-text {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.78rem;
  font-weight: 600;
  color: #dc2626;
  margin-top: 6px;
  margin-left: 4px;
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-3px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Section Navigation Footer */
.section-nav-footer {
  margin-top: 3rem;
  border-top: 1px solid var(--color-border);
  padding-top: 1.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.btn-nav-action {
  min-height: 48px;
  border-radius: 24px;
  padding: 0 1.75rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  font-weight: 500;
  text-decoration: none;
}

/* Pricing Calc Card */
.pricing-calc-card {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  background: var(--blush-bg);
  border: 1px solid var(--color-border);
  border-radius: 10px;
  padding: 1.25rem;
  margin-bottom: 1.75rem;
}

.pricing-calc-item {
  display: flex;
  flex-direction: column;
}

.calc-label {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.calc-val {
  font-size: 1.05rem;
  font-weight: 600;
  margin-top: 2px;
}

/* Readiness Banner */
.readiness-banner {
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.readiness-banner--ready {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
}

.readiness-banner--warning {
  background: #fffbeb;
  border: 1px solid #fde68a;
  color: #92400e;
}

.readiness-circle {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #ffffff;
  border: 3px solid currentColor;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  line-height: 1.1;
}

/* Verification Grid */
.verification-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
}

.verify-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: var(--shadow-sm);
  transition: all 0.2s ease;
}

.verify-card:hover {
  box-shadow: var(--shadow-md);
  border-color: var(--color-primary);
}

.verify-card--pass {
  border-left: 4px solid #10b981;
}

.verify-card--fail {
  border-left: 4px solid var(--color-danger);
}

.verify-card--warning {
  border-left: 4px solid var(--color-warning);
}

.verify-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--color-border);
}

.verify-card__body {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.verify-detail-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
}

.v-label {
  color: var(--color-text-muted);
}

.v-val {
  font-weight: 500;
  color: var(--color-text-primary);
  text-align: right;
}

.verify-card__jump-btn {
  background: var(--blush-bg);
  border: 1px solid var(--color-border);
  color: var(--color-primary);
  padding: 0.5rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  text-align: center;
}

.verify-card__jump-btn:hover {
  background: var(--color-primary);
  color: #ffffff;
}

/* Upload Zone & Media Cards */
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
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.media-card__img-wrap {
  position: relative;
  aspect-ratio: 1;
  background: rgba(0,0,0,0.05);
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
  background: rgba(0,0,0,0.08);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: var(--color-primary);
  transition: width 0.2s ease;
}

/* Color & Size Preset Chips */
.color-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 0.35rem 0.75rem;
  font-size: 0.8rem;
  color: var(--color-text-primary);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease-in-out;
  user-select: none;
}

.color-chip:hover {
  border-color: var(--color-primary);
  background: var(--blush-bg);
}

.color-chip--active {
  background: var(--color-primary) !important;
  color: #ffffff !important;
  border-color: var(--color-primary) !important;
  box-shadow: 0 2px 6px rgba(74, 14, 46, 0.25);
  font-weight: 600;
}

.color-chip__dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 1px solid rgba(0, 0, 0, 0.15);
  flex-shrink: 0;
}

.color-chip__check {
  font-weight: bold;
  font-size: 0.75rem;
  margin-left: 2px;
}

.size-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 20px;
  padding: 0.35rem 0.75rem;
  font-size: 0.8rem;
  color: var(--color-text-primary);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease-in-out;
  user-select: none;
}

.size-chip:hover {
  border-color: var(--color-primary);
  background: var(--blush-bg);
}

.size-chip--active {
  background: var(--color-primary) !important;
  color: #ffffff !important;
  border-color: var(--color-primary) !important;
  box-shadow: 0 2px 6px rgba(74, 14, 46, 0.25);
  font-weight: 600;
}

.size-chip__check {
  font-weight: bold;
  font-size: 0.75rem;
  margin-left: 2px;
}

/* Responsive Overrides */
@media (max-width: 900px) {
  .tabs-nav-container {
    display: none !important;
  }
  .stepper-track {
    overflow-x: auto;
    padding-bottom: 0.5rem;
  }
  .stepper-item {
    flex: 0 0 auto;
  }
}

@media (max-width: 650px) {
  .section-nav-footer {
    flex-direction: column-reverse;
    gap: 0.75rem;
  }
  .section-nav-footer > div {
    width: 100%;
    flex-direction: column;
  }
  .btn-nav-action {
    width: 100%;
  }
}
</style>
