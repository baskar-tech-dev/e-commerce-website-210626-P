<template>
  <div class="stock-matrix-page">
    <!-- Header -->
    <div class="admin-page__header">
      <div class="admin-page__title-section">
        <h1 class="admin-page__title">⚡ Quick Product Stock Entry</h1>
        <span class="admin-page__subtitle">Rapid batch stock management across all colors and sizes in a unified spreadsheet matrix.</span>
      </div>
      <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
        <button 
          v-if="selectedProductId" 
          type="button" 
          class="btn btn--secondary btn--sm" 
          @click="downloadCsvTemplate"
          style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;"
        >
          📥 Export CSV Template
        </button>
        <button 
          type="button" 
          class="btn btn--secondary btn--sm" 
          @click="showCsvModal = true"
          style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem;"
        >
          📁 Import Stock CSV
        </button>
        <router-link 
          to="/admin/inventory" 
          class="btn btn--secondary btn--sm" 
          style="border-radius: 8px; font-weight: 600;"
        >
          📦 Stock Control & Ledger
        </router-link>
      </div>
    </div>

    <!-- Alert Notifications -->
    <div v-if="successMsg" class="badge badge--success" style="margin-bottom: 1.25rem; padding: 0.85rem 1.25rem; width: 100%; border-radius: 8px; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
      <span>✓ {{ successMsg }}</span>
      <button type="button" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.1rem;" @click="successMsg = null">✕</button>
    </div>

    <div v-if="errorMsg" class="badge badge--danger" style="margin-bottom: 1.25rem; padding: 0.85rem 1.25rem; width: 100%; border-radius: 8px; font-size: 0.9rem; display: flex; align-items: center; justify-content: space-between;">
      <span>⚠️ {{ errorMsg }}</span>
      <button type="button" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.1rem;" @click="errorMsg = null">✕</button>
    </div>

    <!-- Step 1: Product Selector Panel -->
    <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
        <label class="form-label" style="font-weight: 700; font-size: 0.95rem; color: var(--color-primary); margin: 0;">
          1. Select Product for Stock Entry
        </label>
        <span v-if="selectedProduct" class="badge badge--secondary" style="font-size: 0.8rem;">
          {{ matrixData?.variants_count || selectedProduct.variants?.length || 0 }} Configured SKUs
        </span>
      </div>

      <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex-grow: 1; min-width: 280px; position: relative;">
          <select 
            v-model="selectedProductId" 
            @change="loadProductMatrix" 
            class="form-select has-value" 
            style="height: 48px; font-size: 0.95rem; font-weight: 600; border-radius: 8px; border: 1.5px solid var(--color-border);"
          >
            <option value="" disabled>-- Select a Product from Catalog --</option>
            <option v-for="prod in productsList" :key="prod.id" :value="prod.id">
              {{ prod.name }} ({{ prod.category?.name || 'Catalog' }}) — Current Total Stock: {{ prod.total_stock || prod.stock || 0 }} units
            </option>
          </select>
        </div>

        <button 
          v-if="selectedProductId" 
          type="button" 
          class="btn btn--secondary" 
          @click="loadProductMatrix" 
          :disabled="loadingMatrix"
          style="height: 48px; border-radius: 8px; padding: 0 1.25rem;"
        >
          🔄 {{ loadingMatrix ? 'Loading...' : 'Reload Matrix' }}
        </button>
      </div>

      <!-- Selected Product Info Banner -->
      <div v-if="selectedProduct" style="margin-top: 1.25rem; padding: 1rem; background: rgba(74, 14, 46, 0.03); border: 1px solid rgba(74, 14, 46, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
          <div style="width: 52px; height: 52px; border-radius: 8px; overflow: hidden; background: #eee; flex-shrink: 0; border: 1px solid #ddd;">
            <img 
              v-if="selectedProduct.primary_image_url" 
              :src="selectedProduct.primary_image_url" 
              :alt="selectedProduct.name" 
              style="width: 100%; height: 100%; object-fit: cover;" 
            />
            <div v-else style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">👗</div>
          </div>
          <div>
            <div style="font-size: 1.05rem; font-weight: 700; color: var(--color-text-primary); font-family: 'Playfair Display', serif;">
              {{ selectedProduct.name }}
            </div>
            <div style="font-size: 0.8rem; color: var(--color-text-muted); display: flex; gap: 0.75rem; align-items: center; margin-top: 2px;">
              <span>Category: <strong>{{ selectedProduct.category_name }}</strong></span>
              <span>•</span>
              <span>Total Available Stock: <strong>{{ currentTotalStock }} units</strong></span>
              <span v-if="currentTotalReserved > 0">• Reserved: <strong>{{ currentTotalReserved }}</strong></span>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center;">
          <span class="badge badge--success" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
            {{ matrixColors.length }} Colors × {{ matrixSizes.length }} Sizes
          </span>
        </div>
      </div>
    </div>

    <!-- Step 2: Controls & Matrix Workspace (When Product is Loaded) -->
    <div v-if="selectedProduct && !loadingMatrix">
      <!-- Operation Mode & Reason Settings Bar -->
      <div class="glass-panel" style="padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; align-items: start;">
          <!-- Operation Mode Radio Pills -->
          <div>
            <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 0.5rem; display: block; text-transform: uppercase; letter-spacing: 0.5px;">
              Stock Operation Mode:
            </label>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
              <button 
                type="button" 
                :class="['btn btn--sm', updateMode === 'add' ? 'btn--primary' : 'btn--secondary']"
                @click="updateMode = 'add'"
                style="border-radius: 20px; padding: 0.4rem 1rem; font-weight: 600;"
              >
                🟢 + Inward (Add to Existing)
              </button>
              <button 
                type="button" 
                :class="['btn btn--sm', updateMode === 'set' ? 'btn--primary' : 'btn--secondary']"
                @click="updateMode = 'set'"
                style="border-radius: 20px; padding: 0.4rem 1rem; font-weight: 600;"
              >
                🔵 = Set Exact Stock Count
              </button>
              <button 
                type="button" 
                :class="['btn btn--sm', updateMode === 'subtract' ? 'btn--primary' : 'btn--secondary']"
                @click="updateMode = 'subtract'"
                style="border-radius: 20px; padding: 0.4rem 1rem; font-weight: 600;"
              >
                🔴 - Outward (Deduct Stock)
              </button>
            </div>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.4rem;">
              <span v-if="updateMode === 'add'">Values entered will be added on top of current stock (e.g. Current 5 + Input 10 = 15).</span>
              <span v-else-if="updateMode === 'set'">Values entered will overwrite current stock directly (e.g. Input 10 => New Stock 10).</span>
              <span v-else>Values entered will be deducted from current stock.</span>
            </div>
          </div>

          <!-- Reason & Notes -->
          <div>
            <label class="form-label" style="font-weight: 700; font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 0.35rem; display: block; text-transform: uppercase; letter-spacing: 0.5px;">
              Reason / Audit Note: *
            </label>
            <div style="display: flex; gap: 0.35rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
              <button 
                v-for="preset in reasonPresets" 
                :key="preset"
                type="button"
                class="btn btn--sm btn--secondary"
                @click="auditReason = preset"
                style="border-radius: 12px; height: 22px; padding: 0 8px; font-size: 0.7rem;"
              >
                {{ preset }}
              </button>
            </div>
            <input 
              type="text" 
              v-model="auditReason" 
              placeholder="e.g. New Batch Arrival, Store Physical Audit..." 
              class="form-input" 
              style="height: 38px; font-size: 0.85rem;" 
              required 
            />
          </div>
        </div>
      </div>

      <!-- Matrix View & Bulk Tools Toolbar -->
      <div class="glass-panel" style="padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.75rem;">
          <!-- View Toggle -->
          <div style="display: inline-flex; gap: 0.25rem; background: #ffffff; padding: 2px; border-radius: 20px; border: 1px solid var(--color-border);">
            <button 
              type="button" 
              :class="['btn btn--sm', viewMode === 'matrix' ? 'btn--primary' : 'btn--secondary']"
              @click="viewMode = 'matrix'"
              style="border-radius: 18px; height: 32px; font-size: 0.8rem; border: none;"
            >
              📊 Color × Size Grid Matrix
            </button>
            <button 
              type="button" 
              :class="['btn btn--sm', viewMode === 'table' ? 'btn--primary' : 'btn--secondary']"
              @click="viewMode = 'table'"
              style="border-radius: 18px; height: 32px; font-size: 0.8rem; border: none;"
            >
              📋 Batch Row Table List
            </button>
          </div>

          <!-- Quick Bulk Fill Helper -->
          <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <span style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary);">Bulk Fill All:</span>
            <div style="display: flex; align-items: center; background: #ffffff; border: 1px solid var(--color-border); border-radius: 20px; padding: 2px 4px;">
              <input 
                type="number" 
                v-model.number="bulkFillValue" 
                min="0" 
                placeholder="Qty" 
                style="width: 60px; border: none; outline: none; font-size: 0.85rem; text-align: center; padding: 4px;" 
              />
              <button 
                type="button" 
                class="btn btn--sm btn--secondary" 
                @click="applyBulkFillAll" 
                style="border-radius: 16px; height: 28px; padding: 0 10px; font-size: 0.75rem; font-weight: 600;"
              >
                Fill All Cells
              </button>
            </div>
            <button 
              type="button" 
              class="btn btn--secondary btn--sm" 
              @click="resetMatrixValues" 
              style="height: 32px; border-radius: 16px; font-size: 0.75rem;"
            >
              Reset Values
            </button>
          </div>
        </div>

        <!-- 1. COLOR X SIZE SPREADSHEET MATRIX (Grid View) -->
        <div v-if="viewMode === 'matrix'" style="overflow-x: auto;">
          <table class="matrix-spreadsheet-table">
            <thead>
              <tr>
                <th class="sticky-col header-corner" style="min-width: 170px;">
                  <span style="font-weight: 700; color: var(--color-primary);">Color \ Size</span>
                </th>
                <th 
                  v-for="size in matrixSizes" 
                  :key="size" 
                  class="size-header-cell"
                  style="min-width: 110px; text-align: center;"
                >
                  <div style="font-weight: 700; font-size: 0.9rem; color: #1e293b;">{{ size }}</div>
                  <div style="font-size: 0.7rem; color: var(--color-text-muted); margin-top: 2px;">
                    Col: <strong>{{ getColumnSum(size) }}</strong>
                  </div>
                  <button 
                    type="button" 
                    class="quick-fill-btn" 
                    @click="promptFillColumn(size)" 
                    title="Fill entire column"
                  >
                    ⚡ Fill
                  </button>
                </th>
                <th style="min-width: 100px; text-align: right; background: rgba(0,0,0,0.02);">
                  Row Total
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="color in matrixColors" :key="color.name">
                <!-- Row Header: Color -->
                <td class="sticky-col color-row-header">
                  <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                      <span 
                        class="color-swatch-dot" 
                        :style="{ background: color.code }"
                      ></span>
                      <span style="font-weight: 600; font-size: 0.85rem; color: #1e293b;">{{ color.name }}</span>
                    </div>
                    <button 
                      type="button" 
                      class="quick-fill-btn" 
                      @click="promptFillRow(color.name)" 
                      title="Fill entire row"
                    >
                      ⚡
                    </button>
                  </div>
                </td>

                <!-- Grid Cells: Matrix Inputs -->
                <td 
                  v-for="size in matrixSizes" 
                  :key="size" 
                  class="matrix-cell"
                >
                  <div v-if="getCell(color.name, size)" class="cell-content-box">
                    <div class="cell-current-stock" :title="`Current Stock: ${getCell(color.name, size).stock_quantity}`">
                      Curr: <strong>{{ getCell(color.name, size).stock_quantity }}</strong>
                    </div>
                    <input 
                      type="number" 
                      v-model.number="cellInputs[`${color.name}_${size}`]"
                      min="0"
                      class="matrix-input"
                      :class="{
                        'matrix-input--modified': isCellModified(color.name, size),
                        'matrix-input--positive': getCellProjected(color.name, size) > getCell(color.name, size).stock_quantity,
                        'matrix-input--negative': getCellProjected(color.name, size) < getCell(color.name, size).stock_quantity,
                      }"
                      placeholder="0"
                      @keydown="handleKeyNavigation($event, color.name, size)"
                    />
                    <div class="cell-projected-stock">
                      ➔ <strong>{{ getCellProjected(color.name, size) }}</strong>
                    </div>
                  </div>
                  <div v-else class="cell-disabled">
                    —
                  </div>
                </td>

                <!-- Row Total -->
                <td style="text-align: right; font-weight: 700; color: var(--color-primary); background: rgba(0,0,0,0.02); font-size: 0.95rem;">
                  {{ getRowSum(color.name) }}
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr style="background: rgba(74, 14, 46, 0.04); font-weight: 700;">
                <td class="sticky-col" style="font-weight: 700; color: var(--color-primary);">
                  Total New Matrix Stock
                </td>
                <td v-for="size in matrixSizes" :key="size" style="text-align: center; color: var(--color-primary);">
                  {{ getColumnProjectedSum(size) }}
                </td>
                <td style="text-align: right; color: var(--color-primary); font-size: 1.05rem;">
                  {{ projectedGrandTotal }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- 2. BATCH ROW TABLE VIEW (List View) -->
        <div v-else style="overflow-x: auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Color</th>
                <th>Size</th>
                <th style="text-align: right;">Current Stock</th>
                <th style="width: 140px; text-align: center;">Entry Qty</th>
                <th style="text-align: right;">Projected New Stock</th>
                <th style="text-align: right;">Delta (Change)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="v in matrixVariantsList" :key="v.id">
                <td><code>{{ v.sku }}</code></td>
                <td>
                  <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span class="color-swatch-dot" :style="{ background: v.color_code || '#6B1124' }"></span>
                    <span>{{ v.color }}</span>
                  </div>
                </td>
                <td><span class="badge badge--secondary">{{ v.size }}</span></td>
                <td style="text-align: right; font-weight: 600;">{{ v.stock_quantity }}</td>
                <td style="text-align: center;">
                  <input 
                    type="number" 
                    v-model.number="cellInputs[`${v.color}_${v.size}`]"
                    min="0"
                    class="form-input"
                    style="height: 36px; text-align: center; font-weight: 700;"
                    placeholder="0"
                  />
                </td>
                <td style="text-align: right; font-weight: 700; color: var(--color-primary);">
                  {{ getCellProjected(v.color, v.size) }}
                </td>
                <td style="text-align: right;">
                  <span :class="['badge', getDeltaClass(v.color, v.size)]">
                    {{ getDeltaFormatted(v.color, v.size) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Sticky Save Summary Bar Footer -->
      <div class="sticky-save-footer glass-panel">
        <div class="summary-metrics-group">
          <div>
            <span class="metric-label">Current Total:</span>
            <span class="metric-value">{{ currentTotalStock }} units</span>
          </div>
          <div class="metric-arrow">➔</div>
          <div>
            <span class="metric-label">Projected Total:</span>
            <span class="metric-value" style="color: var(--color-primary);">{{ projectedGrandTotal }} units</span>
          </div>
          <div>
            <span class="metric-label">Net Difference:</span>
            <span :class="['badge', totalNetDelta >= 0 ? 'badge--success' : 'badge--danger']" style="font-size: 0.85rem;">
              {{ totalNetDelta >= 0 ? '+' + totalNetDelta : totalNetDelta }} units
            </span>
          </div>
        </div>

        <div style="display: flex; gap: 0.75rem; align-items: center;">
          <button 
            type="button" 
            class="btn btn--secondary" 
            @click="resetMatrixValues" 
            :disabled="submitting"
            style="height: 48px; border-radius: 8px; font-weight: 600;"
          >
            Cancel / Reset
          </button>
          <button 
            type="button" 
            class="btn btn--primary" 
            @click="saveStockMatrix" 
            :disabled="submitting || modifiedItemsCount === 0"
            style="height: 48px; border-radius: 8px; padding: 0 2rem; font-weight: 700; box-shadow: var(--shadow-md);"
          >
            🚀 {{ submitting ? 'Updating Stock...' : `Save & Update Stock (${modifiedItemsCount} SKUs)` }}
          </button>
        </div>
      </div>
    </div>

    <!-- Empty / Loading State -->
    <div v-else-if="loadingMatrix" style="text-align: center; padding: 4rem;">
      <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏳</div>
      <div style="font-weight: 600; color: var(--color-primary);">Loading product color & size matrix...</div>
    </div>

    <div v-else-if="!selectedProductId" class="glass-panel" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
      <div style="font-size: 3rem; margin-bottom: 0.75rem;">📦</div>
      <h3 style="color: var(--color-text-primary); margin-bottom: 0.5rem; font-family: 'Playfair Display', serif;">
        Select a product above to start batch stock entry
      </h3>
      <p style="max-width: 500px; margin: 0 auto 1.5rem auto; font-size: 0.9rem;">
        Easily update quantities across all color and size combinations in a single unified spreadsheet grid with automatic audit logging.
      </p>
    </div>

    <!-- CSV Upload Modal -->
    <div v-if="showCsvModal" class="modal-overlay" @click.self="showCsvModal = false">
      <div class="modal-container" style="max-width: 480px;">
        <div class="modal-header">
          <h3 class="modal-title">📁 Import Stock from CSV / Excel</h3>
          <button class="modal-close" @click="showCsvModal = false">&times;</button>
        </div>
        <form @submit.prevent="submitCsvImport">
          <div class="modal-body">
            <div v-if="csvModalError" class="badge badge--danger" style="margin-bottom: 1rem; padding: 0.5rem; width: 100%; border-radius: 6px;">
              ⚠️ {{ csvModalError }}
            </div>

            <div class="floating-label-group" style="margin-bottom: 1.25rem;">
              <select v-model="csvProductId" class="form-select has-value" id="csv_product_select" required>
                <option value="" disabled>-- Choose Target Product --</option>
                <option v-for="prod in productsList" :key="prod.id" :value="prod.id">
                  {{ prod.name }}
                </option>
              </select>
              <label for="csv_product_select" class="form-label">Target Product *</label>
            </div>

            <div style="margin-bottom: 1.25rem;">
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.35rem; display: block;">
                Select CSV File * (Columns: variant_id/sku, new_quantity)
              </label>
              <input 
                type="file" 
                accept=".csv,text/csv,text/plain" 
                @change="handleCsvFileSelect" 
                class="form-input" 
                style="padding: 0.5rem;"
                required 
              />
            </div>

            <div style="margin-bottom: 1.25rem;">
              <label class="form-label" style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 0.35rem; display: block;">
                Import Mode:
              </label>
              <div style="display: flex; gap: 0.5rem;">
                <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                  <input type="radio" v-model="csvMode" value="set" /> Set Exact Count
                </label>
                <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; cursor: pointer;">
                  <input type="radio" v-model="csvMode" value="add" /> Inward / Add
                </label>
              </div>
            </div>

            <div class="floating-label-group" style="margin-bottom: 1rem;">
              <input 
                type="text" 
                v-model="csvReason" 
                class="form-input" 
                :class="{'has-value': !!csvReason}" 
                placeholder=" " 
                id="csv_reason_input"
                required 
              />
              <label for="csv_reason_input" class="form-label">Audit Reason * (e.g. Excel Stock Audit)</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn--secondary" @click="showCsvModal = false">Cancel</button>
            <button type="submit" class="btn btn--primary" :disabled="csvSubmitting">
              {{ csvSubmitting ? 'Importing...' : 'Upload & Update Stock' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useInventoryStore } from '../../stores/inventory';
import { useProductStore } from '../../stores/product';

const route = useRoute();
const inventoryStore = useInventoryStore();
const productStore = useProductStore();

const productsList = ref([]);
const selectedProductId = ref('');
const loadingMatrix = ref(false);
const matrixData = ref(null);

const viewMode = ref('matrix'); // 'matrix' or 'table'
const updateMode = ref('add'); // 'add', 'set', 'subtract'
const auditReason = ref('Supplier Shipment Arrival');
const reasonPresets = [
  'Supplier Shipment Arrival',
  'Physical Store Stock Take',
  'Factory Batch Production',
  'Showroom Transfer',
  'Damage / Return Adjustment',
];

const bulkFillValue = ref(10);
const cellInputs = reactive({});
const submitting = ref(false);
const successMsg = ref(null);
const errorMsg = ref(null);

// CSV Import Modal State
const showCsvModal = ref(false);
const csvProductId = ref('');
const csvFile = ref(null);
const csvMode = ref('set');
const csvReason = ref('CSV Bulk Stock Upload');
const csvSubmitting = ref(false);
const csvModalError = ref(null);

const selectedProduct = computed(() => matrixData.value?.product || null);
const matrixColors = computed(() => matrixData.value?.colors || []);
const matrixSizes = computed(() => matrixData.value?.sizes || []);
const matrixGrid = computed(() => matrixData.value?.matrix || {});
const matrixVariantsList = computed(() => matrixData.value?.variants || []);

const currentTotalStock = computed(() => matrixData.value?.product?.total_stock || 0);
const currentTotalReserved = computed(() => matrixData.value?.product?.total_reserved || 0);

function getCell(colorName, sizeName) {
  return matrixGrid.value[colorName]?.[sizeName] || null;
}

function isCellModified(colorName, sizeName) {
  const val = cellInputs[`${colorName}_${sizeName}`];
  return val !== undefined && val !== null && val !== '' && val !== 0;
}

function getCellProjected(colorName, sizeName) {
  const cell = getCell(colorName, sizeName);
  if (!cell) return 0;
  const inputVal = parseInt(cellInputs[`${colorName}_${sizeName}`]) || 0;

  if (updateMode.value === 'set') {
    return inputVal !== undefined && cellInputs[`${colorName}_${sizeName}`] !== '' ? inputVal : cell.stock_quantity;
  } else if (updateMode.value === 'add') {
    return cell.stock_quantity + inputVal;
  } else if (updateMode.value === 'subtract') {
    return Math.max(0, cell.stock_quantity - inputVal);
  }
  return cell.stock_quantity;
}

function getRowSum(colorName) {
  let sum = 0;
  for (const size of matrixSizes.value) {
    sum += getCellProjected(colorName, size);
  }
  return sum;
}

function getColumnSum(sizeName) {
  let sum = 0;
  for (const color of matrixColors.value) {
    const cell = getCell(color.name, sizeName);
    if (cell) {
      sum += cell.stock_quantity;
    }
  }
  return sum;
}

function getColumnProjectedSum(sizeName) {
  let sum = 0;
  for (const color of matrixColors.value) {
    sum += getCellProjected(color.name, sizeName);
  }
  return sum;
}

const projectedGrandTotal = computed(() => {
  let total = 0;
  for (const color of matrixColors.value) {
    for (const size of matrixSizes.value) {
      total += getCellProjected(color.name, size);
    }
  }
  return total;
});

const totalNetDelta = computed(() => {
  return projectedGrandTotal.value - currentTotalStock.value;
});

const modifiedItemsCount = computed(() => {
  let count = 0;
  for (const color of matrixColors.value) {
    for (const size of matrixSizes.value) {
      const cell = getCell(color.name, size);
      if (!cell) continue;
      const inputVal = cellInputs[`${color.name}_${size}`];
      if (inputVal !== undefined && inputVal !== '' && (updateMode.value === 'set' || inputVal > 0)) {
        count++;
      }
    }
  }
  return count;
});

function getDeltaFormatted(colorName, sizeName) {
  const cell = getCell(colorName, sizeName);
  if (!cell) return '—';
  const projected = getCellProjected(colorName, sizeName);
  const delta = projected - cell.stock_quantity;
  if (delta > 0) return `+${delta}`;
  if (delta < 0) return `${delta}`;
  return '0';
}

function getDeltaClass(colorName, sizeName) {
  const cell = getCell(colorName, sizeName);
  if (!cell) return 'badge--secondary';
  const projected = getCellProjected(colorName, sizeName);
  const delta = projected - cell.stock_quantity;
  if (delta > 0) return 'badge--success';
  if (delta < 0) return 'badge--danger';
  return 'badge--secondary';
}

async function fetchProducts() {
  try {
    const res = await axios.get('/api/admin/products?per_page=100');
    if (res.data.success) {
      productsList.value = res.data.data;
    }
  } catch (err) {
    console.error('Failed to load products:', err);
  }
}

async function loadProductMatrix() {
  if (!selectedProductId.value) return;
  loadingMatrix.value = true;
  errorMsg.value = null;
  successMsg.value = null;

  try {
    const data = await inventoryStore.fetchProductMatrix(selectedProductId.value);
    matrixData.value = data;
    resetMatrixValues();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to load product stock matrix';
  } finally {
    loadingMatrix.value = false;
  }
}

function resetMatrixValues() {
  for (const key in cellInputs) {
    delete cellInputs[key];
  }

  // If in 'set' mode, initialize with current values
  if (updateMode.value === 'set' && matrixColors.value.length > 0) {
    for (const color of matrixColors.value) {
      for (const size of matrixSizes.value) {
        const cell = getCell(color.name, size);
        if (cell) {
          cellInputs[`${color.name}_${size}`] = cell.stock_quantity;
        }
      }
    }
  }
}

function applyBulkFillAll() {
  const val = parseInt(bulkFillValue.value) || 0;
  for (const color of matrixColors.value) {
    for (const size of matrixSizes.value) {
      if (getCell(color.name, size)) {
        cellInputs[`${color.name}_${size}`] = val;
      }
    }
  }
}

function promptFillRow(colorName) {
  const qty = prompt(`Enter quantity for all sizes of "${colorName}":`, '10');
  if (qty === null) return;
  const val = parseInt(qty) || 0;
  for (const size of matrixSizes.value) {
    if (getCell(colorName, size)) {
      cellInputs[`${colorName}_${size}`] = val;
    }
  }
}

function promptFillColumn(sizeName) {
  const qty = prompt(`Enter quantity for all colors of Size "${sizeName}":`, '10');
  if (qty === null) return;
  const val = parseInt(qty) || 0;
  for (const color of matrixColors.value) {
    if (getCell(color.name, sizeName)) {
      cellInputs[`${color.name}_${sizeName}`] = val;
    }
  }
}

function handleKeyNavigation(e, colorName, sizeName) {
  // Allow Enter and Arrow keys to navigate spreadsheet cells
  const colorIdx = matrixColors.value.findIndex(c => c.name === colorName);
  const sizeIdx = matrixSizes.value.indexOf(sizeName);

  if (e.key === 'Enter' || e.key === 'ArrowDown') {
    e.preventDefault();
    if (colorIdx < matrixColors.value.length - 1) {
      const nextColor = matrixColors.value[colorIdx + 1].name;
      focusCell(nextColor, sizeName);
    }
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    if (colorIdx > 0) {
      const prevColor = matrixColors.value[colorIdx - 1].name;
      focusCell(prevColor, sizeName);
    }
  }
}

function focusCell(colorName, sizeName) {
  const el = document.querySelector(`input[name="cell_${colorName}_${sizeName}"]`);
  if (el) el.focus();
}

async function saveStockMatrix() {
  if (!auditReason.value.trim()) {
    errorMsg.value = 'Please provide an Audit Reason / Note before saving.';
    return;
  }

  const items = [];
  for (const color of matrixColors.value) {
    for (const size of matrixSizes.value) {
      const cell = getCell(color.name, size);
      if (!cell) continue;
      const inputVal = cellInputs[`${color.name}_${size}`];
      if (inputVal !== undefined && inputVal !== '') {
        items.push({
          variant_id: cell.variant_id,
          quantity: parseInt(inputVal) || 0,
        });
      }
    }
  }

  if (items.length === 0) {
    errorMsg.value = 'No quantities entered to update.';
    return;
  }

  submitting.value = true;
  errorMsg.value = null;
  successMsg.value = null;

  try {
    const payload = {
      product_id: selectedProductId.value,
      mode: updateMode.value,
      reason: auditReason.value.trim(),
      items: items,
    };

    const res = await inventoryStore.bulkUpdateMatrix(payload);
    successMsg.value = res.message || `Successfully updated stock for ${res.data?.updated_count || items.length} variants!`;
    await loadProductMatrix();
  } catch (err) {
    errorMsg.value = err.response?.data?.message || 'Failed to save stock matrix.';
  } finally {
    submitting.value = false;
  }
}

function downloadCsvTemplate() {
  if (!selectedProductId.value) return;
  window.open(`/api/admin/inventory/export-template/${selectedProductId.value}`, '_blank');
}

function handleCsvFileSelect(e) {
  csvFile.value = e.target.files[0] || null;
}

async function submitCsvImport() {
  if (!csvProductId.value) {
    csvModalError.value = 'Please select a target product.';
    return;
  }
  if (!csvFile.value) {
    csvModalError.value = 'Please choose a CSV file.';
    return;
  }

  csvSubmitting.value = true;
  csvModalError.value = null;

  try {
    const formData = new FormData();
    formData.append('product_id', csvProductId.value);
    formData.append('file', csvFile.value);
    formData.append('mode', csvMode.value);
    formData.append('reason', csvReason.value);

    const res = await inventoryStore.importCsvStock(formData);
    showCsvModal.value = false;
    successMsg.value = res.message;
    selectedProductId.value = csvProductId.value;
    await loadProductMatrix();
  } catch (err) {
    csvModalError.value = err.response?.data?.message || 'Failed to import CSV stock file.';
  } finally {
    csvSubmitting.value = false;
  }
}

onMounted(async () => {
  await fetchProducts();
  if (route.query.product_id) {
    selectedProductId.value = parseInt(route.query.product_id);
    await loadProductMatrix();
  }
});
</script>

<style scoped>
.stock-matrix-page {
  padding-bottom: 5rem;
}

.color-swatch-dot {
  display: inline-block;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1.5px solid #ffffff;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
  flex-shrink: 0;
}

/* Matrix Spreadsheet Styles */
.matrix-spreadsheet-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid var(--color-border);
}

.matrix-spreadsheet-table th,
.matrix-spreadsheet-table td {
  padding: 0.65rem 0.75rem;
  border-right: 1px solid var(--color-border);
  border-bottom: 1px solid var(--color-border);
}

.matrix-spreadsheet-table th:last-child,
.matrix-spreadsheet-table td:last-child {
  border-right: none;
}

.matrix-spreadsheet-table tr:last-child td {
  border-bottom: none;
}

.sticky-col {
  position: sticky;
  left: 0;
  background: #ffffff;
  z-index: 2;
  box-shadow: 2px 0 5px rgba(0,0,0,0.04);
}

.header-corner {
  background: #f8fafc;
  z-index: 3;
}

.size-header-cell {
  background: #f8fafc;
}

.color-row-header {
  background: #fdfefe;
}

.quick-fill-btn {
  background: rgba(74, 14, 46, 0.08);
  color: var(--color-primary);
  border: none;
  border-radius: 10px;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 1px 6px;
  cursor: pointer;
  margin-top: 2px;
  transition: all 0.2s ease;
}

.quick-fill-btn:hover {
  background: var(--color-primary);
  color: #ffffff;
}

.cell-content-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
}

.cell-current-stock {
  font-size: 0.7rem;
  color: var(--color-text-muted);
}

.matrix-input {
  width: 76px;
  height: 36px;
  border: 1.5px solid var(--color-border);
  border-radius: 6px;
  text-align: center;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
  background: #ffffff;
  outline: none;
  transition: all 0.15s ease;
}

.matrix-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(74, 14, 46, 0.15);
  background: #fffdf9;
}

.matrix-input--modified {
  border-color: var(--color-primary);
  background: rgba(74, 14, 46, 0.04);
}

.cell-projected-stock {
  font-size: 0.7rem;
  color: var(--color-primary);
}

.cell-disabled {
  text-align: center;
  color: #cbd5e1;
  font-size: 1.1rem;
}

/* Sticky Save Footer */
.sticky-save-footer {
  position: fixed;
  bottom: 0;
  left: var(--admin-sidebar-width, 260px);
  right: 0;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-top: 1px solid var(--color-border);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 -4px 16px rgba(0,0,0,0.08);
  z-index: 50;
  flex-wrap: wrap;
  gap: 1rem;
}

@media (max-width: 900px) {
  .sticky-save-footer {
    left: 0;
  }
}

.summary-metrics-group {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  flex-wrap: wrap;
}

.metric-label {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  font-weight: 600;
  display: block;
}

.metric-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-text-primary);
}

.metric-arrow {
  font-size: 1.25rem;
  color: var(--color-text-muted);
}
</style>
