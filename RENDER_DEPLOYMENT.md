# Render free demo deployment

The Render Blueprint creates one Laravel web service from the `Development2`
branch. It serves the Vue frontend and the Laravel API from the same URL. The
API container runs a small local MariaDB server and uses `single_database`
tenancy mode. All tenant tables use a restaurant prefix (for example,
`t12_users`) inside the same database; provisioning does not create separate
tenant databases.

## Deploy

1. In Render, create a Blueprint from this repository and select the
   `Development2` branch.
2. Render builds the frontend into the API container. The API entrypoint
   initializes MariaDB, runs central migrations, seeds plans/payment methods,
   and creates a Super Admin. Open the API service URL for the website; API
   requests use the same origin under `/api`.
3. Find the generated `SUPER_ADMIN_PASSWORD` in the API service's Environment
   page. Sign in with username `admin` and that password.

The Blueprint generates the application key, database password, and demo admin
password; no database provider account is required.

## Demo data behavior

The database files are kept under `/tmp/mysql-data`, which is ephemeral on
Render Free. Restaurant, order, account, and other database data can disappear
when Render restarts, redeploys, or spins the free service down. Uploaded files
are also temporary. Use this only for a disposable demo; it is not suitable for
data that must persist.

Render Free web services sleep after inactivity. The first request after sleep
may take time while the service starts and initializes its database. Stripe is
left unconfigured by default. To demo Stripe checkout, add test-mode
`STRIPE_KEY` and `STRIPE_SECRET` in the API service environment. Add
`STRIPE_WEBHOOK_SECRET` when using Stripe webhooks. Keep these secrets in
Render's environment settings; do not commit them to this repository.
