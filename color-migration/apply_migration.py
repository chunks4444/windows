import csv, os, re, glob

ROOT = "/Users/kyungchun/web/windows.pyeongmok.com"
CSV_PATH = os.path.join(ROOT, "color-migration", "color_mapping.csv")

# ---------------- 1. legacy pastel-pairing overrides (found via manual context review) ----------------
PASTEL_OVERRIDES = {
    "#FFF0EE": "accent" ,  # placeholder, real value set below
}
PASTEL_OVERRIDES = {
    "#FFF0EE": "danger-tint",
    "#fff0ee": "danger-tint",
    "#FFF0EB": "warning-tint",
    "#fff0f0": "danger-tint",
    "#fff4e5": "warning-tint",
    "#fff8e6": "warning-tint",
    "#F5F4EE": "accent-tint",
    "#FFF8EE": "accent-tint",
    "#FDF0E6": "accent-tint",
    "#F2F0FB": "accent-tint",
    "#EEF3F8": "accent-tint",
    "#EAF3FB": "accent-tint",
    "#fff3f0": "danger-tint",
    "#ffeaea": "danger-tint",
    "#FECACA": "danger-tint",
    "#FEE2E2": "danger-tint",
    # 자동 Lab-거리 분류가 문맥(텍스트색 vs 강조색 등)을 놓친 것들 - 수동 검증 후 확정
    "#E0E5E4": "border",        # 구 --border 값. accent-tint(ΔE 3.2)보다 가까웠지만 실제 용례는 전부 보더
    "#EDE9FE": "accent-tint",   # 구 --purple-pale. bg(ΔE 10.9)로 오분류됐으나 accent 계열의 tint 짝
    "#5A6B69": "text-muted",    # 구 --text-2. 채도 낮은 회색빛인데 accent(ΔE 16.3)로 오분류 - 텍스트 색상임
    "#5a6b69": "text-muted",
    "#5A6664": "text-muted",    # engine-common.css 텍스트색, 위와 동일 사유
}

# values excluded from blanket substitution entirely (content leakage found during manual review,
# or ambiguous dual-purpose values handled by hand elsewhere)
MANUAL_EXCLUDE = {
    "#28241e",   # AI 프롬프트 예시 텍스트 (api/admin/ai_tuning.php, api/ai/chat.php) - 콘텐츠
    "#d4c5a9",   # guide.css:167 목재 그라데이션 견본 - 콘텐츠 (a8956e와 짝)
    "#111111",   # works.php 작품패널 커스텀 색 DB 기본값 - 콘텐츠 (전용, 다른 UI 용례 없음)
    "#888888",   # works.php 작품패널 커스텀 색 DB 기본값 - 콘텐츠 (전용, 다른 UI 용례 없음)
    "#FFF2F0",   # guide.css(경고박스)/auth_modal.css(에러박스) 두 용도 혼재 - 수동 처리
    "#F5C6C0",   # auth_modal.css 에러박스 보더 - 수동 처리
}

# value -> (target bare name), file allow-list (None = all files)
SCOPED_OVERRIDES = {
    "#ffffff": ("bg", {
        "src/components/mailform/reset.php",
        "src/components/mailform/order.php",
        "src/components/mailform/contact.php",
        "src/components/mailform/welcome.php",
        "src/css/index.css",
    }),
}

# ---------------- 2. resolved UNSURE decisions (from unsure_decisions.csv, with 2 corrections) ----------------
UNSURE_RESOLVED = {
    "#e03": "danger", "#cc2200": "danger", "#CC2200": "danger", "#c00": "danger",
    "#DC2626": "danger", "#B91C1C": "danger-hover", "#A81D00": "danger-hover",
    "#a81c00": "danger-hover", "#c0392b": "danger", "#e53": "danger",
    "#ff8080": "danger", "#ff9494": "danger-tint", "#e05218": "danger",
    "#E08A00": "warning", "#FFB020": "warning", "#B45309": "warning",
    "#FEF3C7": "warning-tint", "#ffdca0": "warning-tint", "#8a5a00": "warning-text",
    "#b07d00": "warning", "#FFE066": "select",
    "#7A6B40": "accent", "#b8894a": "accent", "#B8662F": "accent",
    "#2E6FA8": "accent", "#2A6B8C": "accent", "#3A6B3A": "accent",
    "#5A4DB8": "accent", "#8B5CF6": "accent", "#8b5cf6": "accent", "#6366f1": "accent",
    "#6D28D9": "accent", "#7dd9d0": "accent-tint", "#2a9d8f": "accent",
    "#5AADA2": "accent", "#4db8ac": "accent", "#15803D": "accent", "#1a8a5a": "accent",
    "#BBF7D0": "accent-tint",
    "#8B2A16": "warning-text",   # 원 결정파일은 콘텐츠유지였으나 실제로는 .guide-warn 텍스트색으로 확인되어 override
}
UNSURE_RGBA_EXPLICIT = {
    "rgba(192,57,43,0.15)": "rgba(var(--danger-rgb),0.15)",
    "rgba(200,50,50,0.85)": "rgba(var(--danger-rgb),0.85)",
    "rgba(224,48,48,0.1)": "rgba(var(--danger-rgb),0.1)",
    "rgba(255,100,100,0.25)": "rgba(var(--danger-rgb),0.25)",
    "rgba(224,82,24,0.12)": "rgba(var(--danger-rgb),0.12)",
    "rgba(125,217,208,0.25)": "rgba(var(--accent-rgb),0.25)",
}
UNSURE_CONTENT_KEEP = {
    "rgba(139,115,85,.18)", "rgba(180,155,110,.1)", "#a8956e",
    "rgba(210,170,100,.9)", "rgba(210,170,100,.6)", "#8B4513", "#c8a96e",
}

