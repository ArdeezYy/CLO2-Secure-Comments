# CLO 2 Secure Comments

Aplikasi papan komentar publik berbasis PHP, MySQL, Apache, dan Docker untuk demo pengamanan aplikasi web.

## Menjalankan Aplikasi

```powershell
docker compose up --build -d
```

Jika muncul error `dockerDesktopLinuxEngine` atau Docker engine belum hidup, jalankan:

```powershell
.\scripts\start-site.ps1
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

- `secure-login`: versi aman dengan prepared statement, validasi input, CSRF token, delay login gagal, lockout brute force, session hardening, escaping XSS, pembatasan input, dan hash password.
- `vulnerable-login`: versi rentan untuk demo SQL injection, stored XSS, brute force, dan oversized input sebagai analog buffer overflow pada aplikasi PHP. Branch ini sengaja memakai query SQL mentah, menampilkan komentar tanpa escaping, melonggarkan CSP inline script, tidak memakai delay/lockout login, dan tidak membatasi panjang komentar.

## Kontrol Keamanan

- HTTPS melalui Apache SSL dan self-signed certificate.
- Cookie session memakai `HttpOnly`, `Secure`, `SameSite=Strict`, dan strict session mode.
- Semua form POST memakai CSRF token.
- Password user disimpan dengan `password_hash()` yang otomatis memakai salt.
- Signup menerapkan password policy minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan simbol.
- Halaman login dan signup memiliki tombol tampil/sembunyikan password.
- Halaman signup menampilkan checklist password secara langsung dan tombol daftar hanya aktif jika password memenuhi syarat.
- Branch `secure-login` memakai prepared statement untuk login dan input komentar.
- Branch `secure-login` meng-escape output database dengan `htmlspecialchars()` untuk mitigasi XSS.
- Branch `vulnerable-login` sengaja menampilkan isi komentar tanpa escaping untuk demo stored XSS.
- Branch `vulnerable-login` sengaja tidak memakai delay/lockout login untuk demo brute force.
- Branch `secure-login` membatasi panjang input di sisi server untuk mengurangi risiko overflow/abuse.
- Branch `vulnerable-login` sengaja tidak membatasi panjang komentar dan memakai kolom `LONGTEXT` untuk demo oversized input.
- Admin panel hanya bisa diakses akun dengan role admin.
- Security headers aktif: CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy.

## Skenario Uji

- Buka halaman utama tanpa login untuk membaca komentar.
- Buka `/comment.php` tanpa login; aplikasi harus meminta login.
- Buat akun baru lewat `/signup.php`; akun baru bisa menulis komentar tetapi tidak bisa membuka admin panel.
- Login sebagai `admin`, lalu buka `/admin.php` untuk melihat monitoring tabel `users` dan `comments`.
- Login dengan akun demo, tambah komentar, lalu cek komentar tampil di halaman utama.
- Pada branch `vulnerable-login`, coba SQL injection di form login utama:
  - Username: `' OR '1'='1' -- -`
  - Password: `bebas`
  - Login harus berhasil masuk sebagai `admin`.
- Pada branch `secure-login`, payload yang sama harus gagal.
- Pada branch `vulnerable-login`, login lalu kirim komentar `<script>alert(1)</script>`; alert harus muncul ketika halaman komentar dibuka.
- Pada branch `secure-login`, payload XSS yang sama harus tampil sebagai teks dan tidak dieksekusi.
- Pada branch `vulnerable-login`, kirim komentar sangat panjang; aplikasi tetap menerima dan menyimpannya sebagai demo oversized input.
- Pada branch `secure-login`, kirim komentar lebih dari 500 karakter; aplikasi harus menolak.
- Submit form POST tanpa CSRF token harus ditolak/redirect.
- Pada branch `vulnerable-login`, ulangi login gagal berkali-kali; respons tetap cepat karena tidak ada delay/lockout.
- Pada branch `secure-login`, ulangi login gagal; respons memiliki delay sekitar 2 detik dan setelah 5 kali gagal login dikunci sementara.
- Inspeksi sertifikat browser pada `https://localhost:8443`.
