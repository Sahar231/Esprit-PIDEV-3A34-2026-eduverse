# Stripe Integration - Quick Reference

## Setup (5 Minutes)

### 1. Install Package
```bash
composer require stripe/stripe-php
```

### 2. Get Stripe Keys
- Visit: https://dashboard.stripe.com/apikeys
- Copy `sk_test_...` (Secret Key)
- Copy `pk_test_...` (Public Key)

### 3. Update .env
```dotenv
STRIPE_SECRET_KEY=sk_test_your_key_here
STRIPE_PUBLIC_KEY=pk_test_your_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_secret_here
```

### 4. Setup Webhook
1. Go to https://dashboard.stripe.com/webhooks
2. Click "Add endpoint"
3. Enter: `https://yourdomain.com/stripe/webhook`
4. Select: `checkout.session.completed`
5. Copy webhook secret to `.env`

## Payment Flow

```
1. User clicks "Pay with Stripe"
   ↓
2. POST /student/wallet/checkout
   - StripeService creates session
   - WalletTransaction created (status: pending)
   - Redirect to Stripe Checkout
   ↓
3. User pays on Stripe.com
   ↓
4. Stripe sends webhook
   - Signature verified
   - Transaction status → "paid"
   - Wallet balance increased
   ↓
5. ✅ Credits in wallet
```

## Code Examples

### Create Checkout Session
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

### Handle Webhook (Already Implemented)
```php
// POST /stripe/webhook
public function handleWebhook(Request $request): Response
{
    $payload = $request->getContent();
    $signature = $request->headers->get('stripe-signature');
    
    try {
        $this->stripeService->handleWebhookEvent($payload, $signature);
        return new Response('OK', Response::HTTP_OK);
    } catch (\Exception $e) {
        return new Response('Error', Response::HTTP_BAD_REQUEST);
    }
}
```

### Find Transaction
```php
// By session ID
$transaction = $transactionRepository->findByStripeSessionId($sessionId);

// All pending transactions for user
$pending = $transactionRepository->findPendingByUser($userId);

// All paid transactions for user
$paid = $transactionRepository->findPaidByUser($userId);
```

## Files Created

| File | Purpose |
|------|---------|
| `src/Entity/WalletTransaction.php` | Track credit purchases |
| `src/Repository/WalletTransactionRepository.php` | Query transactions |
| `src/Service/StripeService.php` | Stripe API integration |
| `src/Controller/StripeWebhookController.php` | Handle Stripe webhooks |
| `src/Controller/Student/WalletController.php` | Checkout UI & flow |
| `templates/student/wallet/index.html.twig` | Checkout form |
| `templates/student/wallet/success.html.twig` | Success page |
| `config/services.yaml` | DI configuration |
| `.env` | Environment variables |

## Test Cards (Stripe Test Mode)

```
✅ Success:     4242 4242 4242 4242
❌ Decline:     4000 0000 0000 0002
💳 Exp: Any future date (e.g., 12/25)
🔐 CVC: Any 3 digits (e.g., 123)
```

## Testing Locally

```bash
# Terminal 1: Run app
symfony serve

# Terminal 2: Listen for webhooks
stripe listen --forward-to localhost:8000/stripe/webhook
```

Then:
1. Go to `http://localhost:8000/student/wallet`
2. Enter credit amount
3. Click "Pay with Stripe"
4. Use test card `4242 4242 4242 4242`
5. Check wallet balance increased ✅

## Webhook Testing

```bash
# Trigger a test event
stripe trigger checkout.session.completed
```

## Key Security Points

✅ Stripe signature verified on every webhook
✅ Payment only processed once per transaction
✅ Wallet balance only updated by webhook (not by success redirect)
✅ API keys stored in environment variables
✅ User authentication required for checkout
✅ Prevents double-crediting via status check

## Environment Variables

```dotenv
# Secret key (keep private!)
STRIPE_SECRET_KEY=sk_test_...

# Public key (safe for frontend)
STRIPE_PUBLIC_KEY=pk_test_...

# Webhook signature (verify requests)
STRIPE_WEBHOOK_SECRET=whsec_...
```

## Troubleshooting

**Webhook not working?**
- Check STRIPE_WEBHOOK_SECRET in .env
- Use `stripe listen` to test locally
- Verify endpoint is accessible (firewall, routing)

**Signature verification fails?**
- Use raw request body (not JSON-decoded)
- Verify secret key is correct

**Credits not added?**
- Check transaction status in database
- Review webhook logs in Stripe Dashboard
- Verify Wallet::addBalance() was called

**Payment fails?**
- Use test card instead of real card
- Check STRIPE_SECRET_KEY is correct
- Verify account permissions in Stripe Dashboard

## Next Steps

1. ✅ Stripe installed
2. ✅ Entities created
3. ✅ Service implemented
4. ✅ Controllers built
5. ✅ Templates added
6. 📋 Get Stripe API keys
7. 📋 Configure webhook
8. 📋 Update .env variables
9. 📋 Test end-to-end
10. 📋 Go live with production keys

## Database Schema

```sql
-- Automatically created by migration
CREATE TABLE wallet_transaction (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    credits INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    stripe_session_id VARCHAR(255) UNIQUE,
    created_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

## API Endpoints

| Endpoint | Method | Protected | Purpose |
|----------|--------|-----------|---------|
| `/student/wallet` | GET | ✅ ROLE_STUDENT | Show wallet |
| `/student/wallet/checkout` | POST | ✅ ROLE_STUDENT | Create session |
| `/student/wallet/success` | GET | ✅ ROLE_STUDENT | Success page |
| `/student/wallet/cancel` | GET | ✅ ROLE_STUDENT | Cancel page |
| `/stripe/webhook` | POST | ❌ Public | Receive webhooks |

## Important Notes

⚠️ **Do NOT update wallet on success redirect** - Only webhooks are idempotent and guaranteed to run once.

⚠️ **Always verify webhook signature** - This prevents unauthorized requests.

⚠️ **Use test keys for development** - Switch to production keys only when ready.

⚠️ **Keep secrets in .env** - Never commit `.env` to git.

## Real-World Payment Flow

```
Customer                  Your App              Stripe
   │                         │                    │
   ├─ Click Pay ────────────>│                    │
   │                         │                    │
   │                         ├─ Create Session ──>│
   │                         │                    │
   │                         │ <── Session URL ───│
   │                         │                    │
   │ <── Redirect to Stripe ─┤                    │
   │                         │                    │
   ├────── Pay on Stripe ───────────────────────>│
   │                         │                    │
   │                         │                    │
   │ <–– Redirect ────────────────────────────────┤
   │                         │                    │
   │ <── Back to App ────────┤                    │
   │                         │                    │
   │ (See "Processing..." msg)                    │
   │                         │                    │
   │                         │   Webhook ────────>│
   │                         │←─ Payment Received│
   │                         │                    │
   │ <──── Credits Added ────┤                    │
   │ ✅ Payment Complete     │                    │
   ↓                         ↓                    ↓
```