# ---------------- 3. load auto-classified UI colors from color_mapping.csv ----------------
rows = []
with open(CSV_PATH, encoding="utf-8-sig") as f:
    for r in csv.DictReader(f):
        rows.append(r)

hex_map = {}     # raw literal -> bare target name (e.g. "accent")
rgba_map = {}    # raw literal rgba string -> full replacement string (rgba(var(--x-rgb), a))

def target_bare(name):
    return name[2:] if name.startswith("--") else name

for r in rows:
    val = r["기존색"]
    cat = r["분류"]
    if cat == "UI":
        bare = target_bare(r["대상변수"])
        if val in PASTEL_OVERRIDES:
            bare = PASTEL_OVERRIDES[val]
        if val in MANUAL_EXCLUDE:
            continue
        if val in SCOPED_OVERRIDES:
            continue  # handled separately
        if val.startswith("#"):
            hex_map[val] = bare
        else:
            # rgba/rgb literal -> extract alpha
            m = re.match(r"rgba?\(([^)]+)\)", val)
            parts = [p.strip() for p in m.group(1).split(",")]
            alpha = parts[3] if len(parts) == 4 else "1"
            rgba_map[val] = f"rgba(var(--{bare}-rgb), {alpha})"
    elif cat == "UNSURE":
        if val in UNSURE_CONTENT_KEEP:
            continue
        if val in UNSURE_RESOLVED:
            hex_map[val] = UNSURE_RESOLVED[val]
        elif val in UNSURE_RGBA_EXPLICIT:
            rgba_map[val] = UNSURE_RGBA_EXPLICIT[val]
        # else: leave unresolved values untouched (shouldn't happen - all 53 are covered)
    # CONTENT / EXTERNAL_BRAND / PARSE_ERROR -> never touched

print(f"hex_map entries: {len(hex_map)}  rgba_map entries: {len(rgba_map)}")

# ---------------- 4. legacy var() reference rename map ----------------
VAR_RENAME = {
    "teal-dark": "accent-hover",
    "teal-mid": "accent",
    "teal-pale": "accent-tint",
    "teal": "accent",
    "accent-bg": "accent-tint",
    "page-bg": "bg",
    "red-dark": "danger-hover",
    "red": "danger",
    "gold": "accent",
    "text-1": "text",
    "text-2": "text-muted",
    "text-3": "text-muted",
    "border-md": "border",
    "input-bg": "bg",
    "danger-dark": "danger-hover",
    "danger-pale": "danger-tint",
    "success-pale": "accent-tint",
    "success": "accent",
    "warning-pale": "warning-tint",
    "purple-pale": "accent-tint",
    "purple": "accent",
}

# ---------------- 5. walk files ----------------
def bare_of_declaration(line):
    m = re.match(r"^\s*--([\w-]+)\s*:", line)
    return m.group(1) if m else None

def process_file(path, rel_path):
    with open(path, encoding="utf-8") as f:
        content = f.read()
    original = content

    lines = content.split("\n")
    new_lines = []
    stats = {}
    for line in lines:
        decl_name = bare_of_declaration(line)

        def sub_hex(line):
            for val, bare in sorted(hex_map.items(), key=lambda kv: -len(kv[0])):
                if val not in line:
                    continue
                if decl_name == bare:
                    continue  # would self-reference, skip this line for this value
                pattern = re.compile(r"(?<![0-9a-fA-F])" + re.escape(val) + r"(?![0-9a-fA-F])")
                new_line, n = pattern.subn(f"var(--{bare})", line)
                if n:
                    stats[val] = stats.get(val, 0) + n
                    line = new_line
            return line

        def sub_rgba(line):
            for val, repl in rgba_map.items():
                if val in line:
                    n = line.count(val)
                    line = line.replace(val, repl)
                    stats[val] = stats.get(val, 0) + n
            return line

        def sub_scoped(line):
            for val, (bare, allow) in SCOPED_OVERRIDES.items():
                if rel_path in allow and val in line:
                    pattern = re.compile(r"(?<![0-9a-fA-F])" + re.escape(val) + r"(?![0-9a-fA-F])")
                    new_line, n = pattern.subn(f"var(--{bare})", line)
                    if n:
                        stats[val] = stats.get(val, 0) + n
                        line = new_line
            return line

        line = sub_hex(line)
        line = sub_rgba(line)
        line = sub_scoped(line)
        new_lines.append(line)

    content = "\n".join(new_lines)

    # legacy var() reference rename
    for old, new in VAR_RENAME.items():
        pattern = re.compile(r"var\(--" + re.escape(old) + r"(?![\w-])")
        content, n = pattern.subn(f"var(--{new}", content)
        if n:
            stats[f"var(--{old})"] = stats.get(f"var(--{old})", 0) + n

    if content != original:
        with open(path, "w", encoding="utf-8") as f:
            f.write(content)
    return stats

total_stats = {}
files_changed = 0
for ext in ("*.css", "*.php"):
    for path in glob.glob(os.path.join(ROOT, "**", ext), recursive=True):
        if "/color-migration/" in path:
            continue
        rel_path = os.path.relpath(path, ROOT)
        stats = process_file(path, rel_path)
        if stats:
            files_changed += 1
            for k, v in stats.items():
                total_stats[k] = total_stats.get(k, 0) + v

print(f"files changed: {files_changed}")
print(f"total distinct values/refs substituted: {len(total_stats)}")
print(f"total substitution count: {sum(total_stats.values())}")
