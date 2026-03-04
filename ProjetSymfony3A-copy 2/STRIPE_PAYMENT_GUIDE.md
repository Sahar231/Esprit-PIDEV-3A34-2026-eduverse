# Stripe Checkout Integration - Complete Guide

## Overview

This guide explains how the Stripe Checkout integration works for the EduVerse wallet credit system. Users can purchase credits (1 credit = 1 euro) using their credit card.

## Architecture

### Payment Flow Diagram

```
User Interface
    ↓
[Student clicks "Pay with Stripe" button]
    ↓
Form POST to `/student/wallet/checkout`
    ↓
WalletController::createCheckout()
    ↓
StripeService::createCheckoutSession()
    ├─ Creates Stripe Checkout Session
    ├─ Stores metadata (user_id, credits)
    └─ Creates pending WalletTransaction
    ↓
Redirect to Stripe Hosted Checkout Page
    ↓
[User enters payment details on Stripe]
    ↓
Payment Processing (on Stripe servers)
    ↓
┌─────────────────────────────────────┐
│  TWO OUTCOMES:                      │
├─────────────────────────────────────┤
│ 1. SUCCESS:                         │
│    Redirect to success_url          │
│    (Wallet NOT updated yet)         │
│                                     │
│ 2. CANCELLED:                       │
│    Redirect to cancel_url           │
│    (No charge, no update)           │
└─────────────────────────────────────┘
    ↓
Stripe sends webhook: checkout.session.completed
    ↓
POST to `/stripe/webhook`
    ↓
StripeWebhookController::handleWebhook()
    ↓
StripeService::handleWebhookEvent()
    ├─ Verify Stripe signature
    ├─ Find WalletTransaction by sessionId
    └─ Update wallet balance + mark as "paid"
    ↓
✅ Credits added to user wallet
```

## Setup Instructions

### 1. Install Stripe Package

```bash
composer require stripe/stripe-php
```

✅ Already done in this implementation.

### 2. Create Stripe Account

1. Go to https://stripe.com
2. Sign up for an account
3. Navigate to Dashboard → API keys
4. Note: Keep **Secret Key** private, use **Publishable Key** in frontend

### 3. Configure Environment Variables

Edit `.env` file:

```dotenv
###> Stripe Payment Gateway ###
STRIPE_SECRET_KEY=sk_test_your_secret_key_here
STRIPE_PUBLIC_KEY=pk_test_your_public_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
###< Stripe Payment Gateway ###
```

**Finding Your Keys:**
- **Secret Key**: Dashboard → Developers → API Keys → Secret Key (starts with `sk_live_` or `sk_test_`)
- **Public Key**: Dashboard → Developers → API Keys → Publishable Key (starts with `pk_live_` or `pk_test_`)
- **Webhook Secret**: See "Setup Webhooks" section below

### 4. Setup Webhooks

**Critical for payment processing!**

1. Go to Stripe Dashboard → Developers → Webhooks
2. Click "Add endpoint"
3. Enter your endpoint URL:
   ```
   https://yourdomain.com/stripe/webhook
   ```
4. Select **Events to send**:
   - `checkout.session.completed` (REQUIRED)
5. Copy the **Signing Secret** to `.env` as `STRIPE_WEBHOOK_SECRET`
6. Click "Add endpoint"

**Local Testing with Stripe CLI:**

```bash
# Install Stripe CLI from https://stripe.com/docs/stripe-cli
stripe login
stripe listen --forward-to localhost:8000/stripe/webhook
```

This will output your webhook secret - use this in `.env` for local testing.

## Database Schema

### WalletTransaction Table

```sql
CREATE TABLE wallet_transaction (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    credits INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    stripe_session_id VARCHAR(255) UNIQUE,
    created_at DATETIME NOT NULL,
    completed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES user(id),
    INDEX (user_id)
);
```

**Status values:**
- `pending`: Payment not yet confirmed
- `paid`: Payment successful, credits added
- `failed`: Payment failed or rejected

## Code Components

### 1. Entity: WalletTransaction

**Location**: `src/Entity/WalletTransaction.php`

**Key Properties:**
```php
- id: int (primary key)
- user: User (ManyToOne) - who purchased the credits
- credits: int - number of credits (1 credit = €1)
- amount: decimal - total amount (always = credits)
- status: string - 'pending', 'paid', 'failed'
- stripeSessionId: string - unique Stripe session identifier
- createdAt: DateTimeImmutable
- completedAt: DateTimeImmutable (nullable) - when payment was confirmed
```

