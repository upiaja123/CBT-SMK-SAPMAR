# Data Flow Diagram (DFD) Level 1 — Sistem SAPTA CBT
*(Format Aliran Data Vertikal / Lifeline Sequence Matrix — Sesuai Referensi)*

Dokumen ini memodelkan aliran data fungsional dari Sistem Computer Based Test (**SAPTA CBT**) menggunakan format **Lifeline Matrix / Vertical Sequence Data Flow Diagram** sebagaimana standar teknis akademik yang menyandingkan:
1. **Entitas Eksternal (Aktor)** di kolom kiri (dengan ikon figur orang)
2. **Data Stores (Basis Data)** di kolom tengah (dengan ikon silinder basis data `D1` – `D6`)
3. **Proses Sistem (Sub-Proses)** di kolom kanan (dengan ikon tombol proses `P1` – `P6`)
4. **Garis Pandu Vertikal (Lifeline)** yang menghubungkan aksi dari atas ke bawah secara kronologis

---

## 1. Gambar Diagram DFD Level 1

Diagram telah di-render dalam resolusi tinggi (High Definition) dan dapat dilihat langsung atau diunduh:

![DFD Level 1 Lifeline SAPTA CBT](file:///c:/laragon/www/cbt-sapmar/public/docs/dfd_level_1_reference.png)

> 💡 **File Tersedia:**
> - Versi Gambar HD (PNG 2200x1816 px): [public/docs/dfd_level_1_reference.png](file:///c:/laragon/www/cbt-sapmar/public/docs/dfd_level_1_reference.png)
> - Versi Vektor Skalabel (SVG): [public/docs/dfd_level_1_reference.svg](file:///c:/laragon/www/cbt-sapmar/public/docs/dfd_level_1_reference.svg)
> - Viewer Interaktif di Web (Zoom, Pan & Unduh): Buka browser di [http://127.0.0.1:8000/docs/dfd_level_1.html](http://127.0.0.1:8000/docs/dfd_level_1.html)

---

## 2. Render Diagram Berbasis Teks (Mermaid Format)

```mermaid
sequenceDiagram
    autonumber
    actor Admin as 👤 Admin / Kurikulum
    actor Guru as 👨‍🏫 Guru / Penguji
    actor Siswa as 👨‍🎓 Siswa / Peserta
    actor Proktor as 👁️ Proktor / Pengawas
    
    participant D1 as [(D1: Users / Akun)]
    participant D2 as [(D2: Master Data)]
    participant D3 as [(D3: Bank Soal)]
    participant D4 as [(D4: Ujian & Peserta)]
    participant D5 as [(D5: Sesi & Jawaban)]
    participant D6 as [(D6: Log Integritas)]
    
    participant P1 as [P1: Auth & Akun]
    participant P2 as [P2: Master & Soal]
    participant P3 as [P3: Jadwal Ujian]
    participant P4 as [P4: Exam Engine]
    participant P5 as [P5: Monitoring]
    participant P6 as [P6: Nilai & Laporan]

    %% 1. Autentikasi & Akun
    Admin->>P1: Input Kredensial Login Admin
    Guru->>P1: Input Kredensial Login Guru
    Siswa->>P1: Input Kredensial Login Siswa
    Proktor->>P1: Input Kredensial Login Proktor
    P1-->>D1: Baca / Validasi & Simpan Sesi User & Role
    P1-->>Admin: Status Sesi & Hak Akses Pengguna

    %% 2. Master Data & Bank Soal
    Admin->>P2: Input Master Data Akademik (Tahun Ajaran, Kelas, Mapel)
    P2-->>D2: Simpan / Update Master Data Akademik
    Guru->>P2: Input Butir Soal, Opsi, Rumus Math & Media
    P2-->>D3: Simpan Bank Soal, Opsi & Kunci Jawaban

    %% 3. Penjadwalan Ujian
    Admin->>P3: Konfigurasi Ujian (Durasi, Jadwal, Token, Acak, Rilis)
    Guru->>P3: Pilih Paket Soal & Parameter Bobot
    P3-->>D3: Ambil Butir Soal Terpilih
    P3-->>D1: Validasi Daftar Siswa & Assignment Proktor
    P3-->>D4: Simpan Paket Ujian & Peserta Terjadwal

    %% 4. Pelaksanaan Ujian (CBT Engine)
    Siswa->>P4: Input Token Ujian & Konfirmasi Mulai
    P4-->>D4: Validasi Token, Jadwal & Kelayakan Siswa
    P4-->>Siswa: Distribusi Snapshot Soal Teracak & Timer Ujian
    Siswa->>P4: Kirim Jawaban Siswa (Autosave) & Heartbeat Sesi
    P4-->>D5: Simpan Jawaban & Sesi Attempt Siswa
    Siswa->>P4: Deteksi Event Integritas (Tab Switch / Blur Window)
    P4-->>D6: Catat Log Pelanggaran Layar & Kecurangan

    %% 5. Monitoring & Integritas Real-Time
    Proktor->>P5: Pantau Live Status Sesi Ujian & Alert Integritas
    P5-->>D5: Ambil Telemetri Status Peserta Real-Time
    P5-->>D6: Ambil Feed Insiden Pelanggaran Siswa
    Proktor->>P5: Aksi Kontrol (Lock, Unlock, Force Submit, Tambah Waktu)
    P5-->>D5: Update Status Sesi & Catatan Review Integritas

    %% 6. Penilaian & Pelaporan
    Guru->>P6: Koreksi Nilai Esai Manual & Feedback
    P6-->>D5: Hitung & Simpan Nilai Akhir (Objektif + Esai)
    P6-->>Siswa: Publikasi Nilai & Hasil Ujian Siswa
    Admin->>P6: Request Dokumen (Kartu Peserta, Daftar Hadir, BAP)
    P6-->>D1: Ambil Data Siswa, Pengawas & Akademik
    P6-->>D5: Ambil Rekapitulasi Nilai & Analisis Butir Soal
    P6-->>Admin: Ekspor Dokumen Administrasi & Rekap Nilai (PDF/Excel)
```

---

## 3. Matriks Komponen Diagram

### A. Kolom Entitas Eksternal (Aktor)
1. **Admin / Kurikulum**: Mengatur akun sistem, struktur sekolah/akademik, penerbitan ujian, dan rekapitulasi berkas akhir.
2. **Guru / Penguji**: Menyusun butir pertanyaan (rumus matematika, opsi, kunci), memilih soal untuk ujian, dan menilai esai.
3. **Siswa / Peserta**: Mengikuti ujian dengan token, mengirimkan autosave jawaban, mengirim status heartbeat, dan melihat nilai.
4. **Proktor / Pengawas**: Memantau aktivitas peserta di ruang ujian secara live, mengatasi insiden kecurangan, dan mengontrol sesi (lock/unlock).

### B. Kolom Data Stores (Basis Data)
* **D1 (Users / Akun)**: Tabel `users`, `students`, `teachers`, `roles`, `permissions`.
* **D2 (Master Data)**: Tabel `academic_years`, `majors`, `school_classes`, `subjects`.
* **D3 (Bank Soal)**: Tabel `question_banks`, `questions`, `question_options`, `media`.
* **D4 (Ujian & Peserta)**: Tabel `exams`, `exam_questions`, `exam_participants`, `proctor_exam_assignments`.
* **D5 (Sesi & Jawaban)**: Tabel `exam_attempts`, `attempt_question_snapshots`, `participant_answers`.
* **D6 (Log Integritas)**: Tabel `integrity_events`, `exam_attempt_review_notes`, `audit_logs`.

### C. Kolom Sub-Proses Sistem (P1 – P6)
* **P1 (Autentikasi & Akun)**: Layanan login multi-role, validasi session token, dan verifikasi akun.
* **P2 (Master Data & Bank Soal)**: Layanan CRUD master data akademik, editor butir soal kaya format/formula matematika, dan arsip media.
* **P3 (Penjadwalan & Konfigurasi Ujian)**: Pengaturan token, alokasi waktu, penugasan rombel/siswa susulan, dan pengacakan soal.
* **P4 (Pelaksanaan Ujian / CBT Engine)**: Engine ujian real-time, pengacakan snapshot, autosave jawaban per butir, countdown timer, dan deteksi kecurangan.
* **P5 (Monitoring & Integritas Real-Time)**: Dashboard proctoring live status peserta, feed alert pelanggaran layar, dan perintah pengawas (lock/unlock/force-submit).
* **P6 (Penilaian & Pelaporan)**: Auto-grading soal pilihan, modul koreksi esai guru, ekspor rekap nilai Excel/PDF, cetak Kartu Ujian, Daftar Hadir, dan Berita Acara Pelaksanaan (BAP).
