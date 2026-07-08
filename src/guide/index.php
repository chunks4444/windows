<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/../lib/meta.php'; meta_tags(); ?>
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?php css_tag('/src/css/common.css'); ?>
    <?php css_tag('/src/css/nav.css'); ?>
    <?php css_tag('/src/guide/guide.css'); ?>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="guide-landing">

    <!-- 히어로 -->
    <div class="guide-hero">
        <div class="guide-hero-inner">
            <p class="guide-hero-label">Guide</p>
            <h1>가이드</h1>
            <p class="guide-hero-sub">전통 창호 도면 설계부터 AI 렌더링까지, 평목 스튜디오의 모든 기능을 안내합니다.</p>
        </div>
    </div>

    <!-- 카테고리 카드 -->
    <div class="guide-categories">

        <a href="/guide/intro" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div class="guide-cat-title">평목 스튜디오란?</div>
            <div class="guide-cat-desc">평목이 무엇인지, 어떻게 시작하는지 알아보세요.</div>
            <div class="guide-cat-count">2개 아티클</div>
        </a>

        <a href="/guide/studio-classic" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--danger-tint);color:#000;">
                <svg width="22" height="22" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                    <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                    <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                    <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="23"/>
                    <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="23"/>
                    <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="23"/>
                </svg>
            </div>
            <div class="guide-cat-title">스튜디오란?</div>
            <div class="guide-cat-desc">6가지 격자 패턴 엔진의 상세 사용 방법을 안내합니다.</div>
            <div class="guide-cat-count">6개 아티클</div>
        </a>

        <a href="/guide/drawing" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div class="guide-cat-title">도면 관리</div>
            <div class="guide-cat-desc">도면 저장, 버전 관리, PDF·PNG 내보내기 방법을 안내합니다.</div>
            <div class="guide-cat-count">2개 아티클</div>
        </a>

        <a href="/guide/render" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-stars"></i>
            </div>
            <div class="guide-cat-title">AI 렌더링</div>
            <div class="guide-cat-desc">배경 이미지와 도면을 합성해 AI로 공간을 시각화합니다.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

        <a href="/guide/collection" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-collection-fill"></i>
            </div>
            <div class="guide-cat-title">컬렉션</div>
            <div class="guide-cat-desc">공개 라이브러리 패턴을 열람하고 내 보드에 저장하세요.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

        <a href="/guide/account" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-person-gear"></i>
            </div>
            <div class="guide-cat-title">계정 설정</div>
            <div class="guide-cat-desc">프로필, 비밀번호, 회사 정보를 관리하는 방법을 안내합니다.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

        <a href="/guide/order" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-cart-check"></i>
            </div>
            <div class="guide-cat-title">주문</div>
            <div class="guide-cat-desc">견적요청 방법과 주문 흐름, 도면 잠금 정책을 안내합니다.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

        <a href="/guide/delivery" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-truck"></i>
            </div>
            <div class="guide-cat-title">배송</div>
            <div class="guide-cat-desc">배송 방법·배송비 안내 및 반품·교환 정책을 확인하세요.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

        <a href="/guide/faq" class="guide-cat-card">
            <div class="guide-cat-icon" style="background:var(--accent-tint);color:#000;">
                <i class="bi bi-patch-question-fill"></i>
            </div>
            <div class="guide-cat-title">FAQ</div>
            <div class="guide-cat-desc">자주 묻는 질문: 치수 입력, 견적 요청, 목재·마감 선택 등을 안내합니다.</div>
            <div class="guide-cat-count">1개 아티클</div>
        </a>

    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
