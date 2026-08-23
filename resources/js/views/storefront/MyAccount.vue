<template>
  <div>
    <h1 style="color: var(--color-primary); font-size: 2rem; font-weight: 800; margin-bottom: var(--spacing-lg);">My Account Dashboard</h1>

    <!-- Guest Unauthenticated Banner -->
    <div v-if="!authStore.isAuthenticated" class="glass-panel" style="max-width: 600px; margin: 3rem auto; padding: var(--spacing-xl); text-align: center; border: 1px solid var(--color-border);">
      <span style="font-size: 4rem; display: block; margin-bottom: var(--spacing-sm);">👤</span>
      <h2 style="color: var(--color-primary); font-weight: 800; margin-bottom: var(--spacing-xs);">Welcome to Maya Sree Account</h2>
      <p style="color: var(--color-text-secondary); margin-bottom: var(--spacing-lg); font-size: 0.95rem;">
        Sign in or create an account to view your order history, manage shipping addresses, save favorite items, and post product reviews.
      </p>
      <div style="display: flex; gap: var(--spacing-md); justify-content: center; flex-wrap: wrap;">
        <button class="btn btn--primary" style="padding: 0.75rem 2rem; font-weight: 600;" @click="authStore.openAuthModal('login')">
          Sign In
        </button>
        <button class="btn btn--secondary" style="padding: 0.75rem 2rem; font-weight: 600;" @click="authStore.openAuthModal('register')">
          Create Account
        </button>
      </div>
    </div>

    <!-- Authenticated Customer Dashboard -->
    <div v-else class="account-layout-grid">
      <!-- Left: Navigation Pills -->
      <div class="glass-panel account-nav-pills">
        <button 
          v-for="t in ['profile', 'addresses', 'orders', 'wishlist', 'reviews']" 
          :key="t"
          type="button"
          class="btn account-nav-btn"
          :class="activeTab === t ? 'btn--primary' : 'btn--secondary'"
          @click="changeTab(t)"
        >
          <span v-if="t === 'profile'">
            <span class="tab-icon">👤</span><span class="tab-text"> Personal Profile</span>
          </span>
          <span v-else-if="t === 'addresses'">
            <span class="tab-icon">📍</span><span class="tab-text"> Address Book</span>
          </span>
          <span v-else-if="t === 'orders'">
            <span class="tab-icon">📦</span><span class="tab-text"> Order History</span>
          </span>
          <span v-else-if="t === 'wishlist'">
            <span class="tab-icon">❤️</span><span class="tab-text"> My Wishlist</span>
          </span>
          <span v-else-if="t === 'reviews'">
            <span class="tab-icon">⭐</span><span class="tab-text"> My Reviews</span>
          </span>
        </button>
      </div>

      <!-- Right: Sub-tab panel views -->
      <div class="glass-panel" style="padding: var(--spacing-lg);">
        <div v-if="loading" style="text-align: center; padding: 4rem;">
          <div class="stat-card__value" style="font-size: 1.2rem;">Loading account details...</div>
        </div>

        <div v-else>
          <!-- Tab 1: Profile Details -->
          <div v-if="activeTab === 'profile'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: var(--spacing-xs);">
              <div class="card-header-title" style="border: none; margin: 0; padding: 0;">Personal Profile Details</div>
              <button 
                v-if="!isEditingProfile" 
                class="btn btn--primary btn--sm" 
                @click="startEditingProfile"
                title="Edit Profile"
                aria-label="Edit Profile"
                style="padding: 0.4rem 0.8rem; font-size: 0.9rem; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;"
              >
                ✏️ Edit Profile
              </button>
            </div>

            <div v-if="profileSuccessMsg" class="form-success-alert" style="background: #F3FAF7; border: 1px solid #84E1BC; color: #0E6245; padding: 10px 14px; border-radius: 8px; font-size: 0.88rem;">
              {{ profileSuccessMsg }}
            </div>

            <div v-if="profileErrorMsg" class="form-error-alert" style="background: #FDF2F2; border: 1px solid #F8B4B4; color: #9B1C1C; padding: 10px 14px; border-radius: 8px; font-size: 0.88rem;">
              {{ profileErrorMsg }}
            </div>

            <!-- PROFILE SPLIT LAYOUT: Left (Details) | Right (Avatar) -->
            <div class="profile-split-layout">
              <!-- Left Column: Name & Contact Details -->
              <div class="profile-details-col">
                <!-- VIEW MODE -->
                <div v-if="!isEditingProfile" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                  <div class="account-form-row-2">
                    <div class="form-group">
                      <label class="form-label">First Name</label>
                      <div class="form-input" style="background: rgba(255,255,255,0.03); color: #fff;">{{ profile.first_name || '—' }}</div>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Last Name</label>
                      <div class="form-input" style="background: rgba(255,255,255,0.03); color: #fff;">{{ profile.last_name || '—' }}</div>
                    </div>
                  </div>

                  <div class="account-form-row-2">
                    <div class="form-group">
                      <label class="form-label">Email Address</label>
                      <div class="form-input" style="background: rgba(255,255,255,0.03); color: #fff;">{{ profile.email || '—' }}</div>
                    </div>

                    <div class="form-group">
                      <label class="form-label">Mobile Number</label>
                      <div class="form-input" style="background: rgba(255,255,255,0.03); color: #fff;">{{ profile.phone || '—' }}</div>
                    </div>
                  </div>
                </div>

                <!-- EDIT MODE FORM -->
                <form v-else id="profileDetailsForm" @submit.prevent="saveProfileDetails" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                  <div class="account-form-row-2">
                    <div class="form-group">
                      <label class="form-label">First Name *</label>
                      <input type="text" v-model="editProfileForm.first_name" class="form-input" required style="background: #ffffff; color: #111;" />
                    </div>
                    <div class="form-group">
                      <label class="form-label">Last Name</label>
                      <input type="text" v-model="editProfileForm.last_name" class="form-input" style="background: #ffffff; color: #111;" />
                    </div>
                  </div>

                  <div class="account-form-row-2">
                    <div class="form-group">
                      <label class="form-label">Email Address *</label>
                      <input type="email" v-model="editProfileForm.email" class="form-input" required style="background: #ffffff; color: #111;" />
                    </div>

                    <div class="form-group">
                      <label class="form-label">Mobile Number</label>
                      <input type="tel" v-model="editProfileForm.phone" class="form-input" placeholder="+91 99442 85102" style="background: #ffffff; color: #111;" />
                    </div>
                  </div>

                  <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-xs);">
                    <button type="submit" class="btn btn--primary" :disabled="savingProfile">
                      {{ savingProfile ? 'Saving...' : '💾 Save Profile Changes' }}
                    </button>
                    <button type="button" class="btn btn--secondary" @click="cancelEditingProfile">
                      Cancel
                    </button>
                  </div>
                </form>
              </div>

              <!-- Right Column: Profile Picture Card -->
              <div class="profile-avatar-col">
                <div class="profile-avatar-card" style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.85rem; background: rgba(110, 31, 58, 0.04); border: 1px solid var(--color-border); padding: 1.25rem; border-radius: 12px;">
                  <div class="avatar-circle-frame" style="position: relative; width: 96px; height: 96px; border-radius: 50%; overflow: hidden; border: 3px solid #6E1F3A; box-shadow: 0 4px 15px rgba(110, 31, 58, 0.15); flex-shrink: 0; background: #6E1F3A; display: flex; align-items: center; justify-content: center;">
                    <img v-if="avatarPreviewUrl || profile.avatar" :src="avatarPreviewUrl || profile.avatar" alt="Profile Avatar" style="width: 100%; height: 100%; object-fit: cover;" />
                    <span v-else style="font-size: 2.25rem; font-weight: 700; color: #ffffff; text-transform: uppercase;">
                      {{ profileInitials }}
                    </span>
                  </div>

                  <div style="display: flex; flex-direction: column; gap: 4px;">
                    <h4 style="margin: 0; color: var(--color-primary); font-size: 1rem; font-weight: 700;">Profile Picture</h4>
                    <p style="margin: 0; font-size: 0.78rem; color: #64748b;">JPG, PNG or WebP (max 5 MB)</p>
                    
                    <div style="display: flex; gap: 8px; margin-top: 6px; justify-content: center; flex-wrap: wrap;">
                      <input type="file" ref="avatarInputRef" accept="image/jpeg,image/jpg,image/png,image/webp" style="display: none;" @change="handleAvatarFileSelect" />
                      <button type="button" class="btn btn--secondary btn--sm" @click="triggerAvatarFileSelect" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        📷 {{ avatarPreviewUrl || profile.avatar ? 'Change' : 'Upload' }}
                      </button>
                      <button v-if="avatarPreviewUrl || profile.avatar" type="button" class="btn btn--danger btn--sm" @click="removeAvatarPhoto" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; font-weight: 600;">
                        🗑️ Remove
                      </button>
                    </div>

                    <div v-if="!isEditingProfile && (avatarFile || avatarRemoved)" style="margin-top: 8px;">
                      <button type="button" class="btn btn--primary btn--sm" :disabled="savingProfile" @click="saveProfileDetails" style="width: 100%; padding: 0.4rem 0.8rem; font-size: 0.82rem;">
                        {{ savingProfile ? 'Saving...' : '💾 Save Photo' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 2: Addresses CRUD -->
          <div v-if="activeTab === 'addresses'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: var(--spacing-md);">
              <div class="card-header-title" style="border: none; margin: 0; padding: 0;">Address Book</div>
              <button class="btn btn--primary btn--sm" @click="showAddForm = !showAddForm">
                {{ showAddForm ? 'Cancel Form' : '➕ Add Address' }}
              </button>
            </div>

            <!-- Add Address Form -->
            <div v-if="showAddForm" class="glass-panel" style="padding: var(--spacing-md); border: 1px solid var(--color-primary); background: rgba(0,0,0,0.1); margin-bottom: var(--spacing-lg);">
              <form @submit.prevent="saveAddress" style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                <div class="account-form-row-2col">
                  <div class="form-group">
                    <label class="form-label">Label (e.g. Home, Office) *</label>
                    <input type="text" v-model="addressForm.label" class="form-input" placeholder="Home" required />
                  </div>
                  <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.8rem;">
                    <input type="checkbox" id="is_default" v-model="addressForm.is_default_shipping" />
                    <label for="is_default" style="color: #fff; font-size: 0.85rem; font-weight: bold; cursor: pointer;">Set as Default Shipping Address</label>
                  </div>
                </div>

                <div class="account-form-row-2">
                  <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" v-model="addressForm.first_name" class="form-input" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" v-model="addressForm.last_name" class="form-input" required />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Phone *</label>
                  <input type="text" v-model="addressForm.phone" class="form-input" required />
                </div>

                <div class="form-group">
                  <label class="form-label">Address Line 1 *</label>
                  <input type="text" v-model="addressForm.address_line_1" class="form-input" required />
                </div>

                <div class="form-group">
                  <label class="form-label">Address Line 2</label>
                  <input type="text" v-model="addressForm.address_line_2" class="form-input" />
                </div>

                <div class="account-form-row-3">
                  <div class="form-group">
                    <label class="form-label">City *</label>
                    <input type="text" v-model="addressForm.city" class="form-input" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label">State *</label>
                    <select v-model="addressForm.state" class="form-input" required>
                      <option value="" disabled>-- Select State / UT --</option>
                      <option v-for="st in indianStates" :key="st" :value="st">
                        {{ st }}
                      </option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Postal Pincode *</label>
                    <input type="text" v-model="addressForm.postal_code" class="form-input" required />
                  </div>
                </div>

                <div style="display: flex; gap: var(--spacing-sm); justify-content: flex-end; margin-top: 0.5rem;">
                  <button type="submit" class="btn btn--primary btn--sm" :disabled="savingAddress">
                    Save Address
                  </button>
                </div>
              </form>
            </div>

            <!-- List Addresses -->
            <div class="addresses-list-grid">
              <div v-for="addr in profile.addresses" :key="addr.id" class="glass-panel" style="padding: var(--spacing-md); border: 1px solid var(--color-border); position: relative;">
                <span class="badge" :class="addr.is_default_shipping ? 'badge--primary' : 'badge--secondary'" style="font-size: 0.7rem; text-transform: uppercase; margin-bottom: 0.5rem;">
                  {{ addr.label }} {{ addr.is_default_shipping ? '(Default)' : '' }}
                </span>
                
                <div style="font-weight: bold; color: #fff; margin-bottom: 0.25rem;">{{ addr.first_name }} {{ addr.last_name }}</div>
                <div style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.4; margin-bottom: var(--spacing-md);">
                  {{ addr.address_line_1 }}<br />
                  <span v-if="addr.address_line_2">{{ addr.address_line_2 }}<br /></span>
                  {{ addr.city }}, {{ addr.state }} - {{ addr.postal_code }}<br />
                  📞 {{ addr.phone }}
                </div>

                <div style="display: flex; gap: var(--spacing-sm); justify-content: flex-end; border-top: 1px solid var(--color-border); padding-top: 0.5rem;">
                  <button class="btn btn--secondary btn--sm" @click="editAddress(addr)" style="font-size: 0.75rem; padding: 2px 8px;">✏️ Edit</button>
                  <button class="btn btn--danger btn--sm" @click="deleteAddress(addr.id)" style="font-size: 0.75rem; padding: 2px 8px;">🗑️ Delete</button>
                </div>
              </div>
              <div v-if="profile.addresses?.length === 0 && !showAddForm" style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--color-text-muted);">
                Your shipping address book is empty. Add an address to speed up checkout.
              </div>
            </div>
          </div>

          <!-- Tab 3: Order History -->
          <div v-if="activeTab === 'orders'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; margin-bottom: var(--spacing-xs);">
              <div>
                <div class="card-header-title" style="border: none; margin: 0; padding: 0;">My Orders</div>
                <p style="margin: 2px 0 0 0; font-size: 0.82rem; color: #64748b;">
                  Track shipments, review items, and download invoices for all your past purchases.
                </p>
              </div>
              <span v-if="profile.orders?.length" class="badge badge--secondary" style="font-size: 0.75rem; font-weight: 600;">
                {{ profile.orders.length }} {{ profile.orders.length === 1 ? 'Order' : 'Orders' }}
              </span>
            </div>

            <!-- Empty State -->
            <div v-if="!profile.orders || profile.orders.length === 0" class="order-empty-card">
              <span style="font-size: 3.5rem; display: block; margin-bottom: var(--spacing-sm);">🛍️</span>
              <h3 style="color: var(--color-primary); font-weight: 700; margin-bottom: 0.25rem;">No Orders Placed Yet</h3>
              <p style="color: #64748b; font-size: 0.9rem; max-width: 400px; margin: 0 auto 1.5rem auto;">
                Explore our festive collection of traditional ethnic wear, sarees, and readymade blouses.
              </p>
              <router-link to="/shop" class="btn btn--primary" style="padding: 0.75rem 2rem; font-weight: 600; text-decoration: none;">
                Explore Collections ➔
              </router-link>
            </div>

            <!-- Order Cards List -->
            <div v-else style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
              <div v-for="order in profile.orders" :key="order.id" class="order-card-luxury">
                <!-- Top Header Bar -->
                <div class="order-card-header">
                  <div class="order-header-left">
                    <div class="order-no-wrapper">
                      <span class="order-no-label">Order</span>
                      <span class="order-no-value">#{{ order.order_number }}</span>
                    </div>
                    <div class="order-date-label">
                      <span>📅 Placed on {{ formatDate(order.created_at) }}</span>
                      <span v-if="order.payment_gateway" class="order-gateway-tag">
                        via {{ (order.payment_gateway || 'Cashfree').toUpperCase() }}
                      </span>
                    </div>
                  </div>

                  <div class="order-header-right">
                    <!-- Status Badge -->
                    <span :class="['badge', getStatusBadgeClass(order.status)]" class="order-status-badge">
                      {{ getStatusLabel(order.status) }}
                    </span>
                    <!-- Payment Status Badge -->
                    <span :class="['badge', getPaymentBadgeClass(order.payment_status)]" class="order-payment-badge">
                      {{ getPaymentLabel(order.payment_status, order.payment_method) }}
                    </span>
                  </div>
                </div>

                <!-- Order Progress Stepper (if not cancelled/returned) -->
                <div v-if="!['cancelled', 'returned', 'refunded'].includes(order.status)" class="order-stepper-container">
                  <div class="order-stepper-bar">
                    <div class="stepper-track-bg"></div>
                    <div class="stepper-track-active" :style="{ width: getStepperWidth(order.status) }"></div>

                    <div class="stepper-node" :class="{ 'completed': isStepCompleted(order.status, 1), 'current': isCurrentStep(order.status, 1) }">
                      <div class="node-circle"><span>1</span></div>
                      <span class="node-label">Placed</span>
                    </div>
                    <div class="stepper-node" :class="{ 'completed': isStepCompleted(order.status, 2), 'current': isCurrentStep(order.status, 2) }">
                      <div class="node-circle"><span>2</span></div>
                      <span class="node-label">Confirmed</span>
                    </div>
                    <div class="stepper-node" :class="{ 'completed': isStepCompleted(order.status, 3), 'current': isCurrentStep(order.status, 3) }">
                      <div class="node-circle"><span>3</span></div>
                      <span class="node-label">Packed</span>
                    </div>
                    <div class="stepper-node" :class="{ 'completed': isStepCompleted(order.status, 4), 'current': isCurrentStep(order.status, 4) }">
                      <div class="node-circle"><span>4</span></div>
                      <span class="node-label">Shipped</span>
                    </div>
                    <div class="stepper-node" :class="{ 'completed': isStepCompleted(order.status, 5), 'current': isCurrentStep(order.status, 5) }">
                      <div class="node-circle"><span>5</span></div>
                      <span class="node-label">Delivered</span>
                    </div>
                  </div>

                  <!-- Courier tracking notice if shipped -->
                  <div v-if="order.tracking_number || order.courier_name" class="order-tracking-banner">
                    <span class="tracking-icon">🚚</span>
                    <div class="tracking-info-text" style="flex: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                      <div>
                        <strong>{{ order.courier_name || 'Courier' }}:</strong> Tracking ID <code class="tracking-code">{{ order.tracking_number }}</code>
                      </div>
                      <!-- Live Courier Tracking Page Link -->
                      <a 
                        v-if="getOrderTrackingUrl(order)" 
                        :href="getOrderTrackingUrl(order)" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="track-shipment-btn"
                        title="Track package on courier website"
                      >
                        <span>🔗 Track Package ↗</span>
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Cancelled Banner if cancelled -->
                <div v-else class="order-cancelled-box">
                  <span style="font-size: 1.25rem;">✕</span>
                  <div>
                    <strong style="color: #991b1b; font-size: 0.9rem;">Order Cancelled</strong>
                    <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: #b91c1c;">
                      {{ order.cancellation_reason || 'This order was cancelled and inventory has been released.' }}
                    </p>
                  </div>
                </div>

                <!-- Product Items Preview List -->
                <div class="order-items-container">
                  <div v-for="item in order.items" :key="item.id" class="order-item-row">
                    <!-- Product Image -->
                    <div class="order-item-img-frame">
                      <img v-protect-image :src="getItemImage(item)" :alt="item.product_name" class="order-item-img" />
                    </div>

                    <!-- Item Details -->
                    <div class="order-item-meta">
                      <router-link v-if="item.product?.uuid" :to="`/products/${item.product.uuid}`" class="order-item-name">
                        {{ item.product_name }}
                      </router-link>
                      <span v-else class="order-item-name">{{ item.product_name }}</span>

                      <div class="order-item-attributes">
                        <span v-if="item.variant?.size" class="item-attr-tag">Size: {{ item.variant.size }}</span>
                        <span v-if="item.variant?.color" class="item-attr-tag">Color: {{ item.variant.color }}</span>
                        <span v-if="item.sku" class="item-sku-tag">SKU: {{ item.sku }}</span>
                      </div>

                      <div class="order-item-pricing">
                        <span>Qty: {{ item.quantity }}</span>
                        <span class="pricing-divider">•</span>
                        <span>₹{{ formatPrice(item.unit_price) }} each</span>
                      </div>
                    </div>

                    <!-- Item Total -->
                    <div class="order-item-total">
                      <span class="item-total-amount">₹{{ formatPrice(item.total_price || (item.quantity * item.unit_price)) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Bottom Order Summary & Actions Bar -->
                <div class="order-card-footer">
                  <div class="order-total-summary">
                    <div class="grand-total-label">Grand Total</div>
                    <div class="grand-total-val">₹{{ formatPrice(order.grand_total) }}</div>
                    <div class="total-items-badge">
                      {{ order.items?.length || 1 }} {{ (order.items?.length || 1) === 1 ? 'item' : 'items' }} • {{ (order.payment_method || 'Online').toUpperCase() }}
                    </div>
                  </div>

                  <div class="order-actions-cluster">
                    <!-- Pay Now button if online & unpaid -->
                    <button 
                      v-if="order.payment_method && order.payment_method.toLowerCase() !== 'cod' && order.payment_status !== 'paid' && order.status !== 'cancelled'"
                      class="btn btn--primary btn--sm order-action-btn pay-now-btn"
                      :disabled="retryingOrderId === order.id"
                      @click="retryPayment(order)"
                    >
                      {{ retryingOrderId === order.id ? 'Connecting...' : '💳 Pay Now' }}
                    </button>

                    <!-- Print / Download Invoice -->
                    <button 
                      type="button" 
                      class="btn btn--secondary btn--sm order-action-btn"
                      @click="printCustomerInvoice(order)"
                      title="Download or print receipt invoice"
                    >
                      🧾 Invoice
                    </button>

                    <!-- Need Help / WhatsApp support -->
                    <a 
                      :href="`https://wa.me/919944285102?text=Hi%20Maya%20Sree%20Fashion,%20I%20have%20an%20inquiry%20regarding%20Order%20${order.order_number}`" 
                      target="_blank" 
                      class="btn btn--secondary btn--sm order-action-btn whatsapp-order-link"
                      title="Chat on WhatsApp"
                    >
                      💬 Support
                    </a>

                    <!-- Toggle Details Expand -->
                    <button 
                      type="button" 
                      class="btn btn--secondary btn--sm order-action-btn toggle-details-btn" 
                      @click="toggleOrderDetails(order.id)"
                    >
                      {{ expandedOrderNo === order.id ? '▲ Hide Details' : '▼ Details' }}
                    </button>
                  </div>
                </div>

                <!-- Expanded Breakdown Drawer (Financials & Shipping Address) -->
                <div v-if="expandedOrderNo === order.id" class="order-expanded-panel">
                  <div class="expanded-grid">
                    <!-- Shipping Address Info -->
                    <div class="expanded-col">
                      <h5 class="expanded-heading">📦 Delivery Address</h5>
                      <div class="address-preview-text">
                        <strong>{{ order.shipping_first_name }} {{ order.shipping_last_name }}</strong><br />
                        {{ order.shipping_address_line_1 }}<br />
                        <span v-if="order.shipping_address_line_2">{{ order.shipping_address_line_2 }}<br /></span>
                        {{ order.shipping_city }}, {{ order.shipping_state }} - {{ order.shipping_postal_code }}<br />
                        <span style="color: #64748b; font-size: 0.8rem;">📞 {{ order.shipping_phone }}</span>
                      </div>
                    </div>

                    <!-- Price Breakdown Info -->
                    <div class="expanded-col">
                      <h5 class="expanded-heading">💰 Payment Breakdown</h5>
                      <div class="breakdown-lines">
                        <div class="breakdown-line">
                          <span>Items Subtotal:</span>
                          <span>₹{{ formatPrice(order.subtotal) }}</span>
                        </div>
                        <div v-if="parseFloat(order.discount_amount) > 0" class="breakdown-line discount">
                          <span>Coupon Discount:</span>
                          <span>-₹{{ formatPrice(order.discount_amount) }}</span>
                        </div>
                        <div class="breakdown-line">
                          <span>Delivery / Shipping:</span>
                          <span>{{ parseFloat(order.shipping_amount) > 0 ? '₹' + formatPrice(order.shipping_amount) : 'FREE' }}</span>
                        </div>
                        <div class="breakdown-line total-line">
                          <strong>Grand Total:</strong>
                          <strong style="color: #6E1F3A; font-size: 1.05rem;">₹{{ formatPrice(order.grand_total) }}</strong>
                        </div>
                        <div v-if="order.gateway_payment_id" class="breakdown-line" style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                          <span>Payment Ref:</span>
                          <span style="font-family: monospace;">{{ order.gateway_payment_id }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 4: Wishlist -->
          <div v-if="activeTab === 'wishlist'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-xs);">My Wishlist</div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
              <div v-for="w in wishlist" :key="w.id" class="glass-panel" style="padding: var(--spacing-md); display: flex; gap: var(--spacing-md); align-items: center; border: 1px solid var(--color-border);">
                <!-- Cover -->
                <div style="width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 1px solid var(--color-border); flex-shrink: 0;">
                  <img v-protect-image :src="w.image || 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=150&auto=format&fit=crop'" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" />
                </div>
                
                <!-- Info -->
                <div style="flex: 1; overflow: hidden;">
                  <router-link :to="`/products/${w.uuid || w.id}`" style="color: #fff; font-weight: bold; font-size: 0.85rem; text-decoration: none; display: block; margin-bottom: 2px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                    {{ w.name }}
                  </router-link>
                  <span style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: var(--spacing-xs);">₹{{ w.selling_price }}</span>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: var(--spacing-xs);">
                  <router-link :to="`/products/${w.uuid || w.id}`" class="btn btn--primary btn--sm" style="padding: 0.35rem; font-size: 0.75rem;">Buy</router-link>
                  <button class="btn btn--danger btn--sm" @click="removeWishlist(w.id)" style="padding: 0.35rem; font-size: 0.75rem;">🗑️</button>
                </div>
              </div>

              <div v-if="wishlist.length === 0" style="grid-column: 1 / -1; text-align: center; padding: 4rem; color: var(--color-text-muted);">
                Your wishlist is empty. Tap ❤️ on product cards to add products here.
              </div>
            </div>
          </div>

          <!-- Tab 5: My Reviews -->
          <div v-if="activeTab === 'reviews'" style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <div class="card-header-title" style="margin-bottom: var(--spacing-xs);">My Product Reviews</div>

            <div v-for="rev in profile.reviews" :key="rev.id" class="glass-panel" style="padding: var(--spacing-md); border: 1px solid var(--color-border); display: flex; flex-direction: column; gap: var(--spacing-xs);">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <router-link :to="`/products/${rev.product?.uuid || rev.product_id}`" style="color: var(--color-primary); font-weight: bold; font-size: 0.95rem; text-decoration: none;">
                  {{ rev.product?.name || 'Product Review' }}
                </router-link>
                <span class="badge" :class="rev.status === 'approved' ? 'badge--success' : (rev.status === 'pending' ? 'badge--warning' : 'badge--danger')" style="text-transform: uppercase; font-size: 0.75rem;">
                  {{ rev.status === 'approved' ? '✓ Approved' : (rev.status === 'pending' ? '⏳ Moderation Pending' : '✕ Rejected') }}
                </span>
              </div>

              <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 2px;">
                <div style="color: #f59e0b; font-size: 0.9rem;">
                  <span v-for="s in 5" :key="s" :style="{ opacity: s <= rev.rating ? 1 : 0.25 }">★</span>
                </div>
                <span style="font-size: 0.78rem; color: var(--color-text-muted);">Reviewed on {{ formatDate(rev.created_at) }}</span>
                <span v-if="rev.is_verified_purchase" style="font-size: 0.75rem; color: var(--color-success); font-weight: bold;">✔ Verified Purchase</span>
              </div>

              <p style="font-size: 0.88rem; color: var(--color-text-primary); margin: 0.35rem 0 0 0; line-height: 1.5;">
                {{ rev.review }}
              </p>
            </div>

            <div v-if="!profile.reviews || profile.reviews.length === 0" style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
              You haven't submitted any product reviews yet.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';
import { useIndianStates } from '../../constants/indianStates';

const emit = defineEmits(['update-wishlist-count']);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { indianStates, fetchIndianStates, normalizeState } = useIndianStates();

const getSavedUserEmail = () => {
  return (
    route.query.email ||
    localStorage.getItem('ms_user_email') ||
    localStorage.getItem('vibe_user_email') ||
    sessionStorage.getItem('ms_gift_registered_email') ||
    ''
  );
};

const initialEmail = getSavedUserEmail();
const initialName = initialEmail ? initialEmail.split('@')[0].toUpperCase() : '';

const activeTab = ref(route.query.tab || 'profile');
const loading = ref(true);
const profile = ref({
  first_name: initialName,
  last_name: '',
  email: initialEmail,
  phone: '',
  addresses: [],
  orders: [],
});

const wishlist = ref([]);
const showAddForm = ref(false);
const savingAddress = ref(false);
const expandedOrderNo = ref(null);

// Edit Profile state
const isEditingProfile = ref(false);
const savingProfile = ref(false);
const profileSuccessMsg = ref('');
const profileErrorMsg = ref('');

const editProfileForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: ''
});

const startEditingProfile = () => {
  editProfileForm.value = {
    first_name: profile.value.first_name || '',
    last_name: profile.value.last_name || '',
    email: profile.value.email || '',
    phone: profile.value.phone || ''
  };
  profileSuccessMsg.value = '';
  profileErrorMsg.value = '';
  isEditingProfile.value = true;
};

const cancelEditingProfile = () => {
  isEditingProfile.value = false;
  profileSuccessMsg.value = '';
  profileErrorMsg.value = '';
};

// Avatar state
const avatarInputRef = ref(null);
const avatarFile = ref(null);
const avatarPreviewUrl = ref('');
const avatarRemoved = ref(false);

const profileInitials = computed(() => {
  const fn = profile.value.first_name || '';
  const ln = profile.value.last_name || '';
  if (fn || ln) {
    return `${fn.charAt(0)}${ln.charAt(0)}`.toUpperCase();
  }
  return (profile.value.email || 'U').charAt(0).toUpperCase();
});

const triggerAvatarFileSelect = () => {
  avatarInputRef.value?.click();
};

const handleAvatarFileSelect = (e) => {
  const files = e.target.files;
  if (!files || files.length === 0) return;
  const file = files[0];
  if (!file.type.startsWith('image/')) {
    profileErrorMsg.value = 'Please select a valid image file (JPG, PNG, WebP).';
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    profileErrorMsg.value = 'Image size must be less than 5 MB.';
    return;
  }

  profileErrorMsg.value = '';
  avatarFile.value = file;
  avatarRemoved.value = false;
  avatarPreviewUrl.value = URL.createObjectURL(file);
};

const removeAvatarPhoto = () => {
  avatarFile.value = null;
  if (avatarPreviewUrl.value && avatarPreviewUrl.value.startsWith('blob:')) {
    try { URL.revokeObjectURL(avatarPreviewUrl.value); } catch (e) {}
  }
  avatarPreviewUrl.value = '';
  avatarRemoved.value = true;
};

const saveProfileDetails = async () => {
  savingProfile.value = true;
  profileSuccessMsg.value = '';
  profileErrorMsg.value = '';

  try {
    const formData = new FormData();
    const fn = isEditingProfile.value ? editProfileForm.value.first_name : profile.value.first_name;
    const ln = isEditingProfile.value ? editProfileForm.value.last_name : profile.value.last_name;
    const em = isEditingProfile.value ? editProfileForm.value.email : profile.value.email;
    const ph = isEditingProfile.value ? editProfileForm.value.phone : profile.value.phone;

    formData.append('first_name', fn || '');
    formData.append('last_name', ln || '');
    formData.append('email', em || '');
    formData.append('phone', ph || '');

    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value);
    } else if (avatarRemoved.value) {
      formData.append('remove_avatar', '1');
    }

    formData.append('_method', 'PUT');

    const res = await axios.post('/api/customer/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    if (res.data && res.data.success) {
      profile.value.first_name = res.data.data.first_name;
      profile.value.last_name = res.data.data.last_name;
      profile.value.email = res.data.data.email;
      profile.value.phone = res.data.data.phone;
      profile.value.avatar = res.data.data.avatar;

      avatarFile.value = null;
      if (avatarPreviewUrl.value && avatarPreviewUrl.value.startsWith('blob:')) {
        try { URL.revokeObjectURL(avatarPreviewUrl.value); } catch (e) {}
      }
      avatarPreviewUrl.value = '';
      avatarRemoved.value = false;

      profileSuccessMsg.value = '✓ Personal profile updated successfully!';
      isEditingProfile.value = false;
      await authStore.fetchUser();
    }
  } catch (err) {
    profileErrorMsg.value = err.response?.data?.message || 'Failed to update profile. Please check your details and try again.';
  } finally {
    savingProfile.value = false;
  }
};

