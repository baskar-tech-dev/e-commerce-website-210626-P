# Phase 12: Deployment Plan Document

## Fashion E-Commerce Platform

**Document Version:** 1.0
**Date:** 2026-06-25
**Author:** DevOps Architect
**Status:** Draft

---

## Table of Contents

1. [Infrastructure](#1-infrastructure)
2. [Server Setup](#2-server-setup)
3. [Nginx Configuration](#3-nginx-configuration)
4. [SSL & Security](#4-ssl--security)
5. [Application Deployment](#5-application-deployment)
6. [CI/CD Pipeline](#6-cicd-pipeline)
7. [Backup Strategy](#7-backup-strategy)
8. [Monitoring](#8-monitoring)
9. [Scaling Strategy](#9-scaling-strategy)
10. [Disaster Recovery](#10-disaster-recovery)

---

## 1. Infrastructure

### 1.1 Server Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                     PRODUCTION SETUP                             │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  DNS: Cloudflare (DNS + CDN + DDoS + WAF)                       │
│                                                                  │
│  ┌── Application Server (VPS) ─────────────────────────────┐    │
│  │                                                         │    │
│  │  OS: Ubuntu 22.04 LTS                                   │    │
│  │  CPU: 4 vCPU                                            │    │
│  │  RAM: 8 GB                                              │    │
│  │  Storage: 100 GB SSD                                    │    │
│  │                                                         │    │
│  │  Services:                                              │    │
│  │  ├── Nginx 1.24+                                        │    │
│  │  ├── PHP 8.3-FPM                                        │    │
│  │  ├── MySQL 8.0                                          │    │
│  │  ├── Redis 7.x                                          │    │
│  │  ├── Meilisearch 1.x                                    │    │
│  │  ├── Supervisor (queue workers)                         │    │
│  │  └── Certbot (SSL)                                      │    │
│  │                                                         │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│  ┌── External Services ────────────────────────────────────┐    │
│  │  AWS S3 — File storage                                  │    │
│  │  Mailgun — Transactional email                          │    │
│  │  Twilio — SMS                                           │    │
│  │  Razorpay — Payments                                    │    │
│  │  Sentry — Error tracking                                │    │
│  │  GitHub — Source code + CI/CD                           │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 1.2 Minimum Hardware Requirements

| Component | Minimum | Recommended | Production |
|-----------|---------|-------------|------------|
| CPU | 2 vCPU | 4 vCPU | 8 vCPU |
| RAM | 4 GB | 8 GB | 16 GB |
| Storage | 50 GB SSD | 100 GB SSD | 200 GB SSD |
| Bandwidth | 2 TB/mo | 5 TB/mo | 10 TB/mo |

### 1.3 Software Stack

| Software | Version | Purpose |
|----------|---------|---------|
| Ubuntu | 22.04 LTS | Operating System |
| Nginx | 1.24+ | Web server, reverse proxy |
| PHP | 8.3+ | Application runtime |
| PHP-FPM | 8.3 | Process manager |
| MySQL | 8.0+ | Database |
| Redis | 7.x | Cache, queue, sessions |
| Meilisearch | 1.x | Search engine |
| Node.js | 20 LTS | Frontend build |
| Composer | 2.x | PHP dependency manager |
| Supervisor | 4.x | Process management |
| Certbot | Latest | SSL certificate management |
| Git | Latest | Version control |

---

## 2. Server Setup

### 2.1 Initial Server Setup

```bash
# 1. Update system
sudo apt update && sudo apt upgrade -y

# 2. Create deploy user
sudo adduser deploy
sudo usermod -aG sudo deploy

# 3. SSH hardening
# - Disable root login
# - Disable password auth
# - Use SSH keys only
# - Change default port (optional)

# 4. Firewall setup (UFW)
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable

# 5. Install fail2ban
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
```

### 2.2 PHP Installation & Configuration

```bash
# Install PHP 8.3 with extensions
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install php8.3 php8.3-fpm php8.3-cli \
    php8.3-mysql php8.3-redis php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-gd \
    php8.3-intl php8.3-bcmath php8.3-opcache -y
```

**PHP-FPM Pool Configuration** (`/etc/php/8.3/fpm/pool.d/www.conf`):

| Setting | Value |
|---------|-------|
| `pm` | `dynamic` |
| `pm.max_children` | `50` |
| `pm.start_servers` | `10` |
| `pm.min_spare_servers` | `5` |
| `pm.max_spare_servers` | `20` |
| `pm.max_requests` | `1000` |
| `php_admin_value[memory_limit]` | `256M` |
| `php_admin_value[max_execution_time]` | `30` |
| `php_admin_value[upload_max_filesize]` | `10M` |
| `php_admin_value[post_max_size]` | `12M` |
| `php_admin_value[opcache.enable]` | `1` |
| `php_admin_value[opcache.memory_consumption]` | `256` |

### 2.3 MySQL Configuration

**Key settings** (`/etc/mysql/mysql.conf.d/mysqld.cnf`):

| Setting | Value |
|---------|-------|
| `innodb_buffer_pool_size` | `2G` (25% of RAM) |
| `innodb_log_file_size` | `512M` |
| `max_connections` | `200` |
| `query_cache_size` | `0` (disabled in 8.0) |
| `slow_query_log` | `ON` |
| `long_query_time` | `1` |
| `character-set-server` | `utf8mb4` |
| `collation-server` | `utf8mb4_unicode_ci` |
| `sql_mode` | `TRADITIONAL` |
| `bind-address` | `127.0.0.1` |

### 2.4 Redis Configuration

| Setting | Value |
|---------|-------|
| `maxmemory` | `512mb` |
| `maxmemory-policy` | `allkeys-lru` |
| `appendonly` | `yes` |
| `bind` | `127.0.0.1` |
| `requirepass` | `{strong-password}` |

---

## 3. Nginx Configuration

### 3.1 Main Site Configuration

```nginx
# /etc/nginx/sites-available/fashionstore.conf

server {
    listen 80;
    server_name fashionstore.com www.fashionstore.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name fashionstore.com www.fashionstore.com;

    # SSL
    ssl_certificate /etc/letsencrypt/live/fashionstore.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/fashionstore.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;
    ssl_stapling on;
    ssl_stapling_verify on;

    # Root
    root /var/www/fashionstore/public;
    index index.php index.html;

    # Security Headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript
               text/xml application/xml application/xml+rss text/javascript
               image/svg+xml;

    # Static Assets (Vue SPA build)
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # API Routes
    location /api/ {
        try_files $uri $uri/ /index.php?$query_string;
        add_header Cache-Control "no-store, no-cache, must-revalidate";
    }

    # Webhook Routes
    location /webhooks/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # SPA Fallback (all non-API, non-file routes)
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Deny dotfiles
    location ~ /\. {
        deny all;
    }

    # File size limit
    client_max_body_size 12M;

    # Logs
    access_log /var/log/nginx/fashionstore.access.log;
    error_log /var/log/nginx/fashionstore.error.log;
}
```

---

## 4. SSL & Security

### 4.1 Let's Encrypt SSL

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d fashionstore.com -d www.fashionstore.com

# Auto-renewal (cron)
# Certbot installs auto-renewal timer by default
sudo systemctl status certbot.timer
```

### 4.2 Server Hardening Checklist

| # | Action | Status |
|---|--------|--------|
| 1 | SSH key-only authentication | ☐ |
| 2 | Disable root SSH login | ☐ |
| 3 | UFW firewall configured | ☐ |
| 4 | fail2ban installed and configured | ☐ |
| 5 | Automatic security updates enabled | ☐ |
| 6 | MySQL bound to localhost only | ☐ |
| 7 | Redis bound to localhost with password | ☐ |
| 8 | PHP display_errors OFF in production | ☐ |
| 9 | Laravel APP_DEBUG=false | ☐ |
| 10 | .env file permissions: 600 | ☐ |
| 11 | storage/ and bootstrap/cache writable | ☐ |
| 12 | public/ is the only web-accessible directory | ☐ |

---

## 5. Application Deployment

### 5.1 Directory Structure

```
/var/www/fashionstore/          ← Application root
├── current -> releases/20260625120000   ← Symlink to current release
├── releases/
│   ├── 20260625120000/         ← Latest release
│   ├── 20260624150000/         ← Previous release
│   └── ...                     ← Keep last 5 releases
├── shared/
│   ├── .env                    ← Shared environment file
│   ├── storage/                ← Shared storage directory
│   └── node_modules/           ← Shared node_modules (optional)
└── repo/                       ← Bare git repository
```

### 5.2 Deployment Script

```bash
#!/bin/bash
# deploy.sh — Zero-downtime deployment

set -e

APP_DIR="/var/www/fashionstore"
RELEASE_DIR="$APP_DIR/releases/$(date +%Y%m%d%H%M%S)"
SHARED_DIR="$APP_DIR/shared"

echo "🚀 Starting deployment..."

# 1. Clone/Pull latest code
git clone --depth 1 --branch main git@github.com:org/repo.git $RELEASE_DIR

# 2. Link shared resources
ln -sf $SHARED_DIR/.env $RELEASE_DIR/.env
rm -rf $RELEASE_DIR/storage
ln -sf $SHARED_DIR/storage $RELEASE_DIR/storage

# 3. Install PHP dependencies
cd $RELEASE_DIR
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Install and build frontend
npm ci --production=false
npm run build
rm -rf node_modules  # Clean up after build

# 5. Run database migrations
php artisan migrate --force

# 6. Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Swap symlink (atomic)
ln -sfn $RELEASE_DIR $APP_DIR/current

# 8. Reload services
sudo systemctl reload php8.3-fpm
php artisan queue:restart

# 9. Cleanup old releases (keep last 5)
cd $APP_DIR/releases
ls -dt */ | tail -n +6 | xargs -r rm -rf

echo "✅ Deployment complete!"
```

### 5.3 Rollback Procedure

```bash
#!/bin/bash
# rollback.sh

APP_DIR="/var/www/fashionstore"
PREVIOUS=$(ls -dt $APP_DIR/releases/*/ | sed -n '2p')

if [ -z "$PREVIOUS" ]; then
    echo "❌ No previous release found!"
    exit 1
fi

echo "⏪ Rolling back to: $PREVIOUS"

ln -sfn $PREVIOUS $APP_DIR/current
sudo systemctl reload php8.3-fpm
php artisan queue:restart

echo "✅ Rollback complete!"
```

---

## 6. CI/CD Pipeline

### 6.1 GitHub Actions Workflow

```yaml
# .github/workflows/ci-cd.yml

name: CI/CD Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - run: composer install --no-interaction
      - run: ./vendor/bin/pint --test
      - run: ./vendor/bin/phpstan analyse
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
      - run: npm ci
      - run: npm run lint

  test:
    needs: lint
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports: ['3306:3306']
      redis:
        image: redis:7
        ports: ['6379:6379']
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: xdebug
      - run: composer install --no-interaction
      - run: cp .env.testing .env
      - run: php artisan key:generate
      - run: php artisan migrate
      - run: php artisan test --parallel --coverage-clover coverage.xml
      - name: Frontend tests
        run: |
          npm ci
          npm run test -- --coverage

  build:
    needs: test
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
      - run: npm ci
      - run: npm run build
      - name: Upload build artifacts
        uses: actions/upload-artifact@v4
        with:
          name: build
          path: public/build/

  deploy:
    needs: build
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to Production
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: deploy
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/fashionstore
            bash deploy.sh

      - name: Health Check
        run: |
          sleep 10
          curl -f https://fashionstore.com/api/v1/health || exit 1

      - name: Notify Success
        if: success()
        # Slack/Discord notification

      - name: Notify Failure
        if: failure()
        # Alert team
```

### 6.2 Environment Variables

| Variable | Production Value |
|----------|-----------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://fashionstore.com` |
| `DB_CONNECTION` | `mysql` |
| `CACHE_DRIVER` | `redis` |
| `QUEUE_CONNECTION` | `redis` |
| `SESSION_DRIVER` | `redis` |
| `MAIL_MAILER` | `mailgun` |
| `FILESYSTEM_DISK` | `s3` |
| `SCOUT_DRIVER` | `meilisearch` |
| `LOG_CHANNEL` | `stack` (daily + sentry) |

---

## 7. Backup Strategy

### 7.1 Backup Schedule

| Type | Frequency | Retention | Storage |
|------|-----------|-----------|---------|
| MySQL Full Dump | Daily (3 AM) | 30 days | S3 |
| MySQL Incremental | Hourly | 48 hours | S3 |
| Application Files | Daily (3 AM) | 14 days | S3 |
| Redis RDB | Every 6 hours | 7 days | Local + S3 |
| Uploaded Files (S3) | S3 versioning | 90 days | S3 |

### 7.2 Backup Commands

```bash
# MySQL backup
mysqldump --single-transaction --routines --triggers \
  -u backup_user -p fashionstore | gzip > \
  /backups/mysql/fashionstore_$(date +%Y%m%d_%H%M%S).sql.gz

# Upload to S3
aws s3 cp /backups/mysql/latest.sql.gz \
  s3://fashionstore-backups/mysql/ --sse

# Laravel backup package (alternative)
php artisan backup:run
```

### 7.3 Recovery Targets

| Metric | Target |
|--------|--------|
| **RTO** (Recovery Time Objective) | < 1 hour |
| **RPO** (Recovery Point Objective) | < 1 hour |
| **Backup Verification** | Weekly restore test |

---

## 8. Monitoring

### 8.1 Monitoring Stack

| Layer | Tool | Purpose |
|-------|------|---------|
| Uptime | UptimeRobot / BetterUptime | Site availability (ping every 1 min) |
| Application | Sentry | Error tracking, performance monitoring |
| Queues | Laravel Horizon | Queue health, job metrics |
| Server | Netdata / HTOP | CPU, RAM, disk, network |
| Database | MySQL slow query log | Query performance |
| Logs | Laravel Log + Monolog | Structured application logging |
| SSL | Certbot auto-renew | Certificate validity |

### 8.2 Alerting Rules

| Alert | Condition | Channel | Priority |
|-------|-----------|---------|----------|
| Site Down | No response for 2 min | SMS + Email | P1 Critical |
| High Error Rate | > 5% errors in 5 min | Email + Slack | P2 High |
| High CPU | > 90% for 10 min | Email | P2 High |
| High Memory | > 90% for 5 min | Email | P2 High |
| Disk Space | > 85% | Email | P3 Medium |
| Queue Backlog | > 500 pending jobs | Email | P3 Medium |
| Failed Jobs | > 10 in 1 hour | Email + Slack | P2 High |
| SSL Expiry | < 14 days | Email | P3 Medium |
| Slow Queries | > 5 queries > 2s in 1 min | Slack | P3 Medium |
| Payment Failures | > 10% failure rate | SMS + Email | P1 Critical |

### 8.3 Health Check Endpoint

```
GET /api/v1/health

Response (200):
{
    "status": "healthy",
    "services": {
        "database": "ok",
        "redis": "ok",
        "queue": "ok",
        "storage": "ok",
        "meilisearch": "ok"
    },
    "version": "1.0.0",
    "timestamp": "2026-06-25T10:30:00Z"
}
```

---

## 9. Scaling Strategy

### 9.1 Vertical Scaling (Phase 1)

| Trigger | Action |
|---------|--------|
| CPU > 80% sustained | Upgrade to higher vCPU plan |
| RAM > 80% sustained | Upgrade to higher RAM plan |
| Disk > 80% | Add block storage |
| DB slow queries increasing | Upgrade DB server |

### 9.2 Horizontal Scaling (Phase 2 — When Needed)

```
                    ┌──────────────┐
                    │ Load Balancer │
                    │ (Nginx/HAProxy)│
                    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
        ┌──────────┐ ┌──────────┐ ┌──────────┐
        │  App 1   │ │  App 2   │ │  App 3   │
        │ (PHP-FPM)│ │ (PHP-FPM)│ │ (PHP-FPM)│
        └────┬─────┘ └────┬─────┘ └────┬─────┘
             │             │             │
             └─────────────┼─────────────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
        ┌──────────┐ ┌──────────┐ ┌──────────┐
        │  MySQL   │ │  Redis   │ │Meilisearch│
        │ Primary  │ │ Cluster  │ │          │
        │+Replica  │ │          │ │          │
        └──────────┘ └──────────┘ └──────────┘
```

### 9.3 Scaling Checklist

| Component | Scaling Strategy |
|-----------|-----------------|
| Application | Stateless, no local file storage → horizontal |
| Sessions | Redis (shared across instances) → horizontal |
| Cache | Redis (shared) → horizontal |
| Database | Read replicas → vertical then horizontal |
| Queues | Separate queue worker servers → horizontal |
| Search | Meilisearch clustering → horizontal |
| Files | S3 (inherently scalable) → no action |
| CDN | Cloudflare (inherently scalable) → no action |

---

## 10. Disaster Recovery

### 10.1 Disaster Recovery Plan

| Scenario | RTO | RPO | Procedure |
|----------|-----|-----|-----------|
| Application bug | 5 min | 0 | Rollback to previous release |
| Database corruption | 1 hour | 1 hour | Restore from latest backup |
| Server failure | 2 hours | 1 hour | Provision new server, restore from backup |
| DDoS attack | 5 min | 0 | Cloudflare Under Attack mode |
| Data center outage | 4 hours | 1 hour | Deploy to alternate region |
| S3 outage | N/A | N/A | S3 has 99.999999999% durability |

### 10.2 Recovery Procedures

```
DATABASE RECOVERY:
1. Stop application (maintenance mode)
2. Download latest backup from S3
3. Restore: mysql fashionstore < backup.sql
4. Run any pending migrations
5. Verify data integrity
6. Start application

SERVER RECOVERY:
1. Provision new server (same spec)
2. Run server setup script
3. Clone application code
4. Restore database from backup
5. Restore shared files from backup
6. Update DNS to new server
7. Verify all services operational
8. Monitor for 24 hours
```

### 10.3 Maintenance Mode

```bash
# Enable maintenance mode
php artisan down --secret="bypass-token-123"

# Access site during maintenance (via URL)
https://fashionstore.com/bypass-token-123

# Disable maintenance mode
php artisan up
```

---

### Deployment Checklist (First Deploy)

| # | Step | Status |
|---|------|--------|
| 1 | Server provisioned and hardened | ☐ |
| 2 | PHP, MySQL, Redis, Meilisearch installed | ☐ |
| 3 | Nginx configured with SSL | ☐ |
| 4 | DNS configured (Cloudflare) | ☐ |
| 5 | `.env` production configured | ☐ |
| 6 | Database migrated and seeded | ☐ |
| 7 | Queue workers running (Supervisor) | ☐ |
| 8 | Cron jobs configured | ☐ |
| 9 | Backups configured and tested | ☐ |
| 10 | Monitoring and alerts configured | ☐ |
| 11 | SSL certificate verified | ☐ |
| 12 | Health check endpoint responding | ☐ |
| 13 | Payment gateway test transaction | ☐ |
| 14 | Email sending verified | ☐ |
| 15 | SMS sending verified | ☐ |
| 16 | Search index populated | ☐ |
| 17 | Performance benchmarked | ☐ |
| 18 | Security audit completed | ☐ |
| 19 | Backup restore tested | ☐ |
| 20 | Team briefed on deployment process | ☐ |

---

*End of Deployment Plan Document — Phase 12*
