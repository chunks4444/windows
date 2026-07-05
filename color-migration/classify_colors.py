import re, csv, math
from collections import defaultdict, Counter

RAW = "color-migration/colors_raw.txt"

# ---------- parse raw file:line:color ----------
rows = []  # (file, line, raw_value)
with open(RAW, encoding="utf-8") as f:
    for line in f:
        line = line.rstrip("\n")
        m = re.match(r"^(.*?):(\d+):(.+)$", line)
        if not m:
            continue
        file, ln, val = m.group(1), m.group(2), m.group(3)
        rows.append((file, ln, val))

# ---------- drop known false positive (&#8592; arrow entity) ----------
FALSE_POSITIVES = {"#8592"}
rows = [r for r in rows if r[2] not in FALSE_POSITIVES]

# ---------- exclusion sets ----------
CONTENT_VALUES = {
    "#e03030": "6엔진 문살 stroke/fill 색상피커 기본값 (konvaStrokeColor/konvaFillColor/slatOverrideColor) - 캔버스 렌더링 + JSON 저장 파라미터",
    "#4a4a4a": "6엔진 문틀색 피커 기본값 (muntolColorInput) - 캔버스 렌더링 + JSON 저장 파라미터",
    "#c8102e": "6엔진 면색 피커 기본값 (faceColorInput) + guide 데모 스와치 - 캔버스 렌더링 + JSON 저장 파라미터",
    "#dec898": "admin/colors.php 컬러 팔레트 관리 화면의 예시/기본 색상값 - 사용자 선택형 색상 옵션 데이터",
    "#8B7355": "guide 목재/마감 톤 견본 스와치 (studio-classic.php, render.php)",
    "#6B5A3E": "guide 목재/마감 톤 견본 스와치 (render.php)",
    "#4a3728": "guide 목재/마감 톤 견본 스와치 (guide.css, studio-classic.php, render.php)",
    "#8B6914": "guide 목재/마감 톤 견본 스와치 (울거미색 데모, studio-classic.php, render.php)",
    "#5C3D1E": "guide 목재/마감 톤 견본 스와치 (살색 데모, studio-classic.php, render.php)",
    "#5C4A3A": "guide 목재/마감 톤 견본 스와치 (render.php)",
    "#3d2e1e": "guide 목재/마감 톤 견본 스와치 (render.php)",
    "#b8a98a": "guide 목재/마감 톤 견본 스와치 (studio-classic.php, render.php)",
    "#7a6a50": "guide 목재/마감 톤 견본 스와치 (render.php)",
}

EXTERNAL_BRAND_VALUES = {
    "#4285F4": "Google 로그인 버튼 공식 로고 색상 (SVG path fill)",
    "#34A853": "Google 로그인 버튼 공식 로고 색상 (SVG path fill)",
    "#FBBC05": "Google 로그인 버튼 공식 로고 색상 (SVG path fill)",
    "#EA4335": "Google 로그인 버튼 공식 로고 색상 (SVG path fill)",
    "#FEE500": "Kakao 브랜드 공식 색상 (--kakao)",
    "#F5DA00": "Kakao 브랜드 공식 색상 (--kakao-dark)",
    "#03C75A": "Naver 브랜드 공식 색상 (--naver)",
    "#02B350": "Naver 브랜드 공식 색상 (--naver-dark)",
    "#10a37f": "OpenAI 로고 색상 (admin/render_settings.php AI 카드)",
    "#6B4FBB": "Anthropic 로고 색상 (admin/render_settings.php AI 카드)",
}

# values that appear in BOTH a content context (works.php DB 커스텀 색) AND generic UI elsewhere.
# too generic (pure black/white/gray) to exclude wholesale -> annotate only.
SPLIT_CONTEXT_NOTES = {
    "#111111": "works.php work_panel_bg 등 작품패널 커스텀 색(DB 데이터) 2건 포함 - 값 자체는 범용 근흑색이라 전체 배제하지 않음",
    "#ffffff": "works.php work_panel_title_color 등 작품패널 커스텀 색(DB 데이터) 1건 포함 - 값 자체는 범용 흰색이라 전체 배제하지 않음",
    "#888888": "works.php work_panel_desc_color 작품패널 커스텀 색(DB 데이터) 1건 포함 - 값 자체는 범용 회색이라 전체 배제하지 않음",
}

# ---------- color parsing ----------
def parse_color(raw):
    raw = raw.strip()
    if raw.startswith("#"):
        h = raw[1:]
        if len(h) == 3:
            r, g, b = (int(c*2, 16) for c in h)
            return r, g, b, 1.0
        if len(h) == 4:
            r, g, b, a = (int(c*2, 16) for c in h)
            return r, g, b, a/255
        if len(h) == 6:
            r, g, b = int(h[0:2],16), int(h[2:4],16), int(h[4:6],16)
            return r, g, b, 1.0
        if len(h) == 8:
            r, g, b, a = int(h[0:2],16), int(h[2:4],16), int(h[4:6],16), int(h[6:8],16)
            return r, g, b, a/255
        return None
    m = re.match(r"rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)\s*(?:,\s*([\d.]+))?\s*\)", raw)
    if m:
        r, g, b = float(m.group(1)), float(m.group(2)), float(m.group(3))
        a = float(m.group(4)) if m.group(4) is not None else 1.0
        return r, g, b, a
    return None

def srgb_to_lin(c):
    c = c / 255.0
    return c/12.92 if c <= 0.04045 else ((c+0.055)/1.055) ** 2.4

