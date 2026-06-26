import { createRouter, createWebHistory } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';
import Dashboard from '../views/admin/Dashboard.vue';
import CategoryList from '../views/admin/CategoryList.vue';
import BrandList from '../views/admin/BrandList.vue';
import TagList from '../views/admin/TagList.vue';
import ProductList from '../views/admin/ProductList.vue';
import ProductForm from '../views/admin/ProductForm.vue';
import InventoryList from '../views/admin/InventoryList.vue';
import PurchaseOrderList from '../views/admin/PurchaseOrderList.vue';
import PurchaseOrderForm from '../views/admin/PurchaseOrderForm.vue';
import CustomerList from '../views/admin/CustomerList.vue';
import CustomerProfile from '../views/admin/CustomerProfile.vue';

const routes = [
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: '',
        name: 'admin.dashboard',
        component: Dashboard,
      },
      {
        path: 'categories',
        name: 'admin.categories',
        component: CategoryList,
      },
      {
        path: 'brands',
        name: 'admin.brands',
        component: BrandList,
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
    redirect: '/admin',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
