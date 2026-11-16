import os
from PIL import Image, ImageDraw, ImageFont

# -------------------------------------------------------------
# Configuration & Dimensions
# -------------------------------------------------------------
WIDTH = 1600
HEIGHT = 2300
BG_COLOR = (255, 255, 255)

BOX_BORDER = (0, 0, 0)
TEXT_COLOR = (0, 0, 0)
ARROW_COLOR = (0, 0, 0)

font_path = "C:/Windows/Fonts/arial.ttf"
font_bold_path = "C:/Windows/Fonts/arialbd.ttf"

font_title = ImageFont.truetype(font_bold_path, 18)
font_subtitle = ImageFont.truetype(font_path, 12)
font_node = ImageFont.truetype(font_bold_path, 11)
font_text = ImageFont.truetype(font_path, 10)

img = Image.new("RGB", (WIDTH, HEIGHT), BG_COLOR)
draw = ImageDraw.Draw(img)

# Title
draw.text((60, 40), "FLOWCHART SISTEM CBT SAPMAR", fill=(0, 0, 0), font=font_title)
draw.text((60, 68), "Berdasarkan Referensi Alur Sistem End-to-End Pelaksanaan Ujian Berbasis Komputer", fill=(80, 80, 80), font=font_subtitle)

# Helper drawing functions
def draw_oval(draw, cx, cy, w, h, text):
    draw.ellipse([(cx - w//2, cy - h//2), (cx + w//2, cy + h//2)], fill=(255, 255, 255), outline=BOX_BORDER, width=2)
    bbox = font_node.getbbox(text)
    tw = bbox[2] - bbox[0]
    th = bbox[3] - bbox[1]
    draw.text((cx - tw//2, cy - th//2 - 1), text, fill=TEXT_COLOR, font=font_node)

def draw_box(draw, cx, cy, w, h, text):
    draw.rectangle([(cx - w//2, cy - h//2), (cx + w//2, cy + h//2)], fill=(255, 255, 255), outline=BOX_BORDER, width=1)
    lines = text.split("\n")
    total_h = len(lines) * 14
    start_y = cy - total_h // 2 + 1
    for line in lines:
        bbox = font_node.getbbox(line)
        tw = bbox[2] - bbox[0]
        draw.text((cx - tw//2, start_y), line, fill=TEXT_COLOR, font=font_node)
        start_y += 14

def draw_arrow(draw, points):
    # points: list of (x, y) tuples
    for i in range(len(points) - 1):
        draw.line([points[i], points[i+1]], fill=ARROW_COLOR, width=1)
    
    # Arrow head at the last segment
    p1 = points[-2]
    p2 = points[-1]
    x1, y1 = p1
    x2, y2 = p2
    size = 5
    if x2 > x1 and y1 == y2:
        draw.polygon([(x2, y2), (x2 - size*2, y2 - size), (x2 - size*2, y2 + size)], fill=ARROW_COLOR)
    elif x2 < x1 and y1 == y2:
        draw.polygon([(x2, y2), (x2 + size*2, y2 - size), (x2 + size*2, y2 + size)], fill=ARROW_COLOR)
    elif y2 > y1 and x1 == x2:
        draw.polygon([(x2, y2), (x2 - size, y2 - size*2), (x2 + size, y2 - size*2)], fill=ARROW_COLOR)
    elif y2 < y1 and x1 == x2:
        draw.polygon([(x2, y2), (x2 - size, y2 + size*2), (x2 + size, y2 + size*2)], fill=ARROW_COLOR)

# -------------------------------------------------------------
# Nodes Placement matching the reference image layout
# -------------------------------------------------------------
# Columns:
# Col 1: X = 250 (Left column: Start, User, Login, Menyiapkan Ujian)
# Col 2: X = 550 (Middle column: Bank Soal, Soal, Manajemen Soal, Pelaksanaan Ujian)
# Col 3: X = 950 (Right column: Penjadwalan, Pengerjaan, etc.)
# Col 4: Under Pengerjaan:
#   Left branch: X = 520
#   Center: X = 950
#   Right / Down stream: X = 1350

# 1. Start
draw_oval(draw, 250, 140, 140, 50, "Start")

# 2. User
draw_box(draw, 250, 230, 150, 50, "User\n(Admin/Guru/Siswa/Proktor)")
draw_arrow(draw, [(250, 165), (250, 205)])

# 3. Login / Register
draw_box(draw, 250, 320, 150, 50, "Login / Register\n(Web & Google Auth)")
draw_arrow(draw, [(250, 255), (250, 295)])

# 4. Menyiapkan Ujian
draw_box(draw, 250, 410, 150, 50, "Menyiapkan Ujian\n& Master Data")
draw_arrow(draw, [(250, 345), (250, 385)])

# Arrow from Menyiapkan Ujian -> Membuat Bank Soal (Col 1 to Col 2)
draw_arrow(draw, [(325, 410), (550, 410)])

# 5. Membuat Bank Soal
draw_box(draw, 550, 410, 170, 50, "Membuat Bank Soal\nper Mata Pelajaran")

# 6. Membuat Soal Ujian
draw_box(draw, 550, 500, 170, 50, "Membuat Soal Ujian\n(PG, Esai, Rumus Math)")
draw_arrow(draw, [(550, 435), (550, 475)])

# 7. Manajemen Soal Ujian
draw_box(draw, 550, 590, 170, 50, "Manajemen Soal Ujian\n(Preview & Versioning)")
draw_arrow(draw, [(550, 525), (550, 565)])

# 8. Membuat Pelaksanaan Ujian
draw_box(draw, 550, 680, 170, 50, "Membuat Pelaksanaan\nUjian (Status: Draft)")
draw_arrow(draw, [(550, 615), (550, 655)])

# Arrow from Membuat Pelaksanaan Ujian -> Manajemen Peserta dan Penjadwalan (Col 2 to Col 3)
draw_arrow(draw, [(635, 680), (950, 680)])

# 9. Manajemen Peserta dan Penjadwalan Ujian
draw_box(draw, 950, 680, 220, 50, "Manajemen Peserta dan\nPenjadwalan Ujian")

# 10. Pengerjaan Oleh Siswa
draw_box(draw, 950, 780, 220, 50, "Pengerjaan Oleh Siswa\n(Input Token Ujian)")
draw_arrow(draw, [(950, 705), (950, 755)])

# Branches under Pengerjaan Oleh Siswa:
# Left Branch: Menjalankan Sistem AutoSave Jawaban
draw_box(draw, 520, 900, 240, 54, "Menjalankan Sistem\nAutoSave Jawaban Real-Time")
draw_arrow(draw, [(840, 780), (520, 780), (520, 873)])

# Center: Melakukan Monitoring Ujian
draw_box(draw, 950, 900, 220, 54, "Melakukan Monitoring Ujian\n& Sensor Anti-Curang (5x)")
draw_arrow(draw, [(950, 805), (950, 873)])
# Feedback arrow back from Monitoring to Pengerjaan
draw_arrow(draw, [(970, 873), (970, 805)])

# Right Branch / Down: Melakukan Pengumpulan Pengerjaan Ujian
draw_box(draw, 1340, 900, 220, 54, "Melakukan Pengumpulan\nPengerjaan Ujian")
draw_arrow(draw, [(1060, 780), (1340, 780), (1340, 873)])

# 11. Melakukan Penilaian Hasil Ujian
draw_box(draw, 1340, 1020, 220, 50, "Melakukan Penilaian\nHasil Ujian (Auto/Manual)")
draw_arrow(draw, [(1340, 927), (1340, 995)])

# 12. Melakukan Persetujuan Hasil Penilaian dari Guru
draw_box(draw, 1340, 1120, 220, 50, "Melakukan Persetujuan &\nPublikasi Nilai dari Guru")
draw_arrow(draw, [(1340, 1045), (1340, 1095)])

# 13. Memunculkan Hasil Dashboard Analitik pada Halaman
draw_box(draw, 1340, 1220, 220, 50, "Memunculkan Hasil &\nDashboard Analitik")
draw_arrow(draw, [(1340, 1145), (1340, 1195)])

# 14. Melakukan Ekspor Laporan
draw_box(draw, 1340, 1320, 220, 50, "Melakukan Ekspor Laporan\n(Excel / PDF / Berita Acara)")
draw_arrow(draw, [(1340, 1245), (1340, 1295)])

# 15. Mengarsipkan
draw_box(draw, 1340, 1420, 220, 50, "Mengarsipkan Hasil Ujian\n& Audit Log Sistem")
draw_arrow(draw, [(1340, 1345), (1340, 1395)])

# 16. Selesai
draw_oval(draw, 1340, 1520, 140, 50, "Selesai")
draw_arrow(draw, [(1340, 1445), (1340, 1495)])

# Save PNG
output_png = "c:/laragon/www/cbt-sapmar/public/docs/flowchart_sistem_cbt.png"
os.makedirs(os.path.dirname(output_png), exist_ok=True)
img.save(output_png, quality=95)
print(f"Flowchart PNG generated successfully: {output_png} ({WIDTH}x{HEIGHT})")

# -------------------------------------------------------------
# Generate SVG Vector
# -------------------------------------------------------------
svg = []
svg.append(f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {WIDTH} {HEIGHT}" width="100%" height="100%" style="background:#ffffff; font-family:Arial, sans-serif;">')

# Title
svg.append(f'<text x="60" y="55" font-size="20" font-weight="bold" fill="#000000">FLOWCHART SISTEM CBT SAPMAR</text>')
svg.append(f'<text x="60" y="80" font-size="12" fill="#555555">Berdasarkan Referensi Alur Sistem End-to-End Pelaksanaan Ujian Berbasis Komputer</text>')

# Marker for arrows
svg.append('<defs>')
svg.append('<marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto"><path d="M 0 1 L 10 5 L 0 9 z" fill="#000000"/></marker>')
svg.append('</defs>')

def svg_oval(cx, cy, w, h, text):
    return f'<ellipse cx="{cx}" cy="{cy}" rx="{w/2}" ry="{h/2}" fill="#ffffff" stroke="#000000" stroke-width="2"/><text x="{cx}" y="{cy + 5}" text-anchor="middle" font-size="12" font-weight="bold" fill="#000000">{text}</text>'

def svg_box(cx, cy, w, h, text):
    lines = text.split("\n")
    res = f'<rect x="{cx - w/2}" y="{cy - h/2}" width="{w}" height="{h}" fill="#ffffff" stroke="#000000" stroke-width="1.2"/>'
    start_y = cy - (len(lines)-1)*7
    for line in lines:
        res += f'<text x="{cx}" y="{start_y + 4}" text-anchor="middle" font-size="11" font-weight="bold" fill="#000000">{line}</text>'
        start_y += 14
    return res

def svg_line(pts):
    d = "M " + " L ".join([f"{p[0]},{p[1]}" for p in pts])
    return f'<path d="{d}" fill="none" stroke="#000000" stroke-width="1.2" marker-end="url(#arrow)"/>'

# Nodes
svg.append(svg_oval(250, 140, 140, 50, "Start"))
svg.append(svg_line([(250, 165), (250, 205)]))

svg.append(svg_box(250, 230, 150, 50, "User\n(Admin/Guru/Siswa/Proktor)"))
svg.append(svg_line([(250, 255), (250, 295)]))

svg.append(svg_box(250, 320, 150, 50, "Login / Register\n(Web & Google Auth)"))
svg.append(svg_line([(250, 345), (250, 385)]))

svg.append(svg_box(250, 410, 150, 50, "Menyiapkan Ujian\n& Master Data"))
svg.append(svg_line([(325, 410), (465, 410)]))

svg.append(svg_box(550, 410, 170, 50, "Membuat Bank Soal\nper Mata Pelajaran"))
svg.append(svg_line([(550, 435), (550, 475)]))

svg.append(svg_box(550, 500, 170, 50, "Membuat Soal Ujian\n(PG, Esai, Rumus Math)"))
svg.append(svg_line([(550, 525), (550, 565)]))

svg.append(svg_box(550, 590, 170, 50, "Manajemen Soal Ujian\n(Preview & Versioning)"))
svg.append(svg_line([(550, 615), (550, 655)]))

svg.append(svg_box(550, 680, 170, 50, "Membuat Pelaksanaan\nUjian (Status: Draft)"))
svg.append(svg_line([(635, 680), (840, 680)]))

svg.append(svg_box(950, 680, 220, 50, "Manajemen Peserta dan\nPenjadwalan Ujian"))
svg.append(svg_line([(950, 705), (950, 755)]))

svg.append(svg_box(950, 780, 220, 50, "Pengerjaan Oleh Siswa\n(Input Token Ujian)"))

# Left branch
svg.append(svg_line([(840, 780), (520, 780), (520, 873)]))
svg.append(svg_box(520, 900, 240, 54, "Menjalankan Sistem\nAutoSave Jawaban Real-Time"))

# Center branch
svg.append(svg_line([(940, 805), (940, 873)]))
svg.append(svg_line([(960, 873), (960, 805)]))
svg.append(svg_box(950, 900, 220, 54, "Melakukan Monitoring Ujian\n& Sensor Anti-Curang (5x)"))

# Right branch
svg.append(svg_line([(1060, 780), (1340, 780), (1340, 873)]))
svg.append(svg_box(1340, 900, 220, 54, "Melakukan Pengumpulan\nPengerjaan Ujian"))
svg.append(svg_line([(1340, 927), (1340, 995)]))

svg.append(svg_box(1340, 1020, 220, 50, "Melakukan Penilaian\nHasil Ujian (Auto/Manual)"))
svg.append(svg_line([(1340, 1045), (1340, 1095)]))

svg.append(svg_box(1340, 1120, 220, 50, "Melakukan Persetujuan &\nPublikasi Nilai dari Guru"))
svg.append(svg_line([(1340, 1145), (1340, 1195)]))

svg.append(svg_box(1340, 1220, 220, 50, "Memunculkan Hasil &\nDashboard Analitik"))
svg.append(svg_line([(1340, 1245), (1340, 1295)]))

svg.append(svg_box(1340, 1320, 220, 50, "Melakukan Ekspor Laporan\n(Excel / PDF / Berita Acara)"))
svg.append(svg_line([(1340, 1345), (1340, 1395)]))

svg.append(svg_box(1340, 1420, 220, 50, "Mengarsipkan Hasil Ujian\n& Audit Log Sistem"))
svg.append(svg_line([(1340, 1445), (1340, 1495)]))

svg.append(svg_oval(1340, 1520, 140, 50, "Selesai"))

svg.append('</svg>')

output_svg = "c:/laragon/www/cbt-sapmar/public/docs/flowchart_sistem_cbt.svg"
with open(output_svg, "w", encoding="utf-8") as f:
    f.write("\n".join(svg))
print(f"Flowchart SVG generated successfully: {output_svg}")

