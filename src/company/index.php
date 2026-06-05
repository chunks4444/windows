<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="평목은 전통창호와 한국 목공예의 미를 현대 공간에 담아내는 창호디자인 스튜디오입니다.">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;600;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
    :root {
        --gold:   #b8894a;
        --red:    #cc2200;
        --border: rgba(12,12,11,0.1);
        --text-1: #111;
        --text-2: #555;
        --text-3: #999;
    }

    body {
        font-family: 'Inter', -apple-system, sans-serif;
        color: var(--text-1);
        background: #fff;
    }

    /* ── HERO ─────────────────────────────────────── */
    .ab-hero {
        position: relative;
        min-height: 80vh;
        display: flex;
        align-items: flex-end;
        padding: 0;
        border-bottom: 1px solid var(--border);
        overflow: hidden;
        margin-top: -68px; /* nav 높이 상쇄 */
    }

    .ab-hero-bg {
        position: absolute;
        inset: 0;
        background: url('/src/img/web/bg_1.jpeg') center center / cover no-repeat;
    }

    .ab-hero-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.5);
    }

    .ab-hero-content {
        position: relative;
        z-index: 1;
        width: 100%;
        padding: 80px 0 72px;
    }

    .ab-hero-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #fff;
        margin-bottom: 20px;
    }

    .ab-hero-title {
        font-family: 'Noto Sans KR', sans-serif;
        font-size: clamp(32px, 5vw, 64px);
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.02em;
        color: #fff;
        margin-bottom: 28px;
    }

    .ab-hero-title em {
        font-style: normal;
        color: #fff;
    }

    .ab-hero-desc {
        font-size: 15px;
        font-weight: 400;
        line-height: 1.9;
        color: #fff;
        max-width: 560px;
        word-break: keep-all;
    }

    /* ── SECTION COMMON ───────────────────────────── */
    .ab-section {
        padding: 80px 0;
        border-bottom: 1px solid var(--border);
    }

    .ab-section--no-border {
        border-bottom: none;
    }

    .ab-full-divider {
        border: none;
        border-top: 1px solid var(--border);
        margin: 0;
        width: 100vw;
        position: relative;
        left: 50%;
        transform: translateX(-50%);
    }

    .ab-section-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--red);
        margin-bottom: 16px;
    }

    .ab-section-title {
        font-family: 'Noto Sans KR', sans-serif;
        font-size: clamp(22px, 3vw, 32px);
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: -0.01em;
        margin-bottom: 24px;
    }

    .ab-section-body {
        font-size: 14px;
        line-height: 1.9;
        color: var(--text-2);
        word-break: keep-all;
    }

    /* ── PHILOSOPHY COLS ─────────────────────────── */
    .ab-phil-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 64px;
        align-items: stretch;
        margin-top: 40px;
    }

    .ab-phil-left {
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding-top: 28px;
        padding-bottom: 28px;
    }

    .ab-phil-grid {
        display: flex;
        flex-direction: column;
        gap: 0;
        border-top: 1px solid var(--border);
        border-left: none;
    }

    .ab-phil-item {
        padding: 28px 0;
        border-bottom: 1px solid var(--border);
    }

    @media (max-width: 768px) {
        .ab-phil-cols { grid-template-columns: 1fr; gap: 40px; }
    }

    /* ── PHILOSOPHY TEXT ─────────────────────────── */
    .ab-phil-text {
        margin-top: 40px;
        max-width: 600px;
    }

    .ab-phil-text p {
        font-size: 16px;
        line-height: 2.0;
        color: var(--text-1);
        margin-bottom: 28px;
        word-break: keep-all;
    }

    .ab-phil-text p:last-child {
        margin-bottom: 0;
    }

    /* ── PHILOSOPHY GRID ──────────────────────────── */

    .ab-phil-num {
        display: block;
        font-size: 28px;
        font-weight: 700;
        color: var(--text-1);
        margin-bottom: 16px;
        letter-spacing: -0.02em;
    }

    .ab-phil-name {
        font-family: 'Noto Sans KR', sans-serif;
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .ab-phil-desc {
        font-size: 13px;
        line-height: 1.8;
        color: var(--text-2);
        word-break: keep-all;
        margin: 0;
    }

    /* ── STUDIO TOOLS ─────────────────────────────── */
    .ab-tools-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-top: 48px;
    }

    .ab-tool-card {
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 36px 32px;
        text-decoration: none;
        color: inherit;
        display: flex;
        gap: 24px;
        align-items: flex-start;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .ab-tool-card:hover {
        border-color: var(--red);
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        color: inherit;
    }

    .ab-tool-icon {
        flex-shrink: 0;
        opacity: 0.7;
    }

    .ab-tool-card:hover .ab-tool-icon {
        opacity: 1;
    }

    .ab-tool-name {
        font-family: 'Noto Sans KR', sans-serif;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .ab-tool-desc {
        font-size: 13px;
        line-height: 1.8;
        color: var(--text-2);
        margin: 0;
        word-break: keep-all;
    }

    /* ── INFO ─────────────────────────────────────── */
    .ab-info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0;
        border-top: 1px solid var(--border);
        margin-top: 48px;
    }

    .ab-info-item {
        padding: 32px 0 32px;
        border-bottom: 1px solid var(--border);
        padding-right: 32px;
    }

    .ab-info-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text-3);
        margin-bottom: 10px;
    }

    .ab-info-value {
        font-size: 14px;
        font-weight: 500;
        line-height: 1.6;
        color: var(--text-1);
        margin: 0;
    }

    .ab-info-value a {
        color: inherit;
        text-decoration: none;
    }

    .ab-info-value a:hover {
        color: var(--red);
    }

    /* ── FULL-WIDTH BOXES ────────────────────────── */
    .ab-contact-box,
    .ab-phil-box,
    .ab-studio-box {
        background: #f7f7f6;
        padding: 80px 0;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
    }

    /* ── CONTACT ─────────────────────────────────── */
    .ab-contact-cols {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 40px;
        margin-top: 40px;
        margin-bottom: 40px;
    }

    .ab-contact-map {
        height: 280px;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid var(--border);
    }

    .ab-contact-dl {
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0;
        border-top: 1px solid var(--border);
    }

    .ab-contact-row {
        display: grid;
        grid-template-columns: 72px 1fr;
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid var(--border);
    }

    .ab-contact-row dt {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.05em;
        color: var(--text-3);
        padding-top: 2px;
    }

    .ab-contact-row dd {
        font-size: 14px;
        color: var(--text-1);
        margin: 0;
        line-height: 1.6;
    }

    .ab-contact-row dd a {
        color: inherit;
        text-decoration: none;
    }

    .ab-contact-row dd a:hover {
        color: var(--red);
    }

    @media (max-width: 768px) {
        .ab-contact-cols { grid-template-columns: 1fr; }
        .ab-contact-map { height: 220px; }
    }

    /* ── GALLERY ─────────────────────────────────── */
    .ab-gallery {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-top: 40px;
    }

    .ab-gallery-item {
        overflow: hidden;
        border-radius: 4px;
        aspect-ratio: 4 / 3;
    }

    .ab-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.4s ease;
    }

    .ab-gallery-item:hover img {
        transform: scale(1.04);
    }

    @media (max-width: 640px) {
        .ab-gallery { grid-template-columns: 1fr; }
    }

    /* ── FOOTER ───────────────────────────────────── */
    .ab-footer {
        padding: 32px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ab-footer-copy {
        font-size: 11px;
        font-weight: 300;
        color: var(--text-3);
        margin: 0;
    }

    .ab-footer-link {
        font-size: 11px;
        color: var(--text-3);
        text-decoration: none;
        transition: color 0.2s;
    }

    .ab-footer-link:hover { color: var(--gold); }

    /* ── RESPONSIVE ───────────────────────────────── */
    @media (max-width: 768px) {
        .ab-hero   { padding: 64px 0 56px; }
        .ab-section { padding: 56px 0; }
        .ab-phil-grid { grid-template-columns: 1fr; }
        .ab-tools-grid { grid-template-columns: 1fr; }
        .ab-info-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 480px) {
        .ab-info-grid { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<!-- HERO (full-width, outside container) -->
<section class="ab-hero">
    <div class="ab-hero-bg"></div>
    <div class="ab-hero-overlay"></div>
    <div class="ab-hero-content">
        <div class="container">
            <p class="ab-hero-label">About 평목</p>
            <h2 class="ab-hero-title">나무로 만드는<br>빛과 바람의 길,<em>평목</em></h2>
            <p class="ab-hero-desc">평목(平木)은 전통창호의 아름다움을 현대 공간에 담아내는 창호디자인 스튜디오입니다. <br>
            수백 년을 이어온 문살 기법을 디지털 도구로 재해석하여, 누구나 자신만의 창호를 <br>
            직접 설계하고 제작까지 연결할 수 있는 환경을 만들어갑니다.</p>
        </div>
    </div>
</section>

<!-- PHILOSOPHY — full-width box -->
<div class="ab-phil-box">
    <div class="container">
        <p class="ab-section-label">Philosophy</p>
        <div class="ab-phil-cols">
            <div class="ab-phil-left">
                <h2 class="ab-section-title">평목(平木)<br><span style="font-size:0.6em;font-weight:400;color:var(--text-3);letter-spacing:0.05em;">workgroup pyeongmok</span></h2>
                <div class="ab-phil-text">
                    <p>곡식을 '되'나 '말' 단위로 깎아낼 때 쓰는<br>도구를 '평미레'라고 하는데<br>여기에 해당하는 한자어가 '평목(平木)'입니다.</p>
                    <p>평목은 넘쳐도 아니 되고,<br>모자라도 아니 되는,<br>균형을 잡아 주는 일을 합니다.</p>
                    <p>견고한 나무를 다듬는 평목의 목수들은<br>시간에 마모되지 않고 시간 속에서<br>품격이 더해지는 창호와 가구를 만들고자 합니다.</p>
                </div>
            </div>
            <div class="ab-phil-right">
                <div class="ab-phil-grid">
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">01</p>
                        <h3 class="ab-phil-name">오랜 시간 축적된 지혜와 다듬어진 원리를 공간에 담습니다.</h3>
                        <p class="ab-phil-desc">어금육모, 솟을살, 아자살 등 수백 년을 이어온 전통 문살 기법을 현대적 감각으로 재해석합니다. <br>형태는 단순해지고, 정신은 깊어집니다.</p>
                    </div>
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">02</p>
                        <h3 class="ab-phil-name">실시간 설계</h3>
                        <p class="ab-phil-desc">치수와 비율을 조정하는 순간 도면이 실시간으로 완성됩니다. <br>설계와 제작 사이의 거리를 최소화하여 아이디어가 바로 현실이 됩니다.</p>
                    </div>
                    <div class="ab-phil-item">
                        <p class="ab-phil-num">03</p>
                        <h3 class="ab-phil-name">맞춤 제작</h3>
                        <p class="ab-phil-desc">모든 창호는 공간과 사람에 맞게 설계됩니다. <br>완성된 도면은 실제 제작으로 이어지며, 세상에 하나뿐인 창호가 탄생합니다.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- STUDIO -->
<div class="container">
    <section class="ab-section">
        <p class="ab-section-label">Studio</p>
        <h2 class="ab-section-title">직접 설계해보세요.</h2>
        <p class="ab-section-body">평목 스튜디오는 브라우저에서 바로 사용할 수 있는 창호 설계 도구입니다. 문틀 크기, 살 간격, 패턴을 조정하며 나만의 창호를 완성해보세요.</p>
        <div class="ab-tools-grid">
            <a href="/src/engine/Sabunteok/Sabunteok.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/>
                    <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="4"/>
                    <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                    <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                </svg>
                <div>
                    <p class="ab-tool-name">Square Grid (사분턱)</p>
                    <p class="ab-tool-desc">가로살과 세로살이 직각으로 교차하는 정방형 문살 패턴. 단아하고 절제된 아름다움을 표현합니다.</p>
                </div>
            </a>
            <a href="/src/engine/sambuntok/sambuntok.php" class="ab-tool-card">
                <svg class="ab-tool-icon" width="48" height="48" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/>
                    <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                    <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="4"/></g>
                </svg>
                <div>
                    <p class="ab-tool-name">Triangle Grid (삼분턱)</p>
                    <p class="ab-tool-desc">세 방향의 살이 60° 각도로 교차하는 삼각형 문살 패턴. 역동적이고 세련된 인상을 공간에 더합니다.</p>
                </div>
            </a>
        </div>
    </section>
</div>

<!-- INFO — full-width box -->
<div class="ab-contact-box">
    <div class="container">
        <p class="ab-section-label">Contact</p>
        <h2 class="ab-section-title">함께 만들어가요.</h2>
        <p class="ab-section-body">설계 문의, 제작 상담, 협업 제안 모두 환영합니다.</p>

        <div class="ab-contact-cols">
            <div class="ab-contact-info">
                <dl class="ab-contact-dl">
                    <div class="ab-contact-row">
                        <dt>주소</dt>
                        <dd>경기도 양평군 양서면 도곡리 107-2</dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>전화</dt>
                        <dd><a href="tel:+82705124458">070-5124-4568</a></dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>이메일</dt>
                        <dd><a href="mailto:pyeongmok@gmail.com">pyeongmok@gmail.com</a></dd>
                    </div>
                    <div class="ab-contact-row">
                        <dt>Instagram</dt>
                        <dd><a href="https://instagram.com/pyeongmok_1" target="_blank">@pyeongmok_1</a></dd>
                    </div>
                </dl>
            </div>
            <div class="ab-contact-map">
                <iframe
                    src="https://maps.google.com/maps?q=경기도+양평군+양서면+도곡리+107-2&output=embed&z=15"
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        <div class="ab-gallery">
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0313.jpeg" alt="공방 사진 1">
            </div>
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0315.jpeg" alt="공방 사진 2">
            </div>
            <div class="ab-gallery-item">
                <img src="/src/img/web/IMG_0889.jpeg" alt="공방 사진 4">
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<div class="container">
    <div class="ab-footer">
        <p class="ab-footer-copy">© <?= date('Y') ?> 평목(平木). All rights reserved.</p>
        <a href="https://pyeongmok.com" class="ab-footer-link">pyeongmok.com</a>
    </div>
</div>

</body>
</html>
