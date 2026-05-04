# CLO 2 Secure Comments

Aplikasi papan komentar publik berbasis PHP, MySQL, Apache, dan Docker untuk demo pengamanan aplikasi web.

## Menjalankan Aplikasi

```powershell
docker compose up --build -d
```

URL demo:

- HTTPS: https://localhost:8443
- HTTP redirect: http://localhost:8080

Browser akan menampilkan peringatan karena sertifikat SSL dibuat sendiri. Lanjutkan ke halaman untuk kebutuhan demo lokal.

## Akun Demo

- Username: `admin`
- Password: `Admin@240!`
- User biasa dapat dibuat lewat `/signup.php`.
- Username `admin` dan `root` tidak bisa didaftarkan dari sign up publik.

## Branch Demo

- `secure-login`: versi aman dengan prepared statement, validasi input, CSRF token, delay login gagal, session hardening, dan hash password.
- `vulnerable-login`: versi sementara yang sengaja memakai query SQL mentah di form login utama, tetapi kontrol lain seperti CSRF, XSS escaping, admin authorization, HTTPS, dan session hardening tetap aktif.

## Kontrol Keamanan

- HTTPS melalui Apache SSL dan self-signed certificate.
- Cookie session memakai `HttpOnly`, `Secure`, `SameSite=Strict`, dan strict session mode.
- Semua form POST memakai CSRF token.
- Password user disimpan dengan `password_hash()` yang otomatis memakai salt.
- Signup menerapkan password policy minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan simbol.
- Branch `secure-login` memakai prepared statement untuk login dan input komentar.
- Output dari database di-escape dengan `htmlspecialchars()` untuk mitigasi XSS.
- Input dibatasi panjangnya di sisi server untuk mengurangi risiko overflow/abuse.
- Admin panel hanya bisa diakses akun dengan role admin.
- Security headers aktif: CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy.

## Skenario Uji

- Buka halaman utama tanpa login untuk membaca komentar.
- Buka `/comment.php` tanpa login; aplikasi harus meminta login.
- Buat akun baru lewat `/signup.php`; akun baru bisa menulis komentar tetapi tidak bisa membuka admin panel.
- Login sebagai `admin`, lalu buka `/admin.php` untuk melihat monitoring tabel `users` dan `comments`.
- Login dengan akun demo, tambah komentar, lalu cek komentar tampil di halaman utama.
- Coba SQL injection di form login: `' OR '1'='1`; login harus gagal.
- Coba XSS di komentar: `<script>alert(1)</script>`; teks harus tampil mentah dan tidak dieksekusi.
- Kirim komentar lebih dari 500 karakter; aplikasi harus menolak.
- Submit form POST tanpa CSRF token harus ditolak/redirect.
- Ulangi login gagal; respons memiliki delay sekitar 2 detik.
- Inspeksi sertifikat browser pada `https://localhost:8443`.