**Helper Methods:**
```php
$transaction->isPending()    // status === 'pending'
$transaction->isPaid()       // status === 'paid'
$transaction->isFailed()     // status === 'failed'
```

### 2. Service: StripeService

**Location**: `src/Service/StripeService.php`

**Key Methods:**

#### createCheckoutSession()
```php
public function createCheckoutSession(
    User $user,
    int $credits,
    string $successUrl,
    string $cancelUrl
): array
```

**What it does:**
1. Creates a Stripe Checkout Session
2. Configures payment for `$credits` euros
3. Creates a pending WalletTransaction
4. Returns session object and transaction

**Returns:** `['session' => Session, 'transaction' => WalletTransaction]`

**Example:**
```php
$result = $stripeService->createCheckoutSession(
    user: $user,
    credits: 50,
    successUrl: 'https://app.com/student/wallet/success',
    cancelUrl: 'https://app.com/student/wallet'
);

// Redirect to Stripe
return $this->redirect($result['session']->url);
```

#### handleWebhookEvent()
```php
public function handleWebhookEvent(
    string $payload,
    string $signature
): void
```

**What it does:**
1. Verifies Stripe signature (security check)
2. Parses the webhook event
3. Routes to appropriate handler
4. Currently handles: `checkout.session.completed`

**Important:** Always verify the signature to prevent unauthorized updates!

### 3. Controller: WalletController

**Location**: `src/Controller/Student/WalletController.php`

**Routes:**

| Method | Path | Name | Purpose |
|--------|------|------|---------|
| GET | `/student/wallet` | `student_wallet_index` | Display wallet + credits form |
| POST | `/student/wallet/checkout` | `student_wallet_checkout` | Create Stripe session |
| GET | `/student/wallet/success` | `student_wallet_success` | Success page (after Stripe redirect) |
| GET | `/student/wallet/cancel` | `student_wallet_cancel` | Cancellation page |

**Key Method: createCheckout()**
```php
#[Route('/checkout', name: 'student_wallet_checkout', methods: ['POST'])]
public function createCheckout(Request $request): Response
```

**Validates:**
- Credits between 1-1000
- User is authenticated

**Process:**
1. Validates credits amount
2. Calls StripeService to create session
3. Redirects to Stripe Checkout Page

### 4. Webhook Controller: StripeWebhookController

**Location**: `src/Controller/StripeWebhookController.php`

**Route:**
```php
POST /stripe/webhook
```

**Responsibility:**
1. Receives webhook from Stripe
2. Verifies signature
3. Delegates to StripeService for processing
4. Returns HTTP 200 on success, 400 on error

**IMPORTANT:** Must return raw request body (not JSON-decoded) for signature verification!

## Frontend Example

The checkout form is in the wallet page:

```html
<form method="POST" action="{{ path('student_wallet_checkout') }}">
    <input type="number" name="credits" min="1" max="1000" value="10" required>
    <button type="submit">Pay with Stripe</button>
</form>
```

Or include the provided example:
```twig
{% include 'student/wallet/checkout_form.html.twig' %}
```

## Security Best Practices

### ✅ Implemented

1. **Signature Verification**
   - All webhooks verified using `STRIPE_WEBHOOK_SECRET`
   - Prevents fraudulent webhook requests

2. **Secret Key in Environment**
   - Never hardcode API keys
   - Use `.env` file (git-ignored)
   - Use `.env.local` for production credentials

3. **Webhook Validation**
   - Stripe signature checked before processing
   - Returns 400 on failed verification

4. **Transaction Status Check**
   - Payment only processed once per transaction
   - Prevents duplicate crediting

5. **User Authorization**
   - Routes protected with `#[IsGranted('ROLE_STUDENT')]`
   - Wallets belong only to authenticated users

6. **No Wallet Update on Success Page**
   - Success page doesn't modify wallet (only shows message)
   - Only webhook can modify wallet (idempotent)
   - Prevents issues if user doesn't receive success redirect

### 🔧 Configuration Checklist

**Before Going Live:**

- [ ] Use production Stripe keys (not test keys)
- [ ] Update `.env` with production `STRIPE_SECRET_KEY`, `STRIPE_PUBLIC_KEY`
- [ ] Create webhook endpoint in Stripe Dashboard for production domain
- [ ] Test webhook delivery to ensure endpoint is reachable
- [ ] Enable email notifications for failed payments
- [ ] Set up monitoring/alerts for webhook failures
- [ ] Test a real payment flow end-to-end
- [ ] Verify wallet balance updates after payment
- [ ] Check transaction status in database after payment

