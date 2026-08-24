<template>
  <div class="dashboard-wrapper">
    <!-- Brand Hero Section -->
    <div class="brand-hero-card">
      <div class="brand-hero-content">
        <div class="brand-logo-container">
          <img 
            :src="'/asset/profile/logo.png'" 
            alt="Maya Sree Fashion Logo" 
            class="brand-logo-img" 
          />
        </div>
        <div class="brand-text-container">
          <div class="brand-badge-row">
            <span class="brand-tag">Official Admin Portal</span>
            <span class="brand-status-pill">
              <span class="pulse-dot"></span>
              Storefront Online
            </span>
          </div>
          <h1 class="brand-company-title">Maya Sree Fashion</h1>
          <p class="brand-tagline">
            Premium Ethnic Wear & Readymade Stretchable Blouses
          </p>
        </div>
      </div>
    </div>

    <!-- Under Construction Banner -->
    <div class="construction-card">
      <div class="construction-glow"></div>
      <div class="construction-inner">
        <div class="construction-icon-wrap">
          <span class="construction-emoji">🚧</span>
        </div>
        <div class="construction-details">
          <div class="construction-pill-badge">
            <span class="badge-icon">⚙️</span>
            Dashboard Under Construction
          </div>
          <h2 class="construction-title">Live Analytics & Performance Metrics Coming Soon</h2>
          <p class="construction-desc">
            We are actively developing the real-time business intelligence suite, featuring advanced sales forecasting, customer cohort analysis, and revenue analytics for Maya Sree Fashion.
          </p>
          <div class="construction-meta">
            <span class="meta-item">
              <span class="meta-dot"></span>
              Core ERP & Storefront modules are fully operational.
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Navigation Hub -->
    <div class="quick-hub-section">
      <div class="section-header">
        <h2 class="section-title">Operational Management Modules</h2>
        <span class="section-subtitle">Quick access to daily store operations and management tools</span>
      </div>

      <div class="modules-grid">
        <!-- Module: Orders -->
        <router-link to="/admin/orders" class="module-card">
          <div class="module-icon-wrap module-icon--maroon">
            <span>🛍️</span>
          </div>
          <div class="module-content">
            <h3 class="module-name">Orders & Fulfillment</h3>
            <p class="module-info">Track customer orders, process COD & Online payments, print invoices, and update shipping statuses.</p>
          </div>
          <div class="module-arrow">→</div>
        </router-link>

        <!-- Module: Products -->
        <router-link to="/admin/products" class="module-card">
          <div class="module-icon-wrap module-icon--gold">
            <span>👗</span>
          </div>
          <div class="module-content">
            <h3 class="module-name">Products & Catalog</h3>
            <p class="module-info">Manage stretchable blouses, silk sarees, sizing options, MRP, selling prices, and media galleries.</p>
          </div>
          <div class="module-arrow">→</div>
        </router-link>

        <!-- Module: Stock Inward -->
        <router-link to="/admin/stock-inward" class="module-card">
          <div class="module-icon-wrap module-icon--blue">
            <span>📥</span>
          </div>
          <div class="module-content">
            <h3 class="module-name">Stock Inward</h3>
            <p class="module-info">Record incoming manufacturer inventory batches, unit costs, invoice numbers, and supplier details.</p>
          </div>
          <div class="module-arrow">→</div>
        </router-link>

        <!-- Module: Inventory -->
        <router-link to="/admin/inventory" class="module-card">
          <div class="module-icon-wrap module-icon--emerald">
            <span>📊</span>
          </div>
          <div class="module-content">
            <h3 class="module-name">Live Inventory Ledger</h3>
            <p class="module-info">Real-time SKU quantities, stock adjustments, low stock alerts, and audit transaction logs.</p>
          </div>
          <div class="module-arrow">→</div>
        </router-link>

        <!-- Module: Categories -->
        <router-link to="/admin/categories" class="module-card">
          <div class="module-icon-wrap module-icon--purple">
            <span>🏷️</span>
          </div>
          <div class="module-content">
            <h3 class="module-name">Categories & Occasions</h3>
            <p class="module-info">Configure product categories, festive occasions, styling collections, and custom badges.</p>
          </div>
          <div class="module-arrow">→</div>
        </router-link>

        <!-- Module: Settings -->
        <router-link to="/admin/settings" class="module-card">
          <div class="module-icon-wrap module-icon--slate">
            <span>⚙️</span>
          </div>
          <div class="module-content">
            <h3 class="module-name">Store & SMTP Settings</h3>
            <p class="module-info">Configure order email alerts, dynamic SMTP credentials, payment gateways, and welcome promotions.</p>
          </div>
          <div class="module-arrow">→</div>
        </router-link>
      </div>
    </div>

    <!-- Store Status Highlights -->
    <div class="system-status-card">
      <div class="status-grid">
        <div class="status-item">
          <span class="status-label">Database & Storefront</span>
          <span class="status-val status-val--online">● Operational</span>
        </div>
        <div class="status-item">
          <span class="status-label">Order Placement Notifications</span>
          <span class="status-val status-val--online">● Active</span>
        </div>
        <div class="status-item">
          <span class="status-label">Payment Gateway</span>
          <span class="status-val status-val--online">● Cashfree & COD Ready</span>
        </div>
        <div class="status-item">
          <span class="status-label">Platform Version</span>
          <span class="status-val">Maya Sree Enterprise v2.4</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Admin Dashboard for Maya Sree Fashion
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap');

