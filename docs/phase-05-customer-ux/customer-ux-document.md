# Phase 5: Customer Website UX Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** Senior UX Designer
**Status:** Draft

---

## Table of Contents

1. [Navigation Architecture](#1-navigation-architecture)
2. [Homepage](#2-homepage)
3. [Category Listing Page](#3-category-listing-page)
4. [Product Listing Page](#4-product-listing-page)
5. [Product Detail Page](#5-product-detail-page)
6. [Cart](#6-cart)
7. [Checkout](#7-checkout)
8. [Wishlist](#8-wishlist)
9. [My Account](#9-my-account)
10. [Order History & Detail](#10-order-history--detail)
11. [Track Order](#11-track-order)
12. [Blog](#12-blog)
13. [About Us & Contact Us](#13-about-us--contact-us)
14. [Authentication Pages](#14-authentication-pages)
15. [Mobile-First Design](#15-mobile-first-design)
16. [Component Trees](#16-component-trees)

---

## 1. Navigation Architecture

### 1.1 Customer Header (Desktop)

```
┌─────────────────────────────────────────────────────────────────────┐
│ 🔥 Free Shipping on Orders Above ₹999  |  📞 1800-123-4567        │ ← Top bar (dismissible)
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  [LOGO]     Women  Men  Kids  Accessories  Sale 🔴    [🔍] [♡] [👤] [🛒3]  │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│ Mega Menu (on hover):                                               │
│ ┌───────────────────────────────────────────────────────────────┐   │
│ │ CLOTHING        FOOTWEAR      ACCESSORIES    TRENDING         │   │
│ │ ──────────      ──────────    ──────────     ─────────        │   │
│ │ Dresses         Heels         Bags           New Arrivals     │   │
│ │ Tops            Flats         Jewelry        Best Sellers     │   │
│ │ Kurtas          Sneakers      Watches        Under ₹999       │   │
│ │ Jeans           Boots         Scarves        Collections      │   │
│ │ Shirts          Sandals       Belts          [BANNER IMAGE]   │   │
│ │ Ethnic Wear     Loafers       Sunglasses                      │   │
│ │                                                               │   │
│ │ [Shop All Women →]                                            │   │
│ └───────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
```

### 1.2 Customer Header (Mobile)

```
┌──────────────────────────────────┐
│  [☰]    [LOGO]     [🔍] [🛒3]  │
└──────────────────────────────────┘

Bottom Tab Bar (Fixed):
┌──────────────────────────────────┐
│  🏠       📂       ♡       👤   │
│ Home   Categories Wishlist  Me   │
└──────────────────────────────────┘
```

### 1.3 Mobile Navigation Drawer

```
┌──────────────────────────────────┐
│  [✕ Close]            [LOGO]    │
├──────────────────────────────────┤
│  👤 Hi, Priya                    │
│  ─────────────────────────       │
│  🏠 Home                         │
│  👗 Women                    [>] │
│  👔 Men                      [>] │
│  👶 Kids                     [>] │
│  👜 Accessories              [>] │
│  🔴 Sale                         │
│  ─────────────────────────       │
│  📦 My Orders                    │
│  ♡  Wishlist                     │
│  📍 Addresses                    │
│  ⚙️ Settings                     │
│  ─────────────────────────       │
│  📝 Blog                        │
│  📞 Contact Us                   │
│  ℹ️  About Us                    │
│  ─────────────────────────       │
│  🌙 Dark Mode           [Toggle] │
│  🌐 Language: English   [>]     │
│  ─────────────────────────       │
│  [Logout]                        │
└──────────────────────────────────┘
```

### 1.4 Search Experience

```
Search Bar (Expanded):
┌─────────────────────────────────────────┐
│ 🔍 Search for products, brands...   [✕] │
├─────────────────────────────────────────┤
│                                         │
│  TRENDING SEARCHES                      │
│  [Summer Dresses] [Cotton Kurtas]       │
│  [White Sneakers] [Silk Sarees]         │
│                                         │
│  RECENT SEARCHES                        │
│  🕐 Blue denim jacket                   │
│  🕐 Floral maxi dress                   │
│                                         │
│  As user types → Autocomplete:          │
│  ┌─────────────────────────────────┐    │
│  │ 🔍 Red dress                     │    │
│  │ 🔍 Red heels                     │    │
│  │ 📂 Red → in Dresses              │    │
│  │ 🏷️ RedTag (Brand)                │    │
│  │ ┌──────────────────────────┐    │    │
│  │ │ [img] Red Floral...  ₹1299│    │    │
│  │ │ [img] Red Party Dr... ₹2499│    │    │
│  │ └──────────────────────────┘    │    │
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
```

---

## 2. Homepage

### 2.1 Page Sections (Top to Bottom)

```
┌─────────────────────────────────────────────────────────────────┐
│                          HEADER                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │                  HERO BANNER CAROUSEL                    │   │
│  │                                                         │   │
│  │   "Summer Collection 2026"                              │   │
│  │   Up to 40% off on selected styles                      │   │
│  │                                                         │   │
│  │   [Shop Now]                                            │   │
│  │                                                         │   │
│  │   ● ○ ○ ○                                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│  │  🚚      │ │  🔄      │ │  💳      │ │  📞      │         │
│  │  Free    │ │  Easy    │ │  Secure  │ │  24/7    │         │
│  │ Shipping │ │ Returns  │ │ Payment  │ │ Support  │         │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│                        USP Bar                                  │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                     SHOP BY CATEGORY                            │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐      │
│  │[img] │ │[img] │ │[img] │ │[img] │ │[img] │ │[img] │      │
│  │Women │ │ Men  │ │ Kids │ │Ethnic│ │ Acc  │ │ Sale │      │
│  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘      │
│                   Circular/rounded cards                        │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                    TRENDING NOW                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│  │  [img]   │ │  [img]   │ │  [img]   │ │  [img]   │         │
│  │ Product  │ │ Product  │ │ Product  │ │ Product  │         │
│  │  Card    │ │  Card    │ │  Card    │ │  Card    │         │
│  │ ₹1,299   │ │ ₹999     │ │ ₹2,499   │ │ ₹1,599   │         │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│                [View All Trending →]                            │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                  PROMOTIONAL BANNER                             │
│  ┌──────────────────────┐ ┌──────────────────────┐            │
│  │    NEW ARRIVALS      │ │    FLAT 50% OFF       │            │
│  │    [Shop Now]        │ │    End of Season      │            │
│  │                      │ │    [Shop Sale]        │            │
│  └──────────────────────┘ └──────────────────────┘            │
│                     2-column promo grid                         │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                     NEW ARRIVALS                                │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│  │  [img]   │ │  [img]   │ │  [img]   │ │  [img]   │         │
│  │ Product  │ │ Product  │ │ Product  │ │ Product  │         │
│  │  Card    │ │  Card    │ │  Card    │ │  Card    │         │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│                [View All New Arrivals →]                        │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│              FEATURED COLLECTIONS / LOOKBOOK                    │
│  ┌────────────────────────────────────────────────────────┐    │
│  │     [Large editorial-style image]                      │    │
│  │                                                        │    │
│  │     "The Minimalist Edit"                              │    │
│  │     Curated essentials for your wardrobe               │    │
│  │     [Explore Collection]                               │    │
│  └────────────────────────────────────────────────────────┘    │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                     BESTSELLERS                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│  │  [img]   │ │  [img]   │ │  [img]   │ │  [img]   │         │
│  │ Product  │ │ Product  │ │ Product  │ │ Product  │         │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                    SHOP BY BRAND                                │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐      │
│  │ Logo │ │ Logo │ │ Logo │ │ Logo │ │ Logo │ │ Logo │      │
│  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘      │
│                      Brand logos grid                           │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                CUSTOMER TESTIMONIALS                            │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐           │
│  │ "Amazing     │ │ "Love the    │ │ "Quick       │           │
│  │  quality"    │ │  collection" │ │  delivery"   │           │
│  │ ★★★★★        │ │ ★★★★★        │ │ ★★★★★        │           │
│  │ - Priya S.   │ │ - Rahul M.   │ │ - Sneha K.   │           │
│  └──────────────┘ └──────────────┘ └──────────────┘           │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                 FROM THE BLOG                                   │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐           │
│  │   [img]      │ │   [img]      │ │   [img]      │           │
│  │ Blog Title   │ │ Blog Title   │ │ Blog Title   │           │
│  │ Excerpt...   │ │ Excerpt...   │ │ Excerpt...   │           │
│  │ Read More →  │ │ Read More →  │ │ Read More →  │           │
│  └──────────────┘ └──────────────┘ └──────────────┘           │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                    NEWSLETTER                                   │
│  ┌────────────────────────────────────────────────────────┐    │
│  │  Stay Updated with Fashion Trends                      │    │
│  │  [  Enter your email           ] [Subscribe]           │    │
│  │  Get 10% off your first order!                         │    │
│  └────────────────────────────────────────────────────────┘    │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                          FOOTER                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ COMPANY    HELP        SHOP       FOLLOW US            │    │
│  │ About Us   FAQ         Women      [fb] [ig] [tw] [yt]  │    │
│  │ Careers    Shipping    Men                              │    │
│  │ Blog       Returns     Kids       DOWNLOAD APP         │    │
│  │ Contact    Privacy     Sale       [App Store]           │    │
│  │            Terms                  [Play Store]          │    │
│  │                                                        │    │
│  │ ─────────────────────────────────────────────────      │    │
│  │ © 2026 FashionStore. All rights reserved.              │    │
│  │ 🔒 100% Secure Payments  |  📍 India                  │    │
│  │ [Visa] [MC] [UPI] [PayTM] [RuPay]                     │    │
│  └────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Homepage SEO

| Element | Value |
|---------|-------|
| Title | "FashionStore — Premium Fashion Online \| Free Shipping" |
| Meta Description | "Shop the latest fashion trends. Premium clothing, footwear & accessories for men, women & kids. Free shipping on orders above ₹999." |
| H1 | "Discover Your Style" (in hero) |
| Schema | Organization, WebSite, SearchAction |

---

## 3. Category Listing Page

### 3.1 Layout

```
Breadcrumb: Home / Women

┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              CATEGORY HERO BANNER                        │  │
│  │              "Women's Collection"                        │  │
│  │              Shop the latest trends                      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  SUBCATEGORIES                                                  │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐      │
│  │[img] │ │[img] │ │[img] │ │[img] │ │[img] │ │[img] │      │
│  │Dress │ │Tops  │ │Kurta │ │Jeans │ │Saree │ │ All  │      │
│  │ (42) │ │(128) │ │ (65) │ │ (89) │ │ (34) │ │(358) │      │
│  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘      │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                  POPULAR IN WOMEN                               │
│  Product cards (horizontal scrollable or 4-column grid)         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. Product Listing Page

### 4.1 Layout

```
Breadcrumb: Home / Women / Dresses

┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  "Dresses" (128 products)                                       │
│                                                                 │
│  ┌──────────────────┐  ┌──────────────────────────────────┐    │
│  │   FILTER SIDEBAR │  │                                  │    │
│  │                  │  │  Sort: Popularity ▼   Grid|List  │    │
│  │  CATEGORIES      │  │                                  │    │
│  │  ☑ Maxi (24)     │  │  ┌────────┐ ┌────────┐ ┌────────┐│    │
│  │  ☐ Mini (18)     │  │  │ [img]  │ │ [img]  │ │ [img]  ││    │
│  │  ☐ Midi (32)     │  │  │ Card   │ │ Card   │ │ Card   ││    │
│  │  ☐ A-Line (28)   │  │  │ ₹1299  │ │ ₹999   │ │ ₹2499  ││    │
│  │                  │  │  └────────┘ └────────┘ └────────┘│    │
│  │  PRICE           │  │                                  │    │
│  │  [──●────────●──]│  │  ┌────────┐ ┌────────┐ ┌────────┐│    │
│  │  ₹299 — ₹5,999  │  │  │ [img]  │ │ [img]  │ │ [img]  ││    │
│  │                  │  │  │ Card   │ │ Card   │ │ Card   ││    │
│  │  SIZE            │  │  │ ₹799   │ │ ₹1899  │ │ ₹1199  ││    │
│  │  [XS][S][M][L]   │  │  └────────┘ └────────┘ └────────┘│    │
│  │  [XL][XXL]       │  │                                  │    │
│  │                  │  │  ┌────────┐ ┌────────┐ ┌────────┐│    │
│  │  COLOR           │  │  │ ...    │ │ ...    │ │ ...    ││    │
│  │  ●● ● ● ● ● ●  │  │  └────────┘ └────────┘ └────────┘│    │
│  │  (color swatches)│  │                                  │    │
│  │                  │  │         [Load More] (pg 1/5)     │    │
│  │  BRAND           │  │                                  │    │
│  │  ☑ BrandA (12)   │  └──────────────────────────────────┘    │
│  │  ☐ BrandB (8)    │                                          │
│  │                  │                                          │
│  │  RATING          │                                          │
│  │  ☐ ★★★★★ & up   │                                          │
│  │  ☐ ★★★★ & up    │                                          │
│  │  ☐ ★★★ & up     │                                          │
│  │                  │                                          │
│  │  DISCOUNT        │                                          │
│  │  ☐ 10% & above  │                                          │
│  │  ☐ 20% & above  │                                          │
│  │  ☐ 30% & above  │                                          │
│  │                  │                                          │
│  │  [Clear All]     │                                          │
│  └──────────────────┘                                          │
│                                                                 │
│  Active Filters:                                                │
│  [Maxi ✕]  [₹299–₹2999 ✕]  [BrandA ✕]  [Clear All]           │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 4.2 Sort Options

| Option | API Parameter |
|--------|--------------|
| Popularity | `sort=popularity` |
| Price: Low to High | `sort=price&order=asc` |
| Price: High to Low | `sort=price&order=desc` |
| Newest First | `sort=created_at&order=desc` |
| Rating: High to Low | `sort=rating&order=desc` |
| Discount: High to Low | `sort=discount&order=desc` |

### 4.3 Product Card (Listing)

```
┌──────────────────────────┐
│   ┌────┐         ┌────┐ │
│   │NEW │         │ ♡  │ │  ← Badge + wishlist
│   └────┘         └────┘ │
│                          │
│      [Product Image]     │  ← 3:4 aspect ratio
│                          │
│   ── Hover: Quick View ──│  ← Shows on hover
│                          │
├──────────────────────────┤
│  Brand Name              │  ← Muted, small text
│  Product Name That May   │  ← 2-line clamp
│  Be Quite Long...        │
│                          │
│  ★★★★☆ 4.2 (128)        │  ← Rating + count
│                          │
│  ₹1,299  ₹1,999  35%off │  ← Price, MRP struck, discount
│                          │
│  ● ● ● ● +2             │  ← Available colors
│                          │
│  [Add to Cart]           │  ← Shows on hover (desktop)
└──────────────────────────┘    Always visible (mobile)
```

---

## 5. Product Detail Page

### 5.1 Layout

```
Breadcrumb: Home / Women / Dresses / Floral Maxi Dress

┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  ┌─────────────────────────┐  ┌──────────────────────────────┐ │
│  │                         │  │                              │ │
│  │   [Main Product Image]  │  │  Brand Name                  │ │
│  │                         │  │  Product Name — Full Title    │ │
│  │   🔍 (zoom on hover)   │  │                              │ │
│  │                         │  │  ★★★★☆ 4.2 (128 Reviews)    │ │
│  │                         │  │                              │ │
│  ├─────────────────────────┤  │  ₹1,299  ₹1,999  35% off   │ │
│  │ [thumb] [thumb] [thumb] │  │  Inclusive of all taxes       │ │
│  │ [thumb] [thumb]         │  │                              │ │
│  │                         │  │  ─────────────────────────── │ │
│  └─────────────────────────┘  │                              │ │
│                               │  SELECT COLOR                │ │
│                               │  ● Black  ● Red  ● Blue     │ │
│                               │                              │ │
│                               │  SELECT SIZE                 │ │
│                               │  [XS] [S] [M✓] [L] [XL]     │ │
│                               │  📏 Size Guide               │ │
│                               │                              │ │
│                               │  ┌────────────────────────┐  │ │
│                               │  │ [-]  1  [+]            │  │ │
│                               │  └────────────────────────┘  │ │
│                               │                              │ │
│                               │  ┌────────────────────────┐  │ │
│                               │  │    🛒 ADD TO CART       │  │ │
│                               │  └────────────────────────┘  │ │
│                               │  ┌────────────────────────┐  │ │
│                               │  │    ⚡ BUY NOW           │  │ │
│                               │  └────────────────────────┘  │ │
│                               │                              │ │
│                               │  ♡ Add to Wishlist           │ │
│                               │  🔗 Share                    │ │
│                               │                              │ │
│                               │  ─────────────────────────── │ │
│                               │                              │ │
│                               │  📍 Check Delivery           │ │
│                               │  [Enter Pincode] [Check]     │ │
│                               │  ✓ Delivery by Jun 28        │ │
│                               │  ✓ Free Shipping             │ │
│                               │  ✓ COD Available             │ │
│                               │                              │ │
│                               │  ─────────────────────────── │ │
│                               │                              │ │
│                               │  HIGHLIGHTS                  │ │
│                               │  • Material: 100% Cotton     │ │
│                               │  • Fit: Regular              │ │
│                               │  • Pattern: Floral Print     │ │
│                               │  • Occasion: Casual, Party   │ │
│                               │                              │ │
│                               └──────────────────────────────┘ │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │  [Description]  [Size Guide]  [Reviews (128)]           │   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │                                                         │   │
│  │  PRODUCT DESCRIPTION                                    │   │
│  │                                                         │   │
│  │  This beautiful floral maxi dress is crafted from...    │   │
│  │                                                         │   │
│  │  MATERIAL & CARE                                        │   │
│  │  • Machine wash cold                                    │   │
│  │  • Do not bleach                                        │   │
│  │  • Iron low heat                                        │   │
│  │                                                         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                    CUSTOMER REVIEWS                              │
│                                                                 │
│  Overall: ★★★★☆ 4.2 out of 5  (128 reviews)                   │
│                                                                 │
│  5★ ████████████████████ 64%                                    │
│  4★ █████████████ 22%                                           │
│  3★ ████ 8%                                                     │
│  2★ ██ 4%                                                       │
│  1★ █ 2%                                                        │
│                                                                 │
│  [Write a Review]                                               │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ★★★★★  "Perfect summer dress!"                          │   │
│  │ Priya S. · Verified Purchase · 2 weeks ago              │   │
│  │ "Loved the quality and fit. The fabric is soft and..."  │   │
│  │ [📸 photo] [📸 photo]                                   │   │
│  │ 👍 Helpful (12)                                         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                   YOU MAY ALSO LIKE                              │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│  │ Related  │ │ Related  │ │ Related  │ │ Related  │         │
│  │ Product  │ │ Product  │ │ Product  │ │ Product  │         │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│                                                                 │
│  ═══════════════════════════════════════════════════════════    │
│                   RECENTLY VIEWED                               │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│  │ Product  │ │ Product  │ │ Product  │ │ Product  │         │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.2 Product Detail SEO

| Element | Value |
|---------|-------|
| Title | "Product Name - Brand \| Category \| FashionStore" |
| Meta Description | Auto-generated from short_description |
| H1 | Product Name |
| Schema | Product (name, image, price, availability, rating, review) |
| OG Tags | Product image, name, price |
| Canonical | `/product/{slug}` |

---

## 6. Cart

### 6.1 Cart Drawer (Slide-out)

```
┌──────────────────────────────────┐
│  YOUR CART (3 items)       [✕]  │
├──────────────────────────────────┤
│                                  │
│  ┌────┐ Product Name            │
│  │img │ Size: M | Color: Red    │
│  │    │ ₹1,299                  │
│  └────┘ [-] 1 [+]     [🗑️]     │
│                                  │
│  ─────────────────────────       │
│                                  │
│  ┌────┐ Product Name            │
│  │img │ Size: L | Color: Blue   │
│  │    │ ₹999                    │
│  └────┘ [-] 2 [+]     [🗑️]     │
│                                  │
│  ─────────────────────────       │
│                                  │
│  ┌────┐ Product Name            │
│  │img │ Size: 8 | Color: White  │
│  │    │ ₹2,499                  │
│  └────┘ [-] 1 [+]     [🗑️]     │
│                                  │
├──────────────────────────────────┤
│                                  │
│  Have a coupon?                  │
│  [Enter code        ] [Apply]   │
│  ✓ FIRST20 applied: -₹580      │
│                                  │
│  ─────────────────────────       │
│  Subtotal:          ₹4,797      │
│  Discount:          -₹580       │
│  Shipping:          FREE        │
│  ─────────────────────────       │
│  Total:             ₹4,217      │
│                                  │
│  ┌────────────────────────────┐ │
│  │      CHECKOUT (₹4,217)     │ │
│  └────────────────────────────┘ │
│                                  │
│  [Continue Shopping]             │
│                                  │
└──────────────────────────────────┘
```

### 6.2 Full Cart Page

Same as drawer but in full-page layout with 2 columns:
- **Left (65%):** Cart items list with full details
- **Right (35%):** Order summary, coupon, checkout CTA

---

## 7. Checkout

### 7.1 Multi-Step Checkout

```
Step 1                Step 2              Step 3
[● Address] ──────── [○ Payment] ──────── [○ Review]


STEP 1: SHIPPING ADDRESS
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  ┌── Saved Addresses ─────────────────────────────────────────┐│
│  │                                                             ││
│  │  ┌─────────────────────┐  ┌─────────────────────┐         ││
│  │  │ ✓ HOME              │  │   OFFICE             │         ││
│  │  │ Priya Sharma        │  │   Priya Sharma       │         ││
│  │  │ 123 MG Road         │  │   Tower B, 5th Floor │         ││
│  │  │ Bangalore 560001    │  │   Pune 411001        │         ││
│  │  │ Ph: 9876543210      │  │   Ph: 9876543210     │         ││
│  │  │ [Edit]              │  │   [Edit]             │         ││
│  │  └─────────────────────┘  └─────────────────────┘         ││
│  │                                                             ││
│  │  [+ Add New Address]                                        ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  ┌── Order Summary ───────────────────────────────────────────┐│
│  │  3 items in cart                                            ││
│  │  Subtotal: ₹4,797   Discount: -₹580   Total: ₹4,217      ││
│  │                                                             ││
│  │  ┌────────────────────────────────────────┐                ││
│  │  │       CONTINUE TO PAYMENT →             │                ││
│  │  └────────────────────────────────────────┘                ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
└─────────────────────────────────────────────────────────────────┘


STEP 2: PAYMENT
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  Select Payment Method                                          │
│                                                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │ ⊙ UPI                                                  │    │
│  │   [Enter UPI ID              ] or [📱 QR Code]         │    │
│  ├────────────────────────────────────────────────────────┤    │
│  │ ○ Credit / Debit Card                                   │    │
│  │   [Card Number         ]                                │    │
│  │   [Expiry MM/YY] [CVV  ]                               │    │
│  │   [Name on Card        ]                                │    │
│  ├────────────────────────────────────────────────────────┤    │
│  │ ○ Net Banking                                           │    │
│  │   Popular: [SBI] [HDFC] [ICICI] [Axis]                 │    │
│  │   [Other Banks ▼]                                       │    │
│  ├────────────────────────────────────────────────────────┤    │
│  │ ○ Wallets                                               │    │
│  │   [Paytm] [PhonePe] [Amazon Pay]                       │    │
│  ├────────────────────────────────────────────────────────┤    │
│  │ ○ Cash on Delivery                                      │    │
│  │   ₹49 COD charges applicable                            │    │
│  └────────────────────────────────────────────────────────┘    │
│                                                                 │
│  ┌────────────────────────────────────────┐                    │
│  │         REVIEW ORDER →                 │                    │
│  └────────────────────────────────────────┘                    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘


STEP 3: REVIEW & PLACE ORDER
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  DELIVERY ADDRESS                    [Change]                   │
│  Priya Sharma, 123 MG Road, Bangalore 560001                   │
│  Est. Delivery: Jun 28-30                                       │
│                                                                 │
│  PAYMENT METHOD                      [Change]                   │
│  UPI - user@upi                                                 │
│                                                                 │
│  ORDER ITEMS                                                    │
│  ┌────┐ Floral Maxi Dress (M/Red)   ₹1,299 × 1 = ₹1,299     │
│  ┌────┐ Cotton Casual Top (L/Blue)   ₹999 × 2 = ₹1,998       │
│  ┌────┐ White Sneakers (8/White)     ₹2,499 × 1 = ₹2,499     │
│                                                                 │
│  ───────────────────────────────────                            │
│  Subtotal                                      ₹4,797          │
│  Coupon Discount (FIRST20)                    -₹580            │
│  Shipping                                      FREE            │
│  Tax (GST)                                     ₹500            │
│  ───────────────────────────────────                            │
│  ORDER TOTAL                                   ₹4,717          │
│                                                                 │
│  ┌────────────────────────────────────────┐                    │
│  │     🔒 PLACE ORDER — ₹4,717           │                    │
│  └────────────────────────────────────────┘                    │
│                                                                 │
│  By placing this order, you agree to our Terms & Privacy Policy │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 7.2 Order Confirmation Page

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│                    ✓ ORDER PLACED!                               │
│                                                                 │
│  Thank you, Priya! Your order has been placed successfully.     │
│                                                                 │
│  Order Number: ORD-20260625-00042                               │
│  Estimated Delivery: June 28-30, 2026                           │
│                                                                 │
│  We've sent a confirmation to priya@email.com                   │
│                                                                 │
│  ┌────────────────────────────────────────┐                    │
│  │          TRACK YOUR ORDER              │                    │
│  └────────────────────────────────────────┘                    │
│                                                                 │
│  [Continue Shopping]                                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 8. Wishlist

```
Breadcrumb: Home / My Wishlist

"My Wishlist" (12 items)

┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│  [img]   │ │  [img]   │ │  [img]   │ │  [img]   │
│  ♥       │ │  ♥       │ │  ♥       │ │  ♥       │
│ Product  │ │ Product  │ │ Product  │ │ Product  │
│ ₹1,299   │ │ ₹999     │ │ SOLD OUT │ │ ₹2,499   │
│[Add Cart]│ │[Add Cart]│ │[Notify]  │ │[Add Cart]│
│ [Remove] │ │ [Remove] │ │ [Remove] │ │ [Remove] │
└──────────┘ └──────────┘ └──────────┘ └──────────┘

Empty State: Heart illustration + "Your wishlist is empty" + [Discover Products]
```

---

## 9. My Account

### 9.1 Account Layout

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  ┌──────────────────┐  ┌──────────────────────────────────┐    │
│  │  ACCOUNT SIDEBAR │  │                                  │    │
│  │                  │  │        PAGE CONTENT               │    │
│  │  👤 Priya Sharma │  │                                  │    │
│  │  priya@email.com │  │                                  │    │
│  │                  │  │                                  │    │
│  │  ─────────────── │  │                                  │    │
│  │  📋 My Profile   │  │                                  │    │
│  │  📦 My Orders    │  │                                  │    │
│  │  📍 Addresses    │  │                                  │    │
│  │  ♡  Wishlist     │  │                                  │    │
│  │  🔔 Notifications│  │                                  │    │
│  │  🔒 Change Pwd   │  │                                  │    │
│  │  ─────────────── │  │                                  │    │
│  │  🚪 Logout       │  │                                  │    │
│  │                  │  │                                  │    │
│  └──────────────────┘  └──────────────────────────────────┘    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 9.2 Profile Page

```
MY PROFILE

┌─────────────────────────────────────────────────┐
│                                                 │
│  [Avatar]                                       │
│                                                 │
│  First Name:     [Priya               ]         │
│  Last Name:      [Sharma              ]         │
│  Email:          priya@email.com (verified ✓)   │
│  Phone:          [9876543210          ]         │
│  Date of Birth:  [15/03/1998          ]         │
│  Gender:         [Female  ▼]                    │
│                                                 │
│  [Save Changes]                                 │
│                                                 │
│  ─────────────────────────────────────          │
│                                                 │
│  PREFERENCES                                    │
│  ☑ Email notifications for orders               │
│  ☑ SMS notifications for delivery               │
│  ☐ Marketing emails                             │
│                                                 │
│  [Save Preferences]                             │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 10. Order History & Detail

### 10.1 Order History List

```
MY ORDERS

[All] [Active] [Completed] [Cancelled]    🔍 Search orders...

┌─────────────────────────────────────────────────────────────────┐
│  Order #ORD-20260625-00042            Placed: Jun 25, 2026     │
│                                                                 │
│  ┌────┐ Floral Maxi Dress (M/Red)                     ₹1,299  │
│  ┌────┐ Cotton Casual Top (L/Blue) × 2                ₹1,998  │
│  +1 more item                                                   │
│                                                                 │
│  Status: 🟢 Shipped  |  Total: ₹4,717                         │
│                                                                 │
│  [Track Order]  [View Details]                                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  Order #ORD-20260620-00038            Placed: Jun 20, 2026     │
│                                                                 │
│  ┌────┐ Silk Saree (Free/Red)                          ₹5,999  │
│                                                                 │
│  Status: ✅ Delivered  |  Total: ₹5,999                        │
│                                                                 │
│  [Write Review]  [Return]  [View Details]                       │
└─────────────────────────────────────────────────────────────────┘
```

### 10.2 Order Detail Page

```
Order #ORD-20260625-00042

STATUS TIMELINE
●━━━━━━━●━━━━━━━●━━━━━━━○━━━━━━━○
Placed   Confirmed  Shipped   Out for   Delivered
Jun 25   Jun 25     Jun 26    Delivery

ORDER ITEMS
┌────┐ Floral Maxi Dress          M / Red      ₹1,299 × 1 = ₹1,299
┌────┐ Cotton Casual Top          L / Blue     ₹999 × 2 = ₹1,998
┌────┐ White Sneakers             8 / White    ₹2,499 × 1 = ₹2,499

SHIPPING ADDRESS                      PAYMENT
Priya Sharma                          UPI - user@upi
123 MG Road                           Paid: ₹4,717
Bangalore 560001                      
Ph: 9876543210                        

ORDER SUMMARY
Subtotal:           ₹4,797
Discount:           -₹580
Shipping:           FREE
Tax:                ₹500
───────────────────────
Total:              ₹4,717

Tracking: XYZ123456789  [Track on courier website →]

[Download Invoice]  [Cancel Order]  [Need Help?]
```

---

## 11. Track Order

```
TRACK YOUR ORDER

[Enter Order Number or Email] [Track]

──── OR ────

Tracking Number: XYZ123456789

ORDER STATUS

●━━━━━━━●━━━━━━━●━━━━━━━○━━━━━━━○
Placed   Confirmed  Shipped   Out for   Delivered
Jun 25   Jun 25     Jun 26    Delivery
10:30am  11:15am    2:00pm

TRACKING UPDATES
┌─────────────────────────────────────────────────┐
│ Jun 26, 2:00 PM   Package shipped from Mumbai   │
│ Jun 26, 11:30 AM  Package picked up by courier  │
│ Jun 25, 11:15 AM  Order confirmed               │
│ Jun 25, 10:30 AM  Order placed                  │
└─────────────────────────────────────────────────┘
```

---

## 12. Blog

### 12.1 Blog Listing

```
FROM THE BLOG

[All] [Styling Tips] [Trends] [Fashion News] [Guides]

┌────────────────────────────────────────────────────┐
│  ┌──────────────────┐                              │
│  │                  │  STYLING TIPS                 │
│  │  [Featured       │  "10 Ways to Style a White   │
│  │   Image]         │   Shirt for Every Occasion"  │
│  │                  │                              │
│  │                  │  June 20, 2026 · 5 min read  │
│  │                  │                              │
│  │                  │  The white shirt is a         │
│  │                  │  timeless wardrobe staple...  │
│  └──────────────────┘                              │
│                           [Read More →]             │
└────────────────────────────────────────────────────┘
                  ↑ Featured post (larger)

┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   [img]      │ │   [img]      │ │   [img]      │
│ Category     │ │ Category     │ │ Category     │
│ Blog Title   │ │ Blog Title   │ │ Blog Title   │
│ Jun 18 · 3m  │ │ Jun 15 · 4m  │ │ Jun 12 · 6m  │
│ Excerpt...   │ │ Excerpt...   │ │ Excerpt...   │
│ [Read More]  │ │ [Read More]  │ │ [Read More]  │
└──────────────┘ └──────────────┘ └──────────────┘
```

### 12.2 Blog Detail

```
Breadcrumb: Home / Blog / Styling Tips / 10 Ways to Style...

Category: STYLING TIPS
"10 Ways to Style a White Shirt for Every Occasion"

By Priya Sharma  ·  June 20, 2026  ·  5 min read

[Share: 🔗 📘 🐦 📌]

┌────────────────────────────────────────────────────┐
│                                                    │
│              [Featured Image — Full Width]          │
│                                                    │
└────────────────────────────────────────────────────┘

Article content with rich formatting...

SHOP THE LOOK
┌──────────┐ ┌──────────┐ ┌──────────┐
│ Product  │ │ Product  │ │ Product  │
│ Related  │ │ Related  │ │ Related  │
└──────────┘ └──────────┘ └──────────┘

RELATED ARTICLES
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   [img]      │ │   [img]      │ │   [img]      │
│ Related Post │ │ Related Post │ │ Related Post │
└──────────────┘ └──────────────┘ └──────────────┘
```

---

## 13. About Us & Contact Us

### 13.1 About Us

```
"Our Story"

[Full-width brand image]

Mission statement paragraph...

OUR VALUES
┌──────────┐ ┌──────────┐ ┌──────────┐
│  🌿      │ │  💎      │ │  🤝      │
│Sustainable│ │ Quality  │ │Community │
│  Fashion  │ │  First   │ │ Driven   │
│ Text...   │ │ Text...  │ │ Text...  │
└──────────┘ └──────────┘ └──────────┘

[Team photos / Brand story timeline]
```

### 13.2 Contact Us

```
"Get in Touch"

┌───────────────────────────┐  ┌───────────────────────────┐
│                           │  │                           │
│  CONTACT FORM             │  │  CONTACT INFO             │
│                           │  │                           │
│  Name: [               ]  │  │  📍 123 Fashion Street    │
│  Email: [              ]  │  │     Mumbai 400001         │
│  Subject: [            ]  │  │                           │
│  Message:                 │  │  📞 1800-123-4567         │
│  [                     ]  │  │  📧 support@store.com     │
│  [                     ]  │  │                           │
│  [                     ]  │  │  BUSINESS HOURS           │
│                           │  │  Mon-Sat: 9AM - 8PM       │
│  [Send Message]           │  │  Sun: 10AM - 6PM          │
│                           │  │                           │
│                           │  │  FOLLOW US                │
│                           │  │  [fb] [ig] [tw] [yt]      │
│                           │  │                           │
└───────────────────────────┘  └───────────────────────────┘
```

---

## 14. Authentication Pages

### 14.1 Login

```
┌───────────────────────────────────────────────┐
│                                               │
│  ┌──────────┐    ┌────────────────────────┐  │
│  │          │    │                        │  │
│  │ [Fashion │    │  WELCOME BACK          │  │
│  │  Image]  │    │                        │  │
│  │          │    │  Email or Phone        │  │
│  │          │    │  [                   ] │  │
│  │          │    │                        │  │
│  │          │    │  Password              │  │
│  │          │    │  [                🔒] │  │
│  │          │    │                        │  │
│  │          │    │  ☐ Remember me         │  │
│  │          │    │  Forgot Password?      │  │
│  │          │    │                        │  │
│  │          │    │  [      LOGIN        ] │  │
│  │          │    │                        │  │
│  │          │    │  ──── OR ────          │  │
│  │          │    │                        │  │
│  │          │    │  [G] Login with Google │  │
│  │          │    │                        │  │
│  │          │    │  New here?             │  │
│  │          │    │  Create an Account     │  │
│  │          │    │                        │  │
│  └──────────┘    └────────────────────────┘  │
│                                               │
└───────────────────────────────────────────────┘
```

### 14.2 Register

```
Similar split layout with:
- First Name, Last Name
- Email
- Phone Number
- Password, Confirm Password
- ☐ I agree to Terms & Privacy Policy
- ☐ Subscribe to newsletter
- [CREATE ACCOUNT]
- OR login with Google
- Already have an account? Login
```

---

## 15. Mobile-First Design

### 15.1 Key Mobile Patterns

| Pattern | Implementation |
|---------|----------------|
| **Bottom Sheet** | Filters, sort options, size selection |
| **Swipe Gallery** | Product images, banners |
| **Pull to Refresh** | Product listings, order history |
| **Sticky CTA** | "Add to Cart" button fixed at bottom on PDP |
| **Infinite Scroll** | Product listings (vs pagination) |
| **Bottom Tab Bar** | Home, Categories, Wishlist, Account |
| **Full-Screen Search** | Search overlay with recent/trending |
| **Swipe to Delete** | Cart items, wishlist items |
| **Haptic Feedback** | Add to cart, wishlist toggle (where supported) |

### 15.2 Mobile-Specific Layouts

| Page | Mobile Adaptation |
|------|------------------|
| Homepage | Single column, horizontal scroll sections, smaller hero |
| Product Listing | 2-column grid, sticky filter/sort bar |
| Product Detail | Stacked layout, sticky "Add to Cart" bar at bottom |
| Cart | Full page, swipe to delete |
| Checkout | Single column, step indicators, large touch targets |
| Account | Stacked menu list |

---

## 16. Component Trees

### 16.1 Homepage Component Tree

```
App
└── CustomerLayout
    ├── AnnouncementBar
    ├── CustomerHeader
    │   ├── Logo
    │   ├── DesktopNav (MegaMenu)
    │   ├── SearchBar
    │   ├── WishlistIcon (with count)
    │   ├── UserMenu
    │   └── CartIcon (with count)
    ├── MobileNav (BottomTabBar)
    ├── RouterView → HomePage
    │   ├── HeroBannerCarousel
    │   │   └── HeroBannerSlide (×n)
    │   ├── USPBar
    │   │   └── USPItem (×4)
    │   ├── CategoryGrid
    │   │   └── CategoryCard (×n)
    │   ├── ProductSection (title="Trending Now")
    │   │   └── ProductCard (×n)
    │   ├── PromoBannerGrid
    │   │   └── PromoBanner (×2)
    │   ├── ProductSection (title="New Arrivals")
    │   │   └── ProductCard (×n)
    │   ├── LookbookBanner
    │   ├── ProductSection (title="Bestsellers")
    │   │   └── ProductCard (×n)
    │   ├── BrandGrid
    │   │   └── BrandLogo (×n)
    │   ├── TestimonialCarousel
    │   │   └── TestimonialCard (×n)
    │   ├── BlogPreviewSection
    │   │   └── BlogCard (×3)
    │   └── NewsletterSection
    └── CustomerFooter
        ├── FooterLinkColumns
        ├── SocialLinks
        ├── PaymentIcons
        └── Copyright
```

### 16.2 Product Detail Component Tree

```
App
└── CustomerLayout
    ├── CustomerHeader
    ├── RouterView → ProductDetailPage
    │   ├── BreadcrumbNav
    │   ├── ProductGallery
    │   │   ├── MainImage (with zoom)
    │   │   └── ThumbnailStrip
    │   ├── ProductInfo
    │   │   ├── BrandName
    │   │   ├── ProductTitle
    │   │   ├── RatingStars
    │   │   ├── PriceDisplay (MRP, selling, discount)
    │   │   ├── ColorSelector
    │   │   ├── SizeSelector
    │   │   │   └── SizeGuideLink → SizeGuideModal
    │   │   ├── QuantitySelector
    │   │   ├── AddToCartButton
    │   │   ├── BuyNowButton
    │   │   ├── WishlistButton
    │   │   ├── ShareButton
    │   │   ├── DeliveryChecker (pincode input)
    │   │   └── ProductHighlights
    │   ├── ProductTabs
    │   │   ├── Tab: Description
    │   │   ├── Tab: SizeGuide
    │   │   └── Tab: Reviews
    │   │       ├── ReviewSummary (avg, distribution)
    │   │       ├── ReviewList
    │   │       │   └── ReviewCard (×n)
    │   │       └── WriteReviewForm
    │   ├── RelatedProducts
    │   │   └── ProductCard (×n)
    │   └── RecentlyViewed
    │       └── ProductCard (×n)
    └── CustomerFooter
```

### 16.3 Checkout Component Tree

```
App
└── CustomerLayout (minimal header)
    ├── CheckoutHeader (logo + step indicator only)
    ├── RouterView → CheckoutPage
    │   ├── CheckoutSteps (progress indicator)
    │   ├── Step 1: AddressStep
    │   │   ├── SavedAddressList
    │   │   │   └── AddressCard (×n, selectable)
    │   │   ├── AddNewAddressButton → AddressFormModal
    │   │   └── ContinueButton
    │   ├── Step 2: PaymentStep
    │   │   ├── PaymentMethodSelector
    │   │   │   ├── UPIOption
    │   │   │   ├── CardOption
    │   │   │   ├── NetBankingOption
    │   │   │   ├── WalletOption
    │   │   │   └── CODOption
    │   │   └── ContinueButton
    │   ├── Step 3: ReviewStep
    │   │   ├── AddressSummary
    │   │   ├── PaymentSummary
    │   │   ├── OrderItemsList
    │   │   │   └── OrderItemRow (×n)
    │   │   ├── OrderSummary (pricing)
    │   │   └── PlaceOrderButton
    │   └── OrderSummary (sidebar — desktop)
    │       ├── CartItemPreview (×n)
    │       ├── CouponInput
    │       └── PricingSummary
    └── MinimalFooter
```

---

## User Flow Summary

```mermaid
graph TD
    A[Homepage] --> B[Category Page]
    A --> C[Search]
    A --> D[Product Detail]
    B --> E[Product Listing]
    C --> E
    E --> D
    D --> F{Add to Cart}
    F --> G[Cart Drawer]
    G --> H[Cart Page]
    H --> I[Checkout]
    D --> J{Add to Wishlist}
    J --> K[Login/Register]
    K --> L[Wishlist Page]
    L --> F
    I --> M[Step 1: Address]
    M --> N[Step 2: Payment]
    N --> O[Step 3: Review]
    O --> P[Payment Gateway]
    P --> Q{Success?}
    Q -->|Yes| R[Order Confirmation]
    Q -->|No| S[Retry Payment]
    S --> N
    R --> T[Order History]
    T --> U[Order Detail]
    U --> V[Track Order]
    U --> W[Return Request]
```

---

*End of Customer Website UX Document — Phase 5*
