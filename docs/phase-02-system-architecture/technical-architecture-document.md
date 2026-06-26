# Phase 2: Technical Architecture Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** Senior Solution Architect
**Status:** Draft

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Technology Stack](#2-technology-stack)
3. [Frontend Architecture](#3-frontend-architecture)
4. [Backend Architecture](#4-backend-architecture)
5. [API Architecture](#5-api-architecture)
6. [Database Architecture](#6-database-architecture)
7. [Caching Architecture](#7-caching-architecture)
8. [Queue Architecture](#8-queue-architecture)
9. [Event-Driven Architecture](#9-event-driven-architecture)
10. [File Storage Architecture](#10-file-storage-architecture)
11. [Search Architecture](#11-search-architecture)
12. [Deployment Architecture](#12-deployment-architecture)
13. [Folder Structure](#13-folder-structure)
14. [Module Dependency Map](#14-module-dependency-map)

---

## 1. Architecture Overview

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                                │
│  ┌───────────┐  ┌───────────┐  ┌───────────┐  ┌───────────┐       │
│  │  Browser  │  │  Mobile   │  │   SEO     │  │  Admin    │       │
│  │  (Vue 3   │  │  Browser  │  │  Crawler  │  │  Panel    │       │
│  │   SPA)    │  │  (PWA)    │  │           │  │  (Vue 3)  │       │
│  └─────┬─────┘  └─────┬─────┘  └─────┬─────┘  └─────┬─────┘       │
│        │              │              │              │               │
└────────┼──────────────┼──────────────┼──────────────┼───────────────┘
         │              │              │              │
         ▼              ▼              ▼              ▼
┌─────────────────────────────────────────────────────────────────────┐
│                          CDN / NGINX                                │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  SSL Termination → Static Assets → Reverse Proxy → Gzip    │   │
│  └──────────────────────────────────────────────────────────────┘   │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                       APPLICATION LAYER                             │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │                    Laravel 12 API                            │   │
│  │  ┌────────────┐  ┌────────────┐  ┌────────────┐            │   │
│  │  │ Middleware  │→│ Controllers │→│  Services   │            │   │
│  │  │ (Auth,     │  │ (Request   │  │ (Business   │            │   │
│  │  │  CORS,     │  │  Handling) │  │  Logic)     │            │   │
│  │  │  Rate      │  └────────────┘  └──────┬─────┘            │   │
│  │  │  Limit)    │                         │                   │   │
│  │  └────────────┘                         ▼                   │   │
│  │                              ┌────────────────┐             │   │
│  │                              │  Repositories  │             │   │
│  │                              │ (Data Access)  │             │   │
│  │                              └───────┬────────┘             │   │
│  └──────────────────────────────────────┼──────────────────────┘   │
└─────────────────────────────────────────┼──────────────────────────┘
                                          │
                    ┌─────────────────────┼──────────────────────┐
                    │                     │                      │
                    ▼                     ▼                      ▼
┌──────────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│     MySQL 8          │  │     Redis         │  │    AWS S3        │
│  ┌───────────────┐   │  │  ┌────────────┐  │  │  ┌────────────┐ │
│  │ Primary DB    │   │  │  │ Cache      │  │  │  │ Product    │ │
│  │ (Read/Write)  │   │  │  │ Sessions   │  │  │  │ Images     │ │
│  ├───────────────┤   │  │  │ Queues     │  │  │  │ Invoices   │ │
│  │ Read Replica  │   │  │  │ Rate Limit │  │  │  │ Uploads    │ │
│  │ (Future)      │   │  │  └────────────┘  │  │  └────────────┘ │
│  └───────────────┘   │  └──────────────────┘  └──────────────────┘
└──────────────────────┘
         │
         ▼
┌──────────────────────┐
│    Meilisearch       │
│  ┌───────────────┐   │
│  │ Product Index │   │
│  │ Blog Index    │   │
│  └───────────────┘   │
└──────────────────────┘
```

### Architecture Principles

| Principle | Implementation |
|-----------|----------------|
| **Separation of Concerns** | Controller → Service → Repository → Model |
| **Single Responsibility** | Each class has one reason to change |
| **Dependency Injection** | Laravel IoC container for all bindings |
| **Interface Segregation** | Repository interfaces for swappable implementations |
| **Event-Driven** | Domain events for cross-cutting concerns |
| **Stateless API** | No server-side sessions; token-based auth (Sanctum) |
| **Fail Gracefully** | Graceful error handling with structured responses |
| **Convention Over Configuration** | Follow Laravel conventions where possible |

---

## 2. Technology Stack

### Core Stack

| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| Frontend Framework | Vue 3 | 3.5+ | SPA with Composition API |
| State Management | Pinia | 2.x | Global state management |
| Routing | Vue Router | 4.x | Client-side routing |
| HTTP Client | Axios | 1.x | API communication |
| UI Components | Custom | — | Design system components |
| CSS | Vanilla CSS | — | Custom properties + BEM |
| Build Tool | Vite | 6.x | Frontend build & HMR |
| Backend Framework | Laravel | 12.x | REST API + Business Logic |
| Auth | Laravel Sanctum | 4.x | Token-based API auth |
| Database | MySQL | 8.0+ | Primary data store |
| Cache | Redis | 7.x | Caching + Queues + Sessions |
| Search | Meilisearch | 1.x | Full-text product search |
| File Storage | AWS S3 | — | Images, documents |
| Email | Mailgun | — | Transactional emails |
| SMS | Twilio | — | SMS notifications |
| Payment | Razorpay | — | Primary payment gateway |
| Payment (Intl) | Stripe | — | International payments |

### Development Tools

| Tool | Purpose |
|------|---------|
| Laravel Pint | PHP code formatting |
| PHPStan / Larastan | Static analysis |
| Pest PHP | Testing framework |
| Laravel Telescope | Debug assistant (dev only) |
| Laravel Horizon | Queue monitoring |
| ESLint | JavaScript linting |
| Prettier | Code formatting |

---

## 3. Frontend Architecture

### 3.1 Vue 3 SPA Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Vue 3 Application                     │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐          │
│  │  Router  │    │  Pinia   │    │   Axios  │          │
│  │          │    │  Stores  │    │ Instance │          │
│  └────┬─────┘    └────┬─────┘    └────┬─────┘          │
│       │               │               │                 │
│       ▼               ▼               ▼                 │
│  ┌────────────────────────────────────────────────┐     │
│  │                 App Layout                      │     │
│  │  ┌────────────┐  ┌──────────┐  ┌────────────┐ │     │
│  │  │  Customer  │  │  Admin   │  │   Auth     │ │     │
│  │  │  Layout    │  │  Layout  │  │   Layout   │ │     │
│  │  └────────────┘  └──────────┘  └────────────┘ │     │
│  └────────────────────────────────────────────────┘     │
│       │                                                 │
│       ▼                                                 │
│  ┌────────────────────────────────────────────────┐     │
│  │                   Pages                         │     │
│  │  (Lazy-loaded route-level components)           │     │
│  └────────────────────────────────────────────────┘     │
│       │                                                 │
│       ▼                                                 │
│  ┌────────────────────────────────────────────────┐     │
│  │              UI Components                      │     │
│  │  ┌────┐ ┌────┐ ┌─────┐ ┌─────┐ ┌──────┐      │     │
│  │  │Base│ │Form│ │Modal│ │Table│ │Layout│      │     │
│  │  └────┘ └────┘ └─────┘ └─────┘ └──────┘      │     │
│  └────────────────────────────────────────────────┘     │
│       │                                                 │
│       ▼                                                 │
│  ┌────────────────────────────────────────────────┐     │
│  │              Composables                        │     │
│  │  useAuth, useCart, useProduct, useNotification  │     │
│  └────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────┘
```

### 3.2 State Management (Pinia Stores)

| Store | State | Actions |
|-------|-------|---------|
| `useAuthStore` | user, token, isAuthenticated | login, register, logout, fetchProfile |
| `useCartStore` | items, total, coupon, itemCount | addItem, removeItem, updateQuantity, applyCoupon, clearCart |
| `useProductStore` | products, filters, pagination, currentProduct | fetchProducts, fetchProduct, search, filter |
| `useCategoryStore` | categories, activeCategory | fetchCategories, setActive |
| `useWishlistStore` | items | add, remove, fetch |
| `useOrderStore` | orders, currentOrder | fetchOrders, fetchOrder, placeOrder, cancelOrder |
| `useCheckoutStore` | address, shippingMethod, paymentMethod | setAddress, setShipping, setPayment, processCheckout |
| `useNotificationStore` | notifications, unreadCount | fetch, markRead, markAllRead |
| `useUIStore` | sidebarOpen, modalOpen, loading, toast | toggleSidebar, showModal, showToast |

### 3.3 Routing Strategy

```
Customer Routes (Public + Auth)
├── /                           → HomePage
├── /category/:slug             → CategoryPage
├── /products                   → ProductListPage
├── /product/:slug              → ProductDetailPage
├── /cart                       → CartPage
├── /checkout                   → CheckoutPage (auth required)
├── /wishlist                   → WishlistPage (auth required)
├── /account                    → AccountLayout (auth required)
│   ├── /account/profile        → ProfilePage
│   ├── /account/orders         → OrderListPage
│   ├── /account/orders/:id     → OrderDetailPage
│   ├── /account/addresses      → AddressPage
│   └── /account/wishlist       → WishlistPage
├── /blog                       → BlogListPage
├── /blog/:slug                 → BlogDetailPage
├── /about                      → AboutPage
├── /contact                    → ContactPage
├── /track-order                → TrackOrderPage
├── /login                      → LoginPage
├── /register                   → RegisterPage
├── /forgot-password            → ForgotPasswordPage
└── /reset-password/:token      → ResetPasswordPage

Admin Routes (Auth + Admin Role)
├── /admin                      → DashboardPage
├── /admin/products             → ProductListPage
│   ├── /admin/products/create  → ProductFormPage
│   └── /admin/products/:id/edit → ProductFormPage
├── /admin/categories           → CategoryListPage
├── /admin/brands               → BrandListPage
├── /admin/inventory            → InventoryPage
├── /admin/orders               → OrderListPage
│   └── /admin/orders/:id       → OrderDetailPage
├── /admin/customers            → CustomerListPage
│   └── /admin/customers/:id    → CustomerDetailPage
├── /admin/coupons              → CouponListPage
│   ├── /admin/coupons/create   → CouponFormPage
│   └── /admin/coupons/:id/edit → CouponFormPage
├── /admin/reports              → ReportsPage
│   ├── /admin/reports/sales    → SalesReportPage
│   ├── /admin/reports/inventory → InventoryReportPage
│   └── /admin/reports/customers → CustomerReportPage
├── /admin/blogs                → BlogListPage
│   ├── /admin/blogs/create     → BlogFormPage
│   └── /admin/blogs/:id/edit   → BlogFormPage
├── /admin/settings             → SettingsPage
│   ├── /admin/settings/general → GeneralSettingsPage
│   ├── /admin/settings/tax     → TaxSettingsPage
│   ├── /admin/settings/shipping → ShippingSettingsPage
│   ├── /admin/settings/payment → PaymentSettingsPage
│   ├── /admin/settings/email   → EmailSettingsPage
│   ├── /admin/settings/sms     → SmsSettingsPage
│   └── /admin/settings/users   → UserManagementPage
└── /admin/roles                → RoleManagementPage
```

### 3.4 Component Architecture

```
Components are organized by domain and reusability:

components/
├── base/                  ← Atomic design system components
│   ├── BaseButton.vue
│   ├── BaseInput.vue
│   ├── BaseSelect.vue
│   ├── BaseTextarea.vue
│   ├── BaseCheckbox.vue
│   ├── BaseRadio.vue
│   ├── BaseModal.vue
│   ├── BaseTable.vue
│   ├── BaseCard.vue
│   ├── BaseBadge.vue
│   ├── BaseAlert.vue
│   ├── BaseSpinner.vue
│   ├── BasePagination.vue
│   ├── BaseDropdown.vue
│   ├── BaseTabs.vue
│   ├── BaseToast.vue
│   ├── BaseAvatar.vue
│   └── BaseEmptyState.vue
│
├── layout/                ← Layout components
│   ├── CustomerLayout.vue
│   ├── AdminLayout.vue
│   ├── AuthLayout.vue
│   ├── CustomerHeader.vue
│   ├── CustomerFooter.vue
│   ├── AdminSidebar.vue
│   ├── AdminHeader.vue
│   └── MobileNav.vue
│
├── product/               ← Product-specific components
│   ├── ProductCard.vue
│   ├── ProductGrid.vue
│   ├── ProductGallery.vue
│   ├── ProductInfo.vue
│   ├── ProductVariantSelector.vue
│   ├── ProductReviews.vue
│   ├── ProductFilter.vue
│   ├── ProductSort.vue
│   ├── ProductSearch.vue
│   └── QuickView.vue
│
├── cart/                  ← Cart components
│   ├── CartItem.vue
│   ├── CartSummary.vue
│   ├── CartDrawer.vue
│   └── CouponInput.vue
│
├── checkout/              ← Checkout components
│   ├── AddressForm.vue
│   ├── AddressSelector.vue
│   ├── ShippingMethodSelector.vue
│   ├── PaymentMethodSelector.vue
│   ├── OrderSummary.vue
│   └── CheckoutSteps.vue
│
├── order/                 ← Order components
│   ├── OrderCard.vue
│   ├── OrderTimeline.vue
│   ├── OrderItems.vue
│   └── ReturnForm.vue
│
├── admin/                 ← Admin-specific components
│   ├── StatsCard.vue
│   ├── DataTable.vue
│   ├── FilterPanel.vue
│   ├── ChartWidget.vue
│   ├── ImageUploader.vue
│   ├── RichTextEditor.vue
│   └── ActivityLog.vue
│
└── common/                ← Shared components
    ├── HeroBanner.vue
    ├── CategoryCard.vue
    ├── BreadcrumbNav.vue
    ├── RatingStars.vue
    ├── PriceDisplay.vue
    ├── StockBadge.vue
    ├── SocialShare.vue
    ├── NewsletterForm.vue
    └── CookieConsent.vue
```

### 3.5 Axios Configuration

```
API Client Setup:
├── Base URL: /api/v1/
├── Interceptors:
│   ├── Request: Attach Bearer token, set Accept header
│   └── Response: Handle 401 (redirect to login), 422 (validation errors), 500 (error toast)
├── CSRF: Automatic via Sanctum cookie
└── Retry: Exponential backoff for 5xx errors (max 3 retries)
```

### 3.6 SEO Strategy for SPA

| Technique | Implementation |
|-----------|----------------|
| Pre-rendering | Use `vite-plugin-ssr` or `@vueuse/head` for meta tags |
| Dynamic Meta | `useHead()` composable for per-page title, description, OG |
| Structured Data | JSON-LD injected via `useHead()` for Product, Breadcrumb schemas |
| Sitemap | Server-generated sitemap at `/sitemap.xml` |
| SSR (Optional) | Nuxt 3 migration path if SPA SEO insufficient |
| Prerender Routes | Pre-render critical pages: /, /category/*, /product/* |

---

## 4. Backend Architecture

### 4.1 Laravel Service Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                     REQUEST LIFECYCLE                             │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  HTTP Request                                                    │
│       │                                                          │
│       ▼                                                          │
│  ┌──────────────────────────┐                                    │
│  │      Middleware Stack    │                                    │
│  │  ┌────────────────────┐  │                                    │
│  │  │ CORS               │  │                                    │
│  │  │ Rate Limiting      │  │                                    │
│  │  │ Authentication     │  │                                    │
│  │  │ Localization       │  │                                    │
│  │  │ Request Logging    │  │                                    │
│  │  └────────────────────┘  │                                    │
│  └────────────┬─────────────┘                                    │
│               │                                                  │
│               ▼                                                  │
│  ┌──────────────────────────┐                                    │
│  │       Controller         │  ← Thin layer, delegates to       │
│  │  - Validate request      │    service                         │
│  │  - Call service           │                                    │
│  │  - Return response        │                                    │
│  └────────────┬─────────────┘                                    │
│               │                                                  │
│               ▼                                                  │
│  ┌──────────────────────────┐                                    │
│  │     Form Request         │  ← Validation rules               │
│  │  - Authorization         │                                    │
│  │  - Validation rules      │                                    │
│  │  - Custom messages       │                                    │
│  └────────────┬─────────────┘                                    │
│               │                                                  │
│               ▼                                                  │
│  ┌──────────────────────────┐                                    │
│  │      Service Layer       │  ← Business logic                 │
│  │  - Business rules        │                                    │
│  │  - Orchestration          │                                    │
│  │  - Transaction management │                                    │
│  │  - Event dispatching      │                                    │
│  └────────────┬─────────────┘                                    │
│               │                                                  │
│               ▼                                                  │
│  ┌──────────────────────────┐                                    │
│  │    Repository Layer      │  ← Data access                    │
│  │  - Query building        │                                    │
│  │  - Caching               │                                    │
│  │  - Data transformation    │                                    │
│  └────────────┬─────────────┘                                    │
│               │                                                  │
│               ▼                                                  │
│  ┌──────────────────────────┐                                    │
│  │     Eloquent Model       │  ← Data layer                    │
│  │  - Relationships         │                                    │
│  │  - Scopes                │                                    │
│  │  - Accessors/Mutators    │                                    │
│  │  - Casts                  │                                    │
│  └────────────┬─────────────┘                                    │
│               │                                                  │
│               ▼                                                  │
│  ┌──────────────────────────┐                                    │
│  │    API Resource          │  ← Response transformation        │
│  │  - Data formatting       │                                    │
│  │  - Conditional fields    │                                    │
│  │  - Nested resources      │                                    │
│  └──────────────────────────┘                                    │
└──────────────────────────────────────────────────────────────────┘
```

### 4.2 Design Patterns

| Pattern | Usage | Example |
|---------|-------|---------|
| **Repository Pattern** | Data access abstraction | `ProductRepository implements ProductRepositoryInterface` |
| **Service Layer** | Business logic encapsulation | `OrderService` handles order placement logic |
| **Strategy Pattern** | Payment gateway selection | `PaymentGatewayFactory` returns Razorpay or Stripe |
| **Observer Pattern** | Model lifecycle hooks | `OrderObserver` for status change side effects |
| **Event/Listener** | Decoupled side effects | `OrderPlaced` event → SendConfirmationEmail listener |
| **Pipeline** | Sequential processing | `CouponValidationPipeline` with multiple checks |
| **Factory Pattern** | Object creation | `InvoiceFactory` creates invoices from orders |
| **DTO (Data Transfer Object)** | Type-safe data passing | `CreateProductDTO` for service method params |
| **Action Pattern** | Single-purpose classes | `PlaceOrderAction`, `ProcessRefundAction` |

### 4.3 Service Provider Architecture

| Provider | Purpose |
|----------|---------|
| `AppServiceProvider` | General bindings, model configurations |
| `RepositoryServiceProvider` | Bind repository interfaces to implementations |
| `EventServiceProvider` | Register events and listeners |
| `PaymentServiceProvider` | Payment gateway bindings |
| `SearchServiceProvider` | Meilisearch / Scout configuration |
| `NotificationServiceProvider` | Notification channel configuration |

### 4.4 Middleware Stack

| Middleware | Purpose | Applied To |
|------------|---------|------------|
| `Authenticate` | Verify Sanctum token | Protected routes |
| `EnsureAdmin` | Verify admin role | Admin routes |
| `CheckPermission` | Verify specific permission | Granular access |
| `RateLimiter` | Throttle requests | All API routes |
| `SetLocale` | Set language from header/cookie | All routes |
| `LogRequest` | Log API requests for audit | All routes |
| `TransformResponse` | Consistent API response format | All routes |
| `HandleCors` | CORS headers | All API routes |

### 4.5 Exception Handling

```
Consistent API Error Response Format:

{
    "success": false,
    "message": "Human-readable error message",
    "errors": {
        "field_name": ["Validation error 1", "Validation error 2"]
    },
    "error_code": "VALIDATION_ERROR",
    "trace_id": "uuid-for-debugging"
}

HTTP Status Code Mapping:
├── 200 OK — Successful read/update
├── 201 Created — Successful creation
├── 204 No Content — Successful deletion
├── 400 Bad Request — Invalid request format
├── 401 Unauthorized — Not authenticated
├── 403 Forbidden — Not authorized
├── 404 Not Found — Resource not found
├── 409 Conflict — Business rule violation
├── 422 Unprocessable Entity — Validation failure
├── 429 Too Many Requests — Rate limited
└── 500 Internal Server Error — Unexpected error
```

---

## 5. API Architecture

### 5.1 API Versioning

```
Route Prefix: /api/v1/
Versioning Strategy: URI-based (/api/v1/, /api/v2/)
Content Type: application/json
Authentication: Bearer token (Authorization header)
```

### 5.2 API Response Format

```json
// Success Response (Single Resource)
{
    "success": true,
    "message": "Product retrieved successfully",
    "data": {
        "id": 1,
        "name": "Premium Cotton Shirt",
        "slug": "premium-cotton-shirt"
    }
}

// Success Response (Collection)
{
    "success": true,
    "message": "Products retrieved successfully",
    "data": [...],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "last_page": 8
    },
    "links": {
        "first": "/api/v1/products?page=1",
        "last": "/api/v1/products?page=8",
        "prev": null,
        "next": "/api/v1/products?page=2"
    }
}

// Error Response
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    },
    "error_code": "VALIDATION_ERROR"
}
```

### 5.3 API Route Groups

| Group | Prefix | Middleware | Description |
|-------|--------|-----------|-------------|
| Public | `/api/v1/` | `throttle:60,1` | Guest-accessible routes |
| Customer Auth | `/api/v1/` | `auth:sanctum`, `throttle:120,1` | Customer routes |
| Admin | `/api/v1/admin/` | `auth:sanctum`, `ensure.admin`, `throttle:120,1` | Admin routes |
| Webhook | `/api/webhooks/` | `verify.signature` | Payment webhooks |

### 5.4 Pagination Standards

| Parameter | Default | Max | Description |
|-----------|---------|-----|-------------|
| `page` | 1 | — | Current page number |
| `per_page` | 20 | 100 | Items per page |
| `sort_by` | `created_at` | — | Sort field |
| `sort_order` | `desc` | — | `asc` or `desc` |
| `search` | — | — | Search query string |
| `filter[field]` | — | — | Filter by field value |

---

## 6. Database Architecture

### 6.1 Database Configuration

| Setting | Value | Rationale |
|---------|-------|-----------|
| Engine | InnoDB | Transaction support, row-level locking |
| Character Set | utf8mb4 | Full Unicode support (emojis) |
| Collation | utf8mb4_unicode_ci | Case-insensitive comparisons |
| Strict Mode | ON | Data integrity |
| SQL Mode | TRADITIONAL | Strict SQL compliance |

### 6.2 Naming Conventions

| Element | Convention | Example |
|---------|-----------|---------|
| Tables | snake_case, plural | `products`, `order_items` |
| Columns | snake_case | `first_name`, `created_at` |
| Primary Keys | `id` (auto-increment BIGINT) | `id` |
| Foreign Keys | `{table_singular}_id` | `product_id`, `user_id` |
| Timestamps | `created_at`, `updated_at` | Standard Laravel |
| Soft Delete | `deleted_at` | Standard Laravel |
| Boolean | `is_` prefix | `is_active`, `is_featured` |
| Status | ENUM or TINYINT | `status` |
| Money | DECIMAL(10,2) | `price`, `total` |
| JSON | JSON type | `meta`, `options` |

### 6.3 Database Module Map

| Module | Tables |
|--------|--------|
| Auth & Users | `users`, `roles`, `permissions`, `role_has_permissions`, `model_has_roles`, `model_has_permissions`, `password_reset_tokens`, `personal_access_tokens` |
| Products | `products`, `product_variants`, `product_images`, `product_attributes`, `product_attribute_values`, `categories`, `brands`, `tags`, `product_tag` |
| Inventory | `inventory_ledger`, `purchase_orders`, `purchase_order_items` |
| Customers | `customer_profiles`, `addresses` |
| Cart | `carts`, `cart_items` |
| Orders | `orders`, `order_items`, `order_status_history` |
| Payments | `payments`, `refunds` |
| Returns | `returns`, `return_items` |
| Coupons | `coupons`, `coupon_usage` |
| Billing | `invoices`, `invoice_items`, `credit_notes` |
| Reviews | `reviews` |
| Blog | `blog_posts`, `blog_categories`, `blog_tags`, `blog_post_tag` |
| Wishlist | `wishlists` |
| Notifications | `notifications` (Laravel default) |
| Settings | `settings` |
| Audit | `audit_logs` |

---

## 7. Caching Architecture

### 7.1 Redis Cache Strategy

| Key Pattern | TTL | Invalidation Trigger |
|-------------|-----|---------------------|
| `products:list:{hash}` | 30 min | Product create/update/delete |
| `products:detail:{slug}` | 60 min | Product update |
| `categories:tree` | 24 hours | Category change |
| `brands:list` | 24 hours | Brand change |
| `settings:all` | 24 hours | Settings update |
| `cart:{user_id}` | 7 days | Cart modification |
| `user:{id}:profile` | 60 min | Profile update |
| `home:featured` | 30 min | Manual refresh |
| `home:banners` | 60 min | Banner change |
| `tax:rates` | 24 hours | Tax settings change |
| `shipping:rules` | 24 hours | Shipping settings change |

### 7.2 Cache Invalidation Strategy

| Strategy | Implementation |
|----------|----------------|
| Tag-based | Use Redis tags for group invalidation (`Cache::tags(['products'])`) |
| Event-driven | Listen to model events to clear relevant caches |
| TTL-based | Automatic expiry for non-critical data |
| Manual | Admin action to flush specific cache groups |

---

## 8. Queue Architecture

### 8.1 Queue Configuration

| Queue | Workers | Retry | Timeout | Purpose |
|-------|---------|-------|---------|---------|
| `default` | 2 | 3 | 60s | General jobs |
| `emails` | 2 | 3 | 30s | Email sending |
| `sms` | 1 | 3 | 15s | SMS sending |
| `payments` | 2 | 0 | 120s | Payment processing (no retry) |
| `reports` | 1 | 1 | 300s | Report generation |
| `images` | 1 | 2 | 120s | Image processing (resize, optimize) |
| `exports` | 1 | 1 | 600s | CSV/Excel exports |

### 8.2 Queue Jobs

| Job | Queue | Priority | Description |
|-----|-------|----------|-------------|
| `SendOrderConfirmationEmail` | emails | high | After order confirmed |
| `SendShippingNotificationEmail` | emails | high | After order shipped |
| `SendSmsNotification` | sms | high | Order status SMS |
| `ProcessProductImages` | images | low | Resize, convert to WebP |
| `GenerateSalesReport` | reports | low | Scheduled sales report |
| `GenerateInvoicePdf` | default | normal | Invoice PDF generation |
| `SyncMeilisearchIndex` | default | normal | Update search index |
| `SendAbandonedCartReminder` | emails | low | Scheduled reminder |
| `ProcessRefund` | payments | high | Refund via gateway |
| `ExportOrdersCsv` | exports | low | Bulk export |

### 8.3 Failed Job Handling

| Strategy | Implementation |
|----------|----------------|
| Failed Job Table | `failed_jobs` table for failed job storage |
| Retry | Configurable retry per job class |
| Dead Letter | After max retries, log and notify admin |
| Monitoring | Laravel Horizon for real-time monitoring |
| Alerting | Alert admin when failure rate exceeds threshold |

---

## 9. Event-Driven Architecture

### 9.1 Domain Events

| Event | Fired When | Listeners |
|-------|-----------|-----------|
| `UserRegistered` | New customer registers | SendWelcomeEmail, CreateCustomerProfile |
| `ProductCreated` | New product added | SyncSearchIndex, ClearProductCache |
| `ProductUpdated` | Product modified | SyncSearchIndex, ClearProductCache |
| `InventoryChanged` | Stock level changes | CheckLowStock, UpdateProductAvailability |
| `OrderPlaced` | New order created | ReserveInventory, SendOrderConfirmation |
| `OrderConfirmed` | Payment verified | DeductInventory, GenerateInvoice, SendConfirmation |
| `OrderShipped` | Order shipped | SendShippingNotification |
| `OrderDelivered` | Order delivered | SendDeliveryConfirmation, RequestReview |
| `OrderCancelled` | Order cancelled | RestoreInventory, ProcessRefund, SendCancellationNotification |
| `PaymentReceived` | Payment successful | UpdateOrderStatus, RecordPayment |
| `PaymentFailed` | Payment unsuccessful | NotifyCustomer, ReleaseReservation |
| `RefundProcessed` | Refund completed | UpdateOrderStatus, SendRefundNotification |
| `ReturnRequested` | Customer requests return | NotifyAdmin, CreateReturnRecord |
| `ReturnApproved` | Admin approves return | SchedulePickup, SendReturnApprovalEmail |
| `CouponApplied` | Coupon used | IncrementCouponUsage, LogCouponUsage |
| `ReviewSubmitted` | Customer reviews product | UpdateProductRating, ModerateReview |
| `LowStockAlert` | Stock below threshold | SendLowStockEmail, CreateRestockTask |

### 9.2 Event Architecture Diagram

```
┌──────────────────────────────────────────────────────────────┐
│                    EVENT BUS (Laravel Events)                 │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Producer                    Consumer                        │
│  ────────                    ────────                        │
│                                                              │
│  OrderService    ──┐                                         │
│                    │    ┌─────────────────┐                  │
│  PaymentService  ──┼───▶│  Event          │                  │
│                    │    │  Dispatcher      │                  │
│  InventoryService──┘    └────────┬────────┘                  │
│                                  │                           │
│                    ┌─────────────┼─────────────┐             │
│                    ▼             ▼             ▼             │
│              ┌──────────┐ ┌──────────┐ ┌──────────┐        │
│              │Sync      │ │Queued    │ │Queued    │        │
│              │Listener  │ │Listener  │ │Listener  │        │
│              │(Cache    │ │(Email)   │ │(SMS)     │        │
│              │ Clear)   │ │          │ │          │        │
│              └──────────┘ └──────────┘ └──────────┘        │
└──────────────────────────────────────────────────────────────┘

Synchronous Listeners: Cache invalidation, inventory reservation
Queued Listeners: Emails, SMS, report generation, search indexing
```

---

## 10. File Storage Architecture

### 10.1 Storage Strategy

| Content Type | Storage | Path Pattern | Access |
|-------------|---------|-------------|--------|
| Product Images | S3 | `products/{product_id}/{variant_id}/{hash}.webp` | Public (CDN) |
| Product Thumbnails | S3 | `products/{product_id}/thumbs/{hash}.webp` | Public (CDN) |
| Blog Images | S3 | `blog/{post_id}/{hash}.webp` | Public (CDN) |
| Category Images | S3 | `categories/{hash}.webp` | Public (CDN) |
| Brand Logos | S3 | `brands/{hash}.webp` | Public (CDN) |
| Invoices (PDF) | S3 | `invoices/{year}/{month}/{invoice_number}.pdf` | Private (signed URL) |
| Exports (CSV) | S3 | `exports/{user_id}/{hash}.csv` | Private (signed URL, 1hr TTL) |
| User Avatars | S3 | `avatars/{user_id}/{hash}.webp` | Public |
| Temp Uploads | Local | `tmp/uploads/{hash}` | Private (auto-cleanup) |

### 10.2 Image Processing Pipeline

```
Upload → Validate → Virus Scan → Store Original
                                       │
                                       ▼
                                 Queue Job
                                       │
                    ┌──────────────────┼──────────────────┐
                    ▼                  ▼                  ▼
              Large (1200px)     Medium (600px)     Thumb (300px)
              Quality: 85%      Quality: 80%       Quality: 75%
              Format: WebP      Format: WebP       Format: WebP
                    │                  │                  │
                    └──────────────────┼──────────────────┘
                                       │
                                       ▼
                                Upload to S3
                                       │
                                       ▼
                              Update DB with URLs
```

---

## 11. Search Architecture

### 11.1 Meilisearch Configuration

| Index | Primary Key | Searchable Attributes | Filterable Attributes | Sortable Attributes |
|-------|-------------|----------------------|----------------------|---------------------|
| `products` | `id` | name, description, brand, category, tags, sku | category_id, brand_id, price, color, size, is_active, rating | price, created_at, rating, name |
| `blog_posts` | `id` | title, content, excerpt, category, tags | category_id, status, author_id | created_at, title |

### 11.2 Search Features

| Feature | Implementation |
|---------|----------------|
| Autocomplete | Meilisearch prefix search (as-you-type) |
| Typo Tolerance | Meilisearch built-in (configurable) |
| Faceted Search | Filters returned with result counts |
| Highlighting | Search term highlighting in results |
| Synonyms | Configured synonyms (e.g., "tee" = "t-shirt") |
| Stop Words | Common words excluded from search |
| Ranking | Custom ranking rules (relevancy → popularity → price) |

---

## 12. Deployment Architecture

### 12.1 Infrastructure

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRODUCTION INFRASTRUCTURE                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────┐     ┌──────────────────────────────────────┐     │
│  │  Client  │────▶│           Cloudflare CDN             │     │
│  │ Browser  │     │  (SSL, DDoS Protection, Edge Cache)  │     │
│  └──────────┘     └──────────────┬───────────────────────┘     │
│                                  │                              │
│                                  ▼                              │
│                   ┌──────────────────────────────┐              │
│                   │          Nginx               │              │
│                   │  (Reverse Proxy, Static      │              │
│                   │   Assets, Gzip, Security     │              │
│                   │   Headers)                   │              │
│                   └──────────────┬───────────────┘              │
│                                  │                              │
│                    ┌─────────────┼─────────────┐               │
│                    ▼                           ▼               │
│          ┌──────────────────┐      ┌──────────────────┐        │
│          │   PHP-FPM        │      │   Vue SPA        │        │
│          │   (Laravel)      │      │   (Static Build) │        │
│          │                  │      │                  │        │
│          │   Workers: 8-16  │      │   Served via     │        │
│          │   Memory: 256MB  │      │   Nginx directly │        │
│          └────────┬─────────┘      └──────────────────┘        │
│                   │                                             │
│        ┌──────────┼──────────┬──────────────┐                  │
│        ▼          ▼          ▼              ▼                  │
│  ┌──────────┐ ┌────────┐ ┌──────────┐ ┌──────────┐           │
│  │ MySQL 8  │ │ Redis  │ │Meilisearch│ │  AWS S3  │           │
│  │          │ │        │ │          │ │          │           │
│  │ Primary  │ │ Cache  │ │ Product  │ │ Images   │           │
│  │ + Replica│ │ Queue  │ │ Search   │ │ Files    │           │
│  │          │ │ Session│ │          │ │          │           │
│  └──────────┘ └────────┘ └──────────┘ └──────────┘           │
│                                                                │
│  ┌──────────────────────────────────────────────────────┐     │
│  │               Queue Workers                          │     │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐  │     │
│  │  │ default │ │  emails │ │   sms   │ │ images  │  │     │
│  │  │ (2)     │ │  (2)    │ │   (1)   │ │  (1)    │  │     │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘  │     │
│  │  Managed by: Laravel Horizon + Supervisor           │     │
│  └──────────────────────────────────────────────────────┘     │
└─────────────────────────────────────────────────────────────────┘
```

### 12.2 CI/CD Pipeline

```
GitHub Push → GitHub Actions Pipeline:
│
├── Stage 1: Lint & Static Analysis
│   ├── PHP: Laravel Pint + Larastan
│   └── JS: ESLint + Prettier
│
├── Stage 2: Test
│   ├── PHP: Pest (unit + feature)
│   └── JS: Vitest (unit)
│
├── Stage 3: Build
│   ├── npm run build (Vue SPA)
│   └── composer install --no-dev
│
├── Stage 4: Deploy (main branch only)
│   ├── SSH to server
│   ├── git pull
│   ├── composer install --no-dev
│   ├── php artisan migrate --force
│   ├── php artisan config:cache
│   ├── php artisan route:cache
│   ├── php artisan view:cache
│   ├── php artisan queue:restart
│   └── Reload PHP-FPM
│
└── Stage 5: Post-Deploy
    ├── Health check
    ├── Sentry release tracking
    └── Slack notification
```

---

## 13. Folder Structure

### 13.1 Complete Project Structure

```
e-commerce-website-210626-P/
│
├── app/
│   ├── Actions/                    ← Single-purpose action classes
│   │   ├── Order/
│   │   │   ├── PlaceOrderAction.php
│   │   │   ├── CancelOrderAction.php
│   │   │   └── ProcessRefundAction.php
│   │   ├── Cart/
│   │   │   ├── AddToCartAction.php
│   │   │   └── ApplyCouponAction.php
│   │   ├── Payment/
│   │   │   ├── InitiatePaymentAction.php
│   │   │   └── VerifyPaymentAction.php
│   │   └── Inventory/
│   │       ├── DeductStockAction.php
│   │       └── RestoreStockAction.php
│   │
│   ├── Console/
│   │   └── Commands/
│   │       ├── SendAbandonedCartReminders.php
│   │       ├── GenerateDailyReport.php
│   │       ├── CleanExpiredCarts.php
│   │       ├── ReleaseExpiredReservations.php
│   │       └── SyncSearchIndex.php
│   │
│   ├── DTOs/                       ← Data Transfer Objects
│   │   ├── Product/
│   │   │   ├── CreateProductDTO.php
│   │   │   └── UpdateProductDTO.php
│   │   ├── Order/
│   │   │   ├── PlaceOrderDTO.php
│   │   │   └── OrderFilterDTO.php
│   │   └── ...
│   │
│   ├── Enums/                      ← PHP 8.1 Enums
│   │   ├── OrderStatus.php
│   │   ├── PaymentStatus.php
│   │   ├── PaymentMethod.php
│   │   ├── ReturnStatus.php
│   │   ├── InventoryMovementType.php
│   │   ├── CouponType.php
│   │   ├── BlogStatus.php
│   │   └── UserRole.php
│   │
│   ├── Events/                     ← Domain events
│   │   ├── Order/
│   │   │   ├── OrderPlaced.php
│   │   │   ├── OrderConfirmed.php
│   │   │   ├── OrderShipped.php
│   │   │   ├── OrderDelivered.php
│   │   │   └── OrderCancelled.php
│   │   ├── Payment/
│   │   │   ├── PaymentReceived.php
│   │   │   ├── PaymentFailed.php
│   │   │   └── RefundProcessed.php
│   │   ├── Inventory/
│   │   │   ├── InventoryChanged.php
│   │   │   └── LowStockAlert.php
│   │   └── User/
│   │       └── UserRegistered.php
│   │
│   ├── Exceptions/                 ← Custom exceptions
│   │   ├── InsufficientStockException.php
│   │   ├── InvalidCouponException.php
│   │   ├── PaymentFailedException.php
│   │   ├── OrderCancellationException.php
│   │   └── Handler.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── V1/
│   │   │   │       ├── Auth/
│   │   │   │       │   ├── LoginController.php
│   │   │   │       │   ├── RegisterController.php
│   │   │   │       │   ├── ForgotPasswordController.php
│   │   │   │       │   └── ProfileController.php
│   │   │   │       ├── Customer/
│   │   │   │       │   ├── ProductController.php
│   │   │   │       │   ├── CategoryController.php
│   │   │   │       │   ├── CartController.php
│   │   │   │       │   ├── CheckoutController.php
│   │   │   │       │   ├── OrderController.php
│   │   │   │       │   ├── WishlistController.php
│   │   │   │       │   ├── ReviewController.php
│   │   │   │       │   ├── AddressController.php
│   │   │   │       │   └── BlogController.php
│   │   │   │       ├── Admin/
│   │   │   │       │   ├── DashboardController.php
│   │   │   │       │   ├── ProductController.php
│   │   │   │       │   ├── CategoryController.php
│   │   │   │       │   ├── BrandController.php
│   │   │   │       │   ├── InventoryController.php
│   │   │   │       │   ├── OrderController.php
│   │   │   │       │   ├── CustomerController.php
│   │   │   │       │   ├── CouponController.php
│   │   │   │       │   ├── ReportController.php
│   │   │   │       │   ├── BlogController.php
│   │   │   │       │   ├── SettingsController.php
│   │   │   │       │   ├── UserController.php
│   │   │   │       │   └── RoleController.php
│   │   │   │       └── Webhook/
│   │   │   │           ├── RazorpayWebhookController.php
│   │   │   │           └── StripeWebhookController.php
│   │   │   └── ...
│   │   │
│   │   ├── Middleware/
│   │   │   ├── EnsureAdmin.php
│   │   │   ├── CheckPermission.php
│   │   │   ├── SetLocale.php
│   │   │   ├── LogRequest.php
│   │   │   └── TransformResponse.php
│   │   │
│   │   ├── Requests/               ← Form Request Validation
│   │   │   ├── Auth/
│   │   │   ├── Product/
│   │   │   ├── Order/
│   │   │   ├── Cart/
│   │   │   ├── Coupon/
│   │   │   ├── Blog/
│   │   │   └── Settings/
│   │   │
│   │   └── Resources/              ← API Resources
│   │       ├── ProductResource.php
│   │       ├── ProductCollection.php
│   │       ├── OrderResource.php
│   │       ├── CartResource.php
│   │       ├── CustomerResource.php
│   │       ├── CouponResource.php
│   │       ├── BlogResource.php
│   │       └── ...
│   │
│   ├── Jobs/                       ← Queue jobs
│   │   ├── Email/
│   │   │   ├── SendOrderConfirmationEmail.php
│   │   │   ├── SendShippingNotificationEmail.php
│   │   │   └── SendAbandonedCartEmail.php
│   │   ├── Sms/
│   │   │   └── SendSmsNotification.php
│   │   ├── Image/
│   │   │   └── ProcessProductImages.php
│   │   ├── Report/
│   │   │   ├── GenerateSalesReport.php
│   │   │   └── GenerateInventoryReport.php
│   │   ├── Export/
│   │   │   └── ExportOrdersCsv.php
│   │   └── Payment/
│   │       └── ProcessRefund.php
│   │
│   ├── Listeners/                  ← Event listeners
│   │   ├── Order/
│   │   │   ├── SendOrderConfirmation.php
│   │   │   ├── ReserveInventory.php
│   │   │   ├── DeductInventory.php
│   │   │   ├── RestoreInventory.php
│   │   │   └── GenerateInvoice.php
│   │   ├── Payment/
│   │   │   ├── UpdateOrderStatus.php
│   │   │   └── RecordPayment.php
│   │   ├── Inventory/
│   │   │   ├── CheckLowStock.php
│   │   │   └── UpdateProductAvailability.php
│   │   ├── Cache/
│   │   │   ├── ClearProductCache.php
│   │   │   └── ClearCategoryCache.php
│   │   └── Search/
│   │       └── SyncSearchIndex.php
│   │
│   ├── Models/                     ← Eloquent models
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── Product.php
│   │   ├── ProductVariant.php
│   │   ├── ProductImage.php
│   │   ├── ProductAttribute.php
│   │   ├── ProductAttributeValue.php
│   │   ├── Category.php
│   │   ├── Brand.php
│   │   ├── Tag.php
│   │   ├── InventoryLedger.php
│   │   ├── PurchaseOrder.php
│   │   ├── PurchaseOrderItem.php
│   │   ├── CustomerProfile.php
│   │   ├── Address.php
│   │   ├── Cart.php
│   │   ├── CartItem.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── OrderStatusHistory.php
│   │   ├── Payment.php
│   │   ├── Refund.php
│   │   ├── Return.php
│   │   ├── ReturnItem.php
│   │   ├── Coupon.php
│   │   ├── CouponUsage.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── CreditNote.php
│   │   ├── Review.php
│   │   ├── BlogPost.php
│   │   ├── BlogCategory.php
│   │   ├── BlogTag.php
│   │   ├── Wishlist.php
│   │   ├── Setting.php
│   │   └── AuditLog.php
│   │
│   ├── Notifications/              ← Laravel notifications
│   │   ├── OrderConfirmedNotification.php
│   │   ├── OrderShippedNotification.php
│   │   ├── PasswordResetNotification.php
│   │   └── LowStockNotification.php
│   │
│   ├── Observers/                  ← Model observers
│   │   ├── OrderObserver.php
│   │   ├── ProductObserver.php
│   │   └── ReviewObserver.php
│   │
│   ├── Policies/                   ← Authorization policies
│   │   ├── OrderPolicy.php
│   │   ├── ProductPolicy.php
│   │   ├── CouponPolicy.php
│   │   ├── BlogPolicy.php
│   │   └── UserPolicy.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── RepositoryServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── PaymentServiceProvider.php
│   │
│   ├── Repositories/               ← Repository pattern
│   │   ├── Contracts/              ← Interfaces
│   │   │   ├── ProductRepositoryInterface.php
│   │   │   ├── OrderRepositoryInterface.php
│   │   │   ├── CartRepositoryInterface.php
│   │   │   ├── InventoryRepositoryInterface.php
│   │   │   ├── CouponRepositoryInterface.php
│   │   │   ├── CustomerRepositoryInterface.php
│   │   │   ├── BlogRepositoryInterface.php
│   │   │   └── ReportRepositoryInterface.php
│   │   └── Eloquent/              ← Implementations
│   │       ├── ProductRepository.php
│   │       ├── OrderRepository.php
│   │       ├── CartRepository.php
│   │       ├── InventoryRepository.php
│   │       ├── CouponRepository.php
│   │       ├── CustomerRepository.php
│   │       ├── BlogRepository.php
│   │       └── ReportRepository.php
│   │
│   ├── Services/                   ← Business logic
│   │   ├── ProductService.php
│   │   ├── CartService.php
│   │   ├── CheckoutService.php
│   │   ├── OrderService.php
│   │   ├── PaymentService.php
│   │   ├── InventoryService.php
│   │   ├── CouponService.php
│   │   ├── InvoiceService.php
│   │   ├── CustomerService.php
│   │   ├── BlogService.php
│   │   ├── ReportService.php
│   │   ├── SearchService.php
│   │   ├── ImageService.php
│   │   ├── SmsService.php
│   │   └── SettingsService.php
│   │
│   ├── Services/Payment/           ← Payment gateway abstraction
│   │   ├── PaymentGatewayInterface.php
│   │   ├── PaymentGatewayFactory.php
│   │   ├── RazorpayGateway.php
│   │   └── StripeGateway.php
│   │
│   └── Traits/                     ← Reusable traits
│       ├── HasSlug.php
│       ├── HasSeo.php
│       ├── Filterable.php
│       ├── Sortable.php
│       └── Auditable.php
│
├── bootstrap/
│   └── app.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── horizon.php
│   ├── mail.php
│   ├── queue.php
│   ├── sanctum.php
│   ├── scout.php
│   ├── services.php               ← Razorpay, Stripe, Twilio keys
│   └── ecommerce.php              ← Custom app config
│
├── database/
│   ├── factories/                  ← Model factories for testing
│   ├── migrations/                 ← Database migrations (ordered)
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       ├── PermissionSeeder.php
│       ├── AdminUserSeeder.php
│       ├── CategorySeeder.php
│       ├── BrandSeeder.php
│       ├── ProductSeeder.php
│       ├── SettingsSeeder.php
│       └── SampleDataSeeder.php
│
├── lang/                           ← Translations
│   ├── en/
│   │   ├── auth.php
│   │   ├── messages.php
│   │   ├── products.php
│   │   ├── orders.php
│   │   └── validation.php
│   └── hi/                         ← Hindi translations
│       ├── auth.php
│       ├── messages.php
│       ├── products.php
│       ├── orders.php
│       └── validation.php
│
├── resources/
│   └── js/                         ← Vue 3 SPA
│       ├── App.vue
│       ├── main.js
│       ├── router/
│       │   ├── index.js
│       │   ├── customer.js
│       │   ├── admin.js
│       │   └── guards.js
│       ├── stores/
│       │   ├── auth.js
│       │   ├── cart.js
│       │   ├── product.js
│       │   ├── category.js
│       │   ├── order.js
│       │   ├── wishlist.js
│       │   ├── checkout.js
│       │   ├── notification.js
│       │   └── ui.js
│       ├── api/
│       │   ├── axios.js
│       │   ├── auth.js
│       │   ├── products.js
│       │   ├── cart.js
│       │   ├── orders.js
│       │   ├── checkout.js
│       │   ├── wishlist.js
│       │   ├── admin/
│       │   │   ├── products.js
│       │   │   ├── orders.js
│       │   │   ├── inventory.js
│       │   │   ├── customers.js
│       │   │   ├── coupons.js
│       │   │   ├── reports.js
│       │   │   ├── blogs.js
│       │   │   └── settings.js
│       │   └── webhooks.js
│       ├── components/             ← (see §3.4 above)
│       ├── views/
│       │   ├── customer/
│       │   │   ├── HomePage.vue
│       │   │   ├── CategoryPage.vue
│       │   │   ├── ProductListPage.vue
│       │   │   ├── ProductDetailPage.vue
│       │   │   ├── CartPage.vue
│       │   │   ├── CheckoutPage.vue
│       │   │   ├── WishlistPage.vue
│       │   │   ├── AccountPage.vue
│       │   │   ├── OrderListPage.vue
│       │   │   ├── OrderDetailPage.vue
│       │   │   ├── BlogListPage.vue
│       │   │   ├── BlogDetailPage.vue
│       │   │   ├── AboutPage.vue
│       │   │   ├── ContactPage.vue
│       │   │   └── TrackOrderPage.vue
│       │   ├── admin/
│       │   │   ├── DashboardPage.vue
│       │   │   ├── products/
│       │   │   ├── inventory/
│       │   │   ├── orders/
│       │   │   ├── customers/
│       │   │   ├── coupons/
│       │   │   ├── reports/
│       │   │   ├── blogs/
│       │   │   ├── settings/
│       │   │   └── users/
│       │   └── auth/
│       │       ├── LoginPage.vue
│       │       ├── RegisterPage.vue
│       │       ├── ForgotPasswordPage.vue
│       │       └── ResetPasswordPage.vue
│       ├── composables/
│       │   ├── useAuth.js
│       │   ├── useCart.js
│       │   ├── useProduct.js
│       │   ├── useOrder.js
│       │   ├── usePagination.js
│       │   ├── useFilters.js
│       │   ├── useDebounce.js
│       │   ├── useToast.js
│       │   ├── useMediaQuery.js
│       │   ├── useInfiniteScroll.js
│       │   └── useSeo.js
│       ├── utils/
│       │   ├── formatters.js       ← Currency, date, number formatting
│       │   ├── validators.js       ← Client-side validation helpers
│       │   ├── constants.js        ← App constants
│       │   └── helpers.js          ← General utilities
│       ├── plugins/
│       │   ├── i18n.js             ← Vue I18n setup
│       │   └── seo.js              ← SEO plugin
│       ├── assets/
│       │   ├── css/
│       │   │   ├── main.css        ← Global styles + CSS custom properties
│       │   │   ├── reset.css       ← CSS reset
│       │   │   ├── variables.css   ← Design tokens
│       │   │   ├── typography.css  ← Font styles
│       │   │   ├── animations.css  ← Shared animations
│       │   │   └── utilities.css   ← Utility classes
│       │   ├── fonts/
│       │   └── images/
│       └── locales/
│           ├── en.json
│           └── hi.json
│
├── routes/
│   ├── api.php                     ← API routes
│   ├── web.php                     ← SPA catch-all route
│   ├── channels.php                ← Broadcasting channels
│   └── console.php                 ← Artisan commands
│
├── storage/
├── tests/
│   ├── Unit/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Models/
│   ├── Feature/
│   │   ├── Api/
│   │   │   ├── Auth/
│   │   │   ├── Product/
│   │   │   ├── Cart/
│   │   │   ├── Order/
│   │   │   └── Admin/
│   │   └── ...
│   └── ...
│
├── docs/                           ← Project documentation
│
├── .env.example
├── .github/
│   └── workflows/
│       └── ci.yml                  ← GitHub Actions CI/CD
├── composer.json
├── package.json
├── vite.config.js
├── phpunit.xml
└── README.md
```

---

## 14. Module Dependency Map

### 14.1 Build Order & Dependencies

```
Module Dependency Graph (build bottom-up):

Level 1 (No Dependencies):
├── Product Masters (Categories, Brands, Attributes, Tags)
└── Settings

Level 2 (Depends on Level 1):
├── Product Management → Product Masters
└── Customer Module → Settings

Level 3 (Depends on Level 2):
├── Inventory Management → Product Management
├── Cart → Product Management, Customer Module
└── Blogs → Settings

Level 4 (Depends on Level 3):
├── Checkout → Cart, Customer Module, Inventory
├── Coupons → Product Management
└── Reviews → Product Management, Customer Module

Level 5 (Depends on Level 4):
├── Orders → Checkout, Inventory, Coupons
└── Billing → Orders

Level 6 (Depends on Level 5):
├── Payments → Orders
├── Mail → Orders, Customer Module
└── SMS → Orders, Customer Module

Level 7 (Depends on Level 6):
├── Reports → Orders, Payments, Inventory, Customers
└── Dashboard → Reports
```

### 14.2 Module Interface Contracts

| Module | Provides (Public API) | Consumes (Dependencies) |
|--------|----------------------|------------------------|
| Product Masters | Category CRUD, Brand CRUD, Attribute CRUD | None |
| Product Management | Product CRUD, Variant CRUD, Image Management | Product Masters |
| Inventory | Stock Check, Deduct, Restore, Ledger | Product Management |
| Customer | Registration, Profile, Addresses | Settings |
| Cart | Add/Remove/Update Items, Get Cart | Products, Customer |
| Checkout | Process Checkout, Calculate Totals | Cart, Customer, Inventory |
| Orders | Place/Cancel/Track Order, Status Updates | Checkout, Inventory, Coupons |
| Payments | Initiate/Verify/Refund Payment | Orders |
| Coupons | Validate/Apply/Track Coupon | Products |
| Billing | Generate Invoice/Credit Note | Orders |
| Mail | Send Email Templates | Orders, Customers |
| SMS | Send SMS Templates | Orders, Customers |
| Blogs | Post CRUD, Category Management | Settings |
| Reports | Generate Various Reports | Orders, Payments, Inventory, Customers |
| Dashboard | Aggregate Stats, Recent Activity | Reports |
| Settings | Get/Set Configuration | None |

---

*End of Technical Architecture Document — Phase 2*
