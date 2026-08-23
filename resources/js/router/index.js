import { createRouter, createWebHistory } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import Home from '../views/storefront/Home.vue';
import Catalog from '../views/storefront/Catalog.vue';
import ProductDetail from '../views/storefront/ProductDetail.vue';
import Cart from '../views/storefront/Cart.vue';
import Checkout from '../views/storefront/Checkout.vue';
import MyAccount from '../views/storefront/MyAccount.vue';
import AboutUs from '../views/storefront/AboutUs.vue';
import ContactUs from '../views/storefront/ContactUs.vue';
import PrivacyPolicy from '../views/storefront/PrivacyPolicy.vue';
import TermsConditions from '../views/storefront/TermsConditions.vue';
import RefundPolicy from '../views/storefront/RefundPolicy.vue';
import ShippingPolicy from '../views/storefront/ShippingPolicy.vue';
import FAQ from '../views/storefront/FAQ.vue';
import SignIn from '../views/storefront/SignIn.vue';
import Dashboard from '../views/admin/Dashboard.vue';
import CategoryList from '../views/admin/CategoryList.vue';
import TagList from '../views/admin/TagList.vue';
import ColorList from '../views/admin/ColorList.vue';
import SizeMasterList from '../views/admin/SizeMasterList.vue';
import ProductList from '../views/admin/ProductList.vue';
import ProductForm from '../views/admin/ProductForm.vue';
import InwardList from '../views/admin/InwardList.vue';
import InwardForm from '../views/admin/InwardForm.vue';
import InventoryList from '../views/admin/InventoryList.vue';
import StockMatrixEntry from '../views/admin/StockMatrixEntry.vue';
import PurchaseOrderList from '../views/admin/PurchaseOrderList.vue';
import PurchaseOrderForm from '../views/admin/PurchaseOrderForm.vue';
import CustomerList from '../views/admin/CustomerList.vue';
import CustomerProfile from '../views/admin/CustomerProfile.vue';
import OrderList from '../views/admin/OrderList.vue';
import OrderDetail from '../views/admin/OrderDetail.vue';
import CouponList from '../views/admin/CouponList.vue';
import CouponForm from '../views/admin/CouponForm.vue';
import ReturnList from '../views/admin/ReturnList.vue';
import ReturnDetail from '../views/admin/ReturnDetail.vue';
import ReportDashboard from '../views/admin/ReportDashboard.vue';
import BlogPostList from '../views/admin/BlogPostList.vue';
import BlogPostForm from '../views/admin/BlogPostForm.vue';
import UserList from '../views/admin/UserList.vue';
import UserForm from '../views/admin/UserForm.vue';
import RoleList from '../views/admin/RoleList.vue';
import RoleForm from '../views/admin/RoleForm.vue';
import SettingDashboard from '../views/admin/SettingDashboard.vue';
import AuditLogList from '../views/admin/AuditLogList.vue';
import InstagramReelList from '../views/admin/InstagramReelList.vue';
import ReviewList from '../views/admin/ReviewList.vue';
import CourierList from '../views/admin/CourierList.vue';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/',
    component: CustomerLayout,
    children: [
      {
        path: '',
        name: 'storefront.home',
        component: Home,
      },
      {
        path: 'shop',
        name: 'storefront.shop',
        component: Catalog,
      },
      {
        path: 'products/:uuid',
        name: 'storefront.product.detail',
        component: ProductDetail,
      },
      {
        path: 'cart',
        name: 'storefront.cart',
        component: Cart,
      },
      {
        path: 'checkout',
        name: 'storefront.checkout',
        component: Checkout,
      },
      {
        path: 'my-account',
        name: 'storefront.my_account',
        component: MyAccount,
      },
      {
        path: 'about-us',
        name: 'storefront.about_us',
        component: AboutUs,
      },
      {
        path: 'contact-us',
        name: 'storefront.contact_us',
        component: ContactUs,
      },
      {
        path: 'privacy-policy',
        name: 'storefront.privacy_policy',
        component: PrivacyPolicy,
      },
      {
        path: 'terms-conditions',
        name: 'storefront.terms_conditions',
        component: TermsConditions,
      },
      {
        path: 'refund-policy',
        name: 'storefront.refund_policy',
        component: RefundPolicy,
      },
      {
        path: 'shipping-policy',
        name: 'storefront.shipping_policy',
        component: ShippingPolicy,
      },
      {
        path: 'faq',
        name: 'storefront.faq',
        component: FAQ,
      },
    ]
  },
  {
    path: '/login',
    name: 'storefront.login',
    component: SignIn,
  },
  {
    path: '/sign-in',
    redirect: '/login'
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'admin.dashboard',
        component: Dashboard,
      },
      {
        path: 'orders',
        name: 'admin.orders',
        component: OrderList,
        meta: { permission: 'orders' },
      },
      {
        path: 'orders/:id',
        name: 'admin.orders.show',
        component: OrderDetail,
        meta: { permission: 'orders' },
      },
      {
        path: 'returns',
        name: 'admin.returns',
        component: ReturnList,
        meta: { permission: 'returns' },
      },
      {
        path: 'returns/:id',
        name: 'admin.returns.show',
        component: ReturnDetail,
        meta: { permission: 'returns' },
      },
      {
        path: 'reports',
        name: 'admin.reports',
        component: ReportDashboard,
        meta: { permission: 'reports' },
      },
      {
        path: 'audit-logs',
        name: 'admin.audit-logs',
        component: AuditLogList,
        meta: { permission: 'settings' },
      },
      {
        path: 'settings',
        name: 'admin.settings',
        component: SettingDashboard,
        meta: { permission: 'settings' },
      },
      {
        path: 'users',
        name: 'admin.users',
        component: UserList,
        meta: { permission: 'users' },
      },
      {
        path: 'users/create',
        name: 'admin.users.create',
        component: UserForm,
        meta: { permission: 'users' },
      },
      {
        path: 'users/:id/edit',
        name: 'admin.users.edit',
        component: UserForm,
        meta: { permission: 'users' },
      },
      {
        path: 'roles',
        name: 'admin.roles',
        component: RoleList,
        meta: { permission: 'roles' },
      },
      {
        path: 'roles/create',
        name: 'admin.roles.create',
        component: RoleForm,
        meta: { permission: 'roles' },
      },
      {
        path: 'roles/:id/edit',
        name: 'admin.roles.edit',
        component: RoleForm,
        meta: { permission: 'roles' },
      },
      {
        path: 'blog/posts',
        name: 'admin.blog.posts',
        component: BlogPostList,
        meta: { permission: 'blog' },
      },
      {
        path: 'blog/posts/create',
        name: 'admin.blog.posts.create',
        component: BlogPostForm,
        meta: { permission: 'blog' },
      },
      {
        path: 'blog/posts/:id/edit',
        name: 'admin.blog.posts.edit',
        component: BlogPostForm,
        meta: { permission: 'blog' },
      },
      {
        path: 'couriers',
        name: 'admin.couriers',
        component: CourierList,
        meta: { permission: 'couriers' },
      },
      {
        path: 'coupons',
        name: 'admin.coupons',
        component: CouponList,
        meta: { permission: 'coupons' },
      },
      {
        path: 'coupons/create',
        name: 'admin.coupons.create',
        component: CouponForm,
        meta: { permission: 'coupons' },
      },
      {
        path: 'coupons/:id/edit',
        name: 'admin.coupons.edit',
        component: CouponForm,
        meta: { permission: 'coupons' },
      },
      {
        path: 'instagram-reels',
        name: 'admin.instagram-reels',
        component: InstagramReelList,
        meta: { permission: 'reels' },
      },
      {
        path: 'categories',
        name: 'admin.categories',
        component: CategoryList,
        meta: { permission: 'categories' },
      },
      {
        path: 'tags',
        name: 'admin.tags',
        component: TagList,
        meta: { permission: 'tags' },
      },
      {
        path: 'colors',
        name: 'admin.colors',
        component: ColorList,
        meta: { permission: 'colors' },
      },
      {
        path: 'size-masters',
        name: 'admin.size-masters',
        component: SizeMasterList,
        meta: { permission: 'sizes' },
      },
      {
        path: 'products',
        name: 'admin.products',
        component: ProductList,
        meta: { permission: 'products' },
      },
      {
        path: 'products/create',
        name: 'admin.products.create',
        component: ProductForm,
        meta: { permission: 'products' },
      },
      {
        path: 'products/:id/edit',
        name: 'admin.products.edit',
        component: ProductForm,
        meta: { permission: 'products' },
      },
      {
        path: 'reviews',
        name: 'admin.reviews',
        component: ReviewList,
        meta: { permission: 'reviews' },
      },
      {
        path: 'inward',
        name: 'admin.inward',
        component: InwardList,
        meta: { permission: 'inward' },
      },
      {
        path: 'inward/create',
        name: 'admin.inward.create',
        component: InwardForm,
        meta: { permission: 'inward' },
      },
      {
        path: 'inventory',
        name: 'admin.inventory',
        component: InventoryList,
        meta: { permission: 'inward' },
      },
      {
        path: 'stock-entry',
        name: 'admin.stock-entry',
        component: StockMatrixEntry,
        meta: { permission: 'inward' },
      },
      {
        path: 'purchase-orders',
        name: 'admin.purchase-orders',
        component: PurchaseOrderList,
        meta: { permission: 'purchase_orders' },
      },
      {
        path: 'purchase-orders/create',
        name: 'admin.purchase-orders.create',
        component: PurchaseOrderForm,
        meta: { permission: 'purchase_orders' },
      },
      {
        path: 'purchase-orders/:id/edit',
        name: 'admin.purchase-orders.edit',
        component: PurchaseOrderForm,
        meta: { permission: 'purchase_orders' },
      },
      {
        path: 'purchase-orders/:id/receive',
        name: 'admin.purchase-orders.receive',
        component: PurchaseOrderForm,
        meta: { permission: 'purchase_orders' },
      },
      {
        path: 'customers',
        name: 'admin.customers',
        component: CustomerList,
        meta: { permission: 'customers' },
      },
      {
        path: 'customers/:id',
        name: 'admin.customers.show',
        component: CustomerProfile,
        meta: { permission: 'customers' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  },
});

