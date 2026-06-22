<?php
require_once __DIR__ . '/../lib/logger.php';
require_once __DIR__ . '/../lib/meta.php';
require_once __DIR__ . '/../lib/db.php';

// 상단 네비게이션 공통 컴포넌트
// 포함하는 페이지에서 Bootstrap CSS/JS가 없으면 자동으로 로드합니다.
if (!defined('BOOTSTRAP_LOADED')) {
    define('BOOTSTRAP_LOADED', true);
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
}
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">';
?>
<title>평목 - DESIGN IN REAL TIME</title>
<?php css_tag('/src/css/common.css'); ?>
<?php css_tag('/src/css/nav.css'); ?>

<?php
// 현재 페이지 파일명으로 active 항목 판별
$currentFile = basename($_SERVER['PHP_SELF']);
$isSquare     = (stripos($_SERVER['PHP_SELF'], '/square/') !== false);
$isCross      = (stripos($_SERVER['PHP_SELF'], '/cross/') !== false);
$isClassic    = (stripos($_SERVER['PHP_SELF'], '/classic/') !== false);
$isDiamond  = (!$isSquare && !$isCross && !$isClassic && stripos($currentFile, 'diamond') !== false);
$isTriangle  = (stripos($currentFile, 'triangle') !== false);
$isHexagon   = (stripos($_SERVER['PHP_SELF'], '/hexagon/') !== false);
$isIndex      = ($currentFile === 'index.php' || $_SERVER['PHP_SELF'] === '/');
$isAbout      = (strpos($_SERVER['PHP_SELF'], '/company/') !== false);
$isLibrary    = (strpos($_SERVER['PHP_SELF'], '/collection/') !== false);
$isWork       = (strpos($_SERVER['PHP_SELF'], '/portfolio/') !== false);
$isBlog       = (strpos($_SERVER['PHP_SELF'], '/blog/') !== false);
$isGuide      = (strpos($_SERVER['PHP_SELF'], '/guide/') !== false || $isBlog);

