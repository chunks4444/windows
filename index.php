<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="평목은 전통창호, 한옥창호, 목창호, 맞춤창호를 전문으로 하는 창호디자인 스튜디오입니다. 공장에서 담아낼 수 없는 공간의 깊이를 나무문과 나무창문으로 표현합니다.">
    <meta name="keywords" content="전통창호, 목창호, 한옥창호, 맞춤창호, 창호디자인, 나무창호, 한옥문, 나무문, 전통문, 평목">
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
                        <h2 class="values-title">당신의 공간에 빛과 바람의 길을 디자인하세요.</h2>
                        <p class="values-desc">문틀 크기와 비율을 조정하면 실제 제작 가능한<br>맞춤 창호 디자인을 실시간으로 확인할 수 있습니다</p>
                    </div>
                    <div class="values-item">
                        <h2 class="values-title">DESIGN IN REAL TIME</h2>
                        <p class="values-desc">실시간으로 디자인하고, 공방에서 완성합니다.<br>원하는 디자인을 즉시 확인하고
<br>완성된 설계는 실제 제작으로 이어집니다.</p>
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
