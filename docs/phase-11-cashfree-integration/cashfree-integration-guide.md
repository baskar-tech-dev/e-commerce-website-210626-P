# Cashfree Payments Integration Guide

## 1. Overview

This document outlines the Cashfree Payments integration for Maya Sree South Indian Fashion. Cashfree Payments serves as the sole online payment gateway for credit/debit cards, NetBanking, Instant UPI (Google Pay, PhonePe, Paytm, BHIM), and digital wallets.

---

## 2. Architecture & Transaction Flow

The integration implements server-side session generation, Cashfree Web JS SDK v3 client interaction, and strict server-side verification:

```text
Customer
   ↓ (1) Submits Checkout
Local Order Created in Database (Status: Pending)
   ↓ (2) Request Payment Session (POST /api/payment/cashfree/create)
Backend calls Cashfree PG API (POST https://sandbox.cashfree.com/pg/orders)
   ↓ (3) Returns payment_session_id
Cashfree Web JS SDK v3 opens Modal Checkout
   ↓ (4) Customer completes payment
Modal returns to Frontend callback
   ↓ (5) Frontend calls Backend Verification (POST /api/payment/cashfree/verify)
Backend verifies payment with Cashfree API (GET /orders/{order_id}/payments)
   ↓ (6) Validates Status == SUCCESS & Amount matches order total
Order updated to 'paid' & 'processing', reserved inventory committed
   ↓ (7) Success Confirmation Page rendered
```

---

## 3. Environment Variables Configuration

Add the following environment variables to your `.env` file:

```env
# Cashfree Payments Configuration
CASHFREE_APP_ID=your_cashfree_app_id
CASHFREE_SECRET_KEY=your_cashfree_secret_key
CASHFREE_ENVIRONMENT=sandbox
CASHFREE_API_VERSION=2023-08-01
```

### Environment Settings:
- **Sandbox (Testing)**: `CASHFREE_ENVIRONMENT=sandbox`
  - Base URL: `https://sandbox.cashfree.com/pg`
  - JS SDK Mode: `sandbox`
- **Production (Live)**: `CASHFREE_ENVIRONMENT=production`
  - Base URL: `https://api.cashfree.com/pg`
  - JS SDK Mode: `production`

---

## 4. API Endpoints

### 1. Create Payment Order / Session
- **Route**: `POST /api/payment/cashfree/create`
- **Auth**: Sanctum or Guest
- **Payload**:
  ```json
  {
    "order_id": 123
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "data": {
      "payment_session_id": "session_xxx...",
      "cf_order_id": "12345678",
      "order_id": "VIBE-20260820-0001",
      "order_number": "VIBE-20260820-0001",
      "amount": 1499.00,
      "currency": "INR",
      "environment": "sandbox",
      "customer": {
        "name": "John Doe",
        "email": "customer@example.com",
        "phone": "9876543210"
      }
    }
  }
  ```

### 2. Verify Payment
- **Route**: `POST /api/payment/cashfree/verify`
- **Auth**: Sanctum or Guest
- **Payload**:
  ```json
  {
    "order_id": 123,
    "cashfree_order_id": "VIBE-20260820-0001"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Payment verified and captured successfully."
  }
  ```

### 3. Cancel / Dismiss Payment
- **Route**: `POST /api/payment/cashfree/cancel`
- **Payload**:
  ```json
  {
    "order_id": 123,
    "reason": "Payment modal dismissed by user"
  }
  ```

### 4. Verified Order Payment Status
- **Route**: `GET /api/payment/cashfree/status/{order_id}`

### 5. Webhook Endpoint
- **Route**: `POST /api/payment/cashfree/webhook`
- **Headers**:
  - `x-webhook-timestamp`
  - `x-webhook-signature`

---

## 5. Webhook Configuration in Cashfree Merchant Dashboard

1. Log in to the [Cashfree Merchant Dashboard](https://merchant.cashfree.com/).
2. Navigate to **Payment Gateway > Developers > Webhooks**.
3. Click **Add Webhook Endpoint**.
4. Set the Webhook URL to:
   ```text
   https://yourdomain.com/api/payment/cashfree/webhook
   ```
5. Select the following events:
   - `PAYMENT_SUCCESS_WEBHOOK`
   - `PAYMENT_FAILED_WEBHOOK`
   - `PAYMENT_USER_DROPPED_WEBHOOK`
   - `REFUND_STATUS_WEBHOOK`
6. Save and test the webhook connection.

---

## 6. Webhook Signature Verification Algorithm

Cashfree signatures are verified using HMAC-SHA256:

```php
$rawBody = $request->getContent();
$timestamp = $request->header('x-webhook-timestamp');
$signature = $request->header('x-webhook-signature');

$dataToSign = $timestamp . $rawBody;
$computedSignature = base64_encode(hash_hmac('sha256', $dataToSign, config('services.cashfree.secret_key'), true));

if (hash_equals($computedSignature, $signature)) {
    // Signature is valid
}
```

---

## 7. Switching from Sandbox to Production

Before going live:
1. Complete KYC verification on the Cashfree Merchant Portal.
2. Generate Production API Keys from **Developers > API Keys**.
3. Update production server `.env`:
   ```env
   CASHFREE_APP_ID=prod_app_id_here
   CASHFREE_SECRET_KEY=prod_secret_key_here
   CASHFREE_ENVIRONMENT=production
   ```
4. Run `php artisan config:clear` and `php artisan config:cache`.
5. Verify SSL/HTTPS is active.

---

## 8. Troubleshooting & FAQ

- **Error: "Cashfree payment gateway credentials are not configured"**:
  Ensure `CASHFREE_APP_ID` and `CASHFREE_SECRET_KEY` are populated in `.env`.
- **Payment Verification Failed / Amount Mismatch**:
  Check if coupon discounts or shipping charges were modified after session creation. The backend validates exact amount matching.
- **Stock Reservation Released**:
  If a customer abandons the modal or fails the bank OTP step, stock reservations are safely returned to inventory after cancellation.
