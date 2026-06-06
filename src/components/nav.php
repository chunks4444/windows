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
$isSabunteok  = (stripos($currentFile, 'Sabunteok') !== false);
$isSambuntok  = (stripos($currentFile, 'sambuntok') !== false);
$isIndex      = ($currentFile === 'index.php' || $_SERVER['PHP_SELF'] === '/');
$isAbout      = (strpos($_SERVER['PHP_SELF'], '/company/') !== false);
$isLibrary    = (strpos($_SERVER['PHP_SELF'], '/library/') !== false);
?>
<nav class="pm-navbar navbar navbar-expand-lg fixed-top px-4 py-3">
    <a href="/" class="navbar-brand d-flex align-items-center">
        <img src="https://pyeongmok.com/wp-content/uploads/2024/01/2024_MYEONGMOK_LOGO-e1705317786643.png"
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
                    <li><a class="dropdown-item <?= $isSabunteok ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/Sabunteok/Sabunteok.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                            <rect fill="currentColor" x="148" y="317" width="384" height="46" rx="23"/>
                            <g transform="rotate(45 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                            <g transform="rotate(135 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                        </svg>
                        Square Grid
                    </a></li>
                    <li><a class="dropdown-item <?= $isSambuntok ? 'active' : '' ?> d-flex align-items-center gap-2"
                           href="/src/engine/sambuntok/sambuntok.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/>
                            <g transform="rotate(60 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                            <g transform="rotate(120 340 340)"><rect fill="currentColor" x="317" y="148" width="46" height="384" rx="23"/></g>
                        </svg>
                        Triangle Grid
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="/dashboard.php">
                        <svg width="27" height="27" viewBox="0 0 680 680" xmlns="http://www.w3.org/2000/svg">
                            <rect fill="currentColor" x="148" y="204" width="384" height="46" rx="23"/>
                            <rect fill="currentColor" x="148" y="430" width="384" height="46" rx="23"/>
                            <rect fill="currentColor" x="204" y="148" width="46" height="384" rx="23"/>
                            <rect fill="currentColor" x="430" y="148" width="46" height="384" rx="23"/>
                        </svg>
                        내 도면
                    </a></li>
                </ul>
            </li>
            <li class="nav-item"><a href="/src/library/" class="nav-link <?= $isLibrary ? 'active' : '' ?>">Pattern Library</a></li>
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
                    <li id="navAdminCats" style="display:none;"><a class="dropdown-item" href="/src/admin/categories.php"><i class="bi bi-tags me-1"></i>카테고리 관리</a></li>
                    <li id="navAdminMeta" style="display:none;"><a class="dropdown-item" href="/src/admin/meta.php"><i class="bi bi-search me-1"></i>SEO 메타 관리</a></li>
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
