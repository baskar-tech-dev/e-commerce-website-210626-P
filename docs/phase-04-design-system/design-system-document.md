# Phase 4: Design System Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** Senior UI Architect
**Status:** Draft

---

## Table of Contents

1. [Design Philosophy](#1-design-philosophy)
2. [Color System](#2-color-system)
3. [Typography](#3-typography)
4. [Spacing & Layout](#4-spacing--layout)
5. [Component Library](#5-component-library)
6. [Iconography](#6-iconography)
7. [Responsive Rules](#7-responsive-rules)
8. [Animation & Motion](#8-animation--motion)
9. [Accessibility Rules](#9-accessibility-rules)
10. [Dark Mode](#10-dark-mode)

---

## 1. Design Philosophy

### Brand Identity

| Attribute | Value |
|-----------|-------|
| **Personality** | Premium, Modern, Confident, Approachable |
| **Tone** | Sophisticated yet warm — luxury without intimidation |
| **Visual Style** | Clean minimalism with bold typography and rich imagery |
| **Target Aesthetic** | Contemporary fashion magazine meets seamless digital experience |

### Design Principles

| Principle | Description |
|-----------|-------------|
| **Content First** | Product images are the hero — UI should frame, not compete |
| **Effortless Navigation** | Maximum 3 clicks from homepage to checkout |
| **Whitespace is Luxury** | Generous spacing communicates premium quality |
| **Consistent, Not Uniform** | Same DNA across customer and admin, adapted for context |
| **Progressive Disclosure** | Show essential info first, details on demand |
| **Mobile Native** | Designed for touch-first, adapted upward for desktop |

---

## 2. Color System

### 2.1 Primary Palette

| Token | Light Mode | Dark Mode | Usage |
|-------|-----------|-----------|-------|
| `--color-primary-50` | `hsl(15, 85%, 97%)` | `hsl(15, 30%, 12%)` | Lightest tint / subtle backgrounds |
| `--color-primary-100` | `hsl(15, 80%, 93%)` | `hsl(15, 28%, 17%)` | Light backgrounds |
| `--color-primary-200` | `hsl(15, 75%, 85%)` | `hsl(15, 26%, 24%)` | Hover states |
| `--color-primary-300` | `hsl(15, 70%, 72%)` | `hsl(15, 40%, 40%)` | Borders, dividers |
| `--color-primary-400` | `hsl(15, 68%, 58%)` | `hsl(15, 55%, 55%)` | Icons, secondary text |
| `--color-primary-500` | `hsl(15, 72%, 48%)` | `hsl(15, 65%, 60%)` | **Primary brand — buttons, links, CTA** |
| `--color-primary-600` | `hsl(15, 75%, 40%)` | `hsl(15, 70%, 68%)` | Hover on primary |
| `--color-primary-700` | `hsl(15, 78%, 33%)` | `hsl(15, 60%, 75%)` | Active/pressed states |
| `--color-primary-800` | `hsl(15, 80%, 25%)` | `hsl(15, 50%, 82%)` | Dark accents |
| `--color-primary-900` | `hsl(15, 82%, 18%)` | `hsl(15, 45%, 90%)` | Darkest tint |

> **Primary Color: Warm Terracotta** — `hsl(15, 72%, 48%)` — Evokes warmth, elegance, and fashion-forward sensibility. Unique enough to stand out from typical blue/black e-commerce sites.

### 2.2 Neutral Palette

| Token | Light Mode | Dark Mode | Usage |
|-------|-----------|-----------|-------|
| `--color-neutral-0` | `#FFFFFF` | `#0D0D0F` | Pure white / dark background |
| `--color-neutral-50` | `#F9FAFB` | `#111114` | Page background |
| `--color-neutral-100` | `#F3F4F6` | `#18181C` | Card backgrounds |
| `--color-neutral-200` | `#E5E7EB` | `#222228` | Borders, dividers |
| `--color-neutral-300` | `#D1D5DB` | `#333340` | Disabled text |
| `--color-neutral-400` | `#9CA3AF` | `#55556A` | Placeholder text |
| `--color-neutral-500` | `#6B7280` | `#7A7A90` | Secondary text |
| `--color-neutral-600` | `#4B5563` | `#A0A0B5` | Body text |
| `--color-neutral-700` | `#374151` | `#C5C5D5` | Primary text |
| `--color-neutral-800` | `#1F2937` | `#E0E0EA` | Headings |
| `--color-neutral-900` | `#111827` | `#F0F0F5` | Strongest text |

### 2.3 Semantic Colors

| Token | Value (Light) | Value (Dark) | Usage |
|-------|--------------|-------------|-------|
| `--color-success-500` | `hsl(142, 65%, 40%)` | `hsl(142, 55%, 55%)` | Success states, in-stock |
| `--color-success-50` | `hsl(142, 60%, 96%)` | `hsl(142, 30%, 12%)` | Success background |
| `--color-warning-500` | `hsl(38, 90%, 50%)` | `hsl(38, 80%, 55%)` | Warning states, low stock |
| `--color-warning-50` | `hsl(38, 85%, 96%)` | `hsl(38, 30%, 12%)` | Warning background |
| `--color-error-500` | `hsl(0, 72%, 51%)` | `hsl(0, 65%, 60%)` | Error states, out of stock |
| `--color-error-50` | `hsl(0, 70%, 96%)` | `hsl(0, 30%, 12%)` | Error background |
| `--color-info-500` | `hsl(210, 70%, 50%)` | `hsl(210, 60%, 60%)` | Informational states |
| `--color-info-50` | `hsl(210, 65%, 96%)` | `hsl(210, 30%, 12%)` | Info background |

### 2.4 Special Fashion Colors

| Token | Value | Usage |
|-------|-------|-------|
| `--color-sale` | `hsl(0, 80%, 52%)` | Sale badges, discount prices |
| `--color-new` | `hsl(142, 65%, 40%)` | "New" badges |
| `--color-bestseller` | `hsl(38, 90%, 50%)` | "Bestseller" badges |
| `--color-limited` | `hsl(270, 60%, 50%)` | "Limited Edition" badges |
| `--color-rating` | `hsl(45, 95%, 55%)` | Star ratings |
| `--color-overlay` | `hsla(0, 0%, 0%, 0.5)` | Modal/image overlays |

### 2.5 Gradient Tokens

| Token | Value | Usage |
|-------|-------|-------|
| `--gradient-primary` | `linear-gradient(135deg, hsl(15, 72%, 48%), hsl(25, 80%, 55%))` | Primary CTA buttons, hero banners |
| `--gradient-dark` | `linear-gradient(180deg, hsl(0, 0%, 8%), hsl(0, 0%, 15%))` | Dark sections, footer |
| `--gradient-shine` | `linear-gradient(90deg, transparent, hsla(0,0%,100%,0.15), transparent)` | Shimmer/loading effect |
| `--gradient-glass` | `linear-gradient(135deg, hsla(0,0%,100%,0.1), hsla(0,0%,100%,0.05))` | Glassmorphism cards |

---

## 3. Typography

### 3.1 Font Families

| Token | Font | Fallback | Usage |
|-------|------|----------|-------|
| `--font-display` | 'Playfair Display' | Georgia, serif | Headings, hero text, luxury feel |
| `--font-body` | 'Inter' | system-ui, sans-serif | Body text, labels, buttons |
| `--font-mono` | 'JetBrains Mono' | monospace | Code, SKUs, order numbers |

> **Google Fonts Import:**
> `@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');`

### 3.2 Type Scale

| Token | Size | Line Height | Weight | Usage |
|-------|------|-------------|--------|-------|
| `--text-xs` | 0.75rem (12px) | 1.5 | 400 | Captions, fine print |
| `--text-sm` | 0.875rem (14px) | 1.5 | 400 | Secondary text, meta info |
| `--text-base` | 1rem (16px) | 1.6 | 400 | Body text (default) |
| `--text-md` | 1.125rem (18px) | 1.55 | 400 | Lead text, descriptions |
| `--text-lg` | 1.25rem (20px) | 1.5 | 500 | Section subtitles |
| `--text-xl` | 1.5rem (24px) | 1.4 | 600 | Card headings |
| `--text-2xl` | 1.875rem (30px) | 1.35 | 600 | Section headings |
| `--text-3xl` | 2.25rem (36px) | 1.3 | 700 | Page headings |
| `--text-4xl` | 3rem (48px) | 1.2 | 700 | Hero headings |
| `--text-5xl` | 3.75rem (60px) | 1.1 | 700 | Display text |

### 3.3 Font Weights

| Token | Value | Usage |
|-------|-------|-------|
| `--font-light` | 300 | De-emphasized text |
| `--font-regular` | 400 | Body text |
| `--font-medium` | 500 | Emphasis, labels, nav links |
| `--font-semibold` | 600 | Subheadings, button text |
| `--font-bold` | 700 | Headings, strong emphasis |

### 3.4 Letter Spacing

| Token | Value | Usage |
|-------|-------|-------|
| `--tracking-tight` | -0.025em | Large headings |
| `--tracking-normal` | 0 | Body text |
| `--tracking-wide` | 0.025em | Buttons, labels |
| `--tracking-wider` | 0.05em | All-caps text, badges |
| `--tracking-widest` | 0.1em | Brand name, hero caps |

---

## 4. Spacing & Layout

### 4.1 Spacing Scale (Base: 4px)

| Token | Value | Usage |
|-------|-------|-------|
| `--space-0` | 0 | No spacing |
| `--space-0.5` | 0.125rem (2px) | Micro adjustments |
| `--space-1` | 0.25rem (4px) | Tight gaps |
| `--space-1.5` | 0.375rem (6px) | |
| `--space-2` | 0.5rem (8px) | Icon gaps, inline spacing |
| `--space-3` | 0.75rem (12px) | Compact padding |
| `--space-4` | 1rem (16px) | Standard padding |
| `--space-5` | 1.25rem (20px) | |
| `--space-6` | 1.5rem (24px) | Card padding |
| `--space-8` | 2rem (32px) | Section gaps |
| `--space-10` | 2.5rem (40px) | |
| `--space-12` | 3rem (48px) | Section padding |
| `--space-16` | 4rem (64px) | Major section gaps |
| `--space-20` | 5rem (80px) | Page section padding |
| `--space-24` | 6rem (96px) | Hero section padding |
| `--space-32` | 8rem (128px) | Max section spacing |

### 4.2 Container Widths

| Token | Value | Usage |
|-------|-------|-------|
| `--container-sm` | 640px | Narrow content (auth forms) |
| `--container-md` | 768px | Medium content (blog) |
| `--container-lg` | 1024px | Standard content |
| `--container-xl` | 1280px | Wide content (product grid) |
| `--container-2xl` | 1440px | Maximum content width |

### 4.3 Grid System

```css
/* Product Grid */
.product-grid {
  display: grid;
  gap: var(--space-6);
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
}

/* Responsive overrides */
@media (max-width: 640px)  { /* 2 columns, min 150px */ }
@media (min-width: 1024px) { /* 4 columns */ }
@media (min-width: 1280px) { /* 5 columns for admin */ }
```

### 4.4 Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `--radius-none` | 0 | Sharp edges |
| `--radius-sm` | 0.25rem (4px) | Badges, tags |
| `--radius-md` | 0.5rem (8px) | Buttons, inputs |
| `--radius-lg` | 0.75rem (12px) | Cards |
| `--radius-xl` | 1rem (16px) | Large cards, modals |
| `--radius-2xl` | 1.5rem (24px) | Feature sections |
| `--radius-full` | 9999px | Avatars, pills |

### 4.5 Shadows

| Token | Value | Usage |
|-------|-------|-------|
| `--shadow-sm` | `0 1px 2px hsla(0,0%,0%,0.05)` | Subtle lift |
| `--shadow-md` | `0 4px 6px hsla(0,0%,0%,0.07), 0 2px 4px hsla(0,0%,0%,0.05)` | Cards |
| `--shadow-lg` | `0 10px 15px hsla(0,0%,0%,0.1), 0 4px 6px hsla(0,0%,0%,0.05)` | Dropdowns, popovers |
| `--shadow-xl` | `0 20px 25px hsla(0,0%,0%,0.1), 0 8px 10px hsla(0,0%,0%,0.04)` | Modals, dialogs |
| `--shadow-inner` | `inset 0 2px 4px hsla(0,0%,0%,0.06)` | Pressed inputs |
| `--shadow-glow` | `0 0 20px hsla(15,72%,48%,0.3)` | Focus glow |

### 4.6 Z-Index Scale

| Token | Value | Usage |
|-------|-------|-------|
| `--z-base` | 0 | Default |
| `--z-above` | 10 | Sticky elements |
| `--z-dropdown` | 100 | Dropdowns, popovers |
| `--z-sticky` | 200 | Sticky header |
| `--z-drawer` | 300 | Side drawers |
| `--z-overlay` | 400 | Background overlays |
| `--z-modal` | 500 | Modal dialogs |
| `--z-toast` | 600 | Toast notifications |
| `--z-tooltip` | 700 | Tooltips |
| `--z-max` | 9999 | Critical overlays |

---

## 5. Component Library

### 5.1 Buttons

#### Variants

| Variant | Appearance | Usage |
|---------|-----------|-------|
| **Primary** | Solid primary color with gradient | Main CTA — "Add to Cart", "Place Order" |
| **Secondary** | Outlined with primary border | Secondary actions — "Add to Wishlist", "Cancel" |
| **Ghost** | No background, text only | Tertiary actions — "View All", "Back" |
| **Danger** | Solid error color | Destructive actions — "Delete", "Remove" |
| **Success** | Solid success color | Positive actions — "Approve", "Confirm" |
| **Dark** | Solid dark (near black) | "Buy Now", premium feel |

#### Sizes

| Size | Height | Padding | Font Size | Icon Size |
|------|--------|---------|-----------|-----------|
| `xs` | 28px | 8px 12px | 12px | 14px |
| `sm` | 36px | 8px 16px | 14px | 16px |
| `md` | 44px | 10px 24px | 16px | 18px |
| `lg` | 52px | 12px 32px | 18px | 20px |
| `xl` | 60px | 14px 40px | 18px | 22px |

#### States

| State | Visual Change |
|-------|--------------|
| Default | Base appearance |
| Hover | Lighten 5%, subtle shadow, scale(1.02) |
| Active/Pressed | Darken 5%, shadow-inner, scale(0.98) |
| Focus | Ring outline (2px primary glow) |
| Disabled | 50% opacity, cursor not-allowed |
| Loading | Spinner replaces text/icon, disabled state |

### 5.2 Inputs

#### Types

| Type | Component | Features |
|------|-----------|----------|
| Text | `BaseInput` | Label, placeholder, error, helper text, prefix/suffix icon |
| Textarea | `BaseTextarea` | Auto-resize, character count |
| Select | `BaseSelect` | Custom dropdown, search, multi-select |
| Checkbox | `BaseCheckbox` | Custom design, indeterminate state |
| Radio | `BaseRadio` | Custom design, group support |
| Toggle | `BaseToggle` | Switch toggle, label |
| File | `BaseFileUpload` | Drag & drop, preview, progress |
| Search | `BaseSearchInput` | Debounced, autocomplete |
| Date | `BaseDatePicker` | Calendar popup, range selection |
| Number | `BaseNumberInput` | Increment/decrement buttons |

#### Input States

| State | Border Color | Background | Icon |
|-------|-------------|------------|------|
| Default | `--color-neutral-300` | `--color-neutral-0` | — |
| Focused | `--color-primary-500` + glow | `--color-neutral-0` | — |
| Error | `--color-error-500` | `--color-error-50` | Error icon |
| Success | `--color-success-500` | `--color-success-50` | Check icon |
| Disabled | `--color-neutral-200` | `--color-neutral-100` | — |
| Read-only | `--color-neutral-200` | `--color-neutral-50` | Lock icon |

#### Input Sizes

| Size | Height | Font Size | Label Size |
|------|--------|-----------|------------|
| `sm` | 36px | 14px | 12px |
| `md` | 44px | 16px | 14px |
| `lg` | 52px | 18px | 16px |

### 5.3 Cards

| Variant | Features | Usage |
|---------|----------|-------|
| **Product Card** | Image, name, price, rating, wishlist icon, quick-add | Product listings |
| **Category Card** | Image overlay, name, product count | Category grid |
| **Order Card** | Order number, status badge, date, items preview, total | Order history |
| **Stats Card** | Icon, label, value, trend indicator, sparkline | Admin dashboard |
| **Blog Card** | Featured image, category badge, title, excerpt, date | Blog listing |
| **Review Card** | Avatar, name, rating stars, text, date, helpful count | Product reviews |
| **Address Card** | Label, full address, default badge, edit/delete | Address book |

#### Card Structure

```
┌─────────────────────────────┐
│     Image / Visual Area     │  ← Aspect ratio: 3:4 (product), 16:9 (blog)
│                             │
│  ┌─────┐           ┌─────┐ │  ← Overlay elements (sale badge, wishlist)
│  │SALE │           │  ♡  │ │
│  └─────┘           └─────┘ │
├─────────────────────────────┤
│  Category / Brand            │  ← Micro text, muted
│  Product Name               │  ← Medium weight, truncate 2 lines
│  ★★★★☆ (4.2) · 128 reviews │  ← Rating + count
│                             │
│  ₹1,299  ₹1,999  35% off   │  ← Price: bold, MRP: strikethrough, discount
│                             │
│  [Add to Cart]              │  ← CTA button (shows on hover/mobile tap)
└─────────────────────────────┘
```

### 5.4 Modals

| Variant | Max Width | Usage |
|---------|----------|-------|
| Small | 400px | Confirmation dialogs |
| Medium | 560px | Forms, product quick view |
| Large | 720px | Complex forms, detail views |
| Full | 90vw | Image gallery, large tables |

#### Modal Features
- Backdrop overlay with blur
- Slide-up animation (mobile), fade-scale (desktop)
- Focus trap (keyboard)
- Close on Escape key
- Close on backdrop click (optional)
- Scroll lock on body

### 5.5 Tables (Admin)

| Feature | Implementation |
|---------|----------------|
| Sorting | Click column header, visual indicator (↑↓) |
| Filtering | Inline filter row or filter panel |
| Pagination | Page numbers + per-page selector |
| Selection | Checkbox column, select all, bulk actions bar |
| Actions | Row action menu (Edit, View, Delete) |
| Responsive | Horizontal scroll on mobile, priority columns |
| Loading | Skeleton shimmer rows |
| Empty State | Illustration + message + CTA |

### 5.6 Dropdowns

| Type | Features |
|------|----------|
| **Simple** | List of options, single select |
| **Searchable** | Type-to-filter, keyboard navigation |
| **Multi-select** | Checkboxes, tag chips, "Select All" |
| **Grouped** | Option groups with headers |
| **Cascade** | Multi-level (Category → Subcategory) |

### 5.7 Badges & Tags

| Type | Appearance | Usage |
|------|-----------|-------|
| Status Badge | Rounded pill, semantic color + dot | Order status, stock status |
| Sale Badge | Red background, white text, "% OFF" | Discount indicator |
| Tag | Outlined, small, rounded | Product tags, filter chips |
| Count Badge | Circular, primary, over icon | Cart count, notifications |
| New Badge | Green, uppercase "NEW" | New arrivals |

### 5.8 Toast Notifications

| Type | Icon | Color | Duration |
|------|------|-------|----------|
| Success | ✓ Checkmark | Green | 3 seconds |
| Error | ✕ Cross | Red | 5 seconds (or persistent) |
| Warning | ⚠ Triangle | Amber | 4 seconds |
| Info | ℹ Circle | Blue | 3 seconds |

#### Toast Behavior
- Stacked from top-right (desktop) or bottom (mobile)
- Slide-in animation
- Auto-dismiss with progress bar
- Swipe to dismiss (mobile)
- Click to dismiss (desktop)
- Maximum 3 visible at once

### 5.9 Tabs

| Variant | Style | Usage |
|---------|-------|-------|
| Underline | Bottom border indicator | Product details (Description, Reviews, Sizing) |
| Pill | Filled background | Admin filters (All, Active, Inactive) |
| Segmented | Button group | View toggles (Grid/List) |

### 5.10 Pagination

| Variant | Features | Usage |
|---------|----------|-------|
| Numbered | Page numbers, prev/next, first/last | Admin tables |
| Load More | "Load More" button + count | Customer product listings |
| Infinite Scroll | Auto-load on scroll | Mobile product browsing |
| Cursor-based | Next/Prev buttons | API-driven (large datasets) |

### 5.11 Breadcrumbs

```
Home  /  Women  /  Dresses  /  Maxi Dresses

─  Separator: " / " or chevron icon
─  Last item: non-linked, current page
─  Truncate on mobile (show last 2 levels)
─  Schema.org BreadcrumbList markup
```

### 5.12 Empty States

| Context | Illustration | Message | CTA |
|---------|-------------|---------|-----|
| Empty Cart | Shopping bag illustration | "Your cart is empty" | "Start Shopping" |
| No Results | Search illustration | "No products found" | "Clear Filters" |
| No Orders | Package illustration | "No orders yet" | "Browse Products" |
| Empty Wishlist | Heart illustration | "Your wishlist is empty" | "Discover Products" |
| No Reviews | Star illustration | "No reviews yet" | "Be the first to review" |

---

## 6. Iconography

### 6.1 Icon System

| Attribute | Value |
|-----------|-------|
| Library | Lucide Icons (open-source, consistent with Inter) |
| Default Size | 20px |
| Stroke Width | 1.5px |
| Style | Outline (consistent, modern look) |

### 6.2 Key Icons

| Icon | Usage | Lucide Name |
|------|-------|-------------|
| Search | Search bar, product search | `search` |
| Cart | Shopping cart | `shopping-bag` |
| Wishlist | Wishlist (empty) | `heart` |
| Wishlist (active) | Wishlist (filled) | `heart` (filled) |
| User | Account, profile | `user` |
| Menu | Mobile hamburger | `menu` |
| Close | Modal close, clear | `x` |
| Filter | Filter toggle | `sliders-horizontal` |
| Sort | Sort selector | `arrow-up-down` |
| Star | Rating stars | `star` |
| Truck | Shipping, delivery | `truck` |
| Package | Orders, products | `package` |
| Tag | Coupons, tags | `tag` |
| Trash | Delete actions | `trash-2` |
| Edit | Edit actions | `pencil` |
| Eye | View, visibility | `eye` |
| Plus | Add, increment | `plus` |
| Minus | Remove, decrement | `minus` |
| Chevron | Navigation, expand | `chevron-right` |
| Check | Success, selected | `check` |
| Alert | Warning, error | `alert-triangle` |
| Info | Information | `info` |

---

## 7. Responsive Rules

### 7.1 Breakpoints

| Token | Value | Device | Approach |
|-------|-------|--------|----------|
| `--bp-xs` | 0–479px | Small phone | Base styles (mobile-first) |
| `--bp-sm` | 480–639px | Large phone | `@media (min-width: 480px)` |
| `--bp-md` | 640–767px | Tablet portrait | `@media (min-width: 640px)` |
| `--bp-lg` | 768–1023px | Tablet landscape | `@media (min-width: 768px)` |
| `--bp-xl` | 1024–1279px | Small desktop | `@media (min-width: 1024px)` |
| `--bp-2xl` | 1280–1439px | Desktop | `@media (min-width: 1280px)` |
| `--bp-3xl` | 1440px+ | Large desktop | `@media (min-width: 1440px)` |

### 7.2 Responsive Behavior

| Element | Mobile (< 768px) | Tablet (768–1023px) | Desktop (1024px+) |
|---------|-------------------|--------------------|--------------------|
| **Navigation** | Bottom tab bar + hamburger drawer | Top header + condensed nav | Full horizontal nav |
| **Product Grid** | 2 columns | 3 columns | 4–5 columns |
| **Product Card** | Compact, no hover effects | Standard | Full with hover actions |
| **Filters** | Full-screen overlay | Side drawer | Persistent sidebar |
| **Cart** | Full page | Slide-out drawer | Slide-out drawer |
| **Checkout** | Stacked single-column | Two-column | Two-column |
| **Admin Sidebar** | Collapsed icon-only or hidden | Collapsed icon-only | Full sidebar |
| **Tables** | Horizontal scroll or card view | Horizontal scroll | Full table |
| **Modal** | Full-screen slide-up | Centered, max 90vw | Centered, fixed width |
| **Hero Banner** | 3:4 aspect, stacked text | 16:9 aspect | 21:9 aspect |
| **Font Sizes** | Base 15px | Base 16px | Base 16px |

### 7.3 Touch Targets

| Element | Minimum Size | Spacing |
|---------|-------------|---------|
| Buttons | 44 × 44px | 8px between |
| Links | 44 × 44px (hit area) | 8px between |
| Checkboxes | 44 × 44px (hit area) | — |
| Close Buttons | 44 × 44px | — |
| Nav Items | 48px height | — |

---

## 8. Animation & Motion

### 8.1 Timing Functions

| Token | Value | Usage |
|-------|-------|-------|
| `--ease-in-out` | `cubic-bezier(0.4, 0, 0.2, 1)` | General transitions |
| `--ease-out` | `cubic-bezier(0, 0, 0.2, 1)` | Entrances |
| `--ease-in` | `cubic-bezier(0.4, 0, 1, 1)` | Exits |
| `--ease-spring` | `cubic-bezier(0.34, 1.56, 0.64, 1)` | Bouncy/playful |

### 8.2 Duration Scale

| Token | Value | Usage |
|-------|-------|-------|
| `--duration-fast` | 100ms | Hover color changes |
| `--duration-normal` | 200ms | Standard transitions |
| `--duration-slow` | 300ms | Modals, drawers |
| `--duration-slower` | 500ms | Page transitions |
| `--duration-slowest` | 800ms | Complex animations |

### 8.3 Common Animations

| Animation | Trigger | Effect |
|-----------|---------|--------|
| **Fade In** | Page/component mount | Opacity 0 → 1 |
| **Slide Up** | Modal open, toast | translateY(20px) → 0 |
| **Slide In Right** | Drawer open | translateX(100%) → 0 |
| **Scale** | Card hover, button press | scale(1) → scale(1.02) |
| **Shimmer** | Loading skeleton | Gradient sweep left-to-right |
| **Spin** | Loading spinner | 360° rotation, infinite |
| **Pulse** | Notification badge | Scale pulse 1 → 1.2 → 1 |
| **Stagger** | Product grid load | Cascade delay per item |
| **Heart** | Wishlist toggle | Scale bounce 1 → 1.3 → 1 |
| **Add to Cart** | Product added | Fly-to-cart micro-animation |
| **Price Change** | Coupon applied | Digit roll animation |

### 8.4 Motion Rules

| Rule | Detail |
|------|--------|
| **Respect Preferences** | Honor `prefers-reduced-motion: reduce` |
| **No Layout Shift** | Animations should not cause layout reflows |
| **Progressive** | Enhance, don't block — content accessible without JS |
| **Purposeful** | Every animation must serve UX (guide attention, confirm action) |
| **Consistent** | Same animation for same action pattern |

---

## 9. Accessibility Rules

### 9.1 WCAG 2.1 AA Compliance

| Criteria | Standard | Implementation |
|----------|----------|----------------|
| Color Contrast (text) | 4.5:1 minimum | All text colors verified |
| Color Contrast (large text) | 3:1 minimum | Headings 24px+ |
| Color Contrast (UI) | 3:1 minimum | Borders, icons, focus rings |
| Focus Visibility | Visible focus indicator | 2px solid primary + glow |
| Target Size | 44 × 44px minimum | All interactive elements |
| Alt Text | Descriptive text | All images |
| ARIA Labels | Descriptive labels | All interactive elements |
| Keyboard Navigation | Full tab support | All flows completable via keyboard |
| Screen Reader | Semantic HTML | Proper heading hierarchy, landmarks |
| Error Identification | Clear error messages | Associated with form fields |

### 9.2 ARIA Patterns

| Component | ARIA Pattern |
|-----------|-------------|
| Modal | `role="dialog"`, `aria-modal="true"`, `aria-labelledby` |
| Tabs | `role="tablist"`, `role="tab"`, `role="tabpanel"` |
| Dropdown | `role="listbox"`, `role="option"`, `aria-expanded` |
| Toast | `role="alert"`, `aria-live="polite"` |
| Navigation | `role="navigation"`, `aria-label` |
| Search | `role="search"`, `aria-label` |
| Loading | `aria-busy="true"`, `aria-live="polite"` |
| Breadcrumb | `role="navigation"`, `aria-label="Breadcrumb"` |
| Product Card | `role="article"`, meaningful link text |
| Rating Stars | `aria-label="4 out of 5 stars"` |

### 9.3 Focus Management

| Scenario | Behavior |
|----------|----------|
| Modal Open | Focus moves to first focusable element inside modal |
| Modal Close | Focus returns to trigger element |
| Drawer Open | Focus moves to drawer close button |
| Tab Switch | Focus moves to tab panel content |
| Form Error | Focus moves to first field with error |
| Page Navigation | Focus moves to page heading (`<h1>`) |
| Infinite Scroll | Announce new items via `aria-live` |

### 9.4 Color-Blind Safe

| Strategy | Implementation |
|----------|----------------|
| Don't rely on color alone | Use icons + text alongside color indicators |
| Status badges | Color + icon + text label |
| Stock status | "In Stock ✓" (green) vs "Out of Stock ✕" (red) — both have text |
| Form errors | Red border + error icon + error text |

---

## 10. Dark Mode

### 10.1 Implementation Strategy

| Aspect | Implementation |
|--------|----------------|
| Toggle | System preference auto-detect + manual toggle |
| Persistence | `localStorage` + `prefers-color-scheme` media query |
| CSS | CSS custom properties switch via `[data-theme="dark"]` on `<html>` |
| Transition | Smooth `200ms` transition on color properties |

### 10.2 Dark Mode Rules

| Rule | Detail |
|------|--------|
| **Don't invert everything** | Use adjusted dark-specific colors, not inverted |
| **Reduce elevation** | Lighter surfaces for elevated elements (not shadows) |
| **Maintain contrast** | Test all color combinations in dark mode |
| **Dim images** | Slight opacity reduction on product images (optional) |
| **Saturate less** | Reduce color saturation slightly in dark mode |
| **Status colors** | Use lighter, less saturated variants in dark mode |

### 10.3 Surface Colors (Dark Mode)

```
Level 0 (Background):  #0D0D0F  ← Page background
Level 1 (Surface):     #111114  ← Card background
Level 2 (Elevated):    #18181C  ← Dropdown, modal background
Level 3 (Overlay):     #222228  ← Hover states
Level 4 (Active):      #333340  ← Active/pressed states
```

---

## CSS Custom Properties Summary

```css
:root {
  /* Colors — see section 2 for full palette */
  --color-primary-500: hsl(15, 72%, 48%);
  
  /* Typography */
  --font-display: 'Playfair Display', Georgia, serif;
  --font-body: 'Inter', system-ui, -apple-system, sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
  
  /* Spacing */
  --space-1: 0.25rem;
  --space-2: 0.5rem;
  --space-4: 1rem;
  --space-6: 1.5rem;
  --space-8: 2rem;
  
  /* Borders */
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  
  /* Shadows */
  --shadow-md: 0 4px 6px hsla(0,0%,0%,0.07);
  
  /* Transitions */
  --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
  --duration-normal: 200ms;
  
  /* Z-index */
  --z-modal: 500;
  --z-toast: 600;
}
```

---

*End of Design System Document — Phase 4*
