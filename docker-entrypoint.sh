#!/bin/sh
set -e

PORT="${PORT:-8080}"
export PHP_CLI_SERVER_WORKERS="${PHP_CLI_SERVER_WORKERS:-4}"

# Auto-migrate DB on fresh Railway deploy
if [ -n "$MYSQLHOST" ] || [ -n "$MYSQL_URL" ] || [ -n "$DATABASE_URL" ]; then
    echo "==> Running database seed/migration..."
    php seed.php || echo "==> Seed returned non-zero, continuing..."
fi

echo "==> Starting server on 0.0.0.0:${PORT} with ${PHP_CLI_SERVER_WORKERS} workers..."
exec php -S "0.0.0.0:${PORT}" router.php