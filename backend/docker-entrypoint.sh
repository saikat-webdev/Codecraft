#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
  if [ -f env.docker.example ]; then
    cp env.docker.example .env
    echo "Created .env from env.docker.example"
  fi
fi

echo "Waiting for PostgreSQL..."
TRIES=0
MAX_TRIES=30
until php -r "
  \$host = getenv('DB_HOST') ?: 'postgres';
  \$port = getenv('DB_PORT') ?: '5432';
  \$db = getenv('DB_DATABASE') ?: 'codecraft_db';
  \$user = getenv('DB_USERNAME') ?: 'codecraft';
  \$pass = getenv('DB_PASSWORD') ?: '';
  try {
    new PDO(\"pgsql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
    exit(0);
  } catch (Throwable \$e) {
    exit(1);
  }
" 2>/dev/null; do
  TRIES=$((TRIES + 1))
  if [ "$TRIES" -ge "$MAX_TRIES" ]; then
    echo "ERROR: PostgreSQL not reachable after ${MAX_TRIES} attempts."
    exit 1
  fi
  sleep 2
done
echo "PostgreSQL is ready."

# Composer install on every start + Windows bind mounts = system freeze. Skip when baked into image.
if [ "${SKIP_COMPOSER_INSTALL}" != "1" ] && [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force --no-interaction
fi

php artisan migrate --force --no-interaction
php artisan storage:link --force --no-interaction 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port=8000
