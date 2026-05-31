<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>평목 — 시간 속에서 품격이 더해지는</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@400;600;700;900&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    :root {
        --gold: #b8894a;
        --border: rgba(12, 12, 11, 0.1);
    }

    body {
        font-family: 'Noto Serif KR', 'Georgia', serif;
        font-weight: 900;
    }

    /* HERO WRAPPER */
    .home-wrapper {
        min-height: calc(100vh - 68px);
        display: flex;
        flex-direction: column;
    }

    .pm-hero {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* VALUES */
    .values-section {
        padding: 0 48px 40px;
    }

    .values-inner {
        width: 100%;
        max-width: 1024px;
        margin: 0 auto;
    }

    .values-heading-main {
        font-family: 'Noto Serif KR', serif;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.02em;
        word-break: keep-all;
    }

    .values-heading-sub {
        font-family: 'Noto Serif KR', serif;
        font-weight: 400;
        color: rgba(0,0,0,0.95);
    }

    .values-item {
        padding: 52px 0;
        border-bottom: 1px solid var(--border);
    }

    .values-item:first-of-type {
        border-top: 1px solid var(--border);
    }

    .values-title {
        font-family: 'Noto Serif KR', serif;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: -0.01em;
        margin-bottom: 0;
    }

    .values-desc {
        font-family: 'Inter', sans-serif;
        font-size: 12px;
        font-weight: 400;
        color: rgba(0,0,0,0.55);
        line-height: 1.7;
        word-break: keep-all;
        margin: 6px 0 0;
    }

    /* FOOTER */
    .footer-copy {
        font-family: 'Inter', sans-serif;
        font-size: 11px;
        font-weight: 300;
        color: rgba(0,0,0,0.4);
    }

    .footer-link {
        font-family: 'Inter', sans-serif;
        font-size: 11px;
        color: rgba(0,0,0,0.4);
        text-decoration: none;
        transition: color 0.2s;
    }

    .footer-link:hover { color: var(--gold); }

    /* SYMBOLS */
    .pm-symbols-row {
        display: flex;
        justify-content: center;
        gap: 40px;
    }

    /* VALUES GRID */
    .values-grid {
        display: flex;
        gap: 0;
        border-top: 1px solid var(--border);
    }

    .values-grid .values-item {
        flex: 1;
        border-bottom: none;
        padding: 24px 40px;
    }

    .values-grid .values-item:first-child { padding-left: 0; }
    .values-grid .values-item:last-child  { padding-right: 0; }

    .values-grid .values-item:not(:last-child) {
        border-right: 1px solid var(--border);
    }

    @media (max-width: 768px) {
        .values-section { padding: 0 24px 32px; }

        .pm-symbols-row { gap: 24px; }
        .pm-symbols-row svg { width: 200px; height: 200px; }

        .values-grid { flex-direction: column; }
        .values-grid .values-item {
            padding: 14px 0;
            border-right: none !important;
            border-bottom: 1px solid var(--border);
        }
        .values-grid .values-item:last-child { border-bottom: none; }
        .values-title { font-size: 22px; }
    }

    @media (max-width: 480px) {
        .values-section { padding: 0 20px 24px; }
        .pm-symbols-row { gap: 16px; }
        .pm-symbols-row svg { width: 160px; height: 160px; }
        .values-title { font-size: 18px; }
    }
    </style>
</head>
<body>

    <?php include __DIR__ . '/src/components/nav.php'; ?>

    <div class="home-wrapper">

    <!-- HERO -->
    <section class="pm-hero">
        <div class="pm-symbols-row">
            <a href="/src/engine/sambuntok/sambuntok.php" class="pm-symbol-link">
                <svg width="270" height="270" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                    <g transform="rotate(60 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/></g>
                    <g transform="rotate(120 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/></g>
                </svg>
            </a>
            <a href="/src/engine/Sabunteok/Sabunteok.php" class="pm-symbol-link">
                <svg width="270" height="270" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/>
                    <rect class="pm-symbol-bar" x="148" y="317" width="384" height="46" rx="0"/>
                    <g transform="rotate(45 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/></g>
                    <g transform="rotate(135 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0"/></g>
                </svg>
            </a>
        </div>
    </section>
    <style>
    .pm-symbol-link { display:inline-block; }
    .pm-symbol-link svg { transition: transform .5s cubic-bezier(.25,.46,.45,.94), fill .25s; }
    .pm-symbol-link:hover svg { transform: rotate(45deg); }
    .pm-symbol-bar { fill:#2a2418; }
    .pm-symbol-link:hover .pm-symbol-bar { fill:#cc2200; }
    .values-item { cursor:default; }
    .values-item .values-title { transition:color .2s; }
    .values-item:hover .values-title { color:#cc2200; }
    </style>

    <!-- VALUES -->
    <section class="values-section">
        <div class="values-inner">
            <div class="values-grid">
                <div class="values-item">
                    <h2 class="values-title">당신의 공간에 빛과 바람의 길을 설계합니다.</h2>
                    <p class="values-desc">전통을 잇는 정교함, 미래를 여는 디자인</p>
                </div>
                <div class="values-item d-none">
                    <h2 class="values-title">기다림 대신 창조를</h2>
                    <p class="values-desc">장인의 작업실에서 수개월을 기다려야 했던 전통 창호를, 이제 당신이 직접 생성하고 수정하여 즉시 제작을 의뢰할 수 있습니다. 당신은 오직 영감에 집중하세요.</p>
                </div>
                <div class="values-item">
                    <h2 class="values-title">디지털로 잇는 장인의 숨결</h2>
                    <p class="values-desc">정밀한 joinery(결구) 기법부터 창호의 미세한 간격까지, 전통 목공의 원리를 계산하여 가장 완벽한 설계도를 완성합니다.</p>
                </div>
                <div class="values-item">
                    <h2 class="values-title">당신의 공간을 위한 단 하나의 창(窓)</h2>
                    <p class="values-desc">기성품이 담아내지 못하는 공간의 깊이. 직접 설계한 패턴으로 세상에 하나뿐인 나무문과 나무 창문으로 빛과 바람의 길을 디자인하세요.</p>
                </div>
            </div>
        </div>
    </section>

    </div><!-- home-wrapper -->

    <!-- FOOTER -->
    <div class="border-top d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 px-4 py-4">
        <p class="footer-copy mb-0">© <?= date('Y') ?> 평목(平木). All rights reserved.</p>
        <a href="https://pyeongmok.com" class="footer-link">pyeongmok.com</a>
    </div>


</body>
</html>
