# SAPTA CBT

SAPTA CBT adalah platform Ujian Berbasis Komputer (*Computer Based Test*) yang dikembangkan khusus untuk keperluan manajemen ujian di sekolah. Sistem ini dirancang secara modern dengan fokus pada keamanan, skalabilitas, kecepatan akses, serta kenyamanan pengalaman pengguna (*User Experience*) yang dioptimalkan untuk berbagai perangkat.

## Fitur Utama

- **Manajemen Pengguna Terpusat:** Pengelompokan *role* mulai dari Super Admin, Kurikulum, Guru, Staf, hingga Siswa.
- **Sistem Bank Soal Terintegrasi:** Guru dapat membuat, mengelola, dan mendaur ulang soal dari Bank Soal ke berbagai ujian tanpa bentrok.
- **Dukungan Berbagai Tipe Soal:** Pilihan Ganda, Pilihan Ganda Kompleks (Banyak Jawaban), Benar/Salah, dan Menjodohkan. Tersedia dukungan untuk media gambar pada soal maupun opsi jawaban.
- **Keamanan Real-time:** Tombol manajemen akan terkunci otomatis ketika ujian berstatus *Published* untuk mencegah perubahan yang merusak integritas ujian yang sedang berjalan.
- **Sistem Penjadwalan & Susulan:** Fitur manajemen rombongan belajar (kelas), siswa ekstra, dan pengaturan waktu khusus untuk ujian susulan.
- **Jejak Audit (Audit Log):** Semua tindakan sensitif dicatat ke dalam database untuk mencegah kecurangan dan mempermudah pelacakan aktivitas admin.

## Teknologi (Tech Stack)

Sistem ini dibangun dengan mematuhi *Best Practices* standar industri menggunakan *framework* unggulan:

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** TailwindCSS, AlpineJS, Blade Templating
- **Database:** MySQL / MariaDB
- **Manajemen Hak Akses:** Spatie Laravel Permission
- **Manajemen File:** Spatie Laravel Media Library
- **Log Sistem:** Spatie Activity Log

## Arsitektur Penyimpanan

Sistem SAPTA CBT sangat efisien dalam pengelolaan sumber daya memori server:
1.  **Media (Gambar):** Disimpan sepenuhnya di *file system* (`public/storage`), bukan di dalam *database*. Database hanya menyimpan *path*/alamat file. Hal ini menjaga ukuran database tetap sangat kecil dan ringan.
2.  **Log Kesalahan:** Disimpan pada *file* `storage/logs/laravel.log`.

## Persyaratan Sistem Server

- PHP >= 8.2
- MySQL 8.0+ atau MariaDB 10.6+
- Ekstensi PHP: Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, XML, GD/Imagick (untuk Spatie Media Library).
- Composer 2.x
- Node.js (untuk keperluan instalasi dependensi TailwindCSS di tahap pengembangan).

## Panduan Instalasi (Development)

1. Klon repositori ini ke dalam direktori server lokal Anda (misal: Laragon/XAMPP).
2. Salin `.env.example` menjadi `.env` dan atur konfigurasi *database* Anda:
   ```bash
   cp .env.example .env
   ```
3. Unduh seluruh dependensi PHP dan Javascript:
   ```bash
   composer install
   npm install
   ```
4. Jalankan migrasi database dan *seed* (untuk mengisi data awal):
   ```bash
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
5. Tautkan folder penyimpanan publik agar gambar dapat diakses:
   ```bash
   php artisan storage:link
   ```
6. Jalankan server lokal:
   ```bash
   php artisan serve
   ```
   (Jalankan juga `npm run dev` di terminal terpisah jika Anda ingin melakukan perubahan pada desain TailwindCSS).

## Pengembang / Kontributor

Proyek ini dibangun melalui kolaborasi berkelanjutan dan menggunakan bantuan AI *(Agentic Development)* yang terarah melalui instruksi khusus pada file `AGENTS.md` dan panduan struktur di dalam `docs/architecture.md`. Setiap pengembang lanjutan diwajibkan membaca dokumentasi arsitektur sebelum memodifikasi kode inti.
