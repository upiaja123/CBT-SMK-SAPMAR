import os
from PIL import Image, ImageDraw, ImageFont

# -------------------------------------------------------------
# Configuration & Dimensions
# -------------------------------------------------------------
WIDTH = 2900
HEIGHT = 3850
BG_COLOR = (255, 255, 255)

# Swimlane column layout
PHASE_COL_W = 70
LANE_NAMES = [
    "Super Admin",
    "Kurikulum",
    "Guru",
    "Siswa",
    "Proktor / Pengawas",
    "Sistem & Database CBT"
]
NUM_LANES = len(LANE_NAMES)
LANE_W = (WIDTH - PHASE_COL_W - 80) // NUM_LANES
LANE_LEFT_OFFSET = PHASE_COL_W + 40

LANES = {}
for i, name in enumerate(LANE_NAMES):
    lx = LANE_LEFT_OFFSET + (i * LANE_W)
    LANES[name] = {
        "index": i,
        "left": lx,
        "right": lx + LANE_W,
        "center": lx + LANE_W // 2,
        "name": name
    }

# Colors
BORDER_DARK = (30, 41, 59)
LINE_COLOR = (71, 85, 105)
GRID_LINE = (203, 213, 225)
ARROW_COLOR = (30, 41, 59)

NODE_FILL_EVENT = (237, 242, 247)      # Start/End
NODE_FILL_INPUT = (254, 249, 195)      # Parallelogram (Input)
NODE_FILL_PROC = (238, 242, 255)       # Rectangle (Process)
NODE_FILL_DECISION = (255, 237, 213)   # Diamond (Decision)
NODE_FILL_ALERT = (254, 226, 226)      # Red alert (Terblokir)
NODE_FILL_DB = (254, 252, 232)         # Cylinder (DB)
NODE_FILL_PAGE = (241, 245, 249)       # View/Form

STROKE_DEFAULT = (51, 65, 85)
STROKE_ALERT = (220, 38, 38)
STROKE_DB = (202, 138, 4)

font_bold_path = "C:/Windows/Fonts/segoeuib.ttf"
font_reg_path = "C:/Windows/Fonts/segoeui.ttf"

font_title = ImageFont.truetype(font_bold_path, 20)
font_subtitle = ImageFont.truetype(font_reg_path, 12)
font_lane_header = ImageFont.truetype(font_bold_path, 14)
font_phase = ImageFont.truetype(font_bold_path, 12)
font_node_bold = ImageFont.truetype(font_bold_path, 11)
font_node = ImageFont.truetype(font_reg_path, 10)
font_flow = ImageFont.truetype(font_bold_path, 10)

img = Image.new("RGB", (WIDTH, HEIGHT), BG_COLOR)
draw = ImageDraw.Draw(img)

# -------------------------------------------------------------
# 1. Main Title & Outer Table Frame
# -------------------------------------------------------------
HEADER_TOP_Y = 100
CONTENT_START_Y = 160
CONTENT_END_Y = HEIGHT - 80

# Main Header Title Block
draw.rectangle([(40, 30), (WIDTH - 40, 90)], fill=(248, 250, 252), outline=BORDER_DARK, width=2)
draw.text((60, 42), "ALUR SISTEM CBT SAPMAR YANG AKAN DIKEMBANGKAN", fill=(15, 23, 42), font=font_title)
draw.text((60, 68), "Swimlane Activity Diagram Skenario Komprehensif: Registrasi, Master Data, Ujian, Anti-Cheating & Laporan", fill=(100, 116, 139), font=font_subtitle)

# Outer Frame
draw.rectangle([(40, HEADER_TOP_Y), (WIDTH - 40, CONTENT_END_Y)], outline=BORDER_DARK, width=2)

# Left Phase Column
draw.line([(PHASE_COL_W + 40, HEADER_TOP_Y), (PHASE_COL_W + 40, CONTENT_END_Y)], fill=BORDER_DARK, width=2)
# Draw "Phase" vertical label
draw.text((50, CONTENT_START_Y + 10), "F\nA\nS\nE", fill=(100, 116, 139), font=font_phase, spacing=10)

