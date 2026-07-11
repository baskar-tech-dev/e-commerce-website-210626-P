# Project Brief: Maya Sree South Indian Fashion E-Commerce Platform

Copy and paste this brief into ChatGPT, Claude, or other AI assistants to instantly give them the context of this project.

---

## 1. Project Overview
**Maya Sree South Indian Fashion** is an enterprise-grade fashion e-commerce platform specializing in South Indian garment wear (sarees, kurtis, and ready-made blouses). The brand celebrates traditional South Indian roots through high-end styling, premium fabric options, and modern, comfortable fits.

## 2. Technology Stack
- **Backend**: Laravel 11 (API-driven backend).
- **Authentication**: Laravel Sanctum (token-based auth for customers and admin).
- **Frontend**: Vue 3 Single Page Application (SPA) with Vite compilation, Vue Router, and Pinia stores.
- **Styling**: Vanilla CSS with custom global CSS variables for dynamic themes (avoiding Tailwind unless explicitly configured).
- **Database**: MySQL for local development, SQLite (in-memory) for automated PHPUnit tests.
- **Payment Gateway**: Razorpay (fully integrated with checkouts, signature verification, and secure webhooks).

## 3. Core Directory Structure
- `app/Models/`: Eloquent models (e.g., `Product`, `Order`, `Menu`, `Role`, `User`).
- `app/Http/Controllers/Api/v1/`: API endpoints grouped by modules:
  - `Admin/`: Authenticated management routes (Users, Roles, Products, Audits).
  - `CustomerProfileController.php`, `StorefrontProductController.php`, `PaymentController.php`.
- `app/Models/Traits/HasRoles.php`: Custom trait handling role-based permission sync, assignment, and authorization checks.
- `database/migrations/`: Database schema definitions (including custom `role_id` columns in `users` and `menus`).
- `resources/js/`: Vue 3 codebase:
  - `views/storefront/`: Public buyer views (Home, AboutUs, ContactUs, Catalog).
  - `views/admin/`: Admin panels.
  - `stores/`: Pinia state files (auth, menu, theme).
  - `router/index.js`: Vue Router configuration (includes top-of-page scroll behavior configuration).

## 4. Key Design System (Aesthetics)
Following the project's premium brand guidelines:
- **Primary Color**: Deep Maroon (`#4a0e2e` / `var(--color-primary)`)
- **Secondary Color**: Zari Gold (`#d4af37` / `var(--color-secondary)`)
- **Background**: Warm Ivory/Cream (`#fffcf7` / `var(--color-bg)`)
- **Text Color**: Dark Charcoal (`#2d2d2d` / `var(--color-text-primary)`)
- **Typography**: `Playfair Display` for titles/headings, `Poppins` for body and labels.
- **Layout**: 8px grid system, rounded borders (`12px` to `16px`), soft shadows, and subtle hover scale animations.
- **Mobile First**: Viewports prioritize mobile layouts, adapting to desktop dynamically.

## 5. Security & Authorization Logic
- Admin routes (`/api/admin/*`) are protected by `auth:sanctum` and rate-limited.
- Features are restricted using a custom permission middleware (`permission:manage_products`, `permission:manage_orders`, `permission:manage_users`, etc.).
- Navigation menus (fetched from `/api/admin/menus`) check the user's `role_id` and `permission_name` to filter visible links. The `super_admin` role automatically bypasses all menu restrictions.

## 6. Development & Build Instructions
- Run migrations: `php artisan migrate`
- Seed database: `php artisan db:seed`
- Compile assets: `npm run build` (Note: run `npm.cmd run build` on Windows systems with execution policies).
- Run test suite: `php artisan test`

---

*Use this context to guide any code generation, feature additions, or debugging requests for this e-commerce project.*
