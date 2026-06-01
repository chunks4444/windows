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
    <link rel="stylesheet" href="/src/css/index.css">
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
