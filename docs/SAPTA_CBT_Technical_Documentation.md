# Dokumentasi Teknis Lengkap — SAPTA CBT
**Computer Based Test & School Examination Management Platform**
**SMK Sapta Marga**

> Dokumen ini menjelaskan keseluruhan arsitektur, alur kerja, teknologi, keamanan, dan strategi pengujian sistem SAPTA CBT secara detail dan komprehensif dari ujung ke ujung (End-to-End). Ditujukan untuk Developer, AI Agent, dan pihak terkait yang ingin memahami, melanjutkan pengembangan, atau menyusun laporan teknis tentang sistem ini.

---

## DAFTAR ISI

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Teknologi yang Digunakan (Tech Stack)](#2-teknologi-yang-digunakan-tech-stack)
3. [Arsitektur Sistem](#3-arsitektur-sistem)
4. [Struktur Folder Proyek](#4-struktur-folder-proyek)
5. [Manajemen Peran dan Hak Akses (RBAC)](#5-manajemen-peran-dan-hak-akses-rbac)
6. [Alur Sistem End-to-End](#6-alur-sistem-end-to-end)
7. [Arsitektur Backend](#7-arsitektur-backend)
8. [Arsitektur Frontend](#8-arsitektur-frontend)
9. [Desain Database (ERD Konseptual)](#9-desain-database-erd-konseptual)
10. [Keamanan Sistem (Security)](#10-keamanan-sistem-security)
11. [Pengujian Perangkat Lunak (Software Testing)](#11-pengujian-perangkat-lunak-software-testing)
12. [Fitur Monitoring & Integritas Ujian](#12-fitur-monitoring--integritas-ujian)
13. [Ekspor & Pelaporan](#13-ekspor--pelaporan)
14. [Deployment & Pemeliharaan](#14-deployment--pemeliharaan)
15. [Panduan Instalasi](#15-panduan-instalasi)

---

## 1. Gambaran Umum Sistem

**SAPTA CBT** adalah platform ujian berbasis komputer (Computer Based Test) berbasis web yang dirancang khusus untuk mengelola seluruh siklus hidup ujian sekolah di SMK Sapta Marga, mulai dari:

**Persiapan → Pembuatan Soal → Konfigurasi Ujian → Penjadwalan → Pelaksanaan Ujian → Autosave/Recovery → Monitoring Real-time → Penilaian Otomatis & Manual → Analitik → Pelaporan → Arsip**

### Masalah yang Diselesaikan
- Menggantikan alur kerja ujian manual/terpencar menjadi satu platform terintegrasi.
- Guru dapat membuat dan mengelola soal sendiri tanpa membebani satu operator kurikulum.
- Administrator dan kurikulum tetap memegang kendali tata kelola ujian.
- Menjamin keamanan data ujian, integritas pelaksanaan, dan keandalan penyimpanan jawaban siswa.

---

## 2. Teknologi yang Digunakan (Tech Stack)

### 2.1 Backend (Server-Side)

| Komponen | Teknologi | Versi | Fungsi |
|---|---|---|---|
| Bahasa Pemrograman | **PHP** | >= 8.3 | Bahasa utama server |
| Framework | **Laravel** | 13.x | Kerangka kerja MVC utama |
| Database | **MySQL / MariaDB** | 8.0+ / 10.6+ | Penyimpanan data relasional |
| ORM | **Eloquent ORM** | (Bawaan Laravel) | Abstraksi interaksi database |
| Autentikasi | **Laravel Breeze** | 2.4 | Sistem login/registrasi |
| Otorisasi | **Spatie Laravel Permission** | 8.3 | Manajemen peran & hak akses (RBAC) |
| Antrian (Queue) | **Laravel Queue** | (Bawaan) | Proses latar belakang (backup, export) |
| WebSocket/Realtime | **Laravel Reverb** | * | Komunikasi real-time untuk monitoring |
| Ekspor Excel | **Maatwebsite Excel** | 4.0 | Ekspor laporan ke format .xlsx |
| Ekspor PDF | **Barryvdh DomPDF** | 3.1 | Ekspor laporan ke format .pdf |
| Backup | **Spatie Laravel Backup** | * | Pencadangan database & file |
| Log Viewer | **Opcodes Log Viewer** | * | Antarmuka visual untuk log server |
| Hashing Password | **Bcrypt** | 12 rounds | Enkripsi password satu arah |
| Session Driver | **Database** | — | Session disimpan di database (bukan file) |
| Cache Driver | **Database** | — | Cache disimpan di database |

### 2.2 Frontend (Client-Side)

| Komponen | Teknologi | Versi | Fungsi |
|---|---|---|---|
| Templating Engine | **Laravel Blade** | (Bawaan) | Render halaman HTML server-side |
| CSS Framework | **TailwindCSS** | 3.x | Utility-first CSS framework |
| JavaScript Framework | **Alpine.js** | 3.x | Interaktivitas ringan tanpa SPA |
| Build Tool | **Vite** | 8.x | Kompilasi & bundling aset frontend |
| PostCSS | **PostCSS** | 8.x | Transformasi CSS |
| Form Plugin | **@tailwindcss/forms** | 0.5 | Styling form elements |

### 2.3 Lingkungan Pengembangan

| Komponen | Teknologi |
|---|---|
| Server Lokal | **Laragon** (Windows) |
| Version Control | **Git** |
| Package Manager (PHP) | **Composer** 2.x |
| Package Manager (JS) | **npm** |
| Testing Framework | **PHPUnit** 12.x |
| Code Formatter | **Laravel Pint** |
| Bahasa Antarmuka | **Bahasa Indonesia** (`laravel-lang/common`) |

---

## 3. Arsitektur Sistem

### 3.1 Pola Arsitektur: MVC + Service Layer

```
┌─────────────────────────────────────────────────────────┐
│                      BROWSER (Client)                   │
│     Blade Templates + TailwindCSS + Alpine.js           │
└──────────────────────────┬──────────────────────────────┘
                           │ HTTP Request
                           ▼
┌─────────────────────────────────────────────────────────┐
│                   ROUTES (web.php)                       │
│              Middleware: auth, verified, CSRF            │
└──────────────────────────┬──────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│                     CONTROLLERS                          │
│  Menerima request, validasi input, memanggil service     │
└──────────────────────────┬──────────────────────────────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
┌──────────────────┐ ┌──────────┐ ┌──────────────┐
│    POLICIES      │ │ SERVICES │ │   EXPORTS    │
│ Otorisasi akses  │ │ Logika   │ │ Excel / PDF  │
│ per-resource     │ │ bisnis   │ │ Generator    │
└──────────────────┘ └────┬─────┘ └──────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              MODELS (Eloquent ORM)                       │
│     Relasi antar-tabel, accessor, mutator, casting       │
└──────────────────────────┬──────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────┐
│                    MySQL DATABASE                        │
│               39 tabel relasional aktif                  │
└─────────────────────────────────────────────────────────┘
```

### 3.2 Pola Penyimpanan Data

| Jenis Data | Lokasi Penyimpanan | Alasan |
|---|---|---|
| Data struktural (user, soal, ujian, nilai) | **Database MySQL** | Butuh relasi, pencarian, dan transaksi ACID |
| File media (gambar soal, foto profil, audio) | **File System** (`storage/app/`) | Performa tinggi, tidak membebani database |
| Log kesalahan sistem | **File** (`storage/logs/laravel.log`) | Tetap bisa mencatat walau database mati |
| Audit log aktivitas admin | **Database** (tabel `audit_logs`) | Butuh pencarian berdasar tanggal/aktor |
| Session login pengguna | **Database** (tabel `sessions`) | Konsisten antar-server |
| Cache aplikasi | **Database** (tabel `cache`) | Konsisten dan mudah di-flush |

---

## 4. Struktur Folder Proyek

```
cbt-sapmar/
├── app/
│   ├── Console/              # Perintah artisan kustom
│   ├── Events/               # Event broadcasting (real-time)
│   │   ├── ExamAttemptControlUpdated.php
│   │   ├── ExamAttemptPresenceUpdated.php
│   │   └── IntegrityEventRecorded.php
│   ├── Exports/              # Kelas ekspor Excel
│   │   ├── AttendanceExport.php
│   │   ├── ClassResultExport.php
│   │   └── ExamResultExport.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Master/       # CRUD data master (tahun ajaran, jurusan, kelas, mapel)
│   │       ├── User/         # CRUD pengguna (staf, guru, siswa, impor siswa)
│   │       ├── AnalyticsController.php
│   │       ├── AuditLogController.php
│   │       ├── DashboardController.php
│   │       ├── ExamAttemptController.php
│   │       ├── ExamBuilderController.php
│   │       ├── ExamMonitoringController.php
│   │       ├── ExamProctorControlController.php
│   │       ├── ExamProctorController.php
│   │       ├── ExamResultController.php
│   │       ├── ExportController.php
│   │       ├── IntegrityReviewController.php
│   │       ├── ManualGradingController.php
│   │       ├── MediaController.php
│   │       ├── PrintController.php
│   │       ├── ProfileController.php
│   │       ├── QuestionBankController.php
│   │       ├── QuestionController.php
│   │       └── SystemAdminController.php
│   ├── Models/               # 24 model Eloquent
│   ├── Policies/             # 8 policy otorisasi
│   ├── Providers/            # Service provider
│   ├── Services/             # Business logic layer
│   │   ├── AnalyticsService.php
│   │   ├── ExamAttemptService.php
│   │   ├── GradingService.php
│   │   ├── ManualGradingService.php
│   │   └── StudentImportService.php
│   └── View/                 # Blade component classes
├── database/
│   ├── migrations/           # 33 file migrasi
│   ├── seeders/              # Data awal (role, permission, sekolah)
│   └── factories/            # Factory untuk testing
├── resources/
│   ├── views/                # 15 folder Blade templates
│   ├── css/                  # File CSS utama
│   └── js/                   # File JavaScript utama
├── routes/
│   ├── web.php               # 80+ route web
│   └── auth.php              # Route autentikasi
├── tests/
│   ├── Feature/              # 14 test file (feature/integration)
│   └── Unit/                 # Unit test
├── docs/                     # Dokumentasi pengembang
├── public/                   # Aset publik & entry point
├── storage/                  # File upload, log, cache
├── config/                   # Konfigurasi Laravel
└── AGENTS.md                 # Instruksi untuk AI Agent
```

---

## 5. Manajemen Peran dan Hak Akses (RBAC)

Sistem menggunakan **Role-Based Access Control (RBAC)** melalui library `spatie/laravel-permission`. Otorisasi dilakukan di **3 lapisan**: Route Middleware → Controller Authorization → Blade Directive.

### 5.1 Daftar Peran (Roles)

| No | Peran | Kode | Deskripsi |
|---|---|---|---|
| 1 | **Super Admin** | `super_admin` | Kontrol penuh atas seluruh sistem. Memiliki akses (scope) penuh untuk mengelola, melihat, serta menyetujui (approve) hasil ujian sebelum ditampilkan di halaman siswa. Mampu melakukan audit log, pengaturan server, dan manajemen darurat. |
| 2 | **Kurikulum** | `kurikulum` | Tata kelola ujian akademik: perencanaan, penjadwalan, review/approval, monitoring, dan pelaporan. Memiliki semua hak kecuali audit log. |
| 3 | **Guru** | `guru` | Memiliki akses (scope) yang secara spesifik ditentukan oleh Kurikulum. Guru dapat membuat ujian, membuat bank soal, menilai essay, melihat hasil analitik, serta menyetujui (approve) nilai ujian yang **secara eksklusif merupakan scope mata pelajaran dan kelas miliknya saja**. *Guru tidak memiliki hak untuk mempublikasikan (publish) atau menjeda (pause) ujian.* |
| 4 | **Proktor / Pengawas** | `proktor` | Kontrol operasional ujian saja: memantau peserta aktif, menginvestigasi sinyal integritas, mengunci/membuka sesi, memperpanjang waktu. Tidak bisa mengedit soal atau konfigurasi sistem. |
| 5 | **Siswa** | `siswa` | Peserta ujian. Login → lihat ujian yang tersedia → validasi token → mengerjakan ujian → submit → lihat hasil (sesuai kebijakan review ujian). |

### 5.2 Daftar Hak Akses (Permissions) — Total: 32 Permission

**Pengguna:**
`users.view`, `users.create`, `users.update`, `users.delete`, `users.manage`, `roles.manage`

**Master Data Sekolah:**
`classes.manage`, `subjects.manage`, `majors.manage`, `academic_years.manage`, `students.manage`, `teachers.manage`

**Bank Soal:**
`question_banks.view`, `question_banks.create`, `question_banks.update`, `question_banks.archive`

**Soal:**
`questions.create`, `questions.update`, `questions.delete`, `questions.publish`, `questions.review`

**Ujian:**
`exams.create`, `exams.update`, `exams.schedule`, `exams.publish`, `exams.archive`, `exams.monitor`, `exams.lock`, `exams.unlock`, `exams.extend_time`

**Percobaan Ujian & Hasil:**
`attempts.reset`, `results.view`, `results.export`

**Penilaian & Analitik:**
`essay.grade`, `analytics.view`

**Keamanan:**
`audit_logs.view`, `system.settings.manage`

### 5.3 Matriks Akses Per Peran

| Permission | Super Admin | Kurikulum | Guru | Proktor | Siswa |
|---|:---:|:---:|:---:|:---:|:---:|
| Master Data (CRUD) | ✅ | ✅ | ❌ | ❌ | ❌ |
| Bank Soal (CRUD) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Soal (CRUD + Publish) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Ujian (CRUD) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Monitoring Ujian | ✅ | ✅ | ✅* | ✅** | ❌ |
| Lock/Unlock Sesi | ✅ | ✅ | ✅* | ✅** | ❌ |
| Perpanjangan Waktu | ✅ | ✅ | ❌ | ✅** | ❌ |
| Penilaian Esai | ✅ | ✅ | ✅* | ❌ | ❌ |
| Lihat Hasil | ✅ | ✅ | ✅* | ✅ | ✅ |
| Ekspor Laporan | ✅ | ✅ | ✅* | ❌ | ❌ |
| Analitik | ✅ | ✅ | ✅ | ❌ | ❌ |
| Audit Log | ✅ | ❌ | ❌ | ❌ | ❌ |
| Pengaturan Sistem | ✅ | ❌ | ❌ | ❌ | ❌ |

> *\* Guru hanya untuk ujian yang dibuatnya sendiri (`created_by = user_id`)*
> *\*\* Proktor hanya untuk ujian yang ditugaskan kepadanya (`proctor_exam_assignments`)*

---

## 6. Alur Sistem End-to-End

### 6.1 Alur Persiapan Ujian (Admin/Kurikulum/Guru)

```
1. Login Sistem
   │
2. Kelola Master Data
   ├── Tahun Ajaran
   ├── Semester
   ├── Jurusan / Program
   ├── Rombel / Kelas
   ├── Mata Pelajaran
   ├── Data Guru (+ Penugasan Mapel & Kelas)
   └── Data Siswa (+ Import Bulk Excel)
   │
3. Buat Bank Soal
   ├── Tentukan: Nama, Mapel, Kelas, Deskripsi
   └── Status: Aktif
   │
4. Buat Soal di Dalam Bank Soal
   ├── Tipe: Pilihan Ganda / PG Kompleks / Benar-Salah / Menjodohkan / Esai
   ├── Konten: Teks + Gambar (opsional)
   ├── Opsi Jawaban + Kunci Jawaban
   ├── Metadata: Tingkat Kesulitan, Bobot Nilai
   └── Lifecycle: DRAFT → PUBLISHED (via tombol Publish)
   │
5. Buat Ujian (Exam Builder)
   ├── Judul, Kode, Mata Pelajaran, Tipe Ujian
   ├── Jadwal: Tanggal Mulai & Selesai
   ├── Durasi (menit)
   ├── Token Akses (opsional)
   ├── Opsi: Acak Soal, Acak Opsi, Review Hasil
   ├── Pilih Soal dari Bank Soal
   ├── Tetapkan Peserta (per Kelas atau per Siswa)
   ├── Pengaturan Susulan (waktu khusus per siswa)
   └── Siswa Ekstra (tambah siswa di luar kelas)
   │
6. Publikasi Ujian
   ├── Status: DRAFT → PUBLISHED
   ├── Saat Published: Tombol Edit/Hapus pada manajemen peserta terkunci
   └── Fitur Pause/Unpause tersedia untuk kontrol darurat
```

### 6.2 Alur Pelaksanaan Ujian (Siswa)

```
1. Login ke Sistem
   │
2. Dashboard Siswa → Lihat Ujian yang Tersedia
   │
3. Klik "Mulai Ujian"
   │
4. Validasi Server-Side (Backend):
   ├── ✓ Autentikasi (sudah login?)
   ├── ✓ Kelayakan peserta (terdaftar di ujian ini?)
   ├── ✓ Validasi token (jika diperlukan)
   ├── ✓ Validasi jadwal (dalam rentang waktu?)
   ├── ✓ Cek sesi duplikat (sudah ada attempt aktif?)
   └── ✓ Buat snapshot soal (immutable copy)
   │
5. Masuk Sesi Ujian
   ├── Tampilan: Soal + Opsi + Navigator + Timer
   ├── Timer: Server-authoritative (deadline = start_at + duration)
   ├── Navigasi: Previous / Next / Question Navigator
   ├── Status jawaban: Belum dijawab / Sudah dijawab / Ragu-ragu
   ├── Autosave: Jawaban disimpan otomatis ke server
   │   ├── Debounce untuk teks (mencegah request berlebihan)
   │   ├── Indikator: "Menyimpan..." → "Tersimpan ✓" → "Gagal ✗"
   │   └── Recovery saat koneksi putus & reconnect
   │
6. Submit Ujian
   ├── Manual: Siswa klik "Submit" → Konfirmasi → Finalisasi
   └── Otomatis: Server auto-submit saat deadline terlewat
   │
7. Penilaian (Grading)
   ├── Otomatis: PG, PG Kompleks, Benar/Salah, Menjodohkan
   │   └── Source of Truth: Snapshot (bukan soal mutable!)
   ├── Manual: Esai → Guru/Admin menilai satu per satu
   └── Status: AUTO_GRADED → PARTIALLY_GRADED → GRADED
   │
8. Lihat Hasil (jika review_summary diaktifkan)
```

### 6.3 Alur Monitoring Real-time (Proktor/Admin)

```
1. Buka Halaman Monitoring Ujian
   │
2. Lihat Dashboard Real-time:
   ├── Daftar peserta aktif (online/offline)
   ├── Progress pengerjaan (% soal dijawab)
   ├── Status sesi (IN_PROGRESS / SUBMITTED)
   ├── Sinyal integritas (tab switch, focus loss, dll)
   │
3. Aksi Proktor:
   ├── Lock sesi siswa (kunci layar ujian)
   ├── Unlock sesi siswa
   ├── Perpanjang waktu (tambahan menit)
   ├── Force Submit (paksa submit jawaban)
   └── Review integritas (lihat detail pelanggaran)
```

---

## 7. Arsitektur Backend

### 7.1 Controllers (22 Controller)

| Controller | Tanggung Jawab |
|---|---|
| `DashboardController` | Render dashboard sesuai role (admin/guru/siswa) |
| `AcademicYearController` | CRUD Tahun Ajaran |
| `MajorController` | CRUD Jurusan |
| `SchoolClassController` | CRUD Kelas/Rombel |
| `SubjectController` | CRUD Mata Pelajaran |
| `StaffController` | CRUD Pengguna Staf (Admin/Kurikulum/Proktor) |
| `TeacherController` | CRUD Data Guru |
| `StudentController` | CRUD Data Siswa |
| `StudentImportController` | Import bulk siswa via Excel |
| `QuestionBankController` | CRUD Bank Soal |
| `QuestionController` | CRUD Soal + Publish/Draft/Preview/History |
| `ExamBuilderController` | CRUD Ujian + Peserta + Soal + Publish/Pause |
| `ExamAttemptController` | Start Attempt, Session, Save Answer, Submit, Heartbeat |
| `ExamMonitoringController` | Dashboard monitoring real-time |
| `ExamProctorController` | Penugasan proktor ke ujian |
| `ExamProctorControlController` | Lock/Unlock/ExtraTime/ForceSubmit |
| `IntegrityReviewController` | Review sinyal integritas + catatan |
| `ManualGradingController` | Penilaian esai manual |
| `ExamResultController` | Daftar hasil + publikasi nilai |
| `AnalyticsController` | Analitik ujian & butir soal |
| `ExportController` | Ekspor Excel & PDF |
| `PrintController` | Cetak kartu, absensi, berita acara |
| `MediaController` | Upload/serve/delete media (gambar & audio) |
| `AuditLogController` | Tampilkan audit log |
| `SystemAdminController` | Info server, maintenance mode, backup |
| `ProfileController` | Edit profil pengguna |

### 7.2 Service Layer (5 Service)

| Service | Tanggung Jawab |
|---|---|
| `ExamAttemptService` | Cek eligibilitas, start attempt, buat snapshot, submit, auto-submit |
| `GradingService` | Penilaian otomatis seluruh tipe soal objektif berdasarkan snapshot |
| `ManualGradingService` | Penilaian manual esai + update status grading |
| `AnalyticsService` | Kalkulasi statistik ujian, analisis butir soal, distribusi nilai |
| `StudentImportService` | Parsing, validasi, dan import bulk data siswa dari Excel |

### 7.3 Policy Layer (8 Policy)

| Policy | Melindungi |
|---|---|
| `ExamPolicy` | Ujian (view/create/update/delete/monitor/control/grade) |
| `ExamAttemptPolicy` | Percobaan ujian (start/view/submit) |
| `QuestionBankPolicy` | Bank soal (ownership check untuk Guru) |
| `QuestionPolicy` | Soal (ownership + status check) |
| `MediaPolicy` | File media (ownership + akses aman) |
| `MasterDataPolicy` | Data master sekolah |
| `UserPolicy` | Pengguna |
| `AuditLogPolicy` | Audit log (super_admin only) |

### 7.4 Event Broadcasting (3 Event)

| Event | Channel | Fungsi |
|---|---|---|
| `ExamAttemptPresenceUpdated` | `exam.{id}` | Update status online/offline peserta |
| `ExamAttemptControlUpdated` | `exam.{id}` | Notifikasi lock/unlock/force-submit |
| `IntegrityEventRecorded` | `exam.{id}` | Alert pelanggaran integritas real-time |

---

## 8. Arsitektur Frontend

### 8.1 Pendekatan: Server-Side Rendering (SSR) + Progressive Enhancement

Sistem **TIDAK** menggunakan Single Page Application (SPA). Setiap halaman di-render secara utuh oleh server menggunakan **Blade Templates**, lalu diperkaya dengan interaktivitas menggunakan **Alpine.js** untuk:
- Toggle sidebar/modal
- Live search dropdown
- Form dinamis (tambah/hapus opsi jawaban)
- Timer countdown ujian
- Autosave jawaban
- Notifikasi toast

### 8.2 Komponen UI

| Komponen | Teknologi | Contoh Penggunaan |
|---|---|---|
| Layout utama | Blade `<x-app-layout>` | Sidebar + navbar + konten |
| Sidebar navigasi | Blade partial + Alpine.js | Menu berdasarkan role |
| Tabel data | Blade + TailwindCSS | Daftar siswa, soal, ujian |
| Form input | `@tailwindcss/forms` | CRUD forms |
| Modal dialog | Alpine.js `x-data` | Konfirmasi, form popup |
| Toast notification | Alpine.js | Feedback operasi CRUD |
| Exam session | Alpine.js + Fetch API | Timer, navigator, autosave |
| Dashboard cards | TailwindCSS | Statistik ringkasan |
| Status badges | TailwindCSS | DRAFT/PUBLISHED/ONGOING/ENDED |

### 8.3 Desain Responsif

- **Mobile-first**: Antarmuka dirancang untuk layar kecil terlebih dahulu.
- **Breakpoints**: Mengikuti standar TailwindCSS (`sm`, `md`, `lg`, `xl`).
- **Sidebar**: Collapse otomatis pada layar kecil, toggle via hamburger menu.
- **Tabel**: Menggunakan overflow-scroll horizontal pada layar kecil.
- **Ujian Siswa**: Touch target besar, teks terbaca, kontrol tidak tumpang tindih.

---

## 9. Desain Database (ERD Konseptual)

### 9.1 Daftar Tabel — Total: 39 Tabel Aktif

**Identitas & Sekolah (12 tabel):**

| Tabel | Deskripsi |
|---|---|
| `users` | Akun pengguna (nama, username, email, password, avatar, status) |
| `roles` | Peran (super_admin, kurikulum, guru, proktor, siswa) |
| `permissions` | 32 hak akses |
| `model_has_roles` | Pivot: user ↔ role |
| `model_has_permissions` | Pivot: user ↔ permission langsung |
| `role_has_permissions` | Pivot: role ↔ permission |
| `academic_years` | Tahun ajaran |
| `semesters` | Semester |
| `majors` | Jurusan / Program Keahlian |
| `school_classes` | Rombongan Belajar / Kelas |
| `subjects` | Mata Pelajaran |
| `students` | Data siswa (NIS, kelas, user_id) |
| `teachers` | Data guru (NIP, user_id) |
| `teacher_subjects` | Penugasan guru ↔ mapel |
| `teacher_classes` | Penugasan guru ↔ kelas |

**Bank Soal (6 tabel):**

| Tabel | Deskripsi |
|---|---|
| `question_banks` | Bank soal (nama, mapel, kelas, status) |
| `questions` | Soal (tipe, konten, kesulitan, bobot, status, author) |
| `question_versions` | Versi historis soal (immutable record) |
| `question_options` | Opsi jawaban (teks, gambar, is_correct, urutan) |
| `media` | Metadata file media (path, tipe, ukuran, model relasi) |

**Ujian (4 tabel):**

| Tabel | Deskripsi |
|---|---|
| `exams` | Ujian (judul, mapel, jadwal, durasi, token, status, opsi) |
| `exam_participants` | Peserta ujian (per kelas atau per siswa, susulan) |
| `exam_questions` | Soal yang dipilih untuk ujian (urutan, bobot) |
| `proctor_exam_assignments` | Penugasan proktor ke ujian |

**Percobaan & Jawaban (6 tabel):**

| Tabel | Deskripsi |
|---|---|
| `exam_attempts` | Percobaan ujian (siswa, waktu mulai, deadline, status, skor) |
| `attempt_question_snapshots` | Snapshot immutable soal saat attempt dimulai |
| `attempt_option_snapshots` | Snapshot immutable opsi jawaban |
| `participant_answers` | Jawaban siswa (per soal, skor, status grading) |
| `exam_attempt_review_notes` | Catatan review integritas oleh proktor |
| `integrity_events` | Log peristiwa integritas (tab switch, focus loss, dll) |

**Infrastruktur (6 tabel):**

| Tabel | Deskripsi |
|---|---|
| `audit_logs` | Jejak audit aktivitas admin |
| `sessions` | Session login aktif |
| `cache` / `cache_locks` | Cache aplikasi |
| `jobs` / `job_batches` / `failed_jobs` | Antrian tugas latar belakang |
| `migrations` | Catatan migrasi database |
| `password_reset_tokens` | Token reset password |

### 9.2 Relasi Antar-Tabel (Kunci)

```
users ──┬── 1:1 ── students ── N:1 ── school_classes ── N:1 ── majors
        ├── 1:1 ── teachers ──┬── N:M ── subjects (via teacher_subjects)
        │                     └── N:M ── school_classes (via teacher_classes)
        └── 1:N ── exams (created_by)

question_banks ── 1:N ── questions ── 1:N ── question_options
                                   └── 1:N ── question_versions
                                   └── 1:N ── media

exams ──┬── 1:N ── exam_participants ──┬── N:1 ── school_classes
        │                              └── N:1 ── students
        ├── 1:N ── exam_questions ── N:1 ── questions
        ├── 1:N ── exam_attempts ──┬── 1:N ── attempt_question_snapshots
        │                         │           └── 1:N ── attempt_option_snapshots
        │                         ├── 1:N ── participant_answers
        │                         ├── 1:N ── integrity_events
        │                         └── 1:N ── exam_attempt_review_notes
        └── 1:N ── proctor_exam_assignments ── N:1 ── users (proktor)
```

### 9.3 Prinsip Integritas Data

1. **Snapshot Immutable**: Saat siswa memulai ujian, sistem membuat salinan permanen (*snapshot*) dari semua soal dan opsi. Perubahan soal setelah ujian dimulai **tidak akan mempengaruhi** ujian yang sedang berlangsung.
2. **Server-Authoritative Timer**: Deadline dihitung oleh server (`start_at + duration`). Frontend hanya menampilkan sisa waktu. Server menolak jawaban yang dikirim setelah deadline.
3. **Foreign Keys**: Semua relasi menggunakan foreign key constraint dengan cascading behavior yang disengaja.
4. **Indexes**: Kolom yang sering di-query (exam_id, student_id, status, timestamps) sudah diindeks.
5. **Transactions**: Operasi kritis (start attempt, submit, grading) menggunakan database transaction.

---

## 10. Keamanan Sistem (Security)

### 10.1 Lapisan Keamanan

```
┌─────────────────────────────────────────┐
│  Layer 1: TRANSPORT (HTTPS di produksi) │
├─────────────────────────────────────────┤
│  Layer 2: AUTENTIKASI (Laravel Breeze)  │
│  → Bcrypt 12 rounds, session-based      │
├─────────────────────────────────────────┤
│  Layer 3: OTORISASI (Spatie Permission) │
│  → Middleware + Policy + Gate           │
├─────────────────────────────────────────┤
│  Layer 4: VALIDASI INPUT                │
│  → Form Request + Server-side rules     │
├─────────────────────────────────────────┤
│  Layer 5: PERLINDUNGAN SERANGAN         │
│  → CSRF, XSS, SQL Injection, dll       │
├─────────────────────────────────────────┤
│  Layer 6: AUDIT & MONITORING            │
│  → Audit log, integrity events          │
└─────────────────────────────────────────┘
```

### 10.2 Proteksi Terhadap Serangan Umum

| Jenis Serangan | Mitigasi | Implementasi |
|---|---|---|
| **SQL Injection** | Parameterized queries | Eloquent ORM (otomatis) + prepared statements |
| **Cross-Site Scripting (XSS)** | Output escaping | Blade `{{ }}` auto-escape, `{!! !!}` hanya untuk trusted content |
| **Cross-Site Request Forgery (CSRF)** | Token CSRF | `@csrf` directive pada setiap form |
| **Brute Force Login** | Rate limiting | Laravel throttle middleware pada route login |
| **Password Theft** | Hashing satu arah | Bcrypt dengan 12 rounds (`BCRYPT_ROUNDS=12`) |
| **Session Hijacking** | Secure session | Session disimpan di database, `SESSION_ENCRYPT` tersedia |
| **Privilege Escalation** | Server-side authorization | Policy + middleware, **bukan hanya** hidden UI elements |
| **Path Traversal (File Upload)** | Safe filename + validation | `MediaController` generate nama file server-side |
| **Unauthorized Data Access** | Ownership checks | Policy memeriksa `created_by` untuk guru, `proctor_exam_assignments` untuk proktor |
| **Data Tampering (Ujian)** | Immutable snapshots | Snapshot soal tidak bisa diubah setelah attempt dimulai |
| **Double Submission** | Duplicate prevention | Cek existing attempt sebelum create baru (di `ExamAttemptService`) |

### 10.3 Keamanan File Upload

- ✅ Validasi ekstensi file (`.jpg`, `.png`, `.mp3`, dll)
- ✅ Validasi tipe file sebenarnya (MIME type)
- ✅ Batas ukuran file
- ✅ Nama file di-generate ulang oleh server (UUID/hash)
- ✅ File disimpan di luar webroot yang dapat dieksekusi
- ✅ Akses file melalui controller dengan pengecekan otorisasi

### 10.4 Keamanan Konfigurasi

- ✅ Password database tidak di-hardcode (menggunakan `.env`)
- ✅ `.env` tidak masuk ke Git (ada di `.gitignore`)
- ✅ `APP_DEBUG=false` wajib di produksi (mencegah error stack trace)
- ✅ `APP_KEY` unik per instalasi (untuk enkripsi)
- ✅ Audit log mencatat semua aksi sensitif admin

### 10.5 Audit Log

Setiap aksi sensitif dicatat di tabel `audit_logs` dengan informasi:

| Field | Deskripsi |
|---|---|
| `user_id` | Siapa yang melakukan |
| `action` | Apa yang dilakukan (create, update, delete, publish, dll) |
| `auditable_type` | Tipe entitas (Exam, Question, User, dll) |
| `auditable_id` | ID entitas target |
| `old_values` | Nilai sebelum perubahan (JSON) |
| `new_values` | Nilai setelah perubahan (JSON) |
| `ip_address` | Alamat IP pelaku |
| `user_agent` | Browser/device pelaku |
| `reason` | Alasan (untuk override darurat) |
| `outcome` | Hasil (success/fail) |

---

## 11. Pengujian Perangkat Lunak (Software Testing)

### 11.1 Jenis Pengujian yang Diterapkan

#### A. Black-Box Testing (Pengujian Kotak Hitam)

**Definisi:** Menguji sistem dari perspektif pengguna akhir tanpa melihat kode internal. Fokus pada **input → output** yang diharapkan.

| No | Skenario Uji | Input | Output yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | Login dengan kredensial valid | Username + password benar | Redirect ke dashboard sesuai role | ✅ |
| 2 | Login dengan kredensial salah | Password salah | Pesan error "Kredensial tidak valid" | ✅ |
| 3 | Akses halaman admin tanpa login | URL `/dashboard` langsung | Redirect ke halaman login | ✅ |
| 4 | Guru mengakses audit log | URL `/audit-logs` | Error 403 Forbidden | ✅ |
| 5 | Siswa mengakses halaman admin | URL `/users/staff` | Error 403 Forbidden | ✅ |
| 6 | Buat bank soal tanpa nama | Form kosong, submit | Pesan validasi "Nama harus diisi" | ✅ |
| 7 | Upload file selain gambar | File `.exe` pada form soal | Pesan error "Format file tidak didukung" | ✅ |
| 8 | Siswa submit ujian | Klik tombol Submit | Jawaban tersimpan, status berubah ke SUBMITTED | ✅ |
| 9 | Siswa akses ujian yang belum dimulai | Klik "Mulai" sebelum jadwal | Pesan error "Ujian belum dibuka" | ✅ |
| 10 | Import siswa via Excel | File Excel valid | Siswa berhasil ditambahkan ke database | ✅ |

#### B. White-Box Testing (Pengujian Kotak Putih)

**Definisi:** Menguji logika internal kode, alur kontrol, dan percabangan. Dilakukan menggunakan **PHPUnit** terhadap unit dan service.

**Test Files (14 file test):**

| File Test | Cakupan | Jumlah Skenario |
|---|---|---|
| `ObjectiveGradingTest.php` | Penilaian otomatis semua tipe soal objektif | ~50+ assertions |
| `ManualGradingTest.php` | Penilaian esai manual + update status | ~20+ assertions |
| `ExamBuilderTest.php` | CRUD ujian, peserta, soal, publish | ~15+ assertions |
| `AttemptFinalizationTest.php` | Submit, auto-submit, grading flow | ~15+ assertions |
| `QuestionBankTest.php` | CRUD bank soal + otorisasi | ~10+ assertions |
| `AnalyticsServiceTest.php` | Kalkulasi statistik, analisis butir soal | ~15+ assertions |
| `ExportControllerTest.php` | Generate Excel & PDF | ~10+ assertions |
| `MasterDataAuthorizationTest.php` | Otorisasi akses data master | ~5+ assertions |
| `SecurityHeadersTest.php` | Header keamanan HTTP | ~3+ assertions |
| `MediaTest.php` | Upload, serve, delete file media | ~8+ assertions |
| `StudentImportTest.php` | Import Excel + validasi | ~5+ assertions |
| `TeacherResultTest.php` | Akses guru ke hasil ujian | ~5+ assertions |
| `ProfileTest.php` | Edit profil, ganti password | ~8+ assertions |

**Contoh skenario white-box pada `GradingService`:**

```
gradeAttempt()
├── IF status != SUBMITTED/AUTO_SUBMITTED → throw exception
├── LOOP setiap question snapshot:
│   ├── IF tipe = multiple_choice → bandingkan option_id
│   ├── IF tipe = complex_choice → bandingkan set option_ids
│   ├── IF tipe = true_false → bandingkan boolean
│   ├── IF tipe = matching → bandingkan pairs
│   ├── IF tipe = essay → tandai NEEDS_MANUAL_GRADE
│   └── IF tidak ada jawaban → skor 0
├── Hitung total skor
├── IF ada esai → status = PARTIALLY_GRADED
├── IF semua otomatis → status = GRADED
└── Update attempt: total_score, max_score, grading_status
```

#### C. Grey-Box Testing (Pengujian Kotak Abu-abu)

**Definisi:** Kombinasi black-box dan white-box. Tester mengetahui sebagian struktur internal (seperti database dan API) tetapi menguji dari sisi pengguna.

| No | Skenario | Teknik | Validasi |
|---|---|---|---|
| 1 | Manipulasi ID di URL | Ubah `/exams/5` jadi `/exams/999` | Server return 404, bukan error 500 |
| 2 | Akses ujian orang lain | Ubah `attempt_id` di URL | Policy return 403 Forbidden |
| 3 | Submit setelah deadline | Kirim POST setelah waktu habis | Server tolak dengan error |
| 4 | Double submit | Klik submit 2x cepat | Hanya 1 submission yang tercatat |
| 5 | CSRF token removal | Hapus `_token` dari form | Error 419 (Session Expired) |
| 6 | SQL injection pada search | Input `'; DROP TABLE users;--` | Query aman, tidak ada data rusak |
| 7 | XSS pada nama soal | Input `<script>alert(1)</script>` | Teks di-escape, script tidak berjalan |
| 8 | Refresh saat ujian | Tekan F5/reload browser | Sesi ujian tetap berlanjut (autosave) |
| 9 | Network loss saat ujian | Putus koneksi 30 detik | Jawaban terakhir tetap tersimpan |
| 10 | Siswa akses route proktor | Hit `/exams/1/monitoring` | Error 403 karena policy |

### 11.2 Cara Menjalankan Test

```bash
# Jalankan seluruh test suite
php artisan test

# Jalankan test spesifik
php artisan test --filter=ObjectiveGradingTest

# Jalankan dengan coverage report
php artisan test --coverage
```

---

## 12. Fitur Monitoring & Integritas Ujian

### 12.1 Heartbeat / Presence

- Setiap 30 detik, browser siswa mengirim *heartbeat* ke server.
- Server mencatat `last_seen_at` pada `exam_attempts`.
- Jika tidak ada heartbeat > 60 detik, status berubah menjadi **offline**.

### 12.2 Sinyal Integritas (Anti-Cheat Signals)

| Sinyal | Deteksi | Aksi |
|---|---|---|
| Tab/window switch | `visibilitychange` event | Dicatat di `integrity_events` |
| Focus loss | `blur` event pada window | Dicatat di `integrity_events` |
| Fullscreen exit | `fullscreenchange` event | Dicatat di `integrity_events` |
| Copy/Paste attempt | `copy`/`paste` event | Deterrent (dicegah di UI) |
| Right-click | `contextmenu` event | Deterrent (dicegah di UI) |

> **PENTING**: Sistem **tidak secara otomatis** melabeli siswa sebagai "curang" berdasarkan satu sinyal browser. Sinyal disimpan untuk **review manusia** (proktor/admin).

### 12.3 Kontrol Proktor

| Aksi | Endpoint | Efek |
|---|---|---|
| Lock sesi | `POST /attempts/{id}/lock` | Siswa tidak bisa mengerjakan (layar terkunci) |
| Unlock sesi | `POST /attempts/{id}/unlock` | Sesi kembali normal |
| Tambah waktu | `POST /attempts/{id}/extra-time` | Deadline diperpanjang |
| Paksa submit | `POST /attempts/{id}/force-submit` | Jawaban terakhir di-submit, sesi berakhir |

---

## 13. Ekspor & Pelaporan

### 13.1 Format Ekspor

| Jenis Laporan | Excel (.xlsx) | PDF (.pdf) |
|---|:---:|:---:|
| Hasil ujian per ujian | ✅ | ✅ |
| Hasil ujian per kelas | ✅ | ✅ |
| Daftar hadir ujian | — | ✅ (cetak) |
| Kartu ujian siswa | — | ✅ (cetak) |
| Berita acara ujian | — | ✅ (cetak) |

### 13.2 Analitik Tersedia

- Skor rata-rata, median, tertinggi, terendah per ujian
- Distribusi nilai (histogram)
- Tingkat penyelesaian ujian
- Analisis butir soal (persentase benar/salah per soal)
- Distribusi jawaban per opsi (distractor analysis)
- Identifikasi soal bermasalah (terlalu mudah/sulit)

---

## 14. Deployment & Pemeliharaan

### 14.1 Prasyarat Server Produksi

| Komponen | Minimum |
|---|---|
| OS | Ubuntu Server 22.04+ / Windows Server |
| Web Server | Nginx atau Apache |
| PHP | >= 8.3 dengan ekstensi: ctype, curl, dom, fileinfo, gd, mbstring, openssl, pdo, tokenizer, xml |
| Database | MySQL 8.0+ atau MariaDB 10.6+ |
| Node.js | Untuk build aset (`npm run build`) |
| Disk | Minimal 10 GB (tergantung volume media) |
| RAM | Minimal 2 GB |

### 14.2 Checklist Produksi

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://domain-anda.com`
- [ ] HTTPS/SSL aktif
- [ ] `npm run build` (bukan `npm run dev`)
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] Database backup terjadwal
- [ ] Queue worker berjalan (`php artisan queue:work`)
- [ ] Log rotation aktif
- [ ] Firewall dikonfigurasi (port 80/443 saja)

### 14.3 Backup

Sistem menggunakan `spatie/laravel-backup` untuk:
- Backup otomatis database
- Backup file media
- Download backup via dashboard admin
- Perintah: `php artisan backup:run`

---

## 15. Panduan Instalasi

### Development (Lokal)

```bash
# 1. Clone repository
git clone <repository-url> cbt-sapmar
cd cbt-sapmar

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
# DB_DATABASE=cbt_sapmar
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Migrasi & seed
php artisan migrate:fresh --seed
php artisan storage:link

# 6. Jalankan
php artisan serve          # Terminal 1
npm run dev                # Terminal 2
```

### Akun Default (Seeder)

| Role | Username | Password |
|---|---|---|
| Super Admin | `admin` | `password` |
| Kurikulum | `kurikulum` | `password` |

> ⚠️ **WAJIB** ganti password default sebelum masuk ke produksi!

---

*Dokumen ini terakhir diperbarui pada September 2026.*
*Dibangun melalui kolaborasi berkelanjutan menggunakan pendekatan Agentic Development.*