const addressForm = ref({
  id: null,
  label: '',
  first_name: '',
  last_name: '',
  phone: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  state: '',
  postal_code: '',
  is_default_shipping: false,
});

const fetchProfileDetails = async () => {
  if (!authStore.isAuthenticated) {
    loading.value = false;
    return;
  }
  loading.value = true;
  try {
    const response = await axios.get('/api/customer/profile');
    if (response.data && response.data.success && response.data.data) {
      profile.value = { ...profile.value, ...response.data.data };
    }
  } catch (err) {
    console.error('Failed to load profile params:', err);
  } finally {
    loading.value = false;
  }
};

watch(() => authStore.isAuthenticated, (isAuth) => {
  if (isAuth) {
    fetchProfileDetails();
    loadWishlist();
  } else {
    profile.value = {
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      addresses: [],
      orders: [],
      reviews: [],
    };
  }
});

const saveAddress = async () => {
  savingAddress.value = true;
  try {
    const response = await axios.post('/api/customer/addresses', addressForm.value);
    if (response.data && response.data.success) {
      showAddForm.value = false;
      resetAddressForm();
      await fetchProfileDetails();
    }
  } catch (err) {
    console.error('Failed to save address details:', err);
    alert(err.response?.data?.message || 'Failed to save address details');
  } finally {
    savingAddress.value = false;
  }
};

