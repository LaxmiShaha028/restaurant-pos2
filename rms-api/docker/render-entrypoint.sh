#!/bin/sh
set -eu

: "${CENTRAL_DB_HOST:?Set CENTRAL_DB_HOST in the Render environment.}"
: "${CENTRAL_DB_PORT:?Set CENTRAL_DB_PORT in the Render environment.}"
: "${CENTRAL_DB_DATABASE:?Set CENTRAL_DB_DATABASE in the Render environment.}"
: "${CENTRAL_DB_USERNAME:?Set CENTRAL_DB_USERNAME exactly as shown in TiDB Connect.}"
: "${CENTRAL_DB_PASSWORD:?Set CENTRAL_DB_PASSWORD in the Render environment.}"
: "${APP_KEY:?Set a persistent APP_KEY in the Render environment.}"

case "$CENTRAL_DB_DATABASE" in
  *[!A-Za-z0-9_]* ) echo "CENTRAL_DB_DATABASE may contain only letters, numbers, and underscores." >&2; exit 1 ;;
esac

# Render-generated values are not necessarily in Laravel's `base64:` format.
# Derive a stable 256-bit Laravel key from the configured secret when needed.
APP_KEY=$(php -r '
$key = getenv("APP_KEY") ?: "";
if (str_starts_with($key, "base64:")) {
    $decoded = base64_decode(substr($key, 7), true);
    if ($decoded !== false && strlen($decoded) === 32) {
        echo $key;
        exit;
    }
}
if (strlen($key) === 32) {
    echo $key;
    exit;
}
echo "base64:" . base64_encode(hash("sha256", $key, true));
')
export APP_KEY

php artisan config:clear
php artisan storage:link || true
php artisan migrate --database=central --path=database/migrations/central --force
php artisan db:seed --database=central --class='Database\Seeders\Central\SubscriptionPlanSeeder' --force
php artisan db:seed --database=central --class='Database\Seeders\Central\PaymentMethodSeeder' --force
php artisan db:seed --database=central --class='Database\Seeders\Central\StripePaymentMethodSeeder' --force
php artisan db:seed --database=central --class='Database\Seeders\Central\SuperAdminSeeder' --force
if ! php artisan tenants:seed-real-menus; then
  echo "WARNING: tenant menu seeding failed; continuing web startup. See the error above and run tenants:seed-real-menus again after checking tenant database connections." >&2
fi

php artisan queue:work database --queue=default --sleep=1 --tries=1 --timeout=1800 --memory=256 &
QUEUE_WORKER_PID=$!

php artisan serve --host=0.0.0.0 --port="${PORT:-10000}" &
WEB_SERVER_PID=$!

cleanup() {
  kill "$WEB_SERVER_PID" "$QUEUE_WORKER_PID" 2>/dev/null || true
  wait "$WEB_SERVER_PID" "$QUEUE_WORKER_PID" 2>/dev/null || true
}

trap cleanup EXIT INT TERM
wait "$WEB_SERVER_PID"
