#!/bin/sh
set -eu

DB_DIR=/tmp/mysql-data
DB_SOCKET=/run/mysqld/mysqld.sock
DB_NAME=${CENTRAL_DB_DATABASE:-rms_central}
DB_USER=${CENTRAL_DB_USERNAME:-rms_app}
DB_PASSWORD=${CENTRAL_DB_PASSWORD:?Set CENTRAL_DB_PASSWORD in Render environment variables.}

case "$DB_NAME$DB_USER" in
  *[!A-Za-z0-9_]* ) echo "Database name and username may contain only letters, numbers, and underscores." >&2; exit 1 ;;
esac

mkdir -p /run/mysqld "$DB_DIR"
chown -R mysql:mysql /run/mysqld "$DB_DIR"

if [ ! -d "$DB_DIR/mysql" ]; then
  mariadb-install-db --user=mysql --datadir="$DB_DIR" --auth-root-authentication-method=normal --skip-test-db >/dev/null
fi

mariadbd --user=mysql --datadir="$DB_DIR" --socket="$DB_SOCKET" --bind-address=127.0.0.1 --port=3306 --innodb-buffer-pool-size=64M --max-connections=20 --skip-log-error &
DB_PID=$!

attempt=0
until mariadb-admin --no-defaults --socket="$DB_SOCKET" --user=root ping --silent >/dev/null 2>&1; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge 30 ]; then
    kill "$DB_PID" 2>/dev/null || true
    echo "MySQL-compatible database did not start." >&2
    exit 1
  fi
  sleep 2
done

ESCAPED_PASSWORD=$(printf '%s' "$DB_PASSWORD" | sed "s/\\\\/\\\\\\\\/g; s/'/''/g")
mariadb --no-defaults --socket="$DB_SOCKET" --user=root <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$ESCAPED_PASSWORD';
ALTER USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$ESCAPED_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

if [ -z "${APP_KEY:-}" ]; then
  APP_KEY=$(php artisan key:generate --show)
  export APP_KEY
fi

php artisan config:clear
php artisan storage:link || true
php artisan migrate --database=central --path=database/migrations/central --force
php artisan db:seed --database=central --class='Database\Seeders\Central\SuperAdminSeeder' --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
