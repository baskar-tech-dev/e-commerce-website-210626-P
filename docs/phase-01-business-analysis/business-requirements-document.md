# Phase 1: Business Requirements Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** Senior Business Analyst
**Status:** Draft

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Business Objectives](#2-business-objectives)
3. [Stakeholders](#3-stakeholders)
4. [User Personas](#4-user-personas)
5. [Customer Journey](#5-customer-journey)
6. [Order Journey](#6-order-journey)
7. [Return & Refund Flow](#7-return--refund-flow)
8. [Inventory Flow](#8-inventory-flow)
9. [Payment Flow](#9-payment-flow)
10. [Billing Flow](#10-billing-flow)
11. [Coupon & Promotions Flow](#11-coupon--promotions-flow)
12. [Notification Flow](#12-notification-flow)
13. [Blog & Content Flow](#13-blog--content-flow)
14. [Admin Workflow](#14-admin-workflow)
15. [Reporting Requirements](#15-reporting-requirements)
16. [Non-Functional Requirements](#16-non-functional-requirements)
17. [Business Rules](#17-business-rules)
18. [Glossary](#18-glossary)

---

## 1. Executive Summary

This document defines the complete business requirements for a **Fashion E-Commerce Platform** — an online retail platform specializing in fashion products (clothing, footwear, accessories) targeting both B2C customers and internal operations teams.

The platform will support:
- **Customer-facing storefront** — browse, search, purchase fashion products
- **Admin panel** — manage products, orders, inventory, customers, reports
- **Integrated services** — payment processing, email notifications, SMS alerts
- **Content management** — blogs, pages, SEO content

### Business Model

| Aspect | Detail |
|--------|--------|
| Type | B2C Fashion Retail |
| Revenue | Product sales, potential future marketplace commissions |
| Target Market | Fashion-conscious consumers, age 18–45 |
| Product Types | Clothing, Footwear, Accessories, Bags, Jewelry |
| Geography | India (primary), expandable internationally |
| Currency | INR (primary), multi-currency ready |
| Languages | English (primary), Hindi (secondary) |

---

## 2. Business Objectives

### Primary Objectives

| # | Objective | Success Metric |
|---|-----------|----------------|
| O1 | Launch a fully functional fashion e-commerce store | Platform live with 500+ products |
| O2 | Provide seamless shopping experience | Cart abandonment rate < 30% |
| O3 | Efficient order fulfillment | Order processing time < 24 hours |
| O4 | Real-time inventory management | Stock accuracy > 99% |
| O5 | Secure payment processing | Zero payment fraud incidents |
| O6 | Customer retention | Repeat purchase rate > 25% |

### Secondary Objectives

| # | Objective | Success Metric |
|---|-----------|----------------|
| O7 | SEO-optimized for organic traffic | Top 10 for 50+ fashion keywords |
| O8 | Mobile-first experience | 70%+ mobile conversion rate parity |
| O9 | Actionable analytics | Daily reports for key business metrics |
| O10 | Scalable infrastructure | Support 10,000+ concurrent users |

---

## 3. Stakeholders

| Stakeholder | Role | Responsibilities |
|-------------|------|-----------------|
| Business Owner | Decision Maker | Strategy, pricing, promotions |
| Store Manager | Operations | Day-to-day operations, order management |
| Inventory Manager | Stock Control | Purchase orders, stock levels, damage tracking |
| Marketing Manager | Growth | Coupons, blogs, email campaigns |
| Customer Support | Service | Order issues, returns, inquiries |
| Customer (End User) | Buyer | Browse, purchase, review products |
| Finance Team | Accounting | Billing, reconciliation, tax compliance |
| IT Administrator | Technical | Platform maintenance, user management |

---

## 4. User Personas

### Persona 1: Fashion Shopper (Primary Customer)

| Attribute | Detail |
|-----------|--------|
| Name | Priya, 28 |
| Occupation | Marketing Professional |
| Behavior | Browses on mobile during commute, compares products, values reviews |
| Pain Points | Slow loading, unclear size guides, complicated checkout |
| Goals | Find trendy clothes quickly, get good deals, easy returns |
| Preferred Payment | UPI, Credit Card |

### Persona 2: Casual Buyer

| Attribute | Detail |
|-----------|--------|
| Name | Rahul, 35 |
| Occupation | Software Engineer |
| Behavior | Buys occasionally for events, values quality over price |
| Pain Points | Poor product images, inaccurate descriptions |
| Goals | Buy specific items efficiently, track delivery accurately |
| Preferred Payment | Net Banking, Debit Card |

### Persona 3: Gift Buyer

| Attribute | Detail |
|-----------|--------|
| Name | Sneha, 42 |
| Occupation | HR Manager |
| Behavior | Buys gifts for family/friends, values gift-wrapping options |
| Pain Points | Unsure about sizes, wants easy exchange |
| Goals | Easy gift selection, reliable delivery by specific date |
| Preferred Payment | Credit Card, Wallet |

### Persona 4: Store Administrator

| Attribute | Detail |
|-----------|--------|
| Name | Amit, 30 |
| Occupation | E-commerce Manager |
| Behavior | Manages daily operations, processes orders, updates inventory |
| Pain Points | Manual processes, delayed reports, no real-time stock visibility |
| Goals | Efficient operations, automated workflows, instant insights |

---

## 5. Customer Journey

### 5.1 Complete Customer Journey Map

```
┌─────────────────────────────────────────────────────────────────────┐
│                        CUSTOMER JOURNEY                             │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  DISCOVERY          EXPLORATION         DECISION         ACTION     │
│  ─────────          ───────────         ────────         ──────     │
│                                                                     │
│  ┌──────────┐      ┌──────────┐      ┌──────────┐    ┌──────────┐ │
│  │  Visit   │─────▶│  Browse  │─────▶│  Compare │───▶│  Add to  │ │
│  │  Website │      │ Products │      │ Products │    │   Cart   │ │
│  └──────────┘      └──────────┘      └──────────┘    └──────────┘ │
│       │                 │                                   │       │
│       ▼                 ▼                                   ▼       │
│  ┌──────────┐      ┌──────────┐                      ┌──────────┐ │
│  │  Search  │      │  Filter  │                      │ Checkout │ │
│  │ Products │      │  & Sort  │                      │          │ │
│  └──────────┘      └──────────┘                      └──────────┘ │
│                                                            │       │
│  PURCHASE          FULFILLMENT        POST-PURCHASE        │       │
│  ────────          ───────────        ─────────────        │       │
│                                                            ▼       │
│  ┌──────────┐      ┌──────────┐      ┌──────────┐    ┌──────────┐ │
│  │  Payment │─────▶│  Order   │─────▶│ Delivery │───▶│  Review  │ │
│  │ Process  │      │ Confirm  │      │ Tracking │    │ Product  │ │
│  └──────────┘      └──────────┘      └──────────┘    └──────────┘ │
│                                                            │       │
│                                                            ▼       │
│                                                      ┌──────────┐ │
│                                                      │  Return  │ │
│                                                      │ (if any) │ │
│                                                      └──────────┘ │
└─────────────────────────────────────────────────────────────────────┘
```

### 5.2 Discovery Phase

| Step | Actor | Action | System Response | Touchpoint |
|------|-------|--------|-----------------|------------|
| D1 | Visitor | Lands on homepage via search/social/ad | Display hero banners, featured products, trending categories | Homepage |
| D2 | Visitor | Searches for a product | Show search suggestions (autocomplete), display results | Search Bar |
| D3 | Visitor | Clicks on a category | Display category page with filters | Category Page |
| D4 | Visitor | Browses trending/new arrivals section | Display curated product collections | Homepage Sections |

### 5.3 Exploration Phase

| Step | Actor | Action | System Response | Touchpoint |
|------|-------|--------|-----------------|------------|
| E1 | Visitor | Views product listing page | Display products with images, price, ratings, quick view | Listing Page |
| E2 | Visitor | Applies filters (size, color, price, brand) | Dynamically filter product list | Filter Sidebar |
| E3 | Visitor | Sorts products (price, popularity, newest) | Reorder product list | Sort Dropdown |
| E4 | Visitor | Clicks on a product | Display full product detail page | Product Detail |
| E5 | Visitor | Views product images (zoom, gallery) | Display image carousel with zoom | Product Gallery |
| E6 | Visitor | Reads product description & specs | Display detailed info, size chart | Product Info |
| E7 | Visitor | Reads customer reviews | Display reviews with ratings, photos | Reviews Section |
| E8 | Visitor | Adds product to wishlist | Prompt login if guest; save to wishlist | Wishlist |

### 5.4 Decision Phase

| Step | Actor | Action | System Response | Touchpoint |
|------|-------|--------|-----------------|------------|
| C1 | Customer | Selects size and color variant | Update price, show stock availability | Variant Selector |
| C2 | Customer | Checks delivery availability (pincode) | Show estimated delivery date and cost | Delivery Check |
| C3 | Customer | Clicks "Add to Cart" | Add item to cart, show cart summary | Cart |
| C4 | Customer | Adjusts quantity in cart | Update quantities, recalculate totals | Cart Page |
| C5 | Customer | Applies coupon code | Validate coupon, show discount | Cart/Checkout |
| C6 | Customer | Proceeds to checkout | Display checkout flow | Checkout |

### 5.5 Purchase Phase

| Step | Actor | Action | System Response | Touchpoint |
|------|-------|--------|-----------------|------------|
| P1 | Customer | Enters/selects shipping address | Validate address, calculate shipping | Address Form |
| P2 | Customer | Selects shipping method | Update delivery estimate and cost | Shipping Options |
| P3 | Customer | Reviews order summary | Display items, prices, taxes, total | Order Summary |
| P4 | Customer | Selects payment method | Display payment options (UPI, Card, Net Banking, COD, Wallet) | Payment Selection |
| P5 | Customer | Completes payment | Process via payment gateway | Payment Gateway |
| P6 | System | Payment successful | Generate order, send confirmation email/SMS | Confirmation |
| P7 | System | Payment failed | Show error, allow retry, reserve cart | Error Page |

### 5.6 Post-Purchase Phase

| Step | Actor | Action | System Response | Touchpoint |
|------|-------|--------|-----------------|------------|
| PP1 | System | Order placed | Send order confirmation email + SMS | Email/SMS |
| PP2 | Customer | Checks order status | Display real-time order tracking | Order History |
| PP3 | System | Order shipped | Send shipping notification with tracking link | Email/SMS |
| PP4 | System | Order delivered | Send delivery confirmation, request review | Email/SMS |
| PP5 | Customer | Submits product review | Save review, update product rating | Review Form |
| PP6 | Customer | Initiates return (within return window) | Display return form with reasons | Return Request |

### 5.7 Guest vs Registered Customer

| Feature | Guest | Registered |
|---------|-------|------------|
| Browse Products | ✅ | ✅ |
| Add to Cart | ✅ | ✅ |
| Wishlist | ❌ (prompt login) | ✅ |
| Checkout | ✅ (with email) | ✅ |
| Order History | ❌ (order lookup by email) | ✅ |
| Track Orders | ✅ (via link in email) | ✅ |
| Submit Reviews | ❌ | ✅ |
| Apply Coupons | ✅ | ✅ |
| Saved Addresses | ❌ | ✅ |
| Earn Loyalty Points | ❌ | ✅ (future) |

---

## 6. Order Journey

### 6.1 Order Lifecycle State Machine

```
┌──────────────────────────────────────────────────────────────────┐
│                       ORDER LIFECYCLE                            │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────┐    ┌───────────┐    ┌────────────┐    ┌─────────┐ │
│  │ PENDING │───▶│ CONFIRMED │───▶│ PROCESSING │───▶│ PACKED  │ │
│  └─────────┘    └───────────┘    └────────────┘    └─────────┘ │
│       │                                                  │      │
│       │ (payment                                         │      │
│       │  failed)                                         ▼      │
│       ▼              ┌────────────┐              ┌───────────┐ │
│  ┌─────────┐         │  OUT FOR   │◀─────────────│  SHIPPED  │ │
│  │ FAILED  │         │  DELIVERY  │              └───────────┘ │
│  └─────────┘         └────────────┘                             │
│                            │                                    │
│                            ▼                                    │
│  ┌─────────┐         ┌───────────┐                              │
│  │CANCELLED│         │ DELIVERED │                              │
│  └─────────┘         └───────────┘                              │
│       ▲                    │                                    │
│       │                    ▼                                    │
│  (by customer         ┌───────────┐    ┌───────────┐           │
│   or admin)           │  RETURN   │───▶│ REFUNDED  │           │
│                       │ REQUESTED │    └───────────┘           │
│                       └───────────┘                             │
└──────────────────────────────────────────────────────────────────┘
```

### 6.2 Order Status Definitions

| Status | Description | Trigger | System Actions |
|--------|-------------|---------|----------------|
| **Pending** | Order created, awaiting payment confirmation | Customer places order | Reserve inventory, create order record |
| **Confirmed** | Payment verified, order accepted | Payment gateway callback | Deduct inventory, send confirmation email/SMS |
| **Processing** | Warehouse preparing order | Admin marks processing | Notify warehouse team |
| **Packed** | Order packed and ready for shipping | Warehouse marks packed | Generate shipping label, update weight |
| **Shipped** | Handed to courier | Admin enters tracking number | Send shipping email/SMS with tracking |
| **Out for Delivery** | Courier attempting delivery | Courier API webhook or manual | Send "out for delivery" notification |
| **Delivered** | Customer received order | Courier confirmation or manual | Send delivery confirmation, enable review |
| **Cancelled** | Order cancelled before shipping | Customer or admin cancels | Restore inventory, initiate refund |
| **Failed** | Payment failed or expired | Payment timeout/failure | Release reserved inventory, notify customer |
| **Return Requested** | Customer requested return | Customer submits return form | Create return record, notify admin |
| **Refunded** | Refund processed | Admin approves return + refund | Process refund via gateway, restore inventory |

### 6.3 Order Cancellation Rules

| Rule | Detail |
|------|--------|
| Customer can cancel | Before status reaches "Shipped" |
| Admin can cancel | At any status before "Delivered" |
| Auto-cancel | If payment not received within 30 minutes (for online payment) |
| Partial cancellation | Allowed — individual items can be cancelled from multi-item orders |
| Refund on cancellation | Automatic refund to original payment method (3-7 business days) |
| COD cancellation | No refund needed; inventory restored |

### 6.4 Order Notifications

| Event | Email | SMS | Push (Future) |
|-------|-------|-----|---------------|
| Order Placed | ✅ | ✅ | ✅ |
| Payment Confirmed | ✅ | ❌ | ❌ |
| Order Shipped | ✅ | ✅ | ✅ |
| Out for Delivery | ❌ | ✅ | ✅ |
| Delivered | ✅ | ✅ | ✅ |
| Cancelled | ✅ | ✅ | ❌ |
| Refund Processed | ✅ | ✅ | ❌ |
| Return Approved | ✅ | ❌ | ❌ |

---

## 7. Return & Refund Flow

### 7.1 Return Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                        RETURN FLOW                               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌───────────────┐    ┌───────────┐    ┌───────────────┐        │
│  │    RETURN     │───▶│  ADMIN    │───▶│   APPROVED    │        │
│  │   REQUESTED   │    │  REVIEW   │    │               │        │
│  └───────────────┘    └───────────┘    └───────────────┘        │
│                            │                   │                 │
│                            ▼                   ▼                 │
│                       ┌───────────┐    ┌───────────────┐        │
│                       │ REJECTED  │    │    PICKUP     │        │
│                       │           │    │  SCHEDULED    │        │
│                       └───────────┘    └───────────────┘        │
│                                              │                   │
│                                              ▼                   │
│                                        ┌───────────────┐        │
│                                        │   PICKED UP   │        │
│                                        └───────────────┘        │
│                                              │                   │
│                                              ▼                   │
│                                        ┌───────────────┐        │
│                                        │  QC CHECK     │        │
│                                        └───────────────┘        │
│                                           │        │             │
│                                           ▼        ▼             │
│                                    ┌─────────┐ ┌─────────┐      │
│                                    │  QC     │ │  QC     │      │
│                                    │ PASSED  │ │ FAILED  │      │
│                                    └─────────┘ └─────────┘      │
│                                         │           │            │
│                                         ▼           ▼            │
│                                    ┌─────────┐ ┌─────────────┐  │
│                                    │ REFUND  │ │ RETURN TO   │  │
│                                    │PROCESSED│ │  CUSTOMER   │  │
│                                    └─────────┘ └─────────────┘  │
└──────────────────────────────────────────────────────────────────┘
```

### 7.2 Return Policy Rules

| Rule | Detail |
|------|--------|
| Return Window | 7 days from delivery date (configurable per category) |
| Eligible Reasons | Wrong size, Defective product, Not as described, Wrong item delivered |
| Non-Returnable | Innerwear, swimwear, customized items, sale items (configurable) |
| Condition | Product must be unused, with original tags and packaging |
| Photo Required | Customer must upload photos for defective/wrong item claims |
| Exchange Option | Customer can request exchange instead of refund (same product, different size/color) |

### 7.3 Refund Rules

| Rule | Detail |
|------|--------|
| Refund Method | Original payment method (mandatory) |
| Refund Timeline | 5-7 business days after QC approval |
| Partial Refund | Allowed for partially damaged items (admin discretion) |
| COD Refund | Bank transfer (customer must provide bank details) |
| Shipping Refund | Refunded only if return reason is seller's fault |
| Coupon Adjustment | If coupon was applied, refund amount adjusted proportionally |

---

## 8. Inventory Flow

### 8.1 Inventory Lifecycle

```
┌──────────────────────────────────────────────────────────────────┐
│                      INVENTORY FLOW                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐                                                │
│  │   OPENING    │                                                │
│  │    STOCK     │                                                │
│  └──────┬───────┘                                                │
│         │                                                        │
│         ▼                                                        │
│  ┌──────────────┐    STOCK IN                                    │
│  │   CURRENT    │◀──────────── Purchase Entry                    │
│  │    STOCK     │◀──────────── Customer Return (QC Passed)       │
│  │              │◀──────────── Stock Adjustment (+)               │
│  │              │◀──────────── Transfer In                        │
│  │              │                                                │
│  │              │    STOCK OUT                                   │
│  │              │─────────────▶ Customer Order (Confirmed)       │
│  │              │─────────────▶ Damage / Write-off               │
│  │              │─────────────▶ Stock Adjustment (-)              │
│  │              │─────────────▶ Transfer Out                      │
│  └──────────────┘                                                │
│         │                                                        │
│         ▼                                                        │
│  ┌──────────────┐                                                │
│  │   CLOSING    │                                                │
│  │    STOCK     │                                                │
│  └──────────────┘                                                │
└──────────────────────────────────────────────────────────────────┘
```

### 8.2 Inventory Ledger (Double-Entry)

Every stock movement is recorded as a ledger entry:

| Field | Description |
|-------|-------------|
| `id` | Unique ledger entry ID |
| `product_variant_id` | Which product variant |
| `type` | PURCHASE, SALE, RETURN, DAMAGE, ADJUSTMENT, TRANSFER |
| `direction` | IN or OUT |
| `quantity` | Number of units |
| `reference_type` | Order, PurchaseOrder, Adjustment, etc. |
| `reference_id` | ID of the reference document |
| `stock_before` | Stock level before this entry |
| `stock_after` | Stock level after this entry |
| `notes` | Additional context |
| `created_by` | User who created the entry |
| `created_at` | Timestamp |

### 8.3 Inventory Rules

| Rule | Detail |
|------|--------|
| Stock Reservation | When order is placed (pending), stock is reserved (soft hold) |
| Stock Deduction | When order is confirmed (payment verified), stock is deducted |
| Reservation Timeout | Reserved stock released after 30 minutes if payment not completed |
| Negative Stock | Not allowed — orders fail if stock insufficient |
| Low Stock Alert | Configurable threshold per product (default: 5 units) |
| Out of Stock | Product variant hidden from storefront (configurable: hide or show as "out of stock") |
| Restock Alert | Notification when stock replenished for out-of-stock products |
| Stock Audit | Monthly physical stock audit with variance reporting |

### 8.4 Stock Valuation

| Method | Detail |
|--------|--------|
| Valuation | Weighted Average Cost (WAC) |
| Cost Tracking | Purchase price recorded per batch |
| Margin Calculation | Selling Price - WAC = Gross Margin |

---

## 9. Payment Flow

### 9.1 Payment Process

```
┌──────────────────────────────────────────────────────────────────┐
│                       PAYMENT FLOW                               │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────┐    ┌──────────────┐    ┌───────────────┐          │
│  │  CART    │───▶│   APPLY     │───▶│  CALCULATE    │          │
│  │  TOTAL   │    │   COUPON    │    │    TAXES      │          │
│  └──────────┘    └──────────────┘    └───────────────┘          │
│                                             │                    │
│                                             ▼                    │
│                                      ┌───────────────┐          │
│                                      │  ADD SHIPPING │          │
│                                      │    CHARGES    │          │
│                                      └───────────────┘          │
│                                             │                    │
│                                             ▼                    │
│                                      ┌───────────────┐          │
│                                      │ GRAND TOTAL   │          │
│                                      └───────────────┘          │
│                                             │                    │
│                         ┌───────────────────┼───────────────┐   │
│                         ▼                   ▼               ▼   │
│                  ┌────────────┐     ┌────────────┐   ┌────────┐│
│                  │   ONLINE   │     │    COD     │   │ WALLET ││
│                  │  PAYMENT   │     │            │   │        ││
│                  └────────────┘     └────────────┘   └────────┘│
│                        │                  │               │     │
│                        ▼                  ▼               ▼     │
│                  ┌────────────┐     ┌────────────┐  ┌────────┐ │
│                  │  PAYMENT   │     │   ORDER    │  │  DEBIT │ │
│                  │  GATEWAY   │     │ CONFIRMED  │  │ WALLET │ │
│                  │ (Razorpay/ │     │  (COD)     │  │        │ │
│                  │  Stripe)   │     └────────────┘  └────────┘ │
│                  └────────────┘                           │     │
│                     │      │                             │     │
│                     ▼      ▼                             ▼     │
│              ┌─────────┐ ┌─────────┐              ┌─────────┐ │
│              │ SUCCESS │ │ FAILURE │              │ SUCCESS │ │
│              └─────────┘ └─────────┘              └─────────┘ │
│                   │           │                        │       │
│                   ▼           ▼                        ▼       │
│              ┌─────────┐ ┌─────────┐              ┌─────────┐ │
│              │  ORDER  │ │  RETRY  │              │  ORDER  │ │
│              │CONFIRMED│ │  / FAIL │              │CONFIRMED│ │
│              └─────────┘ └─────────┘              └─────────┘ │
└──────────────────────────────────────────────────────────────────┘
```

### 9.2 Supported Payment Methods

| Method | Provider | Type |
|--------|----------|------|
| UPI | Razorpay | Online |
| Credit Card | Razorpay / Stripe | Online |
| Debit Card | Razorpay / Stripe | Online |
| Net Banking | Razorpay | Online |
| Wallets (Paytm, PhonePe) | Razorpay | Online |
| Cash on Delivery (COD) | Internal | Offline |
| Store Wallet | Internal | Online |

### 9.3 Payment Rules

| Rule | Detail |
|------|--------|
| COD Limit | Max ₹5,000 per order (configurable) |
| COD Availability | Based on pincode, configurable |
| Payment Timeout | 30 minutes for online payment |
| Retry Limit | 3 attempts per order |
| Partial Payment | Not supported in v1 |
| Refund | Auto-refund to original payment method |
| COD to Prepaid | Offer discount for prepaid orders |
| Payment Verification | Webhook-based verification (no polling) |

### 9.4 Payment Reconciliation

| Process | Frequency | Detail |
|---------|-----------|--------|
| Gateway Settlement | Daily | Match gateway settlements with orders |
| COD Collection | Per delivery | Mark COD collected by courier |
| Refund Tracking | Continuous | Track refund status until completed |
| Dispute Handling | As needed | Log and track chargebacks |

---

## 10. Billing Flow

### 10.1 Invoice Generation

| Trigger | Action |
|---------|--------|
| Order Confirmed | Generate proforma invoice |
| Payment Received | Generate tax invoice |
| Return/Refund | Generate credit note |

### 10.2 Invoice Components

| Component | Detail |
|-----------|--------|
| Invoice Number | Auto-generated, sequential (INV-2026-000001) |
| Customer Details | Name, email, phone, billing address |
| Shipping Address | Delivery address |
| Item Details | Product name, SKU, variant, quantity, unit price |
| Subtotal | Sum of items |
| Discount | Coupon discount breakdown |
| Tax | GST breakdown (CGST + SGST or IGST based on state) |
| Shipping | Shipping charges |
| Grand Total | Final payable amount |
| Payment Method | Method used for payment |
| Payment Status | Paid / COD / Pending |

### 10.3 Tax Rules (India - GST)

| Category | GST Rate |
|----------|----------|
| Clothing (< ₹1,000) | 5% |
| Clothing (≥ ₹1,000) | 12% |
| Footwear (< ₹1,000) | 5% |
| Footwear (≥ ₹1,000) | 18% |
| Accessories | 18% |
| Bags & Luggage | 18% |
| Jewelry (Imitation) | 3% |

> **Note:** Tax rates are configurable. GST type (CGST+SGST vs IGST) determined by seller and buyer states.

---

## 11. Coupon & Promotions Flow

### 11.1 Coupon Types

| Type | Description | Example |
|------|-------------|---------|
| Percentage Discount | % off on cart/product | 20% off (max ₹500) |
| Flat Discount | Fixed amount off | ₹200 off on orders above ₹999 |
| Free Shipping | Waive shipping charges | Free shipping on ₹499+ |
| Buy X Get Y | Buy products, get free item | Buy 2 Get 1 Free |
| First Order | Discount for new customers | 15% off first order |
| Category Specific | Discount on specific categories | 30% off on Dresses |
| Brand Specific | Discount on specific brands | Flat 25% off Brand X |

### 11.2 Coupon Rules

| Rule | Detail |
|------|--------|
| Minimum Order Value | Configurable per coupon |
| Maximum Discount | Cap on discount amount |
| Usage Limit (Total) | Max number of total uses |
| Usage Limit (Per User) | Max uses per customer |
| Valid Period | Start date and end date |
| Applicable Products | All / Specific categories / Specific brands / Specific products |
| Excluded Products | Products excluded from coupon |
| Combinable | Whether can be combined with other coupons |
| Auto-Apply | Automatically apply when conditions met |
| First Purchase Only | Valid only for first-time buyers |
| Payment Method Specific | Extra discount for specific payment methods |

### 11.3 Coupon Validation Flow

```
Customer enters coupon code
    │
    ▼
Is coupon code valid?  ──NO──▶ "Invalid coupon code"
    │ YES
    ▼
Is coupon within valid date range?  ──NO──▶ "Coupon expired"
    │ YES
    ▼
Has total usage limit been reached?  ──YES──▶ "Coupon usage limit reached"
    │ NO
    ▼
Has user usage limit been reached?  ──YES──▶ "You've already used this coupon"
    │ NO
    ▼
Does cart meet minimum order value?  ──NO──▶ "Add ₹X more to use this coupon"
    │ YES
    ▼
Are cart items eligible?  ──NO──▶ "Coupon not applicable on these items"
    │ YES
    ▼
Apply coupon ──▶ Show discount amount
```

---

## 12. Notification Flow

### 12.1 Notification Channels

| Channel | Use Case | Provider |
|---------|----------|----------|
| Email | Transactional emails (order confirmation, shipping, etc.) | Mailgun |
| SMS | Critical alerts (OTP, order status, delivery) | Twilio |
| In-App | Admin panel notifications | Internal (database + real-time) |
| Push (Future) | Mobile app notifications | Firebase Cloud Messaging |

### 12.2 Email Notifications

| Event | Template | Recipient | Priority |
|-------|----------|-----------|----------|
| Registration | Welcome email | Customer | Normal |
| Email Verification | Verification link | Customer | High |
| Password Reset | Reset link | Customer | High |
| Order Placed | Order summary + payment status | Customer | High |
| Payment Confirmed | Payment receipt | Customer | High |
| Order Shipped | Tracking details | Customer | High |
| Order Delivered | Delivery confirmation + review CTA | Customer | Normal |
| Order Cancelled | Cancellation + refund info | Customer | High |
| Refund Processed | Refund confirmation | Customer | High |
| Return Approved | Return instructions | Customer | Normal |
| Back in Stock | Product availability alert | Customer | Normal |
| Abandoned Cart | Reminder with cart items | Customer | Low (queued) |
| Low Stock Alert | Stock warning | Admin | High |
| New Order Alert | New order notification | Admin | High |
| Daily Summary | Sales/order summary | Admin | Low (scheduled) |

### 12.3 SMS Notifications

| Event | Template | Priority |
|-------|----------|----------|
| OTP (Login/Register) | "Your OTP is {otp}. Valid for 10 minutes." | Critical |
| Order Placed | "Order #{id} placed successfully. Track: {link}" | High |
| Order Shipped | "Order #{id} shipped. Track: {link}" | High |
| Out for Delivery | "Order #{id} is out for delivery today." | High |
| Order Delivered | "Order #{id} delivered. Rate: {link}" | Normal |
| COD Reminder | "COD order #{id}. Keep ₹{amount} ready." | Normal |

### 12.4 Notification Preferences

| Setting | Options | Default |
|---------|---------|---------|
| Email Notifications | All / Essential Only / None | All |
| SMS Notifications | All / Essential Only / None | Essential Only |
| Promotional Emails | Opt-in / Opt-out | Opt-out |
| Order Updates | Always On | Always On |

---

## 13. Blog & Content Flow

### 13.1 Blog Management

| Feature | Detail |
|---------|--------|
| Posts | Title, content (rich text), excerpt, featured image, SEO meta |
| Categories | Hierarchical blog categories |
| Tags | Multiple tags per post |
| Author | Staff member attribution |
| Status | Draft → Under Review → Published → Archived |
| Scheduling | Publish at a future date/time |
| SEO | Custom slug, meta title, meta description, OG tags |
| Comments | Optional — moderated comments (future) |

### 13.2 Content Types

| Type | Purpose |
|------|---------|
| Blog Posts | Fashion tips, styling guides, trend reports |
| Lookbooks | Curated product collections with editorial content |
| Size Guides | Category-specific size charts |
| About Us | Company story, mission |
| Contact Us | Contact form, address, support info |
| FAQs | Frequently asked questions |
| Policies | Privacy, Terms, Shipping, Returns |

### 13.3 SEO Strategy for Content

| Element | Implementation |
|---------|----------------|
| URL Structure | `/blog/{category-slug}/{post-slug}` |
| Sitemap | Auto-generated XML sitemap including blog posts |
| Schema Markup | BlogPosting, Product, Organization structured data |
| Internal Linking | Auto-link related products within blog posts |
| Image Alt Text | Mandatory for all images |
| Open Graph | Full OG tag support for social sharing |

---

## 14. Admin Workflow

### 14.1 Admin Roles & Access

| Role | Access Level |
|------|-------------|
| Super Admin | Full access to everything |
| Store Manager | Products, orders, customers, inventory, basic reports |
| Inventory Manager | Inventory, purchase entries, stock adjustments |
| Content Manager | Blogs, static pages, SEO settings |
| Customer Support | Orders (view/update status), customers (view), returns |
| Finance | Reports, billing, payment reconciliation |
| Marketing | Coupons, promotions, email campaigns |

### 14.2 Admin Daily Workflow

```
┌──────────────────────────────────────────────────────────────────┐
│                    ADMIN DAILY WORKFLOW                           │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  MORNING                                                         │
│  ───────                                                         │
│  1. Review Dashboard                                             │
│     - Yesterday's sales summary                                  │
│     - Pending orders count                                       │
│     - Low stock alerts                                           │
│     - New customer registrations                                 │
│                                                                  │
│  2. Process New Orders                                           │
│     - Review pending orders                                      │
│     - Confirm valid orders                                       │
│     - Flag suspicious orders                                     │
│                                                                  │
│  MIDDAY                                                          │
│  ──────                                                          │
│  3. Manage Inventory                                             │
│     - Process purchase entries                                   │
│     - Handle low stock items                                     │
│     - Review stock adjustment requests                           │
│                                                                  │
│  4. Handle Returns & Support                                     │
│     - Review return requests                                     │
│     - Approve/reject returns                                     │
│     - Process approved refunds                                   │
│                                                                  │
│  AFTERNOON                                                       │
│  ─────────                                                       │
│  5. Update Products                                              │
│     - Add new products                                           │
│     - Update prices                                              │
│     - Manage product images                                      │
│                                                                  │
│  6. Marketing Activities                                         │
│     - Create/update coupons                                      │
│     - Schedule blog posts                                        │
│     - Review campaign performance                                │
│                                                                  │
│  END OF DAY                                                      │
│  ──────────                                                      │
│  7. Review Reports                                               │
│     - Daily sales report                                         │
│     - Inventory status                                           │
│     - Payment reconciliation                                     │
│     - Customer metrics                                           │
└──────────────────────────────────────────────────────────────────┘
```

### 14.3 Admin Operations by Module

#### Product Management

| Operation | Description | Frequency |
|-----------|-------------|-----------|
| Add Product | Create new product with variants, images, pricing | Weekly |
| Edit Product | Update details, pricing, images | As needed |
| Manage Variants | Add/edit sizes, colors, SKUs | As needed |
| Bulk Upload | CSV import for multiple products | Monthly |
| Bulk Price Update | Update prices for multiple products | Seasonal |
| Manage Categories | Create/edit category hierarchy | As needed |
| Manage Brands | Add/edit brand information | As needed |

#### Order Processing

| Operation | Description | SLA |
|-----------|-------------|-----|
| Review New Orders | Check for fraud, verify details | Within 2 hours |
| Confirm Orders | Move to processing | Within 4 hours |
| Generate Shipping | Create shipping label, assign courier | Within 24 hours |
| Update Tracking | Enter courier tracking number | At shipping |
| Handle Cancellations | Process cancellation requests | Within 4 hours |
| Manage Returns | Review and approve/reject return requests | Within 24 hours |

#### Inventory Operations

| Operation | Description | Frequency |
|-----------|-------------|-----------|
| Purchase Entry | Record new stock received | As received |
| Stock Adjustment | Correct stock discrepancies | As needed |
| Damage Recording | Record damaged/unsellable items | As discovered |
| Stock Count | Physical inventory audit | Monthly |
| Reorder Planning | Identify items needing reorder | Weekly |

---

## 15. Reporting Requirements

### 15.1 Sales Reports

| Report | Content | Frequency |
|--------|---------|-----------|
| Daily Sales | Orders, revenue, average order value | Daily |
| Sales by Category | Revenue breakdown by product category | Weekly |
| Sales by Brand | Revenue breakdown by brand | Weekly |
| Sales by Product | Top/bottom selling products | Weekly |
| Sales by Payment Method | Payment method distribution | Monthly |
| Sales by Geography | Revenue by state/city | Monthly |
| Sales Trend | YoY, MoM, WoW comparison | Monthly |

### 15.2 Inventory Reports

| Report | Content | Frequency |
|--------|---------|-----------|
| Stock Status | Current stock levels for all products | Real-time |
| Low Stock Alert | Products below reorder threshold | Real-time |
| Stock Movement | All stock in/out movements | Daily |
| Dead Stock | Products with no movement in 30/60/90 days | Monthly |
| Stock Valuation | Total inventory value (WAC) | Monthly |
| Damage Report | Damaged/written-off items | Monthly |

### 15.3 Customer Reports

| Report | Content | Frequency |
|--------|---------|-----------|
| New Registrations | New customer sign-ups | Daily |
| Customer Segments | Active, dormant, churned | Monthly |
| Top Customers | By revenue, order count | Monthly |
| Customer Lifetime Value | Average LTV by cohort | Quarterly |
| Geographic Distribution | Customer distribution by location | Monthly |

### 15.4 Financial Reports

| Report | Content | Frequency |
|--------|---------|-----------|
| Revenue Summary | Gross revenue, discounts, net revenue | Daily |
| Tax Report | GST collected (CGST, SGST, IGST) | Monthly |
| Refund Report | Refunds processed, reasons | Weekly |
| Payment Reconciliation | Gateway settlements vs. orders | Daily |
| COD Collection | COD orders, collected amounts | Daily |
| Coupon Performance | Coupon usage, discount impact | Monthly |

### 15.5 Operational Reports

| Report | Content | Frequency |
|--------|---------|-----------|
| Order Fulfillment | Processing times by stage | Daily |
| Return Rate | Returns by category, reason | Weekly |
| Cancellation Rate | Cancellations by reason | Weekly |
| Cart Abandonment | Abandoned carts, recovery rate | Daily |
| Search Analytics | Top searches, zero-result searches | Weekly |

---

## 16. Non-Functional Requirements

### 16.1 Performance

| Requirement | Target |
|-------------|--------|
| Page Load Time | < 2 seconds (initial), < 1 second (subsequent) |
| API Response Time | < 200ms (p95), < 500ms (p99) |
| Concurrent Users | 10,000+ |
| Database Queries per Request | < 10 (average) |
| Image Load Time | < 500ms (with CDN) |
| Search Response | < 300ms |
| Time to Interactive (TTI) | < 3 seconds on 3G |

### 16.2 Scalability

| Requirement | Target |
|-------------|--------|
| Horizontal Scaling | Stateless backend, session-less API |
| Product Catalog | Support 100,000+ products |
| Order Volume | 10,000+ orders per day |
| Image Storage | Unlimited via S3/CDN |
| Database | Read replica support |

### 16.3 Security

| Requirement | Target |
|-------------|--------|
| Authentication | Token-based (Sanctum), MFA (future) |
| Authorization | Role-based (RBAC) with granular permissions |
| Data Encryption | TLS 1.3 in transit, AES-256 at rest for PII |
| Password Policy | Min 8 chars, complexity rules, bcrypt hashing |
| Rate Limiting | Per IP and per user throttling |
| OWASP | Top 10 vulnerability prevention |
| PCI Compliance | No card data stored (delegated to payment gateway) |

### 16.4 Reliability

| Requirement | Target |
|-------------|--------|
| Uptime | 99.9% |
| Backup Frequency | Daily database + hourly incremental |
| Recovery Time (RTO) | < 1 hour |
| Recovery Point (RPO) | < 1 hour |
| Error Rate | < 0.1% of requests |

### 16.5 SEO

| Requirement | Implementation |
|-------------|----------------|
| SSR / Prerendering | Server-side rendering for critical pages (or prerendering via Vue meta) |
| Meta Tags | Dynamic title, description, OG tags per page |
| Structured Data | Product, BreadcrumbList, Organization, BlogPosting schema |
| Sitemap | Auto-generated XML sitemap |
| Robots.txt | Configured for proper crawling |
| Canonical URLs | Prevent duplicate content |
| URL Structure | Clean, keyword-rich URLs |
| Page Speed | Lighthouse score > 90 |

### 16.6 Accessibility (a11y)

| Requirement | Standard |
|-------------|----------|
| Compliance | WCAG 2.1 Level AA |
| Keyboard Navigation | Full keyboard support |
| Screen Reader | ARIA labels and landmarks |
| Color Contrast | Minimum 4.5:1 ratio |
| Focus Indicators | Visible focus states |
| Alt Text | All images have descriptive alt text |

### 16.7 Mobile

| Requirement | Target |
|-------------|--------|
| Approach | Mobile-first responsive design |
| Touch Targets | Minimum 44x44px |
| Viewport | Optimized for 320px to 1440px+ |
| Gestures | Swipe for image galleries, pull-to-refresh |
| PWA (Future) | Offline support, installable |

---

## 17. Business Rules

### 17.1 Product Rules

| # | Rule |
|---|------|
| BR-P1 | Every product must have at least one variant (size/color) |
| BR-P2 | Every variant must have a unique SKU |
| BR-P3 | Product prices must be > 0 |
| BR-P4 | Products must have at least one image |
| BR-P5 | Products without stock are shown as "Out of Stock" (not hidden) |
| BR-P6 | Maximum 10 images per product |
| BR-P7 | Products must belong to at least one category |
| BR-P8 | MRP (Maximum Retail Price) must be ≥ Selling Price |
| BR-P9 | Compare-at price (MRP) shown with strikethrough if different from selling price |

### 17.2 Order Rules

| # | Rule |
|---|------|
| BR-O1 | Minimum order value: ₹299 (configurable) |
| BR-O2 | Maximum items per order: 20 |
| BR-O3 | Customer can have multiple pending orders |
| BR-O4 | Order cannot be modified after confirmation |
| BR-O5 | Cancelled orders are soft-deleted (archived) |
| BR-O6 | Order number format: ORD-YYYYMMDD-XXXXX |
| BR-O7 | Free shipping above ₹999 (configurable) |
| BR-O8 | Shipping charges: ₹49 for standard, ₹99 for express |

### 17.3 Customer Rules

| # | Rule |
|---|------|
| BR-C1 | Email is unique per customer |
| BR-C2 | Phone number is unique per customer |
| BR-C3 | Customer can save up to 5 addresses |
| BR-C4 | One default shipping address and one default billing address |
| BR-C5 | Account deletion soft-deletes (anonymizes PII) |
| BR-C6 | Password reset link valid for 60 minutes |
| BR-C7 | Email verification required for full account access |

### 17.4 Inventory Rules

| # | Rule |
|---|------|
| BR-I1 | Stock cannot go negative |
| BR-I2 | All stock movements recorded in ledger |
| BR-I3 | Low stock threshold default: 5 units (configurable per product) |
| BR-I4 | Stock reserved on order placement, deducted on confirmation |
| BR-I5 | Reserved stock released after 30 minutes if payment not completed |
| BR-I6 | Stock adjustments require notes/reason |
| BR-I7 | Damage write-offs require approval from Inventory Manager |

---

## 18. Glossary

| Term | Definition |
|------|------------|
| SKU | Stock Keeping Unit — unique identifier for each product variant |
| MRP | Maximum Retail Price — highest price at which a product can be sold |
| GST | Goods and Services Tax — India's unified tax system |
| CGST | Central GST — tax collected by central government |
| SGST | State GST — tax collected by state government |
| IGST | Integrated GST — for inter-state transactions |
| COD | Cash on Delivery — payment collected at delivery |
| UPI | Unified Payments Interface — instant payment system in India |
| WAC | Weighted Average Cost — inventory valuation method |
| PII | Personally Identifiable Information |
| LTV | Lifetime Value — total revenue from a customer over time |
| OTP | One-Time Password |
| RBAC | Role-Based Access Control |
| SPA | Single Page Application |
| SSR | Server-Side Rendering |
| PWA | Progressive Web App |
| CDN | Content Delivery Network |
| OWASP | Open Web Application Security Project |
| WCAG | Web Content Accessibility Guidelines |
| OG | Open Graph — social media meta tags |

---

*End of Business Requirements Document — Phase 1*