.dashboard-wrapper {
  display: flex;
  flex-direction: column;
  gap: 24px;
  max-width: 1280px;
  margin: 0 auto;
  padding: 8px 0 32px 0;
  font-family: 'Poppins', sans-serif;
}

/* ==========================================================================
   BRAND HERO CARD
   ========================================================================== */
.brand-hero-card {
  background: linear-gradient(135deg, #FAF8F5 0%, #FFFFFF 100%);
  border: 1px solid rgba(212, 175, 55, 0.25);
  border-radius: 20px;
  padding: 28px 32px;
  box-shadow: 0 10px 30px rgba(91, 22, 58, 0.04), 0 2px 8px rgba(0, 0, 0, 0.02);
  position: relative;
  overflow: hidden;
}

.brand-hero-card::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 260px;
  height: 100%;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
  pointer-events: none;
}

.brand-hero-content {
  display: flex;
  align-items: center;
  gap: 24px;
}

.brand-logo-container {
  width: 90px;
  height: 90px;
  flex-shrink: 0;
  background: #FFFFFF;
  border: 1.5px solid rgba(212, 175, 55, 0.35);
  border-radius: 18px;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 20px rgba(91, 22, 58, 0.08);
}

.brand-logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.brand-text-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.brand-badge-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 2px;
}

.brand-tag {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: #5B163A;
}

.brand-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border: 1px solid rgba(16, 185, 129, 0.25);
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.72rem;
  font-weight: 600;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10B981;
  animation: dotPulse 2s infinite ease-in-out;
}

@keyframes dotPulse {
  0%, 100% { opacity: 0.4; transform: scale(0.9); }
  50% { opacity: 1; transform: scale(1.2); }
}

.brand-company-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.1rem;
  font-weight: 700;
  color: #5B163A;
  margin: 0;
  line-height: 1.15;
  letter-spacing: -0.5px;
}

.brand-tagline {
  font-size: 0.92rem;
  color: #64748B;
  margin: 0;
}

/* ==========================================================================
   UNDER CONSTRUCTION CARD
   ========================================================================== */
.construction-card {
  position: relative;
  background: linear-gradient(135deg, #5B163A 0%, #3D0E26 100%);
  border-radius: 20px;
  padding: 32px;
  color: #FAF8F5;
  box-shadow: 0 16px 40px rgba(91, 22, 58, 0.22);
  overflow: hidden;
}

.construction-glow {
  position: absolute;
  top: -80px;
  right: -60px;
  width: 260px;
  height: 260px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(212, 175, 55, 0.3) 0%, rgba(212, 175, 55, 0) 70%);
  pointer-events: none;
}

