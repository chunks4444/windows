<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php define('BOOTSTRAP_LOADED', true); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../lib/meta.php'; ?>
    <?php meta_tags(); ?>
<?php css_tag('/src/css/dashboard.css'); ?>
    <?php $authRequireRole = 's'; include __DIR__ . '/../components/auth_guard.php'; ?>
    <style>
        .adm-home-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
        @media (max-width:1024px) { .adm-home-grid { grid-template-columns:repeat(3,1fr); } }
        @media (max-width:768px)  { .adm-home-grid { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:480px)  { .adm-home-grid { grid-template-columns:1fr; } }
        .adm-home-card {
            display:flex; align-items:center; gap:14px;
            padding:18px; border:1px solid var(--border-md); border-radius:var(--r);
            background:#fff; text-decoration:none; color:inherit;
            transition:border-color .15s, box-shadow .15s;
        }
        .adm-home-card:hover { border-color:var(--teal); box-shadow:0 4px 16px rgba(0,0,0,.06); color:inherit; }
        .adm-home-icon {
            flex-shrink:0; width:42px; height:42px; border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            font-size:19px; background:var(--accent-bg); color:var(--teal);
        }
        .adm-home-title { font-size:14px; font-weight:700; color:var(--text-1); margin-bottom:2px; }
        .adm-home-desc { font-size:12px; color:var(--text-3); }
        .adm-section { margin-bottom:36px; }
        .adm-section-title {
            font-size:13px; font-weight:700; color:var(--teal);
            letter-spacing:.03em; margin:0 0 12px; padding-bottom:8px;
            border-bottom:1px solid var(--border-md);
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/nav.php'; ?>

<div class="db-page" id="adminHomeAuthWall" style="display:none;">
    <div class="db-auth-banner"><p>슈퍼 권한이 필요합니다.</p></div>
</div>

<div class="db-page" id="adminHomePage" style="display:none;">
    <div class="db-header">
        <h1 class="db-title"><i class="bi bi-speedometer2 me-2"></i>관리자</h1>
    </div>

    <div class="adm-section">
        <h2 class="adm-section-title">콘텐츠 관리</h2>
        <div class="adm-home-grid">
            <a href="/src/admin/works.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-images"></i></div>
                <div><div class="adm-home-title">Works 관리</div><div class="adm-home-desc">포트폴리오 작품</div></div>
            </a>
            <a href="/src/admin/blog.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-journal-text"></i></div>
                <div><div class="adm-home-title">블로그 관리</div><div class="adm-home-desc">창호 이야기 글</div></div>
            </a>
            <a href="/src/admin/svg_motifs.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-flower1"></i></div>
                <div><div class="adm-home-title">문양(SVG) 라이브러리</div><div class="adm-home-desc">엔진 삽입용 꽃살 문양</div></div>
            </a>
            <a href="/src/admin/faq.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-question-circle"></i></div>
                <div><div class="adm-home-title">FAQ 관리</div><div class="adm-home-desc">자주 묻는 질문</div></div>
            </a>
            <a href="/src/admin/notice.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-megaphone"></i></div>
                <div><div class="adm-home-title">공지 배너</div><div class="adm-home-desc">상단 띠 배너 관리</div></div>
            </a>
            <a href="/src/admin/collection.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-image"></i></div>
                <div><div class="adm-home-title">컬렉션 관리</div><div class="adm-home-desc">공개 패턴 라이브러리</div></div>
            </a>
            <a href="/src/admin/hero_slides.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-images"></i></div>
                <div><div class="adm-home-title">슬라이드 관리</div><div class="adm-home-desc">메인 히어로 캐러셀</div></div>
            </a>
            <a href="/src/admin/space_cards.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-grid"></i></div>
                <div><div class="adm-home-title">공간 카드 관리</div><div class="adm-home-desc">메인 공간 큐레이션</div></div>
            </a>
            <a href="/src/admin/studio_cards.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-grid-1x2"></i></div>
                <div><div class="adm-home-title">스튜디오 카드 관리</div><div class="adm-home-desc">메인 스튜디오 소개</div></div>
            </a>
            <a href="/src/admin/colors.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-palette"></i></div>
                <div><div class="adm-home-title">컬러 팔레트 관리</div><div class="adm-home-desc">창호 색상 옵션</div></div>
            </a>
        </div>
    </div>

    <div class="adm-section">
        <h2 class="adm-section-title">설정값 관리</h2>
        <div class="adm-home-grid">
            <a href="/src/admin/cost_table.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-calculator"></i></div>
                <div><div class="adm-home-title">원가 테이블</div><div class="adm-home-desc">견적 산출 원가</div></div>
            </a>
            <a href="/src/admin/meta.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-search"></i></div>
                <div><div class="adm-home-title">SEO 메타 관리</div><div class="adm-home-desc">페이지별 title/description</div></div>
            </a>
            <a href="/src/admin/oauth.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-key"></i></div>
                <div><div class="adm-home-title">SNS 로그인 설정</div><div class="adm-home-desc">Google/Kakao/Naver</div></div>
            </a>
            <a href="/src/admin/mail_settings.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-envelope"></i></div>
                <div><div class="adm-home-title">메일 발송 설정</div><div class="adm-home-desc">SMTP 계정/영업·회원 발신주소</div></div>
            </a>
            <a href="/src/admin/render_settings.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-stars"></i></div>
                <div><div class="adm-home-title">AI 렌더링 설정</div><div class="adm-home-desc">OpenAI API Key/품질</div></div>
            </a>
            <a href="/src/admin/engine_settings.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-sliders"></i></div>
                <div><div class="adm-home-title">엔진 기본값 관리</div><div class="adm-home-desc">엔진별 슬라이더 기본값/레이아웃 상수</div></div>
            </a>
        </div>
    </div>

    <div class="adm-section">
        <h2 class="adm-section-title">회원 관리</h2>
        <div class="adm-home-grid">
            <a href="/src/admin/users.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-shield-lock"></i></div>
                <div><div class="adm-home-title">회원 목록/권한</div><div class="adm-home-desc">가입 회원 목록/권한 관리</div></div>
            </a>
        </div>
    </div>

    <div class="adm-section">
        <h2 class="adm-section-title">통계</h2>
        <div class="adm-home-grid">
            <a href="/src/admin/stats.php" class="adm-home-card">
                <div class="adm-home-icon"><i class="bi bi-bar-chart-line"></i></div>
                <div><div class="adm-home-title">접속 통계</div><div class="adm-home-desc">방문자/페이지뷰</div></div>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('pmok_auth_user') || 'null');
    if (!user || user.role !== 's') { document.getElementById('adminHomeAuthWall').style.display = ''; return; }
    document.getElementById('adminHomePage').style.display = '';
});
</script>
</body>
</html>
