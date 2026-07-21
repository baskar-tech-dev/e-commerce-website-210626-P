<template>
  <div :class="['admin-layout', { 'admin-layout--sidebar-collapsed': isSidebarCollapsed }]">
    
    <!-- Mobile Overlay -->
    <div v-if="!isSidebarCollapsed && isMobile" class="admin-mobile-overlay" @click="toggleSidebar"></div>

    <!-- Sidebar -->
    <aside :class="['sidebar', { 'sidebar--collapsed': isSidebarCollapsed }]">
      <div class="sidebar__brand">
        <template v-if="!isSidebarCollapsed">
          <span>Future Mind</span><span class="sidebar__brand-accent"> One</span>
        </template>
        <template v-else>
          <img :src="'/asset/about/fmonr.png'" alt="Future Mind One Logo" style="max-height: 40px; max-width: 40px; object-fit: contain; border-radius: 4px;" />
        </template>
      </div>
      
      <nav class="sidebar__nav" style="padding-right: 4px;">
        <template v-for="(groupMenus, groupName) in menuStore.menus" :key="groupName">
          <div v-if="!isSidebarCollapsed" style="font-size: 0.7rem; font-weight: 700; color: #475569; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 0.5rem; margin-top: 1.25rem; padding-left: 0.5rem;">
            {{ groupName }}
          </div>
          
          <router-link v-for="menu in groupMenus" :key="menu.id" :to="menu.path" class="sidebar__link" active-class="sidebar__link--active" :exact="menu.path === '/admin'" @click="handleMenuClick">
            <span class="sidebar__link-icon"><component :is="iconMap[menu.icon]" :size="18" /></span>
            <span class="sidebar__link-text">{{ menu.name }}</span>
          </router-link>
        </template>
        
      </nav>

      <div class="sidebar__footer" style="display: flex; flex-direction: column; gap: 0.25rem;">
        <a href="#" class="sidebar__link" style="padding: 0.25rem 0.5rem; gap: 0.5rem; margin-bottom: 0.25rem;" @click.prevent="toggleSidebar">
          <span v-if="isSidebarCollapsed"><ChevronRight :size="18" /></span>
          <span v-else><ChevronLeft :size="18" /></span>
          <span class="sidebar__link-text" v-if="!isSidebarCollapsed">Collapse</span>
        </a>
        <span v-if="!isSidebarCollapsed" style="padding-left: 0.5rem;">Admin Portal v1.0</span>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-content">
      <!-- Top Header -->
      <header class="admin-header" style="position: sticky; top: 0; z-index: 10;">
        <div style="display: flex; align-items: center; gap: var(--spacing-lg);">
          <!-- Hamburger Menu Icon -->
          <button @click="toggleSidebar" class="admin-header__icon-btn" style="font-size: 1.1rem; background: none; border: none; cursor: pointer;">
            <MenuIcon :size="20" />
          </button>
          
          <div class="admin-header__search-wrap">
            <span class="admin-header__search-icon" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"><Search :size="16" /></span>
            <input type="text" class="admin-header__search-input" placeholder="Search for anything..." />
            <span class="admin-header__search-badge">⌘ K</span>
          </div>
        </div>
        
        <div class="admin-header__actions" style="display: flex; align-items: center; gap: var(--spacing-md);">
          <!-- Notifications Bell -->
          <button class="admin-header__icon-btn">
            <Bell :size="18" />
            <span class="admin-header__badge-dot">8</span>
          </button>
          
          <!-- Message bubble -->
          <button class="admin-header__icon-btn">
            <MessageSquare :size="18" />
            <span class="admin-header__badge-dot">4</span>
          </button>
          
          <!-- Fullscreen toggle -->
          <button class="admin-header__icon-btn">
            <Maximize :size="18" />
          </button>
          
          <!-- User info with Dropdown -->
          <div class="admin-header__user" style="position: relative; margin-left: var(--spacing-sm); display: flex; align-items: center; gap: var(--spacing-sm); cursor: pointer; user-select: none;" @click="showDropdown = !showDropdown">
            <div class="admin-header__avatar" style="width: 36px; height: 36px; font-size: 0.95rem; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600;">
              {{ userInitials }}
            </div>
            <div style="display: flex; flex-direction: column; text-align: left; line-height: 1.2;">
              <span class="admin-header__username" style="font-weight: 600; font-size: 0.9rem;">{{ authStore.user?.name || 'User' }}</span>
              <span style="font-size: 0.75rem; color: #64748b; text-transform: capitalize;">{{ userRole }}</span>
            </div>
            <ChevronDown :size="14" style="color: #64748b;" />

            <!-- Dropdown Menu -->
            <div v-if="showDropdown" class="admin-user-dropdown" style="position: absolute; right: 0; top: 100%; margin-top: 8px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); min-width: 180px; z-index: 50; overflow: hidden;" @click.stop>
              <div style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                <div style="font-weight: 600; font-size: 0.85rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ authStore.user?.name || 'User' }}</div>
                <div style="font-size: 0.75rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ authStore.user?.email || '' }}</div>
              </div>
              <a href="#" @click.prevent="handleLogout" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; font-size: 0.85rem; color: #ef4444; text-decoration: none; transition: background 0.15s ease;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
                <LogOut :size="16" />
                <span>Logout</span>
              </a>
            </div>
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
import { ref, onMounted, onUnmounted, computed, markRaw } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useMenuStore } from '@/stores/menu';
import { 
  Shirt, ChevronRight, ChevronLeft, Menu as MenuIcon, Search, Bell, MessageSquare, Maximize,
  LayoutDashboard, ShoppingCart, RefreshCcw, TrendingUp, FileText, Users, Key, ShoppingBag, Folder, Package, ClipboardList, Ticket, Tag, Tags, Settings,
  ChevronDown, LogOut, Film
} from 'lucide-vue-next';

const iconMap = markRaw({
  LayoutDashboard, ShoppingCart, RefreshCcw, TrendingUp, FileText, Users, Key, ShoppingBag, Folder, Package, ClipboardList, Ticket, Tag, Tags, Settings, Film
});

const authStore = useAuthStore();
const menuStore = useMenuStore();

const userInitials = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const userRole = computed(() => {
  if (authStore.user?.roles && authStore.user.roles.length > 0) {
    return authStore.user.roles[0].name.replace('_', ' ');
  }
  return 'Admin';
});

const router = useRouter();
const showDropdown = ref(false);

const closeDropdown = (e) => {
  const el = document.querySelector('.admin-header__user');
  if (el && !el.contains(e.target)) {
    showDropdown.value = false;
  }
};

const handleLogout = async () => {
  try {
    await authStore.logout();
    router.push({ name: 'storefront.login' });
  } catch (error) {
    console.error('Logout failed:', error);
  }
};

const isMobile = ref(false);
const isSidebarCollapsed = ref(false);

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768;
  if (isMobile.value && !isSidebarCollapsed.value) {
    isSidebarCollapsed.value = true;
  }
};

onMounted(() => {
  menuStore.fetchMenus();
  checkMobile();
  window.addEventListener('resize', checkMobile);
  window.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
  window.removeEventListener('click', closeDropdown);
});

const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

const handleMenuClick = () => {
  if (isMobile.value) {
    isSidebarCollapsed.value = true;
  }
};
</script>