.construction-inner {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 24px;
}

.construction-icon-wrap {
  width: 68px;
  height: 68px;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1.5px solid rgba(212, 175, 55, 0.4);
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.construction-emoji {
  font-size: 2rem;
}

.construction-details {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.construction-pill-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(212, 175, 55, 0.2);
  border: 1px solid rgba(212, 175, 55, 0.5);
  color: #F7E7B4;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  width: fit-content;
}

.construction-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.55rem;
  font-weight: 600;
  color: #FFFFFF;
  margin: 0;
  line-height: 1.25;
}

.construction-desc {
  font-size: 0.92rem;
  line-height: 1.6;
  color: rgba(250, 248, 245, 0.85);
  margin: 0;
  max-width: 820px;
}

.construction-meta {
  margin-top: 4px;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  color: #F7E7B4;
  font-weight: 500;
}

.meta-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #D4AF37;
}

/* ==========================================================================
   QUICK NAVIGATION HUB SECTION
   ========================================================================== */
.quick-hub-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-header {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.3rem;
  font-weight: 600;
  color: #1E293B;
  margin: 0;
}

.section-subtitle {
  font-size: 0.82rem;
  color: #64748B;
}

.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 16px;
}

.module-card {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 16px;
  padding: 18px 20px;
  text-decoration: none;
  transition: all 0.25s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.module-card:hover {
  transform: translateY(-3px);
  border-color: rgba(212, 175, 55, 0.5);
  box-shadow: 0 12px 28px rgba(91, 22, 58, 0.08);
}

.module-icon-wrap {
  width: 52px;
  height: 52px;
  flex-shrink: 0;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.45rem;
}

.module-icon--maroon { background: rgba(91, 22, 58, 0.08); }
.module-icon--gold   { background: rgba(212, 175, 55, 0.12); }
.module-icon--blue   { background: rgba(59, 130, 246, 0.1); }
.module-icon--emerald { background: rgba(16, 185, 129, 0.1); }
.module-icon--purple { background: rgba(139, 92, 246, 0.1); }
.module-icon--slate  { background: rgba(100, 116, 139, 0.1); }

.module-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.module-name {
  font-family: 'Poppins', sans-serif;
  font-size: 0.98rem;
  font-weight: 600;
  color: #1E293B;
  margin: 0;
  transition: color 0.2s ease;
}

.module-card:hover .module-name {
  color: #5B163A;
}

.module-info {
  font-size: 0.78rem;
  color: #64748B;
  margin: 0;
  line-height: 1.45;
}

.module-arrow {
  font-size: 1.15rem;
  color: #94A3B8;
  transition: transform 0.2s ease, color 0.2s ease;
}

.module-card:hover .module-arrow {
  transform: translateX(4px);
  color: #5B163A;
}

/* ==========================================================================
   SYSTEM STATUS FOOTER CARD
   ========================================================================== */
.system-status-card {
  background: #FAF8F5;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  padding: 16px 20px;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.status-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.status-label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #64748B;
  font-weight: 600;
}

.status-val {
  font-size: 0.84rem;
  font-weight: 600;
  color: #1E293B;
}

.status-val--online {
  color: #059669;
}

/* ==========================================================================
   RESPONSIVE DESIGN
   ========================================================================== */
@media (max-width: 768px) {
  .brand-hero-card {
    padding: 20px;
  }
  .brand-hero-content {
    flex-direction: column;
    text-align: center;
    gap: 14px;
  }
  .brand-badge-row {
    justify-content: center;
  }
  .brand-company-title {
    font-size: 1.65rem;
  }
  .construction-card {
    padding: 20px;
  }
  .construction-inner {
    flex-direction: column;
    gap: 16px;
  }
  .modules-grid {
    grid-template-columns: 1fr;
  }
}
</style>
