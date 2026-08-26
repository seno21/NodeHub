#!/bin/sh
set -e

cd /var/www/html
KEY_FILE="storage/app/.app_key"

# --- First boot: seed .env from the docker template ---
if [ ! -f .env ]; then
    cp .env.docker .env
fi

# --- Persistent APP_KEY: stored on the shared storage volume so all
#     containers and future container recreations reuse the same key ---
if [ -z "${APP_KEY:-}" ] && [ ! -s "$KEY_FILE" ]; then
    mkdir -p "$(dirname "$KEY_FILE")"
    echo "base64:$(head -c 32 /dev/urandom | base64)" > "$KEY_FILE"
    chmod 600 "$KEY_FILE"
fi
if [ -z "${APP_KEY:-}" ]; then
    APP_KEY="$(cat "$KEY_FILE")"
    export APP_KEY
fi

# --- Wait for the database (skipped with SKIP_BOOTSTRAP=1) ---
wait_for_db() {
    i=0
    until php -r '
        try {
            new PDO(
                sprintf("mysql:host=%s;port=%d", getenv("DB_HOST"), (int) (getenv("DB_PORT") ?: 3306)),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD")
            );
        } catch (Throwable $e) {
            exit(1);
        }
    ' 2>/dev/null; do
        i=$((i + 1))
        if [ "$i" -ge 60 ]; then
            echo "entrypoint: database not reachable after 60s" >&2
            exit 1
        fi
        sleep 1
    done
}

bootstrap() {
    wait_for_db

    attempt=1
    while ! php artisan migrate --force; do
        attempt=$((attempt + 1))
        if [ "$attempt" -gt 5 ]; then
            echo "entrypoint: migrate failed after 5 attempts" >&2
            exit 1
        fi
        sleep 2
    done

    # Seed admin (+ sample devices) only on a fresh database.
    USERS=$(php -r '
        try {
            $pdo = new PDO(
                sprintf("mysql:host=%s;port=%d;dbname=%s", getenv("DB_HOST"), (int) (getenv("DB_PORT") ?: 3306), getenv("DB_DATABASE")),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD")
            );
            echo (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        } catch (Throwable $e) {
            echo "-1";
        }
    ')
    if [ "$USERS" = "0" ]; then
        php artisan db:seed --force
        echo "entrypoint: seeded default admin (${ADMIN_EMAIL:-admin@example.com})"
    fi

    # Cache config/route hanya di production; di lokal cache membuat
    # edit routes/config tidak terlihat sampai container di-restart.
    if [ "${APP_ENV:-production}" = "production" ]; then
        php artisan config:cache
        php artisan route:cache
    fi
}

if [ "${SKIP_BOOTSTRAP:-0}" != "1" ]; then
    bootstrap
fi

exec "$@"
