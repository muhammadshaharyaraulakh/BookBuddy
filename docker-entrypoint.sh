#!/bin/sh
set -e

# Dynamically bind to Railway's assigned $PORT (defaults to 8080 if not set)
PORT="${PORT:-8080}"

echo "==> Starting BookBuddy PHP server on 0.0.0.0:${PORT}..."
exec php -S "0.0.0.0:${PORT}" router.php
