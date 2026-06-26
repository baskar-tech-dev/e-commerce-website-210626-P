# Phase 7: API Specification Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** Senior API Architect
**Status:** Draft
**Base URL:** `/api/v1`

---

## Table of Contents

1. [API Conventions](#1-api-conventions)
2. [Authentication APIs](#2-authentication-apis)
3. [Product APIs](#3-product-apis)
4. [Cart APIs](#4-cart-apis)
5. [Checkout & Order APIs](#5-checkout--order-apis)
6. [Customer APIs](#6-customer-apis)
7. [Wishlist APIs](#7-wishlist-apis)
8. [Review APIs](#8-review-apis)
9. [Blog APIs](#9-blog-apis)
10. [Coupon APIs](#10-coupon-apis)
11. [Admin: Product APIs](#11-admin-product-apis)
12. [Admin: Order APIs](#12-admin-order-apis)
13. [Admin: Inventory APIs](#13-admin-inventory-apis)
14. [Admin: Customer APIs](#14-admin-customer-apis)
15. [Admin: Coupon APIs](#15-admin-coupon-apis)
16. [Admin: Report APIs](#16-admin-report-apis)
17. [Admin: Blog APIs](#17-admin-blog-apis)
18. [Admin: Settings APIs](#18-admin-settings-apis)
19. [Admin: Dashboard APIs](#19-admin-dashboard-apis)
20. [Admin: User & Role APIs](#20-admin-user--role-apis)
21. [Webhook APIs](#21-webhook-apis)
22. [Error Codes](#22-error-codes)

---

## 1. API Conventions

### Request Format

| Header | Value | Required |
|--------|-------|----------|
| `Content-Type` | `application/json` | Yes |
| `Accept` | `application/json` | Yes |
| `Authorization` | `Bearer {token}` | For protected routes |
| `Accept-Language` | `en` \| `hi` | Optional (default: en) |

### Response Format

```json
// Success
{
    "success": true,
    "message": "Resource retrieved successfully",
    "data": { ... }
}

// Success (Paginated)
{
    "success": true,
    "message": "Resources retrieved successfully",
    "data": [ ... ],
    "meta": { "current_page": 1, "per_page": 20, "total": 150, "last_page": 8 },
    "links": { "first": "...", "last": "...", "prev": null, "next": "..." }
}

// Error
{
    "success": false,
    "message": "Validation failed",
    "errors": { "field": ["Error message"] },
    "error_code": "VALIDATION_ERROR"
}
```

### Common Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 20, max: 100) |
| `sort_by` | string | Sort field |
| `sort_order` | string | `asc` or `desc` |
| `search` | string | Search query |
| `filter[field]` | string | Filter by field value |

---

## 2. Authentication APIs

### POST `/auth/register`

Register a new customer account.

| Field | Type | Validation |
|-------|------|------------|
| `first_name` | string | required, max:100 |
| `last_name` | string | required, max:100 |
| `email` | string | required, email, unique:users |
| `phone` | string | required, digits:10, unique:users |
| `password` | string | required, min:8, confirmed |
| `password_confirmation` | string | required |

**Success (201):** Returns user data + auth token.
**Errors:** 422 (validation), 409 (email/phone exists)

---

### POST `/auth/login`

| Field | Type | Validation |
|-------|------|------------|
| `email` | string | required, email |
| `password` | string | required |
| `remember` | boolean | optional |

**Success (200):** Returns user data + auth token.
**Errors:** 401 (invalid credentials), 403 (account disabled)

---

### POST `/auth/logout`

**Auth Required.** Revokes current token.

**Success (200):** `{ "message": "Logged out successfully" }`

---

### POST `/auth/forgot-password`

| Field | Type | Validation |
|-------|------|------------|
| `email` | string | required, email, exists:users |

**Success (200):** `{ "message": "Password reset link sent" }`
**Errors:** 422 (email not found), 429 (too many requests)

---

### POST `/auth/reset-password`

| Field | Type | Validation |
|-------|------|------------|
| `token` | string | required |
| `email` | string | required, email |
| `password` | string | required, min:8, confirmed |
| `password_confirmation` | string | required |

**Success (200):** `{ "message": "Password reset successfully" }`

---

### GET `/auth/profile`

**Auth Required.** Returns authenticated user's profile.

**Success (200):** User resource with customer profile, addresses.

---

### PUT `/auth/profile`

**Auth Required.** Update profile.

| Field | Type | Validation |
|-------|------|------------|
| `first_name` | string | sometimes, max:100 |
| `last_name` | string | sometimes, max:100 |
| `phone` | string | sometimes, digits:10, unique |
| `date_of_birth` | date | sometimes, before:today |
| `gender` | string | sometimes, in:male,female,other |

---

### PUT `/auth/change-password`

**Auth Required.**

| Field | Type | Validation |
|-------|------|------------|
| `current_password` | string | required, current_password |
| `password` | string | required, min:8, confirmed |
| `password_confirmation` | string | required |

---

## 3. Product APIs (Public)

### GET `/products`

List products with filters and pagination.

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | string | Category slug |
| `brand` | string | Brand slug |
| `filter[min_price]` | number | Minimum price |
| `filter[max_price]` | number | Maximum price |
| `filter[size]` | string | Size filter (comma-separated) |
| `filter[color]` | string | Color filter (comma-separated) |
| `filter[rating]` | integer | Minimum rating (1-5) |
| `filter[discount]` | integer | Minimum discount % |
| `filter[is_new_arrival]` | boolean | New arrivals only |
| `filter[is_bestseller]` | boolean | Bestsellers only |
| `filter[is_featured]` | boolean | Featured only |
| `sort_by` | string | `popularity`, `price`, `created_at`, `rating`, `discount` |
| `search` | string | Search query |

**Success (200):** Paginated product collection with variants summary.

---

### GET `/products/{slug}`

Get single product with full details.

**Success (200):** Product resource including: variants, images, category, brand, reviews summary, related products.

---

### GET `/products/{slug}/reviews`

List reviews for a product.

| Parameter | Type | Description |
|-----------|------|-------------|
| `sort_by` | string | `created_at`, `rating`, `helpful_count` |
| `filter[rating]` | integer | Filter by star rating |

**Success (200):** Paginated review collection.

---

### GET `/categories`

List all active categories (tree structure).

**Success (200):** Nested category tree with subcategories, product counts.

---

### GET `/categories/{slug}`

Get single category with subcategories.

---

### GET `/brands`

List all active brands.

---

### GET `/search/suggestions`

Autocomplete search suggestions.

| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Search query (min 2 chars) |
| `limit` | integer | Max suggestions (default: 10) |

**Success (200):** Array of suggestions (products, categories, brands).

---

### POST `/products/{slug}/check-delivery`

Check delivery availability for a pincode.

| Field | Type | Validation |
|-------|------|------------|
| `pincode` | string | required, digits:6 |

**Success (200):** `{ "available": true, "estimated_delivery": "Jun 28-30", "shipping_cost": 0, "cod_available": true }`

---

## 4. Cart APIs

### GET `/cart`

**Auth or Session.** Get current cart with items.

**Success (200):** Cart resource with items, subtotal, discount, tax, total.

---

### POST `/cart/items`

Add item to cart.

| Field | Type | Validation |
|-------|------|------------|
| `product_variant_id` | integer | required, exists |
| `quantity` | integer | required, min:1, max:10 |

**Success (201):** Updated cart resource.
**Errors:** 422 (out of stock), 409 (max quantity exceeded)

---

### PUT `/cart/items/{id}`

Update cart item quantity.

| Field | Type | Validation |
|-------|------|------------|
| `quantity` | integer | required, min:1, max:10 |

**Success (200):** Updated cart resource.

---

### DELETE `/cart/items/{id}`

Remove item from cart.

**Success (200):** Updated cart resource.

---

### DELETE `/cart`

Clear entire cart.

**Success (200):** Empty cart resource.

---

### POST `/cart/coupon`

Apply coupon to cart.

| Field | Type | Validation |
|-------|------|------------|
| `code` | string | required, exists:coupons,code |

**Success (200):** Updated cart with discount applied.
**Errors:** 422 (invalid code), 409 (not eligible, expired, usage limit)

---

### DELETE `/cart/coupon`

Remove coupon from cart.

---

### POST `/cart/merge`

**Auth Required.** Merge guest cart into authenticated user's cart (called after login).

| Field | Type | Validation |
|-------|------|------------|
| `session_id` | string | required |

---

## 5. Checkout & Order APIs

### POST `/checkout/validate`

**Auth Required.** Validate checkout before placing order.

| Field | Type | Validation |
|-------|------|------------|
| `address_id` | integer | required, exists:addresses |
| `shipping_method` | string | required, in:standard,express |

**Success (200):** Order summary with final amounts (tax calculated).

---

### POST `/orders`

**Auth Required.** Place an order.

| Field | Type | Validation |
|-------|------|------------|
| `address_id` | integer | required, exists:addresses |
| `billing_address_id` | integer | optional, exists:addresses |
| `shipping_method` | string | required, in:standard,express |
| `payment_method` | string | required, in:razorpay,stripe,cod |
| `notes` | string | optional, max:500 |

**Success (201):** Order resource with payment initiation data (for online payments).

```json
{
    "success": true,
    "data": {
        "order": { ... },
        "payment": {
            "gateway": "razorpay",
            "order_id": "order_L3x9y2z",
            "amount": 471700,
            "currency": "INR",
            "key": "rzp_live_xxx"
        }
    }
}
```

**Errors:** 422 (validation), 409 (out of stock), 400 (cart empty)

---

### POST `/orders/{uuid}/verify-payment`

Verify payment after gateway redirect/callback.

| Field | Type | Validation |
|-------|------|------------|
| `payment_id` | string | required |
| `order_id` | string | required (gateway order id) |
| `signature` | string | required |

**Success (200):** Updated order with confirmed status.
**Errors:** 400 (payment verification failed)

---

### GET `/orders`

**Auth Required.** List customer's orders.

| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status |
| `sort_by` | string | `created_at` |

**Success (200):** Paginated order collection.

---

### GET `/orders/{uuid}`

**Auth Required.** Get single order details.

**Success (200):** Order with items, status history, payment, shipping.

---

### POST `/orders/{uuid}/cancel`

**Auth Required.** Cancel an order (before shipping).

| Field | Type | Validation |
|-------|------|------------|
| `reason` | string | required, max:500 |

**Success (200):** Updated order with cancelled status.
**Errors:** 409 (cannot cancel — already shipped/delivered)

---

### POST `/orders/{uuid}/return`

**Auth Required.** Request a return.

| Field | Type | Validation |
|-------|------|------------|
| `items` | array | required, min:1 |
| `items.*.order_item_id` | integer | required |
| `items.*.quantity` | integer | required, min:1 |
| `items.*.reason` | string | required, in:wrong_size,defective,not_as_described,wrong_item,other |
| `items.*.photos` | array | optional, max:5 images |
| `description` | string | optional, max:1000 |

**Success (201):** Return request resource.
**Errors:** 409 (return window expired, non-returnable item)

---

### GET `/orders/{uuid}/track`

Track order status.

**Success (200):** Status timeline with timestamps.

---

## 6. Customer APIs

### GET `/addresses`

**Auth Required.** List user's addresses.

---

### POST `/addresses`

**Auth Required.** Add new address.

| Field | Type | Validation |
|-------|------|------------|
| `label` | string | optional, max:50 |
| `first_name` | string | required, max:100 |
| `last_name` | string | required, max:100 |
| `phone` | string | required, digits:10 |
| `address_line_1` | string | required, max:255 |
| `address_line_2` | string | optional, max:255 |
| `city` | string | required, max:100 |
| `state` | string | required, max:100 |
| `postal_code` | string | required, digits:6 |
| `country` | string | optional, default:IN |
| `landmark` | string | optional, max:200 |
| `is_default_shipping` | boolean | optional |
| `is_default_billing` | boolean | optional |

**Success (201):** Address resource.
**Errors:** 409 (max 5 addresses reached)

---

### PUT `/addresses/{id}`

**Auth Required.** Update address.

---

### DELETE `/addresses/{id}`

**Auth Required.** Delete address.

---

## 7. Wishlist APIs

### GET `/wishlist`

**Auth Required.** List wishlist items.

---

### POST `/wishlist`

**Auth Required.** Add to wishlist.

| Field | Type | Validation |
|-------|------|------------|
| `product_id` | integer | required, exists |

**Success (201):** Wishlist item resource.
**Errors:** 409 (already in wishlist)

---

### DELETE `/wishlist/{product_id}`

**Auth Required.** Remove from wishlist.

---

## 8. Review APIs

### POST `/products/{slug}/reviews`

**Auth Required.** Submit a review.

| Field | Type | Validation |
|-------|------|------------|
| `rating` | integer | required, between:1,5 |
| `title` | string | optional, max:200 |
| `body` | string | optional, max:2000 |
| `photos` | array | optional, max:5, each: image, max:5MB |

**Success (201):** Review resource.
**Errors:** 409 (already reviewed this product)

---

## 9. Blog APIs (Public)

### GET `/blog/posts`

List published blog posts.

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | string | Category slug |
| `tag` | string | Tag slug |
| `search` | string | Search query |

---

### GET `/blog/posts/{slug}`

Get single blog post with full content.

---

### GET `/blog/categories`

List blog categories.

---

## 10. Coupon APIs

### POST `/coupons/validate`

Validate a coupon code (without applying).

| Field | Type | Validation |
|-------|------|------------|
| `code` | string | required |

**Success (200):** Coupon details with eligibility status.

---

## 11. Admin: Product APIs

**All admin routes require:** `auth:sanctum` + admin role.
**Prefix:** `/admin`

### GET `/admin/products`

List all products (including inactive).

| Parameter | Type | Description |
|-----------|------|-------------|
| `filter[status]` | string | `active`, `inactive`, `out_of_stock` |
| `filter[category_id]` | integer | Category filter |
| `filter[brand_id]` | integer | Brand filter |
| `search` | string | Name, SKU search |

---

### POST `/admin/products`

Create product.

| Field | Type | Validation |
|-------|------|------------|
| `name` | string | required, max:300 |
| `category_id` | integer | required, exists |
| `brand_id` | integer | optional, exists |
| `short_description` | string | optional, max:500 |
| `description` | string | optional |
| `mrp` | decimal | required, gt:0 |
| `selling_price` | decimal | required, gt:0, lte:mrp |
| `cost_price` | decimal | optional, gt:0 |
| `tax_category` | string | optional |
| `gst_rate` | decimal | optional |
| `hsn_code` | string | optional |
| `material` | string | optional |
| `care_instructions` | string | optional |
| `weight` | decimal | optional |
| `is_returnable` | boolean | optional, default:true |
| `return_window_days` | integer | optional, default:7 |
| `meta_title` | string | optional, max:200 |
| `meta_description` | string | optional, max:500 |
| `is_active` | boolean | optional, default:true |
| `is_featured` | boolean | optional |
| `is_new_arrival` | boolean | optional |
| `tags` | array | optional, array of tag IDs |
| `variants` | array | required, min:1 |
| `variants.*.sku` | string | required, unique |
| `variants.*.size` | string | optional |
| `variants.*.color` | string | optional |
| `variants.*.color_code` | string | optional |
| `variants.*.selling_price` | decimal | optional |
| `variants.*.stock_quantity` | integer | optional, default:0 |
| `variants.*.low_stock_threshold` | integer | optional, default:5 |

---

### PUT `/admin/products/{id}`

Update product. Same fields as create (all optional).

---

### DELETE `/admin/products/{id}`

Soft-delete product. **Errors:** 409 (has pending orders).

---

### POST `/admin/products/{id}/images`

Upload product images (multipart/form-data).

| Field | Type | Validation |
|-------|------|------------|
| `images` | array | required, each: image, max:5MB |
| `variant_id` | integer | optional |

---

### DELETE `/admin/products/{id}/images/{imageId}`

Delete product image.

---

### PUT `/admin/products/{id}/images/reorder`

Reorder product images.

| Field | Type | Validation |
|-------|------|------------|
| `image_ids` | array | required, ordered list of image IDs |

---

### CRUD: `/admin/categories`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/categories` | List all (tree) |
| POST | `/admin/categories` | Create |
| PUT | `/admin/categories/{id}` | Update |
| DELETE | `/admin/categories/{id}` | Delete (if no products) |
| PUT | `/admin/categories/reorder` | Reorder |

### CRUD: `/admin/brands`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/brands` | List all |
| POST | `/admin/brands` | Create |
| PUT | `/admin/brands/{id}` | Update |
| DELETE | `/admin/brands/{id}` | Delete (if no products) |

### CRUD: `/admin/attributes`

Standard CRUD for product attributes and values.

### CRUD: `/admin/tags`

Standard CRUD for tags.

---

## 12. Admin: Order APIs

### GET `/admin/orders`

List all orders with filters.

| Parameter | Type | Description |
|-----------|------|-------------|
| `filter[status]` | string | Order status |
| `filter[payment_status]` | string | Payment status |
| `filter[date_from]` | date | Start date |
| `filter[date_to]` | date | End date |
| `filter[customer_id]` | integer | Filter by customer |
| `search` | string | Order number, customer name/email |

---

### GET `/admin/orders/{id}`

Get full order details with history, payment, items.

---

### PUT `/admin/orders/{id}/status`

Update order status.

| Field | Type | Validation |
|-------|------|------------|
| `status` | string | required, valid next status |
| `comment` | string | optional, max:500 |
| `tracking_number` | string | required if status=shipped |
| `courier_name` | string | required if status=shipped |

**Errors:** 409 (invalid status transition)

---

### POST `/admin/orders/{id}/cancel`

Admin cancel order.

| Field | Type | Validation |
|-------|------|------------|
| `reason` | string | required, max:500 |
| `notify_customer` | boolean | optional, default:true |

---

### POST `/admin/orders/{id}/refund`

Process refund.

| Field | Type | Validation |
|-------|------|------------|
| `amount` | decimal | required, gt:0, lte:order.grand_total |
| `reason` | string | required, max:500 |

---

### GET `/admin/orders/{id}/invoice`

Download invoice PDF.

---

### GET `/admin/orders/export`

Export orders as CSV.

| Parameter | Type | Description |
|-----------|------|-------------|
| `filter[date_from]` | date | Start date |
| `filter[date_to]` | date | End date |
| `filter[status]` | string | Status filter |

**Success (200):** Queued export job, returns download URL when ready.

---

### Admin Returns

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/returns` | List all returns |
| GET | `/admin/returns/{id}` | Return details |
| PUT | `/admin/returns/{id}/approve` | Approve return |
| PUT | `/admin/returns/{id}/reject` | Reject return |
| PUT | `/admin/returns/{id}/qc` | Update QC status |

---

## 13. Admin: Inventory APIs

### GET `/admin/inventory`

List stock levels for all variants.

| Parameter | Type | Description |
|-----------|------|-------------|
| `filter[status]` | string | `ok`, `low`, `out_of_stock` |
| `filter[category_id]` | integer | Category |
| `search` | string | Product name, SKU |

---

### POST `/admin/inventory/adjust`

Stock adjustment.

| Field | Type | Validation |
|-------|------|------------|
| `product_variant_id` | integer | required, exists |
| `type` | string | required, in:adjustment_in,adjustment_out,damage |
| `quantity` | integer | required, gt:0 |
| `reason` | string | required, max:500 |
| `notes` | string | optional |

---

### GET `/admin/inventory/ledger`

Stock movement history.

| Parameter | Type | Description |
|-----------|------|-------------|
| `filter[product_variant_id]` | integer | Specific variant |
| `filter[type]` | string | Movement type |
| `filter[date_from]` | date | Start date |
| `filter[date_to]` | date | End date |

---

### Purchase Orders CRUD

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/purchase-orders` | List POs |
| POST | `/admin/purchase-orders` | Create PO |
| PUT | `/admin/purchase-orders/{id}` | Update PO |
| POST | `/admin/purchase-orders/{id}/receive` | Receive stock |
| DELETE | `/admin/purchase-orders/{id}` | Delete (draft only) |

---

## 14. Admin: Customer APIs

### GET `/admin/customers`

List customers with stats.

| Parameter | Type | Description |
|-----------|------|-------------|
| `filter[status]` | string | `active`, `inactive` |
| `filter[date_from]` | date | Registered after |
| `search` | string | Name, email, phone |
| `sort_by` | string | `total_spent`, `total_orders`, `created_at` |

---

### GET `/admin/customers/{id}`

Customer profile with orders, addresses, activity.

---

### PUT `/admin/customers/{id}`

Update customer (admin notes, status).

---

### GET `/admin/customers/{id}/orders`

Customer's order history.

---

## 15. Admin: Coupon APIs

Standard CRUD at `/admin/coupons`:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/coupons` | List all coupons |
| POST | `/admin/coupons` | Create coupon |
| GET | `/admin/coupons/{id}` | Coupon details |
| PUT | `/admin/coupons/{id}` | Update coupon |
| DELETE | `/admin/coupons/{id}` | Delete coupon |
| GET | `/admin/coupons/{id}/usage` | Usage history |

---

## 16. Admin: Report APIs

### GET `/admin/reports/sales`

| Parameter | Type | Description |
|-----------|------|-------------|
| `date_from` | date | required |
| `date_to` | date | required |
| `group_by` | string | `day`, `week`, `month` |

**Success (200):** Revenue, orders, avg order value, units sold, trend data, category/brand/payment breakdowns.

---

### GET `/admin/reports/inventory`

**Success (200):** Stock summary, low stock items, dead stock, valuation.

---

### GET `/admin/reports/customers`

| Parameter | Type | Description |
|-----------|------|-------------|
| `date_from` | date | required |
| `date_to` | date | required |

**Success (200):** New registrations, repeat rate, top customers, segments, geographic.

---

### GET `/admin/reports/revenue`

Financial report: gross revenue, discounts, taxes, refunds, net revenue.

---

## 17. Admin: Blog APIs

Standard CRUD at `/admin/blog/posts`:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/blog/posts` | List all posts (inc. drafts) |
| POST | `/admin/blog/posts` | Create post |
| GET | `/admin/blog/posts/{id}` | Post details |
| PUT | `/admin/blog/posts/{id}` | Update post |
| DELETE | `/admin/blog/posts/{id}` | Delete post |

Blog category/tag CRUD at `/admin/blog/categories` and `/admin/blog/tags`.

---

## 18. Admin: Settings APIs

### GET `/admin/settings`

Get all settings grouped.

---

### PUT `/admin/settings`

Update settings.

| Field | Type | Validation |
|-------|------|------------|
| `settings` | object | key-value pairs of setting updates |

---

### POST `/admin/settings/test-email`

Send test email.

| Field | Type | Validation |
|-------|------|------------|
| `to` | string | required, email |

---

### POST `/admin/settings/test-sms`

Send test SMS.

| Field | Type | Validation |
|-------|------|------------|
| `to` | string | required, phone |

---

## 19. Admin: Dashboard APIs

### GET `/admin/dashboard/stats`

| Parameter | Type | Description |
|-----------|------|-------------|
| `period` | string | `today`, `7d`, `30d`, `year` |

**Success (200):**

```json
{
    "revenue": { "value": 124500, "change": 12.5, "trend": "up" },
    "orders": { "value": 42, "change": 8.0, "trend": "up" },
    "customers": { "value": 128, "change": 15.2, "trend": "up" },
    "low_stock_count": 15,
    "pending_orders": 5,
    "pending_returns": 3
}
```

---

### GET `/admin/dashboard/chart`

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | `revenue`, `orders` |
| `period` | string | `7d`, `30d`, `year` |

**Success (200):** Chart data points `[{ "date": "2026-06-25", "value": 15000 }, ...]`

---

### GET `/admin/dashboard/recent-orders`

Latest 10 orders.

---

### GET `/admin/dashboard/top-products`

Top 10 selling products for the period.

---

### GET `/admin/dashboard/low-stock`

Products with stock below threshold.

---

### GET `/admin/dashboard/activity`

Recent admin activity log.

---

## 20. Admin: User & Role APIs

### Users CRUD: `/admin/users`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/users` | List admin users |
| POST | `/admin/users` | Create admin user |
| PUT | `/admin/users/{id}` | Update user |
| DELETE | `/admin/users/{id}` | Deactivate user |

### Roles CRUD: `/admin/roles`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/roles` | List roles |
| POST | `/admin/roles` | Create role |
| PUT | `/admin/roles/{id}` | Update role |
| DELETE | `/admin/roles/{id}` | Delete role (if no users) |

### GET `/admin/permissions`

List all available permissions grouped.

---

## 21. Webhook APIs

### POST `/webhooks/razorpay`

Razorpay payment webhook. Verified via `X-Razorpay-Signature` header.

Events handled: `payment.authorized`, `payment.captured`, `payment.failed`, `refund.processed`

---

### POST `/webhooks/stripe`

Stripe payment webhook. Verified via `Stripe-Signature` header.

Events handled: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`

---

## 22. Error Codes

| Error Code | HTTP Status | Description |
|------------|-------------|-------------|
| `VALIDATION_ERROR` | 422 | Request validation failed |
| `AUTHENTICATION_ERROR` | 401 | Not authenticated |
| `AUTHORIZATION_ERROR` | 403 | Not authorized |
| `NOT_FOUND` | 404 | Resource not found |
| `CONFLICT` | 409 | Business rule violation |
| `RATE_LIMIT_EXCEEDED` | 429 | Too many requests |
| `OUT_OF_STOCK` | 409 | Insufficient stock |
| `INVALID_COUPON` | 409 | Coupon not applicable |
| `COUPON_EXPIRED` | 409 | Coupon has expired |
| `COUPON_USAGE_LIMIT` | 409 | Coupon usage limit reached |
| `PAYMENT_FAILED` | 400 | Payment verification failed |
| `ORDER_NOT_CANCELLABLE` | 409 | Order cannot be cancelled |
| `RETURN_WINDOW_EXPIRED` | 409 | Return window has expired |
| `NON_RETURNABLE` | 409 | Product is non-returnable |
| `MAX_ADDRESSES` | 409 | Maximum addresses reached |
| `ALREADY_REVIEWED` | 409 | Already reviewed this product |
| `INVALID_STATUS_TRANSITION` | 409 | Invalid order status change |
| `SERVER_ERROR` | 500 | Internal server error |

### Rate Limits

| Route Group | Limit | Window |
|-------------|-------|--------|
| Public | 60 requests | per minute |
| Authenticated | 120 requests | per minute |
| Auth (login/register) | 5 attempts | per minute |
| Webhooks | 100 requests | per minute |
| Admin | 120 requests | per minute |

---

### API Endpoint Summary

| Group | Endpoints |
|-------|-----------|
| Authentication | 8 |
| Products (Public) | 7 |
| Cart | 7 |
| Orders (Customer) | 6 |
| Customer (Profile, Addresses) | 6 |
| Wishlist | 3 |
| Reviews | 1 |
| Blog (Public) | 3 |
| Coupons (Public) | 1 |
| Admin: Products | 15+ |
| Admin: Orders | 10+ |
| Admin: Inventory | 8+ |
| Admin: Customers | 5 |
| Admin: Coupons | 6 |
| Admin: Reports | 4 |
| Admin: Blog | 8 |
| Admin: Settings | 4 |
| Admin: Dashboard | 6 |
| Admin: Users & Roles | 8 |
| Webhooks | 2 |
| **Total** | **~110+** |

---

*End of API Specification Document — Phase 7*