router.beforeEach(async (to, from, next) => {
  const isAuthenticated = localStorage.getItem('auth_token');
  
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated) {
      return next({ name: 'storefront.login' });
    }

    // Check permission restrictions for specific admin areas
    const requiredPermission = to.meta.permission;
    if (requiredPermission) {
      try {
        const authStore = useAuthStore();
        if (!authStore.user) {
          await authStore.fetchUser();
        }

        if (authStore.user) {
          const isSuperAdmin = 
            authStore.user.roles?.some(r => r.name === 'super_admin') || 
            authStore.user.role_id === 1;

          if (!isSuperAdmin) {
            // Check if user has permission
            const userPermissions = authStore.user.roles?.flatMap(r => r.permissions?.map(p => p.name) || []) || [];
            
            // Check if user has explicit permission, or any permission matching this module (e.g. products.view, products.create)
            const hasAccess = 
              userPermissions.includes(requiredPermission) ||
              userPermissions.some(p => p.startsWith(requiredPermission + '.') || p === requiredPermission);

            if (!hasAccess) {
              console.warn(`[RBAC] Access denied to ${to.path}. Required module permission: ${requiredPermission}`);
              return next({ path: '/admin' });
            }
          }
        }
      } catch (err) {
        console.error('RBAC permission evaluation failed:', err);
      }
    }

    next();
  } else {
    next();
  }
});

export default router;
