# Arsitektur & Panduan Pengembang SAPTA CBT

Dokumen ini ditujukan bagi *Developer* atau *AI Agent* yang akan membantu mengembangkan sistem SAPTA CBT di masa mendatang. Harap patuhi aturan dan arsitektur yang sudah dibangun agar sistem tetap efisien, bersih, dan cepat.

## 1. Arsitektur Penyimpanan Database vs Sistem File (Server)
Sebuah kesalahpahaman umum dalam pengembangan web adalah memasukkan segala hal (termasuk gambar dan *log*) ke dalam tabel database. SAPTA CBT menggunakan pola **Best Practices** industri perangkat lunak:
- **Media dan Gambar:** Tidak pernah disimpan dalam bentuk BLOB di dalam MySQL. Semua file foto profil, gambar soal, audio ujian di-*upload* ke folder `public/storage/`. Tabel `media` di database hanya menyimpan *path* (alamat lokasi) file tersebut. Ini menjaga database tetap ringan (total ukuran database saat ini hanya sekitar 4 MB) dan membuat akses file secepat kilat (langsung dilayani oleh Nginx/Apache, tanpa perlu *query* ke MySQL).
- **Log Sistem:** Pesan kesalahan (*error* PHP/Laravel) murni disimpan dalam file fisik `storage/logs/laravel.log`. Jika server database mati, sistem pencatatan masalah tetap berjalan.
- **Audit Log:** Aktivitas admin (siapa yang menghapus ujian, mengubah nilai, dsb.) ini disimpan di database (tabel `audit_logs`) karena datanya bersifat struktural (teks kecil) dan butuh dicari berdasar tanggal/aktor untuk dirender di UI dasbor keamanan.

## 2. Struktur Kode (MVC Pattern)
Sistem ini menggunakan struktur standar Laravel:
- **Models:** Berada di `app/Models`. Memanfaatkan Eloquent ORM. Menggunakan trait dari Spatie untuk `HasRoles` dan `InteractsWithMedia`.
- **Controllers:** Logika berada di `app/Http/Controllers`. Controller yang rumit (seperti ujian) mungkin dibantu oleh Service Classes atau Traits.
- **Views:** Menggunakan Blade di `resources/views`. Desain berbasis **TailwindCSS**. Interaktivitas *frontend* menggunakan **AlpineJS**. Sebagian halaman dibangun secara modular menggunakan komponen Blade (contoh: `<x-app-layout>`).
- **Routes:** Titik masuk aplikasi ada di `routes/web.php`. Route sudah dikelompokkan berdasarkan hak akses (Super Admin, Kurikulum, Guru).

## 3. Aturan Manajemen Database (Tidak Menghapus Tabel Penting)
Jangan sembarangan menghapus tabel dari skema database. Semua 40+ tabel yang ada dalam `cbt_sapmar` adalah tabel esensial yang saling berelasi. Sebagai contoh:
- Tabel bawaan Laravel: `migrations`, `sessions`, `cache`, `failed_jobs`, `jobs`, `job_batches`.
- Tabel manajemen peran (Spatie): `roles`, `permissions`, `model_has_roles`, dll.
- Tabel media (Spatie): `media`.
- Tabel riwayat (Spatie): `activity_log`.
- Tabel utama (SAPTA): `users`, `students`, `teachers`, `exams`, `questions`, `participant_answers`, dsb.
Semua tabel ini harus tetap dipertahankan. Jika ingin mereset data ujian, gunakan perintah *seeder* atau fitur *truncate* dari antarmuka, bukan dengan `DROP TABLE`.

## 4. Keamanan dan Manajemen Hak Akses
Sistem mengamankan halaman tidak hanya dari UI (seperti menyembunyikan menu di *sidebar* khusus untuk `kurikulum`), tetapi murni dari **Backend** menggunakan:
- Middleware di *Routes*.
- Form Request Validation (mencegah *input* kosong atau tidak logis).
- Policies dan Gates (menggunakan sintaks `$this->authorize()` atau `@can` di Blade).

Developer masa depan dilarang untuk "membypass" fungsi keamanan ini. Pastikan bahwa *Super Admin* memiliki akses ke semua data, sementara peran di bawahnya dibatasi sesuai dengan *School Master Data*.
