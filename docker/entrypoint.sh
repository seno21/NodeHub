#!/bin/sh
set -e

cd /var/www/html

# Disable skip-networking in MariaDB configs if present
sed -i 's/^skip-networking/#skip-networking/' /etc/my.cnf /etc/my.cnf.d/*.cnf 2>/dev/null || true

# 1. Generate SSL Certificate if missing
mkdir -p /etc/nginx/ssl
if [ ! -f /etc/nginx/ssl/nginx.crt ] || [ ! -f /etc/nginx/ssl/nginx.key ]; then
    echo "entrypoint: Generating self-signed SSL certificate..."
    openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
        -keyout /etc/nginx/ssl/nginx.key \
        -out /etc/nginx/ssl/nginx.crt \
        -subj "/C=ID/ST=State/L=City/O=NodeHub/CN=nodehub.local" 2>/dev/null
fi

# 2. Setup Environment & App Key
KEY_FILE="storage/app/.app_key"

if [ ! -f .env ]; then
    cp .env.docker .env
fi

if [ -z "${APP_KEY:-}" ] && [ ! -s "$KEY_FILE" ]; then
    mkdir -p "$(dirname "$KEY_FILE")"
    echo "base64:$(head -c 32 /dev/urandom | base64)" > "$KEY_FILE"
    chmod 600 "$KEY_FILE"
fi

if [ -z "${APP_KEY:-}" ]; then
    APP_KEY="$(cat "$KEY_FILE")"
    export APP_KEY
fi

# Ensure localhost DB_HOST for all-in-one setup
export DB_HOST="${DB_HOST:-127.0.0.1}"
export DB_PORT="${DB_PORT:-3306}"
export DB_DATABASE="${DB_DATABASE:-nodehub}"
export DB_USERNAME="${DB_USERNAME:-nodehub}"
export DB_PASSWORD="${DB_PASSWORD:-nodehub-secret}"

# 3. Setup MariaDB Data Directory
mkdir -p /run/mysqld /var/lib/mysql
chown -R mysql:mysql /run/mysqld /var/lib/mysql

if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "entrypoint: Initializing fresh MariaDB database directory..."
    mariadb-install-db --user=mysql --datadir=/var/lib/mysql >/dev/null
fi

# 4. Start MariaDB temporarily to initialize users and tables
echo "entrypoint: Bootstrapping database schema & initial admin user..."
/usr/bin/mysqld --user=mysql --bind-address=0.0.0.0 --port=3306 --datadir=/var/lib/mysql &
MYSQL_PID=$!

i=0
until mariadb-admin ping --silent 2>/dev/null; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "entrypoint: MariaDB failed to respond within 30s" >&2
        exit 1
    fi
    sleep 1
done

# Grant privileges to nodehub user
mariadb -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\`;" 2>/dev/null || true
mariadb -u root -e "GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';" 2>/dev/null || true
mariadb -u root -e "GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'127.0.0.1' IDENTIFIED BY '${DB_PASSWORD}';" 2>/dev/null || true
mariadb -u root -e "GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';" 2>/dev/null || true
mariadb -u root -e "FLUSH PRIVILEGES;" 2>/dev/null || true

# Run Laravel migrations
php artisan migrate --force

# Cache Laravel configurations, routes, and views for maximum production performance
echo "entrypoint: Optimizing & caching Laravel configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true

# Seed admin user on fresh database if no users exist
php -r '
    $db = getenv("DB_DATABASE") ?: "nodehub";
    $user = getenv("DB_USERNAME") ?: "nodehub";
    $pass = getenv("DB_PASSWORD") ?: "nodehub-secret";
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname={$db}", $user, $pass);
        $count = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count === 0) {
            echo "entrypoint: Seeding default admin...\n";
            system("php artisan db:seed --force");
        }
    } catch (Throwable $e) {
        echo "entrypoint: DB check warning: " . $e->getMessage() . "\n";
    }
'

# Stop temporary MariaDB before Supervisord starts
kill "$MYSQL_PID" 2>/dev/null || true
wait "$MYSQL_PID" 2>/dev/null || true

# 5. Prepare Storage Permissions & Tokens File
mkdir -p storage/app storage/logs storage/framework/views storage/framework/sessions storage/framework/cache
touch storage/app/vnc-tokens.cfg storage/logs/websockify.log
chmod -R 777 storage bootstrap/cache

echo "entrypoint: Launching Supervisord All-in-One Container Services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
