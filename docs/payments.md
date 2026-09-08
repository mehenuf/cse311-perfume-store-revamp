# Online payments: setup and how it works

Cash on Delivery still works exactly as before. This adds three more
payment options at checkout, all following the same shape: the customer
is redirected to the provider's own hosted checkout page, then redirected
back, while the provider's **webhook** (not the browser redirect) is what
actually confirms the order was paid.

| Checkout option | Provider | Also covers |
|---|---|---|
| Card, Google Pay, Apple Pay | Stripe Checkout | Cards, wallets |
| bKash, Rocket, Nagad, Bangla QR | SSLCommerz | Local bank cards too |
| Crypto | Coinbase Commerce | Bitcoin and other coins |

bKash and Rocket are not integrated directly -- SSLCommerz's own hosted
checkout page already offers both (plus Nagad and Bangla QR) as payment
options, so one merchant integration covers all four instead of separate
bKash/Rocket merchant agreements.

## Before this works at all: hosting

**This code will not complete a real payment while the site is hosted on
InfinityFree.** InfinityFree blocks outbound HTTPS connections from PHP in
many cases, and every one of these gateways requires the server itself to
call out to the provider's API (to create a checkout session) and to
receive an incoming webhook call back. Both directions need a host with
normal outbound/inbound HTTPS -- a VPS, Render, Railway, Koyeb, etc. Until
then, the storefront UI, validation, and database side of this all work
correctly and are covered by the test suite, but a customer will not be
able to actually complete an online payment in production.

## 1. Run the migration

`database/mysql/12_migration_payment_gateways.sql` (or the `postgres/`
version) -- see `database/README.md`'s migration table.

## 2. Get sandbox credentials

- **SSLCommerz**: register a free sandbox store at
  https://developer.sslcommerz.com/registration/ -- gives you a
  `store_id` and `store_passwd` immediately, no approval wait.
- **Stripe**: https://dashboard.stripe.com/register, then grab your
  **test-mode** secret key from https://dashboard.stripe.com/test/apikeys.
  Enable Apple Pay / Google Pay for the account under Settings → Payment
  methods (Stripe Checkout picks up whatever is enabled there
  automatically -- no code change needed).
- **Coinbase Commerce**: https://commerce.coinbase.com/signup, then an
  API key from Settings → Security.

## 3. Configure `config/env.php`

Copy the new keys from `config/env.example.php`'s SSLCommerz/Stripe/
Coinbase Commerce sections, and set `APP_BASE_URL` to your real public
URL (used to build the success/cancel/webhook URLs handed to each
provider -- it cannot be a relative path since these redirects happen on
the provider's own domain).

## 4. Point each provider's webhook at this site

| Provider | Webhook URL to register | Where |
|---|---|---|
| SSLCommerz | `https://yourdomain.com/webhooks/sslcommerz-ipn.php` | Store panel → IPN settings (or passed automatically -- SSLCommerz reads it from the session request itself, but double-check the store panel) |
| Stripe | `https://yourdomain.com/webhooks/stripe.php` | Dashboard → Developers → Webhooks → Add endpoint. Copy the **signing secret** it gives you into `STRIPE_WEBHOOK_SECRET`. |
| Coinbase Commerce | `https://yourdomain.com/webhooks/coinbase.php` | Settings → Webhook subscriptions. Copy the **shared secret** into `COINBASE_COMMERCE_WEBHOOK_SECRET`. |

## How a payment is confirmed (and why)

The browser redirect back to `payment-return.php` is **never** trusted to
mean "paid" -- a customer could type `?result=success` into the address
bar for an order they never paid for. The only thing that marks an order
`paid` is the provider's own server-to-server webhook, independently
signed/validated:

- SSLCommerz: the IPN's own POST body is not trusted either -- the
  webhook calls SSLCommerz's **Order Validation API** back and only acts
  on what that authoritative response says.
- Stripe: the webhook verifies the `Stripe-Signature` header (HMAC-SHA256
  over the raw body, with a timestamp check to block replay).
- Coinbase Commerce: the webhook verifies `X-CC-Webhook-Signature`
  (HMAC-SHA256 over the raw body).

Every webhook also re-checks the amount/currency against the order's own
`total_price` before marking it paid, and is idempotent (`payment_events`
table) so a provider retrying the same notification never double-charges
stock or sends a second confirmation email.

## Known limitations (deliberately out of scope)

- **No refund flow.** Refunds/chargebacks are handled directly in each
  provider's own dashboard for now -- issuing one automatically is a
  higher-risk action this change does not take on.
- **No abandoned-order cleanup.** A customer who starts an online payment
  and never finishes it leaves an order behind with `payment_status =
  'pending'` forever (harmless: its stock was never reserved). There is
  no cron on a typical free host to expire these automatically.
- **Coinbase Commerce's `charge:resolved`/`charge:delayed` events** (an
  under/overpaid charge Coinbase support resolves manually) are
  acknowledged but not acted on -- check the Coinbase dashboard directly
  for those.
