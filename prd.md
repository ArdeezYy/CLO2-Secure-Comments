Berikut adalah pembaruan dokumen PRD akhir yang telah mengakomodasi struktur jaringan, alur akses publik/privat, serta skenario pengujian menggunakan lingkungan *host* yang telah disiapkan.

---

## **Project Requirements Document (PRD)**
**Nama Projek:** Pengamanan Aplikasi Web - Tugas CLO 2 Keamanan Sistem
**Identitas Mesin:** Ardika_101032300240[cite: 1]

### **1. Pendahuluan**
*   **Tujuan:** Memenuhi capaian pembelajaran CLO 2, yaitu pemahaman dan implementasi konsep keamanan pada perangkat komputer dan server[cite: 1].
*   **Deskripsi Singkat:** Pembuatan aplikasi web sederhana berbasis HTML, PHP, dan SQL[cite: 1]. Aplikasi ini difungsikan sebagai papan komentar publik di mana pembacaan data bersifat terbuka, namun penulisan data memerlukan autentikasi yang diamankan dari berbagai vektor serangan.

### **2. Spesifikasi Infrastruktur & Jaringan**
| Komponen | Spesifikasi | Keterangan |
| :--- | :--- | :--- |
| **Lingkungan Host** | Kali Linux | Berfungsi sebagai landasan *container* dan mesin utama untuk melakukan simulasi serangan saat demonstrasi. |
| **Virtualisasi** | Docker Container | Implementasi *container* terisolasi untuk layanan web dan *database*[cite: 1]. |
| **Sistem Operasi** | Linux (Debian/Ubuntu Image) | Basis sistem operasi pada *container*[cite: 1]. |
| **IP Address** | `192.168.1.240` | Jaringan Kelas C dengan oktet terakhir menggunakan 3 digit NIM (240)[cite: 1]. |
| **Web Server** | Apache / Nginx | Dikonfigurasi untuk mendukung protokol HTTPS[cite: 1]. |
| **Database** | MySQL | Penyimpanan terstruktur untuk kredensial *user* dan data komentar[cite: 1]. |

### **3. Alur Aplikasi (User Flow)**
*   **Halaman Utama (Public Access):**
    *   Dapat diakses oleh seluruh pengunjung tanpa melalui proses *login*.
    *   Menampilkan data komentar yang ditarik secara dinamis dari *database* SQL[cite: 1].
    *   Menyediakan tombol navigasi menuju halaman autentikasi.
*   **Sistem Autentikasi (Restricted):**
    *   Memuat formulir *input* untuk *Username* dan *Password*[cite: 1].
    *   Menginisiasi sesi (*session*) pengguna yang valid.
*   **Halaman Input Komentar (Restricted):**
    *   Formulir halaman komentar[cite: 1] hanya dapat diakses dan memproses *input* jika pengguna memiliki sesi aktif (*logged in*).

### **4. Matriks Penilaian & Mekanisme Keamanan**
Implementasi keamanan berikut dirancang untuk mencapai standar penilaian maksimal (100 poin):

*   **Keamanan Transport (50 Poin):** Konfigurasi HTTPS pada *web server* menggunakan algoritma kunci publik (seperti RSA) melalui *Self-Signed Certificate*[cite: 1].
*   **Keamanan Kredensial (10 Poin):** Penerapan fungsi *hash* kriptografis yang dikombinasikan dengan teknik *salting* pada pengiriman dan penyimpanan data *password*[cite: 1].
*   **Pertahanan SQL Injection (10 Poin):** Pengamanan *input* pengguna pada formulir *login* dan komentar dari serangan *SQL injection*[cite: 1] menggunakan *Prepared Statements* (PDO/MySQLi).
*   **Pertahanan Cross-Site Scripting / XSS (10 Poin):** Pengamanan aplikasi web dari injeksi *script* XSS[cite: 1] melalui sanitasi *output* pada halaman publik.
*   **Pertahanan Buffer Overflow (10 Poin):** Pengamanan dari *buffer overflow*[cite: 1] dengan melakukan validasi dan pembatasan panjang karakter secara ketat di sisi *server*.
*   **Mitigasi Brute Force (10 Poin):** Konfigurasi penangkal serangan *brute force*[cite: 1] dengan menerapkan penundaan waktu (*delay*) eksekusi setiap kali terjadi kegagalan autentikasi.

### **5. Rencana Simulasi Serangan (Demo)**
Pengujian dilakukan langsung dari lingkungan mesin *host* memanfaatkan *tools penetration testing* bawaan:
1.  **Simulasi Brute Force:** Menggunakan *Burp Suite Intruder* untuk menembakkan kombinasi *password* secara beruntun dan menguji fungsi *delay*.
2.  **Simulasi SQLi:** Memasukkan *payload* modifikasi *query* (contoh: `' OR '1'='1`) langsung ke formulir *login* melalui *browser*.
3.  **Simulasi XSS:** Menyisipkan *tag* `<script>` berbahaya pada formulir komentar dan memverifikasi bahwa *browser* menampilkan teks mentah tanpa mengeksekusi *script* tersebut.
4.  **Verifikasi Transport:** Menginspeksi rincian sertifikat SSL/TLS pada *browser* untuk menunjukkan detail algoritma kunci publik yang aktif.

### **6. Syarat Kelulusan Administratif**
Dokumentasi dan persyaratan berikut harus dilengkapi sebelum batas akhir pengumpulan pada **12 Mei 2026 pukul 19:00 WIB**[cite: 1]:
*   Dokumentasi aplikasi lengkap dalam format berkas[cite: 1].
*   File presentasi yang mencakup pendahuluan, teori keamanan, blok diagram, penjelasan metode mitigasi kerentanan, sesi demo uji serangan, dan kesimpulan/saran[cite: 1].
*   Tautan video presentasi dengan durasi maksimal 3 menit yang dicantumkan di dalam dokumentasi[cite: 1].
*   Aksesibilitas penuh terhadap kode program dan aplikasi saat dokumen dikumpulkan di LMS[cite: 1].