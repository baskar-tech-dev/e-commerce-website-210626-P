# Phase 11: Testing Plan Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** QA Lead
**Status:** Draft

---

## Table of Contents

1. [Testing Strategy](#1-testing-strategy)
2. [Unit Tests](#2-unit-tests)
3. [Feature Tests](#3-feature-tests)
4. [Integration Tests](#4-integration-tests)
5. [Performance Tests](#5-performance-tests)
6. [Security Tests](#6-security-tests)
7. [Edge Cases](#7-edge-cases)
8. [Test Data Strategy](#8-test-data-strategy)

---

## 1. Testing Strategy

### Test Pyramid

```
         ╱  ╲
        ╱ E2E ╲          5% — Critical user flows
       ╱────────╲
      ╱Integration╲      15% — API + cross-module
     ╱──────────────╲
    ╱   Feature Tests  ╲   30% — API endpoints
   ╱────────────────────╲
  ╱     Unit Tests        ╲  50% — Services, repositories
 ╱──────────────────────────╲
```

### Testing Framework

| Layer | Framework | Location |
|-------|-----------|----------|
| PHP Unit Tests | Pest PHP | `tests/Unit/` |
| PHP Feature Tests | Pest PHP | `tests/Feature/` |
| Frontend Unit Tests | Vitest | `resources/js/__tests__/` |
| E2E Tests | Cypress (future) | `tests/E2E/` |

### Coverage Targets

| Module | Target | Critical Paths |
|--------|--------|---------------|
| Authentication | 90% | Login, register, password reset |
| Products | 85% | CRUD, variants, search, filters |
| Cart | 90% | Add, update, remove, coupon |
| Checkout | 95% | Address, payment, order placement |
| Orders | 90% | Status transitions, cancellation |
| Payments | 95% | Gateway, verification, refund |
| Inventory | 95% | Stock deduction, reservation, ledger |
| Coupons | 90% | Validation, application, limits |
| Overall | 85% | Minimum code coverage |

---

## 2. Unit Tests

### 2.1 Service Layer Tests

| Service | Test Cases |
|---------|-----------|
| **ProductService** | Create product with variants, update pricing, toggle status, validate MRP ≥ selling price, slug generation |
| **CartService** | Add item, update quantity, remove item, calculate totals, apply coupon, max quantity check |
| **OrderService** | Place order, calculate taxes (CGST/SGST vs IGST), generate order number, validate status transitions |
| **InventoryService** | Deduct stock, restore stock, reserve stock, release reservation, prevent negative stock, ledger entry creation |
| **CouponService** | Validate code, check dates, check usage limits, check min order, check product eligibility, calculate discount |
| **PaymentService** | Initiate payment, verify signature, process refund, handle gateway errors |
| **InvoiceService** | Generate invoice number, calculate line items, apply tax, generate PDF |
| **CustomerService** | Create profile, update profile, address CRUD, max address limit |
| **SearchService** | Search products, filter results, sort results, autocomplete |
| **ImageService** | Validate file type, validate size, generate variants, upload to S3 |

### 2.2 Repository Layer Tests

| Repository | Test Cases |
|-----------|-----------|
| **ProductRepository** | Find by slug, filter by category/brand/price, search, paginate, eager load relations |
| **OrderRepository** | Find by UUID, filter by status/date, customer orders, aggregations |
| **InventoryRepository** | Get stock level, get movement history, aggregate by type |

### 2.3 Model Tests

| Model | Test Cases |
|-------|-----------|
| Product | Relationships (variants, images, category, brand, tags), scopes (active, featured), accessors (discount percentage) |
| Order | Status transitions (valid/invalid), total calculations, relationships |
| ProductVariant | Available stock calculation (stock - reserved), price resolution (variant or product) |
| Coupon | Is valid check (dates, active, usage), applicable products check |

### 2.4 Enum/DTO Tests

| Class | Test Cases |
|-------|-----------|
| OrderStatus | Valid transitions, labels, colors |
| PaymentStatus | Value mapping |
| CreateProductDTO | Construction from array, validation |

---

## 3. Feature Tests (API)

### 3.1 Authentication API Tests

| Test Case | Method | Endpoint | Expected |
|-----------|--------|----------|----------|
| Register with valid data | POST | `/auth/register` | 201, user + token |
| Register with duplicate email | POST | `/auth/register` | 422, email exists |
| Register with weak password | POST | `/auth/register` | 422, password rules |
| Login with valid credentials | POST | `/auth/login` | 200, user + token |
| Login with wrong password | POST | `/auth/login` | 401 |
| Login with non-existent email | POST | `/auth/login` | 401 |
| Login rate limiting (6th attempt) | POST | `/auth/login` | 429 |
| Access protected route without token | GET | `/auth/profile` | 401 |
| Access protected route with valid token | GET | `/auth/profile` | 200 |
| Logout revokes token | POST | `/auth/logout` | 200, token invalid |
| Forgot password sends email | POST | `/auth/forgot-password` | 200 |
| Reset password with valid token | POST | `/auth/reset-password` | 200 |
| Reset password with expired token | POST | `/auth/reset-password` | 422 |

### 3.2 Product API Tests

| Test Case | Expected |
|-----------|----------|
| List products (default pagination) | 200, 20 items, meta |
| Filter products by category | 200, filtered results |
| Filter products by price range | 200, products within range |
| Filter products by multiple filters | 200, intersection of filters |
| Sort products by price ascending | 200, sorted correctly |
| Search products by name | 200, relevant results |
| Get product by slug | 200, full product with variants |
| Get non-existent product | 404 |
| Get inactive product (public) | 404 |
| Get product reviews | 200, paginated reviews |
| Check delivery availability | 200, delivery info |

### 3.3 Cart API Tests

| Test Case | Expected |
|-----------|----------|
| Add item to cart | 201, cart updated |
| Add same item again (increment quantity) | 200, quantity increased |
| Add item with insufficient stock | 409, out of stock |
| Update item quantity | 200, cart recalculated |
| Update to zero quantity | 422, validation error |
| Remove item from cart | 200, item removed |
| Clear entire cart | 200, empty cart |
| Apply valid coupon | 200, discount applied |
| Apply expired coupon | 409, coupon expired |
| Apply coupon below minimum order | 409, minimum not met |
| Remove coupon | 200, discount removed |
| Cart persists across sessions | Cart items maintained |
| Guest cart merge on login | Guest items merged to user cart |

### 3.4 Checkout & Order API Tests

| Test Case | Expected |
|-----------|----------|
| Validate checkout (valid) | 200, order summary |
| Place order (online payment) | 201, order + payment data |
| Place order (COD) | 201, order confirmed |
| Place order with empty cart | 400 |
| Place order with out-of-stock item | 409 |
| Verify payment (valid signature) | 200, order confirmed |
| Verify payment (invalid signature) | 400 |
| List customer orders | 200, paginated orders |
| Get order details | 200, full order |
| Get another customer's order | 403 |
| Cancel order (before shipping) | 200, cancelled + stock restored |
| Cancel order (after shipping) | 409, not cancellable |
| Request return (within window) | 201, return created |
| Request return (expired window) | 409, window expired |
| Track order | 200, status timeline |

### 3.5 Admin API Tests

| Test Case | Expected |
|-----------|----------|
| Admin CRUD: Create product | 201, product created |
| Admin CRUD: Update product | 200, product updated |
| Admin CRUD: Delete product (no orders) | 200, soft deleted |
| Admin CRUD: Delete product (has orders) | 409, cannot delete |
| Admin: Update order status (valid transition) | 200, status updated |
| Admin: Update order status (invalid transition) | 409, invalid transition |
| Admin: Adjust inventory (+) | 200, stock increased, ledger entry |
| Admin: Adjust inventory (-) below zero | 409, insufficient stock |
| Admin: Access without admin role | 403 |
| Admin: Dashboard stats | 200, stats data |
| Admin: Generate report | 200, report data |
| Admin: Export orders | 200, job queued |
| Non-admin accessing admin route | 403 |
| Permission-restricted endpoint | 403 without permission |

---

## 4. Integration Tests

### 4.1 Cross-Module Flow Tests

| Flow | Test Steps |
|------|-----------|
| **Complete Purchase** | Register → Browse → Add to Cart → Apply Coupon → Checkout → Payment → Verify → Order Confirmed |
| **Order Lifecycle** | Place Order → Confirm → Process → Pack → Ship → Deliver → Review |
| **Return Flow** | Deliver → Request Return → Admin Approve → Pickup → QC → Refund |
| **Inventory Sync** | Add Stock → Customer Orders → Stock Deducted → Cancel Order → Stock Restored |
| **Coupon Lifecycle** | Create Coupon → Customer Applies → Order Placed → Usage Incremented → Limit Reached |
| **Payment Failure & Retry** | Place Order → Payment Fails → Stock Released → Retry → Success |
| **Cart to Order** | Guest Cart → Login → Cart Merged → Checkout → Cart Cleared |

### 4.2 Event-Driven Integration Tests

| Event | Verify Listeners |
|-------|-----------------|
| `OrderPlaced` | Inventory reserved, confirmation email queued |
| `OrderConfirmed` | Inventory deducted, invoice generated, notification sent |
| `OrderCancelled` | Inventory restored, refund initiated (if paid), notification sent |
| `PaymentReceived` | Order status updated, payment recorded |
| `InventoryChanged` | Low stock check triggered, product availability updated |
| `UserRegistered` | Welcome email sent, customer profile created |

---

## 5. Performance Tests

### 5.1 Load Test Scenarios

| Scenario | Users | Duration | Target |
|----------|-------|----------|--------|
| Product browsing | 1000 concurrent | 5 min | < 200ms p95, 0% errors |
| Product search | 500 concurrent | 5 min | < 300ms p95 |
| Add to cart | 200 concurrent | 3 min | < 200ms p95, 0% errors |
| Checkout flow | 100 concurrent | 5 min | < 500ms p95, 0% errors |
| Admin dashboard | 50 concurrent | 3 min | < 300ms p95 |
| Mixed workload | 2000 concurrent | 10 min | < 300ms p95, < 0.1% errors |

### 5.2 Database Performance Tests

| Test | Target |
|------|--------|
| Product listing query (with filters) | < 50ms |
| Order listing query (admin, with joins) | < 100ms |
| Inventory ledger query (30-day range) | < 100ms |
| Report aggregation (monthly sales) | < 500ms |
| Full-text search query | < 100ms (via Meilisearch) |

---

## 6. Security Tests

| Test Category | Test Cases |
|--------------|-----------|
| **Authentication** | Brute force protection, token expiry, session fixation |
| **Authorization** | Access control bypass, privilege escalation, IDOR |
| **Input Validation** | SQL injection, XSS, command injection, path traversal |
| **File Upload** | Malicious file upload, oversized file, wrong MIME |
| **Rate Limiting** | Verify all limits enforced, bypass attempts |
| **CORS** | Verify restricted origins, no wildcard in production |
| **Payment** | Amount tampering, signature verification, replay attacks |
| **Data Exposure** | No sensitive data in error messages, no internal IDs leaked |

---

## 7. Edge Cases

| Module | Edge Cases |
|--------|-----------|
| **Cart** | Adding deleted product, price change while in cart, last item in stock (race condition) |
| **Checkout** | Coupon expiry during checkout, stock depleted between cart and checkout |
| **Orders** | Simultaneous cancel requests, payment webhook received before verify call |
| **Inventory** | Concurrent stock deductions (pessimistic locking), stock adjustment during active orders |
| **Coupons** | Last use of limited coupon (race condition), coupon category deleted |
| **Payments** | Double payment (idempotency), partial refund + full refund |
| **Search** | Empty results, special characters, very long query, SQL injection via search |
| **Images** | Upload during product edit, delete image that's primary |

---

## 8. Test Data Strategy

### 8.1 Factories

| Factory | Generates |
|---------|-----------|
| `UserFactory` | Customer accounts with profiles |
| `AdminFactory` | Admin users with roles |
| `ProductFactory` | Products with variants and images |
| `OrderFactory` | Orders with items and payments |
| `CouponFactory` | Coupons with various rules |
| `ReviewFactory` | Product reviews with ratings |
| `BlogPostFactory` | Blog posts with categories |

### 8.2 Seeders

| Seeder | Purpose |
|--------|---------|
| `TestDatabaseSeeder` | Minimal data for testing |
| `DemoDataSeeder` | Rich sample data for demo/development |

### 8.3 CI/CD Test Configuration

```yaml
# GitHub Actions
test:
  runs-on: ubuntu-latest
  services:
    mysql:
      image: mysql:8.0
    redis:
      image: redis:7
    meilisearch:
      image: getmeili/meilisearch:latest
  steps:
    - uses: actions/checkout@v4
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'
    - run: composer install --no-interaction
    - run: php artisan test --parallel
    - run: npm ci && npm run test
```

---

*End of Testing Plan Document — Phase 11*