# Lane Dividers and Headers
for i, name in enumerate(LANE_NAMES):
    lane = LANES[name]
    # Header box
    draw.rectangle([(lane["left"], HEADER_TOP_Y), (lane["right"], CONTENT_START_Y)], fill=(241, 245, 249), outline=BORDER_DARK, width=1)
    bbox = font_lane_header.getbbox(name)
    lw = bbox[2] - bbox[0]
    draw.text((lane["center"] - lw // 2, HEADER_TOP_Y + 20), name, fill=(15, 23, 42), font=font_lane_header)
    
    # Vertical line dividing lanes
    if i > 0:
        draw.line([(lane["left"], HEADER_TOP_Y), (lane["left"], CONTENT_END_Y)], fill=BORDER_DARK, width=1)

# Right border line
draw.line([(WIDTH - 40, HEADER_TOP_Y), (WIDTH - 40, CONTENT_END_Y)], fill=BORDER_DARK, width=2)

# -------------------------------------------------------------
# Drawing Helpers for Standard Flowchart Symbols
# -------------------------------------------------------------
def draw_terminator(draw, cx, cy, w, h, text):
    draw.rounded_rectangle([(cx - w//2, cy - h//2), (cx + w//2, cy + h//2)], radius=h//2, fill=NODE_FILL_EVENT, outline=STROKE_DEFAULT, width=2)
    lines = text.split("\n")
    ty = cy - (len(lines)*14)//2
    for line in lines:
        bbox = font_node_bold.getbbox(line)
        lw = bbox[2] - bbox[0]
        draw.text((cx - lw//2, ty), line, fill=(15, 23, 42), font=font_node_bold)
        ty += 14

def draw_parallelogram(draw, cx, cy, w, h, text, is_bold=False):
    offset = 14
    pts = [
        (cx - w//2 + offset, cy - h//2),
        (cx + w//2, cy - h//2),
        (cx + w//2 - offset, cy + h//2),
        (cx - w//2, cy + h//2)
    ]
    draw.polygon(pts, fill=NODE_FILL_INPUT, outline=STROKE_DEFAULT)
    draw.line(pts + [pts[0]], fill=STROKE_DEFAULT, width=2)
    font_used = font_node_bold if is_bold else font_node
    lines = text.split("\n")
    ty = cy - (len(lines)*13)//2
    for line in lines:
        bbox = font_used.getbbox(line)
        lw = bbox[2] - bbox[0]
        draw.text((cx - lw//2, ty), line, fill=(15, 23, 42), font=font_used)
        ty += 13

def draw_rect(draw, cx, cy, w, h, text, is_alert=False):
    fill = NODE_FILL_ALERT if is_alert else NODE_FILL_PROC
    stroke = STROKE_ALERT if is_alert else STROKE_DEFAULT
    draw.rounded_rectangle([(cx - w//2, cy - h//2), (cx + w//2, cy + h//2)], radius=6, fill=fill, outline=stroke, width=2)
    lines = text.split("\n")
    ty = cy - (len(lines)*13)//2
    for line in lines:
        bbox = font_node_bold.getbbox(line)
        lw = bbox[2] - bbox[0]
        draw.text((cx - lw//2, ty), line, fill=(15, 23, 42), font=font_node_bold)
        ty += 13

def draw_oval_page(draw, cx, cy, w, h, text):
    draw.ellipse([(cx - w//2, cy - h//2), (cx + w//2, cy + h//2)], fill=NODE_FILL_PAGE, outline=STROKE_DEFAULT, width=2)
    lines = text.split("\n")
    ty = cy - (len(lines)*13)//2
    for line in lines:
        bbox = font_node.getbbox(line)
        lw = bbox[2] - bbox[0]
        draw.text((cx - lw//2, ty), line, fill=(15, 23, 42), font=font_node)
        ty += 13

def draw_diamond(draw, cx, cy, w, h, text):
    pts = [
        (cx, cy - h//2),
        (cx + w//2, cy),
        (cx, cy + h//2),
        (cx - w//2, cy)
    ]
    draw.polygon(pts, fill=NODE_FILL_DECISION, outline=STROKE_DEFAULT)
    draw.line(pts + [pts[0]], fill=STROKE_DEFAULT, width=2)
    lines = text.split("\n")
    ty = cy - (len(lines)*13)//2
    for line in lines:
        bbox = font_node_bold.getbbox(line)
        lw = bbox[2] - bbox[0]
        draw.text((cx - lw//2, ty), line, fill=(15, 23, 42), font=font_node_bold)
        ty += 13

def draw_cylinder(draw, cx, cy, w, h, text):
    left = cx - w // 2
    right = cx + w // 2
    top = cy - h // 2
    bottom = cy + h // 2
    
    draw.rectangle([(left, top + 10), (right, bottom - 10)], fill=NODE_FILL_DB, outline=None)
    draw.line([(left, top + 10), (left, bottom - 10)], fill=STROKE_DB, width=2)
    draw.line([(right, top + 10), (right, bottom - 10)], fill=STROKE_DB, width=2)
    draw.ellipse([(left, bottom - 20), (right, bottom)], fill=NODE_FILL_DB, outline=STROKE_DB, width=2)
    draw.ellipse([(left, top), (right, top + 20)], fill=NODE_FILL_DB, outline=STROKE_DB, width=2)
    
    lines = text.split("\n")
    ty = cy - (len(lines)*13)//2 + 4
    for line in lines:
        bbox = font_node_bold.getbbox(line)
        lw = bbox[2] - bbox[0]
        draw.text((cx - lw//2, ty), line, fill=(113, 63, 18), font=font_node_bold)
        ty += 13

def draw_arrow(draw, x1, y1, x2, y2, label=None, label_side="top"):
    draw.line([(x1, y1), (x2, y2)], fill=ARROW_COLOR, width=2)
    # Arrow head
    size = 5
    if x2 > x1 and y1 == y2:
        draw.polygon([(x2, y2), (x2 - size*2, y2 - size), (x2 - size*2, y2 + size)], fill=ARROW_COLOR)
    elif x2 < x1 and y1 == y2:
        draw.polygon([(x2, y2), (x2 + size*2, y2 - size), (x2 + size*2, y2 + size)], fill=ARROW_COLOR)
    elif y2 > y1 and x1 == x2:
        draw.polygon([(x2, y2), (x2 - size, y2 - size*2), (x2 + size, y2 - size*2)], fill=ARROW_COLOR)
    elif y2 < y1 and x1 == x2:
        draw.polygon([(x2, y2), (x2 - size, y2 + size*2), (x2 + size, y2 + size*2)], fill=ARROW_COLOR)
    
    if label:
        bbox = font_flow.getbbox(label)
        lw = bbox[2] - bbox[0]
        lh = bbox[3] - bbox[1]
        mid_x = (x1 + x2) // 2
        mid_y = (y1 + y2) // 2
        if label_side == "top":
            draw.rectangle([(mid_x - lw//2 - 2, mid_y - 14), (mid_x + lw//2 + 2, mid_y - 2)], fill=(255, 255, 255))
            draw.text((mid_x - lw//2, mid_y - 14), label, fill=(30, 41, 59), font=font_flow)
        elif label_side == "right":
            draw.rectangle([(mid_x + 4, mid_y - lh//2 - 1), (mid_x + lw + 8, mid_y + lh//2 + 2)], fill=(255, 255, 255))
            draw.text((mid_x + 6, mid_y - lh//2), label, fill=(30, 41, 59), font=font_flow)

# -------------------------------------------------------------
# Layout of Nodes by Row (Y Coordinate)
# -------------------------------------------------------------
SA_X = LANES["Super Admin"]["center"]
KR_X = LANES["Kurikulum"]["center"]
GR_X = LANES["Guru"]["center"]
SW_X = LANES["Siswa"]["center"]
PR_X = LANES["Proktor / Pengawas"]["center"]
SYS_X = LANES["Sistem & Database CBT"]["center"]

# PHASE 1: Registrasi & Verifikasi
draw_terminator(draw, SW_X, 210, 140, 44, "Mulai")

# Siswa/Guru/Proktor register via Web / Google Auth
draw_parallelogram(draw, SW_X, 280, 230, 48, "Registrasi Akun Siswa\n(Web / Google Auth)")
draw_arrow(draw, SW_X, 232, SW_X, 256)

draw_parallelogram(draw, GR_X, 280, 230, 48, "Registrasi Akun Guru\n(Web / Google Auth)")
draw_parallelogram(draw, PR_X, 280, 230, 48, "Registrasi Akun Proktor\n(Web / Google Auth)")

# Superadmin create kurikulum privately
draw_parallelogram(draw, SA_X, 280, 250, 48, "Buatkan Akses Privat\nKurikulum / Admin")
draw_rect(draw, SA_X, 360, 250, 48, "Verifikasi Akun Google &\nKategorikan Role (Guru/Siswa/Proktor)")

draw_arrow(draw, SW_X, 304, SYS_X - 100, 304)
draw_arrow(draw, SYS_X - 100, 304, SYS_X - 100, 360)
draw_arrow(draw, SYS_X - 100, 360, SA_X + 125, 360, "Notifikasi Registrasi", "top")
draw_arrow(draw, SA_X, 304, SA_X, 336)

# Database Users
draw_cylinder(draw, SYS_X, 360, 180, 60, "Database:\nusers, roles")
draw_arrow(draw, SA_X, 384, SA_X, 440)
draw_arrow(draw, SA_X, 440, SYS_X - 90, 440)
draw_arrow(draw, SYS_X - 90, 440, SYS_X - 90, 390, "Simpan Akun Terverifikasi", "top")

# PHASE 2: Manajemen Master Data (Kurikulum)
draw_oval_page(draw, KR_X, 490, 240, 46, "Dashboard Kurikulum")
draw_parallelogram(draw, KR_X, 570, 250, 52, "Input Master Data:\nTahun Ajaran, Jurusan, Rombel,\nMapel, Data Guru & Siswa")
draw_arrow(draw, KR_X, 513, KR_X, 544)

draw_cylinder(draw, SYS_X, 570, 190, 60, "Database:\nMaster Data Akademik\n(classes, subjects, etc)")
draw_arrow(draw, KR_X + 125, 570, SYS_X - 95, 570, "Simpan Master Data", "top")

# Cek Rombel Terpenuhi
draw_diamond(draw, KR_X, 670, 200, 64, "Rombel Kelas\nTerpenuhi?")
draw_arrow(draw, KR_X, 596, KR_X, 638)

draw_rect(draw, KR_X, 770, 240, 48, "Cetak Kartu Ujian\nSiswa")
draw_arrow(draw, KR_X, 702, KR_X, 746, "Ya", "right")

# Distribusi Kartu Ujian ke Siswa
draw_oval_page(draw, SW_X, 770, 200, 46, "Menerima Kartu Ujian\n(Nomor & Sesi)")
draw_arrow(draw, KR_X + 120, 770, SW_X - 100, 770, "Distribusi Kartu", "top")

# PHASE 3: Bank Soal & Manajemen Ujian (Guru)
draw_oval_page(draw, GR_X, 860, 220, 46, "Dashboard Guru Mapel")
draw_parallelogram(draw, GR_X, 940, 260, 52, "Buat Bank Soal &\nInput Butir Soal (PG/Esai/Rumus)")
draw_arrow(draw, GR_X, 883, GR_X, 914)

draw_cylinder(draw, SYS_X, 940, 190, 60, "Database:\nquestion_banks,\nquestions, options")
draw_arrow(draw, GR_X + 130, 940, SYS_X - 95, 940, "Simpan Butir Soal", "top")

draw_parallelogram(draw, GR_X, 1040, 260, 52, "Manajemen Jadwal Ujian:\nDurasi, Kelas, Jurusan\n(Status: DRAFT)")
draw_arrow(draw, GR_X, 966, GR_X, 1014)

draw_cylinder(draw, SYS_X, 1040, 190, 60, "Database:\nexams (Status: DRAFT)")
draw_arrow(draw, GR_X + 130, 1040, SYS_X - 95, 1040, "Simpan Ujian (Draft)", "top")

# PHASE 4: Publikasi Ujian & Administrasi Pengawasan (Kurikulum)
draw_rect(draw, KR_X, 1140, 260, 50, "Cetak Berita Acara (BAP)\n& Daftar Hadir Siswa")
draw_arrow(draw, SYS_X - 95, 1070, KR_X + 130, 1140, "Ujian Dibuat", "top")

draw_rect(draw, KR_X, 1230, 260, 50, "Publikasi Ujian (ON GOING) &\nTugaskan Proktor Pengawas")
draw_arrow(draw, KR_X, 1165, KR_X, 1205)

draw_cylinder(draw, SYS_X, 1230, 190, 60, "Database:\nexams (ON GOING),\nproctor_assignments")
draw_arrow(draw, KR_X + 130, 1230, SYS_X - 95, 1230, "Update Status & Proktor", "top")

# Proktor terima BAP & Siswa terima Daftar Hadir
draw_oval_page(draw, PR_X, 1140, 220, 46, "Menerima Berita Acara (BAP)\n& Jadwal Pengawasan")
draw_arrow(draw, KR_X + 130, 1140, PR_X - 110, 1140)

# PHASE 5: Pelaksanaan Ujian, Token & Autosave (Siswa)
draw_oval_page(draw, SW_X, 1330, 240, 48, "Buka Broadcast Jadwal Ujian\n(Status: Terbuka)")
draw_arrow(draw, SYS_X - 95, 1260, SW_X + 120, 1330, "Feed Jadwal Terbuka", "top")

draw_rect(draw, PR_X, 1330, 220, 48, "Rilis Kode Token Ujian\nkepada Siswa di Ruangan")
draw_arrow(draw, PR_X - 110, 1330, SW_X + 120, 1380, "Bagikan Token", "top")

draw_parallelogram(draw, SW_X, 1420, 230, 48, "Input Kode Token Ujian &\nKonfirmasi Mulai")
draw_arrow(draw, SW_X, 1354, SW_X, 1396)

draw_diamond(draw, SYS_X, 1420, 180, 56, "Token &\nWaktu Valid?")
draw_arrow(draw, SW_X + 115, 1420, SYS_X - 90, 1420, "Validasi", "top")

draw_rect(draw, SW_X, 1520, 250, 50, "Mengerjakan Soal Ujian\n(PG, Kompleks, Esai)")
draw_arrow(draw, SYS_X - 90, 1448, SW_X + 125, 1520, "Kirim Snapshot Soal", "top")

draw_rect(draw, SYS_X, 1520, 200, 50, "Sistem Autosave Jawaban\nPer Nomor Soal")
draw_arrow(draw, SW_X + 125, 1520, SYS_X - 100, 1520, "Autosave Respon", "top")

# PHASE 6: Engine Integritas & Deteksi Pelanggaran (Limit 5x)
draw_diamond(draw, SYS_X, 1630, 200, 64, "Terdeteksi\nPelanggaran?")
draw_arrow(draw, SYS_X, 1545, SYS_X, 1598)

# Catatan 8 Jenis Pelanggaran
draw_rect(draw, SYS_X, 1750, 250, 75, "Sensor Integritas:\n• Tab Switch • Blur/Fokus\n• Fullscreen Exit • BeforeUnload\n• Jaringan Offline • Multi-Sesi")
draw_arrow(draw, SYS_X, 1662, SYS_X, 1712, "Ya", "right")

# Pop-up & Limit Cek
draw_diamond(draw, SYS_X, 1880, 200, 64, "Pelanggaran\n> 5 Kali?")
draw_arrow(draw, SYS_X, 1788, SYS_X, 1848)

draw_rect(draw, SW_X, 1880, 230, 48, "Muncul Pop-Up Peringatan\n(Limit Tersisa 1 - 5)")
draw_arrow(draw, SYS_X - 100, 1880, SW_X + 115, 1880, "Tidak (<= 5x)", "top")
draw_arrow(draw, SW_X, 1856, SW_X, 1545)

# Blokir Siswa
draw_rect(draw, SW_X, 1990, 250, 56, "AKSES TERBLOKIR / TERKUNCI\n(Keluar dari Halaman Ujian)\nHubungi Proktor!", is_alert=True)
draw_arrow(draw, SYS_X, 1912, SYS_X, 1990)
draw_arrow(draw, SYS_X, 1990, SW_X + 125, 1990, "Ya (> 5x)", "top")

# PHASE 7: Monitoring Real-Time & Intervensi Proktor
draw_oval_page(draw, PR_X, 1990, 240, 48, "Live Monitoring Dashboard\n(Lihat Siswa Terkunci)")
draw_arrow(draw, SYS_X + 100, 1990, PR_X - 120, 1990, "Alert Kunci", "top")

draw_rect(draw, PR_X, 2090, 250, 54, "Manajemen Siswa Melanggar:\nVerifikasi Alasan -> Tindakan\n(Buka Kunci / Tambah Waktu / Force Submit)")
draw_arrow(draw, PR_X, 2014, PR_X, 2063)

draw_cylinder(draw, SYS_X, 2090, 190, 60, "Database:\nexam_attempts (LOCKED/UNLOCKED),\nintegrity_events")
draw_arrow(draw, PR_X - 125, 2090, SYS_X + 95, 2090, "Update Status", "top")

draw_arrow(draw, SYS_X - 95, 2090, SW_X + 125, 2018, "Buka Kunci Kembali", "top")

# PHASE 8: Submit & Evaluasi Penilaian (Guru)
draw_parallelogram(draw, SW_X, 2200, 230, 48, "Siswa Selesai Ujian &\nTekan Submit / Waktu Habis")
draw_arrow(draw, SW_X, 2018, SW_X, 2176)

draw_rect(draw, SYS_X, 2200, 200, 48, "Auto-Grading Sistem\n(Soal Pilihan Objektif)")
draw_arrow(draw, SW_X + 115, 2200, SYS_X - 100, 2200, "Finalisasi Jawaban", "top")

draw_oval_page(draw, GR_X, 2280, 240, 48, "Halaman Manajemen\nLaporan Hasil Ujian")
draw_arrow(draw, SYS_X - 100, 2224, GR_X + 120, 2280, "Tampilkan Hasil", "top")

draw_diamond(draw, GR_X, 2380, 200, 64, "Ada Butir\nSoal Esai?")
draw_arrow(draw, GR_X, 2304, GR_X, 2348)

draw_parallelogram(draw, GR_X, 2480, 250, 50, "Koreksi Jawaban Esai Manual\n& Input Nilai Poin")
draw_arrow(draw, GR_X, 2412, GR_X, 2455, "Ya", "right")

draw_rect(draw, GR_X, 2570, 250, 50, "Publikasi Nilai &\nHasil Ujian ke Siswa")
draw_arrow(draw, GR_X, 2505, GR_X, 2545)
draw_arrow(draw, GR_X - 100, 2380, GR_X - 100, 2570)
draw_arrow(draw, GR_X - 100, 2570, GR_X - 125, 2570, "Tidak Ada Esai", "top")

# Nilai tersimpan di DB
draw_cylinder(draw, SYS_X, 2570, 190, 60, "Database:\nNilai Akhir Terpublikasi\n(exam_attempts.published)")
draw_arrow(draw, GR_X + 125, 2570, SYS_X - 95, 2570, "Simpan Publikasi", "top")

# Siswa lihat nilai
draw_oval_page(draw, SW_X, 2670, 220, 46, "Melihat Pengumuman\nHasil & Nilai Ujian")
draw_arrow(draw, SYS_X - 95, 2600, SW_X + 110, 2670, "Hasil Terbuka", "top")

# PHASE 9: Kurikulum & Admin Monitor Hasil Terpublikasi
draw_oval_page(draw, KR_X, 2760, 250, 48, "Lihat & Unduh Rekap Nilai\n(Hanya yang Telah Divalidasi Guru)")
draw_arrow(draw, SYS_X - 95, 2600, KR_X + 125, 2760, "Akses Hasil Tervalidasi", "top")

draw_oval_page(draw, SA_X, 2760, 230, 48, "Monitoring Evaluasi\n& Arsip Sistem")
draw_arrow(draw, KR_X - 125, 2760, SA_X + 115, 2760)

# Selesai
draw_terminator(draw, KR_X, 2870, 140, 44, "Selesai")
draw_arrow(draw, KR_X, 2784, KR_X, 2848)

# Save PNG
output_png = "c:/laragon/www/cbt-sapmar/public/docs/swimlane_cbt_workflow.png"
os.makedirs(os.path.dirname(output_png), exist_ok=True)
img.save(output_png, quality=95)
print(f"Swimlane PNG generated: {output_png} ({WIDTH}x{HEIGHT})")

# -------------------------------------------------------------
# Generate SVG Vector
# -------------------------------------------------------------
svg = []
svg.append(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {WIDTH} {HEIGHT}" width="100%" height="100%" style="background:#ffffff; font-family:\'Segoe UI\', Plus Jakarta Sans, sans-serif;">')

# Title
svg.append(f'<rect x="40" y="30" width="{WIDTH - 80}" height="60" fill="#f8fafc" stroke="#1e293b" stroke-width="2"/>')
svg.append(f'<text x="60" y="58" font-size="20" font-weight="bold" fill="#0f172a">ALUR SISTEM CBT SAPMAR YANG AKAN DIKEMBANGKAN</text>')
svg.append(f'<text x="60" y="80" font-size="12" fill="#64748b">Swimlane Activity Diagram Skenario Komprehensif: Registrasi, Master Data, Ujian, Anti-Cheating &amp; Laporan</text>')

# Outer Frame
svg.append(f'<rect x="40" y="{HEADER_TOP_Y}" width="{WIDTH - 80}" height="{CONTENT_END_Y - HEADER_TOP_Y}" fill="none" stroke="#1e293b" stroke-width="2"/>')
svg.append(f'<line x1="{PHASE_COL_W + 40}" y1="{HEADER_TOP_Y}" x2="{PHASE_COL_W + 40}" y2="{CONTENT_END_Y}" stroke="#1e293b" stroke-width="2"/>')

# Lane Headers & Dividers
for i, name in enumerate(LANE_NAMES):
    lane = LANES[name]
    svg.append(f'<rect x="{lane["left"]}" y="{HEADER_TOP_Y}" width="{LANE_W}" height="{CONTENT_START_Y - HEADER_TOP_Y}" fill="#f1f5f9" stroke="#1e293b" stroke-width="1"/>')
    svg.append(f'<text x="{lane["center"]}" y="{HEADER_TOP_Y + 36}" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">{name}</text>')
    if i > 0:
        svg.append(f'<line x1="{lane["left"]}" y1="{HEADER_TOP_Y}" x2="{lane["left"]}" y2="{CONTENT_END_Y}" stroke="#cbd5e1" stroke-width="1.2"/>')

svg.append('</svg>')

output_svg = "c:/laragon/www/cbt-sapmar/public/docs/swimlane_cbt_workflow.svg"
with open(output_svg, "w", encoding="utf-8") as f:
    f.write("\n".join(svg))
print(f"Swimlane SVG generated: {output_svg}")
