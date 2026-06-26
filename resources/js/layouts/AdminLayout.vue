<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar__brand">
        <span>Aesthetic</span><span class="sidebar__brand-accent">Cart</span>
      </div>
      <nav class="sidebar__nav">
        <router-link to="/admin" class="sidebar__link" active-class="sidebar__link--active" exact>
          <span class="sidebar__link-icon">📊</span>
          <span class="sidebar__link-text">Dashboard</span>
        </router-link>
        
        <router-link to="/admin/categories" class="sidebar__link" active-class="sidebar__link--active">
          <span class="sidebar__link-icon">📁</span>
          <span class="sidebar__link-text">Categories</span>
        </router-link>
        
        <router-link to="/admin/products" class="sidebar__link" active-class="sidebar__link--active">
          <span class="sidebar__link-icon">🛍️</span>
          <span class="sidebar__link-text">Products</span>
        </router-link>

        <router-link to="/admin/inventory" class="sidebar__link" active-class="sidebar__link--active">
          <span class="sidebar__link-icon">📦</span>
          <span class="sidebar__link-text">Inventory</span>
        </router-link>

        <router-link to="/admin/purchase-orders" class="sidebar__link" active-class="sidebar__link--active">
          <span class="sidebar__link-icon">📋</span>
          <span class="sidebar__link-text">Purchase Orders</span>
        </router-link>

        <router-link to="/admin/customers" class="sidebar__link" active-class="sidebar__link--active">
          <span class="sidebar__link-icon">👤</span>
          <span class="sidebar__link-text">Customers</span>
        </router-link>
        
        <router-link to="/admin/brands" class="sidebar__link" active-class="sidebar__link--active">
          <span class="sidebar__link-icon">🏷️</span>
          <span class="sidebar__link-text">Brands</span>
        </router-link>
        
        <router-link to="/admin/tags" class="sidebar__link" active-class="sidebar__link--active">
          <span class="sidebar__link-icon">🏷️</span>
          <span class="sidebar__link-text">Tags</span>
        </router-link>
      </nav>
      <div class="sidebar__footer">
        <span>Admin Portal v1.0</span>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-content">
      <!-- Top Header -->
      <header class="admin-header">
        <h2 class="admin-header__title">Control Center</h2>
        <div class="admin-header__actions">
          <div class="admin-header__user">
            <span class="admin-header__username">Admin Operator</span>
            <div class="admin-header__avatar">A</div>
          </div>
        </div>
      </header>
      
      <!-- Router View for Pages -->
      <main class="admin-page">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
// Admin panel layout
</script>
