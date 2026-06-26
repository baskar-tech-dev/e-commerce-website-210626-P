# Phase 6: Admin Panel UX Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** Senior ERP UX Designer
**Status:** Draft

---

## Table of Contents

1. [Admin Navigation](#1-admin-navigation)
2. [Dashboard](#2-dashboard)
3. [Products Module](#3-products-module)
4. [Inventory Module](#4-inventory-module)
5. [Orders Module](#5-orders-module)
6. [Customers Module](#6-customers-module)
7. [Coupons Module](#7-coupons-module)
8. [Reports Module](#8-reports-module)
9. [Blogs Module](#9-blogs-module)
10. [Settings Module](#10-settings-module)
11. [User & Role Management](#11-user--role-management)
12. [Common Patterns](#12-common-patterns)

---

## 1. Admin Navigation

### 1.1 Sidebar Navigation

```
┌──────────────────────┐
│  [LOGO] Admin Panel  │
│                      │
│  ─────────────────── │
│                      │
│  📊 Dashboard        │
│                      │
│  CATALOG             │
│  📦 Products         │
│     ├─ All Products  │
│     ├─ Categories    │
│     ├─ Brands        │
│     └─ Attributes    │
│  📋 Inventory        │
│     ├─ Stock Levels  │
│     ├─ Purchases     │
│     └─ Adjustments   │
│                      │
│  SALES               │
│  🛒 Orders           │
│     ├─ All Orders    │
│     └─ Returns       │
│  👥 Customers        │
│  🎫 Coupons          │
│                      │
│  CONTENT             │
│  📝 Blog             │
│     ├─ Posts         │
│     └─ Categories    │
│                      │
│  ANALYTICS           │
│  📈 Reports          │
│     ├─ Sales         │
│     ├─ Inventory     │
│     ├─ Customers     │
│     └─ Revenue       │
│                      │
│  SYSTEM              │
│  ⚙️ Settings         │
│  👤 Users & Roles    │
│  📜 Audit Log        │
│                      │
│  ─────────────────── │
│  Collapse Sidebar [«]│
│                      │
│  🌙 Dark Mode        │
│  👤 Admin Name       │
│     [Logout]         │
└──────────────────────┘
```

### 1.2 Admin Header

```
┌─────────────────────────────────────────────────────────────────┐
│  [☰]  Dashboard / Overview          🔍 Quick search...         │
│                                                                 │
│                            🔔(3) 📧(5)  👤 Admin ▼             │
└─────────────────────────────────────────────────────────────────┘
   ↑ Breadcrumb               ↑ Notifications  ↑ Profile menu
```

### 1.3 Responsive Behavior

| Screen | Sidebar | Layout |
|--------|---------|--------|
| Desktop (1280px+) | Full expanded, always visible | Sidebar + Content |
| Tablet (768–1279px) | Collapsed (icons only), expand on hover | Icon sidebar + Content |
| Mobile (< 768px) | Hidden, hamburger menu overlay | Full-width content |

---

## 2. Dashboard

### 2.1 Dashboard Layout

```
┌─────────────────────────────────────────────────────────────────┐
│  Dashboard                              Today ▼ | Last 7 Days  │
│                                                                 │
│  ┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐      │
│  │ 💰        │ │ 🛒        │ │ 👥        │ │ 📦        │      │
│  │ REVENUE   │ │ ORDERS    │ │ CUSTOMERS │ │ PRODUCTS  │      │
│  │ ₹1,24,500 │ │ 42        │ │ 128       │ │ 15 low    │      │
│  │ ↑ 12%     │ │ ↑ 8%      │ │ ↑ 15%     │ │ stock     │      │
│  └───────────┘ └───────────┘ └───────────┘ └───────────┘      │
│    KPI cards with sparkline trend                               │
│                                                                 │
│  ┌────────────────────────────────┐ ┌──────────────────────┐   │
│  │                                │ │                      │   │
│  │    SALES CHART                 │ │   ORDER STATUS       │   │
│  │                                │ │                      │   │
│  │    Revenue / Orders over time  │ │   ● Pending: 5       │   │
│  │    Line/Bar chart              │ │   ● Processing: 8    │   │
│  │    [7 Days] [30 Days] [Year]   │ │   ● Shipped: 12      │   │
│  │                                │ │   ● Delivered: 420   │   │
│  │    ┌──────────────────────┐    │ │                      │   │
│  │    │  📈 Chart Area       │    │ │   Donut chart        │   │
│  │    │                      │    │ │                      │   │
│  │    └──────────────────────┘    │ │                      │   │
│  └────────────────────────────────┘ └──────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────┐  ┌────────────────────────┐   │
│  │                             │  │                        │   │
│  │   RECENT ORDERS             │  │   TOP SELLING          │   │
│  │                             │  │   PRODUCTS             │   │
│  │   #00042 Priya  ₹4,717 🟢  │  │                        │   │
│  │   #00041 Rahul  ₹2,399 🟡  │  │   1. Floral Maxi  128 │   │
│  │   #00040 Sneha  ₹5,999 🟢  │  │   2. Cotton Top    95 │   │
│  │   #00039 Amit   ₹1,299 🔴  │  │   3. Silk Saree    72 │   │
│  │   #00038 Neha   ₹3,499 🟢  │  │   4. Sneakers      68 │   │
│  │                             │  │   5. Kurta Set      55 │   │
│  │   [View All Orders →]       │  │                        │   │
│  └─────────────────────────────┘  └────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────┐  ┌────────────────────────┐   │
│  │                             │  │                        │   │
│  │   LOW STOCK ALERTS          │  │   RECENT ACTIVITY      │   │
│  │                             │  │                        │   │
│  │   ⚠️ Blue Denim Jacket - 3  │  │   Amit updated order   │   │
│  │   ⚠️ White Sneakers - 2     │  │   #00042 status        │   │
│  │   🔴 Silk Saree - 0 (OOS)  │  │   10 min ago           │   │
│  │   ⚠️ Cotton Kurta - 5       │  │                        │   │
│  │                             │  │   Priya added product  │   │
│  │   [View Inventory →]        │  │   "Summer Dress"       │   │
│  └─────────────────────────────┘  │   25 min ago           │   │
│                                    │                        │   │
│                                    │   System: Daily backup │   │
│                                    │   completed             │   │
│                                    │   1 hour ago           │   │
│                                    └────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Products Module

### 3.1 Product List

```
Products > All Products

[+ Add Product]                    🔍 Search products...

Filters: [Category ▼] [Brand ▼] [Status ▼] [Price ▼]  [Clear]

┌──┬──────┬──────────────────────┬────────┬────────┬───────┬────────┬────────┬─────────┐
│☐ │ IMG  │ PRODUCT NAME         │ SKU    │ PRICE  │ STOCK │ STATUS │ RATING │ ACTIONS │
├──┼──────┼──────────────────────┼────────┼────────┼───────┼────────┼────────┼─────────┤
│☐ │[img] │ Floral Maxi Dress    │ FMD001 │ ₹1,299 │ 42    │ 🟢 Act │ ★4.2   │ ⋮       │
│☐ │[img] │ Cotton Casual Top    │ CCT002 │ ₹999   │ 3 ⚠️  │ 🟢 Act │ ★4.5   │ ⋮       │
│☐ │[img] │ Silk Saree           │ SS003  │ ₹5,999 │ 0 🔴  │ 🟡 OOS │ ★4.8   │ ⋮       │
│☐ │[img] │ Blue Denim Jacket    │ BDJ004 │ ₹2,499 │ 128   │ 🟢 Act │ ★3.9   │ ⋮       │
│☐ │[img] │ White Sneakers       │ WS005  │ ₹1,899 │ 55    │ 🟢 Act │ ★4.1   │ ⋮       │
└──┴──────┴──────────────────────┴────────┴────────┴───────┴────────┴────────┴─────────┘

Showing 1-20 of 342   [Per page: 20 ▼]   [< 1 2 3 ... 18 >]

Bulk Actions (when items selected):
┌─────────────────────────────────────────────────────────────┐
│ 3 selected  │ [Activate] [Deactivate] [Delete] [Export]    │
└─────────────────────────────────────────────────────────────┘

Row Actions (⋮ menu):
├── View
├── Edit
├── Duplicate
├── Manage Variants
├── Manage Images
├── View Reviews
├── Deactivate
└── Delete
```

### 3.2 Product Create/Edit Form

```
Products > Add New Product

┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  ┌── BASIC INFORMATION ──────────────────────────────────────┐ │
│  │                                                           │ │
│  │  Product Name *          [                              ] │ │
│  │  Slug                    [auto-generated / editable     ] │ │
│  │  Short Description       [                              ] │ │
│  │  Full Description        [Rich Text Editor              ] │ │
│  │                          [                              ] │ │
│  │  Category *              [Select Category         ▼]     │ │
│  │  Brand                   [Select Brand            ▼]     │ │
│  │  Tags                    [tag1] [tag2] [+ add tag]       │ │
│  │                                                           │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌── PRICING ────────────────────────────────────────────────┐ │
│  │                                                           │ │
│  │  MRP (₹) *              [       ]                         │ │
│  │  Selling Price (₹) *    [       ]                         │ │
│  │  Cost Price (₹)         [       ]   (visible to admin)    │ │
│  │                                                           │ │
│  │  Tax Category            [Standard ▼]                     │ │
│  │  GST Rate                [18%      ▼]                     │ │
│  │  HSN Code                [         ]                      │ │
│  │                                                           │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌── VARIANTS ───────────────────────────────────────────────┐ │
│  │                                                           │ │
│  │  [+ Add Variant]                                          │ │
│  │                                                           │ │
│  │  ┌─────────┬──────┬──────┬─────────┬──────┬──────┬─────┐│ │
│  │  │ SKU     │ Size │Color │ Price   │Stock │ Low  │ ⚙️  ││ │
│  │  ├─────────┼──────┼──────┼─────────┼──────┼──────┼─────┤│ │
│  │  │ FMD-S-R │ S    │ Red  │ ₹1,299  │ 15   │ 5    │ ✎🗑 ││ │
│  │  │ FMD-M-R │ M    │ Red  │ ₹1,299  │ 22   │ 5    │ ✎🗑 ││ │
│  │  │ FMD-L-R │ L    │ Red  │ ₹1,299  │ 8    │ 5    │ ✎🗑 ││ │
│  │  │ FMD-S-B │ S    │Blue  │ ₹1,299  │ 12   │ 5    │ ✎🗑 ││ │
│  │  └─────────┴──────┴──────┴─────────┴──────┴──────┴─────┘│ │
│  │                                                           │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌── IMAGES ─────────────────────────────────────────────────┐ │
│  │                                                           │ │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─ ─ ─ ─ ─┐       │ │
│  │  │  [img]  │ │  [img]  │ │  [img]  │ │ + Upload │       │ │
│  │  │  ★ Hero │ │         │ │         │ │ Drag &   │       │ │
│  │  │  [🗑]   │ │  [🗑]   │ │  [🗑]   │ │ Drop     │       │ │
│  │  └─────────┘ └─────────┘ └─────────┘ └─ ─ ─ ─ ─┘       │ │
│  │  Drag to reorder. First image = hero.                     │ │
│  │                                                           │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌── ADDITIONAL INFO ────────────────────────────────────────┐ │
│  │                                                           │ │
│  │  Material               [                              ]  │ │
│  │  Care Instructions      [                              ]  │ │
│  │  Weight (grams)         [       ]                         │ │
│  │  Is Returnable          [Toggle ✓]                        │ │
│  │  Return Window          [7 days ▼]                        │ │
│  │                                                           │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌── SEO ────────────────────────────────────────────────────┐ │
│  │                                                           │ │
│  │  Meta Title             [                              ]  │ │
│  │  Meta Description       [                              ]  │ │
│  │  Meta Keywords          [                              ]  │ │
│  │                                                           │ │
│  │  SEO Preview:                                             │ │
│  │  ┌───────────────────────────────────────────────────┐    │ │
│  │  │ Floral Maxi Dress - BrandName | FashionStore      │    │ │
│  │  │ fashionstore.com/product/floral-maxi-dress         │    │ │
│  │  │ Beautiful floral maxi dress crafted from 100%...   │    │ │
│  │  └───────────────────────────────────────────────────┘    │ │
│  │                                                           │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌── FLAGS ──────────────────────────────────────────────────┐ │
│  │  ☑ Active    ☐ Featured    ☐ New Arrival    ☐ Bestseller │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
│  [Cancel]                              [Save Draft] [Publish]  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 3.3 Category Management

```
Products > Categories

[+ Add Category]

Category tree (drag to reorder):
├── 👗 Women
│   ├── Dresses (42)
│   │   ├── Maxi (12)
│   │   ├── Mini (8)
│   │   └── Midi (22)
│   ├── Tops (128)
│   ├── Ethnic Wear (65)
│   └── Jeans (89)
├── 👔 Men
│   ├── Shirts (92)
│   ├── T-Shirts (156)
│   └── Trousers (73)
├── 👶 Kids
└── 👜 Accessories

Each node shows: [Edit] [Add Child] [Delete] [Toggle Active]
```

---

## 4. Inventory Module

### 4.1 Stock Levels

```
Inventory > Stock Levels

Summary Cards:
┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│ TOTAL    │ │ LOW      │ │ OUT OF   │ │ VALUE    │
│ PRODUCTS │ │ STOCK    │ │ STOCK    │ │          │
│ 342      │ │ 15 ⚠️    │ │ 3 🔴     │ │ ₹24.5L  │
└──────────┘ └──────────┘ └──────────┘ └──────────┘

Filters: [Category ▼] [Stock Status ▼] [Brand ▼]  🔍 Search...

┌──────┬──────────────────────┬────────┬──────┬───────┬──────────┬──────────┐
│ IMG  │ PRODUCT / VARIANT    │ SKU    │STOCK │RESERVED│ STATUS   │ ACTIONS  │
├──────┼──────────────────────┼────────┼──────┼───────┼──────────┼──────────┤
│[img] │ Floral Maxi Dress    │        │      │       │          │          │
│      │  ├─ S / Red          │FMD-S-R │ 15   │ 2     │ 🟢 OK    │ [Adjust] │
│      │  ├─ M / Red          │FMD-M-R │ 22   │ 0     │ 🟢 OK    │ [Adjust] │
│      │  └─ L / Red          │FMD-L-R │ 3    │ 1     │ ⚠️ Low   │ [Adjust] │
├──────┼──────────────────────┼────────┼──────┼───────┼──────────┼──────────┤
│[img] │ Silk Saree            │        │      │       │          │          │
│      │  └─ Free / Red       │SS-F-R  │ 0    │ 0     │ 🔴 OOS   │ [Adjust] │
└──────┴──────────────────────┴────────┴──────┴───────┴──────────┴──────────┘

[Export CSV]
```

### 4.2 Purchase Orders

```
Inventory > Purchase Orders

[+ New Purchase Order]

┌──────────┬───────────────┬──────────┬──────────┬──────────┬─────────┐
│ PO #     │ SUPPLIER      │ ITEMS    │ TOTAL    │ STATUS   │ ACTIONS │
├──────────┼───────────────┼──────────┼──────────┼──────────┼─────────┤
│ PO-00012 │ TextileCo     │ 5 items  │ ₹45,000  │ 🟢 Recv  │ ⋮       │
│ PO-00011 │ FabricWorld   │ 3 items  │ ₹28,000  │ 🟡 Part  │ ⋮       │
│ PO-00010 │ StyleSupply   │ 8 items  │ ₹1,20,000│ 📋 Draft │ ⋮       │
└──────────┴───────────────┴──────────┴──────────┴──────────┴─────────┘
```

### 4.3 Stock Adjustment

```
Inventory > Stock Adjustment

┌─────────────────────────────────────────────────────────────────┐
│  ADJUST STOCK                                                   │
│                                                                 │
│  Product/Variant *    [Search product...              ▼]        │
│  Current Stock        42 units                                  │
│  Adjustment Type *    ⊙ Add Stock  ○ Remove Stock               │
│  Quantity *           [       ]                                  │
│  Reason *             [Damage / Count Correction / Other ▼]     │
│  Notes                [                                      ]  │
│                                                                 │
│  [Cancel]                                         [Submit]      │
└─────────────────────────────────────────────────────────────────┘
```

### 4.4 Stock Ledger

```
Inventory > Stock Ledger

Filter: [Product ▼] [Type ▼] [Date Range]

┌────────────┬───────────────────┬──────────┬─────┬───┬──────┬──────┬─────────┐
│ DATE       │ PRODUCT / VARIANT │ TYPE     │ DIR │QTY│BEFORE│AFTER │ REF     │
├────────────┼───────────────────┼──────────┼─────┼───┼──────┼──────┼─────────┤
│ Jun 25 2PM │ Floral Maxi M/Red │ SALE     │ OUT │ 1 │ 23   │ 22   │ ORD-042 │
│ Jun 25 11A │ Cotton Top L/Blue │ PURCHASE │ IN  │ 50│ 3    │ 53   │ PO-012  │
│ Jun 24 3PM │ Silk Saree F/Red  │ DAMAGE   │ OUT │ 2 │ 2    │ 0    │ ADJ-005 │
│ Jun 24 1PM │ White Snkrs 8/Wht │ RETURN   │ IN  │ 1 │ 54   │ 55   │ RET-003 │
└────────────┴───────────────────┴──────────┴─────┴───┴──────┴──────┴─────────┘

[Export CSV]
```

---

## 5. Orders Module

### 5.1 Order List

```
Orders > All Orders

Summary:  [5 Pending] [8 Processing] [12 Shipped] [3 Returns]

Filters: [Status ▼] [Payment ▼] [Date Range] [Amount ▼]  🔍 Order# / Customer

┌──┬─────────────┬───────────────┬──────────┬──────────┬──────────┬───────────┬─────────┐
│☐ │ ORDER #     │ CUSTOMER      │ ITEMS    │ TOTAL    │ PAYMENT  │ STATUS    │ ACTIONS │
├──┼─────────────┼───────────────┼──────────┼──────────┼──────────┼───────────┼─────────┤
│☐ │ ORD-00042   │ Priya Sharma  │ 3 items  │ ₹4,717   │ ✅ UPI   │ 🟡 Process│ ⋮       │
│☐ │ ORD-00041   │ Rahul Mehta   │ 1 item   │ ₹2,399   │ ✅ Card  │ 🔵 Shipped│ ⋮       │
│☐ │ ORD-00040   │ Sneha Kapoor  │ 2 items  │ ₹5,999   │ 🕐 COD   │ 🟢 Delivd │ ⋮       │
│☐ │ ORD-00039   │ Amit Patel    │ 1 item   │ ₹1,299   │ ❌ Failed │ 🔴 Failed │ ⋮       │
└──┴─────────────┴───────────────┴──────────┴──────────┴──────────┴───────────┴─────────┘

Row Actions:
├── View Details
├── Update Status → [Confirm] [Process] [Pack] [Ship] [Deliver]
├── Print Invoice
├── Print Label
├── Cancel Order
└── Add Note
```

### 5.2 Order Detail (Admin)

```
Orders > ORD-20260625-00042

┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  Order #ORD-20260625-00042                                      │
│  Placed: Jun 25, 2026 10:30 AM                                  │
│                                                                 │
│  Status: [Processing ▼]  ←  Dropdown to change status          │
│          ┌─────────────────┐                                    │
│          │ ● Confirm       │                                    │
│          │ ● Process       │  ← Only valid next statuses shown  │
│          │ ● Cancel        │                                    │
│          └─────────────────┘                                    │
│                                                                 │
│  ┌── CUSTOMER ─────────────────────────────────────────────────┐│
│  │  Priya Sharma                                               ││
│  │  priya@email.com · 9876543210                               ││
│  │  [View Customer Profile]                                    ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  ┌── ORDER ITEMS ──────────────────────────────────────────────┐│
│  │                                                             ││
│  │  ┌────┐ Floral Maxi Dress   M/Red   ₹1,299 × 1 = ₹1,299  ││
│  │  ┌────┐ Cotton Casual Top   L/Blue  ₹999 × 2   = ₹1,998  ││
│  │  ┌────┐ White Sneakers      8/White ₹2,499 × 1 = ₹2,499  ││
│  │                                                             ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  ┌── ADDRESSES ──────────────────────┬── PAYMENT ──────────────┐│
│  │                                   │                         ││
│  │  SHIPPING                         │  Method: UPI            ││
│  │  Priya Sharma                     │  Status: ✅ Paid        ││
│  │  123 MG Road                      │  Gateway: Razorpay      ││
│  │  Bangalore 560001                 │  ID: pay_L3x9y2z       ││
│  │  Ph: 9876543210                   │  Amount: ₹4,717         ││
│  │                                   │  Paid at: Jun 25, 10:32││
│  │  BILLING                          │                         ││
│  │  Same as shipping                 │                         ││
│  │                                   │                         ││
│  └───────────────────────────────────┴─────────────────────────┘│
│                                                                 │
│  ┌── ORDER SUMMARY ───────────────────────────────────────────┐ │
│  │  Subtotal:          ₹4,797                                 │ │
│  │  Coupon (FIRST20):  -₹580                                 │ │
│  │  Shipping:          ₹0 (Free)                              │ │
│  │  CGST:              ₹250                                   │ │
│  │  SGST:              ₹250                                   │ │
│  │  ──────────────────────────                                │ │
│  │  Grand Total:       ₹4,717                                 │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌── SHIPPING ─────────────────────────────────────────────────┐│
│  │  Method: Standard                                          ││
│  │  Courier: [Select Courier ▼]    Tracking #: [            ]││
│  │  Est. Delivery: [Jun 28        ]                           ││
│  │  [Update Shipping Info]                                    ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  ┌── STATUS HISTORY ──────────────────────────────────────────┐ │
│  │  Jun 25, 10:32 AM  Payment verified → Confirmed  (System) │ │
│  │  Jun 25, 10:30 AM  Order placed → Pending  (Customer)     │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  ┌── ADMIN NOTES ─────────────────────────────────────────────┐ │
│  │  [Add a note...                                          ] │ │
│  │  [Post Note]                                               │ │
│  │                                                            │ │
│  │  Jun 25 11:00 AM - Amit: "Verified address with customer" │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                 │
│  [Print Invoice]  [Print Label]  [Cancel Order]  [Process Refund]│
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.3 Returns Management

```
Orders > Returns

Filters: [Status ▼] [Date Range] [Reason ▼]

┌──────────┬───────────┬───────────────┬──────────┬──────────┬───────────┬─────────┐
│ RETURN # │ ORDER #   │ CUSTOMER      │ REASON   │ AMOUNT   │ STATUS    │ ACTIONS │
├──────────┼───────────┼───────────────┼──────────┼──────────┼───────────┼─────────┤
│ RET-003  │ ORD-00038 │ Sneha Kapoor  │ Wrong sz │ ₹1,299   │ 🟡 Pending│ ⋮       │
│ RET-002  │ ORD-00035 │ Rahul Mehta   │ Defectve │ ₹999     │ ✅ Refundd│ ⋮       │
│ RET-001  │ ORD-00030 │ Priya Sharma  │ Not desc │ ₹2,499   │ 🔵 PickUp │ ⋮       │
└──────────┴───────────┴───────────────┴──────────┴──────────┴───────────┴─────────┘

Return Detail Actions:
├── View Return Details
├── View Customer Photos
├── Approve Return
├── Reject Return (with reason)
├── Schedule Pickup
├── Mark QC Passed/Failed
└── Process Refund
```

---

## 6. Customers Module

### 6.1 Customer List

```
Customers > All Customers

Summary: [128 Total] [42 Active This Month] [12 New Today]

┌──┬──────────────────┬────────────────┬─────────┬──────────┬──────────┬─────────┐
│☐ │ CUSTOMER         │ EMAIL          │ ORDERS  │ SPENT    │ JOINED   │ ACTIONS │
├──┼──────────────────┼────────────────┼─────────┼──────────┼──────────┼─────────┤
│☐ │ 👤 Priya Sharma  │ priya@email.com│ 12      │ ₹24,500  │ Jan 2026 │ ⋮       │
│☐ │ 👤 Rahul Mehta   │ rahul@email.com│ 5       │ ₹8,999   │ Mar 2026 │ ⋮       │
│☐ │ 👤 Sneha Kapoor  │ sneha@email.com│ 3       │ ₹12,300  │ May 2026 │ ⋮       │
└──┴──────────────────┴────────────────┴─────────┴──────────┴──────────┴─────────┘
```

### 6.2 Customer Detail

```
Customers > Priya Sharma

┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  👤 Priya Sharma                    Status: ✅ Active           │
│  priya@email.com · 9876543210                                   │
│  Joined: January 15, 2026                                       │
│                                                                 │
│  ┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐      │
│  │ ORDERS    │ │ SPENT     │ │ AVG ORDER │ │ LAST ORDER│      │
│  │ 12        │ │ ₹24,500   │ │ ₹2,041    │ │ Jun 25    │      │
│  └───────────┘ └───────────┘ └───────────┘ └───────────┘      │
│                                                                 │
│  [Orders] [Addresses] [Reviews] [Wishlist] [Activity]           │
│  ─────────────────────────────────────────────────────          │
│                                                                 │
│  RECENT ORDERS                                                  │
│  (Order table filtered by customer)                             │
│                                                                 │
│  ADMIN NOTES                                                    │
│  [Add note about this customer...]                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 7. Coupons Module

### 7.1 Coupon List

```
Coupons > All Coupons

[+ Create Coupon]

┌────────────┬──────────────┬──────────┬────────┬───────┬──────────┬─────────┐
│ CODE       │ NAME         │ TYPE     │ VALUE  │ USED  │ STATUS   │ ACTIONS │
├────────────┼──────────────┼──────────┼────────┼───────┼──────────┼─────────┤
│ FIRST20    │ First Order  │ % off    │ 20%    │ 45/∞  │ 🟢 Active│ ⋮       │
│ SUMMER500  │ Summer Sale  │ Flat     │ ₹500   │ 120/200│ 🟢 Active│ ⋮      │
│ FREESHIP   │ Free Ship    │ Ship     │ ₹0     │ 89/100│ 🟡 Expirg│ ⋮       │
│ DIWALI30   │ Diwali Sale  │ % off    │ 30%    │ 500/500│ 🔴 Full │ ⋮       │
└────────────┴──────────────┴──────────┴────────┴───────┴──────────┴─────────┘
```

### 7.2 Coupon Create/Edit Form

```
Coupons > Create New Coupon

┌── BASIC INFO ──────────────────────────────────────────────────┐
│  Coupon Code *        [               ]  [Generate Random]     │
│  Internal Name *      [                                    ]   │
│  Description          [                                    ]   │
└────────────────────────────────────────────────────────────────┘

┌── DISCOUNT RULES ──────────────────────────────────────────────┐
│  Type *               ⊙ Percentage  ○ Flat Amount  ○ Free Ship│
│  Discount Value *     [     ] %                                │
│  Max Discount Cap     [     ] ₹  (optional)                    │
│  Min Order Value      [     ] ₹  (optional)                    │
└────────────────────────────────────────────────────────────────┘

┌── USAGE LIMITS ────────────────────────────────────────────────┐
│  Total Uses           [     ]  (blank = unlimited)             │
│  Per Customer Uses    [  1  ]                                  │
│  First Order Only     [Toggle ○]                               │
└────────────────────────────────────────────────────────────────┘

┌── APPLICABILITY ───────────────────────────────────────────────┐
│  Applicable To        ⊙ All Products  ○ Specific               │
│  Categories           [Multi-select categories...]             │
│  Brands               [Multi-select brands...]                 │
│  Exclude Products     [Multi-select products...]               │
└────────────────────────────────────────────────────────────────┘

┌── VALIDITY ────────────────────────────────────────────────────┐
│  Start Date *         [Jun 25, 2026  📅]                       │
│  End Date             [Jul 25, 2026  📅]  (optional)           │
│  Status               [Toggle ✓ Active]                        │
└────────────────────────────────────────────────────────────────┘

[Cancel]                                              [Save Coupon]
```

---

## 8. Reports Module

### 8.1 Sales Report

```
Reports > Sales

Date Range: [Jun 1] to [Jun 25] [Apply]     [Export CSV] [Export PDF]

Summary Cards:
┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│ REVENUE   │ │ ORDERS    │ │ AVG ORDER │ │ UNITS SOLD│
│ ₹8,45,000 │ │ 342       │ │ ₹2,471    │ │ 1,248     │
│ ↑ 15%     │ │ ↑ 12%     │ │ ↑ 3%      │ │ ↑ 18%     │
│ vs prev   │ │ vs prev   │ │ vs prev   │ │ vs prev   │
└───────────┘ └───────────┘ └───────────┘ └───────────┘

┌────────────────────────────────────────────────────────────┐
│                                                            │
│   REVENUE TREND (Line Chart)                               │
│                                                            │
│   ₹50K ┤                        ╭───╮                      │
│   ₹40K ┤              ╭────────╯   ╰──╮                   │
│   ₹30K ┤    ╭────────╯                ╰──╮                │
│   ₹20K ┤───╯                              ╰───            │
│        └────────────────────────────────────────            │
│         Jun 1    Jun 7    Jun 14    Jun 21    Jun 25        │
│                                                            │
│   [Daily] [Weekly] [Monthly]                               │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────┐ ┌────────────────────────────┐
│ SALES BY CATEGORY          │ │ SALES BY PAYMENT METHOD    │
│ (Horizontal bar chart)     │ │ (Pie/donut chart)          │
│                            │ │                            │
│ Dresses    ████████ ₹2.4L  │ │   UPI        42%           │
│ Tops       ██████ ₹1.8L    │ │   Card       28%           │
│ Ethnic     █████ ₹1.5L     │ │   COD        18%           │
│ Footwear   ████ ₹1.2L      │ │   NetBank    8%            │
│ Acc        ███ ₹0.9L       │ │   Wallet     4%            │
└────────────────────────────┘ └────────────────────────────┘

TOP PRODUCTS TABLE:
┌──┬──────────────────────┬──────┬──────────┬──────────┐
│# │ PRODUCT              │ SOLD │ REVENUE  │ RETURNS  │
├──┼──────────────────────┼──────┼──────────┼──────────┤
│1 │ Floral Maxi Dress    │ 128  │ ₹1,66,272│ 3 (2.3%) │
│2 │ Cotton Casual Top    │ 95   │ ₹94,905  │ 1 (1.1%) │
│3 │ Silk Saree           │ 72   │ ₹4,31,928│ 0 (0%)   │
└──┴──────────────────────┴──────┴──────────┴──────────┘
```

### 8.2 Inventory Report

```
Reports > Inventory

┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│ TOTAL     │ │ LOW STOCK │ │ OUT OF    │ │ TOTAL     │
│ VARIANTS  │ │           │ │ STOCK     │ │ VALUE     │
│ 1,248     │ │ 15 ⚠️     │ │ 3 🔴      │ │ ₹24.5L   │
└───────────┘ └───────────┘ └───────────┘ └───────────┘

[Stock Movement Chart]  [Dead Stock Report]  [Valuation Report]
```

### 8.3 Customer Report

```
Reports > Customers

┌───────────┐ ┌───────────┐ ┌───────────┐ ┌───────────┐
│ TOTAL     │ │ NEW THIS  │ │ REPEAT    │ │ AVERAGE   │
│ CUSTOMERS │ │ MONTH     │ │ RATE      │ │ LTV       │
│ 2,450     │ │ 128       │ │ 28%       │ │ ₹4,200    │
└───────────┘ └───────────┘ └───────────┘ └───────────┘

[Registration Trend]  [Geographic Distribution]  [Customer Segments]
```

---

## 9. Blogs Module

### 9.1 Blog Post List

```
Blog > All Posts

[+ New Post]

Filters: [Status ▼] [Category ▼] [Author ▼]  🔍 Search...

┌──┬──────────────────────────┬──────────┬──────────┬────────┬──────────┬─────────┐
│☐ │ TITLE                    │ CATEGORY │ AUTHOR   │ VIEWS  │ STATUS   │ ACTIONS │
├──┼──────────────────────────┼──────────┼──────────┼────────┼──────────┼─────────┤
│☐ │ 10 Ways to Style...      │ Tips     │ Priya    │ 1,245  │ 🟢 Pub   │ ⋮       │
│☐ │ Summer Trends 2026       │ Trends   │ Amit     │ 890    │ 🟢 Pub   │ ⋮       │
│☐ │ Size Guide: Finding...   │ Guides   │ Sneha    │ —      │ 📋 Draft │ ⋮       │
└──┴──────────────────────────┴──────────┴──────────┴────────┴──────────┴─────────┘
```

### 9.2 Blog Post Editor

```
Blog > New Post

┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  Title *               [                                      ] │
│  Slug                  [auto-generated                        ] │
│                                                                 │
│  ┌── Rich Text Editor ─────────────────────────────────────┐   │
│  │ [B] [I] [U] [H1] [H2] [H3] [🔗] [📷] [📹] [</>] [≡]  │   │
│  │                                                         │   │
│  │  Content area with rich formatting...                   │   │
│  │                                                         │   │
│  │                                                         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌── SIDEBAR ──────────────────────────────────────────────┐   │
│  │                                                         │   │
│  │  Status:     [Draft ▼]                                  │   │
│  │  Category:   [Select ▼]                                 │   │
│  │  Tags:       [tag1] [tag2] [+]                          │   │
│  │  Author:     [Admin Name]                               │   │
│  │  Publish At: [            📅]  (schedule)               │   │
│  │  Featured:   [Toggle ○]                                 │   │
│  │                                                         │   │
│  │  FEATURED IMAGE                                         │   │
│  │  ┌─────────────────┐                                    │   │
│  │  │ + Upload Image  │                                    │   │
│  │  └─────────────────┘                                    │   │
│  │                                                         │   │
│  │  EXCERPT                                                │   │
│  │  [                                                   ]  │   │
│  │  [                                                   ]  │   │
│  │                                                         │   │
│  │  SEO                                                    │   │
│  │  Meta Title:  [                                      ]  │   │
│  │  Meta Desc:   [                                      ]  │   │
│  │                                                         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  [Save Draft]                                     [Publish]    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 10. Settings Module

### 10.1 Settings Navigation

```
Settings > General

[General] [Tax] [Shipping] [Payment] [Email] [SMS] [SEO]  ← Tab navigation

┌── GENERAL SETTINGS ────────────────────────────────────────────┐
│                                                                │
│  STORE INFO                                                    │
│  Store Name          [FashionStore                          ]  │
│  Store Email         [admin@fashionstore.com                ]  │
│  Store Phone         [1800-123-4567                         ]  │
│  Store Address       [123 Fashion Street, Mumbai            ]  │
│  Currency            [INR (₹) ▼]                               │
│  Timezone            [Asia/Kolkata ▼]                           │
│                                                                │
│  LOGO                                                          │
│  [Logo Preview]  [Upload New]                                  │
│  [Favicon]       [Upload New]                                  │
│                                                                │
│  SOCIAL LINKS                                                  │
│  Facebook        [https://                                  ]  │
│  Instagram       [https://                                  ]  │
│  Twitter         [https://                                  ]  │
│  YouTube         [https://                                  ]  │
│                                                                │
│  [Save Settings]                                               │
│                                                                │
└────────────────────────────────────────────────────────────────┘

TAX SETTINGS:
├── GSTIN, default tax rates by category, state-wise rules

SHIPPING SETTINGS:
├── Free shipping threshold, standard/express rates, COD charges, pincode restrictions

PAYMENT SETTINGS:
├── Razorpay keys, Stripe keys, COD enable/disable, COD limit

EMAIL SETTINGS:
├── Mailgun credentials, sender email, reply-to, test email

SMS SETTINGS:
├── Twilio credentials, sender ID, enable/disable per event

SEO SETTINGS:
├── Default meta tags, robots.txt, sitemap settings, Google Analytics ID
```

---

## 11. User & Role Management

### 11.1 Users List

```
Users & Roles > Users

[+ Add User]

┌──────────────────┬───────────────────┬──────────────┬──────────┬─────────┐
│ USER             │ EMAIL             │ ROLE         │ STATUS   │ ACTIONS │
├──────────────────┼───────────────────┼──────────────┼──────────┼─────────┤
│ Amit Patel       │ amit@store.com    │ Super Admin  │ 🟢 Active│ ⋮       │
│ Priya Designer   │ priya@store.com   │ Content Mgr  │ 🟢 Active│ ⋮       │
│ Rahul Ops        │ rahul@store.com   │ Store Mgr    │ 🟢 Active│ ⋮       │
│ Sneha Support    │ sneha@store.com   │ Support      │ 🔴 Inact │ ⋮       │
└──────────────────┴───────────────────┴──────────────┴──────────┴─────────┘
```

### 11.2 Role Permissions

```
Users & Roles > Roles > Store Manager

Role: Store Manager

┌── PERMISSIONS ─────────────────────────────────────────────────┐
│                                                                │
│  PRODUCTS                    ORDERS                            │
│  ☑ View Products             ☑ View Orders                     │
│  ☑ Create Products           ☑ Update Order Status             │
│  ☑ Edit Products             ☐ Cancel Orders                   │
│  ☐ Delete Products           ☐ Process Refunds                 │
│                                                                │
│  INVENTORY                   CUSTOMERS                         │
│  ☑ View Inventory            ☑ View Customers                  │
│  ☑ Stock Adjustments         ☐ Edit Customers                  │
│  ☐ Purchase Orders           ☐ Delete Customers                │
│                                                                │
│  COUPONS                     REPORTS                           │
│  ☑ View Coupons              ☑ View Reports                    │
│  ☐ Create Coupons            ☐ Export Reports                  │
│  ☐ Edit Coupons                                                │
│                                                                │
│  BLOG                        SETTINGS                          │
│  ☐ Manage Blog               ☐ Manage Settings                │
│                               ☐ Manage Users                   │
│                                                                │
│  [Save Permissions]                                            │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

---

## 12. Common Patterns

### 12.1 Data Table Pattern

All admin tables follow this consistent pattern:

```
[Module Title]                                        [Action Button]

[Filter Row with dropdowns/search]

┌── Table Header (sortable columns) ──────────────────────────────┐
│ ☐ Select All | Columns with ↕ sort | Actions column            │
├── Table Body ───────────────────────────────────────────────────┤
│ Row with data, status badges, action menu (⋮)                  │
├── Table Footer ─────────────────────────────────────────────────┤
│ Showing X-Y of Z | Per page selector | Pagination              │
└─────────────────────────────────────────────────────────────────┘

Bulk Actions Bar (appears when items selected):
┌─────────────────────────────────────────────────────────────────┐
│ N selected | [Action 1] [Action 2] [Delete] | [Deselect All]   │
└─────────────────────────────────────────────────────────────────┘
```

### 12.2 Form Pattern

```
[Module] > [Action]

┌── Section Title ───────────────────────────────────────────────┐
│  Field Label *           [Input                             ]  │
│  Field Label             [Input with helper text            ]  │
│                          Helper text or validation error       │
└────────────────────────────────────────────────────────────────┘

┌── Another Section ─────────────────────────────────────────────┐
│  ...                                                           │
└────────────────────────────────────────────────────────────────┘

[Cancel]                                    [Save Draft] [Submit]
```

### 12.3 Filter System

| Filter Type | UI Component | Usage |
|-------------|-------------|-------|
| Status | Dropdown / Pills | Order status, product status |
| Date Range | Date picker (from-to) | Reports, orders by date |
| Category | Searchable dropdown | Products by category |
| Brand | Searchable dropdown | Products by brand |
| Price Range | Dual slider | Products by price |
| Search | Text input with debounce | Free-text search across fields |

### 12.4 Notification Center

```
🔔 Notifications (3 unread)

┌──────────────────────────────────────┐
│ 🟡 New order #ORD-00042  (2 min)    │
│ 🔴 Low stock: Blue Jacket (5 min)   │
│ 🟢 Return #RET-003 approved (1 hr)  │
│ ─── Earlier ───                      │
│    Order #ORD-00041 delivered (3 hr) │
│    New customer: Sneha K. (5 hr)     │
│                                      │
│ [Mark All Read]  [View All →]        │
└──────────────────────────────────────┘
```

### 12.5 Confirmation Dialogs

```
┌──────────────────────────────────────┐
│                                      │
│  ⚠️ Delete Product?                  │
│                                      │
│  Are you sure you want to delete     │
│  "Floral Maxi Dress"? This action    │
│  cannot be undone.                   │
│                                      │
│  [Cancel]              [Delete]      │
│                                      │
└──────────────────────────────────────┘
```

---

*End of Admin Panel UX Document — Phase 6*