## Testing

### Test Mode (Development)

Use Stripe Test Credit Cards:

```
Successful Payment:
  Card: 4242 4242 4242 4242
  Exp: Any future date (e.g., 12/25)
  CVC: Any 3 digits (e.g., 123)

Failed Payment:
  Card: 4000 0000 0000 0002
  Exp: Any future date
  CVC: Any 3 digits
```

### Local Webhook Testing

```bash
# Terminal 1: Start your app
symfony serve

# Terminal 2: Listen for Stripe webhooks
stripe listen --forward-to localhost:8000/stripe/webhook

# Copy the webhook secret and add to .env
STRIPE_WEBHOOK_SECRET=whsec_xxx...

# Terminal 3: Trigger a test event
stripe trigger checkout.session.completed
```

### End-to-End Test

1. Go to `/student/wallet`
2. Enter credit amount (e.g., 5)
3. Click "Pay with Stripe"
4. Use test card: `4242 4242 4242 4242`
5. Check wallet balance increases
6. Verify `wallet_transaction` record has status `paid`

## Troubleshooting

### Common Issues

**Issue**: Webhook not being received
```
Solution:
  1. Verify STRIPE_WEBHOOK_SECRET in .env
  2. Check firewall allows incoming POST to /stripe/webhook
  3. Test with Stripe CLI: stripe trigger checkout.session.completed
  4. Check server logs for errors
```

**Issue**: Signature verification fails
```
Solution:
  1. Verify STRIPE_WEBHOOK_SECRET is correct
  2. Ensure request body is NOT JSON-decoded (raw string needed)
  3. Check Request::getContent() is used, not Request::toArray()
```

**Issue**: Credits not added after payment
```
Solution:
  1. Verify transaction status is "paid" in database
  2. Check Wallet::addBalance() was called
  3. Review logs for webhook processing errors
  4. Verify user_id in transaction matches wallet user_id
```

**Issue**: Stripe API errors (401 Unauthorized)
```
Solution:
  1. Verify STRIPE_SECRET_KEY is correct
  2. Use sk_test_ for testing, sk_live_ for production
  3. Check key hasn't been revoked in Stripe Dashboard
  4. Verify key permissions allow creating sessions
```

## Database Queries

### Find Transaction by Session ID
```php
$transaction = $transactionRepository->findByStripeSessionId($sessionId);
```

### Find User's Pending Transactions
```php
$pending = $transactionRepository->findPendingByUser($userId);
```

### Find User's Paid Transactions
```php
$paid = $transactionRepository->findPaidByUser($userId);
```

## Monitoring & Logging

### Add Logging to StripeService

```php
use Psr\Log\LoggerInterface;

public function __construct(
    ...
    private LoggerInterface $logger
) {}

// In handleCheckoutSessionCompleted():
$this->logger->info('Processing payment', [
    'user_id' => $transaction->getUser()->getId(),
    'credits' => $transaction->getCredits(),
    'stripe_session' => $session->id
]);
```

### Check Webhook Deliveries in Stripe Dashboard

Stripe Dashboard → Developers → Webhooks → Click endpoint → View logs

## Environment Variables Summary

| Variable | Purpose | Example |
|----------|---------|---------|
| `STRIPE_SECRET_KEY` | API auth key (SECRET!) | `sk_test_abc123...` |
| `STRIPE_PUBLIC_KEY` | Frontend publishable key | `pk_test_xyz789...` |
| `STRIPE_WEBHOOK_SECRET` | Webhook signature key | `whsec_def456...` |

## API Reference

### Stripe Session Object

```php
$session->id              // Unique session ID
$session->url             // URL to redirect user to
$session->payment_status  // 'unpaid', 'paid', 'no_payment_required'
$session->metadata        // Custom data stored with session
$session->customer_email  // Email used for payment
```

### WalletTransaction Status

- `pending`: Created but not yet paid
- `paid`: Successfully paid, credits added
- `failed`: Payment failed or rejected

## References

- Stripe Documentation: https://stripe.com/docs
- Stripe Webhook Events: https://stripe.com/docs/webhooks/events
- Stripe Checkout: https://stripe.com/docs/payments/checkout
- Stripe API Reference: https://stripe.com/docs/api

## Support

For issues or questions:
1. Check logs: `tail -f var/log/dev.log`
2. Review Stripe Dashboard logs
3. Use Stripe CLI for webhook testing
4. Enable debug mode: `APP_DEBUG=true` in `.env`
