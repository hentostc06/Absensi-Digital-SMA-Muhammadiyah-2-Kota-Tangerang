#!/usr/bin/env bash
set +e

APP_DIR="$(pwd)"

if [ ! -f "$APP_DIR/artisan" ]; then
  echo "ERROR: Jalankan dari root Laravel."
  exit 0
fi

echo "=================================================="
echo " CEK EMAIL SISTEM ABSENSI"
echo "=================================================="

echo
echo "[1] Konfigurasi MAIL di .env:"
grep -E '^(MAIL_MAILER|MAIL_HOST|MAIL_PORT|MAIL_USERNAME|MAIL_PASSWORD|MAIL_ENCRYPTION|MAIL_FROM_ADDRESS|MAIL_FROM_NAME)=' .env | sed 's/MAIL_PASSWORD=.*/MAIL_PASSWORD=********/'

echo
echo "[2] Cek apakah kolom users.email ada dan berapa akun yang punya email:"
php artisan tinker --execute="
use Illuminate\Support\Facades\Schema;
use App\Models\User;
echo 'Kolom email: '.(Schema::hasColumn('users','email') ? 'ADA' : 'TIDAK ADA').PHP_EOL;
if (Schema::hasColumn('users','email')) {
    echo 'User dengan email: '.User::whereNotNull('email')->where('email','!=','')->count().PHP_EOL;
    echo 'Total user: '.User::count().PHP_EOL;
}
"

echo
echo "[3] Cara set email user kalau masih kosong:"
echo "php artisan tinker --execute=\"App\\Models\\User::where('username','guru.demo')->update(['email'=>'EMAIL_GMAIL_USER@gmail.com']);\""

echo
echo "[4] Test kirim email:"
if [ -z "$1" ]; then
  echo "Belum ada email tujuan."
  echo "Jalankan contoh:"
  echo "  ./cek-email-absensi.sh emailtujuan@gmail.com"
else
  php artisan tinker --execute="
  use Illuminate\Support\Facades\Mail;
  try {
      Mail::raw('Test email Sistem Absensi QR - '.now(), function (\$message) {
          \$message->to('$1')->subject('Test Email Sistem Absensi QR');
      });
      echo 'OK: perintah kirim email sudah dijalankan. Cek Inbox/Spam.'.PHP_EOL;
  } catch (Throwable \$e) {
      echo 'ERROR KIRIM EMAIL: '.\$e->getMessage().PHP_EOL;
  }
  "
fi

echo
echo "[5] Log Laravel terakhir kalau ada error email:"
tail -80 storage/logs/laravel.log 2>/dev/null | grep -iE 'mail|smtp|password|authentication|connection|failed|error' | tail -30

echo
echo "=================================================="
echo " SELESAI"
echo "=================================================="
