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
import ProductList from '../views/admin/ProductList.vue';
import ProductForm from '../views/admin/ProductForm.vue';
import InventoryList from '../views/admin/InventoryList.vue';
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
import SettingDashboard from '../views/admin/SettingDashboard.vue';
import AuditLogList from '../views/admin/AuditLogList.vue';
import InstagramReelList from '../views/admin/InstagramReelList.vue';

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
      },
      {
        path: 'orders/:id',
        name: 'admin.orders.show',
        component: OrderDetail,
      },
      {
        path: 'returns',
        name: 'admin.returns',
        component: ReturnList,
      },
      {
        path: 'returns/:id',
        name: 'admin.returns.show',
        component: ReturnDetail,
      },
      {
        path: 'reports',
        name: 'admin.reports',
        component: ReportDashboard,
      },
      {
        path: 'audit-logs',
        name: 'admin.audit-logs',
        component: AuditLogList,
      },
      {
        path: 'settings',
        name: 'admin.settings',
        component: SettingDashboard,
      },
      {
        path: 'users',
        name: 'admin.users',
        component: UserList,
      },
      {
        path: 'users/create',
        name: 'admin.users.create',
        component: UserForm,
      },
      {
        path: 'users/:id/edit',
        name: 'admin.users.edit',
        component: UserForm,
      },
      {
        path: 'roles',
        name: 'admin.roles',
        component: RoleList,
      },
      {
        path: 'blog/posts',
        name: 'admin.blog.posts',
        component: BlogPostList,
      },
      {
        path: 'blog/posts/create',
        name: 'admin.blog.posts.create',
        component: BlogPostForm,
      },
      {
        path: 'blog/posts/:id/edit',
        name: 'admin.blog.posts.edit',
        component: BlogPostForm,
      },
      {
        path: 'coupons',
        name: 'admin.coupons',
        component: CouponList,
      },
      {
        path: 'coupons/create',
        name: 'admin.coupons.create',
        component: CouponForm,
      },
      {
        path: 'coupons/:id/edit',
        name: 'admin.coupons.edit',
        component: CouponForm,
      },
      {
        path: 'instagram-reels',
        name: 'admin.instagram-reels',
        component: InstagramReelList,
      },
      {
        path: 'categories',
        name: 'admin.categories',
        component: CategoryList,
      },
      {
        path: 'tags',
        name: 'admin.tags',
        component: TagList,
      },
      {
        path: 'products',
        name: 'admin.products',
        component: ProductList,
      },
      {
        path: 'products/create',
        name: 'admin.products.create',
        component: ProductForm,
      },
      {
        path: 'products/:id/edit',
        name: 'admin.products.edit',
        component: ProductForm,
      },
      {
        path: 'inventory',
        name: 'admin.inventory',
        component: InventoryList,
      },
      {
        path: 'purchase-orders',
        name: 'admin.purchase-orders',
        component: PurchaseOrderList,
      },
      {
        path: 'purchase-orders/create',
        name: 'admin.purchase-orders.create',
        component: PurchaseOrderForm,
      },
      {
        path: 'purchase-orders/:id/edit',
        name: 'admin.purchase-orders.edit',
        component: PurchaseOrderForm,
      },
      {
        path: 'purchase-orders/:id/receive',
        name: 'admin.purchase-orders.receive',
        component: PurchaseOrderForm,
      },
      {
        path: 'customers',
        name: 'admin.customers',
        component: CustomerList,
      },
      {
        path: 'customers/:id',
        name: 'admin.customers.show',
        component: CustomerProfile,
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

router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('auth_token'); // Simple check for now
  
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated) {
      next({ name: 'storefront.login' });
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
