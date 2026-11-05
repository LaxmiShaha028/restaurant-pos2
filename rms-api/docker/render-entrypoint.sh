#!/bin/sh
set -eu

: "${CENTRAL_DB_HOST:?Set CENTRAL_DB_HOST in the Render environment.}"
: "${CENTRAL_DB_PORT:?Set CENTRAL_DB_PORT in the Render environment.}"
: "${CENTRAL_DB_DATABASE:?Set CENTRAL_DB_DATABASE in the Render environment.}"
: "${CENTRAL_DB_USERNAME:?Set CENTRAL_DB_USERNAME in the Render environment.}"
: "${CENTRAL_DB_PASSWORD:?Set CENTRAL_DB_PASSWORD in the Render environment.}"
: "${APP_KEY:?Set a persistent APP_KEY in the Render environment.}"

DB_DIR=/tmp/mysql-data
DB_SOCKET=/run/mysqld/mysqld.sock

case "$CENTRAL_DB_DATABASE$CENTRAL_DB_USERNAME" in
  *[!A-Za-z0-9_]* ) echo "Database name and username may contain only letters, numbers, and underscores." >&2; exit 1 ;;
esac

mkdir -p /run/mysqld "$DB_DIR"
chown -R mysql:mysql /run/mysqld "$DB_DIR"

if [ ! -d "$DB_DIR/mysql" ]; then
  mariadb-install-db --user=mysql --datadir="$DB_DIR" --auth-root-authentication-method=normal --skip-test-db >/dev/null
fi

mariadbd --user=mysql --datadir="$DB_DIR" --socket="$DB_SOCKET" --bind-address=127.0.0.1 --port="$CENTRAL_DB_PORT" --innodb-buffer-pool-size=32M --max-connections=10 --performance-schema=OFF --skip-log-error &
DB_PID=$!

attempt=0
until mariadb-admin --no-defaults --socket="$DB_SOCKET" --user=root ping --silent >/dev/null 2>&1; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge 30 ]; then
    kill "$DB_PID" 2>/dev/null || true
    echo "MariaDB did not start." >&2
    exit 1
  fi
  sleep 2
done

ESCAPED_PASSWORD=$(printf '%s' "$CENTRAL_DB_PASSWORD" | sed "s/\\\\/\\\\\\\\/g; s/'/''/g")
mariadb --no-defaults --socket="$DB_SOCKET" --user=root <<SQL
CREATE DATABASE IF NOT EXISTS \`$CENTRAL_DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$CENTRAL_DB_USERNAME'@'127.0.0.1' IDENTIFIED BY '$ESCAPED_PASSWORD';
ALTER USER '$CENTRAL_DB_USERNAME'@'127.0.0.1' IDENTIFIED BY '$ESCAPED_PASSWORD';
GRANT ALL PRIVILEGES ON \`$CENTRAL_DB_DATABASE\`.* TO '$CENTRAL_DB_USERNAME'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

php artisan config:clear
php artisan storage:link || true
php artisan migrate --database=central --path=database/migrations/central --force
php artisan db:seed --database=central --class='Database\Seeders\Central\SubscriptionPlanSeeder' --force
php artisan db:seed --database=central --class='Database\Seeders\Central\PaymentMethodSeeder' --force
php artisan db:seed --database=central --class='Database\Seeders\Central\StripePaymentMethodSeeder' --force
php artisan db:seed --database=central --class='Database\Seeders\Central\SuperAdminSeeder' --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