// Studio 드롭다운: 메인페이지 카드와 동일한 제목/순서를 쓰도록 DB에서 가져온다
try {
    $navStudioCards = db()->query('SELECT engine_key, title FROM studio_cards WHERE is_active=1 ORDER BY sort_order, id')->fetchAll();
} catch (Exception $e) {
    $navStudioCards = [];
}
$navStudioDefaults = [
    ['engine_key' => 'classic',  'title' => 'Classic Lattice'],
    ['engine_key' => 'square',   'title' => 'Square Lattice'],
    ['engine_key' => 'cross',    'title' => 'Cross Lattice'],
    ['engine_key' => 'triangle', 'title' => 'Triangle Lattice'],
    ['engine_key' => 'diamond',  'title' => 'Diamond Lattice'],
    ['engine_key' => 'hexagon',  'title' => 'Hexagon Lattice'],
];
$navStudioItems = !empty($navStudioCards) ? $navStudioCards : $navStudioDefaults;
$navStudioActive = [
    'classic'  => $isClassic,
    'square'   => $isSquare,
    'cross'    => $isCross,
    'diamond'  => $isDiamond,
    'triangle' => $isTriangle,
    'hexagon'  => $isHexagon,
];
$navStudioIcons = [
    'classic' => '<svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <g transform="rotate(90 340 340)">
                <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="23"/>
                <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="23"/>
                <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="23"/>
            </g>
        </svg>',
    'square' => '<svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
            <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
        </svg>',
    'cross' => '<svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <g transform="rotate(45 340 340)">
                <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
                <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
            </g>
        </svg>',
    'diamond' => '<svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
            <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="23"/>
            <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
            <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
        </svg>',
    'triangle' => '<svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
            <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
            <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
        </svg>',
    'hexagon' => '<svg width="27" height="27" viewBox="0 0 680 680" fill="none" xmlns="http://www.w3.org/2000/svg">
            <polyline points="210,265 340,190 470,265" stroke="currentColor" stroke-width="32" stroke-linejoin="round" stroke-linecap="round"/>
            <line x1="210" y1="265" x2="210" y2="415" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="470" y1="265" x2="470" y2="415" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="210" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
            <line x1="470" y1="415" x2="340" y2="490" stroke="currentColor" stroke-width="32" stroke-linecap="round"/>
        </svg>',
];
?>
<nav class="pm-navbar navbar navbar-expand-lg fixed-top px-4 py-3">
    <a href="/" class="navbar-brand d-flex align-items-center">
        <img src="/src/assets/logo.png"
             srcset="/src/assets/logo.png 1x, /src/assets/logo@2x.png 2x"
             alt="평목" class="pm-nav-logo">
        <span class="pm-nav-tagline"> </span>
    </a>
    <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#pmNavMenu"
            aria-controls="pmNavMenu" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="pmNavMenu">
        <ul class="navbar-nav gap-3">
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">스튜디오</a>
                <ul class="dropdown-menu">
                    <?php foreach ($navStudioItems as $navItem):
                        $navKey = $navItem['engine_key'];
                    ?>
                    <li><a class="dropdown-item <?= ($navStudioActive[$navKey] ?? false) ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/<?= htmlspecialchars($navKey) ?>/<?= htmlspecialchars($navKey) ?>.php">
                        <?= $navStudioIcons[$navKey] ?? '' ?>
                        <?= htmlspecialchars($navItem['title']) ?>
                    </a></li>
                    <?php endforeach; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="/src/mypage/dashboard.php">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="8" height="8" rx="1.5"/>
                            <rect x="13" y="3" width="8" height="8" rx="1.5"/>
                            <rect x="3" y="13" width="8" height="8" rx="1.5"/>
                            <rect x="13" y="13" width="8" height="8" rx="1.5"/>
                        </svg>
                        대시보드
                    </a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a href="/src/collection/" class="nav-link dropdown-toggle <?= $isLibrary ? 'active' : '' ?>"
                   data-bs-toggle="dropdown" aria-expanded="false">컬렉션</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="/src/collection/"><i class="bi bi-collection"></i>전체 컬렉션</a></li>
                    <li id="navBoardSection" style="display:none;">
                        <hr class="dropdown-divider">
                        <span class="dropdown-header" style="font-size:10px;letter-spacing:.06em;color:#aaa;padding:4px 16px 2px;">내 보드</span>
                    </li>
                    <div id="navBoardList"></div>
                </ul>
            </li>
            <li class="nav-item"><a href="/src/portfolio/" class="nav-link <?= $isWork ? 'active' : '' ?>">포트폴리오</a></li>
            <li class="nav-item dropdown">
                <a href="/src/guide/" class="nav-link dropdown-toggle <?= $isGuide ? 'active' : '' ?>" data-bs-toggle="dropdown" aria-expanded="false">가이드</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?= $isBlog ? 'active' : '' ?>" href="/src/blog/"><i class="bi bi-journal-text me-2"></i>블로그</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/src/guide/"><i class="bi bi-book me-2"></i>가이드 홈</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/src/guide/intro.php"><i class="bi bi-info-circle me-2"></i>스튜디오 소개</a></li>
                    <li><a class="dropdown-item" href="/src/guide/studio-classic.php"><i class="bi bi-pencil-square me-2"></i>스튜디오 사용법</a></li>
                    <li><a class="dropdown-item" href="/src/guide/drawing.php"><i class="bi bi-folder2-open me-2"></i>도면 관리</a></li>
                    <li><a class="dropdown-item" href="/src/guide/render.php"><i class="bi bi-stars me-2"></i>AI 렌더링</a></li>
                    <li><a class="dropdown-item" href="/src/guide/collection.php"><i class="bi bi-collection-fill me-2"></i>컬렉션</a></li>
                    <li><a class="dropdown-item" href="/src/guide/account.php"><i class="bi bi-person-gear me-2"></i>계정 설정</a></li>
                    <li><a class="dropdown-item" href="/src/guide/order.php"><i class="bi bi-cart-check me-2"></i>주문</a></li>
                    <li><a class="dropdown-item" href="/src/guide/delivery.php"><i class="bi bi-truck me-2"></i>배송</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/#faqAccordion"><i class="bi bi-question-circle me-2"></i>FAQ</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a href="/src/company/" class="nav-link dropdown-toggle <?= $isAbout ? 'active' : '' ?>" data-bs-toggle="dropdown" aria-expanded="false">회사소개</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/src/company/"><i class="bi bi-book me-2"></i>평목 소개</a></li>
                    <li><a class="dropdown-item" href="/src/company/#studio"><i class="bi bi-pencil-square me-2"></i>스튜디오</a></li>
                    <li><a class="dropdown-item" href="/src/company/#contact"><i class="bi bi-envelope me-2"></i>문의, 제작 상담, 협업 제안</a></li>
                </ul>
            </li>
            <li class="nav-item d-none"><a href="#" class="nav-link">Joiner</a></li>
            <!-- 비로그인 -->
            <li class="nav-item" id="navLoginBtn">
                <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#authModal">로그인</a>
            </li>
            <!-- 로그인 후 -->
            <li class="nav-item dropdown" id="navUserMenu" style="display:none;">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-1"></i><span id="navUserEmail"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text" style="font-size:11px;color:#aaa;padding:8px 18px 4px;">마지막 접속<br><span id="navLastLogin" style="color:#888;font-weight:600;">—</span></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/src/mypage/profile.php"><i class="bi bi-person me-1"></i>프로필</a></li>
                    <li><a class="dropdown-item" href="/src/mypage/company.php"><i class="bi bi-building me-1"></i>회사 정보</a></li>
                    <li><a class="dropdown-item" href="/src/mypage/dashboard.php"><i class="bi bi-grid me-1"></i>대시보드</a></li>
                    <li><a class="dropdown-item" href="/src/mypage/dashboard.php#boards"><i class="bi bi-collection me-1"></i>내 보드</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="authLogout();return false;"><i class="bi bi-box-arrow-right me-1"></i>로그아웃</a></li>
                    <li id="navAdminLink" style="display:none;"><hr class="dropdown-divider"></li>
                    <li id="navAdminMenu" style="display:none;"><a class="dropdown-item" href="/src/admin/"><i class="bi bi-speedometer2 me-1"></i>어드민</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
<?php if (!defined('BOOTSTRAP_JS_LOADED')): define('BOOTSTRAP_JS_LOADED', true); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="/src/js/visitor-log.js" defer></script>
<?php endif; ?>
<?php include __DIR__ . '/auth_modal.php'; ?>