const resetAddressForm = () => {
  addressForm.value = {
    id: null,
    label: '',
    first_name: '',
    last_name: '',
    phone: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: 'Tamil Nadu',
    postal_code: '',
    is_default_shipping: false,
  };
};

const editAddress = (addr) => {
  showAddForm.value = true;
  addressForm.value = {
    id: addr.id,
    label: addr.label,
    first_name: addr.first_name,
    last_name: addr.last_name,
    phone: addr.phone,
    address_line_1: addr.address_line_1,
    address_line_2: addr.address_line_2 || '',
    city: addr.city,
    state: normalizeState(addr.state || 'Tamil Nadu'),
    postal_code: addr.postal_code,
    is_default_shipping: !!addr.is_default_shipping,
  };
};

const deleteAddress = async (id) => {
  if (!confirm('Are you sure you want to remove this address?')) return;
  try {
    const response = await axios.delete(`/api/customer/addresses/${id}`);
    if (response.data && response.data.success) {
      await fetchProfileDetails();
    }
  } catch (err) {
    console.error('Failed to delete address:', err);
  }
};

const loadWishlist = async () => {
  if (authStore.isAuthenticated) {
    try {
      const res = await axios.get('/api/customer/wishlist');
      if (res.data && res.data.success) {
        wishlist.value = res.data.data;
        localStorage.setItem('vibe_wishlist_items', JSON.stringify(wishlist.value));
        emit('update-wishlist-count');
        return;
      }
    } catch (e) {}
  }
  try {
    wishlist.value = JSON.parse(localStorage.getItem('vibe_wishlist_items') || '[]');
  } catch (e) {
    wishlist.value = [];
  }
};

