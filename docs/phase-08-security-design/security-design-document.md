# Phase 8: Security Design Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** Security Architect
**Status:** Draft

---

## Table of Contents

1. [Security Architecture Overview](#1-security-architecture-overview)
2. [Authentication Security](#2-authentication-security)
3. [Authorization (RBAC)](#3-authorization-rbac)
4. [API Security](#4-api-security)
5. [Data Security](#5-data-security)
6. [Upload Security](#6-upload-security)
7. [Payment Security](#7-payment-security)
8. [Audit Logging](#8-audit-logging)
9. [OWASP Top 10 Mapping](#9-owasp-top-10-mapping)
10. [Security Checklist](#10-security-checklist)
11. [Threat Analysis](#11-threat-analysis)
12. [Incident Response](#12-incident-response)

---

## 1. Security Architecture Overview

### Security Layers

```
┌─────────────────────────────────────────────────────────────────┐
│ LAYER 1: NETWORK                                                │
│ Cloudflare DDoS protection, WAF, SSL/TLS 1.3                   │
├─────────────────────────────────────────────────────────────────┤
│ LAYER 2: SERVER                                                 │
│ Nginx security headers, firewall (UFW), fail2ban               │
├─────────────────────────────────────────────────────────────────┤
│ LAYER 3: APPLICATION                                            │
│ Laravel middleware, CORS, rate limiting, CSRF, input validation │
├─────────────────────────────────────────────────────────────────┤
│ LAYER 4: DATA                                                   │
│ Encryption at rest, PII protection, parameterized queries      │
├─────────────────────────────────────────────────────────────────┤
│ LAYER 5: MONITORING                                             │
│ Audit logs, intrusion detection, error tracking                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Authentication Security

### 2.1 Laravel Sanctum Configuration

| Setting | Value | Rationale |
|---------|-------|-----------|
| Token Type | API tokens (stateless) | SPA via token-based auth |
| Token Expiry | 7 days (configurable) | Balance security vs UX |
| Token Abilities | Scoped per role | Principle of least privilege |
| CSRF Protection | Not needed (token-based) | API-first approach |
| Secure Cookie | Yes (for web session fallback) | Prevent cookie theft |

### 2.2 Password Security

| Policy | Rule |
|--------|------|
| Minimum Length | 8 characters |
| Complexity | At least 1 uppercase, 1 lowercase, 1 number |
| Hashing | Bcrypt (cost factor 12) |
| Reset Token | 64-char random, hashed in DB, 60-min expiry |
| History | Prevent reuse of last 5 passwords (future) |
| Brute Force | Lock after 5 failed attempts for 15 minutes |
| OTP | 6-digit, 10-minute expiry, single use |

### 2.3 Session Security

| Setting | Value |
|---------|-------|
| Session Driver | Redis |
| Session Lifetime | 120 minutes |
| Session Encryption | Yes |
| Same-Site Cookie | `Lax` |
| HTTP Only | Yes |
| Secure | Yes (HTTPS only) |
| Regenerate on Login | Yes |

### 2.4 Authentication Flows

| Flow | Security Measures |
|------|-------------------|
| Login | Rate limited (5/min), failed attempt logging, IP tracking |
| Register | Email verification required, phone verification (OTP) |
| Password Reset | Rate limited, token hashed in DB, single use, 60-min expiry |
| Token Refresh | Silent refresh via interceptor, old token invalidated |
| Logout | Token revoked server-side, client token cleared |
| Social Login (Google) | OAuth 2.0, state parameter for CSRF, email verification skipped |

---

## 3. Authorization (RBAC)

### 3.1 Role Hierarchy

| Role | Level | Description |
|------|-------|-------------|
| Super Admin | 100 | Full unrestricted access |
| Store Manager | 80 | Products, orders, customers, inventory, basic reports |
| Inventory Manager | 60 | Inventory, purchase orders, stock adjustments |
| Content Manager | 50 | Blog, static pages, SEO |
| Customer Support | 40 | Orders (view/status), customers (view), returns |
| Finance | 40 | Reports, billing, reconciliation |
| Marketing | 30 | Coupons, promotions |
| Customer | 10 | Public API + own data |
| Guest | 0 | Public API only |

### 3.2 Permission Groups

| Group | Permissions |
|-------|------------|
| Products | `products.view`, `products.create`, `products.edit`, `products.delete` |
| Categories | `categories.view`, `categories.create`, `categories.edit`, `categories.delete` |
| Brands | `brands.view`, `brands.create`, `brands.edit`, `brands.delete` |
| Inventory | `inventory.view`, `inventory.adjust`, `inventory.purchase_orders` |
| Orders | `orders.view`, `orders.update_status`, `orders.cancel`, `orders.refund` |
| Returns | `returns.view`, `returns.approve`, `returns.reject`, `returns.process` |
| Customers | `customers.view`, `customers.edit`, `customers.delete` |
| Coupons | `coupons.view`, `coupons.create`, `coupons.edit`, `coupons.delete` |
| Blog | `blog.view`, `blog.create`, `blog.edit`, `blog.delete`, `blog.publish` |
| Reports | `reports.view`, `reports.export` |
| Settings | `settings.view`, `settings.edit` |
| Users | `users.view`, `users.create`, `users.edit`, `users.delete` |
| Roles | `roles.view`, `roles.create`, `roles.edit`, `roles.delete` |
| Audit | `audit.view` |

### 3.3 Data-Level Authorization

| Rule | Implementation |
|------|----------------|
| Customers can only access their own data | Policy: `OrderPolicy@view` checks `$order->user_id === $user->id` |
| Support can view but not delete orders | Permission: has `orders.view` but not `orders.delete` |
| Finance can view reports but not products | Role-based permission assignment |
| Content Manager cannot access orders | No order permissions assigned |

---

## 4. API Security

### 4.1 Rate Limiting

| Route Group | Limit | Window | Key |
|-------------|-------|--------|-----|
| Public API | 60 | per minute | IP |
| Auth (login) | 5 | per minute | IP + email |
| Auth (register) | 3 | per minute | IP |
| Auth (forgot-password) | 3 | per minute | IP + email |
| Authenticated API | 120 | per minute | User ID |
| Admin API | 120 | per minute | User ID |
| File Upload | 10 | per minute | User ID |
| Webhooks | 100 | per minute | IP |
| OTP Request | 3 | per minute | Phone |

### 4.2 CORS Configuration

```php
'paths' => ['api/*'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
'allowed_origins' => [env('FRONTEND_URL')],  // No wildcard
'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'Accept-Language'],
'exposed_headers' => ['X-Request-Id'],
'max_age' => 3600,
'supports_credentials' => true,
```

### 4.3 Security Headers (Nginx)

| Header | Value |
|--------|-------|
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `DENY` |
| `X-XSS-Protection` | `1; mode=block` |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` |
| `Content-Security-Policy` | Strict CSP with nonces |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` |
| `X-Request-Id` | Unique request UUID for tracing |

### 4.4 Input Validation

| Layer | Technique |
|-------|-----------|
| Client-side | Vue form validation (UX only, not trusted) |
| Server-side | Laravel Form Requests with strict rules |
| Database | Check constraints, foreign keys |
| Output | API Resources sanitize all output |

### 4.5 SQL Injection Prevention

| Technique | Implementation |
|-----------|----------------|
| Parameterized queries | Eloquent ORM (default) |
| No raw queries | Avoid `DB::raw()` unless absolutely needed |
| Query binding | `DB::select('...', [$param])` if raw needed |
| Input sanitization | Strip SQL metacharacters from search inputs |

### 4.6 XSS Prevention

| Technique | Implementation |
|-----------|----------------|
| Output encoding | All API responses JSON-encoded (auto-escaped) |
| Vue auto-escaping | `{{ }}` in templates (default) |
| No `v-html` with user input | Never render user content as raw HTML |
| Sanitize rich text | Server-side HTML purifier for blog content |
| CSP headers | Prevent inline script execution |

---

## 5. Data Security

### 5.1 Data Classification

| Classification | Data | Protection |
|----------------|------|------------|
| **Highly Sensitive** | Passwords, payment tokens, API keys | Hashed (bcrypt), encrypted env vars |
| **Sensitive (PII)** | Name, email, phone, address | Encrypted at rest, access-controlled |
| **Internal** | Orders, inventory, pricing | Access-controlled, audit logged |
| **Public** | Product info, blog content | No special protection needed |

### 5.2 PII Protection

| Data | Storage | Access |
|------|---------|--------|
| Email | Plain (needed for querying) | Rate-limited access, logged |
| Phone | Plain (needed for SMS) | Rate-limited, logged |
| Passwords | Bcrypt hashed | Never exposed in API |
| Addresses | Plain | Customer + authorized admin only |
| IP Addresses | Logged for 90 days | Security/audit access only |
| Payment Details | **Never stored** (delegated to gateway) | N/A |

### 5.3 Data Retention

| Data | Retention | Action |
|------|-----------|--------|
| User accounts | Until deletion request | Anonymize on delete |
| Order data | 7 years (legal) | Archive after 2 years |
| Audit logs | 2 years | Auto-purge |
| Session data | 24 hours after expiry | Auto-purge (Redis TTL) |
| Failed job logs | 30 days | Auto-purge |
| Temp uploads | 24 hours | Auto-cleanup |

### 5.4 Encryption

| Scope | Method |
|-------|--------|
| In Transit | TLS 1.3 (HTTPS) |
| At Rest (DB) | Application-level via Laravel's `Crypt` for PII fields |
| At Rest (Backups) | Encrypted backup files (GPG) |
| Env Variables | `.env` not in version control, server-level encryption |
| API Keys | Stored in `.env`, never in code |

### 5.5 Account Deletion (GDPR/Privacy)

```
User requests deletion:
1. Verify identity (password re-entry)
2. Cancel any active orders (if cancellable)
3. Anonymize PII:
   - Name → "Deleted User"
   - Email → hash@deleted.com
   - Phone → null
   - Addresses → deleted
4. Keep order records (anonymized) for financial compliance
5. Revoke all tokens
6. Set deleted_at timestamp (soft delete)
7. Purge from search index
8. Send confirmation email (to original email, then forget)
```

---

## 6. Upload Security

### 6.1 File Upload Rules

| Rule | Implementation |
|------|----------------|
| Allowed Types | JPEG, PNG, WebP, GIF only (whitelist) |
| Max File Size | 5MB per image |
| MIME Validation | Validate actual MIME type (not just extension) |
| Filename | Generate random hash, never use original filename |
| Storage | S3 (not local public directory) |
| No Execution | Files stored without execute permission |
| Virus Scan | ClamAV integration (optional, for production) |
| Image Processing | Re-encode all images (strips EXIF, malicious payloads) |
| Thumbnail Generation | Server-side, never trust client dimensions |
| Rate Limit | Max 10 uploads per minute per user |

### 6.2 File Access

| Content | Access Control |
|---------|---------------|
| Product images | Public (via CDN, no auth) |
| Invoices (PDF) | Private, signed URL (1-hour expiry) |
| Export files | Private, signed URL (1-hour expiry, single download) |
| User uploads (return photos) | Private, admin access only |

---

## 7. Payment Security

### 7.1 PCI Compliance

| Rule | Implementation |
|------|----------------|
| **No card data stored** | Payment delegated to Razorpay/Stripe |
| **No card data transmitted** | Client-side tokenization via gateway SDK |
| **Server only receives tokens** | Payment ID, order ID, signature |
| **Webhook verification** | Signature-based verification for all webhooks |
| **HTTPS only** | Payment pages enforce HTTPS |

### 7.2 Payment Verification

```
Payment Flow Security:
1. Server creates gateway order (amount, currency, receipt)
2. Client completes payment via gateway SDK
3. Client sends payment_id, order_id, signature to server
4. Server verifies signature using gateway secret
5. Server confirms payment amount matches order amount
6. Server updates order status
7. Webhook provides backup confirmation (double-verification)

Anti-Tampering:
- Amount locked server-side (not from client)
- Order total recalculated before payment initiation
- Signature verification prevents man-in-the-middle
```

### 7.3 Fraud Prevention

| Check | Implementation |
|-------|----------------|
| Order velocity | Max 3 orders per hour per user |
| Address mismatch | Flag if shipping ≠ billing country |
| IP geolocation | Flag orders from unusual locations |
| COD abuse | Limit COD per new customer (first 3 orders) |
| High-value orders | Flag orders above ₹50,000 for manual review |
| Coupon abuse | Track coupon usage patterns across accounts |
| Multiple accounts | Detect same email pattern / phone prefix |

---

## 8. Audit Logging

### 8.1 What's Logged

| Event | Data Captured |
|-------|--------------|
| Login success/failure | User ID, IP, user agent, timestamp |
| Password change | User ID, IP, timestamp |
| Data modification | User, model, old values, new values, IP |
| Order status change | User, order ID, from/to status |
| Payment events | Order ID, gateway response, amount |
| Admin actions | User, action, target, details |
| Permission changes | User, role/permission changes |
| Settings changes | User, key, old/new value |
| Export requests | User, report type, parameters |
| Deletion events | User, what was deleted, data snapshot |

### 8.2 Audit Log Security

| Rule | Implementation |
|------|----------------|
| Immutable | Audit logs are append-only (no update/delete) |
| Tamper-proof | Separate DB user with INSERT-only permissions |
| Retention | 2 years (configurable) |
| Access | Only Super Admin can view |
| Search | Indexed by user, model, event, timestamp |

---

## 9. OWASP Top 10 Mapping

| # | Vulnerability | Mitigation |
|---|--------------|------------|
| A01 | Broken Access Control | RBAC via policies, ownership checks, middleware |
| A02 | Cryptographic Failures | TLS 1.3, bcrypt passwords, encrypted PII, no card storage |
| A03 | Injection | Eloquent ORM, parameterized queries, input validation |
| A04 | Insecure Design | Security by design, threat modeling, code review |
| A05 | Security Misconfiguration | Hardened Nginx, secure headers, no debug in prod |
| A06 | Vulnerable Components | Regular `composer audit`, `npm audit`, automated alerts |
| A07 | Auth Failures | Rate limiting, account lockout, strong password policy |
| A08 | Software & Data Integrity | Webhook signature verification, CI/CD integrity checks |
| A09 | Logging & Monitoring | Comprehensive audit logs, error tracking (Sentry), alerts |
| A10 | SSRF | No user-supplied URLs in server requests, URL whitelist |

---

## 10. Security Checklist

### Pre-Production Checklist

| # | Check | Status |
|---|-------|--------|
| 1 | All API endpoints require appropriate authentication | ☐ |
| 2 | RBAC permissions enforced on all admin routes | ☐ |
| 3 | Rate limiting configured on all route groups | ☐ |
| 4 | CORS restricted to frontend domain only | ☐ |
| 5 | All security headers configured in Nginx | ☐ |
| 6 | SQL injection: No raw queries without binding | ☐ |
| 7 | XSS: No unescaped user input rendered | ☐ |
| 8 | File uploads: Type whitelist + size limit + MIME check | ☐ |
| 9 | Passwords: Bcrypt with cost ≥ 12 | ☐ |
| 10 | Payment: No card data stored, signature verified | ☐ |
| 11 | `.env` not in version control | ☐ |
| 12 | Debug mode OFF in production | ☐ |
| 13 | Error messages don't expose internals | ☐ |
| 14 | HTTPS enforced (redirect HTTP → HTTPS) | ☐ |
| 15 | Database credentials: Separate user with minimal privileges | ☐ |
| 16 | Audit logging enabled for all critical operations | ☐ |
| 17 | Dependency vulnerabilities checked (`composer audit`, `npm audit`) | ☐ |
| 18 | Admin panel not accessible on public subdomain (optional) | ☐ |
| 19 | Backups encrypted and stored securely | ☐ |
| 20 | Monitoring and alerting configured | ☐ |

---

## 11. Threat Analysis

### 11.1 Threat Model

| Threat | Likelihood | Impact | Mitigation |
|--------|-----------|--------|------------|
| Brute force login | High | Medium | Rate limiting, account lockout, CAPTCHA |
| SQL injection | Medium | Critical | ORM, parameterized queries, WAF |
| XSS attack | Medium | High | Output encoding, CSP, no v-html |
| Session hijacking | Low | High | Secure cookies, HTTPS, session regeneration |
| CSRF | Low | Medium | Token-based auth (inherently protected) |
| DDoS | Medium | High | Cloudflare, rate limiting, auto-scaling |
| Data breach | Low | Critical | Encryption, access control, audit logging |
| Payment fraud | Medium | High | Gateway verification, fraud checks |
| Account takeover | Medium | High | Email verification, password policy, MFA (future) |
| Insider threat | Low | Critical | RBAC, audit logs, principle of least privilege |
| Supply chain (npm) | Medium | High | Lock files, audit, minimal dependencies |
| Coupon abuse | High | Medium | Usage limits, validation, fraud detection |

---

## 12. Incident Response

### 12.1 Incident Response Plan

```
SEVERITY LEVELS:
├── P1 (Critical): Data breach, payment system compromise, full outage
├── P2 (High): Partial outage, security vulnerability discovered
├── P3 (Medium): Performance degradation, minor security issue
└── P4 (Low): UI bug, non-critical functionality issue

RESPONSE PROCEDURE:
1. DETECT → Monitoring alerts, user reports, audit log anomalies
2. CONTAIN → Isolate affected systems, revoke compromised credentials
3. ASSESS → Determine scope, affected data, root cause
4. REMEDIATE → Fix vulnerability, patch systems
5. RECOVER → Restore service, verify fix
6. REVIEW → Post-mortem, update security measures
```

---

*End of Security Design Document — Phase 8*
