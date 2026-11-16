import os
from PIL import Image, ImageDraw, ImageFont

# -------------------------------------------------------------
# Dimensions & Configurations
# -------------------------------------------------------------
WIDTH = 1800
HEIGHT = 2200
BG_COLOR = (255, 255, 255)

PHASE_COL_W = 60
LANE_NAMES = [
    "Kurikulum",
    "Guru",
    "Siswa"
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
STROKE_DEFAULT = (51, 65, 85)
LINE_COLOR = (71, 85, 105)
ARROW_COLOR = (30, 41, 59)

NODE_FILL_EVENT = (237, 242, 247)      # Start/End
NODE_FILL_INPUT = (254, 249, 195)      # Parallelogram (Input/Output)
NODE_FILL_PROC = (238, 242, 255)       # Rectangle (Proses)
NODE_FILL_DOC = (254, 243, 199)        # Document / Berkas Kertas
NODE_FILL_SLOW = (254, 226, 226)       # Alert / Proses Manual Lambat

font_bold_path = "C:/Windows/Fonts/segoeuib.ttf"
font_reg_path = "C:/Windows/Fonts/segoeui.ttf"

font_title = ImageFont.truetype(font_bold_path, 20)
font_subtitle = ImageFont.truetype(font_reg_path, 12)
font_lane_header = ImageFont.truetype(font_bold_path, 15)
font_phase = ImageFont.truetype(font_bold_path, 12)
font_node_bold = ImageFont.truetype(font_bold_path, 11)
font_node = ImageFont.truetype(font_reg_path, 10)
font_flow = ImageFont.truetype(font_bold_path, 10)

img = Image.new("RGB", (WIDTH, HEIGHT), BG_COLOR)
draw = ImageDraw.Draw(img)

# -------------------------------------------------------------
# 1. Header Title Block & Outer Frame
# -------------------------------------------------------------
HEADER_TOP_Y = 100
CONTENT_START_Y = 160
CONTENT_END_Y = HEIGHT - 80

# Main Header Title Block
draw.rectangle([(40, 30), (WIDTH - 40, 90)], fill=(248, 250, 252), outline=BORDER_DARK, width=2)
draw.text((60, 42), "ALUR SISTEM UJIAN YANG SEDANG BERJALAN (SISTEM MANUAL / KONVENSIONAL)", fill=(15, 23, 42), font=font_title)
draw.text((60, 68), "Swimlane Activity Diagram Pelaksanaan Ujian Berbasis Kertas (As-Is System)", fill=(100, 116, 139), font=font_subtitle)

# Outer Frame
draw.rectangle([(40, HEADER_TOP_Y), (WIDTH - 40, CONTENT_END_Y)], outline=BORDER_DARK, width=2)

# Left Phase Column
draw.line([(PHASE_COL_W + 40, HEADER_TOP_Y), (PHASE_COL_W + 40, CONTENT_END_Y)], fill=BORDER_DARK, width=2)
draw.text((50, CONTENT_START_Y + 20), "F\nA\nS\nE", fill=(100, 116, 139), font=font_phase, spacing=14)

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
# Drawing Helpers
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

def draw_rect(draw, cx, cy, w, h, text, is_slow=False):
    fill = NODE_FILL_SLOW if is_slow else NODE_FILL_PROC
    stroke = (220, 38, 38) if is_slow else STROKE_DEFAULT
    draw.rounded_rectangle([(cx - w//2, cy - h//2), (cx + w//2, cy + h//2)], radius=6, fill=fill, outline=stroke, width=2)
    lines = text.split("\n")
    ty = cy - (len(lines)*13)//2
    for line in lines:
        bbox = font_node_bold.getbbox(line)
        lw = bbox[2] - bbox[0]
        draw.text((cx - lw//2, ty), line, fill=(15, 23, 42), font=font_node_bold)
        ty += 13

def draw_doc(draw, cx, cy, w, h, text):
    # Document symbol: rectangle with wave bottom
    left = cx - w // 2
    right = cx + w // 2
    top = cy - h // 2
    bottom = cy + h // 2
    pts = [
        (left, top),
        (right, top),
        (right, bottom - 10),
        (cx + w//4, bottom),
        (cx, bottom - 6),
        (cx - w//4, bottom - 12),
        (left, bottom - 6)
    ]
    draw.polygon(pts, fill=NODE_FILL_DOC, outline=STROKE_DEFAULT)
    draw.line(pts + [pts[0]], fill=STROKE_DEFAULT, width=2)
    lines = text.split("\n")
    ty = cy - (len(lines)*13)//2
    for line in lines:
        bbox = font_node_bold.getbbox(line)
        lw = bbox[2] - bbox[0]
        draw.text((cx - lw//2, ty), line, fill=(113, 63, 18), font=font_node_bold)
        ty += 13

def draw_arrow(draw, x1, y1, x2, y2, label=None, label_side="top"):
    draw.line([(x1, y1), (x2, y2)], fill=ARROW_COLOR, width=2)
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
# Nodes & Steps Layout
# -------------------------------------------------------------
KR_X = LANES["Kurikulum"]["center"]
GR_X = LANES["Guru"]["center"]
SW_X = LANES["Siswa"]["center"]

# 1. Mulai
draw_terminator(draw, KR_X, 220, 150, 46, "Mulai")

# Langkah 1: Kurikulum memberitahukan kepada guru untuk membuat soal
draw_parallelogram(draw, KR_X, 320, 360, 56, "1. Memberitahukan Instruksi\nPembuatan Soal Mapel Sesuai Jadwal\n(Tenggat Waktu yang Ditentukan)")
draw_arrow(draw, KR_X, 243, KR_X, 292)

# Mengirimkan informasi ke Guru
draw_rect(draw, GR_X, 320, 340, 54, "Menerima Surat / Pemberitahuan\nInstruksi Pembuatan Soal")
draw_arrow(draw, KR_X + 180, 320, GR_X - 170, 320, "Kirim Instruksi", "top")

# Langkah 2: Guru membuat soal & kunci jawaban PDF
draw_rect(draw, GR_X, 470, 350, 58, "2. Menyusun Butir Soal Ujian &\nKunci Jawaban per Mata Pelajaran")
draw_arrow(draw, GR_X, 347, GR_X, 441)

draw_doc(draw, GR_X, 600, 300, 54, "Dokumen Naskah Soal &\nKunci Jawaban (Format PDF)")
draw_arrow(draw, GR_X, 499, GR_X, 573)

draw_parallelogram(draw, GR_X, 720, 340, 50, "Kirim Berkas PDF Soal & Kunci\nke Bagian Kurikulum")
draw_arrow(draw, GR_X, 627, GR_X, 695)

# Pengiriman ke Kurikulum
draw_arrow(draw, GR_X - 170, 720, KR_X + 180, 720, "Kirim File PDF", "top")

# Langkah 3: Kurikulum menerima PDF & mencetak secara manual
draw_rect(draw, KR_X, 720, 350, 54, "Menerima File PDF Soal\n& Kunci Jawaban Guru")

# Proses Manual Lambat: Cetak A4, Buat BAP, Kartu Ujian, Daftar Hadir
draw_rect(draw, KR_X, 890, 400, 80, "3. Cetak Fisik Naskah Soal (Kertas A4),\nBuat Manual Kartu Peserta Ujian Siswa,\nDaftar Hadir & Berita Acara (BAP)\n[PROSES MANUAL MEMBUTUHKAN WAKTU LAMA]", is_slow=True)
draw_arrow(draw, KR_X, 747, KR_X, 850)

draw_doc(draw, KR_X, 1040, 360, 60, "Bundel Fisik Kertas:\n• Naskah Soal Kertas A4 • Lembar Jawaban\n• Kartu Peserta Ujian • Daftar Hadir • BAP")
draw_arrow(draw, KR_X, 930, KR_X, 1010)

# Distribusi Berkas Fisik ke Siswa & Pengawas
draw_parallelogram(draw, KR_X, 1180, 360, 52, "Distribusi Bundel Soal, Lembar Jawab,\nKartu Peserta, & Form Hadir ke Ruang")
draw_arrow(draw, KR_X, 1070, KR_X, 1154)

# Langkah 4: Siswa Menerima Soal & Menjawab Manual
draw_rect(draw, SW_X, 1180, 340, 54, "Menerima Kartu Ujian, Naskah Soal\n& Lembar Jawaban Kertas")
draw_arrow(draw, KR_X + 180, 1180, SW_X - 170, 1180, "Distribusi Fisik", "top")

draw_rect(draw, SW_X, 1340, 380, 64, "4. Menjawab Soal Ujian Secara Menulis /\nManual pada Lembar Jawaban Kertas\nHingga Selesai Sesuai Alokasi Waktu")
draw_arrow(draw, SW_X, 1207, SW_X, 1308)

# Siswa mengumpulkan lembar jawaban
draw_parallelogram(draw, SW_X, 1490, 340, 52, "Mengumpulkan Lembar Jawaban\nKertas Ujian ke Pengawas")
draw_arrow(draw, SW_X, 1372, SW_X, 1464)

# Lembar jawaban diserahkan ke Guru
draw_doc(draw, GR_X, 1490, 320, 54, "Kumpulan Lembar Jawaban\nKertas Siswa per Kelas")
draw_arrow(draw, SW_X - 170, 1490, GR_X + 160, 1490, "Serahkan Lembar Jawab", "top")

# Langkah 5: Guru memeriksa/menilai manual & melaporkan ke Kurikulum
draw_rect(draw, GR_X, 1650, 380, 68, "5. Memeriksa & Mengoreksi Lembar Jawaban\nKertas Siswa Satu per Satu Secara Manual\nSerta Merekap Akumulasi Nilai Akhir", is_slow=True)
draw_arrow(draw, GR_X, 1517, GR_X, 1616)

draw_doc(draw, GR_X, 1790, 320, 54, "Lembar Fisik Rekap Nilai\nMata Pelajaran Siswa")
draw_arrow(draw, GR_X, 1684, GR_X, 1763)

draw_parallelogram(draw, GR_X, 1920, 340, 52, "Melaporkan Rekap Nilai Ujian\nke Bagian Kurikulum")
draw_arrow(draw, GR_X, 1817, GR_X, 1894)

# Laporan diterima Kurikulum
draw_rect(draw, KR_X, 1920, 350, 54, "Menerima & Merekap Laporan\nNilai Fisik dari Seluruh Guru")
draw_arrow(draw, GR_X - 170, 1920, KR_X + 175, 1920, "Serahkan Rekap Nilai", "top")

# Selesai
draw_terminator(draw, KR_X, 2050, 150, 46, "Selesai")
draw_arrow(draw, KR_X, 1947, KR_X, 2027)

# Save PNG
output_png = "c:/laragon/www/cbt-sapmar/public/docs/swimlane_manual_system.png"
os.makedirs(os.path.dirname(output_png), exist_ok=True)
img.save(output_png, quality=95)
print(f"Manual Swimlane PNG generated: {output_png} ({WIDTH}x{HEIGHT})")

# -------------------------------------------------------------
# Generate SVG Vector
# -------------------------------------------------------------
svg = []
svg.append(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {WIDTH} {HEIGHT}" width="100%" height="100%" style="background:#ffffff; font-family:\'Segoe UI\', Plus Jakarta Sans, sans-serif;">')

# Title Block
svg.append(f'<rect x="40" y="30" width="{WIDTH - 80}" height="60" fill="#f8fafc" stroke="#1e293b" stroke-width="2"/>')
svg.append(f'<text x="60" y="58" font-size="20" font-weight="bold" fill="#0f172a">ALUR SISTEM UJIAN YANG SEDANG BERJALAN (SISTEM MANUAL / KONVENSIONAL)</text>')
svg.append(f'<text x="60" y="80" font-size="12" fill="#64748b">Swimlane Activity Diagram Pelaksanaan Ujian Berbasis Kertas (As-Is System)</text>')

# Outer Frame
svg.append(f'<rect x="40" y="{HEADER_TOP_Y}" width="{WIDTH - 80}" height="{CONTENT_END_Y - HEADER_TOP_Y}" fill="none" stroke="#1e293b" stroke-width="2"/>')
svg.append(f'<line x1="{PHASE_COL_W + 40}" y1="{HEADER_TOP_Y}" x2="{PHASE_COL_W + 40}" y2="{CONTENT_END_Y}" stroke="#1e293b" stroke-width="2"/>')

# Lane Headers & Dividers
for i, name in enumerate(LANE_NAMES):
    lane = LANES[name]
    svg.append(f'<rect x="{lane["left"]}" y="{HEADER_TOP_Y}" width="{LANE_W}" height="{CONTENT_START_Y - HEADER_TOP_Y}" fill="#f1f5f9" stroke="#1e293b" stroke-width="1"/>')
    svg.append(f'<text x="{lane["center"]}" y="{HEADER_TOP_Y + 36}" text-anchor="middle" font-size="15" font-weight="bold" fill="#0f172a">{name}</text>')
    if i > 0:
        svg.append(f'<line x1="{lane["left"]}" y1="{HEADER_TOP_Y}" x2="{lane["left"]}" y2="{CONTENT_END_Y}" stroke="#cbd5e1" stroke-width="1.2"/>')

svg.append('</svg>')

output_svg = "c:/laragon/www/cbt-sapmar/public/docs/swimlane_manual_system.svg"
with open(output_svg, "w", encoding="utf-8") as f:
    f.write("\n".join(svg))
print(f"Manual Swimlane SVG generated: {output_svg}")

