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

## Skenario Uji

- Buka halaman utama tanpa login untuk membaca komentar.
- Buka `/comment.php` tanpa login; aplikasi harus meminta login.
- Buat akun baru lewat `/signup.php`; akun baru bisa menulis komentar tetapi tidak bisa membuka admin panel.
- Login sebagai `admin`, lalu buka `/admin.php` untuk melihat monitoring tabel `users` dan `comments`.
- Login dengan akun demo, tambah komentar, lalu cek komentar tampil di halaman utama.
- Coba SQL injection di form login: `' OR '1'='1`; login harus gagal.
- Coba XSS di komentar: `<script>alert(1)</script>`; teks harus tampil mentah dan tidak dieksekusi.
- Kirim komentar lebih dari 500 karakter; aplikasi harus menolak.
- Ulangi login gagal; respons memiliki delay sekitar 2 detik.
- Inspeksi sertifikat browser pada `https://localhost:8443`.
