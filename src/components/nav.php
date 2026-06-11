<?php
require_once __DIR__ . '/../lib/logger.php';

// 상단 네비게이션 공통 컴포넌트
// 포함하는 페이지에서 Bootstrap CSS/JS가 없으면 자동으로 로드합니다.
if (!defined('BOOTSTRAP_LOADED')) {
    define('BOOTSTRAP_LOADED', true);
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
}
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">';
?>
<title>평목 - DESIGN IN REAL TIME</title>
<link rel="stylesheet" href="/src/css/common.css">
<link rel="stylesheet" href="/src/css/nav.css">

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
            <li class="nav-item d-none"><a href="#" class="nav-link">Works</a></li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Studio</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?= $isClassic ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/classic/classic.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <g transform="rotate(90 340 340)">
                                <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                                <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                                <rect fill="currentColor" x="148" y="148" width="46" height="384" rx="23"/>
                                <rect fill="currentColor" x="294" y="148" width="46" height="384" rx="23"/>
                                <rect fill="currentColor" x="486" y="148" width="46" height="384" rx="23"/>
                            </g>
                        </svg>
                        Classic Lattice
                    </a></li>
                    <li><a class="dropdown-item <?= $isSquare ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/square/square.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                            <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                            <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
                            <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
                        </svg>
                        Square Lattice
                    </a></li>
                    <li><a class="dropdown-item <?= $isCross ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/cross/cross.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <g transform="rotate(45 340 340)">
                                <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                                <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                                <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
                                <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
                            </g>
                        </svg>
                        Cross Lattice
                    </a></li>
                    <li><a class="dropdown-item <?= $isDiamond ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/diamond/diamond.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                            <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="23"/>
                            <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                            <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                        </svg>
                        Diamond Lattice
                    </a></li>
                    <li><a class="dropdown-item <?= $isTriangle ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/triangle/triangle.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                            <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                            <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                        </svg>
                        Triangle Lattice
                    </a></li>
                    <li><a class="dropdown-item <?= $isHexagon ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/hexagon/hexagon.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <polygon points="340,180 432,233 432,447 340,500 248,447 248,233" fill="none" stroke="currentColor" stroke-width="46" stroke-linejoin="round"/>
                        </svg>
                        Hexagon Lattice
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="/src/mypage/dashboard.php">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="8" height="8" rx="1.5"/>
                            <rect x="13" y="3" width="8" height="8" rx="1.5"/>
                            <rect x="3" y="13" width="8" height="8" rx="1.5"/>
                            <rect x="13" y="13" width="8" height="8" rx="1.5"/>
                        </svg>
                        Dashboard
                    </a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a href="/src/collection/" class="nav-link dropdown-toggle <?= $isLibrary ? 'active' : '' ?>"
                   data-bs-toggle="dropdown" aria-expanded="false">Collection</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/src/collection/">전체 컬렉션</a></li>
                    <li id="navBoardSection" style="display:none;">
                        <hr class="dropdown-divider">
                        <span class="dropdown-header" style="font-size:10px;letter-spacing:.06em;color:#aaa;padding:4px 16px 2px;">내 보드</span>
                    </li>
                    <div id="navBoardList"></div>
                </ul>
            </li>
            <li class="nav-item"><a href="/src/company/" class="nav-link <?= $isAbout ? 'active' : '' ?>">About</a></li>
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
                    <li><a class="dropdown-item" href="/src/mypage/profile.php">프로필</a></li>
                    <li><a class="dropdown-item" href="/src/mypage/company.php">회사 정보</a></li>
                    <li id="navAdminLink" style="display:none;"><hr class="dropdown-divider"></li>
                    <li id="navAdminMenu" style="display:none;"><a class="dropdown-item" href="/src/admin/users.php"><i class="bi bi-shield-lock me-1"></i>회원 관리</a></li>
                    <li id="navAdminStats" style="display:none;"><a class="dropdown-item" href="/src/admin/stats.php"><i class="bi bi-bar-chart-line me-1"></i>접속 통계</a></li>
                    <li id="navAdminLib" style="display:none;"><a class="dropdown-item" href="/src/admin/collection.php"><i class="bi bi-image me-1"></i>컬렉션 관리</a></li>
                    <li id="navAdminMeta" style="display:none;"><a class="dropdown-item" href="/src/admin/meta.php"><i class="bi bi-search me-1"></i>SEO 메타 관리</a></li>
                    <li id="navAdminSpaceCards" style="display:none;"><a class="dropdown-item" href="/src/admin/space_cards.php"><i class="bi bi-grid me-1"></i>공간 카드 관리</a></li>
                    <li id="navAdminHeroSlides" style="display:none;"><a class="dropdown-item" href="/src/admin/hero_slides.php"><i class="bi bi-images me-1"></i>슬라이드 관리</a></li>
                    <li id="navAdminWoodTypes" style="display:none;"><a class="dropdown-item" href="/src/admin/cost_table.php"><i class="bi bi-calculator me-1"></i>원가 테이블</a></li>
                    <li id="navAdminOauth" style="display:none;"><a class="dropdown-item" href="/src/admin/oauth.php"><i class="bi bi-key me-1"></i>SNS 로그인 설정</a></li>
                    <li id="navAdminColors" style="display:none;"><a class="dropdown-item" href="/src/admin/colors.php"><i class="bi bi-palette me-1"></i>컬러 팔레트 관리</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="authLogout();return false;">로그아웃</a></li>
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
