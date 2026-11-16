# Swimlane Activity Diagram — Alur Sistem CBT SAPMAR
*(Cross-Functional Process Flowchart Sesuai Skenario Sistem Ujian Online)*

Dokumen ini memodelkan seluruh skenario interaksi dan alur kerja Sistem CBT SAPMAR secara terstruktur dalam bentuk **Swimlane Diagram (Diagram Jalur Renang / Cross-Functional Flowchart)**, di mana setiap kolom (*lane*) merepresentasikan peran aktor dan sistem yang bertanggung jawab atas aktivitas tersebut.

---

## 1. Visualisasi Swimlane Diagram (Sesuai Format Referensi)

Diagram ini mengadopsi struktur visual sebagaimana gambar referensi:
- **Jalur Vertikal (Swimlanes):** Membagi tanggung jawab antara `Super Admin`, `Kurikulum`, `Guru`, `Siswa`, `Proktor / Pengawas`, dan `Sistem & Database CBT`.
- **Simbol Standar Flowchart:**
  - 🟢 **Kapsul / Oval:** Titik Mulai (*Start*) & Selesai (*End*).
  - 🟨 **Jajar Genjang (Parallelogram):** Aktivitas masukan manual / input data pengguna.
  - ⬜ **Persegi Panjang Bulat:** Proses sistem / formulir halaman.
  - 🔶 **Belah Ketupat (Diamond):** Titik keputusan / validasi kondisi.
  - 🛢️ **Silinder:** Penyimpanan & penarikan data ke/dari basis data.
  - 🔴 **Kotak Merah Peringatan:** Status pembekuan / pemblokiran akun siswa jika melanggar melebihi limit.

