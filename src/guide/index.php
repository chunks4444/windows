<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>평목 가이드</title>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/src/css/common.css">
    <link rel="stylesheet" href="/src/css/nav.css">
    <link rel="stylesheet" href="/src/guide/guide.css">
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="guide-landing">

    <!-- 히어로 -->
    <div class="guide-hero">
        <h1>평목 가이드</h1>
        <p>전통 창호 도면 설계부터 AI 렌더링까지,<br>평목 스튜디오의 모든 기능을 안내합니다.</p>
    </div>

    <!-- 카테고리 카드 -->
    <div class="guide-categories">

        <a href="/src/guide/intro.php" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:#E6F4F2;color:#3A8C82;">
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div class="guide-cat-title">평목 스튜디오란?</div>
            <div class="guide-cat-desc">평목이 무엇인지, 어떻게 시작하는지 알아보세요.</div>
            <div class="guide-cat-count">2개 아티클</div>
        </a>

        <a href="/src/guide/studio-classic.php" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:#FFF0EE;color:#cc2200;">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div class="guide-cat-title">스튜디오란?</div>
            <div class="guide-cat-desc">6가지 격자 패턴 엔진의 상세 사용 방법을 안내합니다.</div>
            <div class="guide-cat-count">6개 아티클</div>
        </a>

        <a href="/src/guide/drawing.php" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:#F5F4EE;color:#7A6B40;">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div class="guide-cat-title">도면 관리</div>
            <div class="guide-cat-desc">도면 저장, 버전 관리, PDF·PNG 내보내기 방법을 안내합니다.</div>
            <div class="guide-cat-count">2개 아티클</div>
        </a>

        <a href="/src/guide/render.php" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:#F2F0FB;color:#5A4DB8;">
                <i class="bi bi-stars"></i>
            </div>
            <div class="guide-cat-title">AI 렌더링</div>
            <div class="guide-cat-desc">배경 이미지와 도면을 합성해 AI로 공간을 시각화합니다.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

        <a href="/src/guide/collection.php" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:#FFF8EE;color:#b8894a;">
                <i class="bi bi-collection-fill"></i>
            </div>
            <div class="guide-cat-title">컬렉션</div>
            <div class="guide-cat-desc">공개 라이브러리 패턴을 열람하고 내 보드에 저장하세요.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

        <a href="/src/guide/account.php" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:#EEF3F8;color:#2A6B8C;">
                <i class="bi bi-person-gear"></i>
            </div>
            <div class="guide-cat-title">계정 설정</div>
            <div class="guide-cat-desc">프로필, 비밀번호, 회사 정보를 관리하는 방법을 안내합니다.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

    </div>
</div>

</body>
</html>