def rgb_to_lab(r, g, b):
    rl, gl, bl = srgb_to_lin(r), srgb_to_lin(g), srgb_to_lin(b)
    X = rl*0.4124564 + gl*0.3575761 + bl*0.1804375
    Y = rl*0.2126729 + gl*0.7151522 + bl*0.0721750
    Z = rl*0.0193339 + gl*0.1191920 + bl*0.9503041
    Xn, Yn, Zn = 0.95047, 1.0, 1.08883
    def f(t):
        return t ** (1/3) if t > (6/29)**3 else t/(3*(6/29)**2) + 4/29
    fx, fy, fz = f(X/Xn), f(Y/Yn), f(Z/Zn)
    L = 116*fy - 16
    a = 500*(fx-fy)
    bb = 200*(fy-fz)
    return L, a, bb

def delta_e(lab1, lab2):
    return math.sqrt(sum((c1-c2)**2 for c1, c2 in zip(lab1, lab2)))

def chroma(lab):
    return math.sqrt(lab[1]**2 + lab[2]**2)

# ---------- target palette anchors ----------
PALETTE = {
    "--bg":            "#F3F4F5",  # 바탕(회벽)
    "--text":          "#23262A",  # 텍스트(청묵)
    "--text-muted":    "#8A9199",  # 보조 텍스트(기와)
    "--border":        "#D4D8DB",  # 선/보더(화강암)
    "--accent":        "#2C5F51",  # 강조(뇌록)
    "--accent-hover":  "#1F473C",  # 강조 호버
    "--accent-tint":   "#E6EEEB",  # 강조 틴트
}
PALETTE_LAB = {}
for name, hexv in PALETTE.items():
    r, g, b, _ = parse_color(hexv)
    PALETTE_LAB[name] = rgb_to_lab(r, g, b)

UNSURE_DE_THRESHOLD = 22.0
UNSURE_CHROMA_THRESHOLD = 35.0

# ---------- aggregate ----------
freq = Counter(v for _, _, v in rows)
locations = defaultdict(list)
for file, ln, val in rows:
    locations[val].append(f"{file}:{ln}")

results = []
for val, count in freq.items():
    if val in CONTENT_VALUES:
        results.append(dict(value=val, count=count, category="CONTENT", target="", de="", reason=CONTENT_VALUES[val], locations=locations[val]))
        continue
    if val in EXTERNAL_BRAND_VALUES:
        results.append(dict(value=val, count=count, category="EXTERNAL_BRAND", target="", de="", reason=EXTERNAL_BRAND_VALUES[val], locations=locations[val]))
        continue

    parsed = parse_color(val)
    if parsed is None:
        results.append(dict(value=val, count=count, category="PARSE_ERROR", target="", de="", reason="색상 파싱 실패", locations=locations[val]))
        continue
    r, g, b, a = parsed
    lab = rgb_to_lab(r, g, b)
    best_name, best_de = None, 1e9
    for name, plab in PALETTE_LAB.items():
        d = delta_e(lab, plab)
        if d < best_de:
            best_de, best_name = d, name
    c_star = chroma(lab)
    unsure = best_de > UNSURE_DE_THRESHOLD or (c_star > UNSURE_CHROMA_THRESHOLD and best_de > 12)
    category = "UNSURE" if unsure else "UI"
    reason_bits = [f"ΔE(Lab)={best_de:.1f} → {best_name}"]
    if val in SPLIT_CONTEXT_NOTES:
        reason_bits.append(SPLIT_CONTEXT_NOTES[val])
    if unsure:
        reason_bits.append(f"채도(C*)={c_star:.1f}, 팔레트와 거리 큼 - 원색/브랜드성 색상 여부 확인 필요")
    if a < 1.0:
        reason_bits.append(f"alpha={a}")
    results.append(dict(value=val, count=count, category=category, target=best_name, de=f"{best_de:.1f}", reason="; ".join(reason_bits), locations=locations[val]))

# sort by count desc
results.sort(key=lambda r: -r["count"])

# ---------- write CSV ----------
CSV_PATH = "color-migration/color_mapping.csv"
with open(CSV_PATH, "w", newline="", encoding="utf-8-sig") as f:
    w = csv.writer(f)
    w.writerow(["기존색", "빈도", "분류", "대상변수", "deltaE", "분류근거", "사용위치_예시(최대5)"])
    for r in results:
        loc_sample = " | ".join(r["locations"][:5]) + (f" 외 {len(r['locations'])-5}건" if len(r["locations"]) > 5 else "")
        w.writerow([r["value"], r["count"], r["category"], r["target"], r["de"], r["reason"], loc_sample])

# ---------- write full location listing (per color, all locations) ----------
LOC_PATH = "color-migration/color_locations.txt"
with open(LOC_PATH, "w", encoding="utf-8") as f:
    for r in results:
        f.write(f"{r['value']}  (빈도 {r['count']}, {r['category']}"+(f" -> {r['target']}" if r['target'] else "")+")\n")
        for loc in r["locations"]:
            f.write(f"    {loc}\n")

# ---------- write UNSURE list ----------
UNSURE_PATH = "color-migration/unsure_colors.txt"
unsure_rows = [r for r in results if r["category"] == "UNSURE"]
with open(UNSURE_PATH, "w", encoding="utf-8") as f:
    f.write(f"UNSURE 색상 {len(unsure_rows)}건 (팔레트 거리 큼 / 고채도 원색 등, 수동 검토 필요)\n\n")
    for r in unsure_rows:
        f.write(f"{r['value']}  빈도={r['count']}  {r['reason']}\n")
        for loc in r["locations"][:6]:
            f.write(f"    {loc}\n")
        if len(r["locations"]) > 6:
            f.write(f"    ... 외 {len(r['locations'])-6}건\n")
        f.write("\n")

# ---------- summary ----------
cat_counts = Counter(r["category"] for r in results)
print("분류별 고유 색상 수:", dict(cat_counts))
print("총 고유 색상 수:", len(results))
print("총 occurrence:", sum(r["count"] for r in results))
