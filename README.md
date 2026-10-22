# Hotmad

Aplikasi kasir **Hotmad** (warung jagung bakar & pisang) berbasis Laravel, dirancang mobile-first untuk Android.

## Menjalankan

1. `composer install`
2. Salin `.env.example` menjadi `.env`, lalu sesuaikan koneksi database.
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `php artisan serve`

Akun demo:

- Owner: `owner@warungjagung.test` / `owner123`
- Staff: `staff@warungjagung.test` / `staff123`

## Catatan

- Foto menu disimpan privat di `storage/app/private/menus/` dan disajikan lewat route yang membutuhkan login.
- Upload foto dikompres di browser, lalu dinormalisasi di server menjadi WebP (maksimal 1200 px) dengan thumbnail 480 × 480.
- Server membutuhkan `ffmpeg` dengan dukungan WebP. Lokasi binary bisa diatur lewat `FFMPEG_PATH`.
- Harga topping untuk semua menu kategori **makanan** diatur lewat `HOTMAD_TOPPING_PRICE` (default Rp2.000).
