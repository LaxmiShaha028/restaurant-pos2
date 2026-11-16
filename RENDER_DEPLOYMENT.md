# Render + TiDB Cloud Starter

This deployment uses one MySQL-compatible TiDB Cloud Starter database for
central application data and all restaurant data. Tenant tables are isolated
with a restaurant-specific prefix (for example, `t12_users`). This is needed
because TiDB Cloud Starter does not allow the app to create a separate database
for every restaurant.

## Free-tier limits

The MySQL-compatible Starter free quota currently includes 5 GiB of row
storage, 5 GiB of columnar storage, and 50 million Request Units (RUs) per
month. Keep the monthly spending limit at 0 to avoid paid overages. At quota,
TiDB may throttle work or reject new connections until the monthly reset. The
free quota and policies can change; see the [TiDB Cloud Starter FAQ](https://docs.pingcap.com/tidbcloud/serverless-faqs/).
Render's free web service may sleep when idle. Render and TiDB free tiers do
not include a production availability guarantee.

## Create and configure TiDB

1. Create a **MySQL-compatible TiDB Cloud Starter** instance and keep its
   monthly spending limit at **0**.
2. Create/select the application database in the TiDB SQL editor (for example,
   `restaurant_pos`). A Starter instance has one database namespace for this
   application; tenant data is stored in prefixed tables within it.
3. In TiDB's **Connect** dialog, choose the public endpoint and copy its host,
   port, database, exact username (including the TiDB instance prefix), and
   generated password. Public connections require TLS. Allow the Render
   service's outbound IP in TiDB's firewall; Render Free uses dynamic outbound
   IPs, so a stable allowlist may require a paid Render plan. For a disposable
   demo, TiDB's public firewall can be opened broadly, but use a strong database
   password and understand that this exposes the endpoint to internet login
   attempts.
4. In Render, create a Blueprint from this repository and select the
   `Development2` branch. Set these service environment variables from TiDB:
   `CENTRAL_DB_HOST`, `CENTRAL_DB_DATABASE`, `CENTRAL_DB_USERNAME`, and
   `CENTRAL_DB_PASSWORD`. Keep port `4000`, `TENANCY_MODE=single_database`, and
   `MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt` and
   `MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=true` as in `render.yaml`.
   Do not commit credentials to the repository.
5. Deploy. The entrypoint runs central migrations, seeds plans and payment
   methods, and creates the Super Admin. Find the generated
   `SUPER_ADMIN_PASSWORD` in Render's Environment page; the default username
   is `admin`.

## Move existing data

The deployment initializes schema and demo/admin records; it does **not**
automatically copy data from an existing Render database or local MySQL dump.
Before switching production traffic, export a consistent backup of the source
database, create the TiDB schema, and import the data while preserving the
central tables and tenant prefixes. Check the import output for unsupported
MySQL features and verify row counts and app behavior before retiring the old
database. Keep a restorable backup until the migration is confirmed.

The repository includes recovery dumps at its root, but choose the correct
source dump deliberately: `rms-recovery-2026-10-27.sql` contains restaurant
application data, while `mysql-recovery-all-user-databases-2026-10-27.sql`
also includes unrelated databases. Do not import the latter wholesale into a
shared production instance without reviewing its contents.

Stripe is left unconfigured by default. Add test-mode `STRIPE_KEY` and
`STRIPE_SECRET` in Render when needed; add `STRIPE_WEBHOOK_SECRET` for webhooks.
