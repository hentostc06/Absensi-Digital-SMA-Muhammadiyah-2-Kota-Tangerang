#!/usr/bin/env bash

APP_DIR="$(pwd)"
APP_NAME="absensi-badcoding"
SERVICE_NAME="absensi-octane"
APP_HOST="127.0.0.1"
APP_PORT="8000"
WORKERS="2"

echo "=================================================="
echo " DEPLOY PRODUCTION ABSENSI"
echo " Project : $APP_DIR"
echo " Octane  : http://$APP_HOST:$APP_PORT"
echo " Service : $SERVICE_NAME"
echo "=================================================="

if [ ! -f "$APP_DIR/artisan" ]; then
  echo "ERROR: File artisan tidak ditemukan."
  echo "Jalankan script ini dari root Laravel, contoh:"
  echo "  cd /var/www/absensi-badcoding"
  echo "  ./server-deploy.sh"
else
  cd "$APP_DIR" || true

  echo
  echo "[1/10] Cek .env dan APP_KEY..."
  if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
    echo "[OK] .env dibuat dari .env.example"
  fi

  if grep -q '^APP_KEY=$' .env 2>/dev/null || ! grep -q '^APP_KEY=' .env 2>/dev/null; then
    php artisan key:generate --force
  fi

  echo
  echo "[2/10] Install composer dependency kalau vendor belum ada..."
  if [ ! -d "vendor" ]; then
    composer install --no-dev --optimize-autoloader
  else
    composer install --no-dev --optimize-autoloader
  fi

  echo
  echo "[3/10] Pastikan Laravel Octane terpasang..."
  if ! php artisan list | grep -q "octane:start"; then
    composer require laravel/octane --no-interaction
    php artisan octane:install --server=frankenphp --no-interaction
  else
    echo "[OK] Octane sudah terpasang."
  fi

  echo
  echo "[4/10] Bersihkan Vite dev mode public/hot..."
  if [ -f "public/hot" ]; then
    rm -f public/hot
    echo "[OK] public/hot dihapus. Ini penting agar HP tidak mencari Vite localhost."
  else
    echo "[OK] public/hot tidak ada."
  fi

  echo
  echo "[5/10] Install NPM dependency..."
  if [ -f "package-lock.json" ]; then
    npm ci
  else
    npm install
  fi

  echo
  echo "[6/10] Build asset production..."
  npm run build

  echo
  echo "[7/10] Laravel cache production..."
  php artisan optimize:clear
  php artisan storage:link 2>/dev/null || true
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache

  echo
  echo "[8/10] Permission storage/cache..."
  mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
  chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

  echo
  echo "[9/10] Buat systemd service Octane..."
  PHP_BIN="$(command -v php)"

  cat > "/etc/systemd/system/${SERVICE_NAME}.service" <<EOF
[Unit]
Description=Laravel Octane - ${APP_NAME}
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=${APP_DIR}
ExecStart=${PHP_BIN} artisan octane:start --server=frankenphp --host=${APP_HOST} --port=${APP_PORT} --workers=${WORKERS} --max-requests=500
ExecReload=${PHP_BIN} artisan octane:reload
Restart=always
RestartSec=5
LimitNOFILE=65535

[Install]
WantedBy=multi-user.target
EOF

  systemctl daemon-reload
  systemctl enable "$SERVICE_NAME"
  systemctl restart "$SERVICE_NAME"

  echo
  echo "[10/10] Reload Nginx kalau ada..."
  nginx -t 2>/dev/null && systemctl reload nginx || true

  echo
  echo "=================================================="
  echo " STATUS OCTANE"
  echo "=================================================="
  systemctl status "$SERVICE_NAME" --no-pager -l | head -40

  echo
  echo "=================================================="
  echo " CEK LOCAL"
  echo "=================================================="
  curl -I "http://${APP_HOST}:${APP_PORT}" 2>/dev/null | head -10 || true

  echo
  echo "SELESAI."
  echo
  echo "Command penting:"
  echo "  systemctl status ${SERVICE_NAME} --no-pager -l"
  echo "  systemctl restart ${SERVICE_NAME}"
  echo "  journalctl -u ${SERVICE_NAME} -f"
  echo
  echo "Catatan:"
  echo "  - Production pakai npm run build, bukan npm run dev."
  echo "  - Kalau tampilan HP masih burik, pastikan Nginx/domain mengarah ke http://${APP_HOST}:${APP_PORT}"
  echo "  - Pastikan file public/build/manifest.json ada."
fi
