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
        color: rgba(0, 0, 0, 0.95);
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
        <<<<<<< HEAD font-size: 22px;
        =======font-size: 13px;
        >>>>>>>e3f6bf97afb192d9923184735279903cee7db12e font-weight: 700;
        letter-spacing: -0.01em;
        margin-bottom: 0;
    }

    .values-desc {
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 400;
        color: rgba(0, 0, 0, 0.7);
        line-height: 1.7;
        word-break: keep-all;
        margin: 6px 0 0;
    }

    /* FOOTER */
    .footer-copy {
        font-family: 'Inter', sans-serif;
        font-size: 11px;
        font-weight: 300;
        color: rgba(0, 0, 0);
    }

    .footer-link {
        font-family: 'Inter', sans-serif;
        font-size: 11px;
        color: rgba(0, 0, 0, 0.4);
        text-decoration: none;
        transition: color 0.2s;
    }

    .footer-link:hover {
        color: var(--gold);
    }

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

    .values-grid .values-item:first-child {
        padding-left: 0;
    }

    .values-grid .values-item:last-child {
        padding-right: 0;
    }

    .values-grid .values-item:not(:last-child) {
        border-right: 1px solid var(--border);
    }

    @media (max-width: 768px) {
        .values-section {
            padding: 0 24px 32px;
        }

        .pm-symbols-row {
            gap: 24px;
        }

        .pm-symbols-row svg {
            width: 200px;
            height: 200px;
        }

        .values-grid {
            flex-direction: column;
        }

        .values-grid .values-item {
            padding: 14px 0;
            border-right: none !important;
            border-bottom: 1px solid var(--border);
        }

        .values-grid .values-item:last-child {
            border-bottom: none;
        }

        .values-title {
            font-size: 22px;
        }
    }

    @media (max-width: 480px) {
        .values-section {
            padding: 0 20px 24px;
        }

        .pm-symbols-row {
            gap: 16px;
        }

        .pm-symbols-row svg {
            width: 160px;
            height: 160px;
        }

        .values-title {
            font-size: 18px;
        }
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
                        <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0" />
                        <g transform="rotate(60 340 340)">
                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0" />
                        </g>
                        <g transform="rotate(120 340 340)">
                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0" />
                        </g>
                    </svg>
                </a>
                <a href="/src/engine/Sabunteok/Sabunteok.php" class="pm-symbol-link">
                    <svg width="270" height="270" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                        <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0" />
                        <rect class="pm-symbol-bar" x="148" y="317" width="384" height="46" rx="0" />
                        <g transform="rotate(45 340 340)">
                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0" />
                        </g>
                        <g transform="rotate(135 340 340)">
                            <rect class="pm-symbol-bar" x="317" y="148" width="46" height="384" rx="0" />
                        </g>
                    </svg>
                </a>
            </div>
        </section>
        <style>
        .pm-symbol-link {
            display: inline-block;
        }

        .pm-symbol-link svg {
            transition: transform .5s cubic-bezier(.25, .46, .45, .94), fill .25s;
        }

        .pm-symbol-link:hover svg {
            transform: rotate(45deg);
        }

        .pm-symbol-bar {
            fill: #2a2418;
        }

        .pm-symbol-link:hover .pm-symbol-bar {
            fill: #cc2200;
        }

        .values-item {
            cursor: default;
        }

        .values-item .values-title {
            transition: color .2s;
        }

        .values-item:hover .values-title {
            color: #cc2200;
        }
        </style>

        <!-- VALUES -->

        <section class="values-section">
            <div class="values-inner">
                <div class="values-grid">
                    <div class="values-item">
                        <h2 class="values-title">당신의 공간에<br> 빛과 바람의 길을 디자인하세요.</h2>
                        <p class="values-desc">전통을 잇는 정교함, 미래를 여는 디자인</p>
                    </div>
                    <div class="values-item">
                        <h2 class="values-title">당신의<br> 공간을 위한 단 하나의 창(窓)</h2>
                        <p class="values-desc">공장에서 담아내지 못하는 공간의 깊이. <br>당신이 디자인한 세상에 하나뿐인 나무문과 나무 창문으로 빛과 바람의 길을 만드세요.</p>
                    </div>
                </div>
                <hr>
                <p class="values-desc"><strong>"오랜 시간 궁리해 온 새로운 목공 시스템의 첫걸음을 뗍니다."</strong>

목문과 목창을 필요로 하시는 분들, 그리고 직접 만드는 분들 모두에게 더 나은 경험을 제공하고자 합니다.<br>

현재는 개발 중인 단계로, 완성도 높은 플랫폼이 될 수 있도록 여러분의 소중한 의견을 구합니다. 사이트에서 발견하시는 오타나 버그, 혹은 개선이 필요한 부분을 편하게 말씀해 주세요. 보내주시는 피드백은 하나도 잊지 않고 깊이 새겨 반영하겠습니다.아울러, 바쁜 와중에도 귀한 시간 내어 이 고단한 일의 시작에 기꺼이 동참해주신 '서대문의 현인'께 깊은 감사를 드립니다.<br><br>
※ 원활한 테스트를 위해 가급적 PC 환경에서의 접속을 권장합니다.<br><br>
[문의 및 의견 보내실 곳 전화: 010-5295-3086/이메일: chunks4444@gmail.com]<br><br>
전경수 드림                 
                </p>  
  
                             
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