![Swimlane Diagram CBT SAPMAR](file:///c:/laragon/www/cbt-sapmar/public/docs/swimlane_cbt_workflow.png)

> 💡 **Tautan Berkas:**
> - Versi Gambar HD (PNG 2900x3850 px): [public/docs/swimlane_cbt_workflow.png](file:///c:/laragon/www/cbt-sapmar/public/docs/swimlane_cbt_workflow.png)
> - Viewer Interaktif Web (Zoom, Pan & Cetak): Buka browser di [http://127.0.0.1:8000/docs/swimlane.html](http://127.0.0.1:8000/docs/swimlane.html)

---

## 2. Diagram Alir Swimlane Berbasis Teks (Mermaid Syntax)

```mermaid
flowchart TD
    %% ==========================================
    %% SWIMLANES (SUBGRAPHS)
    %% ==========================================
    subgraph LANE_SA["👤 Super Admin"]
        SA1["Buatkan Akses Privat<br>Kurikulum / Admin"]
        SA2["Verifikasi Akun Google &<br>Kategorikan Role (Guru/Siswa/Proktor)"]
        SA3["Monitoring Evaluasi<br>& Arsip Hasil Akhir"]
    end

    subgraph LANE_KR["📋 Kurikulum"]
        KR1["Dashboard Kurikulum"]
        KR2[/"Input Master Data:<br>Tahun Ajaran, Jurusan, Rombel,<br>Mapel, Data Guru & Siswa"/]
        KR3{"Rombel Kelas<br>Terpenuhi?"}
        KR4["Cetak Kartu Ujian Siswa"]
        KR5["Cetak Berita Acara (BAP)<br>& Daftar Hadir Siswa"]
        KR6["Publikasi Ujian (ON GOING) &<br>Tugaskan Proktor Pengawas"]
        KR7["Lihat & Unduh Rekap Nilai<br>(Tervalidasi Guru)"]
        KR_END(["🏁 Selesai"])
    end

    subgraph LANE_GR["👨‍🏫 Guru"]
        GR1["Dashboard Guru Mapel"]
        GR2[/"Buat Bank Soal & Input Butir Soal<br>(PG, Kompleks, Esai, Rumus Math)"/]
        GR3[/"Manajemen Jadwal Ujian:<br>Durasi, Kelas, Jurusan (DRAFT)"/]
        GR4["Halaman Manajemen Laporan Hasil"]
        GR5{"Ada Butir<br>Soal Esai?"}
        GR6[/"Koreksi Manual Jawaban Esai<br>& Berikan Poin Nilai"/]
        GR7["Publikasi Nilai & Hasil Ujian ke Siswa"]
    end

    subgraph LANE_SW["👨‍🎓 Siswa"]
        SW_START(["🚀 Mulai"])
        SW1[/"Registrasi Akun Siswa<br>(Web / Google Auth)"/]
        SW2["Menerima Kartu Ujian<br>(Nomor & Sesi)"]
        SW3["Buka Broadcast Jadwal Ujian"]
        SW4[/"Input Kode Token Ujian &<br>Konfirmasi Mulai"/]
        SW5["Mengerjakan Soal Ujian<br>(PG, Kompleks, Esai)"]
        SW_POP["Muncul Pop-Up Peringatan<br>(Tersisa 1 - 5 Limit)"]
        SW_LOCK["🚫 AKSES TERBLOKIR / TERKUNCI<br>(Keluar dari Halaman Ujian)<br>Hubungi Proktor!"]
        SW6[/"Selesai Ujian & Submit Respon"/]
        SW7["Melihat Pengumuman Hasil & Nilai"]
    end

    subgraph LANE_PR["👁️ Proktor / Pengawas"]
        PR1[/"Registrasi Akun Proktor<br>(Web / Google Auth)"/]
        PR2["Menerima Berita Acara (BAP)<br>& Jadwal Pengawasan"]
        PR3["Rilis Kode Token Ujian di Ruangan"]
        PR4["Live Monitoring Dashboard Real-Time"]
        PR5["Manajemen Siswa Melanggar:<br>Verifikasi -> Buka Kunci / Tambah Waktu / Force Submit"]
    end

    subgraph LANE_SYS["🖥️ Sistem & Database CBT"]
        DB1[("Database:<br>users, roles")]
        DB2[("Database:<br>Master Data Akademik")]
        DB3[("Database:<br>question_banks, questions")]
        DB4[("Database:<br>exams (DRAFT/ON GOING)")]
        SYS_VAL_TKN{"Token & Waktu<br>Ujian Valid?"}
        SYS_AUTOSAVE["Sistem Autosave Respon Per Nomor"]
        SYS_CHEAT{"Terdeteksi<br>Pelanggaran Integritas?"}
        SYS_SENSOR["Sensor 8 Pelanggaran:<br>Tab Switch, Blur, Fullscreen, BeforeUnload,<br>Offline, Multi-Sesi, dll"]
        SYS_LIMIT{"Pelanggaran<br>> 5 Kali?"}
        DB5[("Database:<br>exam_attempts (LOCKED/UNLOCKED),<br>integrity_events")]
        SYS_AUTOGRADE["Auto-Grading Sistem<br>(Soal Pilihan Objektif)"]
        DB6[("Database:<br>Nilai Akhir Terpublikasi")]
    end

    %% ==========================================
    %% ALUR KONEKSI PROSES (FLOW EDGES)
    %% ==========================================
    %% Fase 1: Registrasi
    SW_START --> SW1
    SW1 -.-> DB1
    PR1 -.-> DB1
    SA1 --> DB1
    DB1 --> SA2
    SA2 --> DB1

    %% Fase 2: Master Data
    DB1 --> KR1
    KR1 --> KR2
    KR2 --> DB2
    KR2 --> KR3
    KR3 -- "Ya" --> KR4
    KR4 --> SW2

    %% Fase 3: Soal & Ujian
    DB2 --> GR1
    GR1 --> GR2
    GR2 --> DB3
    GR2 --> GR3
    GR3 --> DB4

    %% Fase 4: BAP & Publikasi
    DB4 --> KR5
    KR5 --> PR2
    KR5 --> KR6
    KR6 --> DB4

    %% Fase 5: Mulai Ujian & Token
    DB4 --> SW3
    PR2 --> PR3
    PR3 -.-> SW4
    SW3 --> SW4
    SW4 --> SYS_VAL_TKN
    SYS_VAL_TKN -- "Valid" --> SW5
    SW5 --> SYS_AUTOSAVE

    %% Fase 6: Anti-Cheat & 5x Limit
    SW5 -.-> SYS_CHEAT
    SYS_CHEAT -- "Ya" --> SYS_SENSOR
    SYS_SENSOR --> SYS_LIMIT
    SYS_LIMIT -- "Tidak (<= 5x)" --> SW_POP
    SW_POP --> SW5
    SYS_LIMIT -- "Ya (> 5x)" --> SW_LOCK
    SW_LOCK --> DB5

    %% Fase 7: Proctoring
    DB5 --> PR4
    PR4 --> PR5
    PR5 --> DB5
    DB5 -- "Buka Kunci" --> SW5

    %% Fase 8: Selesai & Koreksi
    SW5 --> SW6
    SW6 --> SYS_AUTOGRADE
    SYS_AUTOGRADE --> GR4
    GR4 --> GR5
    GR5 -- "Ya" --> GR6
    GR6 --> GR7
    GR5 -- "Tidak" --> GR7
    GR7 --> DB6
    DB6 --> SW7

    %% Fase 9: Monitoring Hasil Akhir
    DB6 --> KR7
    KR7 --> SA3
    KR7 --> KR_END
```

---

## 3. Rincian Penjelasan Skenario Alur Sistem per Fase

### 📌 Fase 1: Registrasi & Verifikasi Akun Pengguna
1. **Pendaftaran Mandiri:** Pengguna peran **Siswa**, **Guru**, dan **Proktor** melakukan pendaftaran akun melalui portal web atau **Google Auth** (Sign-in with Google).
2. **Akun Khusus Kurikulum / Admin:** Dibuatkan secara privat dan eksklusif oleh **Super Admin** melalui modul manajemen staf internal.
3. **Verifikasi & Kategorisasi Role:** Setiap pengguna yang mendaftar via Google Auth akan masuk ke daftar antrean verifikasi Super Admin untuk divalidasi keabsahannya dan ditentukan peran resminya (`Guru`, `Siswa`, atau `Proktor`). Akun terverifikasi disimpan pada tabel basis data `users` dan `roles`.

---

### 📌 Fase 2: Manajemen Master Data Akademik & Kartu Ujian (Kurikulum)
1. **Pengelolaan Data Sekolah:** Bagian **Kurikulum** mengelola data master mencakup:
   - Tahun Ajaran aktif & Semester
   - Kompetensi Keahlian / Jurusan
   - Rombongan Belajar (Rombel / Kelas)
   - Mata Pelajaran kurikulum
   - Plotting akun Guru ke mata pelajaran yang diampunya
   - Penempatan data Siswa ke rombel kelas masing-masing
2. **Pengecekan Kuota & Cetak Kartu Ujian:** Sistem mengecek kelengkapan data siswa dalam kelas. Jika rombel kelas telah terisi data siswa secara lengkap, Kurikulum mencetak **Kartu Peserta Ujian** (memuat foto, NISN, nomor peserta, dan sesi ujian) untuk dibagikan kepada siswa.

---

### 📌 Fase 3: Penyusunan Bank Soal & Manajemen Jadwal Ujian (Guru)
1. **Penyusunan Butir Pertanyaan:** Guru mengakses dashboard sesuai mata pelajarannya, membuat **Bank Soal**, dan menyusun butir-butir soal mencakup:
   - Pilihan Ganda (satu jawaban)
   - Pilihan Ganda Kompleks (banyak jawaban)
   - Benar / Salah
   - Menjodohkan
   - Isian Singkat
   - Uraian / Esai
   - Editor Rumus Matematika (KaTeX / MathQuill) & berkas media (gambar/audio).
2. **Penyusunan Paket Ujian:** Guru membuat konfigurasi ujian per mata pelajaran:
   - Alokasi waktu / durasi (menit)
   - Sasaran tingkatan kelas dan jurusan
   - Pengacakan urutan soal & pengacakan opsi
3. **Status Awal:** Ujian yang baru dibuat berstatus **`DRAFT`** (belum disiarkan/dapat diakses siswa).

---

### 📌 Fase 4: Administrasi Ujian, Penugasan Proktor & Publikasi (Kurikulum)
1. **Cetak Dokumen Pengawasan:** Begitu ujian berstatus `DRAFT` diajukan guru, Kurikulum dapat mencetak berkas fisik administratif:
   - **Berita Acara Pelaksanaan (BAP)** untuk Proktor/Pengawas ruangan
   - **Daftar Hadir Peserta Ujian** per rombel
2. **Publikasi Ujian & Plotting Proktor:** Kurikulum menugaskan Proktor/Pengawas pada ujian tersebut dan mengubah status siaran ujian menjadi **`PUBLISHED` / `ON GOING`** sesuai jadwal yang telah ditentukan.

---

### 📌 Fase 5: Pelaksanaan Ujian, Kode Token & Autosave (Siswa)
1. **Akses Siaran Ujian:** Siswa membuka antarmuka ujian pada gawai masing-masing. Ujian yang telah dijadwalkan akan muncul dalam status terbuka.
2. **Input Token Ujian:** Saat ujian dibuka, Proktor merilis dan membagikan **Kode Token Ujian** di ruangan. Siswa memasukkan token untuk membuka paket soal.
3. **Pengerjaan & Autosave:** Siswa mengerjakan soal secara interaktif. Setiap pemilihan opsi atau pengetikan jawaban langsung diamankan melalui mekanisme **Autosave** ke server secara real-time.

---

### 📌 Fase 6: Engine Integritas & Deteksi Pelanggaran (Batas Limit 5x)
Selama siswa mengerjakan ujian, sistem menjalankan sensor pemantau integritas di latar belakang yang mendeteksi 8 jenis pelanggaran keamanan:
1. **Perubahan visibilitas halaman** (*visibility change / tab switch*)
2. **Kehilangan fokus jendela browser** (*window blur*)
3. **Keluar atau masuk mode layar penuh** (*fullscreen exit/enter*)
4. **Upaya penutupan atau navigasi halaman** (*beforeunload / page lifecycle*)
5. **Koneksi jaringan terputus** (*network offline / reconnect latency*)
6. **Latensi penyambungan kembali yang mencurigakan**
7. **Penyegaran halaman berulang yang tidak normal** (*abnormal reload frequency*)
8. **Deteksi multi-sesi aktif secara bersamaan** (*concurrent session detection*)

* **Aturan Peringatan:** Setiap terjadi pelanggaran, muncul pop-up peringatan di layar siswa yang menginformasikan sisa toleransi pelanggaran.
* **Batas Maksimal (Limit 5 Kali):** Toleransi pelanggaran dibatasi maksimal **5 kali**.
* **Blokir Otomatis (> 5 Kali):** Jika siswa melanggar lebih dari 5 kali, sesi ujian otomatis **TERBLOKIR / TERKUNCI (*LOCKED*)**, siswa dikeluarkan dari halaman soal, dan diarahkan melapor kepada Pengawas/Proktor.

---

### 📌 Fase 7: Monitoring Real-Time & Intervensi Pengawas (Proktor)
1. **Pemantauan Live:** Proktor memantau dasbor pengawasan secara real-time, melihat status setiap peserta (*Mengerjakan*, *Selesai*, *Koneksi Offline*, atau *Terkunci/Melanggar*).
2. **Penanganan Siswa Terkunci:** Ketika siswa mendatangi proktor akibat terblokir, proktor melakukan verifikasi insiden pada riwayat pelanggaran sistem.
3. **Tindakan Kontrol Proktor:** Proktor berwenang melakukan:
   - **Buka Kunci (*Unlock*):** Mengizinkan siswa melanjutkan ujian.
   - **Tambah Waktu (*Extra Time*):** Kompensasi waktu akibat kendala teknis.
   - **Paksa Selesai (*Force Submit*):** Menyelesaikan paksa ujian siswa jika terbukti melakukan pelanggaran berat.

---

### 📌 Fase 8: Penyelesaian, Evaluasi Nilai & Koreksi Esai (Guru)
1. **Finalisasi / Submit:** Ujian selesai saat siswa menekan tombol submit atau saat batas waktu hitung mundur habis (*auto-submit*).
2. **Penilaian Otomatis (Auto-Grading):** Sistem langsung mengoreksi seluruh soal objektif (Pilihan Ganda, Benar/Salah, Menjodohkan).
3. **Koreksi Esai Manual oleh Guru:**
   - Guru membuka menu **Laporan Hasil Ujian** untuk memeriksa lembar jawaban siswa.
   - Jika terdapat butir pertanyaan esai, guru mengoreksi dan memberikan skor angka secara manual.
4. **Publikasi Hasil:** Setelah seluruh nilai tervalidasi lengkap, guru menekan tombol **Publikasi Nilai**, sehingga siswa dapat melihat skor akhir ujian pada akun masing-masing.

---

### 📌 Fase 9: Monitoring & Rekapitulasi Akhir (Kurikulum & Admin)
1. **Hak Akses Nilai:** Bagian Kurikulum dan Admin hanya dapat melihat rekapitulasi nilai siswa yang **telah tervalidasi dan dipublikasikan secara resmi oleh Guru mata pelajaran**.
2. **Ekspor & Arsip:** Kurikulum dapat mengunduh rekapitulasi nilai akhir dalam format Excel maupun mencetak laporan PDF untuk keperluan arsip kelulusan atau rapor sekolah.

