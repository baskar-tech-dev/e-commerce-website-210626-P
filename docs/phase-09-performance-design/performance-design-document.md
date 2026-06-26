# Phase 9: Performance Design Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** Performance Architect
**Status:** Draft

---

## Table of Contents

1. [Performance Targets](#1-performance-targets)
2. [Caching Plan](#2-caching-plan)
3. [Redis Plan](#3-redis-plan)
4. [Queue Plan](#4-queue-plan)
5. [Database Optimization Plan](#5-database-optimization-plan)
6. [Frontend Performance](#6-frontend-performance)
7. [CDN Strategy](#7-cdn-strategy)
8. [Image Optimization](#8-image-optimization)
9. [Monitoring & Profiling](#9-monitoring--profiling)

---

## 1. Performance Targets

| Metric | Target | Measurement |
|--------|--------|-------------|
| **TTFB** (Time to First Byte) | < 200ms | Server response time |
| **FCP** (First Contentful Paint) | < 1.5s | Lighthouse |
| **LCP** (Largest Contentful Paint) | < 2.5s | Lighthouse |
| **TTI** (Time to Interactive) | < 3.0s | Lighthouse |
| **CLS** (Cumulative Layout Shift) | < 0.1 | Lighthouse |
| **API Response Time (p50)** | < 100ms | Application monitoring |
| **API Response Time (p95)** | < 200ms | Application monitoring |
| **API Response Time (p99)** | < 500ms | Application monitoring |
| **Concurrent Users** | 10,000+ | Load testing |
| **Database Query per Request** | < 10 avg | Query monitoring |
| **Lighthouse Performance** | > 90 | Lighthouse audit |
| **Uptime** | 99.9% | Uptime monitoring |

---

## 2. Caching Plan

### 2.1 Cache Layers

```
Request Flow with Caching:

Browser Cache (static assets)
    ↓ miss
CDN Cache (Cloudflare)
    ↓ miss
Nginx Cache (optional page cache)
    ↓ miss
Application Cache (Redis)
    ↓ miss
Database Query
    ↓ result
Store in Redis → Return Response
```

### 2.2 Application Cache Strategy (Redis)

| Cache Key Pattern | TTL | Data | Invalidation |
|-------------------|-----|------|-------------|
| `products:list:{filters_hash}` | 30 min | Paginated product listings | Product create/update/delete |
| `products:detail:{slug}` | 60 min | Single product with variants | Product/variant update |
| `products:featured` | 30 min | Homepage featured products | Manual or product flag change |
| `products:new_arrivals` | 30 min | New arrivals | Product creation |
| `products:bestsellers` | 60 min | Bestseller products | Hourly recalculation |
| `categories:tree` | 24 hr | Full category hierarchy | Category change |
| `categories:{slug}` | 12 hr | Category details | Category update |
| `brands:all` | 24 hr | All brands list | Brand change |
| `settings:{group}` | 24 hr | Settings by group | Settings update |
| `settings:all` | 24 hr | All settings | Settings update |
| `tax:rates` | 24 hr | Tax rate configuration | Tax settings change |
| `shipping:rules` | 24 hr | Shipping rules | Shipping settings change |
| `home:banners` | 60 min | Homepage banners | Manual refresh |
| `user:{id}:profile` | 60 min | User profile | Profile update |
| `user:{id}:cart` | 7 days | User's cart | Cart modification |
| `pincode:{code}` | 7 days | Delivery availability | Settings change |
| `search:suggestions:{query}` | 15 min | Search autocomplete | Product changes |
| `dashboard:stats:{period}` | 5 min | Admin dashboard stats | Order/payment events |

### 2.3 Cache Invalidation Strategy

| Strategy | When | Implementation |
|----------|------|----------------|
| **Event-driven** | Model changes | Listeners clear relevant cache tags |
| **Tag-based** | Group clearing | `Cache::tags(['products'])->flush()` |
| **TTL-based** | Auto expiry | All cache has TTL, eventual consistency |
| **Manual** | Admin action | "Clear Cache" button in admin settings |

### 2.4 Cache-Aside Pattern (Read-Through)

```php
// Service layer caching pattern
public function getProduct(string $slug): Product
{
    return Cache::tags(['products'])->remember(
        "products:detail:{$slug}",
        now()->addMinutes(60),
        fn() => $this->repository->findBySlug($slug)
    );
}
```

---

## 3. Redis Plan

### 3.1 Redis Configuration

| Setting | Value | Purpose |
|---------|-------|---------|
| Max Memory | 512MB–1GB | Configured based on server |
| Eviction Policy | `allkeys-lru` | Evict least recently used |
| Persistence | AOF (appendonly) | Data durability |
| Databases | 4 separate | Isolation |

### 3.2 Redis Database Allocation

| Database | Purpose | Key Prefix |
|----------|---------|-----------|
| DB 0 | Application cache | `cache:` |
| DB 1 | Sessions | `session:` |
| DB 2 | Queues | `queue:` |
| DB 3 | Rate limiting | `throttle:` |

### 3.3 Redis Data Structures

| Data | Structure | TTL |
|------|-----------|-----|
| Page cache | String (serialized JSON) | 5–60 min |
| User sessions | String (serialized) | 120 min |
| Rate limiting | Sorted Set | 1 min window |
| Cart data | Hash | 7 days |
| Real-time stock | String (integer) | None (persistent) |
| Trending searches | Sorted Set | 24 hours |
| Lock (stock deduction) | String (with TTL) | 30 seconds |

---

## 4. Queue Plan

### 4.1 Queue Configuration

| Queue | Workers | Max Tries | Timeout | Backoff | Purpose |
|-------|---------|-----------|---------|---------|---------|
| `high` | 3 | 3 | 60s | 10s | Payment verification, stock operations |
| `default` | 2 | 3 | 60s | 30s | General async tasks |
| `emails` | 2 | 3 | 30s | 60s | Transactional emails |
| `sms` | 1 | 3 | 15s | 30s | SMS notifications |
| `images` | 1 | 2 | 120s | 60s | Image processing |
| `reports` | 1 | 1 | 300s | 120s | Report generation |
| `exports` | 1 | 1 | 600s | 120s | CSV/PDF exports |
| `low` | 1 | 2 | 60s | 60s | Non-critical tasks |

### 4.2 Job Priority Assignment

| Priority | Jobs |
|----------|------|
| **Critical (high)** | `VerifyPayment`, `DeductStock`, `RestoreStock`, `ProcessRefund` |
| **Normal (default)** | `GenerateInvoice`, `SyncSearchIndex`, `UpdateProductStats`, `ClearCache` |
| **Email** | `SendOrderConfirmation`, `SendShippingNotification`, `SendPasswordReset` |
| **SMS** | `SendOTP`, `SendOrderStatusSMS`, `SendDeliverySMS` |
| **Image** | `ProcessProductImages`, `GenerateThumbnails`, `ConvertToWebP` |
| **Report** | `GenerateSalesReport`, `GenerateInventoryReport` |
| **Low** | `SendAbandonedCartReminder`, `UpdateCustomerStats`, `PurgeExpiredCarts` |

### 4.3 Scheduled Commands (Cron)

| Schedule | Command | Purpose |
|----------|---------|---------|
| Every minute | `queue:work` (via Supervisor) | Process queued jobs |
| Every 5 minutes | `release-expired-reservations` | Free stock from expired carts |
| Every 30 minutes | `clean-expired-carts` | Remove abandoned guest carts |
| Hourly | `recalculate-bestsellers` | Update bestseller rankings |
| Daily (2 AM) | `generate-daily-report` | Auto-generate daily summary |
| Daily (3 AM) | `backup:run` | Database and file backup |
| Daily (4 AM) | `purge-old-audit-logs` | Clean audit logs > 2 years |
| Weekly | `send-abandoned-cart-emails` | Batch process reminders |
| Monthly | `recalculate-customer-stats` | Update LTV, segments |

### 4.4 Failed Job Handling

| Strategy | Implementation |
|----------|----------------|
| Retry with backoff | Exponential: 10s → 30s → 90s |
| Dead letter | After max retries, move to `failed_jobs` table |
| Alert | Notify admin when failure rate > 5% in 1 hour |
| Dashboard | Laravel Horizon for real-time monitoring |
| Manual retry | Admin can retry failed jobs from Horizon |

---

## 5. Database Optimization Plan

### 5.1 Query Optimization

| Technique | Implementation |
|-----------|----------------|
| **Eager Loading** | Always use `with()` to prevent N+1 queries |
| **Select Specific Columns** | `->select(['id', 'name', 'price'])` instead of `select *` |
| **Chunking** | `chunk(100)` for batch processing large datasets |
| **Cursor** | `cursor()` for memory-efficient iteration |
| **Index Usage** | Verify queries use indexes via `EXPLAIN` |
| **Subquery Optimization** | Use subqueries instead of multiple queries |
| **Raw Queries** | For complex aggregations (reports) |

### 5.2 N+1 Prevention

```php
// BAD: N+1 query
$products = Product::all();
foreach ($products as $product) {
    echo $product->category->name;  // Extra query per product
}

// GOOD: Eager loaded
$products = Product::with(['category', 'variants', 'images'])->get();
```

### 5.3 Indexing Strategy

| Table | Index | Type | Purpose |
|-------|-------|------|---------|
| products | `(category_id, is_active, selling_price)` | Covering | Category listing sorted by price |
| products | `(is_active, is_featured, created_at)` | Composite | Featured products query |
| orders | `(user_id, status, created_at)` | Composite | Customer order history |
| orders | `(created_at, status)` | Composite | Admin order list by date |
| inventory_ledger | `(product_variant_id, created_at)` | Composite | Stock movement history |
| audit_logs | `(auditable_type, auditable_id, created_at)` | Composite | Model audit history |

### 5.4 Database Connection Pooling

| Setting | Value | Rationale |
|---------|-------|-----------|
| `max_connections` | 150 | Handle concurrent requests |
| `wait_timeout` | 60 | Close idle connections |
| `pool_min` | 10 | Keep minimum connections warm |
| `pool_max` | 50 | Per application instance |

### 5.5 Read Replica (Future)

```
Write Queries (INSERT, UPDATE, DELETE) → Primary MySQL
Read Queries (SELECT) → Read Replica

Laravel Configuration:
'mysql' => [
    'read' => ['host' => 'replica.host'],
    'write' => ['host' => 'primary.host'],
]
```

---

## 6. Frontend Performance

### 6.1 Code Splitting

| Technique | Implementation |
|-----------|----------------|
| **Route-based splitting** | `() => import('./views/ProductPage.vue')` |
| **Component lazy loading** | Large components loaded on demand |
| **Vendor chunk** | Separate chunk for node_modules |
| **CSS splitting** | Per-route CSS chunks |
| **Dynamic imports** | Heavy libraries loaded when needed (charts, editor) |

### 6.2 Bundle Optimization

| Technique | Implementation |
|-----------|----------------|
| **Tree shaking** | Vite default (ES modules) |
| **Minification** | Terser for JS, cssnano for CSS |
| **Compression** | Gzip + Brotli (Nginx) |
| **Source maps** | Production: hidden-source-map (Sentry only) |
| **Chunk size limit** | Warning at 250KB per chunk |
| **Preload critical chunks** | `<link rel="preload">` for route chunks |

### 6.3 Rendering Optimization

| Technique | Implementation |
|-----------|----------------|
| **Virtual scrolling** | For large product lists (100+ items) |
| **Debounced search** | 300ms debounce on search input |
| **Throttled scroll** | 100ms throttle on scroll events |
| **Image lazy loading** | `loading="lazy"` on below-fold images |
| **Skeleton loading** | Show content skeletons while loading |
| **Optimistic updates** | Update UI immediately, sync in background |
| **List virtualization** | Render only visible items in long lists |

### 6.4 Preloading Strategy

| Resource | Technique | When |
|----------|-----------|------|
| Critical CSS | Inline in `<head>` | Always |
| Fonts | `<link rel="preload" as="font">` | Always |
| Hero image | `<link rel="preload" as="image">` | Homepage |
| Next page | `<link rel="prefetch">` | On hover/viewport |
| API data | Prefetch on hover | Product card → detail |

---

## 7. CDN Strategy

### 7.1 CDN Configuration (Cloudflare)

| Content | Cache | TTL | Purge Strategy |
|---------|-------|-----|---------------|
| Static assets (JS, CSS) | Edge | 1 year | Cache busted via hash in filename |
| Product images | Edge | 30 days | Purge by URL on update |
| Font files | Edge | 1 year | Rarely change |
| API responses | No | — | Dynamic content |
| HTML (SPA shell) | Edge | 1 hour | Purge on deploy |

### 7.2 Cache Headers

```nginx
# Static assets (hashed filenames)
location ~* \.(js|css|woff2|woff|ttf)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# Images
location ~* \.(jpg|jpeg|png|webp|gif|svg|ico)$ {
    expires 30d;
    add_header Cache-Control "public";
}

# API responses
location /api/ {
    add_header Cache-Control "no-store, no-cache, must-revalidate";
}
```

---

## 8. Image Optimization

### 8.1 Image Pipeline

```
Upload Original
    ↓
Validate (type, size, dimensions)
    ↓
Queue Processing Job
    ↓
┌──────────────────────────────────────────────────┐
│                                                  │
│  Generate Variants:                              │
│  ├── Large:     1200px wide, quality 85%, WebP   │
│  ├── Medium:    600px wide, quality 80%, WebP    │
│  ├── Thumbnail: 300px wide, quality 75%, WebP    │
│  └── Blur:      20px wide, quality 20% (LQIP)   │
│                                                  │
│  Additional:                                     │
│  ├── Strip EXIF metadata                         │
│  ├── Auto-orient                                 │
│  └── Progressive encoding                        │
│                                                  │
└──────────────────────────────────────────────────┘
    ↓
Upload to S3
    ↓
Update DB with URLs
    ↓
Purge CDN cache for old URLs
```

### 8.2 Responsive Images

```html
<picture>
  <source srcset="/products/abc-300.webp 300w,
                  /products/abc-600.webp 600w,
                  /products/abc-1200.webp 1200w"
          type="image/webp">
  <img src="/products/abc-600.jpg"
       loading="lazy"
       alt="Product description"
       width="600"
       height="800">
</picture>
```

### 8.3 Image Specifications

| Context | Dimensions | Format | Quality | Lazy Load |
|---------|-----------|--------|---------|-----------|
| Hero banner | 1440×600 (desktop) | WebP | 85% | No (above fold) |
| Product listing | 300×400 | WebP | 75% | Yes |
| Product detail | 1200×1600 | WebP | 85% | First: No, Others: Yes |
| Category card | 400×400 | WebP | 80% | Yes |
| Blog thumbnail | 600×340 | WebP | 80% | Yes |
| Blog featured | 1200×680 | WebP | 85% | No |
| Brand logo | 200×100 | WebP/PNG | 90% | Yes |
| Avatar | 100×100 | WebP | 80% | Yes |

---

## 9. Monitoring & Profiling

### 9.1 Application Monitoring

| Tool | Purpose | Metrics |
|------|---------|---------|
| Laravel Telescope | Dev debugging | Queries, requests, jobs, exceptions |
| Laravel Horizon | Queue monitoring | Job throughput, failures, wait times |
| Sentry | Error tracking | Exceptions, breadcrumbs, releases |
| Application logs | Audit trail | Structured JSON logs via Monolog |

### 9.2 Infrastructure Monitoring

| Metric | Alert Threshold | Tool |
|--------|----------------|------|
| CPU Usage | > 80% for 5 min | Server monitoring |
| Memory Usage | > 85% | Server monitoring |
| Disk Usage | > 80% | Server monitoring |
| MySQL Connections | > 80% of max | MySQL monitoring |
| Redis Memory | > 80% of max | Redis monitoring |
| Queue Size | > 1000 pending | Horizon |
| Failed Jobs | > 10 in 1 hour | Horizon + alert |
| Response Time (p95) | > 500ms | APM |
| Error Rate | > 1% | Sentry |
| SSL Expiry | < 14 days | SSL monitoring |

### 9.3 Performance Profiling (Development)

| Tool | Usage |
|------|-------|
| `EXPLAIN ANALYZE` | Query performance analysis |
| Laravel Debugbar | Request profiling (dev only) |
| Clockwork | Request timeline, queries, cache hits |
| Xdebug Profiler | PHP function-level profiling |
| Lighthouse CI | Automated performance scoring in CI |
| WebPageTest | Real-world page load testing |

### 9.4 Load Testing Plan

| Test | Tool | Target |
|------|------|--------|
| API endpoint stress | k6 / Artillery | 1000 concurrent users, < 200ms p95 |
| Homepage load | k6 | 5000 concurrent, < 2s TTFB |
| Checkout flow | k6 | 500 concurrent checkouts, no failures |
| Search performance | k6 | 2000 concurrent searches, < 300ms |
| Database stress | mysqlslap | 10,000 concurrent queries |
| Queue throughput | Horizon | 1000 jobs/minute processing |

---

*End of Performance Design Document — Phase 9*
