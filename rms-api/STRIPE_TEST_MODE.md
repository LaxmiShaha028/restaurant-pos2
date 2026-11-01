# Stripe test mode setup

The subscription checkout uses Stripe-hosted Checkout. It stores no card data in this application and only activates a subscription after Stripe confirms payment through its signed webhook or the signed-in Super Admin return flow.

1. Create or use a Stripe account, and copy its **test mode** publishable key (`pk_test_...`) and secret key (`sk_test_...`). Test mode does not move real money.
2. Add these values to `rms-api/.env`:

   ```dotenv
   FRONTEND_URL=http://localhost:5173
   STRIPE_KEY=pk_test_your_key
   STRIPE_SECRET=sk_test_your_key
   STRIPE_WEBHOOK_SECRET=whsec_value_from_stripe_listen
   ```

3. From `rms-api`, run `php artisan db:seed --class="Database\\Seeders\\Central\\StripePaymentMethodSeeder"` to enable Stripe without changing other payment methods, then clear cached configuration with `php artisan config:clear`.
4. Install/run the Stripe CLI and forward events to `http://localhost:8000/api/stripe/webhook` using `stripe listen --forward-to localhost:8000/api/stripe/webhook`. Copy the CLI's `whsec_...` value to `STRIPE_WEBHOOK_SECRET` and restart Laravel after changing `.env`.
5. Choose **Stripe (Test Mode)** in the Super Admin restaurant payment, trial upgrade, or renewal flow. Use Stripe's standard test card `4242 4242 4242 4242`, any future expiry, and any CVC/ZIP.

For production, configure live credentials and a public HTTPS webhook endpoint in Stripe. Never commit secret keys. Stripe availability and settlement depend on the merchant account's country; test mode itself does not enable live processing.
