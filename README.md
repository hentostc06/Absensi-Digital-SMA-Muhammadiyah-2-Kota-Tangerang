# Sistem Informasi Absensi Siswa — QR Code Dinamis

Implementasi Laravel + MySQL untuk **SMA Muhammadiyah 2 Tangerang**, disusun berdasarkan kebutuhan skripsi: tiga hak akses (Admin/TU, Guru, Siswa), QR Code dinamis yang berganti setiap 30 detik, validasi token di server, monitoring kehadiran real-time, anti-absen ganda, akun dapat dikunci, dan laporan PDF/Excel.

## Fitur utama

- Admin: CRUD siswa, guru, kelas, mata pelajaran, jadwal; kunci/buka akun; filter laporan; ekspor PDF dan Excel.
- Guru: membuka sesi sesuai jadwal, QR dinamis 30 detik, countdown, monitoring kehadiran polling 3 detik, absensi manual, tutup sesi.
- Siswa: scan kamera smartphone, validasi kelas/sesi/token/waktu, umpan balik animasi sukses/gagal, riwayat kehadiran.
- Keamanan: HMAC-SHA256 menggunakan `APP_KEY`, token terikat ID sesi + UUID + time-slot + token version, unique constraint mencegah scan ganda.
- Status: hadir, terlambat, izin, sakit, alpa.

## Instalasi Ubuntu/Linux

```bash
cd /var/www
sudo mkdir -p absensi-siswa
sudo chown -R $USER:$USER absensi-siswa
# salin seluruh source project ini ke folder tersebut
cd absensi-siswa

cp .env.example .env
composer install
php artisan key:generate

mysql -u root -p -e "CREATE DATABASE absensi_siswa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# sesuaikan DB_USERNAME dan DB_PASSWORD di .env

php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve --host=0.0.0.0 --port=8000
```

Buka `http://127.0.0.1:8000`.

## Akun demo

| Peran | Username | Password |
|---|---|---|
| Admin | `admin` | `Admin123!` |
| Guru | `guru.demo` | `Guru123!` |
| Siswa | `19221273` | `Siswa123!` |

Ganti seluruh password demo sebelum dipakai di sekolah.

## Catatan kamera

Browser hanya memberikan akses kamera pada **HTTPS** atau `localhost`. Saat diakses dari HP melalui alamat IP LAN, gunakan HTTPS (misalnya Nginx + sertifikat lokal/Cloudflare Tunnel).

## Alur QR dinamis

1. Guru membuka sesi dari jadwal.
2. Server membuat token HMAC untuk jendela waktu 30 detik.
3. Layar guru meminta token terbaru saat countdown habis.
4. Siswa scan; server memeriksa signature, sesi aktif, kelas siswa, time-slot aktif, dan duplikasi.
5. Rekaman disimpan dengan waktu server, IP, user-agent, sumber `qr`/`manual`.