const removeWishlist = async (id) => {
  const index = wishlist.value.findIndex(item => item.id === id);
  if (index >= 0) {
const item = wishlist.value[index];
    wishlist.value.splice(index, 1);
    localStorage.setItem('vibe_wishlist_items', JSON.stringify(wishlist.value));
    emit('update-wishlist-count');

    if (authStore.isAuthenticated) {
      try {
        await axios.delete(`/api/customer/wishlist/${item.uuid || id}`);
      } catch (e) {}
    }
  }
};

const toggleOrderDetails = (orderId) => {
  if (expandedOrderNo.value === orderId) {
    expandedOrderNo.value = null;
  } else {
    expandedOrderNo.value = orderId;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatPrice = (amount) => {
  const num = parseFloat(amount || 0);
  return isNaN(num) ? '0.00' : num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getItemImage = (item) => {
  if (item?.product?.images && item.product.images.length > 0) {
    const img = item.product.images[0].image_path;
    return img.startsWith('http') ? img : (img.startsWith('/') ? img : `/${img}`);
  }
  if (item?.variant?.image_path) {
    const img = item.variant.image_path;
    return img.startsWith('http') ? img : (img.startsWith('/') ? img : `/${img}`);
  }
  return '/asset/occasion/Party-wear.png';
};

const getOrderTrackingUrl = (order) => {
  if (!order) return null;
  if (order.courier_tracking_url) {
    return order.courier_tracking_url;
  }
  if (order.tracking_url) {
    return order.tracking_url;
  }
  if (order.courier?.tracking_page_link && order.tracking_number) {
    const template = order.courier.tracking_page_link;
    return template.replace(/\{tracking_number\}|\{tracking_id\}|\{awb\}/g, encodeURIComponent(order.tracking_number.trim()));
  }
  return null;
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'order_placed':
    case 'pending':
      return 'badge--warning';
    case 'order_confirmed':
    case 'confirmed':
      return 'badge--primary';
    case 'processing':
    case 'ready_to_ship':
      return 'badge--secondary';
    case 'shipped':
      return 'badge--warning';
    case 'delivered':
      return 'badge--success';
    case 'cancelled':
    case 'returned':
      return 'badge--danger';
    case 'refunded':
      return 'badge--secondary';
    default:
      return 'badge--secondary';
  }
};

const getStatusLabel = (status) => {
  switch (status) {
    case 'order_placed':
    case 'pending':
      return '📝 Order Placed';
    case 'order_confirmed':
    case 'confirmed':
      return '✓ Order Confirmed';
    case 'processing':
      return '⚙️ Processing';
    case 'ready_to_ship':
      return '📦 Ready to Ship';
    case 'shipped':
      return '🚚 Shipped';
    case 'delivered':
      return '🎉 Delivered';
    case 'cancelled':
      return '✕ Cancelled';
    case 'returned':
      return '↩ Returned';
    case 'refunded':
      return '💰 Refunded';
    default:
      return (status || 'Order Placed').toUpperCase();
  }
};

const getPaymentBadgeClass = (paymentStatus) => {
  switch (paymentStatus) {
    case 'paid':
    case 'captured':
      return 'badge--success';
    case 'pending':
    case 'authorized':
      return 'badge--warning';
    case 'failed':
      return 'badge--danger';
    case 'refunded':
      return 'badge--secondary';
    default:
      return 'badge--secondary';
  }
};

const getPaymentLabel = (paymentStatus, method) => {
  const isCod = (method || '').toLowerCase() === 'cod';
  if (paymentStatus === 'paid' || paymentStatus === 'captured') {
    return isCod ? 'COD (Paid)' : 'Paid Online';
  }
  if (paymentStatus === 'failed') return 'Payment Failed';
  if (paymentStatus === 'refunded') return 'Refunded';
  return isCod ? 'Cash on Delivery' : 'Payment Pending';
};

const getStepperWidth = (status) => {
  switch (status) {
    case 'order_placed':
    case 'pending':
      return '10%';
    case 'order_confirmed':
    case 'confirmed':
      return '30%';
    case 'processing':
      return '50%';
    case 'ready_to_ship':
      return '60%';
    case 'shipped':
      return '80%';
    case 'delivered':
      return '100%';
    default:
      return '10%';
  }
};

const isStepCompleted = (status, step) => {
  const stepOrder = {
    'order_placed': 1,
    'pending': 1,
    'order_confirmed': 2,
    'confirmed': 2,
    'processing': 3,
    'ready_to_ship': 3,
    'shipped': 4,
    'delivered': 5,
  };
  const current = stepOrder[status] || 1;
  return current >= step;
};

const isCurrentStep = (status, step) => {
  const stepOrder = {
    'order_placed': 1,
    'pending': 1,
    'order_confirmed': 2,
    'confirmed': 2,
    'processing': 3,
    'ready_to_ship': 3,
    'shipped': 4,
    'delivered': 5,
  };
  const current = stepOrder[status] || 1;
  return current === step;
};

const printCustomerInvoice = (order) => {
  const itemsHtml = (order.items || []).map(item => `
    <tr>
      <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">
        <strong style="color: #1e293b;">${item.product_name}</strong>
        ${item.sku ? `<br><small style="color: #64748b; font-family: monospace;">SKU: ${item.sku}</small>` : ''}
        ${item.variant?.size ? `<small style="color: #64748b;"> • Size: ${item.variant.size}</small>` : ''}
      </td>
      <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: center;">${item.quantity}</td>
      <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: right;">₹${parseFloat(item.unit_price || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
      <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: right; font-weight: bold; color: #6E1F3A;">₹${parseFloat(item.total_price || (item.quantity * item.unit_price)).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
    </tr>
  `).join('');

  const content = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Tax Invoice - ${order.order_number}</title>
      <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 32px; color: #1e293b; max-width: 820px; margin: 0 auto; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #6E1F3A; padding-bottom: 20px; margin-bottom: 24px; }
        .brand-name { font-size: 22px; font-weight: 800; color: #6E1F3A; letter-spacing: 0.5px; }
        .brand-sub { font-size: 12px; color: #64748b; margin-top: 2px; }
        .invoice-title { font-size: 20px; font-weight: 700; color: #6E1F3A; margin: 0; text-align: right; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; font-size: 13.5px; }
        .info-card { background: #faf7f8; border: 1px solid #ede4ea; padding: 14px 16px; border-radius: 8px; }
        .info-card h4 { margin: 0 0 8px 0; font-size: 14px; color: #6E1F3A; border-bottom: 1px solid #ede4ea; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13.5px; }
        th { background: #6E1F3A; color: #ffffff; padding: 10px; text-align: left; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .totals-box { margin-left: auto; width: 320px; font-size: 13.5px; background: #faf7f8; border: 1px solid #ede4ea; border-radius: 8px; padding: 14px 16px; }
        .totals-row { display: flex; justify-content: space-between; padding: 4px 0; color: #475569; }
        .grand-total { border-top: 2px solid #6E1F3A; padding-top: 8px; margin-top: 4px; font-size: 16px; font-weight: 800; color: #6E1F3A; }
        .footer-note { text-align: center; margin-top: 40px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 16px; }
      </style>
    </head>
    <body>
      <div class="header">
        <div>
          <div class="brand-name">MAYA SREE FASHION</div>
          <div class="brand-sub">A Mother's Dream That Became A Fashion Legacy</div>
          <div style="font-size: 12px; color: #64748b; margin-top: 6px;">Tirupur, Tamil Nadu, India • Support: +91 99442 85102</div>
        </div>
        <div style="text-align: right;">
          <h2 class="invoice-title">RETAIL INVOICE</h2>
          <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">#${order.order_number}</div>
          <div style="font-size: 12.5px; color: #64748b;">Date: ${formatDate(order.created_at)}</div>
        </div>
      </div>

      <div class="grid-2">
        <div class="info-card">
          <h4>Customer & Shipping Details</h4>
          <strong>${order.shipping_first_name} ${order.shipping_last_name}</strong><br />
          ${order.shipping_address_line_1}<br />
          ${order.shipping_address_line_2 ? order.shipping_address_line_2 + '<br />' : ''}
          ${order.shipping_city}, ${order.shipping_state} - ${order.shipping_postal_code}<br />
          📞 ${order.shipping_phone}
        </div>

        <div class="info-card">
          <h4>Payment & Status Details</h4>
          <strong>Payment Mode:</strong> ${(order.payment_method || 'Online').toUpperCase()}<br />
          <strong>Payment Status:</strong> ${(order.payment_status || 'Pending').toUpperCase()}<br />
          ${order.payment_gateway ? `<strong>Gateway:</strong> ${order.payment_gateway.toUpperCase()}<br />` : ''}
          ${order.gateway_payment_id ? `<strong>Transaction Ref:</strong> ${order.gateway_payment_id}<br />` : ''}
          <strong>Order Status:</strong> ${(order.status || 'Processing').toUpperCase()}
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Item Description</th>
            <th style="text-align: center;">Qty</th>
            <th style="text-align: right;">Unit Price</th>
            <th style="text-align: right;">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          ${itemsHtml}
        </tbody>
      </table>

      <div class="totals-box">
        <div class="totals-row">
          <span>Items Subtotal:</span>
          <span>₹${parseFloat(order.subtotal || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
        </div>
        ${parseFloat(order.discount_amount) > 0 ? `
          <div class="totals-row" style="color: #059669; font-weight: 600;">
            <span>Coupon Discount:</span>
            <span>-₹${parseFloat(order.discount_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
          </div>
        ` : ''}
        <div class="totals-row">
          <span>Shipping Charges:</span>
          <span>${parseFloat(order.shipping_amount) > 0 ? '₹' + parseFloat(order.shipping_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 }) : 'FREE'}</span>
        </div>
        <div class="totals-row grand-total">
          <span>Grand Total:</span>
          <span>₹${parseFloat(order.grand_total || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>
        </div>
      </div>

      <div class="footer-note">
        This is a computer-generated tax invoice. Thank you for shopping with Maya Sree Fashion!<br />
        For any support, reach out via WhatsApp at +91 99442 85102 or email support@mayasreefashion.com
      </div>
    </body>
    </html>
  `;
  const win = window.open('', '_blank');
  win.document.write(content);
  win.document.close();
  win.focus();
  setTimeout(() => win.print(), 250);
};

const retryingOrderId = ref(null);

const loadCashfreeScript = () => {
  return new Promise((resolve) => {
    if (window.Cashfree) {
      resolve(true);
      return;
    }
    const script = document.createElement('script');
    script.src = 'https://sdk.cashfree.com/js/v3/cashfree.js';
    script.async = true;
    script.onload = () => resolve(true);
    script.onerror = () => resolve(false);
    document.body.appendChild(script);
  });
};

const retryPayment = async (order) => {
  retryingOrderId.value = order.id;
  try {
    const scriptLoaded = await loadCashfreeScript();
    if (!scriptLoaded || !window.Cashfree) {
      alert('Failed to load Cashfree Payment Gateway SDK.');
      retryingOrderId.value = null;
      return;
    }

    const cfOrderResponse = await axios.post('/api/payment/cashfree/create', {
      order_id: order.id,
    });

    if (!cfOrderResponse.data.success) {
      alert('Failed to initiate payment session.');
      retryingOrderId.value = null;
      return;
    }

    const cfData = cfOrderResponse.data.data;
    
    if (!cfData.payment_session_id) {
      alert('Cashfree session ID could not be retrieved.');
      retryingOrderId.value = null;
      return;
    }

    const cashfree = window.Cashfree({
      mode: cfData.environment === 'production' ? 'production' : 'sandbox',
    });

    const checkoutOptions = {
      paymentSessionId: cfData.payment_session_id,
      redirectTarget: '_modal',
    };

    cashfree.checkout(checkoutOptions).then(async (result) => {
      if (result.error) {
        console.warn('Payment interaction cancelled/failed:', result.error);
        alert(result.error.message || 'Payment cancelled or dismissed.');
        retryingOrderId.value = null;
        return;
      }

      if (result.paymentDetails || result.redirect) {
        retryingOrderId.value = order.id;
        try {
          const verifyResponse = await axios.post('/api/payment/cashfree/verify', {
            order_id: order.id,
            cashfree_order_id: cfData.order_id || cfData.order_number,
          });

          if (verifyResponse.data.success) {
            alert('Payment received and verified successfully!');
            await fetchProfileDetails();
          } else {
            alert('Payment verification could not be confirmed. Please refresh and check your order status.');
          }
        } catch (err) {
          console.error(err);
          alert(err.response?.data?.message || 'Payment verification failed.');
        } finally {
          retryingOrderId.value = null;
        }
      }
    });

  } catch (err) {
    console.error('Failed to retry payment:', err);
    alert(err.response?.data?.message || 'Failed to initiate payment. Please try again.');
    retryingOrderId.value = null;
  }
};

const changeTab = (t) => {
  activeTab.value = t;
  router.replace({ query: { ...route.query, tab: t } });
  scrollActiveTabIntoView();
};

const scrollActiveTabIntoView = () => {
  nextTick(() => {
    const activeEl = document.querySelector('.account-nav-pills .btn--primary');
    if (activeEl) {
      activeEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
  });
};

watch(() => route.query.tab, (newTab) => {
  if (newTab && newTab !== activeTab.value) {
    activeTab.value = newTab;
    scrollActiveTabIntoView();
  }
});

onMounted(() => {
  fetchProfileDetails();
  loadWishlist();
  fetchIndianStates();
  scrollActiveTabIntoView();
});
</script>

<style scoped>
.account-layout-grid {
  display: grid;
  grid-template-columns: 1fr 3fr;
  gap: var(--spacing-lg);
}

.account-nav-pills {
  padding: var(--spacing-md);
  height: fit-content;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.account-nav-btn {
  text-align: left;
  justify-content: flex-start;
  text-transform: capitalize;
  width: 100%;
}

.account-form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

.account-form-row-2col {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: var(--spacing-md);
}

.account-form-row-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: var(--spacing-md);
}

.addresses-list-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

.profile-split-layout {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: var(--spacing-lg);
  align-items: start;
}

.profile-details-col {
  display: flex;
  flex-direction: column;
}

.profile-avatar-col {
  display: flex;
  flex-direction: column;
}

@media (max-width: 900px) {
  .profile-split-layout {
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
  }
}

@media (max-width: 768px) {
  .account-layout-grid {
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
  }

  .account-nav-pills {
    flex-direction: row;
    overflow-x: auto;
    white-space: nowrap;
    padding: var(--spacing-sm);
    scrollbar-width: none;
  }

  .account-nav-pills::-webkit-scrollbar {
    display: none;
  }

  .account-nav-pills button {
    flex-shrink: 0;
  }
}

@media (max-width: 576px) {
  .tab-text {
    display: none !important;
  }

  .account-nav-pills {
    justify-content: center !important;
    gap: 16px !important;
  }

  .account-nav-btn {
    width: 48px !important;
    height: 48px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    font-size: 1.25rem !important;
  }

  .account-form-row-2,
  .account-form-row-2col,
  .account-form-row-3,
  .addresses-list-grid {
    grid-template-columns: 1fr;
    gap: var(--spacing-sm);
  }

  .account-form-row-2col div[style*="margin-top"] {
    margin-top: 0 !important;
  }
}

/* ===================================================
   LUXURY ORDER HISTORY STYLING
=================================================== */
.order-empty-card {
  text-align: center;
  padding: 4rem 2rem;
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 14px;
}

.order-card-luxury {
  background: #ffffff;
  border: 1px solid #ede4ea;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(45, 5, 28, 0.04);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
  display: flex;
  flex-direction: column;
}

.order-card-luxury:hover {
  box-shadow: 0 6px 20px rgba(45, 5, 28, 0.08);
}

.order-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  background: #fdfafb;
  border-bottom: 1px solid #f1e7ec;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.order-header-left {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.order-no-wrapper {
  display: flex;
  align-items: center;
  gap: 6px;
}

.order-no-label {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #64748b;
}

.order-no-value {
  font-family: monospace;
  font-size: 1.05rem;
  font-weight: 700;
  color: #6E1F3A;
  letter-spacing: 0.5px;
}

.order-date-label {
  font-size: 0.8rem;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.order-gateway-tag {
  background: rgba(110, 31, 58, 0.08);
  color: #6E1F3A;
  padding: 1px 6px;
  border-radius: 4px;
  font-size: 0.72rem;
  font-weight: 600;
}

.order-header-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.order-status-badge {
  font-size: 0.78rem;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 20px;
}

.order-payment-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 6px;
}

/* Stepper Progress */
.order-stepper-container {
  padding: 1.25rem 1.25rem 0.75rem 1.25rem;
  background: #ffffff;
  border-bottom: 1px solid #f8f1f4;
}

.order-stepper-bar {
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 600px;
  margin: 0 auto 0.75rem auto;
}

.stepper-track-bg {
  position: absolute;
  top: 14px;
  left: 5%;
  right: 5%;
  height: 3px;
  background: #e2e8f0;
  z-index: 1;
}

.stepper-track-active {
  position: absolute;
  top: 14px;
  left: 5%;
  height: 3px;
  background: #6E1F3A;
  z-index: 2;
  transition: width 0.4s ease;
}

.stepper-node {
  position: relative;
  z-index: 3;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.node-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #ffffff;
  border: 2px solid #cbd5e1;
  color: #94a3b8;
  font-size: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.stepper-node.completed .node-circle {
  background: #6E1F3A;
  border-color: #6E1F3A;
  color: #ffffff;
}

.stepper-node.current .node-circle {
  background: #ffffff;
  border-color: #6E1F3A;
  color: #6E1F3A;
  box-shadow: 0 0 0 3px rgba(110, 31, 58, 0.15);
}

.node-label {
  font-size: 0.72rem;
  font-weight: 600;
  color: #64748b;
  text-align: center;
}

.stepper-node.completed .node-label,
.stepper-node.current .node-label {
  color: #6E1F3A;
  font-weight: 700;
}

.order-tracking-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 0.82rem;
  color: #1e40af;
  margin-top: 0.5rem;
}

.tracking-code {
  font-family: monospace;
  background: #dbeafe;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: bold;
}

.track-shipment-btn {
  background: #1e40af;
  color: #ffffff !important;
  font-size: 0.78rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 6px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: background 0.15s ease, transform 0.15s ease;
  box-shadow: 0 1px 3px rgba(30, 64, 175, 0.2);
}

.track-shipment-btn:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
}

.order-cancelled-box {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fef2f2;
  border-bottom: 1px solid #fee2e2;
  padding: 0.85rem 1.25rem;
  color: #991b1b;
}

/* Items List */
.order-items-container {
  padding: 0.75rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.order-item-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f8f1f4;
}

.order-item-row:last-child {
  border-bottom: none;
}

.order-item-img-frame {
  width: 64px;
  height: 64px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #ede4ea;
  flex-shrink: 0;
  background: #faf7f8;
}

.order-item-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.order-item-meta {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.order-item-name {
  color: #1e293b;
  font-weight: 600;
  font-size: 0.92rem;
  text-decoration: none;
  line-height: 1.35;
}

.order-item-name:hover {
  color: #6E1F3A;
}

.order-item-attributes {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  margin-top: 2px;
}

.item-attr-tag {
  background: #f1f5f9;
  color: #475569;
  padding: 1px 6px;
  border-radius: 4px;
  font-size: 0.72rem;
  font-weight: 500;
}

.item-sku-tag {
  color: #94a3b8;
  font-size: 0.72rem;
  font-family: monospace;
}

.order-item-pricing {
  font-size: 0.8rem;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 2px;
}

.pricing-divider {
  color: #cbd5e1;
}

.order-item-total {
  text-align: right;
  flex-shrink: 0;
}

.item-total-amount {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
}

/* Card Footer */
.order-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.85rem 1.25rem;
  background: #fdfafb;
  border-top: 1px solid #f1e7ec;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.order-total-summary {
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex-wrap: wrap;
}

.grand-total-label {
  font-size: 0.82rem;
  color: #64748b;
  font-weight: 500;
}

.grand-total-val {
  font-size: 1.25rem;
  font-weight: 800;
  color: #6E1F3A;
}

.total-items-badge {
  font-size: 0.75rem;
  color: #64748b;
  background: rgba(0, 0, 0, 0.04);
  padding: 2px 6px;
  border-radius: 4px;
}

.order-actions-cluster {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.order-action-btn {
  padding: 0.4rem 0.85rem;
  font-size: 0.82rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  text-decoration: none;
  border-radius: 6px;
}

.pay-now-btn {
  background: #6E1F3A;
  color: #ffffff;
  border-color: #6E1F3A;
}

.pay-now-btn:hover {
  background: #55162c;
}

.whatsapp-order-link {
  color: #059669;
  border-color: #a7f3d0;
  background: #f0fdf4;
}

.whatsapp-order-link:hover {
  background: #dcfce7;
  color: #047857;
}

/* Expanded Drawer */
.order-expanded-panel {
  padding: 1.25rem;
  background: #faf7f8;
  border-top: 1px dashed #e7d8df;
}

.expanded-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.expanded-col {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.expanded-heading {
  margin: 0 0 6px 0;
  font-size: 0.88rem;
  font-weight: 700;
  color: #1e293b;
}

.address-preview-text {
  font-size: 0.84rem;
  line-height: 1.5;
  color: #334155;
  background: #ffffff;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #ede4ea;
}

.breakdown-lines {
  background: #ffffff;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #ede4ea;
  display: flex;
  flex-direction: column;
  gap: 5px;
  font-size: 0.84rem;
}

.breakdown-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #475569;
}

.breakdown-line.discount {
  color: #059669;
  font-weight: 600;
}

.breakdown-line.total-line {
  border-top: 1px solid #e2e8f0;
  padding-top: 6px;
  margin-top: 2px;
  color: #1e293b;
}

@media (max-width: 640px) {
  .order-card-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .order-card-footer {
    flex-direction: column;
    align-items: flex-start;
  }

  .order-actions-cluster {
    width: 100%;
    justify-content: flex-start;
  }

  .expanded-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>
