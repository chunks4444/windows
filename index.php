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

    /* VALUES */
    .values-section {
        padding: 120px 48px;
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
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.01em;
        margin-bottom: 16px;
    }

    .values-desc {
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 500;
        color: rgba(0,0,0,0.85);
        line-height: 1.85;
        max-width: 560px;
        word-break: keep-all;
        margin: 0;
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

    @media (max-width: 768px) {
        .values-section { padding: 80px 24px; }
        .navbar { background: rgba(255,255,255,0.95); }
    }
    </style>
</head>
<body>

    <?php include __DIR__ . '/src/components/nav.php'; ?>

    <!-- VALUES -->
    <section class="values-section d-flex flex-column align-items-center">
        <div style="width:100%; max-width:1024px;">

            <!-- 심볼 아이콘 -->
            <div style="display:flex; justify-content:center; gap:40px; padding:80px 0 60px;">
                <a href="/src/engine/sambuntok/sambuntok.php" class="pm-symbol-link">
                    <svg width="400" height="400" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                        <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="23"/>
                        <g transform="rotate(60 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="23"/></g>
                        <g transform="rotate(120 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="23"/></g>
                    </svg>
                </a>
                <a href="/src/engine/Sabunteok/Sabunteok.php" class="pm-symbol-link">
                    <svg width="400" height="400" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                        <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="23"/>
                        <rect class="pm-symbol-bar" x="148" y="317" width="384" height="46" rx="23"/>
                        <g transform="rotate(45 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="23"/></g>
                        <g transform="rotate(135 340 340)"><rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="23"/></g>
                    </svg>
                </a>
            </div>
                <style>
                .pm-symbol-link { display:inline-block; }
                .pm-symbol-bar { fill:#2a2418; transition:fill .25s; }
                .pm-symbol-link:hover .pm-symbol-bar { fill:#cc2200; }
                .values-item { cursor:default; }
                .values-item .values-title { transition:color .2s; }
                .values-item:hover .values-title { color:#cc2200; }
                </style>

            <div style="display:flex; gap:0; border-top:1px solid var(--border);">
                <div class="values-item" style="flex:1; border-bottom:none; border-right:1px solid var(--border); padding-right:40px;">
                    <h2 class="values-title">당신의 공간에 빛과 바람의 길을 설계합니다.</h2>
                    <p class="values-desc">전통을 잇는 정교함, 미래를 여는 디자인</p>
                </div>
                <div class="values-item" style="flex:1; border-bottom:none; border-right:1px solid var(--border); padding:52px 40px;">
                    <h2 class="values-title">기다림 대신 창조를</h2>
                    <p class="values-desc">장인의 작업실에서 수개월을 기다려야 했던 전통 창호를, 이제 당신이 직접 생성하고 수정하여 즉시 제작을 의뢰할 수 있습니다. 당신은 오직 영감에 집중하세요.</p>
                </div>
                <div class="values-item" style="flex:1; border-bottom:none; border-right:1px solid var(--border); padding:52px 40px;">
                    <h2 class="values-title">디지털로 잇는 장인의 숨결</h2>
                    <p class="values-desc">정밀한 joinery(결구) 기법부터 창호의 미세한 간격까지, 전통 목공의 원리를 계산하여 가장 완벽한 설계도를 완성합니다.</p>
                </div>
                <div class="values-item" style="flex:1; border-bottom:none; padding-left:40px;">
                    <h2 class="values-title">당신의 공간을 위한 단 하나의 창(窓)</h2>
                    <p class="values-desc">기성품이 담아내지 못하는 공간의 깊이. 직접 설계한 패턴으로 세상에 하나뿐인 나무문과 나무 창문으로 빛과 바람의 길을 디자인하세요.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- FOOTER -->
    <div class="border-top d-flex justify-content-between align-items-center px-4 py-4">
        <p class="footer-copy mb-0">© <?= date('Y') ?> 평목(平木). All rights reserved.</p>
        <a href="https://pyeongmok.com" class="footer-link">pyeongmok.com</a>
    </div>


</body>
</html>
